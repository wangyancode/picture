<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-09 05:27:27
 * @LastEditors: iowen
 * @LastEditTime: 2024-12-22 21:02:15
 * @FilePath: /onenav/iopay/admin/admin.php
 * @Description: 
 */

$functions = array(
    'f-ad',
    'f-order',
);

foreach ($functions as $function) {
    $path = 'iopay/admin/functions/' . $function . '.php';
    require get_theme_file_path($path);
}

/**
 * 添加后台静态资源
 * 
 * @return void
 */
function iopay_setting_scripts(){
    if (isset($_GET['page']) && in_array($_GET['page'], array("io_ad","io_mall"))) {
        wp_enqueue_style('iopay-css', get_theme_file_uri('/iopay/assets/css/iopay-admin.css'), array(), '');
        wp_enqueue_script('iopay-admin',  get_theme_file_uri('/iopay/assets/js/iopay-admin.js') , array('jquery'), '');
    }
}
add_action('admin_enqueue_scripts', 'iopay_setting_scripts');

function iopay_register_mall_menu_page(){
    global $menu;
    $menu[57] = array('', 'read', "separator3", '', 'wp-menu-separator');

    $check_count = '';
    $check_ad    = count(iopay_get_valid_auto_ad_url('AHP', '1', '0'));
    if($check_ad > 0){
        $check_count = ' <span class="update-plugins"><span class="update-count menu-bubble">'.$check_ad.'</span></span>';
    }
    $hook = add_menu_page('商城数据', '商城数据', 'manage_options', 'io_mall', 'iopay_mall_page_callback', 'dashicons-cart', 58);
    add_submenu_page('io_mall', '订单数据', '订单数据', 'manage_options', 'io_order', 'iopay_order_page_callback');
    add_submenu_page('io_mall', '自动广告', '自动广告'.$check_count, 'manage_options', 'io_ad', 'iopay_ad_page_callback');
    //add_action("load-$hook", 'io_invitation_code_update');
    //add_action("load-$hook", 'screen_option');
}
add_action( 'admin_menu', 'iopay_register_mall_menu_page', 2 );



function iopay_mall_page_callback() {
    require get_theme_file_path('iopay/admin/page/index.php');
}

function iopay_order_page_callback() {
    require get_theme_file_path('iopay/admin/page/order.php');
}

function iopay_ad_page_callback() {
    require get_theme_file_path('iopay/admin/page/ad.php');
}

/**
 * 后台顶部提示
 * 
 * @return void
 */
function io_auto_check_count_prompt_menu() {
    if( ! is_admin() ) { return; }
    global $wp_admin_bar; 
    $check_ad = count(iopay_get_valid_auto_ad_url('AHP', '1', '0'));
    if ($check_ad) : 
        $wp_admin_bar->add_menu(array(
            'id' => 'check_ad',  
            'title' => '<span class="update-plugins count-2" style="display: inline-block;background-color:#d11ce4;color: #fff;font-size: 9px;font-weight: 600;border-radius: 10px;z-index: 26;height: 18px;margin-right: 5px;"><span class="update-count" style="display: block;padding: 0 6px;line-height: 17px;">'.$check_ad.'</span></span>个需审核广告', 
            'href' => admin_url('/admin.php?page=io_ad&ad_status=check')
        ));     
    endif; 
}
add_action('admin_bar_menu', 'io_auto_check_count_prompt_menu', 2000);


function iopay_admin_list_views($views='', $link = '', $type='ad') {
    if ( empty( $views ) ) {
        return;
    }
    $lists = array();
    echo "<ul class='subsubsub'>\n";
    foreach ( $views as $class => $view ) {
        if (empty($view['count']))
            continue;
        $url = add_query_arg(array($type.'_status' => $class), $link);
        $current = '';
        if((isset($_GET[$type.'_status'])&&$_GET[$type.'_status']==$class) || (!isset($_GET[$type.'_status']) && 'all' == $class )){
            $current = "class='current'";
        }
        $lists[$class] = "\t<li class='{$class}'><a href='{$url}' {$current}>{$view['title']}<span class='count'>({$view['count']})</span></a>";
    }
    echo implode( " |</li>\n", $lists ) . "</li>\n";
    echo '</ul>';
}

function iopay_admin_post_init() { 
    if ( isset( $_REQUEST['page'] ) && 'io_ad'===$_REQUEST['page'] ) {
        $parent_file = "admin.php?page=io_ad";
        
        $doaction = io_current_action();
        if ( $doaction ) { 
            check_admin_referer( 'io_ad_action' );
        
            $sendback = remove_query_arg( array( 'deleted', 'locked',  'updated', 'ids' ), wp_get_referer() );
            if ( ! $sendback ) {
                $sendback = admin_url( $parent_file );
            }
        
            $post_ids = array();
        
            if ( 'delete_all' === $doaction ) {
                $doaction = 'delete';
            } elseif ( isset( $_REQUEST['ids'] ) ) {
                $post_ids = explode( ',', $_REQUEST['ids'] );
            } elseif ( isset( $_REQUEST['id'] ) ) {
                $post_ids[] = $_REQUEST['id'];
            } 
        
            if ( empty( $post_ids ) ) {
                wp_redirect( $sendback );
                exit;
            }
        
            switch ( $doaction ) {
                case 'delete':
                    $deleted = 0;
                    foreach ( (array) $post_ids as $id ) {

                        iopay_delete_auto_ad($id);
                        
                        $deleted++;
                    }
                    $sendback = add_query_arg( 'deleted', $deleted, $sendback );
                    break;
                case 'edit': 
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(iopay_get_auto_ad($_REQUEST['id']), JSON_NUMERIC_CHECK);
                    break;
                case 'check':
                    iopay_check_auto_ad($_REQUEST['id']);
                    echo json_encode(array('error'=>0,'msg'=>'更新成功','reload'=>1));
                    break;
                case 'uncheck':
                    iopay_check_auto_ad($_REQUEST['id'], 2);
                    echo json_encode(array('error'=>0,'msg'=>'更新成功','reload'=>1));
                    break;
                case 'add':
                case 'update':
                    $data = $_POST;
                    if( empty($data['name']) || empty($data['url']) || empty($data['loc']) || empty($data['expiry']) ){
                        io_error(array('error'=>1,'msg'=>'数据不能留空'));
                    }
                    if(!io_is_url($data['url'])){
                        io_error(array('error'=>1,'msg'=>'url格式有误！'));
                    }
                    unset($data['ajax']);
                    unset($data['action']);
                    $data['expiry'] = date('Y-m-d H:i:s', strtotime($data['expiry']));
                    $data['status'] = isset($data['status']) ? 1 : 0;
                    $data['nofollow'] = isset($data['nofollow']) ? 1 : 0;
                    if('update'===$doaction){
                        iopay_update_auto_ad($data);
                        echo json_encode(array('error'=>0,'msg'=>'更新成功','reload'=>1));
                    }else{
                        $data['token']   = str_shuffle(time()) . mt_rand(100, 999);
                        $data['user_id'] = get_current_user_id();
                        iopay_add_auto_ad_url($data);
                        echo json_encode(array('error'=>0,'msg'=>'添加成功','reload'=>1));
                    }
                    break;
                default:
                    break;
            }
            if(isset($_REQUEST['ajax']) && $_REQUEST['ajax']){
                header('Content-Type: application/json; charset=utf-8');
                exit;
            }
            $sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );
            wp_redirect( $sendback );
            exit;
        } elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
            wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
            exit;
        }
    }
    if ( isset( $_REQUEST['page'] ) && 'io_order'===$_REQUEST['page'] ) {
        $parent_file = "admin.php?page=io_order";
        
        $doaction = io_current_action();
        if ( $doaction ) { 
            check_admin_referer( 'io_order_action' );
        
            $sendback = remove_query_arg( array( 'deleted', 'locked',  'updated', 'ids' ), wp_get_referer() );
            if ( ! $sendback ) {
                $sendback = admin_url( $parent_file );
            }
        
            $post_ids = array();
        
            if ( 'delete_all' === $doaction ) {
                $doaction = 'delete';
            } elseif ( isset( $_REQUEST['ids'] ) ) {
                $post_ids = explode( ',', $_REQUEST['ids'] );
            } elseif ( isset( $_REQUEST['id'] ) ) {
                $post_ids[] = $_REQUEST['id'];
            } 
        
            if ( empty( $post_ids ) ) {
                wp_redirect( $sendback );
                exit;
            }

            switch ( $doaction ) {
                case 'delete':
                    $deleted = 0;
                    foreach ( (array) $post_ids as $id ) {

                        iopay_delete_order($id);
                        
                        $deleted++;
                    }
                    $sendback = add_query_arg( 'deleted', $deleted, $sendback );
                    break;
                case 'clear_order': 
                    iopay_clear_order();
                    break;
                default:
                    break;
            }
            if(isset($_REQUEST['ajax']) && $_REQUEST['ajax']){
                header('Content-Type: application/json; charset=utf-8');
                exit;
            }
            $sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );
            wp_redirect( $sendback );
            exit;
        } elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
            wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
            exit;
        }
    }
    do_action('io_admin_post_action');
} 
add_action('admin_init', 'iopay_admin_post_init', 1);
