<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-06 16:23:03
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-11 13:29:28
 * @FilePath: /onenav/inc/functions/io-hot-api.php
 * @Description: 
 */

function io_get_hot_api_title_html($args, $class = '', $index = 0, $type = 'card')
{
    $hot_type = $args['hot_type'];
    $target   = 'hotapi-' . $hot_type . '-' . $index;

    $title = $args['name'];
    $icon  = $args['ico'] ? '<img class="hotapi-ico" src="' . $args['ico'] . '" alt="' . $title . '">' : '';

    $html = '<div class="' . $class . '" data-target=".' . $target . '">';
    $html .= $icon;
    $html .= '<span class="title-name text-sm">' . $title . '</span>';
    $html .= $type === 'card' ? '<span class="ml-auto d-none d-md-block text-xs slug-name text-muted"></span>' : '';
    $html .= '</div>';

    return $html;
}

function io_get_hot_api_body_html($args)
{ 
    $title     =  __('更多', 'i_theme');  
    $more_link = esc_url(home_url() . '/hotnews/');

    $html = '<div class="card-body d-flex flex-column pb-2 pt-0">';

    $html .= '<div class="overflow-y-auto hotapi-body">';
    $html .= '<ul class="hotapi-list"></ul>';
    $html .= '</div>';

    $html .= '<div class="d-flex mt-auto text-xs text-muted pt-2">';
    $html .= is_hotnews()?'':'<a href= "' . $more_link . '" title="' . $title . '">' . $title . '</a>';
    $html .= '<div class="flex-fill"></div>';
    $html .= '<a href= "javascript:" class="hotapi-refresh" title="' . __('刷新', 'i_theme') . '"><i class="iconfont icon-refresh text-md"></i></a>';
    $html .= '</div>';

    $html .= '</div>';
    
    $html .= '<div class="hotapi-loading">';
    $html .= '<i class="iconfont icon-loading icon-spin text-32"></i>';
    $html .= '</div>';

    return $html;
}
/**
 * 获取热门API卡片
 * @param mixed $value
 * @param mixed $index 序号
 * @return string
 */
function io_get_hot_api_card_html($value, $index)
{
    $iframe     = (io_get_option('hot_iframe', false) && $value['is_iframe'] && !wp_is_mobile()) ? 1 : 0;
    $rule_id    = $value['rule_id'];
    $type       = $value['hot_type'];
    $class      = 'hotapi-' . $type . '-' . $index;
    $head_class = 'card-header card-h-w widget-header d-flex align-items-center';

    $html = '<div class="card hotapi-card ' . $class . '" data-rule_id="' . $rule_id . '" data-is_iframe="' . $iframe . '" data-api_type="' . $type . '" data-type="card">';
    $html .= io_get_hot_api_title_html($value, $head_class, $index);
    $html .= io_get_hot_api_body_html($value);
    $html .= '</div>';

    return $html;
}
/**
 * 获取热门API卡片列表
 * @param mixed $args
 * @return string
 */
function io_get_hot_api_cards_html($args)
{
    $count = count($args);
    $class = 'row-col-' . $count . 'a';
    if ($count > 12) {
        $class = 'row-col-12a';
    }
    $min_width = 340 * $count;


    $html = '<div class="hotapi-x-overflow mb-3">';
    $html .= '<div class="row-a ' . $class . '" style="min-width:' . $min_width . 'px;">';
    foreach ($args as $index => $value) {
        $html .= io_get_hot_api_card_html($value, $index);
    }
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
/**
 * 获取热门API Tab列表
 * @param mixed $args
 * @return string
 */
function io_get_hot_api_tab_html($args)
{
    // tab 选项卡
    $tabs = '<div class="card-header widget-header">';
    $tabs .= '<div class="overflow-x-auto no-scrollbar">';
    foreach ($args as $index => $value) {
        $class = 'hotapi-tab-btn d-inline-block';
        $class .= $index === 0 ? ' active' : '';

        $tabs .= io_get_hot_api_title_html($value, $class, $index, 'tab');
    }
    $tabs .= '</div>';
    $tabs .= '</div>';

    $body = '';
    foreach ($args as $index => $value) {
        $iframe  = (io_get_option('hot_iframe', false) && $value['is_iframe'] && !wp_is_mobile()) ? 1 : 0;
        $rule_id = $value['rule_id'];
        $type    = $value['hot_type'];
        $class   = 'hotapi-' . $type . '-' . $index;
        $class .= $index === 0 ? ' active' : '';

        $body .= '<div class="hotapi-card ' . $class . '" data-index="' . $index . '" data-rule_id="' . $rule_id . '" data-is_iframe="' . $iframe . '" data-api_type="' . $type . '" data-type="tab">';
        $body .= io_get_hot_api_body_html($value);
        $body .= '</div>';
    }

    $html = '<div class="card hotapi-tab-card">';
    $html .= $tabs;
    $html .= $body;
    $html .= '</div>';

    return $html;
}
