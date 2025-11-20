<?php

function get_http_status_codes(){
    $codes   = array(
		// [Informational 1xx]
		100 => 'Continue',
		101 => 'Switching Protocols',
		// [Successful 2xx]
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		// [Redirection 3xx]
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Moved Temporarily',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		//306=>'(Unused)',
		307 => 'Temporary Redirect',
		// [Client Error 4xx]
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		// [Server Error 5xx]
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
	);
    return $codes;
}
if(!function_exists('idn_to_ascii')){
require_once get_theme_file_path('/inc/classes/idna_convert.class.php'); 
function idn_to_ascii( $url, $charset = '' ) {
    $idn = new idna_convert();
    if ( null != $idn ) {
        if ( empty( $charset ) ) {
            $charset = get_bloginfo( 'charset' );
        }

        // 仅对 host 进行编码。
        if ( preg_match( '@(\w+:/*)?([^/:]+)(.*$)?@s', $url, $matches ) ) {
            $host = $matches[2];
            if ( ( strtoupper( $charset ) != 'UTF-8' ) && ( strtoupper( $charset ) != 'UTF8' ) ) {
                $host = io_encode_utf8( $host, $charset, true );
            }
            $host = $idn->encode( $host );
            $url  = $matches[1] . $host . $matches[3];
        }
    }

    return $url;
}
}
add_action( 'wp_ajax_io_current_load',  'io_ajax_current_load' );
function io_ajax_current_load() {
    $load = IOTOOLS::get_server_load();
    if ( empty( $load ) ) {
        die( "未知错误" );
    }

    $one_minute = reset( $load );
    printf( '%.2f', $one_minute );
    die();
}

add_action( 'wp_ajax_io_recheck_link',  'io_ajax_recheck' );
function io_ajax_recheck() {
    if ( ! current_user_can( 'edit_others_posts' ) ) {
        die( json_encode(array(
            'status' => 0,
            'error' => '你不允许这样做！',
            )) 
        );
    }

    if ( ! isset( $_POST['post_id'] ) || ! is_numeric( $_POST['post_id'] ) ) {
        die( json_encode(array(
            'status' => 0,
            'error' => '未指定 post_id',
            )) 
        );
    }

    $id   = intval( $_POST['post_id'] );
    $link = new IOLINK( $id );

    if ( ! $link->valid() ) {
        die(
            json_encode(
                array(
                    'status' => 0,
                    'error' => sprintf( "哎呀，找不到 id:%d 对应的链接！", $id ),
                )
            )
        );
    }

    //如果立即检查失败，这将确保在下一个work()运行期间检查该链接。
    $link->last_check_attempt  = 0;
    //$link->isOptionLinkChanged = true;

    $is_save = true;
    if (get_post_meta($id, '_affirm_dead_url', true) || get_post_meta($id, '_revive_url_m', true)) {
        $is_save = false;
    }
    if($is_save) $link->save();
    //检查链接并保存结果。
    $link->check($is_save);

    $status   = $link->analyse_status();
    $response = array(
        'status'         => 1,
        'status_text'    => $status['text'],
        'status_code'    => $status['code'],
        'http_code'      => empty( $link->http_code ) ? '' : $link->http_code,
        'redirect_count' => $link->redirect_count,
        'final_url'      => $link->final_url,
        'url'            => $link->url,
        'log'            => $link->log,
    );

    die( json_encode( $response ) );
}

if ( ! function_exists( 'microtime_float' ) ) {
	function microtime_float() {
		list( $usec, $sec ) = explode( ' ', microtime() );
		return ( (float) $usec + (float) $sec );
	}
}
/**
 * 做各种事情的主要功能。
 *
 * @return void
 */
function link_check_work() { 
    // 关闭会话以防止锁死。
    // PHP会话是阻塞的。 session_start()将等待所有使用同一会话的其他脚本完成。
    // 因此，一个长期运行的脚本如果无意中保持会话开放，会导致整个网站对当前用户/浏览器 "锁定"。
    // WordPress本身不使用会话，但有些插件使用，所以应该在启动之前明确地关闭会话（如果有的话）。
    if ( session_id() != '' ) {
        session_write_close();
    }

    if ( ! acquire_lock('io_nav_cron_links') ) {
        IOTOOLS::log("另一个检查实例已经在工作了。停止检查！", SAVE_CHECK_LOG);
        return;
    }

    if ( io_server_too_busy() ) {
        // LOG: 服务器负载过高，停止。
        IOTOOLS::log("服务器负载过高，停止检查！", SAVE_CHECK_LOG);
        return;
    }

    $execution_start_time = microtime_float();
    

    $options = io_get_option( "link_check_options", array('max_execution_time'=>420) );
    $max_execution_time = $options['max_execution_time'];
    IOTOOLS::log("====== 开始执行定时检查任务({$max_execution_time}秒) ======", SAVE_CHECK_LOG);

    /*****************************************
                        准备
    ******************************************/
    // 检查是否有安全模式
    if ( IOTOOLS::is_safe_mode() ) {
        // 以安全模式的方式进行--遵守现有的最大执行时间(max_execution_time)设置
        $t = ini_get( 'max_execution_time' );
        if ( $t && ( $t < $max_execution_time ) ) {
            $max_execution_time = $t - 1;
        }
    } else {
        // 按常规方法进行
        @set_time_limit( $max_execution_time * 2 ); //x2应该足够了，再跑下去就意味着出了问题。
    }

    //当连接被关闭时，不要停止脚本
    ignore_user_abort( true );

    //按以下规定关闭连接 http://www.php.net/manual/en/features.connection-handling.php#71172
    //这可以减少资源的使用。
    //(调试时禁用，否则无法获得FireHP输出)
    if ( ! headers_sent() && ( defined( 'DOING_AJAX' ) && constant( 'DOING_AJAX' ) ) && ( ! defined( 'WP_DEBUG' ) || ! constant( 'WP_DEBUG' ) ) ) {
        @ob_end_clean(); //丢弃现有的缓冲区，如果有的话。
        header( 'Connection: close' );
        ob_start();
        echo ( 'Connection closed' ); //这可以是任何东西
        $size = ob_get_length();
        header( "Content-Length: $size" );
        ob_end_flush(); // 奇怪的行为，将无法工作
        flush();        // 除非两者都被调用！
    }

    //目标使用率必须在 1% 到 100% 之间。
    $target_usage_fraction = 0.25;


    /*****************************************
                    解析新文章并检查
    ******************************************/
    IOTOOLS::log("开始解析新文章", SAVE_CHECK_LOG);
    $max_posts_per_query    = 50;

    $start                  = microtime( true );
    $posts                  = io_get_links_to_list( $max_posts_per_query );
    $get_posts_time         = microtime( true ) - $start;
    $is_analyse             = false;
    $options                = io_get_option( "link_check_options", array('check_threshold'=>72) );//默认每 72小时
    $check_threshold        = date( 'Y-m-d H:i:s', strtotime( '-' . $options['check_threshold'] . ' hours' ) );//多久检查一次

    while ( ! empty( $posts ) ) {
        io_sleep_to_maintain_ratio( $get_posts_time, $target_usage_fraction );

        IOTOOLS::log("解析到".count( $posts ) ."个新链接", SAVE_CHECK_LOG);
        $is_analyse = true;

        foreach ( $posts as $post ) {
            $synch_start_time = microtime( true );

            if( strtotime($post->last_check_attempt)<strtotime($check_threshold) ){
                // LOG: 这个链接需要被检查吗？被排除的链接不被检查，但它们的URL仍会被定期测试，看它们是否仍在排除名单上。
                if ( $post->valid() && !io_is_excluded( $post->url ) ) {
                    // LOG: 检查链接。
                    $post->check( true );
                } else {
                    $post->last_check_attempt = current_time( 'timestamp' );
                    $post->save();
                }
            }else{
                IOTOOLS::log("此新链接 ({$post->url}) 可能已经在列队中！", SAVE_CHECK_LOG);
            }
            // 标记为加入检查列队
            io_post_to_check_list( $post->post_id );
            
            $synch_elapsed_time = microtime( true ) - $synch_start_time;

            if ( (microtime_float() - $execution_start_time) > $max_execution_time ) {
                IOTOOLS::log("分配的执行时间已用完，停止解析新文章并检查的任务！", SAVE_CHECK_LOG);
                release_lock('io_nav_cron_links');
                return;
            }
            
            if ( io_server_too_busy() ) {
                IOTOOLS::log("服务器负载过高，停止解析新文章并检查的任务！", SAVE_CHECK_LOG);
                release_lock('io_nav_cron_links');
                return;
            }
            // 放慢解析速度，以减少服务器的负荷。基本上，我们在$target_usage_fraction时间内工作，其余时间则睡觉。
            io_sleep_to_maintain_ratio( $synch_elapsed_time, $target_usage_fraction );
        }
        $start          = microtime( true );
        $posts          = io_get_links_to_list( $max_posts_per_query );
        $get_posts_time = microtime( true ) - $start;
    }
    if($is_analyse){
        IOTOOLS::log("解析完成", SAVE_CHECK_LOG);
    }else{
        IOTOOLS::log("没有解析到新内容", SAVE_CHECK_LOG);
    }
    
    /*****************************************
                        检查链接
    ******************************************/
    IOTOOLS::log("准备检查链接", SAVE_CHECK_LOG);
    $max_links_per_query    = 30;

    $start                  = microtime( true );
    $links                  = io_get_links_to_check( $max_links_per_query );
    $get_links_time         = microtime( true ) - $start;
    $is_check               = false;

    while ( $links ) {
        io_sleep_to_maintain_ratio( $get_links_time, $target_usage_fraction );
        
        IOTOOLS::log("检查".count( $links ) ."个链接", SAVE_CHECK_LOG);
        $is_check = true;

        //将数组随机化可以减少在一排中得到几个指向同一域的链接的机会。
        shuffle( $links );

        foreach ( $links as $link ) {
            //这个链接需要被检查吗？被排除的链接不被检查，但它们的URL仍会被定期测试，看它们是否仍在排除名单上。
            if ( $link->valid() && !io_is_excluded( $link->url ) ) {
                //检查链接。
                $link->check( true );
            } else {
                $link->last_check_attempt = current_time( 'timestamp' );
                $link->save();
            }

            if ( (microtime_float() - $execution_start_time) > $max_execution_time ) {
                IOTOOLS::log("分配的执行时间已用完，停止检查任务！", SAVE_CHECK_LOG);
                release_lock('io_nav_cron_links');
                return;
            }

            if ( io_server_too_busy() ) {
                IOTOOLS::log("服务器负载过高，停止检查任务！", SAVE_CHECK_LOG);
                release_lock('io_nav_cron_links');
                return;
            }
        }

        $start          = microtime( true );
        $links          = io_get_links_to_check( $max_links_per_query );
        $get_links_time = microtime( true ) - $start;
    } 
    if($is_check){
        IOTOOLS::log("检查链接完成", SAVE_CHECK_LOG);
    }else{
        IOTOOLS::log("没有需要检查的内容", SAVE_CHECK_LOG);
    }

    release_lock('io_nav_cron_links');

    IOTOOLS::log("====== 完成此次检查任务！ ======", SAVE_CHECK_LOG);
}
/**
 * 检查服务器当前是否过载
 */
function io_server_too_busy() {
    $options = io_get_option( "link_check_options", array('server_load_limit'=>4) );
    if ( ( isset( $options['server_load_limit'] ) && 0===$options['server_load_limit'] ) || ! isset( $options['server_load_limit'] ) ) {
        return false;
    }

    $loads = IOTOOLS::get_server_load();
    if ( empty( $loads ) ) {
        return false;
    }
    $one_minute = floatval( reset( $loads ) );

    return $one_minute > $options['server_load_limit'];
}
/**
 * 检查URL是否在排除列表。
 *
 * @param string $url 
 * @return bool
 */
function io_is_excluded( $url ) {
    $options = io_get_option( "link_check_options", array('exclusion_list'=>'') );
    $_list = preg_split("/,|，|\s|\n/", $options['exclusion_list']);
    if ( ! is_array( $_list ) ) {
        return false;
    }
    foreach ( $_list as $excluded_word ) {
        if ( stristr( $url, $excluded_word ) ) {
            return true;
        }
    }
    return false;
}
/**
 * 检索需要检查或重新检查的链接。
 *
 * @param integer $max_results 要返回的最大链接数。默认值为0=无限制。
 * @param bool $count_only 如果为true，则只返回找到的链接数，而不返回链接本身。
 * @return int|IOLINK[]|string
 */
function io_get_links_to_check( $max_results = 0, $count_only = false ) {
    global $wpdb; 

    $options = io_get_option( "link_check_options", array('check_threshold'=>72) );
    //每 72小时
    $check_threshold   = date( 'Y-m-d H:i:s', strtotime( '-' . $options['check_threshold'] . ' hours' ) );//多久检查一次
    $recheck_threshold = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 1800 );//多少时间后重新检查  30 * 60  30分钟

    //Note : 这是一个缓慢的查询，但恐怕没有办法加快速度。
    if ( $count_only ) {
        $q = "SELECT COUNT(DISTINCT $wpdb->posts.ID)\n";
    } else {
        $q = "SELECT DISTINCT $wpdb->posts.ID\n";
    }
    //$q .= "FROM $wpdb->posts
    //    INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
    //    INNER JOIN $wpdb->postmeta AS mt1 ON ( $wpdb->posts.ID = mt1.post_id )  
    //    INNER JOIN $wpdb->postmeta AS mt2 ON ( $wpdb->posts.ID = mt2.post_id )  
    //    INNER JOIN $wpdb->postmeta AS mt3 ON ( $wpdb->posts.ID = mt3.post_id )  
    //    WHERE 1=1
    //    AND (
    //        ( $wpdb->postmeta.meta_key = '_last_check' AND $wpdb->postmeta.meta_value < %s )
    //        OR ( 
    //            (  
    //                ($wpdb->postmeta.meta_key = '_dead_link' AND $wpdb->postmeta.meta_value = '1')
    //                OR ($wpdb->postmeta.meta_key = '_being_checked' AND $wpdb->postmeta.meta_value = '1')
    //            )
    //            AND (mt1.meta_key = '_may_recheck' AND mt1.meta_value = '1' )
    //            AND (mt2.meta_key = 'invalid' AND mt2.meta_value < '3' )
    //            AND (mt3.meta_key = '_last_check' AND mt3.meta_value < %s )
    //        ) 
    //    )
    //    AND $wpdb->posts.post_type = 'sites'
    //    AND (($wpdb->posts.post_status = 'publish'))";
        
    $q .= "FROM $wpdb->posts
        INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
        WHERE 1=1
        AND (
            ( $wpdb->postmeta.meta_key = '_last_check' AND $wpdb->postmeta.meta_value < %s )
        )
        AND $wpdb->posts.post_type = 'sites'
        AND (($wpdb->posts.post_status = 'publish'))";
    if ( ! $count_only ) {
        if ( ! empty( $max_results ) ) {
            $q .= "\nLIMIT " . intval( $max_results );
        }
    }

    $link_q = $wpdb->prepare( $q, $check_threshold );//$recheck_threshold
    //LOG: 查找链接以进行检查 . $link_q;

    //如果只需要链接的数量，则检索它并返回
    if ( $count_only ) {
        return $wpdb->get_var( $link_q );
    }

    //获取链接数据
    $link_data = $wpdb->get_results( $link_q, ARRAY_A ); 
    if ( empty( $link_data ) ) {
        return array();
    }

    //为所有获取的链接实例化 IOLINK 对象
    $links = array();
    foreach ( $link_data as $data ) {
        $link = new IOLINK( $data['ID']);
        if($link->valid()){
            $links[] = $link;
        }
    }

    return $links;
}
/**
 * 解析链接加入检查对象。
 *
 * @param integer $max_results 要返回的最大链接数。默认值为0=无限制。
 * @param bool $count_only 如果为true，则只返回找到的链接数，而不返回链接本身。
 * @return int|IOLINK[]|string
 */
function io_get_links_to_list( $max_results = 0, $count_only = false ) {
    global $wpdb; 

    $options = get_option( "link_check_options", array('check_threshold'=>72) );

    if ( $count_only ) {
        $q = "SELECT COUNT( * )\n";
    } else {
        $q = "SELECT ID\n";
    }

    $q .= "FROM $wpdb->posts 
        WHERE 1=1
        AND `post_mime_type` = ''
        AND `post_type` = 'sites'
        AND `post_status` = 'publish'";
    if ( ! $count_only ) {
        if ( ! empty( $max_results ) ) {
            $q .= "\nLIMIT " . intval( $max_results );
        }
    }

    $link_q = $q;

    //如果只需要链接的数量，则检索它并返回
    if ( $count_only ) {
        return $wpdb->get_var( $link_q );
    }

    //获取链接数据
    $link_data = $wpdb->get_results( $link_q, ARRAY_A ); 
    if ( empty( $link_data ) ) {
        return array();
    }

    //为所有获取的链接实例化 IOLINK 对象
    $links = array();
    foreach ( $link_data as $data ) {
        $link = new IOLINK( $data['ID']);
        if($link->valid()){
            $links[] = $link;
        }
    }

    return $links;
}

/**
 * 标记网址已经加入检查列队
 * #TODO:占用 post 表中的 post_mime_type 字段，理论不影响wp功能
 * @return int|false 更新了多少行
 */
function io_post_to_check_list( $post_id) {
	global $wpdb;

	$post = get_post( $post_id );

	if ( ! $post ) {
		return false;
	}

	$return = $wpdb->update( $wpdb->posts, array( 'post_mime_type' => 1 ), array( 'ID' => $post->ID ) );
    //clean_post_cache( $post->ID );
	return $return;
}
/**
 * 睡眠时间足够长，以维持 $elapsed_time 和总运行时间之间所需的 $ratio。
 *
 * 例如，如果 $ratio 为 0.25 且 $elapsed_time 为 1 秒，则此方法将休眠 3 秒。
 * Total runtime(总运行时间) = 1 + 3 = 4, ratio(比例) = 1 / 4 = 0.25.
 *
 * @param float $elapsed_time 消耗的时间
 * @param float $ratio 比例
 */
function io_sleep_to_maintain_ratio( $elapsed_time, $ratio ) {
    if ( ( $ratio <= 0 ) || ( $ratio > 1 ) ) {
        return;
    }
    $sleep_time = $elapsed_time * ( ( 1 / $ratio ) - 1 );
    if ( $sleep_time > 0.0001 ) {
        usleep( $sleep_time * 1000000 );
    }
}
/**
 * 获取独占命名锁。
 */
function acquire_lock( $name, $timeout = 0 ) {
    global $wpdb; 
    $state = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $name, $timeout ) );
    return 1 == $state;
}

/**
 * 释放一个命名锁。
 */
function release_lock( $name ) {
    global $wpdb; 
    $released = $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $name ) );
    return 1 == $released;
}

function io_cron_check_links_events() {
    link_check_work();
}

/**
 * 获取网址状态
 *
 * @param int $post_id 文章 ID
 * @param int $show_level 显示等级
 * @param bool $display 直接输出 html
 * 
 * @return array|bool|null
 */
function get_sites_url_state($post_id, $show_level = 3, $display = false){
    $link = new IOLINK( $post_id );
    /*
		'broken'    死链
		'warning'   注意&超时
		'final_url' 重定向
    */
    $data = false;
    if( $link->broken && $show_level >=1 ){
        $data = array(
            'level' => 1,
            'code'  => 'error',
            'text'  => __('失效链接','i_theme'),
        );
    }elseif( $link->warning && $show_level >=2 ){
        $data = array(
            'level' => 2,
            'code'  => 'warning',
            'text'  => __('连接超时','i_theme'),
        );
    }elseif( '' !== $link->final_url && $show_level >=3 ){
        $data = array(
            'level' => 3,
            'code'  => 'info',
            'text'  => __('重定向&变更','i_theme'),
        );
    }
    if(is_array($data) && $display){
        echo $data['text'];
    }else{
        return $data;
    }
}
