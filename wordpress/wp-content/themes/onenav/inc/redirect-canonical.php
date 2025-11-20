<?php 
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-01-26 18:10:38
 * @LastEditors: iowen
 * @LastEditTime: 2023-03-30 02:30:46
 * @FilePath: \onenav\inc\redirect-canonical.php
 * @Description: rewrite_rules_array 删除判断 is_admin()
 */ 
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * 新分页格式
 * 文章：http://w.w.w/123.html/2  改为 http://w.w.w/123-2.html (需设置链接以 .html 结尾)
 * 分类：http://w.w.w/tag/123.html/page/2  改为 http://w.w.w/tag/123-2.html (需开启 [分类&标签固定链接模式] 的选项)
 * 注意：此选项可能会和其他插件不兼容而造成404，出现问题请关闭
 * 
 * ID
 * www.ww.cc/123.html
 * www.ww.cc/123-2.html
 * 
 * 其他，如名字，可能名称中就包含 - ，无法规则匹配
 * www.ww.cc/名称.html
 * www.ww.cc/名称/2.html
 * 
 */
global $is_new_page;
$is_new_page = io_get_option('new_page_type',true);


// -------------------------------------- [分页] ---------------------------------------------

/**
 * 修改post分页链接的格式
 * 含自定义文章类型
 * 
 * @param string $link
 * @param int $number
 * @return string
 */
function inner_page_link_format( $link, $number ){
	if( $number > 1 ){
		if( preg_match( '%<a href=".*\.html/[0-9]+/?"%', $link ) ){
			if(get_post_type()=='post'){
				if(strpos( get_option('permalink_structure'), '%post_id%' ) !== false){
					$link = preg_replace( "%(\.html)/([0-9]+)(/)?%", '-'."$2$1", $link );
				}else{
					$link = preg_replace( "%(\.html)/([0-9]+)(/)?%", '/'."$2$1", $link );
				}
			}else{
				if(io_get_option('rewrites_types','post_id') == 'post_id'){
					$link = preg_replace( "%(\.html)/([0-9]+)(/)?%", '-'."$2$1", $link );
				}else{
					$link = preg_replace( "%(\.html)/([0-9]+)(/)?%", '/'."$2$1", $link );
				}
			}
		}
	} 
	return $link;
}

/**
 * 修改评论和分类页分页链接
 * 
 * 其它钩子 get_comments_pagenum_link
 * 
 * @param string $result
 * @return string
 */
function comment_page_link_format( $result ){
	// From hello-world.html/comment-page-1#comments to hello-world/comment-page-1.html#comments
	//if( strpos( $result, '.html/' ) !== false ){
	//	if(preg_match( '%([0-9]+)(.html)/comment-page-([0-9]{1,})%', $result )){
	//		$result = preg_replace( '%([0-9]+)(.html)/comment-page-([0-9]{1,})%', "$1/comment-page-$3$2" ,$result );
	//	}elseif(preg_match( '%(-[0-9]+)?(\.html)/page/([0-9]+)%', $result )){
	//		$result = preg_replace( '%(-[0-9]+)?(\.html)/page/([0-9]+)%', '-'."$3$2" ,$result );
	//	}else{
	//		$result = preg_replace( '%(-[0-9]+)?(\.html)/?%',  "$3$2" ,$result );
	//	}
	//}
	
	if( strpos( $result, '.html/' ) !== false ){
		if(preg_match( '%([^/]+)(\.html)/comment-page-([0-9]{1,})%', $result )){ // 评论
			$result = preg_replace( '%([^/]+)(\.html)/comment-page-([0-9]{1,})%', "$1/comment-page-$3$2" ,$result );
		}elseif(io_get_option('rewrites_category_types','default','rewrites') == 'term_name'){
			if(preg_match( '%(/[0-9]+)?(\.html)/page/([0-9]+)%', $result )){
				$result = preg_replace( '%(/[0-9]+)?(\.html)/page/([0-9]+)%',   '/'."$3$2" ,$result );
			}elseif(preg_match( '%([^/]+)?(\.html)/page/([0-9]+)%', $result )){
				$result = preg_replace( '%([^/]+)?(\.html)/page/([0-9]+)%', '$1'.'/'."$3$2" ,$result );
			}else{
				$result = preg_replace( '%(/[^/]+)?(\.html)/?%',  "$3$2" ,$result );
			}
		}elseif(io_get_option('rewrites_category_types','default','rewrites') == 'term_id'){
			if(preg_match( '%(-[^/]+)?(\.html)/page/([0-9]+)%', $result )){
				$result = preg_replace( '%(-[^/]+)?(\.html)/page/([0-9]+)%', '-'."$3$2" ,$result );
			}else{
				$result = preg_replace( '%(-[^/]+)?(\.html)/?%',  "$3$2" ,$result );
			}
		}
	}
	return $result;
}
/**
 * 禁止WordPress将页面分页链接跳转到原来的格式
 * name 和 id 通用
 * @param string $redirect_url
 * @param string $requested_url
 * @return bool|string
 */
function cancel_redirect_for_paged_posts( $redirect_url, $requested_url ){
	global $wp_query;
	if( is_paged() &&   $wp_query->get( 'paged' ) > 1)//分类标签分页禁止跳转
		return false;
	elseif( is_single() && ( $wp_query->get( 'page' ) > 1 || $wp_query->get( 'cpage' ) > 0 )){//文章、评论分页禁止跳转
		return false;
	}
	return $redirect_url;
}
/**
 * 为新的链接格式增加重定向规则,移除原始分页链接的重定向规则，防止重复收录
 *
 * 访问原始链接将返回404
 * @param array $rules
 * @return array
 */
function pagelink_rewrite_rules_id( $rules ){
	$new_rule = [];
	/*
	switch (get_option('permalink_structure')) {
		case '/%category%/%post_id%.html':
			$separator = '-';
			foreach ($rules as $rule => $rewrite) {
				if ( $rule == '([0-9]+).html(?:/([0-9]+))?/?$' || $rule == '([0-9]+).html/comment-page-([0-9]{1,})/?$' ) {
					unset($rules[$rule]);
				}
			}
			$new_rule[ '(.+?)/([0-9]+)/comment-page-([0-9]{1,}).html(\#[^\s])?$' ] = 'index.php?category_name=$matches[1]&p=$matches[2]&cpage=$matches[3]';
			$new_rule[ '(.+?)/([0-9]+)('.$separator.'([0-9]+))?.html/?$' ] = 'index.php?category_name=$matches[1]&p=$matches[2]&page=$matches[4]';
			break;
		case '/%post_id%.html':
			$separator = '-';
			foreach ($rules as $rule => $rewrite) {
				if ( $rule == '([0-9]+).html(?:/([0-9]+))?/?$' || $rule == '([0-9]+).html/comment-page-([0-9]{1,})/?$' ) {
					unset($rules[$rule]);
				}
			}
			$new_rule[ '([0-9]+)/comment-page-([0-9]{1,}).html(\#[^\s])?$' ] = 'index.php?p=$matches[1]&cpage=$matches[2]';
			$new_rule[ '([0-9]+)('.$separator.'([0-9]+))?.html/?$' ] = 'index.php?p=$matches[1]&page=$matches[3]';
			break;
		case '/%postname%.html':
			$separator = '/';
			foreach ($rules as $rule => $rewrite) {
				if ( $rule == '([^/]+).html(?:/([0-9]+))?/?$' || $rule == '([^/]+).html/comment-page-([0-9]{1,})/?$' ) {
					unset($rules[$rule]);
				}
			}
			//([0-9]{1,2})/([^/]+).html(?:/([0-9]+))?/?$
			$new_rule[ '([^/]+)/comment-page-([0-9]{1,}).html(\#[^\s])?$' ] = 'index.php?name=$matches[1]&cpage=$matches[2]';
			$new_rule[ '([^/]+)('.$separator.'([0-9]+))?.html/?$' ] = 'index.php?name=$matches[1]&page=$matches[3]';
			break;
		case '/%category%/%postname%.html':
			$separator = '/';
			foreach ($rules as $rule => $rewrite) {
				if ( $rule == '(.+?)/([^/]+).html(?:/([0-9]+))?/?$' || $rule == '(.+?)/([^/]+).html/comment-page-([0-9]{1,})/?$	' ) {
					unset($rules[$rule]);
				}
			}
			$new_rule[ '(.+?)/([^/]+)/comment-page-([0-9]{1,}).html(\#[^\s])?$' ] = 'index.php?category_name=$matches[1]&name=$matches[2]&cpage=$matches[3]';
			$new_rule[ '(.+?)/([^/]+)('.$separator.'([0-9]+))?.html/?$' ] = 'index.php?category_name=$matches[1]&name=$matches[2]&page=$matches[4]';
			break;
		default:
			break;
	}
	*/
	$permalink = get_option('permalink_structure');
	if( false !== strpos($permalink,'.html') ){
		foreach ($rules as $rule => $rewrite) {
			if( strpos( $rule, '.html(?:/([0-9]+))?' ) !== false ){// 文章分页
				unset($rules[$rule]);
				$new_rule[str_replace(array('([^/]+).html(?:/([0-9]+))?','([0-9]+).html(?:/([0-9]+))?'),array('([^/]+)/?([0-9]+)?.html','([0-9]+)-?([0-9]+)?.html'),$rule)] = $rewrite;
			}elseif( strpos( $rule, '.html/comment-page-([0-9]{1,})' ) !== false ){// 评论分页
				unset($rules[$rule]);
				$new_rule[str_replace(array('([^/]+).html/comment-page-([0-9]{1,})','([0-9]+).html/comment-page-([0-9]{1,})'),array('([^/]+)/comment-page-([0-9]{1,}).html','([0-9]+)/comment-page-([0-9]{1,}).html'),$rule)] = $rewrite;
			}
		}
	}
	return $new_rule + $rules;
}
if($is_new_page){
	add_filter( 'wp_link_pages_link',  'inner_page_link_format' , 1, 2 ); // for inner pages
	add_filter( 'paginate_links', 'comment_page_link_format' ,1);
	add_filter( 'redirect_canonical',  'cancel_redirect_for_paged_posts' ,1, 2 );
	add_filter( 'rewrite_rules_array',  'pagelink_rewrite_rules_id',1 );
}
// --------------------------------------[分页] END ---------------------------------------------




// --------------------------------------[分类]&[标签]---------------------------------------------

/**
 * 设置自定义文章类型的[分类]&[标签]的固定链接结构为 ID.html 
 */
global $IOCATTYPE;
if(get_option('permalink_structure')):
if ( io_get_option('rewrites_category_types','default','rewrites') == 'term_id' ) {
	$IOCATTYPE = get_cat_type();
	add_filter( 'term_link', 'io_category_feed_link_id',1, 3);
	add_filter( 'query_vars', 'io_route_query_vars',1);
	add_action( 'parse_request', 'io_parse_query_vars',1); //send_headers
	add_filter( 'rewrite_rules_array',  'io_category_feed_link_init_id',1 );
}elseif (io_get_option('rewrites_category_types','default','rewrites') == 'term_name') {
	$IOCATTYPE = get_cat_type();
	add_filter( 'term_link', 'io_category_feed_link_name',1, 3);
	add_action( 'rewrite_rules_array', 'io_category_feed_link_init_name',1 );
}
endif;
function get_cat_type(){
	$args = array();
	$cat = array(
		'category'  => 'category',
		'favorites'	=> io_get_option('sites_rewrite','favorites','taxonomy'),
		'apps'	    => io_get_option('app_rewrite','apps','taxonomy'),
		'books'	    => io_get_option('book_rewrite','books','taxonomy'),
		'series'	=> io_get_option('book_rewrite','series','series'),
	);
	$tag = array(
		'post_tag'  => 'tag',
		'sitetag'	=> io_get_option('sites_rewrite','sitetag','tag'),
		'apptag'	=> io_get_option('app_rewrite','apptag','tag'),
		'booktag'	=> io_get_option('book_rewrite','booktag','tag'),
	);
	$types = io_get_option('rewrites_category_types', array('tag'), 'types');
	if(count($types)==2){
		$args = array_merge($cat, $tag);
	}elseif(count($types)==1){
		if($types[0] == 'tag'){
			$args = $tag;
		}else{
			$args = $cat;
		}
	}
	$args = apply_filters( 'io_rewrites_cat_type', $args );
	return $args;
}
//name
function io_category_feed_link_name($termlink, $term, $taxonomy){
	$types = io_get_option('rewrites_category_types', array('tag'), 'types');
	global $IOCATTYPE; 
	if ( 'post_tag' === $taxonomy && in_array('tag',$types) ) {
		return home_url().'/tag/' . $term->slug .  '.html';
	}elseif ( 'category' === $taxonomy && in_array('cat',$types) ){
		return home_url().'/'.$taxonomy.'/' . $term->slug .  '.html';
	}elseif(in_array($taxonomy, array_keys($IOCATTYPE))){
		return home_url().'/'.$IOCATTYPE[$taxonomy].'/' . $term->slug .  '.html';
	}
	return $termlink;
}
function io_category_feed_link_init_name( $rules ){
	$new_rule = array();
	$lang     = io_get_lang_rules(); 
	global $IOCATTYPE, $is_new_page ;
	foreach( $IOCATTYPE as $k => $v ) {
		foreach ($rules as $rule => $rewrite) {
			if ($rule == $v.'/([^/]+)/page/?([0-9]{1,})/?$' || 
				$rule == $v.'/([^/]+)/?$' ||
				$rule == 'category/(.+?)/page/?([0-9]{1,})/?$' ||
				$rule == 'category/(.+?)/?$'||
				$rule == 'tag/([^/]+)/page/?([0-9]{1,})/?$' ||
				$rule == 'tag/([^/]+)/?$' ||
				$rule == $lang . $v.'/([^/]+)/page/?([0-9]{1,})/?$' || 
				$rule == $lang . $v.'/([^/]+)/?$' ||
				$rule == $lang . 'category/(.+?)/page/?([0-9]{1,})/?$' ||
				$rule == $lang . 'category/(.+?)/?$'||
				$rule == $lang . 'tag/([^/]+)/page/?([0-9]{1,})/?$' ||
				$rule == $lang . 'tag/([^/]+)/?$'
			) {
				unset($rules[$rule]);
			}
		}
		if($is_new_page){
            $new_rule[ $v.'/([^/]+)?/([0-9]{1,}).html/?$' ] = 'index.php?'.io_get_taxonomy_query_key($k, 'name').'=$matches[1]&paged=$matches[2]';
            $new_rule[ $v.'/([^/]+)?.html/?$' ] = 'index.php?'.io_get_taxonomy_query_key($k, 'name').'=$matches[1]';
			if($lang){
				$new_rule[$lang . $v . '/([^/]+)?/([0-9]{1,}).html/?$'] = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k, 'name') . '=$matches[2]&paged=$matches[3]';
				$new_rule[$lang . $v . '/([^/]+)?.html/?$']             = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k, 'name') . '=$matches[2]';
			}
		}else{
            $new_rule[ $v.'/([^/]+)?.html/page/?([0-9]{1,})/?$' ] = 'index.php?'.io_get_taxonomy_query_key($k, 'name').'=$matches[1]&paged=$matches[2]';
            $new_rule[ $v.'/([^/]+)?.html/?$' ] = 'index.php?'.io_get_taxonomy_query_key($k, 'name').'=$matches[1]';
			if($lang){
				$new_rule[$lang . $v . '/([^/]+)?.html/page/?([0-9]{1,})/?$'] = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k, 'name') . '=$matches[1]&paged=$matches[2]';
				$new_rule[$lang . $v . '/([^/]+)?.html/?$']                   = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k, 'name') . '=$matches[1]';
			}
        }
	}

	return $new_rule + $rules;
}
//id
function io_category_feed_link_id($termlink, $term, $taxonomy){
	$types = io_get_option('rewrites_category_types', array('tag'), 'types');
	global $IOCATTYPE; 
	if ( 'post_tag' === $taxonomy && in_array('tag',$types) ) {
		return home_url().'/tag/' . $term->term_id .  '.html';
	}elseif ( 'category' === $taxonomy && in_array('cat',$types) ){
		return home_url().'/'.$taxonomy.'/' . $term->term_id .  '.html';
	}elseif(in_array($taxonomy, array_keys($IOCATTYPE))){
		return home_url().'/'.$IOCATTYPE[$taxonomy].'/' . $term->term_id .  '.html'; 
	}
	return $termlink;
}
function io_category_feed_link_init_id( $rules ){
	$new_rule = array(); 
	$lang     = io_get_lang_rules(); 
	global $IOCATTYPE, $is_new_page;
	foreach( $IOCATTYPE as $k => $v ) {
		foreach ($rules as $rule => $rewrite) {
			if ($rule == $v.'/([^/]+)/page/?([0-9]{1,})/?$' || 
				$rule == $v.'/([^/]+)/?$' ||
				$rule == 'category/(.+?)/page/?([0-9]{1,})/?$' ||
				$rule == 'category/(.+?)/?$'||
				$rule == 'tag/([^/]+)/page/?([0-9]{1,})/?$' ||
				$rule == 'tag/([^/]+)/?$' ||
				$rule == $lang . $v.'/([^/]+)/page/?([0-9]{1,})/?$' || 
				$rule == $lang . $v.'/([^/]+)/?$' ||
				$rule == $lang . 'category/(.+?)/page/?([0-9]{1,})/?$' ||
				$rule == $lang . 'category/(.+?)/?$'||
				$rule == $lang . 'tag/([^/]+)/page/?([0-9]{1,})/?$' ||
				$rule == $lang . 'tag/([^/]+)/?$'
			) {
				unset($rules[$rule]);
			}
		}	
		if ($is_new_page) {
			$new_rule[$v . '/([0-9]+)?-([0-9]{1,}).html/?$'] = 'index.php?' . io_get_taxonomy_query_key($k) . '=$matches[1]&paged=$matches[2]';
			$new_rule[$v . '/([0-9]+)?.html/?$']             = 'index.php?' . io_get_taxonomy_query_key($k) . '=$matches[1]';
			if($lang){
				$new_rule[$lang . $v . '/([0-9]+)?-([0-9]{1,}).html/?$'] = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k) . '=$matches[2]&paged=$matches[3]';
				$new_rule[$lang . $v . '/([0-9]+)?.html/?$']             = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k) . '=$matches[2]';
			}
		}else{
			$new_rule[$v . '/([0-9]+)?.html/page/?([0-9]{1,})/?$'] = 'index.php?' . io_get_taxonomy_query_key($k) . '=$matches[1]&paged=$matches[2]';
			$new_rule[$v . '/([0-9]+)?.html/?$']                    = 'index.php?' . io_get_taxonomy_query_key($k) . '=$matches[1]';
			if($lang){
				$new_rule[$lang . $v . '/([0-9]+)?.html/page/?([0-9]{1,})/?$'] = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k) . '=$matches[2]&paged=$matches[3]';
				$new_rule[$lang . $v . '/([0-9]+)?.html/?$']                   = 'index.php?lang=$matches[1]&' . io_get_taxonomy_query_key($k) . '=$matches[2]';
			}
        }
	}
	return $new_rule + $rules;
}
function io_get_taxonomy_query_key($taxonomy, $type = 'id'){
	if($type == 'id'){
		if($taxonomy == 'category'){
			return 'cat';
		}elseif($taxonomy == 'post_tag'){
			return 'tag_id';
		}else{
			return $taxonomy.'_id';
		}
	}else{
		if($taxonomy == 'category'){
			return 'category_name';
		}elseif($taxonomy == 'post_tag'){
			return 'tag';
		}else{
			return $taxonomy;
		}
	}
}
//--------------------在查询里添加分类id查询条件----------------------
function io_route_query_vars($public_query_vars) {
    $public_query_vars[] = 'tag_id';
	$custom_taxonomies = io_get_custom_taxonomies();
	if($custom_taxonomies){
		foreach ($custom_taxonomies as $custom_taxonomy) {
			$public_query_vars[]	= $custom_taxonomy.'_id';
		}
	}
	return $public_query_vars;
}
function io_get_custom_taxonomies(){
	global $custom_taxonomies;
	if(!$custom_taxonomies){
		$args = array(
			'public'   => true,
			'_builtin' => false
		);
		$custom_taxonomies = get_taxonomies($args);
	} 
    return $custom_taxonomies; 
}
function io_array_pull(&$array, $key, $default=null){
	if(isset($array[$key])){
		$value	= $array[$key];

		unset($array[$key]);
		
		return $value;
	}else{
		return $default;
	}
} 
function io_parse_tax_query($taxonomy, $term_id){
	if($term_id == 'none'){
		return ['taxonomy'=>$taxonomy,	'field'=>'term_id',	'operator'=>'NOT EXISTS'];
	}else{
		return ['taxonomy'=>$taxonomy,	'field'=>'term_id',	'terms'=>[$term_id]];
	}
}
function io_parse_query_vars($wp){
	$tax_query	= $wp->query_vars['tax_query'] ?? [];
	$date_query	= $wp->query_vars['date_query'] ?? [];

	$taxonomies	= array_values(io_get_custom_taxonomies());

	foreach(array_merge($taxonomies, ['category', 'post_tag']) as $taxonomy){
		$query_key	= io_get_taxonomy_query_key($taxonomy);
		$term_id    = io_array_pull($wp->query_vars, $query_key);
		if($term_id){
			if($taxonomy == 'category' && $term_id != 'none'){
				$wp->query_vars[$query_key] = $term_id;
			}else{
				$tax_query[] = io_parse_tax_query($taxonomy, $term_id);
			}
		}
	}

	if(!empty($wp->query_vars['taxonomy']) && empty($wp->query_vars['term'])){
		$term_id = io_array_pull($wp->query_vars, 'term_id');
		if($term_id){
			if(is_numeric($term_id)){
				$taxonomy		= io_array_pull($wp->query_vars, 'taxonomy');
				$tax_query[]	= io_parse_tax_query($taxonomy, $term_id);
			}else{
				$wp->set_query_var('term', $term_id);
			}
		}
	}

	foreach(['cursor'=>'before', 'since'=>'after'] as $key => $query_key){
		$value	= io_array_pull($wp->query_vars, $key);
		if($value){
			$date_query[]	= [$query_key => get_date_from_gmt(date('Y-m-d H:i:s', $value))];
		}
	}

	if($tax_query){
		$wp->query_vars['tax_query']	= $tax_query;
	}

	if($date_query){
		$wp->query_vars['date_query']	= $date_query;
	}
}
//-------------在查询里添加分类id查询条件 END----------------------

// --------------------------------------[分类]&[标签] END---------------------------------------------------------


// --------------------------------------[自定义文章]--------------------------------------------------------------

/**
 * 设置自定义文章类型的固定链接结构为 ID.html 
 * ---------------------------------------------------------------------------------------------------------------
 * https://www.wpdaxue.com/custom-post-type-permalink-code.html
 */
$_iotpostypes = array(
	'sites'	   => io_get_option('sites_rewrite','sites','post'),
	'app'	   => io_get_option('app_rewrite','app','post'),
	'book'	   => io_get_option('book_rewrite','book','post'),
	'bulletin' => 'bulletin',
);
global $IOPOSTTYPE;
$IOPOSTTYPE = apply_filters( 'io_rewrites_post_type', $_iotpostypes );
if( get_option('permalink_structure') ):
if( io_get_option('rewrites_end',false) ){ //删除链接结尾 ‘/’
	add_filter( 'user_trailingslashit', 'io_custom_post_trailingslashit', 10, 2 );
}
if( io_get_option('rewrites_types','post_id') == 'post_id' ) {
	add_filter( 'post_type_link', 'io_custom_post_type_link_id', 1, 2 );
	add_action( 'rewrite_rules_array', 'io_custom_post_type_rewrites_init_id' );
} elseif( io_get_option('rewrites_types','postname') == 'postname' && io_get_option('rewrites_end',true) ) {// rewrites_end 为 false 使用wp默认规则
	add_filter( 'post_type_link', 'io_custom_post_type_link_name', 1, 2 );
	add_action( 'rewrite_rules_array', 'io_custom_post_type_rewrites_init_name' );
}
endif;
/**
 * 主题设置加 .html ，但是wp设置里没加，自动删掉自定义文章类型结尾的斜杠，改 sites/123.html/ 为 sites/123.html
 * 加 .html 后缀后，分页 123.html/2 结尾加斜杠，123.html/2/
 * @param string $string 
 * @param string $type_of_url 
 * @return string 
 */
function io_custom_post_trailingslashit($string, $type_of_url){
	if(is_admin()){
		return $string;
	}
	global $IOPOSTTYPE, $is_new_page, $post, $wp_query;
	if( $IOPOSTTYPE && $post && in_array( $post->post_type,array_keys($IOPOSTTYPE) ) && isset($wp_query->query_vars['page']) && $wp_query->query_vars['page']==0){
		return untrailingslashit( $string );
	}
	if( (!$is_new_page && isset($wp_query->query_vars['page']) && $wp_query->query_vars['page']!=0) ){  // 如果没用启用 $is_new_page 则文章分页结尾加 ‘/’
		return trailingslashit( $string );
	}
	return $string;
}
// ID
function io_custom_post_type_link_id( $link, $post ){
	global $IOPOSTTYPE, $wp_rewrite;
	if ( in_array( $post->post_type,array_keys($IOPOSTTYPE) ) ){
		$slashes = $wp_rewrite->use_trailing_slashes?'/':'';
		return home_url().'/'.$IOPOSTTYPE[$post->post_type].'/' . $post->ID . (io_get_option('rewrites_end',true) ? '.html' : $slashes);
	} else {
		return $link;
	}
}
function io_custom_post_type_rewrites_init_id( $rules ){
	global $IOPOSTTYPE, $is_new_page;
	$new_rule = array();
	
	$post_rule = '/([0-9]+)?(?:/([0-9]+))?/?$';
	$comment_rule = '/([0-9]+)?/comment-page-([0-9]{1,})/?$';
	if(io_get_option('rewrites_end',true)){
		if($is_new_page){
            $post_rule = '/([0-9]+)?-?([0-9]+)?.html/?$';
            $comment_rule = '/([0-9]+)?/comment-page-([0-9]{1,}).html/?$';
		}else{
			$post_rule = '/([0-9]+)?.html(?:/([0-9]+))?/?$';
			$comment_rule = '/([0-9]+)?.html/comment-page-([0-9]{1,})/?$';
        }
	}
	foreach( $IOPOSTTYPE as $k => $v ) {
		foreach ($rules as $rule => $rewrite) {
			if ($rule == $v.'/([^/]+)/comment-page-([0-9]{1,})/?$' || 
				$rule == $v.'/([^/]+)(?:/([0-9]+))?/?$' 
			) {
				unset($rules[$rule]);
			}
		}
		$new_rule[ $v.$comment_rule ] = 'index.php?post_type='.$k.'&p=$matches[1]&cpage=$matches[2]';
		$new_rule[ $v.$post_rule ] = 'index.php?post_type='.$k.'&p=$matches[1]&page=$matches[2]';
	}

	return $new_rule + $rules;
}
// post_name
// 只处理 html 结尾规则，自定义文章系统默认为 /%postname%/
function io_custom_post_type_link_name( $link, $post ){
	global $IOPOSTTYPE;
	if ( in_array( $post->post_type,array_keys($IOPOSTTYPE) ) ){
		return home_url().'/'.$IOPOSTTYPE[$post->post_type].'/' . $post->post_name . '.html';
	} else {
		return $link;
	}
}
function io_custom_post_type_rewrites_init_name( $rules ){
	$new_rule = array();
	global $IOPOSTTYPE, $is_new_page;
	
	$lang = io_get_lang_rules(); 

	foreach( $IOPOSTTYPE as $k => $v ) {
		$old = array(
			$v.'/([^/]+)/comment-page-([0-9]{1,})/?$',
			$v.'/([^/]+)(?:/([0-9]+))?/?$'
		);
		if($lang){
			$old[] = $lang . $v . '/([^/]+)/comment-page-([0-9]{1,})/?$';
			$old[] = $lang . $v . '/([^/]+)(?:/([0-9]+))?/?$';
		}
		foreach ($rules as $rule => $rewrite) {
			if (in_array($rule, $old)) {
				unset($rules[$rule]);
			}
		}
		if($is_new_page){
			$new_rule[$v . '/([^/]+)?/comment-page-([0-9]{1,}).html/?$'] = 'index.php?' . $k . '=$matches[1]&cpage=$matches[2]';
			$new_rule[$v . '/([^/]+)?(?:/([0-9]+))?.html/?$']            = 'index.php?' . $k . '=$matches[1]&page=$matches[2]';
			if($lang){
				$new_rule[$lang . $v . '/([^/]+)?/comment-page-([0-9]{1,}).html/?$'] = 'index.php?lang=$matches[1]&' . $k . '=$matches[2]&cpage=$matches[3]';
				$new_rule[$lang . $v . '/([^/]+)?(?:/([0-9]+))?.html/?$']            = 'index.php?lang=$matches[1]&' . $k . '=$matches[2]&page=$matches[3]';
			}
		}else{
			$new_rule[$v . '/([^/]+)?.html/comment-page-([0-9]{1,})/?$'] = 'index.php?' . $k . '=$matches[1]&cpage=$matches[2]';
			$new_rule[$v . '/([^/]+)?.html(?:/([0-9]+))?/?$']            = 'index.php?' . $k . '=$matches[1]&page=$matches[2]';
			if($lang){
				$new_rule[$lang . $v . '/([^/]+)?.html/comment-page-([0-9]{1,})/?$'] = 'index.php?lang=$matches[1]&' . $k . '=$matches[2]&cpage=$matches[3]';
				$new_rule[$lang . $v . '/([^/]+)?.html(?:/([0-9]+))?/?$']            = 'index.php?lang=$matches[1]&' . $k . '=$matches[2]&page=$matches[3]';
			}
        }
	}

	return $new_rule + $rules;
}
// --------------------------------------[自定义文章] END--------------------------------------------------------------