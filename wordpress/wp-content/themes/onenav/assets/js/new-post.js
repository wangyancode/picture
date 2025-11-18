/*!
 * Theme Name:One Nav
 * Theme URI:https://www.iotheme.cn/
 * Author:iowen
 * Author URI:https://www.iowen.cn/
 */

var IO = IO || {};

IO.isContentChanged = false;

// 文章封面图按钮
$.fn.postCoverImg = function () {
    return this.each(function () {
        var $this = $(this),
            $input = $this.find('.cover-img'),
            $preview = $this.find('.show-preview'),
            $delete = $this.find('.cover-delete');
            
        var addIco = $preview.data('add_ico');
        
        var mediaType = $this.data('media-type'),
            maxFileSize = $this.data('size') * 1024 * 1024;

        var media = new IO.media({
            type: 'image',
            mediaType: mediaType,
            maxFileSize: maxFileSize,
            fileTypes: 'jpg,jpeg,png',
            uploadMultiple: 1,
            multiple: 1,
            isUpload: maxFileSize > 0 ? true : false,
        });

        var setImg = function (src) {
            $preview.attr('src', src);
            $input.val(src).trigger('input');
            $delete.show();
        }

        $this.on('click', function () {
            media.open();
        });

        $delete.on('click', function (e) {
            e.stopPropagation();

            $preview.attr('src', addIco);
            $input.val('').trigger('input');
            $delete.hide();
        });
        
        media.$el.on('lists_submit', function (e, data) {
            var lists = data.data;
            for (let item of lists) {
                setImg(item.url);
            }
			
        });

        media.$el.on('input_submit', function (e, data) {
            var lists = data.data;
            for (let item of lists) {
                setImg(item);
            }

        });
    });
};

// 项目组
$.fn.ioMetaItemGroup = function () {
    /**
     * 初始化子模态框数据
     * 
     * @param {jQuery} $modal 当前模态框
     * @param {jQuery} $currentItem 当前项目
     */
    function initChildData($modal, $currentItem) {
        var $metaGroup = $modal.find('.meta-item-group');
        if (!$metaGroup.length || !$metaGroup.attr('is-child')) return;
        
        var $metaBody = $metaGroup.find('.meta-item-body');

        var itemPrefab = $metaGroup.find('.meta-item-prefab').html();
        var titleBy = $metaGroup.data('title-by');

        var updateNames = function () {
            $metaBody.find('.meta-data-box').each(function (index, box) {
                $(box).find('input').each(function (_, input) {
                    var $input = $(input);
                    var name = input.name;
                    var dataID = $input.attr('data-id');
                    input.name = name.replace(/\[\d+\]/, '[' + index + ']');
                    dataID && $input.attr('data-id', dataID.replace(/\[\d+\]/, '[' + index + ']'));
                });
            });
        };

        // 清空所有子元素
        $metaGroup.each(function (_, el) {
            var $el = $(el);
            if ($el.attr('is-child')) {
                $el.find('.meta-item-body').empty();
            }
        });

        // 取值
        var $childInput = $currentItem.find('[data-child]');
        var childInputData = {}
        if ($childInput.length) {
            $childInput.each(function (_, el) {
                var $el = $(el);
                var name = $el.data('id');
                var d = name.split(']').map(item => item.replace('[', ''));
                if (d.length == 2) {
                    // 初始化子对象
                    if (!childInputData[d[0]]) {
                        childInputData[d[0]] = {};
                    }
                    // 赋值
                    childInputData[d[0]][d[1]] = el.value;
                } else {
                    // 其他情况...
                }
            });
        }

        // 赋值，生成子元素
        if (Object.keys(childInputData).length == 0) return;
        // 创建子元素
        $metaBody.append(itemPrefab.repeat(Object.keys(childInputData).length));
        // 赋值
        var $newItems = $metaBody.find('.meta-data-box');
        $newItems.each(function (index, item) {
            var $item = $(item);
            var data = childInputData[index];
            $item.find('input, select, textarea').each(function (_, el) {
                var $el = $(el);
                var dataName = $el.data('name');
                var name = $el.data('id');
                name = name.replace(/\[\d+\]/, '');
                $el.val(data[name]);
                setTimeout(function () {
                    $el.attr('name', dataName).removeAttr('data-name');
                }, 0);
            });
            if (titleBy) {
                var title = titleBy.map(function (name) {
                    return data[name];
                }).join(' ');
                $item.find('.show-item-title').text(title);
            }
        });
        setTimeout(function () {
            updateNames();
        }, 10);
    }

    /**
     * 创建子模态框元素生成的数据
     * 
     * 只处理两级
     * @param {jQuery} $currentItem 当前对象 
     * @param {Object} data
     * @param {jQuery} $modal
     */
    function createChildInput($currentItem, data, $modal) {
        var $metaGroup = $modal.find('.meta-item-group');
        if (!$metaGroup.length || !$metaGroup.attr('is-child')) return;

        var parentID = $currentItem.closest('.meta-data-box').attr('data-index');
        var parentName = $modal.data('name');
        var modalTarget = $metaGroup.data('modal-target');
 
        // 创建子元素
        var html = '';
        for (let key in data) {
            var names = key.split('[').map(item => item.replace(']', ''));
            html += `<input type="hidden" name="${parentName}[${parentID}][${names[0]}][${names[1]}][${names[2]}]" data-child="${modalTarget}" data-id="[${names[1]}]${names[2]}" value="${data[key]}">`;
        }
        $currentItem.find('.hide-input').append(html);
    }

    /**
     * 创建多选输入框生成的数据
     * 
     * @param {jQuery} $currentItem - 插入输入框的目标元素
     * @param {Object} data - 包含字段名及对应值数组的对象
     * @param {jQuery} $modal - 用于获取 parentName 的模态框元素
     */
    function createMultiInput($currentItem, data, $modal) {
        var parentID = $currentItem.closest('.meta-data-box').attr('data-index');
        var parentName = $modal.data('name');

        $.each(data, function (name, values) {
            var baseName = name.replace('[]', '');
            values.forEach(function (value) {
                var $input = $('<input>', {
                    type: "hidden",
                    name: `${parentName}[${parentID}][${baseName}][]`,
                    "data-id": name,
                    "is-multi": "true",
                    value: value
                });
                $currentItem.append($input);
            });
        });
    }

    return this.each(function () {
        var $this = $(this);
        var $body = $this.find('.meta-item-body');
        var $modal = $($this.data('modal-target'));
        var $currentItem = null;

        var itemPrefab = $this.find('.meta-item-prefab').html();
        var titleBy = $this.data('title-by');
        var isChild = $this.attr('is-child');
        var autoIndex = $this.data('auto-index') || false;
        
        // 自动序号，先取最大值
        var getMaxIndex = function () {
            if (!autoIndex) {
                return 0;
            }
            var maxIndex = -Infinity;
            $body.find('[data-id="' + autoIndex + '"]').each(function (_, el) {
                var index = $(el).val();
                maxIndex = Math.max(maxIndex, index);
            });
            return maxIndex === -Infinity ? 0 : maxIndex;
        };
        var maxIndex = getMaxIndex();

        // 更新 name 和 data-id 的序号
        var updateNamesIndex = function () {
            $body.find('.meta-data-box').each(function (index, box) {
                var $box = $(box);
                $box.attr('data-index', index);
                $box.find('input').each(function (_, input) {
                    var $input = $(input);
                    var name = input.name;
                    var dataID = $input.attr('data-id');
                    input.name = name.replace(/\[\d+\]/, '[' + index + ']');
                    if (isChild && dataID) {
                        $input.attr('data-id', dataID.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
            });
            if (!$body.attr('init')) {
                $body.attr('init', 'true');
            } else {
                IO.isContentChanged = true;
            }
        };

        // 添加项目
        $this.on('click', '.meta-item-add', function () {
            var $newItem = $(itemPrefab);
            $newItem.find('input, select, textarea').each(function (_, el) {
                var dataName = $(el).data('name');
                if (dataName) {
                    $(el).attr('name', dataName).removeAttr('data-name');
                }
            });

            if (autoIndex) {
                // 设置自增项目
                maxIndex++;
                $newItem.find('[data-id="' + autoIndex + '"]').val(maxIndex);
            }

            $body.append($newItem);

            $newItem.find('[modal-set]').trigger('click');
            updateNamesIndex();
        });

        // 删除项目
        $this.on('click', '.meta-item-delete', function () {
            $(this).closest('.meta-data-box').remove();
            updateNamesIndex();
        });

        $this.sortable({
            items: '.meta-data-box',
            handle: '.meta-item-sort',
            axis: 'y',
            containment: 'parent',
            cursor: 'move',
            revert: 100,
            tolerance: 'pointer',
            update: updateNamesIndex
        });

        // 模态框触发显示
        $this.on('click', '[modal-set]', function () {
            $currentItem = $(this);
            $modal.modal('show');
            if (!isChild) {
                initChildData($modal, $currentItem);
            }

            // 取值
            var data = {};
            var multiData = {};
            $currentItem.find('input, textarea').each(function (_, el) {
                var dataName = $(el).data('id');
                var value = el.value;
                if (isChild) {
                    dataName = dataName.replace(/\[\d+\]/, '');
                }
                if (dataName) {
                    if (dataName.includes('[]')) {
                        // 多选
                        if (!multiData[dataName]) {
                            multiData[dataName] = [];
                        }
                        if (value) {
                            multiData[dataName].push(value);
                        }
                    } else {
                        data[dataName] = value;
                    }
                }
            });

            // 设置模态框中的input对应name的值
            $modal.find('input, select, textarea').each(function (_, el) {
                var $el = $(el);
                if ($el.attr('data-name')) return;
                var name = $el.attr('name');
                if ($el.attr('type') == 'checkbox') {
                    el.checked = data[name] == '1';
                    $el.trigger('change');
                } else if ($el.attr('type') == 'radio') {
                    if (data[name] == $el.val()) {
                        el.checked = true;
                        $el.trigger('change');
                    }
                } else {
                    el.value = data[name];
                    $el.trigger('change');
                }
            });

            // 设置多选
            multiData && $.each(multiData, function(name, values) {
                values.forEach(function(value) {
                    $('input[type="checkbox"][name="' + name + '"][value="' + value + '"]').prop('checked', true).trigger('change');
                });
            });
        });
        // 模态框提交按钮
        $modal.find('.modal-submit').on('click', function () {
            var data = {};
            var multiData = {};
            var childData = {};
            var isValid = true;

            // 取值
            $modal.find('input, select, textarea').each(function (_, el) {
                var $el = $(el);
                var name = $el.attr('name');
                if (!name) return;
                
                var value = $el.val();
                
                // 必填项验证
                if (value === '' && $el.attr('required')) {
                    var msg = $el.closest('.form-group').find('.option-name').text() + IO.contributeVar.local.no_empty;
                    showAlert({ "status": 3, "msg": msg });
                    isValid = false;  // 设置验证失败
                    return false;  // 停止 .each 循环
                }
        
                // 根据类型转换值
                if ($el.attr('type') === 'checkbox') {
                    if($el.hasClass('switch')){
                        value = $el.is(':checked') ? 1 : 0;
                    } else {
                        value = $el.is(':checked') ? $el.val() : '';
                    }
                } else if ($el.attr('type') === 'number') {
                    value = Number(value);  // 转为数字
                } else if ($el.attr('type') === 'radio') {
                    if ($el.is(':checked')) {
                        value = $el.val();  // 获取选中单选按钮的值
                    } else {
                        return;  // 未选中则跳过此项
                    }
                }
        
                // 存储处理后的值
                // 判断 name 是否包含 []
                if (name.includes('[]')) {
                    // 多选
                    if (!multiData[name]) {
                        multiData[name] = [];
                    }
                    if (value) {
                        multiData[name].push(value);
                    }
                } else {
                    data[name] = value;
                }
                $el.attr('data-child') && (childData[name] = value);
            });

            if (!isValid) return;

            // 删除$currentItem下的 [data-child]
            !isChild && $currentItem.find('[data-child]').remove();
            // 删除 [is-multi]
            multiData && $currentItem.find('[is-multi]').remove();
            // 赋值
            $currentItem.find('input, select, textarea').each(function (_, el) {
                var name = $(el).data('id');
                if (isChild) {
                    name = name.replace(/\[\d+\]/, '');
                }
                el.value = data[name];
            });

            // 创建 [is-multi]
            multiData && createMultiInput($currentItem, multiData, $modal); 

            // 创建 [data-child]
            !isChild && createChildInput($currentItem, childData, $modal);

            if (titleBy) {
                var title = titleBy.map(function (name) {
                    return data[name];
                }).join(' ');
                $currentItem.find('.show-item-title').text(title);
            }
            $modal.modal('hide');
        });
        // 模态框删除按钮
        $modal.find('.modal-delete').on('click', function () {
            $currentItem.closest('.meta-data-box').remove();
            $modal.modal('hide');
            updateNamesIndex();
        });

        updateNamesIndex();
    });
};
/**
 * 缩略图   
 * 
 * 增删图片，排序
 * @returns 
 */
$.fn.ioScreenshot = function () {
    return this.each(function () {
        var $this = $(this);
        var $body = $this.find('.screenshot-body .row-a');
        var $add = $this.find('.screenshot-add');
        var $none = $this.find('.screenshot-none');

        var mediaType = $this.data('media-type'),
            maxFileSize = $this.data('size') * 1024 * 1024,
            inputName = $body.data('name');

        // 注册 IO.media
        var media = new IO.media({
            type: 'image',
            mediaType: mediaType,
            maxFileSize: maxFileSize,
            fileTypes: 'jpg,jpeg,png',
            uploadMultiple: 6,
            maxFileCount: 10,
            isUpload: maxFileSize > 0 ? true : false,
        });

        // 更新$none状态
        var updateNone = function () {
            if ($body.find('.screenshot-item').length == 0) {
                $none.show();
            } else {
                $none.hide();
            }
        };
        // 更新 input 的 name 序号（screenshot[1][img]）
        var updateIndex = function () {
            $body.find('input').each(function (index, el) {
                var $el = $(el);
                var name = $el.attr('name');
                name = name.replace(/\[\d+\]/, '[' + index + ']');
                $el.attr('name', name);
            });
            IO.isContentChanged = true;
            updateNone();
        };

        // 打开媒体选择框
        $add.on('click', function () {
            media.open();
        });

        // 媒体选择框提交
        media.$el.on('lists_submit', function (e, data) {
            var lists = data.data;
            if (lists.length > 0) {
                // 构建 screenshot-item 元素
                var html = lists.map(function (item) {
                    return `<div class="screenshot-item">
                    <div class="screenshot-item-img"><img src="${item.url}"></div>
                    <input type="hidden" name="${inputName}" value="${item.url}">
                    <div class="screenshot-item-delete"><i class="iconfont icon-close"></i></div>
                </div>`;
                }).join('');
                $body.append(html);
            
                updateIndex();
            }
        });

        // 删除
        $body.on('click', '.screenshot-item-delete', function () {
            $(this).closest('.screenshot-item').remove();
            updateIndex();
        });

        // 排序
        $body.sortable({
            items: '.screenshot-item',
            handle: '.screenshot-item-img',
            containment: 'parent',
            placeholder: 'screenshot-item-placeholder', 
            revert: 100,
            cursor: 'move',
            tolerance: 'pointer',
            update: updateIndex
        });

        updateNone();
    });
};

(function ($) { 
    $(document).ready(function () {
        var checkIOMedia = setInterval(function () {
            if (typeof IO !== 'undefined' && IO.media) {
                $('.posts-cover-img').postCoverImg();
                $('.screenshot-set-box').ioScreenshot();
                clearInterval(checkIOMedia);
            }
        }, 100);
        setTimeout(function () {
            clearInterval(checkIOMedia);
        }, 10000);
        $('.meta-item-group').ioMetaItemGroup();
    });
    $('input, textarea, select').on('input', function() {
        IO.isContentChanged = true;
    });
    $(window).on('beforeunload', function (event) {
        if (IO.isContentChanged) {
            event.preventDefault();
            return ''; // 返回空字符串以触发默认的离开确认提示
        }
    });
    
    if (typeof tinymce !== 'undefined') {
        const editor = tinymce.get('post_content'); // 使用你的编辑器ID替换 'your_editor_id'

        if (editor) {
            editor.on('input change', function() {
                IO.isContentChanged = true;
            });
        } else {
            // 如果编辑器未初始化，延时检查直到编辑器可用
            tinymce.on('addeditor', function(e) {
                if (e.editor.id === 'post_content') {
                    e.editor.on('input change', function() {
                        IO.isContentChanged = true;
                    });
                }
            });
        }
    }
    $('#get_info').click(function() {
        var url = $('.sites-link').val();
        if( url != '' ){
            if(isURL(url)){
                getUrlData(url);
            }else{
                showAlert({"status":3,"msg":IO.contributeVar.local.url_error});
            }
        }else{
            showAlert({"status":3,"msg":IO.contributeVar.local.fill_url});
        }
    });
    $('.new-posts-submit').click(function() {
        var $this = $(this); 

        tinyMCE.triggerSave();
        
        captcha_ajax($this, '', function (result) {
            IO.isContentChanged = false;
            if(result.status == 1){
                $('.form-control').not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected');
                //清理图标
                $(".show-sites").attr("src", IO.addico);
                $(".tougao-sites").val('');
                $(".upload-sites").val("").parent().removeClass('disabled');
                $('[name="image_captcha"]').val('');
                $('.image-captcha').click();
            }
            if (result.action) {
                var $target = $(result.action.target);
                $target.attr('href', result.action.url).trigger(result.action.event);
                if (result.action.edit_url) {
                    var modalID = $target.data('modal_id');
                    $(document).on('hidden.bs.modal', '#refresh_modal_' + modalID, function (e) {
                        $this = $(this);
                        if ($this.attr('is-user')) {
                            window.location.href = result.action.edit_url;
                            window.location.reload;
                        } else {
                            console.log('s');
                        }
                        $this.removeAttr('is-user');
                    });
                }
            }
        });
        return false;
    }); 
    $('.remove-ico').click(function() {
        var doc_id = $(this).data('type');
        $("#show_"+doc_id).attr("src", IO.addico);
        $("#remove_"+doc_id).hide();
        $("#upload_"+doc_id).val("");
    });

    $('.count-tips .form-control').off('compositionstart compositionend input').on({
        compositionstart: function () {
            $(this).attr('data-status', false);
        },
        compositionend: function () {
            $(this).attr('data-status', true);
            changeInput($(this));
        },
        input: function () {
            if ($(this).attr('data-status') === 'true') {
                IO.isContentChanged = true;
                changeInput($(this));
            }
        },
    });
    $('.count-tips .form-control').length && changeInput($('.count-tips .form-control'));

})(jQuery);


function getUrlData(_url) {
    $.post("//apiv2.iotheme.cn/webinfo/get.php", { url: _url, type: "json", key: IO.contributeVar.theme_key }, function (data, status) {
        if (data.code == 0) {
            showAlert({ "status": 3, "msg": IO.contributeVar.local.get_failed });
        }
        else {
            dataInput(data);
            showAlert({ "status": 1, "msg": IO.contributeVar.local.get_success });
        }
    }).fail(function () {
        showAlert({ "status": 3, "msg": IO.contributeVar.local.timeout2 });
    });
}
function dataInput(data) {
    var $desc = $('.sites-desc');
    $('.sites-title').val(data.site_title);
    $desc.val(data.site_description.slice(0, $desc.attr('maxlength')));
    changeInput($desc);
    $('.sites-keywords').val(data.site_keywords);
}
