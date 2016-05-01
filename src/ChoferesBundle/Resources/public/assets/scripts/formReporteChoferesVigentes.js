function startSubmit($errorDiv, $reset, $submit, $loader) {
  $errorDiv.empty();
  $reset.prop("disabled", true);
  $submit.prop("disabled", true);
  $loader.show();
}

function endSubmit($reset, $submit, $loader, $input) {
  $loader.hide();
  $reset.prop("disabled", false);
  $submit.prop("disabled", false);
  $input.val("");
}

(function(window, document, $) {
  var $loader   = $('.loader-inner');
  var $form     = $('.formReporteChoferesVigentes');
  var $errorDiv = $('#errorMessage');
  var $reset    = $('button[type="reset"]');
  var $submit   = $('button[type="submit"]');
  var $input    = $('.fechaVigencia');

  $loader.hide();
  $loader.loaders();

  $form.submit(function(e) {
    $.fileDownload($(this).prop('action'), {
      successCallback: function(url) {
        endSubmit($reset, $submit, $loader, $input);
      },
      failCallback: function(responseHtml, url, error) {
        endSubmit($reset, $submit, $loader, $input);
        var jsonResult = $.parseJSON(responseHtml.substring(responseHtml.indexOf("{"), responseHtml.lastIndexOf("}") + 1));
        $('<div/>', {
          'class': 'alert ng-isolate-scope alert-danger alert-dismissible',
          'html': '<div ><span class="ng-binding ng-scope">' + jsonResult.message + '</span></div>',
        }).appendTo($errorDiv);
      },
      httpMethod: "POST",
      data: $(this).serialize()
    });
    e.preventDefault();
    startSubmit($errorDiv, $reset, $submit, $loader);
  });
})(window, document, jQuery);
