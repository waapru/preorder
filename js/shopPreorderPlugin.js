(function($){

$.shopPreorderPlugin = {
	settings: {},
	product_ids:{},
	init: function(options){
		this.settings = $.extend({
			form_id_preffix: 'shop-preorder-plugin-form-',
			done: 'Предзаказ выполнен',
			just_done: 'Предзаказ уже сделан',
			email_error: 'Email некорректен',
			phone_error: 'Телефонный номер некорректен'
		},options);
		
		var self = this;
		$('body').on('click','.preorder-open',function(){
			var sku_id = $(this).data('sku-id'),
				form = $('#'+self.settings.form_id_preffix+sku_id);
			form.find('.error').remove();
			form.preordermodal({
				beforeOpen: function(){
					form.show();
				},
				afterClose: function(){
					form.hide();
				}
			});
			return false;
		});
		$('body').on('click','.preorder-submit',function(){
			var that = $(this),
				v = that.html(),
				form = that.closest('[id^="'+self.settings.form_id_preffix+'"]');
			form.find('.error').remove();
			if ( !that.is('.disabled') ){
				that.addClass('disabled');
				that.html('Ждите..');
				$.post($(this).attr('href'),self.preorderSerializeArray(form),function(response){
					self.showError(response,form);
					that.html(v);
					that.removeClass('disabled');
				},'json');
			}
			return false;
		});
	},
	showBtn: function(sku_id){
		if ( sku_id > 0 ){
			var p = this.settings.form_id_preffix,
				product_id = (typeof this.product_ids[sku_id] != 'undefined')?this.product_ids[sku_id]:'',
				wr = $('#shop-preorder-plugin-form-wr-'+product_id),
				btn = $('#'+p+sku_id+'-btn',wr);
			
			$('.preorder-open',wr).hide();
			btn.size() && $('#'+p+sku_id+'-btn').show();
		}
	},
	preorderSerializeArray: function($form){
		var v = {};
		$form.find('[name^="preorder_"]').each(function(i){
			var name = $(this).attr('name').replace('preorder_','');
			v[name] = $(this).val();
		})
		return v;
	},
	showError: function(response,form){
		if ( response.status == 'ok' ){
			var error = response.data.error;
			switch ( error ){
				case 0 :
					$('.field',form).last().after(this.settings.done)
					$('.field',form).remove();
					setTimeout(function(){
						$.preordermodal('close');
					},2000);
					break;
				case 1 :
					var $error = $('<p />').addClass('error').html(this.settings.email_error);
					form.find('input[name="preorder_email"]').closest('.value').append($error);
					break;
				case 2 :
					$('.field',form).last().after(this.settings.just_done)
					$('.field',form).remove();
					setTimeout(function(){
						$.preordermodal('close');
					},2000);
					break;
				case 4 :
					var $error = $('<p />').addClass('error').html(this.settings.phone_error);
					form.find('input[name="preorder_phone"]').closest('.value').append($error);
					break;
				case 5 :
					var $error = $('<p />').addClass('error').html(this.settings.email_or_phone_error);
					form.find('input[name="preorder_phone"]').closest('.value').append($error);
					break;
			}
		}
	}
};
})(jQuery);


/* modal */
(function($){
var g = {
	type: "html",
	content: "",
	url: "",
	closeOnEsc: true,
	closeOnOverlayClick: true,
	clone: false,
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
		var c = true;
		$(a).each(function() {
			$(b.target).get(0) == $(this).get(0) && (c = false);
			0 == $(b.target).closest("HTML", $(this).get(0)).length && (c = false)
		});
		return c
	}
},
f = {
	getParentEl: function(a) {
		var b = $(a);
		return b.data("preordermodal") ? b : (b =
			$(a).closest(".preordermodal-container").data("preordermodalParentEl")) ? b : false
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
			return false
		})
	},
	init_el: function(a, b) {
		var c = a.data("preordermodal");
		if (!c) {
			c = b;
			j++;
			c.modalID = j;
			c.overlay.block = $(c.overlay.tpl);
			c.overlay.block.css(c.overlay.css);
			c.container.block = $(c.container.tpl);
			c.body = $(".preordermodal-container_i2", c.container.block);
			b.clone ? c.body.html(a.clone(true)) : (a.before('<div id="preordermodalReserve' + c.modalID + '" style="display: none" />'), c.body.html(a));
			f.prepare_body(c, a);
			c.closeOnOverlayClick && c.overlay.block.add(c.container.block).click(function(b) {
				m.isEventOut($(">*", c.body), b) && a.preordermodal("close")
			});
			c.container.block.data("preordermodalParentEl", a);
			a.data("preordermodal", c);
			e = $.merge(e, a);
			$.proxy(h.show, a)();
			return a;
		}
	},
	init: function(a) {
		a = $.extend(true, {}, g, a);
		f.init_el($(this),$.extend(true, {}, a))
	}
},
h = {
	show: function() {
		var a = f.getParentEl(this);
		if (false === a) $.error("jquery.preordermodal: Uncorrect call");
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
				var c = b.wrap.outerWidth(true);
				b.wrap.css("overflow", "hidden");
				var g = b.wrap.outerWidth(true);
				g != c && b.wrap.css("marginRight", g - c + "px")
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
		if ($.isFunction(this))
			e.each(function() {
				$(this).preordermodal("close")
			});
		else return this.each(function() {
			var a = f.getParentEl(this);
			if (false === a) $.error("jquery.preordermodal: Uncorrect call");
			else {
				var b = a.data("preordermodal");
				false !== b.beforeClose(b, a) && (a.trigger("beforeClose"), e.not(a).last().each(function() {
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
				}), false, e = e.not(a))
			}
		})
	},
	setDefault: function(a) {
		$.extend(true, g, a)
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