/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-01-24 21:51:38
 * @LastEditors: iowen
 * @LastEditTime: 2022-06-24 18:46:59
 * @FilePath: \onenav\inc\admin\assets\page-list.js
 * @Description: 
 */
(function ($) {
    $(document).on("click", ".io-recheck-button", function () {
        var _this = $(this);
        var _wind = $('#invalid_info');
        var _close = $('.invalid-close');
        var _notice = $('.invalid-doc');
        _wind.show();
        _close.hide();
        _notice.html('<p style="text-align: center;">稍 等 . . .</p>');
        $.ajax({
            type: "POST",
            url: _this.attr('ajax-url'),
            data: _this.data(),
            dataType: "json",
            error: function (n) {
                var n_con = '<div class="invalid invalid-error">网络异常或者操作失败，请稍候再试！ </div>';
                _notice.html(n_con);
                _close.show();
            },
            success: function (n) {
                var n_con = '';
                if(n.status){
                    var redirect = n.redirect_count>0 ? '<br>最终网址：' + n.final_url : '';
                    n_con = '<div class="invalid invalid-' + n.status_code + '"><p><b>网址：</b>' + n.url + '</p>\
                        状态：' + n.status_text + '<br>代码：' + n.http_code + '<br>重定向：' + n.redirect_count + ' 次'+redirect+'</div>';
                    n_con += '<div class="invalid-body"><div class="invalid-log">'+n.log.replace(/\n/g,"<br>")+'</div></div>';
                }else{
                    n_con = '<div class="invalid invalid-error">'+ n.error + '</div>';
                }
                _notice.html(n_con);
                
                _close.show();
            }
        });
    });
    $(document).on("click", ".invalid-close", function () {
        $('.invalid-doc').html("");
        $('#invalid_info').hide();
    });
})(jQuery);

