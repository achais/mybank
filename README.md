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
    'debug' => true, // 开启调试

    // 实时付款参数
    'mybank' => [
    
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
