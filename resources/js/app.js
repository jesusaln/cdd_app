import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Importación de FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Importar iconos existentes
import {
    faCog,
    faChevronLeft,
    faTachometerAlt,
    faCalendarAlt,
    faCar,
    faUserCog,
    faToolbox,
    faUsers,
    faBox,
    faTags,
    faTrademark,
    faTruck,
    faChartBar,
    faFileAlt,
    faDollarSign,
    faCartShopping,
    faFileInvoiceDollar,
    faWarehouse,
    faWrench,
    // ICONOS FALTANTES PARA EL FORMULARIO DE CLIENTE
    faUser,                    // Para "Información General"
    faMapMarkerAlt,           // Para "Dirección"
    faInfoCircle,             // Para "Información Adicional"
    faExclamationTriangle,    // Para alertas de error
    faCheckCircle,            // Para confirmaciones y validaciones
    faSpinner,                // Para estados de carga
    faCheck,                  // Para botón de submit
    faRedo,                   // Para botón de limpiar
    faSave,                   // Para guardar borrador
    faExclamationCircle,      // Para validaciones de RFC
    faEnvelope,               // Para email (opcional)
    faPhone,                  // Para teléfono (opcional)
    faHome,                   // Para dirección (opcional)
    faEdit,                   // Para editar (opcional)
    faPlus,                   // Para crear nuevo (opcional)
    faTimes,                  // Para cerrar/cancelar (opcional)
    faEye,                    // Para mostrar (opcional)
    faEyeSlash                // Para ocultar (opcional)
} from '@fortawesome/free-solid-svg-icons'

// Agregar todos los iconos a la librería
library.add(
    // Iconos existentes
    faCog,
    faChevronLeft,
    faTachometerAlt,
    faCalendarAlt,
    faCar,
    faUserCog,
    faToolbox,
    faUsers,
    faBox,
    faTags,
    faTrademark,
    faTruck,
    faChartBar,
    faFileAlt,
    faDollarSign,
    faCartShopping,
    faFileInvoiceDollar,
    faWarehouse,
    faWrench,
    // Iconos nuevos para el formulario
    faUser,
    faMapMarkerAlt,
    faInfoCircle,
    faExclamationTriangle,
    faCheckCircle,
    faSpinner,
    faCheck,
    faRedo,
    faSave,
    faExclamationCircle,
    faEnvelope,
    faPhone,
    faHome,
    faEdit,
    faPlus,
    faTimes,
    faEye,
    faEyeSlash
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .component('font-awesome-icon', FontAwesomeIcon)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
