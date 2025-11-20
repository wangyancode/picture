<?php
/*
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:55:57
 * @LastEditors: iowen
 * @LastEditTime: 2025-01-22 17:26:05
 * @FilePath: /onenav/footer.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


show_ad('ad_footer_top',false,'container');
/**
 * -----------------------------------------------------------------------
 * HOOK : ACTION HOOK
 * io_before_footer
 * 
 * 在<footer>前挂载其他菜单。
 * @since  3.xxx
 * -----------------------------------------------------------------------
 */
do_action( 'io_before_footer' );

io_footer();

/**
 * -----------------------------------------------------------------------
 * HOOK : ACTION HOOK
 * io_after_footer
 * 
 * 在</footer>后挂载其他内容。
 * @since  3.xxx
 * -----------------------------------------------------------------------
 */
do_action( 'io_after_footer' );

wp_footer();

?>
</body>
</html>