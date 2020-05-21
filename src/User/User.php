<?php

namespace Achais\MYBank\User;

use Achais\MYBank\Core\AbstractAPI;
use Achais\MYBank\Exceptions\HttpException;
use Achais\MYBank\Support\Collection;

class User extends AbstractAPI
{
    /**
     * 注册个人会员
     * https://tc.mybank.cn/open/index/api/api_116.htm
     *
     * @param string $uid uid为合作方业务平台自定义，字母或数字，不能重复，不建议使用手机号作为 uid
     * 重复提交的开户请求根据uid 作幂等返回成功。接口若调用超时，平台可重新使用相同uid重复提交
     * @param string $realName 真实姓名
     * @param string $memberName 会员名称。用户昵称(平台个人会员登录名)
     * @param string $certificateNo 作为会员实名认证通过后的证件号
     * @param string $certificateType 目前个人会员只支持身份证。ID_CARD
     * @param string $mobile 合作方业务平台用户手机号
     * @param string $email 邮箱号
     * @param string $isVerify 是否认证 Y:是 N:否
     * @param string $isActive 预留字段，可不填 格式T,F
     * @param string $memo 备注信息
     * @return Collection|null
     * @throws HttpException
     */
    public function personalRegister($uid, $realName, $memberName, $certificateNo, $certificateType = 'ID_CARD',
                                     $memo = null, $mobile = null, $email = null, $isVerify = null, $isActive = null)
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
            'is_verify' => in_array($isVerify, ['Y', 'N']) ? $isVerify : null,
            'is_active' => in_array($isActive, ['T', 'F']) ? $isActive : null,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 注册企业会员
     * https://tc.mybank.cn/open/index/api/api_117.htm
     *
     * @param string $uid uid为合作方业务平台自定义，字母或数字，不能重复，不建议使用手机号作为 uid
     * 重复提交的开户请求根据uid 作幂等返回成功。接口若调用超时，平台可重新使用相同uid重复提交
     * @param string $enterpriseName 企业名称
     * @param string $memberName 企业简称
     * @param string $legalPerson 企业法人姓名
     * @param string $legalPersonCertificateType 法人证件类型
     * @param string $legalPersonCertificateNo 法人证件号
     * @param string $legalPersonPhone 法人手机号码
     * @param string $website 企业网址
     * @param string $address 企业地址
     * @param string $licenseNo 执照号,若营业执照号和统一社会信用代码都填写，则取统一社会信用代码
     * @param string $licenseAddress 营业执照所在地
     * @param string $licenseExpireDate 执照过期日（营业期限）yyyymmdd
     * @param string $businessScope 营业范围
     * @param string $telephone 联系电话
     * @param string $organizationNo 组织机构代码
     * @param string $unifiedSocialCreditCode 统一社会信用代码
     * @param string $summary 企业简介
     * @param string $openAccountLicense 开户许可证
     * @param string $isVerify 是否认证 Y:是 N:否
     * @param string $loginName 预留字段，可不填
     * @param string $isActive 预留字段，可不填 T
     * @param string $memo 备注信息
     * @return Collection|null
     * @throws HttpException
     */
    public function enterpriseRegister($uid, $enterpriseName, $memo = null, $memberName = null, $legalPerson = null, $legalPersonCertificateType = null,
                                       $legalPersonCertificateNo = null, $legalPersonPhone = null, $website= null, $address = null,
                                       $licenseNo = null, $licenseAddress = null, $licenseExpireDate = null, $businessScope = null,
                                       $telephone = null, $organizationNo = null, $unifiedSocialCreditCode = null, $summary = null,
                                       $openAccountLicense = null, $isVerify = null, $loginName = null, $isActive = null)
    {
        $service = 'mybank.tc.user.enterprise.register';
        $params = [
            'service' => $service,
            'memo' => $memo,
            'uid' => $uid,
            'enterprise_name' => $enterpriseName,
            'member_name' => $memberName,
            'legal_person' => $legalPerson,
            'legal_person_certificate_type' => $legalPersonCertificateType,
            'legal_person_certificate_no' => $legalPersonCertificateNo,
            'legal_person_phone' => $legalPersonPhone,
            'website' => $website,
            'address' => $address,
            'license_no' => $licenseNo,
            'license_address' => $licenseAddress,
            'license_expire_date' => $licenseExpireDate,
            'business_scope' => $businessScope,
            'telephone' => $telephone,
            'organization_no' => $organizationNo,
            'unified_social_credit_code' => $unifiedSocialCreditCode,
            'summary' => $summary,
            'open_account_license' => $openAccountLicense,
            'is_verify' => $isVerify,
            'login_name' => $loginName,
            'is_active' => $isActive,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 修改个人信息
     * https://tc.mybank.cn/open/index/api/api_118.htm
     *
     * @param string $uid 合作方业务平台用户ID。除uid外，其他要素均可修改
     * @param string $realName 真实姓名。当可空时，不修改原有信息
     * @param string $memberName 用户昵称(平台个人会员登录名) 。当可空时，不修改原有信息
     * @param string $certificateNo 证件类型，暂只支持身份证。见附录4.2证件类型。
     * @param string $certificateType 作为会员实名认证通过后的身份证号。当可空时，不修改原有信息
     * @param string $mobile 会员手机号 当可空时，不修改原有信息
     * @param string $email 邮箱号。当手机号为空时，邮箱不能为空
     * @param string $isVerify 是否认证 Y:是 N:否
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
            'real_name' => $realName,
            'member_name' => $memberName,
            'certificate_type' => $certificateType,
            'certificate_no' => $certificateNo,
            'mobile' => $mobile,
            'email' => $email,
            'is_verify' => in_array($isVerify, ['Y', 'N']) ? $isVerify : null,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 修改企业信息
     * https://tc.mybank.cn/open/index/api/api_119.htm
     *
     * @param string $uid uid为合作方业务平台自定义，字母或数字，不能重复，不建议使用手机号作为 uid
     * 重复提交的开户请求根据uid 作幂等返回成功。接口若调用超时，平台可重新使用相同uid重复提交
     * @param string $enterpriseName 企业名称
     * @param string $memberName 企业简称
     * @param string $legalPerson 企业法人姓名
     * @param string $legalPersonCertificateType 法人证件类型
     * @param string $legalPersonCertificateNo 法人证件号
     * @param string $legalPersonPhone 法人手机号码
     * @param string $website 企业网址
     * @param string $address 企业地址
     * @param string $licenseNo 执照号,若营业执照号和统一社会信用代码都填写，则取统一社会信用代码
     * @param string $licenseAddress 营业执照所在地
     * @param string $licenseExpireDate 执照过期日（营业期限）yyyymmdd
     * @param string $businessScope 营业范围
     * @param string $telephone 联系电话
     * @param string $organizationNo 组织机构代码
     * @param string $unifiedSocialCreditCode 统一社会信用代码
     * @param string $summary 企业简介
     * @param string $openAccountLicense 开户许可证
     * @param string $isVerify 是否认证 Y:是 N:否
     * @param string $loginName 预留字段，可不填
     * @param string $isActive 预留字段，可不填 T
     * @param string $memo 备注信息
     * @return Collection|null
     * @throws HttpException
     */
    public function enterpriseInfoModify($uid, $enterpriseName = null, $memberName = null, $legalPerson = null, $legalPersonCertificateType = null,
                                       $legalPersonCertificateNo = null, $legalPersonPhone = null, $website= null, $address = null,
                                       $licenseNo = null, $licenseAddress = null, $licenseExpireDate = null, $businessScope = null,
                                       $telephone = null, $organizationNo = null, $unifiedSocialCreditCode = null, $summary = null,
                                       $openAccountLicense = null, $isVerify = null, $loginName = null, $isActive = null, $memo = null)
    {
        $service = 'mybank.tc.user.enterprise.info.modify';
        $params = [
            'service' => $service,
            'memo' => $memo,
            'uid' => $uid,
            'enterprise_name' => $enterpriseName,
            'member_name' => $memberName,
            'legal_person' => $legalPerson,
            'legal_person_certificate_type' => $legalPersonCertificateType,
            'legal_person_certificate_no' => $legalPersonCertificateNo,
            'legal_person_phone' => $legalPersonPhone,
            'website' => $website,
            'address' => $address,
            'license_no' => $licenseNo,
            'license_address' => $licenseAddress,
            'license_expire_date' => $licenseExpireDate,
            'business_scope' => $businessScope,
            'telephone' => $telephone,
            'organization_no' => $organizationNo,
            'unified_social_credit_code' => $unifiedSocialCreditCode,
            'summary' => $summary,
            'open_account_license' => $openAccountLicense,
            'is_verify' => $isVerify,
            'login_name' => $loginName,
            'is_active' => $isActive,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 查询个人信息
     * https://tc.mybank.cn/open/index/api/api_120.htm
     *
     * @param string $uid 合作方业务平台用户ID
     * @return Collection|null
     * @throws HttpException
     */
    public function personalInfoQuery($uid)
    {
        $service = 'mybank.tc.user.personal.info.query';
        $params = [
            'service' => $service,
            'uid' => $uid,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 查询企业信息
     * https://tc.mybank.cn/open/index/api/api_121.htm
     *
     * @param string $uid 合作方业务平台用户ID
     * @return Collection|null
     * @throws HttpException
     */
    public function enterpriseInfoQuery($uid)
    {
        $service = 'mybank.tc.user.enterprise.info.query';
        $params = [
            'service' => $service,
            'uid' => $uid,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 查余额
     * https://tc.mybank.cn/open/index/api/api_153.htm
     * 根据uid和account_type查询账户余额。若指定account_type，则获取获取单个账户余额；反之查询该会员项下所有账户余额。
     *
     * @param string $uid 合作方业务平台用户ID
     * @param string $accountType 账户类型，若为空，则默认查询会员所有账户
     * @return Collection|null
     * @throws HttpException
     */
    public function accountBalance($uid, $accountType = null)
    {
        $service = 'mybank.tc.user.account.balance';
        $params = [
            'service' => $service,
            'uid' => $uid,
            'account_type' => $accountType,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 创建会员账户
     * https://tc.mybank.cn/open/index/api/api_149.htm
     *
     * @param string $uid 合作方业务平台用户ID
     * @param string $accountType 账户类型 如:DEPOSIT
     * @param string $alias 账户别名 如:保证金户
     * @return Collection|null
     * @throws HttpException
     */
    public function accountCreate($uid, $accountType, $alias)
    {
        $service = 'mybank.tc.user.account.create';
        $params = [
            'service' => $service,
            'uid' => $uid,
            'account_type' => $accountType,
            'alias' => $alias,
        ];

        return $this->parseJSON('post', $params);
    }

    /**
     * 主账户余额查询
     * https://tc.mybank.cn/open/index/api/api_148.htm
     *
     * @return Collection|null
     * @throws HttpException
     */
    public function partnerBalanceQuery()
    {
        $service = 'mybank.tc.user.partner.balance.query';
        $params = [
            'service' => $service,
        ];

        return $this->parseJSON('post', $params);
    }

}