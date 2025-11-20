<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-04 21:42:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 22:27:27
 * @FilePath: /onenav/inc/action/ajax-app.php
 * @Description: 
 */

// TODO:待完善
//提交网址
function io_ajax_new_posts_app()
{
    io_ajax_new_posts_safety_verify();

    //表单变量初始化
    $title    = __post('post_title', '');
    $content  = isset($_POST['post_content']) ? $_POST['post_content'] : false;
    $category = __post('category', false);
    $keywords = io_split_str(__post('tags', ''));
    $action   = __post('action', '');
    $posts_id = __post('posts_id', 0);

    $describe    = __post('describe', '');
    $app_cover   = __post('cover-img', '');
    $app_type    = __post('internal_type', '');
    $app_name    = __post('app_name', $title);
    $down_list   = __post('down_list', []);
    $platform    = __post('platform', []);
    $screenshot  = __post('screenshot', []);
    $down_formal = __post('down_formal', '');



    $option     = io_get_option('app_tg_config');
    $desc_limit = $option['desc_limit'] ?: 80;
    $u_id       = get_current_user_id();


    // 验证文章基本信息
    io_ajax_new_posts_data_basic_verify('app', compact('title', 'content', 'category', 'keywords', 'u_id', 'app_type'));

    // meta信息验证
    if (empty($describe)) {
        io_error('{"status":4,"msg":"' . __('简介必须填写！', 'i_theme') . '"}');
    }
    if (mb_strlen($describe) > $desc_limit) {
        io_error('{"status":4,"msg":"' . sprintf(__('简介长度不得超过%s字。', 'i_theme'), $desc_limit) . '"}');
    }
    // 判断 $down_list 数组里的 app_version 和 down_url 是否有空值
    if (is_array($down_list)) {
        foreach ($down_list as $key => $value) {
            if (empty($value['app_version'])) {
                io_error('{"status":4,"msg":"' . __('版本号不能为空！', 'i_theme') . '"}');
            }
            if (empty($value['down_url'])) {
                io_error('{"status":4,"msg":"' . sprintf(__('版本(%s)里的下载地址不能为空！', 'i_theme'), $value['app_version']) . '"}');
            }
            if ($value['down_url'] && is_array($value['down_url'])) {
                // 判断 $down_list 数组里的 down_url 数组里的 down_btn_name 和 down_btn_url 是否有空值
                foreach ($value['down_url'] as $k => $v) {
                    if (empty($v['down_btn_name']) || empty($v['down_btn_url'])) {
                        io_error('{"status":4,"msg":"' . sprintf(__('版本(%s)里的下载列表有名称或者链接为空！', 'i_theme'), $value['app_version']) . '"}');
                    }
                }
            } else {
                io_error('{"status":4,"msg":"' . sprintf(__('版本(%s)里没有添加下载列表！', 'i_theme'), $value['app_version']) . '"}');
            }
        }
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
    $is_publish = io_ajax_get_is_publish('app', $u_id, $edit_posts, $category);


    //文章状态
    $post_status = 'pending';
    if ('app_draft' === $action) {
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
        'post_type'      => 'app',
        'post_title'     => $title,
        'post_status'    => $post_status,
        'ping_status'    => 'closed',
        'post_content'   => $content,
        'comment_status' => 'open',
    );

    if (!$posts_id) {
        $post_data['post_author'] = $u_id;
    } else {
        if (isset($edit_posts['ID'])) {
            $post_data = array_merge($edit_posts, $post_data);
        }
    }

    $taxonomy = array(
        'apps'   => $category,
        'apptag' => $keywords
    );

    $meta = array(
        '_app_type'     => $app_type,
        '_app_sescribe' => $describe,
        '_app_ico'      => $app_cover,
        '_app_name'     => $app_name,
        'app_down_list' => $down_list,
        '_app_platform' => $platform,
        '_screenshot'   => $screenshot,
        '_down_formal'  => $down_formal
    );
    if(!$u_id){
        $meta['guest_info'] = __post('guest_info');
        $meta['guest_info']['time'] = date('Y-m-d H:i:s');
    }
    if(!$posts_id || $posts_id && !get_post_meta($posts_id,'_down_default',true)){
        $meta['_down_default'] = $down_list[0]?:[];
    }

    io_ajax_insert_new_posts($post_data, $taxonomy, $meta, isset($edit_posts['post_status']) ? $edit_posts['post_status'] : '');
    exit;
}
add_action('wp_ajax_app_save', 'io_ajax_new_posts_app');
add_action('wp_ajax_nopriv_app_save', 'io_ajax_new_posts_app');
add_action('wp_ajax_app_draft', 'io_ajax_new_posts_app');
add_action('wp_ajax_nopriv_app_draft', 'io_ajax_new_posts_app');
add_action('wp_ajax_app_pass', 'io_ajax_new_posts_app');
add_action('wp_ajax_nopriv_app_pass', 'io_ajax_new_posts_app');
add_action('wp_ajax_app_pay', 'io_ajax_new_posts_app');
add_action('wp_ajax_nopriv_app_pay', 'io_ajax_new_posts_app');





function io_ajax_get_app_down_btn(){
    $post_id   = esc_sql($_REQUEST['post_id']);
    $index     = (int)esc_sql($_REQUEST['id']);
    $down_list = io_get_app_down_by_index($post_id);
    $app_name  = get_post_meta($post_id, '_app_name', true)?:get_the_title($post_id); 
    $down      = $down_list[$index];
    $html      = io_get_modal_header_simple('', 'icon-down', $app_name . ' - ' . $down['app_version']);
    $html      .= '<div class="p-4">
    <div class="row">
        <div class="col-6 col-md-7">'.__('描述','i_theme').'</div>
        <div class="col-2 col-md-2" style="white-space: nowrap;">'.__('提取码','i_theme').'</div>
        <div class="col-4 col-md-3 text-right">'.__('下载','i_theme').'</div>
    </div>
    <div class="col-12 -line- my-2" style="height:1px;background: rgba(136, 136, 136, 0.4);"></div>';
    $list = '';
    if (!empty($down['down_url'])) {
        $i = 0;
        foreach ($down['down_url'] as $d) {
            $url = $d['down_btn_url'] == "" ? "javascript:" : $d['down_btn_url'];
            if (io_get_option('is_go', false) && !io_get_option('is_app_down_nogo', false)) {
                $url = go_to($url);
            }
            $target = $d['down_btn_url'] == "" ? '' : ' target="_blank"';
            $list .= '<div class="row">';
            $list .= '<div class="col-6 col-md-7">' . ($d['down_btn_info'] ?: __('无', 'i_theme')) . '</div>';
            $list .= '<div class="col-2 col-md-2" style="white-space: nowrap;">' . ($d['down_btn_tqm'] ?: __('无', 'i_theme')) . '</div>';
            $list .= '<div class="col-4 col-md-3 text-right"><a class="btn vc-l-theme py-0 px-1 mx-auto down_count copy-data text-sm" href="' . $url . '" ' . $target . ' data-clipboard-text="' . $d['down_btn_tqm'] . '" data-id="' . $post_id . '" data-action="down_count" data-mmid="down-mm-' . $i . '">' . $d['down_btn_name'] . '</a></div>';
            $list .= '</div>';
            $list .= '<div class="col-12 -line- my-2" style="height:1px;background: rgba(136, 136, 136, 0.2);"></div>';
            $i++;
        }
    }else{
        $list = '<div class="tips-box btn-block">'.__('没有内容','i_theme').'</div>';
    }
    $html .= '<div class="down_btn_list mb-4">';
    $html .= $list;
    $html .= '</div>';
    $html .= show_ad('ad_res_down_popup', false, 'd-none d-md-block', '<div class="apd-body">', '</div>', false);
    $html .= '<div class=" tips-box vc-l-blue text-left text-sm" role="alert"><i class="iconfont icon-statement mr-2" ></i><strong>' . __('声明：', 'i_theme') . '</strong>' . io_get_option('down_statement', '') . '</div>';
    $html .= '</div>';
    exit($html);
}
add_action('wp_ajax_nopriv_get_app_down_btn', 'io_ajax_get_app_down_btn');  
add_action('wp_ajax_get_app_down_btn', 'io_ajax_get_app_down_btn');