<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-08 14:29:57
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-25 15:50:54
 * @FilePath: /onenav/iopay/action/ajax-pay.php
 * @Description: 
 */

/**
 * 常规商品支付模态框
 * 
 * @return never
 */
function iopay_ajax_pay_cashier_modal(){
    $post_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $index   = !empty($_REQUEST['index']) ? $_REQUEST['index'] : 0;

    $modal = iopay_pay_cashier_modal($post_id, $index);
    if (!$modal) {
        io_ajax_notice_modal('danger', __('参数异常', 'i_theme'));
    }
    echo $modal;
    exit;
}
add_action('wp_ajax_pay_cashier_modal', 'iopay_ajax_pay_cashier_modal');
add_action('wp_ajax_nopriv_pay_cashier_modal', 'iopay_ajax_pay_cashier_modal');

/**
 * 发起支付
 * @return never
 */
function iopay_initiate_order(){
    if (!empty($_REQUEST['openid'])) {
        if(!session_id()) session_start();
        if (!empty($_SESSION['IOPAY_POST'])) {
            $_POST = array_merge($_SESSION['IOPAY_POST'], $_POST);
        } else {
            io_tips_error(__('PHP session 数据获取失败','i_theme'));
        }
    }

    $order_type = !empty($_POST['order_type']) ? $_POST['order_type'] : 0;
    $pay_method = !empty($_POST['pay_method']) ? $_POST['pay_method'] : 0;
    if (!$order_type || !$pay_method) {
        io_tips_error(__('请选择支付方式','i_theme'));
    }

    //根据商品类型准备订单数据
    $add_order_data = iopay_add_order_data( $_POST );
    if (!$add_order_data) {
        io_tips_error(__('数据获取失败','i_theme'));
    }

    //创建新订单
    $order = iopay_add_order($add_order_data);
    if (!$order) {
        io_tips_error(__('订单创建失败','i_theme'));
    }

    //设置浏览器缓存
    if (!empty($_POST['post_id']) && !$order['user_id']) {
        $expire = time() + 3600 * 24 * io_get_option('pay_cookie_time', '15');
        setcookie('iopay_' . $order['post_id'] . '_' . $order['merch_index'], $order['order_num'], $expire, '/', '', false);
    }

    $_pay_meta = maybe_unserialize($order['pay_meta']);
    $order_price = isset($_pay_meta[$pay_method]) ? $_pay_meta[$pay_method] : $order['order_price']; //订单价格

    $order_data = array(
        'id'          => $order['id'],
        'user_id'     => $order['user_id'],
        'pay_method'  => $pay_method,
        'order_num'   => $order['order_num'],
        'order_price' => $order_price,
        'ip_address'  => $order['ip_address'],
        'order_name'  => !empty($_POST['order_name']) ? $_POST['order_name'] :  get_bloginfo('name') . '-' . iopay_get_buy_type_name($order_type),
        'return_url'  => !empty($_POST['return_url']) ? $_POST['return_url'] : '',
    );

    $initiate_pay = iopay_initiate_pay($order_data);
    if ('auto_ad_url' === $order_type)
        $initiate_pay['expiration'] = __('已占位，请5分钟内支付。','i_theme');

    echo (json_encode($initiate_pay));
    exit();

}
add_action('wp_ajax_initiate_pay', 'iopay_initiate_order');
add_action('wp_ajax_nopriv_initiate_pay', 'iopay_initiate_order');

/**
 * 订单状态查询
 * @return void
 */
function iopay_check_pay(){
    if (empty($_POST['order_num'])) {
        echo (json_encode(array('error' => 1, 'msg' => __('未生成订单！','i_theme'))));
        exit();
    }
    global $wpdb;
    $order_check = $wpdb->get_row($wpdb->prepare("SELECT `id`,`order_num`,`status` FROM `{$wpdb->iopayorder}` WHERE `order_num` = %s", $_POST['order_num']));
    io_error((array)$order_check);
}
add_action('wp_ajax_check_pay', 'iopay_check_pay');
add_action('wp_ajax_nopriv_check_pay', 'iopay_check_pay');


/**
 * 获取自定义广告不同位置的预设价格
 * 
 * @return never
 */
function iopay_ajax_get_auto_ad_url_product_lists(){
    $loc  = isset($_REQUEST['loc']) ? $_REQUEST['loc'] : 'home';
    $html = iopay_get_product_lists_html($loc, $index);
    echo $html;
    exit;
}
add_action('wp_ajax_get_auto_ad_url_product_lists', 'iopay_ajax_get_auto_ad_url_product_lists');
add_action('wp_ajax_nopriv_get_auto_ad_url_product_lists', 'iopay_ajax_get_auto_ad_url_product_lists');

/**
 * 获取自动广告自定义时间价格
 * 
 * @return never
 */
function iopay_ajax_get_auto_ad_url_custom_product_val(){
    $loc         = isset($_REQUEST['loc']) ? $_REQUEST['loc'] : 'home';
    $custom_time = isset($_REQUEST['custom_time']) ? (int) $_REQUEST['custom_time'] : 0;
    $html        = iopay_get_custom_product_val($loc, $custom_time);
    echo '<span class="text-xs">' . io_get_option('pay_unit', '￥') . '</span>' . $html;
    exit;
}
add_action('wp_ajax_get_auto_ad_url_custom_product_val', 'iopay_ajax_get_auto_ad_url_custom_product_val');
add_action('wp_ajax_nopriv_get_auto_ad_url_custom_product_val', 'iopay_ajax_get_auto_ad_url_custom_product_val');
