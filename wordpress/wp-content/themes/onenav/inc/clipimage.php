<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-01-25 03:01:51
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-06 19:34:21
 * @FilePath: /onenav/inc/clipimage.php
 * @Description: 定时任务
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 文章缩略图或图片处理操作相关
 */

/**
 * 添加特色缩略图支持
 * 如果需要，取消下面注释
 */
if ( function_exists('add_theme_support') )add_theme_support('post-thumbnails');

/**
 * 获取文章特色图地址
 * 
 * @param WP_Post|null $post 文章对象，默认为null时使用全局$post
 * @return string 图片URL
 */
function io_theme_get_thumb($post = null)
{
    // 确保获取有效的post对象
    if ($post === null) {
        global $post;
    }

    if (!$post instanceof WP_Post) {
        return get_theme_file_uri('/assets/images/t.png');
    }

    // 尝试获取特色图
    if (has_post_thumbnail($post->ID)) {
        $thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID));
        if ($thumbnail_src && !empty($thumbnail_src[0])) {
            return $thumbnail_src[0];
        }
    }

    // 尝试获取文章中的第一张图片
    $strResult = io_get_post_first_img($post, true);
    if (!empty($strResult[1][0])) {
        return $strResult[1][0];
    }

    // 使用随机图片或默认图片
    $random_img = io_split_str(io_get_option('random_head_img', ''));
    if (!empty($random_img)) {
        $random_img_array = array_rand($random_img);
        return trim($random_img[$random_img_array]);
    }

    // 默认返回
    return get_theme_file_uri('/assets/images/t.png');
}

/**
 * 获取文章内容中的第一张图片地址（带缓存支持）
 * 
 * @param WP_Post $post 文章对象
 * @param bool $return_array 是否返回完整匹配数组
 * @return string|array|null 图片URL/匹配数组/null
 */
function io_get_post_first_img($post, $return_array = false) {
    if (empty($post->ID)) {
        return $return_array ? [] : null;
    }

    $cache_key = 'first_img_' . $post->ID . ($return_array ? '_array' : '_url');
    $cached = wp_cache_get($cache_key, 'post_meta');
    if ($cached !== false) {
        return $cached;
    }

    // 快速检查内容是否包含图片
    if (empty($post->post_content) || stripos($post->post_content, '<img') === false) {
        $result = $return_array ? [] : null;
        wp_cache_set($cache_key, $result, 'post_meta');
        return $result;
    }

    // 匹配图片标签（支持 src/data-src/data-lazy-src）
    preg_match_all(
        '/<img\s+[^>]*?(?:src|data-src|data-lazy-src)=[\'"]([^\'"]+)[\'"][^>]*>/i',
        $post->post_content,
        $matches
    );

    $result = $return_array 
        ? (empty($matches[1]) ? [] : $matches)
        : (!empty($matches[1][0]) ? esc_url($matches[1][0]) : null);

    wp_cache_set($cache_key, $result, 'post_meta');
    return $result;
}

    
/**
 * 获取/输出缩略图地址
 */
function io_get_thumbnail($size = 'thumbnail',$isback = false){
    $post_thumbnail_src = io_theme_get_thumb();
    if($isback){
        return getOptimizedImageUrl($post_thumbnail_src, $size,'90');
    }
    if(io_get_option('lazyload',false)){
        $loadimg_url = get_theme_file_uri('/assets/images/t.png');
        return 'src="'.$loadimg_url.'" data-src="'.getOptimizedImageUrl($post_thumbnail_src, $size,'90').'"';
    } else {
        return 'src="'.getOptimizedImageUrl($post_thumbnail_src, $size,'90').'"';
    }
}

/**
 * 获取Timthumb裁剪的图片链接
 */
function getTimthumbImage($url, $size = 'thumbnail', $q='70', $nohttp = false){
    if($nohttp)
        $timthumb =  get_theme_file_uri('/timthumb.php');
    else
        $timthumb = str_replace(array('https:','http:'),array('',''), get_theme_file_uri()) . '/timthumb.php';
    // 不裁剪Gif，因为生成黑色无效图片
    $imgtype = strtolower(substr($url, strrpos($url, '.')));
    if($imgtype === 'gif') return $url;

    $size = getFormatedSize($size);
    return $timthumb . stripslashes('?src=' . $url . '&q=' . $q . '&w=' . $size['width'] . '&h=' . $size['height'] . '&zc=1');
} 


/**
 * 根据用户设置选择合适的图片链接处理方式(timthumb|cdn)
 */
function getOptimizedImageUrl($url, $size, $q='70', $nohttp = false){
    if (!preg_match('/'. str_replace('/', '\/', IOTOOLS::urlRoot(home_url(),true)) .'/i',$url)) {
        //error_log("非法地址".$url.PHP_EOL, 3, "./php_3.log");
        return getTimthumbImage($url, $size, $q, $nohttp);
    }
    else{
        return getTimthumbImage($url, $size, $q, $nohttp);
    }
}


/**
 * 转换尺寸
 */
function getFormatedSize($size){
    if(is_array($size)){
        $width = array_key_exists('width', $size) ? $size['width'] : 225;
        $height = array_key_exists('height', $size) ? $size['height'] : 150;
        $str = array_key_exists('str', $size) ? $size['str'] : 'thumbnail';
    }else{
        switch ($size){
            case 'medium':
                $width = 375;
                $height = 250;
                $str = 'medium';
                break;
            case 'large':
                $width = 960;
                $height = 640;
                $str = 'large';
                break;
            default:
                $width = 225;
                $height = 150;
                $str = 'thumbnail';
        }
    }
    return array(
        'width'   =>  $width,
        'height'  =>  $height,
        'str'     =>  $str
    );
}
