(function(window, document, $) {

  // Dispara confirm ante click en 'Cancelar Curso'
  $(".cancelarCurso").on("click",function(e) {
    return confirm('¿Se encuentra seguro de cancelar el curso?');
  });

})(window, document, jQuery);
