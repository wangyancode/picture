<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:07
 * @LastEditors: iowen
 * @LastEditTime: 2025-04-25 14:55:40
 * @FilePath: /onenav/search.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$type = esc_attr(get_query_var('post_type'));
io_set_history_search($s, $type);

switch ($type) {
	case 'app':
		$_class = 'row-col-2a row-col-md-3a row-col-lg-4a';
		break;
	case 'book':
		$_class = 'row-col-2a row-col-md-3a row-col-lg-4a';
		break;
	case 'post':
		$_class = 'row-col-2a row-col-md-3a row-col-lg-4a';
		break;
	
	default:
		$_class = 'row-col-1a row-col-md-2a row-col-lg-4a';
		break;
}
get_header(); 
?> 
	<div id="content" class="container mb-4 mb-md-5">
		<?php echo io_search_body_html() ?>
		<main class="content" role="main">
		<div class="content-wrap">
			<div class="content-layout">
				<div class="mb-4"> 
					<?php 
					$search_page = io_get_search_types();
					if(count($search_page)>1){
					foreach($search_page as $v){
						echo '<a class="btn btn-tab-h mr-2 text-gray ' . ($post_type == $v ? 'active' : '') . '" href="' . esc_url(home_url() . '?s=' . htmlspecialchars($s) . '&post_type=' . $v) . '" title="' . sprintf(__('有关“%s”的%s', 'i_theme'), htmlspecialchars($s), io_get_search_type_name($v)) . '">' . io_get_search_type_name($v) . '</a>';
					}}
					?>
				</div>
				<h4 class="text-gray text-lg mb-4"><i class="iconfont icon-search mr-1"></i><?php echo sprintf( __('“%s”的搜索结果', 'i_theme'), htmlspecialchars($s) ) ?></h4>
				<div class="posts-row <?php echo $_class ?>">
				<?php
				if (!have_posts()) { 
					echo get_none_html();
				}
				if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					switch ($type) {
						case 'app':
							echo get_app_card('max');
							break;
						case 'book':
							echo get_book_card(io_get_book_card_mode());
							break;
						case 'post':
							echo get_post_card('card');
							break;
						
						default:
							echo get_sites_card('max');
							break;
					}
				endwhile;
				endif;
				?>
				</div>
				<div class="posts-nav mb-4">
					<?php echo paginate_links(array(
						'prev_next'				=> 0,
						'before_page_number'	=> '',
						'mid_size'				=> 2,
					));?>
				</div>
			</div> 
		</div>
		<?php get_sidebar(); ?> 
		</main> 
	</div> 
<?php
get_footer(); 
