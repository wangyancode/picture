<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-04-27 00:49:55
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 17:14:40
 * @FilePath: /onenav/inc/configs/theme/user-contribute.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

return array(
    'title'  => '用户投稿',
    'icon'   => 'fas fa-edit',
    'fields' => array(
        array(
            'id'      => 'is_contribute',
            'type'    => 'switcher',
            'title'   => '前端投稿',
            'default' => true,
        ),
        array(
            'id'      => 'edit_to_admin',
            'type'    => 'switcher',
            'title'   => '允许编辑进入后台',
            'class'   => 'compact min',
            'default' => false,
        ),
        array(
            'type'       => 'submessage',
            'style'      => 'danger',
            'content'    => '开启「前端投稿」功能后，下面选项才有意义。',
            'dependency' => array('is_contribute', '==', 'false')
        ),
        array(
            'id'      => 'contribute_can',
            'type'    => 'button_set',
            'title'   => '可投稿用户组',
            'options' => array(
                'all'   => '所有人', 
                'user'  => '登录用户', 
                'admin' => '仅管理员', 
            ),
            'default' => 'user',
        ),
        array(
            'id'      => 'contribute_time',
            'type'    => 'spinner',
            'title'   => '投稿间隔时间',
            'min'     => 0,
            'unit'    => '秒',
            'default' => 30,
            'after'   => '填 0 不限制。',
            'class'   => 'compact min',
        ),
        array(
            'id'      => 'tag_temp',
            'type'    => 'switcher',
            'title'   => '标签插入正文',
            'default' => false,
            'label'   => '将标签插入正文，避免直接在站点生成标签地址（因为 wp 的标签没有审核状态）。审核发布时需手动将正文中的“标签”剪切到标签选项。',
            'after'   => '<br><br>如果对应文章类型开启了【直接发布】，则此项无效。',
            'class'   => 'compact min',
        ),
        array(
            'id'       => 'favicon_img_size',
            'type'     => 'spinner',
            'title'    => '网址图标大小',
            'subtitle' => '最大值限制',
            'max'      => 2,
            'min'      => 0,
            'step'     => 0.1,
            'unit'     => 'MB',
            'after'    => $tip_ico . '填 0 关闭图标上传。',
            'default'  => 0.6,
            'class'    => 'compact min',
        ),
        array(
            'id'       => 'cover_img_size',
            'type'     => 'spinner',
            'title'    => '书籍封面大小',
            'subtitle' => '最大值限制',
            'max'      => 2,
            'min'      => 0,
            'step'     => 0.1,
            'unit'     => 'MB',
            'after'    => $tip_ico . '填 0 关闭图标上传。',
            'default'  => 0.6,
            'class'    => 'compact min',
        ),
        array(
            'id'       => 'icon_img_size',
            'type'     => 'spinner',
            'title'    => '软件图标大小',
            'subtitle' => '最大值限制',
            'max'      => 2,
            'min'      => 0,
            'step'     => 0.1,
            'unit'     => 'MB',
            'after'    => $tip_ico . '填 0 关闭图标上传。',
            'default'  => 0.6,
            'class'    => 'compact min',
        ),
        array(
            'id'       => 'screenshot_img_size',
            'type'     => 'spinner',
            'title'    => '软件截图大小',
            'subtitle' => '最大值限制',
            'max'      => 10,
            'min'      => 0,
            'step'     => 0.2,
            'unit'     => 'MB',
            'after'    => $tip_ico . '填 0 关闭图片上传。',
            'default'  => 0.6,
            'class'    => 'compact min',
        ),
        array(
            'id'       => 'posts_img_size',
            'type'     => 'spinner',
            'title'    => '正文图片大小',
            'subtitle' => '所有类型的正文图片最大值',
            'max'      => 10,
            'min'      => 0,
            'step'     => 0.2,
            'unit'     => 'MB',
            'after'    => $tip_ico . '填 0 关闭图片上传。',
            'default'  => 1,
            'class'    => 'compact min',
        ),
        array(
            'id'      => 'audit_edit',
            'type'    => 'switcher',
            'title'   => '编辑后需审核',
            'default' => true,
            'label'   => '开启后，已经发布的内容编辑后需重新审核。',
            'after'   => '付费发布的内容修改一样需要审核，除非再次付费。',
        ),
        array(
            'id'         => 'again_edit_rebate',
            'type'       => 'number',
            'title'      => '再次付费折扣',
            'unit'       => '%',
            'default'    => 80,
            'after'      => '填 100 则不折扣；填 0 则直接发布，不需要审核。（在“销售”价格基础上折扣）',
            'class'      => 'compact min',
            'dependency' => array('audit_edit', '==', true)
        ),
        array(
            'id'         => 'audit_edit_tips',
            'type'       => 'textarea',
            'title'      => '审核提示',
            'default'    => '此内容编辑后需重新审核。',
            'class'      => 'compact min',
            'dependency' => array('audit_edit', '==', true)
        ),
        array(
            'id'         => 'again_pay_tips',
            'type'       => 'textarea',
            'title'      => '再次付费提示',
            'default'    => '编辑将产生费用，请确认内容符合法律法规，提交后平台有权审核、删除且不退款。',
            'class'      => 'compact min',
            'dependency' => array('audit_edit|again_edit_rebate', '==|!=', 'true|0')
        ),
        array(
            'id'         => 'again_nopay_tips',
            'type'       => 'textarea',
            'title'      => '不付费提示',
            'default'    => '再编辑不产生费用，请确认内容符合法律法规，提交后平台有权审核、删除且不退款。',
            'class'      => 'compact min',
            'dependency' => array('audit_edit|again_edit_rebate', '==|==', 'true|0')
        ),
        array(
            'id'         => 'again_notice_admin',
            'type'       => 'switcher',
            'title'      => '再编辑通知管理员',
            'label'      => '开启后，文章再次编辑且不需要审核，管理员会收到通知。',
            'default'    => false,
            'class'      => 'compact min',
        ),
        array(
            'id'       => 'contribute_type',
            'type'     => 'select',
            'title'    => '可投稿类型',
            'subtitle' => '可排序',
            'chosen'   => true,
            'multiple' => true,
            'sortable' => true,
            'options'  => array(
                'post'  => '文章', 
                'sites' => '网址', 
                'app'   => 'APP', 
                'book'  => '书籍', 
            ),
            'placeholder' => '请选择投稿类型',
            'default'  => ['sites', 'post', 'app', 'book'],
        ),
        io_get_contribute_option('sites'),
        io_get_contribute_option('post'),
        io_get_contribute_option('app'),
        io_get_contribute_option('book'),
    )
);

function io_get_contribute_option($post_type)
{
    $posts_name = io_get_post_type_name_option($post_type);

    $posts_pay = array(
        'id'     => 'pay',
        'type'   => 'fieldset',
        'title'  => '',
        'fields' => array(
            array(
                'id'    => 'status',
                'type'  => 'switcher',
                'title' => '开启付费发布', 
                'label' => '开启后，用户可支付费用直接免审核发布 (管理员看不到按钮)。', 
            ),
            array(
                'id'         => 'prices',
                'type'       => 'repeater',
                'before'     => '价格，如果所有用户同价，只添加一项即可。',
                'class'      => 'horizontal',
                'fields'     => array(
                    array(
                        'id'      => 'level',
                        'type'    => 'select',
                        'title'   => '等级',
                        'options' => array(
                            'all'  => '游客', 
                            'user' => '登陆用户', 
                        ),
                    ),
                    array(
                        'id'      => 'pay_price',
                        'type'    => 'number',
                        'title'   => '销售', 
                        'unit'    => '元',
                        'min'     => 0,
                        'step'    => 0.1,
                        'default' => 10,
                    ),
                    array(
                        'id'      => 'price',
                        'type'    => 'number',
                        'title'   => '原价', 
                        'unit'    => '元',
                        'min'     => 0,
                        'step'    => 0.1,
                        'default' => 20,
                    ),
                ),
                'default'    => array(
                    array(
                        'level'     => 'all',
                        'pay_price' => 10,
                        'price'     => 20,
                    ),
                ),
                'dependency' => array('status', '==', true)
            ),
            array(
                'id'         => 'title',
                'type'       => 'text',
                'title'      => '商品名称',
                'class'      => 'compact min',
                'desc'       => '购买时显示的商品名称',
                'default'    => '付费发布' . $posts_name,
                'dependency' => array('status', '==', true)
            ),
            array(
                'id'         => 'btn_text',
                'type'       => 'text',
                'title'      => '按钮名称',
                'class'      => 'compact min',
                'default'    => '支付并发布',
                'dependency' => array('status', '==', true)
            ),
            array(
                'id'         => 'stmt',
                'type'       => 'textarea',
                'title'      => '声明',
                'class'      => 'compact min',
                'default'    => '请确保内容符合法律法规，发布后平台有权审核、删除且不退款，所产生的责任责任由您承担。',
                'dependency' => array('status', '==', true)
            ),
        ),
    );



    $auto_category   = array(
        'id'          => 'auto_category',
        'type'        => 'select',
        'title'       => '免审核投稿分类', 
        'after'       => '不审核直接发布到指定分类，如果设置此项，前台投稿页的分类选择将失效。', 
        'placeholder' => '选择分类', 
        'options'     => 'categories',
        'class'       => 'compact',
        'dependency'  => array('is_publish', '==', true)
    );
    $cat_in          = array(
        'id'          => 'cat_in',
        'type'        => 'select',
        'title'       => $posts_name . '可投稿分类',
        'chosen'      => true,
        'multiple'    => true,
        'placeholder' => '选择分类',
        'options'     => 'categories',
        'before'      => '留空则为所有分类', 
        'class'       => 'compact min',
    );
    $taxonomy        = posts_to_cat($post_type);
    $title_limit_min = 5;
    $types           = array();
    $desc_limit      = array();
    if (in_array($post_type, array('sites', 'app', 'book'))) {
        $auto_category['query_args'] = array(
            'taxonomy' => $taxonomy,
        );

        $cat_in['query_args'] = array(
            'taxonomy' => $taxonomy,
        );

        $title_limit_min = 2;

        $types = array(
            'id'          => 'types',
            'type'        => 'select',
            'title'       => '可投稿类型',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'placeholder' => '选择类型',
            'options'     => array(
                'sites'  => '网址',
                'wechat' => '公众号',
            ),
            'class'       => 'compact min',
            'after'       => '留空则为“网址”。',
        );
        if ($post_type == 'book') {
            $types['options'] = array(
                'books'      => '图书', 
                'periodical' => '期刊', 
                'movie'      => '电影', 
                'tv'         => '电视剧', 
                'video'      => '小视频', 
            );
            $types['after']   = '留空则为“图书”。';
        } elseif ($post_type == 'app') {
            $types['options'] = array(
                'app'  => '软件', 
                'down' => '资源', 
            );
            $types['after']   = '留空则为“软件”。';
        }

        $desc_limit = array(
            'id'      => 'desc_limit',
            'type'    => 'spinner',
            'title'   => '简介字数最多',
            'min'     => 5,
            'max'     => 250,
            'step'    => 10,
            'default' => 150,
            'unit'    => '字',
            'class'   => 'compact min',
        );
    }


    return array(
        'id'         => $post_type . '_tg_config',
        'type'       => 'fieldset',
        'title'      => '「' . $posts_name . '」投稿选项',
        'fields'     => array(
            array(
                'id'    => 'is_publish',
                'type'  => 'switcher',
                'title' => '投稿直接发布',
                'label' => '投稿的“' . $posts_name . '”不需要审核直接发布',
            ),
            $auto_category,
            array(
                'id'          => 'title_limit',
                'type'        => 'dimensions',
                'title'       => '标题字数限制',
                'width_icon'  => '最少',
                'height_icon' => '最多',
                'units'       => array('字'),
            ),
            $desc_limit,
            array(
                'id'       => 'tag_limit',
                'type'     => 'spinner',
                'title'    => '标签数量限制',
                'subtitle' => '管理员不受限制',
                'min'      => 0,
                'unit'     => '个',
                'after'    => '填 0 不限制',
                'class'    => 'compact min',
            ),
            $cat_in,
            array(
                'id'         => 'cat_reverse',
                'type'       => 'switcher',
                'title'      => '反向分类',
                'label'      => '开启后，[可投稿分类]为排除分类。',
                'class'      => 'compact min',
                'default'    => false,
                'dependency' => array('cat_in', '!=', '', '', 'visible'),
            ),
            array(
                'id'       => 'cat_limit',
                'type'     => 'spinner',
                'title'    => '分类多选限制',
                'subtitle' => '管理员不受限制',
                'min'      => 0,
                'class'    => 'compact min',
                'default'  => 2,
                'after'    => '填 0 不限制',
            ),
            $types,
            $posts_pay
        ),
        'default'    => array(
            'is_publish'  => false,
            'tag_limit'   => 5,
            'title_limit' => array(
                'width'  => $title_limit_min,
                'height' => 30,
            ),
            'cat_reverse' => false,
            'cat_limit'   => 2,
        ),
        'dependency' => array('contribute_type', 'any', $post_type)
    );

}
