(function($){
  $(function(){
    var $prestador = $('#choferesbundle_curso_prestador');
    $prestador.change(function() {
        var $form = $(this).closest('form');
        var data = {};
        data[$prestador.attr('name')] = $prestador.val();
        $.ajax({
          url : $form.attr('action'),
          type: $form.attr('method'),
          data : data,
          success : function(html) {
            $('#choferesbundle_curso_docente').replaceWith(
              $(html).find('#choferesbundle_curso_docente')
            );
            $('#choferesbundle_curso_sede').replaceWith(
              $(html).find('#choferesbundle_curso_sede')
            );
          }
        });
    });
  }); // end of document ready
})(jQuery); // end of jQuery name space
