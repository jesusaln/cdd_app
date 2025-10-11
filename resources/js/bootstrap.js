import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configurar axios para CSRF (XSRF cookie)
window.axios.defaults.withCredentials = true;
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// Interceptor para manejar errores 419 y recuperación automática
let isRefreshingCsrf = false;

window.axios.interceptors.response.use(
    response => response,
    async (error) => {
        const { response, config } = error || {};

        if (response?.status === 419 && !config.__retried) {
            try {
                if (!isRefreshingCsrf) {
                    isRefreshingCsrf = true;
                    // Para app Inertia "web" normal, basta un ping GET:
                    await axios.get('/sanctum/csrf-cookie');
                }
            } catch (refreshError) {
                console.warn('Error refreshing CSRF token:', refreshError);
            } finally {
                isRefreshingCsrf = false;
            }
            config.__retried = true;
            return axios(config); // reintenta 1 vez
        }

        return Promise.reject(error);
    }
);

// Importar configuración de notificaciones
import './Utils/notyf';
