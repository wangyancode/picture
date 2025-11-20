<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-12 17:35:58
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-25 19:18:46
 * @FilePath: /onenav/inc/functions/io-title.php
 * @Description: 
 */

/**
 * 获取SEO配置，根据TDK配置返回对应值
 * 
 * @param mixed $loc 位置 title、desc、key
 * @param mixed $type 类型 main 正文页、term 归档页
 * @return string
 */
function io_analyze_tdk_config($loc, $type)
{
    if ('main' === $type) {
        $post_type = get_post_type();
        if (!in_array($post_type, array('post', 'sites', 'app', 'book'))) {
            return '';
        }

        $id      = get_the_ID();
        $c_title = get_post_meta($id, '_seo_title', true);
        $c_keys  = get_post_meta($id, '_seo_metakey', true);
        $c_desc  = get_post_meta($id, '_seo_desc', true);
    } else {
        $term      = get_queried_object();
        $post_type = get_post_types_by_taxonomy($term->taxonomy);

        $id      = $term->term_id;
        $meta    = get_term_meta($id, 'term_io_seo', true);
        $c_title = isset($meta['seo_title']) ? $meta['seo_title'] : '';
        $c_keys  = isset($meta['seo_metakey']) ? $meta['seo_metakey'] : '';
        $c_desc  = isset($meta['seo_desc']) ? $meta['seo_desc'] : '';
    }

    $config = (array)io_get_option($post_type . '_seo_tdk');

    $custom = $config[$type . '_' . $loc . '_custom']; //自定义 包含 %title% %blogname% %cat%
    $make   = (array) (isset($config[$type . '_' . $loc . '_make']) ? $config[$type . '_' . $loc . '_make'] : $loc);
    $is_only = count($make) == 1;

    // 基础数据
    $linker      = 'title' === $loc ? io_get_option('tdk_linker', ' - ') : ',';
    $global_desc = $is_only ? _iol(io_get_option('seo_home_desc', '')) : '';
    $global_keys = $is_only ? _iol(io_get_option('seo_home_keywords', '')) : '';

    $text = [];
    foreach ($make as $v) { // 解析构成
        switch ($v) {
            case 'title':
                $_title = '';
                if ('app' === $post_type) {
                    $_title = get_post_meta($id, '_app_name', true);
                }
                $text[] = $c_title ?: ('main' === $type ? ($_title ?: get_the_title()) : single_term_title('', false));
                break;

            case 'content':
            case 'desc':
                $_desc = $c_desc;
                if (empty($_desc)) {
                    if ('main' === $type) {
                        $main_desc = '';
                        if ('desc' === $v) {
                            if ('sites' === $post_type) {
                                $main_desc = get_post_meta($id, '_sites_sescribe', true);
                            } elseif ('app' === $post_type) {
                                $main_desc = get_post_meta($id, '_app_sescribe', true);
                            } elseif ('book' === $post_type) {
                                $main_desc = get_post_meta($id, '_summary', true);
                            }
                        }
                        $_desc = $main_desc ? io_rtrim_mark($main_desc) : io_get_excerpt(io_get_option('seo_desc_count', 150), '_seo_desc', '');
                    } else {
                        $_desc = strip_tags(term_description());
                    }
                }
                $text[] = $_desc ?: $global_desc;
                break;

            case 'custom':
                $title    = 'main' === $type ? get_the_title() : single_term_title('', false);
                $blogname = get_bloginfo('name');
                $cat      = '';
                
                if ('main' === $type && strpos($custom, '%cat%') !== false) {
                    $taxonomy = posts_to_cat($post_type);
                    $category = get_the_terms($id, $taxonomy);
                    if ($category) {
                        $cat = $category[0]->name;
                    }
                }

                $text[] = str_replace(array('%title%', '%blogname%', '%cat%'), array($title, $blogname, $cat), $custom);
                break;

            case 'key':
                $text[] = $c_keys ?: $global_keys;
                break;

            case 'tag':
                if ('main' === $type) {
                    $taxonomy = posts_to_tag($post_type);
                    $tags     = get_the_terms($id, $taxonomy);
                    if ($tags) {
                        $tag = [];
                        foreach ($tags as $v) {
                            $tag[] = $v->name;
                        }
                        $text[] = implode($linker, $tag);
                    }
                }
                break;

            case 'cat':
                if ('main' === $type) {
                    $taxonomy = posts_to_cat($post_type);
                    $category = get_the_terms($id, $taxonomy);
                    if ($category) {
                        $cat = [];
                        foreach ($category as $v) {
                            $cat[] = $v->name;
                        }
                        $text[] = implode($linker, $cat);
                    }
                }
                break;

            case 'host':
            case 'url':
                if ('sites' === $post_type){
                    $url = get_post_meta($id, '_sites_link', true);
                    if('host'===$v){
                        $text[] = parse_url($url, PHP_URL_HOST);
                    }else{
                        $text[] = $url;
                    }
                }
                break;

            case 'journal':
                if ('book' === $post_type && 'periodical' === get_post_meta($id, '_book_type', true)) {
                    $text[] = io_get_journal_name(get_post_meta($id, '_journal', true));
                }
                break;

            case 'version':// '版本',
            case 'ad':// '是否有广告',
            case 'status':// '状态',
            case 'time':// '更新时间',
                if ('app' === $post_type) {
                    $appinfo = get_post_meta($id, 'app_down_list', true);
                    $_text   = [];
                    if ($appinfo && is_array($appinfo)) {
                        $appinfo = $appinfo[0];

                        if ('version' === $v) {
                            $_text[] = $appinfo['app_version'];
                        }
                        if ('ad' === $v) {
                            $_text[] = $appinfo['app_ad'] ? __('有广告', 'i_theme') : __('无广告', 'i_theme');
                        }
                        if ('status' === $v) {
                            if ($appinfo['app_status'] == "official") {
                                $_text[] = __('官方版', 'i_theme');
                            } elseif ($appinfo['app_status'] == "happy") {
                                $_text[] = __('开心版', 'i_theme');
                            } else {
                                $_text[] = isset($appinfo['status_custom'])? $appinfo['status_custom'] : '';
                            }
                        }
                        if ('time' === $v) {
                            $_text[] = $appinfo['app_date'];
                        }
                    }
                    $text[] = implode($linker, $_text);
                }
                break;

            default:
                break;
        }
    }

    // 去空
    $text = array_filter($text);

    /**
     * 过滤SEO标题、描述、关键词
     * 
     * @var array $text
     * @var mixed $loc 位置 title、desc、key
     * @var mixed $type 类型 main 正文页、term 归档页
     */
    $text = apply_filters('io_seo_tdk', $text, $loc, $type);

    return implode($linker, $text);
}
