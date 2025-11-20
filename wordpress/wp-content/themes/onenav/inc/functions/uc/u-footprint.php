<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-11-01 15:23:29
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-16 21:55:57
 * @FilePath: /onenav/inc/functions/uc/u-footprint.php
 * @Description: 用户足迹
 */

function io_uc_get_footprint_html()
{
    $data = array(
        array(
            'type'    => 'sites',
            'title'   => io_get_post_type_name('sites'),
            'style'   => 'max',
            'columns' => 'row-col-1a row-col-md-2a row-col-lg-3a'
        ),
        array(
            'type'    => 'post',
            'title'   => io_get_post_type_name('post'),
            'style'   => 'min',
            'columns' => 'row-col-1a row-col-md-2a'
        ),
        array(
            'type'    => 'app',
            'title'   => io_get_post_type_name('app'),
            'style'   => 'card',
            'columns' => 'row-col-1a row-col-md-2a row-col-lg-3a'
        ),
        array(
            'type'    => 'book',
            'title'   => io_get_post_type_name('book'),
            'style'   => 'v1',
            'columns' => 'row-col-2a row-col-md-3a row-col-lg-5a'
        ),
    );

    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post']); // 启用的文章类型
    $index = 0;
    
    $html = '<div class="footprint-card load-ajax-card">';
    $html .= '<div class="footprint-head mb-3 d-flex align-items-center">';
    $html .= '<div class="nav-title"><i class="iconfont icon-time-o i-badge mr-1"></i>' . __('最近访问', 'i_theme') . '</div>';
    $html .= '<div class="tips-box vc-l-blue text-xs ml-2">'.__('记录存储于本地浏览器', 'i_theme').'</div>';
    $html .= '<a href="javascript:;" class="text-muted text-xs ml-auto footprint-clear-all">' . __('清空', 'i_theme') . '</a>';
    $html .= '</div>';
    $html .= '<div class="footprint-nav mb-3 overflow-x-auto no-scrollbar">';
    foreach ($data as $value) {
        if(!in_array($value['type'], $posts_type_s)){
            continue;
        }
        $active = $index == 0 ? 'active auto' : '';
        $html .= '<a href="' . esc_url(admin_url('admin-ajax.php')) . '" class="ajax-footprint btn btn-tab-h btn-sm mr-2 ' . $active . '" data-action="get_footprint_posts" data-type="' . $value['type'] . '" data-style="' . $value['style'] . '" data-columns="' . $value['columns'] . '">' . $value['title'] . '</a>';
        $index++;
    }
    $html .= '</div>';
    $html .= '<div class="footprint-body ajax-panel row-a '.$data[0]['columns'].'">';
    $post_style = $data[0]['type'] . '-' . $data[0]['style'];
    $html .= get_posts_placeholder($post_style, 6, false);
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
