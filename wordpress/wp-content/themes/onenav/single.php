<?php
/*
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:00
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-04 11:27:07
 * @FilePath: /onenav/single.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); 
?>
<main role="main" class="container my-2">
    <?php 
    $is_hide = false;
    $header  = io_single_post_header($is_hide);
    iopay_get_auto_ad_html('page', 'mb-3');

    echo get_single_breadcrumb();

    ?>
    <div class="content">
        <div class="content-wrap">
            <div class="content-layout">
            <?php 

            if($is_hide){
                echo $header;
            }else{
                io_single_post_content();
            }

            if (io_get_option('post_related', true)) {
                $related_title = '<i class="site-tag iconfont icon-book icon-lg mr-1" ></i>' . __('相关文章', 'i_theme');
                echo io_posts_related('post', $related_title, 4);
            }
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif; 
            ?>
            </div> 
        </div> 
    <?php get_sidebar(); ?>
    </div>
</main>
<?php get_footer(); ?>