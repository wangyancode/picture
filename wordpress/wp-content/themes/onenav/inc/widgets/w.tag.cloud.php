<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-09-12 23:45:51
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-05 01:14:02
 * @FilePath: /onenav/inc/widgets/w.tag.cloud.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


IOCF::createWidget('iow_tag_cloud_tool', array(
    'title'       => 'IO 标签云',
    'classname'   => 'io-widget-tag-cloud',
    'description' => '按要求显示标签云',
    'fields'      => array(

        array(
            'id'      => 'title',
            'type'    => 'text',
            'title'   => '名称(留空不显示)',
            'default' => '标签云',
        ),

        array(
            'id'      => 'title_ico',
            'type'    => 'icon',
            'title'   => ' ',
            'default' => 'iconfont icon-tools',
            'class'   => 'compact min'
        ),

        array(
            'id'      => 'taxonomy',
            'type'    => 'checkbox',
            'title'   => '类型',
            'options' => array(
                'category'  => '文章分类',
                'post_tag'  => '文章标签',
                'favorites' => '网址分类',
                'sitetag'   => '网址标签',
                'apps'      => 'APP分类',
                'apptag'    => 'APP标签',
                'books'     => '书籍分类',
                'booktag'   => '书籍标签'
            ),
            'inline' => true,
            'default' => array('favorites','sitetag'),
            'class'   => 'compact min'
        ),

        array(
            'id'      => 'orderby',
            'type'    => 'select',
            'title'   => '排序',
            'options' => array(
                'name'  => '名称',
                'count' => '数量',
                'rand'  => '随机',
            ),
            'default' => 'name',
            'class'   => 'compact min'
        ),

        array(
            'id'      => 'count',
            'type'    => 'number',
            'title'   => '显示数量',
            'unit'    => '条',
            'default' => 20,
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
            'id'         => 'show_count',
            'type'       => 'switcher',
            'title'      => '显示标签计数',
            'default'    => false,
            'class'   => 'compact min'
        ),

        array(
            'id'      => 'ajax',
            'type'    => 'switcher',
            'title'   => 'AJAX 加载',
            'default' => true,
            'class'   => 'compact min'
        )
    )
));
function iow_tag_cloud_tool($args, $instance) {
    $ajax_data = array(
        'window'     => $instance['window'],
        'taxonomy'   => $instance['taxonomy'] ?: ['favorites'],
        'count'      => $instance['count'],
        'orderby'    => $instance['orderby'],
        'show_count' => $instance['show_count'],
    );

    $class     = $instance['ajax'] ? ' auto' : '';
    $ajax_attr = 'data-href="' . esc_url(admin_url('admin-ajax.php')) . '" data-target="#' . $args['id'] . ' .ajax-panel" data-action="load_tag_cloud" data-instance="' . esc_attr(json_encode($ajax_data)) . '"';
    $ajax_btn  = '';
    if ('rand' === $instance['orderby']) {
        $ajax_btn = '<a href="" class="ajax-auto-post click' . $class . '" ' . $ajax_attr . ' title="' . __('刷新', 'i_theme') . '"><i class="iconfont icon-refresh"></i></a>';
    } elseif ($instance['ajax']) {
        $ajax_btn = '<span class="ajax-auto-post auto" ' . $ajax_attr . '></span>';
    }

    $body = '';
    if (!$instance['ajax']) {
        $body .= get_tag_cloud_html($ajax_data);
    } else {
        for ($i = 0; $i < $instance['count']; $i++) {
            $body .= '<div class="placeholder flex-fill" style="--height:30px;--width:' . rand(40, 120) . 'px;"></div>';
        }
    }

    echo $args['before_widget'];
    echo get_widget_title($args, $instance);
    echo $ajax_btn;
    echo '<div class="card-body d-flex flex-wrap ajax-panel" style="gap: 6px;">';
    echo $body;
    echo '</div>';
    echo $args['after_widget'];
}

/**
 * 加载单栏文章内容
 * @return never
 */
function load_tag_cloud_callback(){
    $instance = $_POST['instance'];
    if (!is_array($instance) || count($instance) < 1)
        io_error('{"status":3,"msg":"数据错误！"}');

    echo get_tag_cloud_html($instance);
    exit;
}
add_action( 'wp_ajax_load_tag_cloud' , 'load_tag_cloud_callback' );
add_action( 'wp_ajax_nopriv_load_tag_cloud' , 'load_tag_cloud_callback' );
