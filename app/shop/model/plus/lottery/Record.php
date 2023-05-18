<?php

namespace app\shop\model\plus\lottery;

use app\common\model\plus\lottery\Record as RecordModel;

/**
 * Class GiftPackage
 * 记录模型
 * @package app\common\model\plus\giftpackage
 */
class Record extends RecordModel
{
    /**
     * 记录列表
     * @param $data
     */
    public function getList($data)
    {
        $model = $this;
        //搜索会员昵称
        if ($data['search'] != '') {
            $model = $model->where('user_name|nickName|mobile', 'like', '%' . trim($data['search']) . '%');
        }
        if (!empty($data['reg_date'][0])) {
            $model = $model->whereTime('r.create_time', 'between', $data['reg_date']);
        }
        if ($data['status'] != '' && $data['status'] > -1) {
            $model = $model->where('r.status', '=', $data['status']);
        }
        return $model->alias('r')
            ->with(['user'])
            ->join('user u', 'r.user_id=u.user_id')
            ->field('r.*')
            ->order('r.create_time', 'desc')
            ->paginate($data);
    }
}