<?php

namespace app\common\model\plus\table;

use app\common\model\BaseModel;

/**
 * 万能表单模型
 */
class Table extends BaseModel
{
    protected $name = 'table';
    protected $pk = 'table_id';

    /**
     * 获取详情
     */
    public static function detail($table_id)
    {
        $detail = (new static())->find($table_id);
        $detail['tableData'] = json_decode($detail['content'], true);
        unset($detail['content']);
        return $detail;
    }
}
