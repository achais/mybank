<?php

namespace Achais\MYBank\User;

use Achais\MYBank\Core\AbstractAPI;
use Achais\MYBank\Exceptions\HttpException;
use Achais\MYBank\Exceptions\InvalidArgumentException;
use Achais\MYBank\Support\Arr;
use Achais\MYBank\Support\Collection;
use Achais\MYBank\Support\Log;

class User extends AbstractAPI
{
    const FLAG_CARD_PERSON = '0';
    const FLAG_CARD_ORGANIZE = '1';

    /**
     * 注册企业会员
     *
     * @param $uid
     * @param $enterpriseName
     * @param $memo
     * @return Collection|null
     * @throws HttpException
     */
    public function enterpriseRegister($uid, $enterpriseName, $memo = '')
    {
        $service = 'mybank.tc.user.enterprise.register';
        $params = [
            'service' => $service,
            'memo' => $memo,
            'uid' => $uid,
            'enterprise_name' => $enterpriseName,
        ];

        return $this->parseJSON('post', $params);
    }
}