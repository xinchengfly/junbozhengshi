<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/19
 * Time: 16:49
 */

namespace app\common\model\applist;

use app\common\model\BaseModel;

class Applist extends BaseModel
{
    protected $pk = 'id';
    protected $name = 'applist';

    /**
     * 获取风控信息
     */
    public static function detail($where)
    {
        $model = new static;
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['id'] = (int)$where;
        }
        return $model->where($filter)->find();
    }

    public function searchCriteria($params)
    {
        $where = [];
        //名字
        if (!empty($params['name'])){
            $where[] = ['name', 'like', '%'.$params['name'].'%'];
        }
        return $where;
    }

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