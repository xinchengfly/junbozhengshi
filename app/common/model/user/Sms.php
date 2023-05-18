<?php


namespace app\common\model\user;

use app\common\library\sms\Driver as SmsDriver;
use app\common\model\BaseModel;
use app\common\model\settings\Setting as SettingModel;

/**
 * 短信模型
 */
class Sms extends BaseModel
{
    protected $pk = 'sms_id';
    protected $name = 'sms';

    /**
     * 短信发送
     * $sence 场景，login：登录 apply：供应商申请
     */
    public function send($mobile, $sence = 'login'){
        if(empty($mobile)){
            $this->error = '手机号码不能为空';
            return false;
        }
        $smsConfig = SettingModel::getItem('sms', self::$app_id);
        $template_code = $smsConfig['engine'][$smsConfig['default']];
        $send_template = '';
        if($sence == 'login'){
            $send_template = $template_code['login_template'];
            if(empty($template_code)){
                $this->error = '短信登录未开启';
                return false;
            }
        }else if($sence == 'apply'){
            $send_template = $template_code['apply_template'];
        }else if($sence == 'register'){
            $send_template = $template_code['login_template'];
            if(empty($template_code)){
                $this->error = '短信登录未开启';
                return false;
            }
            //判断是否已经注册
            $user = (new User)->where('mobile','=',$mobile)->find();
            if($user){
                $this->error = '手机号码已存在';
                return false;
            }
        }
        $code  = str_pad(mt_rand(100000, 999999), 6, "0", STR_PAD_BOTH);
        $SmsDriver = new SmsDriver($smsConfig);
        $send_data = [
            'code' => $code
        ];
        //短信模板
        $flag = $SmsDriver->sendSms($mobile, $send_template, $send_data);
        if($flag){
            $this->save([
                'mobile' => $mobile,
                'code' => $code,
                'sence' => $sence,
                'app_id' => self::$app_id
            ]);
        }
        return $flag;
    }
    /**
     * 短信发送
     */
    public function sendTemplate($mobile,$template_code){
        if(empty($mobile)){
            $this->error = '手机号码不能为空';
            return false;
        }
        $smsConfig = SettingModel::getItem('sms', self::$app_id);
        $template_code = $smsConfig['engine'][$smsConfig['default']][$template_code];
        if(empty($template_code)){
            $this->error = '短信登录未开启';
            return false;
        }
        $SmsDriver = new SmsDriver($smsConfig);
        $send_data = [
            'code' => '112'
        ];
        //短信模板
        $flag = $SmsDriver->sendSms($mobile, $template_code,$send_data);
        return $flag;
    }
    
    
        /**
     * 调用短信宝发送验证码
     * @param $mobile
     * @param $user_id
     * @return bool|void|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function dxbSend($mobile)
    {
        if (empty($mobile)) {
            $this->error = '手机号码不能为空';
            return false;
        }
    
        //判断是否已经注册
        $user = (new User)->where('mobile', '=', $mobile)->find();
        if ($user) {
            $this->error = '手机号码已存在';
            return false;
        }

        $code = str_pad(mt_rand(100000, 999999), 6, "0", STR_PAD_BOTH);
        //短信模板
        $flag = Sms::dxbcode($mobile, $code);
        if ($flag==0) {
            $insert = [
                'mobile' => $mobile,
                'code' => $code,
                'sence' => 'apply',
                'app_id' => self::$app_id,
                'create_time' => time(),
                'update_time' => time()
            ];
            $sms = Sms::insert($insert);
            if ($sms) {
                return true;
            }
        } else {
            return $flag;
        }
    }

    /**
     * 短信宝
     * @return void
     */
    public static function dxbcode($mobile, $template_code)
    {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://www.smsbao.com/"; //短信网关
        $user = "ruanchengkeji"; //短信平台帐号
        $pass = md5("zsl123456"); //短信平台密码
        $content = "【人人租】手机绑定验证码:{$template_code}";//要发送的短信内容
        $phone = $mobile;
        $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
        $result = file_get_contents($sendurl);
        return $result;
    }
}