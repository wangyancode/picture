<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-08-26 16:23:17
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 19:05:44
 * @FilePath: /onenav/inc/functions/io-cards.php
 * @Description: 
 */

/**
 * 获取文章卡片
 * 
 * esc_attr(implode(' ', get_post_class($class)))
 * 
 * @param string $type 卡片类型 min、min-sm、min-xs、card、card2
 * @param array $args  array(
 *                         'class'  => '',
 *                         'window' => false,
 *                         'echo'   => false,
 *                         'no_img' => false,
 *                     )
 * @return mixed
 */
function get_post_card($type, $args = array()) {
    $default = array(
        'tag'        => 'article',
        'class'      => '',
        'window'     => false,
        'echo'       => false,
        'no_img'     => false,
        'meta'       => array()   
    );

    $args = wp_parse_args($args, $default);

    $post_id = get_the_ID();

    $_class  = 'posts-item post-item d-flex style-post-' . $type . ' post-' . $post_id;
    $class   = $args['class'] ? $_class . ' ' . $args['class'] : $_class;
    $link    = get_the_permalink();
    $title   = get_the_title();
    $stk_tag = get_sticky_tag();
    $new_tag = get_new_tag();
    $target  = $args['window'] ? 'target="_blank"' : new_window();
    $summary = 'min' === $type ? '<div class="line1 text-muted text-sm d-none d-md-block">' . io_get_excerpt(100) . '</div>' : '';

    $img = '';
    if($args['no_img']){
        $class .= ' no-img';
    }else{
        $img = '<div class="item-header">
            <div class="item-media">  
                <a class="item-image" href="' . $link . '" ' . $target . '>
                ' . get_lazy_img(io_theme_get_thumb(), $title,'auto','fill-cover') . '
                </a> 
            </div>
        </div>';
    }

    $html   = '<' . $args['tag'] . ' class="' . $class . '"> 
        ' . $img . '
        <div class="item-body d-flex flex-column flex-fill"> 
            <h3 class="item-title line2"> 
                <a href="' . $link . '" title="' . $title . '"' . $target . '>' . $stk_tag . $new_tag . $title . '</a>
            </h3>
            <div class="mt-auto">
            ' . $summary . '
            ' . ('min-sm' === $type ? '' : io_get_list_tags(io_get_option('post_keywords_meta', ['category', 'post_tag']))) . '
            ' . io_get_list_meta('post', '', $args['meta']) . '
            </div>
        </div>
    </' . $args['tag'] . '>'; 


    if($args['echo']){
        echo $html;
    }else{
        return $html;
    }
}

/**
 * 获取站点卡片
 * @param string $type 卡片类型 min、default、max
 * @param array $args  array(
 *                         'class'  => '',
 *                         'window' => false,
 *                         'echo'   => false,
 *                     )
 * @return mixed
 */
function get_sites_card($type, $args = array()) {
    $post_id = get_the_ID();
    $default = array(
        'tag'      => 'article',
        'class'    => '',
        'window'   => false,
        'echo'     => false,
        'go'       => io_get_option('global_goto'),  //是否直达 false强制详情页 true强制直达  全局设置
        'go_ico'   => io_get_option('togo_btn'),  //直达图标 
        'no_tip'   => 'null' === io_get_option('po_prompt', 'null'), //不显示提示 
        'nofollow' => !get_post_meta($post_id, '_nofollow', true),  //是否使用go跳转 true 强制添加nofollow  网址文章是否开启了nofollow
    );
    $args = wp_parse_args($args, $default);

    /**
     * @var mixed 是否开启go跳转功能
     */
    $is_go_switcher = io_get_option('is_go', true);

    /**
     * @var mixed 无图标模式
     */
    $no_ico = io_get_option('no_ico', false) ? ' no_ico' : '';
    $class = 'posts-item sites-item d-flex style-sites-' . $type . $no_ico . ' post-' . $post_id . io_sites_before_class($post_id);
    $class = $args['class'] ? $class . ' ' . $args['class'] : $class;

    $sites_meta = get_sites_card_meta();

    // 是否直达 go 为 true 或者 站点类型为站点 并且 有 _goto 元数据为 true
    $is_goto = ( $args['go'] ||($sites_meta['sites_type'] == 'sites' && get_post_meta($post_id, '_goto', true))) ? true : false;
    // 是否强制添加nofollow
    $is_nofollow = $args['nofollow'];

    $_link         = $sites_meta['link'];
    $link_target   = $args['window'] ? 'target="_blank"' : $sites_meta['blank'];
    $link_nofollow = '';

    $_go_url       = $is_go_switcher && $is_nofollow ? go_to($sites_meta['go_url']) : $sites_meta['go_url']; // 是否开启go跳转功能 并且 是否强制添加nofollow
    $goto_target   = 'target="_blank"';
    $goto_nofollow = $is_nofollow ? nofollow($sites_meta["go_url"], false, true) : '';

    $link = sprintf(
        'href="%s" %s %s',
        $is_goto ? $_go_url : $_link,
        $is_goto ? $goto_target : $link_target,
        $is_goto ? $goto_nofollow : $link_nofollow
    );
    $link_class = $is_goto ? 'is-views' : '';

    // go 图标按钮链接
    $goto_url   = sprintf(
        'href="%s" %s %s',
        $is_goto ? $_link : $_go_url,
        $is_goto ? $link_target : $goto_target,
        $is_goto ? $link_nofollow : $goto_nofollow
    );
    $goto_class = $is_goto ? '' : 'is-views';
    $goto_title = $is_goto ? __('详情', 'i_theme') : __('直达', 'i_theme');

    // 如果特定条件满足，调转链接
    if ( 
        $args['go'] && // 如果go为null 且启用了链接调转 
        !$is_goto &&  // 不是直达
        !empty($sites_meta['go_url'])  //目标地址不为空
    ) {
        $_go        = $link;
        $link       = $goto_url;
        $link_class = 'is-views';

        $goto_url   = $_go;
        $goto_title = __('详情', "i_theme");
        $goto_class = '';
        unset($_go);
    }

    $ico = '';
    if ('' === $no_ico || 'big' === $type) {
        $ico .= '<div class="item-header"><div class="item-media">';
        $ico .= '<div class="blur-img-bg lazy-bg" ' . get_lazy_img_bg($sites_meta['ico']) . '> </div>';
        $ico .= '<div class="item-image">';

        if ($sites_meta['first_api_ico']) {
            $ico .= get_lazy_img($sites_meta['ico'], $sites_meta['title'], 'auto', 'fill-cover sites-icon', $sites_meta['default_ico'], true, 'onerror=null;src=ioLetterAvatar(alt,60)');
        } else {
            $ico .= get_lazy_img($sites_meta['ico'], $sites_meta['title'], 'auto', 'fill-cover sites-icon', $sites_meta['default_ico']);
        }

        $ico .= '</div></div></div>';
    }

    /** 
     * 摘要
     */
    $summary = 'min' === $type ? '' : '<div class="line1 text-muted text-xs">' . $sites_meta['summary'] . '</div>';

    /**
     * 数据meta 阅读 评论 点赞
     */
    $like_meta = '';
    if ('max' === $type || 'big' === $type) {
        $like_meta .= '<div class="meta-ico text-muted text-xs">';
        $like_meta .= io_get_meta_tag();
        $like_meta .= '</div>';
    }

    /**
     * 直达按钮
     */
    $go_to = '';
    if ($args['go_ico'] && '' !== $sites_meta['go_url']) {
        if('big' === $type){
            $go_to .= '<a ' . $goto_url . ' class="big-togo text-center text-muted ' . $goto_class . '" data-id="' . $post_id . '">' . $goto_title . '</a>';
        }else{
            $go_to .= '<a ' . $goto_url . ' class="togo ml-auto text-center text-muted ' . $goto_class . '" data-id="' . $post_id . '" data-toggle="tooltip" data-placement="right"  title="' . $goto_title . '"><i class="iconfont icon-goto"></i></a>';
        }   
    }else{
        $class .= ' no-go-ico';
    }

    $tooltip = $args['no_tip'] ? '' : $sites_meta['tooltip'] . ' ' . $sites_meta['is_html'] . ' title="' . esc_attr($sites_meta['tip_title']) . '"';

    $stk_tag = get_sticky_tag();
    $new_tag = get_new_tag();

    /**
     * BIG模式背景
     */
    $big_bg = '';
    if('big' === $type){
        if (empty($sites_meta['is_html'])) {
            $tooltip = '';
        }
        $class .= io_get_option('sites_dominant_color') ? ' big-posts' : '';
        $big_bg .= '<div class="big-bg"><div class="big-img">';
        $big_bg .= get_lazy_img($sites_meta['preview'], $sites_meta['title'], 'auto', 'big-bg-cover big-color', '', '', 'crossorigin="anonymous" style="--this-bg:url(' . $sites_meta['preview'] . ')"');
        $big_bg .= get_lazy_img($sites_meta['preview'], $sites_meta['title'], 'auto', 'big-bg-cover bg-reflect', '');
        $big_bg .= '</div></div>';
    }

    $html = '<' . $args['tag'] . ' class="' . $class . '" ' . $tooltip . '>
                ' . $big_bg . '
                ' . ($big_bg ? '<div class="big-meta">' : '') . '
                <a ' . $link . ' data-id="' . $post_id . '" data-url="' . esc_attr(rtrim($sites_meta['go_url'], "/")) . '" class="sites-body ' . esc_attr($link_class) . '" title="' . esc_attr($sites_meta['title']) . '">
                    ' . $ico . '
                    <div class="item-body overflow-hidden d-flex flex-column flex-fill">
                        <h3 class="item-title line1">' . $stk_tag . $new_tag . '<b>' . $sites_meta['title'] . '</b></h3>
                        ' . $summary . '
                    </div>
                </a> 
                    ' . $like_meta . '
                <div class="sites-tags">
                    ' . (('max' === $type || 'big' === $type) ? io_get_list_tags(io_get_option('sites_keywords_meta',['favorites', 'sitetag'])) : '') . '
                    ' . $go_to . '
                </div>
                ' . ($big_bg ? '</div>' : '') . '
            </' . $args['tag'] . '>';

    if ($args['echo']) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 获取应用卡片
 * @param string $type 卡片类型 card、 default、 max
 * @param array $args array(
 *                         'class'  => '',
 *                         'window' => false,
 *                         'echo'   => false,
 *                         'no_img' => false,
 *                     )
 * @return mixed
 */
function get_app_card($type, $args = array()) {
    $default = array(
        'tag'    => 'article',
        'class'  => '',
        'window' => false,
        'echo'   => false,
        'meta'   => array()
    );
    $args = wp_parse_args($args, $default);
    
    $post_id  = get_the_ID();
    $ico_info = get_post_meta($post_id, 'app_ico_o', true);

    $_class = 'posts-item app-item d-flex style-app-' . $type . ' post-' . $post_id;
    $class  = $args['class'] ? $_class . ' ' . $args['class'] : $_class;

    $target  = $args['window'] ? 'target="_blank"' : new_window();
    $link    = get_the_permalink();
    $title   = get_the_title();
    $stk_tag = get_sticky_tag();
    $new_tag = get_new_tag();
    $summary = get_post_meta($post_id, '_app_sescribe', true);
    $history = get_post_meta($post_id, 'app_down_list', true);
    $version = $history ? $history[0]['app_version'] : '';

    $bg   = '';
    $size = '';
    if ($ico_info && $ico_info['ico_a']) {
        $bg   = 'style="background-image: linear-gradient(130deg, ' . $ico_info['ico_color']['color-1'] . ', ' . $ico_info['ico_color']['color-2'] . ');"';
        $size = 'transform: scale(' . $ico_info["ico_size"] . '%)';//'background-size: ' . $ico_info["ico_size"] . '%';
    }

    $like_meta = '';
    if ('card' === $type || 'max' === $type) {
        $like_meta .= '<div class="meta-ico text-muted text-xs">';
        $like_meta .= io_get_meta_tag('app', $args['meta']);
        $like_meta .= '</div>';
    }

    $platform = '';
    if ('max' === $type && $app_platform = get_post_meta($post_id, '_app_platform', true)) {
        $platform = '<div class="app-platform text-muted text-sm mb-n1">';
        foreach ($app_platform as $pl) {
            $platform .= '<i class="iconfont ' . $pl . '" data-toggle="tooltip" title="' . get_app_platform($pl) . '"></i>';
        }
        $platform .= '</div>';
    }

    $html = '<' . $args['tag'] . ' class="' . $class . '"> 
        <div class="item-header">
            <div class="item-media" ' . $bg . '>
                <a class="item-image" href="' . $link . '" ' . $target . '  style="' . $size . '">
                ' . get_lazy_img(get_post_meta_img($post_id, '_app_ico', true), $title, 'auto', 'fill-cover') . '
                </a>
            </div>
        </div>
        <div class="item-body overflow-hidden d-flex flex-column flex-fill">
            <h3 class="item-title line1">
                <a href="' . $link . '" ' . $target . '>' . $stk_tag . $new_tag . $title . '' . '<span class="app-v text-xs"> - ' . $version . '</span>' . '</a>
            </h3>
            <div class="app-content mt-auto"> 
                <div class="text-muted text-xs line1">' . $summary . '</div>
                <div class="app-meta d-flex align-items-center">
                    ' . ('max' === $type ? io_get_list_tags(io_get_option('app_keywords_meta', ['apps', 'apptag'])) : '') . '
                    ' . $like_meta . ' 
                </div>
                ' . $platform . ' 
            </div>
        </div>
    </' . $args['tag'] . '>';

    if ($args['echo']) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 获取书籍卡片
 * @param string $type 卡片类型  v 竖、 h 横
 * @param array $args array(
 *                         'class'  => '',
 *                         'window' => false,
 *                         'echo'   => false,
 *                         'no_img' => false,
 *                     )
 * @return mixed
 */
function get_book_card($type, $args = array()){
    $default = array(
        'tag'    => 'article',
        'class'  => '',
        'window' => false,
        'echo'   => false
    );
    $args = wp_parse_args($args, $default);

    $post_id = get_the_ID();

    $_class = 'posts-item book-item d-flex style-book-' . $type . ' post-' . $post_id;
    $class  = $args['class'] ? $_class . ' ' . $args['class'] : $_class;

    $target  = $args['window'] ? 'target="_blank"' : new_window();
    $link    = get_the_permalink();
    $title   = get_the_title();
    $stk_tag = get_sticky_tag();
    $new_tag = get_new_tag();
    $summary = get_post_meta($post_id, '_summary', true);

    $html = '<' . $args['tag'] . ' class="' . $class . '">
            <div class="item-header">
                <div class="item-media">
                    <a class="item-image" href="' . $link . '" ' . $target . '>
                    ' . get_lazy_img(get_post_meta_img($post_id, '_thumbnail', true), $title, 'auto', 'fill-cover') . '
                    </a>
                </div>
            </div>
            <div class="item-body flex-fill"> 
                <h3 class="item-title line1">
                    <a href="' . $link . '" ' . $target . '>' . $stk_tag . $new_tag . $title . '</a>
                </h3>
                <div class="line1 text-muted text-xs mt-1">
                    ' . $summary . '
                </div> 
            </div>
        </' . $args['tag'] . '>';

    if ($args['echo']) {
        echo $html;
    } else {
        return $html;
    }
}


/**
 * 获取只有标题的卡片
 * 
 * @param int $index
 * @return string
 */
function get_only_title_card($instance, $index)
{
    $new_window = is_new_window($instance);
    $title      = get_the_title();
    $stk_tag    = get_sticky_tag();
    $new_tag    = get_new_tag();

    $target = $new_window ? 'target="_blank"' : new_window();
    if (!empty($index)) {
        $colors = array('vc-l-red', 'vc-l-yellow', 'vc-l-purple');
        $color  = isset($colors[$index - 1]) ? $colors[$index - 1] : 'vc-l-gray';
        $index  = '<span class="badge badge-index ' . $color . ' mr-1">' . $index . '</span>';
    } else {
        $index = '';
    }

    $html = '<div class="card-title text-sm line1">';
    $html .= $index . $stk_tag . $new_tag;
    $html .= '<a href="' . get_the_permalink() . '" ' . $target . ' title="' . esc_attr($title) . '">' . $title . '</a>';
    $html .= '</div>';

    return $html;
}

/**
 * 获取评论卡片
 * 
 * @param string $type
 * @param WP_Comment $comment 
 * @return string
 */
function get_comment_card($type, $comment)
{
    $cont       = get_comment_text($comment->comment_ID);
    $link       = get_comment_link($comment->comment_ID);
    $post_title = get_the_title($comment->comment_post_ID);
    $post_link  = get_the_permalink($comment->comment_post_ID);
    $time = $comment->comment_date;

    $approved = '';
    if ('0' == $comment->comment_approved) {
        $approved = '<span class="badge vc-red ml-2">' . __('待审核', 'i_theme') . '</span>';
    }

    $parent = '';
    if ($comment->comment_parent) {
        $parent = '<span class="badge vc-j-yellow ml-1" >@ ' . get_comment_author($comment->comment_parent) . '</span>';
    }

    $html = '<div class="comment-item ajax-item style-comment-' . $type . '">
        <div class="comment-time text-xs mb-1">
            <span class="text-muted">' . $time . '</span>
            ' . $parent . $approved . '
        </div>
        <div class="comment-link line2">
            <a href="' . $link . '" target="_blank" title="' . $cont . '">' . $cont . '</a>
        </div>
        <div class="post-link text-xs line1 mt-3">
            ' . __('评论于：', 'i_theme') . ' <a href="' . $post_link . '" target="_blank" title="' . $post_title . '">' . $post_title . '</a>
        </div>
    </div>';

    return $html;
}


function remove_comment_pagination_from_link($link, $comment, $args)
{
    // 使用正则表达式去除评论分页部分，比如 /comment-page-2#comment-123
    $link = preg_replace('/\/comment-page-[0-9]+/', '', $link);

    return $link;
}
add_filter( 'get_comment_link', 'remove_comment_pagination_from_link', 10, 3 );
