function postForm($form, callback) {
  /*
   * Get all form values
   */
  var values = {};
  $.each($form.serializeArray(), function(i, field) {
    values[field.name] = field.value;
  });

  /*
   * Throw the form values to the server!
   */
  $.ajax({
    type        : $form.attr( 'method' ),
    url         : $form.attr( 'action' ),
    data        : values,
    success     : function(data) {
      callback( data );
    }
  });
}

(function(window, document, $) {
    var $loader   = $('.loader-inner');
    var $form     = $('.formReporteChoferesVigentes');
    var $errorDiv = $('#errorMessage');
    var $reset    = $('button[type="reset"]');
    var $submit   = $('button[type="submit"]')

    $loader.hide();
    $loader.loaders();

    $form.submit(function(e) {
        e.preventDefault();

        $errorDiv.empty();
        $reset.prop("disabled", true);
        $submit.prop("disabled", true);
        $loader.show();

        postForm( $(this), function(response) {
            $loader.hide();
            $reset.prop("disabled", false);
            $submit.prop("disabled", false);

            if (response.result) {
                var $a = $("<a>");
                $a.attr("href", response.file);
                $("body").append($a);
                $a.attr("download", response.name);
                $a[0].click();
                $a.remove();
            } else {
                $('<div/>', {
                    'class': 'alert ng-isolate-scope alert-danger alert-dismissible',
                    'html': '<div ><span class="ng-binding ng-scope">' + response.message + '</span></div>',
                }).appendTo($errorDiv);
            }
        });
    });

})(window, document, jQuery);