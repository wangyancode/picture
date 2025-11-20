<?php 
/*
 * @Template Name: 公告列表
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-20 16:23:07
 * @LastEditors: iowen
 * @LastEditTime: 2025-01-22 17:38:44
 * @FilePath: /onenav/template-bulletin.php
 * @Description: 
 */

if( !io_get_option('show_bulletin') ){
    if(is_super_admin()){
        wp_die('请前往「主题设置/内容页面/公告设置」启用公告');
        exit;
    }
    set404();
    exit;
}
$img_bg = get_lazy_img_bg(io_get_option('bull_img',''));

get_header(); 
?>
<div id="content" class="container my-2">

<div class="post-cover overlay-hover mb-3 mb-md-4">
    <div class="media br-xl media-5x1">
        <div class="media-content" <?php echo $img_bg ?>><span class="overlay"></span></div>
        <div class="card-img-overlay d-flex justify-content-center text-center flex-column p-3 p-md-4">
            <h1 class="h4 text-white">—— <?php _e('公告','i_theme') ?> ——</h1>      
            <div class="text-white">
                <small><?php _e('总计：','i_theme') ?><?php echo wp_count_posts( 'bulletin')->publish ?></small>
            </div>          
        </div>
    </div>
</div>

<main class="content" role="main">
<div class="content-wrap">
    <div class="content-layout"> 
        <div class="cat_list">
        <?php  
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'post_type' => 'bulletin',
            'orderby'   => 'date',
            'paged' => $paged
        );
        query_posts( $args );  
        if ( have_posts() ) : 
        while ( have_posts() ) : the_post(); 
        ?>
        <article class="card bulletin-card mb-3">
            <div class="card-body py-3 px-3 px-md-4 d-flex flex-fill text-muted">
                <div><i class="iconfont icon-instructions"></i></div>
                <div class="bulletin-swiper mx-1 mx-md-2">
                    <?php 
                    if(get_post_meta(get_the_ID(),'_goto',true)){
                        the_title( sprintf( '<p class="scrolltext-title line1"><a href="%s" target="_blank" rel="bulletin noreferrer noopener%s">', esc_url( get_permalink() ),get_post_meta(get_the_ID(),'_nofollow',true)?' external nofollow':'' ), '</a></p>' );
                    }else{
                        the_title( sprintf( '<p class="scrolltext-title line1"><a href="%s" rel="bulletin">', esc_url( get_permalink() ) ), '</a></p>' ); 
                    }
                    ?>
                </div>
                <div class="flex-fill"></div>
                <div class="text-muted text-xs" style="white-space:nowrap;line-height:1.5625rem"><i class="iconfont icon-time mr-2"></i><?php wp_is_mobile()? the_time('m/d') : the_time(__('Y年m月d日','i_theme')) ?></div>
            </div>
        </article>
        <?php endwhile;endif; ?>

        </div>
        <div class="posts-nav">
        <?php 
        echo paginate_links(array(
            'prev_next'          => 0,
            'before_page_number' => '',
            'mid_size'           => 2,
        ));
        wp_reset_query();
        ?>
        </div>
    </div> 
</div>
<?php get_sidebar("bulletin"); ?>
</main>
</div>
<?php get_footer(); ?>
