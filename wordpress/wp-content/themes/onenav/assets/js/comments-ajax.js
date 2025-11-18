/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-06-03 08:55:59
 * @LastEditors: iowen
 * @LastEditTime: 2024-09-09 00:02:06
 * @FilePath: /onenav/assets/js/comments-ajax.js
 * @Description: 
 */

var _list = 'comment-list'; 
var cancel = $('#cancel-comment-reply-link');

//提交评论
$(document).on('click', "#commentform #submit", function () {
	var _this = $(this);
	var _form = _this.closest('form'); 
	captcha_ajax(_this, '', function (n) {
		var data = n.html;
		if (n.status && data) {
			var parent = $('#comment_parent').val();
                $text = $(data);
			if (parent != '0') {
                $children = $('<ul class="children"></ul>');
                $('#respond').before($children);
                $children.html($text);
			} else if (!$('.' + _list ).length) {
                $children = $('<ul class="' + _list + '"></ul>');
				if (IO.formpostion == 'bottom') {
					$('#respond').before($children);
				} else {
					$('#respond').after($children);
				}
                $children.html($text);
			} else {
				if (IO.order == 'asc') {
					$('.' + _list ).append($text); 
				} else {
					$('.' + _list ).prepend($text); 
				}
            }
            $text.children(".new-comment").animate({opacity : 0},2000);
			showAlert(JSON.parse('{"status":1,"msg":"提交成功!"}'));
			$('#comment').val(''); 
			cancel[0].click();
			_form.find('[name="image_captcha"]').val('');
			_form.find('.image-captcha').click();
		}
	});
	return false;
});

