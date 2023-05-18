<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/4/14
 * Time: 10:58
 */

namespace app\api\controller\timer;

use app\common\model\order\Order as orderModel;
use think\facade\Db;

class Timer
{
    public function order_cancel()
    {
        $where = [];
        $where[] = ['is_delete', '=', '0'];
        $where[] = ['pay_status', '=', '10'];
        $where[] = ['order_status', '=', '1'];

        dump($where);
        $list = Db::name('order')->where($where)->select()->toArray();
        foreach ($list as $k => $v){
            dump($k);
            dump($v);
            exit();
        }
        exit();
    }
}