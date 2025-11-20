<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-02 14:25:50
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-11 18:59:00
 * @FilePath: /onenav/iopay/functions/iopay-order.php
 * @Description: 
 */

/**
 * 新增订单
 * 
 * @param array $data
 * @return array|bool|string 成功则返回订单数据
 */
function iopay_add_order($data){
    return iopay_update_order($data);
}

/**
 * 订单更新和插入
 * 
 * @param array $data 订单数据
 * @return array|bool|string 成功则返回订单数据
 */
function iopay_update_order($data){
    global $wpdb;
    $defaults = array(
        'id'            => '',
        'user_id'       => '',
        'ip_address'    => '',
        'merch_index'   => 0,
        'post_id'       => '',
        'post_user_id'  => '',
        'order_price'   => '',
        'order_type'    => '',
        'order_meta'    => '',
        'create_time'   => '',
        'pay_num'       => '',
        'pay_type'      => '',
        'pay_price'     => '',
        'pay_meta'      => '',
        'pay_time'      => '',
        'status'        => 0,
        'other'         => '',
    );
    $order_data = wp_parse_args((array) $data, $defaults);
    $order_data = wp_unslash($order_data);

    if (!empty($order_data['id'])) { // 更新现有订单
        unset($order_data['id']);
        unset($order_data['create_time']);
        $order_data = array_filter($order_data);
        if (!$order_data) {
            return false;
        }
        if ($wpdb->update($wpdb->iopayorder, $order_data, array('id' => $data['id']))) {
            $order_data['id'] = $data['id'];
            return $order_data;
        }
    }
    // 增加订单
    if (!$order_data['post_user_id'] && $order_data['post_id']) {
        $post = get_post($order_data['post_id']);
        if (!empty($post->post_author)) {
            $order_data['post_user_id'] = $post->post_author;
        }
    }
    $order_data['user_id']      = $order_data['user_id'] ? $order_data['user_id'] : get_current_user_id();
    $order_data['create_time']  = current_time("Y-m-d H:i:s");
    $order_data['ip_address']   = IOTOOLS::get_ip();
    $order_data['order_num']    = current_time('ymdHis'). mt_rand(100, 999) . mt_rand(100, 999); // 订单号

    if ($wpdb->insert($wpdb->iopayorder, $order_data)) {
        $order_data['id'] = $wpdb->insert_id;
        return $order_data;
    }
    return false;
}

/**
 * 支付成功更新订单
 * 
 * @param string $order_id
 * @param array $data
 * @return array|bool|object
 */
function iopay_confirm_pay($order_id, $data){
    global $wpdb;
    $order_data = array(
        'pay_type'  => $data['pay_type'],
        'pay_price' => $data['pay_price'],
        'pay_num'   => $data['pay_num'],
        'status'    => 1,
        'other'     => maybe_serialize($data['other']),
        'pay_time'  => current_time("Y-m-d H:i:s"),
    );
    $where = array(
        'order_num' => $order_id, 
        'status'    => 0
    );
    if ($wpdb->update($wpdb->iopayorder, $order_data, $where)) {
        do_action('iopay_payment_order_success', $order_id);
        return true;
    }
    return false;
}
/**
 * 获取订单信息
 * 
 * @param mixed $id
 * @param mixed $type 类型 id 或者 order_num 
 * @return array|bool|null|object
 */
function iopay_get_order($id = '', $type = 'id'){
    if ( !$id ) {
        return false;
    }
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->iopayorder} WHERE `{$type}` = %s AND `status` = %d", $id, 1));
}


/**
 * 删除订单
 * 
 * @param int $id 订单ID
 * @param string $type 类型 id 或者 order_num 
 * @return bool
 */
function iopay_delete_order($id = '', $type = 'id'){
    if ( !$id ) {
        return false;
    }
    global $wpdb;
    if ($wpdb->query("DELETE FROM {$wpdb->iopayorder} WHERE `{$type}` = $id")) {
        return true;
    }
    return false;
}

/**
 * 清理无效订单
 * 
 * @param mixed $days_ago
 * @return bool
 */
function iopay_clear_order($days_ago = 15){
    global $wpdb;
    $ago_time = date("Y-m-d H:i:s", strtotime("-{$days_ago} day", strtotime(current_time('mysql'))));
    if ($wpdb->query("DELETE FROM {$wpdb->iopayorder} WHERE `status` = 0 and `create_time` < '{$ago_time}'")){
        return true;
    }
    return false;
}

/**
 * 获取订单列表及总页数
 * 
 * @global WPDB $wpdb
 * @param int $user_id 用户ID
 * @param int $page 页码
 * @param int $status 0未支付 1已支付 默认1
 * @param int $limit 每页数量
 * @return array|bool|null|object
 */
function iopay_get_order_list($user_id = '', $page = 1, $status = 1, $limit = 10)
{
    global $wpdb;

    $page   = max(1, $page);
    $offset = ($page - 1) * $limit;
    $list   = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->iopayorder} WHERE `user_id` = %d AND `status` = %d ORDER BY `id` DESC LIMIT %d OFFSET %d",
        $user_id,
        $status,
        $limit,
        $offset
    ));

    if ($list) {

        $total_orders = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->iopayorder} WHERE `user_id` = %d AND `status` = %d",
            $user_id,
            $status
        ));

        $total_pages = $limit > 0 ? ceil($total_orders / $limit) : 1;

        return [
            'orders'      => $list,
            'total_pages' => $total_pages,
        ];
    }
    return false;
}
