<?php

namespace Yurun\PaySDK\AlipayApp\Agreement\Params\Query;

use Yurun\PaySDK\AlipayRequestBase;

/**
 * 支付宝个人代扣协议查询请求类.
 */
class Request extends AlipayRequestBase
{
    /**
     * 接口名称.
     *
     * @var string
     */
    public $method = 'alipay.user.agreement.query';

    /**
     * 业务请求参数
     * 参考https://opendocs.alipay.com/open/3dab71bc_alipay.user.agreement.query
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
