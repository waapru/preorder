/**
 * @author Alexander Belov <waapru@gmail.com>
 */

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
		
		$('[id^="'+self.settings.form_id_preffix+'"] .options a').click(function(){
			var that = $(this),
				o = that.closest('.options');
			$('a',o).removeClass('selected');
			that.addClass('selected');
			$('input',o).val(that.data('sku-id')).attr('value',that.data('sku-id'));
			return false;
		});
		
		$('body').on('click','.preorder-open',function(){
			var sku_id = $(this).data('sku-id'),
				form = $('#'+self.settings.form_id_preffix+sku_id);
			form.find('.error').remove();

				var d = $('#dialog'),
					c = d.find('.content');
				
				c.append(form.clone(true).show());
				
				$('[name="preorder_phone"]',c).inputmask('+7(999)-999-99-99');
				$('[name="preorder_quantity"]',c).inputmask('9');
				
				c.prepend('<a href="#" class="dialog-close">x</a>');
				d.show(function () {
					$("body").addClass("dialog-open");
				});


			// form.arcticmodal({
				// beforeOpen: function(){
					// form.show();
				// },
				// afterClose: function(){
					// form.hide();
				// }
			// });
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
			if ( btn.size() ){
				$('#'+p+sku_id+'-btn').show();
			}
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
						$.arcticmodal('close');
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
						$.arcticmodal('close');
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