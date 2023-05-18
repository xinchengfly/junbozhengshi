<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/5/8
 * Time: 15:02
 */

namespace app\job\controller;

use app\job\model\user\User as UserModel;

class Cark
{

    public function callback()
    {
        log_write('支付宝开卡回调------------------------------------开始-----------------------------------------------');
        $params = $_GET;
        log_write($params);
        log_write('支付宝开卡回调------------------------------------成功-----------------------------------------------');
        echo 'success';
    }

    //会员卡开通，获取会员卡信息
    public function opencard_get()
    {
        $params = $_POST;
        $biz_content = [
            'biz_card_no' => $params['biz_card_no'],
            'external_card_no' => $params['out_serial_no'],
            'open_date' => date('Y-m-d H:i:s',time()),
            'valid_date' => date('Y-m-d H:i:s',time()+31536000),
            'template_id' => $params['template_id'],
        ];
        $userModel = new UserModel();
        $data = $userModel->updateOpencardGet($params['user_id'], $params['template_id'], $params['biz_card_no'], $params['out_serial_no']);
        if ($data){
            $data = [
                'response' => [
                    'code' => '10000',
                    'msg' => 'Success',
                    'card_info' => $biz_content
                ]
            ];
        }else{
            $data = [
                'response' => [
                    'code' => '40004',
                    'msg' => 'Business Failed',
                    'sub_code' => "INVALID_PARAMS" ,
                    'sub_msg' => '系统错误'
                ]
            ];
        }
        echo json_encode($data, true);
        exit();
//        return json_encode($data, true);
    }
}
