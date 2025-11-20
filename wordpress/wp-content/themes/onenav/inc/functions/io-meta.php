<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-02-28 00:39:46
 * @LastEditors: iowen
 * @LastEditTime: 2023-04-11 10:20:13
 * @FilePath: \onenav\inc\functions\io-meta.php
 * @Description: 
 */

/**
 * 获取用户权限设置项
 * 
 * @return mixed
 */
function get_user_purview_filters(){
    $price_type_dependency = array('buy_type', '==', 'annex', '', 'visible');
    if('book' == io_meta_box_post_type()){
        $price_type_dependency = array('_book_type', '==', '', 'all', '');
    }
    $user_purview_filters = apply_filters('io_post_user_purview_filters', array(
        array(
            'id'      => '_user_purview_level',
            'type'    => 'button_set',
            'title'   => __('查看权限', 'io_setting'),
            'options' => array(
                'all'   => '所有',
                'user'  => '登录用户',
                'buy'   => '付费',
                'admin' => '管理员',
            ),
            'default' => 'all',
            'desc'    => '高权用户会看到同权和低权的内容',
        ),
        array(
            'id'         => 'buy_option',
            'type'       => 'fieldset',
            'fields'     => array(
                array(
                    'type'       => 'submessage',
                    'style'      => 'danger',
                    'content'    => '<b>注意：</b>文章发布后“收费模式”和“价格类型”请不要修改，会影响已购用户的订单状态。',
                ),
                array(
                    'type'       => 'submessage',
                    'style'      => 'warning',
                    'content'    => '此类型不能使用“多价格”',
                    'dependency' => array('buy_type|price_type', '!=|==', 'annex|multi'),
                ),
                array(
                    'id'         => 'buy_type',
                    'type'       => 'radio',
                    'title'      => '内容收费模式',
                    'inline'     => true,
                    'options'    => array(
                        'view'  => __('内容查看', 'io_setting'),
                        'part'  => __('部分内容查看', 'io_setting'),
                        'annex' => __('附件下载', 'io_setting'),
                    ),
                    'after'      => '<li>内容查看：文章内容完全隐藏，支付后才能查看</li>
                                    <li>部分内容查看：通过<b><code>[hide_content type="buy"] 隐藏内容 [/hide_content]</code></b>短代码控制</li>
                                    <li>附件下载：---</li>',
                    'desc'       => '“部分内容查看”和“附件下载”可在文章中添加短代码 <code>[hide_content type="buy"] 隐藏内容 [/hide_content]</code>',
                ),
                array(
                    'id'      => 'limit',
                    'type'    => 'radio',
                    'title'   => '购买权限',
                    'inline'  => true,
                    'options' => array(
                        'all' => '所有人',
                    ),
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'pay_type',
                    'type'    => 'radio',
                    'title'   => '支付类型',
                    'inline'  => true,
                    'options' => array(
                        'money'  => __('货币', 'io_setting'),
                        //'points' => __('积分', 'io_setting'),
                    ),
                    'class'   => 'compact min',
                ),
                array(
                    'id'         => 'price_type',
                    'type'       => 'radio',
                    'title'      => '价格类型',
                    'inline'     => true,
                    'options'    => array(
                        'single' => __('总价', 'io_setting'),
                        'multi'  => __('多价格', 'io_setting'),
                    ),
                    'class'      => 'compact min',
                    'dependency' => $price_type_dependency,
                ),
                array(
                    'id'    => 'pay_title',
                    'type'  => 'text',
                    'title' => '商品名称',
                    'class' => 'compact min',
                    'desc'  => '留空则使用文章标题',
                ),
                array(
                    'id'    => 'pay_price',
                    'type'  => 'number',
                    'title' => '销售价格',
                    'class' => 'compact min',
                    'desc'  => '“多价格”模式时作为<b>[购买隐藏]</b>短代码的价格',
                ),
                array(
                    'id'    => 'price',
                    'type'  => 'number',
                    'title' => '原价',
                    'class' => 'compact min',
                    'desc'  => '“多价格”模式时作为<b>[购买隐藏]</b>短代码的价格',
                ),
                get_buy_annex_list()
            ),
            'default'    => array(
                'buy_type'   => 'view',
                'limit'      => 'all',
                'pay_type'   => 'money',
                'pay_price'  => 0,
                'price'      => 0,
                'price_type' => 'single',
            ),
            'class'      => 'compact min',
            'dependency' => array('_user_purview_level', '==', 'buy'),
        ),
    )
    );
    return $user_purview_filters;
}
/**
 * 附件列表
 * @return array
 */
function get_buy_annex_list(){
    $buy_annex_list = array(
        'id'         => 'annex_list',
        'type'       => 'group',
        'title'      => '附件列表',
        'fields'     => array(
            array(
                'id'      => 'index',
                'type'    => 'spinner',
                'title'   => '商品 ID',
                'min'     => 1,
                'max'     => 1000,
                'step'    => 1,
                'default' => 1,
                'after'   => 'ID 不能小于1，且必须唯一，也不要随意修改，因为购买凭证和此ID关联。',
                'dependency' => array('price_type', '==', 'multi', 'all'),
            ),
            array(
                'id'    => 'link',
                'type'  => 'upload',
                'title' => '资源地址',
                'class' => 'compact min',
            ),
            array(
                'id'    => 'name',
                'type'  => 'text',
                'title' => '名称',
                'class' => 'compact min',
                'desc'  => '留空则不显示',
            ),
            array(
                'id'         => 'pay_price',
                'type'       => 'number',
                'title'      => '销售价格',
                'default'    => 0,
                'class'      => 'compact min',
                'dependency' => array('price_type', '==', 'multi', 'all'),
            ),
            array(
                'id'         => 'price',
                'type'       => 'number',
                'title'      => '原价',
                'default'    => 0,
                'class'      => 'compact min',
                'dependency' => array('price_type', '==', 'multi', 'all'),
            ),
            array(
                'id'    => 'info',
                'type'  => 'text',
                'title' => '其他信息',
                'class' => 'compact min',
                'desc'  => '如：提取密码、解压密码等',
            ),
        ),
        'accordion_title_prefix' => '资源ID：',
        'class'      => 'compact min',
        'dependency' => array('buy_type', '==', 'annex'),
    );
    $post_type = io_meta_box_post_type();
    if ( in_array($post_type,array('app','book'))) {
        $buy_annex_list = array(
            'type'       => 'submessage',
            'style'      => 'warning',
            'content'    => '<b>注意：</b>附件请去【下载地址】添加。',
            'dependency' => array('buy_type', '==', 'annex'),
        );
    }

    return $buy_annex_list;
}

function io_meta_box_post_type(){
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    if ('' === $post_type && isset($_GET['post'])) {
        $post_id   = $_GET['post'];
        $post_type = get_post_type($post_id);
    }
    return $post_type;
}