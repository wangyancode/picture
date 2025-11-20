<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-04 02:18:28
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-04 18:55:55
 * @FilePath: /onenav/inc/widgets/w.tabs.box.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';

IOCF::createWidget('iow_tabs_box_max', array(
    'title'       => 'IO [TAB]盒子',
    'classname'   => 'io-big-carousel',
    'description' => '竖向 TAB 标签盒子',
    'fields'      => array(
        array(
            'id'           => 'config',
            'type'         => 'group',
            'before'       => $tip_ico . '警告：添加后选项不能留空，否则网站将爆炸。',
            'fields'       => array(
                array(
                    'id'    => 'title',
                    'type'  => 'text',
                    'title' => '名称',
                ),
                array(
                    'id'      => 'type',
                    'type'    => 'button_set',
                    'title'   => '类型',
                    'options' => array(
                        'favorites' => '网址',
                        'apps'      => 'App',
                        'books'     => '书籍',
                        'category'  => '文章',
                    ),
                    'class'   => 'home-widget-type compact min',
                    'default' => 'favorites',
                ),
                array(
                    'id'          => 'cat',
                    'type'        => 'select',
                    'placeholder' => '选择一个类别，留空则检索全部内容',
                    'chosen'      => true,
                    'ajax'        => true,
                    'options'     => 'categories',
                    'query_args'  => array(
                        'taxonomy' => 'favorites',
                    ),
                    'before'      => $tip_ico . '选择类型后输入<b>分类名称</b>关键字搜索分类',
                    'settings'    => array(
                        'min_length' => 2,
                    ),
                    'class'       => 'home-widget-cat compact min',
                ),
                array(
                    'id'      => 'order',
                    'type'    => 'radio',
                    'title'   => '排序',
                    'inline'  => true,
                    'options' => array(
                        'ID'       => 'ID',
                        'modified' => '修改日期',
                        'date'     => '创建日期',
                        'views'    => '查看次数',
                        'rand'     => '随机',
                    ),
                    'default' => 'modified',
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'num',
                    'type'    => 'spinner',
                    'title'   => '显示数量',
                    'step'    => 1,
                    'default' => 24,
                    'class'   => 'compact min',
                ),
                array(
                    'id'         => 'go',
                    'type'       => 'switcher',
                    'title'      => '直达',
                    'label'      => '直达目标网站',
                    'class'      => 'compact min',
                    'dependency' => array('type', '==', 'favorites'),
                ),
                array(
                    'id'      => 'ico',
                    'type'    => 'icon',
                    'title'   => '图标',
                    'default' => 'io io-bianqian',
                    'class'   => 'compact min',
                ),
            ),
            'button_title' => '添加 TAB'
        ),
    ),
));

function iow_tabs_box_max($args, $instance)
{
    $datas = $instance['config'];
    if (!is_array($datas) || count($datas) == 0) {
        return;
    }
    return;

    $html = '<div class="card tab-sites-widget br-xl">';
    $html .= '<div class=" tab-sites-body p-2 d-flex">';
    $html .= '<div class="tab-widget-nav">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">';

    if (is_array($datas) && $datas) {
        for ($i = 0; $i < count($datas); $i++) {
            $active = $i == 0 ? 'active load' : '';
            $datas  = json_encode(array('data' => $datas[$i]), JSON_UNESCAPED_UNICODE);
            $html .= '<a class="nav-link tab-widget-link ' . $active . '" id="home_widget_' . ($datas[$i]['cat']) . '-tab" data-action="get_tab_widget_post" data-datas="' . esc_attr($datas) . '" data-toggle="pill" href="#home_widget_' . $datas[$i]['cat'] . '" role="tab" aria-controls="home_widget" aria-selected="' . ($i == 0 ? 'true' : 'false') . '">
                    <i class="' . $datas[$i]['ico'] . '"></i>
                    <span class="text-xs text-muted mt-1 d-none d-md-block line1">' . ($datas[$i]['title']) . '</span>
                </a> ';
        }
    }
    $html .= '</div>
        </div>';
    $html .= '<div class="tab-widget-content ml-2 p-2 tab-content" id="v-pills-tabContent">';

    if (is_array($datas) && $datas) {
        for ($i = 0; $i < count($datas); $i++) {
            $active = $i == 0 ? 'show active' : '';

            $html .= '<div class="tab-pane fade ' . $active . '" id="home_widget_' . ($datas[$i]['cat']) . '" role="tabpanel" aria-labelledby="home_widget_' . ($datas[$i]['cat']) . '-tab">';
            $html .= '<div class="widget-item item-' . $datas[$i]['type'] . '">';

            if ($i == 0) {
                $html .= get_tab_post_html($datas[$i], 'tab');
            } else {
                $html .= '<div class="d-flex justify-content-center align-items-center position-absolute w-100 h-100" style="left:0;top:0"><div class="spinner-border m-4" role="status"><span class="sr-only">Loading...</span></div></div>';
            }

            $html .= ' </div>
            </div>';
        }
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    echo $args['before_widget'];
    echo $args['after_widget'];
}

