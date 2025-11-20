import './bootstrap'
import '../css/app.css'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
// Ziggy Vue plugin (usa @routes inyectado desde Blade)
import { ZiggyVue } from 'ziggy-js'
// Ensure Ziggy uses the current origin (avoid hardcoded dev URL)
try {
  if (typeof window !== 'undefined' && window.location && window.Ziggy) {
    window.Ziggy.url = window.location.origin
    window.Ziggy.port = null
    // Ensure recently added routes exist even if ziggy config is stale
    window.Ziggy.routes = window.Ziggy.routes || {}
    if (!window.Ziggy.routes['registro-vacaciones.export']) {
      window.Ziggy.routes['registro-vacaciones.export'] = {
        uri: 'registro-vacaciones/export',
        methods: ['GET', 'HEAD']
      }
    }
    if (!window.Ziggy.routes['registro-vacaciones.ajustar']) {
      window.Ziggy.routes['registro-vacaciones.ajustar'] = {
        uri: 'registro-vacaciones/{empleado}/ajustar',
        methods: ['POST'],
        parameters: ['empleado']
      }
    }
  }
} catch (e) {
  // no-op: fallback to bundled Ziggy config
}
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
  faMoneyBillWave,      // 👈 NUEVO (money-bill-wave)
  faCircle,             // 👈 NUEVO (circle)
  faShieldAlt          // 👈 NUEVO (shield-alt)
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
  faMoneyBillWave,      // money-bill-wave
  faCircle,             // circle
  faShieldAlt          // shield-alt
)
const appName = import.meta.env.VITE_APP_NAME || 'Laravel'
// Mejor práctica: desactivar devtools en producción
if (import.meta.env.PROD) {
  // @ts-ignore
  window.__VUE_DEVTOOLS_GLOBAL_HOOK__ = { emit: () => { }, on: () => { }, once: () => { }, off: () => { }, Vue: null }
}
createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, window.Ziggy)
      // Registrar ambos nombres para evitar discrepancias en plantillas
      .component('FontAwesomeIcon', FontAwesomeIcon)
      .component('font-awesome-icon', FontAwesomeIcon)

    // Hacer route disponible globalmente (configuración correcta para ziggy-js)


    app.mount(el)
  },
  progress: {
    color: '#4B5563', // gris oscuro acorde a tu UI
  },
})
