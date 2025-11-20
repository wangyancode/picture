<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-18 21:12:13
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-10 22:43:38
 * @FilePath: /onenav/inc/functions/io-tools-hotcontent.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 获取热门TAB模块内容
 */
function get_home_hot_card($data){
    global $post;
    $args = array(
        'post_type'           => $data['type'],
        'post_status'         => array( 'publish', 'private' ),//'publish',
        'perm'                => 'readable',
        'ignore_sticky_posts' => 1,
        'posts_per_page'      => $data['num'],
    );
    switch ($data['order']) {
        case 'modified':
        case 'date':
            $args['orderby'] = $data['order'];
            $args['order']   = 'DESC';
            break;
        case 'comment_count':
            $args['orderby'] = array( $data['order'] => 'DESC', 'ID' => 'DESC' );
            break;
        case 'random':
            $args['orderby'] = 'rand';
            break;
        
        default:
            $args['meta_key'] = $data['order'];
            $args['orderby'] = array( 'meta_value_num' => 'DESC', 'date' => 'DESC' );
            break;
    }
    $myposts = new WP_Query($args);
    if (!$myposts->have_posts()){ 
    ?>
        <div class="col-lg-12">
            <div class="nothing mb-4"><?php _e('没有数据！请开启统计并等待产生数据', 'i_theme'); ?></div>
        </div>
    <?php
    }elseif ($myposts->have_posts()){
        while ($myposts->have_posts()): $myposts->the_post();
        if ($data['type'] == 'sites') {
        ?>
        <?php if ($data['mini']) {?>
            <div class="url-card col-6 <?php get_columns('sites','',true,false,'mini') ?> col-xxl-10a <?php echo io_sites_before_class($post->ID) ?>">
            <?php include(get_theme_file_path('/templates/card-sitemini.php')); ?>
            </div>
        <?php } else {?>
            <div class="url-card <?php get_columns('sites','') ?> <?php echo io_sites_before_class($post->ID) ?>">
            <?php include(get_theme_file_path('/templates/card-site.php'));?>
            </div>
        <?php } ?>
        <?php 
        } elseif ($data['type'] == 'app') { ?>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-5a ">
            <?php
            include(get_theme_file_path('/templates/card-appcard.php'));
            ?>
            </div>
            <?php
        } elseif ($data['type'] == "book") {
            echo'<div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xxl-8a">';
            include(get_theme_file_path('/templates/card-book.php'));
            echo'</div>';
        } elseif ($data['type'] == "post") {
            echo '<div class="io-px-2 col-4 col-md-3 col-xl-2 col-xxl-8a py-2 py-md-3">';
            get_template_part( 'templates/card','post' );
            echo '</div>';
        }
        endwhile;
    }
    wp_reset_postdata();
}