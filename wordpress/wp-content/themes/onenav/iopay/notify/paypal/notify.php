<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-04 04:23:56
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-06 09:35:40
 * @FilePath: \onenav\iopay\notify\paypal\notify.php
 * @Description: paypal
 */

if(!session_id()) session_start();
header('Content-type:text/html; Charset=utf-8');

ob_start();
require dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$config = iopay_get_option('paypal');
if (empty($config['user']) || empty($config['pass']) || empty($config['signature']) || empty($config['rate'])) {
    exit('fail');
}

require_once get_theme_file_path('/iopay/sdk/paypal/api.php');
$paypal = new PayPal($config['user'], $config['pass'], $config['signature']);

$token = urlencode( $_REQUEST['token']);

//建立第二个API请求到PayPal，使用令牌作为ID，以获得支付授权的细节。
$nvpstr = array('TOKEN' => $token);

//进行API调用并将结果存储在一个数组中。 如果调用成功，显示授权细节，并提供一个动作来完成支付。 如果失败，显示错误
$result = $paypal->__call("GetExpressCheckoutDetails",$nvpstr);

$pay_num    = $result["CORRELATIONID"];
$ack        = strtoupper($result["ACK"]);
$order_num  = $result["INVNUM"];

if (($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING')) {
    $total_smount  = $result['PAYMENTREQUEST_0_AMT'] + $result['PAYMENTREQUEST_0_SHIPDISCAMT'];
    $currency_code = $_REQUEST['currencyCodeType'];
    $nvpstr        = array(
        'TOKEN'                          => $token,
        'PAYERID'                        => $_REQUEST['PayerID'],
        'PAYMENTREQUEST_0_AMT'           => $total_smount,
        'PAYMENTREQUEST_0_PAYMENTACTION' => $_REQUEST['paymentType'],
        'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency_code
    );
    //调用PayPal以完成付款，如果发生错误，则显示由此产生的错误
    $result = $paypal->__call("DoExpressCheckoutPayment", $nvpstr);

    $ack = strtoupper($result["ACK"]);
    if ($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING') {
        show_paypal_error($result);
        exit;
    } else {
        $order_num = $_SESSION["paypal_num"];
        $pay_price = round($total_smount * $config['rate'], 2);
        $pay       = array(
            'pay_type'  => 'paypal',
            'pay_price' => $pay_price,
            'pay_num'   => $pay_num,
            'other'     => array(
                'currency_code' => $currency_code,
                'pay_price'     => $total_smount,
                'exchange_rate' => $config['rate'],
            ),
        );
        // 更新订单状态
        $order = iopay_confirm_pay($order_num, $pay);

        $ret = home_url();
        if (isset($_SESSION['paypal_return']) && $_SESSION['paypal_return']) {
            $ret = $_SESSION['paypal_return'];
        }
        wp_redirect($ret);
    }
} else {
    show_paypal_error($result);
    exit();
}
function show_paypal_error($result){
    $msg="";
    $msg='<table width="100%"><tr><td colspan="2" class="header">The PayPal API has returned an error!</td></tr>';
    foreach($result as $key => $value)
    {
        $msg.="<tr><td> $key:</td><td>$value</td>";
    }
    $msg.='</table>';
    echo $msg;
}
exit();
