<?php

namespace app\shop\controller\cash;

use app\shop\controller\Controller;
use app\common\library\helper;
use app\shop\model\order\Order as OrderModel;
use app\shop\model\supplier\Supplier as SupplierModel;
use app\shop\model\plus\agent\User as AgentUserModel;
/**
 * 提现
 */
class Cash extends Controller
{
    /**
     * 首页概况
     */
    public function index()
    {
        // 平台统计数据
        $tj_data = [
            'total_money' => helper::number2((new OrderModel())->getTotalMoney('all')),
            'supplier_money' => helper::number2((new OrderModel())->getTotalMoney('supplier')),
            'sys_money' => helper::number2((new OrderModel())->getTotalMoney('sys')),
        ];
        // 供应商统计数据
        $supplier_data = [
            'total_money' => helper::number2((new SupplierModel())->getTotalMoney('total_money')),
            'money' => helper::number2((new SupplierModel())->getTotalMoney('money')),
            'nosettled_money' => helper::number2((new OrderModel())->getTotalMoney('supplier', 0)),
            'freeze_money' => helper::number2((new SupplierModel())->getTotalMoney('freeze_money')),
            'cash_money' => helper::number2((new SupplierModel())->getTotalMoney('cash_money')),
            'deposit_money' => helper::number2((new SupplierModel())->getTotalMoney('deposit_money')),
        ];
        // 分销商统计数据
        $agent_data = [
            'all_money' => (new AgentUserModel())->getTotalMoney('all_money'),
            'money' => (new AgentUserModel())->getTotalMoney('money'),
            'freeze_money' => (new AgentUserModel())->getTotalMoney('freeze_money'),
            'total_money' => (new AgentUserModel())->getTotalMoney('total_money'),
        ];
        return $this->renderSuccess('', compact( 'tj_data', 'supplier_data', 'agent_data'));
    }
}
