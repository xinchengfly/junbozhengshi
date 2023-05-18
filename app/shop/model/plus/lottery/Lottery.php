<?php

namespace app\shop\model\plus\lottery;

use app\common\model\plus\lottery\Lottery as LotteryModel;

/**
 *转盘模型
 */
class Lottery extends LotteryModel
{
    /**
     * 获取数据
     * @param $id
     */
    public function getLottery()
    {
        $data = $this->with(['image'])->find();
        return $data;
    }

    /**
     * 修改
     * @param $value
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            if (empty($data['lottery_id'])) {
                $model = $this;
                $data['app_id'] = self::$app_id;
            } else {
                $model = self::detail();
            }
            $model->save($data);
            $this->addPrize($data['prize'], $model['lottery_id'], $data['prize_ids']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    //添加奖项
    public function addPrize($prize, $lottery_id, $prize_ids)
    {
        $prize_ids && $this->prize()->where('prize_id', 'in', $prize_ids)->update(['status' => 20]);
        $data = array_map(function ($prize) use ($lottery_id) {
            return [
                'app_id' => self::$app_id,
                'lottery_id' => $lottery_id,
                'name' => $prize['name'],
                'stock' => $prize['stock'],
                'type' => $prize['type'],
                'image' => $prize['image'],
                'is_default' => $prize['is_default'],
                'award_id' => $prize['award_id'],
                'prize_id' => $prize['prize_id'],
                'points' => $prize['points'],
                'draw_num' => isset($prize['draw_num'])?$prize['draw_num']:0,
            ];
        }, $prize);
        return $this->prize()->saveAll($data);
    }
}