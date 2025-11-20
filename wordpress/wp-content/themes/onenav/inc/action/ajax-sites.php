<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-04 21:42:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-12-16 17:00:01
 * @FilePath: /onenav/inc/action/ajax-sites.php
 * @Description: 
 */

/**
 * 提交网址
 * @return never
 */
function io_ajax_new_posts_sites()
{
    $delay = io_get_option('contribute_time',30); 
    io_ajax_new_posts_safety_verify();
    
    //表单变量初始化
    $title      = __post('post_title', '');
    $content    = isset($_POST['post_content']) ? $_POST['post_content'] : false;
    $category   = __post('category', false);
    $keywords   = io_split_str(__post('tags', ''));
    $action     = __post('action', '');
    $posts_id   = __post('posts_id', 0);
    
    $sites_type = __post('internal_type', '');
    $describe   = __post('describe', '');
    $sites_link = __post('link', '');
    $sites_ico  = __post('cover-img', '');
    $wechat_id  = __post('wechat_id', '');


    $typename = __('网站', 'i_theme');
    if ($sites_type == 'wechat')
        $typename = __('公众号', 'i_theme');

    $option = io_get_option('sites_tg_config');
    $desc_limit = $option['desc_limit'] ?: 80;
    $u_id = get_current_user_id();


    // 验证文章基本信息
    io_ajax_new_posts_data_basic_verify('sites', compact('title', 'content', 'category', 'keywords', 'u_id', 'sites_type'));
 
    // meta信息验证
    if (!$posts_id && title_exists($title, 'sites')) {
        io_error('{"status":4,"msg":"' . __('存在相同的名称，请不要重复提交哦！', 'i_theme') . '"}');
    }

    if ($sites_type == 'sites' && empty($sites_link)) {
        io_error('{"status":3,"msg":"' . $typename . __('链接必须填写！', 'i_theme') . '"}');
    }
    if (!$posts_id && $sites_type == 'sites' && link_exists($sites_link)) {
        io_error('{"status":4,"msg":"' . __('存在相同的链接地址，请不要重复提交哦！', 'i_theme') . '"}');
    }
    if (!empty($sites_link) && !filter_var($sites_link, FILTER_VALIDATE_URL)) {
        io_error('{"status":3,"msg":"' . $typename . __('链接必须符合URL格式。', 'i_theme') . '"}');
    }


    if (empty($describe)) {
        io_error('{"status":4,"msg":"' . $typename . __('简介必须填写！', 'i_theme') . '"}');
    }
    if (mb_strlen($describe) > $desc_limit) {
        io_error('{"status":4,"msg":"' . $typename . sprintf(__('简介长度不得超过%s字。', 'i_theme'), $desc_limit) . '"}');
    }

    if ($sites_type == 'wechat' && empty($wechat_id)) {
        io_error('{"status":4,"msg":"' . __('必须添加微信号。', 'i_theme') . '"}');
    }


    //执行人机验证
    io_ajax_is_robots();
    $edit_posts = [];
    if ($posts_id) {
        $edit_posts = get_post($posts_id, ARRAY_A);
        if ($edit_posts && $posts_id && $edit_posts['post_author'] != $u_id && !is_super_admin()) {
            // 非管理员编辑非自己文章时，不允许编辑
            io_error(json_encode(array('status' => 4, 'reset' => 1, 'msg' => __('您没有权限编辑此文章！', 'i_theme'))));
        }
    }
    $is_publish = io_ajax_get_is_publish('sites', $u_id, $edit_posts, $category);


    //if(!empty($_wechat_qr) && $_wechat_qr['error']==0){
    //    $_img = IOTOOLS::addImg($_wechat_qr,'wechat_qr',$oldimg_id);
    //    $wechat_qr = $_img["src"];
    //}  //根据微信号通过api生成二维码


    //文章状态
    $post_status = 'pending';
    if ('sites_draft' === $action) {
        if(!$u_id){
            io_error(json_encode(array('status' => 4, 'reset' => 1, 'msg' => __('请先登录！', 'i_theme'))));
        }
        // 草稿状态，优先级最高
        $post_status = 'draft';
    }elseif ($is_publish) {
        $post_status = 'publish';
    }

    if (!empty($keywords) && 'publish' !== $post_status && io_get_option('tag_temp', true)) {
        if ($posts_id) {
            //删掉原来的关键词
            $reg = '/<div\s+class=\\\?"io-delete-keys\\\?"\s*(?:[^>]*?)>(.*?)<\/div>/s';


            $content = preg_replace($reg, '', $content);
        }
        $content = '<div class="io-delete-keys" contenteditable="false"><div contenteditable="true">' . PHP_EOL . implode(',', $keywords) . PHP_EOL . '</div></div>' . PHP_EOL . $content;
    }

    $post_data = array(
        'ID'             => $posts_id,
        'post_type'      => 'sites',
        'post_title'     => $title,
        'post_status'    => $post_status,
        'ping_status'    => 'closed',
        'post_content'   => $content,
        'comment_status' => 'open',
        //'tax_input'        => array( 'favorites' => (array)$category ) //游客不可用
    );

    if (!$posts_id) {
        $post_data['post_author'] = $u_id;
    } else {
        if (isset($edit_posts['ID'])) {
            $post_data = array_merge($edit_posts, $post_data);
        }
    }

    $taxonomy = array(
        'favorites' => $category,
        'sitetag'   => $keywords
    );

    $meta = array(
        '_sites_type'     => $sites_type,
        '_sites_link'     => $sites_link,
        '_sites_sescribe' => $describe,
        '_thumbnail'      => $sites_ico,
        '_wechat_id'      => $wechat_id,
        '_sites_order'    => 0,
    );
    if(!$u_id){
        $meta['guest_info'] = __post('guest_info');
        $meta['guest_info']['time'] = date('Y-m-d H:i:s');
    }
    
    io_ajax_insert_new_posts($post_data, $taxonomy, $meta, isset($edit_posts['post_status']) ? $edit_posts['post_status'] : '');
    exit;
}
add_action('wp_ajax_sites_save', 'io_ajax_new_posts_sites');
add_action('wp_ajax_nopriv_sites_save', 'io_ajax_new_posts_sites');
add_action('wp_ajax_sites_draft', 'io_ajax_new_posts_sites');
add_action('wp_ajax_nopriv_sites_draft', 'io_ajax_new_posts_sites');
add_action('wp_ajax_sites_pass', 'io_ajax_new_posts_sites');
add_action('wp_ajax_nopriv_sites_pass', 'io_ajax_new_posts_sites');
add_action('wp_ajax_sites_pay', 'io_ajax_new_posts_sites');
add_action('wp_ajax_nopriv_sites_pay', 'io_ajax_new_posts_sites');


function io_ajax_get_sites_seo_data(){
    $url = esc_url($_REQUEST['url']);
    if (empty($url)) {
        exit;
    }
    $cache_key = 'seo_' . md5($url);
    $sites_seo = wp_cache_get($cache_key, 'sites_seo');
    if (!$sites_seo) {
        $http = new Yurun\Util\HttpRequest;

        $api_url = "https://apis.5118.com/weight";

        $http->headers([
            'Content-Type'  => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Authorization' => '9E5DD16996FE4427AD40C8DA7872B014',
        ]);

        $params = array(
            "url" => $url
        );

        $response     = $http->post($api_url, $params);
        $ret          = $response->json(true);
        $sites_seo    = $ret;
        $ret['cache'] = 1;
        wp_cache_set($cache_key, $ret, 'sites_seo', DAY_IN_SECONDS);
    }
    io_error($sites_seo);
}
add_action('wp_ajax_nopriv_get_sites_seo', 'io_ajax_get_sites_seo_data');  
add_action('wp_ajax_get_sites_seo', 'io_ajax_get_sites_seo_data');
