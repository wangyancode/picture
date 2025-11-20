<?php

namespace Yurun\PaySDK\AlipayApp\Agreement\Params\Modify;

class BusinessParams
{
    use \Yurun\PaySDK\Traits\JSONParams;

    /**
     * 周期性扣款产品，授权免密支付协议号
     *
     * @var string
     */
    public $agreement_no;

    /**
     * 商户下一次扣款时间
     *
     * @var string
     */
    public $deduct_time;

    /**
     * 具体修改原因
     *
     * @var string
     */
    public $memo;

    public function toString()
    {
        $obj = (array)$this;
        foreach ($obj as $key => $value) {
            if (null === $value) {
                unset($obj[$key]);
            }
        }

        return json_encode($obj);
    }
}
