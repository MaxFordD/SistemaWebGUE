/**
 * Scroll to Top Button
 * Muestra un botón flotante para volver al inicio de la página
 */
(function() {
  'use strict';

  const scrollButton = document.getElementById('scrollToTop');

  if (!scrollButton) return;

  // Mostrar/ocultar botón según scroll
  window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
      scrollButton.classList.add('visible');
    } else {
      scrollButton.classList.remove('visible');
    }
  }, { passive: true });

  // Scroll suave al hacer clic
  scrollButton.addEventListener('click', function() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
})();
