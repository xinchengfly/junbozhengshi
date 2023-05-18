<?php

namespace app\shop\controller\plus;

use app\shop\controller\Controller;
use app\shop\model\plus\lottery\Lottery as LotteryModel;
use app\shop\model\plus\lottery\Record as RecordModel;
use app\shop\model\plus\lottery\LotteryPrize as LotteryPrizeModel;

/**
 * 转盘控制器
 */
class Lottery extends Controller
{
    /**
     * 获取数据
     * @param null $id
     */
    public function getLottery()
    {
        $model = new LotteryModel();
        $data = $model->getLottery();
        $data['prize'] = $data ? LotteryPrizeModel::detail($data['lottery_id']) : [];
        return $this->renderSuccess('', compact('data'));
    }

    /**
     *修改
     */
    public function setting()
    {
        $model = new LotteryModel();
        if ($model->edit($this->postData())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /*
     * 转盘记录列表
     */
    public function record()
    {
        $model = new RecordModel();
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 获取下架奖项
     * @param null $id
     */
    public function award()
    {
        $model = new LotteryModel();
        $data = $model->getLottery();
        $list = LotteryPrizeModel::getlist($this->postData(), $data['lottery_id']);
        return $this->renderSuccess('', compact('list'));
    }
}