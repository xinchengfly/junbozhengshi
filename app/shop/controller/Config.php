<?php


namespace app\shop\controller;


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
                Db::name('config')->where('name','=','name')->update(['value'=>$post['name']]);
                Db::name('config')->where('name','=','ali_gateway_url')->update(['value'=>$post['ali_gateway_url']]);
                Db::name('config')->where('name','=','ali_app_id')->update(['value'=>$post['ali_app_id']]);
                Db::name('config')->where('name','=','ali_alipayrsa_publickey')->update(['value'=>$post['ali_alipayrsa_publickey']]);
                Db::name('config')->where('name','=','ali_rsa_privatekey')->update(['value'=>$post['ali_rsa_privatekey']]);
            }
            return $this->renderSuccess('设置成功');
        }
        $mnp = [
            'name'=>Db::name('config')->where('name','=','name')->value('value'),
            'ali_gateway_url'=>Db::name('config')->where('name','=','ali_gateway_url')->value('value'),
            'ali_app_id'=>Db::name('config')->where('name','=','ali_app_id')->value('value'),
            'ali_alipayrsa_publickey'=>Db::name('config')->where('name','=','ali_alipayrsa_publickey')->value('value'),
            'ali_rsa_privatekey'=>Db::name('config')->where('name','=','ali_rsa_privatekey')->value('value'),
        ];
        return $this->renderSuccess('',$mnp);
    }
}