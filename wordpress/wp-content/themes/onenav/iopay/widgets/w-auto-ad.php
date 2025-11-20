<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-06 16:26:50
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-07 21:18:22
 * @FilePath: /onenav/iopay/widgets/w-auto-ad.php
 * @Description: 
 */

/**
 * 获取自动广告box
 * @param string $loc 位置
 * @param string $home_loc 位置
 * @param bool $echo
 * @return mixed
 */
function iopay_get_auto_ad_html($loc, $class = '', $loc_home='search', $echo = true){
    $html = '';
    $html = apply_filters('io_auto_ad_html_before_filters', $html, $loc, $class, $loc_home, $echo);
    if(!io_get_option('auto_ad_s',true)){
        if($echo){
            echo $html;
            return;
        } else {
            return $html;
        }
    }
    $config = io_get_option('auto_ad_config', array());
    
    $column  = array(
        '1'  => 'row-col-1a',
        '2'  => 'row-col-2a',
        '3'  => 'row-col-3a',
        '4'  => 'row-col-3a row-col-md-4a',
        '5'  => 'row-col-3a row-col-md-5a',
        '6'  => 'row-col-3a row-col-md-6a',
        '7'  => 'row-col-3a row-col-md-6a row-col-lg-7a',
        '8'  => 'row-col-3a row-col-md-6a row-col-lg-8a',
        '9'  => 'row-col-3a row-col-md-6a row-col-lg-9a',
        '10' => 'row-col-3a row-col-md-6a row-col-lg-9a row-col-xl-10a',
        '11' => 'row-col-3a row-col-md-6a row-col-lg-9a row-col-xl-11a',
        '12' => 'row-col-3a row-col-md-6a row-col-lg-9a row-col-xl-12a',
    );
    $column  = $column[$config['column']];
    
    if (!isset($config['loc']) || !in_array($loc, $config['loc']) || ('home' === $loc && $loc_home != $config['loc_home'])) {
        return $html;
    }
    $url = add_query_arg(array('action' => 'pay_auto_ad_modal', 'loc' => $loc), admin_url('admin-ajax.php'));
    $btn = '<a href="'.$url.'" class="btn vc-yellow btn-outline btn-sm py-0 io-ajax-modal-get nofx ml-auto" data-modal_type="overflow-hidden">'._iol($config['btn_multi'],'btn_multi').'</a>';

    $html .= '<div class="auto-ad-url '.$class.'">';
    $html .= '<div class="card my-0 mx-auto">';
    $html .= '<div class="card-head d-flex align-items-center pb-0 px-2 pt-2">';
    $html .= '<div class="text-sm">'._iol($config['title_multi'],'title_multi').'</div>';
    $html .= $btn;
    $html .= '</div>';
    if($config['ajax']){
        $_url = add_query_arg(array('action' => 'get_auto_ad_url_list', 'loc' => $loc), admin_url('admin-ajax.php'));
        $html .= '<div class="card-body auto-ad-body pt-1 pb-1 px-2 posts-row ' . $column . ' ajax-auto-get" data-target=".auto-ad-body" data-href="' . $_url . '">';
        $html .= iopay_get_auto_ad_url_list('', true);
        $html .= '</div>';
    }else{
        $html .= '<div class="card-body pt-1 pb-1 px-2 posts-row ' . $column . '">';
        $html .= iopay_get_auto_ad_url_list($loc);
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    if($echo){
        echo $html;
    } else {
        return $html;
    }
}

function iopay_get_auto_ad_url_list($loc, $placeholder = false){
    $html='';
    $config = io_get_option('auto_ad_config');
    $lists  = $placeholder ? array() : iopay_get_valid_auto_ad_url($loc, '1', '1');
    $class  = $placeholder ? ' auto-placeholder' : '';
    $index  = 1;
    if (!empty($lists) || $placeholder) {
        foreach ($lists as $val) {
            if ($index > (int) $config['total']) {
                break;
            }
            $_n   = isset($val['nofollow']) && $val['nofollow'] ? ' nofollow' : '';
            $html .= '<div class="auto-list auto-list-' . $index . ' ' . $class . '">';
            $html .= '<a href="' . $val['url'] . '" class="d-flex btn on-border align-items-center auto-url-list px-2 py-1" target="_blank" rel="external'.$_n.'" title="' . $val['name'] . '">';
            $html .= '<div class="auto-ad-img rounded-circle overflow-hidden">';
            $html .= '<img src="' . io_letter_ico($val['name'], 21) . '" height="21" width="21">';
            $html .= '</div>';
            $html .= '<div class="auto-ad-name text-sm ml-1 ml-md-2 line1">';
            $html .= $val['name'];
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</div>';
            $index++;
        }
        if ($index <= (int) $config['total']) {
            for ($i = 0; $i <= (int) $config['total'] - $index; $i++) {
                $html .= '<div class="auto-list-null ' . $class . '">';
                $html .= '<div class="d-flex align-items-center auto-url-list px-2 py-1">';
                $html .= '<i class="iconfont icon-ad-copy text-muted"></i>';
                $html .= '<div class="auto-ad-name ml-2">';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
        }
    }else{
        $html = '<div class="auto-url-list col-a1 p-2 text-muted">'.__('欢迎入驻！','i_theme').'</div>';
    }
    return $html;
}

function iopay_get_ad_max_order(){
    global $wpdb;
    return $wpdb->get_var("SELECT MAX(`order`) FROM $wpdb->ioautoad");
}

/**
 * 获取AD的url
 * 过期的保留30天
 * 
 * @param mixed $loc home:首页 page:内页 all:同时在首页和内页 AHP:所有
 * @param mixed $status 取值范围，1：只取已支付，  all：含未支付
 * @param mixed $check 取值范围，1：只取已审核；  2：驳回；  all：含未审核，只有前台显示需要检查
 * @param mixed $is_all false：不含   true：包含 过期资源
 * @return array
 */
function iopay_get_valid_auto_ad_url($loc, $status = '1', $check = 'all', $is_all = false){
    global $wpdb;
    $where = '1';
    if ($loc != '' && $loc != 'AHP') {
        if ($loc == 'home') {
            $where .= " AND `loc` IN ('home', 'all')";
        } else if ($loc == 'page') {
            $where .= " AND `loc` IN ('page', 'all')";
        } else {
            $where .= " AND `loc` = '$loc'";
        }
    }
    if ($status != '' && $status != 'all') {
        $where .= " AND `status` = $status";
    }
    if ($check != '' && $check != 'all') {
        $where .= " AND `check` = $check";
    }
    if (!$is_all) {
        $where .= " AND `expiry` > '" . current_time('Y-m-d H:i:s') . "'";
    }
    if ($loc == '' && $status == '' && $check == '' && $is_all) {
        $where .= " AND `expiry` < '" . current_time('Y-m-d H:i:s') . "'";
    }

    //$order_txt = "ORDER BY `order` ASC";
    
    $orderby = isset($_GET['orderby']) && !empty($_GET['orderby']) ? $_GET['orderby'] : 'order';
    $order  =  isset($_GET['order']) && !empty($_GET['order']) ? $_GET['order'] : 'ASC';

    $by = array('name', 'order', 'loc', 'status', 'expiry', 'check', 'time');
    if (!in_array($orderby, $by)) {
        $orderby = 'order';
    }
    
    $order_txt = "ORDER BY `$orderby` $order";
    
    $sql   = "SELECT * FROM $wpdb->ioautoad WHERE $where $order_txt";// ORDER BY `expiry` DESC
    $lists = $wpdb->get_results($sql, ARRAY_A);
    if($lists){
        $d_ids = array();
        for ($i = 0; $i < count($lists); $i++) {
            if (strtotime($lists[$i]['expiry']) < (current_time('timestamp'))) {
                //删掉过期资源
                if ($lists[$i]['status'] == 0) {
                    $d_ids[] = $lists[$i]['id'];
                    unset($lists[$i]);
                } else {
                    if (strtotime($lists[$i]['expiry']) + MONTH_IN_SECONDS < (current_time('timestamp'))) { // 保留30天  MONTH_IN_SECONDS
                        $d_ids[] = $lists[$i]['id'];
                        unset($lists[$i]);
                    }
                }
            }
        }
        if(!empty($d_ids)){
            $wpdb->query("DELETE FROM $wpdb->ioautoad WHERE `id` IN (" . implode(',', $d_ids) . ")");
        }
        return $lists;
    }else{
        return array();
    }
}

/**
 * 所有自动广告
 * @return array
 */
function iopay_get_all_auto_ad_url(){
    return iopay_get_valid_auto_ad_url('AHP', 'all', 'all', true);
}
/**
 * 保存增加的链接
 * 占位
 * 
 * @param array $url
 * @return array|bool
 */
function iopay_add_auto_ad_url($url){
    $defaults = array(
        'status'    => 0,
        'user_id'   => 0,
        'check'     => 0,
        'url'       => '',
        'name'      => '',
        'icon'      => '',
        'color'     => '',
        'contact'   => '',
        'nofollow'  => 0,
        'loc'       => '',
        'time'      => current_time('mysql'),
        'token'     => '',
        'pay_time'  => '',
        'limit'     => '',
        'order_num' => '',
        'expiry'    => date('Y-m-d H:i:s', current_time('timestamp') + (5 * 60)), //5分钟后过期，支付成功后更新时间
        'order'     => iopay_get_ad_max_order() + 1,
    );
    if (isset($url['id'])) {
        unset($url['id']);
    }
    $url    = wp_parse_args($url, $defaults);
    $config = io_get_option('auto_ad_config');
    if ('all' == $url['loc']) {
        //先查首页
        iopay_auto_loc_is_spare('home',$config['total']);
        iopay_auto_loc_is_spare('page',$config['total']);
    }
    iopay_auto_loc_is_spare($url['loc'],$config['total']);

    global $wpdb;
    if ($wpdb->insert($wpdb->ioautoad, $url)) {
        return true;
    }
    return false;
}
/**
 * 判断是否有位置
 * 
 * @param mixed $loc
 * @param mixed $count
 * @return void
 */
function iopay_auto_loc_is_spare($loc, $count){
    $lists = iopay_get_valid_auto_ad_url($loc, 'all'); //对应位置的所有url
    if (count($lists) >= (int) $count) {
        $e = io_friend_after_date(iopay_get_latest_expiration_time($lists, $loc, 'all'));
        io_error(array('error' => 1, 'status' => 2, 'msg' => sprintf(__('抱歉，%s没有空位，%s 后有才空余，感谢等待！', 'i_theme'),iopay_get_auto_loc_name($loc), $e)));
    }
}
/**
 * 支付成功，更新状态
 * 
 * @param array $url url data
 * @return array
 */
function iopay_update_auto_ad_url($url){
    $check    = io_get_option('auto_ad_config', true, 'check') ? true : false;
    $defaults = array(
        'status'    => 1,
        'user_id'   => 0,
        'check'     => $check ? 0 : 1,
        'url'       => '',
        'name'      => '',
        'icon'      => '',
        'color'     => '',
        'contact'   => '',
        'nofollow'  => 0,
        'loc'       => '',
        'time'      => '',
        'token'     => '',
        'pay_time'  => current_time('mysql'),
        'limit'     => '',
        'order_num' => '',
        'expiry'    => '',
        'order'     => '',
    );
    $url  = wp_parse_args($url, $defaults);
    $unit = io_get_option('auto_ad_config', 'hour', 'unit');
    if($check){
        //如果开启时审核，等待30天过期
        $url['expiry'] = date('Y-m-d H:i:s', strtotime("+30day", current_time('timestamp')));
    } else {
        if ('hour' === $unit) {
            $url['expiry'] = date('Y-m-d H:i:s', current_time('timestamp') + ((int) $url['limit'] * 60 * 60));
        } else {
            $url['expiry'] = date('Y-m-d H:i:s', strtotime("+{$url['limit']}{$unit}", current_time('timestamp')));
        }
    }
    global $wpdb;
    if ($wpdb->update($wpdb->ioautoad, $url, array('token' => $url['token'], 'url' => $url['url'], 'loc' => $url['loc']))) {
        return array('error' => 0, 'reload' => 1);
    } else {
        return array('error' => 1, 'status' => 3, 'msg' => __('未知错误，请联系管理员处理。', 'i_theme'));
    }
}


/**
 * 获取最近过期日期
 * 
 * @param array $lists
 * @param string $loc
 * @param mixed $status
 * @return mixed
 */
function iopay_get_latest_expiration_time($lists, $loc, $status){
    if(empty($lists)){
        $lists = iopay_get_valid_auto_ad_url($loc, $status);
    }
    $_l = array();
    foreach ($lists as $val) {
        $_l[$val['expiry']] = $val;
    }

    ksort($_l);
    
    $_shift = array_shift($_l);
    return $_shift['expiry'];
}
/**
 * 获取时间名称
 * @param mixed $type
 * @return mixed
 */
function iopay_get_auto_unit_name($type){
    $name = array(
        'hour'  => __('小时','i_theme'),
        'day'   => __('天','i_theme'),
        'month' => __('月','i_theme'),
    );
    return $name[$type];
}
/**
 * Summary of iopay_get_auto_loc_name
 * @param mixed $type
 * @return mixed
 */
function iopay_get_auto_loc_name($type){
    $name = array(
        'home' => __('首页','i_theme'),
        'page' => __('内页','i_theme'),
        'all'  => __('所有位置','i_theme'),
    );
    return $name[$type];
}

/**
 * 获取预设时间
 * 
 * @param mixed $loc
 * @param mixed $product_default
 * @return string
 */
function iopay_get_product_lists_html($loc, &$product_default){
    $config = io_get_option('auto_ad_config');
    $unit   = iopay_get_auto_unit_name($config['unit']);
    $i      = 1;
    $_lists = '';
    if($loc == ''){
        $loc = $config['loc'][0];
    }
    foreach ($config['product'] as $val) {
        if ($i === 1) {
            $product_default = 1;
        }
        $price      = round($val['time'] * $config["price_{$loc}"], 2);
        $pay_price  = round($val['time'] * $config["price_{$loc}"] * ($val['discount'] / 100), 2);
        $price_tips = '';
        if ($price != $pay_price) {
            $price_tips = ' <div class="original-price text-xs">' . io_get_option('pay_unit', '￥') . $price . '</div>';
        }
        $_lists .= '<div class="d-flex align-items-center justify-content-center flex-column flex-wrap io-radio br-xl border-2w flex-fill position-relative' . ($i === 1 ? ' active' : '') . '" data-for="index"  data-value="' . $i . '" >';
        $_lists .= '<div class="text-md">' . $val['time'] . '<span class="text-xs">' . $unit . '</span></div>';
        $_lists .= '<div class="text-sm"><span class="text-xs">' . io_get_option('pay_unit', '￥') . '</span>' . $pay_price . $price_tips . '</div>';
        if($val['tag']){
            $_lists .= '<div class="badge vc-red p--t--r">' . $val['tag'] . '</div>';
        }
        $_lists .= '</div>';
        $i++;
    }
    return $_lists;
}
/**
 * 充值限制提示
 * @param mixed $loc_type
 * @param mixed $custom_time
 * @return float
 */
function iopay_get_custom_product_val($loc_type, $custom_time){
    $config       = io_get_option('auto_ad_config');
    $custom_limit = $config['custom_limit'];
    if (!empty($custom_limit['width']) && $custom_time < $custom_limit['width']) {
        io_tips_error(sprintf(__('至少需要充值%s','i_theme'), $custom_limit['width'].iopay_get_auto_unit_name($config['unit'])),false);
    }
    if (!empty($custom_limit['height']) && $custom_time > $custom_limit['height']) {
        io_tips_error(sprintf(__('最高充值%s','i_theme'), $custom_limit['height'].iopay_get_auto_unit_name($config['unit'])),false);
    }
    $price     = (float) $config["price_$loc_type"];
    $pay_price = round($custom_time * $price, 2);
    return $pay_price;
}
/**
 * 根据ID审核自动广告
 * @param mixed $id
 * @param mixed $is_pass 通过
 * @return void
 */
function iopay_check_auto_ad($id, $is_pass = 1){
    global $wpdb;

    $unit  = io_get_option('auto_ad_config', 'hour', 'unit');
    $current = iopay_get_auto_ad($id);

    if ($is_pass == 1) {
        if (!empty($current['limit'])) {
            if ('hour' === $unit) {
                $expiry = date('Y-m-d H:i:s', current_time('timestamp') + ((int) $current['limit'] * 60 * 60));
            } else {
                $_limit = $current['limit'];
                $expiry = date('Y-m-d H:i:s', strtotime("+{$_limit}{$unit}", current_time('timestamp')));
            }
        }else{
            $expiry = $current['expiry'];
        }
        $wpdb->update($wpdb->ioautoad, array('check' => 1, 'expiry' => $expiry), array('id' => $id));
    } else {
        $wpdb->update($wpdb->ioautoad, array('check' => 2), array('id' => $id));
    }

    //发送通知
    if ((isset($current['contact']) && !empty($current['contact'])) || !empty($current['user_id'])) {
        $title = '您在站点「'.get_bloginfo('name').'」申请的自动广告审核通过';
        if (2 == $is_pass) {
            $title          = '您在站点「' . get_bloginfo('name') . '」申请的自动广告被驳回';
            $current['msg'] = '经过审核，发现您提交的站点违反规定，未通过审核。';
        }
        $current['title'] = $title;
        $go               = !empty($current['contact']) ? $current['contact'] : '';
        if(empty($go)){
            $go = get_userdata($current['user_id'])->user_email;
        }
        io_mail($go, $title, io_template_auto_ad_url_check($current));
    }
}

/**
 * 根据ID删除自动广告
 * @param mixed $id
 * @return void
 */
function iopay_delete_auto_ad($id){
    global $wpdb;

    $current = iopay_get_auto_ad($id);

    $wpdb->query("DELETE FROM $wpdb->ioautoad WHERE `id` = {$id}");

    //发送通知
    if (((isset($current['contact']) && !empty($current['contact'])) || !empty($current['user_id'])) && $current['check'] == 0) {
        $title            = '您在站点「'.get_bloginfo('name').'」申请的自动广告被删除';
        $current['title'] = $title;
        $current['msg']   = '经过审核，发现您提交的站点违反规定，已被删除。';
        $go               = !empty($current['contact']) ? $current['contact'] : '';
        if(empty($go)){
            $go = get_userdata($current['user_id'])->user_email;
        }
        io_mail($go , $title, io_template_auto_ad_url_check($current));
    }
}

/**
 * 根据ID获取自动广告信息
 * @param mixed $id
 * @return mixed
 */
function iopay_get_auto_ad($id){
    global $wpdb;
    $sql = "SELECT * FROM $wpdb->ioautoad WHERE `id` = %d";
    $order = $wpdb->get_row($wpdb->prepare($sql, $id), ARRAY_A);
    if ($order) {
        return $order;
    } else {
        return false;
    }
}

/**
 * 更新自动广告
 * @param mixed $data
 * @return void
 */
function iopay_update_auto_ad($data){
    global $wpdb;
    $id = $data['id'];
    unset($data['id']);
    $wpdb->update($wpdb->ioautoad, $data, array('id' => $id));
}
