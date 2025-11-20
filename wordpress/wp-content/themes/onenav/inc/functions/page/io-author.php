<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-18 12:42:12
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-24 13:01:59
 * @FilePath: /onenav/inc/functions/page/io-author.php
 * @Description:  io_author_con_datas()
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function io_author_header()
{
    $author_id = get_query_var('author');
    $author    = get_user_by('ID', $author_id);
    $user_bg   = io_get_user_cover($author_id, "full");

    $content = '<div class="d-flex align-items-center w-100">';
    $content .= '<div class="avatar-img avatar-lg">';
    $content .= get_avatar($author_id, 70);
    $content .= '</div>';
    $content .= '<div class="author-meta overflow-hidden ml-2">';
    $content .= '<h1 class="h3 mb-2">' . $author->display_name . '</h1>';
    $content .= '<div class="text-sm line1">' . get_user_desc($author_id) . '</div>';
    $content .= '</div>';
    $content .= '</div>';

    $html = io_box_head_html('author', $user_bg, $content);
    echo $html;
}
/**
 * 获取用户内容
 * 文章列表、收藏、评论、关注、粉丝
 * @return mixed
 */
function io_author_content()
{

    $author_id  = get_query_var('author');
    $author_url = get_author_posts_url($author_id);

    $current_tab    = isset($_GET['tab']) ? $_GET['tab'] : 'post';
    $post_status    = isset($_GET['post_status']) ? $_GET['post_status'] : 'publish';
    $paged          = isset($_GET['page']) ? $_GET['page'] : 1;
    $star_post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';

    $tabs = array(
        'post'     => array(
            'icon'     => 'icon-post',
            'title'    => io_get_post_type_name('post'),
            'count'    => get_user_post_count($author_id, 'publish'),
            'columns'  => [1, 2],
            'style'    => 'min',
            'per_page' => 10,
        ),
        'sites'    => array(
            'icon'     => 'icon-sites',
            'title'    => io_get_post_type_name('sites'),
            'count'    => get_user_post_count($author_id, 'publish', 'sites'),
            'columns'  => [1, 3],
            'style'    => 'max',
            'per_page' => 12,
        ),
        'book'     => array(
            'icon'     => 'icon-book',
            'title'    => io_get_post_type_name('book'),
            'count'    => get_user_post_count($author_id, 'publish', 'book'),
            'columns'  => [2, 4, 6],
            'style'    => 'v',
            'per_page' => 18,
        ),
        'app'      => array(
            'icon'     => 'icon-app',
            'title'    => io_get_post_type_name('app'),
            'count'    => get_user_post_count($author_id, 'publish', 'app'),
            'columns'  => [1, 2, 3, 4],
            'style'    => 'max',
            'per_page' => 12,
        ),
        'comments' => array(
            'icon'     => 'icon-comment',
            'title'    => __('评论', 'i_theme'),
            'count'    => get_user_comment_count($author_id),
            'columns'  => [1, 2],
            'style'    => '',
            'per_page' => 10,
        ),
        'star'     => array(
            'icon'     => 'icon-collection-line',
            'title'    => __('收藏', 'i_theme'),
            'count'    => io_count_user_star_all_posts($author_id),
            'columns'  => [1, 2],
            'style'    => '',
            'per_page' => 10,
        )
    );

    $tabs_nav     = '';
    $tabs_body    = '';
    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post', 'comments', 'star']);
    foreach ($tabs as $key => $tab) {
        if(!is_author_can_see($key) || !in_array($key, $posts_type_s)){
            continue;
        }
        $active   = $current_tab == $key ? 'loaded active' : '';
        $style    = $key . '-' . $tab['style'];
        $columns  = io_get_author_post_columns($tab['columns']);
        $per_page = $tab['per_page'];

        if ('star' === $key) {
            // 收藏列表数据修改为对应文章类型相关数据
            $per_page = $tabs[$star_post_type]['per_page'];
            $columns  = io_get_author_post_columns($tabs[$star_post_type]['columns']);
            $style    = $star_post_type . '-' . $tabs[$star_post_type]['style'];
        }

        $tabs_nav .= '<a href="' . esc_url(add_query_arg('tab', $key, $author_url)) . '" class="btn-tab is-tab-btn ' . $active . '" ajax-tab ajax-route ajax-method="page" data-toggle="tab" data-target="#tab_' . $key . '" data-style="' . $style . '">';
        $tabs_nav .= '<i class="iconfont ' . $tab['icon'] . ' mr-1"></i>';
        $tabs_nav .= '<span>' . $tab['title'] . '</span>';
        $tabs_nav .= '<span class="badge ml-2">' . $tab['count'] . '</span>';
        $tabs_nav .= '</a>';

        $is_active = $current_tab == $key && in_array($key, array('post', 'sites', 'book', 'app'));
        $active   = $current_tab == $key ? 'active show' : '';

        $tabs_body .= '<div class="tab-pane fade ajax-load-page ' . $active . '" id="tab_' . $key . '">';
        if ($is_active) {
            // 文章内容
            $tabs_body .= io_author_posts_head($author_id, $key);
            $tabs_body .= io_get_author_posts($author_id, $key, $post_status, $paged, $per_page, array('columns' => $columns, 'style' => $style));
        } elseif ($current_tab == $key) {
            // 其他内容
            $tabs_body .= io_author_other_head($author_id, $key);

            $args = array(
                'columns' => $columns,
                'style'   => $style,
            );

            $tabs_body .= io_get_author_other($author_id, $key, '', $paged, $per_page, $args);
        } else {
            $tabs_body .= '<div class="row-a ajax-posts-row ' . $columns . '" data-style="' . $style . '"></div>';
        }
        $tabs_body .= '</div>';
    }
    $tabs_nav = '<div class="btn-tab-group nav overflow-x-auto no-scrollbar">' . $tabs_nav . '</div>';

    $tabs_body = '<div class="tab-content">' . $tabs_body . '</div>';


    $html = '<div class="tab-main">';
    $html .= '<div class="card mb-3">';
    $html .= '<div class="tab-header">';
    $html .= $tabs_nav;
    $html .= '</div>';
    $html .= '</div>';
    $html .= $tabs_body;
    $html .= '</div>';

    echo $html;
}

/**
 * 获取文章每列显示数量
 * @param mixed $columns
 * @return string
 */
function io_get_author_post_columns($columns)
{
    $rows  = array('', 'md-', 'lg-', 'xl-');
    $class = '';
    foreach ($columns as $index => $col) {
        $class .= ' row-col-' . $rows[$index] . $col . 'a';
    }
    return $class;
}

/**
 * 获取文章列表头部按钮
 * 
 * @param mixed $author_id
 * @param mixed $post_type
 * @return string
 */
function io_author_posts_head($author_id, $post_type = 'post')
{
    $author_url  = get_author_posts_url($author_id);
    $post_status = isset($_GET['post_status']) ? $_GET['post_status'] : 'publish';

    $data = array(
        'publish' => array(
            'title' => __('已发布', 'i_theme'),
            'count' => get_user_post_count($author_id, 'publish', $post_type),
            'link'  => add_query_arg(['tab' => $post_type, 'post_status' => 'publish'], $author_url),
            'color' => 'vc-j-blue',
        ),
        'draft'   => array(
            'title' => __('草稿', 'i_theme'),
            'count' => get_user_post_count($author_id, 'draft', $post_type),
            'link'  => add_query_arg(['tab' => $post_type, 'post_status' => 'draft'], $author_url),
            'color' => 'vc-j-green',
        ),
        'pending' => array(
            'title' => __('待审', 'i_theme'),
            'count' => get_user_post_count($author_id, 'pending', $post_type),
            'link'  => add_query_arg(['tab' => $post_type, 'post_status' => 'pending'], $author_url),
            'color' => 'vc-j-yellow',
        ),
    );
    $html = '<div class="mb-3">';
    foreach ($data as $key => $item) {
        if(!is_author_can_see($key)){
            continue;
        }
        $active = $post_status == $key ? 'active' : '';
        $html .= '<a href="' . esc_url($item['link']) . '" class="btn btn-tab-h btn-sm ajax-posts-load is-tab-btn mr-2 ' . $active . '" ajax-method="card">';
        $html .= '<span>' . $item['title'] . '</span>';
        $html .= '<span class="badge ' . $item['color'] . ' ml-2">' . $item['count'] . '</span>';
        $html .= '</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 获取其他用户头部信息
 * @param mixed $author_id
 * @param mixed $type
 * @return string
 */
function io_author_other_head($author_id, $type = 'star')
{
    $author_url      = get_author_posts_url($author_id);
    $post_type       = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
    $comments_status = isset($_GET['status']) ? $_GET['status'] : 'approve';

    $data = array(
        'star'     => array(
            'post'  => array(
                'title'  => io_get_post_type_name('post'),
                'count'  => io_count_user_star_posts($author_id, 'post'),
                'link'   => add_query_arg(['tab' => 'star', 'post_type' => 'post'], $author_url),
                'active' => 'post' === $post_type ? 'active' : '',
                'color'  => 'vc-j-gray',
            ),
            'sites' => array(
                'title'  => io_get_post_type_name('sites'),
                'count'  => io_count_user_star_posts($author_id, 'sites'),
                'link'   => add_query_arg(['tab' => 'star', 'post_type' => 'sites'], $author_url),
                'active' => 'sites' === $post_type ? 'active' : '',
                'color'  => 'vc-j-gray',
            ),
            'book'  => array(
                'title'  => io_get_post_type_name('book'),
                'count'  => io_count_user_star_posts($author_id, 'book'),
                'link'   => add_query_arg(['tab' => 'star', 'post_type' => 'book'], $author_url),
                'active' => 'book' === $post_type ? 'active' : '',
                'color'  => 'vc-j-gray',
            ),
            'app'   => array(
                'title'  => io_get_post_type_name('app'),
                'count'  => io_count_user_star_posts($author_id, 'app'),
                'link'   => add_query_arg(['tab' => 'star', 'post_type' => 'app'], $author_url),
                'active' => 'app' === $post_type ? 'active' : '',
                'color'  => 'vc-j-gray',
            ),
        ),
        'comments' => array(
            'approve' => array(
                'title'  => __('已通过', 'i_theme'),
                'count'  => get_user_comment_count($author_id, 'approve'),
                'link'   => add_query_arg(['tab' => 'comments', 'status' => 'approve'], $author_url),
                'active' => 'approve' === $comments_status ? 'active' : '',
                'color'  => 'vc-j-blue',
            ),
            'hold'    => array(
                'title'  => __('待审', 'i_theme'),
                'count'  => get_user_comment_count($author_id, 'hold'),
                'link'   => add_query_arg(['tab' => 'comments', 'status' => 'hold'], $author_url),
                'active' => 'hold' === $comments_status ? 'active' : '',
                'color'  => 'vc-j-yellow',
            ),
        )
    );

    if (!isset($data[$type])) {
        return '';
    }
    $tabs = $data[$type];

    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post']);

    $html = '<div class="mb-3">';
    foreach ($tabs as $key => $item) {
        if (!is_author_can_see($key) || ('star' === $type && !in_array($key, $posts_type_s))) {
            continue;
        }
        $html .= '<a href="' . esc_url($item['link']) . '" class="btn btn-tab-h btn-sm ajax-posts-load is-tab-btn mr-2 ' . $item['active'] . '" ajax-method="page" data-page="#tab_' . $type . '">';
        $html .= '<span>' . $item['title'] . '</span>';
        $html .= '<span class="badge ' . $item['color'] . ' ml-2">' . $item['count'] . '</span>';
        $html .= '</a>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * 用户可见的文章状态
 * @param  string $type 状态
 * @return string
 */
function is_author_can_see($type)
{
    if (is_super_admin()) {
        return true;
    }
    $can = ['draft', 'pending', 'hold', 'star'];
    if (!in_array($type, $can)) {
        return true;
    }
    
    $author_id = get_query_var('author');
    $user_id   = get_current_user_id();

    if ($author_id == $user_id) {
        return true;
    }
    return false;
}
/**
 * 获取用户文章列表
 * @param mixed $author_id 用户ID
 * @param mixed $type  类型，文章类型或者模块类型 post/sites/book/app star
 * @param mixed $post_status
 * @param mixed $paged
 * @param mixed $per_page
 * @param array $args  附加参数
 * @return string
 */
function io_get_author_posts($author_id, $type = 'post', $post_status = 'publish', $paged = 1, $per_page = 10, $args = array())
{
    $default = array(
        'columns'   => 'row-col-1a',
        'style'     => 'min',
        'post__in'  => array(), //文章ID数组
        'post_type' => '',  // star 类型传递文章类型
    );
    $args    = wp_parse_args($args, $default);

    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post']);
    if (
        (empty($args['post_type']) && !in_array($type, $posts_type_s)) || // 普通文章列表模式
        (!empty($args['post_type']) && !in_array($args['post_type'], $posts_type_s))// 收藏模式 stars
    ) {
        return '';
    }

    $post_args = array(
        'post_type'           => $type,
        'post_status'         => $post_status,
        'ignore_sticky_posts' => 1,
        'posts_per_page'      => $per_page,
        'paged'               => $paged,
    );
    if(!empty($args['post_type'])){
        $post_args['post_type'] = $args['post_type'];
    }
    if('star' === $type || !empty($args['post__in'])){
        $post_args['post__in'] = $args['post__in'];
    }else{
        $post_args['author'] = $author_id;
    }
    $query = new WP_Query($post_args);

    $card_args = array(
        'tag'    => 'div',
        'window' => true,
        'class'  => 'ajax-item'
    );

    $html = '';
    if ($query->have_posts()) {
        $posts = '';
        $_post_type = $args['post_type'] ?: $type;
        while ($query->have_posts()) {
            $query->the_post();

            switch ($_post_type) {
                case 'post':
                    $posts .= get_post_card('min', $card_args);
                    break;
                case 'app':
                    $posts .= get_app_card('max', $card_args);
                    break;
                case 'book':
                    $posts .= get_book_card(io_get_book_card_mode(), $card_args);
                    break;
                case 'sites':
                    $posts .= get_sites_card('max', $card_args);
                    break;
                default:
                    $posts .= get_post_card('min', $card_args);
                    break;
            }
        }
        wp_reset_postdata();

        $html .= '<div class="row-a ajax-posts-row ' . $args['columns'] . '" data-style="' . $args['style'] . '">';
        $html .= $posts;
        $html .= '</div>';
        
        $html .= '<div class="posts-nav mb-4">';
        if ($query->max_num_pages > 1 && $paged < $query->max_num_pages) {
            $html .= '<div class="next-page ajax-posts-load text-center my-3">';
            $arg  = array(
                'tab'  => $type,
                'page' => $paged + 1
            );
            if('publish' !== $post_status){
                $arg['post_status'] = $post_status;
            }
            if(!empty($args['post_type'])){
                $arg['post_type'] = $args['post_type'];
            }
            $link = add_query_arg($arg, get_author_posts_url($author_id));
            $html .= '<a href="' . esc_url($link) . '" class="">' . __('加载更多', 'i_theme') . '</a>';
        } else {
            $html .= '<div class="next-page text-center my-3">';
            $html .= '<a href="javascript:;" class="">' . __('没有更多了', 'i_theme') . '</a>';
        }
        $html .= '</div>';
        $html .= '</div>';
    } else {
        $html .= '<div class="row-a ajax-posts-row ' . $args['columns'] . '" data-style="' . $args['style'] . '">';
        $html .= get_none_html(__('暂无内容...', 'i_theme'), 'ajax-item');
        $html .= '</div>';
        $html .= '<div class="posts-nav mb-4">';
        $html .= '</div>';
    }

    return $html;
}

/**
 * 获取用户其他内容
 * 
 * 收藏、评论
 * @param mixed $author_id
 * @param mixed $type
 * @param mixed $post_status
 * @param mixed $paged
 * @param mixed $per_page
 * @param mixed $args
 * @return string
 */
function io_get_author_other($author_id, $type = 'star', $post_status = '', $paged = 1, $per_page = 10, $args = array())
{
    if ('star' === $type) {
        $post_status = 'publish';

        $post_type         = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
        $post_in           = io_get_user_star_post_ids($author_id, $post_type) ?: ['0'];
        $args['post__in']  = $post_in;
        $args['post_type'] = $post_type;
        return io_get_author_posts($author_id, 'star', $post_status, $paged, $per_page, $args);
    } elseif ('comments' === $type) {
        $post_status = isset($_GET['status']) ? $_GET['status'] : 'approve';
        return io_get_author_comm_list($author_id, $post_status, $paged, $per_page, $args);
    }
    return '';
}

/**
 * 获取用户评论列表
 * @param mixed $author_id 作者ID
 * @param mixed $comments_status 评论状态 approve|hold|spam|trash
 * @param mixed $paged 页码
 * @param mixed $per_page 每页数量
 * @param array $args 附加参数
 * @return string
 */
function io_get_author_comm_list($author_id, $comments_status = 'approve', $paged = 1, $per_page = 10, $args = array())
{
    $default = array(
        'columns' => 'row-col-1a',
        'style'   => 'min',
    );
    $args    = wp_parse_args($args, $default);

    $comment_args = array(
        'user_id' => $author_id,
        'status'  => $comments_status,
        'number'  => $per_page,
        'type'    => 'comment', // 排除 trackback 和 pingback
        'offset'  => ($paged - 1) * $per_page,
    );

    $comments = get_comments($comment_args);
    $html     = '';
    if ($comments) {
        $comment_list = '';
        foreach ($comments as $comment) {
            $comment_list .= get_comment_card('min', $comment);
        }

        $html .= '<div class="row-a ajax-posts-row ' . $args['columns'] . '" data-style="' . $args['style'] . '">';
        $html .= $comment_list;
        $html .= '</div>';

        $count_all   = get_user_comment_count($author_id, $comments_status);
        $total_pages = ceil($count_all / $per_page);

        $html .= '<div class="posts-nav mb-4">';
        if ($total_pages > $paged) {
            $html .= '<div class="next-page ajax-posts-load text-center my-3">';
            $link = add_query_arg(['tab' => 'comments', 'page' => $paged + 1], get_author_posts_url($author_id));
            $html .= '<a href="' . esc_url($link) . '" class="">' . __('加载更多', 'i_theme') . '</a>';
        } else {
            $html .= '<div class="next-page text-center my-3">';
            $html .= '<a href="javascript:;" class="">' . __('没有更多了', 'i_theme') . '</a>';
        }
        $html .= '</div>';
        $html .= '</div>';
    } else {
        $html .= '<div class="row-a ajax-posts-row ' . $args['columns'] . '" data-style="' . $args['style'] . '">';
        $html .= get_none_html(__('暂无评论...', 'i_theme'), 'ajax-item');
        $html .= '</div>';
        $html .= '<div class="posts-nav mb-4">';
        $html .= '</div>';
    }
    return $html;
}
