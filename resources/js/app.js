import './bootstrap'
import '../css/app.css'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { route } from 'ziggy-js'
import { Ziggy } from './ziggy'
// FontAwesome core + componente
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
// === Íconos SOLID que usa la app ===
// Mantén este bloque ordenado alfabéticamente por sección para evitar duplicados.
import {
  // Navegación / flechas
  faPaperPlane,
  faChevronLeft,
  faChevronRight,
  // Dashboard / secciones
  faTachometerAlt,
  faChartBar,
  // Clientes
  faUsers,
  faCalendarAlt,
  // Inventario
  faLaptop,            // 👈 NUEVO (Equipos)
  faBox,
  faWrench,
  faTags,
  faTrademark,
  faWarehouse,
  // Operaciones
  faFileAlt,
  faTruck,
  faDollarSign,
  faCartShopping,
  faFileInvoiceDollar,
  faFileContract,      // 👈 NUEVO (Rentas)
  // Taller
  faCar,
  faTools,
  faUserCog,
  faToolbox,
  faCogs,               // 👈 NUEVO (cogs)
  // Administración / usuario
  faUser,
  faDatabase,
  // UI utilitarios
  faCog,
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
  faEnvelopeOpen,
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
  // Iconos adicionales faltantes
  faExchangeAlt,        // 👈 NUEVO (exchange-alt)
  faTimesCircle,        // 👈 NUEVO (times-circle)
  faCircleExclamation,  // 👈 NUEVO (circle-exclamation)
  faCalculator,         // 👈 NUEVO (calculator)
  faShoppingCart,       // 👈 NUEVO (shopping-cart)
  faClock,              // 👈 NUEVO (clock)
  faFileText,           // 👈 NUEVO (document-text)
  faFile,               // 👈 NUEVO (document)
  // Acciones de rentas (contratos)
  faSyncAlt,           // 👈 NUEVO (Renovar)
  faPause,             // 👈 NUEVO (Suspender)
  faPlay,              // 👈 NUEVO (Reactivar)
  faClipboardList,     // Añadir clipboard-list
  faArrowRight,        // Añadir arrow-right
  faArrowLeft,         // Añadir arrow-left
  // Íconos faltantes para sidebar reportes
  faHandshake,         // 👈 NUEVO (handshake)
  faChartLine,          // 👈 NUEVO (chart-line)
  faHistory,           // 👈 NUEVO (history)
  faMoneyBillWave      // 👈 NUEVO (money-bill-wave)
} from '@fortawesome/free-solid-svg-icons'
// Agrega todos los íconos necesarios a la librería
library.add(
    faPaperPlane,
  faChevronLeft, faChevronRight,
  faTachometerAlt, faChartBar,
  faUsers, faCalendarAlt,
  faLaptop, faBox, faWrench, faTags, faTrademark, faWarehouse,
  faFileAlt, faTruck, faDollarSign, faCartShopping, faFileInvoiceDollar, faFileContract,
  faCar, faTools, faUserCog, faToolbox, faCogs,
  faUser, faDatabase,
  faCog, faMapMarkerAlt, faInfoCircle, faExclamationTriangle, faCheckCircle, faSpinner, faCheck, faRedo, faSave, faExclamationCircle,
  faEnvelope, faEnvelopeOpen, faPhone, faHome, faEdit, faPlus, faTimes, faEye, faEyeSlash, faCopy, faPrint, faTrash,
  faSyncAlt, faPause, faPlay,
  faClipboardList,     // Asegúrate de agregar los nuevos íconos aquí
  faArrowRight,        // Asegúrate de agregar los nuevos íconos aquí
  faArrowLeft,         // Asegúrate de agregar los nuevos íconos aquí
  // Iconos adicionales faltantes
  faExchangeAlt,       // exchange-alt
  faTimesCircle,       // times-circle
  faCircleExclamation, // circle-exclamation
  faCalculator,        // calculator
  faShoppingCart,      // shopping-cart
  faClock,             // clock
  faFileText,          // document-text
  faFile,              // document
  faHandshake,         // handshake
  faChartLine,          // chart-line
  faHistory,           // history
  faMoneyBillWave      // money-bill-wave
)
const appName = import.meta.env.VITE_APP_NAME || 'Laravel'
// Mejor práctica: desactivar devtools en producción
if (import.meta.env.PROD) {
  // @ts-ignore
  window.__VUE_DEVTOOLS_GLOBAL_HOOK__ = { emit: () => {}, on: () => {}, once: () => {}, off: () => {}, Vue: null }
}
createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      // Registrar ambos nombres para evitar discrepancias en plantillas
      .component('FontAwesomeIcon', FontAwesomeIcon)
      .component('font-awesome-icon', FontAwesomeIcon)

    // Hacer route disponible globalmente
    app.config.globalProperties.$route = (name, parameters, absolute, config) =>
      route(name, parameters, absolute, config ?? Ziggy)

    app.mount(el)
  },
  progress: {
    color: '#4B5563', // gris oscuro acorde a tu UI
  },
})
