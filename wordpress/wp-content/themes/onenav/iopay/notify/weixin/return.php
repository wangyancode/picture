<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-02 16:21:15
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-05 18:32:27
 * @FilePath: \onenav\iopay\notify\weixin\return.php
 * @Description: 微信企业支付
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
