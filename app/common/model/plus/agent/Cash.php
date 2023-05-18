<?php

namespace app\common\model\plus\agent;

use app\common\model\BaseModel;

/**
 * 分销商提现明细模型
 */
class Cash extends BaseModel
{
    protected $name = 'agent_cash';
    protected $pk = 'id';

    /**
     * 打款方式
     * @var array
     */
    public $payType = [
        10 => '微信',
        20 => '支付宝',
        30 => '银行卡',
    ];

    /**
     * 申请状态
     * @var array
     */
    public $applyStatus = [
        10 => '待审核',
        20 => '审核通过',
        30 => '驳回',
        40 => '已打款',
    ];

    /**
     * 关联分销商用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * 提现详情
     */
    public static function detail($id)
    {
        return (new static())->find($id);
    }

    /**
     * 审核状态
     * @param $value
     * @return array
     */
    public function getApplyStatusAttr($value)
    {
        $method = [10 => '待审核', 20 => '审核通过', 30 => '驳回', 40 => '已打款'];
        return ['text' => $method[$value], 'value' => $value];
    }

}