<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-26 23:34:47
 * @LastEditors: iowen
 * @LastEditTime: 2024-01-24 23:39:48
 * @FilePath: \onenav\inc\mailfunc\option.php
 * @Description: 
 */

/**
 * 邮件smtp配置
 * 
 * @param mixed $phpmailer
 * @return void
 */
function io_mail_smtp($phpmailer){
    $mailer = io_get_option('i_default_mailer','smtp');
    if($mailer === 'smtp'){
        //$phpmailer->Mailer = 'smtp';
        $phpmailer->Host        = io_get_option('i_smtp_host','');
        $phpmailer->SMTPAuth    = true; // 强制它使用用户名和密码进行身份验证
        $phpmailer->Port        = io_get_option('i_smtp_port','');
        $phpmailer->Username    = io_get_option('i_smtp_username','');
        $phpmailer->Password    = io_get_option('i_smtp_password','');

        // Additional settings…
        $phpmailer->SMTPSecure  = io_get_option('i_smtp_secure','');
        $phpmailer->FromName    = io_get_option('i_smtp_name','');
        $phpmailer->From        = $phpmailer->Username; // 多数SMTP提供商要求发信人与SMTP服务器匹配，自定义发件人地址可能无效
        $phpmailer->Sender      = $phpmailer->From; //Return-Path--
        $phpmailer->AddReplyTo($phpmailer->From,$phpmailer->FromName); //Reply-To--
        $phpmailer->IsSMTP();
    }
}
add_action('phpmailer_init', 'io_mail_smtp');


/**
 * 邮件发件人名称
 * 
 * @param mixed $from_name
 * @return mixed
 */
function io_mail_from_name($from_name)
{
    return io_get_option('i_smtp_name', get_bloginfo('name'));
}
add_filter('wp_mail_from_name', 'io_mail_from_name');

/**
 * 发送邮件
 *
 * @since 2.0.0
 *
 * @param string    $to      收件人
 * @param string    $title   主题
 * @param string    $content 内容
 * @return  bool|array
 */
function io_mail( $to, $title = '', $content = '' ) {
    if(empty($to)){
        return false;
    }
    try {
        $mail = wp_mail( $to, $title, $content);
    } catch (\Exception $e) {
        return array('error' => 1, 'msg' => $e->getMessage());
    }
    if($mail)
        return true;
    else
        return false;
}
//add_action('io_async_send_mail', 'io_mail', 10, 3);

/**
 * 异步发送邮件
 *
 * @since 2.0.0
 * @param $from
 * @param $to
 * @param string $title
 * @param array $args
 * @param string $template
 */
function io_async_mail( $to, $title = '', $content = ''){
    do_action('send_mail', $to, $title, $content);
}

/**
 * 邮件内容模板
 * @param mixed $mail
 * @return array
 */
function io_get_mail_content_template($mail){
    $blog_name   = get_bloginfo('name');
    $logo        = io_get_option('logo_normal_light');

    $content = '<style>
    *{box-sizing: border-box;}
    .io-wrapper{font-size: 14px;line-height: 1.6;color: #333333;background: #f2f5f8; }
    a{color: #3292ff;text-decoration: none;border-bottom-style: dotted;border-bottom-width: 1px;}
    a:hover{text-decoration:none !important;border-bottom-style: solid;}
    blockquote{border-radius:8px;background:#f5f5f5;margin:10px 0;padding:15px 20px;color:#455667;}
    .center{text-align: center;}
	</style>
    <div class="io-wrapper" style="margin:0;padding:0;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tbody>
            <tr>
                <td class="inner" style="padding:30px 25px 40px 25px;background:#f2f5f8;" bgcolor="#f2f5f8" align="center">
                    <table width="650" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                        <tr>
                            <td width="100%" align="center">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        <td width="50%" align="center"><div style="padding:20px 0"><img src="'.$logo.'"  title="'.$blog_name.'" height="20" style="display:inline;margin:0;max-height:20px;height:20px;width:auto;" border="0"></div></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <table width="100%" style="border-radius:8px;background:#ffffff;" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        <td width="100%">
                                            <div style="padding:26px;font-size:15px;line-height:1.5;color:#3d464d;">
                                                '.$mail['message'].'
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td width="100%" align="center" style="font-size:10px;line-height: 1.5;color: #999999;padding: 5px 0;text-align:center;">
                                <p style="margin:10px 0">'.__('此为系统自动发送邮件, 请勿直接回复','i_theme').'<br>
                                '.__('版权所有','i_theme').' © '. date('Y') .' '.$blog_name.'</p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
	';
    $headers         = array('Content-Type: text/html; charset=UTF-8');
    $mail['message'] = $content;
    $mail['headers'] = $headers;
    return $mail;
}
add_filter('wp_mail', 'io_get_mail_content_template');


/**
 * 发送邮件给网站管理员
 * @param mixed $title
 * @param mixed $message
 * @return void
 */
function io_mail_to_admin($title, $message){
    io_mail(get_option('admin_email'), $title, $message);
}
