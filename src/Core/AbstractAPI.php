<?php

namespace Achais\MYBank\Core;

use Achais\MYBank\Exceptions\HttpException;
use Achais\MYBank\Exceptions\InternalException;
use Achais\MYBank\Foundation\Config;
use Achais\MYBank\Support\Arr;
use Achais\MYBank\Support\Collection;
use Achais\MYBank\Support\Log;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

abstract class AbstractAPI
{
    /**
     * Http instance.
     *
     * @var Http
     */
    protected $http;

    /**
     * @var Config
     */
    protected $config;

    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';
    const PUT = 'put';
    const DELETE = 'delete';

    /**
     * @var int
     */
    protected static $maxRetries = 0;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }

    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp()
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }

        if (0 === count($this->http->getMiddlewares())) {
            $this->registerHttpMiddlewares();
        }

        return $this->http;
    }

    /**
     * Set the http instance.
     *
     * @param Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Return the current config.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the config.
     *
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param int $retries
     */
    public static function maxRetries($retries)
    {
        self::$maxRetries = abs($retries);
    }

    public function getBaseUrl()
    {
        $production = $this->getConfig()->get('production');
        if ($production) {
            return 'http://test.tc.mybank.cn/gop/gateway.do';
        } else {
            return 'http://test.tc.mybank.cn/gop/gateway.do';
        }
    }

    /**
     * Parse JSON from response and check error.
     *
     * @param $method
     * @param array $params
     * @return Collection|null
     * @throws HttpException
     */
    public function parseJSON($method, array $params)
    {
        $http = $this->getHttp();

        $charset = $this->getConfig()->get('tc.charset', 'utf-8');
        $version = $this->getConfig()->get('tc.version', '2.1');
        $partnerId = $this->getConfig()->get('tc.partner_id');

        $params['charset'] = $charset;
        $params['partner_id'] = $partnerId;
        $params['version'] = $version;

        $params = $this->buildSignatureParams($params);

        $contents = $http->parseJSON(call_user_func_array([$http, $method], [$this->getBaseUrl(), $params]));

        if (empty($contents)) {
            return null;
        }

        //$this->checkAndThrow($contents);

        return (new Collection($contents));
    }

    /**
     * mybank验签
     * @param $params
     * @return bool
     */
    public function verifySignature($params)
    {
        if (!isset($params['sign']) || !isset($params['sign_type'])) {
            return false;
        }
        $sign = $params['sign'];
        $signType = $params['sign_type'];
        unset($params['sign'], $params['sign_type']);

        $params = $this->filterNull($params);
        $signRaw = $this->httpBuildKSortQuery($params);

        if ($signType === 'RSA') {
            return $this->rsaCheckSign($signRaw, $sign);
        } else {
            //签名方式有误
            return false;
        }
    }

    private function buildSignatureParams($params)
    {
        //排除空参数
        $params = $this->filterNull($params);
        //拼接加密内容
        $signRaw = $this->httpBuildKSortQuery($params);
        //签名类型
        $signType = $this->getConfig()->get('tc.sign_type', 'TWSIGN');

        //天威诚信证书签名
        if ($signType === 'TWSIGN') {
            $params['sign'] = $this->twSign($signRaw);
        }

        $params['sign_type'] = $signType;

        return $params;
    }

    private function filterNull($params)
    {
        // 过滤空参数
        $params = Arr::where($params, function ($key, $value) {
            return !is_null($value);
        });
        return $params;
    }

    private function httpBuildKSortQuery($params)
    {
        // 排序
        ksort($params);
        return urldecode(http_build_query($params));
    }

    public static function randomString($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * rsa验签
     * @param $signRaw
     * @param $sign
     * @return bool
     */
    private function rsaCheckSign($signRaw, $sign)
    {
        $pubKey = $this->getMyBankPublicKey();
        $res = openssl_get_publickey($pubKey);

        // 调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($signRaw, base64_decode($sign), $res);

        Log::debug('Verify Signature:', [$result], $params);

        // 释放资源
        openssl_free_key($res);
        return $result;
    }

    /**
     * 获取公钥
     * @return string
     */
    private function getMyBankPublicKey()
    {
        return <<<s
-----BEGIN PUBLIC KEY-----
{$this->getConfig()->get('tc.public_key')}
-----END PUBLIC KEY-----
s;
    }

    /**
     * 字符串签名
     *
     * @param $signRaw
     * @return string
     * @throws InternalException
     */
    private function twSign($signRaw)
    {
        $certPath = $this->getConfig()->get('tc.cert_path');
        if (!file_exists($certPath)) {
            throw new InternalException('证书不存在');
        }
        $pkcs12 = file_get_contents($certPath);

        $certPassword = $this->getConfig()->get('tc.cert_password');
        if (openssl_pkcs12_read($pkcs12, $certs, $certPassword)) {
            //创建目录
            $date = date('Ymd', time());
            if (!is_dir(sys_get_temp_dir() . "/{$date}/")) {
                mkdir(sys_get_temp_dir() . "/{$date}/");
            }

            //需要签名的文件
            $inFileName = sys_get_temp_dir() . "/{$date}/" . self::randomString() . '.txt';
            file_put_contents($inFileName, $signRaw);

            //开始签名
            $outFileName = sys_get_temp_dir() . "/{$date}/" . self::randomString() . '.txt';
            if (openssl_pkcs7_sign($inFileName, $outFileName, $certs['cert'], $certs['pkey'], [], PKCS7_NOATTR)) {
                $signature = file_get_contents($outFileName);
                $signatureArray = explode("\n\n", $signature, 2);
                return trim($signatureArray[1]);
            } else {
                throw new InternalException('签名失败');
            }
        } else {
            throw new InternalException('证书读取失败');
        }
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // log
        $this->http->addMiddleware($this->logMiddleware());
        // signature
        $this->http->addMiddleware($this->signatureMiddleware());
    }

    protected function signatureMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->config) {
                    return $handler($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Request: {$request->getMethod()} {$request->getUri()} " . json_encode($options));
            Log::debug('Request headers:' . json_encode($request->getHeaders()));
        });
    }

    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param array $contents
     * @throws HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        $successCodes = ['T'];
        if (isset($contents['is_success']) && !in_array($contents['is_success'], $successCodes)) {
            if (empty($contents['error_message'])) {
                $contents['error_message'] = 'Unknown';
            }

            throw new HttpException(json_encode($contents));
        }
    }
}
