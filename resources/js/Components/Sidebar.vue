<template>
    <aside
        :class="{
            'w-64': !isSidebarCollapsed,
            'w-16': isSidebarCollapsed
        }"
        class="bg-gradient-to-b from-gray-800 to-gray-900 text-white fixed left-0 top-0 bottom-0 z-20 transition-all duration-300 ease-in-out overflow-y-auto shadow-2xl border-r border-gray-700"
    >
        <!-- Header del Sidebar -->
        <div class="flex items-center justify-between p-4 border-b border-gray-700 bg-gray-800/50 backdrop-blur-sm">
            <div
                v-show="!isSidebarCollapsed"
                class="flex items-center space-x-2 transition-opacity duration-300"
            >
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <FontAwesomeIcon icon="fa-solid fa-cog" class="text-white text-sm" />
                </div>
                <span class="font-bold text-lg text-gray-100">Sistema</span>
            </div>

            <button
                @click="toggleSidebar"
                class="p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :title="isSidebarCollapsed ? 'Expandir sidebar' : 'Contraer sidebar'"
            >
                <FontAwesomeIcon
                    :icon="isSidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"
                    class="text-gray-300 hover:text-white transition-colors duration-200"
                />
            </button>
        </div>

        <!-- Navegación Principal -->
        <nav class="flex-1 overflow-y-auto">
            <div class="px-2 pb-4">
                <!-- Sección: Dashboard -->
                <div class="mb-6 pt-4">
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
                            Reportes
                        </NavLink>
                    </ul>
                </div>

                <!-- Sección: Gestión de Clientes -->
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
                            Citas
                        </NavLink>
                    </ul>
                </div>

                <!-- Sección: Inventario -->
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
                            Marcas
                        </NavLink>
                        <NavLink href="/almacenes" icon="warehouse" :collapsed="isSidebarCollapsed">
                            Almacenes
                        </NavLink>
                    </ul>
                </div>

                <!-- Sección: Operaciones -->
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
                            Ventas
                        </NavLink>
                        <NavLink href="/compras" icon="cart-shopping" :collapsed="isSidebarCollapsed">
                            Compras
                        </NavLink>
                        <NavLink href="/proveedores" icon="truck" :collapsed="isSidebarCollapsed">
                            Proveedores
                        </NavLink>
                    </ul>
                </div>

                <!-- Sección: Taller -->
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

                <!-- Sección: Administración -->
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

        <!-- Footer con información del usuario -->
        <div
            v-show="!isSidebarCollapsed"
            class="border-t border-gray-700 p-4 bg-gray-800/50 backdrop-blur-sm flex-shrink-0"
        >
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                    <FontAwesomeIcon icon="fa-solid fa-user" class="text-white text-sm" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-100 truncate">
                        Usuario Admin
                    </p>
                    <p class="text-xs text-gray-400 truncate">
                        admin@sistema.com
                    </p>
                </div>
            </div>
        </div>

        <!-- Tooltip para elementos colapsados -->
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
import NavLink from '@/Components/NavLink.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { ref, onMounted, onUnmounted } from 'vue';

const isSidebarCollapsed = ref(false);
const hoveredItem = ref('');
const tooltipStyle = ref({});

// Persistir estado del sidebar
onMounted(() => {
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState !== null) {
        isSidebarCollapsed.value = JSON.parse(savedState);
    }
});

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', JSON.stringify(isSidebarCollapsed.value));
};

// Manejar tooltips en modo colapsado
const showTooltip = (event, text) => {
    if (isSidebarCollapsed.value) {
        hoveredItem.value = text;
        const rect = event.target.getBoundingClientRect();
        tooltipStyle.value = {
            left: `${rect.right + 10}px`,
            top: `${rect.top + rect.height / 2 - 12}px`
        };
    }
};

const hideTooltip = () => {
    hoveredItem.value = '';
};

// Cerrar sidebar en móvil al hacer clic fuera
const handleClickOutside = (event) => {
    if (window.innerWidth <= 768 && !event.target.closest('aside')) {
        isSidebarCollapsed.value = true;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Exponer métodos para uso externo
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

/* Responsive */
@media (max-width: 768px) {
    aside {
        position: fixed;
        z-index: 50;
    }
}
</style>
