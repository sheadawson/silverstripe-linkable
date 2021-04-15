import $ from 'jquery';

window.ss = window.ss || {};

function urlForInline(self, url, field_action) {
  const inline_form = self.closest('.form.element-editor-editform__form');
  if (inline_form.length > 0) {
    let base_action = inline_form.attr('action');
    let link_name = self.siblings('input.link').attr('name');
    if (!link_name) {
      link_name = self.closest('input.link').attr('name');
    }
    return encodeURI(`${base_action}/field/${link_name}/${field_action}`);
  }
  return url;
}

function updateElements(self) {
  const inline_form = self.closest('.form.element-editor-editform__form');
  if (inline_form.length > 0) {
    let link_input = self.siblings('input.link');
    if (link_input.length == 0) {
      link_input = self.closest('input.link');
    }
    const link_value = link_input.val();
    const link_name = link_input.attr('name');
    const form_name = inline_form.attr('id').replace('Form_', '');
    const elements = $('input[type=hidden][value*="ElementForm"].no-change-track');

    let data = JSON.parse(elements.val());
    data[form_name][link_name] = link_value == '' ? '0' : link_value;
    let newval = JSON.stringify(data);
    elements.val(newval);
  }
}

$.entwine('ss', ($) => {
  $('input.link').entwine({
    Loading: null,
    Dialog: null,
    URL: null,
    onmatch() {
      const self = this;
      this.setDialog(self.siblings('.linkfield-dialog:first'));

      const form = this.parents('form');
      let formUrl = form.attr('action');
      const formUrlParts = formUrl.split('?');
      let url = `${encodeURI(formUrl)}/field/${this.attr('name')}/LinkFormHTML`;
      formUrl = formUrlParts[0];

      // override url if inline
      url = urlForInline(this, url, 'LinkFormHTML');

      if (self.val().length) {
        url = `${url}?LinkID=${self.val()}`;
      } else {
        url += '?LinkID=0';
      }

      if (typeof formUrlParts[1] !== 'undefined') {
        url = `${url}&${formUrlParts[1]}`;
      }

      // add extra query params if provided
      const extraQuery = self.data('extra-query');
      if (typeof extraQuery !== 'undefined') {
        url = `${url}${extraQuery}`;
      }

      this.setURL(url);

      // update data for inline editing
      updateElements(this);

      // configure the dialog
      this.getDialog().data('field', this).dialog({
        autoOpen: false,
        width: $(window).width() * (80 / 100),
        height: $(window).height() * (80 / 100),
        modal: true,
        title: this.data('dialog-title'),
        position: { my: 'center', at: 'center', of: window }
      });

      // submit button loading state while form is submitting
      this.getDialog().on('click', 'button', function () {
        $(this).addClass('loading ui-state-disabled');
      });

      // handle dialog form submission
      this.getDialog().on('submit', 'form', function () {
        const options = {};
        options.success = function (response) {
          if ($(response).is('.field')) {
            self.getDialog().empty().dialog('close');
            self.parents('.field:first').replaceWith(response);
            form.addClass('changed');
          } else {
            self.getDialog().html(response);
          }
        };

        $(this).ajaxSubmit(options);

        return false;
      });
    },

    onunmatch() {
      const self = this;
      $('.linkfield-dialog.ui-dialog-content').filter(function () {
        return self[0] === $(this).data('field')[0];
      }).remove();
    },
    showDialog() {
      const dlg = this.getDialog();
      dlg.empty().dialog('open').parent().addClass('loading');
      dlg.load(this.getURL(), () => {
        dlg.parent().removeClass('loading');
      });
    }
  });

  $('.linkfield-button').entwine({
    onclick() {
      this.siblings('input.link').showDialog();
      return false;
    },
  });

  $('.linkfield-remove-button').entwine({
    onclick() {
      const form = this.parents('form');
      let formUrl = form.attr('action');
      const formUrlParts = formUrl.split('?');
      let url = `${encodeURI(formUrl)}/field/${this.siblings('input:first').prop('name')}/doRemoveLink`;

      // override url if inline
      url = urlForInline(this, url, 'doRemoveLink');

      formUrl = formUrlParts[0];

      if (typeof formUrlParts[1] !== 'undefined') {
        url = `${url}&${formUrlParts[1]}`;
      }
      const holder = this.parents('.field:first');
      this.parents('.middleColumn:first').html("<img src='framework/images/network-save.gif' />");
      holder.load(url, () => {
        form.addClass('changed');
        holder.replaceWith(holder.html());
      });

      return false;
    },
  });
});
