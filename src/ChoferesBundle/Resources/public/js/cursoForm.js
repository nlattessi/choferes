(function(window, document, $) {
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
          var $docente = $('#choferesbundle_curso_docente');
          $docente.empty();
          var $newDocenteData = $(html).find('#choferesbundle_curso_docente option');
          $newDocenteData.each(function() {
            $docente.append('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
          });
          var $sede = $('#choferesbundle_curso_sede');
          $sede.empty();
          var $newSedeData = $(html).find('#choferesbundle_curso_sede option');
          $newSedeData.each(function() {
            $sede.append('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
          });
        }
      });
  });
})(window, document, jQuery);
