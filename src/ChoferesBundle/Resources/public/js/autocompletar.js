(function(window, document, $) {
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
              var item = {
                id: obj.id,
                query: obj.nombre + ' ' + obj.apellido + ' -  Dni: ' + obj.dni,
                nombre: obj.nombre,
                apellido: obj.apellido,
                dni: obj.dni,
                tieneCursoBasico: obj.tieneCursoBasico
              };
              data.push(JSON.stringify(item));
            });

            return process(data);
          },
          error: function(result) {
            console.log('error');
          }
        });
      },
      matcher: function(obj) {
        var item = JSON.parse(obj);
        if (item.query.toLowerCase().indexOf(this.query.trim().toLowerCase()) != -1) {
            return true;
        }
      },
      sorter: function (items) {
        return items.sort();
      },
      highlighter: function (obj) {
        var item = JSON.parse(obj);
        var regex = new RegExp( '(' + this.query + ')', 'gi' );
        return item.query.replace( regex, "<strong>$1</strong>" );
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
          var $tdTieneCursoBasico = (item.tieneCursoBasico) ? '<td>Si</td>' : '<td class="highlight">No</td>';
          $('#choferesCandidatos').append(
            '<tr class="gradeA">'
            + '<input type="hidden" class="chofer" name="chofer[]" value="' + item.id + '"/>'
            + '<td>' + item.nombre + '</td>'
            + '<td>' + item.apellido + '</td>'
            + '<td>' + item.dni + '</td>'
            + $tdTieneCursoBasico
            + '<td class="actions">'
            +   '<button class="btn btn-sm btn-icon btn-pure btn-default on-default edit-row noAgregar" data-toggle="tooltip" data-original-title="borrar"><i class="icon wb-trash " aria-hidden="true"></i></a>'
            + '</td>'
            + '</tr>'
          );

          $(".noAgregar").click(function(){
            $(this).parent().parent().remove();
            if ($('#choferesCandidatos tr').length < 1) {
              $('#addChoferSubmit').addClass("disabled");
              $('#addChoferSubmit').prop("disabled", true);
            }
          });
          $('#addChoferSubmit').removeClass("disabled");
          $('#addChoferSubmit').prop("disabled", false);

        }

        return '';
      }
    });
})(window, document, jQuery);
