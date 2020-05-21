<?php


namespace Achais\MYBank\Foundation;


use Achais\MYBank\Support\Collection;

class Config extends Collection
{
    public function getMYBankPublicKey()
    {
        return <<<s
-----BEGIN PUBLIC KEY-----
{$this->get('tc.mybank_public_key')}
-----END PUBLIC KEY-----
s;
    }
}