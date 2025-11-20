<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-02 16:21:15
 * @LastEditors: iowen
 * @LastEditTime: 2023-04-10 21:56:48
 * @FilePath: \onenav\iopay\notify\alipay\notify.php
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

require_once get_theme_file_path('iopay/sdk/alipay/alipay-check.php');

if (io_get_option('pay_alipay_sdk') != 'official_alipay') {
    exit('fail');
}
$config   = iopay_get_option('official_alipay');
$aliPay   = new AlipayServiceCheck($config['publickey']);
$rsaCheck = $aliPay->rsaCheck($_POST);
if ($rsaCheck && $_POST['trade_status'] == 'TRADE_SUCCESS') {
    $pay = array(
        'pay_type'  => 'alipay',
        'pay_price' => $_POST['total_amount'],
        'pay_num'   => $_POST['trade_no'],
        'other'     => '',
    );
    $order = iopay_confirm_pay($_POST['out_trade_no'],$pay);
    echo "success";
    exit();
} else {
    $msg = '//AlipayServiceCheck:' . $rsaCheck . PHP_EOL . '$_POST:' . json_encode($_POST,JSON_UNESCAPED_UNICODE);
    IOTOOLS::log("支付宝：".$msg, true, WP_CONTENT_DIR."/pay_notify_result.log");
}
echo "error";
exit();

/**

$params                = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
$params->appID         = $config['web_appid'];
$params->appPrivateKey = $config['web_privatekey'];
$params->appPublicKey  = $config['publickey'];
// SDK实例化，传入公共配置
$pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
if($pay->verifyCallback($_POST))
{
    // 通知验证成功，可以通过POST参数来获取支付宝回传的参数
    $pay = array(
        'pay_type'  => 'alipay',
        'pay_price' => $_POST['total_amount'],
        'pay_num'   => $_POST['trade_no'],
        'other'     => '',
    );
    $order = iopay_confirm_pay($_POST['out_trade_no'],$pay);
    echo "success";
    exit();
}
else
{
    // 通知验证失败
    $msg = '//AlipayServiceCheck:' . $rsaCheck . PHP_EOL . '$_POST:' . json_encode($_POST,JSON_UNESCAPED_UNICODE);
    IOTOOLS::log("支付宝：".$msg, true, WP_CONTENT_DIR."/pay_notify_result.log");
}
echo "error";
exit();

*/