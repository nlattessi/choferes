(function($){
  $(function(){
      $("#autocompletarInput").keyup(function(){
          if($("#autocompletarInput").val().length > 2){
              $.ajax({
                  method: "POST",
                  url: "autocompletar",
                  data: { search: $("#autocompletarInput").val()}
              })
                  .done(function( msg ) {
                      $("#autocompletarDiv").text('');
                      var obj = jQuery.parseJSON( msg );

                      $.each(obj, function(i, item) {
                          $("#autocompletarDiv").html(
                              '<a class="addAuto" nameChofer= '+ item.nombre +' idChofer=' + item.id +' > agregar a: '
                              + item.nombre + '</a><br/>' + $("#autocompletarDiv").html() );
                          console.log(item.nombre);
                          console.log(item.id);
                          $(".addAuto").click(function(){
                              $('form').append(
                                  '<span class="spanChofer">' +
                                  '<span>'+$(this).attr('nameChofer')+'</span> ' +
                                  '<span>eliminar</span>' +
                                  ' <input type="hidden" class: "chofer" name="chofer[]" value="'+$(this).attr('idChofer')+'" />' +
                                  '<br/>' +
                                  '</span>'

                              );
                              $(".spanChofer").click(function(){
                                  $(this).remove();
                              });

                          });
                      });

                  });
          }
      });
      $(".addAuto").click(function(){
          alert($this)
      });

  }); // end of document ready
})(jQuery); // end of jQuery name space
