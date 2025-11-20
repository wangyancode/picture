<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-11 01:49:11
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-05 15:30:43
 * @FilePath: /onenav/inc/functions/io-single-book.php
 * @Description: 
 */

/**
 * book 头部
 * @param mixed $is_hide
 * @return string
 */
function io_book_header(&$is_hide){
    global $post, $down_list;
    $post_id = $post->ID; 
    $level_d = get_user_level_directions_html('book');
    if($level_d){
        $is_hide = true;
        return $level_d;
    }
    $is_hide = false;

    $down_list  = get_post_meta($post_id, '_down_list', true);  
    $imgurl     = get_post_meta_img($post_id, '_thumbnail', true);
    $booktitle  = get_the_title();

    $ad = '';
    if(io_get_option('book_top_right','') && !wp_is_mobile()){
        $ad .= '<div class="book-top-right ml-0 ml-md-2 mt-3 mt-md-0">';
        $ad .= io_get_option('book_top_right',true);
        $ad .= '</div>';
    }

    $html = '<div class="site-content d-flex flex-column flex-md-row-reverse mb-4 mb-md-5">';
    $html .= '<!-- book信息 -->';
    $html .= '<div class="book-info flex-fill">';
    $html .= io_book_header_info( $booktitle );
    $html .= $ad;
    $html .= '</div>';
    $html .= '<!-- book信息 END -->';
    $html .= '<!-- book封面 -->';
    $html .= '<div class="book-cover text-center mr-0 mr-md-3 mt-4 mt-md-0">';
    $html .= get_lazy_img($imgurl, $booktitle, 'auto');
    $html .= '</div>';
    $html .= '<!-- book封面 END -->';
    $html .= '</div>';

    return $html;
}
/**
 * book 正文
 * @return void
 */
function io_book_content(){ 
    global $post;
    while (have_posts()): the_post();
    $post_id = get_the_ID();
    
    do_action('io_single_content_before', $post_id, 'book');
    ?>
    <div class="panel site-content card"> 
        <div class="card-body">
            <?php show_ad('ad_post_content_top', false) ?>
            <div class="panel-body single">
            <?php 
            do_action('io_single_before', 'book');
            the_content();
            thePostPage();
            do_action('io_single_after', 'book');
            ?>
            </div>
            <?php show_ad('ad_post_content_bottom', false); ?>
        </div>
    </div>
    <?php
    //io_book_content_down(get_the_title());
    do_action('io_single_content_after', $post_id, 'book');
    endwhile;
}


/**
 * 下载资源模态框
 * @param mixed $name
 * @return void
 */
function io_book_content_down(){
    global $post , $down_list, $is_pay;
    if($is_pay || !$down_list){
        return;
    }
    $title  = __('下载地址: ', 'i_theme') . get_the_title();
    echo io_get_down_modal($title, $down_list, 'book', '', 10);
}
add_action('wp_footer', 'io_book_content_down');

/**
 * 期刊名字
 * @param mixed $type
 * @return string|string[]
 */
function io_get_journal_name($type = '')
{
    $data = array(
        '12' => __('周刊', 'i_theme'),
        '9'  => __('旬刊', 'i_theme'),
        '6'  => __('半月刊', 'i_theme'),
        '3'  => __('月刊', 'i_theme'),
        '2'  => __('双月刊', 'i_theme'),
        '1'  => __('季刊', 'i_theme'),
    );

    if (empty($type)) {
        return $data;
    }

    return isset($data[$type]) ? $data[$type] : '';
}

/**
 * 期刊名字徽章
 * @return string
 */
function io_get_journal_name_badge()
{
    global $post, $book_type;

    $journal = '';
    if ($book_type == 'periodical') {
        $journal = '<span class="badge vc-purple text-xs font-weight-normal ml-2 journal">' . io_get_journal_name(get_post_meta($post->ID, '_journal', true)) . '</span>';
    }
    return $journal;
}

/**
 * book 头部信息
 * @param mixed $imgurl
 * @param mixed $booktitle
 * @return string
 */
function io_book_header_info( $booktitle ){
    global $post, $down_list, $is_pay;
    $post_id = $post->ID;
    $is_pay  = false;

    $list = '';
    if ($books_data = get_post_meta(get_the_ID(), '_books_data', true)) {
        foreach ($books_data as $value) {
            $list .= '<div class="table-row">';
            $list .= '<div class="table-title">' . $value['term'] . '</div><div class="table-value">' . $value['detail'] . '</div>';
            $list .= '</div>';
        }

        $_c   = io_get_post_tags($post_id, array('books', 'booktag'));
        if (!empty($_c)) {
            $list .= '<div class="table-row">';
            $list .= '<div class="table-title">' . __('标签', 'i_theme') . '</div><div class="table-value">' . $_c . '</div>';
            $list .= '</div>';
        }

        $_t   = io_get_post_tags($post_id, array('series'));
        if (!empty($_t)) {
            $list .= '<div class="table-row">';
            $list .= '<div class="table-title">' . __('系列', 'i_theme') . '</div><div class="table-value">' . $_t . '</div>';
            $list .= '</div>';
        }

        $list = '<div class="table-div">' . $list . '</div>';
    }

    $html = '<div class="site-body text-sm">';
    $html .= get_single_breadcrumb('book', 'mb-3 mb-md-1 text-muted');
    $html .= '<div class="d-flex flex-wrap mb-4">';
    $html .= '<div class="site-name-box flex-fill mb-3">';
    $html .= '<h1 class="site-name h3 mb-3">' . $booktitle . io_get_journal_name_badge();
    $html .= io_get_post_edit_link($post_id);
    $html .= '</h1>';
    
    $html .= get_post_meta_small(false);
    $html .= '</div>';
    
    $html .= '<div class="posts-like">'.get_posts_star_btn($post_id, 'btn vc-l-red text-md py-1', true).'</div>';
    $html .= '</div>';

    $html .= '<div class="mt-n2">';
    $html .= '<p>' . io_get_excerpt(170, '_summary') . '</p>';
    $html .= '<div class="book-info text-sm text-muted">';
    $html .= $list;
    $html .= '</div>';
    $html .= '<div class="site-go mt-3">';
    if ($buy_list = get_post_meta(get_the_ID(), '_buy_list', true)) {
        foreach ($buy_list as $value) {
            if ($value['price']) {
                $html .= '<a target="_blank" href="' . go_to($value['url']) . '" class="btn vc-theme btn-i-r btn-price-a mr-2" data-toggle="tooltip" data-placement="top" title="' . $value['term'] . '"><span class="b-name">' . $value['term'] . '</span><span class="b-price">' . ($value['price'] ?: '0') . '</span><i class="iconfont icon-buy_car"></i></a>';
            } else {
                $html .= '<a target="_blank" href="' . go_to($value['url']) . '" class="btn vc-l-theme btn-i-r mr-2" title="' . $value['term'] . '"><span class="b-name">' . $value['term'] . '</span><i class="iconfont icon-arrow-r"></i></a>';
            }
        }
    }
    if ($down_list) {
        $html .= io_book_down_btn($is_pay);
    }
    $html .= $is_pay ? iopay_pay_tips_box() : '';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function io_book_down_btn(&$is_pay){
    global $post, $down_list;
    $post_id = $post->ID;
    $name    = __('下载资源', 'i_theme');
    $icon    = '<i class="iconfont icon-down"></i>';
    $btn     = '<a href="javascript:" class="btn vc-l-blue btn-i-r mr-2"  title="' . $name . '" data-id="0" data-toggle="modal" data-target="#book-down-modal"><span>' . $name . $icon . '</span></a>';

    $user_level = get_post_meta($post_id, '_user_purview_level', true);
    if (!$user_level) {
        update_post_meta($post_id, '_user_purview_level', 'all');
        return $btn;
    }
    // new 
    $user_level = io_get_post_visibility_slug($post_id);
    if($user_level === 'all'){
        return $btn;
    }


    if ($user_level && 'buy' === $user_level) {
        $buy_option = get_post_meta($post_id, 'buy_option', true);
    }
    if (isset($buy_option)) {
        if ('annex' === $buy_option['buy_type']) { // 附件模式
            $is_buy = iopay_is_buy($post_id, 0, 'app');
        }
    }
    if (isset($is_buy) && !$is_buy) {
        $name           = __('立即购买', 'i_theme');
        $pay_price      = $buy_option['pay_price'];
        $original_price = $buy_option['price'];
        $unit           = '<span class="text-xs">' . io_get_option('pay_unit', '￥') . '</span>';
        $icon           = '<i class="iconfont icon-buy_car mr-2"></i>';

        $is_pay         = true;
        $original_price = $original_price && $original_price > $pay_price ? '<div class="original-price d-inline-block text-xs">' . $unit . $original_price . '</div>' : '';
        $btn            = apply_filters('iopay_buy_btn_before', 'book', $buy_option, array('price' => ' ' . $unit . $pay_price . $original_price));
        if (empty($btn)) {
            $url = esc_url(add_query_arg(array('action' => 'pay_cashier_modal', 'id' => $post_id, 'index' => 0), admin_url('admin-ajax.php')));
            $btn = '<a href="' . $url . '" class="btn vc-blue btn-i-l io-ajax-modal-get nofx"  title="' . $name . '">' . $icon . $name . ' ' . $unit . $pay_price . $original_price . '</a>';
        }
    }
    return $btn;
}

/**
 * 获取图书类型名称
 * 
 * @param mixed $type
 * @return string|string[]
 */
function get_book_type_name($type = '')
{
    $data = array(
        "books"      => __('图书', 'i_theme'),
        "periodical" => __('期刊', 'i_theme'),
        "movie"      => __('电影', 'i_theme'),
        "tv"         => __('电视剧', 'i_theme'),
        "video"      => __('小视频', 'i_theme'),
    );
    if (isset($data[$type])) {
        return $data[$type];
    }
    return $data;
}
