/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2022-01-24 21:51:38
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-04 16:18:25
 * @FilePath: /onenav/inc/framework/customize/js/options.js
 * @Description: 
 */
(function ($) {
    $(".csf-section .new").each(function(){
        var str = $(this).parent().data("section-id");
        $(".csf-tab-item a[data-tab-id=\'"+str+"\']").addClass("io-new-item");
        if(str){
        $(".csf-tab-item a[data-tab-id=\'"+str.split("/")[0]+"\']").addClass("io-new-item");
        }
    });
    
    $(document).on('click', '#wp-link-backdrop,#wp-link-close,#wp-link-cancel', function() {
        $('#wp-link')[0].reset();
    });

    $(document).ready(function ($) {
        if($('.auto-click').length > 0){
            $('.auto-click').click();
        }
        setTimeout(function () {
            console.log('已加载完毕');
            $('.add-custom input[name*="type"]:checked').each(function () {
                if ($(this).val() == 'tools') {
                    setToolsName($(this));
                }
            });
            $('.add-custom input[name*="sidebar_tools"]:checked').each(function () {
                if ($(this).val() != 'none') {
                    setToolsName($(this));
                }
            });
        },100);
    });

    $(document).on('change', '.add-custom .csf-field-button_set input[type="radio"]', function () {
        setToolsName($(this));
    });

    function setToolsName($this) {
        var $box = $this.closest('.add-custom');
        var $item = $this.closest('.csf-repeater-item');
        var $cloneable = $this.closest('.csf-cloneable-item');
        var $tab = $this.closest('.csf-fieldset-content');

        var groupName = $this.attr('name');
        var isSidebarTools = groupName.includes('sidebar_tools');
        var isSub = $cloneable.length > 0;
        var itemId = $item.index() + 1;
        var selectedValue = $(`input[name="${groupName}"]:checked`).val();

        var title = '首页';
        if (isSub) {
            title = `子《${$cloneable.find('input[name*="second_id"]').val() || '未命名'}》`;
        }

        var modeName = `模块${itemId}`;

        var typeName = $item.find('input[name*="type"]:checked').parent().text() + ' ';
        if (isSidebarTools) {
            typeName += $tab.find('input[name*="sidebar_id"]').val();
        } else {
            if (selectedValue != 'tools') {
                return;
            }
            typeName += $item.find('input[name*="tool_id"]').val();
        }

        var lastName = isSidebarTools ? `${$this.parent().text()}边栏` : '正文模块';

        var name = `${title}-${modeName} - [${typeName}] - ${lastName}`;

        if (isSidebarTools) {
            var $t = $tab.find('.sidebar-name');
            if (selectedValue == 'none') {
                name = '未启用';
                $t.addClass('disabled');
            } else {
                $t.removeClass('disabled');
            }
            $t.text(name);
        } else {
            $item.find('.tools-name').text(name);
        }
        console.log(name);
    }

    $(document).on('click', '.add-custom .csf-repeater-add', function () {
        var $this = $(this);
        var $box = $this.closest('.add-custom');
        var $toolInputs = $box.find('input[name*="tool_id"]');
        var $sidebarInputs = $box.find('input[name*="sidebar_id"]');
        var $newItem = $box.find('.csf-repeater-item:last');

        var toolMaxIndex = Math.max(...$toolInputs.map(function () {
            return parseFloat($(this).val()) || 0;
        }).get());

        var sidebarMaxIndex = Math.max(...$sidebarInputs.map(function () {
            return parseFloat($(this).val()) || 0;
        }).get());

        $newItem.find('input[name*="tool_id"]').val(toolMaxIndex + 1);
        $newItem.find('input[name*="sidebar_id"]').val(sidebarMaxIndex + 1);
        
        console.log('新小工具ID', toolMaxIndex, sidebarMaxIndex, $newItem.length ? '添加成功' : '添加失败');
    });
    
    $(document).on('change', '.csf-fieldset-content .custom-unit input:radio', function () {
        var t = $(this);
        var p = t.closest('.csf-fieldset-content');
        var u = t.next('.csf--text').text();
        var us = [];
        t.closest('ul').find('.csf--text').each(function () {
            us.push($(this).text())
        });
        p.find('.csf--unit,.csf-cloneable-title-prefix').each(function () {
            var _t = $(this);
            var reg = new RegExp(us.join('|'));
            var unit = _t.text().replace(reg, u);
            _t.text(unit);
        })
    });

    $(document).on("click", ".home-widget-type", function () {
        var t = $(this);
        var post_type = { // 文章类型与分类的映射关系,兼容旧版本
            'post': 'category',
            'sites': 'favorites',
            'app': 'apps',
            'book': 'books',
        };
        var type = t.find('input:radio:checked').val();
        if (post_type[type]) { 
            type = post_type[type];
        }
        var target = t.next().find('select.csf-chosen-ajax');
        var query = target.data('chosen-settings');
        query.data.query_args.taxonomy = type;
        target.data('chosen-settings', query);
    });
    $(document).on("click", ".csf-content", function (){ 
        var $wrapper = $('.csf-wrapper.io-option');
        if($wrapper.hasClass('csf-show-all')){
            $wrapper.removeClass('csf-show-all');
        }
    });
    $(document).on("click", ".ajax-submit", function () {
        var _data = {};
        var _this = $(this);
        var form = $(_this.parents(".ajax-form")[0]);
        if (_this.attr("disabled")) {
            return false;
        }
        form.find("input,[ajax-name],[name]").each(function () {
            n = $(this).attr("ajax-name") || $(this).attr("name"), v = $(this).val();
            if (n) {
                _data[n] = v;
            }
        });
        if(_data.action=="get_iotheme_delete_authorization"){
            var r= confirm( "你确定要删除授权吗？" );
            if (r!=true){
                return false;
            }
        }
        return ajax_submit(_this, _data, function (n) {
            if (n.html) {
                form.find('.ret-html').html(n.html);
                var weixin_data_state = $('#weixin_data_state');
                if (n.wx_data) {
                    var wx_data = n.wx_data;
                    if (wx_data.state == '1') {
                        weixin_data_state.text('正常');
                        weixin_data_state.attr('class', 'but c-blue');
                    } else {
                        weixin_data_state.text('异常');
                    }
                    form.find('input[name="data[token]"]').val(wx_data.data.token);
                    form.find('textarea[name="data[cookie]"]').val(wx_data.data.cookie);
                    form.find('input[name="email"]').val(wx_data.email);
                    $('#weixin_data_id').val(wx_data.id);
                } else {
                    weixin_data_state.text('未知');
                }
            }
            if (n.action) {
                form.find('.ret-action').val(n.action);
            }
        }), !1; 
    })
    $(document).on("click", ".ajax-get", function () {
        var _this = $(this);
        var confirm_text = _this.attr('data-confirm');
        if (confirm_text) {
            if (confirm(confirm_text) == true) {
                return ajax_submit(_this, {}), !1;
            } else {
                return !1;
            }
        } else {
            return ajax_submit(_this, {}), !1;
        }
    });
    function ajax_submit(_this, _data, success, notice, e) {
        var form = _this.parents(".ajax-form,ajaxform");
        var _notice = form.find(".ajax-notice");
        var _tt = _this.html();
        var ajax_url = form.attr("ajax-url") || _this.attr("href");
        var spin = '<i class="fa fa-spinner fa-spin fa-fw"></i> '
        var n_type = "warning";
        var n_msg = spin + '正在处理，请稍候...';
        _this.attr("disabled", true);
        if(!_this.hasClass("bnt-svg")) _this.html(spin + "请稍候...");
        if (notice) {
            _notice.html('<div style="padding: 10px;margin: 0;" class="notice"></div>');
            notice = spin + notice;
        }
        _notice.find('.notice').html(notice || n_msg).removeClass('notice-error notice-info').addClass('notice-warning');
        $.ajax({
            type: "POST",
            url: ajax_url,
            data: _data,
            dataType: "json",
            error: function (n) {
                var n_con = '<div style="padding: 10px;margin: 0;" class="notice notice-error"><b>' + "网络异常，请稍候再试！如果使用了CDN，请设置CDN回源跟随协议、SSL严格模式或者开启类似功能，应各家CDN设置不同，具体选项请咨询CDN客服。" + n.status + '|' + n.statusText + '</b></div>';
                _notice.html(n_con);
                _this.attr("disabled", false).removeClass('jb-blue');
                if(!_this.hasClass("bnt-svg")) _this.html("操作失败");
                form.find('.progress').css('opacity', 0).find('.progress-bar').css({
                    'width': '0',
                    'transition': 'width .3s',
                });
            },
            success: function (n) {
                if (n.msg) {
                    n_type = n.error_type || (n.error ? "error" : "info");
                    var n_con = '<div style="padding: 10px;margin: 0;" class="notice notice-' + n_type + '"><b>' + n.msg + '</b></div>';
                    _notice.html(n_con);
                }
                _this.attr("disabled", false);
                if(!_this.hasClass("bnt-svg")) _this.html(n.button || _tt);
                if (n.reload) {
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
                $.isFunction(success) && success(n, _this, _data);
            }
        });
    }
    var ajax_url;
    function update_load(){
		$.get(ajax_url,
			{
				'action' : 'io_current_load'
			},
			function (data, textStatus){
				$('#io_current_load').html(data);
				setTimeout(update_load, 10000); // 每10秒更新一次
			}
		);
    }
	if ( $('#io_current_load').length > 0 ){
        ajax_url = $('#io_current_load').data('url');
        update_load();
	}
})(jQuery);