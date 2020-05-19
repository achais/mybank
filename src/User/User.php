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
    /**
     * 注册个人会员
     *
     * @param $uid
     * @param $realName
     * @param $memberName
     * @param $certificateNo
     * @param string $certificateType
     * @param string $mobile
     * @param string $email
     * @param string $isVerify
     * @param string $isActive
     * @param string $memo
     * @return Collection|null
     * @throws HttpException
     */
    public function personalRegister($uid, $realName, $memberName, $certificateNo, $certificateType = 'ID_CARD',
                                     $mobile = null, $email = null, $isVerify = null, $isActive = null, $memo = null)
    {
        $service = 'mybank.tc.user.personal.register';
        $params = [
            'service' => $service,
            'memo' => $memo,
            'uid' => $uid,
            'mobile' => $mobile,
            'email' => $email,
            'real_name' => $realName,
            'member_name' => $memberName,
            'certificate_type' => $certificateType,
            'certificate_no' => $certificateNo,
            'is_verify' => in_array($isVerify, ['Y', 'N']) ? $isVerify : '',
            'is_active' => in_array($isActive, ['T', 'F']) ? $isActive : '',
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 注册企业会员
     *
     * @param $uid
     * @param $enterpriseName
     * @param $memo
     * @return Collection|null
     * @throws HttpException
     */
    public function enterpriseRegister($uid, $enterpriseName, $memo = null)
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

    /**
     * 修改个人信息
     *
     * @param $uid
     * @param $realName
     * @param $memberName
     * @param $certificateNo
     * @param string $certificateType
     * @param string $mobile
     * @param string $email
     * @param string $isVerify
     * @param string $memo
     * @return Collection|null
     * @throws HttpException
     */
    public function personalInfoModify($uid, $realName = null, $memberName = null, $certificateNo = null, $certificateType = 'ID_CARD',
                                       $mobile = null, $email = null, $isVerify = null, $memo = null)
    {
        $service = 'mybank.tc.user.personal.info.modify';
        $params = [
            'service' => $service,
            'memo' => $memo,
            'uid' => $uid,
            'mobile' => $mobile,
            'email' => $email,
            'real_name' => $realName,
            'member_name' => $memberName,
            'certificate_type' => $certificateType,
            'certificate_no' => $certificateNo,
            'is_verify' => in_array($isVerify, ['Y', 'N']) ? $isVerify : '',
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 合作方余额查询
     *
     * @param string $memo
     * @return Collection|null
     * @throws HttpException
     */
    public function partnerBalanceQuery($memo = null)
    {
        $service = 'mybank.tc.user.partner.balance.query';
        $params = [
            'service' => $service,
            'memo' => $memo,
        ];

        return $this->parseJSON('post', $params);
    }

}