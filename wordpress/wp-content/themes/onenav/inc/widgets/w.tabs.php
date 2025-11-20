<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-04 01:10:59
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-11 13:04:27
 * @FilePath: /onenav/inc/widgets/w.tabs.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

IOCF::createWidget('iow_big_tabs_max', array(
    'title'       => 'IO [TAB]文章列表',
    'classname'   => 'io-widget-big-tabs-list',
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
            'id'           => 'tabs',
            'type'         => 'group',
            'button_title' => '添加 TAB',
            'fields'       => array(
                array(
                    'id'          => 'title',
                    'type'        => 'text',
                    'title'       => 'TAB 名称',
                    'placeholder' => '热门网址'
                ),
                array(
                    'id'      => 'post_type',
                    'type'    => 'button_set',
                    'title'   => '类型',
                    'options' => array(
                        'favorites' => '网址',
                        'apps'      => 'App',
                        'books'     => '书籍',
                        'category'  => '文章',
                    ),
                    'class'   => 'home-widget-type compact min',
                    'default' => 'favorites',
                ),
                array(
                    'id'          => 'cats',
                    'type'        => 'select',
                    'title'       => ' ',
                    'placeholder' => '留空则检索全部内容',
                    'chosen'      => true,
                    'ajax'        => true,
                    'multiple'    => true,
                    'options'     => 'categories',
                    'query_args'  => array(
                        'taxonomy' => 'favorites',
                    ),
                    'before'      => '选择<b>类型</b>后输入<b>分类名称</b>关键字搜索分类',
                    'after'       => '<span style="color:#F23"><b>注意：</b>不能混合不同文章类型的分类！</span>',
                    'settings'    => array(
                        'min_length' => 2,
                    ),
                    'class'       => 'home-widget-cat compact min',
                ),
                array(
                    'id'      => 'orderby',
                    'type'    => 'select',
                    'title'   => '默认排序',
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
                    'default' => 12,
                    'class'   => 'compact min'
                ),
                array(
                    'id'         => 'sites_style',
                    'type'       => 'image_select',
                    'title'      => '样式',
                    'options'    => io_get_posts_card_style('sites'),
                    'class'      => 'compact min min-img-select',
                    'default'    => 'big',
                    'dependency' => array('post_type', '==', 'favorites')
                ),
                array(
                    'id'         => 'book_style',
                    'type'       => 'image_select',
                    'title'      => '样式',
                    'options'    => io_get_posts_card_style('book'),
                    'class'      => 'compact min min-img-select',
                    'default'    => 'v1',
                    'dependency' => array('post_type', '==', 'books')
                ),
                array(
                    'id'         => 'app_style',
                    'type'       => 'image_select',
                    'title'      => '样式',
                    'options'    => io_get_posts_card_style('app'),
                    'class'      => 'compact min min-img-select',
                    'default'    => 'card',
                    'dependency' => array('post_type', '==', 'apps')
                ),
                array(
                    'id'         => 'post_style',
                    'type'       => 'image_select',
                    'title'      => '样式',
                    'options'    => io_get_posts_card_style('post'),
                    'class'      => 'compact min min-img-select',
                    'default'    => 'card',
                    'dependency' => array('post_type', '==', 'category')
                ),
                array(
                    'id'     => 'columns',
                    'type'   => 'fieldset',
                    'title'  => '列数',
                    'before' => '根据屏幕大小设置',
                    'fields' => io_get_screen_item_count(array(
                        'sm'  => 1,
                        'md'  => 2,
                        'lg'  => 2,
                        'xl'  => 3,
                        'xxl' => 4
                    )),
                    'class'  => 'min-columns compact min',
                ),
            )
        ),
        array(
            'id'      => 'ajax',
            'type'    => 'switcher',
            'title'   => 'AJAX 加载',
            'default' => true,
            'class'   => ''
        ),
        array(
            'id'      => 'auto_load',
            'type'    => 'switcher',
            'title'   => '自动加载下一页',
            'default' => false,
            'class'   => 'compact min'
        ),
    )
));

function iow_big_tabs_max($args, $instance) {
    return;
    global $is_sidebar;
    $taxonomy  = $instance['post_type'];
    $post_type = get_post_types_by_taxonomy($taxonomy);
    $columns   = io_get_columns_class($post_type, $instance['columns'], $is_sidebar);
    $style     = $instance[$post_type . '_style'];
    $post_data = array(
        'html'     => '',
        'max_page' => 2,
        'count'    => ''
    );

    $select_by = '';
    if (!empty($instance['select_by'])) {
        foreach ($instance['select_by'] as $v) {
            $select_by .= '<a class="list-select list-ajax-by" href="javascript:;" data-target="#' . $args['id'] . '" data-type="' . $v . '">' . get_select_by($v) . '</a>';
        }
    }
    if(!empty($select_by)){
        $select_by = '<div class="list-select-line"></div><div class="list-selects no-scrollbar">' . $select_by . '</div>';
    }
    $title = '';
    if (!empty($instance['title'])) {
        $title = get_widget_title($args, $instance, 'mb-3', $select_by);
    } elseif (!empty($select_by)) {
        $title = '<div class="d-flex align-items-center white-nowrap"><div class="list-select-title">' . __('排序', 'i_theme') . '</div>' . $select_by . '</div>';
    }

    $body = '';
    if ($instance['ajax']) {
        $body  = get_posts_placeholder($post_type . '-' . $style, $instance['count']);
    } else {
        $config    = array(
            'ajax_class'          => 'ajax-url',
            'echo'                => false,
            'orderby'             => $instance['orderby'],
            'ignore_sticky_posts' => 1,
            'page'                => 1,
            'style'               => $style,
        );
        $post_data = show_card($instance['count'], $instance['cats'], $taxonomy, $config);
        $body      = $post_data['html'];
    }

    $btn = '';
    if ($post_data['max_page'] > 1) {
        $btn = sprintf(
            '<a class="ajax-page-post%1$s" href="%2$s" data-target="#%3$s .ajax-panel" data-action="load_big_posts" data-page="%4$s" data-id="%3$s" data-orderby="%5$s" data-style="%6$s">%7$s</a>',
            ($instance['ajax'] ? ' auto' : '') . ($instance['auto_load'] ? ' auto-load-next' : ''),
            esc_url(admin_url('admin-ajax.php')),
            $args['id'],
            $instance['ajax'] ? '1' : '2',
            $instance['orderby'],
            $post_type . '-' . $style,
            __('加载更多', 'i_theme')
        );
    }
    if (!empty($btn)) {
        $btn = '<div class="ajax-more text-center mt-3">' . $btn . '</div>';
    }

    echo $args['before_widget'];
    echo $title;
    echo '<div class="posts-row ' . $columns . ' ajax-panel">';
    echo $body;
    echo '</div>';
    echo $btn;
    echo $args['after_widget'];
}
