$(window).load(function(){
	$('.-bag-minitext-edit-btn').on('click', function() {
		var _this = $(this),
			minitexts = $('.-bag-minitext-'+_this.attr('data-id')),
			minitext = _this.parents('.-bag-minitext-wr:first').find('.-bag-minitext'),
			loader = _this.parents('.-bag-minitext-wr:first').find('.-bag-minitext-loader'),
			l = minitext.html().length;
		
		if (!_this.hasClass('-bag-minitext-edit-btn-ok')) {
			_this.addClass('-bag-minitext-edit-btn-ok');
			minitext.attr('contenteditable', 'true').focus();
		} else {

			loader.show();
			_this.hide();
			
			var flag = false,
				flag2 = false;
			
			setTimeout(function() {
				flag = true;
				
				if (flag2) {
					loader.hide();
					_this.show();
				}
			}, 500);
			
			$.post('/mpanel/structure/minitext_proc_edit', {'minitext': minitext.html(), 'minitext_id': _this.attr('data-id')}, function(data) {
				if (data.error == 0) {		
					_this.removeClass('-bag-minitext-edit-btn-ok');
					minitext.removeAttr('contenteditable').focusout();
					minitexts.html(minitext.html());
				} else {
					alert(data.errorInfo);
				}
				
				flag2 = true;

				if (flag) {
					loader.hide();
					_this.show();	
				}

			}, 'json');
		}
	});
});

