<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-03-13 10:45:14
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-02 20:35:12
 * @FilePath: /onenav/inc/functions/io-die.php
 * @Description: 
 */

/**
 * wp_die_handler 钩子过滤器
 * 
 * @since 1.0.0
 * @author iowen 
 * @link https://www.iowen.cn/
 * @param mixed $die_handler
 * @return mixed
 */
function io_wp_die_handler_filter($die_handler){
	if (is_admin()) {
		return $die_handler;
	}
	return 'io_die_beauty_html';
}
add_filter( 'wp_die_handler', 'io_wp_die_handler_filter' );

/**
 * 重写wp_die页面模版
 * 
 * 内容可自行修改，保留die()函数
 * @since 1.0.0
 * @author iowen 
 * @link https://www.iowen.cn/
 * @param string|WP_Error $message 错误消息或 WP_Error 对象。
 * @param string $title            可选。错误标题。默认为空字符串。
 * @param array $args              可选。用于控制行为的参数。默认为空数组。
 */
function io_die_beauty_html($message, $title, $args){
	get_header();
	if(is_wp_error($message)){
		$message = $message->get_error_message();
	}
	?>
	<main class="container my-5 pb-4" role="main">
		<article class="die-error p-3" style="max-width: 500px;margin: auto;text-align: center;">
			<div class="die-header">
				<h1 class="die-title text-hide"><?php echo $title; ?></h1>
			</div>
			<img src="<?php echo get_template_directory_uri() . '/assets/images/svg/wp_die.svg' ?>" alt="404" class="img-fluid svg-img die-img">
			<div class="die-content tips-box vc-l-red mt-4">
				<?php echo $message ?>
			</div>
		</article>
	</main>
	<?php
	get_footer();
	die();
}