document.addEventListener("DOMContentLoaded", () => {
    const pwd = document.getElementById('password');
    const toggle = document.getElementById('passwordToggle');
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('submitBtn');
    const spinner = btn.querySelector('.spinner-border');

    // Mostrar / ocultar contraseña
    if (toggle && pwd) {
        toggle.addEventListener('click', () => {
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
        });
    }

    // Prevenir doble envío
    if (form && btn) {
        form.addEventListener('submit', () => {
            btn.disabled = true;
            spinner.classList.remove('d-none');
        });
    }
});
