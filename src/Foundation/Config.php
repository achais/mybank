<?php


namespace Achais\MYBank\Foundation;


use Achais\MYBank\Support\Collection;

class Config extends Collection
{
    public function getInstantPayPrivateKey()
    {
        return <<<s
-----BEGIN RSA PRIVATE KEY-----
{$this->get('instant_pay.private_key')}
-----END RSA PRIVATE KEY-----
s;
    }

    public function getInstantPayPublicKey()
    {
        return <<<s
-----BEGIN PUBLIC KEY-----
{$this->get('instant_pay.public_key')}
-----END PUBLIC KEY-----
s;
    }

    public function getInstantPayLianLianPublicKey()
    {
        return <<<s
-----BEGIN PUBLIC KEY-----
{$this->get('instant_pay.ll_public_key')}
-----END PUBLIC KEY-----
s;
    }
}