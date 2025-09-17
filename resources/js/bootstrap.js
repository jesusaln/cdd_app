import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configurar token CSRF para solicitudes POST, PUT, DELETE
let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.warn('CSRF token not found in meta tags');
}

// Interceptor para manejar errores de respuesta
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            console.error('CSRF token mismatch - page may need to be refreshed');
        }
        return Promise.reject(error);
    }
);

// Importar configuración de notificaciones
import './Utils/notyf';
