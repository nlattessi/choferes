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
                              '<a class="addAuto" idChofer=' + item.id +' >'
                              + item.nombre + '</a><br/>' + $("#autocompletarDiv").html() );
                          console.log(item.nombre);
                          console.log(item.id);
                          $(".addAuto").click(function(){
                              console.log($(this).attr('idChofer'));
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
