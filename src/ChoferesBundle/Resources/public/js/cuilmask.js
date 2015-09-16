$(document).ready(function(){
   $("#choferesbundle_prestador_cuit")
    .inputmask(
      "99-99999999-9",
      {
        //"placeholder": "xx-xxxxxxxx-x",
        "removeMaskOnSubmit": true,
        "clearMaskOnLostFocus": false,
      }
    );
});
