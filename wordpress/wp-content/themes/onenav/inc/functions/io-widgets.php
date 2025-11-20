<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-09 14:00:16
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-16 22:36:34
 * @FilePath: /onenav/inc/functions/io-widgets.php
 * @Description: 
 */

/**
 * 新窗口
 * @param mixed $instance
 * @return bool
 */
function is_new_window($instance){
    $newWindow = false;
    if ((isset($instance['window']) && $instance['window']) || 
        (isset($instance['newWindow']) && $instance['newWindow']) || //向下兼容
        (isset($instance['new-window']) && $instance['new-window']) //向下
    ){
        $newWindow = true;
    }
    return $newWindow;
}
/**
 * 侧边栏网址html
 * 
 * @param WP_Query $myposts
 * @param array $instance 选项
 * @param int $index 计数
 * @return array|string
 */
function load_widgets_min_sites_html($myposts, $instance, $index = '') {
    $new_window = is_new_window($instance);
    $exclude    = array();

    $card_args = array(
        'tag'      => 'div',
        'window'   => $new_window,
        'go'       => $instance['go'],
        'go_ico'   => false,
        'no_tip'   => true,
        'nofollow' => $instance['nofollow'],
        'class'    => 'muted-bg br-md' . (isset($instance['class']) ? ' ' . $instance['class'] : '')
    );

    $temp = '';
    $_i   = empty($index) ? 0 : $index;
    if (!$myposts->have_posts()):
        $temp .= empty($index) ? get_none_html(__('没有数据！', 'i_theme'), 'min-svg') : '';
    elseif ($myposts->have_posts()):
        while ($myposts->have_posts()):
            $myposts->the_post();
            $_i++;
            $exclude[] = get_the_ID();
            if ($instance['only_title']) {
                $temp .= get_only_title_card($instance, $instance['serial'] ? $_i : 0);
            } else {
                $temp .= get_sites_card($instance['style'], $card_args);
            }
        endwhile;
        wp_reset_postdata();
    endif;
    
    if ('' === $index) {
        return $temp;
    }
    return array(
        'html'    => $temp,
        'index'   => $myposts->post_count,
        'exclude' => $exclude,
    );
}

/**
 * 侧边栏文章html
 * 
 * @param WP_Query $myposts
 * @param array $instance 选项
 * @param int $index 计数
 * @return array|string
 */
function load_widgets_min_post_html($myposts, $instance, $index = '') {
    $new_window  = is_new_window($instance);
    $show_thumbs = isset($instance['show_thumbs']) ? !$instance['show_thumbs'] : false;
    $meta_key    = isset($instance['meta-key']) ? $instance['meta-key'] : 'views'; //需要显示的元数据
    $exclude     = array();

    $meta = array(
        'views'   => false,
        'like'    => false,
        'comment' => false,
    );
    if (isset($meta[$meta_key])) {
        $meta[$meta_key] = true;
    }

    $card_args = array(
        'tag'    => 'div',
        'window' => $new_window,
        'no_img' => $show_thumbs,
        'meta'   => $meta,
        'class'  => isset($instance['class']) ? $instance['class'] : ''
    );

    $temp = '';
    $_i   = empty($index) ? 0 : $index;
    if (!$myposts->have_posts()):
        $temp .= empty($index) ? get_none_html(__('没有数据！', 'i_theme'), 'min-svg') : '';
    elseif ($myposts->have_posts()):
        while ($myposts->have_posts()):
            $myposts->the_post();
            $_i++;
            $exclude[] = get_the_ID();
            if ($instance['only_title']) {
                $temp .= get_only_title_card($instance, $instance['serial'] ? $_i : 0);
            } else {
                $temp .= get_post_card($instance['style'], $card_args);
            }
        endwhile;
        wp_reset_postdata();
    endif;
    
    if ('' === $index) {
        return $temp;
    }
    return array(
        'html'    => $temp,
        'index'   => $myposts->post_count,
        'exclude' => $exclude,
    );
}

/**
 * 侧边栏 APP html
 * 
 * @param WP_Query $myposts
 * @param array $instance 选项
 * @param int $index 计数
 * @return array|string
 */
function load_widgets_min_app_html($myposts, $instance, $index = '') {
    $new_window = is_new_window($instance);
    $meta_key   = isset($instance['meta-key']) ? $instance['meta-key'] : 'views';
    $exclude    = array();

    $meta = array(
        'views'   => false,
        'like'    => false,
        'comment' => false,
        'down'    => true,
    );
    if (isset($meta[$meta_key])) {
        $meta[$meta_key] = true;
    }

    $card_args = array(
        'tag'    => 'div',
        'window' => $new_window,
        'meta'   => $meta,
        'class'  => 'no-padding' . (isset($instance['class']) ? ' ' . $instance['class'] : '')
    );

    $temp = '';
    $_i   = empty($index) ? 0 : $index;
    if (!$myposts->have_posts()):
        $temp .= empty($index) ? get_none_html(__('没有数据！', 'i_theme'), 'min-svg') : '';
    elseif ($myposts->have_posts()):
        while ($myposts->have_posts()):
            $myposts->the_post();
            $_i++;
            $exclude[] = get_the_ID();
            if ($instance['only_title']) {
                $temp .= get_only_title_card($instance, $instance['serial'] ? $_i : 0);
            } else {
                $temp .= get_app_card($instance['style'], $card_args);
            }
        endwhile;
        wp_reset_postdata();
    endif;
    
    if ('' === $index) {
        return $temp;
    }
    return array(
        'html'    => $temp,
        'index'   => $myposts->post_count,
        'exclude' => $exclude,
    );
}

/**
 * 侧边栏书籍html
 * 
 * @param WP_Query $myposts
 * @param array $instance 选项
 * @param int $index 计数
 * @return array|string
 */
function load_widgets_min_book_html($myposts, $instance, $index = '') {
    $new_window = is_new_window($instance);
    $exclude     = array();

    $card_args = array(
        'tag'    => 'div',
        'window' => $new_window,
        'class'  => isset($instance['class']) ? ' ' . $instance['class'] : ''
    );

    $temp = '';
    $_i   = empty($index) ? 0 : $index;
    if (!$myposts->have_posts()):
        $temp .= empty($index) ? get_none_html(__('没有数据！', 'i_theme'), 'min-svg') : '';
    elseif ($myposts->have_posts()):
        while ($myposts->have_posts()):
            $myposts->the_post();
            $_i++;
            $exclude[] = get_the_ID();
            if ($instance['only_title']) {
                $temp .= get_only_title_card($instance, $instance['serial'] ? $_i : 0);
            } else {
                $temp .= get_book_card($instance['style'], $card_args);
            }
        endwhile;
        wp_reset_postdata();
    endif;
    
    if ('' === $index) {
        return $temp;
    }
    return array(
        'html'    => $temp,
        'index'   => $myposts->post_count,
        'exclude' => $exclude,
    );
}

/**
 * 侧边栏类型是否启用
 * @param mixed $taxonomy
 * @return bool
 */
function io_is_widget_posts($taxonomy)
{
    $posts_type_s = wp_parse_args((array) io_get_option('posts_type_s'), ['post']);
    $type         = get_post_types_by_taxonomy($taxonomy);
    return in_array($type, $posts_type_s) ? true : false;
}
