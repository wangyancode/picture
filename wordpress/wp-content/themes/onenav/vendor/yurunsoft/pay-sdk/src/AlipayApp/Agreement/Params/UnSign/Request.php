<?php

namespace Yurun\PaySDK\AlipayApp\Agreement\Params\UnSign;

use Yurun\PaySDK\AlipayRequestBase;

/**
 * 支付宝个人代扣协议解约请求类.
 */
class Request extends AlipayRequestBase
{
    /**
     * 接口名称.
     *
     * @var string
     */
    public $method = 'alipay.user.agreement.unsign';

    /**
     * 业务请求参数
     * 参考https://opendocs.alipay.com/open/b841da1f_alipay.user.agreement.unsign
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
