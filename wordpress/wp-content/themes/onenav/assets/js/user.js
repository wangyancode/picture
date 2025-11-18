/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-11-04 20:46:13
 * @LastEditors: iowen
 * @LastEditTime: 2024-11-05 01:02:04
 * @FilePath: /onenav/assets/js/user.js
 * @Description: 
 */
'use strict';

var IO = IO || {};

(function ($) {
    $(document).on('submit', '.io-user-ajax', function () {
        var t = $(this);
        t.find('.submit').text(IO.ucVar.local.saving).attr("disabled", true);
        $.ajax({
            url: IO.ajaxurl,
            data: $(this).serialize(),
            type: 'POST',
            dataType: 'json',
        }).done(function (response) {
            if (response.success && response.data.refresh) {
                setTimeout(function () {
                    location.reload();
                }, 500);
            }
            showAlert(response.data);
        }).fail(function () {
            showAlert({ "status": 4, "msg": IO.ucVar.local.networkError });
        }).always(function () {
            t.find('.submit').text(IO.ucVar.local.saveProfile).removeAttr("disabled");
        });
        return false;
    });

    // 绑定弹窗
    $(document).on('click','.user-bind-modal',function(){
        var $this = $(this);
        var url = $this.attr('href');
        var modal = ioModal($this);
        $.get(url, null, function (data, status) {
            modal.find('.io-modal-content').html(data).slideDown(200, function () {
                modal.find('.loading-anim').fadeOut(200);
                var height = $(this).outerHeight();
                var content = modal.find('.modal-content');
                content.animate({
                    'height': height,
                }, 200, 'swing', function () {
                    content.css({
                        'height': '',
                        'overflow': '',
                        'transition': ''
                    })
                });
            });
            $('[captcha-type]').length && ioRequire('captcha', function(){
                CaptchaInit();
            });
        });
        return false;
    });
    // 绑定弹窗提交
    $(document).on("click",".user-bind-from .btn-submit",function() { 
        var _this = $(this); 
        var content = _this.closest('.modal-content'); 
        captcha_ajax(_this, '', function (n) {
            if (n.html) {
                _this.closest('.io-modal-content').html(n.html).slideDown(200, function () {
                    var height = $(this).outerHeight();
                    content.animate({
                        'height': height,
                    }, 200, 'swing', function () {
                        content.css({
                            'height': '',
                            'overflow': '',
                            'transition': ''
                        })
                    });
                });
                $('[captcha-type]').length && ioRequire('captcha', function(){
                    CaptchaInit();
                });
            }
        });
        return false;
    });
    // 重置密码弹窗
    $(document).on('click','.user-reset-password',function(){
        var _this = $(this);
        var url = _this.attr('href');
        var content = _this.closest('.modal-content'); 
        content.css({
            'height': content.outerHeight()
        }).animate({
            'height': '220px'
        },200);
        content.find('.io-modal-content').html('');
        content.find('.loading-anim').fadeIn(200);
        $.get(url, null, function (data, status) {
            content.find('.io-modal-content').html(data).slideDown(200, function () {
                content.find('.loading-anim').fadeOut(200);
                var height = $(this).outerHeight();
                content.animate({
                    'height': height,
                }, 200, 'swing', function () {
                    content.css({
                        'height': '',
                        'overflow': '',
                        'transition': ''
                    })
                });
            });
            $('[captcha-type]').length && ioRequire('captcha', function(){
                CaptchaInit();
            });
        });
        return false;
    });
    
    $(document).on('click','.unbound-open-id',function(){ 
        var t = $(this);
        ioConfirm('你确定要解除绑定？', '<div>解绑前请先设置邮箱和密码，否则可能造成账号丢失！<br><br>是否继续？</div>',
        function(result){
            if(result){
                var data = {
                    user_id:t.data('user_id'),
                    type:t.data('type'),
                    action:t.data('action')
                };
                unbound_ajax(data);
            }else{
                console.log( '取消操作！');
            }
        }); 
        var unbound_ajax = function(data){
            jQuery.ajax({
                url: IO.ajaxurl, 
                data : data,
                type: 'POST',
                dataType: 'json',
            })
            .done(function(response) {  
                if(response.status == 1){
                    location.reload();
                }
                showAlert(response); 
            })
            .fail(function() {  
                showAlert({ "status": 4, "msg": IO.ucVar.local.networkError });
            });
        }
    }); 

    
    var uploaderAvatar = WebUploader.create({
        auto: true,
        server: IO.ajaxurl,
        pick: {
            id: '.custom-avatar-picker',
            innerHTML: "",
            multiple: false
        },
        accept: {
            title: "Images",
            extensions: "jpg,jpeg,bmp,png",
            mimeTypes: "image/*"
        },
        fileSingleSizeLimit: 512 * 1024,
        formData: {
            action: "upload_user_avatar",
            img_type: "avatar"
        },
        compress: false,
    });
    uploaderAvatar.on('uploadSuccess', function (file, response) {
        if (response.success) {
            if (response.data.refresh) {
                setTimeout(function () {
                    location.reload();
                }, 500);
            }
            $(".io-avatar-custom").attr("src", response.data.data.img);
        }
        showAlert(response.data);
    });
    uploaderAvatar.on('uploadError', function (file) {
        showAlert({ "status": 4, "msg": IO.ucVar.local.avatarFailed });
    });
    uploaderAvatar.on('error', function (type) {
        if (type == 'Q_EXCEED_SIZE_LIMIT' || type == 'F_EXCEED_SIZE') {
            showAlert({ "status": 3, "msg": IO.ucVar.local.avatarMax });
        } else if (type == 'Q_TYPE_DENIED') {
            showAlert({ "status": 3, "msg": IO.ucVar.local.fileType });
        } else {
            showAlert({ "status": 3, "msg": IO.ucVar.local.uploadFailed });
        }
    });

    var uploaderCover = WebUploader.create({
        auto: true,
        server: IO.ajaxurl,
        pick: {
            id: '.custom-cover-picker',
            innerHTML: "",
            multiple: false
        },
        accept: {
            title: "Images",
            extensions: "jpg,jpeg,bmp,png",
            mimeTypes: "image/*"
        },
        fileSingleSizeLimit: 1024 * 1024,
        formData: {
            action: "upload_user_avatar",
            img_type: "cover"
        },
        compress: false,
    });
    uploaderCover.on('uploadSuccess', function (file, response) {
        if (response.success) {
            if (response.data.refresh) {
                setTimeout(function () {
                    location.reload();
                }, 500);
            }
            $(".io-cover-custom").attr("src", response.data.data.img);
        }
        showAlert(response.data);
    });
    uploaderCover.on('uploadError', function (file) {
        showAlert({ "status": 4, "msg": IO.ucVar.local.coverFailed });
    });
    uploaderCover.on('error', function (type) {
        if (type == 'Q_EXCEED_SIZE_LIMIT' || type == 'F_EXCEED_SIZE') {
            showAlert({ "status": 3, "msg": IO.ucVar.local.coverMax });
        } else if (type == 'Q_TYPE_DENIED') {
            showAlert({ "status": 3, "msg": IO.ucVar.local.fileType });
        } else {
            showAlert({ "status": 3, "msg": IO.ucVar.local.uploadFailed });
        }
    });
    
})(jQuery);
