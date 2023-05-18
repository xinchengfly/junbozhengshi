<?php

namespace app\api\model\plus\lottery;

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
    public function getList($data, $user)
    {
        $model = $this;
        return $model->alias('r')
            ->where('user_id', '=', $user['user_id'])
            ->field('r.*')
            ->order('r.create_time', 'desc')
            ->paginate($data);
    }

    /**
     * 记录列表
     * @param $data
     */
    public function getLimitList($limit)
    {
        $model = $this;
        return $model->alias('r')
            ->with(['user'])
            ->field('r.*')
            ->order('r.create_time', 'desc')
            ->limit($limit)
            ->select();
    }
}