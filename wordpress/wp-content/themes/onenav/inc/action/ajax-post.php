<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-04 21:36:40
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 23:12:22
 * @FilePath: /onenav/inc/action/ajax-post.php
 * @Description: 
 */

/**
 * 投稿安全验证
 * 
 * @return void
 */
function io_ajax_new_posts_safety_verify(){
    $delay = io_get_option('contribute_time',30); 

    if (!io_get_option('is_contribute',true)) {
        io_error (json_encode(array('status' => 1,'msg' => __('投稿功能已关闭','i_theme'))));
    }
    if (!wp_verify_nonce($_POST['_wpnonce'],'posts_contribute_submit')){
        io_error('{"status":4,"msg":"'.__('安全检查失败，请刷新或稍后再试！','i_theme').'"}');
    } 
    if( isset($_COOKIE["tougao"]) && ( time() - $_COOKIE["tougao"] ) < $delay ){
        io_error('{"status":2,"msg":"'.sprintf( __('您投稿也太勤快了吧，“%s”秒后再试！', 'i_theme'), $delay - ( time() - $_COOKIE["tougao"] ) ).'"}');
    } 
}
/**
 * 验证文章基本信息
 * 
 * @param mixed $post_type  文章类型
 * @param array $args  参数,包含 title, content, category, keywords, option, u_id 等
 * @param mixed $v_content 是否验证内容
 * @return void
 */
function io_ajax_new_posts_data_basic_verify($post_type, $args, $v_content = false)
{
    extract($args);
    $option = io_get_option($post_type . '_tg_config');

    $type_name = io_get_post_type_name($post_type);
    // 提示信息准备
    if ('post' === $post_type) {
        $title_msg       = __('请填写文章标题', 'i_theme');
        $title_limit_min = __('标题太短！', 'i_theme');
        $title_limit_max = sprintf(__('标题太长了，不能超过%s个字', 'i_theme'), $option['title_limit']['height']);
    } else {
        if ('sites' === $post_type && 'wechat' === $sites_type) {
            $type_name = __('公众号', 'i_theme');
        }
        $title_msg       = sprintf(__('请填写%s名称', 'i_theme'), $type_name);
        $title_limit_min = sprintf(__('%s名称太短！', 'i_theme'), $type_name);
        $title_limit_max = sprintf(__('%s名称太长了，不能超过%s个字', 'i_theme'), $type_name, $option['title_limit']['height']);
    }
    
    if (empty($title)) {
        io_error(array('status' => 4, 'msg' => $title_msg));
    }

    if ($v_content && empty($content)) {
        io_error(array('status' => 4, 'msg' => __('还未填写任何内容', 'i_theme')));
    }

    if ($v_content && io_strlen($content) < 10) {
        io_error(array('status' => 4, 'msg' => __('文章内容过少', 'i_theme')));
    }

    if (io_strlen($title) > $option['title_limit']['height']) {
        io_error(array('status' => 4, 'msg' => $title_limit_max));
    }

    if (io_strlen($title) < $option['title_limit']['width']) {
        io_error(array('status' => 4, 'msg' => $title_limit_min));
    }

    if (empty($category)) {
        io_error(array('status' => 4, 'msg' => __('请选择分类', 'i_theme')));
    }

    if(!is_super_admin() && $option['cat_limit'] > 0 && count((array)$category) > $option['cat_limit']){
        io_error(array('status' => 4, 'msg' => sprintf(__('分类不能超过%s个！', 'i_theme'), $option['cat_limit'])));
    }

    if (!is_super_admin() && !empty($keywords) && 0 != $option['tag_limit']) {
        if (count($keywords) > $option['tag_limit']) {
            io_error('{"status":4,"msg":"' . sprintf(__('标签不能超过%s个！', 'i_theme'), $option['tag_limit']) . '"}');
        }
    }

    
    if (!$u_id) {
        $guest_info = __post('guest_info', false);
        if (empty($guest_info['name'])) {
            io_error(array('status' => 3, 'msg' => __('请输入昵称！', 'i_theme')));
        }
        if (empty($guest_info['contact'])) {
            io_error(array('status' => 3, 'msg' => __('请输入联系方式！', 'i_theme')));
        }
    }
}
/**
 * 获取文章状态是否为发布
 * 
 * @param mixed $post_type  文章类型
 * @param mixed $u_id 登陆的用户ID
 * @param array $edit_posts 编辑的文章数据
 * @param mixed $category 返回分类
 * @return bool
 */
function io_ajax_get_is_publish($post_type, $u_id, $edit_posts, &$category)
{
    $posts_id       = __post('posts_id', 0);
    $action         = __post('action', false);
    $option         = io_get_option($post_type . '_tg_config');
    $current_action = $post_type . '_save';

    $is_publish = false;
    if (is_super_admin()) {
        if ($posts_id && $current_action === $action && 'publish' != $edit_posts['post_status']) {//$edit_posts['post_author'] != $u_id && 
            if($edit_posts['post_author'] == $u_id){
                // 管理员编辑自己的文章时，默认发布，一般是草稿状态的文章
                $is_publish = true;
            } else {
                // 管理员编辑文章时，保持原状态
                $is_publish = false;
            }
        } else {
            // 管理员新投稿时，默认发布
            $is_publish = true;
        }
    } elseif ($posts_id) {
        // 编辑文章时
        if (io_get_option('audit_edit', true)) {
            // 编辑后需要审核
            $is_publish = false;

            $again_edit_rebate = io_get_option('again_edit_rebate', 80); // 再次付费折扣
            $is_pay_post       = get_post_meta($posts_id, 'io_pay_post', true); // 是否是付费内容
            if ($is_pay_post && 0 == $again_edit_rebate) {
                // 付费文章编辑时，且无需再次付费
                $is_publish = true;
            }
        } else {
            // 编辑后不审核
            $is_publish = true;
        }
    }else{
        // 新投稿时
        if($option['is_publish']){ // 默认发布
            if ($option['auto_category'])
                $category = $option['auto_category'];
            $is_publish = true;
        }
    }
    return $is_publish;
}

/**
 * 前台投稿插入数据库
 * @param array $post_data 文章数据
 * @param array $taxonomy 分类 
 * @param array $meta_data 其他meta数据
 * @param string $old_status 旧状态
 * @return void
 */
function io_ajax_insert_new_posts($post_data, $taxonomy, $meta_data = array(), $old_status = '')
{
    $delay       = io_get_option('contribute_time', 30);
    $posts_id    = $post_data['ID'];
    $post_status = $post_data['post_status'];
    $post_type   = $post_data['post_type'];
    $action      = __post('action', false);
    $u_id        = get_current_user_id();

    add_filter('wp_kses_allowed_html', 'io_allow_html_iframe_attributes', 99, 2);
    
    // 将文章插入数据库
    $in_id = wp_insert_post($post_data, true);

    if (is_wp_error($in_id)) {
        io_error(array(
            'status' => 4,
            'reset'  => 1,
            'msg'    => $in_id->get_error_message()
        ));
    }
    if (!$in_id) {
        io_error(array(
            'status' => 4,
            'reset'  => 1,
            'msg'    => __('投稿失败！', 'i_theme')
        ));
    }

    // 插入文章meta信息
    if (!empty($meta_data)) {
        foreach ($meta_data as $key => $value) {
            update_post_meta($in_id, $key, $value);
        } 
    }

    // 设置文章分类
    foreach ($taxonomy as $tax => $terms) {
        if (substr($tax, -3) === 'tag') { //in_array($tax, array('sitetag', 'post_tag', 'apptag', 'booktag')
            if (!empty($terms) && 'publish' === $post_status || !io_get_option('tag_temp', true)) {
                wp_set_object_terms($in_id, (array) $terms, $tax); //设置文章tag
            }
        } else {
            wp_set_post_terms($in_id, (array) $terms, $tax);
        }
    }


    if (!is_super_admin()) {
        setcookie("tougao", time(), time() + $delay + 10, '/', '', false);
    }

    $send = array(
        'status' => 1,
        'msg'    => __('投稿成功！', 'i_theme')
    );
    if ($posts_id && $post_type . '_save' === $action) {
        $send['msg'] = __('保存成功！', 'i_theme');
    } elseif ($post_type . '_pass' === $action) {
        $send['msg'] = __('审核通过！', 'i_theme');
    } elseif ($post_type . '_draft' === $action) {
        $send['msg'] = __('已保存草稿！', 'i_theme');
    }

    if ($post_type . '_pay' === $action) {
        $arg = array(
            'action'    => 'posts_pay_publish',
            'post_id'   => $in_id,
            'post_type' => $post_type,
        );
        $contribute_url = io_get_template_page_url('template-contribute.php');
        $edit_url   = add_query_arg(['type' => $post_type, 'edit' => $in_id], $contribute_url);

        $send['msg']    = '';
        $send['action'] = array(
            'event'    => 'click',
            'target'   => '.io-ajax-modal-get.ajax-click',
            'url'      => add_query_arg($arg, admin_url('admin-ajax.php')),
            'data'     => '',
            'edit_url' => $edit_url,
        );
    } else {
        if ($u_id && !$posts_id && $post_type . '_save' === $action) {
            $send['goto'] = get_permalink($in_id);
            $send['delay'] = 1000; //延迟时间
        } else {
            if ($post_type . '_draft' === $action) {
                $contribute_url = io_get_template_page_url('template-contribute.php');
                $send['goto']   = add_query_arg(['type' => $post_type, 'edit' => $in_id], $contribute_url);
                $send['delay'] = 500; //延迟时间
            } else {
                $send['reload'] = 1; //刷新页面
                $send['delay'] = 1000; //延迟时间
            }
        }
    }

    /**
     * 投稿成功事件
     * 
     * @param int $in_id 文章ID
     * @param string $post_status 新状态
     * @param string $old_status 旧状态
     * @return void
     */
    do_action('io_new_' . ($posts_id ? 'edit' : 'add') . '_posts', $in_id, $post_status, $old_status);

    if (!$posts_id && 'pending' === $post_status) { //新文章投稿成功
        /**
         * 
         * 投稿成功后通知审核
         * @param WP_Post $post
         * @return void
         */
        do_action('io_contribute_to_publish', get_post($in_id));
    }
    io_error($send);
}



/**
 * 新建文章
 * @return never
 */
function io_ajax_new_posts_post(){
    io_ajax_new_posts_safety_verify();

    //表单变量初始化
    $title    = __post('post_title', false);
    $content  = isset($_POST['post_content']) ? $_POST['post_content'] : false;
    $category = __post('category', false);
    $action   = __post('action', false);
    $keywords = io_split_str(__post('tags'));
    $posts_id = __post('posts_id', 0);
    
    $u_id = get_current_user_id();

    //验证文章基本信息
    io_ajax_new_posts_data_basic_verify('post', compact('title', 'content', 'category', 'keywords', 'u_id'), true);

    //人机验证
    io_ajax_is_robots();

    $edit_posts = [];
    if($posts_id){
        $edit_posts = get_post($posts_id, ARRAY_A);
        if ($edit_posts && $posts_id && $edit_posts['post_author'] != $u_id && !is_super_admin()) {
            // 非管理员编辑非自己文章时，不允许编辑
            io_error(json_encode(array('status' => 4, 'reset' => 1, 'msg' => __('您没有权限编辑此文章！', 'i_theme'))));
        }
    }

    $is_publish = io_ajax_get_is_publish('post', $u_id, $edit_posts, $category);

    //文章状态
    $post_status = 'pending';
    if ('post_draft' === $action) {
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


            $content =  preg_replace($reg, '', $content);
        }
        $content = '<div class="io-delete-keys" contenteditable="false"><div contenteditable="true">' . PHP_EOL . implode(',', $keywords) . PHP_EOL . '</div></div>' . PHP_EOL . $content;
    }

    // 文章数据准备
    $post_data = array(
        'ID'             => $posts_id,
        'post_type'      => 'post',
        'post_title'     => $title,
        'post_status'    => $post_status,
        'post_content'   => $content,
        //'post_category'  => (array)$category,
        'comment_status' => 'open',
    ); 
    if (!$posts_id) {
        $post_data['post_author'] = $u_id;
    } else {
        if (isset($edit_posts['ID'])) {
            $post_data = array_merge($edit_posts, $post_data);
        }
    }
    
    //if(!empty($keywords) && 'publish' === $post_status || !io_get_option('tag_temp',true)) {
    //    $post_data['tags_input'] = $keywords;
    //}

    $taxonomy = array(
        'category' => $category,
        'post_tag' => $keywords
    );

    $meta = array();
    if(!$u_id){
        $meta['guest_info'] = __post('guest_info');
        $meta['guest_info']['time'] = current_time('Y-m-d H:i:s');
    }

    io_ajax_insert_new_posts($post_data, $taxonomy, $meta, isset($edit_posts['post_status']) ? $edit_posts['post_status'] : '');
    exit;
}
add_action('wp_ajax_post_save', 'io_ajax_new_posts_post');
add_action('wp_ajax_nopriv_post_save', 'io_ajax_new_posts_post');
add_action('wp_ajax_post_draft', 'io_ajax_new_posts_post');
add_action('wp_ajax_nopriv_post_draft', 'io_ajax_new_posts_post');
add_action('wp_ajax_post_pass', 'io_ajax_new_posts_post');
add_action('wp_ajax_nopriv_post_pass', 'io_ajax_new_posts_post');
add_action('wp_ajax_post_pay', 'io_ajax_new_posts_post');
add_action('wp_ajax_nopriv_post_pay', 'io_ajax_new_posts_post');


function filter_media_library_for_current_user($query) {
    // 检查是否为管理员角色
    if (!current_user_can('manage_options')) {
        // 如果不是管理员，只显示当前用户上传的媒体文件
        $query['author'] = get_current_user_id();
    }
    return $query;
}
add_filter('ajax_query_attachments_args', 'filter_media_library_for_current_user');
