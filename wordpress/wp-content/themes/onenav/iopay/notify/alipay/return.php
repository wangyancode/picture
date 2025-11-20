<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-03 16:26:35
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-05 17:31:49
 * @FilePath: \onenav\iopay\notify\alipay\return.php
 * @Description: 支付宝同步回调
 */


header('Content-type:text/html; Charset=utf-8');

ob_start();
require dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$user_id = get_current_user_id();
if ($user_id) {
    wp_safe_redirect(home_url());
    return;
}

wp_safe_redirect(home_url());
