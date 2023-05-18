<?php


namespace app\common\model\plus\chat;

use app\common\model\BaseModel;

/**
 * 客服消息模型
 */
class Chat extends BaseModel
{
    protected $pk = 'chat_id';
    protected $name = 'chat';

    /**
     * 关联会员表
     */
    public function user()
    {
        return $this->hasOne("app\\common\\model\\user\\User", 'user_id', 'user_id');
    }

    /**
     * 关联会员表
     */
    public function supplier()
    {
        return $this->hasOne("app\\common\\model\\supplier\\Supplier", 'shop_supplier_id', 'shop_supplier_id');
    }

    //获取聊天验证id
    public function getIdentify($user_id, $muser_id)
    {
        if ($user_id > $muser_id) {
            $identify = $user_id . '_' . $muser_id;
        } else {
            $identify = $muser_id . '_' . $user_id;
        }
        return $identify;
    }

    //添加信息
    public function add($data)
    {
        // 开启事务
        $this->startTrans();
        try {
            $ChatRelation = new ChatRelation();
            $this->save($data);
            $info = $ChatRelation->where('user_id', '=', $data['user_id'])->where('supplier_user_id', '=', $data['supplier_user_id'])->find();
            if (!$info) {
                $ChatRelation->save($data);
            } else {
                $info->save(['update_time' => time()]);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            log_write($e->getMessage());
            $this->rollback();
            return false;
        }

    }
}