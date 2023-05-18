<?php

namespace app\common\enum\settings;

use MyCLabs\Enum\Enum;
use app\common\enum\order\OrderPayTypeEnum;

/**
 * 支付方式枚举类
 */
class PlatformEnum extends Enum
{
    // 纯h5
    const H5 = 'h5';
    // 公众号
    const MP = 'mp';
    // 微信小程序
    const WX = 'wx';
    // IOS
    const IOS = 'ios';
    // ANDROID
    const ANDROID = 'android';
    /**
     * 获取枚举数据
     */
    public static function data()
    {
        return [
            self::H5 => [
                'name' => 'H5',
                'value' => self::H5,
                'pay_type' => array_keys(OrderPayTypeEnum::data())
            ],
            self::MP => [
                'name' => '微信公众号',
                'value' => self::MP,
                'pay_type' => array_keys(OrderPayTypeEnum::data())
            ],
            self::WX => [
                'name' => '微信小程序',
                'value' => self::WX,
                'pay_type' => array_keys(OrderPayTypeEnum::data())
            ],
            self::IOS => [
                'name' => 'ios',
                'value' => self::IOS,
                'pay_type' => array_keys(OrderPayTypeEnum::data())
            ],
            self::ANDROID => [
                'name' => 'android',
                'value' => self::ANDROID,
                'pay_type' => array_keys(OrderPayTypeEnum::data())
            ],
        ];
    }
}