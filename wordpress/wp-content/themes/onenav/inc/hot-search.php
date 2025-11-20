<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:55:58
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-09 13:38:05
 * @FilePath: /onenav/inc/hot-search.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }  
function hot_search($hot_data){
    $t= mt_rand();
    $type = isset($hot_data['hot_type'])?$hot_data['hot_type']:'api';
    switch ($type){
        case "weixin":
        case "api":
            $api        = "//ionews.top/api/get.php";
            $key        = iowenKey();
            $title      = $hot_data['name'];
            $ico        = $hot_data['ico'];
            $iframe     = $hot_data['is_iframe'];
            $rule_id    = $hot_data['rule_id'];
            include( get_theme_file_path('/templates/hot/hot-api.php') ); 
            break;
        case "rss":
        case "json":
            $custom_api = get_option( 'io_hot_search_list' )[$type.'_list'];
            $rule_id    = $hot_data['rule_id'];
            $custom_data= $custom_api[$rule_id-1];
            $api        = $custom_data['url'];
            $title      = $custom_data['name'];
            $subtitle   = $custom_data['subtitle'];
            $ico        = $hot_data['ico'];
            $iframe     = $hot_data['is_iframe'];

            $datas_node = $custom_data['datas'];
            $title_node = $custom_data['title'];
            $link_node  = $custom_data['link'];
            $hot_node   = $custom_data['hot'];

            $link_regular = isset($custom_data['link_regular'])?$custom_data['link_regular']:'';
            include( get_theme_file_path('/templates/hot/hot-json.php') ); 
            break;
        default:
            include( get_theme_file_path('/templates/hot/hot-api.php') ); 
    }
}

add_action('wp_ajax_nopriv_get_hot_data', 'io_get_hot_search_data');  
add_action('wp_ajax_get_hot_data', 'io_get_hot_search_data');
if(!function_exists('io_get_hot_search_data')) {
function io_get_hot_search_data(){
    $rule_id   = __post('id');// esc_sql($_REQUEST['id']);
    $type      = __post('type');//esc_sql($_REQUEST['type']);
    $cache_key = "io_free_hot_data_{$rule_id}_{$type}";

    $data = get_transient($cache_key);
    if ($data) {
        io_error($data, false, 10);
    }

    $_ua = array(
        '[dev]general information acquisition module - level 30 min, version:3.2',
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.164 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
    );
    $default_ua = array('userAgent'=>$_ua[wp_rand(0,2)]);
    $custom_api = get_option( 'io_hot_search_list' )[$type.'_list'];
    $custom_data= $custom_api[$rule_id-1];
    $api_url    = $custom_data['url'];
    $api_cache  = isset($custom_data['cache']) ? (int)$custom_data['cache'] : 60;
    $api_data   = isset($custom_data['request_data']) ? io_option_data_to_array($custom_data['request_data']) : '';
    $api_method = strtoupper(isset($custom_data['request_type']) ? $custom_data['request_type'] : 'get');
    $api_header = isset($custom_data['headers']) ? io_option_data_to_array($custom_data['headers'], $default_ua) : $default_ua;
    $api_cookie = isset($custom_data['cookies']) ? io_option_data_to_array($custom_data['cookies']) : '';

    
    $http = new Yurun\Util\HttpRequest;
    $http->headers($api_header);
    if($api_cookie)
        $http->cookies($api_cookie);

    $response = $http->send($api_url, $api_data, $api_method);
    if(!$response->success){
        io_error(array( "state"=>0,"code"=>$response->httpCode(),"data"=> $response->errno()));
    }
    if ('json' === $type) {
        $_data = $response->json(true);
    }else{
        $_data = json_decode(json_encode($response->xml()),true);
    }
    $_data = io_get_free_hot_data($_data, $custom_data['datas']);
    if (!empty($_data)) {
        $res = get_json_hot_data($_data, $custom_data);
        $res['rule_id'] = $rule_id;
        $res['rule_type'] = $type;
        set_transient($cache_key, $res, $api_cache * MINUTE_IN_SECONDS);
        io_error($res, false, 5);
    } else {
        io_error(array( "state"=>0, "code"=>202, "data"=> __("没有获取到内容。",'i_theme'), "res"=>$_data), false, 1);
    }
}
}

/**
 * 获取热搜数据
 * 格式化自定义源的数据
 * @param array $datas
 * @param array $config
 * @return array
 */
function get_json_hot_data($datas, $config)
{
    $lists  = array();
    $is_hot = isset($config['hot']) && $config['hot'];
    foreach ($datas as $index => $data) {
        $list = array(
            'index' => $index + 1,
            'title' => $data[$config['title']],
            'link'  => $data[$config['link']],
        );
        if ($is_hot) {
            $list['hot'] = $data[$config['hot']];
        }
        if (isset($config['link_regular']) && $config['link_regular']) {
            $list['link'] = str_replace("%s%", $data[$config['link']], $config['link_regular']);
        }
        $lists[] = $list;
    }
    $data = array(
        'state'      => 1,
        'title'      => $config['name'],
        'subtitle'   => $config['subtitle'],
        'type'       => $is_hot ? 'hot' : '',
        'data'       => $lists,
        'cache_time' => io_get_time()
    );
    return $data;
}

/**
 * 设置项数据转数组
 * 设置项键名 'key' 'value'
 * @param array $datas 
 * @param array $default 预设值
 * @return array
 */
function io_option_data_to_array($datas, $default = array()){
    $args = $default;
    foreach($datas as $data){
        $args[$data['key']] = $data['value'];
    }
    return $args;
}
/**
 * 获取自定义源的数据内容
 * @param array $datas 返回的json数据
 * @param string $nodes 数据节点路径
 * @return array
 */
function io_get_free_hot_data($datas, $nodes){
    $_nodes = explode('.', $nodes);
    $_data  = $datas;
    foreach($_nodes as $node){
        if(isset($_data[$node])){
            $_data = $_data[$node];
        }else{
            return [];
        }
    }
    return $_data;
}

// 热搜列表
if(!function_exists('all_topnew_list')){
	function all_topnew_list(){  
        $topsearch = array(
            array(
                'rule_id'       => '100000',
                'name'          => '百度热点',
                'description'   => '实时热点排行榜 https://top.baidu.com/buzz.php?p=top10',
                'ico'           => get_hot_ico('baidu'),
                'is_iframe'     => false,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100001',
                'name'          => '36氪人气榜',
                'description'   => '24小时人气阅读 https://www.36kr.com/hot-list/catalog',
                'ico'           => get_hot_ico('36kr'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100002',
                'name'          => '吾爱破解热度排行榜',
                'description'   => '吾爱破解帖子今日热度排行榜',
                'ico'           => get_hot_ico('wuaipojie'),
                'is_iframe'     => false,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100003',
                'name'          => '哔哩哔哩全站排行榜',
                'description'   => '哔哩哔哩全站排行榜 https://www.bilibili.com/v/popular/rank/all',
                'ico'           => get_hot_ico('bilibili'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100004',
                'name'          => '豆瓣小组',
                'description'   => '豆瓣小组讨论精选',
                'ico'           => get_hot_ico('douban'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100005',
                'name'          => '历史上的今天',
                'description'   => 'https://hao.360.com/histoday/',
                'ico'           => get_hot_ico('lssdjt'),
                'is_iframe'     => false,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100006',
                'name'          => '少数派热门文章',
                'description'   => 'https://sspai.com/tag/热门文章',
                'ico'           => get_hot_ico('sspai'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100007',
                'name'          => '微博热搜榜',
                'description'   => 'http://s.weibo.com/top/summary',
                'ico'           => get_hot_ico('weibo'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100015',
                'name'          => '知乎热度',
                'description'   => '知乎热度 https://www.zhihu.com/hot',
                'ico'           => get_hot_ico('zhihu'),
                'is_iframe'     => false,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100016',
                'name'          => '电商报7X24h快讯',
                'description'   => '7X24h快讯 https://www.dsb.cn/news',
                'ico'           => get_hot_ico('dsb'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100017',
                'name'          => '什么值得买',
                'description'   => '什么值得买精选好价 https://www.smzdm.com/jingxuan/',
                'ico'           => get_hot_ico('smzdm'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100018',
                'name'          => '豆瓣电影排行榜',
                'description'   => '豆瓣电影排行榜，豆瓣新片榜',
                'ico'           => get_hot_ico('douban'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100020',
                'name'          => '抖音热点榜',
                'description'   => '抖音热点榜 https://www.iesdouyin.com/share/billboard/',
                'ico'           => get_hot_ico('douyin'),
                'is_iframe'     => false,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100038',
                'name'          => '抖音今日热门视频',
                'description'   => '抖音今日热门视频 https://www.iesdouyin.com/share/billboard/',
                'ico'           => get_hot_ico('douyin'),
                'is_iframe'     => false,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100027',
                'name'          => 'IT之家资讯热榜',
                'description'   => 'IT之家资讯热榜 https://www.ithome.com',
                'ico'           => get_hot_ico('ithome'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100028',
                'name'          => 'IT之家最新资讯',
                'description'   => 'IT之家IT资讯最新 https://it.ithome.com/',
                'ico'           => get_hot_ico('ithome'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100029',
                'name'          => '百度贴吧热议榜',
                'description'   => '百度贴吧热议榜 http://tieba.baidu.com/hottopic/browse/topicList?res_type=1',
                'ico'           => get_hot_ico('baidu'),
                'is_iframe'     => false,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100030',
                'name'          => '虎扑步行街热帖',
                'description'   => '虎扑步行街热帖 https://bbs.hupu.com/all-gambia',
                'ico'           => get_hot_ico('hupu'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100036',
                'name'          => '哔哩哔哩综合热门',
                'description'   => '综合热门 https://www.bilibili.com/v/popular/all',
                'ico'           => get_hot_ico('bilibili'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
            array(
                'rule_id'       => '100037',
                'name'          => '哔哩哔哩入站必刷',
                'description'   => '入站必刷 https://www.bilibili.com/v/popular/history',
                'ico'           => get_hot_ico('bilibili'),
                'is_iframe'     => true,
                'hot_type'      => 'api'
            ),
        );
        $topsearch = apply_filters('io_topnew_list_filters', $topsearch);
        return $topsearch;
    }
}

function get_hot_ico($ico_name){
    return get_theme_file_uri('/assets/images/hotico/'.$ico_name.'.png');
}
//https://feed.mix.sina.com.cn/api/roll/get?pageid=153&lid=2509&k=&num=50&page=1&r=0.466137586907422&callback=jQuery11120153213739791773_1633014950125&_=1633014950127
//http://zhibo.sina.com.cn/api/zhibo/feed?callback=jQuery1112042151262348278307_1583126404217&page=1&page_size=20&zhibo_id=152&tag_id=0&dire=f&dpc=1&pagesize=20&id=1638768&type=0&_=1583126404220
//http://zhibo.sina.com.cn/api/zhibo/feed?callback=jQuery1112042151262348278307_1583126404217&page=1&page_size=20&zhibo_id=152&tag_id=0&dire=f&dpc=1&pagesize=20&id=1638768&type=0&_=1583126404221
//http://zhibo.sina.com.cn/api/zhibo/feed?page=1&page_size=20&zhibo_id=152&tag_id=0&dire=f&dpc=1&pagesize=20&_=1583119028651
//

function get_hot_list_option($data = array(), $show_btn = false){
    $default = array(
        'name'      => '',
        'hot_type'  => 'api',
        'rule_id'   => '',
        'icon'      => get_theme_file_uri('/assets/images/hot_ico.png'),
        'is_iframe' => false,
    );
    $data = wp_parse_args($data, $default);
    $fields = array(
        array(
            'id'    => 'name',
            'type'  => 'text',
            'title' => '名称',
            'default' => $data['name'],
        ),
        array(
            'id'      => 'hot_type',
            'type'    => 'button_set',
            'title'   => '类型',
            'options' => array(
                'json'   => 'JSON',
                'rss'    => 'RSS',
                'api'    => 'API',
                'weixin' => '微信 BETA',
            ),
            'default' => $data['hot_type'],
        ),
        array(
            'type'       => 'submessage',
            'style'      => 'success',
            'content'    => '<h4>前往“<a href="' . esc_url(add_query_arg('page', 'hot_search_settings', admin_url('options-general.php'))) . '" target="_blank">自定义热榜</a>”设置配置自定义热榜</h4>下方<b>热榜ID</b>为对应规则的序号，如1，6，8',
            'dependency' => array( 'hot_type', 'any', 'json,rss' )
        ),
        array(
            'id'         => 'rule_id',
            'type'       => 'text',
            'title'      => '热榜ID',
            'after'      => '⭐️ 如果选择 JSON 或者 RSS ，此项填“自定义热榜”对应类型的序号，如 JSON 类型的第一个，则填 1
            <br>⭐️ 如果选择 API ，请前往“<a target="_blank" href="https://www.ionews.top/list.html">ID列表</a>”查看ID
            <br>⭐️ 如果选择微信，请填微信公众号 biz，如：MzI5MjIwMjIwMA==（<a target="_blank" href="https://www.ionews.top/docs/wx_biz.html">获取方法</a>），并且完成“【微信文章列表】参数配置”',
            'default'    => $data['rule_id'],
        ),
        array(
            'id'      => 'ico',
            'type'    => 'upload',
            'title'   => 'LOGO，标志', 
            'library' => 'image',
            'after'   => '建议 30x30 ，留空则不显示。',
            'default' => $data['icon'],
        ),
        array(
            'id'      => 'is_iframe',
            'type'    => 'switcher',
            'title'   => 'iframe 加载',
            'label'   => '在页面内以 iframe 加载，如果目标站不支持，请关闭',
            'default' => $data['is_iframe'],
        ),
    );
    if($show_btn){
        $fields[] = array(
            'type'       => 'content',
            'content'    => '<div style="text-align:center;"><a id="hot-option" href="javascript:" class="button button-primary" data-id="hot_new" data-user="' . io_get_option('iowen_key', '') . '" style="padding:8px 80px"> 配 置 </a></div>',
            'dependency' => array(
                array('rule_id', '==', ''),
                array('hot_type', '==', 'api')
            )
        );
        $fields[] = array(
            'type'       => 'content',
            'content'    => '<div style="text-align:center;"><a id="hot-modify" href="javascript:" class="button button-primary" data-id="hot_new" data-user="' . io_get_option('iowen_key', '') . '" style="padding:8px 80px"> 修 改 </a></div>',
            'dependency' => array(
                array('rule_id', '!=', ''),
                array('hot_type', '==', 'api')
            )
        );
    }
    return $fields;
}