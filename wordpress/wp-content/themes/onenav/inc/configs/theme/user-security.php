<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:48:05
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-27 00:48:11
 * @FilePath: /onenav/inc/configs/theme/user-security.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '安全相关',
    'icon'   => 'fa fa-shield',
    'fields' => array(
        array(
            'id'      => 'io_administrator',
            'type'    => 'fieldset',
            'title'   => '禁止冒充管理员留言',
            'fields'  => array(
                array(
                    'id'    => 'admin_name',
                    'type'  => 'text',
                    'title' => '管理员名称', 
                ),
                array(
                    'id'    => 'admin_email',
                    'type'  => 'text',
                    'title' => '管理员邮箱', 
                    'class' => 'compact min',
                ),
            ),
            'default' => array(
                'admin_email' => get_option('admin_email'),
            ),
        ),
        array(
            'id'      => 'io_comment_set',
            'type'    => 'fieldset',
            'title'   => '评论过滤',
            'fields'  => array(
                array(
                    'id'    => 'no_url',
                    'type'  => 'switcher',
                    'title' => '评论禁止链接', 
                ),
                array(
                    'id'    => 'no_chinese',
                    'type'  => 'switcher',
                    'title' => '评论必须包含汉字', 
                    'class' => 'compact min',
                ),
            ),
            'default' => array(
                'no_url'     => true,
                'no_chinese' => false,
            ),
        ),
        array(
            'id'      => 'bookmark_share',
            'type'    => 'switcher',
            'title'   => '禁用“用户个人书签页”分享', 
            'label'   => '全局开关，避免非法地址影响域名安全', 
            'default' => true,
        ),
        array(
            'id'      => 'captcha_type',
            'type'    => "select",
            'title'   => '人机验证类型',
            'options' => array(
                'null'     => '关闭',
                'image'    => '图片验证码',
                'slider'   => '滑块验证码',
                'tcaptcha' => '腾讯图形验证码',
                'geetest'  => '极验行为验4.0',
                'vaptcha'  => 'VAPTCHA',
            ),
            'default' => 'null',
            'after'   => $tip_ico . '注意：切换后请刷新静态缓存、cdn缓存等各种缓存',
        ),
        array(
            'id'         => 'tcaptcha_option',
            'type'       => 'fieldset',
            'title'      => '腾讯图形验证码',
            'class'      => 'compact',
            'fields'     => array(
                array(
                    'style'   => 'info',
                    'type'    => 'submessage',
                    'content' => '<span style="color:#f00">开启后，请认真填写，填错会造成无法登录后台</span><br>
                    申请地址：<a target="_blank" href="https://console.cloud.tencent.com/captcha/graphical">腾讯图形验证码【腾讯防水墙】</a>',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => '验证码CaptchaAppId',
                ),
                array(
                    'id'    => 'secret_key',
                    'type'  => 'text',
                    'title' => '验证码AppSecretKey',
                    'after' => '请填写完整，包括后面的**', 
                    'class' => 'compact min',
                ),
                //https://console.cloud.tencent.com/cam/capi
                array(
                    'id'    => 'api_secret_id',
                    'type'  => 'text',
                    'title' => 'API密钥SecretId',
                    'after' => $tip_ico . '注意：以前的【腾讯防水墙】版本请留空',
                ),
                array(
                    'id'    => 'api_secret_key',
                    'type'  => 'text',
                    'title' => 'API密钥SecretKey',
                    'class' => 'compact min',
                    'after' => $tip_ico . '注意：以前的【腾讯防水墙】版本请留空',
                ),

                array(
                    'type'    => 'submessage',
                    'style'   => 'danger',
                    'content' => '如果开启人机验证后进不了后台，请将主题文件‘functions.php’里“LOGIN_007”的 true 改为 false 。',
                ),
            ),
            'dependency' => array('captcha_type', '==', 'tcaptcha'),
        ),
        array(
            'id'         => 'geetest_option',
            'type'       => 'fieldset',
            'title'      => '极验行为参数 ',
            'class'      => 'compact',
            'fields'     => array(
                array(
                    'style'   => 'info',
                    'type'    => 'submessage',
                    'content' => '<span style="color:#f00">开启后，请认真填写，填错会造成无法登录后台</span><br>
                    申请地址：<a target="_blank" href="https://www.geetest.com">极验行为验官网</a>',
                ),
                array(
                    'id'    => 'id',
                    'type'  => 'text',
                    'title' => '验证 Id',
                ),
                array(
                    'id'    => 'key',
                    'type'  => 'text',
                    'title' => '验证 Key',
                    'class' => 'compact min',
                ),
                array(
                    'style'   => 'danger',
                    'type'    => 'submessage',
                    'content' => '如果开启人机验证后进不了后台，请将主题文件‘functions.php’里“LOGIN_007”的 true 改为 false 。',
                ),
            ),
            'dependency' => array('captcha_type', '==', 'geetest'),
        ),
        array(
            'id'         => 'vaptcha_option',
            'type'       => 'fieldset',
            'title'      => 'VAPTCHA参数',
            'class'      => 'compact',
            'fields'     => array(
                array(
                    'style'   => 'info',
                    'type'    => 'submessage',
                    'content' => '<span style="color:#f00">开启后，请认真填写，填错会造成无法登录后台</span><br>
                    申请地址：<a target="_blank" href="https://www.vaptcha.com/">VAPTCHA验证</a>',
                ),
                array(
                    'id'    => 'id',
                    'type'  => 'text',
                    'title' => 'VID',
                ),
                array(
                    'id'    => 'key',
                    'type'  => 'text',
                    'title' => 'KEY',
                    'class' => 'compact min',
                ),
                array(
                    'style'   => 'danger',
                    'type'    => 'submessage',
                    'content' => '如果开启人机验证后进不了后台，请将主题文件‘functions.php’里“LOGIN_007”的 true 改为 false 。',
                ),
            ),
            'dependency' => array('captcha_type', '==', 'vaptcha'),
        ),
        array(
            'id'       => 'login_limit',
            'type'     => 'spinner',
            'title'    => '登录失败尝试限制',
            'subtitle' => '尝试次数',
            'after'    => '默认5次，表示失败5次后要过10分钟才能再次尝试登录。<br>' . $tip_ico . '0为不限制次数',
            'unit'     => '次',
            'default'  => 5,
            'class'    => '',
        ),
        array(
            'id'      => 'login_limit_time',
            'type'    => 'spinner',
            'title'   => '登录失败重试频率',
            'after'   => '默认10分钟，表示失败5次后要过10分钟才能再次尝试登录。',
            'unit'    => '分钟',
            'default' => 10,
            'class'   => 'compact min',
        ),
    )
);