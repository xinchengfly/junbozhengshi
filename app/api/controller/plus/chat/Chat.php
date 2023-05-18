<?php

namespace app\api\controller\plus\chat;

use app\api\model\plus\chat\Chat as ChatModel;
use app\api\controller\Controller;
use \GatewayWorker\Lib\Gateway;
use app\api\model\settings\Setting as SettingModel;

/**
 * 客服消息
 */
class Chat extends Controller
{
    protected $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        $this->user = $this->getUser();
    }

    //我的聊天列表
    public function index()
    {
        $Chat = new ChatModel;
        $list = $Chat->myList($this->user);
        $url = SettingModel::getSysConfig()['url'];
        return $this->renderSuccess('', compact('list', 'url'));
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
    public function message()
    {
        $Chat = new ChatModel;
        $list = $Chat->getMessage($this->postData(), $this->user);
        return $this->renderSuccess('', compact('list'));
    }

    //获取聊天用户信息
    public function getInfo()
    {
        $Chat = new ChatModel;
        $info = $Chat->getInfo($this->postData());
        return $this->renderSuccess('', compact('info'));
    }

    //绑定uid
    public function bindClient()
    {
        $param = $this->postData();
        Gateway::bindUid($param['client_id'], $this->user['user_id']);
        $data['Online'] = Gateway::isUidOnline('supplier_' . $param['supplier_user_id']) ? 'on' : 'off';
        return $this->renderSuccess('绑定成功', compact('data'));
    }

}