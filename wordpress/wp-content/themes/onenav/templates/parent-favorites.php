<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:02
 * @LastEditors: iowen
 * @LastEditTime: 2022-07-17 22:16:24
 * @FilePath: \onenav\templates\parent-favorites.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }?>
<?php 
get_header();

global $is_sidebar; 
$is_sidebar = false; //分类页，无侧边栏模块，排除自动减一

$children = get_categories(array(
    'taxonomy'   => 'favorites',
    'meta_key'   => '_term_order',
    'orderby'    => 'meta_value_num',
    'order'      => 'desc',
    'child_of'   => get_queried_object_id(),
    'hide_empty' => 0
));
/**
 * -----------------------------------------------------------------------
 * HOOK : ACTION HOOK
 * io_parent_tax_card_begin_code
 * 
 * 在父分类页网址内容前挂载其他内容
 * @since   
 * -----------------------------------------------------------------------
 */
do_action( 'io_parent_tax_card_begin_code' ,$children ); 
?>
<div id="content" class="container container-fluid customize-width">
    <div class="card mb-4 p-title">
        <div class="card-body">
            <h1 class="text-gray text-lg m-0"><?php single_cat_title() ?></h1>
        </div>
    </div>
    <?php
    // 加载网址模块  
    if( $children ){  
        foreach($children as $mid) { 
            fav_con($mid);
        } 
    } 
    ?>   
</div> 
<?php
get_footer();
