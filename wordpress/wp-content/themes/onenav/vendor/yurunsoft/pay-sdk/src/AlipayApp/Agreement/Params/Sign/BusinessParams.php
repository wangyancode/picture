<?php

namespace Yurun\PaySDK\AlipayApp\Agreement\Params\Sign;

class BusinessParams
{
    use \Yurun\PaySDK\Traits\JSONParams;

    /**
     * 个人签约产品码，商户和支付宝签约时确定，商户可咨询技术支持。
     *
     * @var string
     */
    public $personal_product_code = 'CYCLE_PAY_AUTH_P';

    /**
     * 请按当前接入的方式进行填充，且输入值必须为文档中的参数取值范围。 扫码或者短信页面签约需要拼装http的请求地址访问中间页面，钱包h5页面签约可直接拼接scheme的请求地址
     *
     * @var array
     */
    public $access_params = ['channel' => 'ALIPAYAPP'];

    /**
     * 周期管控规则参数period_rule_params，在签约周期扣款产品（如CYCLE_PAY_AUTH_P）时必传，在签约其他产品时无需传入。 周期扣款产品，会按照这里传入的参数提示用户，并对发起扣款的时间、金额、次数等做相应限制。
     *
     * @var array
     */
    public $period_rule_params;

    /**
     * 销售产品码，商户签约的支付宝合同所对应的产品码。
     *
     * @var string
     */
    public $product_code = 'GENERAL_WITHHOLDING';

    /**
     * 用户在商户网站的登录账号，用于在签约页面展示，如果为空，则不展示
     *
     * @var string
     */
    public $external_logon_id;

    /**
     * 协议签约场景，商户可根据 代扣产品常见场景值 选择符合自身的行业场景。 说明：当传入商户签约号 external_agreement_no 时，本参数必填，不能为默认值 DEFAULT|DEFAULT。
     *
     * @var string
     */
    public $sign_scene = 'INDUSTRY|DEFAULT_SCENE';

    /**
     * 商户签约号，代扣协议中标示用户的唯一签约号（确保在商户系统中唯一）。 格式规则：支持大写小写字母和数字，最长32位。 商户系统按需自定义传入，如果同一用户在同一产品码、同一签约场景下，签订了多份代扣协议，那么需要指定并传入该值。
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
     * 当前用户签约请求的协议有效周期。 整形数字加上时间单位的协议有效期，从发起签约请求的时间开始算起。 目前支持的时间单位： 1. d：天 2. m：月 如果未传入，默认为长期有效。
     *
     * @var string
     */
    public $sign_validity_period;

    /**
     * 芝麻授权信息，针对于信用代扣签约。json格式。
     *
     * @var array
     */
    public $zm_auth_params;

    /**
     * 签约产品属性，json格式
     *
     * @var array
     */
    public $prod_params;

    /**
     * 签约营销参数，此值为json格式；具体的key需与营销约定
     *
     * @var string
     */
    public $promo_params;

    /**
     * 此参数用于传递子商户信息，无特殊需求时不用关注。目前商户代扣、海外代扣、淘旅行信用住产品支持传入该参数（在销售方案中“是否允许自定义子商户信息”需要选是）。
     *
     * @var array
     */
    public $sub_merchant;

    /**
     * 设备信息参数，在使用设备维度签约代扣协议时，可以传这些信息
     *
     * @var array
     */
    public $device_params;

    /**
     * 用户实名信息参数，包含：姓名、身份证号、签约指定uid。商户传入用户实名信息参数，支付宝会对比用户在支付宝端的实名信息。
     *
     * @var array
     */
    public $identity_params;

    /**
     * 协议生效类型, 用于指定协议是立即生效还是等待商户通知再生效. 可空, 不填默认为立即生效.
     *
     * @var string
     */
    public $agreement_effect_type;


    /**
     * 商户希望限制的签约用户的年龄范围，min表示可签该协议的用户年龄下限，max表示年龄上限。如{"min": "18","max": "30"}表示18=<年龄<=30的用户可以签约该协议。
     *
     * @var string
     */
    public $user_age_range;

    /**
     * 签约有效时间限制，单位是秒，有效范围是0-86400，商户传入此字段会用商户传入的值否则使用支付宝侧默认值，在有效时间外进行签约，会进行安全拦截；（备注：此字段适用于需要开通安全防控的商户，且依赖商户传入生成签约时的时间戳字段timestamp）
     *
     * @var int
     */
    public $effect_time;

    public function toString()
    {
        $obj = (array)$this;
        $jsonParams = [
            'zm_auth_params',
            'prod_params',
            'promo_params',
        ];
        foreach ($jsonParams as $param)
        {
            if (null === $obj[$param])
            {
                unset($obj[$param]);
            }
            else
            {
                $obj[$param] = json_encode($obj[$param]);
            }
        }
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
