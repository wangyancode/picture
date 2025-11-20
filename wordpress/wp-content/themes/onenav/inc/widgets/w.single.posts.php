<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-10 21:22:37
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 16:17:29
 * @FilePath: /onenav/inc/widgets/w.single.posts.php
 * @Description: 单栏文章小工具
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

IOCF::createWidget('iow_single_posts_min', array(
    'title'       => 'IO 单栏文章列表',
    'classname'   => 'io-widget-single-posts-list',
    'description' => '按要求（新增、更新、浏览、点赞、随机等）显示文章、网址、APP、书籍',
    'fields'      => array(

        array(
            'id'      => 'title',
            'type'    => 'text',
            'title'   => '名称(留空不显示)',
            'default' => '热门网址',
        ),

        array(
            'id'      => 'title_ico',
            'type'    => 'icon',
            'title'   => ' ',
            'default' => 'iconfont icon-tools',
            'class'   => 'compact min'
        ),
        array(
            'id'      => 'post_type',
            'type'    => 'button_set',
            'title'   => '文章类型',
            'options' => array(
                'post'  => '文章',
                'sites' => '网址',
                'app'   => 'APP',
                'book'  => '书籍',
            ),
            'default' => 'sites',
            'class'   => 'home-widget-type compact min'
        ),
        array(
            'id'          => 'term_id',
            'type'        => 'select',
            'title'       => '分类',
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy' => 'favorites',
            ),
            'default'     => '',
            'chosen'      => true,
            'ajax'        => true,
            'multiple'    => true,
            'placeholder' => '留空则检索全部内容',
            'class'       => 'home-widget-cat compact min',
            'after'       => '<span style="color:#F23"><b>注意：</b>不能混合不同文章类型的分类！</span>',
        ),
        array(
            'id'         => 'similar',
            'type'       => 'switcher',
            'title'      => '匹配同类',
            'default'    => true,
            'help'       => '匹配同标签和分类',
            'class'      => 'compact min',
            'dependency' => array('term_id', '==', ''),
        ),

        array(
            'id'      => 'orderby',
            'type'    => 'select',
            'title'   => '选择数据条件',
            'options' => 'io_get_sort_data',
            'default' => 'views',
            'help'    => '“下载量”只有 APP 有！！！',
            'class'   => 'compact min'
        ),

        array(
            'id'      => 'count',
            'type'    => 'number',
            'title'   => '显示数量',
            'unit'    => '条',
            'default' => 6,
            'class'   => 'compact min'
        ),

        array(
            'id'      => 'window',
            'type'    => 'switcher',
            'title'   => '在新窗口打开链接',
            'default' => true,
            'class'   => 'compact min'
        ),

        array(
            'id'         => 'days',
            'type'       => 'number',
            'title'      => '时间周期',
            'unit'       => '天',
            'default'    => 0,
            'help'       => '只显示此选项设置时间内发布的内容，填 0 则不限制。',
            'dependency' => array('orderby', '!=', 'rand'),
            'class'      => 'compact min'
        ),

        array(
            'id'      => 'only_title',
            'type'    => 'switcher',
            'title'   => '只显示标题',
            'default' => false,
            'class'   => 'compact min'
        ),

        array(
            'id'         => 'serial',
            'type'       => 'switcher',
            'title'      => '显示编号',
            'default'    => true,
            'class'      => 'compact min',
            'dependency' => array('only_title', '==', 'true'),
        ),

        array(
            'id'         => 'show_thumbs',
            'type'       => 'switcher',
            'title'      => '显示缩略图',
            'default'    => true,
            'dependency' => array('only_title|post_type', '==|==', 'false|post'),
            'class'      => 'compact min'
        ),

        array(
            'id'         => 'go',
            'type'       => 'switcher',
            'title'      => '直达',
            'default'    => false,
            'help'       => '如果主题设置中关闭了“详情页”，则默认直达',
            'dependency' => array('only_title|post_type', '==|==', 'false|sites'),
            'class'      => 'compact min'
        ),

        array(
            'id'         => 'nofollow',
            'type'       => 'switcher',
            'title'      => '使用 go 跳转和 nofollow',
            'default'    => true,
            'dependency' => array('only_title|go|post_type', '==|==|==', 'false|true|sites'),
            'class'      => 'compact min'
        ),

        array(
            'id'      => 'ajax',
            'type'    => 'switcher',
            'title'   => 'AJAX 加载',
            'default' => true,
            'class'   => 'compact min'
        ),

        array(
            'id'         => 'refresh',
            'type'       => 'switcher',
            'title'      => '显示刷新按钮',
            'default'    => false,
            'dependency' => array('ajax|orderby', '==|!=', 'true|rand'),
            'class'      => 'compact min'
        )
    )
));

function iow_single_posts_min($args, $instance)
{
    echo $args['before_widget'];
    echo get_widget_title($args, $instance);

    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post']);
    if (in_array($instance['post_type'], $posts_type_s)) {
        echo get_widget_single_posts_html($args, $instance, '');
    } else {
        echo '<div class="p-3"><div class="text-center text-sm posts-null py-5">文章类型被关闭，请去<a href="' . io_get_admin_iocf_url('全局功能/基础设置') . '" target="_blank"><b>主题设置</b></a>中开启。</div></div>';
    }

    echo $args['after_widget'];
}

/**
 * 获取单栏文章内容
 * @param mixed $args
 * @param mixed $instance
 * @param mixed $data_id  数据所在的 ID
 * @return string
 */
function get_widget_single_posts_html($args, $instance, $data_id = '')
{
    $exclude = array();
    $similar = 0;
    if (isset($instance['similar']) && $instance['similar'] && is_single()) {
        $post_type = get_post_type();
        if ($post_type === $instance['post_type']) {
            $post_id   = get_the_ID();
            $similar   = array(
                'post_id'   => $post_id,
                'post_type' => $post_type,
            );
            $exclude[] = $post_id;
        }
    }

    switch ($instance['post_type']) {
        case 'sites':
            $style = 'min';
            break;
        case 'app':
            $style = 'card';
            break;
        case 'book':
            $style = io_get_book_card_mode();
            break;
        default:
            $style = 'min-sm';
            break;
    }
    $post_style = $style ? $instance['post_type'] . '-' . $style : '';
    if ($instance['only_title']) {
        $style      = 'title';
        $post_style = 'title';
    }

    $ajax     = array(
        'exclude'  => $exclude,
        'similar'  => $similar,
        'fallback' => 'rand' === $instance['orderby'] ? 1 : 0,
        'style'    => $style,
    );
    $instance = wp_parse_args($instance, $ajax);

    $class = $instance['ajax'] ? ' auto' : '';

    $ajax_attr = sprintf(
        'data-href="%1$s" data-target="#%2$s .ajax-panel" data-action="load_single_posts" data-style="%3$s" data-args="%4$s" data-id="%2$s" data-data_id="%5$s"',
        esc_url(admin_url('admin-ajax.php')),
        esc_attr($args['id']),
        esc_attr($post_style),
        esc_attr(json_encode($ajax)),
        esc_attr($data_id)
    );

    $html      = '';
    if ('rand' === $instance['orderby'] || $instance['refresh']) {
        $html .= '<a href="" class="ajax-auto-post click' . $class . '" ' . $ajax_attr . ' title="' . __('刷新', 'i_theme') . '"><i class="iconfont icon-refresh"></i></a>';
    } elseif ($instance['ajax']) {
        $html .= '<span class="ajax-auto-post auto" ' . $ajax_attr . '></span>';
    }

    $html .= '<div class="card-body ajax-panel">';
    if ($instance['ajax']) {
        $html .= get_widgets_posts_html($instance, get_posts_placeholder($post_style, $instance['count'], false));
    } else {
        $html .= get_widgets_posts_html($instance);
    }

    $html .= '</div>';

    return $html;
}

/**
 * 加载单栏文章内容
 * @return never
 */
function load_single_posts_callback()
{
    $widget_id = __post('id');
    $args      = __post('args');
    $data_id   = __post('data_id'); // 数据所在的 ID
    if (!$widget_id || !$args) {
        io_error('{"status":3,"msg":"数据错误！"}');
    }

    $instance = get_widget_config($widget_id);
    if($data_id){
        $instance = $instance[$data_id];
    }
    $instance = array_merge($instance, $args);
    echo get_widgets_posts_html($instance);
    exit;
}
add_action( 'wp_ajax_load_single_posts' , 'load_single_posts_callback' );
add_action( 'wp_ajax_nopriv_load_single_posts' , 'load_single_posts_callback' );