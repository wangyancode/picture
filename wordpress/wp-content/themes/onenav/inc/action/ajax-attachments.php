<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-10-28 16:58:13
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-22 23:26:37
 * @FilePath: /onenav/inc/action/ajax-attachments.php
 * @Description: 
 */


/**
 * 上传用户附件，支持分片上传和普通上传
 * 
 * 图片、压缩包、视频、音频等文件都可以上传
 * 
 * @return void
 */
function io_upload_user_attachments()
{
    //必须登录
    $user_id = get_current_user_id();
    if (!$user_id) {
        __echo(3, __('请先登录', 'i_theme'));
    }

    if (!wp_verify_nonce($_POST['_wpnonce'],'upload_user_attachments')){
        io_error('{"status":2,"msg":"'.__('安全检查失败，请刷新或稍后再试！','i_theme').'"}');
    } 
    
    // 检查是否有文件上传
    if (!empty($_FILES['file'])) {
        $user_id = get_current_user_id();  // 获取当前用户ID

        // 检查用户权限
        //if (!current_user_can('upload_files')) {
        //    __echo(4, '您没有权限上传文件');
        //    return;
        //}

        $upload_dir         = wp_upload_dir();     // 获取上传目录
        $original_file_name = sanitize_file_name($_POST['name']);  // 原始文件名称
        $prefix             = sanitize_file_name($_POST['prefix']) . '_';  // 前缀
        $file_name          = $prefix . $original_file_name;  // 为文件名添加前缀
        $chunk              = isset($_POST['chunk']) ? intval($_POST['chunk']) : -1;  // 当前分片索引，如果不存在分片，则为 -1
        $chunks             = isset($_POST['chunks']) ? intval($_POST['chunks']) : 1;  // 总分片数
        $final_file_path    = $upload_dir['path'] . '/' . $file_name;  // 最终文件路径

        // 非分片上传，进行文件保存
        if ($chunk === -1) {
            // 直接保存非分片文件
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $final_file_path)) {
                __echo(4, '文件上传失败');
                return;
            }

            // 处理文件（插入媒体库、验证类型等）
            io_handle_uploaded_file($final_file_path, $original_file_name, $upload_dir);

        } else {
            // 处理分片上传：保存当前分片
            $temp_dir = $upload_dir['path'] . '/chunks_' . $file_name;
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir, 0777, true);
            }
            $temp_file_path = $temp_dir . '/' . $file_name . '_part_' . $chunk;

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $temp_file_path)) {
                __echo(4, '分片上传失败');
                return;
            }

            __echo(1, '第 ' . ($chunk + 1) . ' 分片上传成功，共 ' . $chunks . ' 分片', ['is_chunk' => 1]);
        }

    } else {
        __echo(3, __('没有文件上传', 'i_theme'));
    }
}
add_action('wp_ajax_upload_user_attachments', 'io_upload_user_attachments');
add_action('wp_ajax_nopriv_upload_user_attachments', 'io_upload_user_attachments');

/**
 * 合并附件分片
 * @return  void
 */
function io_merge_attachments_chunks()
{
    //必须登录
    $user_id = get_current_user_id();
    if (!$user_id) {
        __echo(3, __('请先登录', 'i_theme'));
    }

    $original_file_name = sanitize_file_name($_POST['name']);  // 原始文件名称
    $prefix             = sanitize_file_name($_POST['prefix']) . '_';  // 前缀
    $file_name          = $prefix . $original_file_name;  // 为文件名添加前缀 
    $chunks             = intval($_POST['chunks']); // 总分片数
    $upload_dir         = wp_upload_dir();
    $temp_dir           = $upload_dir['path'] . '/chunks_' . $file_name;
    $final_file_path    = $upload_dir['path'] . '/' . $file_name;

    $lock_file = $temp_dir . '/.lock';
    if (file_exists($lock_file)) {
        __echo(4, '文件正在合并，请稍候');
    }
    touch($lock_file); // 创建锁文件

    // 打开目标文件
    if (($out = fopen($final_file_path, 'wb')) !== false) {
        for ($i = 0; $i < $chunks; $i++) {
            $part_file_path = $temp_dir . '/' . $file_name . '_part_' . $i;
            if (!file_exists($part_file_path)) {
                unlink($lock_file); // 删除锁文件
                __echo(4, '缺少部分文件分片');
            }

            // 读取分片并写入目标文件
            if (($in = fopen($part_file_path, 'rb')) !== false) {
                stream_copy_to_stream($in, $out);
                fclose($in);
                unlink($part_file_path); // 删除已处理的分片
            }
        }
        fclose($out);

        // 删除临时目录和锁文件
        unlink($lock_file);
        rmdir($temp_dir);

        // 处理文件（插入媒体库、验证类型等）
        io_handle_uploaded_file($final_file_path, $original_file_name, $upload_dir);


    } else {
        __echo(4, '文件合并失败');
    }
}
add_action('wp_ajax_merge_attachments_chunks', 'io_merge_attachments_chunks');

/**
 * 允许上传SVG文件
 * @param mixed $mimes
 * @return mixed
 */
function io_allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
//add_filter('upload_mimes', 'io_allow_svg_upload');


/**
 * 处理上传的文件
 * 
 * @param mixed $final_file_path 最终文件路径
 * @param mixed $file_name      文件名
 * @param mixed $upload_dir    上传目录
 * @return void
 */
function io_handle_uploaded_file($final_file_path, $file_name, $upload_dir) {
    $md5 = __post('md5');
    // 检查文件类型
    $file_mime_type = wp_check_filetype($final_file_path)['type'];
    // jpg jpeg png gif svg zip rar 7z mp4 mp3
    $allowed_mime_types = array(
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'video/mp4',
        'audio/mpeg'
    );

    if (!in_array($file_mime_type, $allowed_mime_types)) {
        unlink($final_file_path); // 删除不允许的文件
        __echo(4, '文件类型不被允许');
        return;
    }

    $media_type = __post('media_type', 'default'); // 自定义媒体类型

    // 将文件添加到 WordPress 媒体库
    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . basename($final_file_path),
        'post_mime_type' => $file_mime_type,
        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_name)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $final_file_path, 0);

    // 处理文件元数据
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $final_file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // 如果是图片，增加自定义图片类型
    if (strpos($file_mime_type, 'image') === 0) {
        update_post_meta($attach_id, '_media_type', $media_type); // default, favicon, icon, cover
    }
    // 保存MD5到数据库
    update_post_meta($attach_id, '_file_md5', $md5);

    // 返回上传文件的 URL
    $uploaded_files = io_prepare_attachment_for_js($attach_id);
    __echo(1, sprintf(__('文件 %s 上传成功', 'i_theme'), $file_name), $uploaded_files);
}


 
/**
 * 获取用户已上传的附件
 * 
 * 支持分页
 * @return mixed
 */
function io_get_user_uploaded_attachments()
{
    //必须登录
    $user_id = get_current_user_id();
    if (!$user_id) {
        __echo(3, __('请先登录', 'i_theme'));
    }
    $paged      = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $per_page   = isset($_POST['per_page']) ? intval($_POST['per_page']) : 24;
    $type       = __post('type', 'image'); // 附件类型 image,video,audio,document,other
    $exclude    = __post('exclude', array()); // 排除的附件ID
    $media_type = __post('media_type', ''); // 自定义媒体类型

    $args = [
        'post_type'      => 'attachment',
        'post_mime_type' => $type,
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'post_status'    => 'inherit',
        'author'         => $user_id,
        'post__not_in'   => $exclude,
    ];
    if ($media_type) {
        $args['meta_query'] = array(
            array(
                'key'     => '_media_type',
                'value'   => $media_type,
                'compare' => '=',
            ),
        );
    }

    $images_query = new WP_Query($args);

    $img = [];
    if ($images_query->have_posts()) {
        foreach ($images_query->posts as $post) {
            $img[] = io_prepare_attachment_for_js($post);
        }
        $all_pages  = $images_query->max_num_pages;
        $timer_stop = timer_stop();
        __echo(1, '', array(
            'images'     => $img,
            'all_pages'  => $all_pages,
            'timer_stop' => $timer_stop
        ));
    } else {
        $html = '';
        if($paged === 1){
            $html = get_none_html(__('没有图片', 'i_theme'));
        }
        __echo(2, __('没有更多图片', 'i_theme'), [], $html);
    }
}
add_action('wp_ajax_get_user_attachments', 'io_get_user_uploaded_attachments');
add_action('wp_ajax_nopriv_get_user_attachments', 'io_get_user_uploaded_attachments');



/**
 * 删除用户上传的附件
 * 
 * @return mixed
 */
function io_delete_user_attachments()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        __echo(3, __('请先登录', 'i_theme'));
    }
    $attachment_id = __post('id');
    $attachment    = get_post($attachment_id);
    if (!$attachment) {
        __echo(3, __('附件不存在', 'i_theme'));
    }
    if ($attachment->post_author != $user_id) {
        __echo(3, __('你没有权限删除该附件', 'i_theme'));
    }
    wp_delete_attachment($attachment_id, true);
    __echo(1, __('附件删除成功', 'i_theme'));
}
add_action('wp_ajax_delete_user_attachments', 'io_delete_user_attachments');



/**
 * ajax 判断文件md5是否存在,如果存在则返回附件 json 数据
 * 
 * @return void
 */
function io_check_user_attachments_md5_exists()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        __echo(3, __('请先登录', 'i_theme'));
    }
    $md5 = __post('md5');
    global $wpdb;
    $attachment_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_file_md5' AND meta_value = %s",
        $md5
    ));
    if ($attachment_id) {
        __echo(1, '文件已存在', io_prepare_attachment_for_js($attachment_id));
    } else {
        __echo(1, '文件不存在');
    }
}
add_action('wp_ajax_check_user_attachments', 'io_check_user_attachments_md5_exists');
add_action('wp_ajax_nopriv_check_user_attachments', 'io_check_user_attachments_md5_exists');




/**
 * 为JavaScript准备附件数据
 *
 * 该函数基于WordPress内置的wp_prepare_attachment_for_js函数获取附件数据，并根据需要进行额外处理
 * 主要针对图像类型的附件，添加不同尺寸的URL字段，并移除一些不必要的数据字段，以适应特定的JS需求
 *
 * @param mixed $attachment 附件ID或附件对象，用于获取附件数据
 * @return array 处理后的附件数据数组，包含额外的图像尺寸URL和移除了一些不需要的字段
 */
function io_prepare_attachment_for_js($attachment)
{

    // 获取附件的基本数据
    $attachment_data = wp_prepare_attachment_for_js($attachment);

    // 如果附件类型为图像，则添加不同尺寸的图像URL
    if ($attachment_data['type'] === 'image') {
        // 添加大尺寸图像URL，如果没有大尺寸，则使用原图URL
        $attachment_data['large_url']     = !empty($attachment_data['sizes']['large']['url']) ? $attachment_data['sizes']['large']['url'] : $attachment_data['url'];
        // 添加中尺寸图像URL，如果没有中尺寸，则使用大尺寸URL
        $attachment_data['medium_url']    = !empty($attachment_data['sizes']['medium']['url']) ? $attachment_data['sizes']['medium']['url'] : $attachment_data['large_url'];
        // 添加缩略图尺寸图像URL，如果没有缩略图，则使用中尺寸URL
        $attachment_data['thumbnail_url'] = !empty($attachment_data['sizes']['thumbnail']['url']) ? $attachment_data['sizes']['thumbnail']['url'] : $attachment_data['medium_url'];
    }

    // 移除附件数据中的一些不需要的字段，以减轻数据负担
    foreach (array('authorLink', 'editLink', 'icon', 'link', 'nonces') as $k) {
        // 如果当前字段存在于附件数据中，则移除该字段
        if (isset($attachment_data[$k])) {
            unset($attachment_data[$k]);
        }
    }

    // 返回处理后的附件数据
    return $attachment_data;
}





















/**
 * 获取用户上传的附件类型
 * 
 * @param string $type 附件类型
 * @param bool $all 是否返回所有类型
 * @return string|string[]
 */
function io_get_media_type($type = '', $all = true)
{
    $data = array(
        //'default' => __('普通图片', 'i_theme'),
        //'favicon' => __('网址图标', 'i_theme'),
        //'icon'    => __('app图标', 'i_theme'),
        //'cover'   => __('书籍封面', 'i_theme'),
    );
    if ($all || io_get_option('posts_img_size')) {
        $data['default'] = __('普通图片', 'i_theme');
    }
    if ($all || io_get_option('favicon_img_size')) {
        $data['favicon'] = __('网址图标', 'i_theme');
    }
    if ($all || io_get_option('icon_img_size')) {
        $data['icon'] = __('app图标', 'i_theme');
    }
    if ($all || io_get_option('cover_img_size')) {
        $data['cover'] = __('书籍封面', 'i_theme');
    }
    if ($all || io_get_option('screenshot_img_size')) {
        $data['screenshot'] = __('截图', 'i_theme');
    }
    if ($all || io_get_option('preview_img_size', 0)) {
        $data['preview'] = __('网址截图', 'i_theme');
    }

    if (empty($type)) {
        return $data;
    }
    if (isset($data[$type])) {
        return $data[$type];
    }
    return __('未定义', 'i_theme');
}




/**
 * 媒体类型增加自定义字段
 * 
 * 只适用于列表模式，不适用于网格模式
 */
class MediaTypeMetaBox
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_media_type_meta_box']);
        add_action('edit_attachment', [$this, 'save_media_type_meta']);
        add_action('restrict_manage_posts', [$this, 'add_media_type_filter']);
        add_action('pre_get_posts', [$this, 'filter_media_by_type']);
        add_filter('manage_media_columns', [$this, 'add_media_type_column']);
        add_action('manage_media_custom_column', [$this, 'display_media_type_column'], 10, 2);
        add_filter('manage_upload_sortable_columns', [$this, 'add_upload_sortable_columns']);
    }

    // 添加 "类型" 自定义字段到媒体编辑页面
    public function add_media_type_meta_box()
    {
        add_meta_box(
            'media_type_meta_box',         // Meta box ID
            '类型',                        // 标题
            [$this, 'display_media_type_meta_box'], // 回调函数
            'attachment',                  // 文章类型(媒体文件)
            'side',                        // 位置（边栏）
            'low'                          // 优先级
        );
    }


    // 显示 "类型" 字段
    public function display_media_type_meta_box($post)
    {
        wp_nonce_field(basename(__FILE__), 'media_type_nonce'); // 安全性检查
        $media_type = get_post_meta($post->ID, '_media_type', true); // 获取当前媒体的类型值
        ?>
        <label for="media_type">选择类型：</label>
        <select name="media_type" id="media_type">
            <option value="" <?php selected($media_type, ''); ?>>未定义</option>
            <?php foreach (io_get_media_type() as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($media_type, $key); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    // 保存 "类型" 字段数据
    public function save_media_type_meta($post_id)
    {
        if (!isset($_POST['media_type_nonce']) || !wp_verify_nonce($_POST['media_type_nonce'], basename(__FILE__))) {
            return $post_id;
        }
        $new_media_type = isset($_POST['media_type']) ? sanitize_text_field($_POST['media_type']) : '';
        update_post_meta($post_id, '_media_type', $new_media_type); // 保存自定义字段
    }

    // 在媒体库的筛选栏中添加“类型”筛选选项
    public function add_media_type_filter()
    {
        $screen = get_current_screen();
        if ($screen->base === 'upload') { // 确保只在媒体库页面生效
            $selected = isset($_GET['media_type_filter']) ? $_GET['media_type_filter'] : '';
            ?>
            <select name="media_type_filter" id="media_type_filter">
                <option value="">所有类型</option>
                <?php foreach (io_get_media_type() as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selected, $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <?php
        }
    }

    // 根据“类型”字段筛选媒体文件
    public function filter_media_by_type($query)
    {
        global $pagenow;
        if (!is_admin() || $pagenow !== 'upload.php') {
            return;
        }
        // 处理媒体类型筛选
        $media_type = isset($_GET['media_type_filter']) ? sanitize_text_field($_GET['media_type_filter']) : '';
        if ($media_type) {
            $meta_query = array(
                array(
                    'key'     => '_media_type',
                    'value'   => $media_type,
                    'compare' => '='
                )
            );
            $query->set('meta_query', $meta_query);
        }

        // 处理媒体类型排序
        $orderby = $query->get('orderby');
        if ($orderby === 'media_type') {
            $query->set('meta_key', '_media_type'); // 设置 meta_key 为媒体类型字段
            $query->set('orderby', 'meta_value');   // 按 meta_value 排序
        }
    }

    // 在媒体库列表中添加“类型”列
    public function add_media_type_column($columns)
    {
        $columns['media_type'] = '类型'; // 添加新的列
        return $columns;
    }

    // 显示“类型”列中的值
    public function display_media_type_column($column_name, $post_id)
    {
        if ($column_name === 'media_type') {
            $media_type = get_post_meta($post_id, '_media_type', true);
    
            if ($media_type) {
                // 生成媒体库筛选链接，带有 media_type_filter 参数
                $filter_link = add_query_arg(array(
                    'media_type_filter' => urlencode($media_type),
                    'post_type' => 'attachment' 
                ), admin_url('upload.php'));

                echo '<a href="' . esc_url($filter_link) . '">' . esc_html(io_get_media_type($media_type)) . '</a>';
            } else {
                echo '未定义';
            }
        }
    }

    public function add_upload_sortable_columns($sortable_columns)
    {
        $sortable_columns['media_type'] = 'media_type'; // 设置 media_type 列为可排序
        return $sortable_columns;
    }
    
}
new MediaTypeMetaBox();
