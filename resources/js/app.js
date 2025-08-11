import './bootstrap';
import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Importación de FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Importar iconos específicos
import {
  faChevronRight,
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
  faEyeSlash,
  faCopy,
  faPrint,
  faTrash,
  faDatabase,
} from '@fortawesome/free-solid-svg-icons';

// Agregar todos los iconos a la librería
library.add(
  faChevronRight,
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
  faEyeSlash,
  faCopy,
  faPrint,
  faTrash,
  faDatabase,
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
