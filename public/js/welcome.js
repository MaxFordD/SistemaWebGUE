// public/js/welcome.js
(function(){
  // Ejemplo: inicialización de lightbox accesible si fuese necesario
  var modal = document.getElementById('galeriaModal');
  if (!modal) return;
  modal.addEventListener('shown.bs.modal', function(){
    var closeBtn = modal.querySelector('.btn-close');
    if (closeBtn) closeBtn.focus();
  });
})();
