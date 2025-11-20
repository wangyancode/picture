<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-09 21:11:15
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-04 18:39:08
 * @FilePath: /onenav/inc/functions/io-widget-tab.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


function get_tab_widget_post_callback(){
    $data = $_POST['data'];
    if(is_array($data))
        echo get_tab_post_html($data,'tab');
    exit();
}
add_action('wp_ajax_nopriv_get_tab_widget_post', 'get_tab_widget_post_callback'); 
add_action('wp_ajax_get_tab_widget_post', 'get_tab_widget_post_callback');
/**
 * 
 */
function get_tab_post_html($data, $type, $class = '')
{
    $html = '';
    $args = array(
        'post_type'      => get_post_types_by_taxonomy($data['type']),
        'post_status'    => 'publish',
        'posts_per_page' => $data['num'],
        'orderby'        => $data['order'],
    );
    if ($data['cat']) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => $data['type'],
                'field'    => 'id',
                'terms'    => $data['cat']
            )
        );
    }
    $items = new WP_Query($args);

    $cat_type = array(
        'favorites' => 'sites',
        'apps'      => 'app',
        'books'     => 'book',
        'category'  => 'post',
    );
    /**
     * 添加 $cat_type 过滤器
     */
    $cat_type = apply_filters('io_tab_widget_cat_type', $cat_type);

    if (io_get_option('show_sticky', false)) {
        $items = sticky_posts_to_top($items, get_post_types_by_taxonomy($data['type']), $data['type'], $data['cat']);
    }
    if ($items->have_posts()):
        while ($items->have_posts()):
            $items->the_post();
            if (isset($cat_type[$data['type']])) {
                $html .= call_user_func('get_tab_widget_' . $cat_type[$data['type']], $data, $type, $class);
            }
        endwhile;
    endif;
    wp_reset_postdata();
    return $html;
}


function get_tab_widget_sites($data, $type, $class = '')
{
    global $post;
    $sites_meta = get_sites_card_meta();
    $target     = $data['go'] ? 'target="_blank' : '';
    $nofollow   = $data['go'] ? nofollow($sites_meta["go_url"]) : '';
    $link       = $data['go'] ? go_to($sites_meta['go_url']) : $sites_meta['link'];
    $image      = get_lazy_img($sites_meta['ico'], $sites_meta['title'], '70', '', $sites_meta['default_ico']);

    $html = '<div class="tab-card type-' . $data['type'] . ' ' . $class . '">';
    $html .= '<a href="' . esc_url($link) . '" ' . $target . ' ' . $nofollow . ' class="icon-btn" title="' . $sites_meta['title'] . '">';
    $html .= '<div class="img-bg bg-white overflow-hidden app-rounded">';
    $html .= '<div class="overflow-hidden rounded-circle bg-light d-flex align-items-center justify-content-center">';
    $html .= $image;
    $html .= '</div> ';
    $html .= '</div>';
    $html .= '<div class="icon-title text-center mt-1">';
    $html .= '<span class="text-xs text-muted line1 px-1">' . $sites_meta['title'] . '</span>';
    $html .= '</div>';
    $html .= '</a></div>';

    return $html;
}

function get_tab_widget_app($data, $type, $class = '')
{
    global $post;

    $ico_info = get_post_meta(get_the_ID(), 'app_ico_o', true);
    $bg       = '';
    $size     = '';
    if ($ico_info && $ico_info['ico_a']) {
        $bg   = 'style="background-image: linear-gradient(130deg, ' . $ico_info['ico_color']['color-1'] . ', ' . $ico_info['ico_color']['color-2'] . ');"';
        $size = 'background-size: ' . $ico_info["ico_size"] . '%';
    }

    $html = '<div class="tab-card type-' . $data['type'] . ' ' . $class . '">';
    $html .= '<a href="' . get_permalink() . '" class="icon-btn" title="' . get_the_title() . '">';
    $html .= '<div class="media p-0 app-rounded" ' . $bg . '>';
    $html .= '<div class="media-content" ' . get_lazy_img_bg(get_post_meta_img(get_the_ID(), '_app_ico', true), $size) . '></div>';
    $html .= '</div>';
    $html .= '<div class="icon-title text-center mt-1">';
    $html .= '<span class="text-xs text-muted line1 px-1">' . get_the_title() . '</span>';
    $html .= '</div>';
    $html .= '</a></div>';
    
    return $html;
}

function get_tab_widget_book($data, $type, $class = '')
{
    global $post;
    $m_class = '';
    if ($type == 'tab') {
        $m_class = 'px-2 mb-3 col-2a col-sm-4a col-md-3a col-lg-5a col-xl-6a col-xxl-9a';
    }
    $html = '<div class="tab-card ' . $m_class . ' type-' . $data['type'] . ' ' . $class . '">';
    $html .= '<a class="img-book bg-white media media-5x7 br-lg" href="' . get_permalink() . '">';
    $html .= '<div class="caption h-100 w-100 position-absolute text-right"><span class="text-sm text-muted">' . get_the_title() . '</span></div>';
    $html .= '<div class="media-content img-rounded img-responsive" ' . get_lazy_img_bg(get_post_meta_img(get_the_ID(), '_thumbnail', true)) . '></div>';
    $html .= '</a></div>';

    return $html;
}

function get_tab_widget_post($data, $type, $class = '')
{
    global $post;
    $m_class = '';
    if ($type == 'tab') {
        $m_class = 'px-2 mb-3 col-2a col-md-2a col-lg-3a col-xl-4a col-xxl-5a';
    } else {
        $m_class = 'position-relative d-block';
    }

    $html = '<div class="tab-card ' . $m_class . ' type-' . $data['type'] . ' ' . $class . '">';
    $html .= '<a class="img-post media media-16x9 br-lg overflow-hidden" href="' . get_permalink() . '">';
    $html .= '<div class="media-content img-rounded img-responsive" ' . get_lazy_img_bg(io_theme_get_thumb()) . '></div>';
    $html .= '<div class="caption d-flex align-items-center h-100 position-absolute"><span class="line2 text-xs">' . get_the_title() . '</span></div>';
    $html .= '</a></div>';

    return $html;
}