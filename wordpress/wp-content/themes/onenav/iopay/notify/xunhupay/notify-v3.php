<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-03 16:26:35
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-17 22:45:05
 * @FilePath: \onenav\iopay\notify\xunhupay\notify-v3.php
 * @Description: 虎皮椒支付成功异步回调接口
 * 当用户支付成功后，支付平台会把订单支付信息异步请求到本接口(最多5次)
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

/**
 * 回调数据
 * @var array(
 *       'trade_order_id'，商户网站订单ID
         'total_fee',订单支付金额
         'transaction_id',//支付平台订单ID
         'order_date',//支付时间
         'plugins',//自定义插件ID,与支付请求时一致
         'status'=>'OD'//订单状态，OD已支付，WP未支付
 *   )
 */

if (io_get_option('pay_wechat_sdk') != 'xunhupay_v3' && io_get_option('pay_alipay_sdk') != 'xunhupay_v3') {
    exit('fail');
}


$data = $_POST;
foreach ($data as $k => $v) {
    $data[$k] = stripslashes($v);
}
if (!isset($data['hash']) || !isset($data['trade_order_id'])) {
    echo 'failed';
    exit;
}

//自定义插件ID,请与支付请求时一致
if (!isset($data['plugins']) || $data['plugins'] != 'iopay_xunhupay') {
    echo 'failed';
    exit;
}


//APP SECRET
require_once get_theme_file_path('/iopay/sdk/xunhupay/api-v3.php');
$config  = iopay_get_option('xunhupay_v3');
$hashkey = $config[ $_GET['pay_type'] . "_appsecret" ];
$hash    = XH_Payment_Api::generate_xh_hash($data, $hashkey);
if($data['hash']!=$hash){
    echo 'failed';
    exit;
}

if ($data['status'] == 'OD') {
    /************商户业务处理******************/
    //TODO:此处处理订单业务逻辑,支付平台会多次调用本接口(防止网络异常导致回调失败等情况)
    //     请避免订单被二次更新而导致业务异常！！！
    //     if(订单未处理){
    //         处理订单....
    //      }

    $pay = array(
        'pay_type'  => 'xunhupay_v3_'.$_GET['pay_type'],
        'pay_price' => $data['total_fee'],
        'pay_num'   => $data['transaction_id'],
        'other'     => '',
    );

    iopay_confirm_pay($data['trade_order_id'], $pay);
    /*************商户业务处理 END*****************/
} else {
    //处理未支付的情况
    $msg = '$_POST:' . json_encode($data,JSON_UNESCAPED_UNICODE);
    IOTOOLS::log("虎皮椒：".$msg, true, WP_CONTENT_DIR."/pay_notify_result.log");
}

//以下是处理成功后输出，当支付平台接收到此消息后，将不再重复回调当前接口
echo 'success';
exit;
