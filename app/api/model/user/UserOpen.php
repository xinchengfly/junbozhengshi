<?php

namespace app\api\model\user;

use app\api\model\plus\agent\Referee as RefereeModel;
use think\facade\Cache;
use app\common\exception\BaseException;
use app\common\model\user\User as UserModel;
use app\common\model\user\Sms as SmsModel;
use app\common\model\user\Grade as GradeModel;

/**
 * 公众号用户模型类
 */
class UserOpen extends UserModel
{
    private $token;

    /**
     * 隐藏字段
     */
    protected $hidden = [
        'open_id',
        'is_delete',
        'app_id',
        'create_time',
        'update_time'
    ];

    /**
     * 用户登录
     */
    public function login($userInfo, $referee_id = null)
    {
        $user_id = $userInfo['user_id'];
        // 自动注册用户
//        $user_id = $this->register($userInfo, $referee_id);
        // 生成token (session3rd)
        $this->token = $this->token($userInfo['open_id']);
        // 记录缓存, 7天
        Cache::set($this->token, $user_id, 86400 * 7);
        return $user_id;
    }

    /**
     * 获取token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 生成用户认证的token
     */
    private function token($openid)
    {
        return md5($openid . 'token_salt');
    }

    /**
     * 自动注册用户
     */
    private function register($userInfo, $referee_id = null)
    {
        $data = [];
        //通过unionid查询用户是否存在
        $user = null;
        $data['union_id'] = '';

        if (isset($userInfo['open_id']) && !empty($userInfo['open_id'])) {
//            $data['union_id'] = $userInfo['unionid'];
            $user = self::detailByUnionid($userInfo['open_id']);
        }
        // 查询用户是否已存在
        if (!$user) {
            $user = self::detail(['open_id' => $userInfo['open_id']]);
        }

        if ($user) {
            $model = $user;
            // 只修改union_id
            $data = [
                'union_id' => $data['union_id'],
            ];
        } else {
            $model = $this;
            $data['referee_id'] = $referee_id;
//            $data['appopen_id'] = $userInfo['open_id'];
            // 用户信息
            $data['nickName'] = $userInfo['nickName'];
            $data['avatarUrl'] = $userInfo['avatarUrl'];
            $data['gender'] = $userInfo['gender'];
            $data['province'] = $userInfo['province'];
            $data['country'] = $userInfo['country'];
            $data['city'] = $userInfo['city'];
            $data['reg_source'] = 'app';
            //默认等级
            $data['grade_id'] = GradeModel::getDefaultGradeId();
        }

        try {
            $this->startTrans();
            // 保存/更新用户记录
            if (!$model->save(array_merge($data, [
                'app_id' => self::$app_id
            ]))
            ) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            if (!$user && $referee_id > 0) {
                // 记录推荐人关系，
                RefereeModel::createRelation($model['user_id'], $referee_id);
                //更新用户邀请数量
                (new UserModel())->where('user_id', '=', $referee_id)->inc('total_invite')->update();
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        return $model['user_id'];
    }

    /**
     * 手机号密码用户登录
     */
    public function phoneLogin($data)
    {
        $user = $this->where('mobile', '=', $data['mobile'])
            ->where('password', '=', md5($data['password']))
            ->order('user_id desc')
            ->find();
        if (!$user) {
            $this->error = '手机号或密码错误';
            return false;
        } else {
            if ($user['is_delete'] == 1) {
                $this->error = '手机号被禁止或删除，请联系客服';
                return false;
            }
            $user_id = $user['user_id'];
            $mobile = $user['mobile'];
        }
        // 生成token (session3rd)
        $this->token = $this->token($mobile);
        // 记录缓存, 30天
        Cache::tag('cache')->set($this->token, $user_id, 86400 * 30);
        return $user_id;
    }

    /**
     * 手机号密码用户登录
     */
    public function smslogin($data)
    {
        if (!$this->check($data)) {
            return false;
        }
        $user = $this->where('mobile', '=', $data['mobile'])->order('user_id desc')->find();
        if (!$user) {
            $this->error = '手机号不存在';
            return false;
        } else {
            if ($user['is_delete'] == 1) {
                $this->error = '手机号被禁止或删除，请联系客服';
                return false;
            }
            $user_id = $user['user_id'];
            $mobile = $user['mobile'];
        }
        // 生成token (session3rd)
        $this->token = $this->token($mobile);
        // 记录缓存, 30天
        Cache::tag('cache')->set($this->token, $user_id, 86400 * 30);
        return $user_id;
    }

    /*
    *重置密码
    */
    public function resetpassword($data)
    {
        if (!$this->check($data)) {
            return false;
        }
        $user = $this->where('mobile', '=', $data['mobile'])->order('user_id desc')->find();
        if ($user) {
            if ($user['is_delete'] == 1) {
                $this->error = '手机号被禁止或删除，请联系客服';
                return false;
            }
            return $this->where('mobile', '=', $data['mobile'])->update([
                'password' => md5($data['password'])
            ]);
        } else {
            $this->error = '手机号不存在';
            return false;
        }

    }

    /*
    *手机号注册
    */
    public function phoneRegister($data)
    {
        if (!$this->check($data)) {
            return false;
        }
        $user = $this->where('mobile', '=', $data['mobile'])->find();
        if (!$user) {
            return $this->save([
                'mobile' => $data['mobile'],
                'reg_source' => 'app',
                //默认等级
                'grade_id' => GradeModel::getDefaultGradeId(),
                'app_id' => self::$app_id,
                'password' => md5($data['password'])
            ]);
        } else {
            $this->error = '手机号已存在';
            return false;
        }

    }

    /**
     * 验证
     */
    private function check($data)
    {
        //判断验证码是否过期、是否正确
        $sms_model = new SmsModel();
        $sms_record_list = $sms_model
            ->where('mobile', '=', $data['mobile'])
            ->order(['create_time' => 'desc'])
            ->limit(1)->select();

        if (count($sms_record_list) == 0) {
            $this->error = '未查到短信发送记录';
            return false;
        }
        $sms_model = $sms_record_list[0];
        if ((time() - strtotime($sms_model['create_time'])) / 60 > 30) {
            $this->error = '短信验证码超时';
            return false;
        }
        if ($sms_model['code'] != $data['code']) {
            $this->error = '验证码不正确';
            return false;
        }
        return true;
    }
}
