import { Html5Qrcode } from 'html5-qrcode';

let html5QrCode = null;
let escaneando = false;
let ultimoCodigo = null;
let ultimoTs = 0;

function agregarEscaneo(lista, item) {
    const vacio = lista.querySelector('.escaneo-vacio');
    if (vacio) vacio.remove();

    const fila = document.createElement('div');
    fila.className = `escaneo-item d-flex justify-content-between align-items-center p-2 mb-2 rounded ${item.ok ? 'bg-success-subtle' : 'bg-danger-subtle'}`;

    if (item.ok) {
        fila.innerHTML = `
            <div>
                <span class="fw-semibold">${item.alumno.apellidos}, ${item.alumno.nombres}</span>
                <div class="small text-muted">${item.alumno.grado} — Sección ${item.alumno.seccion}</div>
            </div>
            <div class="text-end">
                <span class="badge ${item.estado === 'Asistio' ? 'bg-success' : 'bg-warning text-dark'}">${item.estado === 'Asistio' ? 'Asistió' : 'Tardanza'}</span>
                <div class="small text-muted">${item.hora}</div>
            </div>`;
    } else {
        fila.innerHTML = `
            <div><i class="bi bi-x-circle me-2 text-danger"></i>${item.error}</div>
            <div class="small text-muted">${new Date().toLocaleTimeString()}</div>`;
    }

    lista.prepend(fila);
}

async function registrarEscaneo(codigoQr) {
    const lista = document.getElementById('listaEscaneos');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    try {
        const resp = await fetch(window.ASISTENCIA_ESCANEAR_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ codigo_qr: codigoQr }),
        });
        const data = await resp.json();
        agregarEscaneo(lista, data);
    } catch (e) {
        agregarEscaneo(lista, { ok: false, error: 'No se pudo conectar con el servidor.' });
    }
}

function onScanSuccess(decodedText) {
    const ahora = Date.now();
    // Evita reprocesar el mismo código en frames consecutivos (debounce 3s)
    if (decodedText === ultimoCodigo && (ahora - ultimoTs) < 3000) return;
    ultimoCodigo = decodedText;
    ultimoTs = ahora;
    registrarEscaneo(decodedText);
}

export function iniciarEscanerQr() {
    if (escaneando) return;
    const el = document.getElementById('lectorQr');
    if (!el) return;

    html5QrCode = new Html5Qrcode('lectorQr');
    html5QrCode
        .start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            () => {}
        )
        .then(() => { escaneando = true; })
        .catch(() => {
            el.innerHTML = '<div class="alert alert-warning m-0">No se pudo acceder a la cámara. Verifica los permisos del navegador.</div>';
        });
}

export function detenerEscanerQr() {
    if (!escaneando || !html5QrCode) return;
    html5QrCode.stop().then(() => {
        html5QrCode.clear();
        escaneando = false;
    }).catch(() => {});
}

window.iniciarEscanerQr = iniciarEscanerQr;
window.detenerEscanerQr = detenerEscanerQr;
