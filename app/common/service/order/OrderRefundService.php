<?php

namespace app\common\service\order;

use app\common\library\alipay\AliPay;
use app\common\library\easywechat\AppOpen;
use app\common\model\user\User as UserModel;
use app\common\model\user\BalanceLog as BalanceLogModel;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\user\balanceLog\BalanceLogSceneEnum;
use app\common\library\easywechat\WxPay;
use app\common\library\easywechat\AppWx;
use app\common\library\easywechat\AppMp;
use app\common\model\order\OrderTrade as OrderTradeModel;

/**
 * 订单退款服务类
 */
class OrderRefundService
{
    /**
     * 执行订单退款
     */
    public function execute(&$order, $money = null)
    {
        // 退款金额，如不指定则默认为订单实付款金额
        is_null($money) && $money = $order['pay_price'];
        $pay_type = $order['pay_type']['value'];
        if ($pay_type != OrderPayTypeEnum::BALANCE && $order['balance'] > 0) {
            if ($order['refund_money'] < $order['balance']) {
                if ($order['refund_money'] + $money > $order['balance']) {
                    $balance = round($order['balance'] - $order['refund_money'], 2);//余额退款金额
                    $money = round($money - $balance, 2);
                    $this->balance($order, $balance);
                } else {
                    $pay_type = 10;
                }
            }
        }
        // 1.微信支付退款
        if ($pay_type == OrderPayTypeEnum::WECHAT) {
            return $this->wxpay($order, $money);
        }
        // 2.余额支付退款
        if ($pay_type == OrderPayTypeEnum::BALANCE) {
            return $this->balance($order, $money);
        }
        // 3.支付宝退款
        if ($pay_type == OrderPayTypeEnum::ALIPAY) {
            return $this->alipay($order, $money);
        }
        return false;
    }

    /**
     * 余额支付退款
     */
    private function balance(&$order, $money)
    {
        // 回退用户余额
        $user = UserModel::detail($order['user_id']);
        $user->where('user_id', '=', $order['user_id'])->inc('balance', $money)->update();
        // 记录余额明细
        BalanceLogModel::add(BalanceLogSceneEnum::REFUND, [
            'user_id' => $user['user_id'],
            'money' => $money,
        ], ['order_no' => $order['order_no']]);
        return true;
    }

    /**
     * 微信支付退款
     */
    private function wxpay(&$order, $money)
    {
        $app = null;
        if ($order['pay_source'] == 'mp' || $order['pay_source'] == 'payH5') {
            $app = AppMp::getWxPayApp($order['app_id']);
        } else if ($order['pay_source'] == 'wx') {
            $app = AppWx::getWxPayApp($order['app_id']);
        } else if ($order['pay_source'] == 'android' || $order['pay_source'] == 'ios') {
            $app = AppOpen::getWxPayApp($order['app_id']);
        }
        //查询是否合并支付
        $tradeOrderInfo = OrderTradeModel::where('order_id', '=', $order['order_id'])
            ->where('pay_status', '=', 20)
            ->find();
        $pay_price = $order['online_money'];
        if ($tradeOrderInfo) {
            $pay_price = $tradeOrderInfo['online_money'];
        }
        $WxPay = new WxPay($app);
        return $WxPay->refund($order['transaction_id'], $pay_price, $money);
    }

    /**
     * 支付宝退款
     */
    private function alipay(&$order, $money)
    {
        $AliPay = new AliPay($order['pay_source']);
        return $AliPay->refund($order['transaction_id'], $order['order_no'], $money);
    }
}