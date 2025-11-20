<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:42:22
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-27 00:43:45
 * @FilePath: /onenav/inc/configs/theme/user-social.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$prk_list = array(
    'qq'        => 'QQ',
    'wx'        => '微信',
    'alipay'    => '支付宝',
    'sina'      => '微博',
    'baidu'     => '百度',
    'huawei'    => '华为',
    'google'    => '谷歌',
    'microsoft' => '微软',
    'facebook'  => 'Facebook',
    'twitter'   => 'Twitter',
    'dingtalk'  => '钉钉',
    'github'    => 'GitHub',
    'gitee'     => 'Gitee',
); 

return array(
    'title'  => '社交登录', 
    'icon'   => 'fa fa-share-alt-square',
    'fields' => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '<div style="text-align:center"><b><i class="fa fa-fw fa-ban fa-fw"></i> “微信公众号”和“微信开放平台”请二选一即可，不互通。</b></div>',
        ),
        array(
            'id'      => 'open_login_url',
            'type'    => 'text',
            'title'   => '登录后返回地址', 
            'desc'    => '登录后返回的地址，一般是首页或者个人中心页',
            'default' => esc_url(home_url()),
        ),
        array(
            'id'    => 'open_qq',
            'type'  => 'switcher',
            'title' => 'qq登录', 
        ),
        array(
            'id'         => 'open_qq_key',
            'type'       => 'fieldset',
            'title'      => '参数设置', 
            'fields'     => array(
                array(
                    'content' => '<h4><b>回调地址：</b>' . esc_url(get_template_directory_uri() . '/inc/auth/qq-callback.php') . '</h4>QQ登录申请地址：<a target="_blank" href="https://connect.qq.com/">https://connect.qq.com</a>',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'APPID',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => 'APPKEY',
                    'class' => 'compact min',
                ),
            ),
            'class'      => 'compact',
            'dependency' => array('open_qq', '==', 'true'),
        ),
        array(
            'id'    => 'open_weibo',
            'type'  => 'switcher',
            'title' => '微博登录', 
        ),
        array(
            'id'         => 'open_weibo_key',
            'type'       => 'fieldset',
            'title'      => '参数设置', 
            'fields'     => array(
                array(
                    'content' => '<h4><b>回调地址：</b>' . esc_url(get_template_directory_uri() . '/inc/auth/sina-callback.php') . '</h4>微博登录申请地址：<a target="_blank" href="https://open.weibo.com/authentication/">https://open.weibo.com/authentication</a>',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'APPID',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => 'APPSECRET',
                    'class' => 'compact min',
                ),
            ),
            'class'      => 'compact',
            'dependency' => array('open_weibo', '==', 'true'),
        ),
        array(
            'id'    => 'open_weixin_gzh',
            'type'  => 'switcher',
            'title' => '微信登录(公众号模式)', 
        ),
        array(
            'title'      => '微信公众号登录配置',
            'id'         => 'open_weixin_gzh_key',
            'type'       => 'fieldset',
            'class'      => 'compact',
            'fields'     => array(
                array(
                    'type'    => 'submessage',
                    'style'   => 'info',
                    'content' => '<h4><b>服务器接口URL：</b></h4>
                    <li>公众号：' . esc_url(get_template_directory_uri() . '/inc/auth/gzh-callback.php') . '</li>
                    <li>订阅号：' . esc_url(get_template_directory_uri() . '/inc/auth/dyh-callback.php') . '</li><br>
                    <h4><b>JS接口安全域名：</b>' . preg_replace('/^(?:https?:\/\/)?([^\/]+).*$/im', '$1', home_url()) . '</h4>
                    申请地址：<a target="_blank" href="https://mp.weixin.qq.com/">https://mp.weixin.qq.com/</a>
                    <br>教程：<a target="_blank" href="https://www.iotheme.cn/yiweizhutidisanfangdenglu-wangzhanjieruweixingongzhonghaodenglutuwenjiaocheng.html">查看设置教程</a>
                    <br><i class="fa fa-fw fa-info-circle"></i> 微信登录请二选一开启',
                ),
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'title'   => '类型',
                    'after'   => '【公众号】为 300元/年 的<b>服务号</b>，其他的都选【订阅号】<br>注意：<b>订阅号</b>即使交了300认证通过，一样只能选【订阅号】',
                    'inline'  => true,
                    'options' => array(
                        'gzh' => '公众号',
                        'dyh' => '订阅号',
                    ),
                    'default' => 'gzh',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => '公众号AppID',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => '公众号AppSecret',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'token',
                    'type'  => 'text',
                    'title' => '接口验证token',
                    'class' => 'compact min',
                    'desc'  => '此处token用于在微信平台校验服务器URL时使用，自行填写，和微信平台一致即可。 <a target="_blank" href="https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Access_Overview.html">查看说明</a>',
                ),
                array(
                    'id'         => 'qr_code',
                    'type'       => 'upload',
                    'title'      => '公众号二维码',
                    'class'      => 'compact min',
                    'dependency' => array('type', '==', 'dyh')
                ),
                array(
                    'id'         => 'subscribe_msg',
                    'type'       => 'textarea',
                    'title'      => '新关注消息',
                    'desc'       => '用户首次扫码关注后自动回复的消息',
                    'class'      => 'compact min',
                    'default'    => '感谢您的关注' . PHP_EOL . home_url(),
                    'attributes' => array(
                        'rows' => 2
                    ),
                    'sanitize'   => false,
                    'dependency' => array('type', '==', 'gzh')
                ),
                array(
                    'id'         => 'scan_msg',
                    'type'       => 'textarea',
                    'title'      => '扫码登录消息',
                    'desc'       => '已经关注的用户扫码登录时候自动回复的消息',
                    'class'      => 'compact min',
                    'default'    => '登录成功' . PHP_EOL . home_url(),
                    'attributes' => array(
                        'rows' => 2
                    ),
                    'sanitize'   => false,
                    'dependency' => array('type', '==', 'gzh')
                ),
                array(
                    'id'         => 'auto_reply',
                    'type'       => 'accordion',
                    'accordions' => array(
                        array(
                            'title'  => '公众号自动回复配置',
                            'fields' => array(
                                array(
                                    'id'           => 'text',
                                    'type'         => 'group',
                                    'title'        => '文本消息自动回复',
                                    'sanitize'     => false,
                                    'button_title' => '添加',
                                    'before'       => '关键字精准回复内容，关键字不要设置“登录”、“登陆”、“绑定”。',
                                    'fields'       => array(
                                        array(
                                            'id'      => 'in',
                                            'type'    => 'text',
                                            'title'   => '关键词',
                                            'default' => '',
                                        ),
                                        array(
                                            'id'      => 'mode',
                                            'type'    => "radio",
                                            'title'   => '匹配方式',
                                            'class'   => 'compact min',
                                            'help'    => "包含：收到的消息中含有设置的关键词，等于：收到的消息与设置的关键词完全相同",
                                            'options' => array(
                                                'include' => '包含关键词',
                                                'same'    => '等于关键词',
                                            ),
                                            'inline'  => true,
                                            'default' => 'include',
                                        ),
                                        array(
                                            'id'         => 'out',
                                            'type'       => 'textarea',
                                            'title'      => '回复内容',
                                            'attributes' => array(
                                                'rows' => 1,
                                            ),
                                            'sanitize'   => false,
                                            'class'      => 'compact min',
                                        ),
                                    ),
                                ),
                                array(
                                    'id'         => 'image',
                                    'type'       => 'textarea',
                                    'title'      => '图片消息自动回复',
                                    'attributes' => array(
                                        'rows' => 1,
                                    ),
                                    'sanitize'   => false,
                                    'class'      => 'compact min',
                                ),
                                array(
                                    'id'         => 'voice',
                                    'type'       => 'textarea',
                                    'title'      => '语音消息自动回复',
                                    'attributes' => array(
                                        'rows' => 1,
                                    ),
                                    'sanitize'   => false,
                                    'class'      => 'compact min',
                                ),
                                array(
                                    'id'         => 'default',
                                    'type'       => 'textarea',
                                    'title'      => '其他消息自动回复',
                                    'attributes' => array(
                                        'rows' => 1,
                                    ),
                                    'sanitize'   => false,
                                    'class'      => 'compact min',
                                ),
                            ),
                        ),
                        array(
                            'title'  => '公众号自定义菜单配置',
                            'fields' => array(
                                array(
                                    'type'    => 'content',
                                    'content' => '<div class="options-notice"><div class="explain">
                                    <h4>微信登录功能正常后，请在此设置微信自定义菜单</h4>
                                    <li>在下方粘贴公众号自定义菜单的json配置代码后提交即可</li>
                                    <li>设置成功后会有几分钟的延迟才能生效，请耐心等待</li>
                                    <li>设置菜单选项请移步<a target="_blank" href="https://wei.jiept.com">wei.jiept.com</a>生成json文件</li>
                                    <ajaxform class="ajax-form" ajax-url="' . admin_url("admin-ajax.php") . '">
                                        <p><textarea class="not-change" ajax-name="json" row="4" placeholder="请粘贴菜单的json配置代码" style="width:100%;height:168px">' . str_replace('\"', '"', base64_decode(get_option('io_gzh_menu_json', ''))) . '</textarea>
                                        注意：微信官方限制，未认证的订阅号由于权限不足没法通过此处设置菜单。</p>
                                        <div class="ajax-notice"></div>
                                        <p><a href="javascript:;" class="button button-primary ajax-submit"><i class="fa fa-paper-plane-o"></i> 设置自定义菜单</a></p>
                                        <input type="hidden" ajax-name="action" value="set_weixin_gzh_menu">
                                    </ajaxform>
                                </div></div>',
                                )
                            ),
                        ),
                    ),
                ),
            ),
            'dependency' => array('open_weixin_gzh', '==', 'true'),
        ),
        array(
            'id'    => 'open_wechat',
            'type'  => 'switcher',
            'title' => '微信登录(开放平台模式)', 
        ),
        array(
            'id'         => 'open_wechat_key',
            'type'       => 'fieldset',
            'title'      => '微信登录参数设置', 
            'fields'     => array(
                array(
                    'content' => '<h4><b>开放平台回调地址：</b>' . parse_url(home_url())['host'] . '</h4>
                    微信登录申请地址：<a target="_blank" href="https://open.weixin.qq.com/">https://open.weixin.qq.com</a>
                    <br><i class="fa fa-fw fa-info-circle fa-fw"></i> 微信登录请三选一开启',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'APPID',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => 'APPSECRET',
                    'class' => 'compact min',
                ),
            ),
            'class'      => 'compact',
            'dependency' => array('open_wechat', '==', 'true'),
        ),
        array(
            'id'    => 'open_baidu',
            'type'  => 'switcher',
            'title' => '百度登录', 
        ),
        array(
            'id'         => 'open_baidu_key',
            'type'       => 'fieldset',
            'title'      => '百度参数配置',
            'fields'     => array(
                array(
                    'content' => '<h4><b>回调地址：</b>' . esc_url(get_template_directory_uri() . '/inc/auth/baidu-callback.php') . '</h4>百度登录申请地址：<a target="_blank" href="http://developer.baidu.com/">http://developer.baidu.com</a>',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'API Key',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => 'Secret Key',
                    'class' => 'compact min',
                ),
            ),
            'class'      => 'compact',
            'dependency' => array('open_baidu', '==', 'true'),
        ),
        array(
            'id'    => 'open_gitee',
            'type'  => 'switcher',
            'title' => '码云(gitee)登录', 
        ),
        array(
            'id'         => 'open_gitee_key',
            'type'       => 'fieldset',
            'title'      => '码云(gitee)参数配置',
            'fields'     => array(
                array(
                    'type'    => 'submessage',
                    'style'   => 'info',
                    'content' => '<h4><b>回调地址：</b>' . esc_url(get_template_directory_uri() . '/inc/auth/gitee-callback.php') . '</h4>
                    码云(gitee)登录申请地址：<a target="_blank" href="https://gitee.com/oauth/applications/">https://gitee.com/oauth/applications</a>',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'AppID',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => 'AppKey',
                    'class' => 'compact min',
                ),
            ),
            'class'      => 'compact',
            'dependency' => array('open_gitee', '==', 'true'),
        ),
        array(
            'id'    => 'open_github',
            'type'  => 'switcher',
            'title' => 'GitHub登录', 
        ),
        array(
            'id'         => 'open_github_key',
            'type'       => 'fieldset',
            'title'      => 'GitHub参数配置',
            'fields'     => array(
                array(
                    'content' => '<h4><b>回调地址：</b>' . esc_url(get_template_directory_uri() . '/inc/auth/github-callback.php') . '</h4>
                    GitHub登录申请地址：<a target="_blank" href="https://github.com/settings/developers">https://github.com/settings/developers</a>',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'AppID',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => 'AppKey',
                    'class' => 'compact min',
                ),
            ),
            'class'      => 'compact',
            'dependency' => array('open_github', '==', 'true'),
        ),
        array(
            'id'    => 'open_prk',
            'type'  => 'switcher',
            'title' => '聚合登录',
        ),
        array(
            'id'         => 'open_prk_key',
            'type'       => 'fieldset',
            'title'      => '聚合登录参数设置', 
            'fields'     => array(
                array(
                    'content' => '<h4>免一切授权验证等步骤，只需要在聚合登录注册就可以使用qq、微信、微博等登录方式。</h4>
                    <b>接口申请：</b><a target="_blank" href="https://iologin.cc/">查找</a>',
                    'style'   => 'info',
                    'type'    => 'submessage',
                ),
                array(
                    'id'    => 'apiurl',
                    'type'  => 'text',
                    'title' => 'API地址',
                ),
                array(
                    'id'    => 'appid',
                    'type'  => 'text',
                    'title' => 'APPID',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'appkey',
                    'type'  => 'text',
                    'title' => 'APPKEY',
                    'class' => 'compact min',
                ),
            ),
            'default'    => array(
                'apiurl' => 'https://iologin.cc/',
            ),
            'class'      => 'compact',
            'dependency' => array('open_prk', '==', 'true'),
        ),
        array(
            'id'         => 'open_prk_list',
            'type'       => 'checkbox',
            'title'      => '聚合登录启用项',
            'options'    => $prk_list,
            'inline'     => true,
            'class'      => 'compact',
            'dependency' => array('open_prk', '==', 'true'),
        ),
    )
);