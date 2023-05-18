<?php

namespace app\shop\model\plus\table;

use app\common\model\plus\table\Record as RecordModel;

/**
 * 优惠券模型
 */
class Record extends RecordModel
{
    /**
     * 获取优惠券列表
     */
    public function getList($data)
    {
        $model = $this;
        if(isset($data['table_id']) && $data['table_id'] > 0){
            $model = $model->where('record.table_id', '=', $data['table_id']);
        }
        $list = $model->alias('record')->field(['record.*'])->with(['tableM', 'user'])
            ->join('table table', 'table.table_id = record.table_id','left')
            ->where('record.is_delete', '=', 0)
            ->where('table.is_delete', '=', 0)
            ->order(['record.create_time' => 'desc'])
            ->paginate($data);
        foreach ($list as &$item){
            $item['tableData'] = json_decode($item['content']);
            unset($item['content']);
        }
        return $list;
    }

    /**
     * 删除记录 (软删除)
     */
    public function setDelete()
    {
        $this->startTrans();
        try {
            $this->save([
                'is_delete' => 1
            ]);
            (new Table())->where('table_id', '=', $this['table_id'])->dec('total_count')->update();
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

}
