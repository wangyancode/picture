/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2024-08-20 16:50:10
 * @LastEditors: iowen
 * @LastEditTime: 2025-06-03 22:54:27
 * @FilePath: /onenav/assets/js/main.js
 * @Description: 
 */
'use strict';
(function ($, jQuery) {
  var $ = $ || {};
  $.window = jQuery(window);
  $.document = jQuery(document);
  $.body = jQuery('body');
  $.html = jQuery('html');

  $.isAsideInitEvent = false;
})(IO, jQuery);

(function ($) {
/**
 * 操作 cookies
 * @param {string} name 指定的 cookie 名称
 * @param {*} value 
 * @param {*} options 
 * @returns 
 */
$.cookie = function (name, value, options = {}) {
  if (typeof value === 'undefined') {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
      var cookie = cookies[i].trim();
      if (cookie.indexOf(name + '=') === 0) {
        return decodeURIComponent(cookie.substring(name.length + 1));
      }
    }
    return null;
  }

  if (value === null) {
    options.expires = -1;
    value = '';
  }

  var expires = '';
  if (options.expires) {
    var date;
    if (typeof options.expires === 'number') {
      date = new Date();
      date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
    } else if (options.expires.toUTCString) {
      date = options.expires;
    }
    expires = `; expires=${date.toUTCString()}`;
  }

  var path = options.path ? `; path=${options.path}` : '; path=/';
  var domain = options.domain ? `; domain=${options.domain}` : '';
  var secure = options.secure ? '; secure' : '';

  document.cookie = `${name}=${encodeURIComponent(value)}${expires}${path}${domain}${secure}`;
};

$.fn.serializeObject = function () {
  var o = {};
  var a = this.serializeArray();
  $.each(a, function () {
    if (o[this.name] !== undefined) {
      if (!o[this.name].push) {
        o[this.name] = [o[this.name]];
      }
      o[this.name].push(this.value || '');
    } else {
      o[this.name] = this.value || '';
    }
  });
  return o;
};

$.fn.io_select_dropdown = function () { 
  return this.each(function () {
    var $this = $(this),
        $input = $this.find('input'),
        $items = $this.find('.dropdown-item'),
        $label = $this.find('.select-item');

    $items.on('click', function () {
      var $t = $(this);
      var value = $t.data('value');
      var text = $t.text();

      $input.val(value);
      $label.text(text);
      if($this.hasClass('hover')) {
        $this.css('pointer-events', 'none');
        setTimeout(function () {
          $this.css('pointer-events', 'auto');
        }, 500);
      }
    });
  });
};

$.fn.io_toggle_div = function () {
  return this.each(function () {
    var $this = $(this),
        $target = $($this.attr('data-target')),
        $mask = $('.fixed-body');

    var custom = $this.attr('data-class') || '',
        zIndex = $this.attr('data-z-index') || '10';

    if (!$mask.length) {
      $mask = $('<div class="fixed-body"></div>');
      IO.body.append($mask);
    }
    
    var updateMask = function () { 
      $mask.removeClass('show ' + custom).off('click');

      if ($mask.css('z-index') != '10') {
        setTimeout(function () {
          $mask.css('z-index', '10');
        }, 300);
      }
    };

    var closeTarget = function () { 
      $target.removeClass('show');
      updateMask();
      $this.attr('aria-expanded', 'false');
    }

    $this.on("click", function (e) {
      e.preventDefault();

      if (!$target.length) return;

      var oldZIndex = $mask.css('z-index');
      var targetShown = $target.toggleClass('show').hasClass('show');

      $this.attr('aria-expanded', targetShown);

      if (targetShown) {
        $mask.trigger('click');
        $this.attr('old-z', oldZIndex);
        
        $mask.css('z-index', zIndex).addClass('show ' + custom);
        $mask.one('click', closeTarget);
        $target.find('.hide-target').off('click').one('click', closeTarget);
      } else {
        updateMask();
      }

    });
  });
};

$.fn.tabToCenter = function () {
  return this.each(function () {
    var $this = $(this);
    var $active = $this.find('.active');

    if ($active.length) {
      var containerWidth = $this.innerWidth();
      var activeTabWidth = $active.innerWidth();
      var currentScrollLeft = $this.scrollLeft();
      var activeTabPosition = $active.position().left;
      var maxScrollLeft = Math.floor($this.prop('scrollWidth') - containerWidth);

      var targetScrollLeft = Math.floor(currentScrollLeft + activeTabPosition - containerWidth / 2 + activeTabWidth / 2);

      targetScrollLeft = Math.max(0, Math.min(targetScrollLeft, maxScrollLeft));

      if (targetScrollLeft !== currentScrollLeft) {
        $this.animate({ scrollLeft: targetScrollLeft }, 300);
      }
    }
  });
};
 
// 滑动选项卡
$.fn.io_slider_tab = function () {
  return this.each(function () {
    var $this = $(this),
      $nav = $this.find('.slider-ul'),
      $tabs = $this.find('.slider-li'),
      $active = $nav.find('.active'),
      $more = $this.find('.tab-more');
    
    var widthScale = 100,
      activeClass = $nav.data('active') || 'active';

    var updateSliderPosition = function (isNew = false) {
      var $slider = $nav.children('.anchor');
      if ($active.length) {
        const scale = widthScale !== 100 ? (100 / widthScale) : 1;
        const offset = widthScale !== 100 ? $active.outerWidth() / (100 / (100 - widthScale)) / 2 : 0;

        $slider.css({
          left: `${$active.position().left + $nav.scrollLeft() + offset}px`,
          width: `${$active.outerWidth() / scale}px`,
          height: isNew ? `${$active.height()}px` : undefined,
          opacity: 1
        });
      } else {
        $slider.css({
          opacity: 0
        })
      }
    };

    var loadPredefinedContent = function (type, $body) {
      const placeholderHtml = getPredefinedContent(type);
      $body.empty().append(placeholderHtml);
    };

    if ($nav.length && !$nav.hasClass('into')) {
      if ($nav.find('.anchor').length === 0) {
        $nav.prepend('<li class="anchor" style="position:absolute;width:0;height:28px"></li>');
        updateSliderPosition(true);
      } else {
        widthScale = $nav.find('.anchor').data('width') || 100;
        updateSliderPosition();
      }
      $nav.addClass('into');
    }

    if ($tabs.length) {

      $tabs.on('click', function () {
        $active = $(this);
        var $target = $($active.data('target'));

        if ($active.hasClass('active')) return;

        $target.addClass(activeClass).siblings().removeClass(activeClass);
        $active.addClass('active').siblings().removeClass('active');

        $more.attr('href', $active.data('more-link'));

        $nav.tabToCenter();
        updateSliderPosition();
        
        if ($active.hasClass('load')) return;
        
        if ($active.data('action')) {
          var $listBody = $target.find('.ajax-list-body');
          // 根据data('taxonomy')加载预制件
          loadPredefinedContent($active.data('style'), $listBody);
          ioAjax($active, $active.data(), function (n, t) {
            $active.addClass('load');
            $listBody.html(n.html);
            if (isPC()) {
              $target.find('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' });
            }
          });
        }
      });
 
    }
  });
};



$.fn.dependency = function () {
  var checkBoolean = function(v) {
    switch (v) {
      case true:
      case 'true':
      case 1:
      case '1':
        v = true;
        break;

      case null:
      case false:
      case 'false':
      case 0:
      case '0':
        v = false;
        break;
    }
    return v;
  };

  var evalCondition = function (condition, val1, val2) {
    if (condition == '==') {
      return checkBoolean(val1) == checkBoolean(val2);
    } else if (condition == '!=') {
      return checkBoolean(val1) != checkBoolean(val2);
    } else if (condition == '>=') {
      return Number(val2) >= Number(val1);
    } else if (condition == '<=') {
      return Number(val2) <= Number(val1);
    } else if (condition == '>') {
      return Number(val2) > Number(val1);
    } else if (condition == '<') {
      return Number(val2) < Number(val1);
    } else if (condition == 'any') {
      var val1Arr = val1.split(',');
      if ($.isArray(val2)) {
        return val2.some((item) => val1Arr.includes(item));
      } else {
        return val1Arr.includes(val2);
      }
    } else if (condition == 'not-any') {
      var val1Arr = val1.split(',');
      if ($.isArray(val2)) {
        return val2.every((item) => !val1Arr.includes(item));
      } else {
        return !val1Arr.includes(val2);
      }
    }
    return false;
  };

  return this.each(function () {
    var $this = $(this),
        $fields = $this.find('[data-controller]');

    
    if ($fields.length) {
      var isOn = 'is-on';
      $fields.each(function () {
        var $field = $(this);
        console.log($field);
        if ($field.attr(isOn)) return;
        var controllers = $field.attr(isOn, true).data('controller').split('|'),
          conditions = $field.data('condition').split('|'),
          values = $field.data('value').toString().split('|');
        $.each(controllers, function (index, depend_id) {
          var value = values[index] || '',
            condition = conditions[index] || conditions[0] || '==';
          $this.on('change', "[name='" + depend_id + "']", function (elem) {
            var $elem = $(this);
            var _type = $elem.attr('type');
            var val2 = (_type == 'checkbox') ? $elem.is(':checked') : $elem.val();
            var is_show = evalCondition(condition, value, val2);

            $field.trigger('controller.change', is_show);
            console.log(elem);
            if (is_show) {
              $field.show()
            } else {
              $field.hide()
            }
          });
        });
      });
    }
  });
};

// 粘性边栏
$.fn.io_sticky_aside = function () {
  return this.each(function () {
    var $this = $(this),
        $footer = $('footer'),
        $outdent = $this.find('.aside-btn.btn-outdent'),
        $header = $('.header-calculate'),
        $asidePopup = $('.aside-popup'),
        $asidePopupList = $asidePopup.find('ul');
    
    var headerHeight = $('header').outerHeight();
    var footerMarginTop = parseFloat($footer.css('margin-top'));
    var isAsideMin = IO.body.hasClass('aside-min');
    var isFullContainer = IO.body.hasClass('full-container');
    var itemWidth = 0;

    if($asidePopup.length == 0){
      IO.body.append('<div class="aside-popup text-sm"><ul></ul></div>');
      $asidePopup = $('.aside-popup');
      $asidePopupList = $asidePopup.find('ul');
    }

    var resize = function (e) {
      if (e.type === 'scroll' && window.innerWidth < 768) {
        return;
      }

      if (e.type !== 'user' && !IO.isHeaderVisible && !IO.isFooterVisible) {
        return;
      }

      var headerDiffer = 0;
      var footerDiffer = 10;

      var scrollTop = IO.window.scrollTop();
      if (!isFullContainer && $header.length) {
        var headerBottom = $header.first().outerHeight(true);
        headerDiffer = scrollTop < (headerBottom + headerHeight) ? Math.max(0.1, (headerBottom - scrollTop)) : 0;
      }

      if ($footer.length) {
        var offset = scrollTop + window.innerHeight;
        var footerTop = $footer.offset().top - footerMarginTop;
        footerDiffer = offset > (footerTop - 168) ? Math.max(10, (offset - footerTop)) : 10;
      }

      $this.css({
        'top': `${headerHeight + headerDiffer}px`,
        'bottom': `${footerDiffer}px`,
        'opacity': 1
      });
    };
    
    var updateLayout = function (e) {
      $this.removeAttr('style');
      // 更新状态
      headerHeight = $('header').outerHeight();
      footerMarginTop = parseFloat($footer.css('margin-top'));
      isFullContainer = IO.body.hasClass('full-container');
      resize(e);
    };

    IO.updateStickyAside = updateLayout;
  
    if (!IO.isAsideInitEvent) {
      IO.isAsideInitEvent = true;

      IO.window.on('scroll', throttle(resize, 10, true));
      //IO.window.on('scroll', debounce(resize, 100, true));

      if (window.innerWidth >= 768) {
        // 移动端不注册悬停事件
        // 监听悬停事件
        $(".aside-ul").hover(
          function () {
            if (isAsideMin) {
              $('#layout_aside').addClass('hover-show');
            }
          },
          function () {
            if (isAsideMin) {
              $('#layout_aside').removeClass('hover-show');
            }
          }
        );
        // 点击事件
        $outdent.on('click', function () {
          var $t = $(this);

          IO.body.toggleClass('aside-min');
          isAsideMin = !isAsideMin; // 更新缓存变量

          var $c = $t.find('[switch-class]');
          var $_c = $c.attr('class');
          $c.attr('class', $c.attr('switch-class')).attr('switch-class', $_c);

          var $t = $t.find('[switch-text]');
          var $_t = $t.text();
          $t.text($t.attr('switch-text')).attr('switch-text', $_t);
        });
        IO.window.resize(function () {
          if (!isAsideMin && window.innerWidth < 992) {
            $outdent.trigger('click');
          }
        });
      }
      if (IO.isShowAsideSub) {
        IO.document.on('mouseenter', '.aside-ul>.aside-item', function () {
          var $t = $(this);

          $t.one('mouseleave', function () {
            $asidePopup.hide();
          });

          if (!$t.find('.aside-sub').length || $this.hasClass('show')) {
            return;
          }

          if (itemWidth === 0) {
            if (IO.body.hasClass('aside-min')) {
              itemWidth = IO.asideWidth - 20;
              setTimeout(() => {
                if ($this.hasClass('hover-show')) {
                  itemWidth = $t.outerWidth();
                } else {
                  itemWidth = 0;
                }
              }, 300);
            } else {
              itemWidth = $t.outerWidth();
            }
          }

          $asidePopupList.html($t.find('.aside-sub').html());
          $asidePopup.show();
          var top = $t.offset().top - IO.window.scrollTop();
          var offset = IO.window.height() - $asidePopup.height();
          if (offset - top <= 0) {
            top = offset >= 0 ? offset - 15 : 0;
          }
          $asidePopup.stop().animate({ "top": top }, 50);

          var left = $t.offset().left + itemWidth;
          $asidePopup.css('left', `${left}px`);
        });

        IO.document.on('mouseenter', '.aside-popup', function () {
          $asidePopup.show();
          $(this).one('mouseleave', function () {
            $asidePopup.hide();
          });
        });
      
        IO.document.on('click', '.aside-popup ul li', function () {
          $asidePopup.fadeOut(200);
        });
      }
    }

    resize({type: 'user', originalEvent: 'init'});

  });
  
};

// 粘性底部
$.fn.io_stick_footer = function () {
  return this.each(function () {
    var $this = $(this);

    var resize = function (e) {
      if (!IO.isFooterVisible) return;

      $this.attr('style', '');

      var winHeight = IO.window.height();
      var footerHeight = $this.outerHeight();
      var mainContentHeight = $this.position().top + footerHeight;

      if (winHeight > mainContentHeight) {
        $this.css({
          marginTop: winHeight - mainContentHeight
        });
      }

      if (IO.isAsideInitEvent) {
        IO.updateStickyAside(e);
      }
    };

    IO.window.off('resize.io_stick_footer').on('resize.io_stick_footer', debounce(resize, 200));
    
    resize({type: 'auto', originalEvent: 'stick footer'} );
  });
};

// 搜索框
$.fn.io_head_search = function () {
  return this.each(function () {
    var $this = $(this),
      $form = $this.find('form'),
      $s = $form.find('.search-key'),
      $submit = $form.find('.search-submit-btn'),
      $menus = $this.find('.search-menu'),
      $terms = $this.find('.search-term'),
      $smartTips = $this.find('.search-smart-tips');
      
    var page = $form.data("page");
    var isSimple = $this.hasClass('simple-search');
    var is_into = false;
      
    var loadLocalStorageSettings = function () {
      var searchMenu = window.localStorage.getItem("search_menu_" + page);
      if (searchMenu) {
        var menu = $this.find('.search-menu[data-id="' + searchMenu + '"]');
        var group = $this.find('#' + searchMenu);

        menu.addClass('active').siblings().removeClass('active');
        group.addClass('active').siblings().removeClass('active');
        if (isSimple) {
          menu.closest('.dropdown').find('.select-item').text(menu.text());
        }
      }
      $this.io_slider_tab();
      
      var searchTerm = window.localStorage.getItem("search_term_" + page);
      if (searchTerm) {
        var term = $this.find('.search-term[data-id="' + searchTerm + '"]');
        window.setTimeout(function () {
          is_into = true;
          term.trigger('click');
        }, 100);
      }
    };

    var getSmartTips = function () {
      var type = IO.hotWords;
      var urls = {
        google: "//suggestqueries.google.com/complete/search?client=firefox&callback=iowenHot",
        baidu: "//suggestion.baidu.com/su?p=3&cb=?"
      };
      var url = urls[type];
      if (!url) return;
      var value = $s.val();

      $.ajax({
        type: "GET",
        url: url,
        async: true,
        data: type === 'google' ? { q: value } : { wd: value },
        dataType: "jsonp",
        jsonp: type === 'google' ? 'callback' : 'cb',
        success: function (res) {
          $smartTips.children("ul").text("");
          var tipsList = type === 'google' ? res[1].length : res.s.length;
          if (tipsList) {
            for (var i = 0; i < tipsList; i++) {
              var tip = type === 'google' ? res[1][i] : res.s[i];
              $smartTips.children("ul").append("<li>" + tip + "</li>");
              $smartTips.find("li").eq(i).click(function () {
                $s.val(tip);
                $form.submit();
                $smartTips.slideUp(200);
              });
            }
            $smartTips.slideDown(200);
          } else {
            $smartTips.slideUp(200);
          }
        },
        error: function (res) {
          console.error('Error:', res);
        }
      });
    };
   
    var handleInputEvents = function () {
      var listIndex = -1;
      var timeout; 
      
      $s.off().on({
        // 输入法开始组合输入时触发
        compositionstart: () => $s.attr('data-status', false),
        // 输入法结束组合输入时触发
        compositionend: () => $s.attr('data-status', true),
        // 输入框失去焦点时触发
        blur: () => $smartTips.delay(150).slideUp(200),
        // 输入框获得焦点时触发
        focus: () => {
          if ($s.attr('data-status') === 'true' && $s.val() && $s.attr('data-smart-tips') === 'true') {
            getSmartTips();
          }
        },
        // 按键抬起时触发
        keyup: (e) => {
          if ($s.attr('data-status') === 'true' && $s.val()) {
            if (e.keyCode !== 38 && e.keyCode !== 40 && $s.attr('data-smart-tips') === 'true') {
              clearTimeout(timeout);
              // 重新设定 timeout
              timeout = setTimeout(() => {
                timeout = null;
                getSmartTips();
                listIndex = -1;
              }, 500);
            }
          } else {
            $smartTips.slideUp(200);
          }
        },
        // 按键按下时触发
        keydown: (e) => {
          if ($s.attr('data-smart-tips') !== 'true') return;

          var tipsList = $smartTips.find("li");

          if (e.keyCode === 40 || e.keyCode === 38) {
            listIndex = (e.keyCode === 40)
              ? (listIndex + 1) % tipsList.length
              : (listIndex <= 0 ? tipsList.length - 1 : listIndex - 1);

            var $smart = tipsList.eq(listIndex);
            $smart.addClass("current").siblings().removeClass("current");
            $s.val($smart.text());
          }
        }
      });
    };

    var handleFormSubmit = function () {
      $submit.on('click', function () {
        $form.trigger('submit');
      });

      $form.on('submit', function () {
        var key = encodeURIComponent($s.val());
        if (!key) return false;

        var searchUrl = $form.attr("action");
        var url = searchUrl.includes("%s%") ? searchUrl.replace("%s%", key) : searchUrl + key;
        window.open(url);
        return false;
      });
    };

    var handleMenuClicks = function () {
      $menus.on('click', function () {
        var $this = $(this);
        var term = $this.data('default');
        var target = $this.data('target');

        window.localStorage.setItem("search_menu_" + page, $this.data("id"));
      
        $(target + ' .' + term).trigger('click');
      });

      $terms.on('click', function () {
        var $this = $(this);
      
        $this.addClass('active').siblings().removeClass('active');
        $this.closest('ul').tabToCenter();

        $form.attr('action', $this.data('value'));
     
        window.localStorage.setItem("search_term_" + page, $this.data("id"));
      
        $s.attr('data-smart-tips', !$this.data('id').includes("zhannei"));
        $s.attr('placeholder', $this.data('placeholder'));
        if (is_into) {
          is_into = false;
        } else {
          $s.focus();
        }
      
      });
    };

    var init = function () {
      handleInputEvents();
      handleFormSubmit();
      handleMenuClicks();
      loadLocalStorageSettings();
    };

    init();

  });
};

// 自动折叠导航菜单
$.fn.io_nav_auto_fold = function () {
  return this.each(function () {
    var $this = $(this),
        $header = $this.closest('.container-header'),
        $fold = $this.find('.io-menu-fold'),
        $foldSubMenu = $fold.find('>.sub-menu');

    var navFolding = function () {
      var surplusWidth = $header.width() - 100, // 初始化为导航栏宽度减去基准宽度
          navWidth = 0;

      // 先清空子菜单，确保顺序正确
      $foldSubMenu.empty();

      // 减去非导航菜单项元素的宽度
      $header.find('>:not(.navbar-header-menu):not(.flex-fill):not(.menu-btn)').each(function () {
        surplusWidth -= $(this).outerWidth(true);
      });

      $this.find('>li:not(.io-menu-fold)').each(function () {
        var $t = $(this),
            id = $t.attr('id');

        navWidth += $t.outerWidth();
        if (navWidth > surplusWidth) {
          // 检查是否已经存在相同的项，如果不存在则添加
          if ($foldSubMenu.find('>#' + id).length === 0) {
            $t.clone().removeClass('hide').appendTo($foldSubMenu);
          }
          $t.addClass('hide');
        } else {
          $t.removeClass('hide');
          // 从 fold 的子菜单中移除对应的项
          $foldSubMenu.find('>#' + id).remove();
        }
      });

      // 显示或隐藏 `fold` 菜单
      $fold.toggleClass('hide', $foldSubMenu.find('>li').length === 0);
    };

    var resize = function () {
      if (window.innerWidth >= 768) {
        navFolding();
      }
    };

    // 使用 debounce 处理窗口调整大小事件
    IO.window.on('resize', debounce(resize, 20, true));
    resize();
  });
};

$.fn.io_dominant_color = function () {
  var calculateBrightness = function ([r, g, b]) {
    return (r * 299 + g * 587 + b * 114) / 1000;
  };
  var isColorValid = function (rgb) {
    const [r, g, b] = rgb;
    const isBlack = (r < 40 && g < 40 && b < 40);  // 接近黑色
    const isWhite = (r > 215 && g > 215 && b > 215); // 接近白色
    return !(isBlack || isWhite);
  };
  
  return this.each(function () {
    var $this = $(this),
        $bg = $this,
        $bigMeta = $this.find('.big-meta');
    
    if ($this.data('inited'))
      return;
    $this.data('inited', true);
    
    var imgElement = $this.find('.big-color')[0];
    if (!imgElement) {
      console.error('imgElement is null');
      return;
    }

    var dominantColor = '';

    var updateColor = function () {
      // 检查color是否为空
      if (!dominantColor) {
        console.error('主色为空');
        return;
      }
      $bg.css('background-color', `rgb(${dominantColor[0]}, ${dominantColor[1]}, ${dominantColor[2]})`);

      var brightness = calculateBrightness(dominantColor);
      if (!IO.isDarkMode || brightness < 168) {
        var textColor = brightness < 168 ? '#ffffff' : '#383b3f';
        var mutedColor = brightness < 168 ? 'rgba(255, 255, 255, 0.7)' : 'rgba(20, 20, 20, 0.5)';
        var filter = brightness < 168 ? 'brightness(1.5)' : '';

        $bigMeta.css({
          '--this-color': textColor,
          '--this-muted-color': mutedColor,
          '--this-filter': filter,
          '--this-bg-color': `rgba(${dominantColor[0]}, ${dominantColor[1]}, ${dominantColor[2]}, 0.6)`
        });
      } else {
        $bigMeta.removeAttr('style').css('--this-filter', 'brightness(1.5)');
      }
    };

    // 提取主色调并设置背景及文本颜色
    var setDominantColor = function () {
      try {
        dominantColor = IO.colorThief.getColor(imgElement);
        updateColor(dominantColor);
      } catch (error) {
        console.error('获取图片主色调失败', error);
      }
    };

    // 添加主题模式切换事件,监听 IO.isDarkMode 变化
    $this.on('themeModeChanged', updateColor);

    // 确保图片加载完成后执行主色调提取
    if (imgElement.complete && imgElement.naturalHeight !== 0) {
      setDominantColor();
    } else {
      imgElement.onload = setDominantColor;
      imgElement.onerror = function () {
        console.error('图片加载失败，请检查图片路径或跨域问题');
      };
    }
    
  });
};

$.fn.load_weather = function () {
  if (this.length === 0) {
    return this;
  }
  
  var type = this.data('token') ? 'weather-v2' : 'weather';
  var functionObject = type == 'weather' ? "ThinkPageWeatherWidgetObject" : "SeniverseWeatherWidgetObject";

  ioRequire(type, function (T, n) {
    T[functionObject] = n;
    T[n] || (T[n] = function () {
      (T[n].q = T[n].q || []).push(arguments)
    });
    T[n].l = +new Date();
  }(window, "ioWidget"));
  
  return this.each(function () {
    var $this = $(this);

    var locale = $this.data('locale') || 'zh',
        token = $this.data('token') || false;

    if (token) {
      ioWidget('show', {
        flavor: "slim",
        location: "WX4FBXXFKE4F",
        geolocation: true,
        language: locale,
        unit: "c",
        theme: "auto",
        token: token,
        hover: "enabled",
        container: "io_weather_widget",
      });
    } else {
      ioWidget("init", {
        flavor: "slim",
        location: "WX4FBXXFKE4F",
        geolocation: "enabled",
        language: locale,
        unit: "c",
        theme: "chameleon",
        container: "io_weather_widget",
        bubble: "enabled",
        alarmType: "badge",
        color: "#888888",
        uid: "UD5EFC1165",
        hash: "2ee497836a31c599f67099ec09b0ef62",
      });

      ioWidget("show");
    }

    var intervalId = setInterval(function () {
      var $href = $this.find('a');
      if ($href.length) {
        $href.removeAttr('href');
        $this.find('.container_A8JAUuC').on('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
        });
        clearInterval(intervalId);
      }
    }, 200);

    setTimeout(function () {
      clearInterval(intervalId); 
    }, 10000);

  });
};

$.fn.io_hot_api = function () {

  return this.each(function () {
    var $this = $(this),
        $list = $this.find('.hotapi-list'),
        $refresh = $this.find('.hotapi-refresh'),
        $refresh_ico = $refresh.children('i'),
        $loading = $this.find('.hotapi-loading'),
        $title = $this.find('.title-name'),
        $slug = $this.find('.slug-name');
    
    var ruleId = $this.data('rule_id'),
        index = $this.data('index'),
        isIframe = $this.data('is_iframe'),
        apiType = $this.data('api_type'),
        cardType = $this.data('type'),
        url = '//ionews.top/api/get.php',
        data = { rule_id: ruleId, key: IO.apikey };
        
    if (apiType === 'json' || apiType === 'rss') {
      url = IO.ajaxurl;
      data = { type: apiType, action: 'get_hot_data', id: ruleId }
    }
    if (cardType === 'tab') {
      var $parent = $this.closest('.hotapi-tab-card');
      $title = $parent.find(`[data-target=".hotapi-${apiType}-${index}"]`).find('.title-name');
      if (!$parent.attr('init')) {
        $parent.attr('init', 'true');
        var $btn = $parent.find('.hotapi-tab-btn');
        $btn.on('click', function () {
          var $t = $(this);
          $t.addClass('active').siblings().removeClass('active');
          $parent.find($t.data('target')).addClass('active').siblings().removeClass('active');
          $btn.parent().tabToCenter();
        });
      }
      
    }
    var colors = {
      1: 'vc-l-red',
      2: 'vc-l-yellow',
      3: 'vc-l-purple',
    };

    var createListItem = function(item, type) {
      var listClass = type === 'taoke' ? 'flex-column' : '';
      var rankColor = colors[item['index']] || '';
      var link = isIframe ? item['link'].replace(/^https?:/, "") : item['link'];
      var linkAttr = isIframe ? `data-fancybox data-type="iframe" data-src="${link}"`
        : `js-href="${link}" target="_blank" rel="external noopener nofollow"`;

      switch (type) {
        case 'taoke':
          var platform = `<div class="d-flex align-items-center mt-1 text-xs"><span class="badge vc-l-blue text-center">${item['platform']}</span><span class="ml-auto white-nowrap text-muted">${item['hot']}</span></div>`;
          break;
        case 'hot':
          var platform = `<div class="ml-auto hot-heat d-none d-md-block white-nowrap text-muted pl-1">${item['hot']}</div>`;
          break;
        default:
          var platform = '';
          break;
      }

      return `<li class="d-flex ${listClass} text-sm mb-2">
            <div class="w-100">
              <badge class="hotapi-rank ${rankColor}">${item['index']}</badge><a href="javascript:;" class="ml-1 word-break" ${linkAttr}>${item['title']}</a>
            </div>
            ${platform}
          </li>`;
    };

    var getList = function () {
      $.get(url, data)
        .done(function (response) {
          if (!response.state) {
            $refresh_ico.removeClass('icon-spin');
            $loading.html(response.data).delay(3500).fadeOut(200);
            return;
          }
          
          var { title, subtitle, type, data } = response;
          
          $title.text(title);
          $slug.text(subtitle);

          var html = data.map(item => createListItem(item, type)).join('');
          $list.html(html);

          $loading.fadeOut(200);
          $refresh_ico.removeClass('icon-spin');
        })
        .fail(function () {
          $refresh_ico.removeClass('icon-spin');
          $loading.html(IO.localize.networkError).delay(3500).fadeOut(200);
        });
    };

    $refresh.on('click', function () {
      $refresh_ico.addClass('icon-spin');
      $loading.html('<i class="iconfont icon-loading icon-spin text-32"></i>').fadeIn(200);
      getList();
      return false;
    });

    getList();

  });
};

$.fn.io_multiple_dropdown = function () {
  return this.each(function () {
    var $this = $(this),
        $select = $this.find('select'),
        $dropdownContainer = $this.find('.multiple-select'),
        $selectedItems = $this.find('.selected-input'),
        $multipleDropdown = $this.find('.multiple-dropdown'),
        $dropdownList = $this.find('.dropdown-list'),
        $selectAll = $this.find('.select-all'),
        $clearAll = $this.find('.clear-all');

    var selectedOrder = [];
    var selectedValue = [];
    var placeholder = $this.data('placeholder') || 'Select options';
    var maxCount = $this.data('max-count') || 0;
    
    // 更新已选中的文本
    var updateSelectedText = function () {
      if (selectedOrder.length > 0) {
        var output = '';
        selectedOrder.forEach(function (item) {
          output += '<span class="selected-item">' + item.trim() + '</span>';
        });
        $selectedItems.html('<div class="selected-list">' + output + '</div>');
      } else {
        $selectedItems.text(placeholder);
      }
    }
    // 更新 select 的 value 值
    var updateSelectValue = function () {
      if (JSON.stringify($select.val()) != JSON.stringify(selectedValue)) {
        $select.val(selectedValue).trigger('input');
      }
    }
    
    // 使用复选框和选项填充下拉列表
    $select.find('option').each(function () {
      var $option = $(this);
      var $listItem = $('<li class="dropdown-item" data-value="' + $option.val() + '">' + $option.text() + '</li>');

      // 将复选框状态与选择选项同步
      if ($option.is(':selected')) {
        $listItem.addClass('selected');
        selectedOrder.push($option.text());  // 加入已选定的项目
        selectedValue.push($option.val());
      }

      $dropdownList.append($listItem);
    });
    updateSelectedText();

    // 单击时切换下拉菜单
    $selectedItems.on('click', function () {
      var windowHeight = $(window).height();
      var dropdownHeight = $multipleDropdown.outerHeight();
      var scrollTop = IO.window.scrollTop();
      var selectedOffsetTop = $selectedItems.offset().top;
      var selectedHeight = $selectedItems.outerHeight();
      var spaceBelow = windowHeight + scrollTop - (selectedOffsetTop + selectedHeight);

      if (spaceBelow < dropdownHeight) {
        $multipleDropdown.css({
          bottom: '100%'
        });
      } else {
        $multipleDropdown.css({
          bottom: 'auto'
        });
      }

      $multipleDropdown.slideToggle(100);
      $selectedItems.toggleClass('active');
    });

    // 更新复选框更改时的选定项目
    $dropdownList.on('click', '.dropdown-item', function () {
      var $item = $(this);
      var value = $item.data('value');
      var text = $item.text();

      if (maxCount > 1 && selectedOrder.length >= maxCount && !$item.hasClass('selected')) {
        return;
      }
      if (maxCount == 1) {
        var $old_item = $dropdownList.find('.selected');
        if ($old_item && $old_item[0] != $item[0]) {
          $item.addClass('selected').siblings().removeClass('selected');
          selectedOrder = [text];
          selectedValue = [value];
        }
      } else {
        if ($item.hasClass('selected')) {
          $item.removeClass('selected');
          selectedOrder = selectedOrder.filter(function (item) {
            return item !== text;  // 删除项目
          });
          selectedValue = selectedValue.filter(function (item) {
            return item !== value;  // 删除项目
          });
        } else {
          $item.addClass('selected');
          selectedOrder.push(text);  // 加入项目
          selectedValue.push(value);
        }
      }
      updateSelectValue();
      updateSelectedText();
    });
    
    $selectAll.on('click', function () {
      $dropdownList.find('.dropdown-item').each(function () {
        var $item = $(this);
        if (!$item.hasClass('selected')) {
          $item.addClass('selected');
          selectedOrder.push($item.text());
          var value = $item.data('value');
          selectedValue.push(value);
        }
      });
      updateSelectValue();
      updateSelectedText(); 
    });

    $clearAll.on('click', function () {
      $dropdownList.find('.dropdown-item').removeClass('selected');
      selectedOrder = [];
      selectedValue = [];
      updateSelectValue();
      updateSelectedText();
    });
    
    // 如果在外部单击则隐藏下拉菜单
    IO.document.on('click', function (event) {
      if (!$dropdownContainer.is(event.target) && $dropdownContainer.has(event.target).length === 0) {
        $multipleDropdown.slideUp(100);
        $selectedItems.removeClass('active');
      }
    });
  });
};
})(jQuery);
function getPredefinedContent(type) {
  let placeholder = '';
  if (type == 'title') {
    placeholder = `
    <div class="placeholder-posts null-${type}">
      <span class="--image"></span>
      <span class="--title"></span>
    </div>`;
  } else {
    placeholder = `
    <div class="placeholder-posts null-${type}">
      <div class="p-header">
        <span class="--image"></span>
      </div>
      <div class="p-meta">
        <span class="--title"></span>
        <div class="--meta"><span></span><span></span><span></span></div>
      </div>
    </div>
  `;
  }

  return placeholder.repeat(6);
};


(function ($) {
  IO.document.ready(function () {
    autoFun();
    readyRun();

    $('#layout_aside').io_sticky_aside();

    $('[select-dropdown]').io_select_dropdown();
    $('[data-toggle-div]').io_toggle_div();
    $('.navbar-header').io_nav_auto_fold();
    $('.io-slider-tab').io_slider_tab();
    $('#search').io_head_search();
    $('#io_weather_widget').load_weather();
    $('.hotapi-card').io_hot_api();
    $('.io-multiple-dropdown').io_multiple_dropdown();
  });
  

  function initVar() {
    IO.isDarkMode = IO.html.hasClass('io-black-mode');
    IO.isDominantColor = $('.big-posts').length > 0;
    IO.isFooterVisible = true;
    IO.isHeaderVisible = true;
    if (!IO.asideObserver) {
      IO.asideObserver = newObserver(800);
    }
    if (!IO.autoLoadObserver) {
      IO.autoLoadObserver = $('.auto-load-next').length ? newObserver(10) : null;
    }
  }

  function loadScript() { 
    IO.isDominantColor && ioRequire(['color-thief'], function () {
      if (!IO.colorThief) {
        console.log('Initialize ColorThief');
        IO.colorThief = new ColorThief();
      }
      if (!$('.lazy').length) {
        $('.big-posts').io_dominant_color();
      }
    });
    
    $('#chart-container').length && ioRequire('echarts');
    $('[captcha-type]').length && ioRequire('captcha'); 
    $('[data-fancybox]').length && ioRequire('fancybox');
    $('#comment').length && ioRequire('comments');
    $('.swiper').length && ioRequire('swiper', initSwiper);
    $('.new-post-content').length && ioRequire('new-post');
  }
  /**
   * 初始化
   */
  function readyRun() {
    setUserFootprint();
    $('.ajax-footprint').length && initFootprint();

    isInViewPort("footer", IO.asideObserver, function (isVisible) {
      IO.isFooterVisible = isVisible;
    });
    isInViewPort(".header-calculate", IO.asideObserver, function (isVisible) {
      IO.isHeaderVisible = isVisible;
    });

    $('.dependency-box').dependency();

    $("#system_popup_ad").each(function () {
      var $this = $(this);
      var id = $this.data('id');
      var ex = $this.data('ex');
      var delay = $this.data('delay');
      if ($.cookie("system_popup_ad") != id) {
        setTimeout(function () {
          $this.modal("show");
          if (ex > 0) {
            $.cookie("system_popup_ad", id, { path: "/", expires: ex });
          }
        }, delay)
      }
    });
    
    if ($('.io-footer-tools').length || $('.main-header').length || $('.tabbar-go-up').length) {
      var $toolsGoUp = $('.io-footer-tools .go-to-up');
      var $tabbarGoUp = $('.tabbar-go-up .go-to-up');
      var $header = $('.main-header');
      var scrollTimeout;

      if ($tabbarGoUp.length) {
        $toolsGoUp.remove();
      }

      var scrollEvent = function () {
        var scrollTop = IO.window.scrollTop();
        if (scrollTop >= 168) {
          $toolsGoUp.fadeIn(200);
          $tabbarGoUp.addClass('show');
        } else {
          $toolsGoUp.fadeOut(200);
          $tabbarGoUp.removeClass('show');
        }

        if (scrollTop > 40) {
          $header.addClass('scroll');
        } else {
          $header.removeClass('scroll');
        }
      };

      var handleScroll = function () {
        scrollEvent();
      
        if (IO.window.width() < IO.homeWidth) {
          IO.body.addClass('scroll-ing');
          clearTimeout(scrollTimeout);
          scrollTimeout = setTimeout(function () {
            IO.body.removeClass('scroll-ing');
          }, 500);
        }
      };

      scrollEvent();
      IO.window.scroll(handleScroll);
    }

    //未开启详情页计算访客方法
    IO.document.on('click', 'a.is-views[data-id]', function () {
      var $this = $(this);
      addUserFootprint($this.data('id'), 'sites');
      $.get(IO.ajaxurl, {
        action: 'io_postviews',
        postviews_id: $this.data('id'),
      }, function (data) {
        //console.log(data);
      });
    });
    //复制
    IO.document.on('click', "[data-clipboard-text]", function (e) {
      var $this = $(this);
      var text = $this.data('clipboard-text');
      if ($this.hasClass('down_count')) {
        $.ajax({
          type: "POST",
          url: IO.ajaxurl,
          data: $this.data(),
          success: function (n) {
            $('.down-count-text').html(n);
          }
        });
      }
      if (text) {
        copyText(text, function () {
          alert(IO.localize.extractionCode);
        }, function () {
        }, this);
      }
    });
    
    $('.sidebar-tools').length && ioRequire('sticky-sidebar', function () {
      $('.sidebar-tools').theiaStickySidebar({
        additionalMarginTop: 80,
        additionalMarginBottom: 20
      });
    });

    // 自动加载内容 GET
    $('.ajax-auto-get').each(function () {
      var $this = $(this);
      var $target = $($this.data('target'));
      var url = $this.attr('href');
      if (!url) {
        url = $this.data('href');
      }
      $.get(url, null, function (data, status) {
        $target.html(data);
        ioAutoFun();
      });
    });

    // 自动加载内容 POST
    $('.ajax-auto-post').each(function () {
      const $this = $(this);
      const $ico = $this.find('.icon-refresh');
      const $target = $($this.data('target'));
      const isAuto = $this.hasClass('auto');
      const placeholder = isAuto ? $target.html() : '<div class="' + $target.find('.posts-row').attr('class') + '">' + getPredefinedContent($this.data('style')) + '</div>';
      const url = $this.attr('href') || $this.data('href');
      var post = function () {
        if ($ico.hasClass('icon-spin')) {
          return false;
        }
        
        $target.html(placeholder);
        
        $ico.addClass('icon-spin');
        $.post(url, $this.data(), function (data, status) {
          $target.html(data);
          $ico.removeClass('icon-spin');
          ioAutoFun();
        }, "html");
        return false;
      }
      if ($this.hasClass('click')) {
        $this.on('click', post);
      }
      if (isAuto) {
        post();
      }
    });
    // 小工具加载更多
    $('.ajax-page-post').each(function () {
      const $this = $(this);
      const $target = $($this.data('target'));
      const isAuto = $this.hasClass('auto');
      const placeholder = isAuto ? $target.html() : getPredefinedContent($this.data('style'));
      const url = $this.attr('href') || $this.data('href');
      var post = function () {
        if ($this.hasClass('loading')) {
          return false;
        }

        $this.addClass('loading');
        const currentPage = parseInt($this.data('page'), 10);
        if (currentPage > 1) {
          $target.append(placeholder);
        } else {
          $target.html(placeholder);
        }
        $.ajax({
          url: url,
          data: $this.data(),
          type: 'POST',
          success: function (data) {
            $target.find('.placeholder-posts').remove();
            $target.append(data);

            if (data.includes('nothing-msg')) {
              $this.remove();
            } else {
              $this.data('page', currentPage + 1);
            }
            $this.removeClass('loading');
            ioAutoFun();
          },
          error: function (error) {
            $this.removeClass('loading');
            console.error('error:', error);
          }
        });
        return false;
      }
      
      $this.on('click', post);
      
      if (isAuto) {
        post();
      }
      if ($this.hasClass('auto-load-next') && IO.autoLoadObserver) {
        isInViewPort($this[0], IO.autoLoadObserver, function (isVisible) {
          if (isVisible && $this.data('page') < 3 ) {
            $this.trigger('click');
          }
        });
      }
    });
    $('.list-ajax-by').each(function () {
      const $this = $(this);
      const $parent = $($this.data('target'));
      const $target = $parent.find('.ajax-page-post');
      const $nav = $this.closest('.list-selects');
      $this.on('click', function () {
        if ($this.hasClass('active')) return;
        const $btn = $(this);
        $btn.addClass('active').siblings().removeClass('active');
        $nav.tabToCenter();
        $target.data({
          'page': 1,
          'orderby': $this.data('type'),
        }).trigger('click');
      });
    });

    setTimeout(function () {
      /// 自动滚动到锚点
      var hash = window.location.hash;
  
      if (!hash || hash.includes('=')) return;
      
      var $target = $(hash);
  
      if (!$target.length) return;
  
      var $smoothLink = $('a.smooth[href="' + hash + '"]');
  
      if ($smoothLink.length) {
        $smoothLink.click();
      } else {
        $("html, body").animate({
          scrollTop: $target.offset().top - 95
        }, 500);
      }
    }, 300);
    


    if ($('.sites-seo-load').length) {
      var $this = $('.sites-seo-load');
      $.get(IO.ajaxurl + "?action=get_sites_seo&url=" + $this.data('url'), null, function (data, status) {
        if (data.errcode == "0") {
          var _html = "";
          var _name = "";
          var _url = $this.data('go_to');
          data.data.result.forEach(list => {
            switch (list.type) {
              case "BaiduPCWeight":
                _name = "百度PC";
                break;
              case "BaiduMobileWeight":
                _name = "百度移动";
                break;
              case "HaoSouWeight":
                _name = "360";
                break;
              case "SMWeight":
                _name = "神马";
                break;
              case "TouTiaoWeight":
                _name = "头条";
                break;
            }
            _html += '<a class="sites-weight ' + list.type + '" href="' + _url + '" title="' + _name + '" target="_blank" rel="external nofollow"><span>' + list.weight + '</span></a>';
          });
          $this.html(_html);
        }
      });
    }
  }
  
  function autoFun() {
    initVar();
    loadScript();
    
    $('.footer-stick').io_stick_footer();

    // 网址块提示 
    if (isPC()) {
      $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        container: 'body',
      });
    } else {
      $('.qr-img[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        container: 'body',
      });
    }
    $('[contenteditable="true"]').attr('contenteditable', 'false');
  }

  function initSwiper() {
    if (IO.initSwiper) return;
    IO.initSwiper = true;

    var swiper_post = new Swiper(".swiper-post-module", {
      autoplay: {
        disableOnInteraction: false,
      },
      lazy: {
        loadPrevNext: true,
      },
      slidesPerView: 1,
      loop: true,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      }
    });
    var swiper_widgets = new Swiper(".swiper-widgets", {
      autoplay: {
        disableOnInteraction: false,
        delay: 5000,
      },
      effect: 'fade',
      thumbs: {
        swiper: {
          el: '.swiper-widgets-thumbs',
          slidesPerView: "auto",
          freeMode: true,
          centerInsufficientSlides: true,
        },
        autoScrollOffset: 1,
      },
      on: {
        init: function (swiper) {
          var slide = this.slides.eq(0);
          slide.addClass('anim-slide');
        },
        transitionStart: function () {
          for (var i = 0; i < this.slides.length; i++) {
            var slide = this.slides.eq(i);
            slide.removeClass('anim-slide');
          }
        },
        transitionEnd: function () {
          var slide = this.slides.eq(this.activeIndex);
          slide.addClass('anim-slide');
        },
      }
    });
    var swiper_term_content = new Swiper(".swiper-term-content", {
      nested: true,
      slidesPerView: "auto",
      freeMode: true,
      mousewheel: true,
      watchSlidesProgress: true,
      resistanceRatio: false
    });

    if (!$('.lazy').length) {
      lazyLoadInstance.update();
    }
  }
  window.ioAutoFun = debounce(autoFun, 100);
  $('.go-to-up').click(function () {
    $("html, body").animate({
      scrollTop: 0
    }, 500);
    return false;
  });
  
  IO.document.on('click', '.mobile-nav .icon-arrow-b', function (e) {
    e.preventDefault();
    var $this = $(this);
    var $li = $this.closest('li');
    var $subMenu = $li.find('.sub-menu').first();
    $li.toggleClass('show');
    $subMenu.slideToggle(200);
  });

  IO.document.on('click', '[js-href]', function (e) {
    e.preventDefault();
    var $this = $(this);
    var url = $this.attr('js-href');
    var target = $this.attr('target');
    if (url) {
      if (target) {
        window.open(url,'_blank');
      } else {
        window.location.href = url;
      }
    }
  });

  /* ----- tab路由 ----- */
  IO.document.on('shown.bs.tab', '[ajax-route]', function () {
    var $this = $(this);
    if ($this.attr('onpopstate')) {
      $this.attr('onpopstate', false);
    } else {
      var route = $this.attr('href') || $this.attr('route') || $this.attr('ajax-route');
      var tab_id = $this.attr('data-target');
      if (route) {
        var title = document.title || $this.text();
        history.pushState(
          {
            tab_id: tab_id,
          },
          title,
          route
        );
      }
    }
  });
  IO.document.on('hide.bs.tab', '[ajax-route]', function () {
    var $this = $(this);
    if (!history.state) {
      var route = $this.attr('href') || $this.attr('route') || $this.attr('ajax-route');
      var tab_id = $this.attr('data-target');
      if (route) {
        history.replaceState(
          {
            tab_id: tab_id,
          },
          null,
          route
        );
      }
    }
  });
  window.onpopstate = function (event) {
    var tab = event.state && $('[data-toggle="tab"][data-target="' + event.state.tab_id + '"]');
    if (tab && tab.length) {
      tab.attr('onpopstate', true).click();
    }
  };
  /* ----- tab路由 END ----- */
  
  // TAB ajax加载文章
  IO.document.on('click', '[ajax-tab]', function () { 
    var $this = $(this);
  
    //$this.addClass('active').siblings().removeClass('active').parent().tabToCenter();
    $this.parent().tabToCenter();
    
    if ($this.hasClass('loaded'))
      return false;

    $this.addClass('loaded');

    var page = $this.data('target');
    return ajaxPosts($this, page);
  });
  // TAB 页面加载
  IO.document.on('click', '[ajax-tab-page]', function () {
    var $this = $(this);
    var $target = $($this.data('target'));
    var backUrl = $this.data('route_back') || window.location.href;

    $this.addClass('active').siblings().removeClass('active');
    if (IO.window.width() < 768) {
      var title = $this.text();
      var $drawerHeader = $('<div class="drawer-header"><div class="drawer-close"><i class="iconfont icon-arrow-l"></i></div><div class="drawer-title">' + title + '</div></div>');
      $drawerHeader.on('click', '.drawer-close', function () {
        IO.body.removeClass('show-tab-page');
        history.replaceState(null, document.title, backUrl);
        setTimeout(function () {
          $drawerHeader.remove();
        }, 300);
        $this.removeClass('active');
      });
      IO.body.append($drawerHeader);
      setTimeout(function () {
        IO.body.addClass('show-tab-page');
      }, 10);
    }

    if (!$this.hasClass('loaded')) {
      $this.addClass('loaded');

      if ($this.hasClass('disabled'))
        return false;
      $target.length && $this.addClass('disabled');

      var url = $this.attr('href');
      // 使用$.load()加载页面
      $target.load(url + ' ' + $this.data('target')+ ' .load-ajax-card', function () {
        $this.removeClass('disabled');
        $target.find('.ajax-footprint').length && initFootprint();
      });
    }
  });
  if (IO.window.width() < 768 && $('.uc-content-body').length) { 
      if (IO.body.hasClass('user-center-')) return;
      $('[ajax-tab-page].loaded').click();
  }

  IO.document.on('click', '.ajax-click-post', function () { 
    var $this = $(this);
    if ($this.hasClass('is-tab-btn')) {
      $this.addClass('active').siblings().removeClass('active');
    }
    getPosts($this, $this.data(), "POST");
    return false;
  });
  
  // 文章页面加载更多、切换类型
  IO.document.on('click', '.ajax-posts-load', function () {
    var $this = $(this);
    if ($this.hasClass('disabled')) return false;

    $this.addClass('disabled');

    var page = '.ajax-load-page';
    var card = $this.data('card') || '.ajax-item';
    var replace = ['.posts-nav'];

    if ($this.hasClass('is-tab-btn')) {
      $this.addClass('active').siblings().removeClass('active');
    }
    
    if ($this.attr('ajax-method') === 'card') {
      $this.parent().tabToCenter();
    } else if ($this.attr('ajax-method') === 'page') {
      page = $this.data('page') || '.ajax-load-page';
      card = '';
      replace = $this.data('replace') || ['.page-head-content'];
    }
    return ajaxPosts($this, page, card, replace);
  });
  
	//清空搜索关键词
  IO.document.on('click', '.trash-history-search', function (e) {
    var $this = $(this);
		if (confirm("确认要清空全部搜索记录？") == true) {
			$this.closest('.search-keywords-box').slideUp().delay(1000, function () {
				$(this).remove();
			});
			$.cookie('io_history_search', null);
		}
  });
  
	IO.document.on('click', '.search-ico-btn', function () {
		var $search_form = $('.search-body form');
		if ($search_form.length) {
			setTimeout(function () {
				$search_form.find('[name="s"]').focus();
			}, 500);
		}
  });
  
  IO.document.on('click', '.full-windows', function () {
    var $container = $('.switch-container');
    $container.toggleClass('container container-fluid');
    if ($container.hasClass('container-fluid')) {
      IO.body.addClass('full-container');
    } else {
      IO.body.removeClass('full-container');
    }
    IO.isAsideInitEvent && IO.updateStickyAside({type: 'user', originalEvent: 'btn click'});
  });
    

  IO.document.on('click', 'a.smooth', function (e) {
    e.preventDefault();
    var $this = $(this);
    var target = $this.attr("href");

    if (target.substr(0, 1) == "#" && $(target).length) {
      $("html, body").animate({
        scrollTop: $(target).offset().top - 95
      }, 500);
    }
    if (target == '#search') {
      setTimeout(function () {
        $('#search-text').focus();
      }, 600);
    }
    if ($this.hasClass('change-href')) {
      var menu = $('li' + target);
      if (menu[0]) {
        menu.click();
      }
    }
  });

  IO.document.on('click', '.add-favorites', function (e) {
    e.preventDefault();
    var url = window.location.href;
    var title = document.title;

    try {
      window.external.AddFavorite(url, title);
    } catch (e) {
      alert("请使用 Ctrl+D (Windows) 或 Command+D (Mac) 手动将此页面添加到收藏夹。");
    }
  });

  IO.document.on("click", ".modal .io-ajax-price-get", function () {
    var $this = $(this);
    var url = $this.attr('href');
    if (!url) {
      url = $this.data('href');
    }
    var $parent = $this.parent();
    if ($parent.hasClass('disabled') || $this.attr('disabled')) {
      return false;
    }

    $parent.children().attr('disabled', false);
    $this.attr('disabled', true);
    $parent.addClass('disabled');

    var $form = $this.closest('form');
    var $target = $form.find($this.data('target'));
    var loading = '<div class="d-flex align-items-center justify-content-center bg-o-muted position-absolute io-radius h-100 w-100"><i class="iconfont icon-loading icon-spin text-32"></i></div>';
    $target.append(loading);
    $.get(url, null, function (data, status) {
      var _t = $(data);
      $target.html(_t);
      _t[0].click();
      $parent.removeClass('disabled');
    });

    return false;
  });

  IO.document.on("click", ".io-ajax-modal", function () {
    var $this = $(this);
    var modal = ioModal($this);
    $.ajax({
      type: 'POST',
      url: IO.ajaxurl,
      data: $this.data(),
      success: function (data) {
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
      },
      error: function () {
        modal.modal('hide');
        showAlert({ "status": 4, "msg": IO.localize.networkError });
      }
    });
    return false;
  });
  IO.document.on("click", ".io-ajax-modal-get", function () {
    var $this = $(this);
    var $url = $this.attr('href');
    if (!$url) {
      $url = $this.data('href');
    }
    var $modal = ioModal($this);
    $.get($url, null, function (data, status) {
      $modal.find('.io-modal-content').html(data).slideDown(200, function () {
        $modal.find('.loading-anim').fadeOut(200);
        var height = $(this).outerHeight();
        var content = $modal.find('.modal-content');
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
      $modal.find('.dependency-box').dependency();
      $modal.find('.initiate-pay').length && ioRequire('pay');
      
      if ($this.data('modal_id') == 'pay_publish') {// 增加标记
        $modal.find('.io-close').on('click', function () {
          $modal.attr('is-user', true);
        });
      }
    });
    return false;
  });
    
  IO.document.on("click", ".io-posts-like", function () {
    var $this = $(this);
    var $icon = $this.find('i');
    var $count = $this.find('.star-count');

    var isIconChange = $icon.data('class');

    if ($this.hasClass('disabled'))
      return false;

    $this.addClass('disabled');
    
    var requestData = {
      action: 'posts_like',
      type: $this.data('type'),
      post_type: $this.data('post_type'),
      post_id: $this.data('post_id'),
      ticket: $this.data('ticket')
    };

    $.ajax({
      type: 'POST',
      url: IO.ajaxurl,
      data: requestData,
      dataType: 'json',
      success: function (n) {
        var d = n.data || n;
        if (d.type === 1) {
          if (d.data.action === 'add') {
            $this.addClass('liked');
          } else {
            $this.removeClass('liked');
          }

          isIconChange && $icon.toggleClass(isIconChange);
          
          $count.html(d.data.count);
        }
        showAlert(n.data)
        $this.removeClass('disabled');
      },
      error: function () {
        showAlert({ "status": 4, "msg": IO.localize.networkError });
        $this.removeClass('disabled');
      }
    });
    return false;
  });

  IO.document.on('click', '.ajax-form-captcha #submit', function () {
    var $this = $(this);
    var $form = $this.closest('form');
    captcha_ajax($this, '', function (n) {
      if (n.status == 1) {
        $form[0].reset();
        $form.find('.image-captcha').click();
      }
    });
    return false;
  });

  IO.document.on('click', '[data-for]', function () {
    var $this = $(this);
    var $form = $this.closest('form');
    
    var originText = $this.html();
    var _for = $this.data('for');
    var value = $this.data('value');

    var enable = $this.data('enable');
    var disable = $this.data('disable');
    if (enable) {
      $form.find(enable).show();
    }
    if (disable) {
      $form.find(disable).hide();
    }

    var $group = $this.closest('[for-group]');
    if ($group.length) { // 选项组，如果选项不在同级，需要共同的父级
      $group.find('[data-for="' + _for + '"]').removeClass('active');
    } else {
      $this.siblings().removeClass('active');
    }
    $this.addClass('active');

    $form.find('input[name="' + _for + '"]').val(value).trigger('change');

    $form.find('span[name="' + _for + '"]').html(originText);
  });

  IO.document.on("input", ".get-ajax-custom-product-val", debounce(function () {
    var $this = $(this);
    var url = $this.data('href');
    if ($this.hasClass('disabled')) {
      return false;
    }
    $this.addClass('disabled');
    var $form = $this.closest('form');
    var $target = $form.find($this.data('target'));
    var hh = '<i class="iconfont icon-point"></i>';
    var loading = '<i class="iconfont icon-loading icon-spin"></i>';
    $target.html(loading);
    $.get(url, $form.serializeObject(), function (data, status) {
      if (data.msg) {
        alert.status = data.status ? data.status : (data.error ? 4 : 1);
        alert.msg = data.msg;
        showAlert(alert);
        $target.html(hh);
      } else {
        $target.html(data);
      }
      $this.removeClass('disabled');
    });
    return false;
  }, 800));
  
  /**  登录表单  **/
  IO.document.on('click', "#wp_login_form #submit", function () {
    var _this = $(this);
    captcha_ajax(_this, '', function (m) {
      if (m.status == 1) {
        if (!m.goto) {
          window.location.reload();
        }
      }
    });
    return false;
  });
  IO.document.on("input propertychange","#user_email",function(){
      if($(this).val().length > 4)
          $(".verification").slideDown();
  });
  IO.document.on('click', '.password-show-btn', function () {
    var $this = $(this);
    var $ico = $this.find('.iconfont');
    var $input = $this.siblings('input');
    if ($this.data('show') == "0") {
      $ico.removeClass("icon-chakan-line");
      $ico.addClass("icon-hide-line");
      $input.attr('type', 'text');
      $this.data('show', 1);
    } else {
      $ico.removeClass("icon-hide-line");
      $ico.addClass("icon-chakan-line");
      $input.attr('type', 'password');
      $this.data('show', 0);
    }
  });
  IO.document.on("click", ".btn-token", function () {
    var $this = $(this);

    if ($this.attr('disabled')) {
      return false;
    }

    var $form = $this.closest('form');
    var $btn = $form.find(".btn-token");
    var $email = $form.find('#user_email');
    if (!$email.length) {
      $email = $form.find('.mm_mail');
    }

    var countdown = 60;
    var originalText = $this.html();

    var startCountdown = function () {
      if (countdown > 0) {
        $btn.html(countdown + IO.localize.reSend);
        countdown--;
        setTimeout(startCountdown, 1000);
      } else {
        $btn.html(originalText).attr('disabled', false);
        countdown = 60;
      }
    }

    var submitToken = function () {
      captcha_ajax($this, '', function (n) {
        $email.attr("readonly", "readonly");
        if (n.status == 1) {
          $btn.attr('disabled', true);
          startCountdown();
        } else {
          $email.removeAttr("readonly");
        }
      });
      return false;
    }
    
    submitToken();
  });
  /**  登录表单  **/


  /** 足迹 **/
  function addUserFootprint(postId, postType) {
    IO.postData = {
      postId: postId,
      postType: postType
    };
    setUserFootprint();
  }
  /**
   * 记录用户足迹
   */
  function setUserFootprint() {
    if (typeof IO.postData !== 'undefined' && IO.uid) {
      var userFootprint = localStorage.getItem('ioUserFootprint_' + IO.uid);
  
      if (!userFootprint) {
        userFootprint = [];
      } else {
        userFootprint = JSON.parse(userFootprint);
      }
  
      var currentPage = {
        post_id: IO.postData.postId,
        post_type: IO.postData.postType,
        visit_time: new Date().toISOString()
      };
  
      // 是否存在记录中
      var exists = userFootprint.some(item => item.post_id === currentPage.post_id);
  
      if (!exists) {
        userFootprint.push(currentPage);
      } else {
        // 更新访问时间
        userFootprint.forEach(item => {
          if (item.post_id === currentPage.post_id) {
            item.visit_time = new Date().toISOString();
          }
        });
      }
  
      // 删除超过7天的记录
      var sevenDaysAgo = new Date();
      sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
  
      userFootprint = userFootprint.filter(item => new Date(item.visit_time) >= sevenDaysAgo);
      localStorage.setItem('ioUserFootprint_' + IO.uid, JSON.stringify(userFootprint));
    }
  }
  /** 
   * 按类型筛选记录
   */
  function getUserFootprintByType(type) {
    var userFootprint = localStorage.getItem('ioUserFootprint_' + IO.uid);

    if (!userFootprint) {
      return [];
    } else {
      userFootprint = JSON.parse(userFootprint);
    }

    // 筛选
    var filteredFootprint = userFootprint.filter(item => item.post_type === type);

    // 时间排序 降
    filteredFootprint.sort((a, b) => new Date(b.visit_time) - new Date(a.visit_time));

    return filteredFootprint;
  }
  IO.document.on('click', '.footprint-clear-all', function () {
    if (confirm(IO.localize.clearFootprint)) {
      localStorage.removeItem('ioUserFootprint_' + IO.uid);
      location.reload();
    }
  });
  function initFootprint() {
    $('.ajax-footprint').each( function () { 
      var $this = $(this);
      $this.on('click', function () {
        var type = $this.data('type');
        IO.footprintData = getUserFootprintByType(type);
        getFootprint($this);
        return false;
      });
      if ($this.hasClass('auto')) {
        $this.removeClass('auto').trigger('click');
      }
    });
    IO.document.on('click', '.footprint-next', function () {
      var $this = $(this);
      getFootprint($this);
      $this.remove();
      return false;
    });
  }
  function getFootprint($this) {
    if ($this.hasClass('disabled')) return false;
    $this.addClass('disabled active').siblings().removeClass('active');
    
    var $target = $this.closest('.footprint-card').find('.ajax-panel');
    var type = $this.data('type');
    var action = $this.data('action');
    var style = $this.data('style');
    var columns = $this.data('columns');

    var page = $this.data('page') || 1;
    var group = $this.data('group') || [];

    var placeholder = getPredefinedContent(type + '-' + style);

    $target.removeClass(function (index, className) {
      return (className.match(/\brow-col-\S+/g) || []).join(' ');
    });
    $target.addClass(columns);
    if (page > 1) {
      $target.append(placeholder);
    } else {
      $target.html(placeholder);
    }

    $.ajax({
      url: IO.ajaxurl,
      data: {
        action: action,
        type: type,
        style: style,
        columns: columns,
        posts: IO.footprintData,
        group: group,
        page: page
      },
      dataType: 'html',
    }).done(function (response) {
      if (page > 1) {
        $target.find('.placeholder-posts').remove();
        $target.append(response);
      } else {
        $target.html(response);
      }
      ioAutoFun();
    }).fail(function () {
      showAlert({ "status": 4, "msg": IO.localize.networkError });
    }).always(function () {
      $this.removeClass('disabled');
    });
  }
  /** 足迹 END **/


  //夜间模式
  IO.document.on('click', '.switch-dark-mode', function (event) {
    // 切换模式
    IO.html.toggleClass('io-black-mode');
    // 获取当前是否是黑暗模式
    IO.isDarkMode = IO.html.hasClass('io-black-mode');
    switchThemeMode(true);
  });


  IO.document.on('click', ".open-login", function () {
    var _this = $(this);
    if ($('#user_agreement')[0] && !$('#user_agreement').is(':checked')) {
      showAlert({ "status": 2, "msg": IO.localize.userAgreement });
      return false;
    }
  });
  
})(jQuery);

document.addEventListener('DOMContentLoaded', function () {
  //$('#layout_aside').io_sticky_aside();
});

/**
 * 获取文章列表
 * @param {jQuery} $this 
 * @param {Object} data  请求数据
 * @param {string} type  POST | GET
 * @param {function} callback 
 * @returns {boolean} 返回false，防止链接的默认行为，如页面跳转
 */
function getPosts($this, data, type, callback) {
  if ($this.hasClass('disabled')) return false;
  $this.addClass('disabled');

  var $parent = $this.closest('.ajax-parent');
  if (!$parent.length) {
    showAlert({ "status": 4, "msg": IO.localize.parameterError });
    return false;
  }

  data = data || $this.data();
  type = type || 'GET';

  var $target = $parent.find($this.data('target'));
  var url = $this.attr('href') || $this.data('href');
  var placeholder = getPredefinedContent($this.data('style'));

  if (url === 'javascript:;') {
    url = IO.ajaxurl;
  }
  
  $target.html(placeholder);

  $.ajax({
    type: type,
    url: url,
    data: data,
    dataType: 'html',
  }).done(function (response) {
    $target.html(response);
    if (callback) {
      callback($response);
    }
    ioAutoFun();
  }).fail(function (jqXHR, textStatus, errorThrown) {
    $target.find('.placeholder-posts').remove();
    //showAlert({ "status": 4, "msg": IO.localize.networkError });
    showAlert({ "status": 4, "msg": `Error: ${textStatus} - ${errorThrown}` });
  }).always(() => {
    $this.removeClass('disabled');
  });

  return false;
}

/**
 * 获取预定义内容
 * 
 * @param {jQuery} $this 触发按钮
 * @param {string} parent 父级选择器
 * @param {string} card 加载的内容卡片选择器
 * @param {string[]} replace 需替换的内容
 * @returns {boolean}
 */
function ajaxPosts($this, parent, card, replace) {
  var $parent = $this.closest(parent);
  if ($parent.length == 0) { 
    $parent = $(parent);
  }
  var $target = $parent.find('.ajax-posts-row');
  var method = $this.attr('ajax-method');
  var href = $this.attr('ajax-href') || $this.attr('href') || $this.find('a').attr('ajax-href') || $this.find('a').attr('href');

  var style = $this.data('style') || $target.data('style');
  var placeholder = getPredefinedContent(style);

  if (method) { // page or card
    $target.html(placeholder);
  } else {
    $target.append(placeholder);
  }

  $.ajax({
    type: 'GET',
    url: href,
    dataType: 'html',
  }).done(function (response) {
    var $response = $(response);
    if (method === 'page') { // page 模式直接替换 $parent
      $parent.html($response.find(parent).html());
      $parent.find('.selects-box .active').parent().tabToCenter();
    } else {
      var $load = $response.find(card);
    
      $target.find('.placeholder-posts').remove();


      if (method) {
        $target.html($load);
      } else {
        $target.append($load);
      }
    }
    if (replace) {
      replace.forEach(function (selector) {
        var $replace = $response.find(selector);
        if ($replace.length) {
          var $ser = $parent.find(selector);
          if (!$ser.length) { 
            $ser = $(selector);
          }
          $ser.html($replace.html());
        }
      });
    }

    $this.removeClass('disabled');

    ioAutoFun();
  }).fail(function () {
    $target.find('.placeholder-posts').remove();
    $this.removeClass('disabled');
    showAlert({ "status": 4, "msg": IO.localize.networkError });
  });

  return false;
}

if (IO.themeType == 'auto-system' && $.cookie('io_night_mode') == null) {
  var handleColorSchemeChange = function (e) {
    IO.isDarkMode = e.matches;

    if (IO.isDarkMode) {
      IO.html.addClass('io-black-mode');
    } else {
      IO.html.removeClass('io-black-mode');
    }
    switchThemeMode(false);

    $.cookie('prefers-color-scheme', IO.isDarkMode ? 'dark' : 'light');
  }

  var darkModeMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
  darkModeMediaQuery.addEventListener("change", handleColorSchemeChange);

  handleColorSchemeChange(darkModeMediaQuery);
}

function switchThemeMode(isManual) {
  var $btn = $('.switch-dark-mode');
  var $modeIco = $btn.find(".mode-ico");
  var $tinymceBody = $("#post_content_ifr").contents().find('body');

  // 检查 TinyMCE body 是否存在
  if ($tinymceBody.length > 0) {
    // 根据当前模式设置 TinyMCE body 样式和 cookie
    if (IO.isDarkMode) {
      $tinymceBody.addClass('io-black-mode');
    } else {
      $tinymceBody.removeClass('io-black-mode');
    }
  }

  // 如果手动切换，更新 cookie
  if (isManual) {
    $.cookie('io_night_mode', IO.isDarkMode ? 0 : 1);
  }

  // 更新按钮和图标的状态
  if ($btn.attr("data-original-title")) {
    $btn.attr("data-original-title", IO.isDarkMode ? IO.localize.lightMode : IO.localize.nightMode);
  } else {
    $btn.attr("title", IO.isDarkMode ? IO.localize.lightMode : IO.localize.nightMode);
  }
  $modeIco.removeClass(IO.isDarkMode ? "icon-night" : "icon-light")
    .addClass(IO.isDarkMode ? "icon-light" : "icon-night");

  switchSrc();
  
  var themeChangeEvent = new CustomEvent('themeModeChanged', {
    detail: { isDarkMode: IO.isDarkMode }
  });
  window.dispatchEvent(themeChangeEvent);

  // big-posts 对象触发自定义事件
  var $bigPosts = $('.big-posts');
  var jqThemeChangeEvent = $.Event('themeModeChanged');
  $bigPosts.trigger(jqThemeChangeEvent);
}

/**
 * 切换图片的 src 属性
 */
function switchSrc() {
  $("img[switch-src]").each(function () {
    var $this = $(this);
    var src = $this.attr("data-src") || $this.attr("src");
    var switchSrc = $this.attr("switch-src");

    var isDark = JSON.parse($this.attr("is-dark").toLowerCase());

    if (isDark != IO.isDarkMode) {
      $this.attr("src", switchSrc).attr("switch-src", src)
        .removeAttr("data-src").attr("is-dark", !isDark);
    }
  });
}
/**
 * 判断元素是否在视口内，并通过回调函数通知观察者。
 * 
 * @param {string|Element} element - 要观察的元素的选择器或Element对象。
 * @param {IntersectionObserver} observer - 用于观察元素可视状态的IntersectionObserver实例。
 * @param {function} callback - 当元素可视状态改变时调用的回调函数，参数为布尔值，表示元素是否在视口内。
 */
function isInViewPort(element, observer, callback) {
  if (!('IntersectionObserver' in window)) {
    callback(false);
    console.log('此浏览器不支持 IntersectionObserver。');
    return;
  }

  const targetElement = typeof element === 'string' ? document.querySelector(element) : element;
  
  if (!targetElement) {
    callback(false);
    return;
  }

  targetElement.callback = callback;

  observer.observe(targetElement);
}

/**
 * 创建 Modal 并显示。
 * @param {jQuery} $this - 触发 Modal 显示的元素。
 * @returns Modal 对象
 */
function ioModal($this) {
  var size = $this.data('modal_size') || 'modal-medium';
  var type = $this.data('modal_type') || 'modal-suspend';
  var id = $this.data('modal_id') ||  type;
  var id = 'refresh_modal_' + id;
  var esc = $this.data('modal_esc') !== undefined ? $this.data('modal_esc') : true;

  var loading = '<div class="io-modal-content"></div><div class="loading-anim io-radius blur-bg-20"><div class="d-flex align-items-center justify-content-center h-100"><i class="iconfont icon-loading icon-spin text-32"></i></div></div>';

  var modalHtml = `<div class="modal fade" id="${id}" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog ${size} modal-dialog-centered" role="document">
      <div class="modal-content ${type}"></div>
    </div>
  </div>`;

  var $modal = $('#' + id);
  if (!$modal.length) {
    $('body').append(modalHtml);
    $modal = $('#' + id);
  }
  $modal.find('.modal-content').html(loading).css({
    'height': '220px',
    'overflow': 'hidden'
  });
  if (!esc) {
    // 不关闭
    $modal.modal({
      backdrop: 'static',
      keyboard: false
    });
  }
  $modal.modal('show');
  return $modal;
}

function newObserver(offset) {
  return new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      const target = entry.target;
      const isVisible = entry.isIntersecting;
      if (target.callback && typeof target.callback === 'function') {
        target.callback(isVisible);
      }
    });
  }, {
    root: null,
    rootMargin: offset + "px 0px"
  });
}

/**
 * 节流函数生成器
 * 用于控制函数执行的频率，常用于防抖动场景，例如窗口的resize、scroll事件监听，
 * 表单输入验证、鼠标移动等高频触发的事件处理函数
 * 
 * @param {Function} func 需要进行节流处理的函数
 * @param {number} delay 延迟的毫秒数，即等待下一次执行的时间间隔
 * @param {boolean} immediate 是否立即执行，默认不立即执行如果设为true，函数在第一次被调用时会立即执行，
 * 之后的行为和setTimeout一致；如果设为false，则函数在第一次被调用时不会立即执行，而是在等待时间结束后才执行
 * 
 * @return {Function} 返回一个新的函数，这个函数会根据指定的时间间隔进行节流控制
 */
function debounce(callback, delay, immediate = false) {
  let timeout;

  return function (...args) {
    const context = this;

    // 如果设置了 immediate 并且 timeout 为 null，则立即执行 callback
    const callNow = immediate && !timeout;

    // 清除之前的 timeout 计时器
    clearTimeout(timeout);

    // 重新设定 timeout
    timeout = setTimeout(() => {
      timeout = null;
      if (!immediate) {
        callback.apply(context, args);
      }
    }, delay);

    // 如果是立即执行，则调用 callback
    if (callNow) {
      callback.apply(context, args);
    }
  };
}

function throttle(callback, delay, immediate = false) {
  let timeout = null;
  let lastCall = 0;

  return function (...args) {
    const now = Date.now();
    const context = this;

    if (immediate && !lastCall) {
      callback.apply(context, args);
      lastCall = now;
    }

    const remainingTime = delay - (now - lastCall);

    clearTimeout(timeout);

    if (remainingTime <= 0) {
      callback.apply(context, args);
      lastCall = now;
    } else if (!timeout) {
      timeout = setTimeout(() => {
        timeout = null;
        lastCall = immediate ? 0 : Date.now();
        if (!immediate) {
          callback.apply(context, args);
        }
      }, remainingTime);
    }
  };
}


/**
 * ajax请求
 * @param {*} $this 
 * @param {*} data 
 * @param {*} success 
 * @returns 
 */
function ioAjax($this, data = '', success = '') {
  if ($this.attr('disabled')) {
    return false;
  }
  if (!data) {
    var form = $this.closest('form');
    data = form.serializeObject();
  }
  var action = $this.data('action')
  if (action) {
    data.action = action;
  }

  var alert = {};
  
  $this.attr('disabled', true);

  $.ajax({
    url: IO.ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: data,
  }).done(function (n) {
    if (n.msg) {
      alert.status = n.status;
      alert.msg = n.msg;
      showAlert(alert);
    } else {
      removeAlert();
    }
    $this.attr('disabled', false);
    $.isFunction(success) && success(n, $this, data);
    if (n.goto) {
      window.location.href = n.goto;
      window.location.reload;
    } else if (n.reload) {
      window.location.reload();
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
    $this.attr('disabled', false);
  })
}

/**
 * 提示
 * @param {{}} data 提示数据 
 *                  {
 *                    status: 0,  // 0: 加载中 1: 成功 2: 信息 3: 警告 4: 错误
 *                    msg: '提示信息'
 *                  }
 */
function showAlert(data) {
  if(!data) return;
  var alert, ico, title;
  var type = data.status || data.type || 0;
  var msg = data.msg || data.message || '';
  if (msg == '') return;
  
  switch (type) {
    case 0:
      title = IO.localize.successAlert;
      alert = 'vc-gray';
      ico = 'icon-loading icon-spin';
      break;
    case 1:
      title = IO.localize.successAlert;
      alert = 'vc-blue';
      ico = 'icon-adopt';
      break;
    case 2:
      title = IO.localize.infoAlert;
      alert = 'vc-violet';
      ico = 'icon-tishi';
      break;
    case 3:
      title = IO.localize.warningAlert;
      alert = 'vc-yellow';
      ico = 'icon-warning';
      break;
    case 4:
      title = IO.localize.errorAlert;
      alert = 'vc-red';
      ico = 'icon-close-circle';
      break;
    default:
  }
  var $alertPlaceholder = $('#alert_placeholder');
  if (!$alertPlaceholder[0]) {
    IO.body.append('<div id="alert_placeholder" class="alert-system"></div>');
    $alertPlaceholder = $('#alert_placeholder');
  }
  var $html = $('<div class="alert-body io-alert-' + type + ' tips-box ' + alert + '"><i class="iconfont ' + ico + '"></i><span>' + msg + '</span></div>');
  removeAlert();
  $alertPlaceholder.append($html);
  if (type == 0) {
    $html.slideDown().addClass('show');
  } else {
    $html.slideDown().addClass('show');
    setTimeout(function () {
      removeAlert($html);
    }, 3500);
  }
}
function removeAlert(e) {
  if (!e) {
      e = $('.io-alert-0');
  }
  if (e[0]) {
      e.removeClass('show');
      setTimeout(function () {
          e.remove();
      }, 300);
  }
}


console.log("\n %c OneNav  V"+IO.version+" 导航主题 By 一为 %c https://www.iotheme.cn/ \n", "color: #ffffff; background: #f1404b; padding:5px 0;", "background: #030307; padding:5px 0;");

/**
 * 复制文本到剪贴板
 * @param {string} text 
 * @param {function} success 
 * @param {function} error 
 * @param {HTMLElement} _this 
 */
function copyText(text, success, error, _this) {
  // 数字没有 .length 不能执行selectText 需要转化成字符串
  var textString = text.toString();
  var input = document.querySelector('#copy-input');
  if (!input) {
    input = document.createElement('input');
    input.id = "copy-input";
    input.readOnly = "readOnly"; // 防止ios聚焦触发键盘事件
    input.style.position = "fixed";
    input.style.left = "-2000px";
    input.style.zIndex = "-1000";
    _this.parentNode.appendChild(input)
  }

  input.value = textString;
  // ios必须先选中文字且不支持 input.select();
  selectText(input, 0, textString.length);
  if (document.execCommand('copy')) {
    $.isFunction(success) && success();
  } else {
    $.isFunction(error) && error();
  }
  input.blur();

  // input自带的select()方法在苹果端无法进行选择，所以需要自己去写一个类似的方法
  // 选择文本。createTextRange(setSelectionRange)是input方法
  function selectText(textbox, startIndex, stopIndex) {
    if (textbox.createTextRange) { //ie
      var range = textbox.createTextRange();
      range.collapse(true);
      range.moveStart('character', startIndex); //起始光标
      range.moveEnd('character', stopIndex - startIndex); //结束光标
      range.select(); //不兼容苹果
    } else { //firefox/chrome
      textbox.setSelectionRange(startIndex, stopIndex);
      textbox.select();
    }
  }
}


function isPC() {
  if (navigator.userAgentData) {
    return !navigator.userAgentData.mobile;
  }
  // 回退到 userAgent 检查，支持更多设备
  const userAgent = navigator.userAgent.toLowerCase();
  const mobileAgents = [
    'android', 'iphone', 'webos', 'blackberry', 'symbian', 'windows phone', 
    'ipad', 'ipod', 'mobile', 'tablet', 'kindle', 'silk', 'playbook'
  ];
  return !mobileAgents.some(agent => userAgent.includes(agent));
}

function isURL(url) {
  var Expression = /https?:\/\/([\w-]+\.)+[\w-]+(\/[\w-.\/?%&=]*)?/;
  return Expression.test(url);
}
function changeInput($this) {
  if ($this.attr('data-status') != 'true') return;
  var maxCount = $this.parent().attr('data-max');
  if ($this.val().length <= maxCount) {
    $this.parent().attr('data-min', $this.val().length);
  } else {
    $this.val($this.val().substring(0, maxCount - 1)).trigger('input');
  }
}
/**
 * 处理人机验证表单
 * @param {jQuery} $this 
 * @param {*} data 
 * @param {*} success 
 * @returns 
 */
function captcha_ajax($this, data = '', success = '') {
  if ($this.attr('disabled')) {
    return false;
  }
  if (!data) {
    var form = $this.closest('form');
    data = form.serializeObject();
  }
  var action = $this.data('action') || $this.attr('action');
  if (action) {
    data.action = action;
  }
  if (data.captcha_type && window.captcha && !window.captcha.ticket) {
    CaptchaOpen($this, data.captcha_type);
    return false;
  }

  if (window.captcha) {
    data.captcha = JSON.parse(JSON.stringify(window.captcha));
    data.captcha._this && delete (data.captcha._this);
    window.captcha = {};
  }

  var alert = {};
  alert.status = 0;
  alert.msg = IO.localize.loading;
  showAlert(alert);
  
  var _text = $this.html();
  $this.attr('disabled', true).html('<i class="iconfont icon-loading icon-spin mr-2"></i>' + IO.localize.wait);

  $.ajax({
    url: IO.ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: data,
  }).done(function (n) {
    if (n.msg) {
      alert.status = n.status;
      alert.msg = n.msg;
      showAlert(alert);
    } else {
      removeAlert();
    }
    $this.attr('disabled', false).html(_text);
    $.isFunction(success) && success(n, $this, data);
    if (n.goto) {
      if (n.delay) {
        setTimeout(function () {
          window.location.href = n.goto;
          window.location.reload;
        }, n.delay);
      } else {
        window.location.href = n.goto;
        window.location.reload;
      }
    } else if (n.reload) {
      if (n.delay) {
        setTimeout(function () {
          window.location.reload();
        }, n.delay);
      } else {
        window.location.reload();
      }
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
    $this.attr('disabled', false).html(_text);
  });
}

/**
 * 获取滚动条宽度
 * @returns 
 */
function getScrollbarWidth() {
  var noScroll, scroll, oDiv = document.createElement("DIV");
  oDiv.style.cssText = "position:absolute; top:-1000px; width:100px; height:100px; overflow:hidden;";
  noScroll = document.body.appendChild(oDiv).clientWidth;
  oDiv.style.overflowY = "scroll";
  scroll = oDiv.clientWidth;
  document.body.removeChild(oDiv);
  return noScroll - scroll;
}
/**
 * 判断是否有滚动条
 * @returns 
 */
function hasScrollbar() {
  return document.documentElement.scrollHeight > window.innerHeight;
} 

function ioPopup(type, html, maskStyle, btnCallBack) {
  var size = '';
  switch (type) {
    case 'big':
      size = 'io-bomb-lg';
      break;
    case 'no-padding':
      size = 'io-bomb-nopd';
      break;
    case 'cover':
      size = 'io-bomb-cover io-bomb-nopd';
      break;
    case 'full':
      size = 'io-bomb-xl';
      break;
    case 'small':
      size = 'io-bomb-sm';
      break;
    case 'confirm':
      size = 'io-bomb-md';
      break;
    case 'pay':
      size = 'io-bomb-sm io-bomb-nopd';
      break;
    default:
      size = '';
  }
  var template = `<div class="io-bomb ${size} io-bomb-open">
	  	<div class="io-bomb-overlay" style="${maskStyle}"></div>
	  	<div class="io-bomb-body text-center">
	  		<div class="io-bomb-content">${html}</div>
	  		<div class="btn-close-bomb mt-2"><i class="iconfont icon-close-circle"></i></div>
	  	</div>
	  </div>`;

  var $popup = $(template);

  $('body').addClass('modal-open').append($popup);
  if (hasScrollbar()) $('body').css("padding-right", getScrollbarWidth());

  var closePopup = function () {
    $popup.removeClass('io-bomb-open').addClass('io-bomb-close');
    setTimeout(function () {
      $('body').removeClass('modal-open');
      if (hasScrollbar()) $('body').css("padding-right", '');
      $popup.remove();
    }, 300);
  };

  $($popup).on('click touchstart', '.btn-close-bomb i, .io-bomb-overlay', function (e) {
    e.preventDefault();
    if ($.isFunction(btnCallBack)) btnCallBack(true);
    closePopup();
  });

  return $popup;
}


function ioConfirm(title, message, btnCallBack) {
  var template = `<div class="io-bomb io-bomb-confirm io-bomb-open">
      <div class="io-bomb-overlay"></div>
      <div class="io-bomb-body">
        <div class="io-bomb-content text-sm">
          <div class="io-bomb-header fx-yellow modal-header-bg text-center p-3">
            <i class="iconfont icon-tishi text-32"></i>
            <div class="text-md mt-1">${title}</div>
          </div>
          <div class="m-4">${message}</div>
          <div class="io-bomb-footer text-center mb-4 mx-4">
            <button class="btn vc-red btn-shadow flex-fill io-confirm-ok">${IO.localize.okBtn}</button>
            <button class="btn vc-l-yellow btn-outline flex-fill io-confirm-cancel">${IO.localize.cancelBtn}</button>
          </div>
        </div>
      </div>
    </div>`;

  var $popup = $(template);

  // 关闭弹窗
  var closePopup = function() {
    $popup.removeClass('io-bomb-open').addClass('io-bomb-close');
    setTimeout(function () {
      $('body').removeClass('modal-open');
      if (hasScrollbar()) $('body').css("padding-right", '');
      $popup.remove();
    }, 300);
  };

  // 添加事件监听器
  $popup.find('.io-confirm-ok').on('click', function() {
    closePopup();
    if ($.isFunction(btnCallBack)) btnCallBack(true);
  });

  $popup.find('.io-confirm-cancel').on('click', function() {
    closePopup();
    if ($.isFunction(btnCallBack)) btnCallBack(false);
  });

  $('body').addClass('modal-open').append($popup);
  if (hasScrollbar()) $('body').css("padding-right", getScrollbarWidth());

  return $popup;
}
