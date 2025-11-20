<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-07-22 18:01:47
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 23:22:54
 * @FilePath: /onenav/inc/functions/io-footer.php
 * @Description: 
 */

/**
 * 输出页脚版权信息
 * @param mixed $class
 * @param mixed $simple
 * @param mixed $echo
 * @return string|void
 */
function io_copyright($class = '', $simple = false, $echo = true) {
    $html = '';
    $aff  = io_get_option('io_aff', true) ? '由<a href="https://www.iotheme.cn/?aff=' . io_get_option('io_id', '') . '" title="一为主题-精品wordpress主题" target="_blank" class="' . $class . '" rel="noopener"><strong> OneNav </strong></a>强力驱动&nbsp' : '';
    if (io_get_option('footer_copyright', '') && !$simple) {
        $html .= io_get_option('footer_copyright', '') . "&nbsp;&nbsp;" . $aff . io_get_option('footer_statistics', '');
    } else {
        $copy  = 'Copyright © ' . date('Y') . ' <a href="' . esc_url(home_url()) . '" title="' . get_bloginfo('name') . '" class="' . $class . '" rel="home">' . get_bloginfo('name') . '</a>&nbsp;';
        $icp   = io_get_option('icp', false) ? '<a href="https://beian.miit.gov.cn/" target="_blank" class="' . $class . '" rel="link noopener">' . io_get_option('icp', '') . '</a>&nbsp;' : '';
        $p_icp = '';
        if ($police_icp = io_get_option('police_icp', '')) {
            if (preg_match('/\d+/', $police_icp, $arr)) {
                $p_icp = ' <a href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=' . $arr[0] . '" target="_blank" class="' . $class . '" rel="noopener"><img style="margin-bottom: 3px;" src="' . get_theme_file_uri('/assets/images/gaba.png') . '" height="17" width="16"> ' . $police_icp . '</a>&nbsp;';
            }
        }
        $html .= $copy . $icp . $p_icp . $aff;
        $html .= io_get_option('footer_statistics', '');
        unset($copy, $icp, $p_icp, $aff);
    }
    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 输出页脚
 * @return void
 */
function io_footer() {
    $class = get_page_mode_class();

    $body = '';
    if (io_get_option('footer_layout', 'def') == "big") {
        $body .= io_big_footer();
    } else {
        $body .= '<div class="footer-copyright text-xs my-4">';
        $body .= io_get_home_links();
        $body .= io_copyright('', false, false);
        $body .= '</div>';
    }
    echo <<<HTML
    <footer class="main-footer footer-stick">
        <div class="switch-container container-footer{$class}">
            {$body}
        </div>
    </footer>
HTML;
}

/**
 * 输出大页脚
 * @return string
 */
function io_big_footer() {
    $socials = io_get_option('footer_social', '');

    $blog_name = get_bloginfo('name');

    $footer_t1 = '';
    if (!wp_is_mobile() || (wp_is_mobile() && io_get_option('footer_t1', true))) {
        $logo_light = io_get_option('logo_normal_light', '');
        $logo_dark  = io_get_option('logo_normal', '');

        $footer_t1 .= '<div class="col-12 col-md-4 mb-4 mb-md-0">';
        $footer_t1 .= '<a href="' . esc_url(home_url()) . '" class="logo-expanded footer-logo">';
        if (theme_mode() == "io-black-mode") {
            $footer_t1 .= '<img src="' . $logo_dark . '" height="40" switch-src="' . $logo_light . '" is-dark="true" alt="' . $blog_name . '">';
        } else {
            $footer_t1 .= '<img src="' . $logo_light . '" height="40" switch-src="' . $logo_dark . '" is-dark="false" alt="' . $blog_name . '">';
        }
        $footer_t1 .= '</a>';
        $footer_t1 .= '<div class="text-sm mt-4">' . io_get_option('footer_t1_code', '') . '</div>';
        
        if (is_array($socials) && count($socials) > 0) {
            $footer_t1 .= '<div class="footer-social mt-3">';
            foreach ($socials as $social) {
                if ($social['loc'] != "tools") {
                    $_url   = $social['url'];
                    $_title = $social['name'];
                    $_attr  = 'target="_blank" data-toggle="tooltip" data-placement="top"';
    
                    if ($social['type'] == 'img') {
                        $_url   = 'javascript:;';
                        $_title = '<img src="' . $social['url'] . '" height="100" width="100">';
                        $_attr  = 'data-toggle="tooltip" data-placement="top" data-html="true"';
                    } elseif (preg_match('|wpa.qq.com(.*)uin=([0-9]+)\&|', $_url, $matches)) {
                        $_url = IOTOOLS::qq_url($matches[2]);
                    }
                    $footer_t1 .= '<a class="social-btn bg-l" href="' . $_url . '" ' . $_attr . ' title="' . esc_attr($_title) . '" rel="external noopener nofollow"><i class="' . $social['ico'] . '"></i></a>';
                }
            }
            $footer_t1 .= '</div>';
        }

        $footer_t1 .= '</div>';
    }

    $footer_t2 = '';
    if (io_get_option('footer_t2_code', '')) {
        $footer_t2 .= '<p class="footer-links text-sm mb-3">' . io_get_option('footer_t2_code', '') . '</p>';
    }
    if (io_get_option('footer_t2_nav', '')) {
        $footer_t2 .= '<ul class="footer-nav-links d-flex justify-content-center justify-content-md-start text-sm mb-3 ">';
        $footer_t2 .= wp_nav_menu(array(
            'menu'       => io_get_option('footer_t2_nav', ''),
            'container'  => false,
            'items_wrap' => '%3$s',
            'echo'       => false
        ));
        $footer_t2 .= '</ul>';
    }
    $footer_t2 .= io_get_home_links();

    $footer_t3 = '';
    if (!wp_is_mobile() || (wp_is_mobile() && io_get_option('footer_t3', false))) {
        $footer_t3 .= '<div class="col-12 col-md-3 text-md-right mb-4 mb-md-0">';

        $f_imgs = io_get_option('footer_t3_img', '');
        if (is_array($f_imgs) && count($f_imgs) > 0) {
            foreach ($f_imgs as $f_img) {
                $footer_t3 .= '<div class="footer-mini-img text-center" data-toggle="tooltip" title="' . $f_img['text'] . '">';
                $footer_t3 .= '<div class="bg-l br-md p-1">';
                $footer_t3 .= '<img class=" " src="' . $f_img['image'] . '" alt="' . $f_img['text'] . $blog_name . '">';
                $footer_t3 .= '</div>';
                $footer_t3 .= '<span class="text-muted text-xs mt-2">' . $f_img['text'] . '</span>';
                $footer_t3 .= '</div>';
            }
        }

        $footer_t3 .= '</div>';
    }

    $copyright = io_copyright('', false, false);

    return <<<HTML
    <div class="footer row pt-5 text-center text-md-left">
        {$footer_t1}
        <div class="col-12 col-md-5 my-4 my-md-0"> 
            {$footer_t2}
        </div>
        {$footer_t3}
        <div class="footer-copyright m-3 text-xs">
            {$copyright}
        </div>
    </div>
HTML;
}


function io_footer_tools_right(){
    if(is_admin() || is_404() || is_io_login() || is_bookmark()){
        return;
    }
    $socials = io_get_option('footer_social','');
    ?>
    
    <div id="footer-tools" class="tools-right io-footer-tools d-flex flex-column">
        <a href="javascript:" class="btn-tools go-to-up go-up my-1" rel="go-up" style="display: none">
            <i class="iconfont icon-to-up"></i>
        </a>
        <?php  
        if(is_array($socials) && count($socials)>0){
            $index = 0;
            foreach($socials as $social){
                if ($social['loc']!="footer") {
                    if ($social['type']=='img') {
                        ?><a class="btn-tools custom-tool<?php echo $index ?> my-1 qr-img" href="javascript:;" data-toggle="tooltip" data-html="true" data-placement="left" title="<img src='<?php echo $social['url'] ?>' height='100' width='100'>">
                    <i class="<?php echo $social['ico'] ?>"></i>
                </a><?php
                    } else {
                        $url = $social['url'];
                        if(preg_match('|wpa.qq.com(.*)uin=([0-9]+)\&|',$url,$matches)){
                            $url = IOTOOLS::qq_url($matches[2]);
                        }
                        ?><a class="btn-tools custom-tool<?php echo $index ?> my-1" href="<?php echo $url ?>" target="_blank"  data-toggle="tooltip" data-placement="left" title="<?php echo $social['name'] ?>" rel="external noopener nofollow">
                    <i class="<?php echo $social['ico'] ?>"></i>
                </a><?php
                    }
                }
                $index++;
            }
        }
        ?>
        <?php if(io_get_option('weather',false) && io_get_option('weather_location','footer')=='footer'){ ?>
        <!-- 天气  -->
        <div class="btn-tools btn-weather weather my-1">
            <?php io_get_weather_widget() ?>
        </div>
        <!-- 天气 end -->
        <?php } ?>
        <?php if (io_get_option('theme_auto_mode', 'manual-theme')!='null'){?>
        <a href="javascript:" class="btn-tools switch-dark-mode my-1" data-toggle="tooltip" data-placement="left" title="<?php _e('夜间模式','i_theme') ?>">
            <i class="mode-ico iconfont icon-light"></i>
        </a>
        <?php } ?>
    </div>
    <?php
}
/**
 * 输出页脚左侧工具
 * @return void
 */
function io_footer_tools_left(){
    if(is_admin() || is_404() || is_io_login() || is_bookmark()){
        return;
    }
    
    if(is_home() || is_front_page()){
        $post_id = 0;
    }else{
        $post_id = get_queried_object_id();
        if(!$post_id){
            return;
        }
    }
    $page_config = get_page_module_config($post_id);

    if ($page_config && $page_config['aside_show']) {
        echo '<div class="tools-left io-footer-tools">';
        echo '<div class="btn-tools btn-show-side my-1" data-toggle-div data-target="#layout_aside" data-class="is-mobile"><i class="iconfont icon-expand"></i></div>';
        echo '</div>';
    }
}
/**
 * 经典编辑器分类增加搜索
 * @return void
 */
function io_term_box_search()
{
    $screen    = get_current_screen();
    $post_type = $screen->post_type;
    if (in_array($post_type, ['sites', 'book', 'app', 'post'])) {
        $taxonomy = posts_to_cat($post_type);
        ?>
        <script src="<?php echo get_theme_file_uri('assets/js/tiny-pinyin.min.js') ?>"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.querySelector('#<?php echo $taxonomy ?>div #<?php echo $taxonomy ?>-all.tabs-panel');
            if (!container || typeof Pinyin === 'undefined') return;

            const listItems = container.querySelectorAll('ul.categorychecklist li');
            const form = container.closest('.categorydiv');

            // 给每个分类项添加拼音和首拼属性
            listItems.forEach(li => {
                const label = li.querySelector('label');
                if (!label) return;
            
                const text = label.textContent.trim();
                const pinyin = ioGetPinyin(text);

                li.dataset.pinyin = pinyin.full;
                li.dataset.initials = pinyin.first;
                li.dataset.label = text.toLowerCase();
            });

            // 创建搜索框
            const input = document.createElement('input');
            input.type = 'search';
            input.placeholder = '搜索分类(汉字、拼音、首写字母)';
            input.style.width = '100%';

            form.prepend(input);

            input.addEventListener('input', function () {
                const keyword = this.value.trim().toLowerCase();

                listItems.forEach(li => {
                    const label = li.dataset.label || '';
                    const pinyin = li.dataset.pinyin || '';
                    const initials = li.dataset.initials || '';

                    // 匹配原文、拼音或首拼
                    if (label.includes(keyword) || pinyin.includes(keyword) || initials.includes(keyword)) {
                        li.style.display = '';
                        // 同时确保所有祖先 li 也显示（保留层级结构）
                        let parent = li.parentElement.closest('li');
                        while (parent) {
                            parent.style.display = '';
                            parent = parent.parentElement.closest('li');
                        }
                    } else {
                        li.style.display = 'none';
                    }
                });

                // 如果搜索框为空，全部显示
                if (keyword === '') {
                    listItems.forEach(li => li.style.display = '');
                }
            });
        });
        </script>
        <?php
    }
}
add_action('admin_footer', 'io_term_box_search');

/**
 * 输出控制台信息
 * @return void
 */
function io_win_console()
{
    if (!is_super_admin() || (defined('WP_CACHE') && WP_CACHE)) {
        return;
    }

    echo '<script type="text/javascript">';
    echo 'console.log("数据库查询：' . get_num_queries() . '次 | 页面生成耗时：' . timer_stop(0, 6) . 's' . '");';
    echo '</script>';
}

function io_get_footer_content(){
    io_footer_tools_left();
    io_footer_tools_right();
    io_nav_search_html();
    io_popup_html();
    io_footer_js();

    io_mobile_footer_nav();

    io_win_console();
    // 自定义代码
    echo io_get_option('code_2_footer', '');
}
add_action('wp_footer','io_get_footer_content');
add_action('login_footer','io_get_footer_content');

function io_footer_js(){
    echo '<script type="text/javascript">';

    echo 'window.IO = ' . json_encode(io_js_var(), JSON_UNESCAPED_UNICODE) . ';';
    
    //echo '$(document).ready(function(){if($("#search-text")[0]){$("#search-text").focus();}});';
 
    echo '</script>';
}


/**
 * 输出弹出搜索框
 * @return void
 */
function io_nav_search_html() {
    if (is_search() || is_404() || is_io_login() || is_bookmark()) {
        return;
    }

    $args = array(
        'class' => 'mx-0 mx-md-3',
    );

    echo '<div class="search-modal" id="search-modal">';
    echo io_search_body_html($args);
    echo '</div>';
}

/**
 * 弹窗广告
 * @return void
 */
function io_popup_html() {
    if (is_404() || !io_get_option('enable_popup', false) || is_io_login())
        return;

    $config = io_get_option('popup_set', array());
    if ($config['only_home'] && !(is_home() || is_front_page()))
        return;

    $_id         = md5(json_encode($config));
    $_ex_days    = 30;
    $show_policy = $config['show_policy'];
    if ('login' === $show_policy && is_user_logged_in()) {
        return;
    } elseif ('one' === $show_policy) {
        if (isset($_COOKIE['system_popup_ad']) && $_COOKIE['system_popup_ad'] == $_id) {
            return;
        }
    } else {
        if ($config['cycle']) {
            $_ex_days = round($config['cycle'] / 24, PHP_ROUND_HALF_EVEN);
            if (isset($_COOKIE['system_popup_ad']) && $_COOKIE['system_popup_ad'] == $_id) {
                return;
            }
        } else {
            $_ex_days = 0;
        }
    }

    if ($config['valid'] && !validity_inspection($config['time_frame']['from'], $config['time_frame']['to'])) {
        return;
    }

    $_delay = $config['delay'] ? $config['delay'] * 1000 : 200;

    $modal_header = '';
    if ($config['title']) {
        $modal_header .= '<div class="modal-header">';
        $modal_header .= '<h5 class="modal-title">' . $config['title'] . '</h5>';
        $modal_header .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="iconfont icon-close" aria-hidden="true"></i></button>';
        $modal_header .= '</div>';
    }

    $modal_footer = '';
    if ($config['buts']) {
        $modal_footer .= '<div class="modal-footer">';
        foreach ($config['buts'] as $but) {
            $modal_footer .= '<a href="' . $but['url']['url'] . '" target="' . $but['url']['target'] . '" class="btn ' . $but['class'] . ' btn-sm">' . $but['url']['text'] . '</a>';
        }
        $modal_footer .= '</div>';
    }

    $modal = '<div class="modal fade" id="system_popup_ad" data-delay="'.$_delay.'" data-ex="'.$_ex_days.'" data-id="' . $_id . '" tabindex="-1">';
    $modal .= '<div class="modal-dialog modal-dialog-centered" style="max-width:' . $config['width'] . 'px">';
    $modal .= '<div class="modal-content">';
    $modal .= $modal_header;
    $modal .= '<div class="modal-body">';
    if(empty($modal_header)){
        $modal .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="iconfont icon-close" aria-hidden="true"></i></button>';
    }
    $modal .= $config['content'];
    $modal .= '</div>';
    $modal .= $modal_footer;
    $modal .= '</div>';
    $modal .= '</div>';
    $modal .= '</div>';

    echo $modal;
}


/**
 * 移动端底部导航
 * @return void
 */
function io_mobile_footer_nav()
{
    if (!wp_is_mobile() || is_admin() || is_io_login() || !apply_filters('io_mobile_footer_nav', true)) {
        return;
    }


    $config = io_get_option('mobile_footer_nav');
    if (!io_get_option('mobile_footer_nav_s', true) || !$config || !is_array($config)) {
        return;
    }

    $btn = '';
    foreach ($config as $item) {
        $type      = $item['type'];
        $icon      = $item['icon_c'] ? trim($item['icon_c']) : trim($item['icon']);
        $icon_size = !empty($item['icon_size']) ? $item['icon_size'] : 24;


        $text  = $item['text'];
        $badge = $item['badge'];

        $class = 'nav-bar-' . $type;
        $attr  = '';
        $url   = '';

        switch ($type) {
            case 'home':
                if(is_home() || is_front_page()){
                    $class .= ' active';
                }
                $url = home_url();
                $home_btn = io_get_footer_nav_btn(compact('icon', 'text', 'url', 'class', 'badge', 'icon_size', 'attr'));

                if (!empty($item['go_top'])) {
                    $icon  = $item['go_top_icon'] ? trim($item['go_top_icon']) : 'iconfont icon-go-up';
                    $text  = $item['go_top_text'];
                    $url   = '';
                    $attr  = 'rel="nofollow"';
                    $class = 'go-to-up';
                    $badge = '';

                    $btn .= '<span class="tabbar-item tabbar-go-up overflow-hidden">';
                    $btn .= io_get_footer_nav_btn(compact('icon', 'text', 'url', 'class', 'badge', 'icon_size', 'attr'));
                    $btn .= $home_btn;
                    $btn .= '</span>';
                } else {
                    $btn .= $home_btn;
                }
                break;


            case 'link':
                $url = !empty($item['link']) ? $item['link'] : '';
                if (io_get_current_url() == $url) {
                    $class .= ' active';
                }
                $btn .= io_get_footer_nav_btn(compact('icon', 'text', 'url', 'class', 'badge', 'icon_size', 'attr'));
                break;
            case 'user':
                $attr = 'rel="nofollow"';
                if (io_user_center_enable()) {
                    $url = home_url('user/');
                } else {
                    $url = wp_login_url(io_get_current_url());
                }
                if (is_io_user()) {
                    $class .= ' active';
                }
                $btn .= io_get_footer_nav_btn(compact('icon', 'text', 'url', 'class', 'badge', 'icon_size', 'attr'));
                break;
            case 'add':
                if (!empty($item['btns'])) {
                    if(is_contribute()){
                        $class .= ' active';
                    }
                    $btn_menu = io_get_footer_nav_btn(compact('icon', 'text', 'url', 'class', 'badge', 'icon_size', 'attr'));

                    $btn .= io_get_new_posts_btn($item['btns'], 'tabbar-item', $btn_menu);
                }
                break;
            case 'minnav':
                $nav_menu_list = io_get_option('nav_menu_list', '');
                if ($nav_menu_list) {
                    
                    if(is_mininav()){
                        $class .= ' active';
                    }

                    $attr = 'rel="nofollow"';

                    $btn .= '<div class="minnav-tabbar hover-show tabbar-item">';
                    $btn .= io_get_footer_nav_btn(compact('icon', 'text', 'url', 'class', 'badge', 'icon_size', 'attr'));
                    $btn .= io_get_min_nav_menu_btn($nav_menu_list);
                    $btn .= '</div>';
                }
                break;
        }
    }

    $html = '';
    if ($btn) {
        $scrolling = io_get_option('footer_nav_scroll_hide') ? ' scrolling-hide' : '';
        $nav_type  = io_get_option('mobile_footer_nav_type');

        $html .= '<div class="footer-tabbar ' . $scrolling . ' tabbar-type-' . $nav_type . '">';
        $html .= '<div class="tabbar-bg blur-bg">';
        $html .= $btn;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="footer-tabbar-placeholder"></div>';
    }
    echo $html;
}

/**
 * 获取页脚导航按钮
 * @param mixed $args
 * @return string
 */
function io_get_footer_nav_btn($args)
{
    $default = array(
        'icon'      => 'iconfont icon-home',
        'text'      => '',
        'url'       => 'javascript:;',
        'class'     => '',
        'badge'     => '',
        'icon_size' => 24,
        'attr'      => '',
    );
    extract(wp_parse_args($args, $default));

    if (filter_var($icon, FILTER_VALIDATE_URL)) {
        $size = ' style="--this-size:' . $icon_size . 'px;"';
        $icon = '<span' . $size . ' class="icon-img"><img src="' . $icon . '" alt="' . $text . '"></span>';
    } else {
        $size = ' style="font-size:' . $icon_size . 'px;"';

        if (preg_match('/^<svg/', $icon)) {// 判断是不是 svg内容 
            // 不做任何修改
        } elseif (preg_match('/^data:image\/svg\+xml;base64,/', $icon)) {// 判断是不是 base64 编码的svg内容
            $icon = base64_decode(str_replace('data:image/svg+xml;base64,', '', $icon));
        } else {
            $icon = '<i class="' . $icon . '"></i>';
        }
        $icon = '<span' . $size . ' class="icon-svg">' . $icon . '</span>';
    }

    $url       = $url ? $url : 'javascript:;';
    $_text     = $text ? '<text class="mt-1">' . $text . '</text>' : '';
    $text_attr = $text ? 'title="' . $text . '"' : '';
    $badge     = $badge ? '<span class="badge vc-red">' . $badge . '</span>' : '';
    $attr      = $attr ? $text_attr . ' ' . $attr : $text_attr;

    $class .= ' tabbar-item';
    return '<a class="' . $class . '" ' . $attr . ' href="' . $url . '">' . $icon . $_text . $badge . '</a>';
}
