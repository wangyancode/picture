<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:54:50
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:24:25
 * @FilePath: /onenav/inc/configs/theme/basic-code.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '添加代码',
    'icon'   => 'fa fa-code',
    'fields' => array(
        array(
            'id'       => 'custom_css',
            'type'     => 'code_editor',
            'title'    => '自定义样式css代码',
            'subtitle' => '显示在网站头部 &lt;head&gt;',
            'after'    => '<p class="cs-text-muted"> 自定义 CSS,自定义美化...<br>如： body .test{color:#ff0000;}<br><span style="color:#f00"> 注意：</span>不要填写<strong>&lt;style&gt; &lt;/style&gt;</strong></p>',
            'settings' => array(
                'tabSize' => 2,
                'theme'   => 'mbo',
                'mode'    => 'css',
            ),
            'sanitize' => false,
        ),
        array(
            'id'       => 'code_2_header',
            'type'     => 'code_editor',
            'title'    => '顶部(header)自定义 js 代码',
            'subtitle' => '显示在网站 &lt;/head&gt; 前',
            'after'    => '<p class="cs-text-muted">出现在网站顶部 &lt;/head&gt; 前。<br><span style="color:#f00">注意：</span>必须填写<strong>&lt;script&gt; &lt;/script&gt;</strong></p>',
            'settings' => array(
                'theme' => 'dracula',
                'mode'  => 'javascript',
            ),
            'sanitize' => false,
            'class'    => '',
        ),
        array(
            'id'       => 'code_2_footer',
            'type'     => 'code_editor',
            'title'    => '底部(footer)自定义 js 代码',
            'subtitle' => '显示在网站底部',
            'after'    => '<p class="cs-text-muted">出现在网站底部 body 前。<br><span style="color:#f00">注意：</span>必须填写<strong>&lt;script&gt; &lt;/script&gt;</strong></p>',
            'settings' => array(
                'theme' => 'dracula',
                'mode'  => 'javascript',
            ),
            'sanitize' => false,
        ),
    )
);