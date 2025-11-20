<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-07-27 14:18:44
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-07 15:14:20
 * @FilePath: /onenav/templates/hot/hot-home.php
 * @Description: 
 */
?>
<?php 
if ( ! defined( 'ABSPATH' ) ) { exit; }

if (!io_get_option('is_show_hot',true)) {
    set404();
}
$list = (array)io_get_option('hot_home_list', array());
get_header() 
?> 
<main id="content" class="container my-2" role="main">
	<div class="row-a  row-col-1a row-col-md-2a row-col-lg-3a">
        <?php
        foreach ($list as $index => $value) {
            echo io_get_hot_api_card_html($value, $index);
        } 
        ?>
    </div>
</main> 
<?php
get_footer(); 
