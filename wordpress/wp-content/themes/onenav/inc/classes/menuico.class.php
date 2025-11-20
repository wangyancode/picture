<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:02
 * @LastEditors: iowen
 * @LastEditTime: 2024-08-28 15:15:07
 * @FilePath: /onenav/inc/classes/menuico.class.php
 * @Description: 菜单自定义图标
 */
class MENUICO {
    function __construct(){
        add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_css_class' ),1,3 );
        add_filter( 'walker_nav_menu_start_el', array( $this, 'walker_nav_menu_start_el' ), 10, 4 );
    } 

    function nav_menu_css_class($classes, $item, $args){
        if($args->theme_location == 'nav_main') { //这里的 main 是菜单id
            $classes[] = 'sidebar-item'; //这里的 nav-item 是要添加的class类
        } 

        if( is_array( $classes ) ){
            $tmp_classes = preg_grep( '/^(fa[b|s]?|io)(-\S+)?$/i', $classes );
            if( !empty( $tmp_classes ) ){
                $classes = array_values( array_diff( $classes, $tmp_classes ) );
            }
        }
        return $classes;
    }

    protected function replace_item( $item_output, $icon, $arrow){
        preg_match( '/(<a.+>)(.+)(<\/a>)/i', $item_output, $matches );
        if (4 === count($matches)) {
            $item_output = $matches[1];
            $item_output .= ($icon . '<span>' . $matches[2] . '</span>' . $arrow);
            $item_output .= $matches[3];
        }
        return $item_output;
    }

    function walker_nav_menu_start_el($item_output, $item, $depth, $args) {
        if (!$classes = get_post_meta($item->ID, 'menu_ico', true)) {
            $classes = preg_grep('/^(fa[b|s]?|io)(-\S+)?$/i', $item->classes);
            if (!empty($classes)) {
                $classes = implode(" ", $classes);
            } elseif ($args->theme_location == 'nav_main' && $depth == 0) {
                $classes = 'iconfont icon-category';
            }
        }
        $has_children = !empty($item->classes) && in_array('menu-item-has-children', $item->classes);

        $icon  = $classes ? '<i class="' . $classes . ' icon-fw icon-lg mr-2"></i>' : '';
        $arrow = $has_children ? '<i class="iconfont icon-arrow-b"></i>' : '';

        if (!empty($icon) || !empty($arrow)) {
            $item_output = $this->replace_item($item_output, $icon, $arrow);
        }
        return $item_output;
    }
}
new MENUICO();
