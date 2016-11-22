(function($) {
    var g = {
            type: "html",
            content: "",
            url: "",
            ajax: {},
            ajax_request: null,
            closeOnEsc: !0,
            closeOnOverlayClick: !0,
            clone: !1,
            overlay: {
                block: void 0,
                tpl: '<div class="preordermodal-overlay"></div>',
                css: {
                    backgroundColor: "#000",
                    opacity: 0.6
                }
            },
            container: {
                block: void 0,
                tpl: '<div class="preordermodal-container"><table class="preordermodal-container_i"><tr><td class="preordermodal-container_i2"></td></tr></table></div>'
            },
            wrap: void 0,
            body: void 0,
            errors: {
                tpl: '<div class="preordermodal-error preordermodal-close"></div>',
                autoclose_delay: 2E3,
                ajax_unsuccessful_load: "Error"
            },
            openEffect: {
                type: "fade",
                speed: 400
            },
            closeEffect: {
                type: "fade",
                speed: 400
            },
            beforeOpen: $.noop,
            afterOpen: $.noop,
            beforeClose: $.noop,
            afterClose: $.noop,
            afterLoading: $.noop,
            afterLoadingOnShow: $.noop,
            errorLoading: $.noop
        },
        j = 0,
        e = $([]),
        m = {
            isEventOut: function(a, b) {
                var c = !0;
                $(a).each(function() {
                    $(b.target).get(0) == $(this).get(0) && (c = !1);
                    0 == $(b.target).closest("HTML", $(this).get(0)).length && (c = !1)
                });
                return c
            }
        },
        f = {
            getParentEl: function(a) {
                var b = $(a);
                return b.data("preordermodal") ? b : (b =
                    $(a).closest(".preordermodal-container").data("preordermodalParentEl")) ? b : !1
            },
            transition: function(a, b, c, e) {
                e = void 0 == e ? $.noop : e;
                switch (c.type) {
                    case "fade":
                        "show" == b ? a.fadeIn(c.speed, e) : a.fadeOut(c.speed, e);
                        break;
                    case "none":
                        "show" == b ? a.show() : a.hide(), e()
                }
            },
            prepare_body: function(a, b) {
                $(".preordermodal-close", a.body).unbind("click.preordermodal").bind("click.preordermodal", function() {
                    b.preordermodal("close");
                    return !1
                })
            },
            init_el: function(a, b) {
                var c = a.data("preordermodal");
                if (!c) {
                    c = b;
                    j++;
                    c.modalID = j;
                    c.overlay.block =
                        $(c.overlay.tpl);
                    c.overlay.block.css(c.overlay.css);
                    c.container.block = $(c.container.tpl);
                    c.body = $(".preordermodal-container_i2", c.container.block);
                    b.clone ? c.body.html(a.clone(!0)) : (a.before('<div id="preordermodalReserve' + c.modalID + '" style="display: none" />'), c.body.html(a));
                    f.prepare_body(c, a);
                    c.closeOnOverlayClick && c.overlay.block.add(c.container.block).click(function(b) {
                        m.isEventOut($(">*", c.body), b) && a.preordermodal("close")
                    });
                    c.container.block.data("preordermodalParentEl", a);
                    a.data("preordermodal", c);
                    e = $.merge(e, a);
                    $.proxy(h.show, a)();
                    if ("html" == c.type) return a;
                    if (void 0 != c.ajax.beforeSend) {
                        var k = c.ajax.beforeSend;
                        delete c.ajax.beforeSend
                    }
                    if (void 0 != c.ajax.success) {
                        var g = c.ajax.success;
                        delete c.ajax.success
                    }
                    if (void 0 != c.ajax.error) {
                        var l = c.ajax.error;
                        delete c.ajax.error
                    }
                    var n = $.extend(!0, {
                        url: c.url,
                        beforeSend: function() {
                            void 0 == k ? c.body.html('<div class="preordermodal-loading" />') : k(c, a)
                        },
                        success: function(b) {
                            a.trigger("afterLoading");
                            c.afterLoading(c, a, b);
                            void 0 == g ? c.body.html(b) : g(c, a, b);
                            f.prepare_body(c,
                                a);
                            a.trigger("afterLoadingOnShow");
                            c.afterLoadingOnShow(c, a, b)
                        },
                        error: function() {
                            a.trigger("errorLoading");
                            c.errorLoading(c, a);
                            void 0 == l ? (c.body.html(c.errors.tpl), $(".preordermodal-error", c.body).html(c.errors.ajax_unsuccessful_load), $(".preordermodal-close", c.body).click(function() {
                                a.preordermodal("close");
                                return !1
                            }), c.errors.autoclose_delay && setTimeout(function() {
                                a.preordermodal("close")
                            }, c.errors.autoclose_delay)) : l(c, a)
                        }
                    }, c.ajax);
                    c.ajax_request = $.ajax(n);
                    a.data("preordermodal", c)
                }
            },
            init: function(a) {
                a =
                    $.extend(!0, {}, g, a);
                if ($.isFunction(this))
                    if (void 0 == a) $.error("jquery.preordermodal: Uncorrect parameters");
                    else if ("" == a.type) $.error('jquery.preordermodal: Don\'t set parameter "type"');
                else switch (a.type) {
                    case "html":
                        if ("" == a.content) {
                            $.error('jquery.preordermodal: Don\'t set parameter "content"');
                            break
                        }
                        var b = a.content;
                        a.content = "";
                        return f.init_el($(b), a);
                    case "ajax":
                        if ("" == a.url) {
                            $.error('jquery.preordermodal: Don\'t set parameter "url"');
                            break
                        }
                        return f.init_el($("<div />"), a)
                } else return this.each(function() {
                    f.init_el($(this),
                        $.extend(!0, {}, a))
                })
            }
        },
        h = {
            show: function() {
                var a = f.getParentEl(this);
                if (!1 === a) $.error("jquery.preordermodal: Uncorrect call");
                else {
                    var b = a.data("preordermodal");
                    b.overlay.block.hide();
                    b.container.block.hide();
                    $("BODY").append(b.overlay.block);
                    $("BODY").append(b.container.block);
                    b.beforeOpen(b, a);
                    a.trigger("beforeOpen");
                    if ("hidden" != b.wrap.css("overflow")) {
                        b.wrap.data("preordermodalOverflow", b.wrap.css("overflow"));
                        var c = b.wrap.outerWidth(!0);
                        b.wrap.css("overflow", "hidden");
                        var g = b.wrap.outerWidth(!0);
                        g !=
                            c && b.wrap.css("marginRight", g - c + "px")
                    }
                    e.not(a).each(function() {
                        $(this).data("preordermodal").overlay.block.hide()
                    });
                    f.transition(b.overlay.block, "show", 1 < e.length ? {
                        type: "none"
                    } : b.openEffect);
                    f.transition(b.container.block, "show", 1 < e.length ? {
                        type: "none"
                    } : b.openEffect, function() {
                        b.afterOpen(b, a);
                        a.trigger("afterOpen")
                    });
                    return a
                }
            },
            close: function() {
                if ($.isFunction(this)) e.each(function() {
                    $(this).preordermodal("close")
                });
                else return this.each(function() {
                    var a = f.getParentEl(this);
                    if (!1 === a) $.error("jquery.preordermodal: Uncorrect call");
                    else {
                        var b = a.data("preordermodal");
                        !1 !== b.beforeClose(b, a) && (a.trigger("beforeClose"), e.not(a).last().each(function() {
                            $(this).data("preordermodal").overlay.block.show()
                        }), f.transition(b.overlay.block, "hide", 1 < e.length ? {
                            type: "none"
                        } : b.closeEffect), f.transition(b.container.block, "hide", 1 < e.length ? {
                            type: "none"
                        } : b.closeEffect, function() {
                            b.afterClose(b, a);
                            a.trigger("afterClose");
                            b.clone || $("#preordermodalReserve" + b.modalID).replaceWith(b.body.find(">*"));
                            b.overlay.block.remove();
                            b.container.block.remove();
                            a.data("preordermodal",
                                null);
                            $(".preordermodal-container").length || (b.wrap.data("preordermodalOverflow") && b.wrap.css("overflow", b.wrap.data("preordermodalOverflow")), b.wrap.css("marginRight", 0))
                        }), "ajax" == b.type && b.ajax_request.abort(), e = e.not(a))
                    }
                })
            },
            setDefault: function(a) {
                $.extend(!0, g, a)
            }
        };
    $(function() {
        g.wrap = $(document.all && !document.querySelector ? "html" : "body")
    });
    $(document).bind("keyup.preordermodal", function(a) {
        var b = e.last();
        b.length && b.data("preordermodal").closeOnEsc && 27 === a.keyCode && b.preordermodal("close")
    });
    $.preordermodal =
        $.fn.preordermodal = function(a) {
            if (h[a]) return h[a].apply(this, Array.prototype.slice.call(arguments, 1));
            if ("object" === typeof a || !a) return f.init.apply(this, arguments);
            $.error("jquery.preordermodal: Method " + a + " does not exist")
        }
})(jQuery);