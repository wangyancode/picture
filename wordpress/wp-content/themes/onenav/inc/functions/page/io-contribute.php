<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-20 21:06:18
 * @LastEditors: iowen
 * @LastEditTime: 2025-04-01 23:24:23
 * @FilePath: /onenav/inc/functions/page/io-contribute.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 投稿页面数据处理
 * @param mixed $post_type
 * @return array 返回投稿页面数据
 */
function io_get_contribute_edit_data($post_type)
{
    // 默认数据准备
    $data = array(
        'ID'           => '',
        'post_title'   => '',
        'post_content' => '',
        'view_btn'     => '',
        'uptime_badge' => '',
        'category'     => array(),
        'tags'         => '',
        'post_status'  => '',
        'status_tip'   => '',
        'is_edit'      => false,
        'u_id'         => get_current_user_id(),
        'submit_text'  => __('提交审核', 'i_theme'),
        '_nonce'       => wp_create_nonce('posts_contribute_submit'),
    );
    $option = io_get_option($post_type . '_tg_config');
    if ($option['is_publish'] || is_super_admin()) {
        $data['submit_text'] = __('提交发布', 'i_theme');
    }

    switch ($post_type) {
        case 'sites':
            $meta = array(
                'cover'      => '',
                'sites_type' => '',
                'wechat_id'  => '',
                'link'       => '',
                'describe'   => '',
            );
            break;
        case 'app':
            $meta = array(
                'cover'       => '',
                'app_name'    => '',
                'app_type'    => '',
                'describe'    => '',
                'platform'    => [],
                'screenshot'  => [],
                'down_formal' => '',
                'down_list'   => [],
            );
            break;
        case 'book':
            $meta = array(
                'cover'      => '',
                'book_type'  => '',
                'score_type' => '',
                'score'      => 0,
                'describe'   => '',
                'journal'    => '',
                'meta_data'  => [],
                'buy_list'   => [],
                'down_list'  => [],
            );
            break;

        case 'post':
        default:
            $meta = array();
            break;
    }
    $data = array_merge($data, $meta);

    $edit_post = '';
    if (!isset($_GET['edit']) || !$_GET['edit']) {
        return $data;
    }

    // 权限判断
    $edit_id = $_GET['edit'];
    // 拥有编辑权限，则拥有此权限
    $edit_post = get_post($edit_id);
    if (!$edit_post || !is_user_logged_in() || !io_current_user_can('new_post_edit', $edit_post)) {
        wp_safe_redirect(home_url(remove_query_arg('edit')));
        exit;
    }

    // 判断文章类型，跳转到正确的编辑页面
    if ($edit_post->post_type !== $post_type) {
        $arg = array(
            'type' => $edit_post->post_type,
            'edit' => $edit_id,
        );
        wp_safe_redirect(add_query_arg($arg, io_get_template_page_url('template-contribute.php')));
        exit;
    }

    
    // 基础数据准备
    $cat_name = posts_to_cat($post_type);
    $tag_name = posts_to_tag($post_type);

    $data                 = array_merge($data, (array) $edit_post);
    $data['is_edit']      = true;
    $data['view_btn']     = '<a class="btn vc-l-blue text-xs btn-sm flex-fill btn-i-l" href="' . get_permalink($edit_post) . '"><i class="iconfont icon-chakan mr-1"></i>' . __('预览文章', 'i_theme') . '</a>';
    $data['uptime_badge'] = '<span class="badge vc-j-yellow">' . __('最后保存：', 'i_theme') . $data['post_modified'] . '</span>';
    $data['status_tip']   = '<span class="badge vc-j-' . $data['post_status'] . '">' . io_get_post_status_name($data['post_status']) . '</span>';

    if (is_super_admin()) {
        $data['view_btn'] .= '<a class="btn vc-l-yellow text-xs btn-sm flex-fill btn-i-l ml-2" href="' . get_edit_post_link($edit_post) . '"><i class="iconfont icon-modify mr-1"></i>' . __('后台编辑', 'i_theme') . '</a>';
    }

    $the_terms = get_the_terms($edit_id, $cat_name);
    if ($the_terms && !is_wp_error($the_terms)) {
        $data['category'] = array_column((array) $the_terms, 'term_id');
    }

    $the_tags = get_the_terms($edit_id, $tag_name);
    if ($the_tags) {
        $the_tags     = array_column((array) $the_tags, 'name');
        $data['tags'] = implode(',', $the_tags);
    }

    // meta数据准备
    switch ($post_type) {
        case 'sites':
            $data['cover'] = get_post_meta($edit_id, '_thumbnail', true);
            $data['sites_type'] = get_post_meta($edit_id, '_sites_type', true);
            $data['wechat_id'] = get_post_meta($edit_id, '_wechat_id', true);
            $data['link'] = get_post_meta($edit_id, '_sites_link', true);
            $data['describe'] = get_post_meta($edit_id, '_sites_sescribe', true);
            break;
        case 'app':
            $data['cover'] = get_post_meta($edit_id, '_app_ico', true);
            $data['app_name'] = get_post_meta($edit_id, '_app_name', true);
            $data['app_type'] = get_post_meta($edit_id, '_app_type', true);
            $data['describe'] = get_post_meta($edit_id, '_app_sescribe', true);
            $data['platform'] = get_post_meta($edit_id, '_app_platform', true);
            $data['screenshot'] = get_post_meta($edit_id, '_screenshot', true);
            $data['down_formal'] = get_post_meta($edit_id, '_down_formal', true);
            $data['down_list'] = get_post_meta($edit_id, 'app_down_list', true);
            break;
        case 'book':
            $data['cover'] = get_post_meta($edit_id, '_thumbnail', true);
            $data['book_type'] = get_post_meta($edit_id, '_book_type', true);
            $data['score_type'] = get_post_meta($edit_id, '_score_type', true);
            $data['score'] = get_post_meta($edit_id, '_score', true) ?: 0;
            $data['describe'] = get_post_meta($edit_id, '_summary', true);
            $data['journal'] = get_post_meta($edit_id, '_journal', true);
            $data['meta_data'] = get_post_meta($edit_id, '_books_data', true);
            $data['buy_list'] = get_post_meta($edit_id, '_buy_list', true);
            $data['down_list'] = get_post_meta($edit_id, '_down_list', true);
            break;

        case 'post':
        default:
            break;
    }
    return $data;
}

/**
 * 获取投稿页面头部
 * 
 * 投稿中心，显示用户投稿的文章、站点、应用等数量，以及审核状态
 * @return string
 */
function io_get_contribute_header()
{
    $post_id = get_the_ID();
    if(!get_post_meta($post_id,'_top_menu',true)){
        return '';
    }
    $user_id         = get_current_user_id();
    $user_link       = get_author_posts_url($user_id);
    $link            = get_permalink();
    $contribute_type = io_get_contribute_allow_type(); // 可投稿类型
    $count           = count($contribute_type);
    switch ($count) {
        case 1:
        case 2:
        case 3:
        case 4:
            $item_class = 'row-col-' . $count . 'a';
            break;
        case 5:
            $item_class = 'row-col-2a row-col-md-4a';
            break;
        default:
            $item_class = 'row-col-1a';
            break;
    }
    $sub_title = get_post_meta($post_id, '_sub_title', true) ?: '把你的发现记录下来，让每一份灵感与收获都成为你成长的基石。';

    $html = '<div class="contribute-header mb-4">';
    $html .= '<div class="contribute-title">';
    $html .= '<div class="modal-header-bg fx-blue">';
    $html .= '<img class="" src="' . get_theme_file_uri('/assets/images/svg/contribute.svg') . '"/>';
    $html .= '<h1 class="text-xl text-md-lg mb-2">' . get_the_title() . '</h1>';
    $html .= '<p class="text-xs mb-4 mb-md-5 mb-lg-0">' . $sub_title . '</p>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="contribute-body blur-bg row-a ' . $item_class . ' p-1">';
    foreach ($contribute_type as $index => $type) {
        $active   = ((isset($_GET['type']) && $_GET['type'] === $type) || (!isset($_GET['type']) && $index == 0)) ? ' active' : '';
        $name     = io_get_post_type_name($type);
        $new_link = add_query_arg(array('type' => $type), $link);
        // ?tab=post&post_status=draft
        $publish_link = add_query_arg(array('tab' => $type, 'post_status' => 'publish'), $user_link);
        $pending_link = add_query_arg(array('tab' => $type, 'post_status' => 'pending'), $user_link);
        $draft_link   = add_query_arg(array('tab' => $type, 'post_status' => 'draft'), $user_link);

        $html .= '<div class="contribute-item d-flex flex-column flex-fill' . $active . '">';
        $html .= '<div class="item-title text-xs badge ' . get_theme_color($index + 1, 'j') . ' text-right mb-2 d-none d-md-block"><i class="iconfont icon-' . $type . '"></i>' . $name . '</div>';
        $html .= '<div class="item-btn align-items-center mb-2 d-none d-md-flex">';
        $html .= '<a href="' . esc_url($publish_link) . '" class="badge vc-j-blue mr-2" target="_blank">' . __('已发布', 'i_theme') . '<span class="ml-1">' . get_user_post_count($user_id, 'publish', $type) . '</span></a>';
        $html .= '<a href="' . esc_url($pending_link) . '" class="badge vc-j-yellow mr-auto" target="_blank">' . __('审核中', 'i_theme') . '<span class="ml-1">' . get_user_post_count($user_id, 'pending', $type) . '</span></a>';
        $html .= '<a href="' . esc_url($draft_link) . '" class="badge vc-j-gray" target="_blank">' . __('草稿', 'i_theme') . '<span class="ml-1">' . get_user_post_count($user_id, 'draft', $type) . '</span></a>';
        $html .= '</div>';
        $html .= '<a href="' . esc_url($new_link) . '" class="item-new-btn btn ' . get_theme_color($index + 1, 'l') . ' btn-sm btn-i-l btn-block" target="_blank"><i class="iconfont icon-' . $type . '"></i><span>' . sprintf(__('新建%s', 'i_theme'), $name) . '</span></a>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * 投稿内容提示
 * @return void
 */
function io_contribute_content_tip()
{
    if (get_post_meta(get_the_ID(), '_content_s', true)) { ?>
        <div class="card panel mb-4">
            <div class="card-body panel-body single mt-2"> 
                <h2 id="comments-list-title" class="comments-title item-title text-lg mb-4">
                    <i class="iconfont icon-tishi mr-2"></i><?php echo (get_post_meta(get_the_ID(), '_notice_text', true) ?: '投稿须知') ?> 
                    <?php edit_post_link('<i class="iconfont icon-modify mr-1"></i>' . __('编辑', 'i_theme'), '<span class="edit-link text-xs text-muted">', '</span>'); ?>
                </h2>
                <?php the_content(); ?> 
            </div>
        </div>
        <?php
    }
}

/**
 * 投稿页面正文表单
 * @param mixed $post_type
 * @param mixed $edit_data
 * @return void
 */
function io_contribute_edit_content_form($post_type, $edit_data)
{
    switch ($post_type) {
        case 'sites':
            $form = io_get_sites_form_html($edit_data);
            break;
        case 'app':
            $form = io_get_app_form_html($edit_data);
            break;
        case 'book':
            $form = io_get_book_form_html($edit_data);
            break;

        case 'post':
        default:
            $form = '<div class="new-posts-title mb-3">';
            $form .= '<input type="text" class="form-control new-title" name="post_title" placeholder="' . __('请输入文章标题', 'i_theme') . '" value="' . esc_attr($edit_data['post_title']) . '"></input>';
            $form .= '</div>';
            break;
    }
    
    echo '<div class="panel card">';
    echo '<div class="card-body">';
    echo $form;

    $editor_id = 'post_content';
    $settings  = array(
        'textarea_rows'  => 20,
        'editor_height'  => (wp_is_mobile() ? 260 : 560),
        'media_buttons'  => false,
        'default_editor' => 'tinymce',
        'quicktags'      => false,
        'editor_css'     => '<link rel="stylesheet" href="' . get_theme_file_uri('/assets/css/new-posts.css?ver=' . IO_VERSION) . '" type="text/css">',
        'teeny'          => false,
        'wpautop'        => true
    );
    wp_editor($edit_data['post_content'], $editor_id, $settings);

    echo '<div class="br-sm d-flex align-items-center text-xs mt-2">';
    echo '<span class="badge"><i class="iconfont icon-'.$post_type.' mr-1"></i>' . io_get_post_type_name($post_type) . '</span>';
    echo '<span class="ml-auto modified-time">' . $edit_data['uptime_badge'] . '</span>';
    echo '</div>';

    echo '</div>';
    echo '</div>';
}

/**
 * 获取网址名称表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_sites_form_html($edit_data)
{
    $option     = io_get_option('sites_tg_config');
    $lists_name = get_site_type_name();
    $lists = isset($option['types']) ? $option['types'] : ['sites'];

    $switch = array(
        'sites'  => array(
            'enable'  => '.tg-sites-url',
            'disable' => '.tg-wechat-id',
        ),
        'wechat' => array(
            'enable'  => '.tg-wechat-id',
            'disable' => '.tg-sites-url',
        ),
    );

    $type_btn = io_get_posts_type_tab_btn_html($lists_name, $lists, $edit_data['sites_type'], $switch);
    $cover = io_get_posts_cover_box_html('sites', $edit_data['cover']);

    $is_wx = false;
    if (in_array('wechat', $lists) && (1 === count($lists) || 'wechat' === $lists[0] || 'wechat' === $edit_data['sites_type'])) {
        $is_wx = true;
    } elseif ('wechat' === $edit_data['sites_type']) {
        $is_wx = true;
    }

    $html = $type_btn;
    $html .= '<div class="d-flex mb-3">';
    $html .= $cover;
    $html .= '<div class="sites-meta flex-fill">';

    $html .= '<div class="input-box">';
    $html .= '<input type="text" class="form-control sites-title" value="' . $edit_data['post_title'] . '" name="post_title" placeholder="' . __('名称', 'i_theme') . '" maxlength="' . $option['title_limit']['height'] . '"/>';
    $html .= '</div>';

    $html .= '<div class="input-box tg-wechat-id" ' . ($is_wx ? '' : 'style="display:none"') . '>';
    $html .= '<input type="text" class="form-control" value="' . $edit_data['wechat_id'] . '" name="wechat_id" placeholder="' . __('公众号ID(微信号)', 'i_theme') . '"/>';
    $html .= '</div>';

    $html .= '<div class="input-box tg-sites-url" ' . ($is_wx ? 'style="display:none"' : '') . '>';
    $html .= '<input type="text" class="form-control sites-link" value="' . $edit_data['link'] . '" name="link" placeholder="' . __('链接', 'i_theme') . ' ( https:// )"/>';
    $html .= '<a href="javascript:" id="get_info" class="btn vc-l-blue text-xs get-info btn-sm">' . __('获取 TDK', 'i_theme') . '</a>';
    $html .= '</div>';

    $html .= '</div> ';

    $html .= '</div>';

    $html .= '<div class="input-box mb-3 ' . (wp_is_mobile() ? '' : 'count-tips') . '" data-min="0" data-max="' . $option['desc_limit'] . '">';
    $html .= '<textarea rows="2" class="form-control sites-desc" name="describe" data-status="true" placeholder="' . __('简介', 'i_theme') . '" maxlength="' . $option['desc_limit'] . '">' . $edit_data['describe'] . '</textarea>';
    $html .= '</div> ';

    return $html;
}
/**
 * 获取 book 名称表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_book_form_html($edit_data)
{
    $option     = io_get_option('book_tg_config');
    $lists_name = get_book_type_name();
    $lists = isset($option['types']) ? $option['types'] : ['books'];

    $switch = array(
        'periodical' => array(
            'enable'  => '.book-journal-type',
            'disable' => '',
        ),
        'books'      => array(
            'enable'  => '',
            'disable' => '.book-journal-type',
        ),
        'movie'      => array(
            'enable'  => '',
            'disable' => '.book-journal-type',
        ),
        'tv'         => array(
            'enable'  => '',
            'disable' => '.book-journal-type',
        ),
        'video'      => array(
            'enable'  => '',
            'disable' => '.book-journal-type',
        ),
    );

    $type_btn = io_get_posts_type_tab_btn_html($lists_name, $lists, $edit_data['book_type'], $switch);
    $cover = io_get_posts_cover_box_html('book', $edit_data['cover']);

    $html = $type_btn;
    $html .= '<div class="d-flex mb-3">';
    $html .= $cover;
    $html .= '<div class="book-meta flex-fill">';
    $html .= '<div class="input-box">';
    $html .= '<input type="text" class="form-control book-title" value="' . $edit_data['post_title'] . '" name="post_title" placeholder="' . __('名称', 'i_theme') . '" maxlength="' . $option['title_limit']['height'] . '"/>';
    $html .= '</div>';
    $html .= '<div class="input-box ' . (wp_is_mobile() ? '' : 'count-tips') . '" data-min="0" data-max="' . $option['desc_limit'] . '">';
    $html .= '<textarea rows="2" class="form-control book-desc" name="describe" data-status="true" placeholder="' . __('简介', 'i_theme') . '" maxlength="' . $option['desc_limit'] . '">' . $edit_data['describe'] . '</textarea>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '</div>';


    return $html;
}

/**
 * 获取 app 名称表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_app_form_html($edit_data){
    $option     = io_get_option('app_tg_config');
    $lists_name = get_app_type_name();
    $lists = isset($option['types']) ? $option['types'] : ['app'];

    $switch = array(
        'app'  => array(
            'enable'  => '.app-platform-box,.app-down-formal-box',
            'disable' => '',
        ),
        'down' => array(
            'enable'  => '',
            'disable' => '.app-platform-box,.app-down-formal-box',
        ),
    );

    $type_btn = io_get_posts_type_tab_btn_html($lists_name, $lists, $edit_data['app_type'], $switch);
    $cover    = io_get_posts_cover_box_html('app', $edit_data['cover']);

    $html = $type_btn;
    $html .= '<div class="d-flex mb-3">';
    $html .= $cover;
    $html .= '<div class="app-meta flex-fill">';

    $html .= '<div class="input-box">';
    $html .= '<input type="text" class="form-control app-title" value="' . $edit_data['post_title'] . '" name="post_title" placeholder="' . __('标题', 'i_theme') . '" maxlength="' . $option['title_limit']['height'] . '"/>';
    $html .= '</div>';
    $html .= '<div class="input-box">';
    $html .= '<input type="text" class="form-control app-title" value="' . $edit_data['app_name'] . '" name="app_name" placeholder="' . __('名称（同上则留空）', 'i_theme') . '" maxlength="10"/>';
    $html .= '</div>';

    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="input-box mb-3 ' . (wp_is_mobile() ? '' : 'count-tips') . '" data-min="0" data-max="' . $option['desc_limit'] . '">';
    $html .= '<textarea rows="2" class="form-control app-desc" name="describe" data-status="true" placeholder="' . __('简介', 'i_theme') . '" maxlength="' . $option['desc_limit'] . '">' . $edit_data['describe'] . '</textarea>';
    $html .= '</div>';
    return $html;
 

}
/**
 * 获取文章类型切换按钮HTML
 * 
 * 网址、公众号等
 * @param array $lists_name 类型名称数组
 * @param array $lists 类型数组
 * @param string $current 当前类型
 * @param array $switch 启用、禁用的表单容器选择器
 * @return string
 */
function io_get_posts_type_tab_btn_html($lists_name, $lists, $current, $switch = array())
{
    $current = $current ?: $lists[0];
    $type_btn = '';
    if (count($lists) > 1) {
        $type_btn .= ' <div class="d-flex align-items-center mb-3">';
        $type_btn .= '<span class="badge vc-l-blue" data-toggle="tooltip" title="' . __('先选择类型', 'i_theme') . '"><i class="iconfont icon-name"></i></span>';
        $type_btn .= '<div class="ml-auto nav overflow-x-auto no-scrollbar" role="tablist">';

        foreach ($lists as $v) {
            $enable   = isset($switch[$v]) ? $switch[$v]['enable'] : '';
            $disable  = isset($switch[$v]) ? $switch[$v]['disable'] : '';
            $type_btn .= sprintf(
                '<div class="btn btn-tab-h btn-sm text-xs ml-2%s" data-value="%s" data-for="internal_type" data-enable="%s" data-disable="%s">%s</div>',
                ($v === $current ? ' active' : ''),
                esc_attr($v),
                esc_attr($enable),
                esc_attr($disable),
                $lists_name[$v]
            );
        }

        $type_btn .= '</div>';
        $type_btn .= '</div> ';
    }
    $type_btn .= '<input type="hidden" name="internal_type" value="' . esc_attr($current) . '"></input>';

    return $type_btn;
}

/**
 * 获取文章封面HTML
 * @param mixed $post_type
 * @param mixed $value
 * @param mixed $alt
 * @param mixed $name
 * @return string
 */
function io_get_posts_cover_box_html($post_type, $value = '', $name = 'cover-img')
{
    $alt   = __('图标', 'i_theme');
    $type  = 'favicon';
    if ('book' === $post_type) {
        $alt  = __('封面', 'i_theme');
        $type = 'cover';
    }
    if('app' === $post_type){
        $type = 'icon';
    }

    $size = (float)io_get_option($type.'_img_size', 0.6);

    if ($size <= 0) {
        return '';
    }
    $add_ico = get_theme_file_uri('/assets/images/add.png');

    $html = '<div class="posts-cover-box mr-2">';
    $html .= '<div class="posts-cover-img ' . $post_type . '-cover-preview" data-media-type="' . esc_attr($type) . '" data-size="' . esc_attr($size) . '" data-toggle="tooltip" title="' . esc_attr($alt) . '">';
    $html .= '<input type="hidden" value="' . esc_attr($value) . '" class="cover-img" name="' . esc_attr($name) . '"/>';
    $html .= '<img class="fill-cover show-preview" src="' . esc_attr($value ?: $add_ico) . '" data-add_ico="' . esc_attr($add_ico) . '" width="120" height="120" alt="' . esc_attr($alt) . '">';
    $html .= '<span class="cover-delete" style="'.($value ?'':'display:none').'"><i class="iconfont icon-close"></i></span>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * 投稿页面侧边栏表单
 * @param mixed $post_type
 * @param mixed $edit_data
 * @return void
 */
function io_contribute_edit_sidebar_form($post_type, $edit_data)
{
    $option   = io_get_option($post_type . '_tg_config');
    $cat_name = posts_to_cat($post_type);
    $is_pay   = $option['pay']['status'];

    $cat_args = array(
        'hide_empty'       => 0,
        'show_option_none' => __('请选择分类', 'i_theme'),
        'id'               => 'post_cat',
        'taxonomy'         => $cat_name,
        'name'             => 'category',
        'class'            => 'form-control text-sm',
        'hierarchical'     => 1,
        'selected'         => $edit_data['category'],
        'echo'             => false,
        'max_count_limit'  => is_super_admin() ? 0 : (isset($option['cat_limit']) ? $option['cat_limit'] : 0),
    );
    if (isset($option['cat_in']) && !empty($option['cat_in'])) {
        if ($option['cat_reverse']) {
            $cat_args['exclude'] = $option['cat_in'];
        } else {
            $cat_args['include'] = $option['cat_in'];
        }
    }
    $status = '';
    if ($edit_data['ID']) {
        $status = io_get_posts_status_html($post_type, $edit_data);
    }

    // 分类、标签
    $taxonomy = '<div class="card my-3 ' . ($edit_data['ID'] ? '' : 'mt-md-0') . '">';
    $taxonomy .= '<div class="card-body">';
    $taxonomy .= '<p class="contr-title text-muted text-sm mb-2">' . __('分类', 'i_theme') . '</p>';
    $taxonomy .= '<div class="form-select">';
    $taxonomy .= io_dropdown_categories_multiple($cat_args); // wp_dropdown_categories($cat_args);
    $taxonomy .= '</div>';
    $taxonomy .= '<p class="contr-title text-muted text-sm mb-2 mt-3">' . __('标签', 'i_theme') . '</p>';
    $taxonomy .= '<textarea class="form-control sites-keywords text-sm" rows="2" name="tags" placeholder="' . __('输入标签', 'i_theme') . '" tabindex="6">' . $edit_data['tags'] . '</textarea>';
    $taxonomy .= '<div class="text-muted text-xs mt-2"><i class="iconfont icon-tishi mr-1"></i>' . __('填写标签，每个标签用逗号隔开', 'i_theme') . '</div>';
    $taxonomy .= '</div>';
    $taxonomy .= '</div>';

    // 其他 meta 数据
    $meta = io_get_posts_meta_form_html($post_type, $edit_data);

    // 游客表单
    $guest = '';
    if (!$edit_data['u_id']) {
        $guest .= io_get_guest_contact_form_html();
    }

    // 提交按钮
    $submit = '<div class="btn-group w-100">';

    $audit_edit        = io_get_option('audit_edit', true); // 编辑后是否需要审核
    $again_edit_rebate = io_get_option('again_edit_rebate', 80); // 再次付费折扣
    $is_pay_post       = get_post_meta($edit_data['ID'], 'io_pay_post', true); // 是否是付费内容

    $is_again_pay = $audit_edit && $is_pay_post && 0 != $again_edit_rebate; // 是否需要再次付费

    $submit_before_tips = '';
    if ('publish' !== $edit_data['post_status'] && 'pending' !== $edit_data['post_status']) {
        if (is_user_logged_in()) { //是否登陆
            $submit .= '<button type="button" action="' . $post_type . '_draft" name="submit" class="btn vc-l-green new-posts-submit btn-i-l is-post"><i class="iconfont icon-article mr-2"></i>' . __('保存草稿', 'i_theme') . '</button>';
        } else {
            $submit_before_tips .= '<div class="tips-box vc-l-yellow btn-block text-xs text-left mb-2 br-sm"><i class="iconfont icon-tishi mr-1"></i>' . __('登录后可以管理您的所有投稿内容并查看投稿状态！', 'i_theme') . '</div>';
        }
    } elseif (is_super_admin() || $edit_data['post_status']) {
        // 如果是超级管理员或当前内容状态允许编辑

        // 超级管理员无需审核，或审核设置关闭，或付费内容且无需再次编辑扣费
        if (
            is_super_admin() ||
            !$audit_edit ||
            ($audit_edit && $is_pay_post && 0 == $again_edit_rebate)
        ) {
            $edit_data['submit_text'] = __('保存', 'i_theme');
        } else {
            if ('publish' === $edit_data['post_status']) {
                $submit_before_tips .= '<div class="tips-box vc-l-yellow btn-block text-xs text-left mb-2 br-sm"><i class="iconfont icon-tishi mr-1"></i>' . io_get_option('audit_edit_tips', true) . '</div>';
            }
        }
    }
    // 增加超级管理员审核发布按钮
    if ('pending' === $edit_data['post_status'] && is_super_admin()) {
        if ((int) $edit_data['post_author'] !== get_current_user_id()) {
            // 用户文章
            $pass_text = __('审核并发布', 'i_theme');
        } else {
            // 自己文章
            $pass_text = __('发布', 'i_theme');
        }
        $submit .= '<button type="button" action="' . $post_type . '_pass" name="submit" class="btn vc-l-blue new-posts-submit btn-i-l is-post"><i class="iconfont icon-adopt mr-2"></i>' . $pass_text . '</button>';
    }

    $submit .= '<button type="button" action="' . $post_type . '_save" name="submit" class="btn vc-l-blue new-posts-submit btn-i-l is-post"><i class="iconfont icon-upload-wd mr-2"></i>' . $edit_data['submit_text'] . '</button>';
    $submit .= '</div>';


    // 付费按钮，编辑时，或新投稿时，且需要付费
    if (($audit_edit || $edit_data['post_status'] != 'publish') && $is_pay && !is_super_admin()) {
        if ($is_pay_post && 0 == $again_edit_rebate) {
            // 付费内容，且无需再次付费，显示提示
            $submit .= '<div class="tips-box vc-l-yellow btn-block text-xs text-left mt-2 br-sm"><i class="iconfont icon-tishi mr-1"></i>' . io_get_option('again_nopay_tips', true) . '</div>';
        } else {
            // 新内容或者付费内容，且需要再次付费
            $prices         = iopay_get_pay_publish_prices($option['pay']['prices'], $edit_data['ID']);
            $unit           = io_get_option('pay_unit', '￥');
            $pay_price      = $prices[0];
            $original_price = $prices[1];
            $original_price = $original_price && $original_price > $pay_price ? ' <div class="original-price d-inline-block text-xs">(' . __('原价', 'i_theme') . '<span class="text-xs">' . $unit . '</span><span>' . $original_price . '</span>)</div>' : '';
            $pay_price      = '<div class="pay-price d-inline-block"><span class="text-xs">' . $unit . '</span>' . $pay_price . '</div>';

            $submit .= '<a href="" class="io-ajax-modal-get ajax-click" data-modal_id="pay_publish" data-modal_esc="false"></a>';
            $submit .= '<button type="button" action="' . $post_type . '_pay" name="submit" class="btn vc-j-blue btn-shadow new-posts-submit btn-i-l is-post btn-block mt-3"><i class="iconfont icon-version mr-2"></i>' . $option['pay']['btn_text'] . $pay_price . $original_price . '</button>';
            $stmt   = '';
            if ($is_again_pay) {
                $stmt = '<div class="tips-box vc-l-red btn-block text-xs text-left mt-2 br-sm"><i class="iconfont icon-tishi mr-1"></i>' . io_get_option('again_pay_tips', true) . '</div>';
            } elseif ($option['pay']['stmt']) {
                $stmt = '<div class="tips-box vc-l-red btn-block text-xs text-left mt-2 br-sm"><i class="iconfont icon-tishi mr-1"></i>' . $option['pay']['stmt'] . '</div>';
            }
            $submit .= $stmt;
        }
    }

    $submit_card = '<div class="card">';
    $submit_card .= '<div class="card-body">';
    $submit_card .= '<input type="hidden" name="_wpnonce" value="' . $edit_data['_nonce'] . '"></input>';
    $submit_card .= '<input type="hidden" name="posts_id" value="' . $edit_data['ID'] . '">';
    $submit_card .= get_captcha_input_html('contribute_submit');
    $submit_card .= $submit_before_tips;
    $submit_card .= $submit;
    $submit_card .= '</div>';
    $submit_card .= '</div>';

    echo $status;
    echo $taxonomy;
    echo $meta;
    echo $guest;
    echo $submit_card;
}

/**
 * 获取文章状态HTML
 * 
 * 发布状态、修改时间、作者等
 * @param mixed $post_type
 * @param mixed $edit_data
 * @return string
 */
function io_get_posts_status_html($post_type, $edit_data)
{
    $author_name = get_the_author_meta('display_name', $edit_data['post_author']);
    $author_link = get_author_posts_url($edit_data['post_author']);
    $author_a   = '<a href="' . $author_link . '" class="text-xs text-muted ml-2" target="_blank">' . $author_name . '</a>';

    $html = '<div class="card fx-header-bg my-3 mt-md-0 p-3">';

    $html .= '<div class="d-flex align-items-center text-sm mb-3">';
    $html .= '<span class="text-height-xs" data-toggle="tooltip" title="' . __('作者', 'i_theme') . '"><i class="iconfont icon-user-circle"></i></span>';
    $html .= $author_a;
    $html .= '<span class="ml-auto">' . $edit_data['status_tip'] . '</span>';
    $html .= '</div>';


    $html .= '<div class="d-flex align-items-center">';
    $html .=  $edit_data['view_btn'];
    $html .= '</div>';

    $html .= '</div>';
    return $html;
}

/**
 * 获取游客联系表单HTML
 * @return string
 */
function io_get_guest_contact_form_html()
{
    $html = '<div class="card mb-3">';

    $html .= '<div class="card-body">';
    $html .= '<p class="contr-title text-muted text-sm mb-2">' . __('昵称', 'i_theme') . '</p>';
    $html .= '<div class="mb20">';
    $html .= '<input class="form-control text-sm" name="guest_info[name]" placeholder="' . __('请输入昵称', 'i_theme') . '">';
    $html .= '</div>';
    $html .= '<p class="contr-title text-muted text-sm mb-2 mt-3">' . __('联系方式', 'i_theme') . '</p>';
    $html .= '<input class="form-control text-sm" name="guest_info[contact]" placeholder="' . __('输入联系方式', 'i_theme') . '">';
    $html .= '</div>';

    $html .= '</div>';
    return $html;
}





/**
 * 获取文章meta表单HTML
 * @param mixed $post_type
 * @param mixed $edit_data
 * @return string
 */
function io_get_posts_meta_form_html($post_type, $edit_data)
{
    switch ($post_type) {
        case 'book':
            $html = io_get_book_meta_form_html($post_type, $edit_data);
            break;
        case 'app':
            $html = io_get_app_meta_form_html($post_type, $edit_data);
            break;
        case 'sites':
        case 'post':
        default:
            $html = '';
            break;
    }
    return $html;
}


/**
 * 获取图书meta表单HTML
 * @param mixed $post_type
 * @param mixed $edit_data
 * @return string
 */
function io_get_book_meta_form_html($post_type, $edit_data)
{
    // 评分
    $input = '<div class="form-group">
                <p class="contr-title text-muted text-sm mb-2">' . __('评分', 'i_theme') . '<span class="badge vc-l-blue ml-2">' . __('没得请保持默认', 'i_theme') . '</span></p>
                <div class="d-flex">
                    <input name="score_type" type="text" class="form-control text-sm" placeholder="' . __('平台', 'i_theme') . '" value="' . $edit_data['score_type'] . '"/>
                    <input name="score" type="number" class="form-control text-sm ml-2" placeholder="' . __('分值', 'i_theme') . '" min="0" value="' . $edit_data['score'] . '"/>
                </div>
            </div>';

    // 期刊类型选择
    $input .= '<div class="form-group book-journal-type" style="' . ('periodical' === $edit_data['book_type'] ? '' : 'display:none;') . '">';
    $input .= '<p class="contr-title text-muted text-sm mb-2">' . __('期刊类型', 'i_theme') . '</p>';
    $input .= '<select class="form-control text-sm" name="journal">';
    foreach (io_get_journal_name() as $key => $value) {
        $selected = $edit_data['journal'] == $key ? 'selected' : '';
        $input .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
    }
    $input .= '</select>';
    $input .= '</div>';

    // 元数据
    $input .= '<div class="form-group meta-item-group m-0">';
    $input .= '<div class="d-flex align-items-center mb-1 mt-3">
                <span class="contr-title text-muted text-sm">' . __('元数据', 'i_theme') . '</span>
                <div class="btn vc-l-blue btn-outline btn-sm ml-auto meta-item-add" data-toggle="tooltip" title="' . __('添加元数据', 'i_theme') . '"><i class="iconfont icon-add-o"></i></div>
                <div class="meta-item-prefab" style="display:none;">
                    <div class="meta-data-box">
                        <input type="text" data-name="meta_data[0][term]" class="form-control term-name" placeholder="' . __('项目', 'i_theme') . '" value=""/>
                        <span class="-line-"></span>
                        <input type="text" data-name="meta_data[0][detail]" class="form-control" placeholder="' . __('内容', 'i_theme') . '" value=""/>
                        <div class="meta-btn meta-item-sort"><i class="iconfont icon-classification"></i></div>
                        <div class="meta-btn meta-item-delete"><i class="iconfont icon-close-circle"></i></div>
                    </div>
                </div>
            </div>';
    $input .= '<div class="meta-item-body">';
    if (!empty($edit_data['meta_data'])) {
        foreach ($edit_data['meta_data'] as $index => $value) {
            $input .= '<div class="meta-data-box">';
            $input .= '<input type="text" name="meta_data[' . $index . '][term]" class="form-control term-name" placeholder="' . __('项目', 'i_theme') . '" value="' . $value['term'] . '"/>';
            $input .= '<span class="-line-"></span>';
            $input .= '<input type="text" name="meta_data[' . $index . '][detail]" class="form-control" placeholder="' . __('内容', 'i_theme') . '" value="' . $value['detail'] . '"/>';
            $input .= '<div class="meta-btn meta-item-sort"><i class="iconfont icon-classification"></i></div>';
            $input .= '<div class="meta-btn meta-item-delete"><i class="iconfont icon-close-circle"></i></div>';
            $input .= '</div>';
        }
    }
    $input .= '</div>';
    $input .= '</div>';


    $basis = '<div class="card mb-3">';

    $basis .= '<div class="card-body">';
    $basis .= $input;
    $basis .= '</div>';

    $basis .= '</div>';

    // 平台购买链接
    $buy_lists = io_get_book_buy_list_html($edit_data);

    // 资源下载地址
    $download = io_get_book_down_list_html($edit_data);

    return $basis . $buy_lists . $download; 
}
/**
 * 获取图书购买链接表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_book_buy_list_html($edit_data)
{
    $dow_args = array(
        'id'          => 'book_buy_list',
        'name'        => 'buy_list',
        'title'       => __('获取渠道', 'i_theme'),
        'tooltip'     => __('添加获取列表', 'i_theme'),
        'modal_title' => __('编辑获取渠道', 'i_theme'),
        'tips'        => __('资源的订阅、购买、观看等渠道。', 'i_theme'),
        'fields'      => array(
            array(
                'name'        => 'term',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('按钮名称', 'i_theme'),
                'tips'        => __('渠道名称，比如爱奇艺、当当等', 'i_theme'),
                'placeholder' => '',
                'required'    => true,
            ),
            array(
                'name'        => 'url',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('渠道地址', 'i_theme'),
                'tips'        => '',
                'placeholder' => 'http://',
                'required'    => true,
            ),
            array(
                'name'        => 'price',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('价格', 'i_theme'),
                'tips'        => __('比如杂志的订阅价格 100/年', 'i_theme'),
                'placeholder' => '',
                'required'    => false,
            )
        )
    );
    return io_get_modal_meta_set_html($edit_data['buy_list'], $dow_args, ['term', 'url']);
}
/**
 * 获取图书下载地址表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_book_down_list_html($edit_data)
{
    $dow_args = array(
        'id'          => 'book_down_list',
        'name'        => 'down_list',
        'title'       => __('下载地址', 'i_theme'),
        'tooltip'     => __('添加资源下载地址', 'i_theme'),
        'modal_title' => __('编辑下载地址', 'i_theme'),
        'tips'        => __('电子存档文件的下载地址。', 'i_theme'),
        'fields'      => array(
            array(
                'name'        => 'name',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('按钮名称', 'i_theme'),
                'tips'        => '',
                'placeholder' => '',
                'required'    => true,
            ),
            array(
                'name'        => 'url',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('下载地址', 'i_theme'),
                'tips'        => '',
                'placeholder' => 'http://',
                'required'    => true,
            ),
            array(
                'name'        => 'tqm',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('提取码', 'i_theme'),
                'tips'        => '',
                'placeholder' => '',
                'required'    => false,
            ),
            array(
                'name'        => 'info',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('描述', 'i_theme'),
                'tips'        => __('格式、大小、版本、更新时间等', 'i_theme'),
                'placeholder' => '',
                'required'    => false,
            ),
        )
    );
    return io_get_modal_meta_set_html($edit_data['down_list'], $dow_args, ['name', 'url']);
}

/**
 * 获取 app meta 表单HTML
 * @param mixed $post_type
 * @param mixed $edit_data
 * @return string
 */
function io_get_app_meta_form_html($post_type, $edit_data)
{
    // 软件基础信息
    $basis = io_get_app_basis_meta_html($edit_data);

    // 资源下载地址
    $download = io_get_app_down_list_html($edit_data);

    return $basis . $download;
}

/**
 * 软件基础信息表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_app_basis_meta_html($edit_data){
    // 支持平台选择
    $platform = '<div class="form-group app-platform-box">';
    $platform .= '<p class="contr-title text-muted text-sm mb-2">' . __('支持平台', 'i_theme') . '</p>';
    $platform .= '<div class="label-group">';
    foreach (get_app_platform() as $key => $value) {
        $selected = in_array($key, $edit_data['platform']) ? 'checked' : '';
        $platform .= '<label class="m-1"><input type="checkbox" value="' . $key . '" name="platform[]" class="hide" ' . $selected . ' /><span class="form-radio multiple">' . $value . '</span></label>';
    }
    $platform .= '</div>';
    $platform .= '</div>';

    // 官网地址
    $website = '<div class="form-group app-down-formal-box">';
    $website .= '<p class="contr-title text-muted text-sm mb-2">' . __('官网地址', 'i_theme') . '</p>';
    $website .= '<input type="url" name="down_formal" class="form-control text-sm" value="' . $edit_data['down_formal'] . '" placeholder="http://">';
    $website .= '</div>';

    // 截图
    $screenshot = io_get_app_screenshot_html($edit_data);


    $html = '<div class="card mb-3">';

    $html .= '<div class="card-body">';
    $html .= $platform;
    $html .= $website;
    $html .= $screenshot;
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

/** 
 * 软件截图表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_app_screenshot_html($edit_data){
    $size = (float) io_get_option('screenshot_img_size', 1);
    if ($size <= 0) {
        return '';
    }
    $html = '<div class="screenshot-set-box" data-media-type="screenshot" data-size="' . $size . '">';
    $html .= '<div class="d-flex align-items-center mb-1">';
    $html .= '<span class="contr-title text-muted text-sm">' . __('截图', 'i_theme') . '</span>';
    $html .= '<div class="btn vc-l-blue btn-outline btn-sm ml-auto screenshot-add meta-item-add" data-toggle="tooltip" title="' . __('添加截图', 'i_theme') . '"><i class="iconfont icon-add-o"></i></div>';
    $html .= '</div>';
    $html .= '<div class="screenshot-body">';

    $html .= '<div class="screenshot-none" style="' . (empty($edit_data['screenshot']) ? '' : 'display:none') . '">';
    $html .= '<div class="text-muted text-sm">' . __('暂无截图', 'i_theme') . '</div>';
    $html .= '</div>';

    $html .= '<div class="row-a row-sm row-col-3a" data-name="screenshot[0][img]">';
    if (!empty($edit_data['screenshot'])) {
        foreach ($edit_data['screenshot'] as $index => $value) {
            $html .= '<div class="screenshot-item">';
            $html .= '<div class="screenshot-item-img">';
            $html .= '<img src="' . $value['img'] . '" alt="' . __('截图', 'i_theme') . '" class="">';
            $html .= '</div>';
            $html .= '<input type="hidden" name="screenshot[' . $index . '][img]" value="' . $value['img'] . '">';
            $html .= '<div class="screenshot-item-delete"><i class="iconfont icon-close"></i></div>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';

    $html .= '</div>';
    // 提示
    $html .= '<div class="tips-box vc-l-blue btn-block text-left br-xs text-xs mt-1"><i class="iconfont icon-tishi mr-1"></i>' . sprintf(__('可增删、排序，截图大小不能超过%sMB。', 'i_theme'), $size) . '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 软件版本和对应下载地址表单HTML
 * @param mixed $edit_data
 * @return string
 */
function io_get_app_down_list_html($edit_data)
{
    // 资源下载地址
    $dow_args = array(
        'id'          => 'app_version_list',
        'name'        => 'down_list',
        'title'       => __('版本管理', 'i_theme'),
        'tooltip'     => __('添加资源版本', 'i_theme'),
        'modal_title' => __('编辑版本', 'i_theme'),
        'tips'        => __('版本管理和电子存档文件的下载地址，第一个为最新版本。', 'i_theme'),
        'auto_index'  => 'index',
        'fields'      => array(
            array(
                'type'   => 'group',
                'title'  => __('版本、大小、更新日期', 'i_theme'),
                'tips'   => __('数据包大小请自行填写单位：KB,MB,GB,TB', 'i_theme'),
                'inputs' => array(
                    array(
                        'name'        => 'app_version',
                        'type'        => 'text',
                        'value'       => '',
                        'title'       => __('版本号', 'i_theme'),
                        'tips'        => '',
                        'placeholder' => __('版本号', 'i_theme'),
                        'required'    => true,
                    ),
                    array(
                        'name'        => 'app_size',
                        'type'        => 'text',
                        'value'       => '',
                        'title'       => __('大小', 'i_theme'),
                        'placeholder' => __('大小', 'i_theme'),
                        'default'     => io_get_app_default_down('app_size',''),
                    ),
                    array(
                        'name'        => 'app_date',
                        'type'        => 'date',
                        'value'       => current_time('Y-m-d'),
                        'title'       => __('更新日期', 'i_theme'),
                        'placeholder' => __('更新日期', 'i_theme'),
                        'required'    => true,
                    ),
                )
            ),
            array(
                'name'        => 'app_status',
                'type'        => 'radio',
                'value'       => '',
                'title'       => __('APP状态', 'i_theme'),
                'tips'        => '',
                'placeholder' => '',
                'required'    => false,
                'options'     => array(
                    'official' => __('官方版', 'i_theme'),
                    'cracked'  => __('开心版', 'i_theme'),
                    'other'    => __('自定义', 'i_theme'),
                ),
                'inline'      => true,
                'default'     => io_get_app_default_down('app_status','official'),
            ),
            array(
                'name'        => 'status_custom',
                'type'        => 'text',
                'value'       => '',
                'placeholder' => __('请填写自定义状态', 'i_theme'),
                'tips'        => __('留空则不显示', 'i_theme'),
                'default'     => io_get_app_default_down('status_custom',''),
                'dependency'  => array('app_status', '==', 'other'),
            ),
            array(
                'name'   => 'app_ad',
                'type'   => 'switch',
                'value'  => '',
                'title'  => __('是否有广告', 'i_theme'),
                'tips'   => '',
                'inline' => true,
                'default' => io_get_app_default_down('app_ad',false),
            ),
            array(
                'name'        => 'cpu',
                'type'        => 'checkbox',
                'value'       => '',
                'title'       => __('支持的 CPU', 'i_theme'),
                'tips'        => __('苹果 M 系列芯片也是 ARM', 'i_theme'),
                'placeholder' => '',
                'required'    => false,
                'options'     => array(
                    'x86' => 'X86',
                    'arm' => 'ARM'
                ),
                'inline'      => true,
                'default'     => io_get_app_default_down('cpu',['x86']),
            ),
            array(
                'name'        => 'app_language',
                'type'        => 'text',
                'value'       => '',
                'title'       => __('支持语言', 'i_theme'),
                'tips'        => __('用逗号隔开', 'i_theme'),
                'placeholder' => '',
                'default'     => io_get_app_default_down('app_language', '中文'),
            ),
            array(
                'id'          => 'down_url_child',
                'name'        => 'down_url',
                'type'        => 'modal',
                'title'       => __('下载地址', 'i_theme'),
                'tooltip'     => __('添加资源下载地址', 'i_theme'),
                'modal_title' => __('编辑下载地址', 'i_theme'),
                'is_child'    => true,
                'tips'        => __('电子存档文件的下载地址。', 'i_theme'),
                'fields'      => array(
                    array(
                        'name'     => 'down_btn_name',
                        'type'     => 'text',
                        'value'    => '',
                        'title'    => __('按钮名称', 'i_theme'),
                        'required' => true,
                    ),
                    array(
                        'name'     => 'down_btn_url',
                        'type'     => 'text',
                        'value'    => '',
                        'title'    => __('下载地址', 'i_theme'),
                        'required' => true,
                    ),
                    array(
                        'name'  => 'down_btn_tqm',
                        'type'  => 'text',
                        'value' => '',
                        'title' => __('提取码', 'i_theme'),
                    ),
                    array(
                        'name'  => 'down_btn_info',
                        'type'  => 'text',
                        'value' => '',
                        'title' => __('描述', 'i_theme'),
                        'tips'  => __('资源名称或者其他说明。', 'i_theme'),
                    ),
                ),
            ),
            array(
                'name'  => 'index',
                'type'  => 'hidden',
                'value' => '1',
            ),
            array(
                'name'  => 'pay_price',
                'type'  => 'hidden',
                'value' => '0',
            ),
            array(
                'name'  => 'price',
                'type'  => 'hidden',
                'value' => '0',
            ),
            array(
                'name'        => 'version_describe',
                'type'        => 'textarea',
                'value'       => '',
                'title'       => __('版本描述', 'i_theme'),
                'tips'        => '',
                'placeholder' => '',
            ),
        )
    );
    return io_get_modal_meta_set_html($edit_data['down_list'], $dow_args, ['app_version', 'app_date']);
}

/**
 * 获取模态框设置表单HTML
 * @param array $data 默认数据
 * @param array $args 表单元素
 * @param string|array $title_by 标题字段 key 或 key 数组
 * @return string
 */
function io_get_modal_meta_set_html($data, $args, $title_by = '')
{
    global $meta_modal_set_data;
    // 添加 modal 弹窗
    if (!$meta_modal_set_data || !is_array($meta_modal_set_data)) {
        $meta_modal_set_data = [];
    }
    $meta_modal_set_data[] = $args;

    $name = $args['name'];
    $child_id = isset($args['is_child']) && $args['is_child'] ? $args['id'] : '';

    $head = '<div class="d-flex align-items-center">
                <span class="contr-title text-muted text-sm">' . $args['title'] . '</span>
                <div class="btn vc-l-blue btn-outline btn-sm ml-auto meta-item-add" data-toggle="tooltip" title="' . $args['tooltip'] . '"><i class="iconfont icon-add-o"></i></div>
                <div class="meta-item-prefab" style="display:none;">
                    ' . io_get_mode_add_form_html($name, $args['fields'], 0, $title_by, $child_id, true) . '
                </div>
            </div>';

    $input = '';
    if (!empty($data)) {
        foreach ($data as $index => $value) {
            $input .= io_get_mode_add_form_html($name, $value, $index, $title_by, $child_id);
        }
    }

    $tips = '';
    if ($args['tips']) {
        $tips = '<div class="tips-box vc-l-blue btn-block text-left br-xs text-xs mt-1"><i class="iconfont icon-tishi mr-1"></i>' . $args['tips'] . '</div>';
    }

    $is_child = isset($args['is_child']) && $args['is_child'] ? true : false;
    $auto_index = isset($args['auto_index']) && $args['auto_index'] ? ' data-auto-index="' . $args['auto_index'] . '"' : '';

    $body = sprintf(
        '<div class="meta-item-group m-0" data-modal-target="#%s"%s is-child="%s" data-title-by="%s">',
        esc_attr($args['id']),
        $auto_index,
        esc_attr($is_child),
        esc_attr(json_encode((array) $title_by))
    );
    $body .= $head;
    $body .= '<div class="meta-item-body">';
    $body .= $input;
    $body .= '</div>';
    $body .= '</div>';
    $body .= $tips;

    $html = '';
    if (empty($child_id)) {
        $html .= '<div class="card mb-3">';

        $html .= '<div class="card-body">';
        $html .= $body;
        $html .= '</div>';

        $html .= '</div>';
    }else{
        $html .= '<div class="mb-3">';
        $html .= $body;
        $html .= '</div>';
    }
    return $html;
}

/**
 * 获取模态框设置表单HTML
 * @param mixed $name 表单name
 * @param array $data 数据
 * @param mixed $index 索引
 * @param string|array $title_by 标题字段 key 或 key 数组
 * @param string $child_id 子项ID
 * @param mixed $is_prefab 是否预制
 * @return string
 */
function io_get_mode_add_form_html($name, $data, $index = 0, $title_by = '', $child_id = '', $is_prefab = false)
{
    if ($is_prefab) {
        $data = io_flatten_set_array($data);
    }
    $show_v = '';
    if (!empty($title_by)) {
        if (is_array($title_by)) {
            foreach ($title_by as $key) {
                $show_v .= $data[$key] . ' ';
            }
            $show_v = trim($show_v);
        } else {
            $show_v = $data[$title_by];
        }
    } else {
        $show_v = reset($data);
    }
    $show_v = $show_v ?: __('待编辑', 'i_theme');

    $child = $child_id ? ' data-child="#' . $child_id . '"' : '';

    $attr = $is_prefab ? 'data-name' : 'name';

    $input = '<div class="meta-data-box" data-index="' . $index . '">';
    $input .= '<div class="meta-value-box word-break text-muted text-xs flex-fill" modal-set>';
    $input .= '<span class="show-item-title line2">' . esc_html($show_v) . '</span>';
    $input .= '<div class="hide-input">';
    foreach ($data as $key => $value) {
        $_n  = $name . '[' . $index . '][' . $key . ']';
        $_id = $child_id ? '[' . $index . ']' . $key : $key;
        if (is_array($value)) {
            foreach ($value as $i => $item) {
                if (is_array($item)) {
                    // 子模块
                    // 如果是预制件则不处理
                    if ($is_prefab) {
                        continue;
                    }
                    $_child = ' data-child="#' . $key . '_child"';
                    foreach ($item as $k => $v) {
                        $__n   = $_n . '[' . $i . '][' . $k . ']';
                        $input .= '<input type="hidden" ' . $attr . '="' . esc_attr($__n) . '"' . $_child . ' data-id="' . esc_attr('[' . $i . ']' . $k) . '" value="' . esc_attr($v) . '"/>';
                    }
                } else {
                    // 多选表单
                    $input .= '<input type="hidden" ' . $attr . '="' . esc_attr($_n) . '[]"' . $child . ' data-id="' . esc_attr($_id) . '[]" is-multi="true" value="' . esc_attr($item) . '"/>';
                }
            }
        } else {
            $input .= '<input type="hidden" ' . $attr . '="' . esc_attr($_n) . '"' . $child . ' data-id="' . esc_attr($_id) . '" value="' . esc_attr($value) . '"/>';
        }
    }
    $input .= '</div>';
    $input .= '</div>';
    $input .= '<div class="meta-helper-box d-flex align-items-center">';
    $input .= '<div class="meta-btn meta-item-sort"><i class="iconfont icon-classification"></i></div>';
    $input .= '<div class="meta-btn meta-item-delete"><i class="iconfont icon-close-circle"></i></div>';
    $input .= '</div>';
    $input .= '</div>';

    return $input;
}

/**
 * 将多维数组或对象的特定字段提取到一个扁平化的数组中
 * 该函数主要用于处理包含 'group' 或 'modal' 类型项的数组，将它们的子项提取出来
 * 
 * @param array $array 需要被处理的多维数组或对象
 * @return array 返回一个扁平化的数组，包含提取出的子项
 */
function io_flatten_set_array($array)
{
    global $meta_modal_set_data;

    $result = [];

    foreach ($array as $item) {
        // 检查当前项是否为 'group' 类型
        if ($item['type'] === 'group' && isset($item['inputs'])) {
            // 遍历 'group' 内的子项，并将它们提取到结果数组中
            foreach ($item['inputs'] as $subItem) {
                if (isset($subItem['name'])) {
                    // 设置默认值，并将子项的值或默认值添加到结果数组中
                    $default                  = isset($subItem['default']) ? $subItem['default'] : '';
                    $result[$subItem['name']] = $subItem['value'] ?: $default;
                }
            }
        } elseif ($item['type'] === 'modal' && isset($item['fields'])) {
            // 处理 'modal' 类型的项，将其添加到全局变量中
            if (!$meta_modal_set_data || !is_array($meta_modal_set_data)) {
                $meta_modal_set_data = [];
            }
            $meta_modal_set_data[] = $item;

            // 递归处理 'modal' 的字段，并将结果添加到结果数组中
            $result[$item['name']][] = io_flatten_set_array($item['fields']);
        } else {
            // 处理既不是 'group' 也不是 'modal' 类型的项，直接将其值或默认值添加到结果数组中
            $default               = isset($item['default']) ? $item['default'] : '';
            $result[$item['name']] = $item['value'] !== '' ? $item['value'] : $default;
        }
    }

    return $result;
}
/**
 * 显示游客投稿人信息
 * @param mixed $post
 * @return void
 */
function display_guest_info_meta_box($post) {
    $guest_info = get_post_meta($post->ID, 'guest_info', true);
    // 如果 guest_info 存在，则添加 meta box
    if ($guest_info) {
        add_meta_box(
            'guest_info_meta_box',         // Meta box ID
            '投稿人',                       // Meta box 标题
            'show_guest_info_meta_box',    // 回调函数
            $post->post_type,              // 当前文章类型
            'side',                        // 在右边栏显示
            'high'                         // 优先级
        );
    }
}
function add_guest_info_meta_box_conditionally() {
    // 定义要显示的文章类型
    $post_types = ['post', 'sites', 'book', 'app'];
    
    foreach ($post_types as $post_type) {
        add_action("add_meta_boxes_{$post_type}", 'display_guest_info_meta_box');
    }
}
add_action('add_meta_boxes', 'add_guest_info_meta_box_conditionally');
function show_guest_info_meta_box($post) {
    $guest_info = get_post_meta($post->ID, 'guest_info', true);
    echo '<div>';
    echo '<strong>投稿人:</strong> '; 
    echo '<span>' . esc_html($guest_info['name']) . '</span>';
    echo '</div>';
    echo '<div>';
    echo '<strong>联系方式:</strong> '; 
    echo '<span>' . esc_html($guest_info['contact']) . '</span>';
    echo '</div>';
    echo '<div>';
    echo '<strong>投稿时间:</strong> '; 
    echo '<span>' . esc_html($guest_info['time']) . '</span>';
    echo '</div>';
}

/**
 * 获取可投稿类型
 * @return array
 */
function io_get_contribute_allow_type()
{
    $contribute_type = io_get_option('contribute_type', array('sites')); // 投稿类型
    if(empty($contribute_type)){
        return array();
    }
    $posts_type_s    = wp_parse_args((array) io_get_option('posts_type_s'), ['post']); // 启用的文章类型
    $contribute_type = array_values(array_intersect($contribute_type, $posts_type_s));
    return $contribute_type;
}

/**
 * 投稿 js var
 * @param mixed $var
 */
function io_contribute_js_var($var)
{
    $var['contributeVar'] = array(
        'cover_img_size'   => io_get_option('cover_img_size', 0.6),
        'favicon_img_size' => io_get_option('favicon_img_size', 0.6),
        'icon_img_size'    => io_get_option('icon_img_size', 0.6),
        'post_img_max'     => io_get_option('posts_img_size', 1),
        'theme_key'        => ioThemeKey(),
        'media_type'       => io_get_media_type('', false),
        'local'            => array(
            'timeout'             => __('网络连接错误！', 'i_theme'),
            'cover_img_size_msg'  => sprintf(__('图片大小不能超过 %s MB', 'i_theme'), io_get_option('cover_img_size', 0.6)),
            'get_failed'          => __('获取失败，请再试试，或者手动填写！', 'i_theme'),
            'get_success'         => __('获取成功，没有的请手动填写！', 'i_theme'),
            'timeout2'            => __('访问超时，请再试试，或者手动填写！', 'i_theme'),
            'url_error'           => __('链接格式错误！', 'i_theme'),
            'fill_url'            => __('请先填写网址链接！', 'i_theme'),
            'image'               => _x('图片', 'mce', 'i_theme'),
            'video'               => _x('视频', 'mce', 'i_theme'),
            'attachment'          => _x('附件', 'mce', 'i_theme'),
            'all_att'             => _x('所有', 'mce', 'i_theme'),
            'my_title'            => _x('我的%s', 'mce', 'i_theme'),
            'out_title'           => _x('外链%s', 'mce', 'i_theme'),
            'start_uploading'     => __('开始上传', 'i_theme'),
            'max_file_size'       => __('最大支持%sMB，', 'i_theme'),
            'single_count'        => __('单次可上传%d个文件，', 'i_theme'),
            'select_att'          => __('选择%s', 'i_theme'),
            'drag_upload'         => __('将%s拖到这里上传', 'i_theme'),
            'uncheck'             => __('取消选中', 'i_theme'),
            'please_select'       => __('请选择', 'i_theme'),
            'other_tips'          => __('支持拖文件上传，支持粘贴板。', 'i_theme'),
            'out_tips'            => __('请填写图片地址，支持批量输入，一行一个链接。', 'i_theme'),
            'ok'                  => __('确定', 'i_theme'),
            'not_refresh'         => __('还有文件正在上传，请勿刷新页面！', 'i_theme'),
            'first_upload'        => __('请先上传图片！', 'i_theme'),
            'file_uploading'      => __('文件正在上传中，请稍后操作！', 'i_theme'),
            'confirm_delete'      => __('确定要删除该文件吗？删除后不可恢复！', 'i_theme'),
            'loading'             => __('加载中', 'i_theme'),
            'input_url'           => __('请输入地址！', 'i_theme'),
            'max_input_count'     => __('最多可输入%d个%s地址', 'i_theme'),
            'tobe_uploaded'       => __('待上传', 'i_theme'),
            'uploading'           => __('上传中...', 'i_theme'),
            'upload_failed'       => __('上传失败', 'i_theme'),
            'upload_success'      => __('上传成功', 'i_theme'),
            'processing'          => __('处理中...', 'i_theme'),
            'load_more'           => __('加载更多', 'i_theme'),
            'log_max_up_file'     => __('最多上传%d个文件。', 'i_theme'),
            'log_max_size_file'   => __('文件大小不能超过%sMB。', 'i_theme'),
            'log_file_type'       => __('文件类型不支持！', 'i_theme'),
            'log_upload_failed'   => __('上传失败，请重试！', 'i_theme'),
            'log_max_select_file' => __('最多只能选择%d个附件。', 'i_theme'),
            'no_empty'            => __('不能为空', 'i_theme'),
            'btn_delete'          => __('删除', 'i_theme'),
        ),
    );
    return $var;
}
