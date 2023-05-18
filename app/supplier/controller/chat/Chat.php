<?php

namespace app\supplier\controller\chat;

use app\supplier\model\chat\Chat as ChatModel;
use app\supplier\controller\Controller;
use app\supplier\model\user\User as UserModel;
use app\supplier\model\supplier\Supplier as SupplierModel;
use app\supplier\model\order\Order as OrderModel;
use \GatewayWorker\Lib\Gateway;

/**
 * 客服消息
 */
class Chat extends Controller
{

    //我的聊天列表
    public function index()
    {
        $Chat = new ChatModel;
        $list = $Chat->getList($this->supplier['user'], $this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    //我的实时消息聊天列表
    public function list()
    {
        $Chat = new ChatModel;
        $list = $Chat->getList($this->supplier['user'], $this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    //获取聊天信息
    public function record()
    {
        $Chat = new ChatModel;
        $list = $Chat->getMessage($this->postData(), $this->supplier['user']);
        return $this->renderSuccess('', compact('list'));
    }

    //获取未读消息条数
    public function messageCount()
    {
        $Chat = new ChatModel;
        $num = $Chat->mCount($this->supplier['user']);
        return $this->renderSuccess('', compact('num'));
    }

    //获取用户信息
    public function getInfo($user_id)
    {
        //用户信息
        $userInfo = UserModel::detail($user_id);
        //供应商信息
        $supplierInfo = SupplierModel::detail($this->getSupplierId(), ['logo']);
        //用户订单
        $orderList = (new OrderModel)->getOrderList($user_id, $this->postData());
        $data['userInfo'] = $userInfo;
        $data['logo'] = $supplierInfo['logo']['file_path'];
        $data['name'] = $supplierInfo['name'];
        $data['orderList'] = $orderList;
        return $this->renderSuccess('', compact('data'));
    }

    //绑定uid
    public function bindClient()
    {
        $data = $this->postData();
        Gateway::bindUid($data['client_id'], 'supplier_' . $this->supplier['user']['supplier_user_id']);
        return $this->renderSuccess('绑定成功');
    }


}