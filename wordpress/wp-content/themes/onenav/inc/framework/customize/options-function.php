<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-08-30 11:10:06
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-05 14:04:23
 * @FilePath: /onenav/inc/framework/customize/options-function.php
 * @Description: 
 */
function active_html(){
        if (IO_PRO) {
            $con = '<div id="authorization_form" class="ajax-form" ajax-url="' . esc_url(admin_url('admin-ajax.php')) . '">
            <div class="vip-certificate">
            <div class="ok-icon"><svg t="1585712312243" class="icon" style="width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3845" data-spm-anchor-id="a313x.7781069.0.i0"><path d="M115.456 0h793.6a51.2 51.2 0 0 1 51.2 51.2v294.4a102.4 102.4 0 0 1-102.4 102.4h-691.2a102.4 102.4 0 0 1-102.4-102.4V51.2a51.2 51.2 0 0 1 51.2-51.2z m0 0" fill="#FF6B5A" p-id="3846"></path><path d="M256 13.056h95.744v402.432H256zM671.488 13.056h95.744v402.432h-95.744z" fill="#FFFFFF" p-id="3847"></path><path d="M89.856 586.752L512 1022.72l421.632-435.2z m0 0" fill="#6DC1E2" p-id="3848"></path><path d="M89.856 586.752l235.52-253.952h372.736l235.52 253.952z m0 0" fill="#ADD9EA" p-id="3849"></path><path d="M301.824 586.752L443.136 332.8h137.216l141.312 253.952z m0 0" fill="#E1F9FF" p-id="3850"></path><path d="M301.824 586.752l209.92 435.2 209.92-435.2z m0 0" fill="#9AE6F7" p-id="3851"></path></svg></div>
            <div class="ok-text" style="font-size: 15px; ">恭喜您! 已完成授权</div>
            <input type="hidden" ajax-name="action" value="get_iotheme_delete_authorization">
            '.wp_nonce_field('delete_authorization','_ajax_nonce').'
            <a href="javascript:;" title="撤销授权" id="authorization_submit" class="bnt-svg ajax-submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52.3 52.3"><path d="M26.1 1.1c-13.8 0-25 11.2-25 25s11.2 25 25 25 25-11.2 25-25-11.2-25-25-25zm11.7 32.7c1.1 1.1 1.1 2.9 0 4-.5.5-1.3.8-2 .8s-1.4-.3-2-.8l-7.7-7.7-7.7 7.7c-.5.5-1.3.8-2 .8s-1.4-.3-2-.8c-1.1-1.1-1.1-2.9 0-4l7.7-7.7-7.7-7.7c-1.1-1.1-1.1-2.9 0-4s2.9-1.1 4 0l7.7 7.7 7.7-7.7c1.1-1.1 2.9-1.1 4 0s1.1 2.9 0 4l-7.7 7.7 7.7 7.7z" fill="#efefef"/></svg></a>
            </div>
            <div class="ajax-notice"></div>
            </div>';
        } else {
            $con = '<div id="authorization_form" class="authorization-form ajax-form" ajax-url="' . esc_url(admin_url('admin-ajax.php')) . '">
            <h4 class="aut-title">授权主题</h4>
            <div class="aut-content">
            <p style="color:#fd4c73;">请先使用订单提供的激活码<a href="//www.iotheme.cn/user?action=reg" target="_blank" title="授权域名">授权域名</a>(<a href="https://www.iotheme.cn/zhutishouquanyumingzhuceshuoming.html" target="_blank" title="授权教程">授权教程</a>)。 如果没有购买，请访问<a href="//www.iotheme.cn/store/onenav.html" target="_blank" title="购买主题">iTheme</a>购买。</p>
            <div style="margin-bottom: 20px">
            <input class="regular-text not-change" type="text" ajax-name="key_code" value="" placeholder="请输入激活码">';
            if(filter_var( $_SERVER["HTTP_HOST"], FILTER_VALIDATE_IP) !== false){
                $con .= '<div style="text-align:center;margin-top:8px">
                <span style="display:initial;background:#ffdede;color:#760d0d;padding:2px 10px;border-radius:5px;margin-top:5px">注意：<code>'.$_SERVER["HTTP_HOST"].'</code>
                不是域名，请用授权的域名或子域名访问网站。</span></div>';
            }
            $con .= '<input type="hidden" ajax-name="action" value="get_iotheme_authorization">
            </div>
            <a href="javascript:;" id="authorization_submit" class="but c-blue ajax-submit curl-aut-submit">一键授权</a>
            <div class="ajax-notice"></div>
            </div>
            </div>';
        }
        echo apply_filters('io_active_html_filters', $con);
}
function weixin_data_html(){
    if (IO_PRO) {
        $html = IOTOOLS::getWeiXinData();
        echo $html;
    }
}
function ip_db_manage(){
    $basedir            = wp_upload_dir()['basedir'].'/ip_data';
    $l_qqwry_path       = $basedir.'/qqwry.dat';
    $l_ip2region_path   = $basedir.'/ip2region.xdb';
    $l_v6_path          = $basedir.'/ipv6wry.db';

    $ipv4_type          = io_get_option('ip_location', 'qqwry', 'v4_type');
    $ip_qqwry_path      = maybe_unserialize(get_option('ip_qqwry_path', ''));
    $ip_ip2region_path  = maybe_unserialize(get_option('ip_ip2region_path', ''));
    $ip_v6_path         = maybe_unserialize(get_option('ip_v6_path', ''));

    if(empty($ip_qqwry_path) && file_exists($l_qqwry_path)){
        $ip_qqwry_path = array('time'=>'手动上传','path'=>$l_qqwry_path);
    }
    if(empty($ip_ip2region_path) && file_exists($l_ip2region_path)){
        $ip_ip2region_path = array('time'=>'手动上传','path'=>$l_ip2region_path);
    }
    if(empty($ip_v6_path) && file_exists($l_v6_path)){
        $ip_v6_path = array('time'=>'手动上传','path'=>$l_v6_path);
    }

    $ip_v4_path         = $ip_ip2region_path;
    if ($ipv4_type == 'qqwry')
        $ip_v4_path = $ip_qqwry_path;

    $admin_ajax_url = admin_url('admin-ajax.php');
    $v4_html = 'IPv4数据库：<span class="ip-v4-path">未下载</span> <a href="'.add_query_arg(array('action'=>'update_ip_data','type'=>'v4'), $admin_ajax_url).'" class="but c-blue ajax-get">下载数据</a>';
    $v6_html = 'IPv6数据库：<span class="ip-v6-path">未下载</span> <a href="'.add_query_arg(array('action'=>'update_ip_data','type'=>'v6'), $admin_ajax_url).'" class="but c-blue ajax-get">下载数据</a>';
    if($ip_v4_path){
        $v4_html = 'IPv4数据库：<span class="ip-v4-path">数据更新日期('.$ip_v4_path['time'].')</span> <a href="'.add_query_arg(array('action'=>'update_ip_data','type'=>'v4'), $admin_ajax_url).'" class="but c-blue ajax-get">更新数据</a>';
    }
    if($ip_v6_path){
        $v6_html = 'IPv6数据库：<span class="ip-v6-path">数据更新日期('.$ip_v6_path['time'].')</span> <a href="'.add_query_arg(array('action'=>'update_ip_data','type'=>'v6'), $admin_ajax_url).'" class="but c-blue ajax-get">更新数据</a>';
    }
    $html = '<h4>IP 归属地数据配置</h4><div class="ajax-form">
    <div style="display:flex;align-items:center">'.$v4_html.'</div>
    <div style="display:flex;align-items:center">'.$v6_html.'</div>
    <div class="ajax-notice"></div></div>
    <p><b>注意：</b>如果无法下载，请 <a href="https://iowen.lanzouf.com/iJwA90msx90d" target="_blank">下载数据包</a> 手动 <b>上传</b> 并 <b>解压</b> 到 \wp-content\uploads\ip_data 目录</p>';
    return $html;
}
/**
 * 下载ip数据包
 * @return never
 */
function io_update_ip_data(){ 
    $type = $_REQUEST['type'];
    $basedir = wp_upload_dir()['basedir'].'/ip_data';
    if (!file_exists($basedir)) {
        if(!mkdir($basedir, 0755)){
            exit(json_encode(array('error' => 1, 'msg' => '创建文件夹失败，请检测(/wp-content/uploads)文件夹权限，赋予 755/www 权限。')));
        }
    }
    
    $ipv4_type          = io_get_option('ip_location', 'qqwry', 'v4_type');
    $ip_qqwry_path      = maybe_unserialize(get_option('ip_qqwry_path', ''));
    $ip_ip2region_path  = maybe_unserialize(get_option('ip_ip2region_path', ''));
    $ip_v6_path         = maybe_unserialize(get_option('ip_v6_path', ''));
    $ip_v4_path         = $ip_ip2region_path;
    
    $ip_qqwry_url      = 'https://api.iowen.cn/ip_data/qqwry.dat.zip';//'https://99wry.cf/qqwry.dat';
    $ip_ip2region_url  = 'https://api.iowen.cn/ip_data/ip2region.xdb.zip';//'https://github.com/lionsoul2014/ip2region/raw/master/data/ip2region.xdb';
    $ip_v6_url         = 'https://api.iowen.cn/ip_data/ipv6wry.db.zip';//'https://ip.zxinc.org/ip.7z';
    $ip_v4_url         = $ip_ip2region_url;

    $option_key = 'ip_ip2region_path';
    $data_name  = 'ip2region.xdb';
    if ($ipv4_type == 'qqwry'){
        $ip_v4_path = $ip_qqwry_path;
        $ip_v4_url  = $ip_qqwry_url;
        $option_key = 'ip_qqwry_path';
        $data_name  = 'qqwry.dat';
    }
    $ip_path = $ip_v4_path;
    $ip_url  = $ip_v4_url;
    if("v4" != $type){
        $option_key = 'ip_v6_path';
        $data_name  = 'ipv6wry.db';
        $ip_path    = $ip_v6_path;
        $ip_url     = $ip_v6_url;
    }

    $http      = new Yurun\Util\HttpRequest;
    $response  = $http->get('https://api.iowen.cn/ip_data/v.html');
    $v_data    = $response->json(true);
    if(!$v_data) {
        exit(json_encode(array('error' => 1, 'msg' => '数据信息获取失败，请重试！')));
    }
    $data_time = $v_data[$data_name];

    if (!empty($ip_path) && isset($ip_path['time'])) {
        if(strtotime($ip_path['time'])>=strtotime($data_time)){
            exit(json_encode(array('error' => 0, 'msg' => '已经是最新数据，无需更新！服务器数据日期：'.$data_time)));
        }
    }

    $stime = microtime(true);

    $response = $http->get($ip_url);
    $result   = $response->body();

    $qqwry_time = microtime(true);
    
    $download_spend = $qqwry_time - $stime;
    if (!$result) {
        exit(json_encode(array('error' => 1, 'msg' => '下载失败，'.sprintf("下载耗时%s秒", sprintf("%.2f",$download_spend)))));
    }

    $unzip_time = microtime(true);
    $tmp_file    = $basedir . '/' . 'ipdata.zip';
    $online_file = $basedir . '/' . $data_name;

    //if(!empty($ip_path) && isset($ip_path['path']) && file_exists($ip_path['path'])){
    //    if(!unlink($ip_path['path'])){
    //        exit(json_encode(array('error' => 1, 'msg' => '旧文件删除错误，请检查文件夹权限或者手动删除('.$ip_path['path'].')')));
    //    }
    //}
    if (file_put_contents($tmp_file, $result)) {
        if (!class_exists('ZipArchive')) {
            exit(json_encode(array('error' => 1, 'msg' => '错误：您的 PHP 版本不支持解压缩(unzip)功能。')));
        }
        $zip = new ZipArchive;
        // 检查存档是否可读。
        if ($zip->open($tmp_file) === TRUE) {
            if (is_writeable($basedir.'/')) {
                $zip->extractTo($basedir);
                $zip->close();
                if(!unlink($tmp_file)){
                    exit(json_encode(array('error' => 1, 'msg' => '缓存文件删除错误，请检查文件夹权限或者手动删除('.$tmp_file.')')));
                }
            } else {
                exit(json_encode(array('error' => 1, 'msg' => '错误：服务器无法写入目录('.$basedir.'/'.')。')));
            }
        } else {
            exit(json_encode(array('error' => 1, 'msg' => '错误：无法读取存档。')));
        }
        
        $put_time  = microtime(true);
        $put_spend = $put_time - $unzip_time;
        update_option($option_key, maybe_serialize(array('time' => $data_time, 'path' => $online_file)), false);
        exit(json_encode(array('error' => 0, 'reload' => 1, 'msg' => "更新成功， " . sprintf("下载耗时%s秒，写入耗时%s秒", sprintf("%.2f",$download_spend), sprintf("%.2f",$put_spend)))));
    } else {
        exit(json_encode(array('error' => 1, 'msg' => "更新失败， " . sprintf("下载耗时%s秒，", sprintf("%.2f",$download_spend)))));
    }
}
add_action('wp_ajax_update_ip_data', 'io_update_ip_data');

function add_customize_scripts(){
    wp_register_style('admin-options', IOCF::include_plugin_url('customize/css/options.css'), array(), IO_VERSION, '');
    wp_enqueue_style('admin-options'); 
    
    wp_register_script( 'admin-options', IOCF::include_plugin_url('customize/js/options.js'), array('jquery'), IO_VERSION, true );
    wp_enqueue_script( 'admin-options' ); 
}
add_action('iocf_enqueue','add_customize_scripts');
$cats_id = false;

// 获取自定义文章父分类
if(!function_exists('get_all_taxonomy')){
    function get_all_taxonomy(){  
        if( ! is_admin() ) { return; }
        $term_query = new WP_Term_Query( array(
            'taxonomy'   =>  array('favorites','apps'),
            'hide_empty' => false,
        ));
        $customize = array(); 
        if ( ! empty( $term_query->terms ) ) {
            foreach ( $term_query ->terms as $term ) { 
                if($term->parent == 0)
                    $customize["id_".$term->term_id] = $term->name;
            }
        }  
        return $customize;
    }
}

// 获取效果列表
if(!function_exists('get_all_fx_bg')){
    function get_all_fx_bg(){  
        $fx_bg = array(
            '0'      => '随机',
            '01'     => '1蜂窝侵蚀',
            '02'     => '2方格电流',
            '03'     => '3点-线',
            '04'     => '4视差点云',
            '05'     => '5方块龙卷',
            '06'     => '6圆-点',
            '07'     => '7星空白熊',
            '08'     => '8星空流星',
            '09'     => '9复古银幕',
            '10'     => '10晚霞',
            '11'     => '11星空小人',
            '12'     => '12夜',
            '13'     => '13色',
            '14'     => '14轮回方块',
            '15'     => '15粒子点',
            '16'     => '16圆',
            '17'     => '17夜-树',
            'custom' => '自定义',
        );
        return $fx_bg;
    }
}
/**
 * Sitemap 设置选项
 */
function io_site_map_but() {
    if( class_exists( 'IODX_Seo_Sitemap_Do_Sitemap' ) ) {
        echo '<div id="settings-container"><h2 class="menu-title"></h2>';
        IODX_Seo_Sitemap_Do_Sitemap::xml_notice();
        echo '<h2></h2></div>
        <a id="generate-baidu" class="button button-primary generate-sitemap">生成sitemap</a>
        <a id="delete-baidu" class="button button-secondary delete-sitemap">删除sitemap</a>
        <p id="sitemap-progress" class="test-mail-text" style="line-height:30px;color:#dd0c0c">修改SiteMAP选项后请保存成功再点“生成sitemap”</p>';
        IODX_Seo_Sitemap_Do_Sitemap::sitemap_jquery();
    }else{
        echo '<h2 style="line-height:30px;color:#dd0c0c">请先保存设置，然后刷新页面...</h2>';
    }
}
/**
 * Get taxonomies
 */
function setting_get_taxes() {
    $taxes = get_taxonomies( array( '_builtin' => false ), 'objects' );
	if( $taxes ) {
		foreach( $taxes as $key => $tax ) {
			$res[ $key ] = $tax->labels->name;
		}
	}		
	$res['post_tag'] = __('标签','i_theme');
	$res['category'] = __('分类目录','i_theme');
	$res = array_reverse( $res ); 
	return $res;
}

function io_test_mail_action(){
    $email = $_POST['email'];
    
    if (empty($email)) {
        exit(json_encode(array('error' => 1, 'msg' => '请输入邮箱号码')));
    }
	$subject = __('发信测试邮件', 'i_theme');
	$result = io_mail( $email, $subject, '收到这封邮件就说明你的设置正确！');
    if (is_array($result)) {
        exit(json_encode($result));
    }elseif($result){
        exit(json_encode(array('error' => 0, 'msg' => '发送成功')));
    }else{
        exit(json_encode(array('error' => 1, 'msg' => '发送失败')));
    }
}
add_action('wp_ajax_nopriv_test_mail', 'io_test_mail_action');  
add_action('wp_ajax_test_mail', 'io_test_mail_action');

//备份
function io_backup()
{ 
    if (IO_PRO) {
        $csf = array();
        $prefix = 'io_get_option';
        $options = get_option($prefix . '_backup');
        $lists = '暂无备份数据！';
        $admin_ajax_url = admin_url('admin-ajax.php');
        $delete_but = '';
        if ($options) {
            $lists = '';
            $options = array_reverse($options);
            $count = 0;
            foreach ($options as $key => $val) {
                $ajax_url = add_query_arg('key', $key, $admin_ajax_url);
                $del = '<a href="' . add_query_arg('action', 'options_backup_delete', $ajax_url) . '" data-confirm="确认要删除此备份[' . $key . ']？删除后不可恢复！" class="but c-yellow ajax-get ml10">删除</a>';
                $restore = '<a href="' . add_query_arg('action', 'options_backup_restore', $ajax_url) . '" data-confirm="确认将主题设置恢复到此备份吗？[' . $key . ']？" class="but c-blue ajax-get ml10">恢复</a>';
                $lists .= '<div class="backup-item flex ac jsb">';
                $lists .= '<div class="item-left"><div>' . $val['time'] . '</div><div> [' . $val['type'] . ']</div></div>';
                $lists .= '<span class="shrink-0">' . $restore . $del .  '</span>';
                $lists .= '</div>';
                $count++;
            }
            if ($count > 3) {
                $delete_but = '<a href="' . add_query_arg(array('action' => 'options_backup_delete_surplus', 'key' => 'all'), $admin_ajax_url) . '" data-confirm="确认要删除多余的备份数据吗？删除后不可恢复！" class="button csf-warning-primary ajax-get">删除备份 保留最新三份</a>';
            }
        }
        echo'<div class="csf-submessage csf-submessage-warning"><h3 style="color:#fd4c73;"><i class="csf-tab-icon fa fa-fw fa-copy"></i> 备份及恢复</h3>
        <ajaxform class="ajax-form">
        <div style="margin:10px 0">
        <p>系统会在重置、更新等重要操作时自动备份主题设置，您可以此进行恢复备份或手动备份</p>
        <p><b>备份列表：</b></p>
        <div class="card-box backup-box">
        ' . $lists . '
        </div>
        </div>
        <a href="' . add_query_arg('action', 'options_backup', $admin_ajax_url) . '" class="button button-primary ajax-get">备份当前配置</a>
        ' . $delete_but . '
        <p><i class="fa fa-fw fa-info-circle fa-fw"></i> 仅能保存主题设置，不能保存整站数据。（此操作可能会清除设置数据，请谨慎操作）</p>
        <div class="ajax-notice"></div>
        </ajaxform>
        </div>';
    }
}

//备份主题设置
function io_ajax_options_backup()
{
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '手动备份';
    $backup = io_options_backup($type);
    echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '当前配置已经备份')));
    exit();
}
add_action('wp_ajax_options_backup', 'io_ajax_options_backup');
//备份主题数据
function io_options_backup($type = '自动备份')
{
    $prefix = 'io_get_option';
    $options = get_option($prefix);

    $options_backup = get_option($prefix . '_backup');
    if (!$options_backup) $options_backup = array();
    $time = current_time('Y-m-d H:i:s');
    $options_backup[$time] = array(
        'time' => $time,
        'type' => $type,
        'data' => $options,
    );
    return update_option($prefix . '_backup', $options_backup);
}

function io_ajax_options_backup_delete()
{
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'msg' => '操作权限不足')));
        exit();
    }
    if (empty($_REQUEST['key'])) {
        echo (json_encode(array('error' => 1, 'msg' => '参数传入错误')));
        exit();
    }

    $prefix = 'io_get_option';
    if ($_REQUEST['action'] == 'options_backup_delete_all') {
        update_option($prefix . '_backup', false);
        echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '已删除全部备份数据')));
        exit();
    }

    $options_backup = get_option($prefix . '_backup');

    if ($_REQUEST['action'] == 'options_backup_delete_surplus') {
        if ($options_backup) {
            $options_backup = array_reverse($options_backup);
            update_option($prefix . '_backup', array_reverse(array_slice($options_backup, 0, 3)));
            echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '已删除多余备份数据，仅保留份')));
            exit();
        }
        echo (json_encode(array('error' => 1, 'msg' => '暂无可删除的数据')));
    }

    if (isset($options_backup[$_REQUEST['key']])) {
        unset($options_backup[$_REQUEST['key']]);

        update_option($prefix . '_backup', $options_backup);
        echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '所选备份已删除')));
    } else {
        echo (json_encode(array('error' => 1, 'msg' => '此备份已删除')));
    }
    exit();
}
add_action('wp_ajax_options_backup_delete', 'io_ajax_options_backup_delete');
add_action('wp_ajax_options_backup_delete_all', 'io_ajax_options_backup_delete');
add_action('wp_ajax_options_backup_delete_surplus', 'io_ajax_options_backup_delete');


function io_ajax_options_backup_restore()
{
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'msg' => '操作权限不足')));
        exit();
    }
    if (empty($_REQUEST['key'])) {
        echo (json_encode(array('error' => 1, 'msg' => '参数传入错误')));
        exit();
    }

    $prefix = 'io_get_option';
    $options_backup = get_option($prefix . '_backup');
    if (isset($options_backup[$_REQUEST['key']]['data'])) {
        update_option($prefix, $options_backup[$_REQUEST['key']]['data']);
        echo (json_encode(array('error' => 0, 'reload' => 1, 'msg' => '主题设置已恢复到所选备份[' . $_REQUEST['key'] . ']')));
    } else {
        echo (json_encode(array('error' => 1, 'msg' => '备份恢复失败，未找到对应数据')));
    }
    exit();
}
add_action('wp_ajax_options_backup_restore', 'io_ajax_options_backup_restore');



function io_iocf_reset_to_backup()
{
    io_options_backup('重置全部 自动备份');
}
add_action('iocf_io_get_option_reset_before', 'io_iocf_reset_to_backup');

function io_iocf_reset_section_to_backup()
{
    io_options_backup('重置选区 自动备份');
}
add_action('iocf_io_get_option_reset_section_before', 'io_iocf_reset_section_to_backup');

function io_new_theme_to_backup()
{
    $prefix = 'io_get_option';
    $options_backup = get_option($prefix . '_backup');
    $time = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ($val['type'] == '更新主题 自动备份') {
                $time = $key;
                break;
            }
        }
    }
    if (!$time || (floor((strtotime(current_time("Y-m-d H:i:s")) - strtotime($time)) / 3600) > 240)) {
        io_options_backup('更新主题 自动备份');
        wp_cache_flush();
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
add_action('new_io_theme_admin_notices', 'io_new_theme_to_backup');

function io_iocf_save_section_to_backup()
{
    $prefix = 'io_get_option';
    $options_backup = get_option($prefix . '_backup');
    $time = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ($val['type'] == '定期自动备份') {
                $time = $key;
                break;
            }
        }
    }
    if (!$time || (floor((strtotime(current_time("Y-m-d H:i:s")) - strtotime($time)) / 3600) > 600)) {
        io_options_backup('定期自动备份');
    }
}
add_action('iocf_io_get_option_save_after', 'io_iocf_save_section_to_backup');


//主题更新后发送通知
function io_notice_update()
{
    if (IO_PRO) {
        $version = get_option('onenav_update_version');
        if ($version && version_compare($version, IO_VERSION, '<')) {
            do_action('new_io_theme_admin_notices');
            $con = '<div class="notice notice-success is-dismissible">
    			<h2 style="color:#f1404b;"><i class="fa fa-hand-o-right fa-fw"></i> 恭喜您！OneNav 主题已更新</h2>
                <p>更新主题请记得清空缓存、刷新CDN，再保存一下<a href="' . io_get_admin_iocf_url() . '">主题设置</a>，保存主题设置后此通知会自动关闭。</p>
                <p><a class="button" style="margin: 2px;" href="' . io_get_admin_iocf_url() . '">体验新功能</a><a target="_blank" class="button" style="margin: 2px;" href="https://www.iotheme.cn/store/onenav.html#update-log">查看更新日志</a></p>
    		</div>';
            echo  $con;
        } elseif (!$version) {
            $con = '<div class="notice notice-info is-dismissible">
    			<h2 style="color:#f1404b;"><i class="fa fa-bullhorn fa-fw"></i> 感谢您使用 OneNav 主题</h2>
                <p>首次启动请先完成以下几步：</p>
                <ul>
                <li>1、确保站点“伪静态规则”和“固定链接”设置正确，<a target="_blank" href="https://www.iotheme.cn/wordpressweijingtaihewordpressgudinglianjieshezhi.html">设置方法</a><li>
                <li>2、授权域名并填写激活码到主题设置中，<a target="_blank" href="https://www.iotheme.cn/user?action=reg">前往授权</a><li>
                <li>3、保存<a href="' . io_get_admin_iocf_url() . '">主题设置</a>，保存主题设置后此通知会自动关闭。<li>
                </ul>
    		</div>';
            echo  $con;
        }
    }else{
        $con = '<div class="notice notice-warning">
        <p style="color:#ff2f86"><i class="fa fa-bullhorn fa-fw"></i></p>
        <b style="font-size: 1.2em;color:#ff2f86;">欢迎使用 OneNav 主题</b>
        <p>当前主题还未授权，部分功能无法使用，请在主题设置中进行授权验证</p><p><a class="button button-primary" href="'.esc_url(admin_url('admin.php?page=theme_settings#tab=%e5%bc%80%e5%a7%8b%e4%bd%bf%e7%94%a8')).'">立即授权</a><a style="margin-left: 10px;" class="button" href="https://www.iotheme.cn/zhutishouquanyumingzhuceshuoming.html" target="_blank">授权教程</a></p><p></p>
        </div>';
        echo $con;
    }
}
add_action('admin_notices', 'io_notice_update');

/**
 * 获取主题设置链接
 * @param mixed $tab 选项卡
 * @param mixed $page 页面
 * @return string
 */
function io_get_admin_iocf_url($tab = '', $page = 'theme_settings') {
    $tab_attr = $tab ? esc_attr(implode("/", array_map('sanitize_title', explode("/", $tab)))) : '';
    $url      = add_query_arg('page', $page, admin_url('admin.php'));

    if ($tab_attr) {
        $url .= '#tab=' . $tab_attr;
    }

    return esc_url($url);
}
//保存主题更新主题版本
function io_save_theme_version()
{
    update_option('onenav_update_version', IO_VERSION);
}
add_action("iocf_io_get_option_save_after", 'io_save_theme_version');

/**
 * 获取系统信息
 * @return string
 */
function io_get_system_info(){
    global $wpdb, $wp_object_cache;
    $_theme      = wp_get_theme();
    $_theme_name = sprintf('%1$s (%2$s)', $_theme->name, $_theme->get_stylesheet());
    $sub         = '<li><strong>主题版本</strong>： ' . $_theme_name . ' / ' . $_theme->get('Version') . ' </li>';
    if ($_theme->get('Template')) {
        $sub         = '<li><strong>子主题信息</strong>： ' . $_theme_name . ' / ' . $_theme->get('Version') . ' </li>';
        $_theme      = wp_get_theme($_theme->get('Template'));
        $_theme_name = sprintf('%1$s (%2$s)', $_theme->name, $_theme->get_stylesheet());
        $sub .= '<li><strong>父主题版本</strong>： ' . $_theme_name . ' / ' . $_theme->get('Version') . '</li>';
    }
    $_c = '未启用 cURL';
    if (function_exists('curl_version')) {
        $curl = curl_version();
        $_c   = sprintf('cURL %s %s', $curl['version'], $curl['ssl_version']);
    }
    if (is_resource($wpdb->dbh)) {
        $extension = 'mysql';
    } elseif (is_object($wpdb->dbh)) {
        $extension = get_class($wpdb->dbh);
    } else {
        $extension = '未知';
    }

    $server = $wpdb->get_var( 'SELECT VERSION()' );

    $system  = '<h4><strong>系统信息：</strong><a class="button button-primary" style="vertical-align:inherit" href="' . admin_url('site-health.php?tab=debug') . '">查看更多系统信息</a></h4>';
    $system .= '<ul style="margin-left:10px">';
    $system .= '<li><strong>操作系统</strong>： ' . PHP_OS . ' </li>';
    $system .= '<li><strong>运行环境</strong>： ' . $_SERVER["SERVER_SOFTWARE"] . ' </li>';
    $system .= '<li><strong>PHP版本</strong>： ' . PHP_VERSION . ' | ' . $_c . '</li>';
    $system .= '<li><strong>数据库版本</strong>： ' . $extension . ' ' . $server . ' </li>';
    $system .= '<li><strong>WordPress版本</strong>： ' . get_bloginfo('version') . '</li>';
    $system .= '<li><strong>URL</strong>： ' . home_url() . '</li>';
    $system .= $sub;
    $system .= '<li><strong>系统信息</strong>： ' . php_uname() . ' </li>';
    $system .= '<li><strong>服务器时间</strong>： ' . current_time('mysql') . ' | ' . wp_timezone_string() . '</li>';
    $system .= '</ul>';

    $memcached = '<h4><strong>Memcached</strong>： 未启用</h4>';
    if (method_exists($wp_object_cache, 'get_mc')) {
        $r_cache   = esc_url(wp_nonce_url(admin_url('admin-ajax.php') . '?action=flush_memcached_cache', 'flush-memcached'));
        $d_cache   = esc_url(wp_nonce_url(admin_url('admin-ajax.php') . '?action=db_cache_files&type=delete', 'cache-files'));
        $memcached = '<div class="ajax-form" style="margin-top:20px;">';
        $memcached .= '<h4 style="margin-bottom:5px"><strong>Memcached</strong>： 已启用 ';
        $memcached .= '<a class="button button-primary min-btn ajax-get" style="vertical-align:inherit" href="' . $r_cache . '">刷新缓存</a>';
        $memcached .= '<a class="but c-yellow ml-2 min-btn ajax-get" style="vertical-align:inherit" href="' . $d_cache . '">关闭</a>';
        $memcached .= '</h4>';
        $memcached .= '<div class="ajax-notice"></div>';
        $memcached .= '<ul style="margin:5px 0 15px 10px;background: #ededed;border-radius: 8px;padding: 5px 8px 2px 8px;display: inline-block">';
        $memcached .= io_memcached_usage_data();
        $memcached .= '</ul></div>';
    } else {
        $r_cache   = esc_url(wp_nonce_url(admin_url('admin-ajax.php') . '?action=db_cache_files&type=copy', 'cache-files'));
        $memcached .= '<p style="margin-top:-15px">请在服务器上启用Memcached服务，以提高网站性能。<a href="https://www.iotheme.cn/wei-wordpress-qiyongduixianghuancun.html#memcached" target="_blank">教程</a></p>';
        $memcached .= '<div class="ajax-form" style="margin-top:-15px;margin-bottom:10px"><a class="ajax-get" style="vertical-align:inherit" href="' . $r_cache . '">启用 Memcached</a>';
        $memcached .= '<div class="ajax-notice"></div>';
        $memcached .= '</div>';
    }

    $redis = '<h4><strong>Redis</strong>： 未安装</h4>';
    if ((defined('WP_REDIS_VERSION') && WP_REDIS_VERSION) || (defined('RedisCachePro\Version') && RedisCachePro\Version)) {
        $_url = admin_url('options-general.php?page=redis-cache');
        if (defined('RedisCachePro\Version')) {
            $_url = admin_url('options-general.php?page=objectcache');
        }
        if ((method_exists($wp_object_cache, 'redis_status') && $wp_object_cache->redis_status()) || $wp_object_cache instanceof \RedisCachePro\ObjectCaches\ObjectCacheInterface) {
            $redis = '<h4><strong>Redis</strong>： 已启用</h4>';
            $redis .= '<p style="margin-top:-15px"><a href="' . esc_url($_url) . '" target="_blank">查看详情</a></p>';
        } else {
            $redis = '<h4><strong>Redis</strong>： 未启用</h4>';
            $redis .= '<p style="margin-top:-15px"><a href="' . esc_url($_url) . '" target="_blank">前往启用</a></p>';
        }
    } else {
        $redis .= '<p style="margin-top:-15px">请在服务器上启用Redis服务，以提高网站性能。<a href="https://www.iotheme.cn/wei-wordpress-qiyongduixianghuancun.html#redis" target="_blank">教程</a></p>';
        $redis .= '<p style="margin-top:-15px">安装插件。<a href="' . admin_url('plugin-install.php?s=redis&tab=search&type=term') . '" target="_blank">安装并启用 Redis Object Cache 插件</a></p>';
    }

    $dbcache = '<p style="margin-top:20px;margin-bottom:5px"><b>Memcached</b> 和 <b>Redis</b> 只能二选一</p>';
    $dbcache .= '<div style="margin-left:10px;margin-bottom:20px;">';
    $dbcache .= $memcached;
    $dbcache .= $redis;
    $dbcache .= '</div>';


    $opcache = '<h4><strong>OPcache</strong>： 未启用</h4>';
    if (function_exists('opcache_get_status')) {
        $o_cache = esc_url(wp_nonce_url(admin_url('admin-ajax.php') . '?action=flush_opcache_cache', 'flush-opcache'));
        $opcache = '<div class="ajax-form" style="margin-top:20px;"><h4><strong>OPcache</strong>： 已启用 <a class="button button-primary ajax-get" style="vertical-align:inherit" href="' . $o_cache . '">刷新缓存</a></h4></h4>';
        $opcache .= '<div class="ajax-notice"></div>';
        $opcache .= '<ul style="margin-left:10px">';
        $opcache .= io_opcache_usage_data();
        $opcache .= '</ul></div>';
    } else {
        $opcache .= '<p style="margin-top:-15px">请在服务器上启用OPcache服务，以提高网站性能。<a href="https://www.iotheme.cn/kaiqi-php-jiaobenhuancun-opcache.html" target="_blank">教程</a></p>';
    }

    return $system . $dbcache . $opcache;
}


function io_memcached_usage_data(){
    $items = IOTOOLS::getMemcachedData();
    $html = '<li><strong>命中次数</strong>： ' . $items['get_hits'] . ' / ' . number_format($items['get_hits'] / $items['cmd_get'] * 100, 2) . '%</li>';
    $html .= '<li><strong>已用内存</strong>： ' . round($items['bytes'] / (1024 * 1024), 2) . ' / ' . number_format(($items['bytes'] / (1024 * 1024)) / ($items['limit_maxbytes'] / (1024 * 1024)) * 100, 2) . '%</li>';
    $html .= '<li><strong>每秒命中次数</strong>： ' . round($items['get_hits'] / $items['uptime'], 2) . '</li>';
    $html .= '<li><strong>每秒获取请求次数</strong>： ' . round($items['cmd_get'] / $items['uptime'], 2) . '</li>';
    return $html;
}

function io_opcache_usage_data(){
    $status = IOTOOLS::getOpcacheData();
    if (empty($status) || !is_array($status) || !isset($status['memory_usage']) || !isset($status['opcache_statistics']))
        return '<li>OPcache数据获取失败</li>';

    $memory_usage = round($status['memory_usage']['used_memory'] / (1024 * 1024), 2);
    $memory_total = ($status['memory_usage']['used_memory'] + $status['memory_usage']['free_memory'] + $status['memory_usage']['wasted_memory']) / (1024 * 1024);
    $memory_total = $memory_total ?: 1;

    $memory_hits  = $status['opcache_statistics']['hits'];
    $hits_total   = $status['opcache_statistics']['hits'] + $status['opcache_statistics']['misses'];
    $hits_total   = $hits_total ?: 1;

    $cached_keys  = $status['opcache_statistics']['num_cached_keys'];
    $keys_total   = $status['opcache_statistics']['max_cached_keys'];
    $keys_total   = $keys_total ?: 1;

    $html = '<li><strong>已用内存</strong>： ' . $memory_usage . ' / ' . number_format($memory_usage / $memory_total * 100, 2) . '%</li>';
    $html .= '<li><strong>命中</strong>： ' . $memory_hits . ' / ' . number_format($memory_hits / $hits_total * 100, 2) . '%</li>';
    $html .= '<li><strong>已用Keys</strong>： ' . $cached_keys . ' / ' . number_format($cached_keys / $keys_total * 100, 2) . '%</li>';
    return $html;
}

/**
 * 清空Memcached缓存
 * @return never
 */
function io_ajax_flush_memcached_cache(){
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'msg' => '操作权限不足')));
        exit();
    }
    if (check_admin_referer('flush-memcached')) {
        wp_cache_flush();
        echo (json_encode(array('error' => 0, 'msg' => '缓存已刷新', 'reload' => 1)));
    } else {
        echo (json_encode(array('error' => 1, 'msg' => '系统错误，请刷新重试！')));
    }
    exit();
}
add_action('wp_ajax_flush_memcached_cache', 'io_ajax_flush_memcached_cache');

/**
 * Memcached object-cache 文件操作
 * 
 * @global WP_Filesystem_Base $wp_filesystem
 * @return void
 */
function io_ajax_db_cache_files()
{
    global $wp_filesystem;
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'msg' => '操作权限不足')));
        exit();
    }

    if (!check_admin_referer('cache-files')) {
        echo (json_encode(array('error' => 1, 'msg' => '系统错误，请刷新重试！')));
    }

    // 初始化 WP_Filesystem
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (!WP_Filesystem()) {
        echo json_encode(array('error' => 1, 'msg' => '无法初始化文件系统'));
        exit();
    }

    $type = sanitize_key($_GET['type']);

    if ($type === 'copy') {
        $result = $wp_filesystem->copy(
            get_theme_file_path('/templates/object-cache.php'),
            WP_CONTENT_DIR . '/object-cache.php',
            true,
            FS_CHMOD_FILE
        );
        if ($result) {
            echo (json_encode(array('error' => 0, 'msg' => '启动成功！', 'reload' => 1)));
        } else {
            echo (json_encode(array('error' => 1, 'msg' => '启动失败，请手动复制主题内“/templates/object-cache.php”文件到“wp-content”目录下！')));
        }
        exit();
    }
    if ($type === 'delete') {
        $result = $wp_filesystem->delete(WP_CONTENT_DIR . '/object-cache.php');
        if ($result) {
            echo (json_encode(array('error' => 0, 'msg' => '关闭成功！', 'reload' => 1)));
        } else {
            echo (json_encode(array('error' => 1, 'msg' => '关闭失败，请手动删除“wp-content”目录下的“object-cache.php”文件！')));
        }
        exit();
    }
}
add_action('wp_ajax_db_cache_files', 'io_ajax_db_cache_files');

/**
 * 清空OPcache缓存
 * @return never
 */
function io_ajax_flush_opcache_cache(){
    if (!is_super_admin()) {
        echo (json_encode(array('error' => 1, 'msg' => '操作权限不足')));
        exit();
    }
    if (check_admin_referer('flush-opcache')) {
        opcache_reset();
        echo (json_encode(array('error' => 0, 'msg' => '缓存已刷新', 'reload' => 1)));
    } else {
        echo (json_encode(array('error' => 1, 'msg' => '系统错误，请刷新重试！')));
    }
    exit();
}
add_action('wp_ajax_flush_opcache_cache', 'io_ajax_flush_opcache_cache');

/**
 * 获取主题设置对应类型的卡片样式
 * @param mixed $type
 * @param mixed $loc
 * @return string[]
 */
function io_get_posts_card_style($type = 'post', $loc = 'option'){
    $styles = array(
        'post'  => array(
            'min'   => get_theme_file_uri('/assets/images/option/op-app-c-card.png'),
            'card'  => get_theme_file_uri('/assets/images/option/op-post-c-def.png'),
            'card2' => get_theme_file_uri('/assets/images/option/op-post-c-card2.png'),
        ),
        'sites' => array(
            'max'     => get_theme_file_uri('/assets/images/option/op-site-c-max.png'),
            'default' => get_theme_file_uri('/assets/images/option/op-site-c-def.png'),
            'min'     => get_theme_file_uri('/assets/images/option/op-site-c-min.png'),
            'big'     => get_theme_file_uri('/assets/images/option/op-site-c-big.png'),
        ),
        'app'   => array(
            'card'    => get_theme_file_uri('/assets/images/option/op-app-c-card.png'),
            'default' => get_theme_file_uri('/assets/images/option/op-app-c-def.png'),
            'max'     => get_theme_file_uri('/assets/images/option/op-app-c-max.png'),
        ),
        'book'  => array(
            'h'  => get_theme_file_uri('/assets/images/option/op-book-c-h.png'),
            'v'  => get_theme_file_uri('/assets/images/option/op-book-c-v.png'),
            'h1' => get_theme_file_uri('/assets/images/option/op-book-c-h1.png'),
            'v1' => get_theme_file_uri('/assets/images/option/op-book-c-v1.png'),
        ),
    );

    $style = $styles[$type];
    if ($loc !== 'option') {
        //在开头加一个默认选项
        $style = array('none' => get_theme_file_uri('/assets/images/option/op-null.png')) + $style;
    }
    return $style;
}
