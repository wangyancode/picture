<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:30:42
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-27 00:30:48
 * @FilePath: /onenav/inc/configs/theme/other-email.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'       => '邮箱发信',
    'icon'        => 'fa fa-envelope',
    'description' => '',
    'fields'      => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '<h4>邮件发信服务设置</h4><p>如果你不需要评论邮件通知等功能，可不设置。<br>国内一般使用 SMTP 服务</p>
            <p><i class="fa fa-fw fa-info-circle fa-fw"></i> 注：如果要关闭或者使用<b>第三方插件</b>
            请选择“关闭”，不能和其他SMTP插件一起开启！同时请注意开启服务器对应的端口！</p>
            <a href="' . io_get_admin_iocf_url('用户安全/用户注册') . '">登录/注册功能设置</a>',
        ),
        array(
            'id'      => 'i_default_mailer',
            'type'    => 'radio',
            'title'   => 'SMTP服务',
            'default' => 'php',
            'inline'  => true,
            'options' => array(
                'php'  => '关闭',
                'smtp' => 'SMTP'
            ),
            'after'   => '使用 “SMTP” 或 “关闭”用第三方插件 作为默认邮件发送方式', 
        ),
        array(
            'id'         => 'i_smtp_host',
            'type'       => 'text',
            'title'      => 'SMTP 主机', 
            'after'      => '您的 SMTP 服务主机', 
            'class'      => 'compact',
            'dependency' => array('i_default_mailer', '==', 'smtp')
        ),
        array(
            'id'         => 'i_smtp_port',
            'type'       => 'text',
            'title'      => 'SMTP 端口', 
            'after'      => '您的 SMTP 服务端口', 
            'default'    => 465,
            'class'      => 'compact',
            'dependency' => array('i_default_mailer', '==', 'smtp')
        ),
        array(
            'id'         => 'i_smtp_secure',
            'type'       => 'radio',
            'title'      => 'SMTP 安全', 
            'after'      => '您的 SMTP 服务器安全协议', 
            'default'    => 'ssl',
            'inline'     => true,
            'options'    => array(
                'auto' => 'Auto',
                'ssl'  => 'SSL',
                'tls'  => 'TLS',
                'none' => 'None'
            ),
            'class'      => 'compact',
            'dependency' => array('i_default_mailer', '==', 'smtp')
        ),
        array(
            'id'         => 'i_smtp_username',
            'type'       => 'text',
            'title'      => 'SMTP 用户名', 
            'after'      => '您的 SMTP 用户名', 
            'class'      => 'compact',
            'dependency' => array('i_default_mailer', '==', 'smtp')
        ),
        array(
            'id'         => 'i_smtp_password',
            'type'       => 'text',
            'title'      => 'SMTP 密码', 
            'after'      => '您的 SMTP 密码', 
            'class'      => 'compact',
            'dependency' => array('i_default_mailer', '==', 'smtp')
        ),
        array(
            'id'         => 'i_smtp_name',
            'type'       => 'text',
            'title'      => '你的姓名', 
            'default'    => get_bloginfo('name'),
            'after'      => '你发送的邮件中显示的名称', 
            'class'      => 'compact',
            'dependency' => array('i_default_mailer', '==', 'smtp'),
        ),
        array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h4>邮件发送测试</h4>
            <p>输入接收邮件的邮箱号码，发送测试邮件</p>
            <ajaxform class="ajax-form" ajax-url="' . admin_url('admin-ajax.php') . '">
            <p><input type="text" class="not-change" ajax-name="email"></p>
            <div class="ajax-notice"></div>
            <p><a href="javascript:;" class="button button-primary ajax-submit"><i class="fa fa-paper-plane-o"></i> 发送测试邮件</a></p>
            <input type="hidden" ajax-name="action" value="test_mail">
            </ajaxform>',
        ),
    ),
);