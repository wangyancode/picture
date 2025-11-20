<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-02 16:21:15
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-17 22:44:25
 * @FilePath: \onenav\iopay\notify\senhuo\notify.php
 * @Description: 
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

if (empty($_POST)) {
    echo '非法请求';
    exit();
}



if (io_get_option('pay_wechat_sdk') != 'senhuo' && io_get_option('pay_alipay_sdk') != 'senhuo') {
    exit('FAil');
}

$config = iopay_get_option('senhuo');

if ( $config['appkey'] == $_POST['appkey'] ){
    $trade_no       = $_POST['trade_no'];//微信订单号
    $out_trade_no   = $_POST['out_trade_no'];//网站订单号
    $title          = $_POST['title'];//订单名称，商品名称
    $total          = $_POST['total'];//订单金额，单位是分
    $pay_type       = $_POST['pay_type'];//wechat或alipay
    //这里填写你支付成功后的逻辑、比如更新订单状态、给用户增加金币或开通会员等等

    $pay = array(
        'pay_type'  => 'senhuo_' . $pay_type,
        'pay_price' => $total/100,
        'pay_num'   => $trade_no,
        'other'     => '',
    );
    $order = iopay_confirm_pay($out_trade_no, $pay);

    echo 'SUCCESS';
    exit();
}else{ //这里填写你支付失败后的逻辑、一般不需要写
    $msg = '$_POST:' . json_encode($_POST,JSON_UNESCAPED_UNICODE);
    IOTOOLS::log("站长支付：".$msg, true, WP_CONTENT_DIR."/pay_notify_result.log");
}
echo 'FAil';
exit();