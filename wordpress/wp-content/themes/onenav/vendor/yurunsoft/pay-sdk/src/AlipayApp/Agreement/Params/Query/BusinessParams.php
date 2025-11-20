<?php

namespace Yurun\PaySDK\AlipayApp\Agreement\Params\Query;

class BusinessParams
{
    use \Yurun\PaySDK\Traits\JSONParams;

    /**
     * 用户的支付宝账号对应 的支付宝唯一用户号，以 2088 开头的 16 位纯数字 组成。 本参数与alipay_logon_id若都填写，则以本参数为准，优先级高于 alipay_logon_id。
     *
     * @var string
     */
    public $alipay_user_id;

    /**
     * 用户的支付宝账号对应 的支付宝唯一用户号， 本参数与alipay_logon_id若都填写，则以本参数为准，优先级高于 alipay_logon_id。  详情可查看 openid简介
     *
     * @var string
     */
    public $alipay_open_id;

    /**
     * 协议产品码，商户和支付宝签约时确定，商户可咨询技术支持。
     *
     * @var string
     */
    public $personal_product_code;

    /**
     * 用户的支付宝登录账号，支持邮箱或手机号码格式。本参数与alipay_open_id 或 alipay_user_id 同时填写，优先按照 alipay_open_id 或 alipay_user_id 处理。
     *
     * @var string
     */
    public $alipay_logon_id;

    /**
     * 签约场景码，该值需要与系统/页面签约接口调用时传入的值保持一 致。如：周期扣款场景与调用 alipay.user.agreement.page.sign(支付宝个人协议页面签约接口) 签约时的 sign_scene 相同。 注意：当传入商户签约号 external_agreement_no 时，该值不能为空或默认值 DEFAULT|DEFAULT。
     *
     * @var string
     */
    public $sign_scene;


    /**
     * 代扣协议中标示用户的唯一签约号(确保在商户系统中 唯一)。 格式规则:支持大写小写字母和数字，最长 32 位。
     *
     * @var string
     */
    public $external_agreement_no;

    /**
     * 签约第三方主体类型。对于三方协议，表示当前用户和哪一类的第三方主体进行签约。 默认为PARTNER。
     *
     * @var string
     */
    public $third_party_type;

    /**
     * 支付宝系统中用以唯一标识用户签约记录的编号（用户签约成功后的协议号 ） ，如果传了该参数，其他参数会被忽略
     *
     * @var string
     */
    public $agreement_no;

    public function toString()
    {
        $obj = (array)$this;
        foreach ($obj as $key => $value)
        {
            if (null === $value)
            {
                unset($obj[$key]);
            }
        }

        return json_encode($obj);
    }
}
