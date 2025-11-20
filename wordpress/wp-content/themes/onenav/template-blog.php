<?php
/*
 * @Template Name: 博客页面
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:01
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-10 23:45:20
 * @FilePath: /onenav/template-blog.php
 * @Description: 
 */

get_header();

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$args  = array(
    'ignore_sticky_posts' => 1,
    'paged'               => $paged,
    'post_type'           => 'post',
);

if (isset($_GET['cat'])) {
    $args['cat'] = __post('cat');
}

//$args = io_add_visibility_tax_query($args);

?>
<main class="container my-2" role="main">
    <?php io_show_sidebar('max-blog-top-full-sidebar') ?>
    <div class="content">
        <div class="content-wrap">
            <div class="content-layout ajax-load-page">
                <?php echo get_ajax_load_posts() ?> 
                <div class="posts-row ajax-posts-row row-col-1a row-col-md-2a" data-style="post-min">
                    <?php 
                    query_posts( $args );
                    if ( have_posts() ) :  
                      while ( have_posts() ) : the_post(); 
                      echo get_post_card('min', ['class' => 'ajax-item']);
                      endwhile; 
                    endif;
                    ?>
                </div>
                <div class="posts-nav">
                    <?php 
                    io_paging();
                
                    wp_reset_query();
                    ?>
                </div>
                <?php io_show_sidebar('max-blog-bottom-sidebar') ?>
            </div> 
        </div> 
    <?php get_sidebar('blog'); ?>
    </div>
    <?php io_show_sidebar('max-blog-bottom-full-sidebar') ?>
</main>
<?php 
get_footer();


function get_ajax_load_posts()
{
    $cats = io_get_option('blog_index_cat', '');
    if (empty($cats))
        return;

    $cat    = __post('cat');
    $active = $cat ? '' : 'active';
    $link   = get_permalink();

    $html = '<div class="text-sm overflow-x-auto no-scrollbar white-nowrap blog-tab mb-3">';
    $html .= '<a href="' . esc_url($link) . '" class="btn btn-tab-h ajax-posts-load is-tab-btn py-0 text-sm ' . $active . ' m-1" ajax-method="card" data-cat="-1">' . __('最新文章', 'i_theme') . '</a>';

    foreach ($cats as $value) {
        $active = $cat == $value ? 'active' : '';
        $link   = add_query_arg('cat', $value, $link);

        $html .= '<a href="' . esc_url($link) . '" class="btn btn-tab-h ajax-posts-load is-tab-btn py-0 text-sm ' . $active . ' m-1" ajax-method="card" data-cat="' . $value . '">' . get_cat_name($value) . '</a>';
    }

    $html .= '</div>';
    return $html;
}
