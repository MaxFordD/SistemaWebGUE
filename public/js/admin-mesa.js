(function(){
  // Autosubmit de selects con clase js-auto-submit
  document.addEventListener('change', function(e){
    var sel = e.target;
    if (sel && sel.classList && sel.classList.contains('js-auto-submit')){
      var form = sel.closest('form');
      if (form) form.submit();
    }
  });

  // Confirmación de formularios con clase js-confirmable
  document.addEventListener('submit', function(e){
    var form = e.target;
    if (form && form.classList && form.classList.contains('js-confirmable')){
      var msg = form.getAttribute('data-confirm') || '¿Confirmar acción?';
      if (!window.confirm(msg)){
        e.preventDefault();
      }
    }
  });
})();
