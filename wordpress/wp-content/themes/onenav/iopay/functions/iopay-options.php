<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-01 12:48:51
 * @LastEditors: iowen
 * @LastEditTime: 2024-05-13 23:48:25
 * @FilePath: /onenav/iopay/functions/iopay-options.php
 * @Description: 
 */

if (!is_admin()) {
    return;
}
$prefix = 'io_get_option';
$tip_ico = '<i class="fa fa-fw fa-info-circle"></i> ';
IOCF::createSection($prefix, array(
    'parent'      => 'io_pay',
    'title'       => '商城配置',
    'icon'        => 'fa fa-shopping-bag',
    'description' => '',
    'fields'      => array(
        array(
            'type'    => 'submessage',
            'style'   => 'info',
            'content' => '<h4>商城系统</h4>
            <p><b>注意：</b>使用支付功能请先配置好<a href="' . io_get_admin_iocf_url('商城设置/支付接口') . '">支付接口</a></p>',
        ),
        array(
            'id'      => 'pay_no_login',
            'type'    => 'switcher',
            'title'   => '免登陆购买',
            'default' => true,
            'label'   => '开启后如果用户未登录则使用浏览器缓存验证是否购买',
            'class'   => 'new',
            'after'   => '<br><br>'.$tip_ico.'如果开启缓存插件，请在【永不缓存（Cookies）】的类似选项里添加“iopay_”<br>'
        ),
        array(
            'id'         => 'pay_cookie_time',
            'type'       => 'spinner',
            'title'      => '有效时间',
            'desc'       => '免登陆购买的浏览器缓存有效时间',
            'max'        => 31,
            'min'        => 1,
            'step'       => 1,
            'unit'       => '天',
            'default'    => 15,
            'class'      => 'compact min',
            'dependency' => array('pay_no_login', '!=', ''),
        ),
        array(
            'id'        => 'pay_no_login_tips_multi',
            'type'      => 'group',
            'title'     => '未登录提醒',
            'fields'    => array(
                array(
                    'id'    => 'language',
                    'type'  => 'text',
                    'title' => '语言缩写',
                    'after' => '如：zh  en ，<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>'
                ),
                array(
                    'id'         => 'content',
                    'type'       => 'textarea',
                    'title'      => '内容',
                    'desc'       => '支持HTML代码，请注意代码规范及标签闭合',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                    'sanitize'   => false,
                    'class'      => 'compact min',
                ),
            ),
            'before'       => '需在基础设置开启多语言(默认语言放第一个)',
            'class'        => 'compact min',
            'button_title' => '添加语言',
            'accordion_title_prefix' => '语言：',
            'default'   => array(
                array(
                    'language' => 'zh',
                    'content'  => '您当前未登录！建议登陆后购买，可保存购买订单。',
                ),
                array(
                    'language' => 'en',
                    'content'  => 'You are not currently logged in! It is recommended to purchase after logging in, and the purchase order can be saved.',
                ),
            ),
            'dependency' => array('pay_no_login', '!=', ''),
        ),
        array(
            'id'      => 'pay_unit',
            'type'    => 'text',
            'title'   => '货币符号',
            'desc'    => '（例如 i币）',
            'class'   => 'mini-input',
            'default' => '￥',
        ),
        array(
            'id'      => 'pay_box_loc',
            'type'    => "radio",
            'title'   => '文章商品显示位置',
            'desc'    => "在文章页面中商品购买模块的显示位置",
            'options' => array(
                'before' => '内容顶部',
                'after'  => '内容底部',
                'null'   => '不显示',
            ),
            'after'   => '如果“不显示”则不会这正文显示商品购买模块，请谨慎操作',
            'default' => 'before',
        ),
        //array(
        //    'id'       => 'pay_show_pay_count',
        //    'type'     => 'switcher',
        //    'title'    => '销量显示',
        //    'subtitle' => '详情页显示销售数量',
        //    'default'  => true,
        //),
        //array(
        //    'id'      => 'down_limit',
        //    'type'    => 'spinner',
        //    'title'   => '下载限速',
        //    'desc'    => '仅对站内地址有效',
        //    'step'    => 1,
        //    'unit'    => 'kb',
        //    'default' => 256,
        //),
        array(
            'id'           => 'pay_service',
            'type'         => 'group',
            'title'        => '客户服务',
            'subtitle'     => '用户服务内容',
            'fields'       => array(
                array(
                    'id'      => 'value',
                    'type'    => 'text',
                    'title'   => '服务内容(多语言)',
                ),
                array(
                    'id'           => 'icon',
                    'type'         => 'icon',
                    'title'        => '自定义图标',
                    'button_title' => '选择图标',
                    'class'        => 'compact min',
                    'default'      => 'iconfont icon-point',
                ),
            ),
            'default' => array(
                array(
                    'value' => '免费更新',
                    'icon'  => 'iconfont icon-point',
                ),
                array(
                    'value' => '客服服务',
                    'icon'  => 'iconfont icon-point',
                )
            ),
            'button_title' => '添加属性',
        ),
        array(
            'id'        => 'pay_tips_multi',
            'type'      => 'group',
            'title'     => '收银台提示',
            'fields'    => array(
                array(
                    'id'    => 'language',
                    'type'  => 'text',
                    'title' => '语言缩写',
                    'after' => '如：zh  en ，<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>'
                ),
                array(
                    'id'         => 'content',
                    'type'       => 'textarea',
                    'title'      => '内容',
                    'desc'       => '支持HTML代码，请注意代码规范及标签闭合',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                    'sanitize'   => false,
                    'class'      => 'compact min',
                ),
            ),
            'before'       => '需在基础设置开启多语言(默认语言放第一个)',
            'class'        => 'compact min',
            'button_title' => '添加语言',
            'accordion_title_prefix' => '语言：',
            'default'   => array(
                array(
                    'language' => 'zh',
                    'content'  => '<i class="iconfont icon-tishi mr-2"></i>虚拟物品，不支持退款！',
                ),
                array(
                    'language' => 'en',
                    'content'  => '<i class="iconfont icon-tishi mr-2"></i>Virtual items, no refunds!',
                ),
            ),
        ),
    ),
));

// 自定义颜色加收费用
IOCF::createSection($prefix, array(
    'parent'      => 'io_pay',
    'title'       => '自助自动',
    'icon'        => 'fa fa-ad',
    'fields'      => array(
        array(
            'id'      => 'auto_ad_s',
            'type'    => 'switcher',
            'title'   => '自动广告位',
        ),
        array(
            'id'         => 'auto_ad_config',
            'type'       => 'fieldset',
            'title'      => '自动广告位参数配置',
            'fields'     => array(
                array(
                    'type'    => 'submessage',
                    'style'   => 'danger',
                    'content' => '<p>'.$tip_ico.'<b>警告：</b>位置不能为空</p>',
                    'dependency' => array('loc', '==', ''),
                ),
                array(
                    'id'      => 'loc',
                    'type'    => 'checkbox',
                    'title'   => '位置',
                    'inline'  => true,
                    'options' => array(
                        'home'  => '首页',
                        'page'  => '内页',
                    ),
                    'default' => array('home','page'),
                ),
                array(
                    'id'      => 'loc_home',
                    'type'    => 'radio',
                    'title'   => '首页位置',
                    'inline'  => true,
                    'options' => array(
                        'search'  => '搜索框下',
                        'content' => '内容上方',
                    ),
                    'default' => 'search',
                    'after'   => '注意：【搜索框下】需选择<a href="'.io_get_admin_iocf_url('搜索设置').'">“搜索布局样式”</a>选项的第2个布局',
                    'class'   => 'compact min',
                    'dependency' => array('loc', 'any', 'home'),
                ),
                array(
                    'id'      => 'column',
                    'type'    => 'number',
                    'title'   => '列数',
                    'default' => 6,
                    'unit'    => '个',
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'total',
                    'type'    => 'number',
                    'title'   => '总数',
                    'desc'    => '广告位数量最大值',
                    'default' => 12,
                    'unit'    => '个',
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'unit',
                    'type'    => 'radio',
                    'title'   => '单位',
                    'inline'  => true,
                    'options' => array(
                        'hour'  => '小时',
                        'day'   => '天',
                        'month' => '月',
                    ),
                    'default' => 'hour',
                    'class'   => 'compact min custom-unit',
                ),
                array(
                    'id'      => 'price_home',
                    'type'    => 'number',
                    'title'   => '首页单价',
                    'default' => 1,
                    'unit'    => '元/'.io_get_auto_ad_unit(),
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'price_page',
                    'type'    => 'number',
                    'title'   => '内页单价',
                    'default' => 0.6,
                    'unit'    => '元/'.io_get_auto_ad_unit(),
                    'class'   => 'compact min',
                ),
                array(
                    'id'      => 'price_all',
                    'type'    => 'number',
                    'title'   => '总单价',
                    'default' => 1.2,
                    'unit'    => '元/'.io_get_auto_ad_unit(),
                    'desc'    => '同时上架首页和内页的价格',
                    'class'   => 'compact min',
                ),
                array(
                    'id'         => 'product',
                    'type'       => 'group',
                    'title'      => '快速选择',
                    'fields'     => array(
                        array(
                            'id'      => 'time',
                            'type'    => 'number',
                            'title'   => '时间',
                            'default' => 100,
                            'unit'    => '小时',
                        ),
                        array(
                            'id'      => 'discount',
                            'type'    => 'number',
                            'title'   => '折扣',
                            'default' => 100,
                            'unit'    => '%',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'         => 'tag',
                            'type'       => 'textarea',
                            'title'      => '小标签',
                            'desc'       => '支持HTML，请注意代码规范',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                            'class'      => 'compact min',
                        ),
                    ),
                    'default' => array(
                        array(
                            'time'     => 4,
                            'discount' => 100,
                            'tag'       => '',
                        ),
                        array(
                            'time'     => 8,
                            'discount' => 100,
                            'tag'       => '',
                        ),
                        array(
                            'time'     => 24,
                            'discount' => 80,
                            'tag'       => '推荐',
                        ),
                        array(
                            'time'     => 48,
                            'discount' => 60,
                            'tag'       => '特惠',
                        ),
                    ),
                    'accordion_title_prefix' => '时间('.io_get_auto_ad_unit().')：',
                    'class'   => 'compact min',
                ),
                array(
                    'id'       => 'custom',
                    'type'     => 'switcher',
                    'title'    => '自定义时间',
                    'label'    => '允许用户手动输入时间',
                    'default'  => true,
                ),
                array(
                    'id'         => 'custom_limit',
                    'type'       => 'dimensions',
                    'title'      => '自定义时间限制',
                    'width_icon' =>'最少',
                    'height_icon'=>'最多',
                    'units'      => array( io_get_auto_ad_unit() ),
                    'default'    => array(
                        'width'  => 1,
                        'height' => 240,
                    ),
                    'class'      => 'compact min',
                    'dependency' => array('custom', '!=', ''),
                ),
                array(
                    'id'        => 'title_multi',
                    'type'      => 'group',
                    'title'     => '模块名称',
                    'fields'    => array(
                        array(
                            'id'    => 'language',
                            'type'  => 'text',
                            'title' => '语言缩写',
                            'after' => '如：zh  en ，<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>'
                        ),
                        array(
                            'id'         => 'content',
                            'type'       => 'textarea',
                            'title'      => '内容',
                            'desc'       => '支持HTML代码，请注意代码规范及标签闭合',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                            'sanitize'   => false,
                            'class'      => 'compact min',
                        ),
                    ),
                    'before'       => '需在基础设置开启多语言(默认语言放第一个)',
                    'class'        => 'compact min',
                    'button_title' => '添加语言',
                    'accordion_title_prefix' => '语言：',
                    'default'   => array(
                        array(
                            'language' => 'zh',
                            'content'  => '<i class="iconfont icon-hot mr-2"></i>热门',
                        ),
                        array(
                            'language' => 'en',
                            'content'  => '<i class="iconfont icon-hot mr-2"></i>Popular',
                        ),
                    ),
                ),
                array(
                    'id'        => 'btn_multi',
                    'type'      => 'group',
                    'title'     => '按钮',
                    'fields'    => array(
                        array(
                            'id'    => 'language',
                            'type'  => 'text',
                            'title' => '语言缩写',
                            'after' => '如：zh  en ，<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>'
                        ),
                        array(
                            'id'         => 'content',
                            'type'       => 'textarea',
                            'title'      => '内容',
                            'desc'       => '支持HTML代码，请注意代码规范及标签闭合',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                            'sanitize'   => false,
                            'class'      => 'compact min',
                        ),
                    ),
                    'before'       => '需在基础设置开启多语言(默认语言放第一个)',
                    'class'        => 'compact min',
                    'button_title' => '添加语言',
                    'accordion_title_prefix' => '语言：',
                    'default'   => array(
                        array(
                            'language' => 'zh',
                            'content'  => '<i class="iconfont icon-ad-copy mr-2"></i>立即入驻',
                        ),
                        array(
                            'language' => 'en',
                            'content'  => '<i class="iconfont icon-ad-copy mr-2"></i>join now',
                        ),
                    ),
                ),
                array(
                    'id'        => 'tips_multi',
                    'type'      => 'group',
                    'title'     => '收银台提示语',
                    'fields'    => array(
                        array(
                            'id'    => 'language',
                            'type'  => 'text',
                            'title' => '语言缩写',
                            'after' => '如：zh  en ，<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>'
                        ),
                        array(
                            'id'         => 'content',
                            'type'       => 'textarea',
                            'title'      => '内容',
                            'desc'       => '支持HTML代码，请注意代码规范及标签闭合',
                            'attributes' => array(
                                'rows' => 2,
                            ),
                            'sanitize'   => false,
                            'class'      => 'compact min',
                        ),
                    ),
                    'before'       => '需在基础设置开启多语言(默认语言放第一个)',
                    'class'        => 'compact min',
                    'button_title' => '添加语言',
                    'accordion_title_prefix' => '语言：',
                    'default'   => array(
                        array(
                            'language' => 'zh',
                            'content'  => '<i class="iconfont icon-statement mr-1"></i>请不要提交中华人民共和国法律所不允许的内容，发现后将立即删除，受不予退款！',
                        ),
                        array(
                            'language' => 'en',
                            'content'  => '<i class="iconfont icon-statement mr-1"></i>Please do not submit content that is not permitted by law, and will be removed immediately upon discovery, subject to non-refundable!',
                        ),
                    ),
                ),
                array(
                    'id'      => 'ajax',
                    'type'    => 'switcher',
                    'title'   => 'ajax加载',
                    'default' => true,
                    'desc'    => '开启选项解决开静态缓存导致新增失效的问题，但可能会导致原站爬虫检查不到反链。',
                ),
                array(
                    'id'      => 'check',
                    'type'    => 'switcher',
                    'title'   => '需审核',
                    'default' => false,
                ),
                array(
                    'id'        => 'check_tips_multi',
                    'type'      => 'group',
                    'title'     => '审核提示语',
                    'fields'    => array(
                        array(
                            'id'    => 'language',
                            'type'  => 'text',
                            'title' => '语言缩写',
                            'after' => '如：zh  en ，<a href="https://zh.wikipedia.org/wiki/ISO_639-1" target="_blank">各国语言缩写参考</a>'
                        ),
                        array(
                            'id'         => 'content',
                            'type'       => 'textarea',
                            'title'      => '内容',
                            'desc'       => '支持HTML代码，请注意代码规范及标签闭合',
                            'attributes' => array(
                                'rows' => 2,
                            ),
                            'sanitize'   => false,
                            'class'      => 'compact min',
                        ),
                    ),
                    'before'       => '需在基础设置开启多语言(默认语言放第一个)',
                    'class'        => 'compact min',
                    'button_title' => '添加语言',
                    'accordion_title_prefix' => '语言：',
                    'default'   => array(
                        array(
                            'language' => 'zh',
                            'content'  => '<i class="iconfont icon-tishi mr-1"></i>支付后内容需审核，如内容违反<a href="'.home_url().'" target="_blank">规定</a>，将直接删除，且不退款！',
                        ),
                        array(
                            'language' => 'en',
                            'content'  => '<i class="iconfont icon-tishi mr-1"></i>The content needs to be reviewed after payment. If the content violates the <a href="'.home_url().'" target="_blank">regulations</a>, it will be deleted directly and no refund will be given!',
                        ),
                    ),
                    'dependency' => array('check', '==', 'true'),
                ),
            ),
            'class'      => 'compact min',
            'dependency' => array('auto_ad_s', '==', 'true'),
        ),

    ),
));

IOCF::createSection($prefix, array(
    'parent' => 'io_pay',
    'title'  => '支付接口',
    'icon'   => 'fa fa-credit-card',
    'fields' => array(
        array(
            'type'    => 'submessage',
            'style'   => 'danger',
            'content' => '<p>'.$tip_ico.'<b>警告：</b>主题仅提供API接入服务，收款平台的可靠性请自行斟酌！</p>'
        ),
        array(
            'id'      => 'pay_wechat_sdk',
            'type'    => 'select',
            'title'   => '微信支付接口',
            'options' => array(
                'null'            => '关闭微信收款',
                'official_wechat' => '微信官方',
                'xunhupay_v3'     => '虎皮椒V3-微信',
                'xunhupay_v4'     => '迅虎PAY-微信',
                'senhuo'          => '站长支付-微信',
                'epay'            => '易支付-微信',
                'payjs'           => 'PAYJS-微信',
            ),
            'default' => 'null',
        ),
        array(
            'id'      => 'pay_alipay_sdk',
            'type'    => 'select',
            'title'   => '支付宝接口',
            'options' => array(
                'null'            => '关闭支付宝收款',
                'official_alipay' => '支付宝企业支付/当面付',
                'xunhupay_v3'     => '虎皮椒V3-支付宝',
                'xunhupay_v4'     => '迅虎PAY-支付宝',
                'senhuo'          => '站长支付-支付宝',
                'epay'            => '易支付-支付宝',
                'payjs'           => 'PAYJS-支付宝',
            ),
            'default' => 'null',
            'class'   => 'compact min',
        ),
        array(
            'id'         => 'official_alipay',
            'type'       => 'accordion',
            'title'      => '支付宝官方',
            'accordions' => array(
                array(
                    'title'  => '支付宝官方',
                    'fields' => array(
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p><b>回调地址：</b><code>' . home_url('/') . '</code><br>
                            同时填写了企业支付以及当面付参数，则优先使用当面付</p>',
                        ),
                        array(
                            'id'         => 'publickey',
                            'type'       => 'textarea',
                            'title'      => '支付宝公钥(必填)',
                            'attributes' => array(
                                'rows' => 4,
                            ),
                            'sanitize'   => false,
                        ),
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p>支付宝当面付：个人可申请<br>
                            当面付申请地址：<a target="_blank" href="https://b.alipay.com/signing/productDetailV2.htm?productId=I1011000290000001003">立即申请</a></p>
                            <p><b>注意：</b>如需接入此方式请填写下方参数，反之请留空</p>',
                        ),
                        array(
                            'id'      => 'appid',
                            'type'    => 'text',
                            'title'   => '当面付：APPID',
                        ),
                        array(
                            'id'         => 'privatekey',
                            'type'       => 'textarea',
                            'title'      => '当面付：应用私钥 privatekey',
                            'attributes' => array(
                                'rows' => 4,
                            ),
                            'sanitize'   => false,
                            'class'      => 'compact min',
                        ),
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p>支付宝企业支付</b>',
                        ),
                        array(
                            'id'      => 'web_appid',
                            'type'    => 'text',
                            'title'   => '网站应用：APPID',
                        ),
                        array(
                            'id'         => 'web_privatekey',
                            'type'       => 'textarea',
                            'title'      => '网站应用：应用私钥 appPrivateKey',
                            'class'      => 'compact min',
                            'attributes' => array(
                                'rows' => 4,
                            ),
                            'sanitize'   => false,
                        ),
                        array(
                            'id'      => 'h5',
                            'type'    => 'switcher',
                            'title'   => '开启H5支付',
                            'label'    => '移动端自动跳转到支付宝APP支付，需签约<b>手机网站支付</b>',
                            'default' => false,
                            'class'   => 'compact min',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'official_wechat',
            'type'       => 'accordion',
            'title'      => '微信官方',
            'accordions' => array(
                array(
                    'title'  => '微信企业支付',
                    'fields' => array(
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p><b>native回调地址：</b><code>' . get_theme_file_uri('/iopay/notify/weixin/return.php') . '</code><br>
                            <b>JS接口安全域名、授权回调域：</b><code>' . preg_replace('/^(?:https?:\/\/)?([^\/]+).*$/im', '$1', home_url()) . '</code></p>',
                        ),
                        array(
                            'id'      => 'mchid',
                            'type'    => 'text',
                            'title'   => '商户号(MCHID)',
                        ),
                        array(
                            'id'      => 'appid',
                            'type'    => 'text',
                            'title'   => '授权绑定的AppID',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'key',
                            'type'    => 'text',
                            'title'   => '支付API密钥(V2)',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'h5',
                            'type'    => 'switcher',
                            'title'   => 'H5支付',
                            'label'   => '移动端跳转到微信APP支付(需开通 H5支付)',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'js_api',
                            'type'    => 'switcher',
                            'title'   => 'JSAPI支付',
                            'label'   => '微信APP内直接发起支付',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'         => 'appsecret',
                            'type'       => 'text',
                            'title'      => '公众号AppSecret',
                            'class'      => 'compact min',
                            'desc'       => '授权绑定的公众号或小程序的AppSecret<br>'
                            .$tip_ico.'如果此处留空，则会获取<a href="' . io_get_admin_iocf_url('用户安全/社交登录') . '">社交登录(微信公众号登录)</a>的APPID和AppSecret',
                            'dependency' => array('js_api', '!=', ''),
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'xunhupay_v3',
            'type'       => 'accordion',
            'title'      => '虎皮椒V3',
            'accordions' => array(
                array(
                    'title'  => '虎皮椒V3',
                    'fields' => array(
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p>虎皮椒是迅虎网络旗下的支付产品，无需营业执照、无需企业，申请简单。适合个人站长申请，有一定的费用</p>
                            <li>开通地址：<a target="_blank" href="https://admin.xunhupay.com/sign-up/12207.html">点击跳转</a></li>',
                        ),
                        array(
                            'id'      => 'wechat_appid',
                            'type'    => 'text',
                            'title'   => '微信：APPID',
                            'default' => '',
                        ),
                        array(
                            'id'      => 'wechat_appsecret',
                            'type'    => 'text',
                            'title'   => '微信：APPSECRET',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'    => 'alipay_appid',
                            'type'  => 'text',
                            'title' => '支付宝：APPID',
                        ),
                        array(
                            'id'      => 'alipay_appsecret',
                            'type'    => 'text',
                            'title'   => '支付宝：APPSECRET',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'api_url',
                            'type'    => 'text',
                            'title'   => '自定义API网关网址',
                            'default' => 'https://api.xunhupay.com/payment/do.html',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'xunhupay_v4',
            'type'       => 'accordion',
            'title'      => '迅虎PAY',
            'accordions' => array(
                array(
                    'title'  => '迅虎PAY（虎皮椒V4）',
                    'fields' => array(
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p>迅虎PAY又叫虎皮椒V4，是迅虎网络打造的一个全新的个人收款平台，申请简单，适合个人站长</p>
                            <li><b>注意：</b>“迅虎PAY”（虎皮椒V4）已经关闭新用户注册，新用户请使用“虎皮椒V3”</li>
                            <li>开通地址：<a target="_blank" href="https://pay.xunhuweb.com">点击跳转</a></li>',
                        ),
                        array(
                            'id'      => 'mchid',
                            'type'    => 'text',
                            'title'   => '商户号 mchid',
                        ),
                        array(
                            'id'      => 'key',
                            'type'    => 'text',
                            'title'   => 'API密钥 key',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'alipay_v2',
                            'type'    => 'switcher',
                            'title'   => '支付宝V2.0',
                            'default' => false,
                            'label'   => '如开通的支付宝接口为2.0版本，需开启此项',
                        ),
                        array(
                            'id'      => 'api_url',
                            'type'    => 'text',
                            'title'   => '自定义API网关网址',
                            'default' => 'https://admin.xunhuweb.com',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'senhuo',
            'type'       => 'accordion',
            'title'      => '站长支付',
            'accordions' => array(
                array(
                    'title'  => '站长支付',
                    'fields' => array(
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p>站长支付，申请简单，适合个人站长</p>
                            <li>开通地址：<a target="_blank" href="https://pay.senhuo.cn/">点击跳转</a></li>',
                        ),
                        array(
                            'id'      => 'appid',
                            'type'    => 'text',
                            'title'   => 'APPID',
                        ),
                        array(
                            'id'      => 'appkey',
                            'type'    => 'text',
                            'title'   => 'APPKEY',
                            'class'   => 'compact min',
                        )
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'epay',
            'type'       => 'accordion',
            'title'      => '易支付',
            'accordions' => array(
                array(
                    'title'  => '易支付',
                    'fields' => array(
                        array(
                            'id'      => 'apiurl',
                            'type'    => 'text',
                            'title'   => 'API接口网址',
                            'desc'    => '请填写完整的接口网址，例如：<code>https://pay.v8jisu.cn/</code>',
                        ),
                        array(
                            'id'      => 'pid',
                            'type'    => 'text',
                            'title'   => '商户ID',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'key',
                            'type'    => 'text',
                            'title'   => '商户秘钥',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'qrcode',
                            'type'    => 'switcher',
                            'title'   => 'PC端扫码支付',
                            'default' => false,
                            'label'    => '提示 “收款码请求失败” ，则需关闭此功能',
                        ),
                        array(
                            'id'      => 'type',
                            'type'    => 'button_set',
                            'title'   => '请求接口路由',
                            'options' =>  array(
                                'mapi'      => 'mapi.php',
                                'apisubmit' => 'pay/apisubmit',
                            ),
                            'default' => 'mapi',
                            'desc'    => '源支付为 pay/apisubmit',
                            'class'   => 'compact min',
                            'dependency' => array('qrcode', '==', 'true'),
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'payjs',
            'type'       => 'accordion',
            'title'      => 'PAYJS',
            'accordions' => array(
                array(
                    'title'  => 'PAYJS',
                    'fields' => array(
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '开通地址：<a target="_blank" href="https://payjs.cn">点击跳转</a>',
                        ),
                        array(
                            'id'      => 'mchid',
                            'type'    => 'text',
                            'title'   => '商户号 mchid',
                        ),
                        array(
                            'id'      => 'key',
                            'type'    => 'text',
                            'title'   => 'API密钥 key',
                            'class'   => 'compact min',
                        ),
                    ),
                ),
            ),
        ),
        array(
            'id'         => 'paypal',
            'type'       => 'accordion',
            'title'      => 'PayPal（官方接口）',
            'accordions' => array(
                array(
                    'title'  => 'PayPal',
                    'fields' => array(
                        array(
                            'type'    => 'submessage',
                            'style'   => 'info',
                            'content' => '<p>PayPal近期调整的政策，境内用户没法对境内用户进行收款。（待测试，不要使用）</p>
                            <li>开通地址：<a target="_blank" href="https://www.paypal.com/businessmanage/credentials/apiAccess">点击跳转</a></li>',
                        ),
                        array(
                            'id'      => 'user',
                            'type'    => 'text',
                            'title'   => 'API帐号',
                        ),
                        array(
                            'id'      => 'pass',
                            'type'    => 'text',
                            'title'   => 'API密码',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'signature',
                            'type'    => 'text',
                            'title'   => 'API签名',
                            'class'   => 'compact min',
                        ),
                        array(
                            'id'      => 'rate',
                            'type'    => 'spinner',
                            'title'   => '汇率',
                            'step'    => 0.01,
                            'default' => 5,
                            'desc'    => '填5表示1美元=5元',
                            'class'   => 'compact min',
                        ),
                    ),
                ),
            ),
        ),
    ),
));


function io_get_auto_ad_unit(){
    $unit = array(
        'hour'  => '小时',
        'day'   => '天',
        'month' => '月',
    );
    return $unit[io_get_option('auto_ad_config','hour','unit')];
}