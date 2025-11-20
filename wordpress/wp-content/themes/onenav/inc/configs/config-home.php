<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 01:08:54
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:15:40
 * @FilePath: /onenav/inc/configs/config-home.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' )  ) { die; }

$prefix = 'io_home_config';
IOCF::createOptions($prefix, array(
    'framework_title'    => '页面布局',
    'menu_title'         => '首页布局',
    'menu_slug'          => 'home_module',
    'menu_icon'          => 'dashicons-admin-home',
    'menu_position'      => 56,
    'save_defaults'      => true,
    'ajax_save'          => true,
    'show_search'        => false,
    'show_reset_section' => false,
    'show_footer'        => false,
    'show_all_options'   => false,
    'show_sub_menu'      => false,
    'show_bar_menu'      => false,
    'theme'              => 'light',
    'nav'                => 'inline',
    'class'              => 'io-home-config',
    'footer_credit'      => '感谢您使用 <a href="https://www.iotheme.cn/" target="_blank">ioTheme</a> 的WordPress主题',
));
$tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';

/** 
 * 首页布局
 */
IOCF::createSection( $prefix, array(
    'title'  => '首页布局',
    'fields' => array(
        array(
            'id'      => 'aside_show',
            'type'    => 'switcher', 
            'title'   => '✪ 显示边栏菜单',
            'default' => false,
        ),
        io_get_custom_module_config(),
    ),
    'class' => 'group',
));

/** 
 * 子页面布局
 */
IOCF::createSection( $prefix, array(  
    'title'       => '子页面布局', 
    'fields'   => array( 
        array(
            'content' => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 子页面布局填写规则和“首页布局”一致，保存后去【页面】中新建页面，选择“次级导航”模板，在选项里选择对应的布局。',
            'style' => 'info',
            'type' => 'submessage',
        ), 
        array(
            'id'        => 'second_page_list',
            'type'      => 'group',  
            'fields'    => array(
                array(
                    'id'    => 'second_id',
                    'type'  => 'text',
                    'title' => '次级名称',
                ),
                array(
                    'id'      => 'aside_show',
                    'type'    => 'switcher', 
                    'title'   => '✪ 显示边栏菜单',
                    'default' => false,
                ),
                io_get_custom_module_config('sub'),
            ),
            'class'                  => 'no-sort',
            'button_title'           => '新增页面布局',
            'accordion_title_number' => true,
        ),
    )
));

/** 
 * 导入导出
 */
IOCF::createSection( $prefix, array(  
    'title'       => '导入导出', 
    'fields'   => array( 
        //备份
        array(
            'type' => 'backup',
            'before' => '<i class="fa fa-fw fa-info-circle fa-fw"></i> 导出数据分享给好友。'
        ),
    )
));

/**
 * 获取自定义模块配置
 * @param string $loc 模块位置 home:首页，sub:子页面
 * @return array
 */
function io_get_custom_module_config($loc = 'home'){
    return array(
        'id'                     => 'page_module',
        'type'                   => 'repeater',
        'fields'                 => array(
            array(
                'id'      => 'type',
                'type'    => 'button_set',
                'title'   => '模块类型',
                'options' => 'io_custom_module_list',
                'default' => 'content',
            ),
            io_get_search_config(),
            io_get_content_config(),
            io_get_tools_config($loc),
            io_get_custom_config(),
        ),
        'default'                => array(
            array(
                'type' => 'search',
                'search_config' => array(
                    'search_id' => '',
                    'aside_name' => '',
                )
            )
        ),
        'class'                  => 'fieldset-0 add-custom',
        'sanitize'               => false,
        'button_title'           => '添加模块',
        'accordion_title_number' => true,
    );
}

function io_get_search_config(){
    $fields = array(
        'id'         => 'search_config',
        'type'       => 'fieldset',
        'fields'     => array(
            array(
                'id'      => 'search_id',
                'type'    => 'select',
                'title'   => '自定义搜索列表ID',
                'options' => get_search_min_list(),
                'class'   => 'compact min line1',
                'after'   => '<a href="' . esc_url(add_query_arg('page', 'search_settings', admin_url('options-general.php'))) . '" target="_blank" class="button button-primary">自定义配置</a>
                                <span style="color:#dd0d0d;background:#ffe3e3;padding:4px 6px;margin:2px;border-radius:4px;display:inline-block">
                                <i class="fa fa-fw fa-info-circle"></i>[<b>搜索模块</b>] 推荐放在第一个。</span>',
                'desc'    => '',
            ),
            array(
                'id'         => 'aside_name',
                'type'       => 'text',
                'title'      => '✪ 边栏菜单名称',
                'class'      => 'compact min',
                'placeholder'=> '留空则不显示在【边栏菜单】',
                'dependency' => array('aside_show', '==', true, 'group'),
            ),
            array(
                'id'           => 'aside_icon',
                'type'         => 'icon',
                'title'        => '✪ 边栏菜单图标',
                'default'      => 'iconfont icon-diandian',
                'class'        => 'compact min',
                'button_title' => '设置图标',
                'dependency'   => array(
                    array( 'aside_show', '==', true, 'group' ),
                ),
            )
        ),
        'dependency' => array('type', '==', 'search'),
    );
    return apply_filters('io_get_module_search_config_filters', $fields);
}

/**
 * 内容模块配置
 * @return mixed
 */
function io_get_content_config(){
    $fields = array(
        'id'         => 'content_config',
        'type'       => 'fieldset',
        'fields'     => array(
            array(
                'id'          => 'nav_id',
                'type'        => 'select',
                'title'       => '请选择菜单',
                'placeholder' => '选择菜单',
                'options'     => 'menus'
            ),
            array(
                'id'      => 'sidebar_tools',
                'type'    => 'button_set',
                'title'   => '侧边栏小工具',
                'subtitle' => '<a href="' . admin_url('widgets.php') . '" target="_blank" class="sidebar-name disabled">未启用</a>',
                'options' => array(
                    'none'  => '无',
                    'left'  => '左',
                    'right' => '右',
                ),
                'default' => 'none',
                'class'   => 'compact min',
            ),
            array(
                'id'         => 'sidebar_id',
                'type'       => 'number',
                'title'      => '侧边栏ID',
                'after'      => '<span style="color:red"><b>注意：</b>在同一组布局内必须<b>唯一</b>，调整会影响“小工具”中的内容。</span> 去【外观->小工具】中添加内容',
                'default'    => 1,
                'class'      => 'compact min hide',
                'dependency' => array('sidebar_tools', '!=', 'none'),
            ),
            array(
                'id'      => 'show_card',
                'type'    => 'switcher',
                'title'   => '卡片显示',
                'default' => true,
                'class'   => 'compact min',
            ),
            array(
                'id'         => 'tab_ajax',
                'type'       => 'switcher',
                'title'      => 'tab模式 ajax 加载',
                'label'      => '降低首次载入时间，但切换tab时有一定延时',
                'default'    => true,
                'class'      => 'compact min',
            ),
            array(
                'id'      => 'aside_s',
                'type'    => 'switcher',
                'title'   => '✪ 边栏菜单',
                'label'   => '显示到【边栏菜单】里',
                'default' => true,
                'class'   => 'compact min',
                'dependency' => array('aside_show', '==', true, 'group'),
            ),
            io_custom_background_fields(),
        ),
        'dependency' => array('type', '==', 'content'),
    );
    return apply_filters('io_get_module_content_config_filters', $fields);
}
/**
 * 小工具模块配置
 * @return mixed
 */
function io_get_tools_config($loc){
    $title = $loc == 'home' ? '首页' : '子《次级名称》';
    $fields = array(
        'id'         => 'tools_config',
        'type'       => 'fieldset',
        'fields'     => array(
            array(
                'id'      => 'tool_id',
                'type'    => 'number',
                'title'   => '内容正文边栏ID',
                'after'   => '<span style="color:red"><b>注意：</b>在同一组布局内必须<b>唯一</b>，调整会影响“小工具”中的内容</span>',
                'default' => 1,
                'desc'    => '<i class="fa fa-fw fa-info-circle"></i>去【外观->小工具】中添加内容，模块名称「 xx - [小工具ID] - 模块」',
                'class'   => 'hide',
            ),
            array(
                'type'    => 'submessage',
                'style'   => 'success',
                'content' => '<i class="fa fa-fw fa-info-circle"></i>去【外观->小工具】中添加内容，模块名称「 <a href="' . admin_url('widgets.php') . '" target="_blank" class="tools-name">' . $title . ' - [小工具ID] - 模块</a> 」',
            ),
            array(
                'id'      => 'sidebar_tools',
                'type'    => 'button_set',
                'title'   => '侧边栏小工具',
                'subtitle' => '<a href="' . admin_url('widgets.php') . '" target="_blank" class="sidebar-name disabled">未启用</a>',
                'options' => array(
                    'none'  => '无',
                    'left'  => '左',
                    'right' => '右',
                ),
                'default' => 'none',
            ),
            array(
                'id'         => 'sidebar_id',
                'type'       => 'number',
                'title'      => '侧边栏ID',
                'after'      => '<span style="color:red"><b>注意：</b>在同一组布局内必须<b>唯一</b>，调整会影响“小工具”中的内容。</span> 去【外观->小工具】中添加内容',
                'default'    => 1,
                'class'      => 'compact min hide',
                'dependency' => array('sidebar_tools', '!=', 'none'),
            ),
            array(
                'id'         => 'aside_name',
                'type'       => 'text',
                'title'      => '✪ 边栏菜单名称',
                'placeholder'=> '留空则不显示在边栏菜单',
                'dependency' => array('aside_show', '==', true, 'group'),
            ),
            array(
                'id'           => 'aside_icon',
                'type'         => 'icon',
                'title'        => '✪ 边栏菜单图标',
                'default'      => 'iconfont icon-diandian',
                'class'        => 'compact min',
                'button_title' => '设置图标',
                'dependency'   => array(
                    array( 'aside_show', '==', true, 'group' ),
                ),
            ),
            io_custom_background_fields(),
        ),
        'dependency' => array('type', '==', 'tools'),
    );
    return apply_filters('io_get_module_tools_config_filters', $fields);
}
/**
 * 自定义模块配置
 * @return mixed
 */
function io_get_custom_config(){
    $fields = array(
        'id'         => 'custom_config',
        'type'       => 'fieldset',
        'fields'     => array(
            array(
                'id'       => 'html_code',
                'type'     => 'code_editor',
                'before'   => '填写 html 代码，注意代码规范。', 
                'after'    => '<i class="fa fa-fw fa-info-circle"></i> 如果保存报错，请临时关掉服务器的防御类功能。',
                'sanitize' => false,
                'class'    => 'compact min min-height',
                'default'  => '<div class="card">
  <div class="card-header">自定义示例</div>
  <div class="card-body text-center">
    <h5 class="card-title">TITLE</h5>
    <p class="text-muted">自定义内容</p>
    <a href="https://www.iotheme.cn" class="btn vc-l-violet">IOTHEME</a>
  </div>
  <div class="card-footer text-muted text-right">iotheme.cn</div>
</div>',
            ),
            array(
                'id'      => 'sidebar_tools',
                'type'    => 'button_set',
                'title'   => '侧边栏小工具',
                'subtitle' => '<a href="' . admin_url('widgets.php') . '" target="_blank" class="sidebar-name disabled">未启用</a>',
                'options' => array(
                    'none'  => '无',
                    'left'  => '左',
                    'right' => '右',
                ),
                'default' => 'none',
                'class'   => 'compact min',
            ),
            array(
                'id'         => 'sidebar_id',
                'type'       => 'number',
                'title'      => '侧边栏ID',
                'after'      => '<span style="color:red"><b>注意：</b>在同一组布局内必须<b>唯一</b>，调整会影响“小工具”中的内容。</span> 去【外观->小工具】中添加内容',
                'default'    => 1,
                'class'      => 'compact min hide',
                'dependency' => array('sidebar_tools', '!=', 'none'),
            ),
            array(
                'id'         => 'aside_name',
                'type'       => 'text',
                'title'      => '✪ 边栏菜单名称',
                'class'      => 'compact min',
                'placeholder'=> '留空则不显示在【边栏菜单】',
                'dependency' => array('aside_show', '==', true, 'group'),
            ),
            array(
                'id'           => 'aside_icon',
                'type'         => 'icon',
                'title'        => '✪ 边栏菜单图标',
                'default'      => 'iconfont icon-diandian',
                'class'        => 'compact min',
                'button_title' => '设置图标',
                'dependency'   => array(
                    array( 'aside_show', '==', true, 'group' ),
                ),
            ),
            io_custom_background_fields()
        ),
        'dependency' => array('type', '==', 'custom'),
    );
    return apply_filters('io_get_module_custom_config_filters', $fields);
}


function io_custom_background_fields() {
    return array(
        'id'         => 'background',
        'type'       => 'accordion',
        'accordions' => array(
            array(
                'title'  => '背景配置（不设置请‘保持默认’。设置后如有‘细节调整’看右边问号）',
                'icon'   => 'fa fa-photo',
                'fields' => array(
                    array(
                        'id'      => 'margin',
                        'type'    => 'spacing',
                        'title'   => 'margin',
                        'left'    => false,
                        'right'   => false,
                        'units'   => array('px'),
                        'default' => array(
                            'top'    => '0',
                            'bottom' => '0',
                        ),
                    ),
                    array(
                        'id'      => 'padding',
                        'type'    => 'spacing',
                        'title'   => 'padding',
                        'units'   => array('px'),
                        'default' => array(
                            'top'    => '0',
                            'right'  => '0',
                            'bottom' => '0',
                            'left'   => '0',
                        ),
                        'class'   => 'compact min',
                    ),
                    //array(
                    //    'id'        => 'color',
                    //    'type'      => 'color_group',
                    //    'title'     => 'Color',
                    //    'options'   => array(
                    //      'color' => 'Color',
                    //      'hover' => 'Hover',
                    //      'muted' => 'Muted',
                    //    ),
                    //    'class'   => 'compact min',
                    //),
                    array(
                        'id'                    => 'background',
                        'type'                  => 'background',
                        'title'                 => 'background-image',
                        'subtitle'              => '如不使用，<b>颜色</b> 和 <b>背景图片</b> 请留空',
                        'background_gradient'   => true,
                        'background_origin'     => true,
                        'background_clip'       => true,
                        'background_blend_mode' => true,
                        'default'               => array(
                            'background-gradient-direction' => 'to bottom',
                        ),
                        'class'                 => 'compact min',
                    ),
                )
            )
        ),
        'help' => '细节调整：请去 “主题设置/全局功能/添加代码” 的 “自定义样式css” 里面编写<br>模块父css【.module-id-模块ID】',
    );
}
