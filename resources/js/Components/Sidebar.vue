<template>
  <aside
    :class="{
      'w-64': !isSidebarCollapsed,
      'w-20': isSidebarCollapsed
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
        :class="{'justify-center w-full': isSidebarCollapsed}"
        :title="isSidebarCollapsed ? 'Ir al Panel' : null"
      >
        <img
          src="/images/logo.png"
          alt="Logo"
          class="h-10 w-auto transition-transform duration-200 group-hover:scale-105"
          :class="{'mx-auto': isSidebarCollapsed}"
        />
        <span
          v-if="!isSidebarCollapsed"
          class="ml-3 text-xl font-semibold whitespace-nowrap overflow-hidden"
        >
          <!-- Climas del Desierto -->
        </span>
      </Link>

      <button
        v-if="!isMobile"
        @click="toggleSidebar"
        class="p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 ml-auto"
        :title="isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
        :aria-label="isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
      >
        <FontAwesomeIcon
          :icon="isSidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"
          class="text-gray-300 hover:text-white transition-colors duration-200"
        />
      </button>
    </div>

    <!-- Navegación -->
    <nav class="flex-1 overflow-y-auto pt-4">
      <div class="px-2 pb-4">
        <!-- Dashboard -->
        <div class="mb-6">
          <h3
            v-show="!isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Dashboard
          </h3>
          <ul class="space-y-1">
            <NavLink href="/panel" icon="tachometer-alt" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Panel' : null">
              Panel
            </NavLink>
            <NavLink href="/reportes" icon="chart-bar" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Reportes Largos' : null">
              Reportes Largos
            </NavLink>
          </ul>
        </div>

        <!-- Clientes -->
        <div class="mb-6">
          <h3
            v-show="!isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Clientes
          </h3>
          <ul class="space-y-1">
            <NavLink href="/clientes" icon="users" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Clientes' : null">
              Clientes
            </NavLink>
            <NavLink href="/citas" icon="calendar-alt" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Citas Agendadas' : null">
              Citas Agendadas
            </NavLink>
          </ul>
        </div>

        <!-- Inventario -->
        <div class="mb-6">
          <h3
            v-show="!isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Inventario
          </h3>
          <ul class="space-y-1">
            <!-- NUEVO: Equipos -->
            <NavLink href="/equipos" icon="laptop" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Equipos' : null">
              Equipos
            </NavLink>

            <NavLink href="/productos" icon="box" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Productos' : null">
              Productos
            </NavLink>
            <NavLink href="/servicios" icon="wrench" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Servicios' : null">
              Servicios
            </NavLink>
            <NavLink href="/categorias" icon="tags" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Categorías' : null">
              Categorías
            </NavLink>
            <NavLink href="/marcas" icon="trademark" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Marcas de Productos' : null">
              Marcas de Productos
            </NavLink>
            <NavLink href="/almacenes" icon="warehouse" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Almacenes' : null">
              Almacenes
            </NavLink>
          </ul>
        </div>

        <!-- Operaciones -->
        <div class="mb-6">
          <h3
            v-show="!isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Operaciones
          </h3>
          <ul class="space-y-1">
            <NavLink href="/cotizaciones" icon="file-alt" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Cotizaciones' : null">
              Cotizaciones
            </NavLink>
            <NavLink href="/pedidos" icon="truck" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Pedidos' : null">
              Pedidos
            </NavLink>
            <NavLink href="/ventas" icon="dollar-sign" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Ventas Realizadas' : null">
              Ventas Realizadas
            </NavLink>
            <NavLink href="/compras" icon="cart-shopping" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Compras a Proveedores' : null">
              Compras a Proveedores
            </NavLink>
            <NavLink href="/ordenescompra" icon="file-invoice-dollar" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Órdenes de Compra' : null">
              Órdenes de Compra
            </NavLink>
            <NavLink href="/proveedores" icon="truck" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Proveedores' : null">
              Proveedores
            </NavLink>

            <!-- NUEVO: Rentas -->
            <NavLink href="/rentas" icon="file-contract" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Rentas' : null">
              Rentas
            </NavLink>
          </ul>
        </div>

        <!-- Taller -->
        <div class="mb-6">
          <h3
            v-show="!isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Taller
          </h3>
          <ul class="space-y-1">
            <NavLink href="/carros" icon="car" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Vehículos' : null">
              Vehículos
            </NavLink>
            <NavLink href="/mantenimientos" icon="tools" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Mantenimientos' : null">
              Mantenimientos
            </NavLink>
            <NavLink href="/tecnicos" icon="user-cog" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Técnicos' : null">
              Técnicos
            </NavLink>
            <NavLink href="/herramientas" icon="toolbox" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Herramientas' : null">
              Herramientas
            </NavLink>
          </ul>
        </div>

        <!-- Administración -->
        <div class="mb-6">
          <h3
            v-show="!isSidebarCollapsed"
            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Administración
          </h3>
          <ul class="space-y-1">
            <NavLink href="/usuarios" icon="user" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Usuarios' : null">
              Usuarios
            </NavLink>

            <!-- Si usas Ziggy, puedes dejar :href="route('backup.index')" -->
            <NavLink :href="routeOr('/backup')" icon="database" :collapsed="isSidebarCollapsed" :title="isSidebarCollapsed ? 'Copia de Seguridad' : null">
              Copia de Seguridad
            </NavLink>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Usuario -->
    <div
      class="border-t border-gray-700 p-4 bg-gray-800/50 backdrop-blur-sm flex-shrink-0"
      :class="{'flex justify-center': isSidebarCollapsed}"
    >
      <div class="flex items-center" :class="{'w-full justify-center': isSidebarCollapsed, 'space-x-3': !isSidebarCollapsed}">
        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
          <FontAwesomeIcon icon="fa-solid fa-user" class="text-white text-sm" />
        </div>
        <div v-show="!isSidebarCollapsed" class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-100 truncate">
            Usuario Admin
          </p>
          <p class="text-xs text-gray-400 truncate">
            admin@sistema.com
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import NavLink from '@/Components/NavLink.vue';

const isSidebarCollapsed = ref(false);
const isMobile = ref(false);

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

// Móvil/desktop + persistencia del estado
const STORAGE_KEY = 'sidebarCollapsed';

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
  if (isMobile.value) {
    // En móvil: colapsado por defecto
    isSidebarCollapsed.value = true;
  } else {
    const savedState = localStorage.getItem(STORAGE_KEY);
    isSidebarCollapsed.value = savedState !== null ? JSON.parse(savedState) : false;
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
  // Solo persistimos en desktop; en móvil siempre parte colapsado
  if (!isMobile.value) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(isSidebarCollapsed.value));
  }
};

// Exponer si necesitas manipular desde fuera
defineExpose({
  toggleSidebar,
  isSidebarCollapsed
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
