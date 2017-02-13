var jconfirm, Jconfirm;

!function (t) {
  t.confirm = function (t) {
    return jconfirm(t)
  }, t.alert = function (t) {
    return t.cancelButton = !1, jconfirm(t)
  }, t.dialog = function (t) {
    return t.cancelButton = !1, t.confirmButton = !1, jconfirm(t)
  }, jconfirm = function (e) {
    jconfirm.defaults && t.extend(jconfirm.pluginDefaults, jconfirm.defaults);
    var e = t.extend({}, jconfirm.pluginDefaults, e);
    return new Jconfirm(e)
  }, Jconfirm = function (e) {
    t.extend(this, e), this._init()
  }, Jconfirm.prototype = {
    _init: function () {
      var t = this;
      this._rand = Math.round(99999 * Math.random()), this._buildHTML(), this._bindEvents(), setTimeout(function () {
        t.open()
      }, 0)
    },
    animations: ["anim-scale", "anim-top", "anim-bottom", "anim-left", "anim-right", "anim-zoom", "anim-opacity", "anim-none", "anim-rotate", "anim-rotatex", "anim-rotatey", "anim-scalex", "anim-scaley"],
    _buildHTML: function () {
      this.animation = "anim-" + this.animation.toLowerCase(), "none" === this.animation && (this.animationSpeed = 0), this.$el = t(this.template).appendTo(this.container).addClass(this.theme), this.$el.find(".jconfirm-box-container").addClass(this.columnClass), this.CSS = {
        "-webkit-transition-duration": this.animationSpeed / 1e3 + "s",
        "transition-duration": this.animationSpeed / 1e3 + "s",
        "-webkjit-transition-timing-function": "cubic-bezier(0.27, 1.12, 0.32, " + this.animationBounce + ")",
        "transition-timing-function": "cubic-bezier(0.27, 1.12, 0.32, " + this.animationBounce + ")"
      }, this.$el.find(".jconfirm-bg").css(this.CSS), this.$b = this.$el.find(".jconfirm-box").css(this.CSS).addClass(this.animation), this.setTitle(), this.contentDiv = this.$el.find("div.content"), this.$btnc = this.$el.find(".buttons"), this.confirmButton && "" !== this.confirmButton.trim() && (this.$confirmButton = t('<button class="btn">' + this.confirmButton + "</button>").appendTo(this.$btnc).addClass(this.confirmButtonClass)), this.cancelButton && "" !== this.cancelButton.trim() && (this.$cancelButton = t('<button class="btn">' + this.cancelButton + "</button>").appendTo(this.$btnc).addClass(this.cancelButtonClass)), this.confirmButton || this.cancelButton || this.$btnc.remove(), this.confirmButton || this.cancelButton || null != this.closeIcon || (this.$closeButton = this.$b.find(".closeIcon").show()), this.closeIcon === !0 && (this.$closeButton = this.$b.find(".closeIcon").show()), this.setContent(), this.autoClose && this._startCountDown()
    },
    setTitle: function (t) {
      this.title = "undefined" != typeof t ? t : this.title, this.title ? this.$el.find("div.title").html('<i class="' + this.icon + '"></i> ' + this.title) : this.$el.find("div.title").remove()
    },
    setContent: function (e) {
      var i = this;
      this.content = e ? e : this.content;
      var n = e ? !0 : !1;
      if ("boolean" == typeof this.content) this.content ? console.error("Invalid option for property content: passed TRUE") : this.contentDiv.remove();
      else if ("string" == typeof this.content) if ("url:" === this.content.substr(0, 4).toLowerCase()) {
        this.contentDiv.html(""), this.$btnc.find("button").prop("disabled", !0);
        var r = this.content.substring(4, this.content.length);
        t.get(r).done(function (t) {
          i.contentDiv.html(t)
        }).always(function (t, e, n) {
          "function" == typeof i.contentLoaded && i.contentLoaded(t, e, n), i.$btnc.find("button").prop("disabled", !1), i.setDialogCenter()
        })
      } else this.contentDiv.html(this.content);
      else if ("function" == typeof this.content) {
        this.contentDiv.html(""), this.$btnc.find("button").attr("disabled", "disabled");
        var o = this.content(this);
        "object" != typeof o ? console.error("The content function must return jquery promise.") : "function" != typeof o.always ? console.error("The object returned is not a jquery promise.") : o.always(function () {
          i.$btnc.find("button").removeAttr("disabled"), i.setDialogCenter()
        })
      } else console.error("Invalid option for property content, passed: " + typeof this.content);
      this.setDialogCenter(n)
    },
    _startCountDown: function () {
      var e = this.autoClose.split("|");
      if (/cancel/.test(e[0]) && "alert" === this.type) return !1;
      if (/confirm|cancel/.test(e[0])) {
        this.$cd = t('<span class="countdown">').appendTo(this["$" + e[0] + "Button"]);
        var i = this;
        i.$cd.parent().click();
        var n = e[1] / 1e3;
        this.interval = setInterval(function () {
          i.$cd.html(" [" + (n -= 1) + "]"), 0 === n && (i.$cd.parent().trigger("click"), clearInterval(i.interval))
        }, 1e3)
      } else console.error("Invalid option " + e[0] + ", must be confirm/cancel")
    },
    _bindEvents: function () {
      var e = this;
      this.$el.find(".jconfirm-scrollpane").click(function () {
        e.backgroundDismiss ? (e.cancel(), e.close()) : (e.$b.addClass("hilight"), setTimeout(function () {
          e.$b.removeClass("hilight")
        }, 400))
      }), this.$el.find(".jconfirm-box").click(function (t) {
        t.stopPropagation()
      }), this.$confirmButton && this.$confirmButton.click(function (t) {
        t.preventDefault();
        var i = e.confirm(e.$b);
        e.onAction(), ("undefined" == typeof i || i) && e.close()
      }), this.$cancelButton && this.$cancelButton.click(function (t) {
        t.preventDefault();
        var i = e.cancel(e.$b);
        e.onAction(), ("undefined" == typeof i || i) && e.close()
      }), this.$closeButton && this.$closeButton.click(function (t) {
        t.preventDefault(), e.cancel(), e.onAction(), e.close()
      }), this.keyboardEnabled && setTimeout(function () {
        t(window).on("keyup." + this.rand, function (t) {
          e.reactOnKey(t)
        })
      }, 500), t(window).on("resize." + this.rand, function () {
        e.setDialogCenter(!0)
      })
    },
    reactOnKey: function (e) {
      var i = t(".jconfirm");
      if (i.eq(i.length - 1)[0] !== this.$el[0]) return !1;
      var n = e.which;
      if (27 === n) {
        if (!this.backgroundDismiss) return this.$el.find(".jconfirm-bg").click(), !1;
        this.$cancelButton ? this.$cancelButton.click() : this.close()
      }
      (13 === n || 32 == n) && this.$confirmButton && this.$confirmButton.click()
    },
    setDialogCenter: function (e) {
      var i = t(window).height(),
        n = this.$b.outerHeight(),
        r = (i - n) / 2,
        o = 100;
      if (n > i - o) var a = {
        "margin-top": o / 2,
        "margin-bottom": o / 2
      };
      else var a = {
        "margin-top": r
      };
      e ? this.$b.animate(a, {
        duration: this.animationSpeed,
        queue: !1
      }) : this.$b.css(a)
    },
    close: function () {
      var e = this;
      return this.isClosed() ? !1 : ("function" == typeof this.onClose && this.onClose(), t(window).unbind("resize." + this._rand), this.keyboardEnabled && t(window).unbind("keyup." + this._rand), e.$el.find(".jconfirm-bg").removeClass("seen"), this.$b.addClass(this.animation), setTimeout(function () {
        e.$el.remove()
      }, this.animationSpeed + 10), jconfirm.record.closed += 1, jconfirm.record.currentlyOpen -= 1, jconfirm.record.currentlyOpen < 1 && t("body").removeClass("jconfirm-noscroll"), !0)
    },
    open: function () {
      var e = this;
      return this.isClosed() ? !1 : (e.$el.find(".jconfirm-bg").addClass("seen"), t("body").addClass("jconfirm-noscroll"), this.$b.removeClass(this.animations.join(" ")), t("body :focus").trigger("blur"), this.$b.find("input[autofocus]:visible:first").focus(), jconfirm.record.opened += 1, jconfirm.record.currentlyOpen += 1, "function" == typeof this.onOpen && this.onOpen(), !0)
    },
    isClosed: function () {
      return "" === this.$el.css("display") ? !0 : !1
    }
  }, jconfirm.pluginDefaults = {
    template: '<div class="jconfirm"><div class="jconfirm-bg"></div><div class="jconfirm-scrollpane"><div class="container"><div class="row"><div class="jconfirm-box-container span6 offset3"><div class="jconfirm-box"><div class="closeIcon"><span class="glyphicon glyphicon-remove"></span></div><div class="title"></div><div class="content"></div><div class="buttons"></div><div class="jquery-clear"></div></div></div></div></div></div></div>',
    title: "Hello",
    content: "Are you sure to continue?",
    contentLoaded: function () {
    },
    icon: "",
    confirmButton: "Okay",
    cancelButton: "Cancel",
    confirmButtonClass: "btn-default",
    cancelButtonClass: "btn-default",
    theme: "white",
    animation: "scale",
    animationSpeed: 400,
    animationBounce: 1.5,
    keyboardEnabled: !1,
    container: "body",
    confirm: function () {
    },
    cancel: function () {
    },
    backgroundDismiss: !0,
    autoClose: !1,
    closeIcon: null,
    columnClass: "col-md-6 col-md-offset-3",
    onOpen: function () {
    },
    onClose: function () {
    },
    onAction: function () {
    }
  }, jconfirm.record = {
    opened: 0,
    closed: 0,
    currentlyOpen: 0
  }
}(jQuery);

$.tap = function(t, e) {
  return $(document).tap(t, e)
};

$.fn.tap = function (t, e) {
  var i;
  return i = this, i.off("click tap", t).on("click tap", t, function (t) {
    return $.proxy(e, this, t)()
  })
};

jconfirm.defaults = {
  title: !1,
  content: !1,
  confirmButton: "\u786e\u5b9a",
  cancelButton: "\u53d6\u6d88"
};

$.fn.modal = function () {
  var t;
  if (!this.data("modal_binded")) return t = this, t.on("modal.toggle", function () {
    var e;
    return t.hasClass("hidden") ? ($('body').addClass("modal-open"), e = t.find("video:first"), e.length && e[0].play()) : ($('body').removeClass("modal-open"), e = t.find("video:first"), e.length && e[0].pause()), t.toggleClass("hidden")
  }).tap(".title .fa-close", function () {
    return t.trigger("modal.toggle")
  }).tap("a.close", function () {
    return t.trigger("modal.toggle")
  }), this.data("modal_binded", !0)
};

window.alert = function (t) {
  var e;
  return e = $('<div class="modal"><div class="modal-dialog"><div class="title">' + t + '</div><div class="content"><button>\u786e\u5b9a</button></div></div></div>').appendTo("body").modal(), e.tap("button", function () {
    return e.remove()
  })
};

