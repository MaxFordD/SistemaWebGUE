/**
 * Confirmación de eliminación para formularios
 * Se aplica a todos los formularios con clase 'delete-form'
 */

document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los formularios de eliminación
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Personalizar mensaje según el contexto
            const confirmMessage = '¿Estás seguro de que deseas eliminar esta noticia?\n\nEsta acción no se puede deshacer.';

            if (confirm(confirmMessage)) {
                // Si confirma, enviar el formulario
                form.submit();
            }
        });
    });
});
