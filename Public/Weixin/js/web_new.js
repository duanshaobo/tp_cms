/**
 * Created by Xiaofan Zhang on 2015/11/16.
 */
jQuery.browser = {};
jQuery.browser.msie = false;
jQuery.browser.version = 0;
if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
  jQuery.browser.msie = true;
  jQuery.browser.version = RegExp.$1;
}

$.extend($, {
  slider: function (option) {
    /**
     * option example :
     * {
     *   box: '.box',
     *   wrapper: '.wrap',
     *   items: '.item',
     *   itemnum: '3',
     *   pre: '.pre',
     *   next: '.next',
     *   interval: 1000
     * }
     */
    if (!option) {
      throw 'option should not be empty';
    } else {
      var $box = $(option.box),
        $wrap = $(option.wrapper),
        $items = $(option.items),
        $pre = $(option.pre),
        $next = $(option.next);

      var totalPage = Math.ceil($items.length / option.itemnum),
        countLine = 0;
      $wrap.width(totalPage * $box.width());
      $pre.on('click', function(){
        countLine--;
//    console.info('pre: ' + countLine);
        if (countLine < 0) {
          countLine = 0;
          $wrap.animate({marginLeft: 0},1000,'swing');
        } else {
          $wrap.animate({marginLeft: -(countLine * $box.width()) + 'px'},1000,'swing');
        }
      });
    }

    function slide () {
      countLine++;
//    console.info('next: ' + countLine);
      if (countLine > totalPage - 1) {
        countLine = 0;
        $wrap.animate({marginLeft: 0});
      } else {
        $wrap.animate({marginLeft: -(countLine * $box.width()) + 'px'},1000,'swing');
      }
    }

    $next.on('click', function(){
      slide();
    });

    if (option.interval) {
      setInterval(slide, option.interval);
    }
  },
  popup: function (popWindow) {

    /*
     * button: string example: ".btn-simple" 按钮
     * popWindow: string example: ".pop-window" 弹窗
     * modal: string example: "#modal" 透明层
     * closeBtn: string example: ".btn-close" 关闭按钮
     */
    var $popWindow = $(popWindow);

    var _width = $(popWindow).outerWidth(),
      _height = $(popWindow).outerHeight();


    // 给弹出层添加默认定位位置
    var isIE6 = $.browser.msie && ($.browser.version == "6") && !$.support.style;
    var cssPosition = isIE6 ? "absolute" : "fixed";

    //样式初始化
    $popWindow.css({
      "position": cssPosition,
      "display": "none",
      "z-index": 1100,
      "margin-left": -(_width / 2) + "px",
      "left": 50 + "%",
      "top": 50 + "%",
      "margin-top": -(_height/2)+"px"
    });

    return $popWindow.show().fadeTo(200, 1);
  },
  modalInit: function (target) {
    // 给弹出层添加默认定位位置
    var isIE6 = $.browser.msie && ($.browser.version == "6") && !$.support.style
      , $o = $(target);

    if(isIE6) {
      return false;
    } else {
      $o.css({
        "position": "fixed",
        "z-index": 100,
        "top": 0,
        "left": 0,
        "display": "none",
        "width": "100%",
        "height": "100%",
        "background-color": "#000",
        "opacity": 0.7,
        "filter": "alpha(opacity=70)"
      });
    }
    return $o;
  }
});

$(function(){
  var $dropDownModal = $('.dropdown-modal');
  var $loggedNumber = $('.logged').find('.number');
  $loggedNumber.click(function(e){
    e.preventDefault();
    if (!$(this).hasClass('show')) {
      $(this).addClass('show');
      $dropDownModal.show();
      $(this).siblings('.dropdown-menu').show().animate({top: '55px', opacity: 1}, 200);
    } else {
      $(this).removeClass('show');
      $dropDownModal.hide();
      $(this).siblings('.dropdown-menu').animate({top: '65px', opacity: 0}).fadeOut(100);
    }
  });

  //add by peng
  //$('#modal').on('touchend',function(){
  //  var $modal = $.modalInit('#modal');
  //  $modal.hide();
  //  $('#bind_phone_box').remove();
  //});
//end by peng
  $dropDownModal.click(function(){

    //alert("");


    $('.dropdown-menu').animate({top: '65px', opacity: 0}).fadeOut(100);
    $(this).hide();
    $loggedNumber.removeClass('show');
  });

  jQuery.extend( jQuery.easing,
    {
      def: 'easeOutQuad',
      swing: function (x, t, b, c, d) {
        //alert(jQuery.easing.default);
        return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
      },
      easeOutQuad: function (x, t, b, c, d) {
        return -c *(t/=d)*(t-2) + b;
      }
    });

  //back to top
  //$("body").append('<div id="tbox"><a class="back-top" href="javascript:void(0)"><span>回到顶部</span></a>'
  //  +'<a class="fixed-notification" href="http://www.yidianling.com/smsWeb/myLetter/"><span>消息通知</span><span class="click-me"></span></a>'
  //  +'<a class="weChat" id="weChat"><span>微信关注</span></a>'
  //  +'<a class="qqChat" href="http://crm2.qq.com/page/portalpage/wpa.php?uin=4008789610&amp;aty=0&amp;a=0&amp;curl=www.yidianling.com&amp;ty=1"><span>在线客服</span></a>'
  //  +'</div>');
  var a = $(".back-top");
  $(window).scroll(function() {
    $(window).scrollTop() > 100 ? a.fadeIn() : a.fadeOut()
  });
  $(window).scroll();
  a.click(function() {
    $("body,html").animate({
      scrollTop: 0
    }, 1E3)
  })
});
$(function() {
  var a = $(".ser-handle"),
    b = $(".fix-right-contact");
  $(".s-close,.v-close").click(function() {
    b.hide();
    a.show()
  });
  a.click(function() {
    b.show();
    a.hide()
  })
});
$(function() {
  var a = $(".fix-right-contact"),
    b = $(".ser-handle");
  $(window).scroll(function() {
    $(window).scrollTop() > 200 ? a.addClass("scroll") : a.removeClass("scroll");
    $(window).scrollTop() > 200 ? b.addClass("scroll") : b.removeClass("scroll")
  })
});
$(function() {
  var a = $(".index-wrap .fix-right-contact"),
    b = $(".index-wrap .ser-handle");
  $(window).scroll(function() {
    $(window).scrollTop() > 500 ? a.addClass("scroll") : a.removeClass("scroll");
    $(window).scrollTop() > 630 ? b.addClass("scroll") : b.removeClass("scroll")
  })
});
$(function() {
  var a = $(".chat-we"),
    b = $(".weChat");
  a = a.find(".patent-hd");
  a.mouseover(function() {
    b.show()
  });
  a.mouseout(function() {
    b.hide()
  })
});
$(function() {
  var a = $(".div-side-contactv,.pr-we,.bt-pr"),
    b = $(".weChat");
  a.each(function(c) {
    $(this).mouseover(function() {
      $(b[c]).show()
    });
    $(this).mouseout(function() {
      b.hide()
    })
  })
});
$(function() {
  var a = $(".on-chat-box"),
    b = a.find(".alt");
  a.each(function(c) {
    $(this).mouseover(function() {
      $(b[c]).show()
    });
    $(this).mouseout(function() {
      b.hide()
    })
  })
});
$(function() {
  var a = $(".div-feedback"),
    b = $(".feedback-box");
  a.mouseover(function() {
    $(this).addClass("feed-hover");
    b.show()
  });
  a.mouseout(function() {
    $(this).removeClass("feed-hover");
    b.hide()
  })
});
$(function() {
  function a() {
    if (d.width() < c) {
      $(e).css("width", c + "px");
      $(".fix-right-contact").css({
        left: 810,
        marginLeft: 0
      })
    } else {
      $(e).css("width", "auto");
      $(".fix-right-contact").css({
        left: "50%",
        marginLeft: 515
      })
    }
  }

  function b() {
    if (d.width() <= c) {
      $("body .index-wrap .scroll").css({
        left: "auto",
        right: "0px"
      });
      d.scrollTop() <= 500 && $(".fix-right-contact").css({
        left: "810px",
        marginLeft: "0px",
        right: "auto"
      });
      d.scrollTop() <= 630 && $(".index-wrap .ser-handle").css({
        left: "979px",
        marginLeft: "0px",
        right: "auto"
      })
    }
  }
  var c = 1E3,
    e = ".minwidthbox",
    d = $(window);
  d.resize(a);
  a();
  d.scroll(b);
  b()
});
