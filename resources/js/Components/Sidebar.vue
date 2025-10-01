<template>
  <aside
    :class="{
      'w-64': !props.isSidebarCollapsed,
      'w-20': props.isSidebarCollapsed
    }"
    class="bg-gradient-to-b from-gray-800 to-gray-900 text-white fixed left-0 top-0 bottom-0 z-20 transition-all duration-300 ease-in-out overflow-y-auto shadow-2xl border-r border-gray-700 flex flex-col"
    role="navigation"
    aria-label="Barra lateral"
  >
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-700 bg-gray-800/50 backdrop-blur-sm flex-shrink-0">
      <Link
        href="/panel"
        class="flex items-center group overflow-hidden"
        :class="{'justify-center w-full': props.isSidebarCollapsed}"
        :title="props.isSidebarCollapsed ? 'Ir al Panel' : null"
      >
        <img
          src="/images/logo.png"
          alt="Logo"
          class="h-10 w-auto transition-transform duration-200 group-hover:scale-105"
          :class="{'mx-auto': props.isSidebarCollapsed}"
        />
        <span
          v-if="!props.isSidebarCollapsed"
          class="ml-3 text-xl font-semibold whitespace-nowrap overflow-hidden"
        >
          <!-- Climas del Desierto -->
        </span>
      </Link>

      <button
        v-if="!isMobile"
        @click="toggleSidebar"
        class="p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 ml-auto"
        :title="props.isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
        :aria-label="props.isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
      >
        <FontAwesomeIcon
          :icon="props.isSidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"
          class="text-gray-300 hover:text-white transition-colors duration-200"
        />
      </button>
    </div>

    <!-- Navegación -->
    <nav class="flex-1 overflow-y-auto pt-4">
      <div class="px-2 pb-4">
        <!-- Dashboard -->
        <div class="mb-4">
          <h3
            v-show="!props.isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Dashboard
          </h3>
          <ul class="space-y-1">
            <NavLink href="/panel" icon="tachometer-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Panel' : null">
              Panel
            </NavLink>
          </ul>
        </div>

        <!-- Operación de Venta -->
        <div class="mb-4">
          <div
            @click="toggleAccordion('ventas')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <span>Operación de Venta</span>
            <svg
              :class="accordionStates.ventas ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.ventas" class="mt-2 space-y-1">
            <NavLink href="/cotizaciones" icon="file-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Cotizaciones' : null">
              Cotizaciones
            </NavLink>
            <NavLink href="/pedidos" icon="truck" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Pedidos' : null">
              Pedidos
            </NavLink>
            <NavLink href="/ventas" icon="dollar-sign" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Ventas Realizadas' : null">
              Ventas Realizadas
            </NavLink>
            <NavLink href="/cobranza" icon="dollar-sign" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Cobranza' : null">
              Cobranza
            </NavLink>
          </div>
        </div>

        <!-- Operación de Compra -->
        <div class="mb-4">
          <div
            @click="toggleAccordion('compras')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <span>Operación de Compra</span>
            <svg
              :class="accordionStates.compras ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.compras" class="mt-2 ml-2 space-y-1">
            <NavLink href="/compras" icon="cart-shopping" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Compras a Proveedores' : null">
              Compras a Proveedores
            </NavLink>
            <NavLink href="/ordenescompra" icon="file-invoice-dollar" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Órdenes de Compra' : null">
              Órdenes de Compra
            </NavLink>
            <NavLink href="/proveedores" icon="truck" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Proveedores' : null">
              Proveedores
            </NavLink>
          </div>
        </div>

        <!-- Catálogos -->
        <div class="mb-4">
          <div
            @click="toggleAccordion('catalogos')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <span>Catálogos</span>
            <svg
              :class="accordionStates.catalogos ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.catalogos" class="mt-2 space-y-1">
            <NavLink href="/clientes" icon="users" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Clientes' : null">
              Clientes
            </NavLink>
            <NavLink href="/productos" icon="box" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Productos' : null">
              Productos
            </NavLink>
            <NavLink href="/servicios" icon="wrench" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Servicios' : null">
              Servicios
            </NavLink>
            <NavLink href="/categorias" icon="tags" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Categorías' : null">
              Categorías
            </NavLink>
            <NavLink href="/marcas" icon="trademark" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Marcas de Productos' : null">
              Marcas de Productos
            </NavLink>
            <NavLink href="/almacenes" icon="warehouse" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Almacenes' : null">
              Almacenes
            </NavLink>
          </div>
        </div>

        <!-- Administración -->
        <div class="mb-4">
          <div
            @click="toggleAccordion('administracion')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <span>Administración</span>
            <svg
              :class="accordionStates.administracion ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.administracion" class="mt-2 space-y-1">
            <NavLink href="/reportes/dashboard" icon="chart-bar" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Centro de Reportes' : null">
              Centro de Reportes
            </NavLink>
            <NavLink href="/usuarios" icon="user" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Usuarios' : null">
              Usuarios
            </NavLink>
            <NavLink :href="routeOr('/backup')" icon="database" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Copia de Seguridad' : null">
              Copia de Seguridad
            </NavLink>
            <!-- NUEVO: Entregas de Dinero (Solo para administradores) -->
            <NavLink v-if="props.usuario.is_admin || (props.usuario.roles && props.usuario.roles.some(role => role.name === 'admin'))" href="/entregas-dinero" icon="dollar-sign" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Entregas de Dinero' : null">
              Entregas de Dinero
            </NavLink>
          </div>
        </div>

        <!-- Rentas y Equipos -->
        <div class="mb-4">
          <div
            @click="toggleAccordion('rentas')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <span>Rentas y Equipos</span>
            <svg
              :class="accordionStates.rentas ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.rentas" class="mt-2 ml-2 space-y-1">
            <NavLink href="/rentas" icon="file-contract" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Rentas' : null">
              Rentas
            </NavLink>
            <NavLink href="/equipos" icon="laptop" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Equipos' : null">
              Equipos
            </NavLink>
          </div>
        </div>

        <!-- Taller Mantenimiento y Vehículos -->
        <div class="mb-4">
          <div
            @click="toggleAccordion('taller')"
            class="flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white hover:bg-gray-700/50 rounded-md transition-colors duration-200"
          >
            <span>Taller y Mantenimiento</span>
            <svg
              :class="accordionStates.taller ? 'rotate-90' : ''"
              class="w-3 h-3 transition-transform duration-200"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
          <div v-show="accordionStates.taller" class="mt-2 ml-2 space-y-1">
            <NavLink href="/carros" icon="car" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Vehículos' : null">
              Vehículos
            </NavLink>
            <NavLink href="/mantenimientos" icon="tools" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Mantenimientos' : null">
              Mantenimientos
            </NavLink>
            <NavLink href="/tecnicos" icon="user-cog" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Técnicos' : null">
              Técnicos
            </NavLink>
            <NavLink href="/herramientas" icon="toolbox" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Herramientas' : null">
              Herramientas
            </NavLink>
          </div>
        </div>

        <!-- Secciones Adicionales -->
        <div class="mb-4">
          <h3
            v-show="!props.isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Módulos Adicionales
          </h3>
          <ul class="space-y-1">
            <NavLink href="/citas" icon="calendar-alt" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Citas Agendadas' : null">
              Citas Agendadas
            </NavLink>
            <NavLink href="/bitacora" icon="clipboard-list" :collapsed="props.isSidebarCollapsed" :title="props.isSidebarCollapsed ? 'Bitácora' : null">
              Bitácora
            </NavLink>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Usuario -->
    <div
      class="border-t border-gray-700 p-4 bg-gray-800/50 backdrop-blur-sm flex-shrink-0"
      :class="{'flex justify-center': props.isSidebarCollapsed}"
    >
      <div class="flex items-center" :class="{'w-full justify-center': props.isSidebarCollapsed, 'space-x-3': !props.isSidebarCollapsed}">
        <img
          :src="props.usuario.profile_photo_url"
          :alt="props.usuario.name"
          class="w-10 h-10 rounded-full border-2 border-gray-600 object-cover flex-shrink-0"
        />
        <div v-show="!props.isSidebarCollapsed" class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-100 truncate">
            {{ props.usuario.name }}
          </p>
          <p class="text-xs text-gray-400 truncate">
            {{ props.usuario.email }}
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

// Props
const props = defineProps({
  usuario: {
    type: Object,
    required: true
  },
  isSidebarCollapsed: {
    type: Boolean,
    default: false
  },
  isMobile: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['toggleSidebar']);

// Estado del acordeón
const accordionStates = ref({
  ventas: false,
  compras: false,
  catalogos: false,
  administracion: false,
  rentas: false,
  taller: false
});

// Función para alternar acordeón
const toggleAccordion = (section) => {
  // Si el sidebar está colapsado, expandir la sección
  if (props.isSidebarCollapsed) {
    Object.keys(accordionStates.value).forEach(key => {
      accordionStates.value[key] = key === section;
    });
  } else {
    // Si no está colapsado, alternar normalmente
    accordionStates.value[section] = !accordionStates.value[section];
  }
};

// Función para determinar la sección actual basada en la URL
const getCurrentSection = () => {
  const path = window.location.pathname;

  if (path.includes('/cotizaciones') || path.includes('/pedidos') || path.includes('/ventas') || path.includes('/cobranza')) {
    return 'ventas';
  } else if (path.includes('/compras') || path.includes('/ordenescompra') || path.includes('/proveedores')) {
    return 'compras';
  } else if (path.includes('/clientes') || path.includes('/productos') || path.includes('/servicios') || path.includes('/categorias') || path.includes('/marcas') || path.includes('/almacenes')) {
    return 'catalogos';
  } else if (path.includes('/usuarios') || path.includes('/backup') || path.includes('/entregas-dinero')) {
    return 'administracion';
  } else if (path.includes('/rentas') || path.includes('/equipos')) {
    return 'rentas';
  } else if (path.includes('/carros') || path.includes('/mantenimientos') || path.includes('/tecnicos') || path.includes('/herramientas')) {
    return 'taller';
  }

  return null;
};

// Auto-expandir la sección actual cuando se carga la página
const autoExpandCurrentSection = () => {
  const currentSection = getCurrentSection();
  if (currentSection) {
    accordionStates.value[currentSection] = true;
  }
};

// Helper para tolerar ausencia de Ziggy route()
const routeOr = (fallback) => {
  try {
    // si Ziggy está disponible
    if (typeof route === 'function') return route('backup.index');
    return fallback;
  } catch {
    return fallback;
  }
};

const toggleSidebar = () => {
  emit('toggleSidebar');
};

// Lifecycle hooks
onMounted(() => {
  // Auto-expandir la sección actual cuando se carga la página
  autoExpandCurrentSection();
});

// Exponer si necesitas manipular desde fuera
defineExpose({
  toggleSidebar,
  toggleAccordion,
  autoExpandCurrentSection,
  isSidebarCollapsed: props.isSidebarCollapsed,
  accordionStates
});
</script>

<style scoped>
/* Scrollbar personalizado */
aside::-webkit-scrollbar { width: 4px; }
aside::-webkit-scrollbar-track { background: rgba(55, 65, 81, 0.5); }
aside::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.5); border-radius: 2px; }
aside::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.7); }

/* Suaves */
.transition-opacity { transition: opacity 0.3s ease-in-out; }

/* Animaciones del acordeón */
.accordion-section {
  transition: all 0.3s ease-in-out;
}

.accordion-section:hover {
  background-color: rgba(55, 65, 81, 0.3);
}

.accordion-chevron {
  transition: transform 0.2s ease-in-out;
}

.accordion-chevron.rotated {
  transform: rotate(90deg);
}

/* Responsive móvil */
@media (max-width: 768px) {
  aside {
    position: fixed;
    z-index: 50;
    width: 4rem; /* w-16 */
    transform: translateX(-100%); /* oculto por defecto */
  }
  aside.w-64 {
    transform: translateX(0%); /* visible cuando expandes */
  }
}
</style>
