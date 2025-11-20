<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-01 19:02:29
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-06 19:29:28
 * @FilePath: /onenav/inc/functions/io-cache.php
 * @Description: 
 */

/**
 * 在文章更新时清除缓存
 * 
 * @param int $post_id 文章ID
 */
function io_clear_posts_cache($post_id) {
    // 清理缓存的键
    $taxonomy = get_object_taxonomies(get_post_type($post_id)); // 获取文章的所有分类法

    $cache_key = 'cat_btn_' . $post_id . '_' . md5(implode('_', $taxonomy));

    wp_cache_delete($cache_key, 'cat_btn');
    
}
add_action('save_post', 'io_clear_posts_cache');
add_action('delete_post', 'io_clear_posts_cache');
add_action('clean_post_cache', 'io_clear_posts_cache');

/**
 * 刷新页面缓存
 * 
 * 模板文件对应的缓存
 * @see io_get_template_page_url()
 * @param mixed $post_id
 * @param mixed $post
 * @param mixed $update
 * @return void
 */
function io_refresh_page_url_cache($post_id, $post, $update)
{
    $template = get_post_meta($post_id, '_wp_page_template', true);

    if (!empty($template)) {
        wp_cache_delete($template, 'page_url');
        wp_cache_delete($template . '_is_id', 'page_url');
    }
}
add_action('save_post_page', 'io_refresh_page_url_cache', 10, 3);

/**
 * 清理 io_counts 缓存
 * 
 * @see io_count_posts()
 * @param mixed $post_id
 * @return void
 */
function io_clear_post_counts_cache($post_id)
{
    $post = get_post($post_id);
    if (!$post)
        return;

    $post_type = $post->post_type;

    // 清除 publish/draft/... 所有状态的缓存  
    $key = "posts-{$post_type}";
    wp_cache_delete($key, 'io_counts');
}
add_action( 'save_post', 'io_clear_post_counts_cache', 10 );
add_action( 'delete_post', 'io_clear_post_counts_cache', 10 );
add_action( 'trash_post',  'io_clear_post_counts_cache', 10 );

/**
 * 清除文章缩略图缓存
 * 
 * @see io_get_post_first_img()
 * @param int $post_id 文章ID
 */
function io_clear_post_first_img_cache($post_id)
{
    wp_cache_delete('first_img_' . $post_id . '_url', 'post_meta');
    wp_cache_delete('first_img_' . $post_id . '_array', 'post_meta');
}
add_action('save_post', 'io_clear_post_first_img_cache', 10);
