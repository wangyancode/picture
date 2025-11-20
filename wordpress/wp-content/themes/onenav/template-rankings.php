<?php
/*
 * @Template Name: 排行榜
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-07-02 16:29:55
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-11 00:11:06
 * @FilePath: /onenav/template-rankings.php
 * @Description: 
 */
get_header();

io_get_rankings_head_html();

?>
<main class="container my-2">
    <div class="content-wrap">
        <div class="content-layout ajax-load-page">
        <?php io_get_rankings_content_html() ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php 

get_footer();
