<?php

namespace app\api\controller\plus\points;

use app\api\model\plus\points\Product as ProductModel;
use app\api\service\order\settled\PointsOrderSettledService;
use app\api\controller\Controller;
use app\api\model\settings\Message as MessageModel;
use app\api\model\order\Order as OrderModel;
use app\api\service\pay\PayService;
use app\common\enum\order\OrderTypeEnum;
use app\common\library\helper;
use app\common\model\applist\Applist as ApplistModel;

/**
 * 积分商城兑换订单
 */
class Order extends Controller
{
    /**
     * 订单确认
     */
    public function buy()
    {
        // 积分商城兑换订单：获取订单商品列表
        $params = json_decode($this->postData()['params'], true);
        $post = $this->request->post();
        if (!empty($post['appid'])){
            $appid = $post['appid'];
            $data = ApplistModel::detail(['appid'=>$appid]);
            if (!$data){
                return $this->renderError('appid错误');
            }
            $appid_id = $data['id'];
        }else{
            $appid_id = 1;
        }
        $supplierData = ProductModel::getPointsProduct($params);
        $user = $this->getUser();
        // 实例化订单service
        $orderService = new PointsOrderSettledService($user, $supplierData, $params);
        //判断偏远地区
        $remoteAddress = $orderService->remoteAddress($params['address']);
        if ($remoteAddress){
            return $this->renderError('当前地区不在配送范围');
        }
        // 获取订单信息
        if (!isset($params['address'])){
            $params['address'] = [];
        }
        $orderInfo = $orderService->settlement($params['address']);

        if ($this->request->isGet()) {
            // 如果来源是小程序, 则获取小程序订阅消息id.获取支付成功,发货通知.
            $template_arr = MessageModel::getMessageByNameArr($params['pay_source'], ['order_pay_user', 'order_delivery_user']);
            // 是否开启支付宝支付
            $show_alipay = PayService::isAlipayOpen($params['pay_source'], $user['app_id']);
            $balance = $user['balance'];
            return $this->renderSuccess('', compact('orderInfo', 'template_arr', 'show_alipay', 'balance'));
        }
        // 订单结算提交
        if ($orderService->hasError()) {
            return $this->renderError($orderService->getError());
        }
        $orderInfo['orderData']['appid_id'] = $appid_id;
        // 创建订单
        $order_id = $orderService->createOrder($orderInfo);
        if (!$order_id) {
            return $this->renderError($orderService->getError() ?: '订单创建失败');
        }
        // 返回订单信息
        return $this->renderSuccess('', [
            'order_id' => $order_id,   // 订单号
        ]);
    }
}