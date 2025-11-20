<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-04 01:30:17
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:29:08
 * @FilePath: /onenav/inc/configs/theme/content-bulletin.php
 * @Description: 
 */

return array(
    'title'  => '公告设置',
    'icon'   => 'fa fa-bullhorn',
    'fields' => array(
        array(
            'id'      => 'show_bulletin',
            'type'    => 'switcher',
            'title'   => '启用公告',
            'label'   => '启用自定义文章类型“公告”，启用后刷新页面',
            'default' => true,
        ),
        array(
            'id'         => 'bulletin',
            'type'       => 'switcher',
            'title'      => '显示公告',
            'label'      => '在首页顶部显示公告',
            'default'    => false,
            'class'      => 'compact',
            'dependency' => array('show_bulletin', '==', true)
        ),
        array(
            'id'         => 'bulletin_n',
            'type'       => 'spinner',
            'title'      => '公告数量',
            'after'      => '需要显示的公告篇数',
            'max'        => 10,
            'min'        => 1,
            'step'       => 1,
            'default'    => 2,
            'class'      => 'compact',
            'dependency' => array('bulletin|show_bulletin', '==|==', 'true|true')
        ),
        array(
            'id'      => 'bull_img',
            'type'    => 'upload',
            'title'   => '公告页头部图片',
            'class'   => 'compact',
            'default' => '//cdn.iocdn.cc/gh/owen0o0/ioStaticResources@master/banner/banner015.jpg',
        ),
    )
);