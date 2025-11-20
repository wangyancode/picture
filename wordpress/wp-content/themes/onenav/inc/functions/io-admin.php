<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-04-02 22:08:22
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-07 14:18:00
 * @FilePath: /onenav/inc/functions/io-admin.php
 * @Description: 
 */

/**
 * 添加古腾堡区块内容
 * @return void 
 */
function io_theme_block() {
    
    $depends = array( 'wp-blocks', 'wp-element', 'wp-components' );

    if ( wp_script_is( 'wp-edit-widgets' ) ) {
        $depends[] = 'wp-edit-widgets';
    } else {
        $depends[] = 'wp-edit-post';
    }

    wp_register_script( 
        'io_block',
        get_template_directory_uri() . '/assets/js/gutenberg-edit.js',
        $depends,
        IO_VERSION
    );

    wp_register_style(
        'io_block',
        get_template_directory_uri() . '/assets/css/gutenberg-edit.css',
        array( 'wp-edit-blocks' ),
        IO_VERSION
    );

    register_block_type( 'io/block', array(
        'editor_script' => 'io_block',
        'editor_style'  => 'io_block',
    ) );

}
if (function_exists('register_block_type')) {
    add_action('init', 'io_theme_block');
    $wp_version = get_bloginfo('version', 'display');

    if (version_compare('5.7.9', $wp_version) == -1) {
        add_filter('block_categories_all', function ($categories, $post) {
            return io_add_block_categories($categories, $post);
        }, 10, 2);
    } else {
        add_filter('block_categories', function ($categories, $post) {
            return io_add_block_categories($categories, $post);
        }, 10, 2);
    }
}

function io_add_block_categories($categories, $post){
    $is_one = false;
    foreach ($categories as $value) {
        if($value['slug'] == 'io_block_cat'){
            $is_one = true;
            break;
        }
    }
    if($is_one){
        return $categories;
    }
    return array_merge(
        array(
            array(
                'slug'  => 'io_block_cat',
                'title' => 'IO 模块',
            ),
        ),
        $categories
    );
}
