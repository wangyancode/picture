<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-25 00:04:08
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 13:48:31
 * @FilePath: /onenav/iopay/functions/iopay-success.php
 * @Description: 
 */



/**
 * 付成功后执行相关处理
 * 
 * @param mixed $order_id 订单号
 * @return void
 */
function iopay_payment_order_success_action($order_id){
    global $wpdb;
    $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->iopayorder} WHERE `order_num` = %s AND `status` = %d", $order_id, 1));

    if($order->post_id){
        //存储文章销量数据，根据文章内商品序号
        $_data = (get_post_meta($order->post_id, 'io_pay_count', true));
        if($_data && is_array($_data)){
            $_data['p' . $order->index] = isset($_data['p' . $order->index]) ? ((int) $_data['p' . $order->index] + 1) : 1;
        }else{
            $_data = array(
                'p' . $order->index => 1,
            );
        }
        update_post_meta($order->post_id, 'io_pay_count', $_data);
        io_add_post_view($order->post_id, 1, 'buy');
    }

    switch ($order->order_type) {
        case 'auto_ad_url':
            $ad_data = maybe_unserialize($order->order_meta);
            $ad_data['order_num'] = $order->order_num;
            iopay_update_auto_ad_url($ad_data);

            //通知管理员
            $data  = $ad_data;
            $check = io_get_option('auto_ad_config', false, 'check');
            if($check){
                $data['check'] = true;
                $data['msg']   = "请在30天内完成审核，否则会自动删除。";
                $title = sprintf(__('[%s]新自动广告需审核', 'i_theme'), get_bloginfo('name'));
            }else{
                $data['check'] = false;
                $title = sprintf(__('[%s]自动广告入驻通知', 'i_theme'), get_bloginfo('name'));
            }
            io_mail_to_admin($title, io_template_add_auto_ad_url($data));

            //待审核通知客户
            if ($check && ((isset($data['contact']) && !empty($data['contact'])) || !empty($data['user_id']))) {
                $title         = sprintf(__('您在站点「%s」申请的自动广告待审核', 'i_theme'), get_bloginfo('name'));
                $data['title'] = $title;
                $go            = !empty($data['contact']) ? $data['contact'] : '';
                if (empty($go)) {
                    $go = get_userdata($data['user_id'])->user_email;
                }
                $data['msg']   = "请等待审核通过或者联系客服，谢谢！";
                io_mail($go, $title, io_template_auto_ad_url_check($data));
            }
            break;
        case 'pay_publish':
            io_update_pay_publish_posts($order);
            break;

        default:
            break;
    }
}
add_action('iopay_payment_order_success', 'iopay_payment_order_success_action');

/**
 * 更新文章发布状态
 * 
 * 检查文章正文是否有标签，有就提取标签作为文章标签，然后删除正文标签
 * @param mixed $order 订单数据
 * @return void
 */
function io_update_pay_publish_posts($order){
    $post_id = (int) $order->post_id;
    $post = get_post($post_id);
    if(!$post){
        return;
    }
    $post_content = $post->post_content;
    
    $keywords = io_extract_inner_content_keys($post_content);
    if (!empty($keywords)) {
        $reg          = '/<div\s+class=\\\?"io-delete-keys\\\?"\s*(?:[^>]*?)>(.*?)<\/div>/s';
        $post_content = preg_replace($reg, '', $post_content); //删除原来的关键词

        $post_type   = $post->post_type;
        $taxonomy = posts_to_tag($post_type);
        wp_set_post_terms($post_id, $keywords, $taxonomy); //设置文章tag
    }

    // 修改文章状态和内容
    $update_id = wp_update_post(array(
        'ID'           => $post_id,
        'post_status'  => 'publish',
        'post_content' => $post_content,
    ));

    // 增加付费标识和次数
    $pay_count = get_post_meta($post_id, 'io_pay_post', true);
    $pay_count = $pay_count ? ((int) $pay_count + 1) : 1;
    update_post_meta($post_id, 'io_pay_post', $pay_count);

    if(is_wp_error($update_id)){
        // 万一失败了通知管理员处理
        $data = array(
            'title'     => $post->post_title,
            'link'      => get_permalink($post_id),
            'pay_time'  => $order->pay_time,
            'pay_money' => $order->pay_price,
            'pay_type'  => $order->pay_type,
            'error'     => $update_id->get_error_message(),
        );
        io_mail_to_admin(sprintf(__('错误：您的站点「%s」付费发布的内容更新状态失败', 'i_theme'), get_bloginfo('name')), io_template_pay_publish_posts_error($data));
        return;
    }

    // 通知管理员
    $data = array(
        'title'     => $post->post_title,
        'link'      => get_permalink($post_id),
        'pay_time'  => $order->pay_time,
        'pay_money' => $order->pay_price,
        'pay_type'  => $order->pay_type,
        'pay_count' => $pay_count,
    );
    io_mail_to_admin(sprintf(__('您的站点「%s」新增付费发布的内容', 'i_theme'), get_bloginfo('name')), io_template_pay_publish_posts($data));
}

/**
 * 提取文章内部关键词
 * @param mixed $html
 * @return array
 */
function io_extract_inner_content_keys($html)
{
    $pattern = '/<div\s+class=\\\?"io-delete-keys\\\?"\s*(?:[^>]*?)>(.*?)<\/div>/s';
    $keys    = [];
    if (preg_match($pattern, $html, $matches)) {
        $keys = io_split_str(trim(strip_tags($matches[1])));
        $keys = array_filter($keys);
    }

    return $keys;
}
