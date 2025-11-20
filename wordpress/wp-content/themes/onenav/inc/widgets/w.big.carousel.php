<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-04 02:14:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-17 01:44:22
 * @FilePath: /onenav/inc/widgets/w.big.carousel.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';

IOCF::createWidget('iow_big_carousel_max', array(
    'title'       => 'IO [BIG]轮播模块',
    'classname'   => 'io-big-carousel',
    'description' => '[BIG]轮播模块',
    'fields'      => array(
        array(
            'id'           => 'config',
            'type'         => 'group',
            'before'       => $tip_ico . '警告：添加后选项不能留空，否则网站将爆炸。',
            'fields'       => array(
                array(
                    'id'    => 'title',
                    'type'  => 'text',
                    'title' => '名称',
                ),
                array(
                    'id'    => 'info',
                    'type'  => 'text',
                    'title' => '简介',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'img',
                    'type'  => 'upload',
                    'title' => '图片',
                    'after' => $tip_ico . '图片尺寸推荐 21:9',
                    'class' => 'compact min',
                ),
                array(
                    'id'      => 'type',
                    'type'    => 'button_set',
                    'title'   => '类型',
                    'options' => array(
                        'favorites' => '网址',
                        'apps'      => 'App',
                        'books'     => '书籍',
                        'category'  => '文章',
                        'img'       => 'Url',
                    ),
                    'class'   => 'home-widget-type compact min',
                    'default' => 'favorites',
                ),
                array(
                    'id'          => 'cat',
                    'type'        => 'select',
                    'placeholder' => '选择一个分类',
                    'chosen'      => true,
                    'ajax'        => true,
                    'options'     => 'categories',
                    'query_args'  => array(
                        'taxonomy' => 'favorites',
                    ),
                    'before'      => $tip_ico . '选择类型后输入<b>分类名称</b>关键字搜索',
                    'settings'    => array(
                        'min_length' => 2,
                    ),
                    'class'       => 'home-widget-cat compact min',
                    'dependency'  => array('type', '!=', 'img'),
                ),
                array(
                    'id'         => 'order',
                    'type'       => 'radio',
                    'title'      => '排序',
                    'inline'     => true,
                    'options'    => array(
                        'ID'       => 'ID',
                        'modified' => '修改日期',
                        'date'     => '创建日期',
                        'views'    => '查看次数',
                    ),
                    'default'    => 'modified',
                    'class'      => 'compact min',
                    'dependency' => array('type', '!=', 'img'),
                ),
                array(
                    'id'         => 'num',
                    'type'       => 'spinner',
                    'title'      => '显示数量',
                    'step'       => 1,
                    'default'    => 10,
                    'class'      => 'compact min',
                    'dependency' => array('type', '!=', 'img'),
                ),
                array(
                    'id'         => 'go',
                    'type'       => 'switcher',
                    'title'      => '直达',
                    'label'      => '直达目标网站',
                    'class'      => 'compact min',
                    'dependency' => array('type', '==', 'favorites'),
                ),
                array(
                    'id'         => 'url',
                    'type'       => 'text',
                    'title'      => 'Url',
                    'class'      => 'compact min',
                    'dependency' => array('type', '==', 'img'),
                ),
                array(
                    'id'         => 'is_ad',
                    'type'       => 'switcher',
                    'title'      => '是广告',
                    'label'      => '注意：广告将直达目标URL,不会添加跳转和nofollow',
                    'class'      => 'compact min',
                    'dependency' => array('type', '==', 'img'),
                )
            ),
            'button_title' => '添加卡片'
        ),
    ),
));

function iow_big_carousel_max($args, $instance)
{
    $datas = $instance['config'];
    if (!is_array($datas) || count($datas) == 0) {
        return;
    }

    $html = '<div class="swiper-widgets-card position-relative">';
    $html .= '<div class="swiper swiper-widgets br-xl">';
    $html .= '<div class="swiper-wrapper">';
    foreach ($datas as $data) {
        $html .= get_big_swiper_widgets_card($data);
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= get_big_swiper_widgets_thumbs($datas);
    $html .= '</div>';


    echo $args['before_widget'];
    echo $html;
    echo $args['after_widget'];
}

function get_big_swiper_widgets_thumbs($datas)
{
    $html = '<div class="swiper swiper-widgets-thumbs">';
    $html .= '<div class="swiper-wrapper">';
    foreach ($datas as $data) {
        $bg_img = get_lazy_img_bg($data['img']);
        $html .= '<div class="swiper-slide tab-card position-relative d-block mx-2">';
        $html .= '<div class="img-post media media-16x9 br-xl overflow-hidden" >';
        $html .= '<div class="media-content img-rounded img-responsive" ' . $bg_img . '></div>';
        $html .= '<div class="caption d-flex align-items-center h-100 position-absolute"><span class="line2 text-sm">' . $data['title'] . '</span></div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

function get_big_swiper_widgets_card($data)
{
    $bg_img = get_lazy_img_bg($data['img']);
    $html   = '<div class="swiper-slide media media-21x9">';
    $html .= '<div class="media-content media-title-bg" ' . $bg_img . '>';
    $html .= '<div class="media-title d-flex align-items-center">';
    $html .= '<div class="flex-fill swiper-widgets-content">';
    $html .= '<p class="text-sm position-relative pl-3">' . $data['title'] . '</p>';
    $html .= '<h3 class="d-none d-md-block mt-2">' . $data['info'] . '</h3>';
    if ($data['type'] != "img") {
        if ($data['cat']) {
            $term_link = get_term_link((int) $data['cat']);
            $html .= '<a href="' . esc_url($term_link) . '" class="btn btn-detailed px-4 px-lg-5 py-lg-2 mt-2 mt-lg-3">' . __('查看详情', 'i_theme') . '</a>';
        }
        if (!wp_is_mobile()) {
            $html .= '<div class="d-none d-md-flex justify-content-end mt-n4 mt-lg-0">';
            $html .= '<div class="swiper swiper-term-content">';
            $html .= '<div class="swiper-wrapper">';
            $html .= get_tab_post_html($data, 'swiper', 'swiper-slide mx-1 mx-lg-2');
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
    } else {
        $link = $data['is_ad'] ? $data['url'] : go_to($data['url']);
        $html .= '<a href="' . esc_url($link) . '" target="_blank" class="btn btn-detailed px-4 px-lg-5 py-lg-2 mt-2 mt-lg-3">' . __('查看详情', 'i_theme') . '</a>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

