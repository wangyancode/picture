<?php 
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:55:58
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-29 23:48:29
 * @FilePath: /onenav/go.php
 * @Description: 
 */
if(strlen($_SERVER['REQUEST_URI']) > 384 || strpos($_SERVER['REQUEST_URI'], "eval(") || strpos($_SERVER['REQUEST_URI'], "base64")) {
    header("HTTP/1.1 414 Request-URI Too Long");
    header("Status: 414 Request-URI Too Long");
    header("Connection: Close");
    exit;
}
$is_safe = true;
// -------------通过[referer]禁止站外访问跳转地址----------------
if(io_get_option("is_must_on_site",true)){
    if(!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_HOST'] != parse_url($_SERVER['HTTP_REFERER'])["host"]) { 
        $is_safe = false;
        //header("location: //{$_SERVER['HTTP_HOST']}"); 
        //exit; 
    }
}
// -----------通过[referer]禁止站外访问跳转地址 END--------------
if($is_safe){
    $is_home = false;
    if (isset($_GET['url']) && !empty($_GET['url'])) {
        $url = urldecode($_GET['url']);
        $title = __('加载中', 'i_theme');
        if ($url == base64_encode(base64_decode($url))) {
            $b =  base64_decode($url);
        } else {
            $b = $url;
        }
    } else {
        $title = __('参数缺失，正在返回首页...', 'i_theme');
        $b = '//'.$_SERVER['HTTP_HOST'];
        $is_home = true;
    }
    $ref_url = get_ref_url(io_get_option('ref_id', array(array('key'=>'ref', 'value'=>''))), htmlspecialchars_decode($b), $is_home);
}else{
    $title = __('危险...', 'i_theme');
}
$tip = io_get_option('go_tip', array('switch'=>true,'time'=> 0));


?>
<!DOCTYPE html>
<html <?php language_attributes() ?> <?php io_html_class() ?>>
<head>
<?php io_auto_theme_mode() ?>
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width,height=device-height, initial-scale=1.0, user-scalable=no" />
<meta name="apple-mobile-web-app-capable" content="yes"> 
<meta name="robots" content="noindex,follow">
<title><?php bloginfo('name') ?>-<?php _e('安全中心','i_theme') ?> | <?php echo $title ?></title>
<link rel="shortcut icon" href="<?php echo io_get_option('favicon','') ?>">
<?php if($is_safe&&(!$tip['switch'] || $is_home)): ?>
<meta http-equiv="refresh" content="1;url=<?php echo $ref_url; ?>">
<?php endif; ?>
<style>
body{margin:0;padding:0}body{height:100%}#loading{-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-box-align:center;-ms-flex-align:center;align-items:center;display:-webkit-box;display:-ms-flexbox;display:flex;position:fixed;top:0;left:0;width:100%;height:100%;background:#e8eaec}.io-black-mode #loading{background:#1b1d1f}
.loading-content{position:relative;max-width:1200px;margin:auto;margin-top:50px;padding:0 12px;box-sizing:border-box;z-index:10000000}.flex{display:flex}.flex-center{align-items:center}.flex-end{display:flex;justify-content:flex-end}.flex-fill{-ms-flex:1 1 auto !important;flex:1 1 auto !important}.logo-img{text-align:center}.logo-img img{width:200px;height:auto;margin-bottom:20px}.loading-info{padding:20px;background:#fff;border-radius:10px;box-shadow:0 15px 20px rgba(18,19,20,.2)}.loading-tip{background:rgba(255,158,77,.1);border-radius:6px;padding:5px}.loading-text{color:#b22e12;font-weight:bold}.loading-topic{padding:20px 0;border-bottom:1px solid rgba(136,136,136,.2);margin-bottom:20px;font-size:12px;word-break:break-all}a{text-decoration:none}.loading-btn,.loading-btn:active,.loading-btn:visited{color:#fc5531;border-radius:5px;border:1px solid #fc5531;padding:5px 20px;transition:.3s}.loading-btn:hover{color:#fff;background:#fc5531;box-shadow:0 15px 15px -10px rgba(184,56,25,0.8)}.loading-url{color:#fc5531}.taxt-auto{color:#787a7d;font-size:14px}.auto-second{color:#fc5531;font-size:16px;margin-right:5px;font-weight:bold}
.safe-tip{max-width:580px;margin:10% auto 50px auto;}
.warning-ico{width:30px;height:26px;margin-right:5px;background-image:url("data:image/svg+xml,%3Csvg class='icon' viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M872.7 582.6L635.2 177c-53.5-91.3-186.6-88.1-235.6 5.7L187.7 588.3c-46.8 89.7 18.2 197 119.4 197h449.4c104 0 168.8-112.9 116.2-202.7zM496.6 295.2c0-20.5 11.7-31.5 35.1-32.9 22 1.5 33.7 12.5 35.1 32.9V315l-26.4 267.9h-13.2L496.6 315v-19.8zm35.2 406.3c-22-1.5-34.4-13.2-37.3-35.1 1.4-19 13.2-29.3 35.1-30.7 23.4 1.5 36.6 11.7 39.5 30.7-1.5 21.9-13.9 33.6-37.3 35.1z' fill='%23f55d49'/%3E%3C/svg%3E")}
.io-black-mode .loading-info{color:#eee;background:#2b2d2f}.io-black-mode .loading-text{color:#ff8369}
.container img{width:100%;height:auto;}
@media (min-width:768px){.loading-content{min-width:450px}}
</style>
</head>
<body class="go-to">
<div id="loading">
    <?php
    $_tip  = '';
    $fx_id = 0;
    if(!$is_safe || ($tip['switch'] && !$is_home)){
        $blog_name = get_bloginfo('name'); 
        $fx_id = 5;
        if($is_safe){
            $warning = __('请注意您的账号和财产安全','i_theme');
            $ref_tip = sprintf( __('您即将离开%s，去往：%s', 'i_theme'), $blog_name,'<span class="loading-url">'.$b.'</span>' );
        }else{
            $warning = __('目标网址未通过安全检查','i_theme');
            $ref_tip = __('已中止跳转，即将返回首页！', 'i_theme');
            $tip['time'] = 5;
            $ref_url = '//'.$_SERVER['HTTP_HOST'];
        }
        ob_start();
    ?>
    <div class="safe-tip">
        <div class="logo-img">
            <img id="img_logo" src="<?php echo io_get_option('logo_normal_light','') ?>" alt="<?php echo $blog_name ?>">
        </div>
        <div class="loading-info">                        
            <div class="flex flex-center loading-tip">                          
                <div class="warning-ico"></div><div class="loading-text"><?php echo $warning ?></div>                        
            </div>                        
            <div class="loading-topic">
                <?php echo $ref_tip ?>                       
            </div>                        
            <div class="flex flex-center"> 
                <?php if( $tip['time']!=0 ): ?>
                <div class="taxt-auto"><?php echo sprintf( __('%s秒后自动跳转', 'i_theme'),'<span id="time" class="auto-second">'.$tip['time'].'</span>' ) ?></div> 
                <script type="text/javascript">  
                    delayURL();    
                    function delayURL() { 
                        var delay = document.getElementById("time").innerHTML;
                        var t = setTimeout("delayURL()", 1000);
                        if (delay > 0) {
                            delay--;
                            document.getElementById("time").innerHTML = delay;
                        } else {
                        clearTimeout(t); 
                            window.location.href = "<?php echo $ref_url ?>";
                        }        
                    } 
                </script>
                <?php endif; ?>   
                <div class="flex-fill"></div>                     
                <a class="loading-btn" href="<?php echo $ref_url ?>" rel="external nofollow"><?php _e('继续','i_theme') ?></a>                        
            </div>                      
        </div>
    </div>
    <?php 
        $_tip = ob_get_contents();
        ob_end_clean();
    }
    ?>
    <?php loading_type($fx_id) ?>
</div>
<div class="loading-content">
<?php echo $_tip ?>
<?php do_action('io_go_page_content_ad') ?>
</div>
<script>
    if(document.documentElement.classList.contains('io-black-mode')){
        document.getElementById('img_logo').src='<?php echo io_get_option('logo_normal','') ?>';
    }
    //延时30S关闭跳转页面，用于文件下载后不会关闭跳转页的问题
    setTimeout(function() {
        window.opener = null;
        window.close();
    }, 30000);
</script>
</body>
</html>