<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:06
 * @LastEditors: iowen
 * @LastEditTime: 2024-08-30 18:36:02
 * @FilePath: /onenav/index.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

io_home_content(get_page_module_config());

get_footer();
