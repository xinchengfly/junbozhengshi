<?php


namespace app\admin\controller;


use think\facade\Db;

class Config extends Controller
{
    /**
     * 设置小程序
     * @return mixed
     */
    public function setMnp()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if ($post){
                Db::name('config')->where('name','=','name')->update(['name'=>$post['name']]);
                Db::name('config')->where('name','=','ali_gateway_url')->update(['ali_gateway_url'=>$post['ali_gateway_url']]);
                Db::name('config')->where('name','=','ali_app_id')->update(['ali_app_id'=>$post['ali_app_id']]);
                Db::name('config')->where('name','=','ali_alipayrsa_publickey')->update(['ali_alipayrsa_publickey'=>$post['ali_alipayrsa_publickey']]);
                Db::name('config')->where('name','=','ali_rsa_privatekey')->update(['ali_rsa_privatekey'=>$post['ali_rsa_privatekey']]);
            }
        }
        $mnp = Db::name('config')->where('type','=','ali_mnp')->select();
        return $this->renderSuccess('',$mnp);
    }
}