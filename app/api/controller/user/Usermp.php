<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\controller\logic\AlipayLoginLogic;
use app\api\model\user\UserMp as UserMpModel;
use app\common\library\easywechat\AppMp;

/**
 * 公众号用户管理
 */
class Usermp extends Controller
{

    /**
     * 用户自动登录
     */
    public function login($referee_id = '')
    {
        $app = AppMp::getApp($this->app_id);
        $redirect_uri = base_url()."index.php/api/user.usermp/login_callback?app_id={$this->app_id}&referee_id={$referee_id}";
        $app->oauth->scopes(['snsapi_userinfo'])->redirect($redirect_uri)->send();
    }

    /**
     * 用户自动登录
     */
    public function login_callback()
    {
        $app = AppMp::getApp($this->app_id);
        $oauth = $app->oauth;
        // 获取 OAuth 授权结果用户信息
        $userInfo = $oauth->user();
        // 保存数据库
        $model = new UserMpModel;
        $referee_id = $this->request->param('referee_id');
        $user_id = $model->login($userInfo, $referee_id);
        return redirect(base_url().'h5/pages/login/mplogin?app_id='.$this->app_id.'&token='.$model->getToken().'&user_id='.$user_id);
    }


    /**
     * Notes: 支付宝小程序旧用户登录
     * @author ljj(2021/6/25 18:12)
     */
    public function mnpAlipaySilentLogin()
    {
        $post = $this->request->post();
        if (empty($post['code'])) {
            return $this->renderError('参数缺失');
        }

        $data = AlipayLoginLogic::mnpAlipaySilentLogin($post);
        return $this->renderSuccess('',$data);
    }
    /**
     * Notes: 支付宝小程序新用户登录(新用户登录->需要提交昵称和头像参数)
     * @author zxy(2022-08-23 16:20)
     */
    public function mnpAlipayAuthLogin()
    {
        $post = $this->request->post();
        if (empty($post['code']))
        {
            return $this->renderError('参数缺失');
        }
        $data = AlipayLoginLogic::mnpAlipayAuthLogin($post);
        return $this->renderSuccess('',$data);
    }
}
