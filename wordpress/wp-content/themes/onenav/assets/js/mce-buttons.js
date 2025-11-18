/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2019-09-23 19:45:05
 * @LastEditors: iowen
 * @LastEditTime: 2025-03-26 01:49:13
 * @FilePath: /onenav/assets/js/mce-buttons.js
 * @Description: https://www.tiny.cloud/docs-4x/integrations/angular2/#eventbinding
 * https://www.tiny.cloud/docs-4x/api/tinymce.ui/tinymce.ui.listbox/#settings
 */
(function ($, window) {
	
	var IO = window.IO || {}; 

	var IOL = IO.contributeVar.local;
	
	tinymce.create('tinymce.plugins.MyButtons', {
		init : function(ed, url) {
			ed.addButton( 'io_h2', {
				title : 'H2',
				icon: 'io-h2',
				onclick : function() {
					ed.formatter.toggle('h2')
					ed.nodeChanged() 
				},
				onPostRender: function () {
					$('.mce-i-io-h2').replaceWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" style="width: 21px;height: 21px;fill: currentColor;"><path d="M143.616 219.648v228.864h278.016V219.648h89.856V768H421.632v-242.688H143.616V768H53.76V219.648h89.856z m660.48-10.752c52.992 0 96.768 15.36 131.328 46.08 33.792 30.72 50.688 69.888 50.688 119.04 0 47.616-18.432 90.624-53.76 129.792-16.554667 17.706667-43.093333 39.082667-78.933333 64.426667l-22.613334 15.701333-12.117333 8.192c-52.309333 34.389333-85.248 64.810667-99.413333 91.178667l-2.730667 5.589333h270.336V768h-382.464c0-56.064 17.664-104.448 54.528-145.92 8.746667-10.069333 21.76-22.186667 38.912-36.352l15.786667-12.586667c5.589333-4.352 11.52-8.874667 17.834666-13.568l19.84-14.506666 21.888-15.488 11.690667-8.106667c35.328-24.576 59.904-45.312 75.264-61.44 23.808-26.88 36.096-56.064 36.096-86.784 0-29.952-8.448-52.224-23.808-66.816-16.128-14.592-39.936-21.504-71.424-21.504-33.792 0-59.136 11.52-76.032 34.56-15.36 19.541333-24.362667 48.64-27.050667 86.058667l-0.597333 11.477333h-89.856c0.768-61.44 18.432-110.592 53.76-148.224 36.096-39.936 83.712-59.904 142.848-59.904z"></path></svg>');
				}
			});

			ed.addButton( 'io_h3', {
				title : 'H3',
				icon: 'io-h3',
				onclick : function() {
					ed.formatter.toggle('h3')
					ed.nodeChanged() 
				},
				onPostRender: function () {
					$('.mce-i-io-h3').replaceWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" style="width: 21px;height: 21px;fill: currentColor;"><path d="M801.024 208.896c55.296 0 100.608 13.056 134.4 39.936 33.024 26.88 49.92 63.744 49.92 111.36 0 59.904-30.72 99.84-91.392 119.808 32.256 9.984 57.6 24.576 74.496 44.544 18.432 20.736 27.648 47.616 27.648 79.872 0 50.688-17.664 92.16-52.992 124.416-36.864 33.024-85.248 49.92-145.152 49.92-56.832 0-102.912-14.592-137.472-43.776-38.4-32.256-59.904-79.872-64.512-141.312h91.392c1.536 35.328 12.288 62.976 33.792 82.176 19.2 17.664 44.544 26.88 76.032 26.88 34.56 0 62.208-9.984 82.176-29.184 17.664-17.664 26.88-39.168 26.88-65.28 0-31.488-9.984-54.528-28.416-69.12-18.432-15.36-45.312-22.272-80.64-22.272h-38.4V449.28h38.4c32.256 0 56.832-6.912 73.728-20.736 16.128-13.824 24.576-34.56 24.576-61.44 0-26.88-7.68-46.848-22.272-60.672-16.128-13.824-39.936-20.736-71.424-20.736-32.256 0-56.832 7.68-74.496 23.808-18.432 16.128-29.184 40.704-32.256 73.728h-88.32c4.608-55.296 24.576-98.304 61.44-129.024 34.56-30.72 79.104-45.312 132.864-45.312z m-657.408 10.752v228.864h278.016V219.648h89.856V768H421.632v-242.688H143.616V768H53.76V219.648h89.856z"></path></svg>');
				}
			});

			ed.addButton( 'io_ad', {
				title : '插入广告',
				icon: 'io-ad',
				onclick : function() {
					ed.selection.setContent('[ad]' + ed.selection.getContent() + '');
				},
				onPostRender: function () {
					$('.mce-i-io-ad').replaceWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" style="width: 20px;height: 20px;fill: currentColor;"><path d="M484.62 111.52v776.96H342.74V500H198.4v388.48H79V380.3c0-24.92 4.6-52.95 13.81-84.11 9.2-31.15 24.2-60.36 45-87.66 20.78-27.28 47.96-50.29 81.53-68.97 33.55-18.69 74.69-28.04 123.4-28.04h141.88zM198.4 449.21h144.34V150.72c-21.99 0-41.88 5.93-59.7 17.77s-32.96 28.14-45.44 48.86-22.14 45.01-28.95 72.84c-6.83 27.84-10.25 57.46-10.25 88.84v70.18zM674.19 111.52c40.39 0 76.03 3.42 106.92 10.25 30.88 6.83 56.72 16.03 77.52 27.62 20.78 11.58 36.38 25.25 46.78 40.99 10.38 15.75 15.59 32.23 15.59 49.45v524.8c0 14.26-3.72 28.67-11.14 43.21-7.43 14.56-20.5 27.93-39.2 40.1-18.71 12.18-43.96 21.99-75.73 29.41-31.78 7.43-72.03 11.14-120.73 11.14H539.41V111.52h134.78zm123.85 129.19c0-18.41-3.55-33.7-10.62-45.88-7.08-12.17-16.36-21.83-27.86-28.95-11.5-7.12-24.78-12.17-39.82-15.15a235.338 235.338 0 0 0-45.56-4.45v706.57c21.24 0 39.66-2.82 55.29-8.47 15.62-5.63 28.6-13.05 38.92-22.27 10.31-9.2 17.83-19.29 22.55-30.29 4.72-10.99 7.08-21.83 7.08-32.52l.02-518.59z"/></svg>');
				}
			});

			ed.addButton('io_img', {
                title: IOL.image,
                icon: 'image',
				onclick: function () {
					ed.ioImgUpload.open();
				},
				onPostRender: function () {
					var media = new IO.media({
						type: 'image',
						maxFileSize: IO.contributeVar.post_img_max * 1024 * 1024,
						fileTypes: 'jpg,jpeg,png', // 允许的文件类型
						uploadMultiple: 6,
						isUpload: IO.contributeVar.post_img_max > 0 ? true : false,
					});
					ed.ioImgUpload = media;

					media.$el.on('lists_submit', function (e, data) {
						var lists = data.data;
						var html = '';
						for (let item of lists) {
							var full = item.url;
							var src = item.large_url;
							html += GetInsertContent(src, full, item.id, item.title);
						}
			
						ed.insertContent(html + '<p></p>');
					});

					media.$el.on('input_submit', function (e, data) {
						var lists = data.data;
						var html = '';
						for (let item of lists) {
							html += GetInsertContent(item);
						}
						ed.insertContent(html + '<p></p>');
					});

					function GetInsertContent(src, full, id, alt) {
						var id_attr = id ? ' data-edit-file-id="' + id + '"' : '';
						var alt_attr = alt ? ' alt="' + alt + '"' : '';
						var full_attr = full ? ' data-full-url="' + full + '"' : '';
			
						return '<p><img src="' + src + '"' + id_attr + alt_attr + full_attr + '></p>';
					}
				}
			});
			
			ed.addButton('io_hide', {
				title: '隐藏内容',
				icon: 'io-hide',
				onclick: function () {
					var e = ed.dom.getViewPort(),
						w = Math.min((Math.min(e.w - 20, window.innerWidth - 20) || 660), 660),
						h = Math.min((Math.min(e.h - 360, window.innerHeight - 360) || 360), 360);

					var selectedNode = ed.selection.getNode(); //选中的元素
					// 如果选择了p标签内部分内容，那么选中整个p标签
					if ($.trim(ed.selection.getContent()) && selectedNode.nodeName === 'P') {
						ed.selection.select(selectedNode);
					}
					var isMedia = false;
					var imageStyle = 'width:100%;box-sizing:border-box';
					//判断 wp.media 
					if (typeof wp !== 'undefined' && typeof wp.media !== 'undefined') {
						isMedia = true;
						imageStyle = 'width:calc(100% - 90px);box-sizing:border-box;height:100%';
					}

					var title = '添加隐藏内容';
					var typeValue = 'reply';
					var passwordValue = '';
					var tipsValue = '';
					var imageValue = '';
					var contentValue = '';
					var isEdit = false;
					if (selectedNode.className === 'io-edit-hide-content') {
						// 简单解析短代码内容
						var shortcodeContent = selectedNode.innerHTML;
						var match = shortcodeContent.match(/\[hide_content type="(\w+)"(?: password="([^"]+)" tips="([^"]+)?" image="([^"]+)?")?\]/);
						if (match) {
							title = '编辑隐藏内容';
							typeValue = match[1];// 短代码中的类型
							passwordValue = match[2];// 短代码中的密码
							tipsValue = match[3];// 短代码中的提示
							imageValue = match[4];// 短代码中的图片
							//contentValue = HtmlToText(selectedNode.querySelector('div').innerHTML);// 短代码中的内容
							// 获取[contenteditable="true"]的内容
							contentValue = selectedNode.querySelector('[contenteditable="true"]').innerHTML;
							isEdit = true;
						}
					} else {
						contentValue = ed.selection.getContent() || '';
					}
					var win = ed.windowManager.open({
						title: title,
						minWidth: w,
						body: [
							{
								type: "listbox",
								name: "type",
								label: "类型",
								values: [
									{ text: '评论可见', value: 'reply' },
									{ text: '登录可见', value: 'logged' },
									{ text: '密码验证', value: 'password' },
									{ text: '付费阅读', value: 'buy' }
								],
								value: typeValue,
								onselect: function (e) {
									toggleDisplay(this.value());
								}
							},
							{
								type: "textbox",
								name: "password",
								label: "请输密码",
								multiline: !1,
								//hidden: true,
								tooltip: "显示内容的密码。",
								value: passwordValue,
							},
							{
								type: "textbox",
								name: "tips",
								label: "请输提示",
								multiline: !1,
								tooltip: "提示语，比如发送 ABCD 到公众号获取密码。",
								placeholder: '通过提示和图片，可实现关注公众号获取密码等功能。',
								value: tipsValue,
							},
							{
								type: 'container',  // 创建一个容器用于分组
								label: '添加图片',
								name: 'image-box',
								items: [
									{
										type: "textbox",
										name: "image",
										label: "图片URL",
										//readonly: true, // 图片选择后显示URL，不允许手动编辑
										style: imageStyle,
										tooltip: "添加微信二维码图片，用于发送提示获取密码。",
										value: imageValue,
									},
									{
										type: "button",
										label: "选择图片",
										text: "选择图片",
										style: 'width: 80px;text-align: center;margin-left: 10px;',
										hidden: !isMedia,
										onclick: function (e) {
											var mediaUploader;
	
											if (mediaUploader) {
												mediaUploader.open();
												return;
											}

											mediaUploader = wp.media({
												title: '选择或上传图片',
												button: {
													text: '使用此图片'
												},
												multiple: false
											});
	
											// 当图片被选择时，回调处理图片URL
											mediaUploader.on('select', function () {
												var attachment = mediaUploader.state().get('selection').first().toJSON();
												win.find('#image').value(attachment.url);
											});
	
											mediaUploader.open();
										}
									},
								],
							},
							{
								type: 'container',
								html: '<textarea id="custom-rich-editor" style="width: 100%; height: ' + h + 'px;"></textarea><div style=" margin-top:5px">隐藏内容，可留空，添加后到正文中再写入。</div>',  // 添加 textarea 用于富文本编辑器
								value: contentValue,
							},
							//{
							//	type: "textbox",
							//	name: "content",
							//	multiline: !0,
							//	placeholder: "隐藏内容，可留空，添加后到正文中再写入。",
							//	minHeight: h - 115,
							//	value: contentValue,
							//},
						],
						onSubmit: function (api) {
							if (api.data.type !== "") {
								var type = api.data.type;
								var password = api.data.password;
								var tips = api.data.tips;
								var image = api.data.image;
								var content = tinymce.get('custom-rich-editor').getContent();//api.data.content;

								if (content == '') {
									content = '<p>请输入需要隐藏的内容</p>';
								} else {
									//content = TextToHtml(content);
								}
								if (type == 'password' && password === '') {
									alert('密码不能为空');
									return false;
								} else {
									var _html = '<p class="hide-before">[hide_content type="' + type + '"]</p><div contenteditable="true">' + content + '</div><p class="hide-after">[/hide_content]</p>';
									if (type == 'password') {
										_html = '<p class="hide-before">[hide_content type="password" password="' + password + '" tips="' + tips + '" image="' + image + '"]</p><div contenteditable="true">' + content + '</div><p class="hide-after">[/hide_content]</p>';
									}
									content = '<div class="io-edit-hide-content" contenteditable="false">' + _html + '</div>';
								}

								if (!isEdit) {
									content += '<p>&nbsp;</p>';
									ed.insertContent(content);
								} else {
									ed.dom.setOuterHTML(selectedNode, content);
									// 隐藏 Toolbar
									//ed.nodeChanged(); // 刷新编辑器的状态
									ed.selection.collapse(); //取消选中状态
								}
							}
						},
						onOpen: function () {
							var type = win.find('#type')[0].value(); // 获取类型值
							toggleDisplay(type);
 

							var style = 'body{background:#fff;color:#333}';
							if (typeof IO !== 'undefined' && IO.isDarkMode) {
								style = 'body{background:#25282a;color:#fff}';
							}
							
							// 在对话框打开时初始化 TinyMCE 编辑器
							tinymce.init({
								selector: '#custom-rich-editor',  // 选择 textarea 的 ID
								menubar: false,
								toolbar: ' ',
								branding: false,
								resize: false,
								height: h - 50,
								content_style: style,
								content_css: IO.mceCss,
								setup: function (editor) {
									editor.on('init', function () {
										// 使用动态内容初始化编辑器
										editor.setContent(contentValue);

										var editorContainer = editor.getContainer();
										editorContainer.classList.add('io-custom-editor');
									});
								}
							});
						},
						onClose: function () {
							// 销毁 TinyMCE 实例
							tinymce.remove('#custom-rich-editor');
						}
					});
					
					function toggleDisplay(type) {
						// 根据类型值显示或隐藏密码和提示字段
						var passwordField = win.find('#password').parent();
						var tipsField = win.find('#tips').parent();
						var imgField = win.find('#image-box').parent();
									
						if (type === 'password') {
							passwordField.show();
							tipsField.show();
							imgField.show();
						} else {
							passwordField.hide();
							tipsField.hide();
							imgField.hide();
						}
					}
				},
				onPostRender: function () {
					$('.mce-i-io-hide').replaceWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" style="width:20px;height:20px" fill="currentColor"><path d="M512 861.867c-217.6 0-405.333-136.534-469.333-337.067-4.267-8.533-4.267-17.067 0-25.6 25.6-72.533 68.266-140.8 119.466-192 17.067-17.067 42.667-17.067 59.734 0 17.066 17.067 17.066 42.667 0 59.733C179.2 409.6 149.333 456.533 128 512c55.467 157.867 204.8 264.533 379.733 264.533 42.667 0 81.067-4.266 119.467-17.066 21.333-8.534 46.933 4.266 51.2 29.866 8.533 21.334-4.267 46.934-29.867 51.2C610.133 857.6 563.2 861.867 512 861.867zM832 729.6c-12.8 0-21.333-4.267-29.867-12.8-17.066-17.067-17.066-42.667 0-59.733C844.8 614.4 874.667 567.467 896 512c-55.467-157.867-204.8-264.533-379.733-264.533-42.667 0-81.067 4.266-119.467 17.066-25.6 8.534-51.2-4.266-55.467-29.866-8.533-21.334 4.267-46.934 25.6-51.2 46.934-12.8 93.867-21.334 145.067-21.334 217.6 0 405.333 136.534 469.333 337.067 4.267 8.533 4.267 17.067 0 25.6-21.333 72.533-64 140.8-119.466 192-8.534 8.533-21.334 12.8-29.867 12.8z"/><path d="M840.533 789.333L665.6 610.133c21.333-29.866 34.133-68.266 34.133-106.666 0-98.134-76.8-174.934-174.933-174.934-38.4 0-72.533 12.8-102.4 29.867L247.467 174.933c-17.067-17.066-42.667-17.066-59.734 0-17.066 17.067-17.066 42.667 0 59.734l593.067 614.4c8.533 8.533 21.333 12.8 29.867 12.8 12.8 0 21.333-4.267 29.866-12.8 17.067-17.067 17.067-42.667 0-59.734zM524.8 409.6c51.2 0 89.6 38.4 89.6 89.6 0 17.067-4.267 29.867-8.533 42.667L486.4 418.133c8.533-4.266 21.333-8.533 38.4-8.533z"/></svg>');
				}
			});
			
			ed.addButton('io_post_card', {
				title: '内容卡片',
				icon: 'io_card',
				onPostRender: function () {
					$('.mce-i-io_card').replaceWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" style="width: 20px;height: 20px;fill: currentColor;"><path d="M888 232H136v560h752V232zm72 600c0 17.673-14.327 32-32 32H96c-17.673 0-32-14.327-32-32V192c0-17.673 14.327-32 32-32h832c17.673 0 32 14.327 32 32v640zM760 640a8 8 0 0 0 8-8v-48a8 8 0 0 0-8-8H457a8 8 0 0 0-8 8v48a8 8 0 0 0 8 8h303zm7-200a8 8 0 0 1-8 8H456a8 8 0 0 1-8-8v-48a8 8 0 0 1 8-8h303a8 8 0 0 1 8 8v48zM312 704a8 8 0 0 0 8-8V328a8 8 0 0 0-8-8h-48a8 8 0 0 0-8 8v368a8 8 0 0 0 8 8h48z"/></svg>');
				},
				onclick: function () {
					var e = ed.dom.getViewPort(),
						w = Math.min((Math.min(e.w - 20, window.innerWidth - 20) || 460), 460);
					
					var selectedNode = ed.selection.getNode(); //选中的元素
					//var selectedContent = ed.selection.getContent(); //选中的文本

					var title = '插入内容卡片';
					var typeValue = 'post';
					var idsValue = '';
					var isEdit = false;
					if (selectedNode.className === 'io-edit-post-card-content') {
						// 简单解析短代码内容
						var shortcodeContent = selectedNode.innerHTML;
						var match = shortcodeContent.match(/\[(\w+)_card ids="([^"]+)"\]/);
						if (match) {
							title = '编辑内容卡片';
							typeValue = match[1];// 短代码中的类型
							idsValue = match[2];// 短代码中的ID
							isEdit = true;
						}
					}

					ed.windowManager.open({
						title: title,
						minWidth: w,
						body: [
							{
								type: "listbox",
								name: "type",
								label: "类型",
								values: [
									{ text: '文章', value: 'post' },
									{ text: '网址', value: 'site' },
									{ text: 'app', value: 'app' },
									{ text: '书籍', value: 'book' }
								],
								value: typeValue,
							}, {
								type: "textbox",
								name: "ids",
								label: "内容ID",
								multiline: !1,
								placeholder: "如：12,2,234",
								tooltip: '可填一个或者多个ID，用英语逗号分割！',
								value: idsValue,
							},
						],
						onSubmit: function (api) {
							if (api.data.ids !== "") {
								var data = api.data;
								var content = '<div class="io-edit-post-card-content" contenteditable="false">[' + data.type + '_card ids="' + data.ids.replaceAll("，", ",") + '"]</div>';
								if (!isEdit) {
									content += '<p>&nbsp;</p>';
									//ed.dom.setOuterHTML(selectedNode, content);
								}
								ed.insertContent(content);
							}
						}
					});
				}
			});
			
			ed.addButton('dom_remove', {
				tooltip: 'Remove',
				icon: 'dashicon dashicons-no',
				onclick: function () {
					var selectedNode = ed.selection.getNode();
					ed.dom.remove(selectedNode);
					ed.nodeChanged();
					ed.undoManager.add();
				}
			});

			ed.on('wptoolbar', function (event) {
				if (!ed.wp)
					return;
				if (event.element.className === 'io-edit-hide-content') {
					event.toolbar = ed.wp._createToolbar(['io_hide', 'remove'], true);
				}
				if (event.element.className === 'io-edit-post-card-content') {
					event.toolbar = ed.wp._createToolbar(['io_post_card', 'remove'], true);
				}
				if (event.element.className === 'io-delete-keys') {
					event.toolbar = ed.wp._createToolbar(['remove'], true);
				}

				var all_toolbar = get_all_toolbar(event, ed.selection.getContent());
				if (all_toolbar) {
					event.toolbar = all_toolbar;
				}
			}); 

			function get_all_toolbar(event, getContent) {
				var bars = [];
				var nodeName = event.element.nodeName;
				var innerText = $.trim(event.element.innerText);
	
				if ($.inArray(nodeName, ['P', 'H1', 'H2', 'H3', 'H4', 'H5', 'UL', 'LI', 'STRONG', 'SPAN', 'CODE']) !== -1) {
	
					var parentElement = event.element.parentElement;
					if (parentElement.id === 'tinymce' || ($.inArray(nodeName, ['SPAN']) !== -1 && parentElement && parentElement.parentElement.id === 'tinymce')) {
						bars.push('io_h2', 'io_h3');
					}
	
					if (innerText) {
						//有内容了
						bars.push('bullist', 'numlist', 'aligncenter');
						if (($.inArray(nodeName, ['P']) !== -1 && parentElement && parentElement.id === 'tinymce')) {
							bars.push('io_hide');
						}
						bars.push('remove');
					} else {
						bars.push('bullist', 'io_hide');
					}
	
					if (getContent) {
						//选中了部分文字
						bars = [];
						if ($.inArray(nodeName, ['H1', 'H2', 'H3', 'H4', 'H5']) === -1) {
							bars = ['bold', 'link', 'wp_code', 'code'];
						}
						bars.push('forecolor');
						if ($.inArray(nodeName, ['P']) !== -1) {
							bars.push('io_hide');
						}
						bars.push('remove');
					}
				}
	
				return bars[0] ? ed.wp._createToolbar(bars) : false;
			}
		},
		createControl : function(n, cm) {
			return null;
		},
	});
	tinymce.PluginManager.add( 'io_button_script', tinymce.plugins.MyButtons );
	

	function TextToHtml(str) {
		return str.replace(/\r\n/g, '<br>').replace(/\n/g, '<br>').replace(/\s/g, ' ');
	}
	function HtmlToText( str ) {
		if (str == null) {
			return "";
		}
		str = str.replaceAll("<br>", "\n");
		str = str.replaceAll("<br>", "\r");    
		return str;
	} 

	setTimeout(function () {
		// 自动夜间模式处理编辑器状态
		var _tinymce_body = $("#post_content_ifr").contents().find('body');
		if ($('html').hasClass('io-black-mode') && _tinymce_body[0]) {
			_tinymce_body.addClass('io-black-mode');
		}
		else {
			_tinymce_body.removeClass('io-black-mode');
		}
	}, 256);


	/**
	 * 前台文件上传
	 * @param {object} options 
	 * @returns 
	 */
	IO.media = function (options) {
		this.options = $.extend({
			type: 'image', 			//上传类型
			isUpload: false, 		//允许上传
			isInput: true, 			//允许输入内容
			uploadMultiple: 0, 		//批量上传限制数量, 0为不限制
			multiple: 0, 			//限制允许选择的数量，0为不限制 
			mediaType: 'all',       //自定义媒体类型 all 显示所有类型， default, favicon, icon, cover
			autoUpload: true, 		//自动上传
			fileTypes: 'jpg,jpeg,png,gif,mp4,avi,zip,rar,doc,pdf',
			maxFileSize: 30 * 1024 * 1024, // 默认30MB
			chunked: true,
			chunkSize: 2 * 1024 * 1024, // 分片大小，默认 2MB
			threads: 3,  				// 并发上传数
			retries: 3,  				// 分片重试次数
			log: !1, 				    // 是否开启日志
		}, options);
		
		this.type = this.options.type || 'image';
		this.refresh_lists = true;
 
		this.file_data = {}; // 存储文件信息
		this.new_upload = []; // 新上传的文件ID
		this.active_lists = []; // 当前选中的ID

		this.init = function () {
			this.initDOM(); //创建DOM
			this.options.isUpload && this.initUploader(); //初始化上传
		};

		var title = this.type === 'image' ? IOL.image : this.type === 'video' ? IOL.video : IOL.attachment;

		var local = {
			start_uploading: IOL.start_uploading,//'开始上传',
			max_file_size: sprintf(IOL.max_file_size, (this.options.maxFileSize / 1024 / 1024).toFixed(2)),//'最大支持%sMB，'
			single_count: sprintf(IOL.single_count, this.options.uploadMultiple),//'单次可上传%d个文件，'
			select_att: sprintf(IOL.select_att, title),//'选择%s',
			drag_upload: sprintf(IOL.drag_upload, title),//'将%s拖到这里上传',
			uncheck: IOL.uncheck,//'取消选中',
			please_select: IOL.please_select,//'请选择',
			other_tips: IOL.other_tips,//'支持拖文件上传，支持粘贴板。',
			out_tips: IOL.out_tips,//'请填写图片地址，支持批量输入，一行一个链接。',
			ok: IOL.ok,//'确定',
			not_refresh: IOL.not_refresh,//'还有文件正在上传，请勿刷新页面！',
			first_upload: IOL.first_upload,//'请先上传图片！',
			file_uploading: IOL.file_uploading,//'文件正在上传中，请稍后操作！',
			confirm_delete: IOL.confirm_delete,//'确定要删除该文件吗？删除后不可恢复！',
			loading: IOL.loading,//'加载中',
			input_url: IOL.input_url,//'请输入地址！',
			max_input_count: sprintf(IOL.max_input_count, this.options.multiple, title),//'最多可输入%d个%s地址',
			tobe_uploaded: IOL.tobe_uploaded,//'待上传',
			uploading: IOL.uploading,//'上传中...',
			upload_failed: IOL.upload_failed,//'上传失败',
			upload_success: IOL.upload_success,//'上传成功',
			processing: IOL.processing,//'处理中...',
			load_more: IOL.load_more,//'加载更多',
			log_max_up_file: sprintf(IOL.log_max_up_file, this.options.uploadMultiple),//'最多上传%d个文件。'
			log_max_size_file: sprintf(IOL.log_max_size_file, (this.options.maxFileSize / 1024 / 1024).toFixed(2)),//'文件大小不能超过%sMB。'
			log_file_type: IOL.log_file_type,//'文件类型不支持！',
			log_upload_failed: IOL.log_upload_failed,//'上传失败，请重试！',
			log_max_select_file: sprintf(IOL.log_max_select_file, this.options.multiple),//'最多只能选择%d个附件。'
		};

		this.initDOM = function () {
			this.options.log && console.log(this);
			if (this.$el) return;
			var that = this;
			that.uid = that.helper.uid();
			
			var modalContent = function () {
				var list_class = that.type === 'video' ? 'row-col-2a row-col-md-4a' : 'row-col-4a row-col-md-6a';
				var upload_btn = that.options.autoUpload ? '' : `<div class="btn upload-btn btn-sm vc-l-blue hide">${local.start_uploading}</div>`;
				var media_btn = '';
				if (that.options.mediaType === 'all') {
					media_btn += `<div class="media-btn btn btn-tab-h btn-sm active" data-value="">${IOL.all_att}</div>`;
					//循环 IO.contributeVar.media_type 获取按钮 
					$.each(IO.contributeVar.media_type, function (key, value) {
						media_btn += `<div class="media-btn btn btn-tab-h btn-sm" data-value="${key}">${value}</div>`;
					});
					media_btn = `<div class="media-btn-group overflow-x-auto no-scrollbar">${media_btn}</div>`;
				} else {
					media_btn += `<div class="media-btn btn btn-tab-h btn-sm mr-2" data-value="">${IOL.all_att}</div>`;
					media_btn += `<div class="media-btn btn btn-tab-h btn-sm active" data-value="${that.options.mediaType}">${IO.contributeVar.media_type[that.options.mediaType]}</div>`;
				}
				var tips = '';
				if (that.options.maxFileSize > 0) {
					tips += local.max_file_size;
				}
				if (that.options.uploadMultiple > 0) {
					tips += local.single_count;
				}
				
				var up = `
				<div class="tab-pane fade uploader-tab active show" id="tab_${that.uid}_my" role="tabpanel">
					<div class="uploader-header">
						${media_btn}
						<div class="flex-fill"></div>
						${upload_btn}
						<div id="picker_box_${that.uid}" class="btn vc-l-theme btn-sm picker-box ml-2">${local.select_att}</div>
					</div>
					<div id="drag_box_${that.uid}" class="uploader-body uploader-content drag-box p-1 p-md-2 my-3">
						<div class="attachment-lists lists-type-${that.type} row-a row-sm ${list_class}"></div>
						<div class="drag-tips">${local.drag_upload}</div>
					</div>
					<div class="uploader-footer d-flex align-items-center">
						<div class="text-muted" data-toggle="tooltip" title="${tips + local.other_tips}"><i class="iconfont icon-tishi mr-2"></i></div>
						<div class="flex-fill"></div>
						<div class="btn vc-l-red btn-sm list-uncheck hide mr-3">${local.uncheck}</div>
						<div class="btn vc-theme btn-sm px-3 list-submit btn-shadow disabled">${local.please_select}</div>
					</div>
				</div>`;
				var input_class = that.options.isUpload && that.options.isInput ? '' : 'active show';
				var input = `
				<div class="tab-pane fade input-modal ${input_class}" id="tab_${that.uid}_outer" role="tabpanel">
					<div class="uploader-body">
						<p class="text-sm text-muted">${local.out_tips}</p>
						<textarea rows="4" class="form-control input-textarea" style="height:168px;" placeholder="http://..."></textarea>
					</div>
					<div class="uploader-footer mt-3">
						<div class="btn vc-theme input-submit btn-shadow btn-block">${local.ok}</div>
					</div>
				</div>`;
				
				var html = '';
				if (that.options.isUpload) {
					html += up;
				}
				if (that.options.isInput) {
					html += input;
				}
				
				return html;
			}
			var modalHeader = function () {
				var my_title = sprintf(IOL.my_title, title);
				var out_title = sprintf(IOL.out_title, title);
				var close = `<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="iconfont icon-close text-lg" aria-hidden="true"></i></button>`;
				if (that.options.isUpload && !that.options.isInput) {
					return `<div class="modal-header">
						<div class="modal-title">${my_title}</div>${close}
					</div>`;
				} else if (!that.options.isUpload && that.options.isInput) {
					return `<div class="modal-header">
						<div class="modal-title">${out_title}</div>${close}
					</div>`;
				} else {
					return `<div class="modal-header">
						<div class="nav modal-tabs" role="tablist">
							<div class="tab-title active mr-3" data-toggle="tab" data-target="#tab_${that.uid}_my">${my_title}</div>
							<div class="tab-title" data-toggle="tab" data-target="#tab_${that.uid}_outer">${out_title}</div>
						</div>
						${close}
					</div>`;
				}
			}
			
			// 初始化 WebUploader 的 DOM 结构
			// 设置唯一ID
			var id = 'media_uploader_' + that.uid;

			var modal_html = `<div class="modal fade io-media-modal" id="${id}" tabindex="-1" role="dialog" aria-hidden="false" style="display: none;">\
            	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            		<div class="modal-content">
						${modalHeader()}
						<div class="modal-body tab-content">
						${modalContent()}
						</div>
					</div>
            	</div>
            </div>`;

			$('body').append(modal_html);
			that.$el = $('#' + id);
			that.$lists = $('#' + id + ' .attachment-lists');

			that.$el.find('[data-toggle="tooltip"]').tooltip();
			
			that.$el.on('hidden.bs.modal', function () {
				that.$lists.find('.list-item.uploading').length && showAlert({
					status: 3,
					msg: local.not_refresh
				});
			});
			// 选择图片
			that.$lists.on('click', '.list-item', function () {
				var $this = $(this);
				if ($this.hasClass('upload')) {
					showAlert({
						status: 2,
						msg: local.first_upload
					})
					return;
				}
				if ($this.hasClass('uploading')) {
					showAlert({
						status: 2,
						msg: local.file_uploading
					})
					return;
				}
				that.pickItem($(this));
			});
			// 删除图片
			that.$lists.on('click', '.delete-item', function (e) {
				e.stopPropagation();
				var $this = $(this);
				var $item = $this.parent();
				var id = $item.data('file_id');
				if (isNaN(id)) {
					$item.remove();
					that.uploader.getFile(id) && that.uploader.removeFile(id, true);
				} else {
					// 弹出确认框
					if (confirm(local.confirm_delete)) {
						var ids = that.active_lists;
						if (ids) {
							var multiple = that.options.multiple;
							var index = ids.indexOf(id);
							if (index > -1) {
								ids.splice(index, 1);
							}
							var $other = that.$lists.find('.list-item:not(.active)');
							if (multiple > 1 && ids.length >= multiple) {
								$other.attr('disabled', true);
							} else {
								$other.removeAttr('disabled');
							}
						}
						var ajax_data = {
							action: 'delete_user_attachments',
							id: id
						};
						that.ajax(ajax_data);
						$item.remove();
					}
				}
				return false;
			});
			// 切换媒体类型
			that.$el.on('click', '.media-btn', function () {
				var $this = $(this);
				$this.addClass('active').siblings().removeClass('active').parent().tabToCenter();
				// 清除基础数据
				that.file_data = {};
				that.new_upload = [];
				that.clearItem();
				that.refresh_lists = true;
				// 刷新列表
				that.loadLists();
			});
			
			//加载下一页
			that.$lists.on('click', '.next-page', function (e) {
				e.preventDefault();
				var $this = $(this);
				if ($this.hasClass('disabled')) return;
				$this.addClass('disabled');

				$this.html(`<a><i class="iconfont icon-loading icon-spin mr-2"></i>${local.loading}</a>`);
				that.loadLists($this.data('paged'));
				return false;
			});
			//取消选中
			that.$el.on('click', '.list-uncheck', function () {
				that.clearItem();
			});
			//列表选择确认
			that.$el.on('click', '.list-submit', function () {
				if ($(this).hasClass('disabled')) return;
				
				var SelectData = that.getItemData(that.active_lists);
				that.$el.trigger('select_submit').trigger('lists_submit', {
					data: SelectData,
				});
				that.clearItem();
				that.close();
			});
			//手动输入地址确认
			that.$el.on('click', '.input-submit', function () {
				var $input = that.$el.find('.input-textarea');
				var val = that.helper.encodeHTML($input.val());
				if (!val) {
					showAlert({
						status: 2,
						msg: local.input_url
					});
					return;
				}

				var multiple = that.options.multiple;
				val = val.split(/[(\r\n)\r\n]+/);
				val.forEach((item, index) => {
					if (!item) {
						val.splice(index, 1); //删除空项
					}
				});

				if (multiple && val.length > multiple) {
					showAlert({
						status: 3,
						msg: local.max_input_count
					});
					return;
				}

				that.$el.trigger('select_submit').trigger('input_submit', {
					data: val,
				});
				$input.val('');
				that.close();
			});
		};

		// 初始化 WebUploader
		this.initUploader = function () {
			var that = this;
			if (that.is_init_upload) return;
			that.is_init_upload = true;

			// 存储每个文件的分片上传状态
			var fileChunks = {};
 
			// 在文件上传前先判断服务是否已存在这个文件
			WebUploader.Uploader.register({
				'before-send-file': 'beforeSendFile'
			}, {
				beforeSendFile: function (file) {
					var owner = this.owner,
						deferred = WebUploader.Deferred();
			
					owner.md5File(file.source).fail(function () {
						// 如果读取出错了，则通过reject告诉webuploader文件上传出错。
						deferred.reject();
					}).then(function (md5) {
						// md5值计算完成, 与服务验证
						file.md5 = md5;
						var ajax_data = {
							action: 'check_user_attachments',
							md5: md5,
							noMsg: true
						};
						that.ajax(ajax_data, function (n) {
							if (n.success) {
								if (n.data.data.id) {
									that.options.log && console.log('已存在，跳过文件：', file.name);
									// 文件已存在，则跳过上传，直接添加到已上传列表
									that.uploadComplete(file, n);
									owner.skipFile(file);
								}
								deferred.resolve();
							} else {
								that.options.log && console.log('未知错误！');
								deferred.reject();
							}
						});
					});
					return deferred.promise();
				}
			});

			
			var id = '#media_uploader_' + that.uid;
			var uploader = WebUploader.create({
				auto: that.options.autoUpload,// 自动上传
				server: IO.ajaxurl,// 服务器处理文件上传的接口
				formData: { //上传时传递的额外参数
					action: 'upload_user_attachments',
				},
				pick: {
					id: '#picker_box_' + that.uid,// 选择文件按钮的ID
					multiple: that.options.uploadMultiple == 1 ? false : true // 支持多文件选择
				},
				dnd: '#drag_box_' + that.uid,// 支持拖拽文件到上传区域
				paste: document.body,// 粘贴板功能，支持直接粘贴图片
				fileNumLimit: that.options.uploadMultiple,// 限制上传文件数量
				fileSingleSizeLimit: that.options.maxFileSize,// 单个文件最大大小
				chunked: that.options.chunked,// 开启分片上传
				chunkSize: that.options.chunkSize,// 分片大小，单位为字节（这里是2MB）
				accept: {// 允许的文件类型（图片、视频、附件）
					title: that.options.type,
					extensions: that.options.fileTypes,
					mimeTypes: that.helper.getAccept(that.options.type)
				},
				//sendAsBinary: false,  //文件上传后的路径格式
				compress: false,// 禁用图片压缩（默认）
				threads: that.options.threads,
				chunkRetry: that.options.retries,
				duplicate: true,
			});
			that.uploader = uploader;

			// 点击上传按钮
			that.$el.on('click', '.upload-btn', function () {
				uploader.upload();
			});


			// 监听 fileDequeued 事件，当文件被移除时触发
			uploader.on('fileDequeued', function (file) {
				if (that.$lists.find('.upload,.uploading').length === 0) {
					that.$el.find('.upload-btn').addClass('hide');
					// 刷新 uploader
					uploader.reset();
				}
			});
			
			// 文件添加到队列时
			uploader.on('fileQueued', function (file) {
				fileChunks[file.id] = {
					totalChunks: Math.ceil(file.size / that.options.chunkSize),
					uploadedChunks: 0
				};
				
				// 显示上传按钮
				if (!that.options.autoUpload) {
					that.$el.find('.upload-btn').removeClass('hide');
				}

				// 添加前缀
				file.prefix = that.helper.uid();

				//获取原始的file
				var _file = file.source.source;
				var _preview_list = $(that.createItem(file, 'upload up-id-' + file.id));
				_preview_list.append(`<div class="progress progress-striped active">
						<div class="progress-bar" role="progressbar" style="width: 0%"></div>
						<span class="state">${local.tobe_uploaded}</span>
					</div>`);
				//图片预览
				that.type === 'image' && that.setImgPreview(_file, _preview_list);
				//插入预览及进度DOM
				that.uploadBeforeDOM(_preview_list);
			});

			// 在上传之前，将 MD5 值附加到上传数据中
			uploader.on('uploadBeforeSend', function (block, data) {
				var file = block.file;
				var media_type = that.$el.find('.media-btn.active').data('value');
				// 将之前计算好的 MD5 值附加到上传数据
				data.md5 = file.md5;
				data.prefix = file.prefix;
				data.media_type = media_type;
				data._wpnonce = mce.upload_nonce;
			});

			// 处理每个分片上传成功的逻辑
			uploader.on('uploadAccept', function (object, ret) {
				var file = object.file;  // 当前上传的文件
				var chunks = fileChunks[file.id];  // 获取文件对应的分片信息
				if (chunks.totalChunks > 1) {
					// 增加已上传分片的计数
					chunks.uploadedChunks++;
					that.options.log && console.log('分片上传成功:', file.name, chunks.uploadedChunks, '/', chunks.totalChunks);
					// 如果所有分片上传完成，发起合并请求
					if (chunks.uploadedChunks === chunks.totalChunks) {
						that.options.log && console.log('所有分片上传完成，开始合并文件:', file.name);
						var media_type = that.$el.find('.media-btn.active').data('value');
						// 发起请求，通知服务器合并分片
						var ajax_data = {
							action: 'merge_attachments_chunks',
							name: file.name,
							chunks: chunks.totalChunks,
							md5: file.md5,
							prefix: file.prefix,
							media_type: media_type,
							noMsg: true
						};
						that.ajax(ajax_data, function (n) {
							that.uploadComplete(file, n);
							if (n.success) {
								that.options.log && console.log('文件合并成功：', file.name);
							} else {
								that.options.log && console.log('文件合并失败：', file.name);
							}
						});
					}
				}
			});
			// 文件上传过程中
			uploader.on('uploadProgress', function (file, percentage) {
				var $item = $(`${id} .up-id-${file.id}`);
				let $percent = $item.find('.progress .progress-bar');
				
				$item.removeClass('upload').addClass('uploading');
				if (!$percent.length) {
					$percent = $(`<div class="progress progress-striped active">
						<div class="progress-bar" role="progressbar" style="width: 0%"></div>
					</div>`).appendTo($item).find('.progress-bar');
				}
	
				$item.find('.state').text(local.uploading);
				$percent.css('width', percentage * 100 + '%');
			});
			// 文件上传成功
			uploader.on('uploadSuccess', function (file, response) {
				var $item = $(`${id} .up-id-${file.id}`);
				if (!response) {
					$item.find('.state').text(local.upload_failed);
					return;
				}
				if (response.data.data.is_chunk) {
					$item.find('.state').text(local.processing).data('is_chunk', true);
				} else {
					that.uploadComplete(file, response);
				}
			});
			// 上传完成后的回调（整个文件上传完成）
			uploader.on('uploadComplete', function (file) {
				that.options.log && console.log('列队完成:', file.name);
				uploader.removeFile(file, true);
			});
			// 文件上传失败
			uploader.on('uploadError', function (file) {
				var $item = $(`${id} .up-id-${file.id}`);
				$item.find('.state').text(local.upload_failed);
				uploader.removeFile(file, true);
				setTimeout(() => {
					$item.remove();
					uploader.removeFile(file, true);
				}, 1500);
			});
			//error
			uploader.on('error', function (type) {
				if (type == 'Q_EXCEED_NUM_LIMIT') {
					showAlert({ "status": 3, "msg": local.log_max_up_file });
				} else if (type == 'Q_EXCEED_SIZE_LIMIT' || type == 'F_EXCEED_SIZE') {
					showAlert({ "status": 3, "msg": local.log_max_size_file });
				} else if (type == 'Q_TYPE_DENIED') {
					showAlert({ "status": 3, "msg": local.log_file_type });
				} else {
					showAlert({ "status": 3, "msg": local.log_upload_failed });
				}
			});
		}

		// 上传完成
		this.uploadComplete = function (file, data) {
			var that = this;
			var multiple = that.options.multiple;
			var $item = that.$lists.find('.up-id-' + file.id);
			if (data.success) {
				var n = data.data.data;
				var active = false;
				// 判断 that.file_data 是否有这个文件
				if (!that.file_data[n.id]) {
					// 新文件
					that.file_data[n.id] = n;
					that.new_upload.push(n.id);
				} else {
					var $oldItem = that.$lists.find(`[data-file_id="${n.id}"]`);
					active = $oldItem.hasClass('active');
					that.$lists.find(`[data-file_id="${n.id}"]`).remove();
				}
				$item.removeClass('uploading upload').attr('data-file_id', n.id).find('.progress').remove();
				if (active) {
					$item.addClass('active');
				} else {
					if (multiple > 1 && that.active_lists.length >= multiple) {
						$item.attr('disabled', true);
					} else {
						$item.trigger('click');
					}
				}
				that.uploader.removeFile(file, true);
			} else {
				$item.find('.state').text(local.upload_failed);
			}
		}

		// 清除选中的附件
		this.clearItem = function () {
			this.$lists.find('.list-item.active').removeClass('active').siblings().removeAttr('disabled');
			this.active_lists = [];
			this.$el.find('.list-submit').addClass('disabled').text(local.please_select);
			this.$el.find('.list-uncheck').addClass('hide');
		};
		/**
		 * 设置选中的附件
		 * @param {jQuery} $this 
		 * @returns 
		 */
		this.pickItem = function ($this) {
			var multiple = this.options.multiple;
			var file_id = ~~$this.attr('data-file_id');
			if (!file_id) return;
			

			var ids = multiple == 1 ? [] : this.active_lists;
			
			if ($this.attr('disabled')) {
				if (multiple > 1 && ids.length >= multiple) {
					showAlert({ "status": 3, "msg": local.log_max_select_file });
				}
				return;
			}

			if (multiple == 1) {
				$this.addClass('active').siblings().removeClass('active');
				ids = [file_id];
				this.active_lists = ids;
			} else {
				if ($this.hasClass('active')) {
					$this.removeClass('active');
					ids.splice(ids.indexOf(file_id), 1);
				} else {
					$this.addClass('active');
					ids.push(file_id);
				}
			}

			var $other = this.$lists.find('.list-item:not(.active)');
			if (multiple > 1 && ids.length >= multiple) {
				$other.attr('disabled', true);
			} else {
				$other.removeAttr('disabled');
			}

			
			//切换显示插入按钮
			var $submit = this.$el.find('.list-submit');
			if (ids.length) {
				$submit.removeClass('disabled').text(local.ok);
			} else {
				$submit.addClass('disabled').text(local.please_select);
			}
			var $uncheck = this.$el.find('.list-uncheck');
			if (ids.length > 1 && multiple != 1) {
				$uncheck.removeClass('hide');
			} else {
				$uncheck.addClass('hide');
			}
		};

		//根据文件ID获取文件数据
		this.getItemData = function (ids) {
			if (!$.isArray(ids)) ids = [ids];
			var data = [];
			for (let key in ids) {
				var file_id = ids[key];
				this.file_data[file_id] && data.push(this.file_data[file_id]);
			}
			return data;
		};
		//渲染图片元素
		this.setImgPreview = function (file, box) {
			var r = new FileReader();
			r.readAsDataURL(file);
			(r.onload = function (e) {
				box.find('img').attr({
					alt: file.name,
					src: e.target.result,
				});
			});
		};

		//上传前先插入预览
		this.uploadBeforeDOM = function (_list) {
			var $lists = this.$lists;
			var $first = $lists.find('.list-item').first();
			$lists.find('.nothing-box').remove();

			if ($first.length) {
				$first.before(_list);
			} else {
				$lists.prepend(_list);
			}
		};
		
		//ajax请求
		this.ajax = function (data, fun, error_fun) {
			$.ajax({
				type: 'POST',
				url: IO.ajaxurl,
				data: data,
				dataType: 'json',
				error: function (n) {
					showAlert(n.data);
					$.isFunction(error_fun) && error_fun(n);
				},
				success: function (n) {
					if (!data.noMsg || !n.success) {
						showAlert(n.data);
					}
					$.isFunction(fun) && fun(n);
				},
			});
		};
		
		//加载列表
		this.loadLists = function (paged) {
			var that = this;
			if (!that.$lists || !that.$lists.length) return;
			var loading_class = 'loading-box';
			var media_type = that.$el.find('.media-btn.active').data('value');
			paged = paged || 1;
			var $listsBox = that.$lists;
			var loading = `<div style="height: 200px;width: 100%;" class="${loading_class} col-1a-i"><div class="d-flex align-items-center justify-content-center h-100"><i class="iconfont icon-loading icon-spin text-32"></i></div></div>`;

			var ajax_data = {
				action: 'get_user_attachments',
				type: that.type,
				media_type: media_type,
				paged: paged,
			};

			if (paged == 1) {
				$listsBox.html(loading);
			} else {
				ajax_data.exclude = that.new_upload; //排除
			}

			that.ajax(ajax_data, function (n) {
				var data = n.data.data;
				var lists = data.images;
				var pagination_class = 'media-next';

				var lists_html = '';
				if (lists) {
					lists_html = that.createItems(lists);
				}

				if (lists_html && data.all_pages > paged) {
					lists_html += `<div class="text-center col-1a-i ${pagination_class}"><div class="next-page next-hover my-3" data-paged="${paged + 1}"><a href="javascript:;">${local.load_more}</a></div></div>`;
				}

				var $lastItem = $listsBox.find('.list-item').last();
				$listsBox.find(`.${pagination_class},.${loading_class}`).remove(); //删除下一页

				if ($lastItem.length) {
					$lastItem.after(lists_html);
				} else {
					lists_html = lists_html || n.data.html;
					$listsBox.html(lists_html);
				}

				that.refresh_lists = false; //重置状态
			});
		};

		this.helper = {
			uid: function () {
				return Math.random().toString(36).slice(2, 11);
			},
			encodeHTML: function (value) {
				var div = document.createElement('div');
				div.textContent = value.trim();
				return div.innerHTML;
			},
			getAccept: function (type) {
				var accepts = {
					image: 'image/gif,image/jpeg,image/jpg,image/png',//,image/svg+xml
					video: 'video/*',
					file: '*',
				};
				return accepts[type];
			}
		}
		
		this.createItem = function (data, add_class) {
			var list_html = '<img src="' + (data.thumbnail_url || data.medium_url || '') + '" alt="' + (data.title || this.type) + '">';
			return `<div class="${add_class ? add_class + ' ' : ''}list-item" data-file_id="${data.id || 0}" data-file_type="${this.type}">
				<div class="list-box">${list_html}</div>
				<div class="delete-item"><i class="iconfont icon-close"></i></div>
			</div>`;
		};
		//构建项列表html
		this.createItems = function (lists_data) {
			var lists_html = '';
			var that = this;

			if (lists_data) {
				$.each(lists_data, function (i, data) {
					lists_html += that.createItem(data);
					that.file_data[data.id] = data;
				});
			}

			return lists_html;
		};

		this.open = function () {
			this.$el.modal('show');
			this.refresh_lists && this.loadLists();
		};


		this.close = function () {
			this.$el.modal('hide');
		};
		
		this.init();
		return this;
	}

	function sprintf(format, ...args) {
		let i = 0;
		return format.replace(/%s|%d|%f/g, (match) => {
			let arg = args[i++];
			switch (match) {
				case "%d": return parseInt(arg);
				case "%f": return parseFloat(arg);
				case "%s": return String(arg);
				default: return arg;
			}
		});
	}

})(jQuery,window);


