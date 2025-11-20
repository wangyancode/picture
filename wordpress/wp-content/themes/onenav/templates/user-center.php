<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-31 15:38:30
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 03:36:30
 * @FilePath: /onenav/templates/user-center.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// 添加webuploader.html5only.js
wp_enqueue_script('webuploader');
wp_enqueue_script('user-js', get_theme_file_uri('/assets/js/user.js'), array('jquery', 'webuploader'), IO_VERSION, true);

get_header();

?>
<main role="main" class="container my-2">
    <?php
    io_uc_header();
    io_uc_content();
    ?> 
</main>
<?php

io_add_captcha_js_html();

add_action('io_js_var', function($var){
    $var['ucVar'] = array(
        'server' => get_theme_file_uri('/upload.php'),
        'local' => array(
            'saving' => __('保存中...','i_theme'),
            'saveProfile' => __('保存资料','i_theme'),
            'avatarMax' => __('头像大小不能超过 512KB！','i_theme'),
            'avatarFailed' => __('头像设置失败！','i_theme'),
            'coverMax' => __('封面大小不能超过 1MB！','i_theme'),
            'coverFailed' => __('封面设置失败！','i_theme'),
            'fileType' => __('文件类型不支持！','i_theme'),
            'uploadFailed' => __('上传失败，请重试！','i_theme'),
        ),
    );
    return $var;
});

get_footer();
