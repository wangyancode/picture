<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:31:58
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-27 00:32:03
 * @FilePath: /onenav/inc/configs/theme/other-sms.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'       => '短信接口',
    'icon'        => 'fa fa-comments',
    'description' => '',
    'fields'      => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '<h4>如需使用手机账户等相关功能，请在下方设置短信接口</h4>
            <li>阿里云和腾讯云需要网站备案！其它接口无需备案</li>
            <li>短信能正常发送后，请记得开启手机绑定、手机号登录、手机验证等功能</li>
            <li><a href="' . io_get_admin_iocf_url('用户安全/用户注册') . '">登录/注册功能设置</a></li>',
        ),
        array(
            'id'      => 'sms_sdk',
            'type'    => "select",
            'title'   => '设置短信接口',
            'options' => array(
                'ali'     => '阿里云短信', 
                'tencent' => '腾讯云短信', 
                'smsbao'  => '短信宝', 
            ),
            'default' => 'null',
        ),
        array(
            'id'         => 'sms_ali_option',
            'type'       => 'accordion',
            'title'      => '阿里云',
            'accordions' => array(
                array(
                    'title'  => '阿里云短信配置',
                    'fields' => array(
                        array(
                            'id'      => 'keyid',
                            'type'    => 'text',
                            'title'   => 'AccessKey Id',
                            'default' => '',
                        ),
                        array(
                            'id'      => 'keysecret',
                            'type'    => 'text',
                            'title'   => 'AccessKey Secret',
                            'class'   => 'compact min',
                            'default' => '',
                        ),
                        array(
                            'id'      => 'sign_name',
                            'type'    => 'text',
                            'title'   => '签名',
                            'class'   => 'compact min',
                            'desc'    => '已审核的的短信签名，示例：一为主题',
                            'default' => '',
                        ),
                        array(
                            'id'      => 'template_code',
                            'type'    => 'text',
                            'title'   => '模板CODE',
                            'class'   => 'compact min',
                            'desc'    => '已审核的的短信模板代码，示例：SMS_154950000<br>
                            模板内容示例：<code>您的验证码为：${code}，......</code> 必须要有 <code style="color: #ee0f00;padding:0px 3px">${code}</code> 变量。<br>
                            <a target="_blank" href="https://www.aliyun.com/product/sms?source=5176.11533457&userCode=d7pz9hw8">申请地址</a>',
                            'default' => '',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'sms_tencent_option',
            'type'       => 'accordion',
            'title'      => '腾讯云',
            'accordions' => array(
                array(
                    'title'  => '腾讯云短信配置',
                    'fields' => array(
                        array(
                            'id'    => 'app_id',
                            'type'  => 'text',
                            'title' => 'SDK AppID',
                        ),
                        array(
                            'id'    => 'app_key',
                            'type'  => 'text',
                            'title' => 'App Key',
                            'class' => 'compact min',
                            'desc'  => '腾讯云短信应用的SDK AppID和AppKey',
                        ),
                        /* SDK3.0需要的参数
                        array(
                            'id'    => 'secret_id',
                            'type'  => 'text',
                            'title' => 'Access Id',
                            'class' => 'compact min',
                        ),
                        array(
                            'id'    => 'secret_key',
                            'type'  => 'text',
                            'title' => 'Access Key',
                            'class' => 'compact min',
                        ),
                        */
                        array(
                            'id'    => 'sign_name',
                            'type'  => 'text',
                            'title' => '签名',
                            'class' => 'compact min',
                            'desc'  => '已审核的的短信签名，示例：一为主题',
                        ),
                        array(
                            'id'    => 'template_id',
                            'type'  => 'text',
                            'title' => '模板ID',
                            'class' => 'compact min',
                            'desc'  => '已审核的的短信模板ID，示例：1660000<br>
                            模板内容示例：<code>您的验证码为{1}，{2}分钟内有效，......</code> 必须要有 <code style="color: #ee0f00;padding:0px 3px">{1}</code> 和 <code style="color: #ee0f00;padding:0px 3px">{2}</code> 变量。<br>
                            <a target="_blank" href="https://cloud.tencent.com/act/cps/redirect?redirect=10068&cps_key=bda57913e36ec90681a3b90619e44708">申请地址</a>',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'sms_smsbao_option',
            'type'       => 'accordion',
            'title'      => '短信宝',
            'accordions' => array(
                array(
                    'title'  => '短信宝配置',
                    'fields' => array(
                        array(
                            'id'    => 'userame',
                            'type'  => 'text',
                            'title' => '账号用户名',
                        ),
                        array(
                            'id'    => 'password',
                            'type'  => 'text',
                            'title' => '账号密码',
                            'class' => 'compact min',
                        ),
                        array(
                            'id'    => 'api_key',
                            'type'  => 'text',
                            'title' => 'ApiKey',
                            'class' => 'compact min',
                            'desc'  => '短信宝ApiKey（可选）<br>ApiKey是短信宝新推出的接口方式，更高效安全，ApiKey和密码二选一即可',
                        ),
                        array(
                            'id'    => 'template',
                            'type'  => 'text',
                            'class' => 'compact min',
                            'title' => '模板内容',
                            'desc'  => '模板内容，必须要有<code style="color: #ee0f00;padding:0px 3px">{code}</code>变量。<br>
                            模板内容示例：<code>【一为主题】您的验证码为{code}，{time}分钟内有效。</code><br>
                            <a target="_blank" href="http://www.smsbao.com/reg?r=12245">短信宝官网</a>',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h4>短信发送测试：</h4>
            <p>输入接收短信的手机号码，在此发送验证码为8888的测试短信</p>
            <ajaxform class="ajax-form" ajax-url="' . admin_url('admin-ajax.php') . '">
            <p><input type="text" class="not-change" ajax-name="phone_number"></p>
            <div class="ajax-notice"></div>
            <p><a href="javascript:;" class="button button-primary ajax-submit"><i class="fa fa-paper-plane-o"></i> 发送测试短信</a></p>
            <input type="hidden" ajax-name="action" value="test_send_sms">
            </ajaxform>',
        ),
    ),
);