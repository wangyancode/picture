<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2023-03-03 15:24:35
 * @LastEditors: iowen
 * @LastEditTime: 2023-09-01 19:21:40
 * @FilePath: \onenav\iopay\functions\iopay-pay.php
 * @Description: 
 */

/**
 * 发起支付
 * 
 * @param mixed $pay_sdk
 * @param mixed $order_data
 * @return array
 */
function iopay_initiate_pay_to_sdk($pay_sdk, $order_data){
    switch ($pay_sdk) {
        case 'official_alipay':
            $data = iopay_initiate_by_official_alipay($order_data);
            break;

        case 'official_wechat':
            $data = iopay_initiate_by_official_wechat($order_data);
            break;

        case 'xunhupay_v3':
            $data = iopay_initiate_by_xunhupay_v3($order_data);
            break;

        case 'xunhupay_v4':
            $data = iopay_initiate_by_xunhupay_v4($order_data);
            break;

        case 'senhuo':
            $data = iopay_initiate_by_senhuo($order_data);
            break;

        case 'epay':
            $data = iopay_initiate_by_epay($order_data);
            break;

        case 'paypal':
            $data = iopay_initiate_by_paypal($order_data);
            break;

        case 'payjs':
            $data = iopay_initiate_by_payjs($order_data);
            break;

        default:
            $data = array();
            break;
    }
    return $data;
}

/**
 * 支付宝官方
 * @param mixed $order_data
 * @return mixed
 */
function iopay_initiate_by_official_alipay($order_data){
    $config = iopay_get_option('official_alipay');

    if (empty($config['publickey'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
    }

    $return_url = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();
    if (wp_is_mobile() && $config['h5'] && $config['web_appid'] && $config['web_privatekey']) {
        $params                = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID         = $config['web_appid'];
        $params->appPrivateKey = $config['web_privatekey'];
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);

        // 支付接口
        $request                               = new \Yurun\PaySDK\AlipayApp\Wap\Params\Pay\Request;
        $request->notify_url                   = get_template_directory_uri() . '/iopay/notify/alipay/notify.php';
        $request->return_url                   = get_template_directory_uri() . '/iopay/notify/alipay/return.php';
        $request->businessParams->out_trade_no = $order_data['order_num'];
        $request->businessParams->total_amount = $order_data['order_price'];
        $request->businessParams->subject      = $order_data['order_name'];

        $pay->prepareExecute($request, $url, $data);
        if (empty($data['sign'])) {
            return array('error' => 1, 'msg' => __('支付接口参数错误，签名失败！','i_theme'));
        }

        return array('open_url' => 1, 'url' => $url);
    } elseif ($config['web_appid'] && $config['web_privatekey'] && (empty($config['privatekey']) || empty($config['appid']))) {
        $params                = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID         = $config['web_appid'];
        $params->appPrivateKey = $config['web_privatekey'];
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);

        // 支付接口
        $request                               = new \Yurun\PaySDK\AlipayApp\Page\Params\Pay\Request;
        $request->notify_url                   = get_template_directory_uri() . '/iopay/notify/alipay/notify.php';
        $request->return_url                   = get_template_directory_uri() . '/iopay/notify/alipay/return.php';
        $request->businessParams->out_trade_no = $order_data['order_num']; // 商户订单号
        $request->businessParams->total_amount = $order_data['order_price']; // 价格
        $request->businessParams->subject      = $order_data['order_name']; // 商品标题

        $pay->prepareExecute($request, $url, $data);
        if (empty($data['sign'])) {
            return array('error' => 1, 'msg' => __('支付接口参数错误，签名失败！','i_theme'));
        }

        return array('open_url' => 1, 'url' => $url);
    } else {
        //支付宝当面付
        if (empty($config['privatekey']) || empty($config['appid'])) {
            return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
        }
        $params                = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID         = $config['appid'];
        $params->appPrivateKey = $config['privatekey'];
        $params->appPublicKey  = $config['publickey'];
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        // 支付接口
        $request                               = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request;
        $request->notify_url                   = get_template_directory_uri() . '/iopay/notify/alipay/notify.php'; // 支付后通知地址
        $request->businessParams->out_trade_no = $order_data['order_num']; // 商户订单号
        $request->businessParams->total_amount = $order_data['order_price']; // 价格
        $request->businessParams->subject      = $order_data['order_name']; // 商品标题

        // 调用接口
        try {
            $data = $pay->execute($request);
        } catch (Exception $e) {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }

        if (!empty($data['alipay_trade_precreate_response']['qr_code'])) {
            $data['alipay_trade_precreate_response']['url_qrcode'] = io_get_qrcode_base64($data['alipay_trade_precreate_response']['qr_code']);
            $data['alipay_trade_precreate_response']['msg']        = __('处理完成，请扫码支付','i_theme');
            if (wp_is_mobile()) {
                $data['alipay_trade_precreate_response']['more_html'] = '<a href="' . esc_url($data['alipay_trade_precreate_response']['qr_code']) . '" class="but btn-block c-blue em09 padding-h10">'.__('跳转到支付宝APP付款','i_theme').'</a>';
            }
            return $data['alipay_trade_precreate_response'];
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    }
}

/**
 * 微信企业支付
 * @param mixed $order_data
 * @return mixed
 */
function iopay_initiate_by_official_wechat($order_data){
    $config = iopay_get_option('official_wechat');
    if (empty($config['mchid']) || empty($config['appid']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
    }

    $params         = new \Yurun\PaySDK\Weixin\Params\PublicParams;
    $params->appID  = $config['appid'];
    $params->mch_id = $config['mchid'];
    $params->key    = $config['key'];
    $pay = new \Yurun\PaySDK\Weixin\SDK($params);

    $is_wechat_app = io_is_wechat_app();
    $gzh_appid     = $config['appid'];
    $open_id       = false;
    if (wp_is_mobile() && $config['h5'] && !$is_wechat_app) {
        $request                       = new \Yurun\PaySDK\Weixin\H5\Params\Pay\Request;
        $request->body                 = $order_data['order_name'];
        $request->out_trade_no         = $order_data['order_num']; 
        $request->total_fee            = round($order_data['order_price'] * 100);
        $request->spbill_create_ip     = !empty($order_data['ip_address']) ? $order_data['ip_address'] : '127.0.0.1'; 
        $request->notify_url           = get_template_directory_uri() . '/iopay/notify/weixin/notify.php';
        $request->scene_info           = new \Yurun\PaySDK\Weixin\H5\Params\SceneInfo;

        $request->scene_info->type     = 'Wap';
        $request->scene_info->wap_url  = home_url(); 
        $request->scene_info->wap_name = get_bloginfo('name');

        $result = $pay->execute($request);
        if ($pay->checkResult()) {
            $result['open_url'] = 1;
            $result['url']      = $result['mweb_url'];
            return $result;
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    } elseif ($config['js_api'] && $is_wechat_app) {
        if ($order_data['user_id']) {
            $open_id = get_user_meta($order_data['user_id'], 'wechat_gzh_openid', true);
        }
        if (!$open_id && !empty($_REQUEST['openid'])) {
            $open_id = $_REQUEST['openid']; //用户微信openid
        }
        if (!$open_id) {
            if(!session_id()) session_start();
            $return_url   = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();
            $redirect_uri = add_query_arg(array('iopay' => 'wechat', 'return_url' => $return_url,'action' => 'get_gzh_open_id'), admin_url('admin-ajax.php'));
            $api_url      = 'https://open.weixin.qq.com/connect/oauth2/authorize?';

            $api_data = array(
                'appid'         => $gzh_appid,
                'redirect_uri'  => $redirect_uri,
                'response_type' => 'code',
                'scope'         => 'snsapi_base',
                'state'         => 'io_pay_wechat',
            );

            $url                    = $api_url . http_build_query($api_data) . '#wechat_redirect';
            $_SESSION['IOPAY_POST'] = $_POST;
            return array('open_url' => 1, 'url' => $url);
        }

        //JSAPI模式，在微信APP内调用
        $request                   = new \Yurun\PaySDK\Weixin\JSAPI\Params\Pay\Request;
        $request->body             = $order_data['order_name']; 
        $request->out_trade_no     = $order_data['order_num'];
        $request->total_fee        = round($order_data['order_price'] * 100); 
        $request->spbill_create_ip = !empty($order_data['ip_address']) ? $order_data['ip_address'] : '127.0.0.1'; 
        $request->notify_url       = get_template_directory_uri() . '/iopay/notify/weixin/notify.php'; 
        $request->openid           = $open_id; 

        $result = $pay->execute($request);
        if ($pay->checkResult()) {
            $request                = new \Yurun\PaySDK\Weixin\JSAPI\Params\JSParams\Request;
            $request->prepay_id     = $result['prepay_id'];
            $jsapiParams            = $pay->execute($request);
            $result['jsapiParams']  = $jsapiParams;
            return $result;
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    } else {
        $request                   = new \Yurun\PaySDK\Weixin\Native\Params\Pay\Request;
        $request->body             = $order_data['order_name'];
        $request->out_trade_no     = $order_data['order_num'];
        $request->total_fee        = round($order_data['order_price'] * 100);
        $request->spbill_create_ip = empty($order_data['ip_address']) ? $order_data['ip_address'] : '127.0.0.1';
        $request->notify_url       = get_template_directory_uri() . '/iopay/notify/weixin/notify.php';

        $result   = $pay->execute($request);
        $shortUrl = $result['code_url'];
        if (is_array($result) && $shortUrl) {
            $result['url_qrcode'] = io_get_qrcode_base64($shortUrl);
            return $result;
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    }
}

/**
 * 虎皮椒V3
 * @param mixed $order_data
 * @throws Exception
 * @return array
 */
function iopay_initiate_by_xunhupay_v3($order_data){
    $payment = 'alipay' == $order_data['pay_method'] ? 'alipay' : 'wechat';

    $config = iopay_get_option('xunhupay_v3');
    if ('wechat' == $payment && empty($config['wechat_appid']) && empty($config['wechat_appsecret'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
    }
    if ('alipay' == $payment && empty($config['alipay_appid']) && empty($config['alipay_appsecret'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
    }

    require_once get_theme_file_path('/iopay/sdk/xunhupay/api-v3.php');

    $appid          = $config["{$payment}_appid"];
    $appsecret      = $config["{$payment}_appsecret"];
    $trade_order_id = $order_data['order_num'];
    $home_url       = home_url();
    $return_url     = !empty($order_data['return_url']) ? $order_data['return_url'] : $home_url;
    $data = array(
        'version'        => '1.1', //固定值，api 版本，目前暂时是1.1
        'lang'           => 'zh-cn', //必须的，zh-cn或en-us 或其他，根据语言显示页面
        'plugins'        => 'iopay_xunhupay', //必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
        'appid'          => $appid, //必须的，APPID
        'trade_order_id' => $trade_order_id, //必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+
        'payment'        => $payment, //必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
        'total_fee'      => $order_data['order_price'], //人民币，单位精确到分(测试账户只支持0.1元内付款)
        'title'          => $order_data['order_name'], //必须的，订单标题，长度32或以内
        'time'           => time(), //必须的，当前时间戳，根据此字段判断订单请求是否已超时，防止第三方攻击服务器
        'notify_url'     => get_template_directory_uri() . '/iopay/notify/xunhupay/notify-v3.php?pay_type='.$payment,
        'return_url'     => $return_url,
        'callback_url'   => $return_url, 
        'nonce_str'      => str_shuffle(time()), 
    );
    if ('wechat' == $payment) {
        $data['type']     = "WAP";
        $data['wap_url']  = $home_url;
        $data['wap_name'] = $home_url;
    }

    $data['hash'] = XH_Payment_Api::generate_xh_hash($data, $appsecret);

    $url = 'https://api.xunhupay.com/payment/do.html';
    if (!empty($config['api_url'])) {
        $url = $config['api_url'];
    }

    try {
        $response = XH_Payment_Api::http_post($url, json_encode($data));
        $result = $response ? json_decode($response, true) : null;
        if (!$result) {
            throw new Exception('Internal server error', 500);
        }

        $hash = XH_Payment_Api::generate_xh_hash($result, $appsecret);
        if (!isset($result['hash']) || $hash != $result['hash']) {
            throw new Exception('Invalid sign!', 500);
        }

        if (0 != $result['errcode']) {
            throw new Exception($result['errmsg'], $result['errcode']);
        }
        /**
         * 支付回调数据
         * @var array
         *  array(
         *      order_id,//支付系统订单ID
         *      url,//支付跳转地址
         *      url_qrcode//二维码
         *  )
         */
        $result['open_url'] = wp_is_mobile();
        return $result;
        
    } catch (Exception $e) {
        return array('error' => 1, 'msg' => $e->getCode().' '.$e->getMessage());
    }
}

/**
 * 迅虎PAY v4
 * @param mixed $order_data
 * @return mixed
 */
function iopay_initiate_by_xunhupay_v4($order_data){
    $config = iopay_get_option('xunhupay_v4');
    if (empty($config['mchid']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
    }

    $is_mobile    = wp_is_mobile();
    $is_alipay_v2 = !empty($config['alipay_v2']);
    $mchid        = $config['mchid'];
    $key          = $config['key'];


    require_once get_theme_file_path('/iopay/sdk/xunhupay/api-v4.php');

    $pay_method = 'alipay' === $order_data['pay_method'] ? 'alipay' : 'wechat';

    $order_data['order_name'] = strtolower($order_data['order_name']); //订单名称转小写，避免出错
    $data = array(
        'mchid'        => $mchid,//必须的，APPID
        'out_trade_no' => $order_data['order_num'],//必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+ 
        'type'         => $pay_method, //必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
        'total_fee'    => round($order_data['order_price'] * 100), //人民币，单位精确到分(测试账户只支持0.1元内付款)
        'body'         => $order_data['order_name'],//必须的，订单标题，长度32或以内
        'notify_url'   => get_template_directory_uri() . '/iopay/notify/xunhupay/notify-v4.php',//必须的，支付成功异步回调接口
        'nonce_str'    => str_shuffle(time()), //必须的，随机字符串，作用：1.避免服务器缓存，2.防止安全密钥被猜测出来
        "attach"       => 'iopay_xunhupay_v4_' . $pay_method, //用户自定义数据，在notify的时候会原样返回
    );

    $return_url = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();
    $xhpay      = new XunhuPay($config);
    if (io_is_wechat_app() && 'wechat' === $pay_method) {
        if(!session_id()) session_start();
        if (empty($_REQUEST['openid'])) {
            $return_url              = add_query_arg('iopay', 'wechat', $return_url);
            $url                     = 'https://admin.xunhuweb.com/pay/openid?mchid=' . $mchid . '&redirect_url=' . $return_url;
            $_SESSION['IOPAY_POST'] = $_POST;
            return array('open_url' => 1, 'url' => $url);
        } else {
            $data["openid"]       = $_REQUEST['openid'];
            $data["redirect_url"] = $return_url;

            $result = $xhpay->jsapi($data);
            if (strtolower($result['return_code']) == 'success' && $result['jsapi']) {
                $result['jsapiParams'] = json_decode($result['jsapi']);
                return $result;
            } else {
                return array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
            }
        }
    }

    if ($is_mobile && $pay_method === 'wechat') {
        $data["wap_url"]  = $return_url;
        $data["wap_name"] = get_bloginfo('name');
        $result           = $xhpay->h5($data);
        if ($result['return_code'] == 'SUCCESS' && $result['mweb_url']) {
            return array('open_url' => 1, 'url' => $result['mweb_url']);
        } else {
            return array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
        }
    }

    if ($is_mobile && $pay_method === 'alipay') {
        $data["redirect_url"] = $return_url;
        if (!empty($config['alipay_v2'])) {
            $result = $xhpay->h5($data);
            if ($result['return_code'] == 'SUCCESS' && $result['mweb_url']) {
                return array('open_url' => 1, 'url' => $result['mweb_url']);
            } else {
                return array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
            }
        }
        $url = $xhpay->cashier($data);
        if ($url) {
            return array('open_url' => 1, 'url' => $url);
        }
    }
    if (!empty($config['alipay_v2']) && $pay_method === 'alipay') {
        $data['trade_type'] = "WEB";
    }

    $result = $xhpay->native($data);
    if ('SUCCESS' == $result['return_code'] && $result['code_url']) {
        $result['url_qrcode'] = io_get_qrcode_base64($result['code_url']);
    } else {
        $result = array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
    }
    return $result;
}

/**
 * 站长支付
 * @param mixed $order_data
 * @return mixed
 */
function iopay_initiate_by_senhuo($order_data){
    $config = iopay_get_option('senhuo');
    if (empty($config['appid']) || empty($config['appkey'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
    }

    $is_mobile  = wp_is_mobile();
    $appid      = $config['appid'];
    $pay_method = 'alipay' === $order_data['pay_method'] ? 'alipay' : 'wechat';
    $return_url = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();

    $order_data['order_name'] = strtolower($order_data['order_name']); //订单名称转小写，避免出错
    $data = array(
        'appid'          => $appid,//站长支付平台管理面板可以获得，zp开头
        'out_trade_no'   => $order_data['order_num'],//商户系统内部订单号，只能是数字、大小写字母_-*且在同一个商户号下唯一。
        'total'          => round($order_data['order_price'] * 100), //商品总金额，单位为分，100就是1元。
        'title'          => $order_data['order_name'],//商品的名称
        'sub_notify_url' => get_template_directory_uri() . '/iopay/notify/senhuo/notify.php',//订单支付成功之后的回调通知地址
        'redirect_url'   => $return_url, //订单支付成功|取消支付之后的跳转的地址
        'pay_type'       => $pay_method, //微信支付是：wechat，支付宝是：alipay，目前暂时只支持wechat
    );
    if (io_is_wechat_app() && 'wechat' === $pay_method) {
        $api_url = 'https://pay.senhuo.cn/pay/WeChatJSAPI.php';
        if(!session_id()) session_start();
        if (empty($_REQUEST['openid'])) {
            $return_url              = add_query_arg('iopay', 'wechat', $return_url);
            $url                     = 'https://pay.senhuo.cn/pay/WechatOpenId.php?redirect_url=' . $return_url;
            $_SESSION['IOPAY_POST'] = $_POST;
            return array('open_url' => 1, 'url' => $url);
        } else {
            $data["openid"] = $_REQUEST['openid'];
            $url = add_query_arg($data, $api_url);
            return array('open_url' => 1, 'url' => $url);
        }
    }

    if ($is_mobile && $pay_method === 'wechat') {
        $api_url  = 'https://pay3.maijiaxin.cn/pay/WeChatH5Go.php';
        $url = add_query_arg($data, $api_url);
        return array('open_url' => 1, 'url' => $url);
    }

    if ($is_mobile && $pay_method === 'alipay') {
        $api_url  = 'https://pay.senhuo.cn/pay/alipay/pay.php';
        $url = add_query_arg($data, $api_url);
        return array('open_url' => 1, 'url' => $url);
    }
    $api_url  = 'https://pay.senhuo.cn/pay/Pay.php';
    $url = add_query_arg($data, $api_url);
    return array('open_url' => 1, 'url' => $url);
}

/**
 * 易支付
 * 
 * @param mixed $order_data
 * @return array
 */
function iopay_initiate_by_epay($order_data){
    $config = iopay_get_option('epay');
    if (empty($config['apiurl']) || empty($config['pid']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！','i_theme'));
    }
    if (!isset($config['qrcode']))
        $config['qrcode'] = 0;

    $config['apiurl'] = rtrim($config['apiurl'], '/') . '/';

    require_once get_theme_file_path('/iopay/sdk/epay/EpayCore.class.php');

    $pay_method = 'alipay' === $order_data['pay_method'] ? 'alipay' : 'wxpay';

    $data = array(
        "pid"          => trim($config['pid']),
        "type"         => $pay_method,
        'notify_url'   => get_template_directory_uri() . '/iopay/notify/epay/notify.php',
        'return_url'   => !empty($order_data['return_url']) ? $order_data['return_url'] : home_url(),
        "out_trade_no" => $order_data['order_num'], //本地订单号
        "name"         => $order_data['order_name'],
        "money"        => $order_data['order_price'],
        "param"        => $order_data['pay_method'],
    );

    //建立请求
    $epay = new EpayCore($config);

    if (wp_is_mobile() || $order_data['pay_method']=="wechat" || empty($config['qrcode'])) {
        $html_text = $epay->pagePay($data);
        return array('js_go' => '<div style="display:none">' . $html_text . '</div>');
    } else {
        $data["device"] = 'pc';
        $data["clientip"] = IOTOOLS::get_ip();
        $result           = $epay->apiPay($data);

        if (isset($result['code']) && 1 == $result['code'] && !empty($result['qrcode'])) {
            $result['url_qrcode'] = io_get_qrcode_base64(urldecode( $result['qrcode']));
        } else {
            $result['error'] = 1;
            $result['msg']   = !empty($result['msg']) ? $result['msg'] : __('收款码请求失败','i_theme');
        }
        return $result;
    }
}

/**
 * PAYJS 支付
 * @param mixed $order_data
 * @return mixed
 */
function iopay_initiate_by_payjs($order_data){
    $config = iopay_get_option('payjs');
    if (empty($config['mchid']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！', 'i_theme'));
    }

    require_once get_theme_file_path('/iopay/sdk/payjs/payjs.class.php');

    $mchid      = $config['mchid'];
    $key        = $config['key'];
    $pay_method = 'alipay' == $order_data['pay_method'] ? 'alipay' : '';
    $data       = [
        "mchid"        => $mchid,
        "total_fee"    => round($order_data['order_price'] * 100), //金额。单位：分
        "out_trade_no" => $order_data['order_num'],
        "body"         => $order_data['order_name'],
        "notify_url"   => get_template_directory_uri() . '/iopay/notify/payjs/notify.php',
        "type"         => $pay_method, //支付宝交易传值：alipay ，微信支付无需此字段
        "attach"       => 'iopay_payjs', //用户自定义数据，在notify的时候会原样返回
    ];

    $payjs = new Payjs($mchid, $key);

    if (io_is_wechat_app() && 'wechat' == $order_data['pay_method']) {
        $data["callback_url"] = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();
        $data["auto"]         = 1; //auto=1：无需点击支付按钮，自动发起支付。
        $data["logo"]         = io_get_option('favicon','');
        $url                  = $payjs->cashier($data);
        if (isset($result['status']) && 0 == $result['status']) {
            return array('error' => 1, 'msg' => $result['return_msg']);
        }
        return array('open_url' => 1, 'url' => $url);
    }

    $result = $payjs->native($data);

    if ($result['return_code'] && $result['qrcode']) {
        $result['url_qrcode'] = $result['qrcode'];
    } else {
        $result = array('error' => 1, 'msg' => $result['return_msg']);
    }
    return $result;
}

/**
 * paypal官方
 * @param mixed $order_data
 * @return mixed
 */
function iopay_initiate_by_paypal($order_data){
    $config = iopay_get_option('paypal');

    if (empty($config['user']) || empty($config['pass']) || empty($config['signature']) || empty($config['rate'])) {
        return array('error' => 1, 'msg' => __('支付接口参数错误！', 'i_theme'));
    }

    require_once get_theme_file_path('/iopay/sdk/paypal/api.php');
    $paypal = new PayPal($config['user'], $config['pass'], $config['signature']);

    $order_price = $order_data['order_price'];
    $order_price = round((float) $order_price / (float) $config['rate'], 2);

    $currency_code = 'USD';
    $payment_type  = 'Sale';

    $return_url = get_template_directory_uri() . '/iopay/notify/paypal/notify.php?currencyCodeType=' . $currency_code . '&paymentType=' . $payment_type;
    $cancel_url = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();

    $nvpstr = array(
        'RETURNURL'                      => $return_url,
        'CANCELURL'                      => $cancel_url,
        'NOSHIPPING'                     => '1',//不显示送货地址字段并从交易中删除送货信息。
        'L_PAYMENTREQUEST_0_AMT0'        => $order_price,
        'L_PAYMENTREQUEST_0_NAME0'       => $order_data['order_name'],
        'L_PAYMENTREQUEST_0_QTY0'        => '1',
        'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency_code,
        'PAYMENTREQUEST_0_INVNUM'        => $order_data['order_num'],
        'PAYMENTREQUEST_0_AMT'           => $order_price,
        'PAYMENTREQUEST_0_PAYMENTACTION' => $payment_type,
        'PAYMENTREQUEST_0_CUSTOM'        => 'io_paypal'
    );

    $result = $paypal->__call("SetExpressCheckout", $nvpstr);

    $ack = strtoupper($result["ACK"]);
    if ($ack == "SUCCESS") {
        if(!session_id()) session_start();
        $_SESSION["paypal_num"]     = $order_data['order_num'];
        $_SESSION["paypal_return"]  = $order_data['return_url'];

        $token      = urldecode($result["TOKEN"]);
        $paypal_url = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=' . $token;
        return array('open_url' => 1, 'url' => $paypal_url);
    } else {
        $result['error'] = 1;
        $result['msg']   = $result['L_LONGMESSAGE0'];
        return $result;
    }
}
