<?php

namespace Achais\MYBank;

use Achais\MYBank\Core\Http;
use Achais\MYBank\Support\Arr;
use Achais\MYBank\Support\Log;
use Achais\MYBank\Trade\Trade;
use Achais\MYBank\User\User;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Application
 *
 * @property Trade $trade
 * @property User $user
 *
 * @package Achais\MYBank
 */
class MYBank extends Container
{
    protected $providers = [
        Foundation\ServiceProviders\UserProvider::class,
        Foundation\ServiceProviders\TradeProvider::class,
    ];

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        $this['config'] = function () use ($config) {
            return new Foundation\Config($config);
        };

        $this->registerBase();
        $this->registerProviders();
        $this->initializeLogger();

        Http::setDefaultOptions($this['config']->get('guzzle', ['timeout' => 5.0]));

        $this->logConfiguration($config);
    }

    public function logConfiguration($config)
    {
        $config = new Foundation\Config($config);

        $keys = ['partner_id'];
        foreach ($keys as $key) {
            !$config->has($key) || $config[$key] = '***' . substr($config[$key], -5);
        }

        Log::debug('Current config:', $config->toArray());
    }

    public function addProvider($provider)
    {
        array_push($this->providers, $provider);
        return $this;
    }

    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    private function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };
    }

    private function initializeLogger()
    {
        if (Log::hasLogger()) {
            return;
        }

        $logger = new Logger('mybank');

        if (!$this['config']['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($this['config']['log.handler'] instanceof HandlerInterface) {
            $logger->pushHandler($this['config']['log.handler']);
        } elseif ($logFile = $this['config']['log.file']) {
            try {
                $logger->pushHandler(new StreamHandler(
                        $logFile,
                        $this['config']->get('log.level', Logger::WARNING),
                        true,
                        $this['config']->get('log.permission', null))
                );
            } catch (\Exception $e) {
            }
        }

        Log::setLogger($logger);
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        if (is_callable([$this['fundamental.api'], $method])) {
            return call_user_func_array([$this['fundamental.api'], $method], $args);
        }

        throw new \Exception("Call to undefined method {$method}()");
    }

    /**
     * 验证签名
     * @param $params
     * @return bool
     */
    public function verifySignature($params)
    {
        if (!isset($params['sign']) || !isset($params['sign_type'])) {
            return false;
        }

        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['sign_type']);
        $signRaw = $this->httpBuildKSortQuery($params);

        $pubKey = $this['config']->getMYBankPublicKey();
        $res = openssl_get_publickey($pubKey);

        // 调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($signRaw, base64_decode($sign), $res);

        Log::debug('Verify Signature Result:', compact('result', 'params'));

        // 释放资源
        openssl_free_key($res);
        return $result;
    }

    private function httpBuildKSortQuery($params)
    {
        // 排序
        ksort($params);
        return urldecode(http_build_query($params));
    }
}