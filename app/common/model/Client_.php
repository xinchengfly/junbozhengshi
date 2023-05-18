<?php
// +----------------------------------------------------------------------
// | LikeShop100%开源免费商用电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | Gitee下载：https://gitee.com/likemarket/likeshopv2
// | 访问官网：https://www.likemarket.net
// | 访问社区：https://home.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 微信公众号：好象科技
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------

// | Author: LikeShopTeam
// +----------------------------------------------------------------------


namespace app\common\model;


class Client_
{
    const mnp = 1;//小程序
    const oa = 2;//公众号
    const ios = 3;//苹果APP
    const android = 4;//安卓APP
    const pc = 5;//PC端
    const h5 = 6;//h5(非微信环境h5)
    const ali_mnp = 7;//支付宝小程序

    function getName($value)
    {
        switch ($value) {
            case self::mnp:
                $name = '微信小程序';
                break;
            case self::h5:
                $name = '支付宝生活号';
                break;
            case self::ios:
                $name = '苹果APP';
                break;
            case self::android:
                $name = '安卓APP';
                break;
            case self::oa:
                $name = '微信公众号';
                break;
            case self::ali_mnp:
                $name = '支付宝小程序';
                break;
        }
        return $name;
    }

    public static function getClient($type = true)
    {
        $desc = [
            self::mnp     => '微信小程序',
            self::h5      => '支付宝生活号',
            self::ali_mnp => '支付宝小程序',
        ];
        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知';
    }
}