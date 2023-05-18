<?php

namespace app\common\model\plus\lottery;

use app\common\model\BaseModel;

/**
 * Class GiftPackage
 * 转盘模型
 * @package
 */
class Lottery extends BaseModel
{
    protected $name = 'lottery';
    protected $pk = 'lottery_id';
    /**
     * 追加字段
     * @var string[]
     */
    protected $append = ['status_text'];

    /**
     * 转盘详情
     */
    public static function detail()
    {
        return (new static())->with(['image'])->find();
    }

    /**
     * 状态
     */
    public function getStatusTextAttr($value, $data)
    {
        $text = '';
        if ($value == 1) {
            $text = '开启';
        } else {
            $text = '关闭';
        }
        return $text;
    }
    /**
     * 关联奖项
     */
    public function prize()
    {
        return $this->hasMany('app\\common\\model\\plus\\lottery\\LotteryPrize', 'lottery_id', 'lottery_id');
    }
    /**
     * 关联文件库
     */
    public function image()
    {
        return $this->belongsTo('app\\common\\model\\file\\UploadFile', 'image_id', 'file_id')
            ->bind(['file_path']);
    }
}