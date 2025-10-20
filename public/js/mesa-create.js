(function () {
  'use strict';

  var input = document.getElementById('archivos');
  var preview = document.getElementById('archivos-preview');
  var form = document.getElementById('mesaForm');

  if (!input || !preview) return;

  // Preview mejorado de archivos con validación
  input.addEventListener('change', function () {
    preview.innerHTML = '';
    if (!this.files || this.files.length === 0) return;

    var container = document.createElement('div');
    container.className = 'row g-2';

    Array.prototype.slice.call(this.files).forEach(function (file, index) {
      // Validar tamaño (5MB)
      var maxSize = 5 * 1024 * 1024; // 5MB
      var isValid = file.size <= maxSize;
      var sizeMB = (file.size / 1024 / 1024).toFixed(2);
      var sizeText = sizeMB < 1 ? (file.size / 1024).toFixed(1) + ' KB' : sizeMB + ' MB';

      // Determinar icono según tipo
      var icon = 'file-earmark';
      if (file.type.includes('pdf')) icon = 'file-pdf';
      else if (file.type.includes('word')) icon = 'file-word';
      else if (file.type.includes('image')) icon = 'file-image';

      var col = document.createElement('div');
      col.className = 'col-12 col-sm-6 col-md-4';

      var card = document.createElement('div');
      card.className = 'card file-preview-card ' + (isValid ? '' : 'border-danger');
      card.innerHTML = `
        <div class="card-body p-2">
          <div class="d-flex align-items-start">
            <i class="bi bi-${icon} fs-3 text-primary me-2"></i>
            <div class="flex-grow-1 min-w-0">
              <div class="small fw-semibold text-truncate" title="${file.name}">${file.name}</div>
              <div class="text-muted" style="font-size: 0.75rem">${sizeText}</div>
              ${!isValid ? '<div class="text-danger small">Archivo muy grande</div>' : ''}
            </div>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" data-index="${index}">
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
        </div>
      `;

      col.appendChild(card);
      container.appendChild(col);
    });

    preview.appendChild(container);

    // Eliminar archivos individualmente
    preview.querySelectorAll('[data-index]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var dt = new DataTransfer();
        var files = Array.from(input.files);
        var indexToRemove = parseInt(this.getAttribute('data-index'));

        files.forEach(function(file, i) {
          if (i !== indexToRemove) {
            dt.items.add(file);
          }
        });

        input.files = dt.files;
        // Trigger change event
        var event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
      });
    });
  });

  // Validación del formulario con feedback visual mejorado
  if (form) {
    var submitButton = form.querySelector('button[type="submit"]');
    var originalButtonText = submitButton ? submitButton.innerHTML : '';
    var isSubmitting = false;

    form.addEventListener('submit', function(e) {
      // Prevenir múltiples envíos
      if (isSubmitting) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }

      // Validar formulario
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated');

        // Mostrar alerta de campos requeridos
        var firstInvalidField = form.querySelector(':invalid');
        if (firstInvalidField) {
          // Scroll al primer campo inválido
          firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalidField.focus();

          // Mostrar mensaje de error
          showValidationMessage('Por favor, complete todos los campos obligatorios marcados con (*)', 'error');
        }
        return;
      }

      // Si es válido, marcar como enviando y cambiar botón
      form.classList.add('was-validated');
      isSubmitting = true;

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';
      }
    });

    // Resetear estado si hay error de servidor
    window.addEventListener('pageshow', function() {
      isSubmitting = false;
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
      }
    });
  }

  // Función para mostrar mensajes de validación
  function showValidationMessage(message, type) {
    // Buscar si ya existe un mensaje
    var existingAlert = document.querySelector('.validation-alert');
    if (existingAlert) {
      existingAlert.remove();
    }

    // Crear alerta
    var alertClass = type === 'error' ? 'alert-danger' : 'alert-warning';
    var iconClass = type === 'error' ? 'bi-exclamation-circle-fill' : 'bi-exclamation-triangle-fill';

    var alert = document.createElement('div');
    alert.className = 'alert ' + alertClass + ' alert-dismissible fade show validation-alert shadow-sm';
    alert.setAttribute('role', 'alert');
    alert.style.animation = 'slideInDown 0.3s ease-out';
    alert.innerHTML = `
      <div class="d-flex align-items-center">
        <i class="bi ${iconClass} fs-4 me-3"></i>
        <div class="flex-grow-1">${message}</div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    `;

    // Insertar antes del formulario
    var formCard = document.querySelector('.mesa-form-card');
    if (formCard) {
      formCard.parentNode.insertBefore(alert, formCard);
    }

    // Auto-cerrar después de 5 segundos
    setTimeout(function() {
      if (alert && alert.parentNode) {
        alert.classList.remove('show');
        setTimeout(function() {
          if (alert.parentNode) alert.remove();
        }, 150);
      }
    }, 5000);
  }

  // Validación en tiempo real del DNI
  var dniInput = document.getElementById('dni');
  if (dniInput) {
    dniInput.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '').substring(0, 8);
    });
  }
})();
