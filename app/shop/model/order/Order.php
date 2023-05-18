<?php

namespace app\shop\model\order;

use app\common\model\order\Order as OrderModel;
use app\common\enum\order\OrderPayStatusEnum;
use app\shop\service\order\ExportService;
use app\common\model\manufacturer\Manufacturer as ManufacturerModel;
use think\facade\Db;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\common\service\Sendmsg;

/**
 * 订单模型
 */
class Order extends OrderModel
{
    /**
     * 订单列表
     */
    public function getList($dataType, $data = null)
    {
        $model = $this;
        // 检索查询条件
        $model = $model->setWhere($model, $data);
        $where = $this->transferDataType($dataType);
        if (isset($where['order_status']) && $where['order_status'] == 30) {
            unset($where['order_status']);
            return $model->alias('order')
                ->join('user', 'user.user_id=order.user_id')
                ->join('order_product', 'order_product.order_id = order.order_id')
                ->with(['product' => ['image', 'refund'], 'user', 'supplier', 'address', 'applist'])
                ->where('order.is_delete', '=', 0)
                ->where('order.order_status', 'in', [30, 32, 31])
                ->field('order.*')
                ->order(['order.create_time' => 'desc'])
                ->paginate($data);
        }
        // 获取数据列表
        return $model->alias('order')
            ->join('user', 'user.user_id=order.user_id')
            ->join('order_product', 'order_product.order_id = order.order_id')
            ->with(['product' => ['image', 'refund'], 'user', 'supplier', 'address', 'applist'])
            ->where('order.is_delete', '=', 0)
            ->where($where)
            ->field('order.*')
            ->order(['order.create_time' => 'desc'])
            ->paginate($data);
    }

    /**
     * 获取订单总数
     */
    public function getCount($type = 'all')
    {
        // 筛选条件
        $filter = [];
        $filter['is_delete'] = 0;
        // 订单数据类型
        switch ($type) {
            case 'all'://全部
                break;
            case 'examine';//待审核
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 21;
                break;
            case 'payment';//代付款
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 1;
                break;
            case 'delivery';//代发货
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';//待收货
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'haveInHand';//进行中
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 20;
                $filter['order_status'] = 10;
                break;
            case 'returned';//待归还
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 11;
                break;
            case 'complete';//已完成
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 20;
                $filter['order_status'] = 30;
                break;
            case 'cancel';//已取消
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 20;
                break;
            case 'Returning';//归还中
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 20;
                break;
        }
//        switch ($type) {
//            case 'all':
//                break;
//            case 'payment';
//                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
//                $filter['order_status'] = 10;
//                break;
//            case 'delivery';
//                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
//                $filter['delivery_status'] = 10;
//                $filter['order_status'] = 10;
//                break;
//            case 'received';
//                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
//                $filter['delivery_status'] = 20;
//                $filter['receipt_status'] = 10;
//                $filter['order_status'] = 10;
//                break;
//            case 'cancel';
//                $filter['order_status'] = 21;
//                break;
//
//        }
        return $this->where($filter)->count();
    }

    /**
     * 订单列表(全部)
     */
    public function getListAll($dataType, $query = [])
    {
        $model = $this;
        // 检索查询条件
        $model = $model->setWhere($model, $query);
        // 获取数据列表
        return $model->with(['product.image', 'address', 'user', 'extract', 'extract_store'])
            ->alias('order')
            ->field('order.*')
            ->join('user', 'user.user_id = order.user_id')
            ->join('order_product', 'order_product.order_id = order.order_id')
            ->where($this->transferDataType($dataType))
            ->where('order.is_delete', '=', 0)
            ->order(['order_product.manufacturer_id' => 'desc'])
            ->order(['order_product.category_id' => 'desc'])
            ->order(['order.create_time' => 'desc'])
            ->select();
    }

    /**
     * 订单导出
     */
    public function exportList($dataType, $query)
    {
        // 获取订单列表
        $list = $this->getListAll($dataType, $query);
        $list = $this->getManufacturer($list);
        // 导出excel文件
        return (new Exportservice)->orderList($list);
    }

    /**
     * 一键订单导出
     */
    public function exportListOne($dataType, $query)
    {
        // 获取订单列表
        $list = $this->getListAll($dataType, $query);
        $list = $this->getManufacturer($list);
//        $list = json_decode(json_encode($list, true), true);
        $data = [];
        foreach ($list as $key => $value) {
            $data[] = $value['product'][0]['manufacturer_id'];
        }
        $listData = [];
        $data = array_unique($data);
        foreach ($data as $key => $value) {
            $listData[$key] = [];
            foreach ($list as $key2 => $value2) {
                if ($value == $value2['product'][0]['manufacturer_id']){
                    $listData[$key][] = $value2;
                }
            }
        }
        //存放文件名称
        $excelData = [];
        foreach ($listData as $key => $value){
            $name = 'excel/'.'订单'.$key . '-' . date('YmdHis') . '.xlsx';
            $excelData[] = $name;
            (new Exportservice)->orderListOne($value, $name);
        }
        $filename = 'excel/'.'order' . '-' . date('YmdHis') . '.zip';; // 压缩包存放路径与名称
        $zip = new \ZipArchive();
        $zip->open($filename,\ZipArchive::CREATE);   //打开压缩包
        //遍历文件
        foreach($excelData as $file){
            $zip->addFile($file,basename($file));   //向压缩包中添加文件
        }
        $zip->close();  //关闭压缩包
        //删除生成的Excel文件
        foreach($excelData as $file){
            unlink($file);
        }
        //下载到浏览器
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename='.basename($filename)); //文件名
        header("Content-Type: application/force-download");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小
        readfile($filename);
        //删除压缩文件
        unlink($filename);
    }

    public function getManufacturer($list)
    {
        foreach ($list as $key => $value) {
            $value['pay_price'] = round($value['pay_price'] - $value['coupon'], 2);
            foreach ($value['product'] as $key2 => $value2){
                //获取分类名称
                if ($value2['category_id'] != 0){
                    $category = Db::name('category')->where('category_id','=',$value2['category_id'])->find();
                    $list[$key]['product'][$key2]['category'] = $category['name'];
                    $list[$key]['category'] = $category['name'];
                }else{
                    $list[$key]['product'][$key2]['category'] = '';
                    $list[$key]['category'] = '';
                }
                //获取厂商名称
                if ($value2['manufacturer_id'] != 0){
                    $model = Db::name('manufacturer')->where('id','=',$value2['manufacturer_id'])->find();
                    $list[$key]['product'][$key2]['manufacturer'] = $model['name'];
                    $list[$key]['manufacturer'] = $model['name'];
                }else{
                    $list[$key]['product'][$key2]['manufacturer'] = '';
                    $list[$key]['manufacturer'] = '';
                }
            }
        }
        return $list;
    }

    /**
     * 设置检索查询条件
     */
    private function setWhere($model, $data)
    {
        //搜索订单号
        if (isset($data['order_no']) && $data['order_no'] != '') {
            $model = $model->where('order_no', 'like', '%' . trim($data['order_no']) . '%');
        }
        //搜索自提门店
        if (isset($data['store_id']) && $data['store_id'] != '') {
            $model = $model->where('extract_store_id', '=', $data['store_id']);
        }
        //搜索配送方式
        if (isset($data['style_id']) && $data['style_id'] != '') {
            $model = $model->where('delivery_type', '=', $data['style_id']);
        }
        //搜索小程序
        if (isset($data['applet_id']) && $data['applet_id'] != '') {
            $model = $model->where('order.applet_id', '=', $data['applet_id']);
        }
        //搜索时间段
        if (isset($data['create_time']) && $data['create_time'] != '') {
            $sta_time = array_shift($data['create_time']);
            $end_time = array_pop($data['create_time']) . ' 23:59:59';
            $model = $model->whereBetweenTime('order.create_time', $sta_time, $end_time);
        }
        //搜索时间段
        if (isset($data['delivery_time']) && $data['delivery_time'] != '') {
            $model = $model->where('order.delivery_time', '<>', 0)->where('order.delivery_time', '<', $data['delivery_time']);
        }
        //搜索配送方式
        if (isset($data['search']) && $data['search']) {
            $model = $model->where('user.user_id|user.nickName|user.mobile', 'like', '%' . $data['search'] . '%');
        }
        if (isset($data['manufacturer_id']) && $data['manufacturer_id']){
            $model = $model->where('order_product.manufacturer_id', 'like', '%' . $data['manufacturer_id'] . '%');
        }
        if (isset($data['category_id']) && $data['category_id']){
            $model = $model->where('order_product.category_id', 'like', '%' . $data['category_id'] . '%');
        }
        return $model;
    }

    /**
     * 转义数据类型条件
     */
    private function transferDataType($dataType)
    {
        $filter = [];
        // 订单数据类型
        switch ($dataType) {
            case 'all'://全部
                break;
            case 'examine';//待审核
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 21;
                break;
            case 'payment';//代付款
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 1;
                break;
            case 'delivery';//代发货
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';//待收货
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'haveInHand';//进行中
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 20;
                $filter['order_status'] = 10;
                break;
            case 'returned';//待归还
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 11;
                break;
            case 'complete';//已完成
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 20;
                $filter['order_status'] = 30;
                break;
            case 'cancel';//已取消
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 20;
                break;
            case 'Returning';//归还中
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['order_status'] = 20;
                break;
        }
//        switch ($dataType) {
//            case 'all':
//                break;
//            case 'payment';
//                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
//                $filter['order_status'] = 10;
//                break;
//            case 'delivery';
//                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
//                $filter['delivery_status'] = 10;
//                $filter['order_status'] = 10;
//                break;
//            case 'received';
//                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
//                $filter['delivery_status'] = 20;
//                $filter['receipt_status'] = 10;
//                $filter['order_status'] = 10;
//                break;
//            case 'comment';
//                $filter['is_comment'] = 0;
//                $filter['order_status'] = 30;
//                break;
//            case 'complete';
//                $filter['is_comment'] = 1;
//                $filter['order_status'] = 30;
//                break;
//            case 'cancel';
//                $filter['order_status'] = 21;
//                break;
//        }
        return $filter;
    }

    /**
     * 获取待处理订单
     */
    public function getReviewOrderTotal()
    {
        $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
        $filter['delivery_status'] = 10;
        $filter['order_status'] = 10;
        return $this->where($filter)->count();
    }

    /**
     * 获取某天的总销售额
     * 结束时间不传则查一天
     */
    public function getOrderTotalPrice($startDate = null, $endDate = null)
    {
        $model = $this;
        $model = $model->where('pay_time', '>=', strtotime($startDate));
        if (is_null($endDate)) {
            $model = $model->where('pay_time', '<', strtotime($startDate) + 86400);
        } else {
            $model = $model->where('pay_time', '<', strtotime($endDate) + 86400);
        }
        return $model->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', '=', 0)
            ->sum('pay_price');
    }

    /**
     * 获取某天的客单价
     * 结束时间不传则查一天
     */
    public function getOrderPerPrice($startDate = null, $endDate = null)
    {
        $model = $this;
        $model = $model->where('pay_time', '>=', strtotime($startDate));
        if (is_null($endDate)) {
            $model = $model->where('pay_time', '<', strtotime($startDate) + 86400);
        } else {
            $model = $model->where('pay_time', '<', strtotime($endDate) + 86400);
        }
        return $model->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', '=', 0)
            ->avg('pay_price');
    }

    /**
     * 获取某天的下单用户数
     */
    public function getPayOrderUserTotal($day)
    {
        $startTime = strtotime($day);
        $userIds = $this->distinct(true)
            ->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->where('is_delete', '=', 0)
            ->column('user_id');
        return count($userIds);
    }

    /**
     * 获取兑换记录
     * @param $param array
     * @return \think\Paginator
     */
    public function getExchange($param)
    {
        $model = $this;
        if (isset($param['order_status']) && $param['order_status'] > -1) {
            $model = $model->where('order.order_status', '=', $param['order_status']);
        }
        if (isset($param['nickName']) && !empty($param['nickName'])) {
            $model = $model->where('user.nickName', 'like', '%' . trim($param['nickName']) . '%');
        }

        return $model->with(['user'])->alias('order')
            ->join('user', 'user.user_id = order.user_id')
            ->where('order.order_source', '=', 20)
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->paginate($param);
    }

    /**
     * 获取平台的总销售额
     */
    public function getTotalMoney($type = 'all', $is_settled = -1)
    {
        $model = $this;
        $model = $model->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', '=', 0);
        if ($is_settled == 0) {
            $model = $model->where('is_settled', '=', 0);
        }
        if ($type == 'all') {
            return $model->sum('pay_price');
        } else if ($type == 'supplier') {
            return $model->sum('supplier_money');
        } else if ($type == 'sys') {
            return $model->sum('sys_money');
        }
        return 0;
    }

    /**
     * 获取视频订单
     */
    public function getAgentLiveOrder($params)
    {
        $model = $this;
        if (isset($params['order_no']) && !empty($params['order_no'])) {
            $model = $model->where('order.order_no', 'like', '%' . trim($params['order_no']) . '%');
        }
        if (isset($params['room_name']) && !empty($params['room_name'])) {
            $model = $model->where('room.name', 'like', '%' . trim($params['room_name']) . '%');
        }
        if (isset($params['nickName']) && !empty($params['nickName'])) {
            $model = $model->where('user.nickName', 'like', '%' . trim($params['real_name']) . '%');
        }
        if (isset($params['supplier_name']) && !empty($params['supplier_name'])) {
            $model = $model->where('supplier.name', 'like', '%' . trim($params['supplier_name']) . '%');
        }
        return $model->alias('order')->field(['order.*'])->with(['product.image', 'user', 'room.user', 'supplier'])
            ->join('live_room room', 'room.room_id = order.room_id', 'left')
            ->join('supplier supplier', 'supplier.shop_supplier_id = room.shop_supplier_id', 'left')
            ->join('user user', 'user.user_id = room.user_id', 'left')
            ->where('order.room_id', '>', 0)
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->paginate($params);
    }

    public function import2($filePath)
    {
        //加载文件
        $reader = IOFactory::createReaderForFile($filePath);
        $spreadSheet = $reader->load($filePath);
        $worksheet = $spreadSheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow('B'); // 总行数

        // 开启事务
        $this->startTrans();
        try {
            for ($row = 2; $row <= $highestRow; ++$row) {
                if (empty($worksheet->getCellByColumnAndRow(12, $row)->getValue())){
                    break;
                }
                $commodity_information = $worksheet->getCellByColumnAndRow(7, $row)->getValue(); //商品信息
                $express_company = $worksheet->getCellByColumnAndRow(12, $row)->getValue(); //快递公司
                $express_company = Db::name('express')->where('express_name','like','%'.$express_company.'%')->find()['express_id'];
                $tracking_number = $worksheet->getCellByColumnAndRow(13, $row)->getValue(); //快递单号
                $commodity_information = str_replace('***', '', $commodity_information.textdomain('***'));
                $commodity_information = explode('：', $commodity_information);
                $order_no = $commodity_information[6]; //订单号
                $data = $this->detail(['order_no'=>$order_no]);
                if (empty($data)){
                    continue;
                }
                Db::name('order')->where('order_no', '=', $order_no)->update(['express_no' => $tracking_number, 'express_id' => $express_company, 'delivery_status' => 20]);
                $sendmsg = new Sendmsg();
                $data = $this->with(['address', 'user','product'])->where('order_no','=',$order_no)->find()->toArray();
                $keyword = ["keyword1"=> ["value" => $data['address']['name']],"keyword2"=> ["value" => $data['address']['phone']],"keyword3"=> ["value" => $data['product'][0]['product_name']],"keyword4"=> ["value" => $data['address']['region']['province'].$data['address']['region']['city'].$data['address']['region']['region'].$data['address']['detail']],"keyword5"=> ["value" => $data['product'][0]['product_attr']]];
                $sendmsg->sendmsg($data['user']['open_id'],'34eec90ff0fe41449a719457218fc577',$keyword);
            }
            $this->commit();
        }catch (\Exception $e) {
            if ($e->getMessage() == "Undefined offset: 0"){
                $this->error = '请添加规格';
            }else{
                $this->error = $e->getMessage();
            }
            $this->rollback();
            return false;
        }
        return '导入成功';
    }
}