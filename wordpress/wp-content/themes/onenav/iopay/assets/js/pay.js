var order_result = {};
var pay_inputs = {};

(function ($) { 
    $(document).on("click", '.initiate-pay', function () {
        var _this = $(this);
        if (_this.attr('disabled')) {
            return false;
        }
        var form = _this.parents('form');
        var data = form.serializeObject();
        data.action = 'initiate_pay';
        data.return_url || (data.return_url = window.location.href);
        pay_inputs = data;


        pay_ajax(data,_this);
        return false;
    });
})(jQuery);

function weixin_auto_send() {
    var iopay = GetQueryVal('iopay');
    var openid = GetQueryVal('openid');
    if (iopay && openid && is_weixin_app()) {
        pay_inputs.pay_type = 'wechat';
        pay_inputs.openid = openid;
        pay_inputs.action = 'initiate_pay';

        pay_ajax(pay_inputs, $('<div></div>'));
    }
}

function is_weixin_app() {
    var ua = window.navigator.userAgent.toLowerCase();
    return (ua.match(/MicroMessenger/i) == 'micromessenger');
}

function verify_pay() {
    if (order_result.order_num) {
        $.ajax({
            type: "POST",
            url: IO.ajaxurl,
            data: {
                "action": "check_pay",
                "order_num": order_result.order_num,
            },
            dataType: "json",
            success: function (n) {
                if (n.status == "1") {
                    var alert = {};
                    alert.status = 1;
                    alert.msg = IO.localize.payGoto;
                    showAlert(alert);
                    setTimeout(function () {
                        if ("undefined" != typeof pay_inputs.return_url && pay_inputs.return_url) {
                            window.location.href = delQueStr('openid', delQueStr('iopay', pay_inputs.return_url));
                            window.location.reload;
                        } else {
                            location.href = delQueStr('openid', delQueStr('iopay'));
                            location.reload;
                        }
                    }, 300);
                } else {
                    setTimeout(function () {
                        verify_pay();
                    }, 2000);
                }
            }
        });
    }
}

function weixin_bridge_ready(jsapiParams) {
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest', jsapiParams,
        function (res) {
            if (res.err_msg == "get_brand_wcpay_request:ok") {
                // 使用以上方式判断前端返回,微信团队郑重提示：
                //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                //支付成功刷新页面
            }
            location.href = delQueStr('openid', delQueStr('iopay'));
            location.reload;
        }
    );
}


function delQueStr(ref, url) {
    var str = "";
    url = url || window.location.href;
    if (url.indexOf('?') != -1) {
        str = url.substr(url.indexOf('?') + 1);
    } else {
        return url;
    }
    var arr = "";
    var returnurl = "";
    if (str.indexOf('&') != -1) {
        arr = str.split('&');
        for (var i in arr) {
            if (arr[i].split('=')[0] != ref) {
                returnurl = returnurl + arr[i].split('=')[0] + "=" + arr[i].split('=')[1] + "&";
            }
        }
        return url.substr(0, url.indexOf('?')) + "?" + returnurl.substr(0, returnurl.length - 1);
    } else {
        arr = str.split('=');
        if (arr[0] == ref) {
            return url.substr(0, url.indexOf('?'));
        } else {
            return url;
        }
    }
}

function pay_ajax(_data,_this) {
    var alert = {};
    alert.status = 0;
    alert.msg = IO.localize.loading;
    showAlert(alert);

    var _text = _this.html();
    _this.attr('disabled', true).html('<i class="iconfont icon-loading icon-spin mr-2"></i>'+IO.localize.wait);
    $.ajax({
        url: IO.ajaxurl,
        type: 'POST', 
        dataType: 'json',
        data : _data, 
    }).done(function(n){
        if (n.msg) {
            alert.status = n.status?n.status:(n.error ? 4 : 1);
            alert.msg = n.msg;
            showAlert(alert);
        }
        _this.attr('disabled', false).html(_text);

        if (n.expiration) {
            alert.status = n.status?n.status:(n.error ? 4 : 1);
            alert.msg = n.expiration;
            showAlert(alert);
        }

        if (n.error) {
            removeAlert();
            return;
        }

        if (n.url && n.open_url) {
            window.location.href = n.url;
            window.location.reload;
            return;
        }

        if (n.reload) {
            window.location.reload();
            return;
        }

        if (n.jsapiParams) {
            var jsapiParams = n.jsapiParams;
            if (typeof WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener('WeixinJSBridgeReady', weixin_bridge_ready(jsapiParams), false);
                } else if (document.attachEvent) {
                    document.attachEvent('WeixinJSBridgeReady', weixin_bridge_ready(jsapiParams));
                    document.attachEvent('onWeixinJSBridgeReady', weixin_bridge_ready(jsapiParams));
                }
            } else {
                weixin_bridge_ready(jsapiParams);
            }
            removeAlert();
            return;
        }

        if (n.url_qrcode) {
            $(".modal").modal('hide'); //隐藏其他模态框
            var tips = IO.localize.scanQRPay;
            if (n.expiration) {
                tips = n.expiration;
            }
            var html = '<div class="pay-qr '+n.pay_method+'">\
                <div class="pay-header my-4"><span class="pay-logo"></span><span class="pay-name title-wechat">'+IO.localize.weChatPay+'</span><span class="pay-name title-alipay">'+IO.localize.alipay+'</span></div>\
                <div class="pay-body p-3">\
                    <div class="pay-title text-sm">'+n.order_name+'</div>\
                    <div class="pay-price my-2"><span class="text-xs">￥</span><span class="text-xl">'+n.order_price+'</span></div>\
                    <div class="mx-4">\
                    <div class="pay-qrcode mx-auto">\
                        <img src="'+n.url_qrcode+'" alt="pay-qrcode">\
                    </div>\
                    </div>\
                <div class="pay-notice text-sm">'+tips+'</div>\
                </div>\
            </div> ';
            ioPopup('pay', html,'', function (r) {
                order_result.order_num = false;
            });
            //开始ajax检测是否付费成功
            order_result = n;
            removeAlert();
            verify_pay();
        }

        if (n.js_go) {
            $("body").before(n.js_go);
        }
    }).fail(function (n) { 
        n = n.responseJSON;
        if (n && n.msg) {
            alert.status = n.status;
            alert.msg = n.msg;
            showAlert(alert);
        } else {
            alert.status = 4;
            alert.msg = IO.localize.networkError;
            showAlert(alert);
        }
        _this.attr('disabled', false).html(_text);
    })
    
}
