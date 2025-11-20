<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-04-15 04:33:41
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-05 01:01:43
 * @FilePath: /onenav/inc/theme-update.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function updateDB(){
    $rewrite = false;
    if(is_admin()){
        $version = get_option( 'onenav_version',false );
        if(!$version){
            $version = IO_VERSION;
            update_option( 'onenav_version', $version );
        }
        if ( version_compare( $version, '3.0330', '<' ) && version_compare( $version, '2.0407', '>' ) ) {
            $rewrite = ioup_30330();
        }
        if ( version_compare( $version, '3.0731', '<' ) && version_compare( $version, '3.0330', '>=' ) ) {
            $rewrite = ioup_30731();
        }
        if ( version_compare( $version, '3.0901', '<' ) && version_compare( $version, '3.0731', '>=' ) ) {
            $rewrite = ioup_30901();
        }
        if ( version_compare( $version, '3.1421', '<' ) ) {
            $rewrite = ioup_31421();
        }
        if ( version_compare( $version, '3.1918', '<' ) && version_compare( $version, '3.0330', '>=' ) ) {
            $rewrite = ioup_31918();
        }
        if( version_compare( $version, '3.2139', '<' ) ){
            $rewrite = ioup_32139();
        }
        if( version_compare( $version, '5.03', '<' ) ){
            $rewrite = io_term_meta_data_5_03();
        }
        if(version_compare( $version, '5.05', '<' )){
            $rewrite = io_ioviews_table_add_field_5_05();
        }
        if(version_compare( $version, '5.53', '<' )){
            $rewrite = io_content_visibility_init_5_53();
        }
        if($rewrite){
            delete_transient( 'onenav_manual_update_version' );
            wp_cache_flush();
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }
    return $rewrite;
}


function ioup_30330(){
    update_option( 'onenav_version', '3.0330' );
    global $wpdb;
    $list = $wpdb->get_results("SELECT * FROM $wpdb->users");
    if($list) {
        foreach($list as $value){
            if(substr($value->user_login , 0 , 2)=="io"){
                //update_user_meta($value->ID, 'name_change', 1);
                if($value->qq_id && !get_user_meta($value->ID,'qq_openid')){
                    update_user_meta($value->ID, 'qq_avatar', get_user_meta($value->ID,'avatar',true));
                    update_user_meta($value->ID, 'qq_name', $value->display_name);
                    update_user_meta($value->ID, 'qq_openid', $value->qq_id);
                    update_user_meta($value->ID, 'avatar_type', 'qq');
                }
                if($value->wechat_id && !get_user_meta($value->ID,'wechat_openid')){
                    update_user_meta($value->ID, 'wechat_avatar', get_user_meta($value->ID,'avatar',true));
                    update_user_meta($value->ID, 'wechat_name', $value->display_name);
                    update_user_meta($value->ID, 'wechat_openid', $value->wechat_id);
                    update_user_meta($value->ID, 'avatar_type', 'wechat');
                }
                if($value->sina_id && !get_user_meta($value->ID,'sina_openid')){
                    update_user_meta($value->ID, 'sina_avatar', get_user_meta($value->ID,'avatar',true));
                    update_user_meta($value->ID, 'sina_name', $value->display_name);
                    update_user_meta($value->ID, 'sina_openid', $value->sina_id);
                    update_user_meta($value->ID, 'avatar_type', 'sina');
                }
            }
        }
    }
    $wpdb->query("ALTER TABLE `$wpdb->iocustomurl` CHANGE `url` `url` TEXT DEFAULT NULL");
    $wpdb->query("ALTER TABLE `$wpdb->iocustomurl` CHANGE `url_name` `url_name` TEXT DEFAULT NULL");
    $wpdb->query("ALTER TABLE `$wpdb->iocustomurl` CHANGE `url_ico` `url_ico` TEXT DEFAULT NULL");
    $wpdb->query("ALTER TABLE `$wpdb->iocustomterm` CHANGE `name` `name` TEXT DEFAULT NULL");
    $wpdb->query("ALTER TABLE `$wpdb->iocustomterm` CHANGE `ico` `ico` TEXT DEFAULT NULL");

    if(!column_in_db_table($wpdb->iocustomurl,'post_id')){
        $wpdb->query("ALTER TABLE $wpdb->iocustomurl ADD post_id bigint(20)");
    }
    if(!column_in_db_table($wpdb->iocustomurl,'summary')){
        $wpdb->query("ALTER TABLE $wpdb->iocustomurl ADD summary varchar(255) DEFAULT NULL");
    }
    return true;
}

function ioup_30731(){
    update_option( 'onenav_version', '3.0731' );
    global $wpdb;
    $wpdb->query("ALTER TABLE $wpdb->iocustomterm ADD INDEX `user_id` (`user_id`);");
    $wpdb->query("ALTER TABLE $wpdb->iocustomurl ADD INDEX `user_id` (`user_id`);");
    $wpdb->query("ALTER TABLE $wpdb->iocustomurl ADD INDEX `term_id` (`term_id`);");
    return true;
}

function ioup_30901(){
    update_option( 'onenav_version', '3.0901' );
    global $wpdb;
    $wpdb->query("ALTER TABLE `$wpdb->iomessages` CHANGE `msg_read` `msg_read` TEXT DEFAULT NULL");
    if(!column_in_db_table($wpdb->iomessages,'meta')){
        $wpdb->query("ALTER TABLE $wpdb->iomessages ADD `meta` text DEFAULT NULL");
    }
    return true;
}

function ioup_31421(){
    update_option( 'onenav_version', '3.1421' );
    global $wpdb ,$iodb;
    //$iodb = new IODB();
    $list = $wpdb->get_results("SELECT * FROM `$wpdb->postmeta` WHERE (`meta_key` IN ('_app_screenshot','_sites_screenshot') AND `meta_value` != '')");
    if($list){
        //$datas=array();
        foreach($list as $value){
            $app_screen = explode( ',', $value->meta_value );
            $data = array();
            for ($i=0;$i<count($app_screen);$i++) {
                $data[] = array(
                    'img'=>wp_get_attachment_image_src($app_screen[$i], 'full')[0]
                );
            }
            update_post_meta( $value->post_id, '_screenshot', $data );
            //$datas[] = array( $value->post_id, '_screenshot', maybe_serialize($data)); 
        }
        //$wpdb->query($iodb->multArrayInsert($wpdb->postmeta, array("post_id","meta_key","meta_value"),$datas));
    }
    return true;
}

function ioup_31918(){
    update_option( 'onenav_version', '3.1918' );
    global $wpdb;
    if (!column_in_db_table($wpdb->iocustomterm, 'parent')) {
        $wpdb->query("ALTER TABLE $wpdb->iocustomterm ADD `parent` bigint(20) NOT NULL DEFAULT 0 AFTER `user_id`");
    }
    return true;
}

function ioup_32139(){
    update_option( 'onenav_version', '3.2139' );
    global $wpdb;
    $list = $wpdb->get_results("SELECT * FROM $wpdb->users WHERE `user_email` REGEXP '^io.*@io.com$'");
    if($list ){
        foreach($list as $value){
            $wpdb->query("UPDATE $wpdb->users SET `user_email`='' WHERE `ID`=$value->ID");
        }
    }
    //开始更新
    io_update_post_purview();
    return true;
}


/**
 * 将 star 相关的 postmeta 数据迁移到 usermeta 中
 * @return string
 */
function io_optimize_star_data_5_0()
{
    global $wpdb;
    if(get_option('io_star_update_data', 0)){
        echo (json_encode(array(
            'error' => 1,
            'msg'   => '已经优化过了！',
        )));
        exit;
    }
    update_option('io_star_update_data', 1, false);

    set_time_limit(0);

    $meta_key_map = [
        'io_sites_star_users' => 'io_star_sites',
        'io_app_star_users'   => 'io_star_app',
        'io_post_star_users'  => 'io_star_post',
        'io_book_star_users'  => 'io_star_book',
    ];

    $batch_size    = 1000; // 每次处理的批次大小
    $offset        = 0;
    $total_deleted = 0;  

    // 遍历每个 meta_key 进行处理
    foreach ($meta_key_map as $postmeta_key => $usermeta_key) {
        $user_post_map = [];

        // 分批获取指定 meta_key 的所有记录
        do {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT `post_id`, `meta_value` FROM {$wpdb->postmeta} WHERE `meta_key` = %s LIMIT %d OFFSET %d",
                    $postmeta_key,
                    $batch_size,
                    $offset
                )
            );

            if (empty($results)) {
                break;
            }

            // 遍历结果，将 post_id 归类到对应的 user_id（meta_value）中
            foreach ($results as $row) {
                $user_id = intval($row->meta_value);
                $post_id = intval($row->post_id);

                if (!isset($user_post_map[$user_id])) {
                    $user_post_map[$user_id] = [];
                }
                $user_post_map[$user_id][] = $post_id;
            }

            $offset += $batch_size;

        } while (count($results) === $batch_size);

        // 将数据存储到 usermeta 中
        foreach ($user_post_map as $user_id => $post_ids) {
            $existing_value = get_user_meta($user_id, $usermeta_key, true);
            if (!is_array($existing_value)) {
                $existing_value = [];
            }
            $new_value = array_unique(array_merge($existing_value, $post_ids));
            update_user_meta($user_id, $usermeta_key, $new_value);
        }

        // 删除处理完的 postmeta 数据并统计删除条目数
        $delete_count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` = %s",
                $postmeta_key
            )
        );

        // 累计删除的条目数
        $total_deleted += $delete_count;

        // 重置偏移量，准备处理下一个 meta_key
        $offset = 0;
    }

    delete_transient( 'onenav_manual_update_version' );

    echo (json_encode(array(
        'error' => 0,
        'msg'   => '优化完成。共优化数据：' . $total_deleted . ' 条。请刷新页面。',
    )));
    exit;
}

/**
 * termmeta表中的meta_key更新操作
 * @global wpdb $wpdb
 * @return bool
 */
function io_term_meta_data_5_03()
{
    update_option( 'onenav_version', '5.03' );
    global $wpdb;

    // 1. 更新特定的 meta_key 为 term_io_seo
    $wpdb->query(
        "UPDATE {$wpdb->termmeta}
        SET meta_key = 'term_io_seo'
        WHERE meta_key IN ('category_meta', 'post_tag_meta', 'sitetag_meta', 'apptag_meta', 'booktag_meta', 'series_meta')"
    );

    // 2. 将 seo_title, seo_metakey, seo_desc 合并存储到 term_io_seo 中
    // 先获取所有有 seo_title, seo_metakey, seo_desc 的 term_id
    $seo_meta_keys = $wpdb->get_results(
        "SELECT term_id, meta_key, meta_value
        FROM {$wpdb->termmeta}
        WHERE meta_key IN ('seo_title', 'seo_metakey', 'seo_desc')", 
        ARRAY_A
    );

    // 保存 term_io_seo 需要更新的数据
    $term_meta_to_update = [];

    foreach ($seo_meta_keys as $meta) {
        $term_id    = $meta['term_id'];
        $meta_key   = $meta['meta_key'];
        $meta_value = $meta['meta_value'];

        if (!isset($term_meta_to_update[$term_id])) {
            $term_meta_to_update[$term_id] = [
                'seo_title'   => '',
                'seo_metakey' => '',
                'seo_desc'    => ''
            ];
        }

        $term_meta_to_update[$term_id][$meta_key] = $meta_value;
    }

    // 将数据插入或更新到 term_io_seo 中
    foreach ($term_meta_to_update as $term_id => $seo_meta) {
        // 序列化合并的数据
        $term_io_seo_value = maybe_serialize($seo_meta);

        $existing_term_io_seo = $wpdb->get_var(
            $wpdb->prepare(
            "SELECT meta_id 
            FROM {$wpdb->termmeta} 
            WHERE term_id = %d AND meta_key = 'term_io_seo'",
            $term_id
            )
        );

        if ($existing_term_io_seo) {
            // 更新
            $wpdb->update(
                $wpdb->termmeta,
                ['meta_value' => $term_io_seo_value],
                ['meta_id' => $existing_term_io_seo]
            );
        } else {
            // 插入
            $wpdb->insert(
                $wpdb->termmeta,
                [
                    'term_id'    => $term_id,
                    'meta_key'   => 'term_io_seo',
                    'meta_value' => $term_io_seo_value
                ]
            );
        }
    }

    // 3. 删除旧的 seo_title, seo_metakey, seo_desc
    $wpdb->query(
        "DELETE FROM {$wpdb->termmeta}
        WHERE meta_key IN ('seo_title', 'seo_metakey', 'seo_desc')"
    );
    return true;
}

/**
 * ioviews 表增加 favorite, like, comment, buy 字段
 * @return bool
 */
function io_ioviews_table_add_field_5_05()
{
    global $wpdb;
    update_option('onenav_version', '5.05');

    $duplicates_query = "SELECT post_id, time, COUNT(*) as count
                         FROM $wpdb->ioviews
                         GROUP BY post_id, time
                         HAVING count > 1";

    $duplicates = $wpdb->get_results($duplicates_query);

    if (!empty($duplicates)) {
        foreach ($duplicates as $row) {
            $delete_query = "DELETE FROM $wpdb->ioviews
                             WHERE post_id = %d AND time = %s
                             AND id NOT IN (
                                 SELECT id FROM (
                                     SELECT id FROM $wpdb->ioviews
                                     WHERE post_id = %d AND time = %s
                                     ORDER BY id ASC
                                     LIMIT 1
                                 ) as tmp
                             )";
            $wpdb->query($wpdb->prepare($delete_query, $row->post_id, $row->time, $row->post_id, $row->time));
        }
    }

    $sql = "ALTER TABLE `$wpdb->ioviews`
            ADD `favorite` INT(11) NOT NULL,
            ADD `like` INT(11) NOT NULL,
            ADD `comment` INT(11) NOT NULL,
            ADD `buy` INT(11) NOT NULL,
            ADD UNIQUE KEY `post_time_unique` (`post_id`, `time`);";
    $wpdb->query($sql);

    return true;
}

/**
 * 查看权限数据迁移
 * @return bool
 */
function io_content_visibility_init_5_53()
{
    global $wpdb;
    update_option('onenav_version', '5.53');

    set_time_limit(0);
    $offset     = 0;
    $batch_size = 100;
    while (io_convert_meta_to_taxonomy($batch_size, $offset)) {
        $offset += $batch_size;
        sleep(1);
    }
    return true;
}
/**
 * 查看权限数据迁移工具
 * @param mixed $batch_size
 * @param mixed $offset
 * @return bool
 */
function io_convert_meta_to_taxonomy($batch_size = 100, $offset = 0)
{
    global $wpdb;

    $level_map = [
        'all'   => 'public',
        'user'  => 'logged_in',
        'buy'   => 'purchase',
        'admin' => 'administrator',
    ];

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_user_purview_level'
        LIMIT %d OFFSET %d",
        $batch_size,
        $offset
    ));

    if (!$results) {
        IOTOOLS::log("🎉 所有文章已处理完毕。", true, WP_CONTENT_DIR . "/up_5_53.log");
        return false;
    }

    foreach ($results as $row) {
        $post_id = intval($row->post_id);
        $level   = $row->meta_value;

        if (!isset($level_map[$level]))
            continue;

        $term_slug = $level_map[$level];
        IOTOOLS::log('--处理：' . $post_id . '-' . $level . '->' . $term_slug, true, WP_CONTENT_DIR . "/up_5_53.log");
        // 设置 taxonomy term（覆盖旧的）
        wp_set_post_terms($post_id, [$term_slug], 'content_visibility', false);
    }

    IOTOOLS::log("✅ 处理完 offset: $offset - " . ($offset + $batch_size), true, WP_CONTENT_DIR . "/up_5_53.log");
    return true;
}
/**
 * 优化 postmeta 数据
 * @return string
 */
function io_optimize_postmeta_5_0()
{
    global $wpdb;
    if(get_option('io_postmeta_update_data', 0)){
        echo (json_encode(array(
            'error' => 1,
            'msg'   => '已经优化过了！',
        )));
        exit;
    }
    update_option('io_postmeta_update_data', 1, false);
    // 要迁移的meta_key
    $meta_keys_to_migrate = [
        '_goto',
        '_wechat_id',
        '_is_min_app',
        '_sites_link',
        '_spare_sites_link',
        '_sites_sescribe',
        '_sites_language',
        '_sites_country',
        '_thumbnail',
        '_sites_preview',
        '_wechat_qr',
        '_down_version',
        '_down_size',
        '_down_url_list',
        '_dec_password',
        '_app_platform',
        '_down_preview',
        '_down_formal',
        '_screenshot'
    ];

    $meta_keys_str = implode("','", $meta_keys_to_migrate);

    $batch_size    = 100; // 每次处理的文章数量
    $offset        = 0;
    $total_deleted = 0; // 用于统计删除的记录数量
    $total_updated = 0; // 用于统计更新的记录数量
    //$old_all_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE 1");

    do {
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID 
                 FROM {$wpdb->posts} 
                 WHERE post_type IN ('sites','post','app','book') 
                 ORDER BY ID ASC 
                 LIMIT %d OFFSET %d",
                $batch_size,
                $offset
            )
        );
        // 如果没有文章了，退出循环
        if (empty($results)) {
            break;
        }
        $post_ids_str = implode(',', $results);

        $post_meta = $wpdb->get_results(
            "SELECT post_id, meta_key, meta_value 
             FROM {$wpdb->postmeta} 
             WHERE meta_key IN ('$meta_keys_str')
             AND post_id IN ($post_ids_str)"
        );

        $post_meta_data = [];

        // 整理数据，按 post_id 分组并构建多元数组
        foreach ($post_meta as $row) {
            $post_id    = intval($row->post_id);
            $meta_key   = $row->meta_key;
            $meta_value = $row->meta_value;

            if (!isset($post_meta_data[$post_id])) {
                $post_meta_data[$post_id] = [];
            }

            $post_meta_data[$post_id][$meta_key] = maybe_unserialize($meta_value);
        }

        $insert_values = [];
        foreach ($post_meta_data as $post_id => $meta_array) {
            $serialized_meta = maybe_serialize($meta_array);

            $insert_values[] = $wpdb->prepare(
                "(%d, %s, %s)",
                $post_id,
                'posts_config_data',
                $serialized_meta
            );

            $total_updated++;
        }

        // 执行批量插入或更新
        if (!empty($insert_values)) {
            $insert_values_str = implode(', ', $insert_values);

            $wpdb->query(
                "INSERT INTO {$wpdb->postmeta} 
                 (post_id, meta_key, meta_value) 
                 VALUES {$insert_values_str} 
                 ON DUPLICATE KEY 
                 UPDATE meta_value = VALUES(meta_value)"
            );
        }

        // 删除旧的 meta_key
        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} 
             WHERE post_id IN ($post_ids_str) 
             AND meta_key IN ('$meta_keys_str')"
        );

        $total_deleted += $wpdb->rows_affected;

        $offset += $batch_size;

    } while (count($results) === $batch_size); 

    delete_transient( 'onenav_manual_update_version' );

    echo (json_encode(array(
        'error' => 0,
        'msg'   => '优化完成。将 ' . $total_deleted . ' 条数据减少到 ' . $total_updated . '条数据。请刷新页面。',
    )));
    exit;
}

/**
 * 为文章批量添加自定义字段
 * 
 * @param string|array $meta_keys 需添加的字段名
 * @param string|array $meta_vals 需添加的字段值
 * @param string|array $post_type 需要添加的文章类型
 * @return mixed
 */
function io_update_post_purview($meta_keys = ['_user_purview_level'], $meta_vals = ['all'], $post_type = ['sites','post','app','book'])
{
    global $wpdb;
    set_time_limit(0);

    // 确保 $meta_keys 和 $meta_vals 是数组
    if(!is_array($meta_keys)){
        $meta_keys = [$meta_keys];
        $meta_vals = [$meta_vals];
    }
    // 拼接 post_type 的 WHERE 条件
    if (is_array($post_type)) {
        $WHERE = "`post_type` IN ('" . implode("','", array_map('esc_sql', $post_type)) . "')";
    } else {
        $WHERE = $wpdb->prepare("`post_type` = %s", $post_type);
    }

    // 如果键值数组数量不一致，返回错误
    if (count($meta_keys) !== count($meta_vals)) {
        return new WP_Error('mismatched_array', 'meta_keys 和 meta_vals 数组长度不一致');
    }


    $step     = 500; //每次从数据库取多少文章
    $offset   = 0;
    $do_count = 0;    //已更新的文章数量
    do {
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID 
                 FROM {$wpdb->posts} 
                 WHERE {$WHERE}
                 ORDER BY ID 
                 LIMIT %d OFFSET %d",
                $step,
                $offset
            )
        );
        if (empty($results)) {
            break;
        }

        // 准备插入的数据
        $insert_values = [];
        foreach ($results as $id) {
            // 遍历所有的 meta_key 和 meta_val，插入同一篇文章
            foreach ($meta_keys as $index => $meta_key) {
                $meta_val = $meta_vals[$index];
                $insert_values[] = $wpdb->prepare(
                    "(%d, %s, %s)",
                    $id,
                    $meta_key,
                    $meta_val
                );
            }
            $do_count++; // 统计文章更新数量
        }

        // 批量插入或更新自定义字段
        if (!empty($insert_values)) {
            $insert_values_str = implode(', ', $insert_values);
            $wpdb->query(
                "INSERT INTO {$wpdb->postmeta} 
                 (post_id, meta_key, meta_value) 
                 VALUES {$insert_values_str} 
                 ON DUPLICATE KEY 
                 UPDATE meta_value = VALUES(meta_value)"
            );
        }

        $offset += $step;

    } while (count($results) === $step);

    return $do_count;
}

/**
 * 获取更新任务
 * @return string
 */
function io_get_update_task(&$count){
    global $wpdb;

    $count = 0;

    // 旧版本
    $current_v = '5.53';
    $v = get_option('onenav_version', false);
    if($v === false){
        update_option( 'onenav_version', $current_v );
        return '';
    }
    $v_html = '';
    $nonce= wp_create_nonce('io_up_db');
    // 判断版本 $current_v 是否大于 $v
    if (version_compare($current_v, $v, '>')) {
        $url    = add_query_arg(array(
            'action'   => 'io_update_theme',
            'type'     => 'update',
            '_wpnonce' => $nonce
        ), admin_url('admin-ajax.php'));
        $v_html = '<p><a class="button ajax-up-get" href="' . esc_url($url) . '">立即更新</a></p>';
        $count++;
    }

    // 数据库更新
    $db = array();
    if(!column_in_db_table($wpdb->iocustomurl,'post_id')){
        $db[] = 1;
    }
    if(!column_in_db_table($wpdb->iocustomurl,'summary')){
        $db[] = 2;
    }
    if(!column_in_db_table($wpdb->iomessages,'meta')){
        $db[] = 3;
    }
    if(!column_in_db_table($wpdb->iocustomterm,'parent')){
        $db[] = 4;
    }
    $db_html = '';
    if ($db) {
        $url     = add_query_arg(array(
            'action'   => 'io_update_theme',
            'type'     => 'update_db',
            'data'     => implode('-', $db),
            '_wpnonce' => $nonce
        ), admin_url('admin-ajax.php'));
        $db_html = '<h4>检查到数据库缺少字段(如果点击后没效果，请切换一下主题再点)。</h4>';
        $db_html .= '<p><a class="button ajax-up-get" href="' . esc_url($url) . '">立即补缺</a></p>';
        $count++;
    }

    // 优化点赞数据
    $star_html = '';
    if( !get_option('io_star_update_data', false) ){
        $url     = add_query_arg(array(
            'action'   => 'io_update_theme',
            'type'     => 'update_star',
            '_wpnonce' => $nonce
        ), admin_url('admin-ajax.php'));
        $star_html = '<h4>☞☞☞ 点赞数据需要优化。</h4>';
        $star_html .= '<p><a class="button ajax-up-get" href="' . esc_url($url) . '">立即优化</a></p>';
        $count++;
    }
    // 优化文章 META 数据
    $postmeta_html = '';
    //if( !get_option('io_postmeta_update_data', false) ){
    //    $url     = add_query_arg(array(
    //        'action'   => 'io_update_theme',
    //        'type'     => 'update_postmeta',
    //        '_wpnonce' => $nonce
    //    ), admin_url('admin-ajax.php'));
    //    $postmeta_html = '<h4>☞☞☞ 文章 META 数据需要优化。</h4>';
    //    $postmeta_html .= '<p><a class="button ajax-up-get" href="' . esc_url($url) . '">立即优化</a></p>';
    //    $count++;
    //}

    // 按顺序执行，
    $task = $db_html;
    if($postmeta_html){
        $task = $postmeta_html;
    }
    if($star_html){
        $task = $star_html;
    }
    if($v_html){
        $task = $v_html;
    }

    return $task;
}

function io_update_theme_after_update_db() {
    $html = '';

    $do_action = io_get_update_task($count);
    if ($do_action) {
        $js = '<script type="text/javascript">
        (function ($) {
        $(".ajax-up-get").click( function () {
            if (!confirm("你确定已经备份数据库了吗？请确保已保存所有数据。")) {
                return false; // 如果用户点击取消，则不继续执行
            }

            var _this = $(this);
            if(_this.attr("disabled")){
                return !1;
            }
            var _notice = _this.parents(".notice-error").find(".ajax-notice");
            var _tt = _this.html();
            var ajax_url = _this.attr("href");
            var spin = "<i class=\'fa fa-spinner fa-spin fa-fw\'></i> "
            var n_type = "warning";
            var n_msg = spin + "正在处理，请稍候...";
            _this.attr("disabled", true);
            _this.html(spin + "请稍候...");
            $.ajax({
                type: "GET",
                url: ajax_url,
                dataType: "json",
                error: function (n) {
                    var n_con = "<div style=\'padding: 10px;margin: 0;\' class=\'notice notice-error\'><b>" + "网络异常或者操作失败，请稍候再试！ " + n.status + "|" + n.statusText + "</b></div>";
                    _notice.html(n_con);
                    _this.attr("disabled", false);
                    _this.html( _tt );
                },
                success: function (n) {
                    if (n.msg) {
                        n_type = n.error_type || (n.error ? "error" : "info");
                        var n_con = "<div style=\'padding: 10px;margin: 0;\' class=\'notice notice-" + n_type + "\'><b>" + n.msg + "</b></div>";
                        _notice.html(n_con);
                    }
                    _this.attr("disabled", false);
                    _this.html( _tt );
                    if (n.reload) {
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    }
                }
            });
            return !1;
        });
    })(jQuery);
    </script>';
        $html .= '<div class="notice notice-error is-dismissible">';
        $html .= '<h3>新 OneNav 版本需更新数据！共 ' . $count . ' 项更新。</h3>';
        $html .= '<p style="color:#F52"><b>注意：</b>更新前请<b>备份数据库</b>！请<b>备份数据库</b>！请<b>备份数据库</b>！因未备份导致数据丢失与主题无关！</p>';
        $html .= '<p style="color:#F23"><b>警告：</b>升级后不支持降级！</p>';
        $html .= $do_action;
        $html .= '<div class="ajax-notice" style="margin-bottom:10px"></div>';
        $html .= '</div>';

        $html .= $js;
    }
    echo $html;
}
add_action('admin_notices', 'io_update_theme_after_update_db');

/**
 * 判断表中是否有字段
 * @param mixed $table 完整表名
 * @param mixed $column 字段名称
 * @return bool true 为字段已经存在
 */
function column_in_db_table($table, $column){
    global $wpdb;
    $column_exists = $wpdb->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    if ($column_exists) {
        return true;
    } else {
        return false;
    }
}
/**
 * 判断是否有表
 * @param mixed $table 完整表名
 * @return bool true 为表名已经存在
 */
function io_is_table($table){
    global $wpdb;
    if($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
        return true;
    } else {
        return false;
    }
}

function io_update_theme_ajax(){
    $type = $_GET['type'];
    if( !is_super_admin() ){
        echo (json_encode(array('error' => 1, 'msg' => '权限不足！')));
        exit();
    }
    if (!wp_verify_nonce($_GET['_wpnonce'],"io_up_db")){
        echo (json_encode(array('error' => 1, 'msg' => '安全检查失败，请刷新或稍后再试！')));
        exit();
    }
    if(get_transient( 'onenav_manual_update_version' )){
        echo (json_encode(array('error' => 1, 'msg' => '正在后台操作或者已经完成，请3分钟后刷新窗口，如果窗口消失，说明操作成功！')));
        exit();
    }
    set_transient('onenav_manual_update_version', 1, 3 * MINUTE_IN_SECONDS);

    switch ($type) {
        case 'update':
            io_update_theme_v_ajax();
            break;
        case 'update_star':
            io_optimize_star_data_5_0();
            break;
        case 'update_postmeta':
            io_optimize_postmeta_5_0();
            break;
        case 'update_db':
            io_update_theme_db_ajax();
            break;

        default:
            echo (json_encode(array('error' => 1, 'msg' => '参数错误！')));
            break;
    }
    exit();
}
add_action('wp_ajax_io_update_theme', 'io_update_theme_ajax');

function io_update_theme_v_ajax(){
    updateDB();
    echo (json_encode(array('error' => 0, 'msg' => '更新成功！', 'reload' => 1)));
    exit();
}


function io_update_theme_db_ajax(){
    $db = $_GET['type'];
    $type = explode('-', $db);
    global $wpdb;
    foreach ($type as $v) {
        switch ($v) {
            case '1':
                if(!column_in_db_table($wpdb->iocustomurl,'post_id')){
                    $wpdb->query("ALTER TABLE $wpdb->iocustomurl ADD `post_id` bigint(20)");
                }
                break;
            case '2':
                if(!column_in_db_table($wpdb->iocustomurl,'summary')){
                    $wpdb->query("ALTER TABLE $wpdb->iocustomurl ADD `summary` varchar(255) DEFAULT NULL");
                }
                break;
            case '3':
                if(!column_in_db_table($wpdb->iomessages,'meta')){
                    $wpdb->query("ALTER TABLE $wpdb->iomessages ADD `meta` text DEFAULT NULL");
                }
                break;
            case '4':
                if(!column_in_db_table($wpdb->iocustomterm,'parent')){
                    $wpdb->query("ALTER TABLE $wpdb->iocustomterm ADD `parent` bigint(20) NOT NULL DEFAULT 0 AFTER `user_id`");
                }
                break;
        }
    }

    delete_transient( 'onenav_manual_update_version' );
    
    echo (json_encode(array('error' => 0, 'msg' => '插入成功！','reload' => 1)));
    exit();
}












/**
 * 主题设置更新文章权限字段
 * @return never
 */
function io_update_post_purview_ajax(){
    if( !is_super_admin() ){
        echo (json_encode(array('error' => 1, 'msg' => '权限不足！')));
        exit();
    }
    if(get_option( 'onenav_manual_post_purview' , 0 )){
        echo (json_encode(array('error' => 1, 'msg' => '已经执行了，不要重复点击！')));
        exit();
    }
    update_option('onenav_manual_post_purview', 1);

    io_update_post_purview('_user_purview_level', 'all', 'post');

    echo (json_encode(array('error' => 0, 'msg' => '更新成功！', 'reload' => 1)));
    exit();
}
add_action('wp_ajax_io_update_post_purview', 'io_update_post_purview_ajax');
