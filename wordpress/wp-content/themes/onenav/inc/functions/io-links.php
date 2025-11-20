<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-02 23:46:16
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-12 10:47:01
 * @FilePath: /onenav/inc/functions/io-links.php
 * @Description: 
 */


/**
 * 友情链接列表
 * @return void
 */
function io_links_list()
{
	$col         = get_post_meta(get_the_ID(), 'sidebar_layout', true) != "sidebar_no" ? "row-col-md-3a" : "row-col-md-4a";
	$default_ico = get_template_directory_uri() . '/assets/images/favicon.png';
	$link_cats   = get_terms('link_category');
	if (!empty($link_cats)) {
		foreach ($link_cats as $cat) {
			echo '<div class="link-title mb-3"><h3 class="link-cat text-md mt-4"><i class="site-tag iconfont icon-tag icon-lg mr-1"></i>' . $cat->name . '</h3></div>';
			$bookmarks = get_bookmarks(array(
				'orderby'  => 'rating',
				'order'    => 'asc',
				'category' => $cat->term_id,
			));
			echo '<div class="posts-row row-col-2a ' . $col . '">';
			foreach ($bookmarks as $bookmark) {
				io_get_links_card($bookmark, $default_ico);
			}
			echo '</div>';
		}
		$uncategorized_links = get_uncategorized_bookmarks();
		if (!empty($uncategorized_links)) {
			echo '<div class="link-title mb-3"><h3 class="link-cat text-md mt-4"><i class="site-tag iconfont icon-tag icon-lg mr-1"></i>' . __('未分类', 'i_theme') . '</h3></div>';
			echo '<div class="posts-row row-col-2a ' . $col . '">';
			foreach ($uncategorized_links as $bookmark) {
				io_get_links_card($bookmark, $default_ico);
			}
			echo '</div>';
		}
	} else {
		echo '<div class="posts-row row-col-2a ' . $col . '">';
		$bookmarks = get_bookmarks(array(
			'orderby' => 'rating',
			'order'   => 'asc'
		));
		foreach ($bookmarks as $bookmark) {
			io_get_links_card($bookmark, $default_ico);
		}
		echo '</div>';
	}
}

/**
 * 获取未分类的书签链接
 *
 * @return array
 */
function get_uncategorized_bookmarks()
{
	global $wpdb;

	$links = $wpdb->get_results(
		"SELECT * FROM {$wpdb->links} l
        LEFT JOIN {$wpdb->term_relationships} tr ON (l.link_id = tr.object_id)
        WHERE tr.object_id IS NULL"
	);

	return $links ?: [];
}

/**
 * 友情链接卡片
 * @param mixed $bookmark
 * @param mixed $default_ico
 * @return void
 */
function io_get_links_card($bookmark, $default_ico)
{
	$ico = $bookmark->link_image ?: get_favicon_api_url($bookmark->link_url);

	$mane = $bookmark->link_name;
	$link = esc_url($bookmark->link_url);
	$desc = $bookmark->link_description;

	$img = get_lazy_img($ico, $mane, '50', 'rounded-circle fill-cover', $default_ico);
	echo <<<HTML
	<div class="link-card d-flex align-items-center"> 
		<div class="link-img rounded-circle">
		{$img}
		</div> 
		<div class="link-info overflow-hidden flex-fill">
			<div class="text-sm line1">
				<a href="{$link}" title="{$mane}" target="_blank"><strong>{$mane}</strong></a>
			</div>
			<div class="line1 text-muted text-xs">{$desc}</div>
		</div>
	</div>
HTML;
}

/**
 * 友情链接提交表单
 * @return void
 */
function io_get_add_links_form()
{
    $captcha = get_captcha_input_html('io_submit_link');

    $link_name                    = __('链接名称', 'i_theme');
    $link_name_placeholder        = __('请输入链接名称', 'i_theme');
    $link_url                     = __('链接地址', 'i_theme');
    $link_url_placeholder         = __('请输入链接地址', 'i_theme');
    $link_description             = __('链接简介', 'i_theme');
    $link_description_placeholder = __('请输入链接简介', 'i_theme');
    $link_image                   = __('LOGO地址', 'i_theme');
    $link_image_placeholder       = __('请输入LOGO图像地址', 'i_theme');

    $reset  = __('重填', 'i_theme');
    $submit = __('提交申请', 'i_theme');

    echo <<<HTML
    <form method="post" class="io-add-link-form ajax-form-captcha only-submit">							
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="link_name">{$link_name}</label>
                <input type="text" size="40" class="form-control" id="link_name" name="link_name" placeholder="{$link_name_placeholder}" />
            </div>
            <div class="form-group col-md-6">
                <label for="link_url">{$link_url}</label>
                <input type="text" size="40" class="form-control" id="link_url" name="link_url" placeholder="{$link_url_placeholder}" />
            </div>
        </div>  
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="link_description">{$link_description}</label>
                <input type="text" size="40" class="form-control" id="link_description" name="link_description" placeholder="{$link_description_placeholder}" />
            </div>
            <div class="form-group col-md-6">
                <label for="link_image">{$link_image}</label>
                <input type="text" size="40" class="form-control" id="link_image" name="link_image" placeholder="{$link_image_placeholder}" />
            </div>
        </div> 
        <div class=" d-flex justify-content-end flex-wrap">  
        <input type="hidden" name="action" value="io_submit_link"> 
        {$captcha}
        <button type="reset" class="btn vc-l-gray ml-2">{$reset}</button>
        <button type="submit" id="submit" class="btn vc-theme ml-2">{$submit}</button>
        </div>
    </form> 
HTML;
}

/**
 * 获取首页友情链接
 * @return string
 */
function io_get_home_links()
{
	if (!io_get_option('show_friendlink', false) || !io_get_option('links', false) || !is_home() || !is_front_page()) {
		return '';
	}
	$cats = io_get_option('home_links', '') ?: '';

	$args  = array(
		'orderby'     => 'rating',
		'order'       => 'DESC',
		'category'    => $cats,
		'categorize'  => 0,
		'title_li'    => '',
		'before'      => '',
		'after'       => '',
		'show_images' => 0,
		'echo'        => 0,
	);
	$links = wp_list_bookmarks($args);
	$links .= '<a href="' . io_get_template_page_url('template-links.php') . '" target="_blank" title="' . __('更多链接', 'i_theme') . '">' . __('更多链接', 'i_theme') . '</a>';
	$title = __('友情链接', 'i_theme');

	$html = '<div id="friendlink" class="mt-4 mb-3 text-left">';
	$html .= '<h4 class="text-sm mb-2">' . $title . '</h4>';
	$html .= '<div class="friend-link text-sm">' . $links . '</div>';
	$html .= '</div>';

	return $html;
}
