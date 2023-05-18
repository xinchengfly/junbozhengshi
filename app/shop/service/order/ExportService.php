<?php

namespace app\shop\service\order;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 订单导出服务类
 */
class ExportService
{
    /**
     * 订单导出
     */
    public function orderList($list)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //列宽
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('P')->setWidth(30);

        //设置工作表标题名称
        $sheet->setTitle('订单明细');

        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '商品信息');
//        $sheet->setCellValue('C1', '订单总额');
//        $sheet->setCellValue('D1', '优惠券抵扣');
//        $sheet->setCellValue('E1', '积分抵扣');
//        $sheet->setCellValue('F1', '运费金额');
//        $sheet->setCellValue('G1', '后台改价');
//        $sheet->setCellValue('H1', '实付款金额');
//        $sheet->setCellValue('I1', '支付方式');
        $sheet->setCellValue('C1', '下单时间');
        $sheet->setCellValue('D1', '买家');
//        $sheet->setCellValue('L1', '买家留言');
        $sheet->setCellValue('E1', '配送方式');
//        $sheet->setCellValue('N1', '自提门店名称');
//        $sheet->setCellValue('O1', '自提联系人');
//        $sheet->setCellValue('P1', '自提联系电话');
        $sheet->setCellValue('F1', '收货人姓名');
        $sheet->setCellValue('G1', '联系电话');
        $sheet->setCellValue('H1', '收货人地址');
        $sheet->setCellValue('I1', '物流公司');
        $sheet->setCellValue('J1', '物流单号');
        $sheet->setCellValue('K1', '发货状态');
        $sheet->setCellValue('L1', '发货时间');
//        $sheet->setCellValue('Z1', '收货状态');
//        $sheet->setCellValue('AA1', '收货时间');
//        $sheet->setCellValue('AB1', '订单状态');
//        $sheet->setCellValue('AC1', '微信支付交易号');
//        $sheet->setCellValue('AD1', '是否已评价');
        $sheet->setCellValue('M1', '厂商名称');
        $sheet->setCellValue('N1', '分类');
        $sheet->setCellValue('O1', '付款状态');
        $sheet->setCellValue('P1', '付款时间');
        $sheet->setCellValue('Q1', '实付金额');
        $sheet->setCellValue('R1', '进货价格');
        $sheet->setCellValue('S1', '税率');

        //填充数据
        $index = 0;
        $buying_price_count = 0; //进货总价
        $tax_rate_count = 0; //税率总价
        foreach ($list as $order) {
            $address = $order['address'];
            $sheet->setCellValue('A' . ($index + 2), "\t" . $order['order_no'] . "\t");
            $sheet->setCellValue('B' . ($index + 2), $this->filterProductInfo($order));
//            $sheet->setCellValue('C' . ($index + 2), $order['total_price']);
//            $sheet->setCellValue('D' . ($index + 2), $order['coupon_money']);
//            $sheet->setCellValue('E' . ($index + 2), $order['points_money']);
//            $sheet->setCellValue('F' . ($index + 2), $order['express_price']);
//            $sheet->setCellValue('G' . ($index + 2), "{$order['update_price']['symbol']}{$order['update_price']['value']}");
//            $sheet->setCellValue('H' . ($index + 2), $order['pay_price']);
//            $sheet->setCellValue('I' . ($index + 2), $order['pay_type']['text']);
            $sheet->setCellValue('C' . ($index + 2), $order['create_time']);
            $sheet->setCellValue('D' . ($index + 2), $order['user']['nickName']);
//            $sheet->setCellValue('L' . ($index + 2), $order['buyer_remark']);
            $sheet->setCellValue('E' . ($index + 2), $order['delivery_type']['text']);
//            $sheet->setCellValue('N' . ($index + 2), !empty($order['extract_store']) ? $order['extract_store']['shop_name'] : '');
//            $sheet->setCellValue('O' . ($index + 2), !empty($order['extract']) ? $order['extract']['linkman'] : '');
//            $sheet->setCellValue('P' . ($index + 2), !empty($order['extract']) ? $order['extract']['phone'] : '');
            $sheet->setCellValue('F' . ($index + 2), $order['address']['name']);
            $sheet->setCellValue('G' . ($index + 2), $order['address']['phone']);
            $sheet->setCellValue('H' . ($index + 2), $address ? $address->getFullAddress() : '');
            $sheet->setCellValue('I' . ($index + 2), $order['express']['express_name']);
            $sheet->setCellValue('J' . ($index + 2), $order['express_no']);

            $sheet->setCellValue('K' . ($index + 2), $order['delivery_status']['text']);
            $sheet->setCellValue('L' . ($index + 2), $this->filterTime($order['delivery_time']));
//            $sheet->setCellValue('Z' . ($index + 2), $order['receipt_status']['text']);
//            $sheet->setCellValue('AA' . ($index + 2), $this->filterTime($order['receipt_time']));
//            $sheet->setCellValue('AB' . ($index + 2), $order['order_status']['text']);
//            $sheet->setCellValue('AC' . ($index + 2), $order['transaction_id']);
//            $sheet->setCellValue('AD' . ($index + 2), $order['is_comment'] ? '是' : '否');
            $sheet->setCellValue('M' . ($index + 2), $order['product'][0]['manufacturer']);
            $sheet->setCellValue('N' . ($index + 2), $order['product'][0]['category']);
            $sheet->setCellValue('O' . ($index + 2), $order['pay_status']['text']);
            $sheet->setCellValue('P' . ($index + 2), $this->filterTime($order['pay_time']));
            $sheet->setCellValue('Q' . ($index + 2), $order['pay_price']);
            $sheet->setCellValue('R' . ($index + 2), $this->buyprice($order));
            $sheet->setCellValue('S' . ($index + 2), $this->tax_rate($order));
            foreach ($order['product'] as $key => $product) {
                $product['tax_rate']; //税率
                $product['buying_price'] * $product['total_num']; //进货总价
                $buying_price_count += $product['buying_price'] * $product['total_num'];
                if ($product['tax_rate'] > 0){
                    $tax_rate_count += ($product['buying_price'] * $product['total_num']) * $product['tax_rate'];
                }
            }
            $index++;
        }
        $sheet->setCellValue('R' . ($index + 2), '合计:'.$buying_price_count);
        $sheet->setCellValue('S' . ($index + 2), '合计:'.$tax_rate_count);

        //保存文件
        $writer = new Xlsx($spreadsheet);
        $filename = iconv("UTF-8", "GB2312//IGNORE", '订单') . '-' . date('YmdHis') . '.xlsx';


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    public function orderListOne($list, $name)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //列宽
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('P')->setWidth(30);
        //设置工作表标题名称
        $sheet->setTitle('订单明细');
        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '商品信息');
        $sheet->setCellValue('C1', '下单时间');
        $sheet->setCellValue('D1', '买家');
        $sheet->setCellValue('E1', '配送方式');
        $sheet->setCellValue('F1', '收货人姓名');
        $sheet->setCellValue('G1', '联系电话');
        $sheet->setCellValue('H1', '收货人地址');
        $sheet->setCellValue('I1', '物流公司');
        $sheet->setCellValue('J1', '物流单号');
        $sheet->setCellValue('K1', '发货状态');
        $sheet->setCellValue('L1', '发货时间');
        $sheet->setCellValue('M1', '厂商名称');
        $sheet->setCellValue('N1', '分类');
        $sheet->setCellValue('O1', '付款状态');
        $sheet->setCellValue('P1', '付款时间');
        $sheet->setCellValue('Q1', '实付金额');
        $sheet->setCellValue('R1', '进货价格');
        $sheet->setCellValue('S1', '税率');
        //填充数据
        $index = 0;
        $buying_price_count = 0; //进货总价
        $tax_rate_count = 0; //税率总价
        foreach ($list as $order) {
            $address = $order['address'];
            $sheet->setCellValue('A' . ($index + 2), "\t" . $order['order_no'] . "\t");
            $sheet->setCellValue('B' . ($index + 2), $this->filterProductInfo($order));
            $sheet->setCellValue('C' . ($index + 2), $order['create_time']);
            $sheet->setCellValue('D' . ($index + 2), $order['user']['nickName']);
            $sheet->setCellValue('E' . ($index + 2), $order['delivery_type']['text']);
            $sheet->setCellValue('F' . ($index + 2), $order['address']['name']);
            $sheet->setCellValue('G' . ($index + 2), $order['address']['phone']);
            $sheet->setCellValue('H' . ($index + 2), $address ? $address->getFullAddress() : '');
            $sheet->setCellValue('I' . ($index + 2), $order['express']['express_name']);
            $sheet->setCellValue('J' . ($index + 2), $order['express_no']);
            $sheet->setCellValue('K' . ($index + 2), $order['delivery_status']['text']);
            $sheet->setCellValue('L' . ($index + 2), $this->filterTime($order['delivery_time']));
            $sheet->setCellValue('M' . ($index + 2), $order['product'][0]['manufacturer']);
            $sheet->setCellValue('N' . ($index + 2), $order['product'][0]['category']);
            $sheet->setCellValue('O' . ($index + 2), $order['pay_status']['text']);
            $sheet->setCellValue('P' . ($index + 2), $this->filterTime($order['pay_time']));
            $sheet->setCellValue('Q' . ($index + 2), $order['pay_price']);
            $sheet->setCellValue('R' . ($index + 2), $this->buyprice($order));
            $sheet->setCellValue('S' . ($index + 2), $this->tax_rate($order));
            foreach ($order['product'] as $key => $product) {
                $product['tax_rate']; //税率
                $product['buying_price'] * $product['total_num']; //进货总价
                $buying_price_count += $product['buying_price'] * $product['total_num'];
                if ($product['tax_rate'] > 0){
                    $tax_rate_count += ($product['buying_price'] * $product['total_num']) * $product['tax_rate'];
                }
            }
            $index++;
        }
        $sheet->setCellValue('R' . ($index + 2), '合计:'.$buying_price_count);
        $sheet->setCellValue('S' . ($index + 2), '合计:'.$tax_rate_count);
        //保存文件
        $writer = new Xlsx($spreadsheet);
        $writer->save($name);
    }

    /**
     * 分销订单导出
     */
    public function agentOrderList($list)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //列宽
        $sheet->getColumnDimension('B')->setWidth(30);

        //设置工作表标题名称
        $sheet->setTitle('分销订单明细');

        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '商品信息');
        $sheet->setCellValue('C1', '订单总额');
        $sheet->setCellValue('D1', '实付款金额');
        $sheet->setCellValue('E1', '支付方式');
        $sheet->setCellValue('F1', '下单时间');
        $sheet->setCellValue('G1', '一级分销商');
        $sheet->setCellValue('H1', '一级分销佣金');
        $sheet->setCellValue('I1', '二级分销商');
        $sheet->setCellValue('J1', '二级分销佣金');
        $sheet->setCellValue('K1', '三级分销商');
        $sheet->setCellValue('L1', '三级分销佣金');
        $sheet->setCellValue('M1', '买家');
        $sheet->setCellValue('N1', '付款状态');
        $sheet->setCellValue('O1', '付款时间');
        $sheet->setCellValue('P1', '发货状态');
        $sheet->setCellValue('Q1', '发货时间');
        $sheet->setCellValue('R1', '收货状态');
        $sheet->setCellValue('S1', '收货时间');
        $sheet->setCellValue('T1', '订单状态');
        $sheet->setCellValue('U1', '佣金结算');
        $sheet->setCellValue('V1', '结算时间');
        //填充数据
        $index = 0;
        foreach ($list as $agent) {
            $order = $agent['order_master'];
            $sheet->setCellValue('A' . ($index + 2), "\t" . $order['order_no'] . "\t");
            $sheet->setCellValue('B' . ($index + 2), $this->filterProductInfo($order));
            $sheet->setCellValue('C' . ($index + 2), $order['total_price']);
            $sheet->setCellValue('D' . ($index + 2), $order['pay_price']);
            $sheet->setCellValue('E' . ($index + 2), $order['pay_type']['text']);
            $sheet->setCellValue('F' . ($index + 2), $order['create_time']);
            $sheet->setCellValue('G' . ($index + 2), $agent['agent_first']['nickName']);
            $sheet->setCellValue('H' . ($index + 2), $agent['first_money']);
            $sheet->setCellValue('I' . ($index + 2), $agent['agent_second']['nickName']);
            $sheet->setCellValue('J' . ($index + 2), $agent['second_money']);
            $sheet->setCellValue('K' . ($index + 2), $agent['agent_third']['nickName']);
            $sheet->setCellValue('L' . ($index + 2), $agent['third_money']);
            $sheet->setCellValue('M' . ($index + 2), $order['user']['nickName']);
            $sheet->setCellValue('N' . ($index + 2), $order['pay_status']['text']);
            $sheet->setCellValue('O' . ($index + 2), $this->filterTime($order['pay_time']));
            $sheet->setCellValue('P' . ($index + 2), $order['delivery_status']['text']);
            $sheet->setCellValue('Q' . ($index + 2), $this->filterTime($order['delivery_time']));
            $sheet->setCellValue('R' . ($index + 2), $order['receipt_status']['text']);
            $sheet->setCellValue('S' . ($index + 2), $this->filterTime($order['receipt_time']));
            $sheet->setCellValue('T' . ($index + 2), $order['order_status']['text']);
            $sheet->setCellValue('U' . ($index + 2), $agent['is_settled'] == 1 ? '已结算' : '未结算');
            $sheet->setCellValue('V' . ($index + 2), $this->filterTime($agent['settle_time']));
            $index++;
        }

        //保存文件
        $filename = iconv("UTF-8", "GB2312//IGNORE", '分销订单') . '-' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    /**
     * 提现订单导出
     */
    public function cashList($list)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //列宽
        $sheet->getColumnDimension('H')->setWidth(50);

        //设置工作表标题名称
        $sheet->setTitle('提现明细');

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', '分销商id');
        $sheet->setCellValue('C1', '分销商姓名');
        $sheet->setCellValue('D1', '微信昵称');
        $sheet->setCellValue('E1', '手机号');
        $sheet->setCellValue('F1', '提现金额');
        $sheet->setCellValue('G1', '提现方式');
        $sheet->setCellValue('H1', '提现信息');
        $sheet->setCellValue('I1', '审核状态');
        $sheet->setCellValue('J1', '申请时间');
        $sheet->setCellValue('K1', '审核时间');
        //填充数据
        $index = 0;
        foreach ($list as $cash) {
            $sheet->setCellValue('A' . ($index + 2), $cash['id']);
            $sheet->setCellValue('B' . ($index + 2), $cash['user_id']);
            $sheet->setCellValue('C' . ($index + 2), $cash['real_name']);
            $sheet->setCellValue('D' . ($index + 2), $cash['nickName']);
            $sheet->setCellValue('E' . ($index + 2), "\t" . $cash['mobile'] . "\t");
            $sheet->setCellValue('F' . ($index + 2), $cash['money']);
            $sheet->setCellValue('G' . ($index + 2), $cash['pay_type']['text']);
            $sheet->setCellValue('H' . ($index + 2), $this->cashInfo($cash));
            $sheet->setCellValue('I' . ($index + 2), $cash['apply_status']['text']);
            $sheet->setCellValue('J' . ($index + 2), $cash['create_time']);
            $sheet->setCellValue('K' . ($index + 2), $cash['audit_time']);
            $index++;
        }
        //保存文件
        $filename = iconv("UTF-8", "GB2312//IGNORE", '提现明细') . '-' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    /**
     * 格式化提现信息
     */
    private function cashInfo($cash)
    {
        $content = '';
        if ($cash['pay_type']['value'] == 20) {
            $content .= "支付宝姓名：{$cash['alipay_name']}\n";
            $content .= "  支付宝账号：{$cash['alipay_account']}\n";
        } elseif ($cash['pay_type']['value'] == 30) {
            $content .= "开户行：{$cash['bank_name']}\n";
            $content .= "  姓名：{$cash['bank_account']}\n";
            $content .= "  卡号：{$cash['bank_account']}\n";
        }
        return $content;
    }

    /**
     * 格式化商品信息
     */
    private function filterProductInfo($order)
    {
        $content = '';
        foreach ($order['product'] as $key => $product) {
            $content .= ($key + 1) . ".商品名称：{$product['product_name']}\n";
            !empty($product['product_attr']) && $content .= "　商品规格：{$product['product_attr']}\n";
            $content .= "　购买数量：{$product['total_num']}\n";
//            $content .= "　商品总价：{$product['total_price']}元\n";
            $content .= "　厂商名称：{$product['manufacturer']}\n";
            $content .= "　分类：{$product['category']}\n";
            $content .= "　订单ID：{$order['order_no']}\n";
        }
        return $content;
    }

    private function buyprice($order)
    {
        $content = '';
        foreach ($order['product'] as $key => $product) {
            $content .= "　进货价格：".$product['buying_price']."\n";
            $content .= "　进货总价：".($product['buying_price'] * $product['total_num'])."\n";
        }
        return $content;
    }

    private function tax_rate($order)
    {
        $content = '';
        foreach ($order['product'] as $key => $product) {
            $content .= "　税率：".$product['tax_rate']."\n";
        }
        return $content;
    }

    private function tax_rate_test($order)
    {
        $content = '';
        foreach ($order['product'] as $key => $product) {
            $content .= "　税率：".$product['tax_rate_text']."\n";
        }
        return $content;
    }



    /**
     * 日期值过滤
     */
    private function filterTime($value)
    {
        if (!$value) return '';
        return date('Y-m-d H:i:s', $value);
    }

}