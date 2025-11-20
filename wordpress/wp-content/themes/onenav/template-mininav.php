<?php
/*
 * @Template Name: 次级导航
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-28 16:29:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-29 23:43:35
 * @FilePath: /onenav/template-mininav.php
 * @Description: 
 */
get_header();

$post_id = get_the_ID();
io_home_content(get_page_module_config($post_id));

get_footer();

