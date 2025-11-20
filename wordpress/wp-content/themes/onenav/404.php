<?php 
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:00
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-04 23:35:54
 * @FilePath: /onenav/404.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); 
?>
<div class="container">
    <section class="text-center mb-5">
        <h1 class="h3 d-none">404</h1>
        <?php echo get_none_html(__('抱歉，没有你要找的内容...', 'i_theme'), 'max-svg', '404') ?> 
        <div style="margin-top: 30px">
            <a class="btn btn-shadow vc-red btn-lg px-5 rounded-pill text-lg" href="<?php echo esc_url( home_url() ) ?>"><?php _e('返回首页','i_theme') ?></a>
        </div>
    </section>
</div>
<?php get_footer(); ?>