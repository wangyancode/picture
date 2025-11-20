<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-09 21:11:15
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-11 00:18:40
 * @FilePath: /onenav/inc/functions/functions.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$functions = array(
    'io-theme',
    'io-tool',
    'io-cap',
    'io-visibility',
    'io-admin-visibility',
    'io-title',
    'io-user',
    'io-header',
    'io-home',
    'io-check-link',
    'io-widget-tab',
    'io-widgets',
    'io-login',
    'io-admin',
    'io-tools-hotcontent',
    'io-post',
    'io-single-post',
    'io-single-site',
    'io-single-app',
    'io-single-book',
    'io-letter-ico',
    'io-footer',
    'io-oauth',
    'io-meta',
    'io-search',
    'io-cards',
    'io-die',
    'io-bulletin',
    'io-links',
    'io-hot-api',
    'page/io-contribute',
    'page/io-taxonomy',
    'page/io-rankings',
    'page/io-author',
    'uc/index',
);

foreach ($functions as $function) {
    $path = 'inc/functions/' . $function . '.php';
    require get_theme_file_path($path);
}


/**
 * 获取模块名称
 * @param string $type
 * @return string
 */
function io_custom_module_list($type = ''){
    $types= array(
        'search'  => '搜索模块',
        'content' => '内容列表',
        'tools'   => '小工具',
        'custom'  => '自定义模块',
    );
    return $type ? $types[$type] : $types;
}

/**
 * 获取分类排序规则
 * @param string $_order
 * @return array
 */
function get_term_order_args($_order, $sort = 'DESC'){
    switch ($_order) {
        // 浏览量
        case 'views':
            $args = array(
                'meta_key' => 'views',
                'orderby'  => array('meta_value_num' => $sort, 'date' => 'DESC'),
            );
            break;
        // 点赞数
        case '_like_count':
        case 'like':
            $args = array(
                'meta_key' => '_like_count',
                'orderby'  => array('meta_value_num' => $sort, 'date' => 'DESC'),
            );
            break;
        // 收藏数
        case '_star_count':
        case 'star':
            $args = array(
                'meta_key' => '_star_count',
                'orderby'  => array('meta_value_num' => $sort, 'date' => 'DESC'),
            );
            break;
        // 评论数·
        case 'comment_count':
        case 'comment':
            $args = array(
                'orderby' => 'comment_count',
                'order'   => $sort,
            );
            break;
        // 自定义排序
        case '_sites_order':
            if (io_get_option('sites_sortable', false)) {
                $args = array(
                    'orderby' => array('menu_order' => 'ASC', 'ID' => 'DESC'),
                );
            } else {
                $args = array(
                    'meta_key' => '_sites_order',
                    'orderby'  => array('meta_value_num' => 'DESC', 'date' => 'DESC'),
                );
            }
            break;
        // 下载量
        case 'down':
        case '_down_count':
            $args = array(
                'meta_key' => '_down_count',
                'orderby'  => array('meta_value_num' => $sort, 'date' => 'DESC'),
            );
            break;
        // ID
        case 'ID':
            $args = array(
                'orderby' => $_order,
                'order'   => $sort,
            );
            break;
        // 随机
        case 'random':
        case 'rand':
            $args = array(
                'orderby' => 'rand',
            );
            break;
        // 最近添加
        case 'date':
            $args = array(
                'orderby' => 'date',
                'order'   => $sort,
            );
            break;
        // 最近更新
        case 'modified':
            $args = array(
                'orderby' => 'modified',
                'order'   => $sort,
            );
            break;
        // 字母排序
        case 'title':
            $args = array(
                'orderby' => 'title',
                'order'   => $sort,
            );
            break;
            
        default:
            $args = array(
                'orderby' => array($_order => $sort, 'ID' => 'DESC'),
            );
            break;
    }
    return apply_filters('io_term_order_args_filters', $args, $_order);
}

/**
 * 排序
 * @description: 
 * @param string $db 数据库
 * @param object $origins 源数据
 * @param array $data 排序数据
 * @param string $origin_key 排序数据源key
 * @param string $order_key 数据库排序字段
 * @param string $where_key 判断条件
 * @return array
 */
function io_update_obj_order($db,$origins,$data,$origin_key,$order_key,$where_key='id'){
    $results = array(
        'status' => 0,
        'msg'    => '',
    );
    if (!is_array($origins) || count($origins) < 1){
        $results['msg'] = __('数据错误！','i_theme');
        return $results; 
    }
    //创建ID列表
    $objects_ids    = array();
    foreach($origins as $origin)
    {
        $objects_ids[] = (int)$origin->id;   
    }
    $index = 0;
    for($i = 0; $i < count($origins); $i++){
        if(!isset($objects_ids[$i]))
            break;
            
        $objects_ids[$i] = (int)$data[$origin_key][$index];//替换列表id为排序id
        $index++;
    }
    global $wpdb;
    //更新数据库中的菜单顺序
    foreach( $objects_ids as $order => $id ) 
    {
        $update = array(
            $order_key => $order
        );
        $wpdb->update( $db , $update, array($where_key => $id) ); 
    } 
    $results = array(
        'status' => 1,
        'msg'    => __('排序成功！','i_theme'),
    );
    return $results;
}
/**
 * 搜索
 * @return string
 */
function search_results(){   
    global $wp_query;    
    return get_search_query() . '<i class="text-danger px-1">•</i>' . sprintf(__('找到 %s 个相关内容', 'i_theme'), $wp_query->found_posts);
}

/**
 * 发送验证码
 * @param mixed $to 邮箱或者电话号码
 * @param string $type 类型
 * @return mixed
 */
function io_send_captcha($to, $type = 'email'){
    if(!session_id()) session_start();
    if (!empty($_SESSION['code_time'])) {
        $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['code_time']);
        if ($time_x < 60) {
            //剩余时间
            return array('status' => 2, 'msg' => (60 - $time_x) . '秒后可重新发送');
        }
    }
    $code = io_get_captcha();
    
    $_SESSION['reg_mail_token'] = $code;
    $_SESSION['new_mail']       = $to;
    $_SESSION['code_time']      = current_time('mysql');
    session_write_close();

    switch ($type) {
        case 'email':
            $result = io_mail($to, sprintf(__('「%s」邮箱验证码', 'i_theme'), get_bloginfo('name')), io_template_verification_code(array('date' => date("Y-m-d H:i:s", current_time('timestamp')), 'code' => $code)));
            if (is_array($result)) {
                return array('status' => 3, 'msg' => $result['msg']);
            } elseif ($result) {
                return array('status' => 1, 'msg' => __('发送成功，请前往邮箱查看！', 'i_theme'));
            } else {
                return array('status' => 3, 'msg' => __('发送验证码失败，请稍后再尝试。', 'i_theme'));
            }
        case 'phone':
            $result = IOSMS::send($to, $code);
            if (!empty($result['result'])) {
                $result['error'] = 0;
                $result['msg'] = __('短信已发送', 'i_theme');
            }
            $ret = array('status' => 1, 'msg' => $result['msg']);
            if ($result['error'] == 1) {
                $ret['status'] = 3;
            }
            return $ret;
    }
}

/**
 * 验证码判断
 * @param mixed $type
 * @param mixed $to
 * @param mixed $code_name
 * @return bool
 */
function io_ajax_is_captcha($type, $to = '', $code_name = 'verification_code'){
    if (empty($to)) {
        io_error('{"status":2,"msg":"'.__('参数错误!','i_theme').'"}'); 
    } 
    $name = array(
        'email' => __('邮箱', 'i_theme'),
        'phone' => __('手机号', 'i_theme'),
    );
    if (empty($_REQUEST[$code_name])) {
        io_error('{"status":2,"msg":"'.sprintf(__('请输入%s验证码','i_theme'),$name[$type]).'"}'); 
    } 
    $is_captcha = io_is_captcha($type, $to, $_REQUEST[$code_name]);
    if ($is_captcha['error']) {
        io_error('{"status":3,"msg":"' . $is_captcha['msg'] . '"}');
    }

    return true;
}
/**
 * 获取二维码图片url
 * @param mixed $data
 * @param mixed $size
 * @param mixed $margin
 * @return string
 */
function get_qr_url($data, $size, $margin = 10){
    if (io_get_option('qr_api','local') === 'local') {
        //$cache_key = 'qr'.io_md5($data);
        //if(!get_transient($cache_key)){
        //    $_d = array(
        //        'u' => $data,
        //        's' => $size,
        //        'm' => $margin,
        //    );
        //    set_transient($cache_key, maybe_serialize($_d),YEAR_IN_SECONDS);
        //}
        //return esc_url(home_url()."/qr/{$cache_key}.png");
        return esc_url(home_url()."/qr/?text={$data}&size={$size}&margin={$margin}");
    } else {
        return str_ireplace(array('$size', '$url'), array($size, $data), io_get_option('qr_url',''));
    }
}

/**
 * 16位 md5
 * 
 * @param mixed $data
 * @return string
 */
function io_md5($data){
    $hash = md5($data);
    $short_hash = substr($hash, 8, 16);
    return $short_hash;
}

/**
 * 获取验证码input
 * @param mixed $id
 * @return mixed
 */
function get_captcha_input_html($id = '', $class = 'form-control'){
    if(!LOGIN_007) return true;
    $captcha_type = io_get_option('captcha_type','null');
    $input = '';
    switch ($captcha_type) {
        case 'image':
            $input = '<div class="image-captcha-group'.( in_array($id,array('io_submit_link','ajax_comment'))?'':' mb-2').'">';
            $input .= '<input captcha-type="image" type="text" size="6" name="image_captcha" class="'.$class.'" placeholder="'.__('图形验证码','i_theme').'" autocomplete="off">';
            $input .= '<input type="hidden" name="image_id" value="' . $id . '">';
            $input .= '<span class="image-captcha" data-id="' . $id . '" data-toggle="tooltip" title="'.__('点击刷新','i_theme').'"></span>';
            $input .= '</div>';
            break;
        case 'slider':
            $input = '<input captcha-type="slider" type="hidden" name="captcha_type" value="slider" slider-id="">';
            break;
        case 'tcaptcha':
            $option = io_get_option('tcaptcha_option');
            if (!empty($option['appid']) && !empty($option['secret_key'])) {
                $input = '<input captcha-type="tcaptcha" type="hidden" name="captcha_type" value="tcaptcha" data-appid="' . $option['appid'] . '" data-isfree="'.(empty($option['api_secret_id'])?'true':'false').'">';
            }
            break;
        case 'geetest':
            $option = io_get_option('geetest_option');
            if (!empty($option['id']) && !empty($option['key'])) {
                $input = '<input captcha-type="geetest" type="hidden" name="captcha_type" value="geetest" data-appid="' . $option['id'] . '">';
            }
            break;
        case 'vaptcha':
            $option = io_get_option('vaptcha_option');
            if (!empty($option['id']) && !empty($option['key'])) {
                $input = '<input captcha-type="vaptcha" type="hidden" name="captcha_type" value="vaptcha" data-appid="' . $option['id'] . '" data-scene="' . (char_to_num($id)%5) . '">';
            }
            break;
    }
    io_add_captcha_js_html($captcha_type);
    return $input;
}
function io_add_captcha_js_html($status = ''){
    $status = $status ?: io_get_option('captcha_type', 'null');
    if ($status != 'null') {
        add_captcha_js();
    }
}

/**
 * 验证码是否有效
 * @param mixed $type
 * @param mixed $to
 * @param mixed $code
 * @return array
 */
function io_is_captcha($type, $to, $code){
    $name = array(
        'email' => __('邮箱', 'i_theme'),
        'phone' => __('手机号', 'i_theme'),
    );
    if(!session_id()) session_start(); 

    if (empty($_SESSION['reg_mail_token']) || $_SESSION['reg_mail_token'] != $code || empty($_SESSION['new_mail']) || $_SESSION['new_mail'] != $to) {
        return array('error' => 1, 'msg' => sprintf( __('%s验证码错误！', 'i_theme'),$name[$type]));
    } else {
        if (!empty($_SESSION['code_time'])) {
            $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['code_time']);
            if ($time_x > 1800) {//30分钟有效
                return array('error' => 1, 'msg' => sprintf( __('%s验证码已过期', 'i_theme'),$name[$type]));
            }
        }
        return array('error' => 0, 'msg' => sprintf( __('%s验证码效验成功', 'i_theme'),$name[$type]));
    }
}
/**
 * 删除验证码
 * @return void
 */
function io_remove_captcha(){
    if(!session_id()) session_start(); 
    unset($_SESSION['new_mail']);
    unset($_SESSION['reg_mail_token']);
    unset($_SESSION['code_time']);
}
/**
 * 人机验证
 * @param mixed $id
 * @return bool
 */
function io_ajax_is_robots($id=''){
    if(!LOGIN_007) return true;
    $captcha_type = io_get_option('captcha_type','null');
    switch ($captcha_type) {
        case 'image':
            $id = isset($_REQUEST['image_id']) ? esc_sql($_REQUEST['image_id']) : '';
            $id = $id ?: (!empty($_REQUEST['action']) ? $_REQUEST['action'] : 'code');
            if(!session_id()) session_start();
            if (empty($_REQUEST['image_captcha']) || strlen($_REQUEST['image_captcha']) < 4) {
                echo (json_encode(array('status' => 2, 'msg' => '请输入图形验证码')));
                exit();
            }
            if (empty($_SESSION['captcha_img_code_' . $id]) || empty($_SESSION['captcha_img_time_' . $id])) {
                echo (json_encode(array('status' => 3, 'msg' => '环境异常，请刷新后重试')));
                exit();
            }
            if ($_SESSION['captcha_img_code_' . $id] !== strtolower($_REQUEST['image_captcha'])) {
                echo (json_encode(array('status' => 3, 'msg' => '图形验证码错误')));
                exit();
            }
            if (($_SESSION['captcha_img_time_' . $id] + 300) < time()) {
                echo (json_encode(array('status' => 3, 'msg' => '图形验证码已过期')));
                unset($_SESSION['captcha_img_code_' . $id]);
                unset($_SESSION['captcha_img_time_' . $id]);
                exit();
            }
            break;
        case 'slider':
            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['randstr']) || empty($_REQUEST['captcha']['spliced']) || empty($_REQUEST['captcha']['check'])) {
                echo (json_encode(array('status' => 2, 'msg' => '人机验证失败!')));
                exit();
            }
            if (!io_slider_captcha_verification($_REQUEST['captcha']['ticket'], $_REQUEST['captcha']['randstr'])) {
                echo (json_encode(array('status' => 2, 'msg' => '人机验证失败!')));
                exit();
            }
            break;
        case 'tcaptcha':
            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['randstr'])) {
                echo (json_encode(array('status' => 2, 'msg' => '人机验证失败!')));
                exit();
            }
            $tencent007 = io_tcaptcha_verification($_REQUEST['captcha']['ticket'], $_REQUEST['captcha']['randstr']);
            if($tencent007['error']){
                echo (json_encode(array('status' => 2, 'msg' => $tencent007['msg'])));
                exit();
            }
            break;
        case 'geetest':
            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['lot_number'])) {
                echo (json_encode(array('status' => 2, 'msg' => '人机验证失败!')));
                exit();
            }
            $verification = io_geetest_verification($_REQUEST['captcha']);
            if ($verification['error']) {
                echo (json_encode(array('status' => 2, 'msg' => $verification['msg'])));
                exit();
            }
            break;
        case 'vaptcha':
            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['server'])) {
                echo (json_encode(array('status' => 2, 'msg' => '人机验证失败!')));
                exit();
            }
            $verification = io_vaptcha_verification($_REQUEST['captcha']);
            if ($verification['error']) {
                echo (json_encode(array('status' => 2, 'msg' => $verification['msg'])));
                exit();
            }
            break;
    }
    return true;
}

/**
 * 腾讯请求服务器验证
 */
function io_tcaptcha_verification($Ticket,$Randstr){
    $option         = io_get_option('tcaptcha_option');
    $AppSecretKey   = $option['secret_key'];  
    $appid          = $option['appid'];  
    $UserIP         = IOTOOLS::get_ip();
    $http           = new Yurun\Util\HttpRequest;
    if(!empty($option['api_secret_id'])){
        $url = "https://captcha.tencentcloudapi.com";
        $params = array(
            "Action"       => 'DescribeCaptchaResult',
            "Version"      => '2019-07-22',
            "CaptchaType"  => 9,
            "Ticket"       => $Ticket,
            "UserIp"       => $UserIP,
            "Randstr"      => $Randstr,
            "CaptchaAppId" => (int)$appid,
            "AppSecretKey" => $AppSecretKey,
            "Timestamp"    => time(),
            "Nonce"        => rand(),
            "SecretId"     => $option['api_secret_id'],
        );
        $params["Signature"] = tcaptcha_calculate_sig($params,$option['api_secret_key']);

        $result = [];
        $result['response'] = 0;

        $response = $http->post($url, $params);
        $ret      = $response->json(true);

        if(!isset($ret['Response'])){
            $result['err_msg'] = $ret;
        } else {
            $resp = $ret['Response'];
            if (!empty($resp['Error']['Message'])) {
                $result['err_msg'] = $resp['Error']['Message'];
            } elseif (isset($resp['CaptchaMsg'])) {
                if ($resp['CaptchaCode'] === 1 || strtolower($resp['CaptchaMsg']) === 'ok') {
                    $result['response'] = 1;
                } elseif ($resp['CaptchaMsg']) {
                    $result['err_msg'] = $resp['CaptchaMsg'];
                    $result['captcha_code'] = $resp['CaptchaCode'];
                }
            } else {
                $result['err_msg'] = $ret;
            }
        }
    } else {
        $url = "https://ssl.captcha.qq.com/ticket/verify";
        $params = array(
            "aid"          => $appid,
            "AppSecretKey" => $AppSecretKey,
            "Ticket"       => $Ticket,
            "Randstr"      => $Randstr,
            "UserIP"       => $UserIP
        );
        $response = $http->get($url, $params);
        $result   = $response->json(true);
    }
    if($result){
        if($result['response'] == 1){
            
            return array(
                'error'=>0,
                'msg'  => ''
            );
        }else{
            return array(
                'error'=>1,
                'msg'  => (isset($result['captcha_code'])?$result['captcha_code'].': ':'').$result['err_msg']
            );
        }
    }else{
        return array(
            'error'=>1,
            'msg'  => __('请求失败,请再试一次！','i_theme')
        );
    }
}
/**
 * 腾讯验证码签名
 * @param mixed $param
 * @param mixed $secretKey
 * @return string
 */
function tcaptcha_calculate_sig($param,$secretKey) { 
    $tmpParam = [];
    ksort($param);
    foreach ($param as $key => $value) {
        array_push($tmpParam, $key . "=" . $value);
    }
    $strParam  = join("&", $tmpParam);
    $signStr   = 'POSTcaptcha.tencentcloudapi.com/?' . $strParam;
    $signature = base64_encode(hash_hmac('SHA1', $signStr, $secretKey, true));
    return $signature;
}

/**
 * 滑动拼图验证
 * @param mixed $Ticket
 * @param mixed $Randstr
 * @return bool
 */
function io_slider_captcha_verification($Ticket, $Randstr){
    if(!session_id()) session_start();
    if (empty($_SESSION['captcha_slider_x']) || empty($_SESSION['captcha_slider_rand_str'])) {
        return false;
    }
    $machine_slider_x        = $_SESSION['captcha_slider_x'];
    $machine_slider_rand_str = $_SESSION['captcha_slider_rand_str'];
    
    $T_a = (int) substr($Ticket, 0, 2);
    $T_b = (int) substr($Ticket, -2);
    $T_x = (int) substr($Ticket, $T_a + 2, $T_b - 2);

    if (absint($T_x - $machine_slider_x) > 8) {
        return false;
    }

    $R_a = (int) substr($Randstr, 0, 1);
    $R_b = (int) substr($Randstr, -2);
    $R_x = substr($machine_slider_rand_str, $R_a, $R_b - $R_a);
    if ($R_a . $R_x . $R_b !== $Randstr) {
        return false;
    }

    return true;
}
/**
 * 极验行为验
 * @param mixed $data
 * @return array
 */
function io_geetest_verification($data){
    $option         = io_get_option('geetest_option');
    $api_server     = "http://gcaptcha4.geetest.com/validate?captcha_id=" . $option['id'];
    $captcha_key    = $option['key'];
    $lot_number     = $data['lot_number'];
    $captcha_output = $data['captcha_output'];
    $pass_token     = $data['ticket'];
    $gen_time       = $data['gen_time'];
    $sign_token     = hash_hmac('sha256', $lot_number, $captcha_key);

    $query = array(
        "lot_number"     => $lot_number,
        "captcha_output" => $captcha_output,
        "pass_token"     => $pass_token,
        "gen_time"       => $gen_time,
        "sign_token"     => $sign_token,
    );

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->post($api_server, $query);
    $result   = $response->json(true);

    if (!isset($result['result'])) {
        return array('error' => 1, 'msg' => '验证失败');
    }

    if ($result['result'] === 'success') {
        return array('error' => 0);
    }

    return array('error' => 1, 'msg' => '验证失败' . ((!empty($result['reason']) ? '：' . $result['reason'] : '')) . ((!empty($result['msg']) ? '：' . $result['msg'] : '')));
}


/**
 * vaptcha
 * @param mixed $data
 * @return array
 */
function io_vaptcha_verification($data){
    $option    = io_get_option('vaptcha_option');
    $api_server = $data['server'];
    $token      = $data['ticket'];
    $user_ip    = IOTOOLS::get_ip(); 

    $query = array(
        "id"        => $option['id'],
        "secretkey" => $option['key'],
        "scene"     => 0,
        "token"     => $token,
        "ip"        => $user_ip,
    );

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->post($api_server, $query);
    $result   = $response->json(true);

    if (!isset($result['success'])) {
        return array('error' => 1, 'msg' => '验证失败');
    }

    if ($result['success']) {
        return array('error' => 0);
    }

    return array('error' => 1, 'msg' => '验证失败' .  (!empty($result['msg']) ? '：' . $result['msg'] : ''));
}

/**
 * 获取多语言规则列表
 * 
 * @return string
 */
function io_get_lang_rules(){
    if(!io_get_option('m_language',false)){
        return '';
    }else{
        return '(' . io_get_option('lang_list', 'en') . ')/';
    }
}



/**
 * 判断是否已经评论
 * @param mixed $user_id
 * @param mixed $post_id
 * @return bool|null|string
 */
function io_user_is_commented($user_id = 0, $post_id = 0){
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $WHERE = '';
    if ($user_id) {
        $WHERE = "`user_id`={$user_id}";
    } elseif (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) {
        $email = str_replace('%40', '@', $_COOKIE['comment_author_email_' . COOKIEHASH]);
        $WHERE = "`comment_author_email`='{$email}'";
    } else {
        return false;
    }

    global $wpdb;
    $query = "SELECT `comment_ID` FROM {$wpdb->comments} WHERE `comment_post_ID`={$post_id} and `comment_approved`='1' and $WHERE LIMIT 1";
    return $wpdb->get_var($query);
}

/**
 * 获取模态框的炫彩头部
 * @param mixed $class
 * @param mixed $icon
 * @param mixed $title
 * @return string
 */
function io_get_modal_header($class = 'fx-blue', $icon = '', $title = ''){
    $class = !empty($class) ? $class : 'fx-blue';
    $html = '<div class="modal-header modal-header-bg ' . $class . '">';
    $html .= '<button type="button" class="close io-close" data-dismiss="modal" aria-label="Close"><i class="iconfont icon-close text-lg" aria-hidden="true"></i></button>';
    $html .= '<div class="text-center">';
    $html .= $icon ? '<i class="iconfont ' . $icon . ' text-32"></i>' : '';
    $html .= $title ? '<div class="mt-2 text-lg">' . $title . '</div>' : '';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 获取模态框简单头部
 * @param mixed $class
 * @param mixed $icon
 * @param mixed $title
 * @return string
 */
function io_get_modal_header_simple($class = 'vc-blue', $icon = '', $title = ''){
    $class = !empty($class) ? $class : 'vc-blue';
    $html = '<div class="modal-header py-2 modal-header-simple ' . $class . '">';
    $html .= '<span></span>';
    $html .= '<div class="text-md">';
    $html .= $icon ? '<i class="iconfont ' . $icon . ' mr-2"></i>' : '';
    $html .= $title ? '<span class="text-sm">' . $title . '</span>' : '';
    $html .= '</div>';
    $html .= '<button type="button" class="close io-close" data-dismiss="modal" aria-label="Close"><i class="iconfont icon-close text-lg" aria-hidden="true"></i></button>';
    $html .= '</div>';
    return $html;
}


/**
 * ajax模态框通知
 * @param mixed $type 提示类型 success 正常 info 信息 warning 错误 danger 危险
 * @param mixed $msg
 * @return never
 */
function io_ajax_notice_modal($type = 'warning', $msg = ''){
    $type_class = array(
        'success' => 'blue',
        'info'    => 'green',
        'warning' => 'yellow',
        'danger'  => 'red',
    );
    $icon_class = array(
        'success' => 'icon-adopt',
        'info'    => 'icon-tishi',
        'warning' => 'icon-warning',
        'danger'  => 'icon-crying-circle',
    );

    $class = isset($type_class[$type]) ? $type_class[$type] : 'yellow';
    $icon  = isset($icon_class[$type]) ? $icon_class[$type] : 'icon-warning';

    $html = io_get_modal_header('fx-' . $class, $icon);
    $html .= '<div class="modal-body blur-bg">';
    $html .= '<div class="d-flex justify-content-center align-items-center text-md p-3 c-' . $class . '" style="min-height:135px">' . $msg . '</div>';
    $html .= '</div>';
    echo $html;
    exit;
}
/**
 * 头部效果
 * @return string
 */
function io_header_fx(){
    $s = false;
    if($s){
        return '';
    }
    $html = '<div class="background-fx">';
    $html .= '</div>';
    return $html;
}
/**
 * 获取编辑按钮
 * 
 * @param mixed $text
 * @param mixed $before
 * @param mixed $after
 * @param mixed $post_id
 * @param mixed $class
 * @return string|null
 */
function io_get_post_edit_link($post_id = 0, $text = null, $before = '', $after = '', $class = 'post-edit-link')
{
    if (!io_current_user_can('new_post_edit', $post_id)) {
        return;
    }
    $url = io_get_template_page_url('template-contribute.php');
    if (!$url || !io_get_option('is_contribute', false)) {
        $url = get_edit_post_link($post_id);
    } else {
        $post_type = get_post_type($post_id);
        $url       = add_query_arg(['type' => $post_type, 'edit' => $post_id], $url);
    }
    if (!$url) {
        return;
    }

    $text   = $text ?: '<i class="iconfont icon-modify mr-1"></i>' . __('编辑', 'i_theme');
    $before = $before ?: '<span class="edit-link text-xs ml-2 text-muted">';
    $after  = $after ?: '</span>';

    if (null === $text) {
        $text = __('Edit This');
    }

    $link = '<a class="' . esc_attr($class) . '" href="' . esc_url($url) . '">' . $text . '</a>';

    return $before . $link . $after;
}

/**
 * 获取文章分类和标签html
 * 
 * @param int    $post_id
 * @param array  $taxonomy
 * @param string $before 
 * @param string $sep    
 * @param string $after  
 * @return string
 */
function io_get_post_tags($post_id, $taxonomy, $before = '', $sep = '', $after = ''){
    $before = $before?:'<span class="mr-2">';
    $sep    = $sep?:'<i class="iconfont icon-wailian text-ss"></i></span> <span class="mr-2">';
    $after  = $after?:'<i class="iconfont icon-wailian text-ss"></i></span>';

    $html = '';
    foreach ($taxonomy as $tax) {
        $html .= get_the_term_list($post_id, $tax, $before, $sep, $after);
    }
    return $html;
}


/**
 * 获取文章分类和标签
 * @param array  $taxonomy
 * @return string
 */
function io_get_list_tags($taxonomy, $is_l = true){
    global $post;

    if(empty($taxonomy)){
        return '';
    }

    $tags = '';
    $cats  = array('category', 'favorites' , 'apps', 'books');

    foreach ($taxonomy as $tax) {
        $terms  = get_the_terms($post->ID, $tax);
        $before = '# ';
        $count  = 3;
        $class  = $is_l ? '' : 'vc-gray';
        if (in_array($tax, $cats)) {
            $before = '<i class="iconfont icon-folder mr-1"></i>';
            $count  = 2;
            $class  = $is_l ? 'vc-l-theme' : 'vc-theme';
        }
        if ($terms) {
            foreach ($terms as $index => $term) {
                $tags .= '<a href="' . get_term_link($term->term_id) . '" class="badge ' . $class . ' text-ss mr-1" rel="tag" title="' . __('查看更多文章', 'i_theme') . '">' . $before . $term->name . '</a>';
                if ($index == $count - 1) {
                    break;
                }
            }
        }
    }

    $html = '<div class="item-tags overflow-x-auto no-scrollbar">';
    $html .= $tags;
    $html .= '</div>';
    return $html;
}

/**
 * 获取文章列表的底部meta
 * @param mixed $type post，app，book，sites
 * @param mixed $class
 * @param mixed $hide 需要隐藏的标签
 * @return string
 */
function io_get_list_meta($type = 'post', $class = '', $hide = array()){
    global $post;
    $config  = io_get_option($type . '_list_meta') ?: array();//'date', 'author'
    $default = array(
        'date'    => in_array('date', $config) ? true : false,
        'author'  => in_array('author', $config) ? true : false,
        'views'   => in_array('views', $config) ? true : false,
        'like'    => in_array('like', $config) ? true : false,
        'comment' => in_array('comment', $config) ? true : false,
    );

    $args = wp_parse_args($hide, $default);
    if(!io_get_option('post_date_show', true)){
        $args['date'] = false;
    }

    $meta_left = '';
    if ($args['author']) {

        $author_name = '';
        $author_id   = $post->post_author;
        if (!$args['date']) {
            $user = get_userdata($author_id);
            if (isset($user->display_name)) {
                $author_name = '<span>' . $user->display_name . '</span>';
            }

        }
        $meta_left .= '<a href="' . get_author_posts_url($author_id) . '" class="avatar-sm mr-1" ' . new_window() . '>' . get_avatar(get_the_author_meta('email'), '20') . '</a>';
        $meta_left .= $author_name;
    }
    if ($args['date']) {
        $icon = '';
        if (!$args['author']){
            $icon = '<i class="iconfont icon-time-o"></i>';
        }
        $time      = get_the_modified_time('Y-m-d H:i:s', $post);
        $time_ago  = io_time_ago($time);
        $meta_left .= '<span title="' . esc_attr($time) . '" class="meta-time">' . $icon . $time_ago . '</span>';
    }
    $l_c = '';
    if ($meta_left) {
        $l_c = 'ml-auto';
        $meta_left = '<div class="meta-left">' . $meta_left . '</div>';
    }
    $meta_right = '<div class="' . $l_c . ' meta-right">' . io_get_meta_tag($post, $args) . '</div>';

    $html = '<div class="item-meta d-flex align-items-center flex-fill text-muted text-xs' . $class . '">';
    $html .= $meta_left;
    $html .= $meta_right;
    $html .= '</div>';
    return $html;
}

/**
 * 获取文章数据标签
 * @param mixed $type
 * @param mixed $hide 需要隐藏的标签 array(
 *                          'views'   => true,
 *                          'like'    => true,
 *                          'comment' => true,
 *                          'down'    => true,
 *                      )
 * @return string
 */
function io_get_meta_tag($type = 'posts', $hide = array()) {
    global $post;
    $post_id = $post->ID;

    $default = array(
        'views'   => true,
        'like'    => true,
        'comment' => true,
        'down'    => true,
    );
    $args = wp_parse_args($hide, $default);
    
    $meta         = '';
    $comment_href = '';
    
    if ($args['comment'] && comments_open($post) && io_get_option('nav_comment', true)) {
        if (is_single()) {
            $comment_href = '#comments';
        } else {
            $comment_href = get_comments_link($post_id);
        }
        $meta .= '<span class="meta-comm d-none d-md-inline-block" data-toggle="tooltip" title="' . __('去评论', 'i_theme') . '" js-href="' . $comment_href . '"><i class="iconfont icon-comment"></i>' . get_comments_number($post_id) . '</span>';
    }
    if ($args['views'] && function_exists('the_views')) {
        $meta .= '<span class="meta-view"><i class="iconfont icon-chakan-line"></i>' . the_views(false) . '</span>';
    }
    if ($args['like'] && io_get_option('post_like_s', true)) {
        $meta .= '<span class="meta-like d-none d-md-inline-block"><i class="iconfont icon-like-line"></i>' . io_number_format(io_get_posts_like_count($post_id)) . '</span>';
    }
    if ($args['down'] && 'app' === $type) {
        $meta .= '<span class="meta-down d-none d-md-inline-block"><i class="iconfont icon-down"></i> ' . io_number_format(get_post_meta($post_id, '_down_count', true) ?: 0) . '</span>';
    }
    return $meta;
}
/**
 * 获取文章分类和标签按钮html
 * 
 * @param int $post_id 文章ID
 * @param array|string $taxonomy 分类法名称或数组
 * @param array|string $before 按钮前缀
 * @param array|string $after 按钮后缀
 * @param string $class 按钮样式
 * @param int $count 限制显示的标签数量
 * @return string 分类和标签按钮的HTML
 */
function io_get_posts_terms_btn($post_id, $taxonomy, $before = '', $after = '', $class='text-xs', $count = 0)
{
    $taxonomy = (array) $taxonomy;
    $before   = (array) $before;
    $after    = (array) $after;

    $cache_key = 'cat_btn_' . $post_id . '_' . md5(implode('_', $taxonomy));
    $btn       = wp_cache_get($cache_key, 'cat_btn');

    if ($btn !== false) {
        return $btn;
    }

    $i   = 0;
    $btn = '';
    foreach ($taxonomy as $index => $tax) {
        $terms = get_the_terms($post_id, $tax);

        if (empty($terms) || is_wp_error($terms)) {
            continue;
        }

        $begin = $before[$index] ?? $before[0] ?? '';
        $end   = $after[$index] ?? $after[0] ?? '';

        foreach ($terms as $term) {
            $url = get_term_link($term->term_id);
            $css = get_theme_color($i, 'l', true) . ' btn btn-sm text-height-xs m-1 rounded-pill ' . $class;

            $btn .= sprintf(
                '<a href="%s" class="%s" rel="tag" title="%s">%s</a>',
                esc_url($url),
                esc_attr($css),
                esc_attr(__('查看更多', 'i_theme')),
                $begin . $term->name . $end,
            );

            $i++;
            if ($count && $i >= $count) {
                break 2;
            }
        }

    }

    wp_cache_set($cache_key, $btn, 'cat_btn', MONTH_IN_SECONDS);

    return $btn;
}

/**
 * 获取文章时间
 * 
 * @return string
 */
function io_get_post_time($before = '<span class="mr-3"><i class="iconfont icon-time-o"></i>', $after = '</span>')
{
    if (!io_get_option('post_date_show', true)) {
        return '';
    }
    global $post;
    $modified_time = get_the_modified_time('U', $post);
    $time          = get_the_time('U', $post);

    $data_type = io_get_option('post_date_type', 'update');
    if ($data_type == 'update') {
        if ($modified_time > $time) {
            $time_html = '<span title="' . io_date_time($time) . __('发布', 'i_theme') . '">' . io_time_ago($modified_time) . __('更新', 'i_theme') . '</span>';
        } else {
            $time_html = '<span title="' . io_date_time($time) . __('发布', 'i_theme') . '">' . io_time_ago($time) . __('发布', 'i_theme') . '</span>';
        }
    } else {
        $time_html = '<span title="' . io_date_time($time) . __('发布', 'i_theme') . '">' . io_time_ago($time) . __('发布', 'i_theme') . '</span>';
    }
    return $before . $time_html . $after;
}
/**
 * 下载列表模态框
 * 
 * @param mixed $title 标题
 * @param mixed $down_list 资源列表
 * @param mixed $type app book
 * @param mixed $decompression
 * @return string
 */
function io_get_down_modal($title, $down_list, $type, $decompression = '', $count = 0){
    global $post;
    $post_id = $post->ID;
    $key     = '';
    if('app'===$type){
        $key = 'down_btn_';
    }
    $html = '<div class="modal fade resources-down-modal" id="'.$type.'-down-modal">';
    $html .= '<div class="modal-dialog modal-lg modal-dialog-centered">';
    $html .= '<div class="modal-content overflow-hidden">';
    $html .= io_get_modal_header_simple('', 'icon-down', $title );
    $html .= '<div class="modal-body down_body">';

    $html .= '<div class="down_btn_list mb-4">';
    
    if($down_list){
        $html .= '<div class="row no-gutters">';
        $html .= '<div class="col-6 col-md-7">'.__('描述','i_theme').'</div>';
        $html .= '<div class="col-2 col-md-2" style="white-space: nowrap;">'.__('提取码','i_theme').'</div>';
        $html .= '<div class="col-4 col-md-3 text-right">'.__('下载','i_theme').'</div>';
        $html .= '</div>';
        $html .= '<div class="col-12 -line- my-2"></div>';

        $list = '';
        for($i=0; $i<count($down_list); $i++){
            if ($count && $i > $count) {
                break;
            }
            $list .= '<div class="row no-gutters">';
            $list .= '<div class="col-6 col-md-7">'. ($down_list[$i][$key.'info']?:__('无','i_theme')) .'</div>';
            $list .= '<div class="col-2 col-md-2" style="white-space: nowrap;">'. ($down_list[$i][$key.'tqm']?:__('无','i_theme')) .'</div>';
            $list .= '<div class="col-4 col-md-3 text-right"><a class="btn vc-l-theme py-0 px-1 mx-auto down_count text-sm" href="'. go_to($down_list[$i][$key.'url']) .'" target="_blank" data-id="'. $post_id .'" data-action="down_count" data-clipboard-text="'.($down_list[$i][$key.'tqm']?:'').'" data-mmid="down-mm-'.$i.'">'.$down_list[$i][$key.'name'].'</a></div>';
            if($down_list[$i][$key.'tqm']) 
                $list .= '<input type="text" style="width:1px;position:absolute;height:1px;background:transparent;border:0px solid transparent" name="down-mm-'.$i.'" value="'.$down_list[$i][$key.'tqm'].'" id="down-mm-'.$i.'">';
            $list .= '</div>';
            $list .= '<div class="col-12 -line- my-2"></div>';
        }
        $html .= '<div class="down_btn_row">'.$list.'</div>';

    }else{
        $html .= '<div class="tips-box btn-block">'.__('没有内容','i_theme').'</div>';
    }
    if ($decompression)
        $html .= '<div class="w-100 text-right"><p class="mt-2 tips-box text-sm py-0">' . __('解压密码：', 'i_theme') . $decompression . '</p></div>';
    $html .= '</div>';

    $html .= show_ad('ad_res_down_popup', false, 'd-none d-md-block', '<div class="apd-body">', '</div>', false);       
    $html .= '<div class="tips-box vc-l-blue text-sm" role="alert"><i class="iconfont icon-statement mr-2" ></i><strong>' . __('声明：', 'i_theme') . '</strong>' . io_get_option('down_statement', '') . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';  
    $html .= '</div>'; 
    
    return $html;
}


/**
 * 知心天气
 * @return void
 */
function io_get_weather_widget($html = ''){
    $locale = 'zh-chs';
    switch (determine_locale()) {
        case 'zh':
        case 'zh_CN':
            $locale = 'zh-chs';
            break;
        case 'zh_TW':
        case 'zh_HK':
            $locale = 'zh-cht';
            break;
        case 'pt_PT':
        case 'pt_BR':
        case 'pt_AO':
            $locale = 'pt';
            break;
        case 'ja':
            $locale = 'ja';
            break;
        case 'en_AU':
        case 'en_GB':
        case 'en_US':
        default:
            $locale = 'en';
            break;
    }
    $key = io_get_option('weather_token','');
    $key = $key ? ' data-token="' . $key . '"' : '';
    echo '<div id="io_weather_widget" class="io-weather-widget" data-locale="' . $locale . '"'.$key.'>' . $html . '</div>';
}
