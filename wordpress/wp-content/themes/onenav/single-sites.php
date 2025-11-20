<?php
/*
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:00
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 19:03:34
 * @FilePath: /onenav/single-sites.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); 

global $sites_type;
$sites_type = get_post_meta(get_the_ID(), '_sites_type', true);
$sites_type = $sites_type ?: 'sites';
?>
<main class="container my-2" role="main">
    <?php 

    iopay_get_auto_ad_html('page', 'mb-3');
    echo get_single_breadcrumb('sites');
    
    $header  = io_site_header($is_hide);
    if (!$is_hide){
        echo $header;
    }
    ?>
    <div class="content">
        <div class="content-wrap">
            <div class="content-layout">
                <?php  
                if($is_hide){
                    echo $header;
                }else{
                    io_site_content();
                }
                if (io_get_option('sites_related', true)) {
                    $related_title = '<i class="site-tag iconfont icon-tag icon-lg mr-1" ></i>' . __('相关导航', 'i_theme');
                    echo io_posts_related('sites', $related_title, 8);
                }
                if (comments_open() || get_comments_number()) {
                    comments_template();
                }
                ?>
            </div><!-- content-layout end -->
        </div><!-- content-wrap end -->
    <?php get_sidebar('sites');  ?>
    </div>
</main><!-- container end -->
<?php 

get_footer();
