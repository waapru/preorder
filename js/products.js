(function($) {
	$.products.preorderProductsAction = function (params) {
		this.load('?plugin=preorder&module=list', function () {
			$("#s-sidebar li.selected").removeClass('selected');
			$("#s-action-preorder").addClass('selected');
			document.title = 'Предзаказанные товары';
		});
	}
	
	$('body').on('click','#s-preorder-load-list',function(){
		$(this).prepend('<i class="icon16 loading"></i>')
		var self = $(this),
			d = $('<div />').load('?plugin=preorder&module=list&offset='+$(this).data('offset'),function(){
				console.log('tr=',$('#shop-preorder-plugin-table tr',d));
				$('#shop-preorder-plugin-table tr:last').after($('#shop-preorder-plugin-table tbody tr',d));
				$('#shop-preorder-plugin-table + p').replaceWith($('#shop-preorder-plugin-table + p',d));
			});
			$('#s-preorder-load-list .loading').remove();
		return false;
	})
})(jQuery);