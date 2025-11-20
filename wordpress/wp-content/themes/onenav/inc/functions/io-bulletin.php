<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-09-05 14:12:10
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-05 15:48:27
 * @FilePath: /onenav/inc/functions/io-bulletin.php
 * @Description: 
 */

/**
 * 获取公告卡片
 * @param mixed $count
 * @param mixed $type
 * @return string
 */
function io_bulletin_card($count, $type='head') {
    $args = array(
        'post_type'      => 'bulletin',
        'posts_per_page' => $count
    );

    $html      = '';
    $i         = 0;
    $the_query = new WP_Query($args);
    while ($the_query->have_posts()):
        $the_query->the_post();
        $goto     = get_post_meta(get_the_ID(), '_goto', true);
        $is_go    = get_post_meta(get_the_ID(), '_is_go', true);
        $nofollow = get_post_meta(get_the_ID(), '_nofollow', true);


        $active = $i == 0 ? 'active' : '';
        $url    = $goto ? ($is_go ? go_to($goto) : $goto) : esc_url(get_permalink());
        $date   = get_the_time('m/d');
        $rel    = $goto && $nofollow && !$is_go  ? 'noopener external nofollow' : 'noopener';
        $target = $goto ? ' target="_blank"' : '';

        if ('head' === $type) {
            $html .= '<div class="carousel-item ' . $active . '">';
            $html .= '<a class="line1" href="' . $url . '" rel="' . $rel . '"' . $target . '>' . get_the_title() . ' (' . $date . ')</a>';
            $html .= '</div>';
        } else {

        }

        $i++;
    endwhile;
    wp_reset_postdata();
    
    return $html;
}

/**
 * 公告盒子
 * @param mixed $class
 * @return string
 */
function io_head_bulletin_box($class = '') {
    if (!io_get_option('show_bulletin', false) || !io_get_option('bulletin', false)) {
        return '';
    }
    $count = io_get_option('bulletin_n', 5);
    $card  = io_bulletin_card($count, 'head');
    $btn   = '<a title="' . __('关闭', 'i_theme') . '" href="javascript:;" rel="external nofollow" class="bulletin-close" onClick="$(\'#bulletin_box\').slideUp(\'slow\');"><i class="iconfont icon-close" style="line-height:25px"></i></a>';

    $html = <<<HTML
    <div id="bulletin_box" class="{$class} card my-2" >
        <div class="card-body py-1 px-2 px-md-3 d-flex flex-fill text-xs text-muted">
            <div><i class="iconfont icon-bulletin" style="line-height:25px"></i></div>
            <div class="bulletin-swiper mx-1 mx-md-2 carousel-vertical">
                <div class="carousel slide" data-ride="carousel" data-interval="3000">
                    <div class="carousel-inner" role="listbox">
                        {$card}
                    </div>
                </div>
            </div> 
            <div class="flex-fill"></div>
            {$btn}
        </div>
    </div>
HTML;

    return $html;
}