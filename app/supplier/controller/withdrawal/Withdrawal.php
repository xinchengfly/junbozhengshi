<?php


namespace app\supplier\controller\withdrawal;


use app\supplier\controller\Controller;
use app\supplier\model\withdrawal\Withdrawal as WithdrawalModel;
use think\facade\Db;

class Withdrawal extends Controller
{
    public function withdrawalList($list_rows){
        $data = WithdrawalModel::alias('w')
            ->field('w.*,u.nickName,u.username')
            ->join('user u','w.user_id=u.user_id')
            ->paginate(['list_rows' => $list_rows]);
        if ($data){
            return $this->renderSuccess('', $data);
        }
        return $this->renderError('系统反繁忙');
    }

    public function withdrawalAudit($withdrawal_id, $type){
        if (!empty($type) && $type){
           $res = WithdrawalModel::where('withdrawal_id','=',$withdrawal_id)->update(['type' => $type, 'time' => time()]);
        }
        if ($res){
            return $this->renderSuccess('审核成功');
        }
        return $this->renderError('审核失败');
    }
}