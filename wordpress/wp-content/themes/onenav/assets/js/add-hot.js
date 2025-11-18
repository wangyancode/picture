/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-07-26 12:01:17
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-07 14:38:35
 * @FilePath: /onenav/assets/js/add-hot.js
 * @Description: 
 */
(function($){
    $(document).on('click','a#hot-option',function(){
        var _this = $(this);
        var parent = _this.parents(".csf-cloneable-content.ui-accordion-content");
        var body = io_hot_box(true);
        pupopTip(body);
        var box = $('.io-popup-box .popup-box');
        box.find('input[name="rule_id"]').val(parent.find("input[data-depend-id='rule_id']").val());
        box.find('input[name="user"]').val(_this.data('user'));
        box.find('input[name="parent_id"]').val(parent.attr('id'));
        box.find('#rule_list').data('parent_id',parent.attr('id'));
        getRuleList('',null);
    });
    $(document).on('click','a#hot-modify',function(){
        var _this = $(this);
        var parent = _this.parents(".csf-cloneable-content.ui-accordion-content");
        var _tip = '<div class="popup-header">获取内容失败，请重试！<i class="popup-close ml-auto fa fa-times"></i></div>';
        $.ajax({
            url: '//ionews.top/api/getruleinfo.php', 
            data: {
                rule_id:parent.find('input[data-depend-id="rule_id"]').val(),
                key:io_news.apikey
            },
            type: 'POST',
            dataType: 'json',
        })
        .done(function(response) {  
            if(response.state == 1){
                var body = io_hot_box(false);
                pupopTip(body);
                var box = $('.io-popup-box .popup-box');
                box.find('input[name="name').val(response.data.name);
                box.find('input[name="description"]').val(response.data.description);
                box.find('input[name="url"]').val(response.data.url);
                box.find('input[name="title"]').val(response.data.title);
                box.find('input[name="subtitle"]').val(response.data.subtitle);
                box.find('input[name="rule[title]"]').val(response.data.rule.title);
                box.find('input[name="rule[url]"]').val(response.data.rule.url);
                box.find('input[name="rule[hot]"]').val(response.data.rule.hot);

                //判断 response.data.is_merge==1 ，控制 checkbox input[name="is_merge"] 状态
                if (response.data.is_merge == 1) {
                    box.find('input[name="is_merge"]').prop('checked',true);
                } else {
                    box.find('input[name="is_merge"]').prop('checked',false);
                }

                
                box.find('input[name="rule[platform]"]').val(response.data.rule.platform);
                box.find('input[name="url_prefix"]').val(response.data.url_prefix);
                box.find('textarea[name="rule[cookie]"]').val(response.data.rule.cookie);
                box.find('input[name="refresh_time"]').val(response.data.refresh_time);

                if(response.data.user == _this.data('user')){
                    box.find('input[name="rule_id"]').val(parent.find("input[data-depend-id='rule_id']").val());
                } else {
                    box.find('.add-submit').before('<div class="tips-box">注：此规则为系统模板，无法修改，可以根据此模板修改为新的规则</div>');
                }
                box.find('input[name="rule_id"]').val(parent.find("input[data-depend-id='rule_id']").val());
                box.find('input[name="user"]').val(_this.data('user'));
                box.find('input[name="parent_id"]').val(parent.attr('id'));
                box.find('.box-switch').remove();
                return;
            }else{
                pupopTip(_tip);
            }
        })
        .fail(function() {  
            pupopTip(_tip);
        });
    });
    function pupopTip(pupText) {
        var popup = $('<div class="io-popup-box">\
            <div class="popup-box">\
            <div class="popup-content">' + pupText + '</div>\
            </div></div>');
        $("body").append(popup); 
        $('.io-popup-box').fadeIn();
        $('body').on('click','.popup-close',function() {
            $('.io-popup-box').fadeOut(500,function () {$(this).remove()})
        })
    }
    var keyWord;
    $(document).on('submit','#search-rule',function(){
        var _this = $(this); 
        var key_word = _this.find('#key_word').val();
        if(keyWord == key_word){
            return false;
        }else{
            keyWord = key_word;
            getRuleList(keyWord,_this.find('.submit'));
        }
        return false;
    });
    var old_text='';
    $(document).on('focus','.rule-label',function(){
        if($('#rule_verify').val()==1)
            old_text = $(this).val();
    });
    $(document).on('input propertychange','.rule-label',function(){
        if($('#rule_verify').val()==1){
            $('#rule_verify').val(0);
            $('.btn.add-submit').addClass('btn-verify').val('验证');
        }else if(old_text!='' && old_text==$(this).val()){
            $('#rule_verify').val(1);
            $('.btn.add-submit').removeClass('btn-verify').val('保存');
        }
    });
    $(document).on('blur','.rule-label',function(){
        old_text = '';
    });
    function getRuleList(keyWord,but){
        if(but) but.val("检索中...").attr("disabled",true);
        var _url = '//ionews.top/api/getrulelist.php'; 
        var _data = {key_word:keyWord};
        if(keyWord == ''){
            _url    = io_news.ajaxurl; 
            _data   = {action:'load_hot_list'};
        }
        $.ajax({
            url: _url, 
            data: _data,
            type: 'POST',
            dataType: 'json',
        })
        .done(function(response) {  
            //console.log(response);
            if(response.state == 1){  
                var li='';
                response.data.forEach(element => {
                    var rule_id     = element.id?element.id : element.rule_id;
                    var ico         = element.ico?element.ico:'';
                    var is_iframe   = element.is_iframe?1:0;
                    if(keyWord==""||element.source=="system")
                        var badge = '<span class="badge badge-success ml-auto">官方</span>';
                    else
                        var badge = '<span class="badge badge-secondary ml-auto">用户</span>';
                    li += '<li class="rule-box" data-rule_id="'+rule_id+'" data-ico="'+ico+'" data-is_iframe="'+is_iframe+'" data-type="'+element.type+'">\
                      <div class="rule-name-box">\
                        <span class="rule-name">'+element.name+'</span>\
                        ' + badge + '\
                      </div>\
                      <div class="rule-id">ID: <b>'+rule_id+'</b></div>\
                      <div class="rule-description text-sm">'+element.description+'</div>\
                    </li>';
                });
                $('#rule_list').html(li);
            }else{ 
                $('#rule_list').html(response.message);
            }
            if(but) but.val("搜索").removeAttr("disabled");
        })
        .fail(function() {  
            $('#rule_list').html('失败，请重试！');
            if(but) but.val("搜索").removeAttr("disabled"); 
        });
    }
    $(document).on('click','.rule-box',function(){
        var _this = $(this); 
        var parent = $('#'+$("#rule_list").data('parent_id'));
        
        parent.find('input[data-depend-id="name"]').val(_this.find('.rule-name').text());
        parent.find('input[data-depend-id="description"]').val(_this.find('.rule-description').text());
        parent.find('input[data-depend-id="rule_id"]').val(_this.data('rule_id'));
        parent.find('input[data-depend-id="type"]').val(_this.data('type'));
        if(_this.data('is_iframe'))
            parent.find('input[data-depend-id="is_iframe"]').val(_this.data('is_iframe'));
        if(_this.data('ico'))
            parent.find('input[data-depend-id="ico"]').val(_this.data('ico'));
        $('.io-popup-box').fadeOut(500,function () {$(this).remove()});
    });
    $(document).on('submit','#add-rule',function(){
        var _this = $(this); 
        //var rule_id =_this.find('input[name="rule_id"]').val();
        var verify =_this.find('input[name="verify"]').val();
        if(verify == 1){
            //获取 rule_id 并保存
            var parent = $('#'+_this.find("input[name='parent_id']").val());
            _this.find('.add-submit').val("保存中...").attr("disabled",true);
            $.ajax({
                url: '//ionews.top/api/addrule.php', 
                data: _this.serialize()+'&key='+io_news.apikey,
                type: 'POST',
                dataType: 'json',
            })
            .done(function(response) {  
                if(response.state == 1){ 
                    _this.find('.add-submit').val("保存").removeAttr("disabled"); 
                    _this.find('input[name="verify"]').val(1);
                    parent.find('input[data-depend-id="name"]').val(response.name);
                    parent.find('input[data-depend-id="description"]').val(response.description);
                    parent.find('input[data-depend-id="rule_id"]').val(response.rule_id);
                    parent.find('input[data-depend-id="type"]').val(response.type);
                    $('.io-popup-box').fadeOut(500,function () {$(this).remove()});
                }else{
                    _this.find('.add-submit').val("保存").removeAttr("disabled"); 
                    $('.response-state').val(JSON.stringify(response.data,null,2));
                }
            })
            .fail(function() {  
                _this.find('.add-submit').val("保存").removeAttr("disabled");
            });
            return false;
        }
        //执行验证
		_this.find('.add-submit').val("验证中...").attr("disabled",true);
		$.ajax({
			url: '//ionews.top/api/get.php', 
			data: _this.serialize()+'&key='+io_news.apikey,
			type: 'POST',
			dataType: 'json',
		})
		.done(function(response) {
			if(response.state == 1){ 
                _this.find('.add-submit').removeClass('btn-verify').val("保存").removeAttr("disabled"); 
                _this.find('input[name="verify"]').val(1);
			}else{
                _this.find('.add-submit').val("验证").removeAttr("disabled"); 
            }
            $('.response-state').val(JSON.stringify(response.data,null,2));
		})
		.fail(function() {  
			_this.find('.add-submit').val("验证").removeAttr("disabled");
		});
		return false;
    });

    $(document).on("click",".box-switch", function(){
        var target = $(this).data("target");
        var hide = $(this).data("hide");
        $(hide).toggle();
        $(target).toggle();
    });
    
})(jQuery);

function io_hot_box(is_new) { 
    var action  = is_new ? 'add' : 'modify';

    var header = '<div class="popup-header">\
      <h3 style="margin: 0;">自定义：</h3>\
      <a class="btn-help" href="https://www.iotheme.cn/io-api-user-manual.html" target="_blank">api 使用手册</a>\
      <i class="popup-close fa fa-times"></i>\
    </div>\
    <div class="tips-box">警告：请不要添加中华人民共和国法律所不允许的内容，发现将立即删除！</div>\
    <div class="popup-body">';
    var l = '<div class="popup-l" id="customize_rule">\
          <div class="popup-list-title">\
            自定义规则：\
            <button class="btn btn-help show-customize ml-auto d-block d-md-none box-switch" data-target="#preset_rule" data-hide="#customize_rule">查看预设库</button>\
          </div>\
          <form id="add-rule" name="add-rule" method="post">\
            <div class="form-input">\
              <div class="input-box">\
                <label>模块名称 *:</label>\
                <input class="rule-label" type="text" name="name" required="required" placeholder="百度热点榜">\
              </div>\
              <div class="input-box">\
                <label>模块介绍:</label>\
                <input class="rule-label" type="text" name="description" placeholder="https://top.baidu.com/buzz.php?p=top10 百度热点榜">\
              </div>\
              <div class="input-box">\
                <label>目标地址 *:</label>\
                <input class="rule-label" type="text" name="url" required="required" placeholder="https://top.baidu.com/buzz.php?p=top10">\
              </div>\
              <div class="input-group">\
                <div class="input-box">\
                  <label>显示名称 *:</label>\
                  <input class="rule-label" type="text" name="title" required="required" placeholder="百度">\
                </div>\
                <div class="input-box">\
                  <label>小标题:</label>\
                  <input class="rule-label" type="text" name="subtitle" placeholder="热点">\
                </div>\
              </div>\
              <div class="input-box">\
                <label>标题 xpath 规则 *:</label>\
                <input class="rule-label" type="text" name="rule[title]" required="required" placeholder="//a[@class=&quot;list-title&quot;]/text()">\
              </div>\
              <div class="input-box">\
                <label>url xpath 规则 *:</label>\
                <input class="rule-label" type="text" name="rule[url]" required="required" placeholder="//a[@class=&quot;list-title&quot;]/@href">\
              </div>\
              <div class="input-box">\
                <label>热度 xpath 规则:</label>\
                <input class="rule-label" type="text" name="rule[hot]" placeholder="//td[@class=&quot;last&quot;]/span/text()">\
              </div>\
              <div class="input-box">\
                <label>资讯平台 xpath 规则:</label>\
                <span class="text-sm" for="is_merge">资讯平台合入标题\
                <input name="is_merge" type="checkbox" value="1" id="is_merge" checked="">\
                </span>\
                <input class="rule-label" type="text" name="rule[platform]">\
              </div>\
              <div class="input-box">\
                <label>url补全:</label>\
                <input class="rule-label" type="text" name="url_prefix">\
              </div>\
              <div class="input-box">\
                <label>cookie:</label>\
                <textarea class="" rows="3" name="rule[cookie]"></textarea>\
              </div>\
              <div class="input-box">\
                <label>缓存时间(分钟) *:</label>\
                <input type="text" name="refresh_time" required="required" value="30" style="width: 90px;margin-bottom: 0;">\
                <span class="tips-box c-blue d-block" style="margin-top: 2px;">服务器数据缓存时间，缓存时间不低于10分钟。请根据目标站更新频率设置时间。比如吾爱破解排行榜更新频率为1天，请设置时间为 24x60=1440 分钟。</span>\
              </div>\
              <input type="hidden" name="rule_id" value="">\
              <input type="hidden" name="user" value="">\
              <input type="hidden" name="parent_id" value="">\
              <input type="hidden" name="action" value="'+ action +'">\
              <input type="hidden" id="rule_verify" name="verify" value="0">\
            </div>\
            <input type="submit" class="btn btn-verify add-submit" value="验证">\
          </form>\
          <div class="state-div">\
            <label>抓取&amp;反馈:</label>\
            <textarea class="response-state" readonly rows="6"></textarea>\
          </div>\
        </div>';
    var r = '<div class="popup-r" id="preset_rule">\
            <div class="popup-list-title">\
              预设规则库：\
              <button class="btn btn-help show-customize ml-auto d-block d-md-none box-switch" data-target="#customize_rule" data-hide="#preset_rule">自定义规则</button>\
            </div>\
            <form id="search-rule">\
              <input class="search-input" type="text" name="key_word" id="key_word" placeholder="ID、关键词、域名">\
              <input class="btn btn-search" type="submit" value="搜索">\
            </form>\
            <div class="tips-box c-blue">搜索 “ <b>官方</b> ” 可显示最新官方规则</div>\
            <div class="tips-box c-green"><i class="fa fa-fw fa-info-circle fa-fw"></i>ID列表：<a target="_blank" href="https://www.ionews.top/list.html">查看</a></div>\
          <div class="popup-lists">\
            <ul id="rule_list" data-parent_id="load">\
              <li class="rule-box">\
                Loading...\
              </li>\
            </ul>\
          </div>\
        </div>';
    var end = '</div>';
    if (is_new) {
        return header + '<div class="layout-body">' + l + r + '</div>' + end;
    } else {
        return header + l + end;
    }
}