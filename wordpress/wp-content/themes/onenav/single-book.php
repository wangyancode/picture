<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:00
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 19:02:51
 * @FilePath: /onenav/single-book.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); 

global $book_type;
$book_type = get_post_meta(get_the_ID(), '_book_type', true);
$book_type = $book_type ?: 'books';
?>
<main class="container my-2" role="main">
    <?php
    echo io_header_fx();

    iopay_get_auto_ad_html('page', 'mb-3');
    
    $header  = io_book_header($is_hide);
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
                    io_book_content();
                }
                if (io_get_option('book_related', true)) {
                    $related_title = '<i class="site-tag iconfont icon-book icon-lg mr-1" ></i>' . sprintf(__('相关%s', 'i_theme'), get_book_type_name($book_type));
                    echo io_posts_related('book', $related_title, 4);
                }
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif; 
                ?>
            </div><!-- content-layout end -->
        </div><!-- content-wrap end -->
        <?php get_sidebar('book');  ?>
    </div>
</main>
<?php 
get_footer();
