<?php
/*
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:55:57
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-18 23:36:16
 * @FilePath: /onenav/author.php
 * @Description: 
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } 

$current_tab     = isset($_GET['tab']) ? $_GET['tab'] : '';
$comments_status = isset($_GET['status']) ? $_GET['status'] : '';
$post_status     = isset($_GET['post_status']) ? $_GET['post_status'] : '';

if (
    ($current_tab && !is_author_can_see($current_tab)) ||
    ($comments_status && !is_author_can_see($comments_status)) ||
    ($post_status && !is_author_can_see($post_status))
) {
    set404();
}

get_header(); 

?>
<main role="main" class="container my-2">
    <?php
    io_author_header();
    io_author_content();
    ?> 
</main>
<?php 
get_footer();
