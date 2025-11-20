<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-02-25 22:59:25
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 15:36:42
 * @FilePath: /onenav/iopay/functions/iopay-ajax.php
 * @Description: 
 */

/**
 * 商品支付模态框
 * 
 * @param mixed $id 文章ID
 * @param mixed $index 商品序号
 * @return string|null
 */
function iopay_pay_cashier_modal($post_id = 0, $index = 0){
    if(!$post_id){
        return;
    }
    $user_id = get_current_user_id();
    $post    = get_post($post_id);

    $buy_option      = get_post_meta($post_id, 'buy_option', true);
    $buy_type        = $buy_option['buy_type'];
    $order_type_name = iopay_get_buy_type_name($buy_type , true);

    $pay_title       = iopay_get_post_pay_title($buy_option, $post, $index);

    $order_name      = get_bloginfo('name') . '-' . iopay_get_buy_type_name($buy_type);

    $unit      = io_get_option('pay_unit','￥');
    $pay_limit = !empty($buy_option['limit']) ? $buy_option['limit'] : 'all';

    //价格
    $_price = iopay_get_post_pay_price_data($post, $index);
    if(isset($_price['name'])){
        $order_name = $order_name . '-' . $_price['name'];
    }
    $original_price = $_price['price'];
    $pay_price      = $_price['pay_price'];
    $original_price = $original_price && $original_price > $pay_price ? '<div class="original-price d-inline-block text-sm">'.__('原价','i_theme').' <span class="text-xs">' . $unit . '</span><span>' . $original_price . '</span></div>' : '';

    $pay_name = '价格';
    $price = '<div class="mb-3 muted-box text-center order-type-' . $buy_type . '">';
    $price .= '<div class="text-ss tips-top-l"><i class="iconfont icon-jinbi mr-1"></i>' . $pay_name . '</div>';
    if($pay_limit == 'all'){
        $price .= '<div class="text-danger"><span class="text-md">' . $unit . '</span><span class="text-32 text-height-xs">' . $pay_price . '</span></div>';
        $price .= $original_price;
    }
    $price .= '</div>';

    $form = '<form>';
    $form .= '<input type="hidden" name="post_id" value="' . $post_id . '">';
    $form .= '<input type="hidden" name="index" value="' . $index . '">';
    $form .= '<input type="hidden" name="order_type" value="' . $buy_type . '">';
    $form .= '<input type="hidden" name="order_name" value="' . $order_name . '">';
    $form .= iopay_get_pay_input($buy_type, $pay_price);
    $form .= '</form>';

    $tips = io_get_option('pay_tips_multi', '');
    if ($tips) {
        $tips = '<div class="bg-muted text-muted br-bottom-inherit text-xs text-center p-2">'.$tips.'</div>';
    }

    $html = io_get_modal_header('', 'icon-buy_car');
    $html .= '<div class="modal-body blur-bg">';
    $html .= '<div class="p-3">';
    $html .= "<div class='d-flex align-items-center justify-content-center flex-wrap mb-3'><span class='tips-box vc-red text-xs px-1 py-0 mr-2'>$order_type_name</span>$pay_title</div>";
    $html .= $price . $form;
    $html .= '</div>';
    $html .= $tips;
    $html .= '</div>';

    return $html;
}


/**
 * 新订单信息数据
 * @param mixed $post_data
 * @return array
 */
function iopay_add_order_data($post_data = array()){
    $order_type = !empty($post_data['order_type']) ? $post_data['order_type'] : 0;
    $pay_method = !empty($post_data['pay_method']) ? $post_data['pay_method'] : '';
    $post_id    = !empty($post_data['post_id']) ? (int) $post_data['post_id'] : 0;
    $user_id    = get_current_user_id();

    $_data = array(
        'user_id'     => $user_id,
        'order_type'  => $order_type,
        'post_id'     => $post_id,
        'merch_index' => !empty($post_data['index']) ? (int) $post_data['index'] : 0,
        'order_meta'  => '',
    );

    switch ($order_type) {
        case 'auto_ad_url':
            $product_id  = $_data['merch_index'];
            $loc_type    = !empty($post_data['loc']) ? $post_data['loc'] : 'home';
            $custom_time = !empty($post_data['custom_time']) ? (int)$post_data['custom_time'] : 0;
            $ad_data = array(
                'user_id' => $user_id,
                'url'     => $post_data['url'],
                'name'    => $post_data['url_name'],
                'contact' => isset($post_data['contact']) ? $post_data['contact'] : '',
                'loc'     => $loc_type,
                'time'    => current_time('mysql'),
                'limit'   => $custom_time,
                'token'   => str_shuffle(time()) . mt_rand(100, 999),
                
            );
            if (empty($ad_data['url']) || empty($ad_data['name'])) {
                io_tips_error(__('请填写您的的网站！','i_theme'));
            }
            if(strlen($ad_data['name']) > 20){
                io_tips_error(__('名称不能多于20个字！','i_theme'));
            }
            if(!io_is_url($ad_data['url'])){
                io_tips_error(__('url格式有误！','i_theme'));
            }
            if(isset($post_data['contact']) && (empty($post_data['contact']) || !filter_var($post_data['contact'], FILTER_VALIDATE_EMAIL))){
                io_tips_error(__('联系邮箱错误！','i_theme'));
            }
            $config = io_get_option('auto_ad_config');
            $price  = (float) $config["price_$loc_type"];
            if ($product_id == 0) {
                $custom_limit = $config['custom_limit'];
                if (!empty($custom_limit['width']) && $custom_time < $custom_limit['width']) {
                    io_tips_error(sprintf(__('至少需要充值%s','i_theme'), $custom_limit['width'].iopay_get_auto_unit_name($config['unit'])));
                }
                if (!empty($custom_limit['height']) && $custom_time > $custom_limit['height']) {
                    io_tips_error(sprintf(__('最高充值%s','i_theme'), $custom_limit['height'].iopay_get_auto_unit_name($config['unit'])));
                }
                $_data['order_price'] = round($custom_time * $price, 2);
            } else {
                $product              = $config['product'][$product_id-1];
                $_time                = (float)$product['time'];
                $_discount            = (float)$product['discount'];
                $pay_price            = round(($price * $_time) * ($_discount / 100), 2);
                $ad_data['limit']     = $_time;
                $_data['order_price'] = $pay_price;
            }
            iopay_add_auto_ad_url($ad_data);
            $_data['order_meta'] = maybe_serialize($ad_data);
            break;
        case 'pay_publish':
            $post_type = __post('post_type');
            if (!$post_id || !$post_type) {
                io_tips_error(__('数据获取错误!','i_theme'));
            }
            $post_status = get_post_status($post_id);
            if ('publish' == $post_status) {
                io_tips_error(__('文章已发布', 'i_theme'));
            }
            $option = io_get_option($post_type . '_tg_config', array(), 'pay');
            $_data['order_price'] = iopay_get_pay_publish_prices($option['prices'], $post_id)[0];
            break;
        default:
            if (!$post_id) {
                io_tips_error(__('数据获取错误!','i_theme'));
            }
            $post = get_post($post_id);
            if (empty($post->post_author)) {
                io_tips_error(__('数据获取错误!','i_theme'));
            }
            if (!$user_id && !io_get_option('pay_no_login', true)) {
                io_tips_error(__('请先登录!','i_theme'));
            }
            $_data['post_user_id'] = $post->post_author;
            $_price = iopay_get_post_pay_price_data($post, $_data['merch_index']);
            $_data['order_price'] = $_price['pay_price'];
        }

    if (!$_data['order_price']) {
        io_tips_error(__('订单金额异常！','i_theme'));
    }

    $pay_meta = array('pay_method' => $pay_method, $pay_method => $_data['order_price']);
    $_data['pay_meta'] = maybe_serialize($pay_meta);
    return $_data;
}


/**
 * 支付
 * @param mixed $order_data
 * @return array
 */
function iopay_initiate_pay($order_data){
    $defaults = array(
        'id'          => 0,
        'user_id'     => 0,
        'pay_method'  => '',
        'order_num'   => '',
        'order_price' => 0,
        'ip_address'  => '',
        'order_name'  => get_bloginfo('name') . __('支付','i_theme'),
        'return_url'  => home_url(),
    );
    $order_data = wp_parse_args($order_data, $defaults);

    if (empty($order_data['order_num'])) {
        return array('error' => 1, 'msg' => __('订单创建失败','i_theme'));
    }

    //价格为0，直接付款
    if (!$order_data['order_price']) {
        $pay = array(
            'order_num' => $order_data['order_num'],
            'pay_type'  => '',
            'pay_price' => 0,
            'pay_num'   => $order_data['order_num'],
            'other'     => '',
        );
        iopay_confirm_pay($order_data['order_num'], $pay);

        return array('error' => 0, 'status' => 1, 'msg' => __('支付成功','i_theme'), 'return_url' => $order_data['return_url']);
    }

    $pay_sdk = '';
    switch ($order_data['pay_method']) {
        case 'wechat':
            $pay_sdk = io_get_option('pay_wechat_sdk');
            break;

        case 'alipay':
            $pay_sdk = io_get_option('pay_alipay_sdk');
            break;
            
        default:
            $pay_sdk = $order_data['pay_method'];
            break;
    }

    $pay = iopay_initiate_pay_to_sdk( $pay_sdk, $order_data );

    $pay = array_merge($order_data, $pay);
    return $pay;
}

function iopay_pay_auto_ad_modal($loc){
    $config          = io_get_option('auto_ad_config');
    $buy_type        = 'auto_ad_url';
    $unit            = iopay_get_auto_unit_name($config['unit']);

    $product_default = '';
    $product_lists = '';
    if (!empty($config['product'])) {
        $_lists  = iopay_get_product_lists_html($loc, $product_default);
        $_custom = '';
        if($config['custom']){
            $_url = add_query_arg(array('action' => 'get_auto_ad_url_custom_product_val'), admin_url('admin-ajax.php'));
            $_custom .= '<div class="text-sm text-muted mb-2">'.sprintf(__('自定义时间(%s)','i_theme'),$unit).'</div>';
            $_custom .= '<div class="" data-for="index"  data-value="0" >';
            $_custom .= '<div class="form-group position-relative m-0">';
            $_custom .= '<input type="number" name="custom_time" size="5" class="form-control get-ajax-custom-product-val" data-href="'.$_url.'" data-target=".ajax-custom-product-val">';
            $_custom .= '<div class="tips-box vc-yellow py-0 ajax-custom-product-val text-sm p-l"><i class="iconfont icon-point"></i></div>';
            $_custom .= '</div>';
            $_custom .= '<small class="form-text text-muted text-left mb-3"><i class="iconfont icon-tishi mr-1"></i>'. sprintf(__('最少%s，最多%s','i_theme'),$config['custom_limit']['width']. $unit,$config['custom_limit']['height']. $unit).'</small>';
            $_custom .= '</div>';
        }
        $product_lists .= '<div for-group="index">';
        $product_lists .= '<div class="text-sm text-muted mb-2">'.__('选择时间','i_theme').'</div>';
        $product_lists .= '<div class="d-flex mb-2 ad-product-lists position-relative">'.$_lists.'</div>';
        $product_lists .= $_custom;
        $product_lists .= '</div>';
    }

    $loc_default = $loc;
    $loc_lists   = '';
    $ii          = 1;
    foreach($config['loc'] as $val){
        $_url = add_query_arg(array('action' => 'get_auto_ad_url_product_lists','loc' => $val), admin_url('admin-ajax.php'));
        $loc_lists .= '<div class="btn vc-l-blue btn-sm' . ($loc_default == $val ? ' active' : '') . ' io-ajax-price-get" data-href="' . $_url . '" data-target=".ad-product-lists" data-for="loc"  data-value="' . $val . '" ' . ($loc_default == $val ? 'disabled="disabled"' : '') . '>';
        $loc_lists .= iopay_get_auto_loc_name($val);
        $loc_lists .= '</div>';
        $ii++;
    }
    if($ii>2){
        $_url = add_query_arg(array('action' => 'get_auto_ad_url_product_lists','loc' => 'all'), admin_url('admin-ajax.php'));
        $loc_lists .= '<div class="btn vc-l-blue btn-sm' . ($loc_default == 'all' ? ' active' : '') . ' io-ajax-price-get" data-href="'.$_url.'" data-target=".ad-product-lists" data-for="loc"  data-value="all" ' . ($loc_default == 'all' ? 'disabled="disabled"' : '') . '>';
        $loc_lists .= __('所有','i_theme');
        $loc_lists .= '</div>';
    }else{
        $loc_lists   = '';
    }
    if($loc_lists){
        $loc_lists = '<div class="text-sm text-muted mb-2">'.__('入驻位置','i_theme').'</div><div class="btn-group w-100 mb-2" role="group">'.$loc_lists.'</div>';
    }

    $order_name = get_bloginfo('name') . '-' . __("入驻广告",'i_theme');

    //审核提示，联系方式
    $check_tips = '';
    $contact    = '';
    if(isset($config['check']) && $config['check']){
        if (!is_user_logged_in()) {
            //如果未登录，则添加联系方式
            $contact = '<input type="email" name="contact" class="form-control mt-2" placeholder="' . __('联系邮箱', 'i_theme') . '" value="">';
        }
        $check_tips = '<div class="tips-box vc-l-red btn-block text-xs text-center p-1 mt-3">'._iol($config['check_tips_multi'],'check_tips_multi').'</div>';
    }

    $form = '<form>';
    $form .= $loc_lists;
    $form .= '<input type="hidden" name="post_id" value="0">';
    $form .= '<input type="hidden" name="index" value="' . $product_default . '">';
    $form .= '<input type="hidden" name="loc" value="' . $loc_default . '">';
    $form .= '<input type="hidden" name="order_type" value="' . $buy_type . '">';
    $form .= '<input type="hidden" name="order_name" value="'.$order_name.'">';
    $form .= $product_lists;
    $form .= '<div class="text-sm text-muted mb-2">'.__('链接参数','i_theme').'</div>';
    $form .= '<div class="mb-3">';
    $form .= '<input type="text" name="url_name" class="form-control mb-2" placeholder="'.__('请输名称','i_theme').'" maxlength="15" value="">';
    $form .= '<input type="url" name="url" class="form-control" placeholder="'.__('请输URL','i_theme').'" value="">';
    $form .= $contact;
    $form .= '</div>';
    $form .= iopay_get_pay_input($buy_type);
    $form .= '</form>';

    $tips = _iol($config['tips_multi'],'tips_multi');
    if ($tips) {
        $tips = '<div class="bg-muted text-muted text-xs text-center p-2">'.$tips.'</div>';
    }

    $html = io_get_modal_header_simple('', 'icon-ad-copy', __('加入队列','i_theme'));
    $html .= '<div class="p-3">';
    $html .= $form;
    $html .= $check_tips;
    $html .= '</div>';
    $html .= $tips;

    return $html;
}