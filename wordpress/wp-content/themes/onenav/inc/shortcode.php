<?php
/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_shortcode('site_card', 'add_site_card');
add_shortcode('post_card', 'add_post_card');
add_shortcode('app_card', 'add_app_card');
add_shortcode('book_card', 'add_book_card');
add_shortcode('ad', 'post_ad');
add_shortcode('hide_content', 'add_hide_content');


/**
 * 添加网址小卡片
 * [site_card ids=1]
 * [site_card ids=1,2,3]
 */
function add_site_card($atts, $content = null)
{
    if (is_home() || is_front_page()) {
        return '';
    }
    extract(shortcode_atts(
        array(
            'ids' => ''
        ),
        $atts
    ));

    if (empty($ids)) {
        return '';
    }

    $post_ids = array_filter(io_split_str($ids));
    $myposts = new WP_Query(array(
        'post__in'            => $post_ids,
        'post_type'           => 'sites',
        'orderby'             => 'post__in',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => true
    ));

    $class = (count($post_ids) > 1) ? 'col-2a col-lg-4a --card' : 'col-2a col-md-3a --card';
    $args  = array(
        'class'  => $class,
        'tag'    => 'div',
        'go'     => false,
        'no_tip' => true,
    );

    $content = '';
    if ($myposts->have_posts()) {
        while ($myposts->have_posts()) {
            $myposts->the_post();
            $content .= get_sites_card('min', $args);
        }
    }

    wp_reset_postdata();
 
    return '<div class="posts-row justify-content-center">' . $content . '</div>';
}

/**
 * 添加文章小卡片
 * [post_card ids=1]
 * [post_card ids=1,2,3]
 */
function add_post_card($atts, $content = null)
{
    if (is_home() || is_front_page()) {
        return '';
    }
    extract(shortcode_atts(
        array(
            'ids' => ''
        ),
        $atts
    ));

    if (empty($ids)) {
        return '';
    }

    $post_ids = array_filter(io_split_str($ids));
    $myposts  = new WP_Query(array(
        'post__in'            => $post_ids,
        'post_type'           => 'post',
        'orderby'             => 'post__in',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => true
    ));

    $content = '';
    if ($myposts->have_posts()) {
        $args = array(
            'class' => 'mx-auto --card',
            'tag'   => 'div',
        );
        while ($myposts->have_posts()) {
            $myposts->the_post();
            $content .= get_post_card('min-sm', $args);
        }
    }
    wp_reset_postdata();
    return $content;
}

/**
 * 添加app小卡片
 * [app_card ids=1]
 * [app_card ids=1,2,3]
 */
function add_app_card($atts, $content = null)
{
    if (is_home() || is_front_page()) {
        return '';
    }
    extract(shortcode_atts(
        array(
            'ids' => ''
        ),
        $atts
    ));

    if (empty($ids)) {
        return '';
    }

    $post_ids = array_filter(io_split_str($ids));
    $myposts  = new WP_Query(array(
        'post__in'            => $post_ids,
        'post_type'           => 'app',
        'orderby'             => 'post__in',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => true
    ));

    $content = '';
    if ($myposts->have_posts()) {
        $class = (count($post_ids) > 1) ? 'col-1a col-lg-3a --card' : 'col-1a col-md-2a --card';
        $args  = array(
            'class' => $class,
            'tag'   => 'div',
        );
        while ($myposts->have_posts()) {
            $myposts->the_post();
            $content .= get_app_card('card', $args);
        }
    }
    wp_reset_postdata();

    return '<div class="posts-row justify-content-center">' . $content . '</div>';
}
/**
 * 添加book小卡片
 * [book_card ids=1]
 * [book_card ids=1,2,3]
 */
function add_book_card($atts, $content = null)
{
    if (is_home() || is_front_page()) {
        return '';
    }
    extract(shortcode_atts(
        array(
            'ids' => ''
        ),
        $atts
    ));

    if (empty($ids)) {
        return '';
    }

    $post_ids = array_filter(io_split_str($ids));
    $myposts  = new WP_Query(array(
        'post__in'            => $post_ids,
        'post_type'           => 'book',
        'orderby'             => 'post__in',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => true
    ));

    $content = '';
    if ($myposts->have_posts()) {
        $args  = array(
            'class' => 'col-2a col-md-3a col-lg-5a --card',
            'tag'   => 'div',
        );
        while ($myposts->have_posts()) {
            $myposts->the_post();
            $content .= get_book_card(io_get_book_card_mode(), $args);
        }
    }
    wp_reset_postdata();

    return '<div class="posts-row justify-content-center">' . $content . '</div>';
}

/**
 * 短代码广告
 * [ad]
 */
function post_ad(){
    return '<div class="post-apd my-3">'.stripslashes( io_get_option('ad_po','') ).'</div>';
}


/**
 * 添加隐藏内容
 * 
 * [hide_content type="reply"] [/hide_content]
 * [hide_content type="password" password="123"] [/hide_content]
 */
function add_hide_content($atts, $content = null)
{
    extract(shortcode_atts(array(
        'type'     => 'reply',
        'password' => '',
        'image'    => '',
        'tips'     => '',
    ), $atts));


    if ('<span>' === substr($content, -6) && '</span>' === substr($content, 0, 7)) {
        $content = substr($content, 7, -6);
    }
    
    if ('<p class="hide-after">' === substr($content, -22) && '</p>' === substr($content, 0, 4)) {
        $content = substr($content, 4, -22);
    }
    

    $user_id = get_current_user_id();

    // type 类型 
    $type_text = array(
        'reply'    => __('评论', 'i_theme'),
        'logged'   => __('登录', 'i_theme'),
        'password' => __('密码验证', 'i_theme'),
        'buy'      => __('付费阅读', 'i_theme'),
    );

    global $post;
    if (is_super_admin()) {
        return get_hide_show_html($content, ' - ' . __('管理员可见', 'i_theme') . ' [' . $type_text[$type] . ']');
    }

    if ($user_id && $user_id == $post->post_author) {
        return get_hide_show_html($content, ' - ' . __('作者可见', 'i_theme') . ' [' . $type_text[$type] . ']');
    }

    switch ($type) {
        case 'reply':
            if (io_user_is_commented()) {
                return get_hide_show_html($content);
            } else {
                return get_hide_tips_html($type, $type_text[$type]);
            }
        case 'logged':
            if ($user_id > 0) {
                return get_hide_show_html($content);
            } else {
                return get_hide_tips_html($type, $type_text[$type]);
            }
        case 'password':
            $pas = !empty($_POST['secret-key']) ? $_POST['secret-key'] : '';
            if ($pas && $pas == $password) {
                return get_hide_show_html($content);
            } else {
                return get_hide_tips_html($type, $type_text[$type], $pas, $image, $tips);
            }
        case 'buy':
            $is_buy = iopay_is_buy($post->ID, 0, $post->post_type);
            if ($is_buy) {
                return get_hide_show_html($content);
            } else {
                return get_hide_tips_html($type, $type_text[$type]);
            }
    }
}
/**
 * 获取隐藏块显示的内容
 * 
 * @param mixed $content
 * @param mixed $tips
 * @return string
 */
function get_hide_show_html($content, $tips = ''){
    return '<div class="content-hide-tips show"><div class="hidden-text"><i class="iconfont icon-unlock mr-2"></i>' . sprintf(__('隐藏内容%s','i_theme'), $tips) . '</div>' . do_shortcode($content) . '</div>';
}
/**
 * 获取隐藏块的内容提示
 * 
 * @param mixed $type
 * @param mixed $title
 * @param mixed $pas
 * @param mixed $image
 * @param mixed $tips
 * @return string
 */
function get_hide_tips_html($type, $title, $pas = '', $image = '', $tips = ''){
    $type_ico = array(
        'reply'    => 'icon-comment',
        'logged'   => 'icon-user',
        'password' => 'icon-key-circle',
        'buy'      => 'icon-buy_car',
    );
    $pay      = '';
    switch ($type) {
        case 'reply':
            $action = '<a href="#comments" class="btn smooth no-c px-4"><i class="iconfont ' . $type_ico[$type] . ' mr-2"></i>' . $title . '</a>';
            break;
        case 'logged':
            $action = '<a href="' . esc_url(wp_login_url(io_get_current_url())) . '" class="btn   no-c px-4"><i class="iconfont ' . $type_ico[$type] . ' mr-2"></i>' . $title . '</a>';
            break;
        case 'password':
            $action = $pas ? '<div class="text-xs text-danger"><i class="iconfont icon-warning mr-1"></i>' . __('密码错误，请重新输入', 'i_theme') . '</div>' : '';
            $action .= '<form class="d-flex" action="' . io_get_current_url() . '" method="POST">';
            $action .= '<input type="text" name="secret-key" class="form-control" placeholder="' . __('请输入密码', 'i_theme') . '">';
            $action .= '<button type="submit" class="btn white-nowrap  ml-2 flex-none px-4"><i class="iconfont ' . $type_ico[$type] . ' mr-2"></i>' . __('提交', 'i_theme') . '</button>';
            $action .= '</form>';
            break;
        case 'buy':
            global $post;
            $url = esc_url(add_query_arg(array('action' => 'pay_cashier_modal', 'id' => $post->ID, 'index' => 0), admin_url('admin-ajax.php')));
            $buy_option = get_post_meta($post->ID, 'buy_option', true);
            $pay_price = $buy_option['pay_price'];
            $original_price = $buy_option['price'];
            $unit = '<span class="text-xs">' . io_get_option('pay_unit', '￥') . '</span>';

            $original_price = $original_price && $original_price > $pay_price ? ' <div class="original-price d-inline-block text-xs">' . $unit . $original_price . '</span></div>' : '';
            $pay = '<div><span class="text-xl text-danger">' . $unit . $pay_price . '</span>' . $original_price . '</div>';

            $action = apply_filters('iopay_buy_btn_before', 'shortcode', $buy_option, array());
            if (empty($action)) {
                $pay .= '<div class="w-100 text-md-right"><div class="text-xs tips-box px-2 py-0">多个隐藏块只需支付一次</div></div>';
                $action = '<a href="' . $url . '" class="btn   no-c px-4 io-ajax-modal-get nofx"><i class="iconfont ' . $type_ico[$type] . ' mr-2"></i>' . $title . '</a>';
            }
            break;
    }
    $thumbnail = '<i class="iconfont ' . $type_ico[$type] . ' text-48"></i>';
    if ('password' === $type && '' !== $image) {
        $thumbnail = '<img src="' . $image . '" alt="' . $title . '">';
    }
    $hide = '<div class="content-hide-tips hide-type-' . $type . ' d-flex p-3 flex-column flex-md-row">';
    $hide .= '<div class="card-thumbnail modal-header-bg mx-auto mr-md-3 ' . (('password' === $type && '' !== $image) ? '' : 'd-none d-md-block') . '">';
    $hide .= '<div class="h-100 img-box">' . $thumbnail . '</div>';
    $hide .= '</div>';
    $hide .= '<div class="d-flex flex-fill flex-column">';
    $hide .= '<div class="list-body text-center flex-fill text-md-left my-2 my-md-0">';
    $hide .= '<div class="hide-tips-title"><i class="iconfont icon-lock mr-2"></i>' . __('隐藏内容！', 'i_theme') . '</div>';
    $hide .= '<div class="mt-2 text-xs text-muted"><i class="iconfont icon-tishi mr-1"></i>' . ($tips ? $tips . '<br>' : '') . sprintf(__('%s后才能查看！', 'i_theme'), $title) . '</div>';
    $hide .= $pay;
    $hide .= '</div>';
    $hide .= '<div class="' . ('password' === $type ? '' : 'text-center text-md-right') . ' my-2 my-md-0">' . $action . '</div>';
    $hide .= '</div>';
    $hide .= '</div>';

    return $hide;
}
