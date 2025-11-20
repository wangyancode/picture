<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:02:06
 * @LastEditors: iowen
 * @LastEditTime: 2024-04-27 00:02:15
 * @FilePath: /onenav/inc/configs/theme/start.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '开始使用', 
    'icon'   => 'fa fa-shopping-cart',
    'fields' => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '<h3 style="color:#ee187e"><i class="fa fa-heart fa-fw"></i> 感谢您使用 ioTheme 一为主题</h3><p style="font-size:13px">' . $tip_ico . '<b>注意 & 必看</b></p>
            <li><b>必须配置</b> 服务器 <b>伪静态规则</b> 和 <b>wp<a href="' . admin_url('options-permalink.php') . '">固定链接</a></b> 格式，否则会造成<b>404</b>，<a href="https://www.iotheme.cn/wordpressweijingtaihewordpressgudinglianjieshezhi.html" target="_blank">伪静态设置方法</a></li>
            <li><b>演示数据</b>： <a href="https://www.iotheme.cn/one-nav-yidaohangyanshishujushiyongjiaocheng.html" target="_blank">下载</a> （<b>警告</b>：安装演示数据前必须配置服务器 <b>伪静态规则</b>）</li>
            <li><a href="https://www.iotheme.cn/update-log" target="_blank">查看更新日志</a></li>',
        ),
        array(
            'type'     => 'callback',
            'function' => 'active_html',
        ),
        array(
            'id'      => 'update_theme',
            'type'    => 'switcher',
            'title'   => '检测主题更新', 
            'label'   => '在线更新为替换更新，如果你修改了主题代码，请关闭（如需修改，可以使用子主题）', 
            'default' => true,
            'desc'    => $tip_ico . '请勿修改主题文件夹名称，可能会导致检查不到更新。<br>' . $tip_ico . '基于wp更新通道，请勿关闭wp更新检查。',
        ),
        array(
            'id'         => 'update_beta',
            'type'       => 'switcher',
            'title'      => '加入Beta版体验', 
            'label'      => '可体验最新功能',
            'before'     => '<a href="' . admin_url('update-core.php') . '" class="but c-blue" style="margin:0">检查更新</a>',
            'desc'       => '开启后加入Beta版更新通道，Beta版及测试版，可体验最新功能，同时也可能会有各种bug。',
            'class'      => 'compact min',
            'default'    => false,
            'dependency' => array('update_theme', '==', true)
        ),
        array(
            'type'    => 'content',
            'title'   => '系统环境',
            'content' => io_get_system_info(),
        ),
        array(
            'type'    => 'content',
            'title'   => '推荐环境',
            'content' => '<ul><li><strong>WordPress</strong>：6.1+，推荐使用最新版</li>
            <li><strong>PHP</strong>：PHP7.4及以上</li>
            <li><strong>服务器配置</strong>：1核2G，不推荐虚拟机</li>
            <li><strong>操作系统</strong>：无要求，不推荐使用Windows系统</li>
            <li><strong>环境</strong>：推荐宝塔或者自建</li></ul>',
        ),
    )
);