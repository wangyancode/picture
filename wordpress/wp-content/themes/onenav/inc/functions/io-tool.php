<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-04 21:26:44
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-06 17:19:40
 * @FilePath: /onenav/inc/functions/io-tool.php
 * @Description: 
 */

/**
 * 是否为博客页
 * @return bool 
 */
function is_blog(){
	return is_page_template('template-blog.php');
}
/**
 * 是否为用户管理页
 * @return bool 
 */
function is_io_user(){
	return get_query_var('is_user_route');
}
/**
 * 是否为次级导航页
 * @return bool 
 */
function is_mininav(){
	return is_page_template('template-mininav.php');
}
/**
 * 是否为排行榜页
 * @return bool 
 */
function is_rankings(){
	return is_page_template('template-rankings.php');
}
/**
 * 是否为投稿页
 * @return bool 
 */
function is_contribute(){
	return is_page_template('template-contribute.php');
}
/**
 * 是否为登录页
 * @return bool 
 */
function is_io_login(){
	return get_query_var('custom_action') === 'login' || is_login();
}
/**
 * 是否为书签页
 * @return bool 
 */
function is_bookmark(){
    return get_query_var('bookmark_id') ? true : false;
}

/**
 * 是否为热榜页
 * @return bool
 */
function is_hotnews(){
    return get_query_var('custom_page') === 'hotnews';
}

/**
 * 获取字符串的16位MD5（取中间16位）
 *
 * @param string $string 输入的字符串
 * @return string 返回16位MD5值
 */
function md5_16($string) {
    return substr(md5($string), 8, 16);
}
/**
 * 获取字符串的8位MD5
 * 
 * @param mixed $string 输入的字符串
 * @return string
 */
function md5_8($string) {
    return substr(md5($string), 8, 8);
}

/**
 * 删除内容或者数组的两端空格
 * 
 * @param array|string $input
 * @return array|string
 */
function io_trim($input){
    if (!is_array($input)) {
        return trim($input);
    }
    return array_map('io_trim', $input);
}

/**
 * is url
 * 
 * @param mixed $url
 * @return bool
 */
function io_is_url($url){
	//if (preg_match("/^http[s]?:\/\/.*$/", $url)) {
	if (false !== filter_var($url, FILTER_VALIDATE_URL)) {
		return true;
	} else {
		return false;
	}
}
/**
 * 分割字符串为数组，支持空格、逗号（中英文）、换行符等多种分隔符
 *
 * @param string $input 需要处理的字符串
 * @return array 分割后的数组
 */
function io_split_str($input) {
    if (empty($input)) {
        return array();
    }
    return preg_split('/[\s,，、]+/u', $input);//preg_split("/,|，|\s|\n/", $input);
}

/**
 * 生成二维码
 * @param mixed $text 内容
 * @param mixed $size 尺寸
 * @param mixed $margin 边距
 * @param mixed $level 容错级别
 * @return bool|string
 */
function io_get_qrcode($text, $size = 256, $margin = 10, $level = 'L', $cache = true){
	if ($cache) {
		$cache_key = 'qr_' . strtolower(substr(md5($text),8,16)) . $size . $margin . $level;
		$_cache = wp_cache_get($cache_key);
		if (false !== $_cache) {
			return $_cache;
		}
	}
    //引入phpqrcode类库
    require_once get_theme_file_path('/inc/classes/phpqrcode.php');
    ob_start();
    QRcode::png($text, false, $level, $size, $margin);
    $data = ob_get_contents();
    ob_end_clean();
	if ($cache) {
		wp_cache_set($cache_key, $data);
	}
	return $data;
}
/**
 * 获取二维码数据
 *
 * @param string $text
 * @return string
 */
function io_get_qrcode_base64($text){

	$imageString = base64_encode(io_get_qrcode($text, 256, 10, 'L', false));
    header('Content-Type: application/json; charset=utf-8');
    return 'data:image/jpeg;base64,' . $imageString;
}
/**
 * 输出二维码图片
 * @param mixed $text 内容
 * @param mixed $size 尺寸
 * @param mixed $margin 边距
 * @param mixed $level 容错级别
 * @return void
 */
function io_show_qrcode($text, $size = 256, $margin = 10, $level = 'L'){
	$headers = array(
		'X-Robots-Tag: noindex, nofollow',
		'Content-type: image/png',
		'cache-control: public, max-age=86400'
	);
	foreach ($headers as $header) {
        header($header);
    }
	echo io_get_qrcode($text, $size, $margin, $level);
}
/**
 * 获取时间
 * @param string $format
 * @param string $offset  日期偏移 如前一天 '-1day'
 * @return string
 */
function io_get_time($format='', $offset=''){
	$format = $format ?: 'Y-m-d H:i:s';
	if($offset)
		$time = date($format, strtotime($offset,current_time( 'timestamp' )));
	else
		$time = date($format, current_time('timestamp'));
	return $time;
}
/**
 * 文字计数
 *
 * @param string $str
 * @param string $charset
 * @return int
 */
function io_strlen($str, $charset = 'utf-8'){
    //中文算一个，英文算半个
    return mb_strlen($str, $charset);//(int) ((strlen($str) + mb_strlen($str, $charset)) / 4);
}

function set404(){
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    get_template_part('404');
    exit();
}
/**
 * 网址查重
 *
 * @param string $link
 * @return bool
 */
function link_exists($link){
    global $wpdb;
    $link = str_replace(array('http://','https://'), '', $link);
    if(!empty($link)){
        $sql = "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_value` REGEXP '^http(s)?://{$link}(/)?$' AND `meta_key`='_sites_link'";
        if($wpdb->get_row($sql)) {
            return true;
        } 
    }
    return false;
}

/**
 * 文章标题查重
 *
 * @param string $title
 * @param string $type
 * @return bool
 */
function title_exists($title, $type='sites'){
    global $wpdb;
    if(!empty($title)){
        $sql = "SELECT `ID` FROM $wpdb->posts WHERE `post_status` IN ('pending','publish') AND `post_type` = '{$type}' AND `post_title` = '{$title}'";
        if($wpdb->get_row($sql)) {
            return true;
        } 
    }
    return false;
}

/**
 * 将任意编码的字符串转换为 UTF-8
 *
 * @param string $string 要编码的字符串
 * @param string $encoding  编码；默认值：ISO-8859-1
 * @param bool $safe_mode 安全模式：如果设置为TRUE，则在出现错误时返回原始字符串
 * @return  string 
 */
function io_encode_utf8( $string = '', $encoding = 'iso-8859-1', $safe_mode = false ) {
	$safe = ( $safe_mode ) ? $string : false;
	if ( strtoupper( $encoding ) == 'UTF-8' || strtoupper( $encoding ) == 'UTF8' ) {
		return $string;
	} elseif ( strtoupper( $encoding ) == 'ISO-8859-1' ) {
		return utf8_encode( $string );
	} elseif ( strtoupper( $encoding ) == 'WINDOWS-1252' ) {
		return utf8_encode( io_map_w1252_iso8859_1( $string ) );
	} elseif ( strtoupper( $encoding ) == 'UNICODE-1-1-UTF-7' ) {
		$encoding = 'utf-7';
	}
	if ( function_exists( 'mb_convert_encoding' ) ) {
		$conv = @mb_convert_encoding( $string, 'UTF-8', strtoupper( $encoding ) );
		if ( $conv ) {
			return $conv;
		}
	}
	if ( function_exists( 'iconv' ) ) {
		$conv = @iconv( strtoupper( $encoding ), 'UTF-8', $string );
		if ( $conv ) {
			return $conv;
		}
	}
	if ( function_exists( 'libiconv' ) ) {
		$conv = @libiconv( strtoupper( $encoding ), 'UTF-8', $string );
		if ( $conv ) {
			return $conv;
		}
	}
	return $safe;
}
/**
 * 特殊模式
 * Windows-1252 基本上是 ISO-8859-1 ——除了一些例外
 * @param string $string 
 * @return  string 
 */
function io_map_w1252_iso8859_1( $string = '' ) {
	if ( '' == $string ) {
		return '';
	}
	$return = '';
	for ( $i = 0; $i < strlen( $string ); ++$i ) {
		$c = ord( $string[ $i ] );
		switch ( $c ) {
			case 129:
				$return .= chr( 252 );
				break;
			case 132:
				$return .= chr( 228 );
				break;
			case 142:
				$return .= chr( 196 );
				break;
			case 148:
				$return .= chr( 246 );
				break;
			case 153:
				$return .= chr( 214 );
				break;
			case 154:
				$return .= chr( 220 );
				break;
			case 225:
				$return .= chr( 223 );
				break;
			default:
				$return .= chr( $c );
				break;
		}
	}
	return $return;
}
/**
 * 获取链接根域名
 * @param string $url 
 * @return string 
 */
function get_url_root($url){
	if (!$url) {
		return $url;
	}
	if (!preg_match("/^http/is", $url)) {
		$url = "http://" . $url;
	}
	$url = parse_url($url)["host"];
	$url_arr   = explode(".", $url);
	if (count($url_arr) <= 2) {
		$host = $url;
	} else {
		$last   = array_pop($url_arr);
		$last_1 = array_pop($url_arr);
		$last_2 = array_pop($url_arr);
		$host   = $last_1 . '.' . $last;
		if (in_array($host, DUALHOST))
			$host = $last_2 . '.' . $last_1 . '.' . $last;
	}
	return $host;
}
/**
 * 获取随机数
 * @param int $counts 随机数位数
 * @return string
 */
function io_get_captcha($counts = 6){
    $original = '0,1,2,3,4,5,6,7,8,9';
    $original = explode(',', $original);
    $code      = "";
    for ($j = 0; $j < $counts; $j++) {
        $code .= $original[rand(0, count($original) - 1)];
    }
    return strtolower($code);
}
/**
 * 
 * 
 * @param mixed $abc
 * @return float|int
 */
function char_to_num($abc){
    $ten = 0;
    $len = strlen($abc);
    for($i=1;$i<=$len;$i++){
		$char = substr($abc,0-$i,1);//反向获取单个字符
        $int = ord($char);
        $ten += ($int-65)*pow(26,$i-1);
    }
    return $ten;
}

/**
 * 多久后
 * 
 * @param mixed $time
 * @return bool|string
 */
function io_friend_after_date($time){
	if (!$time)
		return false;
	if (!is_numeric($time)) {
		$time = strtotime($time);
	}
	$today      	= strtotime(date("Y-m-d", current_time('timestamp'))); //今天
	$tomorrow       = $today + 3600 * 24; //明天
	$after_tomorrow = $tomorrow + 3600 * 24; //后天
	$three_days     = $after_tomorrow + 3600 * 24; //大后天

	$date = '';

	switch ($time) {
		case $time > $three_days:
			$date = date(__('m月d日 H:i', 'i_theme'), $time);
			break;
		case $time > $after_tomorrow:
			$date = __('后天', 'i_theme') . date('H:i', $time);
			break;
		case $time > $tomorrow:
			$date = __('明天', 'i_theme') . date('H:i', $time);
			break;
		case $time > $today:
			$date = __('今天', 'i_theme') . date('H:i', $time);
			break;
		default:
			$date = date(__('m月d日 H:i', 'i_theme'), $time);
			break;
	}
	return $date;
}

/**
 * 判断是否为爬虫
 * @return bool
 */
function io_is_spider() {
    // 扩展的常见蜘蛛爬虫用户代理关键词
    $bots = array(
        // Google
        'googlebot', 'mediapartners-google', 'adsbot-google', 'googlebot-image', 'googlebot-video', 'google-favicon',
        
        // Bing
        'bingbot', 'bingpreview',

        // Yahoo
        'slurp',

        // DuckDuckGo
        'duckduckbot',

        // Baidu
        'baiduspider', 'baidu-imagespider', 'baiduspider-video', 'baidunews', 'baiduspider-news', 'baiduspider-favo', 'baiduspider-cpro', 'baiduspider-ads',

        // Yandex
        'yandexbot', 'yandeximages', 'yandexvideo', 'yandexmedia', 'yandexblogs', 'yandexnews',

        // Sogou
        'sogou', 'sogou-spider', 'sogou web spider', 'sogouinst spider',

        // Exalead
        'exabot',

        // Facebook
        'facebot', 'facebookexternalhit',

        // Twitter
        'twitterbot',

        // LinkedIn
        'linkedinbot',

        // Other common bots
        'ia_archiver', // Archive.org
        'msnbot',      // MSN Bot
        'mj12bot',     // Majestic-12
        'seznambot',   // Seznam.cz
        'ahrefsbot',   // Ahrefs
        'semrushbot',  // SEMrush
        'rogerbot',    // Moz/Site Crawl
        'dotbot',      // Moz/DotBot
        'gigabot',     // GigaBot
        'surveybot',   // SurveyBot
        'linkpadbot',  // Linkpad
        'outbrain',    // Outbrain
        'pinterest',   // Pinterest Bot
        'telegrambot', // Telegram
        'slackbot',    // Slack
        'discordbot',  // Discord
        'applebot',    // Applebot
        'baiduspider', // Baidu
        'yahoo',       // Yahoo
        'ecosia',      // Ecosia
        'archive',     // Internet Archive
        'curl',        // cURL requests
        'wget',        // Wget requests
        'python',      // Python requests
        'python-requests', // Specific Python library for HTTP requests
        'postman',     // Postman tool
        'axios',       // Axios library
        'java',        // Generic Java based bots
        'google-page-speed-insights', // Google Page Speed Insights bot
        'bytespider'   // ByteDance (TikTok)
    );

    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }

    // 获取用户代理字符串
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

    // 检查用户代理是否包含任何爬虫标识符
    foreach ($bots as $bot) {
        if (strpos($user_agent, $bot) !== false) {
            return true;
        }
    }

    return false;
}
/**
 * 获取动态边栏
 * 
 * @param mixed $index 边栏的名称或ID
 * @return string
 */
function get_dynamic_sidebar($index = 1) {
    if(is_active_sidebar($index)){
        ob_start();
        dynamic_sidebar($index);
        $sidebar = ob_get_clean();
        return $sidebar;
    }
    return '';
}

/**
 * 获取自定义背景
 * 
 * @param mixed $args 配置参数
 * @param mixed $class 额外的 class
 * @return string
 */
function get_div_custom_background($args, &$class) {
	if (!isset($args['background'])) {
		return '';
	}

	$margin     = $args['background']['margin'];
	$padding    = $args['background']['padding'];
	//$color	    = $args['background']['color'];
	$background = $args['background']['background'];

	$style = array(
		//'--this-main-color'  => $color['color'],
		//'--this-hover-color' => $color['hover'],
		//'--this-muted-color' => $color['muted'],
		//'color'              => 'var(--this-main-color)',
		'margin-top'         => $margin['top'] . 'px',
		'margin-bottom'      => $margin['bottom'] . 'px',
		'padding'            => $padding['top'] . 'px ' . $padding['right'] . 'px ' . $padding['bottom'] . 'px ' . $padding['left'] . 'px',
	);

	if (!empty($background['background-image']['url'])) {
		$class = $class . ' card-blue';

		$style['background-image']      = 'url(' . $background['background-image']['url'] . ')';
		$style['background-size']       = $background['background-size'];
		$style['background-repeat']     = $background['background-repeat'];
		$style['background-attachment'] = $background['background-attachment'];
		$style['background-position']   = $background['background-position'];
		$style['background-clip']       = $background['background-clip'];
		$style['background-origin']     = $background['background-origin'];
		$style['background-blend-mode'] = $background['background-blend-mode'];
		$style['background-color']      = $background['background-color'];
	} elseif (!empty($background['background-color'])) {
		$style['background'] = $background['background-color'];
		if (!empty($background['background-gradient-color'])) {
			$style['background'] = 'linear-gradient(' . $background['background-gradient-direction'] . ', ' . $background['background-color'] . ' 0%, ' . $background['background-gradient-color'] . ' 100%)';
		}
	}

	// 将数组转换为 HTML 的 style 属性字符串
	$style_attr = '';
	foreach ($style as $key => $value) {
		if (!empty($value)) {
			$style_attr .= $key . ': ' . esc_attr($value) . '; ';
		}
	}

	// 去掉最后一个分号和空格
	$style_attr = rtrim($style_attr, '; ');

	return 'style="' . $style_attr . '"';
}

/**
 * 分类法转文章类型
 * @param mixed $taxonomy
 * @global WP_Taxonomy[] $wp_taxonomies
 * @return mixed
 */
function get_post_types_by_taxonomy($taxonomy)
{
    $is_wp = false;  // 使用wp方法返回第一个关联的文章类型

    if ($is_wp) {
        global $wp_taxonomies;

        if (isset($wp_taxonomies[$taxonomy])) {
            return $wp_taxonomies[$taxonomy]->object_type[0];
        }
    } else {
        if ($taxonomy == "favorites" || $taxonomy == "sitetag")
            return 'sites';
        if ($taxonomy == "apps" || $taxonomy == "apptag")
            return 'app';
        if ($taxonomy == "books" || $taxonomy == "booktag" || $taxonomy == "series")
            return 'book';
        if ($taxonomy == "category" || $taxonomy == "post_tag")
            return 'post';
    }
    return $taxonomy;
}

/**
 * 判断是否为分类法 
 * 
 * true:分类法 false:标签法
 * @param mixed $taxonomy 分类法名称
 * @return bool
 */
function is_taxonomy_cat($taxonomy)
{
    $taxonomy_obj = get_taxonomy($taxonomy);

    if (!$taxonomy_obj) {
        return false;
    }

    if ($taxonomy_obj->hierarchical) {
        return true;
    } else {
        return false;
    }
}

/**
 * 只获取分类法名称
 * @param mixed $taxonomy
 * @return string
 */
function get_taxonomy_type_name($taxonomy){
    if( $taxonomy=="favorites"||$taxonomy=="sitetag" )
        return 'favorites';
    if( $taxonomy=="apps"||$taxonomy=="apptag" )
        return 'apps';
    if( $taxonomy=="books"||$taxonomy=="booktag" ||$taxonomy=="series" )
        return 'books';
    if( $taxonomy=="category"||$taxonomy=="post_tag" )
        return 'category';

    return $taxonomy;
}

/**
 * 获取文章类型对应的标签法名称
 * @param mixed $post_type
 * @return string
 */
function posts_to_tag($post_type) {
    if ($post_type == "sites")
        return 'sitetag';
    if ($post_type == "app")
        return 'apptag';
    if ($post_type == "book")
        return 'booktag';
    
    return 'post_tag';
}
/**
 * 获取文章类型对应的分类法名称
 * @param mixed $post_type
 * @return string
 */
function posts_to_cat($post_type) {
    if ($post_type == "sites")
        return 'favorites';
    if ($post_type == "app")
        return 'apps';
    if ($post_type == "book")
        return 'books';
    return 'category';
}

/**
 * 获取相关文章
 * 
 * 结果缓存 1 小时
 * @param mixed $type 文章类型
 * @param mixed $title 标题
 * @param mixed $limit 数量
 * @return string
 */
function io_posts_related($type, $title = '', $limit = 6) {
    global $post;

    $cache_key    = 'io_related_posts_' . $post->ID;
    $cached_posts = wp_cache_get($cache_key, 'related_posts');
    if ($cached_posts) {
        return $cached_posts;
    }

    $cat  = posts_to_cat($type);
    $tag  = posts_to_tag($type);
    $cats = get_the_terms($post, $cat);
    $tags = get_the_terms($post, $tag);

    $args = array(
        'post_type'           => $type,// 文章类型
        'posts_per_page'      => $limit,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'orderby'             => 'rand', // 随机排序
        'no_found_rows'       => true,
        'post__not_in'        => array($post->ID),
        'tax_query'           => array(
            'relation' => 'OR',
            array(
                'taxonomy' => $tag,
                'field'    => 'term_id',
                'terms'    => array_column((array) $tags, 'term_id'),
            ),
            array(
                'taxonomy' => $cat,
                'field'    => 'term_id',
                'terms'    => array_column((array) $cats, 'term_id'),
            ),
        ),
    );

    $posts_lits    = '';
    $related_items = new WP_Query($args);
    while ($related_items->have_posts()) {
        $related_items->the_post();
        switch ($type) {
            case 'sites':
                $posts_lits .= get_sites_card('default', array('class' => 'col-2a col-md-4a'));
                break;
            case 'app':
                $posts_lits .= get_app_card('max', array('class' => 'col-2a col-md-3a'));
                break;
            case 'book':
                $posts_lits .= get_book_card(io_get_book_card_mode(), array('class' => 'col-2a col-md-4a'));
                break;
            default:
                $posts_lits .= get_post_card('card', array('class' => 'col-2a col-md-4a'));
                break;
        }

    }
    wp_reset_postdata();

    if (empty($posts_lits)) {
        $posts_lits .= '<div class="col-1a"><div class="nothing">' . __('没有相关内容!', 'i_theme') . '</div></div>';
    }

    $html = '<h4 class="text-gray text-lg my-4">' . $title . '</h4>';
    $html .= '<div class="posts-row">';
    $html .= $posts_lits;
    $html .= '</div>';

    wp_cache_set($cache_key, $html, 'related_posts', HOUR_IN_SECONDS); // 缓存1小时

    return $html;
}

/**
 * 提取颜色的RGB值
 * @param mixed $color 颜色 #123123 | #123 | rgb(11,11,11) | rgba(11,11,11, 0.2)
 * @return array|bool
 */
function extractRGB($color) {
    $rgb = [];

    if (preg_match('/^#?([a-fA-F0-9]{6})$/', $color, $matches)) {
        $hex      = $matches[1];
        $rgb['r'] = hexdec(substr($hex, 0, 2));
        $rgb['g'] = hexdec(substr($hex, 2, 2));
        $rgb['b'] = hexdec(substr($hex, 4, 2));
        $rgb['a'] = 1;
    } elseif (preg_match('/^#?([a-fA-F0-9]{3})$/', $color, $matches)) {
        $hex      = $matches[1];
        $rgb['r'] = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $rgb['g'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $rgb['b'] = hexdec(str_repeat(substr($hex, 2, 1), 2));
        $rgb['a'] = 1;
    } elseif (preg_match('/^rgba?\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})(?:,\s*[\d\.]+)?\)$/', $color, $matches)) {
        $rgb['r'] = (int) $matches[1];
        $rgb['g'] = (int) $matches[2];
        $rgb['b'] = (int) $matches[3];
        $rgb['a'] = isset($matches[4]) ? (float)$matches[4] : 1;
    } else {
        return false;
    }

    return $rgb;
}

/**
 * 颜色加深
 * @param mixed $color
 * @param mixed $deepen
 * @return string
 */
function get_color_deepen($color, $deepen = 0.1) {
    $rgb = extractRGB($color);
    if (!$rgb) {
        return $color;
    }
    $rgb = array_map(function ($value) use ($deepen) {
        $value = $value * (1 - $deepen);
        return $value < 0 ? 0 : $value;
    }, $rgb);

    // 将 RGB 转换为 HSL
    $hsl = rgbToHsl($rgb['r'], $rgb['g'], $rgb['b']);
    
    // 色相偏移20度
    $hsl['h'] = ($hsl['h'] - 20) % 360; // 确保色相值在 0 到 360 之间
    
    // 将 HSL 转换回 RGB
    $rgb = hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);
    
    
    
    // 返回 #hex 格式
    $r = sprintf("%02x", $rgb['r']);
    $g = sprintf("%02x", $rgb['g']);
    $b = sprintf("%02x", $rgb['b']);
    return '#' . $r . $g . $b;
}
/**
 * 设置颜色透明度
 * @param mixed $color
 * @param mixed $opacity
 * @return string
 */
function set_color_opacity($color, $opacity = 0.5) {
    $rgb = extractRGB($color);
    return "rgba({$rgb['r']}, {$rgb['g']}, {$rgb['b']}, {$opacity})";
}

/**
 * 将 RGB 转换为 HSL
 * @param mixed $r
 * @param mixed $g
 * @param mixed $b
 * @return array
 */
function rgbToHsl($r, $g, $b) {
    $r /= 255;
    $g /= 255;
    $b /= 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);

    $h = 0;
    $s = 0;
    $l = ($max + $min) / 2;

    if ($max == $min) {
        $h = $s = 0; // achromatic
    } else {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        switch ($max) {
            case $r:
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = ($b - $r) / $d + 2;
                break;
            case $b:
                $h = ($r - $g) / $d + 4;
                break;
        }

        $h /= 6;
    }

    return array('h' => $h * 360, 's' => $s, 'l' => $l);
}

/**
 * 将 HSL 转换回 RGB
 * @param mixed $h
 * @param mixed $s
 * @param mixed $l
 * @return array
 */
function hslToRgb($h, $s, $l) {
    $h /= 360;

    $r = 0; 
    $g = 0; 
    $b = 0;

    if ($s == 0) {
        $r = $g = $b = $l; // achromatic
    } else {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $r = hueToRgb($p, $q, $h + 1/3);
        $g = hueToRgb($p, $q, $h);
        $b = hueToRgb($p, $q, $h - 1/3);
    }

    return array('r' => round($r * 255), 'g' => round($g * 255), 'b' => round($b * 255));
}

/**
 * 辅助函数：用于 HSL 转换为 RGB 的步骤
 * @param mixed $p
 * @param mixed $q
 * @param mixed $t
 * @return mixed
 */
function hueToRgb($p, $q, $t) {
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if ($t < 1/2) return $q;
    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
    return $p;
}


/**
 * 显示置顶标签
 * 
 * @return string
 */
function get_sticky_tag() {
    $default = array(
        'switcher' => false,
        'icon'     => '顶',
        'name'     => '置顶',
    );

    if(!is_sticky()){
        return '';
    }
    
    $sticky = io_get_option('sticky_tag', array());
    $sticky = wp_parse_args($sticky, $default);
    $span   = '';
    if ($sticky['switcher'])
        $span = '<span class="badge badge-title vc-j-red mr-1" data-toggle="tooltip" title="' . $sticky['name'] . '">' . $sticky['icon'] . '</span>';
    return $span;
}
/**
 * 显示 NEW 标签
 * 
 * @return string
 */
function get_new_tag() {
    $default = array(
        'switcher' => false,
        'icon'     => '新',
        'name'     => '新增',
        'date'     => 7,
    );

    $new  = io_get_option('new_tag', array());
    $new  = wp_parse_args($new, $default);
    $span = '';
    if ($new['switcher']) {
        $now   = date("Y-m-d H:i:s", current_time('timestamp'));
        $due   = $new['date'] * 24;
        $diff = (strtotime($now) - strtotime(get_the_time('Y-m-d H:i:s'))) / 3600;
        if ($diff < $due) {
            $span = '<span class="badge badge-title vc-j-purple mr-1" data-toggle="tooltip" title="' . $new['name'] . '">' . $new['icon'] . '</span>';
        }
    }
    return $span;
}

/**
 * 根据菜单ID获取层级化的菜单项
 *
 * 该函数通过递归方式构建一个层级结构，用于表示WordPress菜单项的层次关系
 * 它首先获取所有菜单项，然后根据菜单项的父级关系，将它们组织成树状结构
 *
 * @param int $menu_id 菜单ID，表示需要获取的特定菜单的项
 * @return array 层级化的菜单结构，包含顶级菜单及其子菜单（如果有）
 */
function get_menu_items_by_level($menu_id) {
    static $cache_nav_menu = array();
    if (empty($menu_id)) {
        return array();
    }

    if (isset($cache_nav_menu[$menu_id])) {
        return $cache_nav_menu[$menu_id];
    }

    $menu_items = wp_get_nav_menu_items($menu_id);
    if (!$menu_items) {
        return array();
    }
    $menu_tree  = array();

    foreach ($menu_items as $menu_item) {
        $menu_item_array = (array) $menu_item;
        $menu_item_array['children'] = array();
        // 如果没有父级菜单（即顶级菜单），直接添加到根数组
        if (!$menu_item->menu_item_parent) {
            $menu_tree[$menu_item->ID] = $menu_item_array;
        } else {
            add_menu_item_to_parent($menu_tree, $menu_item_array);
        }
    }
    
    $cache_nav_menu[$menu_id] = $menu_tree;
    return $menu_tree;
}

/**
 * 将菜单项添加到父菜单下
 * 
 * 该函数通过递归遍历菜单树，将指定的菜单项添加到其父菜单的子菜单列表中
 * 如果菜单项的父菜单不存在，则该菜单项将不会被添加
 *
 * @param array $menu_tree 菜单树数组，包含多个菜单项及它们的关系
 * @param array $menu_item 待添加的菜单项对象，包含menu_item_parent属性，指定其父菜单的ID
 * @return void
 */
function add_menu_item_to_parent(&$menu_tree, $menu_item) {
    foreach ($menu_tree as &$parent_menu_item) {
        // 找到匹配的父菜单
        if ($parent_menu_item['ID'] == $menu_item['menu_item_parent']) {
            $parent_menu_item['children'][$menu_item['ID']] = $menu_item;
            return;
        }

        // 如果当前项还有子级，则递归继续找父级菜单
        if (!empty($parent_menu_item['children'])) {
            add_menu_item_to_parent($parent_menu_item['children'], $menu_item);
        }
    }
}

/**
 * 获取半透明模式颜色
 * 
 * @param mixed $index 颜色索引
 * @param mixed $type  颜色类型 默认为空 l、半透明  j、渐变
 * @param mixed $rand 是否随机
 * @return string
 */
function get_theme_color($index, $type = '', $rand = false)
{
    $type   = $type ? '-' . $type : '';
    $colors = array(
        'vc' . $type . '-theme',
        'vc' . $type . '-blue',
        'vc' . $type . '-green',
        'vc' . $type . '-red',
        'vc' . $type . '-yellow',
        'vc' . $type . '-cyan',
        'vc' . $type . '-violet',
        'vc' . $type . '-purple',
    );
    if ($rand) {
        return $colors[array_rand($colors)];
    } else {
        return $colors[$index % count($colors)];
    }
}

/**
 * 获取ajax预制占位符
 * @param mixed $style 样式
 * @param int $count 数量
 * @param bool $is_half 只显示一半
 * @param mixed $attr 其他属性
 * @return string
 */
function get_posts_placeholder($style, $count = 0, $is_half = true) {
    
    if ($count > 0) {
        $count = $is_half ? min(8, ceil($count / 2)) : $count;
    }
    $html = '';
    for ($i = 0; $i < $count; $i++) {
        $attr = 'style="--this-title-width:' . rand(50, 100) . '%;"';
        if ('title' == $style) {
            $html .= '<div class="placeholder-posts null-' . $style . '">';
            $html .= '<span class="--image"></span>';
            $html .= '<span class="--title" ' . $attr . '></span>';
            $html .= '</div>';
        } else {
            $html .= '<div class="placeholder-posts null-' . $style . '">';
            $html .= '<div class="p-header">';
            $html .= '<span class="--image"></span>';
            $html .= '</div>';
            $html .= '<div class="p-meta">';
            $html .= '<span class="--title" ' . $attr . '></span>';
            $html .= '<div class="--meta"><span></span><span></span><span></span></div>';
            $html .= '</div>';
            $html .= '</div>';
        }
    }
    //if ($count > 0) {
    //    $count = $is_half ? min(8, ceil($count / 2)) : $count;
    //    $html  = str_repeat($html, $count);
    //}
    return $html;
}

/**
 * 获取文章排序方式
 * 
 * @param mixed $type 排序类型，默认为空。如果提供了类型，则返回对应类型的名称。
 * @return string|string[]
 */
function get_select_by($type = '') {
    $list = array(
        'date'     => __('发布', 'i_theme'),
        'modified' => __('更新', 'i_theme'),
        'views'    => __('浏览', 'i_theme'),
        'like'     => __('点赞', 'i_theme'),
        'start'    => __('收藏', 'i_theme'),
        'comment'  => __('评论', 'i_theme'),
        'down'     => __('下载', 'i_theme'),
        'rand'     => __('随机', 'i_theme'),
    );
    if ($type) {
        return $list[$type];
    }
    return $list;
}

/**
 * 获取文章排序方式的元键
 * @param mixed $type
 * @return mixed
 */
function get_select_to_meta_key($type) {
    $list = array(
        'date'     => 'date',
        'modified' => 'modified',
        'views'    => 'views',
        'like'     => '_like_count',
        'start'    => '_star_count',
        'comment'  => 'comment_count',
        'down'     => '_down_count',
    );
    if (isset($list[$type])) {
        return $list[$type];
    }
    return $type;
}

/**
 * 获取配置项里的项目值
 * 
 * 自动缓存
 * @param mixed $type 配置项类型 post|term|user|comment
 * @param mixed $key 配置项名称
 * @param mixed $meta_id 文章id|分类id|用户id|评论id
 * @param mixed $single 是否单一
 * @return mixed
 */
function io_get_meta($type, $key, $meta_id, $single = false)
{
    $cache_key = $type . '_meta_' . $meta_id;
    $value     = wp_cache_get($cache_key, 'io_meta_data');
    if (false === $value) {
        //$value = get_metadata($type, $meta_id, '', $single);
        switch ($type) {

            case 'post':
            default:
                $value = get_post_meta($meta_id, 'posts_config_data', $single);
                break;
        }
        wp_cache_set($cache_key, $value, 'io_meta_data', WEEK_IN_SECONDS);
    }
    return isset($value[$key]) ? $value[$key] : false;
}

/**
 * 更新配置项里的项目值
 * 
 * 自动缓存
 * @param mixed $type 配置项类型 post|term|user|comment
 * @param mixed $key 配置项名称
 * @param mixed $value 配置项值
 * @param mixed $meta_id 文章id|分类id|用户id|评论id
 * @return mixed
 */
function io_update_meta($type, $key, $value, $meta_id)
{
    $cache_key = $type . '_meta_' . $meta_id;
    switch ($type) {
        case 'post':
        default:
            $old_value = get_post_meta($meta_id, 'posts_config_data', true);
            $old_value[$key] = $value;
            $state = update_post_meta($meta_id, 'posts_config_data', $old_value);
            break;
    }
    wp_cache_set($cache_key, $old_value, 'io_meta_data', WEEK_IN_SECONDS);

    return $state;
}

/**
 * 检查指定的类型和键是否存在于预定义的元键数组中
 *
 * @param mixed $type 元数据类型，如'post'
 * @param mixed $key 元数据键，如果提供了此参数，函数将检查该键是否存在于指定类型中
 * @return bool|string[] 如果提供了$key参数，返回布尔值，表示键是否存在于类型中；否则返回类型的所有键数组
 */
function have_meta_keys($type, $key = '')
{
    $meta_keys = array(
        'post' => array(
            '_goto','_wechat_id','_is_min_app','_sites_language','_sites_country',
            '_thumbnail','_sites_preview','_wechat_qr','_down_version',
            '_down_size','_down_url_list','_dec_password','_app_platform','_down_preview','_down_formal',
            '_screenshot','_app_ico','app_ico_o','_summary','_journal',
            '_books_data','_buy_list','_down_list',
        )
    );

    $keys = isset($meta_keys[$type]) ? $meta_keys[$type] : array();
    if ($key) {
        return in_array($key, $keys);
    }
    return $keys;
}

/**
 * 获取文章meta数据
 * @param mixed $post_id
 * @param mixed $key
 * @param mixed $single
 * @return mixed
 */
function _gt_post_m($post_id, $key, $single = true)
{
    if (have_meta_keys('post', $key)) {
        return io_get_meta('post', $key, $post_id, $single);
    }
    return get_post_meta($post_id, $key, $single);
}

/**
 * 更新文章meta数据
 * @param mixed $post_id
 * @param mixed $key
 * @param mixed $value
 * @return mixed
 */
function _up_post_m($post_id, $key, $value)
{
    if (have_meta_keys('post', $key)) {
        return io_update_meta('post', $key, $value, $post_id);
    }
    return update_post_meta($post_id, $key, $value);
}
/**
 * 数据清洗
 * 支持数组和字符串
 * @param array|string $data
 * @return array|string
 */
function sanitize_input($data)
{
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = sanitize_input($v);
            } else {
                $data[$k] = trim(htmlspecialchars($v, ENT_QUOTES));
            }
        }
    } else {
        $data = trim(htmlspecialchars($data, ENT_QUOTES));
    }

    return $data;
}

/**
 * 查找字符串中是否包含数组中的字符
 * 
 * @param string $string 要查找的字符串
 * @param array $args 要匹配的字符数组
 * @return bool 如果找到则返回true，否则返回false
 */
function find_character($string, $args)
{
    // 使用 preg_quote() 来转义数组中的特殊字符，避免正则表达式冲突
    $escapedArr = array_map('preg_quote', $args, array_fill(0, count($args), '#'));

    // 使用 preg_match 而不是 preg_match_all，找到一个即可返回 true
    return (bool)preg_match('#(' . implode('|', $escapedArr) . ')#', $string);
}

/**
 * 查找字符是否存在
 * 
 * 匹配最前面的字符，用于 $title 只保留最前面的部分
 * 
 * @description:
 * @param string $str
 * @param array $needles
 * @return int
 */
function io_strpos($str, $needles)
{
    $index = 0;
    foreach ($needles as $needle) {
        $new = strpos($str, $needle);
        if ($new !== false) {
            if ($index === 0 || $new < $index) {
                $index = $new;
            }
        }
    }
    return $index;
}

/**
 * 将数字四舍五入为K（千），M（百万）或B（十亿）
 * 
 * number_format
 * 
 * @param mixed $number 
 * @param int $min_value 
 * @param int $decimal 
 * @return string|void 
 */
function io_number_format( $number, $min_value = 1000, $decimal = 1 ) {
    if( $number < $min_value ) {
        return number_format_i18n( $number );
    }
    $alphabets = array( 1000000000 => 'B', 1000000 => 'M', 1000 => 'K' );
    foreach ($alphabets as $key => $value) {
        if ($number >= $key) {
            if ($number >= $key * 100) {
                $decimal = 0;
            }
            return round($number / $key, $decimal) . '<span class="num-unit">' . $value . '</span>';
        }
    }
}
/**
 * 通过 api 获取 favicon 图标
 * @param mixed $url
 * @return string
 */
function get_favicon_api_url($url)
{
    $api = io_get_option('favicon_api', 'https://t' . rand(0, 3) . '.gstatic.cn/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&size=128&url=%url%');

    $parsed_url = parse_url($url);

    if (isset($parsed_url['host'])) {
        $host = $parsed_url['host'];
    } else {
        return '';
    }

    $link = str_replace(
        array('%host%', '%url%'),
        array($host, $url),
        $api
    );
    return esc_url($link);
}

/**
 * 通过 api 获取网站预览图
 * @param mixed $url 
 * @param mixed $width
 * @param mixed $height
 * @return string
 */
function get_preview_api_url($url, $width = 800, $height = 600)
{
    $api = io_get_option('preview_api', 'https://cdn.iocdn.cc/mshots/v1/%host%?w=%width%&h=%height%');

    $parsed_url = parse_url($url);

    if (isset($parsed_url['host'])) {
        $host = $parsed_url['host'];
    } else {
        return '';
    }

    $link = str_replace(
        array('%host%', '%url%', '%width%', '%height%'),
        array($host, $url, $width, $height),
        $api
    );
    return esc_url($link);
}

/**
 * 旋转数组，使指定元素成为数组的第一个元素
 *
 * @param array $array 原始数组
 * @param mixed $start 指定的起始元素
 * @return array 旋转后的数组
 */
function rotate_array($array, $start)
{
    $index = array_search($start, $array);

    if ($index === false) {
        return $array;
    }

    $part1 = array_splice($array, $index);

    return array_merge($part1, $array);
}

/**
 * 分类选择框
 * 多选，需配合 js 使用
 * @param array $args
 * @return mixed
 */
function io_dropdown_categories_multiple($args = array())
{
    // 设置默认参数
    $defaults = array(
        'taxonomy'         => 'category',        // 分类法（默认是分类）
        'name'             => 'cat',             // 下拉框的 name 属性
        'id'               => '',                // 下拉框的 id 属性
        'class'            => '',                // 自定义 CSS 类
        'selected'         => array(),           // 默认选中的分类
        'hide_empty'       => 1,                 // 是否隐藏没有文章的分类
        'hierarchical'     => 0,                 // 是否显示层级分类
        'include'          => array(),           // 包含的分类 ID
        'exclude'          => array(),           // 排除的分类 ID
        'show_count'       => 0,                 // 是否显示文章数量
        'show_option_none' => '',                // 默认显示的提示文本
        'max_count_limit'  => 0,                 // 分类多选时，最多可选数量
        'orderby'          => 'id',
        'order'            => 'ASC',
        'echo'             => 1,
    );

    $args = wp_parse_args($args, $defaults);

    // 获取分类数据
    $categories = get_terms(array(
        'orderby'    => $args['orderby'],
        'order'      => $args['order'],
        'taxonomy'   => $args['taxonomy'],
        'hide_empty' => $args['hide_empty'],
        'include'    => $args['include'],       // 只包含指定的分类 ID
        'exclude'    => $args['exclude'],       // 排除指定的分类 ID
    ));

    // 如果需要层级分类，先构建一个带深度信息的分类数组
    if ($args['hierarchical'] && empty($args['include']) && empty($args['exclude'])) { // 不包含和排除
        $categories = io_get_category_hierarchy($categories);
    }

    // 开始构建下拉框
    $output = '<select name="' . esc_attr($args['name']) . '" multiple="multiple" style="display: none;"';
    if (!empty($args['id'])) {
        $output .= ' id="' . esc_attr($args['id']) . '"';
    }
    $output .= ' class="' . esc_attr($args['class']) . '"';
    $output .= '>';

    // 循环添加分类到下拉框中
    if (!empty($categories) && !is_wp_error($categories)) {
        $args['selected'] = (array) $args['selected'];
        foreach ($categories as $category) {
            $output .= '<option value="' . esc_attr($category->term_id) . '"';
            if (in_array($category->term_id, $args['selected'])) {
                $output .= ' selected="selected"';
            }
            // 如果是层级分类，显示缩进
            $depth  = isset($category->depth) ? $category->depth : 0;
            $output .= '>' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);

            $output .= esc_html($category->name);

            if ($args['show_count']) {
                $output .= ' (' . $category->count . ')';
            }

            $output .= '</option>';
        }
    }

    $output .= '</select>';

    $html = '<div class="io-multiple-dropdown" data-placeholder="' . esc_attr($args['show_option_none']) . '" data-max-count="' . $args['max_count_limit'] . '">';
    $html .= $output;
    $html .= '<div class="multiple-select">';
    $html .= '<div class="selected-input">' . esc_html($args['show_option_none']) . '</div>';
    $html .= '<div class="multiple-dropdown">';
    $html .= '<ul class="dropdown-list"></ul>';

    if($args['max_count_limit'] != 1){
        $html .= '<div class="dropdown-footer mt-1 row-a row-sm row-col-' . ($args['max_count_limit'] == 0 ? '2' : '1') . 'a">';
        if($args['max_count_limit'] == 0){
            $html .= '<div class="btn vc-l-blue btn-sm select-all">' . __('全选', 'i_theme') . '</div>';
        }
        $html .= '<div class="btn vc-l-red btn-sm clear-all">' . __('清空', 'i_theme') . '</div>';
        if($args['max_count_limit'] > 1){
            $html .= '<div class=" badge vc-l-blue text-xs col-1a-i text-left"><i class="iconfont icon-tishi mr-1"></i>' . sprintf(__('最多可选%s个', 'i_theme'), $args['max_count_limit']) . '</div>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    if ($args['echo']) {
        echo $html;
    } else {
        return $html;
    }
}


/**
 * 构建带深度信息的层级分类
 * 
 * 辅助函数
 * @param mixed $categories
 * @param mixed $parent_id
 * @param mixed $depth
 * @return array
 */
function io_get_category_hierarchy($categories, $parent_id = 0, $depth = 0)
{
    $output = array();

    foreach ($categories as $category) {
        if ($category->parent == $parent_id) {
            $category->depth = $depth; // 设置深度
            $output[]        = $category;

            // 递归获取子分类
            $child_categories = io_get_category_hierarchy($categories, $category->term_id, $depth + 1);
            $output           = array_merge($output, $child_categories);
        }
    }

    return $output;
}

/**
 * 获取缓存
 * @param mixed $key
 * @param mixed $group
 * @param mixed $force
 * @param mixed $found
 */
function io_cache_get($key, $group = '', $force = false, &$found = null)
{
    if (wp_using_ext_object_cache()) {
        return wp_cache_get($key, $group, $force, $found);
    }

    $cache = get_transient($key . '_' . $group);
    $found = $cache !== false;
    return $cache;
}

/**
 * 设置缓存
 * @param mixed $key
 * @param mixed $value
 * @param mixed $group
 * @param mixed $expire
 */
function io_cache_set($key, $value, $group = '', $expire = 0)
{
    if (wp_using_ext_object_cache()) {
        return wp_cache_set($key, $value, $group, $expire);
    }

    return set_transient($key . '_' . $group, $value, $expire);
}

/**
 * 删除缓存
 * @param mixed $key
 * @param mixed $group
 */
function io_cache_delete($key, $group = '')
{
    if (wp_using_ext_object_cache()) {
        return wp_cache_delete($key, $group);
    }

    return delete_transient($key . '_' . $group);
}
