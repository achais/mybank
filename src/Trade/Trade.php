<?php

namespace Achais\MYBank\Trade;

use Achais\MYBank\Core\AbstractAPI;
use Achais\MYBank\Exceptions\HttpException;
use Achais\MYBank\Support\Collection;

class Trade extends AbstractAPI
{

    const WITHDRAWAL_CARD_PERSON = 'C'; // 对私
    const WITHDRAWAL_CARD_ORGANIZE = 'B'; // 对公

    const CARD_TYPE_DEBIT = 'DC'; // 借记
    const CARD_TYPE_CREDIT = 'CC'; // 贷记（信用卡）

    /**
     * 生产有效的业务平台订单号(最好排重)
     *
     * @return string
     */
    public static function findAvailableTradeNo()
    {
        return date('YmdHis') . substr(explode(' ', microtime())[0], 2, 6) . rand(1000, 9999);
    }

    /**
     * 交易流水查询
     * https://tc.mybank.cn/open/index/api/api_126.htm
     *
     * 默认取最近12小时内的流水 时间不能大于12小时
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $memo
     * @return Collection|null
     * @throws HttpException
     */
    public function query($startDate = null, $endDate = null, $memo = null)
    {
        $service = 'mybank.tc.trade.query';
        $params = [
            'service' => $service,
            'memo' => $memo,
        ];
        if ($startDate == null) {
            $params['start_time'] = date('YmdHis', strtotime('-12 hours'));
            $params['end_time'] = date('YmdHis', time());
        } else {
            $startTime = strtotime($startDate);
            $params['start_time'] = date('YmdHis', $startTime);
            if ($endDate == null) {
                $params['end_time'] = date('YmdHis', strtotime($startDate . ' +12 hours'));
            } else {
                $endTime = strtotime($endDate);
                $params['end_time'] = date('YmdHis', $endTime);
            }
        }
        return $this->parseJSON('post', $params);
    }

    /**
     * 交易详情查询
     * https://tc.mybank.cn/open/index/api/api_127.htm
     *
     * @param string $outerTradeNo 业务平台订单号
     * @param string $memo 备注
     * @return Collection|null
     * @throws HttpException
     */
    public function infoQuery($outerTradeNo, $memo = null)
    {
        $service = 'mybank.tc.trade.info.query';
        $params = [
            'service' => $service,
            "outer_trade_no" => $outerTradeNo,
            'memo' => $memo,
        ];
        return $this->parseJSON('post', $params);
    }

    /**
     * 单笔提现到支付宝
     * @param string $outerTradeNo
     * @param string $outerInstOrderNo
     * @param string $uid
     * @param float $amount
     * @param string $alipayNo
     * @param string $alipayName
     * @param string $buyFee
     * @param string $memo
     * @param string $province
     * @param string $city
     * @throws HttpException
     * @return Collection|null
     */
    public function withdrawalToAliPay($outerTradeNo, $outerInstOrderNo, $uid, $amount,
                                       $alipayNo, $alipayName,
                                       $buyFee = null, $memo = null, $province = null, $city = null)
    {
        $cardAttribute = self::WITHDRAWAL_CARD_PERSON;
        $bankCode = 'ALIPAY';
        $bankName = null;
        $bankLineNo = null;
        $bankBranch = null;
        $cardType = self::CARD_TYPE_DEBIT;
        return self::withdrawalToCard($outerTradeNo, $outerInstOrderNo, $uid, $cardAttribute, $amount,
            $alipayNo, $alipayName, $bankCode, $bankName, $bankLineNo, $bankBranch,
            $buyFee = null, $memo = null, $cardType, $province = null, $city = null,
            $isWebAccess = null, $accountIdentity = null, $productCode = null, $payAttribute = null);
    }

    /**
     * 单笔提现到卡对私
     *
     * @param $outerTradeNo
     * @param $outerInstOrderNo
     * @param $uid
     * @param $amount
     * @param $bankAccountNo
     * @param $accountName
     * @param $bankName
     * @param string $buyFee
     * @param string $memo
     * @param string $province
     * @param string $city
     * @throws HttpException
     * @return Collection|null
     */
    public function withdrawalToCardPerson($outerTradeNo, $outerInstOrderNo, $uid, $amount,
                                      $bankAccountNo, $accountName, $bankName,
                                      $buyFee = null, $memo = null, $province = null, $city = null)
    {
        $cardAttribute = self::WITHDRAWAL_CARD_PERSON;
        $bankCode = null;
        $bankLineNo = null;
        $bankBranch = null;
        $cardType = self::CARD_TYPE_DEBIT;
        return self::withdrawalToCard($outerTradeNo, $outerInstOrderNo, $uid, $cardAttribute, $amount,
            $bankAccountNo, $accountName, $bankCode, $bankName, $bankLineNo, $bankBranch,
            $buyFee = null, $memo = null, $cardType, $province = null, $city = null,
            $isWebAccess = null, $accountIdentity = null, $productCode = null, $payAttribute = null);
    }

    /**
     * 单笔提现到卡对公
     *
     * @param $outerTradeNo
     * @param $outerInstOrderNo
     * @param $uid
     * @param $amount
     * @param $bankAccountNo
     * @param $accountName
     * @param $bankName
     * @param $bankLineNo
     * @param $bankBranch
     * @param string $buyFee
     * @param string $memo
     * @param string $province
     * @param string $city
     * @throws HttpException
     * @return Collection|null
     */
    public function withdrawalToCardOrganize($outerTradeNo, $outerInstOrderNo, $uid, $amount,
                                      $bankAccountNo, $accountName, $bankName,
                                      $bankLineNo, $bankBranch,
                                      $buyFee = null, $memo = null, $province = null, $city = null)
    {
        $cardAttribute = self::WITHDRAWAL_CARD_ORGANIZE;
        $cardType = self::CARD_TYPE_DEBIT;
        return self::withdrawalToCard($outerTradeNo, $outerInstOrderNo, $uid, $cardAttribute, $amount,
            $bankAccountNo, $accountName, $bankCode = null, $bankName, $bankLineNo, $bankBranch,
            $buyFee, $memo, $cardType, $province, $city,
            $isWebAccess = null, $accountIdentity = null, $productCode = null, $payAttribute = null);
    }


    /**
     * 单笔提现到卡/支付宝
     *
     * @param string $outerTradeNo 合作方业务平台订单号
     * @param string $outerInstOrderNo 外部机构订单号，合作方对接出款渠道使用的提现订单号。若出款渠道是网商银行，则此处填写与outer_trade_no保持一致。
     * @param string $uid 合作方业务平台用户ID
     * @param string $cardAttribute 卡属性:C:对私;B:对公
     * @param float $amount 提现金额。金额必须不大于账户可用余额
     * @param string $bankAccountNo 提现到银行卡此项为银行卡号；提现到支付宝为支付宝账号
     * @param string $accountName 户名（银行卡户名或者支付宝户名）
     * @param string $bankCode 1、提现到银行卡时可空;2、提现到支付宝时必填为ALIPAY
     * @param string $bankName 银行名称;提现到银行卡不可空；提现到支付宝为空
     * @param string $bankLineNo 银行分支行号;提现到银行卡时，根据卡属性card_attribute 对公不可空，对私可空；提现到支付宝为空
     * @param string $bankBranch 支行名称;提现到银行卡时，根据卡属性card_attribute 对公不可空，对私可空；提现到支付宝为空
     * @param string $buyFee 手费用
     * @param string $memo 备注
     * @param string $cardType 卡类型:DC:借记;CC:贷记（信用卡）
     * @param string $province 省份
     * @param string $city 城市
     * @param string $isWebAccess 预留字段，不填
     * @param string $accountIdentity 预留字段，不填（账户标识）
     * @param string $productCode 预留字段，不填
     * @param string $payAttribute 预留字段，不填（卡支付属性):NORMAL普通卡;QPAY快捷
     * @return Collection|null
     * @throws HttpException
     */
    private function withdrawalToCard($outerTradeNo, $outerInstOrderNo, $uid, $cardAttribute, $amount,
                                      $bankAccountNo, $accountName, $bankCode, $bankName, $bankLineNo, $bankBranch,
                                      $buyFee = null, $memo = null, $cardType = self::CARD_TYPE_DEBIT, $province = null, $city = null,
                                      $isWebAccess = null, $accountIdentity = null, $productCode = null, $payAttribute = null)
    {
        $service = 'mybank.tc.trade.withdrawtocard';
        $params = [
            //公共参数
            'service' => $service,
            'memo' => $memo,
            //必选参数
            'outer_trade_no' => $outerTradeNo,
            'uid' => $uid,
            'outer_inst_order_no' => $outerInstOrderNo,
            'white_channel_code' => $this->getConfig()->get('tc.white_channel_code'),
            'account_type' => 'BASIC',
            'bank_account_no' => $bankAccountNo,
            'account_name' => $accountName,
            //特定条件可为空
            'bank_code' => $bankCode,
            'bank_name' => $bankName,
            'bank_line_no' => $bankLineNo,
            'bank_branch' => $bankBranch,
            'card_type' => $cardType,
            'card_attribute' => $cardAttribute,
            'amount' => $amount,
            'notify_url' => $this->getConfig()->get('tc.notify_url'),
            //可空
            'province' => $province,
            'city' => $city,
            'fee_info' => [
                'buyerFee' => $buyFee,
            ],
            'is_web_access' => $isWebAccess,
            'account_identity' => $accountIdentity,
            'product_code' => $productCode,
            'pay_attribute' => $payAttribute,
        ];
        return $this->parseJSON('post', $params);
    }

    /**
     * 平台可调用该接口实现订单交易，合作方业务平台的买家在业务平台选择商品下单，付款成功后款项直接结算给卖家账户，业务平台交易成功后，将成功的交易同步给交易见证平台。
     *
     * @param string $outerTradeNo 合作方业务平台订单号
     * @param string $buyerId 买家在业务平台的ID（UID）
     * @param string $payMethod 支付方式，格式为Json，具体说明见下方接口参数补充说明。
     * @param string $subject 商品的标题/交易标题/订单标题/订单关键字等。
     * @param float $price 商品单价。单位为：RMB Yuan。取值范围为[0.01，1000000.00]，精确到小数点后两位。
     * @param int $quantity 商品的数量。
     * @param float $totalAmount 交易金额=（商品单价×商品数量）。卖家实际扣款和卖家实际收款金额计算规则请参考接口参数补充说明。
     * @param string $sellerId 卖家在业务平台的用户ID（UID）
     * @param string $accountType 卖家账户类型
     * @param null $memo
     * @return Collection|null
     * @throws HttpException
     */
    public function payInstant($outerTradeNo, $buyerId, $payMethod, $subject, $price, $quantity, $totalAmount, $sellerId,
                               $accountType, $memo = null)
    {
        $service = 'mybank.tc.trade.pay.instant';
        $params = [
            'service' => $service,
            "outer_trade_no" => $outerTradeNo,
            "buyer_id" => $buyerId,
            "pay_method" => $payMethod,
            "subject" => $subject,
            "price" => $price,
            "quantity" => $quantity,
            "total_amount" => $totalAmount,
            "seller_id" => $sellerId,
            "account_type" => $accountType,
            'notify_url' => $this->getConfig()->get('tc.notify_url'),
            'memo' => $memo,
        ];
        return $this->parseJSON('post', $params);
    }
}
