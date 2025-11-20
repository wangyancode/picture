<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-02 16:21:15
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-17 22:44:55
 * @FilePath: \onenav\iopay\notify\weixin\notify.php
 * @Description: 微信企业支付
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();


if (io_get_option('pay_wechat_sdk') != 'official_wechat') {
    exit('fail');
}

$config = iopay_get_option('official_wechat');
if (!$config['appid'] || !$config['key']|| !$config['mchid']) {
    exit('fail');
}

$params         = new \Yurun\PaySDK\Weixin\Params\PublicParams;
$params->appID  = $config['appid'];
$params->mch_id = $config['mchid'];
$params->key    = $config['key'];

$sdk = new \Yurun\PaySDK\Weixin\SDK($params);

class PayNotify extends \Yurun\PaySDK\Weixin\Notify\Pay
{
    protected function __exec(){
        $pay = array(
            'pay_type'  => 'wechat',
            'pay_price' => $this->data['total_fee'] / 100,
            'pay_num'   => $this->data['transaction_id'],
            'other'     => '',
        );

        iopay_confirm_pay($this->data['out_trade_no'],$pay);

        $this->reply(true, 'OK');
    }
}

$payNotify = new PayNotify;

try {
    $sdk->notify($payNotify);
} catch (Exception $e) {
    IOTOOLS::log("微信支付：".$e->getMessage() . ':' . var_export($payNotify->data, true), true, WP_CONTENT_DIR."/pay_notify_result.log");
}
