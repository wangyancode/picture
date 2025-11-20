<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-04 04:23:56
 * @LastEditors: iowen
 * @LastEditTime: 2023-04-10 21:50:03
 * @FilePath: \onenav\iopay\notify\epay\notify.php
 * @Description: epay
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

if (empty($_GET)) {
    echo '非法请求';
    exit();
}

if (io_get_option('pay_wechat_sdk') != 'epay' && io_get_option('pay_alipay_sdk') != 'epay') {
    exit('fail');
}

require_once get_theme_file_path('/iopay/sdk/epay/EpayCore.class.php');
$config = iopay_get_option('epay');

//计算得出通知验证结果
$epay = new EpayCore($config);
$verify_result = $epay->verifyNotify();

if($verify_result) {//验证成功
	//商户订单号
	$out_trade_no = $_GET['out_trade_no'];
	//彩虹易支付交易号
	$trade_no = $_GET['trade_no'];
	//交易状态
	$trade_status = $_GET['trade_status'];
	//支付方式
    $type = $_GET['type'] == 'wxpay' ? 'wechat' : $_GET['type'];
	//支付金额
	$money = $_GET['money'];
	if ($_GET['trade_status'] == 'TRADE_SUCCESS') {
		//判断该笔订单是否在商户网站中已经做过处理
		//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
		//如果有做过处理，不执行商户的业务程序
        
        $pay = array(
            'pay_type'  => 'epay_' . $type,
            'pay_price' => $money,
            'pay_num'   => $trade_no,
            'other'     => '',
        );
        $order = iopay_confirm_pay($out_trade_no, $pay);
	}

	//验证成功返回
	echo "success";
} else {
	//验证失败
    $msg = '$_POST:' . json_encode($_GET,JSON_UNESCAPED_UNICODE);
    IOTOOLS::log("易支付：".$msg, true, WP_CONTENT_DIR."/pay_notify_result.log");
	echo "fail";
}
exit();