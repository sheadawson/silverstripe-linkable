import $ from 'jquery';

window.ss = window.ss || {};


$.entwine('ss', ($) => {
  $('.embeddedObjectLoad').entwine({
    onclick() {
      const params = {
        SecurityID: $('input[name=SecurityID]').val(),
        URL: $(this).parent().find('input[type=text]').val()
      };
      const container = $(this).parents('div.embeddedobject');
      const button = this;
      const buttonText = button.val();
      button.val('Loading').prop('disabled', 'disabled');

      $.post($(this).data('href'), params, (data) => {
        button.val(buttonText).removeAttr('disabled');
        if (data && data.length) {
          container.html(data);
        }
      });
    }
  });
});
