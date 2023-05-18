<?php

namespace app\api\controller\plus\chat;

use app\api\model\plus\chat\Chat as ChatModel;
use app\api\controller\Controller;
use \GatewayWorker\Lib\Gateway;

/**
 * 客服消息
 */
class SupplierChat extends Controller
{
    protected $user;
    protected $supplierUser;
    /**
     * 构造方法
     */
    public function initialize()
    {
        $this->user = $this->getUser();
        $this->supplierUser = $this->getSupplierUser($this->user);
    }

    //我的聊天列表
    public function index()
    {
        $Chat = new ChatModel;
        $list = $Chat->mySupplierList($this->supplierUser['supplier_user_id']);
        return $this->renderSuccess('', compact('list'));
    }

    //添加消息
    public function add()
    {
        $Chat = new ChatModel;
        if ($Chat->add($this->postData(), $this->user)) {
            return $this->renderSuccess('发送成功');
        } else {
            return $this->renderError($Chat->getError() ?: '发送失败');
        }

    }

    //获取聊天信息
    public function message($user_id)
    {
        $Chat = new ChatModel;
        $user['user_id'] = $user_id;
        $data = $this->postData();
        $data['supplier_user_id'] = $this->supplierUser['supplier_user_id'];
        $list = $Chat->getMessage($data, $user);
        return $this->renderSuccess('', compact('list'));
    }

    //获取消息条数
    public function messageCount()
    {
        $Chat = new ChatModel;
        $num = $Chat->mCount($this->user);
        return $this->renderSuccess('', compact('num'));
    }

    //获取聊天用户信息
    public function getInfo()
    {
        $Chat = new ChatModel;
        $data = $this->postData();
        $data['shop_supplier_id'] = $this->supplierUser['shop_supplier_id'];
        $data['supplier_user_id'] = $this->supplierUser['supplier_user_id'];
        $info = $Chat->getInfo($data);
        return $this->renderSuccess('', compact('info'));
    }

    //绑定uid
    public function bindClient()
    {
        $param = $this->postData();
        Gateway::bindUid($param['client_id'], 'supplier_' .$this->supplierUser['supplier_user_id']);
        $data['Online'] = Gateway::isUidOnline($param['user_id']) ? 'on' : 'off';
        return $this->renderSuccess('绑定成功', compact('data'));
    }

}