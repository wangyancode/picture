<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-10 17:02:19
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 13:23:34
 * @FilePath: /onenav/inc/functions/page/io-rankings.php
 * @Description: 
 */


/**
 * 获取排行榜文章列表
 * @param mixed $instance
 * @return string
 */
function io_get_rankings_posts_html($instance)
{
    $default = array(
        'post_type'   => 'sites',
        'range'       => 'today',
        'count'       => 6,
        'window'      => true,
        'only_title'  => false,
        'serial'      => true,
        'show_thumbs' => true,
        'go'          => false,
        'nofollow'    => false,
    );
    $instance = wp_parse_args($instance, $default);

    $post_type = $instance['post_type'];

    $styles = array(
        'post'  => 'min-sm',
        'sites' => 'default',
        'app'   => 'card',
        'book'  => io_get_book_card_mode('card'),
    );

    $instance['style'] = $instance['only_title'] ? 'title' : $styles[$post_type];

    $html    = '';
    $myposts = io_get_post_rankings($instance['range'], $post_type, $instance['count'], '', true);
    if ($myposts && $myposts->have_posts()) {
        switch ($post_type) {
            case 'post':
                $html .= load_widgets_min_post_html($myposts, $instance);
                break;
            case 'app':
                $html .= load_widgets_min_app_html($myposts, $instance);
                break;
            case 'book':
                $html .= load_widgets_min_book_html($myposts, $instance);
                break;
            case 'sites':
            default:
                $html .= load_widgets_min_sites_html($myposts, $instance);
                break;
        }
        wp_reset_postdata();
    } else {
        $html .= get_none_html();
    }
    return $html;
}

/**
 * 获取排行榜排序规则类型名称
 * 
 * @param mixed $type 类型, 非空时返回对应的名称
 * @return string|string[]
 */
function get_ranking_range($type = '')
{
    $data = array(
        'today'     => __('日榜', 'i_theme'),
        'yesterday' => __('昨日', 'i_theme'),
        'week'      => __('周榜', 'i_theme'),
        'last_week' => __('上周', 'i_theme'),
        'month'     => __('月榜', 'i_theme'),
        'all'       => __('总榜', 'i_theme'),
    );
    if (empty($type)) {
        return $data;
    }
    return isset($data[$type]) ? $data[$type] : '';
}
/**
 * 获取排行榜数据类型名称
 * 
 * @param mixed $type
 * @return mixed
 */
function get_ranking_lists($type = ''){
    $data = array(
        'sites' => sprintf(__('%s排行榜', 'i_theme'), io_get_post_type_name('sites')),
        'post'  => sprintf(__('%s排行榜', 'i_theme'), io_get_post_type_name('post')),
        'book'  => sprintf(__('%s排行榜', 'i_theme'), io_get_post_type_name('book')),
        'app'   => sprintf(__('%s排行榜', 'i_theme'), io_get_post_type_name('app')),
    );
    /**
     * 排行榜数据
     * 
     * @var array $data
     */
    $data = apply_filters('io_get_ranking_lists', $data);

    if (empty($type)) {
        return $data;
    }
    return isset($data[$type]) ? $data[$type] : '';
}


function io_get_rankings_head_html()
{
    io_get_color_head_html('rankings', io_get_rankings_head_content_html());
}

function io_get_rankings_head_content_html()
{
    $list  = (array) io_get_option('ranking_show_list', '');
    $range = (array) io_get_option('ranking_range', '');

    if(count($list) === 2){
        $list[] = 'none';
    }

    $post_type = __post('type') ?: $list[0];
    $range_key = __post('range') ?: $range[0];

    $_list = rotate_array($list, $post_type);
    $length = count($_list);
    $btn = '';
    foreach ($_list as $index => $key) {
        if('none' === $key){
            if($index===2){
                $key = $_list[1];
            }else{
                $key = $_list[2];
            }
        }
        
        if ($index === 0) {
            $class = 'active';
        } else {
            $position = $index; 
            if ($index <= ($length - 1) / 2) {
                $class = '--r' . $position;
            } else {
                $class = '--l' . ($length - $index);
            }
        }
        $link   = get_permalink();
        $link   = add_query_arg(array(
            'type'   => $key,
            'range'  => $range[0],
        ), $link);
        $btn .= '<a href="' . esc_url($link) . '" class="ajax-posts-load range-tab-btn ' . $class . '" ajax-method="page">' . get_ranking_lists($key) . '</a>';
    }
    $btn = $btn ? '<div class="range-nav position-relative text-md">' . $btn . '</div>' : '';

    $title = sprintf(__('%s热度%s', 'i_theme'), io_get_post_type_name($post_type), get_ranking_range($range_key));
    //echo determine_locale();
    if (explode('_', determine_locale())[0] === 'zh' && mb_substr($title, -1) != '榜') {
        $title .= '榜';
    }

    $days = array(
        'today'     => __('今天', 'i_theme'),
        'yesterday' => __('昨天', 'i_theme'),
        'week'      => __('本周', 'i_theme'),
        'last_week' => __('上周', 'i_theme'),
        'month'     => __('本月', 'i_theme'),
        'all'       => __('总榜', 'i_theme'),
    );
    $desc = sprintf(__('根据%s浏览量降序排列', 'i_theme'), $days[$range_key]);

    $html = $btn;
    $html .= '<h1 class="ranking-title h2">'.$title.'</h1>';
    $html .= '<div class="ranking-desc text-xs">';
    $html .= $desc;
    $html .= '</div>';

    return $html;
}

function io_get_rankings_content_html()
{
    $list  = (array) io_get_option('ranking_show_list', '');
    $range = (array) io_get_option('ranking_range', '');


    $styles = array(
        'post'  => 'min-sm',
        'sites' => 'default',
        'app'   => 'card',
        'book'  => io_get_book_card_mode('card'),
    );

    $post_type = __post('type') ?: $list[0];
    $range_key = __post('range') ?: $range[0];
    $style     = $post_type . '-' . ($styles[$post_type] ?: 'min-sm');

    $btn = '';
    foreach ($range as $key) {
        // 获取当前页面链接
        $link   = get_permalink();
        $link   = add_query_arg(array(
            'type'   => $post_type,
            'range'  => $key,
        ), $link);
        $active = $key == $range_key ? ' active' : '';
        $btn .= '<a href="' . esc_url($link) . '" class="ajax-posts-load is-tab-btn range-btn ' . $active . '" ajax-method="page">' . get_ranking_range($key) . '</a>';
    }
    
    $btn = '<div class="ranking-range-body">' . $btn . '</div>';

    $list = io_get_rankings_page_list($post_type, $range_key);

    $html = '<div class="ranking-panel d-flex flex-column flex-md-row">';
    $html .= '<div class="ranking-range-nav">';
    $html .= $btn;
    $html .= '</div>';
    $html .= '<div class="card position-relative flex-fill">';
    $html .= '<div class="ranking-h-ico"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><g opacity=".7" fill-rule="evenodd" clip-rule="evenodd" fill="#FFF"><path opacity=".6" d="M51.2 401l1.3-9.1 1.7-7.3 2.4-6 2.6-4.7 2.9-3.3 3.2-2.5 3.5-1.5 7.3-.5 4.7.9 5.5 1.8 6.9 3.2 7.9 4.4 8.9 6.1 17.4 14.8 9.5 9.6 8.9 10 9.1 11.2 9.2 12.6 8.6 13.2 8.2 14.3 8 15.3 6.9 15.8 6.4 16.9 5.9 17.8 4.6 18.2 3.8 17.6 2.7 17.1 1.9 17.1 1.1 16.2.2 15.2-1.5 29.5-3.9 25.9-5.7 21-3.7 9.6-3.8 7.8-3.7 6.1-3.6 4.3-3.2 3.2-4.3 2.2.2-30-.3-28.2-1.4-25.3-2.4-23.1-2.8-20.5-3.8-18.5-4.1-16.5-5.2-15.9-6.8-17.9-8.7-20.4-10.9-22.8-13.2-25.7-16.1-28.6-19.1-31.9-3 1.8 19 31.9 16 28.4 13.2 25.6 10.8 22.8 8.7 20.2 6.7 17.8 5.2 15.7 4 16.3 3.8 18.3 2.8 20.3 2.3 22.9 1.4 25.3.4 28-.2 31.4-2.3.3-4.1-.5-4.5-1.5-5-2.8-5.7-4.6-6-5.7-6.4-7.9-7.1-9.6-7.7-12.3-9.1-16.2-9.6-19.4-10.2-23-9.2-23.1-9.3-26-9.4-28.9-8.6-29.2-6.7-26-5.2-23.5-4.3-23.7-2.8-20.1-1.3-17.3-.3-13.4z"/><path d="M11.9 594.2l.7-5.7 2.5-7.6 3.9-4.3 3-1.8 7.3-1.8h4.5l5.7.9 6.9 2.4 8.2 4.1 8 4.8 9.3 6.7 11 9.3 22.5 22.1 33.6 37.9 16 19.4 10.2 12.8 14.4 20.7 5.7 10 3.6 7.8 1.9 6.1.6 4.8-.6 3.5-1.4 2.9-2.5 2.3-1.4.8-15.9-7.3-11.6-5.8-11.7-7-11.4-8.3-11.5-9.6-11.3-10.9-12.7-13.7-12.3-14.3L75 660.3l-11.5-15.6-11.2-16.5-2.9 1.9 11.2 16.6 11.6 15.7 12.2 15.2 12.3 14.5 12.9 13.8L121 717l11.7 9.7 11.7 8.4 11.9 7.2 11.8 6 12.6 5.7-2 .5-6.7.9-14.8-.3-18.4-3.2-20.3-6.8-10.1-4.6-10.3-5.7-10.7-6.8-10-7.7-9.8-8.7-9.8-10.3-8.7-10.9-7.9-10.8-6.6-10.7-11-21.4-7.3-20.1-3.7-17.7-.9-8.3z"/></g><g opacity=".7" fill-rule="evenodd" clip-rule="evenodd" fill="#FFF"><path opacity=".6" d="M912.7 726.8l1.9-11.9 3.1-12.5 3.2-9.8 7.6-17.4 4.5-8.2 9.4-13.9 10.2-11.6 9.8-8.6 8.3-5.1 4.1-1.6 3.2-.6 2.5.4 2.5 1.2 2.2 2.5 1.7 3.8 1.3 5.9.5 7.6-.3 10.2-1.6 13.3-2.5 13-8.8 33.2-6.2 19.2-6.2 16.4-7 15.1-6.2 11.6-4.5 7.1-7.8 9.1-5.9 3.7-3.5.2-.2-19.4.5-16.9 1.4-14.5 2.2-12.4 2.4-10.9 3.4-10.2 5.2-12.2 6.7-14.6 9.1-17.1 11.5-19.9-1.5-.9-11.6 20-9.1 17.1-6.8 14.7-5.2 12.3-3.4 10.4-2.5 10.9-2.1 12.5-1.5 14.7-.5 17 .2 18.8-3.2-2.1-2.3-2.8-4.5-9.6-3.7-16.1-1.1-8.7v-21.4z"/><path d="M925.5 795.2l3.2-7.8 8.7-13.2 20.3-24.8 15.5-16L985 724l4.8-2.5 3.9-1.5 3.9-.5 3 .4 2.5 1.2 1.5 1.6 1 2.3.7 7.5-1.6 9.8-3.7 11-3 6.3-7.5 12.3-5 6.2-5.5 5.7-5.5 4.8-5.7 3.9-12 6.1-5.5 1.9-10.7 2.2h-8.3l-1.9-.7 6.3-3 6.5-3.4 6.5-4.1 6.3-5.2 6.3-6.1 9.4-10.6 9.2-11.4 8.5-12.5-1.4-1.1-8.6 12.5-9 11.4-9.4 10.4-6.2 6-6.2 5.2-6.3 4-6.4 3.3-7.9 3.8-.7-.3-1.3-1.3-.7-1.7z"/></g><path opacity=".6" fill="#FFF" d="M334.7 418.5l.4 6.2 1.1 6.4 1.8 6.1 2.7 5.7 3.4 5.7 54.9 78.7 4.3 10.3 2 10.7-.4 11-2.8 10.9-32.1 83.9-2.1 7.5-1.1 7.3v7.5l1.2 7.5 2.1 7.1 3.2 6.9 4.3 6.4 5 5.5 5.7 4.8 6.4 3.9 6.8 2.8 7.5 2 6.4.9h6.2l6.2-.7 6.2-1.4 86.7-25.6 6.4-1.4 6.2-.7h6.2l6.2.9 6.1 1.6 5.9 2.3 5.5 2.8 5.3 3.7 70.9 56.3 5.3 3.9 5.7 3 5.9 2.3 6.2 1.4 6.2.9h6.4l6.2-.9 6.2-1.4 6.2-2.5 5.7-3 5.3-3.7 4.6-4.3 4.1-4.8 3.6-5.3 2.8-5.7 2.1-6.1 1.4-6.2.5-6.6 2.3-95.3 2.7-10.9 4.8-9.8 6.8-8.7 8.7-7.1 75.9-49 5.2-3.9 4.6-4.5 4.1-5 3.4-5.3 2.7-5.9 2-6.1 1.1-6.2.5-6.4-.5-6.4-1.2-6.6-2-6.1-2.7-5.7-3.4-5.3-3.9-5-4.6-4.5-5.2-3.7-5.5-3-6.1-2.5-85.8-29.9-5.9-2.5-5.5-3-5-3.7-4.6-4.3-3.9-4.8-3.4-5.2-2.4-5.7-2.1-6.1-24-86.7-2.1-6.2-2.8-5.7-3.4-5.3-4.3-4.8-4.6-4.5-5.2-3.6-5.7-3.2-5.9-2.3-6.4-1.6-6.6-.9h-6.4l-6.4.7-6.2 1.4-5.9 2.3-5.7 2.8-5.2 3.7-4.8 4.3-4.5 5-54.7 71.2-4.3 5-4.6 4.1-5.2 3.6-5.5 3-5.9 2.3-6.1 1.4-6.1.9h-6.6l-90.7-4.5h-6.6l-6.2.9-6.2 1.6-5.9 2.3-5.5 3-5.2 3.7-4.6 4.3-4.1 5-3.6 5.5-2.8 5.9-2.1 6.1-1.2 6.2-.4 6.7zm287.2-20.6c7.7-1.5 15.2 3.5 16.7 11.2l3.3 17 3.2 13.2 3.4 10.4 3.1 7.7 2.9 5.7 2.3 3.5 2.8 3 4.7 3.8 6.5 4.5 8.6 4.9 11.2 5.3 14.6 5.9c7.3 3 10.8 11.3 7.9 18.6-2.2 5.5-7.6 8.9-13.2 8.9-1.8 0-3.6-.3-5.3-1l-15-6.1-12.8-6-10.6-6.1-8.5-5.8-7.6-6.1-5.8-6.2-4-6.2-4.3-8.3-4-9.7-4.1-12.3-3.7-15.2-3.5-17.9c-1.5-7.7 3.5-15.2 11.2-16.7z"/><path fill="#FFF" d="M348.3 550.6h4.1l4.3.7 3.9 1.4 3.6 2.1 3.2 3 26.7 29 2.7 2.5 6.4 3.6 7.1 1.2 3.7-.2 39.7-5.5 4.3-.2 4.1.5 3.9 1.2 3.7 2.1 3.2 2.5 2.7 3.4 2.1 3.7 1.2 3.9.5 4.3-.2 4.1-1.1 4.1-1.8 3.9-19.9 34.2-1.6 3.4-1.4 7.1.9 7.1 1.4 3.6 17.1 35.3 1.4 4.1.7 4.1-.2 4.3-.7 4.1-1.6 3.7-2.5 3.7-3 3-3.4 2.5-3.7 1.8-4.1 1.1-4.1.2-43.3-8.2-3.9-.5-7.1.9-6.8 3.2-31.9 29.6-6.1 4.3-3.4 1.2-5 .9-5-.2-4.6-1.2-4.5-2.3-3.9-3.2-3-3.9-2.1-4.3-1.1-5-4.1-39-.7-3.7-3-6.6-5-5.2-3.2-2-34.9-18.3-3.6-2.3-3-3-2.5-3.4-1.6-3.7-1.1-4.1-.2-4.3.5-4.3 1.2-3.9 2.1-3.6 2.7-3.4 3.2-2.5 40.2-18.5 3.2-1.8 5.3-5 3.6-6.2 1.1-3.7 7.3-38.6 1.2-4.3 1.8-3.7 2.5-3.2 3.2-2.8 3.6-2.3 4.1-1.4 4.5-1.1z"/></svg></div>';
    $html .= '<div class="card-body">';
    $html .= '<div class="posts-row ajax-posts-row row-col-1a" data-style="' . $style . '">';
    $html .= $list;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    echo $html;
}

function io_get_rankings_page_list($post_type, $range)
{
    $args = array(
        'post_type' => $post_type,
        'range'     => $range,
        'count'     => io_get_option('ranking_show_count', 10),
        'go'        => io_get_option('ranking_url_go', false),
        'nofollow'  => io_get_option('ranking_url_nofollow', false),
        'class'     => 'ajax-item',
    );
    $list = io_get_rankings_posts_html($args);
    return $list;
}

/**
 * 获取排行榜页面链接
 * 
 * @param string $post_type 排行榜的类型 (可选)
 * @param string $range_key 排行榜的范围 (可选)
 * @return string 排行榜页面链接
 */
function io_get_ranking_page_link($post_type = '', $range_key = '')
{
    $link = io_get_template_page_url('template-rankings.php');

    $query_args = [];

    if (!empty($post_type)) {
        $query_args['type'] = $post_type;
    }

    if (!empty($range_key)) {
        $query_args['range'] = $range_key;
    }

    if (!empty($query_args)) {
        $link = add_query_arg($query_args, $link);
    }

    return esc_url($link);
}
