<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-24 17:22:44
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 15:48:59
 * @FilePath: /onenav/iopay/action/ajax-posts.php
 * @Description: 
 */

/**
 * 获取自动广告支付模态框
 * 
 * @return never
 */
function iopay_ajax_posts_pay_auto_publish()
{
    $post_id   = __post('post_id');
    $post_type = __post('post_type');
    if (!$post_id || !$post_type) {
        io_ajax_notice_modal('danger', __('参数异常', 'i_theme'));
    }
    $modal = iopay_posts_pay_publish_modal($post_id);
    echo $modal;
    exit;
}
add_action('wp_ajax_posts_pay_publish', 'iopay_ajax_posts_pay_auto_publish');
add_action('wp_ajax_nopriv_posts_pay_publish', 'iopay_ajax_posts_pay_auto_publish');


/**
 * 文章购买发布模态框
 * 
 * @param mixed $id 文章ID
 * @return string|null
 */
function iopay_posts_pay_publish_modal($post_id = 0, $post_type = 'sites')
{
    if (!$post_id) {
        return;
    }
    $user_id = get_current_user_id();

    $post_status = get_post_status($post_id);
    if ('publish' == $post_status) {
        io_ajax_notice_modal('info', __('文章已发布', 'i_theme'));
        return;
    }

    $option = io_get_option($post_type . '_tg_config', array(), 'pay');
    if (!$option['status']) {
        io_ajax_notice_modal('info', __('付费发布功能已关闭', 'i_theme'));
        return;
    }

    $buy_type        = 'pay_publish';
    $order_type_name = iopay_get_buy_type_name($buy_type, true);

    $pay_title = $option['title'];

    $order_name = get_bloginfo('name') . '-' . iopay_get_buy_type_name($buy_type);

    $unit      = io_get_option('pay_unit', '￥');
    $pay_limit = $option['limit']; //购买用户组限制 all 所以  user 登陆

    //价格
    $prices         = iopay_get_pay_publish_prices($option['prices'], $post_id);
    $original_price = $prices[1];
    $pay_price      = $prices[0];

    $original_price = $original_price && $original_price > $pay_price ? '<div class="original-price d-inline-block text-sm">' . __('原价', 'i_theme') . ' <span class="text-xs">' . $unit . '</span><span>' . $original_price . '</span></div>' : '';

    $pay_name = '价格';
    $price    = '<div class="mb-3 muted-box text-center order-type-' . $buy_type . '">';
    $price .= '<div class="text-ss tips-top-l"><i class="iconfont icon-jinbi mr-1"></i>' . $pay_name . '</div>';

    $price .= '<div class="text-danger"><span class="text-md">' . $unit . '</span><span class="text-32 text-height-xs">' . $pay_price . '</span></div>';
    $price .= $original_price;

    $price .= '</div>';

    $form = '<form>';
    $form .= '<input type="hidden" name="post_id" value="' . $post_id . '">';
    $form .= '<input type="hidden" name="post_type" value="' . $post_type . '">';
    $form .= '<input type="hidden" name="order_type" value="' . $buy_type . '">';
    $form .= '<input type="hidden" name="order_name" value="' . $order_name . '">';
    $form .= iopay_get_pay_input($buy_type);
    $form .= '</form>';

    $tips = io_get_option('pay_tips_multi', '');
    if ($tips) {
        $tips = '<div class="bg-muted text-muted br-bottom-inherit text-xs text-center p-2">' . $tips . '</div>';
    }

    $html = io_get_modal_header('', 'icon-buy_car');
    $html .= '<div class="modal-body blur-bg">';
    $html .= '<div class="p-3">';
    $html .= "<div class='d-flex align-items-center justify-content-center flex-wrap mb-3'><span class='tips-box vc-red text-xs px-1 py-0 mr-2'>{$order_type_name}</span>{$pay_title}</div>";
    $html .= $price . $form;
    $html .= '</div>';
    $html .= $tips;
    $html .= '</div>';

    return $html;
}

/**
 * 获取支付表单
 * 
 * 以level为key的关联数组
 * @param array $prices 设置的价格列表
 * @return array
 */
function iopay_get_pay_publish_prices_data($prices){
    $p = array();
    if (count($prices) > 1) {
        foreach ($prices as $price) {
            $p[$price['level']] = [$price['pay_price'], $price['price']];
        }
    }else{
        $p[] = [$prices[0]['pay_price'], $prices[0]['price']];
    }
    return $p;
}

/**
 * 获取支付和原价
 * @param array $prices 设置的价格列表
 * @param int $post_id 文章ID
 * @return array
 */
function iopay_get_pay_publish_prices($prices, $post_id = 0)
{
    $user_id = get_current_user_id();

    if ($post_id) {
        $audit_edit        = io_get_option('audit_edit', true); // 编辑后是否需要审核
        $again_edit_rebate = io_get_option('again_edit_rebate', 80); // 再次付费折扣
        $is_pay_post       = get_post_meta($post_id, 'io_pay_post', true); // 是否是付费内容

        $is_again_pay = $audit_edit && $is_pay_post && 0 != $again_edit_rebate; // 是否需要再次付费
    } else {
        $is_again_pay = false;
    }

    $prices         = iopay_get_pay_publish_prices_data($prices);
    $original_price = 0;
    $pay_price      = 0;
    if (count($prices) > 1) {
        if ($user_id) {
            $original_price = $prices['user'][1];
            $pay_price      = $prices['user'][0];
        } else {
            $original_price = $prices['all'][1];
            $pay_price      = $prices['all'][0];
        }
    } else {
        $original_price = $prices[0][1];
        $pay_price      = $prices[0][0];
    }

    if ($is_again_pay) {
        $pay_price = round($pay_price * $again_edit_rebate / 100, 2);
    }
    return array($pay_price, $original_price);
}
