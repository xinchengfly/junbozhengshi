<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/12
 * Time: 15:10
 */

namespace app\common\model\manufacturer;

use app\common\model\BaseModel;

class Manufacturer extends BaseModel
{

    protected $pk = 'id';
    protected $name = 'manufacturer';

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
}