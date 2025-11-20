<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-04-10 21:36:34
 * @LastEditors: iowen
 * @LastEditTime: 2023-04-10 21:57:32
 * @FilePath: \onenav\iopay\notify\payjs\notify.php
 * @Description: payjs异步通知
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

if (empty($_POST['return_code']) || empty($_POST['attach'])) {
    echo '非法请求';
    exit();
}

$config = iopay_get_option('payjs');
if (empty($config['mchid']) || empty($config['key'])) {
    exit('fail');
}

if (io_get_option('pay_wechat_sdk') != 'payjs' && io_get_option('pay_alipay_sdk') != 'payjs') {
    exit('fail');
}

require_once(get_theme_file_path('/iopay/sdk/payjs/payjs.class.php'));
$payjs = new Payjs($config['mchid'], $config['key']);

if($payjs->checkSign($_POST)){
    $type = (empty($_POST['return_code']) && $_POST['return_code'] == 'alipay') ? 'payjs_alipay':'payjs_wechat';
    $pay  = array(
        'pay_type'  => $type,
        'pay_price' => $_POST['total_fee'] / 100,
        'pay_num'   => $_POST['payjs_order_id'],
        'other'     => '',
    );
    $order = iopay_confirm_pay($_POST['out_trade_no'], $pay);
    echo 'success';
}else{  
    $msg = '$_POST:' . json_encode($_POST,JSON_UNESCAPED_UNICODE);
    IOTOOLS::log("PAYJS 支付：".$msg, true, WP_CONTENT_DIR."/pay_notify_result.log");
    echo 'fail';
}

exit();