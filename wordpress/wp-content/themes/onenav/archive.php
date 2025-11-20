<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:00
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 21:33:38
 * @FilePath: /onenav/archive.php
 * @Description: 分类、标签、搜索、作者、日期归档页面
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( is_category() ) {
    $taxonomy_type = 'category';
} elseif ( is_tag() ) {
    $taxonomy_type = 'post_tag';
} else {
    $taxonomy_type = get_query_var('taxonomy');
}
$post_type = get_post_types_by_taxonomy($taxonomy_type);

get_header(); 

io_get_taxonomy_head_html($post_type, $taxonomy_type);

?>
<main class="container is_category my-2" role="main">
    <div class="content-wrap">
        <div class="content-layout ajax-load-page">
            <?php
            io_get_taxonomy_head_content_html($post_type, $taxonomy_type, 0, 'mb-4');
            io_get_taxonomy_select_html($post_type, $taxonomy_type);
            io_get_taxonomy_content_html($post_type);
            ?>
            <div class="posts-nav my-3">
                <?php io_paging(io_get_option('ajax_next_page', true)) ?>
            </div>
        </div> 
    </div>
	<?php get_sidebar(); ?>
</main> 
<?php 
get_footer(); 
