<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:40:49
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 18:16:25
 * @FilePath: /onenav/inc/configs/theme/user-reg.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$default_uc_key = IOTOOLS::getKm(6);

return array(
    'title'  => '用户注册',
    'icon'   => 'fa fa-user-plus',
    'fields' => array(
        array(
            'type'       => 'submessage',
            'style'      => 'danger',
            'content'    => '<p style="margin:22px 0">' . $tip_ico . '您已关掉用户中心，<b>“用户&安全”</b> 选项卡内设置基本都无效。</p>',
            'dependency' => array('user_center', '==', 'false'),
        ),
        array(
            'id'      => 'user_center',
            'type'    => 'switcher',
            'title'   => '启用用户中心', 
            'label'   => '同时启用个性化登录页', 
            'desc'    => '<p style="color: #e31313;"><i class="fa fa-fw fa-info-circle fa-fw"></i> 启用和禁用<b>[用户中心]</b>后必须<b>重新保存<a href="' . admin_url('options-permalink.php') . '">固定链接</a></b></p>'
                . (get_option('users_can_register') ? '' : '您的站点已禁止注册，请前往开启“<a href="' . admin_url('options-general.php') . '">任何人都可以注册</a>”'),
            'default' => false,
        ),
        array(
            'id'         => 'uc_key',
            'type'       => 'text',
            'title'      => '安全码', 
            'default'    => $default_uc_key,
            'after'      => $tip_ico . '如果登陆页无法访问，可以通过 '.home_url('wp-login.php?uc_key=' . io_get_option('uc_key', $default_uc_key)) . ' 进入默认登陆页',
            'class'      => 'compact min',
            'dependency' => array('user_center', '==', 'true'),
        ),
        array(
            'id'         => 'modify_default_style',
            'type'       => 'switcher',
            'title'      => '美化默认登录页', 
            'default'    => true,
            'class'      => 'compact min',
            'dependency' => array('user_center', '==', 'false'),
        ),
        array(
            'content' => '<h4>本页某些设置依赖一下功能：</h4><ol>
            <li><a href="' . io_get_admin_iocf_url('用户安全/社交登录') . '">社交登录</a></li>
            <li><a href="' . io_get_admin_iocf_url('其他功能/邮箱发信') . '">邮件发信设置</a></li>
            <li><a href="' . io_get_admin_iocf_url('其他功能/短信接口') . '">短信接口设置</a></li>
            </ol><p>' . $tip_ico . '开启前请检查相关设置是否配置正确。</p>',
            'style'   => 'info',
            'type'    => 'submessage',
        ),
        array(
            'type'       => 'submessage',
            'style'      => 'danger',
            'content'    => '<p style="text-align: center">' . $tip_ico . '【注册验证项】至少选一项</p>',
            'dependency' => array('reg_verification|reg_type', '==|==', 'true|'),
        ),
        array(
            'id'      => 'reg_verification',
            'type'    => 'switcher',
            'title'   => '注册时验证', 
            'label'   => '发送验证码，请先在“其他功能”中配置好发信服务。', 
            'default' => false
        ),
        array(
            'id'         => 'reg_type',
            'type'       => 'checkbox',
            'title'      => '注册验证项', 
            'inline'     => true,
            'options'    => array(
                'email' => '邮箱', 
                'phone' => '手机', 
            ),
            'class'      => 'compact min',
            'default'    => array('email'),
            'after'      => $tip_ico . '如果都勾选，注册时可任选一项验证。【至少选一项】',
            'dependency' => array('reg_verification', '==', 'true', '', 'visible'),
        ),
        array(
            'type'    => 'submessage',
            'style'   => 'info',
            'content' => '<h4>社交登录后是否提示绑定邮箱/手机。</h4><ol><li>不绑定：就是不绑定。</li><li>提醒绑定：提示绑定，并跳转到绑定页，可跳过。</li><li>强制绑定：用户第一次使用社交登录后并未完成注册，需添加邮箱/手机、密码等操作后才能真正完成注册，同时也可以绑定现有账号（比如用户以前用邮箱注册了账号，就可以通过登录以前的账号自动关联社交账户）。</li></ol>
                            <p>' . $tip_ico . '如果选择“强制绑定”，用户没有完成绑定前不会插入用户表，同时实现绑定已有账号。</p>
                            <p>' . $tip_ico . '此功能需<a href="' . io_get_admin_iocf_url('其他功能/邮箱发信') . '">邮件发信设置</a>和<a href="' . io_get_admin_iocf_url('其他功能/短信接口') . '">短信接口设置</a>，请提前配置好相关设置。</p>',
        ),
        array(
            'type'       => 'submessage',
            'style'      => 'danger',
            'content'    => '<p style="text-align: center">' . $tip_ico . '【绑定项】至少选一项</p>',
            'dependency' => array('bind_email|bind_type', 'any|==', 'bind,must|'),
        ),
        array(
            'id'      => 'bind_email',
            'type'    => 'button_set',
            'title'   => '绑定设置',
            'options' => array(
                'null' => '不绑定', 
                'bind' => '提醒绑定', 
                'must' => '强制绑定', 
            ),
            'default' => 'null',
        ),
        array(
            'id'         => 'bind_type',
            'type'       => 'checkbox',
            'title'      => '绑定项', 
            'inline'     => true,
            'options'    => array(
                'email' => '邮箱', 
                'phone' => '手机', 
            ),
            'class'      => 'compact min',
            'default'    => array('email'),
            'after'      => $tip_ico . '如果都勾选，则必须都绑定。【至少选一项】',
            'dependency' => array('bind_email', 'any', 'bind,must', '', 'visible'),
        ),
        array(
            'id'         => 'remind_bind',
            'type'       => 'switcher',
            'title'      => '提醒绑定', 
            'label'      => '未绑定的用户，每次登录都提醒绑定', 
            'default'    => false,
            'class'      => 'compact min',
            'dependency' => array('bind_email', 'any', 'bind,must', '', 'visible'),
        ),
        array(
            'id'         => 'remind_only',
            'type'       => 'switcher',
            'title'      => '绑定提醒1次', 
            'label'      => '一天只提醒一次（同一个会话周期）', 
            'default'    => true,
            'class'      => 'compact min',
            'dependency' => array('remind_bind|bind_email', '==|any', 'true|bind,must', '', 'visible'),
        ),
        array(
            'type'    => 'switcher',
            'id'      => 'bind_phone',
            'title'   => '手机绑定',
            'label'   => '用户中心显示绑定、修改手机号功能',
            'default' => false,
        ),
        array(
            'id'      => 'user_login_phone',
            'type'    => 'switcher',
            'title'   => '手机号登录',
            'label'   => '允许使用手机号作为用户名登录',
            'default' => false,
        ),
        array(
            'id'       => 'lost_verify_type',
            'type'     => "checkbox",
            'title'    => '找回密码',
            'subtitle' => '找回密码验证方式',
            'inline'   => true,
            'options'  => array(
                'email' => '邮箱',
                'phone' => '手机',
            ),
            'default'  => "email",
            'after'    => $tip_ico . '如果都勾选，找回密码时可任选一项验证。【至少选一项】',
        ),
        array(
            'type'       => 'submessage',
            'style'      => 'danger',
            'content'    => '<p style="text-align: center">' . $tip_ico . '【找回密码】至少选一项</p>',
            'dependency' => array('lost_verify_type', '==', ''),
        ),
        array(
            'id'      => 'user_agreement',
            'type'    => 'fieldset',
            'title'   => '用户协议',
            'fields'  => array(
                array(
                    'id'    => 'switch',
                    'type'  => 'switcher',
                    'title' => '开关', 
                ),
                array(
                    'id'         => 'pact_page',
                    'type'       => 'select',
                    'title'      => '用户协议页面',
                    'options'    => 'pages',
                    'query_args' => array(
                        'posts_per_page' => -1,
                    ),
                    'desc'       => '新建页面，写入用户协议，然后选择该页面',
                    'class'      => 'compact min',
                    'dependency' => array('switch', '==', true)
                ),
                array(
                    'id'         => 'privacy_page',
                    'type'       => 'select',
                    'title'      => '隐私协议页面',
                    'options'    => 'pages',
                    'query_args' => array(
                        'posts_per_page' => -1,
                    ),
                    'desc'       => '新建页面，写入隐私协议，然后选择该页面',
                    'class'      => 'compact min',
                    'dependency' => array('switch', '==', true)
                ),
                array(
                    'id'         => 'default',
                    'type'       => 'switcher',
                    'title'      => '默认勾选',
                    'dependency' => array('switch', '==', true)
                ),
            ),
            'default' => array(
                'switch'       => false,
                'pact_page'    => '',
                'privacy_page' => '',
                'default'      => false,
            ),
        ),
        array(
            'id'       => 'user_nickname_stint',
            'type'     => 'textarea',
            'title'    => '用户昵称限制', 
            'subtitle' => '禁止的昵称关键词', 
            'desc'     => '前台注册或修改昵称时，不能使用包含这些关键字的昵称(请用逗号或换行分割)', 
            'default'  => "赌博,博彩,彩票,性爱,色情,做爱,爱爱,淫秽,傻b,妈的,妈b,admin,test",
            'sanitize' => false,
        ),
        array(
            'id'      => 'nickname_exists',
            'type'    => 'switcher',
            'title'   => '昵称唯一',
            'label'   => '禁止昵称重复，不允许修改为已存在的昵称',
            'default' => true,
        ),
    )
);