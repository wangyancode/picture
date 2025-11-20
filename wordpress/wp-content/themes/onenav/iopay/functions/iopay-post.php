<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-01 15:24:35
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-16 16:30:47
 * @FilePath: /onenav/iopay/functions/iopay-post.php
 * @Description: 
 */

/**
 * 获取标题
 * @param mixed $pay_mate
 * @param mixed $post
 * @param mixed $index
 * @return mixed
 */
function iopay_get_post_pay_title($buy_option = array(), $post = '', $index = 0){
    if (!$buy_option) {
        if (!$post) {
            $post_id = get_the_ID();
            $post    = get_post($post_id);
        }

        $buy_option = get_post_meta($post->ID, 'buy_option', true);
    }
    $_data = iopay_get_post_pay_price_data($post, $index);
    $_name = '';
    if (isset($_data['name'])) {
        $_name = '-' . $_data['name'];
    }
    $pay_title = !empty($buy_option['pay_title']) ? $buy_option['pay_title'] : get_the_title($post->ID);
    return $pay_title . $_name;
}

/**
 * 根据 index 重新排序资料
 * 
 * @param mixed $annex
 * @return mixed
 */
function iopay_get_annex_sort_by_index($annex){
    $data = array();
    foreach ($annex as $value) {
        if (isset($value['index'])) {
            $data[$value['index']] = $value;
        }
    }
    if (empty($data))
        return $annex;
    else
        return $data;
}

/**
 * 收银台获取商品价格
 * 
 * @param mixed $post
 * @param mixed $index
 * @return array
 */
function iopay_get_post_pay_price_data($post, $index){
    $post_id    = $post->ID;
    $buy_option = get_post_meta($post_id, 'buy_option', true);
    $buy_data   = $buy_option;
    $data       = array();

    if ($index != 0) {
        switch ($post->post_type) {
            case 'app':
                if ('multi' == $buy_option['price_type'] && 'annex' === $buy_option['buy_type']) { //附件模式且是多价格
                    $buy_data            = io_get_app_down_by_index($post_id)[$index];
                    $buy_data['io_name'] = $buy_data['app_version'];
                }
                break;
            case 'post':
            case 'sites':
                if ('multi' == $buy_option['price_type'] && 'annex' === $buy_option['buy_type']) { //附件模式且是多价格
                    $buy_data            = io_get_down_by_index($buy_option['annex_list'])[$index];
                    $buy_data['io_name'] = empty($buy_data['name']) ? __('资源', 'i_theme') . $index : $buy_data['name'];
                }
                break;
            //书籍无多价格描述
            default:
                break;
        }
    }
    $data['name']      = isset($buy_data['io_name']) ? $buy_data['io_name'] : $buy_data['pay_title'];
    $data['price']     = isset($buy_data['price']) ? round((float) $buy_data['price'], 2) : 0;
    $data['pay_price'] = isset($buy_data['pay_price']) ? round((float) $buy_data['pay_price'], 2) : 0;

    return $data;
}


/**
 * 根据序号排序资源
 * 
 * @param mixed $post_id
 * @param bool $first 取第一个值
 * @return array
 */
function io_get_down_by_index($lists, $first = false){
    $data  = array();
    foreach ($lists as $val) {
        $data[$val['index']] = $val;
    }
    if ($first) {
        return $lists[0];
    }
    return $data;
}

/**
 * 文章资源购买按钮和价格
 * 多价格模式状态判断
 * 
 * 用于文章和网址
 * 
 * @param mixed $buy_option
 * @return array
 */
function iopay_get_post_annex_buy_btn($buy_option, $class=''){
    global $post;
    $post_id   = $post->ID;
    $post_type = $post->post_type;
    $unit      = '<span class="text-xs">' . io_get_option('pay_unit', '￥') . '</span>';

    $pay_price = 0;
    $org_price = 0;
    $is_login  = false;
    $prices    = array();
    $orgs      = array();

    if ('single' == $buy_option['price_type']) {
        $pay_price = round((float) $buy_option['pay_price'], 2);
        $org_price = round((float) $buy_option['price'], 2);

        $icon     = '<i class="iconfont icon-buy_car mr-2"></i>';
        $btn_name = __('立即购买', 'i_theme');
        $url      = esc_url(add_query_arg(array('action' => 'pay_cashier_modal', 'id' => $post_id, 'index' => 0), admin_url('admin-ajax.php')));
        $btn      = apply_filters('iopay_buy_btn_before', $post_type, $buy_option, array('class' => 'position-relative '.$class));
        if(!empty($btn)){
            $is_login  = true;
            return compact('is_login', 'pay_price', 'org_price', 'btn');
        }
        $btn = '<a href="' . $url . '" class="position-relative btn ' . $class . ' vc-blue btn-shadow io-ajax-modal-get nofx no-c mb-3"  title="' . $btn_name . '">' . $icon . $btn_name . '</a>';
    } else {
        switch ($post->post_type) {
            case 'post':
            case 'sites':
                $lists = $buy_option['annex_list'];
                $pay_price = 0;
                $org_price = 0;
                // 资源列表按钮
                $btn = array();
                $_i = '<img src="' . get_theme_file_uri('/iopay/assets/img/annex.svg') . '" alt="annex" width="24" height="24">';
                foreach ($lists as $l) {
                    $order = iopay_is_buy($post_id, $l['index']);
                    if($order){
                        $l_btn = iopay_get_single_annex_down_btn($l, $order);
                        $btn[] = $l_btn;
                    } else {
                        $_pay_price = round((float) $l['pay_price'], 2);
                        $_org_price = round((float) $l['price'], 2);
                        $prices[]   = $_pay_price;
                        $orgs[]     = (empty($_org_price) ? $_pay_price : $_org_price);
                        //$pay_price += $_pay_price;
                        //$org_price += (empty($_org_price) ? $_pay_price : $_org_price);
                        $url        = esc_url(add_query_arg(array('action' => 'pay_cashier_modal', 'id' => $post_id, 'index' => $l['index']), admin_url('admin-ajax.php')));
                        $_name      = empty($l['name']) ? __('资源', 'i_theme') . $l['index'] : $l['name'];
                        $_org_price = $_org_price && $_org_price > $_pay_price ? ' <span class="original-price d-inline-block">' . $unit . $_org_price . '</span>' : '';
                        $icon       = '<i class="iconfont icon-buy_car"></i>';

                        $l_btn = '<div class="pay-list-btn d-flex align-items-center bg-muted br-xl p-2 mb-2">';
                        $l_btn .= $_i;
                        $l_btn .= '<span class="ml-1">' . $_name . '</span>';
                        $l_btn .= '<div class="ml-auto">' . $unit . '<span class="text-xl">' . $_pay_price . '</span>' . $_org_price . '</div>';
                        $l_btn .= '<a href="' . $url . '" class="btn vc-blue io-ajax-modal-get nofx no-c ml-2"  title="' . $_name . '">' . $icon . '</a>';
                        $l_btn .= '</div>';
                        $btn[] = $l_btn;
                    }
                }
                $_btn = apply_filters('iopay_buy_btn_before', $post_type, $buy_option, array('class' => 'position-relative '.$class));
                if(!empty($_btn)){
                    $is_login  = true;
                    $btn      = $_btn;
                    return compact('is_login', 'pay_price', 'org_price', 'btn');
                }
                if (!empty($prices))
                    $pay_price = min($prices);
                if (!empty($orgs))
                    $org_price = min($orgs);

                if($pay_price){
                    $btn = '<div class="position-relative buy-btn-group">' . implode($btn). '</div>';
                }
                break;

            default:
                break;
        }
    }

    return compact('is_login', 'pay_price', 'org_price', 'btn');
}
/**
 * 文章资源下载按钮
 * 
 * @param mixed $buy_option
 * @return string
 */
function iopay_get_post_annex_down_btn($buy_option, $order, $class=''){
    $is_single = 'single' == $buy_option['price_type'] ? true : false;
    if(!isset($buy_option['annex_list'])){
        return '';
    }
    $lists = $buy_option['annex_list'];

    $html = '';
    if ($is_single) {
        $order_num = isset($order['order_num']) ? $order['order_num'] : __('免费','i_theme');
        $pay_time = isset($order['pay_time']) ? $order['pay_time'] : '';
        $_order = '<div class="tips-box d-flex text-xs vc-yellow mb-3 py-3">';
        $_order .= '<span>' . __('订单号：', 'i_theme') . $order_num . '</span>';
        $_order .= '<span class="ml-auto d-none d-md-block">' . $pay_time . '</span>';
        $_order .= '</div>';
        // 资源列表按钮
        $l_btn = '';
        foreach ($lists as $l) {
            $l_btn .= '<div class="col-12 col-md-6">';
            $l_btn .= iopay_get_single_annex_down_btn($l, '', 'w-100');
            $l_btn .= '</div>';
        }
        $html .= $_order;
        $html .= '<div class="row">';
        $html .= $l_btn;
        $html .= '</div>';
    }
    return $html;
}

/**
 * 单个资源下载地址
 * 
 * @param mixed $data
 * @param mixed $class
 * @return string
 */
function iopay_get_single_annex_down_btn($data, $order='', $class=''){
    $_i    = '<img src="' . get_theme_file_uri('/iopay/assets/img/annex.svg') . '" alt="annex" width="24" height="24">';
    $url   = $data['link'];
    $_name = empty($data['name']) ? __('资源', 'i_theme') . $data['index'] : $data['name'];
    $icon  = '<i class="iconfont icon-down"></i>';

    $_order = '';
    if($order){
        $_order = '<div class="d-flex flex-fill text-xs order-info mb-2">';
        $_order .= '<span>' . __('订单号：', 'i_theme') . $order['order_num'] . '</span>';
        //$_order .= '<span class="ml-auto d-none d-md-block">' . $order['pay_time'] . '</span>';
        $_order .= '</div>';
    }

    $_info = !empty($data['info']) ? $data['info'] : __('无', 'i_theme');
    $_info = '<div class="text-xs mt-2 text-muted"><i class="iconfont icon-tishi"></i> '.$_info.'</div>';

    $btn = '<div class="pay-list-btn d-flex flex-column bg-muted br-xl p-2 mb-2 ' . $class . '">';
    $btn .= $_order;
    $btn .= '<div class="d-flex align-items-center flex-fill">';
    $btn .= $_i;
    $btn .= '<span class="ml-1">' . $_name . '</span>';
    $btn .= '<a href="' . $url . '" class="ml-auto btn vc-yellow nofx no-c ml-2" target="_blank" rel="external noopener nofollow" title="' . $_name . '">' . $icon . '</a>';
    $btn .= '</div>';
    $btn .= $_info;
    $btn .= '</div>';

    return $btn;
}
/**
 * 文章内容中附件购买框
 * 单价格模式
 * 多价格模式，需判断单个附件状态
 * 
 * @param mixed $post_type
 * @param mixed $echo
 * @return mixed
 */
function iopay_get_post_pay_body_html($post_type, $echo = true){
    if (!in_array($post_type, array('post', 'sites'))) {
        return '';
    }
    global $post;
    $post_id  = $post->ID;
    $buy_data = get_post_meta($post_id, 'buy_option', true);
    if (!$buy_data || !isset($buy_data['buy_type']) || 'annex' !== $buy_data['buy_type']) {
        return '';
    }
    $btn_data = iopay_get_post_annex_buy_btn($buy_data);
    if (!$btn_data['is_login'] && empty($btn_data['pay_price'])) {
        //说明商品都已经购买
        $html = iopay_get_post_down_body_html($post_type, $btn_data, false);
        if ($echo) {
            echo $html;
        } else {
            return $html;
        }
    }
    $title     = __('付费资源', 'i_theme');
    $tips      = __('此内容已隐藏，请购买后查看！', 'i_theme');
    $meta      = '';
    $org       = '';
    $tag       = '';
    if ((float) $btn_data['pay_price'] < (float) $btn_data['org_price']) {
        $org = '<span class="original-price text-sm"><span class="text-xs">' . io_get_option('pay_unit', '￥') . '</span>' . $btn_data['org_price'] . '</span>';
        $tag = '<span class="badge vc-red"><i class="iconfont icon-time-o mr-2"></i>' . __('限时特惠', 'i_theme') . '</span>';
    }
    $meta .= '<div class="text-32"><span class="text-xs text-danger">' . io_get_option('pay_unit', '￥') . '</span><span class="text-danger font-weight-bold">' . $btn_data['pay_price'] . '</span> ' . $org . '</div>';
    $tips_b    = iopay_pay_tips_box('end');
    $name      = $post->post_title;
    $thumbnail = io_get_post_thumbnail($post);
    $btn       = ('single' == $buy_data['price_type'] || $btn_data['is_login']) ? '<div class=" text-right">'.$btn_data['btn'].'</div>' : $btn_data['btn'];


    $html = '<div class="user-level-box post-content position-relative my-4">';

    $html .= '<div class="user-level-body br-xl shadow p-3">';
    $html .= '<h3 class="text-lg flex-fill item-title"><i class="iconfont icon-version mr-2"></i><span>' . $title . '</span></h3>';
    $html .= '<div class="d-flex">';
    $html .= '<div class="card-thumbnail img-type-' . $post_type . ' mr-2 mr-md-3 d-none d-md-block">';
    $html .= '<div class="h-100 img-box">';
    $html .= '<img src="' . $thumbnail . '" alt="' . $name . '">';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="d-flex flex-fill flex-column">';
    $html .= '<div class="list-body">';
    $html .= '<span class="text-lg mr-2">'.__('隐藏内容','i_theme').'</span>' . $tag;
    $html .= '<div class="mt-2 text-xs text-muted"><i class="iconfont icon-tishi mr-1"></i>' . $tips . '</div>';
    $html .= $meta;
    $html .= '</div>';
    $html .= $btn;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= $tips_b;
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 附件下载框
 * 
 * @param mixed $post_type
 * @param mixed $order     单订单信息 或者 多附件下载列表
 * @param mixed $echo
 * @return mixed
 */
function iopay_get_post_down_body_html($post_type, $order, $echo = true){
    if (!in_array($post_type, array('post', 'sites'))) {
        return '';
    }
    if(isset($order['pay_price']) && isset($order['btn'])){
        $l_btn = '';
        foreach ($order['btn'] as $b) {
            $l_btn .= '<div class="col-12 col-md-6">'.$b.'</div>';
        }
        $btn_data = '<div class="row">'.$l_btn.'</div>';
    } else {
        global $post;
        $post_id  = $post->ID;
        $buy_data = get_post_meta($post_id, 'buy_option', true);
        if (!$buy_data || !isset($buy_data['buy_type']) || 'annex' !== $buy_data['buy_type']) {
            return '';
        }
        $btn_data = iopay_get_post_annex_down_btn($buy_data, $order);
    }
    $title    = __('已购买', 'i_theme');
    $tips_b   = iopay_pay_tips_box('end');


    $html = '<div class="user-level-box post-content position-relative my-4">';

    $html .= '<div class="user-level-body is-buy br-xl shadow p-3">';
    $html .= '<h3 class="text-lg flex-fill"><i class="iconfont icon-adopt mr-2"></i><span>' . $title . '</span></h3>';
    $html .= '<div class="d-flex flex-fill flex-column">';
    $html .= $btn_data;
    $html .= '</div>';
    $html .= '</div>';

    $html .= $tips_b;
    $html .= '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 获取文章商品购买BOX
 * 
 * @param mixed $post_type
 * @return void
 */
function iopay_get_post_commodity_box_html($post_type){
    if (!in_array($post_type, array('post', 'sites'))) {
        return;
    }
    global $post, $buy_data;
    $post_id  = $post->ID;
    $buy_data = get_post_meta($post_id, 'buy_option', true);
    if (!$buy_data || !isset($buy_data['buy_type']) || 'annex' !== $buy_data['buy_type']) {
        return;
    }

    
    if(!$buy_data || 'annex' !== $buy_data['buy_type']){
        return;
    }
    $html = '';
    switch ($buy_data['price_type']) {
        case 'multi':
            $html = iopay_get_post_pay_body_html($post_type,false);
            break;

        default:
            $order = iopay_is_buy($post_id);
            if($order){
                $html = iopay_get_post_down_body_html($post_type, $order, false);
            } else{
                $html = iopay_get_post_pay_body_html($post_type,false);
            }
            break;
    }
    echo '<div id="posts_pay_box">'.$html.'</div>';
}
add_action('io_single_' . io_get_option('pay_box_loc', 'before'), 'iopay_get_post_commodity_box_html');

/**
 * 获取下载附件列表
 * 
 * @param mixed $post_id
 * @param mixed $post_type
 * @return array
 */
function iopay_get_post_down_list($post_id, $post_type){
    return array();
}


/**
 * 获取购买按钮
 * 
 * @param mixed $type
 * @param mixed $buy_data
 * @param mixed $args
 * @return mixed
 */
function iopay_get_pay_btn($type, $buy_data, $args){
    $defaults = array(
        'tag'   => 'a',
        'class' => '',
        'text'  => __('登录购买', 'i_theme'),
        'icon'  => '<i class="iconfont icon-user-circle mr-2" aria-hidden="true"></i>',
        'price' => '',
    );
    $args = wp_parse_args($args, $defaults);

    $user_id = get_current_user_id();
    $btn     = '';

    //免登陆购买
    $pay_limit = !empty($buy_data['limit']) ? $buy_data['limit'] : 'all';
    if (!$user_id) {
        if (!io_get_option('pay_no_login', true) || 'all' != $pay_limit) {
            $btn = '<' . $args['tag'] . ' href="javascript:;" class="btn vc-blue login-btn-action ' . $args['class'] . ' nofx no-c">' . $args['icon'] . $args['text'] . $args['price'] . '</' . $args['tag'] . '>';
        }
    }

    return $btn;
}
add_action('iopay_buy_btn_before', 'iopay_get_pay_btn', 10, 3);
