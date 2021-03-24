<h1 align="center"> MYBank </h1>

<p align="center"> 网商银行-云资金管理平台 API SDK for PHP..</p>

<p align="center"> 让你忽略第三方的 Http 请求规则、加密方式和加密实现, 只需关注自己的业务代码</p>


## 安装

```shell
$ composer require achais/mybank:dev-master -vvv
```

## 使用
配置信息和实例化
```php
use Achais\MYBank\MYBank;

$config = [
    'debug' => true,
    'production' => env('MYBANK_TC_PRODUCTION', false),

    'tc' => [
        'partner_id' => env('MYBANK_TC_PARTNER_ID'),
        'cert_path' => env('MYBANK_TC_CERT_PATH'),
        'cert_password' => env('MYBANK_TC_CERT_PASSWORD'),
        'notify_url' => env('MYBANK_TC_NOTIFY_URL', 'http://localhost/'),

        'mybank_public_key' => env('MYBANK_TC_PUBLIC_KEY'),
        'white_channel_code' => env('MYBANK_TC_WHITE_CHANNEL_CODE'),

        'version' => env('MYBANK_TC_VERSION', '2.1'),
        'charset' => env('MYBANK_TC_CHARSET', 'utf-8'),
        'production' => env('MYBANK_TC_PRODUCTION', false),
    ],

    // 日志
    'log' => [
        'level' => 'debug',
        'permission' => 0777,
        'file' => '/tmp/logs/mybank-' . date('Y-m-d') . '.log', // 日志文件, 你可以自定义
    ],
];

$mybank = new MYBank($config);
```
> 不管使用什么功能, 配置信息和实例化 MYBank 是必须的
