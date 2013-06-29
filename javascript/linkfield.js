jQuery.entwine("linkfield", function($) {

	$("input.link").entwine({
		Loading: null,
		Dialog:  null,
		URL:  null,
		onmatch: function() {
			var self = this;
			this.setDialog(self.siblings('.linkfield-dialog:first'));

			var url = this.parents('form').attr('action') + '/field/' + this.attr('name') + '/LinkFormHTML';
			if(self.val().length){
				url = url + '?LinkID=' + self.val();
			}else{
				url = url + '?LinkID=0';
			}
			this.setURL(url);

			// configure the dialog
			var windowHeight = $(window).height();

			this.getDialog().data("field", this).dialog({
				autoOpen: 	false,
				width:   	$(window).width()  * 80 / 100,
				height:   	$(window).height() * 80 / 100,
				modal:    	true,
				title: 		this.data('dialog-title'),
				position: 	{ my: "center", at: "center", of: window }
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
					} else {
						self.getDialog().html(response);
					}
				}

				$(this).ajaxSubmit(options);

				return false;
			});
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
			var url = this.parents('form').attr('action') + '/field/' + this.siblings('input:first').prop('name') + '/doRemoveLink';
			var holder = this.parents('.field:first');
			this.parents('.middleColumn:first').html("<img src='framework/images/network-save.gif' />");
			holder.load(url);
			return false;
		},
	});
});


