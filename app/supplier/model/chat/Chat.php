<?php

namespace app\supplier\model\chat;

use app\common\model\plus\chat\Chat as ChatModel;

/**
 * 客服消息模型类
 */
class Chat extends ChatModel
{

    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
        'status',
        'update_time'
    ];

    //消息列表
    public function getList($user, $data)
    {
        $model = new ChatRelation();
        if ($data['nickName']) {
            $model = $model->where('ju.nickName', 'like', '%' . $data['nickName'] . '%');
        }
        $list = $model->with(['user', 'supplier.logo'])
            ->where(['supplier_user_id' => $user['supplier_user_id']])
            ->order('update_time desc')
            ->paginate($data);
        foreach ($list as $key => &$value) {
            $value['newMessage'] = $this->where('user_id', '=', $value['user_id'])
                ->where('supplier_user_id', '=', $value['supplier_user_id'])
                ->order('chat_id desc')
                ->find();
        }
        return $list;
    }

    //获取聊天信息
    public function getMessage($data, $user)
    {
        $list = $this->with(['user', 'supplier.logo'])
            ->where('supplier_user_id', '=', $user['supplier_user_id'])
            ->where('user_id', '=', $data['user_id'])
            ->order('chat_id desc')
            ->paginate($data);
        return $list;
    }

    //获取消息条数
    public function mCount($user)
    {
        $num = 0;
        if ($user) {
            $where[] = ['user_id', '=', $user['user_id']];
            $where[] = ['status', '=', 0];
            $where[] = ['msg_type', '=', 1];
            $num = $this->where($where)->count();
        }
        return $num;
    }
}
