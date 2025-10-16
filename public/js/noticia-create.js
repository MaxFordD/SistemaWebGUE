/**
 * noticia-create.js
 * JavaScript para el formulario de crear noticia con múltiples archivos
 */

(function() {
    'use strict';

    const archivosInput = document.getElementById('archivos');
    const previewContainer = document.getElementById('archivos-preview');

    if (!archivosInput || !previewContainer) return;

    let archivosSeleccionados = new DataTransfer();

    // Manejar la selección de archivos
    archivosInput.addEventListener('change', function(e) {
        const nuevosArchivos = Array.from(e.target.files);

        nuevosArchivos.forEach(file => {
            // Validar tamaño (2MB máximo)
            if (file.size > 2 * 1024 * 1024) {
                alert(`El archivo "${file.name}" excede el tamaño máximo de 2MB`);
                return;
            }

            // Agregar al DataTransfer
            archivosSeleccionados.items.add(file);
        });

        // Actualizar el input con los archivos acumulados
        archivosInput.files = archivosSeleccionados.files;

        // Mostrar preview
        mostrarPreview();
    });

    function mostrarPreview() {
        previewContainer.innerHTML = '';

        if (archivosSeleccionados.files.length === 0) return;

        const row = document.createElement('div');
        row.className = 'row g-3';

        Array.from(archivosSeleccionados.files).forEach((file, index) => {
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3';

            const card = document.createElement('div');
            card.className = 'card h-100 shadow-sm position-relative';

            // Botón para eliminar
            const btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 m-2';
            btnEliminar.style.zIndex = '10';
            btnEliminar.innerHTML = '<i class="bi bi-x-lg"></i>';
            btnEliminar.onclick = () => eliminarArchivo(index);

            const cardBody = document.createElement('div');
            cardBody.className = 'card-body p-2 d-flex flex-column';

            // Preview del archivo
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'img-fluid rounded mb-2';
                img.style.maxHeight = '150px';
                img.style.objectFit = 'cover';
                img.style.width = '100%';

                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                cardBody.appendChild(img);
            } else {
                // Icono para documentos
                const iconDiv = document.createElement('div');
                iconDiv.className = 'text-center mb-2';
                iconDiv.style.fontSize = '3rem';

                let iconClass = 'bi-file-earmark';
                if (file.type.includes('pdf')) iconClass = 'bi-file-pdf';
                else if (file.type.includes('word')) iconClass = 'bi-file-word';
                else if (file.type.includes('excel') || file.type.includes('spreadsheet')) iconClass = 'bi-file-excel';

                iconDiv.innerHTML = `<i class="bi ${iconClass} text-primary"></i>`;
                cardBody.appendChild(iconDiv);
            }

            // Nombre y tamaño del archivo
            const fileName = document.createElement('small');
            fileName.className = 'text-muted text-truncate d-block';
            fileName.title = file.name;
            fileName.textContent = file.name;

            const fileSize = document.createElement('small');
            fileSize.className = 'text-muted';
            fileSize.textContent = formatBytes(file.size);

            cardBody.appendChild(fileName);
            cardBody.appendChild(fileSize);

            card.appendChild(btnEliminar);
            card.appendChild(cardBody);
            col.appendChild(card);
            row.appendChild(col);
        });

        // Contador de archivos
        const contador = document.createElement('div');
        contador.className = 'alert alert-info d-flex align-items-center mb-3';
        contador.innerHTML = `
            <i class="bi bi-info-circle me-2"></i>
            <span><strong>${archivosSeleccionados.files.length}</strong> archivo(s) seleccionado(s)</span>
        `;

        previewContainer.appendChild(contador);
        previewContainer.appendChild(row);
    }

    function eliminarArchivo(index) {
        const dt = new DataTransfer();

        Array.from(archivosSeleccionados.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });

        archivosSeleccionados = dt;
        archivosInput.files = archivosSeleccionados.files;

        mostrarPreview();
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Prevenir envío múltiple del formulario
    const form = document.getElementById('noticiaForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publicando...';
            }
        });
    }
})();
