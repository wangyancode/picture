<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:07
 * @LastEditors: iowen
 * @LastEditTime: 2022-06-25 22:49:28
 * @FilePath: \onenav\sidebar-bulletin.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } 
if (io_is_show_sidebar()==" sidebar_no" || wp_is_mobile()) return; 
?>
<div class="sidebar sidebar-tools d-none d-lg-block">
<?php 
if (is_single()) : 
	if ( ! dynamic_sidebar( 'sidebar-bulletin-r' ) ) : 
?>
	<div id="add-widgets" class="card widget_text bk">
		
		<div class="card-header">
			<span><i class="iconfont icon-category mr-2"></i><?php _e('添加小工具','i_theme') ?></span>
		</div>
		<div class="card-body text-sm">
			<a href="<?php echo admin_url(); ?>widgets.php" target="_blank"><?php _e('点此为“公告侧边栏”添加小工具','i_theme') ?></a>
		</div>
	</div>
<?php 
	endif;
endif;
if (is_page() ) {
	if ( ! dynamic_sidebar( 'sidebar-bull' ) ) : 
?> 
	<div id="add-widgets" class="card widget_text bk">
		
		<div class="card-header">
			<span><i class="iconfont icon-category mr-2"></i><?php _e('添加小工具','i_theme') ?></span>
		</div>
		<div class="card-body text-sm">
			<a href="<?php echo admin_url(); ?>widgets.php" target="_blank"><?php _e('点此为“公告侧边栏”添加小工具','i_theme') ?></a>
		</div>
	</div>
<?php 
	endif; 
}
?>
</div>