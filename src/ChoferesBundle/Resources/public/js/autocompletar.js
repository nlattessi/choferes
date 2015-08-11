(function($){
  $(function(){
    $('#typeahead').typeahead({
      minLength: 3,
      source: function (query, process) {
        return $.ajax({
          url: 'autocompletar',
          type: 'GET',
          data: {
            query: query,
            idcurso: $('#idcurso').val()
          },
          dataType: 'json',
          success: function(result) {
            var data = [];
            $.each(result, function(i, obj) {
              var item = { id: obj.id, nombre: obj.nombre + ' ' + obj.apellido + ' -  Dni: ' + obj.dni };
              data.push(JSON.stringify(item));
            });

            return process(data);
          }
        });
      },
      matcher: function(obj) {
        var item = JSON.parse(obj);
        if (item.nombre.toLowerCase().indexOf(this.query.trim().toLowerCase()) != -1) {
            return true;
        }
      },
      sorter: function (items) {
        return items.sort();
      },
      highlighter: function (obj) {
        var item = JSON.parse(obj);
        var regex = new RegExp( '(' + this.query + ')', 'gi' );
        return item.nombre.replace( regex, "<strong>$1</strong>" );
      },
      updater: function (obj) {
        var item = JSON.parse(obj);
        var exists = false;
        $(".chofer").each(function() {
          if($(this).val() == item.id){
            exists = true;
            return false;
          }
        });
        if(!exists) {
          $('#choferesCandidatos').append(
              '<li><input type="hidden" class="chofer" name="chofer[]" value="' + item.id + '"/>'
              + item.nombre
              + ' <button class="btn btn-mini noAgregar">No agregar</button>'
              + '</li>'
          );
          $(".noAgregar").click(function(){
            $(this).parent().remove();
            if ($('#choferesCandidatos li').length < 1) {
              $('#addChoferSubmit').addClass("disabled");
              $('#addChoferSubmit').prop("disabled", true);
            }
          });
          $('#addChoferSubmit').removeClass("disabled");
          $('#addChoferSubmit').prop("disabled", false);

        }

        return;
      }
    });
  }); // end of document ready
})(jQuery); // end of jQuery name space
