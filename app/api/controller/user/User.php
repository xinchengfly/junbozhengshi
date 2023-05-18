<?php

namespace app\api\controller\user;

use app\api\model\user\User as UserModel;
use app\api\controller\Controller;
use app\api\model\settings\Setting as SettingModel;
use app\common\library\easywechat\AppWx;
use think\facade\Db;

/**
 * 用户管理模型
 */
class User extends Controller
{
    /**
     * 用户自动登录,默认微信小程序
     */
    public function login()
    {
        $model = new UserModel;
        $user_id = $model->login($this->request->post());
        return $this->renderSuccess('', [
            'user_id' => $user_id,
            'token' => $model->getToken()
        ]);
    }

    /**
     * 当前用户详情
     */
    public function detail()
    {
        // 当前用户信息
        $userInfo = $this->getUser();
        $gift_name = SettingModel::getItem('live')['gift_name'];
        return $this->renderSuccess('', compact('userInfo', 'gift_name'));
    }

    public function getSession($code)
    {
        // 微信登录 获取session_key
        $app = AppWx::getApp();
        $session_key = AppWx::sessionKey($app, $code)['session_key'];
        return $this->renderSuccess('', compact('session_key'));
    }

    /**
     * 绑定手机号
     */
    public function bindMobile()
    {
        $model = $this->getUser();
        if ($model->bindMobile($this->request->post())) {
            return $this->renderSuccess('');
        }
        return $this->renderError('绑定失败');
    }

    /**
     * 修改用户信息
     */
    public function updateInfo()
    {
        // 当前用户信息
        $model = $this->getUser();
        $post = $this->request->post();
        if ($post['avatarUrl']) {
            $post['avatarUrl'] = explode('/', $post['avatarUrl']);
            unset($post['avatarUrl'][0]);
            unset($post['avatarUrl'][1]);
            unset($post['avatarUrl'][2]);
            $post['avatarUrl'] = implode('/', $post['avatarUrl']);
        }
        if ($model->edit($post)) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    public function updateInfo2()
    {
        // 当前用户信息
        $model = $this->getUser();
        $post = $this->request->post();
        if ($model['collect'] != 0){
            return $this->renderError($model->getError() ?: '修改失败');
        }
        if ($model->edit($post)) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 积分转换余额
     */
    public function transPoints($points = 0)
    {
        // 当前用户信息
        $model = $this->getUser();
        if ($model->transPoints($points)) {
            return $this->renderSuccess('转换成功');
        }
        return $this->renderError($model->getError() ?: '转换失败');
    }

    /**
     * 阿里云实名认证(验证姓名身份证账户)
     */
    public function realNameAuthentication($username,$usernum){
        $user = $this->getUser();
        if (!$username || !$usernum){
            return $this->renderError('请输入姓名和身份证号');
        }
        $data = $this->ce($username,$usernum);
        if ($data['code']==0){
            $res = UserModel::where('user_id','=',$user['user_id'])->update(['username'=>$username,'usernum'=>$usernum]);
            if ($res !== false){
                return $this->renderSuccess('认证成功');
            }else{
                return $this->renderError('认证失败,请稍后再试');
            }
        }
        return $this->renderError('身份证与姓名不符，请重新填写');
    }
    function ce($username,$usernum){
        $host = "https://eid.shumaidata.com";
        $path = "/eid/check";
        $method = "POST";
        $appcode = "198b15481d174f4fa083b9289e780b76";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "idcard=$usernum&name=".urlencode("$username");
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //设定返回信息中是否包含响应信息头，启用时会将头文件的信息作为数据流输出，true 表示输出信息头, false表示不输出信息头
        //如果需要将字符串转成json，请将 CURLOPT_HEADER 设置成 false
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $ce = curl_exec($curl);
        return json_decode($ce,true);
    }
    /**
     * 提现申请
     */
    public function withdrawalApplication(){
        $user = $this->getUser();
        $price = $this->request->param('price');
        $cardNumber = $this->request->param('cardNumber');
        $bankOfDeposit = $this->request->param('bankOfDeposit');
        $name = $this->request->param('name');
        Db::startTrans();
        try {
            UserModel::where('user_id','=',$user['user_id'])->dec('balance',$price)->update();
            $data =[
                'price' => $price,
                'user_id' => $user['user_id'],
                'name' => $name,
                'card_number' => $cardNumber,
                'bank_of_deposit' => $bankOfDeposit,
                'create_time' => time()
            ];
            Db::name('user_withdrawal')->insert($data);
            Db::commit();
            return $this->renderSuccess('申请成功');
        }catch (\Exception $e){
            log_write('=================用户提现异常======================');
            log_write($e->getMessage());
            log_write('=================================================');
            Db::rollback();
            return $this->renderError('系统繁忙，请稍后再试');
        }
    }
}