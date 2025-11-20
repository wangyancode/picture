<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-10 21:22:37
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 23:43:17
 * @FilePath: /onenav/inc/widgets/w.ranking.php
 * @Description: 热门内容，排行榜
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

IOCF::createWidget('iow_ranking_post_min', array(
    'title'       => 'IO 排行榜内容',
    'classname'   => 'io-widget-ranking-list ajax-parent',
    'description' => '注意：根据排行榜数据显示热门内容，需开启[主题设置->全局功能/统计浏览]里的“按天记录统计数据”功能',
    'fields'      => array(

        array(
            'id'      => 'title',
            'type'    => 'text',
            'title'   => '名称(留空不显示)',
            'default' => '网址',
        ),
        array(
            'id'      => 'class',
            'type'    => 'text',
            'title'   => '额外 class',
            'default' => 'fx-header-bg',
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
            'class'   => 'refresh-category compact min',
            'default' => 'sites',
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
            'id'      => 'range',
            'type'    => 'select',
            'title'   => '范围',
            'options' => 'get_ranking_range',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'default' => array('today', 'week', 'month'),
            'after'   => '注意：如果没有数据，请检查是否开启了“<a href="' . io_get_admin_iocf_url('全局功能/统计浏览') . '" target="_blank">按天记录统计数据</a>”功能',
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
            'id'         => 'only_title',
            'type'       => 'switcher',
            'title'      => '只显示标题',
            'default'    => false,
            'class'      => 'compact min'
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
            'title'      => '不使用 go 跳转和 nofollow',
            'default'    => false,
            'dependency' => array('only_title|go|post_type', '==|==|==', 'false|true|sites'),
            'class'      => 'compact min'
        ),

        array(
            'id'      => 'ajax',
            'type'    => 'switcher',
            'title'   => 'AJAX 加载',
            'default' => true,
            'class'   => 'compact min csf-hide'
        ),
    )
));

function iow_ranking_post_min($args, $instance)
{
    $styles = array(
        'post'  => 'min-sm',
        'sites' => 'default',
        'app'   => 'card',
        'book'  => io_get_book_card_mode('card'),
    );

    $range = (array) $instance['range'];

    $data = array(
        'post_type'   => $instance['post_type'],
        'range'       => $range[0],
        'count'       => $instance['count'],
        'window'      => $instance['window'],
        'only_title'  => $instance['only_title'],
        'serial'      => $instance['serial'],
        'show_thumbs' => $instance['show_thumbs'],
        'go'          => $instance['go'],
        'nofollow'    => $instance['nofollow'],
    );

    $posts = io_get_rankings_posts_html($data);

    $style = $instance['only_title'] ? 'title' : $instance['post_type'] . '-' . $styles[$instance['post_type']];

    $btn = '';
    $range_data = array(
        'today'     => _x('日榜', 'w', 'i_theme'),
        'yesterday' => _x('昨日', 'w', 'i_theme'),
        'week'      => _x('周榜', 'w', 'i_theme'),
        'last_week' => _x('上周', 'w', 'i_theme'),
        'month'     => _x('月榜', 'w', 'i_theme'),
        'all'       => _x('总榜', 'w', 'i_theme'),
    );
    foreach ($range as $index => $value) {
        $data['range'] = $value;
        $class         = !$index ? ' active loaded' : '';
        $btn .= sprintf(
            '<a href="javascript:;" class="is-tab-btn ajax-click-post%s" data-target=".ajax-panel" data-action="%s" data-args="%s" data-style="%s">%s</a>',
            $class,
            'get_w_rankings_posts',
            esc_attr(json_encode($data)),
            esc_attr($style),
            $range_data[$value]
        );
    }
    $btn = $btn ? '<div class="range-nav text-md">' . $btn . '</div>' : '';

    $before_class  = isset($instance['class']) && $instance['class'] ? $instance['class'] : '';
    $before_widget = str_replace('class="', 'class="' . $before_class . ' ', $args['before_widget']);

    echo $before_widget;
    echo get_widget_title($args, $instance);
    echo $btn;
    echo '<div class="card-body">';
    echo '<div class="posts-row row-sm ajax-panel row-col-1a">';
    echo $posts;
    echo '</div>';
    echo '</div>';
    echo '<a href="' . esc_url(io_get_ranking_page_link($instance['post_type'])) . '" class="btn vc-l-yellow d-block mx-3 mb-3 text-sm" target="_blank">' . __('查看完整榜单', 'i_theme') . '</a>';
    echo $args['after_widget'];
}


function io_ajax_get_w_rankings_posts()
{
    $args = __post('args');
    if (empty($args)) {
        return;
    }

    echo io_get_rankings_posts_html($args);
    exit();
}
add_action('wp_ajax_nopriv_get_w_rankings_posts', 'io_ajax_get_w_rankings_posts');
add_action('wp_ajax_get_w_rankings_posts', 'io_ajax_get_w_rankings_posts');
