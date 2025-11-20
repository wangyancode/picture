<?php
/*
 * @Theme Name:One Nav
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-01-25 03:01:51
 * @LastEditors: iowen
 * @LastEditTime: 2024-10-22 14:06:20
 * @FilePath: /onenav/inc/cron.php
 * @Description: 定时任务
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 执行的定时任务
 * daily 每天   hourly 每小时    twicedaily 每日两次    10min 10分钟 weekly
 */
function io_setup_cron_events_schedule()
{
    if (io_get_option('leader_board', false)) {
        if (!wp_next_scheduled('io_clean_expired_ranking_data')) {
            wp_schedule_event(time() + 260, 'daily', 'io_clean_expired_ranking_data');
        }
    } else {
        wp_clear_scheduled_hook('io_clean_expired_ranking_data');
    }
    //if(!wp_next_scheduled('io_daily_theme_state_event')){
    //    wp_schedule_event(time() + 3600, 'weekly', 'io_daily_theme_state_event');
    //}
    if (io_get_option('server_link_check', false)) {
        if (!wp_next_scheduled('io_cron_check_links')) {
            wp_schedule_event(time() + 600, 'hourly', 'io_cron_check_links');
        }
    } else {
        wp_clear_scheduled_hook('io_cron_check_links');
    }
    // 在插件或主题激活时，创建定时任务
    if (!wp_next_scheduled('io_scheduled_cleanup_update_chunks_files')) {
        wp_schedule_event(time(), 'daily', 'io_scheduled_cleanup_update_chunks_files');
    }
}
add_action('init', 'io_setup_cron_events_schedule');

/**
 * 自动删除排行榜历史事件.
 * 
 * @return bool
 */
function io_auto_delete_close_order(){
    global $wpdb;
    $day = io_get_option('how_long',30);
    $sql = "DELETE FROM {$wpdb->ioviews} where DATEDIFF(curdate(), `time`)>{$day}";
    // `time` < date_sub(curdate(), INTERVAL 30 DAY 
    //mysqli_query($link, $sql) or die('删除数据出错：' . mysqli_error($link));
    $delete = $wpdb->query($sql);
    return (bool) $delete;
}
add_action('io_clean_expired_ranking_data', 'io_auto_delete_close_order');

/**
 * 修改事件下次执行时间
 * @param mixed $event_name
 * @param mixed $new_time
 * @param mixed $recurrence
 * @return bool
 */
function io_reschedule_cron_event($event_name, $new_time, $recurrence) {
    $timestamp = wp_next_scheduled($event_name);

    // 如果事件已计划，取消计划现有事件
    if ($timestamp) {
        wp_unschedule_event($timestamp, $event_name);

        // 重新调度新的事件
        wp_schedule_event($new_time, $recurrence, $event_name);
        
        return true;
    } else {
        return false;
    }
}

/**
 * 定期清理临时文件
 */
function io_clean_old_temp_files()
{
    $upload_dir      = wp_upload_dir();
    $temp_dirs       = glob($upload_dir['path'] . '/chunks_*', GLOB_ONLYDIR);
    $expiration_time = 24 * 60 * 60; // 24小时

    foreach ($temp_dirs as $dir) {
        if (time() - filemtime($dir) > $expiration_time) {
            // 递归删除目录内文件
            array_map('unlink', glob("$dir/*"));
            rmdir($dir);
        }
    }
}
add_action('io_scheduled_cleanup_update_chunks_files', 'io_clean_old_temp_files');
