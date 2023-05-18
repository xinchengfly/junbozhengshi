<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/12
 * Time: 15:15
 */

namespace app\shop\model\manufacturer;

use app\common\model\manufacturer\Manufacturer as ManufacturerModel;

class Manufacturer extends ManufacturerModel
{

    /**
     * 厂商列表
     */
    public static function getList($params)
    {
        $model = new static();
        $where = $model->searchCriteria($params);
        // 获取用户列表
        return $model->where('is_delete', '=', 0)
            ->where($where)
            ->order('id desc')
            ->paginate($params);
    }

    /**
     * 新增记录
     */
    public function add($data)
    {
        return $this->save($data);
    }

    /**
     * 修改记录
     */
    public function edit($data)
    {
        $data['update_time'] = time();
        return $this->update($data, ['id', '=', $data['id']]);
    }


    /**
     * 软删除
     */
    public function setDelete()
    {
        return $this->transaction(function () {
            // 标记为已删除
            return $this->save(['is_delete' => 1]);
        });
    }
}