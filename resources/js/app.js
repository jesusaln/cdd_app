import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Importación de FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core'
import {
  faCog,
  faChevronLeft,
  faTachometerAlt,
  faCalendarAlt,
  faCar,
  faUserCog,
  faToolbox
} from '@fortawesome/free-solid-svg-icons'

// Agregar los iconos a la librería ANTES de montar la app
library.add(faCog, faChevronLeft, faTachometerAlt, faCalendarAlt, faCar, faUserCog, faToolbox)
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faUsers, faBox, faTags, faTrademark, faTruck } from '@fortawesome/free-solid-svg-icons';

// Añadir iconos a la librería global de FontAwesome
library.add(faUsers, faBox, faTags, faTrademark, faTruck);

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
