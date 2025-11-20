<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-08-05 19:58:10
 * @LastEditors: iowen
 * @LastEditTime: 2024-08-21 21:32:29
 * @FilePath: /onenav/inc/meta-menu.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!is_admin()) return;

if( class_exists( 'IOCF' ) && IO_PRO ) {

    $prefix = '_io_one_nav_menu_options_ico';
    IOCF::createNavMenuOptions( $prefix, array(
        'data_type' => 'unserialize', //   `serialize` or `unserialize`
        'depth' => 0, //作用层数
    ));
    IOCF::createSection( $prefix, array(
        'fields' => array(
            array(
                'id'    => 'menu_ico',
                'type'  => 'icon',
                'title' => '图标',
                'default' => 'iconfont icon-category'
            ),
        )
    ));
}
