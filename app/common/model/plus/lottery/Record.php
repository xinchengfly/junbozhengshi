<?php

namespace app\common\model\plus\lottery;

use app\common\model\BaseModel;

/**
 * Class GiftPackage
 * 记录模型
 * @package app\common\model\plus\giftpackage
 */
class Record extends BaseModel
{
    protected $name = 'lottery_record';
    protected $pk = 'record_id';
    /**
     * 追加字段
     * @var string[]
     */
    protected $append = ['status_text', 'lottery_type_text'];

    /**
     * 礼包详情
     */
    public static function detail($record_id)
    {
        return (new static())->find($record_id);
    }

    /**
     * 状态
     */
    public function getStatusTextAttr($value, $data)
    {
        $text = '';
        if ($data['status'] == 1) {
            $text = '已使用';
        } else {
            $text = '未使用';
        }
        return $text;
    }

    /**
     * 状态
     */
    public function getLotteryTypeTextAttr($value, $data)
    {
        $text = [0 => '无礼品', 1 => '优惠券', 2 => '积分', 3 => '商品'];
        return $text[$data['prize_type']];
    }

    /**
     * 关联会员
     */
    public function user()
    {
        return $this->belongsTo('app\\common\\model\\user\\User', 'user_id', 'user_id');
    }
}