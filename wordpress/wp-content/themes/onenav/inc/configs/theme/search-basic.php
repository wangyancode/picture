<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:05:44
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 18:23:48
 * @FilePath: /onenav/inc/configs/theme/search-basic.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '搜索设置',
    'icon'   => 'fas fa-search',
    'fields' => array(
        array(
            'id'       => 'search_skin',
            'type'     => 'fieldset',
            'title'    => 'head 搜索设置',
            'fields'   => array(
                array(
                    'id'      => 'search_big',
                    'type'    => "image_select",
                    'title'   => '搜索布局样式',
                    'options' => array(
                        'def' => get_theme_file_uri('/assets/images/option/op_search_layout_def.png'),
                        '1'   => get_theme_file_uri('/assets/images/option/op_search_layout_big.png'),
                    ),
                    'default' => 'def',
                ),
                array(
                    'id'         => 'search_station',
                    'type'       => 'switcher',
                    'title'      => '前置站内搜索',
                    'label'      => '开头显示站内搜索，关闭将不显示搜索推荐',
                    'default'    => true,
                    'class'      => 'compact min',
                    'dependency' => array('search_big', '==', true)
                ),
                array(
                    'id'         => 'height',
                    'type'       => 'slider',
                    'title'      => '搜索框高度',
                    'subtitle'   => '默认 360px',
                    'min'        => 180,
                    'max'        => 720,
                    'step'       => 10,
                    'unit'       => 'px',
                    'default'    => 360,
                    'class'      => 'compact min',
                    'dependency' => array('search_big', '==', true)
                ),
                array(
                    'id'         => 'mobile_height',
                    'type'       => 'slider',
                    'title'      => '移动端搜索框高度',
                    'subtitle'   => '默认 260px',
                    'min'        => 180,
                    'max'        => 720,
                    'step'       => 10,
                    'unit'       => 'px',
                    'default'    => 260,
                    'class'      => 'compact min',
                    'dependency' => array('search_big', '==', true)
                ),
                array(
                    'id'         => 'big_title',
                    'type'       => 'textarea',
                    'title'      => '大字标题',
                    'after'      => $tip_ico . '留空不显示，支持 html',
                    'class'      => 'compact min',
                    'sanitize'   => false,
                    'attributes' => array(
                        'rows' => 1,
                    ),
                    'dependency' => array('search_big', '==', true)
                ),
                array(
                    'id'         => 'big_skin',
                    'type'       => 'radio',
                    'title'      => '背景模式',
                    'default'    => 'css-color',
                    'inline'     => true,
                    'options'    => array(
                        'no-bg'     => '无背景',
                        'css-color' => '颜色',
                        'css-img'   => '自定义图片',
                        'css-bing'  => 'bing 每日图片',
                        'canvas-fx' => 'canvas 特效',
                    ),
                    'class'      => 'compact min',
                    'dependency' => array('search_big', '==', true)
                ),
                array(
                    'id'         => 'search_color',
                    'type'       => 'color_group',
                    'title'      => '背景颜色',
                    'options'    => array(
                        'color-1' => 'Color 1',
                        'color-2' => 'Color 2',
                        'color-3' => 'Color 3',
                    ),
                    'default'    => array(
                        'color-1' => '#ff3a2b',
                        'color-2' => '#ed17de',
                        'color-3' => '#f4275e',
                    ),
                    'class'      => 'compact min',
                    'dependency' => array('search_big|big_skin', '==|==', 'true|css-color')
                ),
                array(
                    'id'         => 'search_img',
                    'type'       => 'upload',
                    'title'      => '背景图片',
                    'add_title'  => '上传',
                    'class'      => 'compact min',
                    'dependency' => array('search_big|big_skin', '==|==', 'true|css-img')
                ),
                array(
                    'id'         => 'canvas_id',
                    'type'       => 'radio',
                    'title'      => 'canvas 样式',
                    'default'    => '0',
                    'inline'     => true,
                    'options'    => get_all_fx_bg(),
                    'class'      => 'compact min',
                    'dependency' => array('search_big|big_skin', '==|==', 'true|canvas-fx')
                ),
                array(
                    'id'         => 'custom_canvas',
                    'type'       => 'text',
                    'title'      => 'canvas地址',
                    'after'      => '留空会爆炸，既然选择了，请不要留空！！！<br>示例：//owen0o0.github.io/ioStaticResources/canvas/01.html<br>注意：可能会有跨域问题，解决方法百度。',
                    'default'    => '//owen0o0.github.io/ioStaticResources/canvas/01.html',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                    'class'      => 'compact min',
                    'dependency' => array('search_big|canvas_id|big_skin', '==|==|==', 'true|custom|canvas-fx')
                ),
                array(
                    'id'         => 'changed_bg',
                    'type'       => 'switcher',
                    'title'      => '暗色主题压暗背景',
                    'label'      => '切换到暗色主题时自动压暗背景',
                    'default'    => true,
                    'class'      => 'compact min',
                    'dependency' => array('search_big|big_skin', '==|!=', 'true|no-bg')
                ),
                array(
                    'id'         => 'bg_gradual',
                    'type'       => 'switcher',
                    'title'      => '背景渐变',
                    'default'    => false,
                    'class'      => 'compact min',
                    'dependency' => array('search_big|big_skin', '==|!=', 'true|no-bg')
                ),
                array(
                    'id'         => 'post_top',
                    'type'       => 'switcher',
                    'title'      => '内容上移',
                    'default'    => false,
                    'class'      => 'compact min',
                    'label'      => '搜索框下面的内容覆盖到搜索框背景上面',
                    'dependency' => array('search_big|big_skin', '==|!=', 'true|no-bg')
                ),
            ),
            'sanitize' => false,
        ),
        array(
            'id'       => 'baidu_hot_words',
            'type'     => 'radio',
            'title'    => ' ',
            'subtitle' => '搜索词补全',
            'default'  => 'baidu',
            'inline'   => true,
            'options'  => array(
                'null'   => '无',
                'baidu'  => '百度',
                'google' => 'Google',
            ),
            'after'    => '选择搜索框词补全源，选无则不补全。',
            'class'    => 'compact min'
        ),
        array(
            'id'          => 'search_page_post_type',
            'type'        => 'select',
            'title'       => '站内可搜索的类型',
            'subtitle'    => '移动端无法设置，请用PC',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'options'     => 'io_get_search_type_name',
            'placeholder' => '选择文章类型',
            'default'     => array('sites', 'post', 'app', 'book'),
            'before'      => '拖动排序，不需要的“x”掉。注：启用必须保留一个',
        ),
        array(
            'id'         => 'local_search_login',
            'type'       => 'switcher',
            'title'      => '站内搜索必须登陆',
            'default'    => false,
            'label'      => '开启后站内搜索必须登陆',
        ),
        array(
            'id'     => 'local_search_config',
            'type'   => 'fieldset',
            'title'  => '站内搜索配置',
            'fields' => array(
                array(
                    'id'      => 'placeholder',
                    'type'    => 'text',
                    'title'   => '搜索框占位文案',
                    'default' => '你想了解些什么',
                ),
                array(
                    'id'      => 'hot_search_title',
                    'type'    => 'text',
                    'title'   => '热门搜索文案',
                    'default' => '热门搜索',
                    'after'   => '留空不显示 “热门搜索” 模块',
                ),
                array(
                    'id'       => 'hot_search_preset',
                    'type'     => 'textarea',
                    'title'    => '热门搜索预设',
                    'class'    => 'compact min',
                    'after'    => '显示值在热门搜索中的关键词，使用逗号分割，例如：<code>一为,主题,onenav@post</code>
                                <br>使用后缀<code>@xxx</code>可定义搜索类型，可选类型：post(文章)、sites、app、book，默认为「搜索页选项卡」启用的第一个',
                    'sanitize' => 'strip_tags',
                ),
                array(
                    'id'      => 'history_title',
                    'type'    => 'text',
                    'title'   => '历史搜索文案',
                    'default' => '历史搜索',
                    'after'   => '留空不显示 “历史搜索” 模块',
                ),
                array(
                    'id'    => 'gadget',
                    'type'  => 'switcher',
                    'title' => '搜索框弹窗小工具',
                    'label' => '开启后在搜索框下面显示小工具',
                )
            ),
            'class'  => 'new'
        ),
        array(
            'id'      => 'search_limit_config',
            'type'    => 'fieldset',
            'title'   => '站内搜索限制',
            'fields'  => array(
                array(
                    'id'    => 'limit',
                    'type'  => 'number',
                    'title' => '搜索频率次数',
                    'after' => '默认 2 次/ 10 秒',
                    'unit'  => '次',
                ),
                array(
                    'id'    => 'time',
                    'type'  => 'number',
                    'title' => '搜索频率时间',
                    'after' => '填 0 秒不限制；默认 10 秒',
                    'unit'  => '秒',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'limit_msg',
                    'type'  => 'text',
                    'title' => '频率限制提示',
                    'after' => '留空直接返回首页。',
                    'class' => 'compact min',
                ),
                array(
                    'id'         => 'black',
                    'type'       => 'text',
                    'title'      => '搜索黑名单',
                    'after'      => '留空不限制；用逗号分割，如：赌博,博彩,彩票',
                    'attributes' => array(
                        'style' => 'width: 100%'
                    ),
                ),
                array(
                    'id'    => 'black_msg',
                    'type'  => 'text',
                    'title' => '黑名单提示',
                    'after' => '留空直接返回首页。',
                    'class' => 'compact min',
                ),
                array(
                    'id'    => 'count',
                    'type'  => 'number',
                    'title' => '搜索词长度限制',
                    'after' => '填 0 不限制；默认 10 个字',
                    'unit'  => '个字',
                ),
                array(
                    'id'    => 'count_msg',
                    'type'  => 'text',
                    'title' => '长度限制提示',
                    'after' => '留空直接返回首页。',
                    'class' => 'compact min',
                ),
            ),
            'default' => array(
                'limit'     => 2,
                'time'      => 10,
                'black'     => '妹子,美女,裸聊,色情,黄色,赌博,博彩,彩票,性爱,性交,做爱,约炮',
                'count'     => 10,
                'limit_msg' => '您搜索过于频繁，请稍后再试。',
                'black_msg' => '您的搜索包含了禁止的关键词，请重新输入。',
                'count_msg' => '搜索词过长，最多10个字。',
            ),
            'before'  => '管理员不限制，只限制普通用户',
            'class'   => 'new'
        )
    )
);