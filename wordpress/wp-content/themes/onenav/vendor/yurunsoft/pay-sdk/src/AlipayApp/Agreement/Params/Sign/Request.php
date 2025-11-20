<?php

namespace Yurun\PaySDK\AlipayApp\Agreement\Params\Sign;

use Yurun\PaySDK\AlipayRequestBase;

/**
 * 支付宝个人协议页面签约请求类.
 */
class Request extends AlipayRequestBase
{
    /**
     * 接口名称.
     *
     * @var string
     */
    public $method = 'alipay.user.agreement.page.sign';

    /**
     * 同步返回地址，HTTP/HTTPS开头字符串.
     *
     * @var string
     */
    public $return_url;

    /**
     * 支付宝服务器主动通知商户服务器里指定的页面http/https路径。
     *
     * @var string
     */
    public $notify_url;

    /**
     * 业务请求参数
     * 参考https://opendocs.alipay.com/open/8bccfa0b_alipay.user.agreement.page.sign
     *
     * @var \Yurun\PaySDK\AlipayApp\Agreement\Params\Sign\BusinessParams
     */
    public $businessParams;

    public function __construct()
    {
        $this->businessParams = new BusinessParams();
        $this->_method = 'GET';
    }
}
