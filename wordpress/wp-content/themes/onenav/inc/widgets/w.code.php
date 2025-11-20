<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-09-13 01:28:58
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-06 15:20:49
 * @FilePath: /onenav/inc/widgets/w.code.php
 * @Description: 
 */

IOCF::createWidget('iow_code_embed', array(
    'title'       => 'IO 嵌入代码',
    'classname'   => 'io-widget-code-embed',
    'description' => '可插入html、js、css代码，广告代码，或者插入 iframe 视频',
    'fields'      => array(
        array(
            'id'      => 'title',
            'type'    => 'text',
            'title'   => '名称(留空不显示)',
            'default' => '',
        ),
        array(
            'id'      => 'title_ico',
            'type'    => 'icon',
            'title'   => ' ',
            'default' => 'iconfont icon-tools',
            'class'   => 'compact min'
        ),
        array(
            'id'         => 'code',
            'type'       => 'textarea',
            'title'      => '代码',
            'after'      => '请输入HTML代码，注意代码规范，不规范会破坏页面布局。',
            'class'      => 'compact min',
            'sanitize'   => false,
            'attributes' => array(
                'rows' => 5
            )
        ),
        array(
            'id'    => 'is_video',
            'type'  => 'switcher',
            'title' => '是视频',
            'help'  => '如果是iframe视频，清开启。开启后可只填视频链接，系统会自动添加iframe标签',
            'class' => 'compact min'
        ),
        array(
            'id'         => 'aspect',
            'type'       => 'slider',
            'title'      => '长宽比例（长/宽）',
            'default'    => 56,
            'max'        => 200,
            'min'        => 30,
            'unit'       => '%',
            'help'       => '16/9=56, 4/3=75, 1/1=100, 9/16=177, 3/4=133',
            'dependency' => array('is_video', '==', 'true'),
            'class'      => 'compact min'
        ),
    )
));
function iow_code_embed($args, $instance) {
    $body = '';
    if ($instance['is_video']) {
        $url = $instance['code'];
        //判断url是不是 链接
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $iframe = '<iframe class="lazy" framespacing="0" border="0" frameborder="no" data-src="' . esc_url($url) . '"></iframe>';
        } else {
            $iframe = $url;
        }
        $body = '<div class="iframe-video-aspect" style="padding-top:' . $instance['aspect'] . '%">' . $iframe . '</div>';
    }else{
        $body = $instance['code'];
    }

    echo $args['before_widget'];
    echo get_widget_title($args, $instance);
    echo $instance['title'] ? '<div class="card-body">' : '';
    echo $body;
    echo $instance['title'] ? '</div>' : '';
    echo $args['after_widget'];
}
