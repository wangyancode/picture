<?php
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

/*
 * @brief url封装类，将常用的url请求操作封装在一起
 * */
class URL{ 

    public function __construct(){ 
    }

    /**
     * combineURL
     * 拼接url
     * @param string $baseURL   基于的url
     * @param array  $keysArr   参数列表数组
     * @return string           返回拼接的url
     */
    public function combineURL($baseURL,$keysArr){
        $combined = $baseURL."?";
        $valueArr = array();

        foreach($keysArr as $key => $val){
            $valueArr[] = "$key=$val";
        }

        $keyStr = implode("&",$valueArr);
        $combined .= ($keyStr);
        
        return $combined;
    }

    /**
     * get_contents
     * 服务器通过get请求获得内容
     * @param string $url       请求的url,拼接后的
     * @return string           请求返回的内容
     */
    public function get_contents($url){
        if (ini_get("allow_url_fopen") == "1") {
            $response = wp_remote_retrieve_body(wp_remote_get($url));
        }else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//iiiiiii
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT,5);
            $response =  curl_exec($ch);
            curl_close($ch);
        }

        //-------请求为空
        if(empty($response)){ 
			wp_die('可能PHP未开启curl、allow_url_fopen等支持,请尝试开启支持，重启web服务器，如果问题仍未解决，请联系我们', '请求失败', array('response'=>403));
        }

        return $response;
    }

    /**
     * get
     * get方式请求资源
     * @param string $url     基于的baseUrl
     * @param array $keysArr  参数列表数组      
     * @return string         返回的资源内容
     */
    public function get($url, $keysArr){
        $combined = $this->combineURL($url, $keysArr);
        return $this->get_contents($combined);
    }

    /**
     * post
     * post方式请求资源
     * @param string $url       基于的baseUrl
     * @param array $keysArr    请求的参数列表
     * @param int $flag         标志位
     * @return string           返回的资源内容
     */
    public function post($url, $keysArr, $flag = 0){

        $ch = curl_init();
        if(! $flag) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($ch, CURLOPT_POST, TRUE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $keysArr); 
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }
}

class ioHttpChecker  {
	var $implementation = null;

	function __construct() {
		if ( function_exists( 'curl_init' ) || is_callable( 'curl_init' ) ) {
			$this->implementation = new ioCurlHttp();
		} else {
			//尝试使用wp请求方法
			$this->implementation = new ioWPHttp();
		}
	}

	function can_check( $url, $parsed ) {
		if ( isset( $this->implementation ) ) {
			return $this->implementation->can_check( $url, $parsed );
		} else {
			return false;
		}
	}

	function check( $url, $use_get = false ) {
		return $this->implementation->check( $url, $use_get );
	}
}

class ioHttpCheckerBase{

	function clean_url( $url ) {
		$url = html_entity_decode( $url );

		$ltrm = preg_quote( json_decode( '"\u200E"' ), '/' );
		$url  = preg_replace(
			array(
				'/([\?&]PHPSESSID=\w+)$/i', //remove session ID
				'/(#[^\/]*)$/',             //and anchors/fragments
				'/&amp;/',                  //convert improper HTML entities
				'/([\?&]sid=\w+)$/i',       //remove another flavour of session ID
				'/' . $ltrm . '/',          //remove Left-to-Right marks that can show up when copying from Word.
			),
			array( '', '', '&', '', '' ),
			$url
		);
		$url  = trim( $url );

		return $url;
	}

	function is_error_code( $http_code ) {
		$good_code = ( ( $http_code >= 200 ) && ( $http_code < 400 ) ) || ( 401 === $http_code );
		return ! $good_code;
	}

	/**
	 * 仅接受 http(s) 链接。
	 *
	 * @param string $url
	 * @param array|bool $parsed
	 * @return bool
	 */
	function can_check( $url, $parsed ) {
		if ( ! isset( $parsed['scheme'] ) ) {
			return false;
		}

		return in_array( strtolower( $parsed['scheme'] ), array( 'http', 'https' ) );
	}

	/**
	 * 获取一个URL，并将空格和其他一些非字母数字字符替换为其URL编码的等价物。
	 *
	 * @param string $url
	 * @return string
	 */
	function urlencodefix( $url ) {
		return preg_replace_callback(
			'|[^a-z0-9\+\-\/\\#:.,;=?!&%@()$\|*~_]|i',
			function( $str ) {
				return rawurlencode( $str[0] );
			},
			$url
		);
	}

}

class ioCurlHttp extends ioHttpCheckerBase {

	var $last_headers = ''; 

	function check( $url, $use_get = false ) {
		IOTOOLS::log( '正在['.($use_get?'GET':'HEAD').']检查链接：' . $url, SAVE_CHECK_LOG );
		$this->last_headers = '';

		$url = $this->clean_url( $url ); 
		IOTOOLS::log( '洁净后的链接：' . $url, SAVE_CHECK_LOG );

		$result = array(
			'broken'  => false,
			'timeout' => false,
			'warning' => false,
		);
		$log    = '';

		$conf = io_get_option( "link_check_options", array('timeout'=>30) );

		//初始化 curl。
		$ch              = curl_init();
		$request_headers = array();
		curl_setopt( $ch, CURLOPT_URL, $this->urlencodefix( $url ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36';
		curl_setopt( $ch, CURLOPT_USERAGENT, $ua );

		//请求后关闭连接
		curl_setopt( $ch, CURLOPT_FORBID_REUSE, true );
		$request_headers[] = 'Connection: close';

		//添加referer头以避免被一些机器人拦截
		curl_setopt( $ch, CURLOPT_REFERER, home_url() );

		//当安全模式或 open_basedir 启用时，重定向不起作用。
		if ( ! IOTOOLS::is_safe_mode() && ! IOTOOLS::is_open_basedir() ) {
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		} else {
			$log .= "[警告] 由于启用了 safemode 或 open base dir，无法跟踪重定向URL。\n";
		}

		//设置最大重定向
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );

		//设置超时
		curl_setopt( $ch, CURLOPT_TIMEOUT, $conf['timeout'] );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $conf['timeout'] );

		//设置代理配置。可以在 wp_config.php 中定义这些信息。
		if ( defined( 'WP_PROXY_HOST' ) ) {
			curl_setopt( $ch, CURLOPT_PROXY, WP_PROXY_HOST );
		}
		if ( defined( 'WP_PROXY_PORT' ) ) {
			curl_setopt( $ch, CURLOPT_PROXYPORT, WP_PROXY_PORT );
		}
		if ( defined( 'WP_PROXY_USERNAME' ) ) {
			$auth = WP_PROXY_USERNAME;
			if ( defined( 'WP_PROXY_PASSWORD' ) ) {
				$auth .= ':' . WP_PROXY_PASSWORD;
			}
			curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $auth );
		}

		//让CURL在得到404或其他错误时也能返回一个有效的结果。
		curl_setopt( $ch, CURLOPT_FAILONERROR, false );

		$nobody = ! $use_get; //是发送 HEAD 请求（默认）还是 GET 请求

		$parts = @parse_url( $url );
		if ( 'https' === $parts['scheme'] ) {
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		}

		if ( $nobody ) {
			//如果可能的话，使用 HEAD 请求以提高速度。
			curl_setopt( $ch, CURLOPT_NOBODY, true );
		} else {
			//如果必须使用 GET，限制下载的数据量。
			$request_headers[] = 'Range: bytes=0-2048'; //2 KB
		}

		//设置请求标头。
		if ( ! empty( $request_headers ) ) {
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $request_headers );
		}

		//注册一个回调函数，它将处理HTTP标头。如果重定向，它可以被多次调用。
		curl_setopt( $ch, CURLOPT_HEADERFUNCTION, array( $this, 'read_header' ) );

		//记录请求标头。
		if ( defined( 'CURLINFO_HEADER_OUT' ) ) {
			curl_setopt( $ch, CURLINFO_HEADER_OUT, true );
		}

		// 应用过滤器以获得更多的选项
		curl_setopt_array( $ch, apply_filters( 'io_link_checker_curl_options', array() ) );

		//执行请求
		$start_time                = microtime_float();
		$content                   = curl_exec( $ch );
		$measured_request_duration = microtime_float() - $start_time;
		IOTOOLS::log( sprintf( 'HTTP 请求耗时 %.3f 秒', $measured_request_duration ), SAVE_CHECK_LOG );

		$info = curl_getinfo( $ch );

		//存储结果
		$result['http_code']        = intval( $info['http_code'] );
		$result['final_url']        = $info['url'];
		$result['request_duration'] = $info['total_time'];
		$result['redirect_count']   = $info['redirect_count'];

		//CURL在超时发生时不返回请求持续时间，所以自己计算。
		if ( empty( $result['request_duration'] ) ) {
			$result['request_duration'] = $measured_request_duration;
		}

		//判断链接是否计为“失效”
		if ( 0 === absint( $result['http_code'] ) ) {
			$result['broken'] = true;

			$error_code = curl_errno( $ch );
			$log       .= sprintf( "%s [Error #%d]\n", curl_error( $ch ), $error_code );

			//简单处理 CURL 的错误代码
			switch ( $error_code ) {
				case 6: //CURLE_COULDNT_RESOLVE_HOST
					$result['status_code'] = 'warning';
					$result['status_text'] = __( '未找到服务器', 'i_theme' );
					$result['error_code']  = 'couldnt_resolve_host';
					break;

				case 28: //CURLE_OPERATION_TIMEDOUT
					$result['timeout'] = true;
					break;

				case 7: //CURLE_COULDNT_CONNECT
					//此错误代码通常表示连接尝试超时。
					if ( $result['request_duration'] >= 0.9 * $conf['timeout'] ) {
						$result['timeout'] = true;
					} else {
						$result['status_code'] = 'warning';
						$result['status_text'] = __( '连接失败', 'i_theme' );
						$result['error_code']  = 'connection_failed';
					}
					break;

				default:
					$result['status_code'] = 'warning';
					$result['status_text'] = __( '未知错误', 'i_theme' );
			}
		} elseif ( 999 === $result['http_code'] ) {
			$result['status_code'] = 'warning';
			$result['status_text'] = __( '未知错误', 'i_theme' );
			$result['warning'] = true;
		} else {
			$result['broken'] = $this->is_error_code( $result['http_code'] );
		}

		curl_close( $ch );

		IOTOOLS::log(sprintf('HTTP 响应：%d，持续时间：%.2f 秒，状态文本：“%s”', $result['http_code'], $result['request_duration'], isset( $result['status_text'] ) ? $result['status_text'] : 'N/A'), SAVE_CHECK_LOG);

		if ( $nobody && !$result['timeout'] && !$use_get && ($result['broken'] || $result['redirect_count'] == 1)){
			//有问题的网站可能应该使用 GET 而不是 HEAD，所以使用 GET 重试请求。但不是在超时的情况下。
			return $this->check( $url, true );
			//Note : 通常情况下，不允许 HEAD 请求特定资源的服务器应该返回 "405 Method Not Allowed"。但是，有些网站会返回404或其他错误代码。所以仅仅检查405是不够的。
		}

		//当 safe_mode 或 open_basedir 被启用时，CURL将被禁止跟随重定向。
		//所以对于所有 URL，redirect_count 将为 0。作为一种变通方法，当HTTP响应代码表明有重定向但redirect_count为0时，将其设置为1。
		//Note: 提取Location头也可能有帮助。
		if ( ( 0 === absint( $result['redirect_count'] ) ) && ( in_array( $result['http_code'], array( 301, 302, 303, 307 ) ) ) ) {
			$result['redirect_count'] = 1;
		}

		//从HTTP代码和头构建日志
		$log .= '=== ';
		if ( $result['http_code'] ) {
			$log .= sprintf( __( 'HTTP 代码：%d', 'i_theme' ), $result['http_code'] );
		} else {
			$log .= __( '（无响应）', 'i_theme' );
		}
		$log .= " ===\n\n";

		$log .= "Response headers\n" . str_repeat( '=', 16 ) . "\n";
		$log .= htmlentities( $this->last_headers );

		if ( isset( $info['request_header'] ) ) {
			$log .= "Request headers\n" . str_repeat( '=', 16 ) . "\n";
			$log .= htmlentities( $info['request_header'] );
		}

		if ( ! $nobody && ( false !== $content ) && $result['broken'] ) {
			$log .= "Response HTML\n" . str_repeat( '=', 16 ) . "\n";
			$log .= htmlentities( substr( $content, 0, 2048 ) );
		}

		if ( ! empty( $result['broken'] ) && ! empty( $result['timeout'] ) ) {
			$log .= "\n(" . __( "很可能是连接超时、此域名不存在或者墙外。", 'i_theme' ) . ')';
		}

		$result['log'] = $log;

		// hash 应该包含链接是否工作有关的所有数据片断的信息。
		$result['result_hash'] = implode(
			'|',
			array(
				$result['http_code'],
				! empty( $result['broken'] ) ? 'broken' : '0',
				! empty( $result['timeout'] ) ? 'timeout' : '0',
				IOTOOLS::remove_query_string( $result['final_url'] ),
			)
		);

		return $result;
	}

	function read_header( $ch, $header ) {
		$this->last_headers .= $header;
		return strlen( $header );
	}

}

class ioWPHttp extends ioHttpCheckerBase {

	function check( $url ) {

		// Note : Snoopy对HTTPS网址的工作不是很好？？？？
		// $url = $this->clean_url( $url );


		$result = array(
			'broken'  => false,
			'timeout' => false,
		);
		$log    = '';

		$conf    = io_get_option( "link_check_options", array('timeout'=>30) );
		$timeout = $conf['timeout'];

		$start_time = microtime_float();

		//使用 Snoopy 获取 URL
		$snoopy               = new WP_Http;
		$request_args         = array(
			'timeout'    => $timeout,
			'user-agent' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
			'aa' => 1024 * 5,
		);
		$request = wp_safe_remote_get( $this->urlencodefix( $url ), $request_args );

		//请求超时导致 WP ERROR
		if ( is_wp_error( $request ) ) {
			$result['http_code'] = 0;
			$result['timeout']   = true;
			$result['message']   = $request->get_error_message();
		} else {
			$http_resp           = $request['http_response'];
			$result['http_code'] = $request['response']['status']; //HTTP 状态码
			$result['message']   = $request['response']['message'];
		}

		//编写日志
		$log .= '=== ';
		if ( $result['http_code'] ) {
			$log .= sprintf( __( 'HTTP 代码：%d', 'i_theme' ), $result['http_code'] );
		} else {
			$log .= __( '（无响应）', 'i_theme' );
		}
		$log .= " ===\n\n";

		if ( $result['message'] ) {
			$log .= $result['message'] . "\n";
		}

		if ( is_wp_error( $request ) ) {
			$log              .= __( '请求超时。', 'i_theme' ) . "\n";
			$result['timeout'] = true;
		}

		//判断链接是否算作“失效”
		$result['broken'] = $this->is_error_code( $result['http_code'] ) || $result['timeout'];

		$log          .= '<em>(' . __( '使用 WP HTTP', 'i_theme' ) . ')</em>';
		$result['log'] = $log;

		$result['final_url'] = $url;

		// hash 应该包含链接是否工作有关的所有数据片断的信息。
		$result['result_hash'] = implode(
			'|',
			array(
				$result['http_code'],
				$result['broken'] ? 'broken' : '0',
				$result['timeout'] ? 'timeout' : '0',
				IOTOOLS::remove_query_string( $result['final_url'] ),
			)
		);

		return $result;
	}

}

function parse_error( $ch, $timeout){
	$info = curl_getinfo( $ch );
    $result = array();
    //存储结果
    $result['http_code']        = intval( $info['http_code'] );
    $result['final_url']        = $info['url'];
    $result['request_duration'] = $info['total_time'];
    $result['redirect_count']   = $info['redirect_count'];

    //CURL在超时发生时不返回请求持续时间，所以自己计算。
    if ( empty( $result['request_duration'] ) ) {
        $result['request_duration'] = $timeout;
    }

    //判断链接是否计为“失效”
    if ( 0 === absint( $result['http_code'] ) ) {
        $result['broken'] = true;

        $error_code 	= curl_errno( $ch );
        $result['log'] 	= sprintf( "%s [Error #%d]\n", curl_error( $ch ), $error_code );

        //简单处理 CURL 的错误代码
        switch ( $error_code ) {
            case 6: //CURLE_COULDNT_RESOLVE_HOST
                $result['status_code'] = 'warning';
                $result['status_text'] = __( '未找到服务器', 'i_theme' );
                $result['error_code']  = 'couldnt_resolve_host';
                break;

            case 28: //CURLE_OPERATION_TIMEDOUT
                $result['timeout'] = true;
                break;

            case 7: //CURLE_COULDNT_CONNECT
                //此错误代码通常表示连接尝试超时。
                if ( $result['request_duration'] >= 0.9 * $timeout ) {
                    $result['timeout'] = true;
                } else {
                    $result['status_code'] = 'warning';
                    $result['status_text'] = __( '连接失败', 'i_theme' );
                    $result['error_code']  = 'connection_failed';
                }
                break;

            default:
                $result['status_code'] = 'warning';
                $result['status_text'] = __( '未知错误', 'i_theme' );
        }
    } elseif ( 999 === $result['http_code'] ) {
        $result['status_code'] = 'warning';
        $result['status_text'] = __( '未知错误', 'i_theme' );
        $result['warning'] = true;
    } else {
		$http_code = $result['http_code'];
		$good_code = ( ( $http_code >= 200 ) && ( $http_code < 400 ) ) || ( 401 === $http_code ); 
        $result['broken'] =  ! $good_code;
    }
    return $result;
}