jQuery.entwine("linkfield", function($) {

	$("input.link").entwine({
		Loading: null,
		Dialog: null,
		URL: null,
		onmatch: function() {
			var self = this;
			this.setDialog(self.siblings('.linkfield-dialog:first'));

			var form = this.parents('form');
				formUrl = form.attr('action'),
				formUrlParts = formUrl.split('?'),
				formUrl = formUrlParts[0],
				url = encodeURI(formUrl) + '/field/' + this.attr('name') + '/LinkFormHTML';

			if(self.val().length){
				url = url + '?LinkID=' + self.val();
			}else{
				url = url + '?LinkID=0';
			}

			if(typeof formUrlParts[1] !== 'undefined') {
				url = url + '&' + formUrlParts[1];
			}

			this.setURL(url);

			// configure the dialog
			var windowHeight = $(window).height();

			this.getDialog().data("field", this).dialog({
				autoOpen: false,
				width: $(window).width()	* 80 / 100,
				height: $(window).height() * 80 / 100,
				modal: true,
				title: this.data('dialog-title'),
				position: { my: "center", at: "center", of: window }
			});

			// submit button loading state while form is submitting
			this.getDialog().on("click", "button", function() {
				$(this).addClass("loading ui-state-disabled");
			});

			// handle dialog form submission
			this.getDialog().on("submit", "form", function() {

				var dlg = self.getDialog().dialog(),
					options = {};

				options.success = function(response) {
					if($(response).is(".field")) {
						self.getDialog().empty().dialog("close");
						self.parents('.field:first').replaceWith(response);
						form.addClass('changed');
					} else {
						self.getDialog().html(response);
					}
				}

				$(this).ajaxSubmit(options);

				return false;
			});
		},

		onunmatch: function () {
			var self = this;
			$('.linkfield-dialog.ui-dialog-content').filter(function(){
				return self[0] == $(this).data("field")[0];
			}).remove();
		},
		showDialog: function(url) {
			var dlg = this.getDialog();

			dlg.empty().dialog("open").parent().addClass("loading");

			dlg.load(this.getURL(), function(){
				dlg.parent().removeClass("loading");
			});
		}
	});

	$(".linkfield-button").entwine({
		onclick: function() {
			this.siblings('input.link').showDialog();
			return false;
		},
	});

	$(".linkfield-remove-button").entwine({
		onclick: function() {
			var form = this.parents('form');
			var formUrl = form.attr('action'),
				formUrlParts = formUrl.split('?'),
				formUrl = formUrlParts[0],
				url = encodeURI(formUrl) + '/field/' + this.siblings('input:first').prop('name') + '/doRemoveLink';

			if(typeof formUrlParts[1] !== 'undefined') {
				url = url + '&' + formUrlParts[1];
			}
			var holder = this.parents('.field:first');
			this.parents('.middleColumn:first').html("<img src='framework/images/network-save.gif' />");
			holder.load(url, function() {
				 form.addClass('changed');
			});

			return false;
		},
	});
});
