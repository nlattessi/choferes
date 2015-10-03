(function(window, document, $) {
   $("#choferesbundle_prestador_cuit")
    .inputmask(
      "99-99999999-9",
      {
        "removeMaskOnSubmit": true,
        "clearMaskOnLostFocus": false,
      }
    );
    $("#choferesbundle_prestadorfiltertype_cuit")
     .inputmask(
       "99-99999999-9",
       {
         "removeMaskOnSubmit": true,
         "clearMaskOnLostFocus": false,
       }
     );
})(window, document, jQuery);
