<?php
/*
 * @Theme Name:OneNav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-02-21 12:46:57
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-05 15:31:19
 * @FilePath: /onenav/inc/functions/io-single-site.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!function_exists('get_data_evaluation')):
/**
 * 数据评估HTML
 * @param mixed $name
 * @param mixed $views
 * @param mixed $url
 * @return void
 */
function get_data_evaluation($name,$views,$url){
    if(!io_get_option('sites_default_content',false)) return;
    global $post;
    $aizhan_data = go_to('https://www.aizhan.com/seo/'. get_url_host($url));
    $chinaz_data = go_to('https://seo.chinaz.com/?q='. get_url_host($url));
    $e5118_data  = go_to('https://seo.5118.com/'. get_url_host($url) . '?t=ydm');
?>
    <h2 class="text-gray text-lg my-4"><i class="iconfont icon-tubiaopeizhi mr-1"></i><?php _e('数据评估','i_theme') ?></h2>
    <div class="panel site-content sites-default-content card"> 
        <div class="card-body">
            <p class="viewport">
            <?php echo $name ?>浏览人数已经达到<?php echo $views ?>，如你需要查询该站的相关权重信息，可以点击"<a class="external" href="<?php echo $e5118_data ?>" rel="nofollow" target="_blank">5118数据</a>""<a class="external" href="<?php echo $aizhan_data ?>" rel="nofollow" target="_blank">爱站数据</a>""<a class="external" href="<?php echo $chinaz_data ?>" rel="nofollow" target="_blank">Chinaz数据</a>"进入；以目前的网站数据参考，建议大家请以爱站数据为准，更多网站价值评估因素如：<?php echo $name ?>的访问速度、搜索引擎收录以及索引量、用户体验等；当然要评估一个站的价值，最主要还是需要根据您自身的需求以及需要，一些确切的数据则需要找<?php echo $name ?>的站长进行洽谈提供。如该站的IP、PV、跳出率等！</p>
            <div class="text-center my-2"><span class=" content-title"><span class="d-none">关于<?php echo $name ?></span>特别声明</span></div>
            <p class="text-muted text-sm m-0">
            本站<?php bloginfo('name'); ?>提供的<?php echo $name ?>都来源于网络，不保证外部链接的准确性和完整性，同时，对于该外部链接的指向，不由<?php bloginfo('name'); ?>实际控制，在<?php echo the_time(TIME_FORMAT) ?>收录时，该网页上的内容，都属于合规合法，后期网页的内容如出现违规，可以直接联系网站管理员进行删除，<?php bloginfo('name'); ?>不承担任何责任。</p>
        </div>
        <div class="card-footer text-muted text-xs">
            <div class="d-flex"><span><?php bloginfo('name'); ?>致力于优质、实用的网络站点资源收集与分享！</span><span class="ml-auto d-none d-md-block">本文地址<?php the_permalink() ?>转载请注明</span></div>
        </div>
    </div>
<?php
}
endif;

if(!function_exists('get_report_button')):
/**
 * 获取举报按钮
 * @param mixed $post_id
 * @return mixed
 */
function get_report_button($post_id = ''){
    if(!io_get_option('report_button',true))
        return;

    if($post_id==''){
        global $post;
        $post_id = get_the_ID();
    }                                            
    return '<a href="javascript:" class="btn vc-red tooltip-toggle mr-2" data-post_id="'.$post_id.'" data-toggle="modal" data-placement="top" data-target="#report-sites-modal" title="'. __('反馈','i_theme') .'"><i class="iconfont icon-statement icon-lg"></i></a>';
}
endif;



/**
 * site 头部
 * @return string
 */
function io_site_header(&$is_hide){
    global $sites_type, $is_hide;

    $level_d = get_user_level_directions_html('sites');
    if($level_d){
        $is_hide = true;
        return $level_d;
    }
    $is_hide = false;

    $html = io_sites_header();

    return $html;
}
/**
 * site 正文
 * @return void
 */
function io_site_content(){
    global $sites_type;
    while (have_posts()): the_post();
    $post_id = get_the_ID();
    do_action('io_single_content_before', $post_id, 'sites');
    ?>
    <div class="panel site-content card"> 
        <div class="card-body">
            <?php show_ad('ad_post_content_top', false) ?>
            <div class="panel-body single">
                <?php  
                do_action('io_single_before', 'sites');
                $contentinfo = get_the_content();
                if( $contentinfo ){
                    echo apply_filters('the_content', $contentinfo);
                    thePostPage();
                }else{
                    echo htmlspecialchars(get_post_meta($post_id, '_sites_sescribe', true));
                }
                if('down' === $sites_type){
                    if ($formal_url = get_post_meta($post_id, '_down_formal', true))
                        echo ('<div class="text-center"><a href="' . go_to($formal_url) . '" target="_blank" class="btn btn-lg btn-outline-primary   text-lg radius-50 py-3 px-5 my-3">' . __('去官方网站了解更多', 'i_theme') . '</a></div>');
                }
                do_action('io_single_after', 'sites');
                ?>
            </div>
            <?php show_ad('ad_post_content_bottom', false); ?>
        </div>
    </div>
    <?php
    if (io_get_option('leader_board', false) && io_get_option('details_chart', false) && get_post_status($post_id) === 'publish') {
        //图表统计
    ?>
    <h2 class="text-gray text-lg my-4"><i class="iconfont icon-zouxiang mr-1"></i><?php _e('数据统计', 'i_theme') ?></h2>
    <div class="card io-chart"> 
        <div id="chart-container" class="" style="height:300px" data-type="<?php echo $sites_type ?>" data-post_id="<?php echo $post_id ?>" data-nonce="<?php echo wp_create_nonce('post_ranking_data') ?>">
            <div class="chart-placeholder p-4">
                <div class="legend">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="pillar">
                    <span style="height:40%"></span>
                    <span style="height:60%"></span>
                    <span style="height:30%"></span>
                    <span style="height:70%"></span>
                    <span style="height:80%"></span>
                    <span style="height:60%"></span>
                    <span style="height:90%"></span>
                    <span style="height:50%"></span>
                    <span style="height:40%"></span>
                    <span style="height:80%"></span>
                    <span style="height:60%"></span>
                    <span style="height:50%"></span>
                </div>
            </div>
        </div> 
    </div> 
    <?php
    }
    $title = get_the_title();

        $views      = function_exists('the_views')? the_views(false) :  '0' ;
        $m_link_url = get_post_meta($post_id, '_sites_link', true); 
        get_data_evaluation($title, $views, $m_link_url);

    do_action('io_single_content_after', $post_id, 'sites');
    endwhile;
}


function io_sites_header()
{
    global $sites_type;

    $post_id    = get_the_ID();
    $m_link_url = get_post_meta($post_id, '_sites_link', true);
    $is_dead    = get_post_meta($post_id, '_affirm_dead_url', true);
    $site_title = get_the_title();

    $view = function_exists('the_views') ? the_views(false, '<span class="views mr-3"><i class="iconfont icon-chakan-line"></i> ', '</span>') : '';

    $html = '<div class="d-flex flex-column flex-md-row site-content mb-4 mb-md-5">';

    $html .= '<!-- 网址信息 -->';
    $html .= '<div class="site-body flex-fill text-sm">';

    $html .= '<div class="d-flex flex-wrap mb-4">';
    $html .= '<div class="site-name-box flex-fill mb-3">';
    // 标题
    $html .= '<h1 class="site-name h3 mb-3">' . $site_title;
    $language = get_post_meta($post_id, '_sites_language', true);
    if ($m_link_url != "" && $language && !find_character($language, ['中文', '汉语', 'zh', 'cn', '简体'])) {
        $html .= '<a class="text-xs" href="//fanyi.baidu.com/transpage?query=' . get_url_host($m_link_url) . '&from=auto&to=zh&source=url&render=1" target="_blank" rel="nofollow noopener noreferrer">' . __('翻译站点', 'i_theme') . '<i class="iconfont icon-wailian text-ss"></i></a>';
    }
    $html .= io_get_post_edit_link($post_id);
    $html .= '</h1>';

    $html .= get_post_meta_small();
    $html .= '</div>';
    
    $html .= '<div class="posts-like">'.get_posts_star_btn($post_id, 'btn vc-l-red text-md py-1', true).'</div>';
    $html .= '</div>';
    

    $html .= io_site_header_info($m_link_url, $is_dead, $site_title);

    $html .= '</div>';
    $html .= '<!-- 网址信息 end -->';

    $html .= io_site_header_img($m_link_url, $site_title);

    $html .= '</div>';

    // 还原主循环
    //$post = $tmp_post;
    //setup_postdata($post);
    return $html;
}


/**
 * 头部网址预览图
 * @param mixed $link 网址链接
 * @param mixed $title 网址标题
 * @return string
 */
function io_site_header_img($link, $title)
{
    global $sites_type;
    $html = '';
    if (io_get_option('sites_preview', true) || 'wechat' === $sites_type) {
        $post_id = get_the_ID();
        $preview = get_sites_preview($post_id, $link);
        $favicon = get_sites_favicon($post_id, $link);


        $html = '<div class="sites-preview ml-0 ml-md-2 mt-3 mt-md-0"><div class="preview-body">';
        $html .= '<div class="site-favicon">';
        $html .= '<img src="' . $favicon . '" alt="' . $title . '" width="16" height="16">';
        $html .= '<span class="text-muted text-xs">' . $title . '</span>';
        $html .= '</div>';
        $html .= '<div class="site-img img-'. $sites_type .'">';
        $html .= get_lazy_img($preview, $title, ['456', '300'], '', get_theme_file_uri('/assets/images/sites_null.png'));
        if ('sites' === $sites_type) {
            $html .= '<a href="' . go_to($link) . '" title="' . $title . '" target="_blank" class="btn preview-btn rounded-pill vc-theme btn-shadow px-4 btn-sm"><span>' . io_get_option('open_sites_title', __('打开网站', 'i_theme')) . '</span></a>';
        }
        $html .= '</div>';

        $html .= '</div></div>';
    }else{
        if(io_get_option('sites_top_right','')){
            $html .= '<div class="sites-preview sites-top-right ml-0 ml-md-2 mt-3 mt-md-0">';
            $html .= io_get_option('sites_top_right',true);
            $html .= '</div>';
        }
    }

    return $html;
}

if(!function_exists('io_site_header_info')):
function io_site_header_info($m_link_url,$is_dead,$sitetitle){
    global $sites_type;
    $post_id = get_the_ID();

    $terms = io_get_posts_terms_btn($post_id, array('favorites', 'sitetag'), array('<i class="iconfont icon-folder mr-1"></i>', '# '), '', 'text-sm');
    if ($terms) {
        $terms = '<div class="terms-list mt-3">' . $terms . '</div>';
    }
    $wechat_id = get_post_meta_img($post_id, '_wechat_id', true);
    $width = 150;
    if(get_post_meta_img($post_id, '_wechat_qr', true) || $sites_type == 'wechat'){
        $m_qrurl = get_post_meta_img($post_id, '_wechat_qr', true);
        $qrurl = "<img src='".$m_qrurl."' width='{$width}'>";
        if(get_post_meta($post_id,'_is_min_app', true) ){
            $qrname = __("小程序",'i_theme');
            if($m_qrurl == "")
                $qrurl = '<p>'.__('居然没有添加二维码','i_theme').'</p>';
        }else{
            $qrname = __("公众号",'i_theme');
            if($m_qrurl == ""){
                if($wechat_id){
                    $qrurl = "<img src='https://open.weixin.qq.com/qr/code?username=".$wechat_id."' width='{$width}'>";
                }else{
                    $qrurl = '<p>'.__('居然没有添加二维码','i_theme').'</p>';
                }
            }
        }
    }else{
        $m_post_link_url = $m_link_url ?: get_permalink($post_id);
        $qrurl = "<img src='".get_qr_url($m_post_link_url, $width)."' width='{$width}'>";
        $qrname = __("手机查看",'i_theme');
    }

    // 网址信息
    $table   = '';
    $country = get_post_meta($post_id, '_sites_country', true);
    if ($country) {
        $table .= '<div class="table-row"><div class="table-title">'.__('所在地：','i_theme').'</div><div class="table-value">' . $country . '</div></div>';
    }
    $language = get_post_meta($post_id, '_sites_language', true);
    if ($language) {
        $table .= '<div class="table-row"><div class="table-title">'.__('语言：','i_theme').'</div><div class="table-value">' . $language . '</div></div>';
    }
    if ($wechat_id) {
        $table .= '<div class="table-row"><div class="table-title">' . __('公众号ID：', 'i_theme') . '</div><div class="table-value">' . $wechat_id . '</div></div>';
    }
    $table .= '<div class="table-row"><div class="table-title">'.__('收录时间：','i_theme').'</div><div class="table-value">' . get_the_time('Y-m-d') . '</div></div>';
    $spare_link = get_post_meta($post_id, '_spare_sites_link', true);
    if (!$is_dead && $spare_link) {
        $table .= '<div class="table-row">';
        $table .= '<div class="table-title">' . __('其他站点:', 'i_theme') . '</div>';
        $table .= '<div class="table-value">';
        for ($i = 0; $i < count($spare_link); $i++) {
            $table .= '<a class="mb-2 mr-3" href="' . go_to($spare_link[$i]['spare_url']) . '" data-toggle="tooltip" title="' . $spare_link[$i]['spare_note'] . '" target="_blank" style="white-space:nowrap"><span>' . $spare_link[$i]['spare_name'] . '<i class="iconfont icon-wailian"></i></span></a>';
        }
        $table .= '</div>';
        $table .= '</div>';
    }
    $table = $table ? '<div class="table-div">' . $table . '</div>' : '';

    // 爱站权重
    $az_html = '';
    if($sites_type == 'sites' && !$is_dead && io_get_option('url_rank',false)){
        $aizhan  = go_to('https://seo.5118.com/' . get_url_host($m_link_url) . '?t=ydm', true);
        $az_html .= '<div class="mt-2 sites-seo-load" data-url="'.get_url_host($m_link_url).'" data-go_to="'. $aizhan .'">';
        $az_html .= '<span class="sites-weight loading"></span><span class="sites-weight loading"></span><span class="sites-weight loading"></span><span class="sites-weight loading"></span><span class="sites-weight loading"></span>';
        //$az_html .= '<span class="mr-2">PC <a href="'. $aizhan .'" title="百度权重" target="_blank"><img class="" src="//baidurank.aizhan.com/api/br?domain='.get_url_host($m_link_url).'&style=images" alt="百度权重" title="百度权重" style="height:18px"></a></span>';
        //$az_html .= '<span class="mr-2">'.__('移动','i_theme') .' <a href="'. $aizhan .'" title="百度移动权重" target="_blank"><img class="" src="//baidurank.aizhan.com/api/mbr?domain='.get_url_host($m_link_url).'&style=images" alt="百度移动权重" title="百度移动权重" style="height:18px"></a></span>';
        $az_html .= '</div>';
    }

    // 目标站链接、手机查看按钮
    $btn = '';
    if ($m_link_url != "") {
        $a_class = '';
        $a_ico   = 'icon-arrow-r-m';
        if ($is_dead) {
            $m_link_url = esc_url(home_url());
            $a_class = ' disabled';
            $a_ico   = 'icon-subtract';
        }
        $btn .= '<a href="' . go_to($m_link_url) . '" title="' . $sitetitle . '" target="_blank" class="btn vc-theme btn-shadow px-4 btn-i-r mr-2' . $a_class . '"><span>' . io_get_option('open_sites_title', __('打开网站', 'i_theme')) . '<i class="iconfont ' . $a_ico . '"></i></span></a>';
    }
    if(!$is_dead && ((io_get_option('mobile_view_btn',true) && !wp_is_mobile())|| $sites_type == "wechat")){
        $btn .= '<a href="javascript:" class="btn vc-l-theme btn-outline qr-img btn-i-r mr-2"  data-toggle="tooltip" data-placement="bottom" data-html="true" title="'.$qrurl.'"><span>'.$qrname.'<i class="iconfont icon-qr-sweep"></i></span></a>';
    }
    $btn .= get_report_button();
    /**
     * HOOK : FILTER HOOK
     * io_site_header_btn
     * 
     * @var mixed $btn 网址头部按钮
     * @var mixed $post_id 文章ID
     */
    $btn = apply_filters('io_site_header_btn', $btn, $post_id);
    $btn = '<div class="site-go mt-3">'.$btn.'</div>';

    // 其他信息
    $other = '';
    if($is_dead){
        $other .= '<div class="text-xs tips-box vc-l-red mt-3"><i class="iconfont icon-warning mr-2"></i>'.__('经过确认，此站已经关闭，故本站不再提供跳转，仅保留存档。','i_theme').'</div>';
    }

    $html = '<div class="mt-2">';
    $html .= '<p class="mb-2">'.io_get_excerpt(170,'_sites_sescribe').'</p>';

    $html .= $table;
    $html .= $az_html;
    $html .= $btn;
    $html .= $other;
    $html .= $terms;
    $html .= '</div>';

    return $html;
}
endif;
/**
 * 获取网址类型文章的缩略图
 * 
 * @param string $title      网址标题
 * @param string $link       网址目标地址
 * @param string $type       网址类型
 * @param bool   $show       二维码是否可见
 * @param bool   $is_preview 是否显示预览
 * @return string
 */
function get_site_thumbnail($title, $link, $type, $show, &$is_preview = ''){
    $post_id = get_the_ID();

    $preview = true;  // 优先显示预览图
    
    $sites_ico = get_post_meta_img($post_id, '_thumbnail', true);

    if($show && $type == "wechat"){
        return get_sites_wechat_qr();
    }
    if($sites_ico == ''){
        if( $link != '' || ($type == "sites" && $link != '') ){
            if($sites_ico = get_post_meta($post_id, '_sites_preview', true)){
                $is_preview = true;
            }else{
                if(!$preview){
                    if(empty($sites_ico) && io_get_option('is_letter_ico',false) && !io_get_option('first_api_ico',false)){
                        $sites_ico = io_letter_ico($title, 160);
                    }else{
                        $sites_ico = get_favicon_api_url($link);
                    }
                }else{
                    $sites_ico  = get_preview_api_url($link, 383, 268);
                    $is_preview = true;
                }
            }
        } elseif ($type == "wechat") {
            $sites_ico = get_theme_file_uri('/assets/images/qr_ico.png');
        } else {
            $sites_ico = get_theme_file_uri('/assets/images/favicon.png');
        }
    }
    return $sites_ico;
}

/**
 * 获取网址预览图
 * 
 * @param mixed $post_id
 * @param mixed $link
 * @return mixed
 */
function get_sites_preview($post_id, $link)
{
    global $sites_type;
    if (!$sites_type) {
        $sites_type = get_post_meta($post_id, '_sites_type', true);
    }

    $preview = get_post_meta($post_id, '_sites_preview', true);
    if ($sites_type == "wechat") {
        $preview = get_sites_wechat_qr();
    } elseif ($preview == '') {
        $preview = get_preview_api_url($link, 456, 300);
    }
    return $preview;
}

/**
 * 获取网址图标
 * @param mixed $post_id
 * @param mixed $link
 * @return mixed
 */
function get_sites_favicon($post_id, $link)
{
    $type    = get_post_meta($post_id, '_sites_type', true);
    $favicon = get_post_meta_img($post_id, '_thumbnail', true);
    if ($favicon == '') {
        if ($link != '') {
            if (io_get_option('is_letter_ico', false) && !io_get_option('first_api_ico', false)) {
                $title   = get_the_title($post_id);
                $favicon = io_letter_ico($title, 160);
            } else {
                $favicon = get_favicon_api_url($link);
            }
        } else {
            if ($type == "wechat") {
                $favicon = get_theme_file_uri('/assets/images/qr_ico.png');
            } else {
                $favicon = get_theme_file_uri('/assets/images/favicon.png');
            }
        }
    }
    return $favicon;
}
/**
 * 获取微信二维码
 * @return string
 */
function get_sites_wechat_qr(){
    global $post;

    $qrurl = get_post_meta_img($post->ID, '_wechat_qr', true);
    if (empty($qrurl)) {
        if (!get_post_meta($post->ID, '_is_min_app', true)) {
            if ($wechat_id = get_post_meta_img($post->ID, '_wechat_id', true)) {
                $qrurl = "https://open.weixin.qq.com/qr/code?username={$wechat_id}";
            }
        } else {
            $qrurl = get_theme_file_uri('/assets/images/qr_ico.png');
        }
    }
    return $qrurl;
}


if(!function_exists('get_sites_card_meta')):
/**
 * 获取网址 meta 数据
 * 
 * @return array
 */
function get_sites_card_meta() {
    $post_id   = get_the_ID();
    $post_type = get_post_type();

    // 基本 meta 信息
    $go_url       = get_post_meta($post_id, '_sites_link', true);
    $link         = get_permalink();
    $default_ico  = get_theme_file_uri('/assets/images/favicon.png');
    $title        = get_the_title();
    $is_dead      = get_post_meta($post_id, '_affirm_dead_url', true);
    $summary      = htmlspecialchars(get_post_meta($post_id, '_sites_sescribe', true)) ?: io_get_excerpt(30);
    $sites_type   = get_post_meta($post_id, '_sites_type', true);
    $blank        = new_window();
    
    // 更新摘要信息
    if (empty($summary)) {
        update_post_meta($post_id, '_sites_sescribe', $summary);
    }


    // 根据 post_type 设置默认链接
    if ($post_type != 'sites') {
        $go_url = get_permalink($post_id);
    }

    // 初始化 tooltip 和二维码信息
    $tooltip   = 'data-toggle="tooltip" data-placement="bottom"';
    $tip_title = $go_url;
    $is_html   = '';
    $width     = 128;

    // 检查微信二维码信息
    if ($wechat_qr = get_post_meta_img($post_id, '_wechat_qr', true)) {
        $tip_title = "<img src='{$wechat_qr}' width='{$width}'>";
        $is_html   = 'data-html="true"';
    } elseif (($wechat_id = get_post_meta_img($post_id, '_wechat_id', true)) && !get_post_meta_img($post_id, '_is_min_app', true)) {
        $tip_title = "<img src='https://open.weixin.qq.com/qr/code?username={$wechat_id}' width='{$width}'>";
        $is_html   = 'data-html="true"';
    } else {
        $tip_title = get_tooltip_title($go_url, $sites_type, $title, $summary, $width, $is_html);
    }


    // 处理图标信息
    $ico = get_site_icon($post_id, $post_type, $title, $go_url, $default_ico, $sites_type);

    // 检查是否为死链
    if ($is_dead) {
        $go_url = get_permalink();
    }

    // 检查权限
    $post_show  = true;
    $user_level = io_get_post_visibility_slug($post_id);
    if (!is_user_logged_in() && $user_level && $user_level != 'all') {
        $go_url = get_permalink();
        $post_show = false;
    }

    // 获取网站截图
    $preview = get_post_meta($post_id, '_sites_preview', true) ?: get_preview_api_url($go_url, 400, 300);

    return array(
        'post_id'       => $post_id,
        'title'         => $title,         // 名字
        'ico'           => $ico['ico'],    // 图标
        'link'          => $link,          // 详情页
        'is_html'       => $is_html,       // tooltip 类型
        'tooltip'       => $tooltip,       // tooltip 开关
        'tip_title'     => $tip_title,     // tooltip 内容
        'blank'         => $blank,         // 新窗口
        'summary'       => $summary,       // 简介
        'sites_type'    => $sites_type,    // 类型 网址 公众号
        'go_url'        => $go_url,        // 目标地址
        'default_ico'   => $default_ico,   // 默认图标
        'first_api_ico' => $ico['first_api'], // 使用js首字母图标
        'is_dead'       => $is_dead,       // 确认是死链
        'post_show'     => $post_show,     // 权限 是否可见见
        'preview'       => $preview        // 网站截图
    );
}
endif;

/**
 * 获取 tooltip 内容
 */
function get_tooltip_title($link_url, $sites_type, $title, $summary, $width, &$is_html)
{
    $is_html = '';
    switch (io_get_option('po_prompt', 'null')) {
        case 'null':
            $tip_title = $title;
            break;
        case 'url':
            $tip_title = get_url_tooltip($link_url, $sites_type);
            break;
        case 'summary':
            $tip_title = ($sites_type == "down") ? __('下载', 'i_theme') . "“{$title}”" : $summary;
            break;
        case 'qr':
            if (!empty($link_url)) {
                $tip_title = "<img src='" . get_qr_url($link_url, $width) . "' width='{$width}' height='{$width}'>";
                $is_html   = 'data-html="true"';
            } else {
                $tip_title = get_url_tooltip($link_url, $sites_type);
            }
            break;
        default:
            $tip_title = $title;
    }
    return $tip_title;
}

/**
 * 获取 URL
 */
function get_fallback_url($link_url, $sites_type) {
    if (!io_get_option('global_goto', false)) {
        return get_permalink();
    } elseif ($sites_type && $sites_type != "sites") {
        return get_permalink();
    } elseif (empty($link_url)) {
        return '';
    } else {
        return go_to($link_url);
    }
}

/**
 * 获取图标信息
 * 
 * @param mixed $post_id 文章ID
 * @param mixed $post_type 文章类型
 * @param mixed $title 文章标题
 * @param mixed $link_url 目标链接 
 * @param mixed $default_ico 默认图标
 * @param mixed $sites_type 网址文章类型 网站 公众号 下载
 * @return array
 */
function get_site_icon($post_id, $post_type, $title, $link_url, $default_ico, $sites_type) {
    $ico = '';
    $first_api_ico = false;

    if ($post_type != 'sites') {
        $ico = io_theme_get_thumb();
    } else {
        $ico = get_post_meta_img($post_id, '_thumbnail', true);
        if (empty($ico) && io_get_option('is_letter_ico', false)) {
            $ico = io_letter_ico($title);
            if (io_get_option('first_api_ico', false)) {
                $ico           = '';
                $first_api_ico = true;
            }
        }
    }

    if (empty($ico)) {
        if (!empty($link_url) && $sites_type == "sites") {
            $ico = get_favicon_api_url($link_url);
        } elseif ($sites_type == "wechat") {
            $ico = get_theme_file_uri('/assets/images/qr_ico.png');
        } elseif ($sites_type == "down") {
            $ico = get_theme_file_uri('/assets/images/down_ico.png');
        } else {
            $ico = $default_ico;
        }
    }

    return array('ico' => $ico, 'first_api' => $first_api_ico);
}

/**
 * 获取站点类型名称
 * 
 * @param mixed $type
 * @return string|string[]
 */
function get_site_type_name($type = ''){
    $data = array(
        "sites" => __('网址','i_theme'),
        "wechat" => __('公众号','i_theme'),
    );
    if(isset($data[$type])){
        return $data[$type];
    }
    return $data;
}

/**
 * 获取 URL 提示信息
 */
function get_url_tooltip($link_url, $sites_type) {
    if (empty($link_url)) {
        if ($sites_type == "wechat") {
            return __('居然没有添加二维码', 'i_theme');
        } else {
            return __('没有 url', 'i_theme');
        }
    }
    return $link_url;
}

function get_report_reason(){
    $reasons = array(
        '1' => __('已失效','i_theme'),
        '2' => __('重定向&变更','i_theme'),//必须为 2
        '3' => __('已屏蔽','i_theme'),
        '4' => __('敏感内容','i_theme'),
        '0' => __('其他','i_theme'), //必须为 0
    );
    return apply_filters('io_sites_report_reason', $reasons);
}

/**
 * 举报模态框
 * @return void
 */
function report_model_body(){
    global $post_type;
    $post_id = get_the_ID();

    if ($post_type != 'sites' || get_post_meta($post_id, '_sites_type', true) == 'down') {
        return;
    }
    ?>
    <div class="modal fade add_new_sites_modal" id="report-sites-modal" tabindex="-1" role="dialog" aria-labelledby="report-sites-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-md" id="report-sites-title"><?php _e('反馈','i_theme') ?></h5>
                    <button type="button" id="close-sites-modal" class="close io-close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="iconfont icon-close text-lg"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <div class="tips-box vc-l-blue btn-block" role="alert">
                    <i class="iconfont icon-statement "></i> <?php _e('让我们一起共建文明社区！您的反馈至关重要！','i_theme') ?>
                    </div>
                    <form id="report-form" method="post"> 
                        <input type="hidden" name="post_id" value="<?php echo $post_id ?>">
                        <input type="hidden" name="action" value="report_site_content">
                        <div class="form-row">
                            <?php
                            $option = get_report_reason();
                            if(get_post_meta($post_id, '_affirm_dead_url', true)){
                                $option = array('666' => __('已可访问','i_theme'));
                            }
                            foreach ($option as $key => $reason) {
                                echo '<div class="col-6 py-1">
                                <label><input type="radio" name="reason" class="reason-type-' . $key . '" value="' . $key . '" ' . (in_array($key,array(1,666)) ? 'checked' : '') . '> ' . $reason . '</label>
                            </div>';
                            }
                            ?>
                        </div>
                        <div class="form-group other-reason-input" style="display: none;">
                            <input type="text" class="form-control other-reason" value="" placeholder="<?php _e('其它信息，可选','i_theme') ?>">
                        </div>  
                        <div class="form-group redirect-url-input" style="display: none;">
                            <input type="text" class="form-control redirect-url" value="" placeholder="<?php _e('重定向&变更后的地址','i_theme') ?>">
                        </div> 
                        <div class=" text-center">
                            <button type="submit" class="btn vc-l-red"><?php _e('提交反馈','i_theme') ?></button>
                        </div> 
                    </form>
                </div> 
            </div>
        </div>
        <script>
        $(function () {
            $('.tooltip-toggle').tooltip();
            $('input[type=radio][name=reason]').change(function() {
                var t = $(this); 
                var reason = $('.other-reason-input');
                var url = $('.redirect-url-input');
                reason.hide();
                url.hide();
                if(t.val()==='0'){
                    reason.show();
                }else if(t.val()==='2'){
                    url.show();
                }
            }); 
            $(document).on("submit",'#report-form', function(event){
                event.preventDefault(); 
                var t = $(this); 
                var reason = t.find('input[name="reason"]:checked').val();
                if(reason === "0"){
                    reason = t.find('.other-reason').val();
                    if(reason==""){
                        showAlert(JSON.parse('{"status":4,"msg":"<?php _e('信息不能为空！','i_theme') ?>"}'));
                        return false;
                    }
                }
                if(reason === "2"){
                    if(t.find('.redirect-url').val()==""){
                        showAlert(JSON.parse('{"status":4,"msg":"<?php _e('信息不能为空！','i_theme') ?>"}'));
                        return false;
                    }
                }
                $.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                    type: 'POST', 
                    dataType: 'json',
                    data: {
                        action : t.find('input[name="action"]').val(),
                        post_id : t.find('input[name="post_id"]').val(),
                        reason : reason,
                        redirect : t.find('.redirect-url').val(),
                    },
                })
                .done(function(response) {   
                    if(response.status == 1){
                        $('#report-sites-modal').modal('hide');
                    } 
                    showAlert(response);
                })
                .fail(function() {  
                    showAlert(JSON.parse('{"status":4,"msg":"<?php _e('网络错误 --.','i_theme') ?>"}'));
                }); 
                return false;
            });
        });
        </script>
    </div>
    <?php
}
if(io_get_option('report_button',true)){
    add_action('wp_footer', 'report_model_body');
}
