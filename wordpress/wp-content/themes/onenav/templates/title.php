<?php
/*
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:56:02
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-03 16:24:19
 * @FilePath: /onenav/templates/title.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$blog_name = get_bloginfo('name');
$linker    = io_get_option('seo_linker','');

$title       = "";
$title_after = $linker . $blog_name;
$keywords    = "";
$description = "";

$type = 'article';
$url  = home_url();

$global_desc = _iol(io_get_option('seo_home_desc', ''));
$global_keys = _iol(io_get_option('seo_home_keywords', ''));

$img = io_get_option("og_img", get_theme_file_uri('/screenshot.jpg'));

if ((is_home() || is_front_page())) {
	$title       = $blog_name . $linker . get_bloginfo('description');
	$title_after = '';
	$keywords    = $global_keys;
	$description = $global_desc;
	$type        = 'website';
}
if( is_search() ) {
	$title       = sprintf(__('%s的搜索结果', 'i_theme'), '&#8220;' . htmlspecialchars($s) . '&#8221;');
	$keywords    = $s . ',' . $blog_name;
	$description = $global_desc;
}

if (is_single() || is_page()) {
	$post_id   = get_the_ID();
	$post_type = get_post_type();

	$url = get_permalink();

	$title       = io_analyze_tdk_config('title', 'main');
	$keywords    = io_analyze_tdk_config('key', 'main');
	$description = io_analyze_tdk_config('desc', 'main');

	if ($post_type == 'post') {
		$img = io_theme_get_thumb();
	} elseif ($post_type == 'sites') {
		$img = get_post_meta_img($post_id, '_thumbnail', true);
		if ($img == '') {
			$link_url = get_post_meta($post_id, '_sites_link', true);
			$img      = $link_url ? get_favicon_api_url($link_url) : get_favicon_api_url($url);
		}
	} elseif ($post_type == 'app') {
		$img = get_post_meta_img($post_id, '_app_ico', true);
	} elseif ($post_type == 'book') {
		$img = get_post_meta_img($post_id, '_thumbnail', true);
	} else {
		$img = io_theme_get_thumb();

		$excerpt = get_post_meta($post_id, '_seo_desc', true);
		if (empty($excerpt))
			$excerpt = io_get_excerpt(io_get_option('seo_desc_count', 150));

		$tags = get_post_meta($post_id, '_seo_metakey', true) ?: get_the_tags();
		if ($tags && is_array($tags)) {
			$tag = '';
			foreach ($tags as $val) {
				$tag .= ',' . $val->name;
			}
			$tag = ltrim($tag, ',');
		} else {
			$tag = $tags;
		}

		$title       = get_post_meta($post_id, '_seo_title', true) ?: get_the_title();
		$keywords    = $tag ?: $global_keys;
		$description = $excerpt ?: $global_desc;
	}

}

if (is_category() || is_tag() || is_tax(['favorites', 'sitetag', 'apps', 'apptag', 'books', 'booktag', 'series'])) {
	$_img = get_term_meta(get_queried_object_id(), 'thumbnail', true);
	$img = $_img ? $_img : $img;

	$title       = io_analyze_tdk_config('title', 'term');
	$keywords    = io_analyze_tdk_config('key', 'term');
	$description = io_analyze_tdk_config('desc', 'term');
}


if (is_year()) {
	$title = sprintf(__('“%s”年所有文章', 'i_theme'), get_the_time('Y'));
}
if (is_month()) {
	$title = sprintf(__('“%s”份所有文章', 'i_theme'), get_the_time('F'));
}
if (is_day()) {
	$title = sprintf(__('“%s”所有文章', 'i_theme'), get_the_time(__('Y年n月j日', 'i_theme')));
}
if (is_author()) {
	$title       = get_the_author();
	$keywords    = $title . ',' . $blog_name;
	$description = get_user_desc(get_current_user_id());
}
if(is_io_user()){
	$title = __('用户中心', 'i_theme');
	$keywords = $title . ',' . $global_keys . ',' . $blog_name;
	$description = $global_desc;
}
if ( is_404() ) {  $title = __('没有你要找的内容', 'i_theme'); }  

if($custom_page = get_query_var('custom_page')){ 
	switch ($custom_page) {
		case 'hotnews':
			$title = __('今日热榜', 'i_theme');
			break;
	}
}

if ($me_route = get_query_var('user_child_route')) {
	switch ($me_route) {
		case 'settings':
			$title = __('用户信息', 'i_theme');
			break;
		case 'notifications':
			$title = __('站内消息', 'i_theme');
			break;
		case 'stars':
			$title = __('我的收藏', 'i_theme');
			break;
		case 'sites':
			$title = __('网址管理', 'i_theme');
			break;
		case 'security':
			$title = __('账户安全', 'i_theme');
			break;
		default:
			$title = __('用户中心', 'i_theme');
	}
}
if(is_bookmark()){
	global $bm_user;
	$bm_route = get_query_var('bookmark_id');
	if($bm_route == 'default'){
		$title = __('新标签页', 'i_theme');
	}else{
		$user_id = base64_io_decode($bm_route);
		if(!$bm_user){
			$bm_user = get_user_by('ID', $user_id);
		}
		if($bm_user){
			$title = $bm_user->display_name;
		}
	}
}

// 截断 $title 到 60 个字符，$description 到 160 个字符
//$title       = rtrim(mb_substr($title, 0, 60));
//$description = rtrim(mb_substr($description, 0, 160));

global $paged, $page;
if ($paged >= 2 || $page >= 2) {
	$page        = sprintf(__('第%s页', 'i_theme'), max($paged, $page));
	$title       = $title . $linker . $page;
	$description = $description . $linker . $page;
}

$title       = $title ?: $blog_name;
$keywords    = $keywords ?: $global_keys;
$description = $description ?: $global_desc;

extract(apply_filters('io_post_tkd_filters', compact('title', 'title_after', 'keywords', 'description', 'type', 'url', 'img', 'paged', 'page')));
?>
<title><?php echo esc_html($title . $title_after) ?></title>
<meta name="theme-color" content="<?php echo (theme_mode()=="io-black-mode"?'#2C2E2F':'#f9f9f9') ?>" />
<meta name="keywords" content="<?php echo esc_attr($keywords) ?>" />
<meta name="description" content="<?php echo esc_attr($description) ?>" />
<?php
if (io_get_option('og_switcher', true)) {
?>
<meta property="og:type" content="<?php echo esc_attr($type) ?>">
<meta property="og:url" content="<?php echo esc_attr($url) ?>"/> 
<meta property="og:title" content="<?php echo esc_attr($title . $title_after) ?>">
<meta property="og:description" content="<?php echo esc_attr($description) ?>">
<meta property="og:image" content="<?php echo esc_attr($img) ?>">
<meta property="og:site_name" content="<?php echo esc_attr($blog_name) ?>">
<?php
}
