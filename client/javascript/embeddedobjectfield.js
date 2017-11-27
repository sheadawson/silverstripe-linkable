;(function ($) {
	$('.embeddedObjectLoad').entwine({
		onclick: function () {
			var params = {
				'SecurityID': $('input[name=SecurityID]').val(),
				'URL': $(this).parent().find('input[type=text]').val()
			};
			var container = $(this).parents('div.embeddedobject');
			var button = this;
			var buttonText = button.val();
			button.val('Loading').prop('disabled', 'disabled');

			$.post($(this).data('href'), params, function (data) {
				button.val(buttonText).removeAttr('disabled');
				if (data && data.length) {
					container.html(data);
					delete container;
				}
			})
		}
	})
})(jQuery);
