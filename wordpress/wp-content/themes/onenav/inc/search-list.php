<?php
/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } 
/**
 * 搜索工具搜索引擎列表
 * 修改后如果前台不显示选单，请清空浏览器缓存
 * 如果还是不显示，请检测对应 default 对应的 id 是否存在
 * id 必须唯一
 * 修改默认项请设置“常用”里 default 的值
 * 排序请调整先后顺序
 * 暂只支持添加“keyname=key”结构的搜索引擎，如：https://www.baidu.com/s?wd=
 */
$search_list = apply_filters('io_search_list_filters', array(
    array(
        'id'      => 'group-a',
        'name'    => '常用',
        'default' => 'type-baidu',
        'list'    => array(
            array(
                'name'        => '百度',
                'placeholder' => '百度一下',
                'id'          => 'type-baidu',
                'url'         => 'https://www.baidu.com/s?wd=%s%',
            ),
            array(
                'name'        => 'Google',
                'placeholder' => '谷歌两下',
                'id'          => 'type-google',
                'url'         => 'https://www.google.com/search?q=%s%',
            ),
            array(
                'name'        => '站内',
                'placeholder' => '站内搜索',
                'id'          => 'type-zhannei',
                'url'         => esc_url(home_url()) . '/?post_type=' . (io_get_search_types()[0]) . '&s=%s%',
            ),
            array(
                'name'        => '淘宝',
                'placeholder' => '淘宝',
                'id'          => 'type-taobao',
                'url'         => 'https://s.taobao.com/search?q=%s%',
            ),
            array(
                'name'        => 'Bing',
                'placeholder' => '微软Bing搜索',
                'id'          => 'type-bing',
                'url'         => 'https://cn.bing.com/search?q=%s%',
            ),
        )
    ),
    array(
        'id'      => 'group-b',
        'name'    => '搜索',
        'default' => 'type-baidu1',
        'list'    => array(
            array(
                'name'        => '百度',
                'placeholder' => '百度一下',
                'id'          => 'type-baidu1',
                'url'         => 'https://www.baidu.com/s?wd=%s%',
            ),
            array(
                'name'        => 'Google',
                'placeholder' => '谷歌两下',
                'id'          => 'type-google1',
                'url'         => 'https://www.google.com/search?q=%s%',
            ),
            array(
                'name'        => '360',
                'placeholder' => '360好搜',
                'id'          => 'type-360',
                'url'         => 'https://www.so.com/s?q=%s%',
            ),
            array(
                'name'        => '搜狗',
                'placeholder' => '搜狗搜索',
                'id'          => 'type-sogo',
                'url'         => 'https://www.sogou.com/web?query=%s%',
            ),
            array(
                'name'        => 'Bing',
                'placeholder' => '微软Bing搜索',
                'id'          => 'type-bing1',
                'url'         => 'https://cn.bing.com/search?q=%s%',
            ),
            array(
                'name'        => '神马',
                'placeholder' => 'UC移动端搜索',
                'id'          => 'type-sm',
                'url'         => 'https://yz.m.sm.cn/s?q=%s%',
            ),
        )
    ),
    array(
        'id'      => 'group-c',
        'name'    => '工具',
        'default' => 'type-br',
        'list'    => array(
            array(
                'name'        => '权重查询',
                'placeholder' => '请输入网址(不带https://)',
                'id'          => 'type-br',
                'url'         => 'https://seo.5118.com/%s%?t=ydm',
            ),
            array(
                'name'        => '友链检测',
                'placeholder' => '请输入网址(不带https://)',
                'id'          => 'type-links',
                'url'         => 'https://ahrefs.5118.com/%s%?t=ydm',
            ),
            array(
                'name'        => '备案查询',
                'placeholder' => '请输入网址(不带https://)',
                'id'          => 'type-icp',
                'url'         => 'https://icp.5118.com/domain/%s%?t=ydm',
            ),
            array(
                'name'        => 'SEO查询',
                'placeholder' => '请输入网址(不带https://)',
                'id'          => 'type-seo',
                'url'         => 'https://seo.5118.com/%s%?t=ydm',
            ),
            array(
                'name'        => '关键词挖掘',
                'placeholder' => '请输入关键词',
                'id'          => 'type-ciku',
                'url'         => 'https://www.5118.com/seo/newrelated/%s%?t=ydm',
            ),
            array(
                'name'        => '素材搜索',
                'placeholder' => '请输入关键词',
                'id'          => 'type-51key',
                'url'         => 'https://so.5118.com/all/%s%?t=ydm',
            ),
            array(
                'name'        => '大数据词云',
                'placeholder' => '请输入关键词',
                'id'          => 'type-51kt',
                'url'         => 'https://www.kt1.com/wordfrequency/yuliao/%s%?t=ydm',
            ),
        )
    ),
    array(
        'id'      => 'group-d',
        'name'    => '社区',
        'default' => 'type-zhihu',
        'list'    => array(
            array(
                'name'        => '知乎',
                'placeholder' => '知乎',
                'id'          => 'type-zhihu',
                'url'         => 'https://www.zhihu.com/search?type=content&q=%s%',
            ),
            array(
                'name'        => '微信',
                'placeholder' => '微信',
                'id'          => 'type-wechat',
                'url'         => 'https://weixin.sogou.com/weixin?type=2&query=%s%',
            ),
            array(
                'name'        => '微博',
                'placeholder' => '微博',
                'id'          => 'type-weibo',
                'url'         => 'https://s.weibo.com/weibo/%s%',
            ),
            array(
                'name'        => '豆瓣',
                'placeholder' => '豆瓣',
                'id'          => 'type-douban',
                'url'         => 'https://www.douban.com/search?q=%s%',
            ),
            array(
                'name'        => '搜外问答',
                'placeholder' => 'SEO问答社区',
                'id'          => 'type-why',
                'url'         => 'https://ask.seowhy.com/search/?q=%s%',
            ),
        )
    ),
    array(
        'id'      => 'group-e',
        'name'    => '生活',
        'default' => 'type-taobao1',
        'list'    => array(
            array(
                'name'        => '淘宝',
                'placeholder' => '淘宝',
                'id'          => 'type-taobao1',
                'url'         => 'https://s.taobao.com/search?q=%s%',
            ),
            array(
                'name'        => '京东',
                'placeholder' => '京东',
                'id'          => 'type-jd',
                'url'         => 'https://search.jd.com/Search?keyword=%s%',
            ),
            array(
                'name'        => '下厨房',
                'placeholder' => '下厨房',
                'id'          => 'type-xiachufang',
                'url'         => 'https://www.xiachufang.com/search/?keyword=%s%',
            ),
            array(
                'name'        => '香哈菜谱',
                'placeholder' => '香哈菜谱',
                'id'          => 'type-xiangha',
                'url'         => 'https://www.xiangha.com/so/?q=caipu&s=%s%',
            ),
            array(
                'name'        => '12306',
                'placeholder' => '12306',
                'id'          => 'type-12306',
                'url'         => 'https://www.12306.cn/?%s%',
            ),
            array(
                'name'        => '快递100',
                'placeholder' => '快递100',
                'id'          => 'type-kd100',
                'url'         => 'https://www.kuaidi100.com/?%s%',
            ),
            array(
                'name'        => '去哪儿',
                'placeholder' => '去哪儿',
                'id'          => 'type-qunar',
                'url'         => 'https://www.qunar.com/?%s%',
            ),
        )
    ),

));
