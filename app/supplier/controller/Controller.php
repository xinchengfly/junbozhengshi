<?php

namespace app\supplier\controller;

use app\common\exception\BaseException;
use app\common\model\settings\Setting;
use app\JjjController;
use app\supplier\service\AuthService;
use app\supplier\model\supplier\OptLog as OptLogModel;
/**
 * 商户后台控制器基类
 */
class Controller extends JjjController
{
    /** @var array $store 商家登录信息 */
    protected $supplier;

    /** @var string $route 当前控制器名称 */
    protected $controller = '';

    /** @var string $route 当前方法名称 */
    protected $action = '';

    /** @var string $route 当前路由uri */
    protected $routeUri = '';

    /** @var string $route 当前路由：分组名称 */
    protected $group = '';

    /** @var string $route 当前路由：分组名称 */
    protected $menu = '';
    /** @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        // 登录页面
        '/passport/login',
        /*系统设置*/
        '/index/base'
    ];

    /**
     * 后台初始化
     */
    public function initialize()
    {
        // 商家登录信息
        $this->supplier = session('jjjshop_supplier');

        // 当前路由信息
        $this->getRouteinfo();
        //  验证登录状态
        $this->checkLogin();
        // 写入操作日志
        $this->saveOptLog();
        // 验证当前页面权限
        $this->checkPrivilege();

    }
    /**
     * 操作日志
     */
    private function saveOptLog(){
        if($this->supplier == null){
            return;
        }
        $supplier_user_id = $this->supplier['user']['supplier_user_id'];
        if(!$supplier_user_id){
            return;
        }
        // 如果不记录查询日志
        $config = Setting::getItem('store');
        if(!$config || !$config['is_get_log']){
            return;
        }
        $model = new OptLogModel();
        $model->save([
            'supplier_user_id' => $supplier_user_id,
            'ip' => \request()->ip(),
            'request_type' => $this->request->isGet()?'Get':'Post',
            'url' => $this->routeUri,
            'content' => json_encode($this->request->param(), JSON_UNESCAPED_UNICODE),
            'browser' => get_client_browser(),
            'agent' => $_SERVER['HTTP_USER_AGENT'],
            'title' => AuthService::getAccessNameByPath($this->routeUri, $this->supplier['app']['app_id']),
            'app_id' => $this->supplier['user']['app_id'],
            'shop_supplier_id' => $this->supplier['user']['shop_supplier_id'],
        ]);
    }

    /**
     * 解析当前路由参数 （分组名称、控制器名称、方法名）
     */
    protected function getRouteinfo()
    {
        // 控制器名称
        $this->controller = strtolower($this->request->controller());
        $this->controller = str_replace(".","/",$this->controller);
        // 方法名称
        $this->action = Request()->action();
        // 控制器分组 (用于定义所属模块)
        $groupstr = strstr($this->controller, '.', true);
        $this->group = $groupstr !== false ? $groupstr : $this->controller;
        // 当前uri
        $this->routeUri =  '/' . $this->controller . '/' . $this->action;
    }

    /**
     * 验证登录状态
     */
    private function checkLogin()
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        // 验证登录状态
        if (!empty($this->supplier) || $this->supplier['is_login'] == 1) {
            return true;
        }
        throw new BaseException(['code' => -1, 'msg' => 'not_login']);
        return false;
    }

    /**
     * 获取供应商id
     */
    protected function getSupplierId(){
        return $this->supplier['user']['shop_supplier_id'];
    }

    /**
     * 验证当前页面权限
     */
    private function checkPrivilege()
    {
        if (!AuthService::getInstance()->checkPrivilege($this->routeUri)) {
            throw new BaseException(['msg' => '很抱歉，没有访问权限']);
        }
        return true;
    }
}
