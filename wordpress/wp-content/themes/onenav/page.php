<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-03-01 10:19:06
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-10 17:44:53
 * @FilePath: /onenav/page.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main class="container my-2">
    <div class="content-wrap">
        <div class="content-layout">
        <div class="panel card">
        <div class="card-body">
            <div class="panel-header mb-4">
                <h1 class="h3"><?php echo get_the_title() ?>
                    <?php edit_post_link('<i class="iconfont icon-modify mr-1"></i>'.__('编辑','i_theme'), '<span class="edit-link text-xs text-muted">', '</span>' ); ?>
                </h1>
            </div>
            <div class="panel-body mt-2">
                <?php while( have_posts() ): the_post(); ?>
                <?php the_content();?>
                <?php endwhile; ?>
            </div>
        </div>
        </div>
        <?php 
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif; 
        ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php get_footer(); ?>