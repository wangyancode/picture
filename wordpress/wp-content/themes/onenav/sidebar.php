<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:07
 * @LastEditors: iowen
 * @LastEditTime: 2024-08-30 14:57:13
 * @FilePath: /onenav/sidebar.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if (io_is_show_sidebar()==" sidebar_no" || wp_is_mobile()) return; 
?>
<div class="sidebar sidebar-tools d-none d-lg-block">
	<?php 
	if ( (is_home() || is_front_page())  ) {
		
	} 
	?>
	<?php if (is_single()) : ?>
		<?php if ( ! dynamic_sidebar( 'sidebar-s' ) ) : ?>
			<div id="add-widgets" class="card widget_text bk">
				
				<div class="card-header">
					<span><i class="iconfont icon-category mr-2"></i><?php _e('添加小工具','i_theme') ?></span>
				</div>
				<div class="card-body text-sm">
					<a href="<?php echo admin_url(); ?>widgets.php" target="_blank"><?php _e('点此为“正文侧边栏”添加小工具','i_theme') ?></a>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if (is_page() ) : ?>
		<?php 
		if( is_mininav() ){
			$sidebar_id = 'sidebar-page-'.get_queried_object_id(); // 次级导航
		}else{
			$sidebar_id = 'sidebar-page';
		}
		?>
		<?php if ( ! dynamic_sidebar( $sidebar_id ) ) : ?>
			<div id="add-widgets" class="card widget_text bk">
				
				<div class="card-header">
					<span><i class="iconfont icon-category mr-2"></i><?php _e('添加小工具','i_theme') ?></span>
				</div>
				<div class="card-body text-sm">
					<a href="<?php echo admin_url(); ?>widgets.php" target="_blank"><?php _e('点此为“页面侧边栏”添加小工具','i_theme') ?></a>
				</div>
			</div>
		<?php endif;?>
	<?php endif; ?>

	<?php if ( is_archive() || is_search() || is_404() ) : ?>
		<?php if ( ! dynamic_sidebar( 'sidebar-a' ) ) : ?>
			<div id="add-widgets" class="card widget_text bk">
				<div class="card-header">
					<span><i class="iconfont icon-category mr-2"></i><?php _e('添加小工具','i_theme') ?></span>
				</div>
				<div class="card-body text-sm">
					<a href="<?php echo admin_url(); ?>widgets.php" target="_blank"><?php _e('点此为“分类归档侧边栏”添加小工具','i_theme') ?></a>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
