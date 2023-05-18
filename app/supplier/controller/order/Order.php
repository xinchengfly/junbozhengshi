<?php

namespace app\supplier\controller\order;

use app\api\model\user\User;
use app\common\enum\settings\DeliveryTypeEnum;
use app\supplier\model\order\OrderBill;
use app\supplier\model\store\Store as StoreModel;
use app\supplier\controller\Controller;
use app\supplier\model\order\Order as OrderModel;
use app\common\model\settings\Express as ExpressModel;
use app\supplier\model\store\Clerk as ClerkModel;
use app\common\Service\Sendmsg;

/**
 * 订单控制器
 */
class Order extends Controller
{
    /**
     * 订单列表
     */
    public function index($dataType = 'all')
    {
        // 订单列表
        $model = new OrderModel();
        $list = $model->getList($dataType, $this->postData(), $this->getSupplierId());

        $order_count = [
            'order_count' => [
                'all' => $model->getCount('all', $this->getSupplierId()),//全部
                'payment' => $model->getCount('payment', $this->getSupplierId()),//代付款
                'delivery' => $model->getCount('delivery', $this->getSupplierId()),//代发货
                'received' => $model->getCount('received', $this->getSupplierId()),//待收货
                'examine' => $model->getCount('examine', $this->getSupplierId()),//待审核
                'haveInHand' => $model->getCount('haveInHand', $this->getSupplierId()),//进行中
                'returned' => $model->getCount('returned', $this->getSupplierId()),//待归还
                'complete' => $model->getCount('complete', $this->getSupplierId()),//已完成
                'cancel' => $model->getCount('cancel', $this->getSupplierId()),//已取消
                'Returning' => $model->getCount('Returning', $this->getSupplierId()),//归还中

            ],];
        // 自提门店列表
        $shop_list = StoreModel::getAllList($this->getSupplierId());

        $ex_style = DeliveryTypeEnum::data();
        return $this->renderSuccess('', compact('list', 'order_count', 'shop_list', 'ex_style'));
    }

    /**
     * 订单详情
     */
    public function detail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);

//获取分期账单-------------------------------------------------
//        if ($detail['order_status']['value'] == 1){
            $data = OrderBill::getOrderBill($order_id);
            $order_bill =[];
            foreach ($data as $k=>$v){
                $order_bill[] = [
                    'bill_id'=>$v['bill_id'],
                    'order_id'=>$v['order_id'],
                    'price'=>$v['price'],
                    'current_period'=>$v['current_period'],
                    'repayment_date'=>date('Y-m-d',$v['repayment_date']),
                    'is_pay'=>$v['is_pay'],
                    'is_pay_status'=>$v['is_pay_status'],
                ];
            }
//        }else{
//            $order_bill =[];
//        }
//------------------------------------------------------------------------
        if (isset($detail['pay_time']) && $detail['pay_time'] != '') {
            $detail['pay_time'] = date('Y-m-d H:i:s', $detail['pay_time']);
        }
        if (isset($detail['delivery_time']) && $detail['delivery_time'] != '') {
            $detail['delivery_time'] = date('Y-m-d H:i:s', $detail['delivery_time']);
        }
        // 物流公司列表
        $model = new ExpressModel();
        $expressList = $model->getAll();
        // 门店店员列表
        $shopClerkList = (new ClerkModel)->getClerk($detail['extract_store_id']);
        return $this->renderSuccess('', compact('detail', 'expressList', 'shopClerkList','order_bill'));
    }

    /**
     * 确认发货
     */
    public function delivery($order_id)
    {
        $sendmsg = new Sendmsg();
        $orderModel = new OrderModel();
        $model = OrderModel::detail($order_id);
        $data = $orderModel->with(['address', 'user','product'])->where('order_id','=',$order_id)->find()->toArray();
        dump($data);
        exit();
        if ($model->delivery($this->postData('param'))) {

            $keyword = ["keyword1"=> ["value" => $data['product'][0]['product_name']],"keyword2"=> ["value" => date('Y-m-d H:i:s')],"keyword3"=> ["value" => '您租赁的商品已经通过审核,请保持手机联系通畅'],"keyword4"=> ["value" => $data['total_price']],"keyword5"=> ["value" => $data['order_no']]];
            $sendmsg->sendmsg($data['user']['open_id'],'34eec90ff0fe41449a719457218fc577',$keyword);
            return $this->renderSuccess('发货成功');
        }
        return $this->renderError('发货失败');
    }

    /**
     * 修改订单价格
     */
    public function updatePrice($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->updatePrice($this->postData('order'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 获取物流信息
     */
    public function express($order_id)
    {
        // 订单信息
        $order = OrderModel::detail($order_id);
        if (!$order['express_no']) {
            return $this->renderError('没有物流信息');
        }
        // 获取物流信息
        $model = $order['express'];
        $express = $model->dynamic($model['express_name'], $model['express_code'], $order['express_no']);
        if ($express === false) {
            return $this->renderError($model->getError());
        }
        return $this->renderSuccess('', compact('express'));
    }

    /**
     * 订单改地址
     */
    public function updateAddress($order_id)
    {
        // 订单信息
        $order = OrderModel::detail($order_id);
        if ($order['delivery_type']['value'] == 10 && $order['delivery_status']['value'] == 20) {
            return $this->renderError('订单已发货不允许修改');
        }
        // 获取物流信息
        $model = $order['address'];
        if (!$model->updateAddress($this->postData())) {
            return $this->renderError($model->getError() ?: '修改失败');
        }
        return $this->renderSuccess('修改成功');
    }

    /**
     * 取消订单
     */
    public function orderCancel($order_no)
    {
        // 订单信息
        $model = OrderModel::detail(['order_no' => $order_no]);
        if ($model->orderCancel($this->postData())) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 虚拟商品发货
     */
    public function virtual($order_id)
    {
        // 订单信息
        $model = OrderModel::detail($order_id);
        if ($model->virtual($this->postData())) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 归还审核
     */
    public function returnReview($order_id,$type){
        if ($type==1){
            $order = OrderModel::where('order_id','=',$order_id)->find();
            $res = OrderModel::where('order_id','=',$order_id)->update(['order_status'=>30]);
            if ($res){
                $res = User::where('user_id','=',$order['user_id'])->inc('balance',$order['deposit'])->update();
                if ($res){
                    return $this->renderSuccess('操作成功');
                }
            }
        }else if ($type == 2){
            $order = OrderModel::where('order_id','=',$order_id)->find();
            $res = OrderModel::where('order_id','=',$order_id)->update(['order_status'=>29]);
            if ($res){
                    return $this->renderSuccess('操作成功');
            }
        }

        return $this->renderError('操作失败');
    }
}