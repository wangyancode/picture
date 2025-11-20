<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:00
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 19:17:22
 * @FilePath: /onenav/single-app.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); 

global $app_type;
$app_type = get_post_meta(get_the_ID(), '_app_type', true);
$app_type = $app_type ?: 'app';
?>
<main class="container my-2" role="main">
    <?php
    echo io_header_fx();

    iopay_get_auto_ad_html('page', 'mb-3');

    $header  = io_app_header($is_hide);
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
                    io_app_content();
                }
                if (io_get_option('app_related', true)) {
                    $related_title = '<i class="site-tag iconfont icon-tag icon-lg mr-1" ></i>' . sprintf(__('相关%s', 'i_theme'), get_app_type_name($app_type));
                    echo io_posts_related('app', $related_title, 3);
                }
                if (comments_open() || get_comments_number()) {
                    comments_template();
                }
                ?>
            </div><!-- content-layout end -->
        </div><!-- content-wrap end -->
        <?php get_sidebar('app');  ?>
    </div>
</main>
<?php 
get_footer();
