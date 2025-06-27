<template>
    <aside
        :class="{
            'w-64': !isSidebarCollapsed,
            'w-20': isSidebarCollapsed // Ancho reducido para solo mostrar iconos
        }"
        class="bg-gradient-to-b from-gray-800 to-gray-900 text-white fixed left-0 top-0 bottom-0 z-20 transition-all duration-300 ease-in-out overflow-y-auto shadow-2xl border-r border-gray-700 flex flex-col"
    >
        <div class="flex items-center justify-between p-4 border-b border-gray-700 bg-gray-800/50 backdrop-blur-sm flex-shrink-0">
            <Link
                href="/panel"
                class="flex items-center group overflow-hidden"
                :class="{'justify-center w-full': isSidebarCollapsed}"
            >
            <img
                    src="/images/logo.png"
                    alt="Logo"
                    class="h-10 w-auto transition-transform duration-200 group-hover:scale-105"
                    :class="{'mx-auto': isSidebarCollapsed}"
                />

                <span v-if="!isSidebarCollapsed" class="ml-3 text-xl font-semibold whitespace-nowrap overflow-hidden">
                    <!-- Climas del Desierto -->
                </span>
            </Link>

            <button
                v-if="!isMobile"
                @click="toggleSidebar"
                class="p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 ml-auto"
                :title="isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
            >
                <FontAwesomeIcon
                    :icon="isSidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"
                    class="text-gray-300 hover:text-white transition-colors duration-200"
                />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto pt-4">
            <div class="px-2 pb-4">
                <div class="mb-6">
                    <h3
                        v-show="!isSidebarCollapsed"
                        class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                        Dashboard
                    </h3>
                    <ul class="space-y-1">
                        <NavLink href="/panel" icon="tachometer-alt" :collapsed="isSidebarCollapsed">
                            Panel
                        </NavLink>
                        <NavLink href="/reportes" icon="chart-bar" :collapsed="isSidebarCollapsed">
                            Reportes Largos
                        </NavLink>
                    </ul>
                </div>

                <div class="mb-6">
                    <h3
                        v-show="!isSidebarCollapsed"
                        class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                        Clientes
                    </h3>
                    <ul class="space-y-1">
                        <NavLink href="/clientes" icon="users" :collapsed="isSidebarCollapsed">
                            Clientes
                        </NavLink>
                        <NavLink href="/citas" icon="calendar-alt" :collapsed="isSidebarCollapsed">
                            Citas Agendadas
                        </NavLink>
                    </ul>
                </div>

                <div class="mb-6">
                    <h3
                        v-show="!isSidebarCollapsed"
                        class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                        Inventario
                    </h3>
                    <ul class="space-y-1">
                        <NavLink href="/productos" icon="box" :collapsed="isSidebarCollapsed">
                            Productos
                        </NavLink>
                        <NavLink href="/servicios" icon="wrench" :collapsed="isSidebarCollapsed">
                            Servicios
                        </NavLink>
                        <NavLink href="/categorias" icon="tags" :collapsed="isSidebarCollapsed">
                            Categorías
                        </NavLink>
                        <NavLink href="/marcas" icon="trademark" :collapsed="isSidebarCollapsed">
                            Marcas de Productos
                        </NavLink>
                        <NavLink href="/almacenes" icon="warehouse" :collapsed="isSidebarCollapsed">
                            Almacenes
                        </NavLink>
                    </ul>
                </div>

                <div class="mb-6">
                    <h3
                        v-show="!isSidebarCollapsed"
                        class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                        Operaciones
                    </h3>
                    <ul class="space-y-1">
                        <NavLink href="/cotizaciones" icon="file-alt" :collapsed="isSidebarCollapsed">
                            Cotizaciones
                        </NavLink>
                        <NavLink href="/pedidos" icon="truck" :collapsed="isSidebarCollapsed">
                            Pedidos
                        </NavLink>
                        <NavLink href="/ventas" icon="dollar-sign" :collapsed="isSidebarCollapsed">
                            Ventas Realizadas
                        </NavLink>
                        <NavLink href="/compras" icon="cart-shopping" :collapsed="isSidebarCollapsed">
                            Compras a Proveedores
                        </NavLink>
                        <NavLink href="/proveedores" icon="truck" :collapsed="isSidebarCollapsed">
                            Proveedores
                        </NavLink>
                    </ul>
                </div>

                <div class="mb-6">
                    <h3
                        v-show="!isSidebarCollapsed"
                        class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                        Taller
                    </h3>
                    <ul class="space-y-1">
                        <NavLink href="/carros" icon="car" :collapsed="isSidebarCollapsed">
                            Vehículos
                        </NavLink>
                        <NavLink href="/mantenimientos" icon="tools" :collapsed="isSidebarCollapsed">
                            Mantenimientos
                        </NavLink>
                        <NavLink href="/tecnicos" icon="user-cog" :collapsed="isSidebarCollapsed">
                            Técnicos
                        </NavLink>
                        <NavLink href="/herramientas" icon="toolbox" :collapsed="isSidebarCollapsed">
                            Herramientas
                        </NavLink>
                    </ul>
                </div>

                <div class="mb-6">
                    <h3
                        v-show="!isSidebarCollapsed"
                        class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                        Administración
                    </h3>
                    <ul class="space-y-1">
                        <NavLink href="/usuarios" icon="user" :collapsed="isSidebarCollapsed">
                            Usuarios
                        </NavLink>
                    </ul>
                </div>
            </div>
        </nav>

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

        <div
            v-if="isSidebarCollapsed && hoveredItem"
            class="fixed bg-gray-900 text-white px-2 py-1 rounded shadow-lg text-sm z-30 pointer-events-none"
            :style="tooltipStyle"
        >
            {{ hoveredItem }}
        </div>
    </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3'; // Asumo que usas Inertia.js Link
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import NavLink from '@/Components/NavLink.vue'; // Asegúrate que esta ruta sea correcta

const isSidebarCollapsed = ref(false);
const hoveredItem = ref('');
const tooltipStyle = ref({});
const isMobile = ref(false);

// Funciones para manejar tooltips (se pasan a NavLink)
const showTooltip = (event, text) => {
    if (isSidebarCollapsed.value) {
        hoveredItem.value = text;
        const rect = event.currentTarget.getBoundingClientRect(); // currentTarget para asegurar el elemento correcto
        tooltipStyle.value = {
            left: `${rect.right + 10}px`,
            top: `${rect.top + rect.height / 2 - 12}px`
        };
    }
};

const hideTooltip = () => {
    hoveredItem.value = '';
};

// Persistir estado del sidebar y manejar estado móvil
const checkMobile = () => {
    isMobile.value = window.innerWidth <= 768;
    if (isMobile.value) {
        isSidebarCollapsed.value = true; // Colapsar por defecto en móvil
    } else {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState !== null) {
            isSidebarCollapsed.value = JSON.parse(savedState);
        }
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
    localStorage.setItem('sidebarCollapsed', JSON.stringify(isSidebarCollapsed.value));
};

// Exponer métodos para uso externo (si es necesario)
defineExpose({
    toggleSidebar,
    isSidebarCollapsed
});
</script>

<style scoped>
/* Scrollbar personalizado */
aside::-webkit-scrollbar {
    width: 4px;
}

aside::-webkit-scrollbar-track {
    background: rgba(55, 65, 81, 0.5);
}

aside::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

aside::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.7);
}

/* Animaciones suaves */
.transition-opacity {
    transition: opacity 0.3s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    aside {
        position: fixed;
        z-index: 50;
        /* Por defecto colapsado en móvil, ancho inicial de w-16 */
        width: 4rem; /* w-16 */
        transform: translateX(-100%); /* Ocultar sidebar por defecto */
    }
    aside.w-64 { /* Cuando el sidebar se expande en móvil */
        transform: translateX(0%);
    }
}
</style>
