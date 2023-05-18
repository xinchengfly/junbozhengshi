<?php

namespace app\shop\controller\order;

use app\shop\controller\Controller;
use app\shop\model\order\Order as OrderModel;
use app\common\enum\settings\DeliveryTypeEnum;
use app\shop\model\settings\Express as ExpressModel;
use app\shop\model\store\Clerk as ShopClerkModel;
use app\shop\model\order\OrderOptLog as OrderOptLogModel;
use app\common\model\shop\OrderOptLog;
use app\common\service\Sendmsg;
use app\api\controller\order\OrderCenter;
use think\facade\Env;

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
        $list = $model->getList($dataType, $this->postData());
        $list = $model->getManufacturer($list);
        $order_count = [
            'order_count' => [
                'all' => $model->getCount('all'),//全部
                'payment' => $model->getCount('payment'),//代付款
                'delivery' => $model->getCount('delivery'),//代发货
                'received' => $model->getCount('received'),//待收货
                'examine' => $model->getCount('examine'),//待审核
                'haveInHand' => $model->getCount('haveInHand'),//进行中
                'returned' => $model->getCount('returned'),//待归还
                'complete' => $model->getCount('complete'),//已完成
                'cancel' => $model->getCount('cancel'),//已取消
                'Returning' => $model->getCount('Returning'),//归还中
//                'payment' => $model->getCount('payment'),
//                'delivery' => $model->getCount('delivery'),
//                'received' => $model->getCount('received'),
//                'cancel' => $model->getCount('cancel'),
            ],];
        $ex_style = DeliveryTypeEnum::data();
        return $this->renderSuccess('', compact('list', 'ex_style', 'order_count'));
    }

    /**
     * 订单详情
     */
    public function detail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);
        $detail['pay_price'] = round($detail['pay_price'] - $detail['coupon'], 2);
        if (isset($detail['pay_time']) && $detail['pay_time'] != '') {
            $detail['pay_time'] = date('Y-m-d H:i:s', $detail['pay_time']);
        }
        if (isset($detail['delivery_time']) && $detail['delivery_time'] != '') {
            $detail['delivery_time'] = date('Y-m-d H:i:s', $detail['delivery_time']);
        }
        $detail['buy_remark'] = json_decode($detail['buy_remark'], 1);
        // 物流公司列表
        $model = new ExpressModel();
        $expressList = $model->getAll();
        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getAll($detail['shop_supplier_id']);
        //备注
        $beizhu = (new OrderOptLog())->alias('log')->field(['log.*','user.user_name','user.real_name'])
            ->join('shop_user user', 'user.shop_user_id = log.shop_user_id','left')->where(['log.order_id' => $order_id])->select();
        return $this->renderSuccess('', compact('detail', 'expressList', 'shopClerkList', 'beizhu'));
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
        if ($model->delivery($this->postData('param'))) {
            $keyword = ["keyword1"=> ["value" => $data['address']['name']],"keyword2"=> ["value" => $data['address']['phone']],"keyword3"=> ["value" => $data['product'][0]['product_name']],"keyword4"=> ["value" => $data['address']['region']['province'].$data['address']['region']['city'].$data['address']['region']['region'].$data['address']['detail']],"keyword5"=> ["value" => $data['product'][0]['product_attr']]];
            //
            $sendmsg->sendmsg($data['user']['open_id'],'34eec90ff0fe41449a719457218fc577',$keyword);
            $data = [
                'app_id' => 10001,
                'order_id' => $order_id,
                'states' => 5,
            ];
            curlPost('https://'.Env::get('url.url', 'yuzhou.haidiao888.com').'/index.php/api/order.order_center/index2', $data);
            return $this->renderSuccess('发货成功');
        }
        return $this->renderError('发货失败');
    }

    public function editLogistics($order_id, $express_id, $express_no)
    {
        $model = OrderModel::detail($order_id);
        if (!empty($model)){
            $arr = $model->editLogistics(['express_id' => $express_id, 'express_no' => $express_no]);
            if ($arr){
                return $this->renderSuccess('修改成功');
            }else{
                return $this->renderError('修改失败');
            }
        }
        return $this->renderError('修改失败');
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
        if ($order['delivery_type'] == 10 && $order['delivery_status'] == 20) {
            return $this->renderError('订单已发货不允许修改');
        }
        // 获取物流信息
        $model = $order['address'];
        if (!$model->updateAddress($this->postData())) {
            return $this->renderError($model->getError() ?: '修改失败');
        }
        return $this->renderSuccess('修改成功');
    }

    //添加备注
    public function saveopt($order_id,$content){
        $shop_user_id = $this->store['user']['shop_user_id'];

        if(!$shop_user_id){
            return $this->renderError('添加失败');
        }
        $ordermodel = new OrderModel();
        $orderinfo = $ordermodel->where(['order_id'=>$order_id])->find();
        if (!$orderinfo){
            return $this->renderError('订单信息不存在');
        }
        $model = new OrderOptLogModel();


        if ($orderinfo['pay_status']['value'] == 10 && $orderinfo['order_status']['value'] == 1 && $orderinfo['deposit_pay_status'] == 1){
            $type = '待审核';
        }elseif ($orderinfo['pay_status']['value'] == 10 && $orderinfo['order_status']['value'] == 1 && $orderinfo['deposit_pay_status'] == 0){
            $type = '代付款';
        }elseif ($orderinfo['pay_status']['value'] == 20 && $orderinfo['delivery_status']['value'] == 10 && $orderinfo['order_status']['value'] == 10){
            $type = '代发货';
        }elseif ($orderinfo['pay_status']['value'] == 20 && $orderinfo['delivery_status']['value'] == 20 && $orderinfo['receipt_status']['value'] == 10 && $orderinfo['order_status']['value'] == 10){
            $type = '待收货';
        }elseif ($orderinfo['pay_status']['value'] == 20 && $orderinfo['delivery_status']['value'] == 20 && $orderinfo['receipt_status']['value'] == 20 && $orderinfo['order_status']['value'] == 10){
            $type = '进行中';
        }elseif ($orderinfo['pay_status']['value'] == 20 && $orderinfo['order_status']['value'] == 11){
            $type = '待归还';
        }elseif ($orderinfo['pay_status']['value'] == 20 && $orderinfo['delivery_status']['value'] == 20 && $orderinfo['receipt_status']['value'] == 20 && $orderinfo['order_status']['value'] == 30){
            $type = '已完成';
        }elseif ($orderinfo['order_status']['value'] == 20){
            $type = '已取消';
        }elseif ($orderinfo['pay_status']['value'] == 20 && $orderinfo['order_status']['value'] == 12){
            $type = '归还中';
        }elseif ($orderinfo['pay_status']['value'] == 20 && $orderinfo['order_status']['value'] == 29){
            $type = '拒绝归还';
        }elseif ($orderinfo['order_status']['value'] == 40){
            $type = '驳回';
        }

        $optdata = [
            'shop_user_id' => $shop_user_id,
            'order_id' => $order_id,
            'type' => $type,
            'content' => $content,
            'app_id' => $this->store['app']['app_id'],
            'create_time' => time()
        ];
        $model->create($optdata);
        return $this->renderSuccess('完成');
    }

    /**
     * 订单删除
     */
    public function delete($order_id)
    {
        $model = new OrderModel();
        // 订单详情
        $detail = OrderModel::detail($order_id);
        if (!$detail){
            return $this->renderError('未查询到订单');
        }
        $model->where('order_id', '=', $order_id)->update(['is_delete' => 1]);
        return $this->renderSuccess('删除成功');
    }

}