<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-27 15:42:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 22:27:20
 * @FilePath: /onenav/inc/action/ajax-book.php
 * @Description: 
 */

/**
 * 提交网址
 * @return never
 */
function io_ajax_new_posts_book()
{
    io_ajax_new_posts_safety_verify();

    //表单变量初始化
    $title    = __post('post_title', '');
    $content  = isset($_POST['post_content']) ? $_POST['post_content'] : false;
    $category = __post('category', false);
    $keywords = io_split_str(__post('tags', ''));
    $action   = __post('action', '');
    $posts_id = __post('posts_id', 0);

    $describe   = __post('describe', '');
    $book_cover = __post('cover-img', '');
    $book_type  = __post('internal_type', '');
    $score      = __post('score');
    $score_type = __post('score_type', '');
    $journal    = __post('journal', '');
    $meta_data  = __post('meta_data', '');
    $down_list  = __post('down_list', '');
    $buy_list   = __post('buy_list', '');



    $option     = io_get_option('book_tg_config');
    $desc_limit = $option['desc_limit'] ?: 80;
    $u_id       = get_current_user_id();


    // 验证文章基本信息
    io_ajax_new_posts_data_basic_verify('book', compact('title', 'content', 'category', 'keywords', 'u_id', 'book_type'));

    // meta信息验证
    if ($book_type === 'periodical' && empty($journal)) {
        io_error('{"status":3,"msg":"' . __('请选择期刊类型！', 'i_theme') . '"}');
    }

    if (empty($score) && '0' != $score) {
        io_error('{"status":3,"msg":"' . __('请填写评分，或者填 0 ！', 'i_theme') . '"}');
    } else if ($score && empty($score_type)) {
        io_error('{"status":3,"msg":"' . __('请填写评分的来源平台！', 'i_theme') . '"}');
    }



    if (empty($describe)) {
        io_error('{"status":4,"msg":"' . __('简介必须填写！', 'i_theme') . '"}');
    }
    if (mb_strlen($describe) > $desc_limit) {
        io_error('{"status":4,"msg":"' . sprintf(__('简介长度不得超过%s字。', 'i_theme'), $desc_limit) . '"}');
    }

    // 判断 $meta_data 数组里是否有空值
    if (is_array($meta_data)) {
        foreach ($meta_data as $key => $value) {
            if (empty($value['term']) || empty($value['detail'])) {
                io_error('{"status":4,"msg":"' . __('有元数据的内容没有填写完整！', 'i_theme') . '"}');
            }
        }
    }
    // 判断 $down_list 数组里的 name 和 url 是否有空值
    if (is_array($down_list)) {
        foreach ($down_list as $key => $value) {
            if (empty($value['name']) || empty($value['url'])) {
                io_error('{"status":4,"msg":"' . __('下载链接的名称和链接不能为空！', 'i_theme') . '"}');
            }
        }
    }
    // 判断 $buy_list 数组里的 term 和 url 是否有空值
    if (is_array($buy_list)) {
        foreach ($buy_list as $key => $value) {
            if (empty($value['term']) || empty($value['url'])) {
                io_error('{"status":4,"msg":"' . __('渠道的名称和链接不能为空！', 'i_theme') . '"}');
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
    $is_publish = io_ajax_get_is_publish('book', $u_id, $edit_posts, $category);


    //文章状态
    $post_status = 'pending';
    if ('book_draft' === $action) {
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
        'post_type'      => 'book',
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
        'books'   => $category,
        'booktag' => $keywords
    );

    $meta = array(
        '_book_type'  => $book_type,
        '_summary'    => $describe,
        '_thumbnail'  => $book_cover,
        '_score_type' => $score_type,
        '_score'      => $score,
        '_journal'    => $journal,
        '_books_data' => $meta_data,
        '_down_list'  => $down_list,
        '_buy_list'   => $buy_list,
    );
    if(!$u_id){
        $meta['guest_info'] = __post('guest_info');
        $meta['guest_info']['time'] = date('Y-m-d H:i:s');
    }

    io_ajax_insert_new_posts($post_data, $taxonomy, $meta, isset($edit_posts['post_status']) ? $edit_posts['post_status'] : '');
    exit;
}
add_action('wp_ajax_book_save', 'io_ajax_new_posts_book');
add_action('wp_ajax_nopriv_book_save', 'io_ajax_new_posts_book');
add_action('wp_ajax_book_draft', 'io_ajax_new_posts_book');
add_action('wp_ajax_nopriv_book_draft', 'io_ajax_new_posts_book');
add_action('wp_ajax_book_pass', 'io_ajax_new_posts_book');
add_action('wp_ajax_nopriv_book_pass', 'io_ajax_new_posts_book');
add_action('wp_ajax_book_pay', 'io_ajax_new_posts_book');
add_action('wp_ajax_nopriv_book_pay', 'io_ajax_new_posts_book');
