<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-31 16:31:27
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 12:34:15
 * @FilePath: /onenav/inc/functions/uc/index.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$functions = array(
    'u-footprint',
);

foreach ($functions as $function) {
    $path = 'inc/functions/uc/' . $function . '.php';
    require get_theme_file_path($path);
}
/**
 * 获取用户链接
 * @param mixed $tab
 * @return string
 */
function io_get_uc_link($tab = '', $page = ''){
    if(empty($tab)){
        return home_url('user/');
    }

    return home_url('user/' . $tab . (empty($page) ? '' : '/page/' . $page));
}
/**
 * 头部
 * @return void
 */
function io_uc_header()
{
    $user_id   = get_current_user_id();
    if ($user_id) {
        $user_bg   = io_get_user_cover($user_id, "full");
        $user_name = get_the_author_meta('nickname',$user_id);
        $user_desc = get_user_desc($user_id);
    }else{
        $user_bg   = get_theme_file_uri('/assets/images/user-default-cover-full.jpg');
        $user_name = __('游客', 'i_theme');
        $user_desc = __('登录后即可体验更多功能', 'i_theme');
    }
    $content = '<div class="d-flex align-items-center w-100">';
    $content .= '<div class="avatar-img avatar-md">';
    $content .= get_avatar($user_id, 70);
    $content .= '</div>';
    $content .= '<div class="author-meta overflow-hidden ml-2">';
    $content .= '<h1 class="h3 mb-2">' . $user_name . '</h1>';
    $content .= '<div class="text-sm line1">' . $user_desc . '</div>';
    $content .= '</div>';
    $content .= '</div>';

    $html = io_box_head_html('author', $user_bg, $content);

    echo $html;
}
/**
 * 内容
 * @return void
 */
function io_uc_content()
{
    $user_data = wp_get_current_user();
    $route = get_query_var('user_child_route', 'spoor');

    $sidebar = io_us_get_sidebar_menu_html($user_data, $route);

    $body_html   = '';
    if ($user_data->ID) {
        switch ($route) {
            case 'info':
                $body = io_uc_get_info_html($user_data);
                break;

            case 'safe':
                $body = io_us_get_safe_html($user_data);
                break;

            case 'order':
                $body = io_uc_get_order_html($user_data);
                break;

            case 'msgs':
                $body = io_uc_get_msgs_html($user_data);
                break;

            case 'spoor':
            default:
                $body = io_uc_get_footprint_html();
                break;
        }
        $route_data  = allowed_user_routes();
        $placeholder = get_posts_placeholder('uc', 6, false);
        foreach ($route_data as $key => $value) {
            if ($route == $key) {
                $body_html .= '<div class="ajax-load-page tab-pane fade active show" id="user_tab_' . $key . '">' . $body . '</div>';
            } else {
                $body_html .= '<div class="ajax-load-page tab-pane fade" id="user_tab_' . $key . '">' . $placeholder . '</div>';
            }
        }
    }else{
        $body_html = get_none_html();
    }
    $html = '<div class="row">';
    $html .= '<div class="sidebar col-md-3 user-menu">';
    $html .= $sidebar;
    $html .= '</div>';
    $html .= '<div class="col-md-9 uc-content-body">';
    $html .= '<div class="tab-content main-tab-content">';
    $html .= $body_html;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    echo $html;
}
/**
 * 侧边栏菜单
 * @param mixed $user
 * @param mixed $route
 * @return string
 */
function io_us_get_sidebar_menu_html($user, $route)
{
    $user_id  = $user->ID;

    $html = '';
    // 获取用户文章、站点、评论、收藏数量
    if ($user_id) {
        $user_url = get_author_posts_url($user_id);
        $posts_data = array(
            array(
                'title' => __('文章', 'i_theme'),
                'count' => get_user_post_count($user_id, 'publish'),
                'link'  => add_query_arg(['tab' => 'post'], $user_url),
            ),
            array(
                'title' => __('站点', 'i_theme'),
                'count' => get_user_post_count($user_id, 'publish', 'sites'),
                'link'  => add_query_arg(['tab' => 'sites'], $user_url),
            ),
            array(
                'title' => __('评论', 'i_theme'),
                'count' => get_user_comment_count($user_id),
                'link'  => add_query_arg(['tab' => 'comments'], $user_url),
            ),
            array(
                'title' => __('收藏', 'i_theme'),
                'count' => io_count_user_star_all_posts($user_id),
                'link'  => add_query_arg(['tab' => 'star'], $user_url),
            ),
        );
        $html .= '<div class="card mb-4">';
        $html .= '<div class="p-3 d-flex align-items-center">';
        foreach ($posts_data as $item) {
            $html .= '<a href="' . esc_url($item['link']) . '" class="user-menu-item flex-fill d-flex flex-column align-items-center">';
            $html .= '<span  class="text-lg">' . $item['count'] . '</span>';
            $html .= '<span class="text-xs text-muted">' . $item['title'] . '</span>';
            $html .= '</a>';
        }
        $html .= '</div>';
        $html .= '</div>';
    }

    // 获取创作中心box
    if (io_get_option('is_contribute', true)) {
        $contribute_type = io_get_contribute_allow_type();
        if (!empty($contribute_type)) {
            $contribute_link = io_get_template_page_url('template-contribute.php');

            $html .= '<div class="card mb-4">';
            $html .= '<div class="p-3">';
            $html .= '<div class="d-flex align-items-center text-sm text-muted pb-2 border-bottom border-color mb-3 mt-n1"><i class="iconfont icon-creation i-badge vc-l-blue mr-1"></i>' . __('收录投稿', 'i_theme') . '<i class="iconfont icon-arrow-r-m ml-auto"></i></div>';
            $html .= '<div class="d-flex align-items-center">';
            foreach ($contribute_type as $index => $type) {
                $href  = add_query_arg('type', $type, $contribute_link);
                $color = get_theme_color($index + 2, 'j');
                $title = get_new_post_name($type);

                $icon = '<span class="tips-box tips-icon ' . $color . '"><i class="iconfont icon-' . $type . '"></i></span>';
                $html .= '<a rel="nofollow" class="btn-new-posts flex-fill d-flex flex-column align-items-center" href="' . $href . '">' . $icon . '<span class="text-xs text-center mt-1">' . $title . '</span></a>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
    }

    // 垂直排列功能按钮
    $btn_data = array(
        array(
            'route' => 'spoor',
            'title' => __('我的足迹','i_theme'),
            'icon'  => 'icon-time-o',
            'link'  => io_get_uc_link('spoor'),
        ),
        array(
            'route' => 'info',
            'title' => __('我的资料','i_theme'),
            'icon'  => 'icon-data',
            'link'  => io_get_uc_link('info'),
        ),
        array(
            'route' => 'order',
            'title' => __('我的订单','i_theme'),
            'icon'  => 'icon-order',
            'link'  => io_get_uc_link('order'),
        ),
        array(
            'route' => 'msgs',
            'title' => __('消息通知','i_theme'),
            'icon'  => 'icon-message',
            'link'  => io_get_uc_link('msgs'),
        ),
        array(
            'route' => 'safe',
            'title' => __('账号安全','i_theme'),
            'icon'  => 'icon-safe',
            'link'  => io_get_uc_link('safe'),
        ),
    );
    $html .= '<div class="card mb-4">';
    $html .= '<div class="p-2">';
    foreach ($btn_data as $index => $item) {
        $active = $route == $item['route'] ? (wp_is_mobile() ? ' loaded' : ' active loaded') : '';
        $color  = get_theme_color($index + 2, 'l');
        $html .= '<a href="' . esc_url($item['link']) . '" class="uc-set-btn m-1' . $active . '" ajax-tab-page ajax-route ajax-method="page" data-toggle="tab" data-target="#user_tab_' . $item['route'] . '" data-route_back="' . esc_url(io_get_uc_link()) . '"><i class="iconfont ' . $item['icon'] . ' i-badge ' . $color . ' mr-2"></i>' . $item['title'] . '<i class="iconfont icon-arrow-r-m text-sm text-muted ml-auto"></i></a>';
    }
    $html .= '</div>';
    $html .= '</div>';

    // 退出登录
    if($user_id){
        $html .= '<a href="' . wp_logout_url() . '" class="btn btn-block btn-shadow vc-l-red mb-4"><i class="iconfont icon-quit mr-2"></i>' . __('退出登录','i_theme') . '</a>';
    }
    
    return $html;
}
/**
 * 安全信息
 * @param mixed $user
 * @return string
 */
function io_us_get_safe_html($user)
{
    $html = '<div class="card load-ajax-card">';
    $html .= '<div class="card-body">';
    $html .= '<div class="text-lg pb-3 border-bottom border-color border-2w mb-3">' . __('安全信息', 'i_theme') . '</div>';
    $html .= io_get_security_info_bind_btn($user, 'email');
    $html .= (io_get_option('bind_phone', false) ? io_get_security_info_bind_btn($user, 'phone') : '');
    $html .= io_get_security_info_bind_btn($user, 'password');
    $html .= io_bind_oauth_html($user->ID);
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
/**
 * 个人资料设置
 * @param mixed $user
 * @return string
 */
function io_uc_get_info_html($user)
{
    $extra_info = get_user_meta($user->ID,'extra_info',true);

    $data = array(
        'user'   => array(
            'title'       => __('用户名', 'i_theme'),
            'value'       => $user->user_login,
            'placeholder' => '',
        ),
        'name'   => array(
            'title'       => __('昵称', 'i_theme'),
            'value'       => $user->nickname,
            'placeholder' => '',
        ),
        'avatar' => array(
            'title'       => __('头像', 'i_theme'),
            'value'       => $user->avatar_type,
            'placeholder' => '',
        ),
        'url'    => array(
            'title'       => __('网址', 'i_theme'),
            'value'       => $user->user_url,
            'placeholder' => '',
        ),
        'desc'   => array(
            'title'       => __('个人描述', 'i_theme'),
            'value'       => $user->description,
            'placeholder' => __('帅气的我简直无法用语言描述！', 'i_theme'),
        ),
        'cover'  => array(
            'title'       => __('个人封面', 'i_theme'),
            'value'       => io_get_user_cover($user->ID, 'full'),
            'placeholder' => '',
        ),
        'qq'     => array(
            'title'       => __('QQ', 'i_theme'),
            'value'       => isset($extra_info['qq']) ? $extra_info['qq'] : '',
            'placeholder' => '',
        ),
        'wechat' => array(
            'title'       => __('微信', 'i_theme'),
            'value'       => isset($extra_info['wechat']) ? $extra_info['wechat'] : '',
            'placeholder' => '',
        ),
        'weibo'  => array(
            'title'       => __('微博', 'i_theme'),
            'value'       => isset($extra_info['weibo']) ? $extra_info['weibo'] : '',
            'placeholder' => '',
        ),
        'github' => array(
            'title'       => __('Github', 'i_theme'),
            'value'       => isset($extra_info['github']) ? $extra_info['github'] : '',
            'placeholder' => '',
        ),
    );

    $html = '<div class="card load-ajax-card">';
    $html .= '<div class="card-body">';
    $html .= '<form class="io-user-ajax" method="post">';

    foreach ($data as $key => $item) {
        if ($key == 'avatar') {
            $html .= io_get_user_avatar_set_html($user, $item);
            continue;
        }elseif($key == 'cover'){
            $html .= io_get_user_cover_set_html($item);
            continue;
        }elseif($key == 'user'){// && !get_user_meta($user->ID, 'name_change', true)){
            continue;
        }
        $html .= '<div class="form-group row">';
        $html .= '<label for="uc_' . $key . '" class="col-sm-3 col-md-2 col-form-label">' . $item['title'] . '</label>';
        $html .= '<div class="col-sm-9 col-md-10">';
        $html .= '<input type="text" class="form-control" id="uc_' . $key . '" name="' . $key . '" placeholder="' . esc_attr($item['placeholder']) . '" value="' . esc_attr($item['value']) . '">';
        $html .= '</div>';
        $html .= '</div>';
    } 
    
    $html .= '<input type="hidden" name="action" value="change_user_info">';
    $html .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce('change_user_info') . '"/>';
    $html .= '<div class="form-group row">';
    $html .= '<label class="col-sm-3 col-md-2 col-form-label"></label> ';
    $html .= '<div class="col-sm-9 col-md-10">';
    $html .= '<button type="submit" class="submit btn vc-yellow px-4">' . __('保存资料', 'i_theme') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
/**
 * 头像设置
 * @param mixed $user
 * @param mixed $data
 * @return string
 */
function io_get_user_avatar_set_html($user, $data){
    $avatar_type = $data['value'];
    $html = '';
    $html .= '<div class="form-group row">';
    $html .= '<label class="col-sm-3 col-md-2 col-form-label">' . $data['title'] . '</label>';

    $html .= '<div class="col-sm-9 col-md-10">';
    $html .= '<div class="avatar-lists">';

    $html .= '<label class="avatar-img avatar-md m-2 text-center local-avatar-label position-relative" title="' . __('上传头像', 'i_theme') . '">';
    $html .= '<input type="radio" id="avatar-custom" name="avatar" class="hide" value="custom" ' . (($avatar_type == 'custom') ? 'checked' : '') . '>';
    $html .= '<img src="' . ($user->custom_avatar ?: get_theme_file_uri('/assets/images/t.png')) . '" class="custom-avatar-radio io-avatar-custom avatar rounded-circle" data-filename="' . $user->ID . '.jpg" width="38" height="38">';
    $html .= '<span class="custom-avatar-picker user-img-picker"><i class="iconfont icon-camera"></i></span>';
    $html .= '<span class="text-xs text-muted" for="avatar-custom">' . __('自定义', 'i_theme') . '</span>';
    $html .= '</label>';

    $html .= '<label class="avatar-img avatar-md m-2 text-center letter-avatar-label">';
    $html .= '<input type="radio" id="avatar-letter" name="avatar" class="hide" value="letter" ' . (($avatar_type == 'letter') ? 'checked' : '') . '>';
    $html .= '<img src="' . get_avatar_url($user->user_email, array('size' => 80)) . '" class="custom-avatar-radio avatar rounded-circle" width="38" height="38">';
    $html .= '<span class="text-xs text-muted" for="avatar-letter">' . __('默认', 'i_theme') . '</span>';
    $html .= '</label>';

    $avatar_list = get_open_avatar_list($user);
    if (!empty($avatar_list) && is_array($avatar_list)) {
        foreach ($avatar_list as $type => $url) {
            $html .= '<label class="avatar-img avatar-md m-2 text-center ' . $type . '-avatar-label">';
            $html .= '<input type="radio" id="avatar-' . $type . '" name="avatar" class="hide" value="' . $type . '" ' . ($avatar_type == $type ? 'checked' : '') . '>';
            $html .= '<img src="' . $url . '" class="custom-avatar-radio avatar rounded-circle" width="38" height="38">';
            $html .= '<span class="text-xs text-muted" for="avatar-' . $type . '">' . sprintf(__('%s头像', 'i_theme'), get_open_login_name($type)) . '</span>';
            $html .= '</label>';
        }
    }

    $html .= '</div>';
    $html .= '</div> ';
    $html .= '</div>';
    return $html;
}
/**
 * 个人封面设置
 * @param mixed $data
 * @return string
 */
function io_get_user_cover_set_html($data){
    $html = '';
    $html .= '<div class="form-group row">';
    $html .= '<label class="col-sm-3 col-md-2 col-form-label">' . $data['title'] . '</label>';
    $html .= '<div class="col-sm-9 col-md-10">';
    $html .= '<div class="user-cover-label position-relative "><img src="' . esc_url($data['value']) . '" class="io-cover-custom" />';  
    $html .= '<span class="custom-cover-picker user-img-picker"><i class="iconfont icon-camera"></i></span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 消息列表
 * @param mixed $user
 * @return string
 */
function io_uc_get_msgs_html($user)
{
    $io_filter_type = get_query_var('user_grandchild_route');
    $io_page        = get_query_var('page') ?: 1;
    $data           = io_get_notification_data($user->ID, $io_filter_type, $io_page);
    $notifications  = $data->notifications;
    $count          = $data->count;
    $total          = $data->total;
    $max_pages      = $data->max_pages;

    $list = '';
    if ($count > 0) {
        $list .= '<ul class="msgs-list ajax-posts-row">';
        foreach ($notifications as $notification) {
            $list .= '<li id="msgs-item-' . $notification->id . '" class="msgs-item text-sm my-4 p-2 ajax-item">';
            $list .= '<div class="mr-3 text-muted text-xs notifi-time">' . $notification->msg_date . '</div>';
            $list .= __('发送者: ', 'i_theme');
            $list .= '<span class="mr-3">';
            if ($notification->sender_id != 0) {
                $list .= '<a href="' . get_author_posts_url($notification->sender_id) . '" target="_blank">' . $notification->sender . '</a>';
            } else {
                $list .= $notification->sender;
            }
            $list .= '</span>';
            $list .= '<span class="mr-3">' . $notification->msg_title . '</span>';
            if (!empty($notification->msg_content)) {
                $list .= '<div class="msgs-content p-2 mt-2">';
                $list .= '<p>' . htmlspecialchars_decode($notification->msg_content) . '</p>';
                $list .= '</div>';
            }
            $list .= '</li>';
        }
        $list .= '</ul>';
    }else{
        $list .= get_none_html();
    }

    $next = '';
    if ($max_pages > 1) {
        if ($io_page < $max_pages) {
            $next_link = $data->next_page;
            $next = '<div class="posts-nav mb-4"><div class="next-page ajax-posts-load text-center"><a href="' . $next_link . '" class="">' . __('加载更多', 'i_theme') . '</a></div></div>';
        } else {
            $next = '<div class="posts-nav mb-4"><div class="next-page text-center"><a href="javascript:;" class="">' . __('没有更多了', 'i_theme') . '</a></div></div>';
        }
    }

    $html = '<div class="card load-ajax-card">';
    $html .= '<div class="card-body"> ';
    $html .= '<div class="pb-3 border-bottom border-color border-2w mb-3 d-flex">' . __('站内消息', 'i_theme') . '<span class="ml-auto text-sm text-muted">' . sprintf(__('总共 %d 条消息', 'i_theme'), $total) . '</span></div>';
  
    $html .= '<div class="info-group">';
    $html .= $list;
    $html .= $next;
    $html .= '</div>';

    $html .= '</div>';
    $html .= '</div>';
        
    return $html;
}

/**
 * 我的订单
 * @param mixed $user
 * @return string
 */
function io_uc_get_order_html($user)
{
    $page   = get_query_var('page') ?: 1;
    $orders = iopay_get_order_list($user->ID, $page);
    $list   = '';
    $next   = '';
    if ($orders) {
        $list .= '<ul class="order-list ajax-posts-row">';
        foreach ($orders['orders'] as $order) {
            $pay_type_name = '<span class="badge vc-l-theme mr-1">' . iopay_get_buy_type_name($order->order_type, true) . '</span>';
            $post_title    = '';
            if ($order->post_id && 'pay_publish' !== $order->order_type) {
                $post       = get_post($order->post_id);
                $post_title = '<a href="' . get_permalink($order->post_id) . '" target="_blank" title="查看文章">' . $post->post_title . '</a>';
            }

            $list .= '<li class="order-item ajax-item">';
            $list .= '<div class="order-pay-title mb-2">' . $pay_type_name . $post_title . '</div>';
            $list .= '<div class="d-flex align-items-end">';
            $list .= '<div class="text-xs text-muted">';
            $list .= '<div class="order-num">' . $order->order_num . '</div>';
            $list .= '<div class="order-time">' . $order->pay_time . '</div>';
            $list .= '</div>';
            $list .= '<div class="order-price ml-auto">' . $order->order_price . '</div>';
            $list .= '</div>';
            $list .= '</li>';
        }
        $list .= '</ul>';

        if ($orders['total_pages'] > 1) {
            if ($page < $orders['total_pages']) {
                $next_link = io_get_uc_link('order', $page + 1);
                $next      = '<div class="posts-nav mb-4"><div class="next-page ajax-posts-load text-center"><a href="' . $next_link . '" class="">' . __('加载更多', 'i_theme') . '</a></div></div>';
            } else {
                $next = '<div class="posts-nav mb-4"><div class="next-page text-center"><a href="javascript:;" class="">' . __('没有更多了', 'i_theme') . '</a></div></div>';
            }
        }
    } else {
        $list .= get_none_html();
    }

    $html = '';
    $html .= '<div class="load-ajax-card">';
    $html .= '<div class="text-md mb-3"><i class="iconfont icon-order i-badge vc-l-yellow mr-2"></i>' . __('我的订单', 'i_theme') . '</div>';
    $html .= $list;
    $html .= $next;
    $html .= '</div>';
    return $html;
}


function io_uc_footer_action()
{
    if (!is_io_user())
        return;


    if (io_get_oauth_login_url('weixin_gzh')) {
        get_weixin_qr_js();
    }
}
add_action('wp_footer', 'io_uc_footer_action');
