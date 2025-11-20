<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-03 16:26:35
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-17 22:45:10
 * @FilePath: \onenav\iopay\notify\xunhupay\notify-v4.php
 * @Description: 迅虎pay异步通知
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();



if (io_get_option('pay_wechat_sdk') != 'xunhupay_v4' && io_get_option('pay_alipay_sdk') != 'xunhupay_v4') {
    exit('fail');
}

$config = iopay_get_option('xunhupay_v4');
if (!$config['mchid'] || !$config['key']) {
    exit('fail');
}

require_once get_theme_file_path('/iopay/sdk/xunhupay/api-v4.php');
$xhpay  = new XunhuPay($config);
$result = $xhpay->getNotify();
if ($result && $result['return_code'] == 'SUCCESS') {
    $type = str_replace("iopay_", "", $result['attach']);
    $pay  = array(
        'pay_type'  => $type,
        'pay_price' => $result['total_fee'] / 100,
        'pay_num'   => $result['order_id'],
        'other'     => '',
    );

    iopay_confirm_pay($result['out_trade_no'], $pay);
    /**返回不在发送异步通知 */
    echo 'success';
} else {
    //处理未支付的情况
    $msg = 'result:' . json_encode($result,JSON_UNESCAPED_UNICODE);
    IOTOOLS::log("迅虎pay：".$msg, true, WP_CONTENT_DIR."/pay_notify_result.log");
}

exit();
