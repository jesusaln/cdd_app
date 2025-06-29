import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Importación de FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'; // Importar el componente Vue de FontAwesome

// Importa *todos* los iconos 'solid' que utilizas en tu aplicación.
// Asegúrate de que 'faFileInvoiceDollar' esté en esta lista.
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
    faChartBar, // Icono para Reportes Largos
    faFileAlt, // Icono para Cotizaciones
    faDollarSign, // Icono para Ventas Realizadas
    faCartShopping, // Icono para Compras a Proveedores
    faFileInvoiceDollar, // <--- ¡ESTE ES EL ICONO QUE FALTABA Y SE HA AÑADIDO!
    faWarehouse, // Icono para Almacenes
    faWrench // Icono para Servicios
} from '@fortawesome/free-solid-svg-icons'

// Agregar todos los iconos necesarios a la librería global de FontAwesome
// Es crucial que todos los iconos que uses en tu HTML estén añadidos aquí.
library.add(
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
    faFileInvoiceDollar, // <--- ¡Añadido a la librería!
    faWarehouse,
    faWrench
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .component('font-awesome-icon', FontAwesomeIcon) // Registrar globalmente FontAwesome
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
