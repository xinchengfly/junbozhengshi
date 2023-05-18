<?php

namespace app\shop\model\plus\live;

use app\common\model\plus\live\Gift as GiftModel;

/**
 * 礼物模型
 */
class Gift extends GiftModel
{
    /**
     * 列表
     */
    public function getList($params)
    {
        return $this->with(['image'])->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate($params);
    }

    /**
     * 添加新记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 更新记录
     */
    public function edit($data)
    {
        return $this->save($data);
    }

    /**
     * 删除记录 (软删除)
     */
    public function setDelete($where)
    {
        return self::update(['is_delete' => 1], $where);
    }
}
