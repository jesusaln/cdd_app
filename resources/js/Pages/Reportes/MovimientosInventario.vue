<template>
    <AppLayout title="Reporte de Movimientos de Inventario">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Movimientos de Inventario
                </h2>
                <div class="flex space-x-2">
                    <Link
                        :href="route('reportes.movimientos-inventario.export', filters)"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Exportar
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Estadísticas -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas Generales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ stats.total_movimientos }}</div>
                                <div class="text-blue-800">Total Movimientos</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ formatNumber(stats.total_entradas) }}</div>
                                <div class="text-green-800">Total Entradas</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ formatNumber(stats.total_salidas) }}</div>
                                <div class="text-red-800">Total Salidas</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ stats.productos_con_movimientos }}</div>
                                <div class="text-purple-800">Productos con Movimientos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <!-- Producto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                                <select
                                    v-model="filters.producto_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Todos los productos</option>
                                    <option
                                        v-for="(nombre, id) in filterOptions.productos"
                                        :key="id"
                                        :value="id"
                                    >
                                        {{ nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select
                                    v-model="filters.tipo"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Todos los tipos</option>
                                    <option
                                        v-for="(label, value) in filterOptions.tipos"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Usuario -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                                <select
                                    v-model="filters.user_id"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Todos los usuarios</option>
                                    <option
                                        v-for="(nombre, id) in filterOptions.usuarios"
                                        :key="id"
                                        :value="id"
                                    >
                                        {{ nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Fecha Desde -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                                <input
                                    type="date"
                                    v-model="filters.fecha_desde"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <!-- Fecha Hasta -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                                <input
                                    type="date"
                                    v-model="filters.fecha_hasta"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <!-- Motivo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                                <input
                                    type="text"
                                    v-model="filters.motivo"
                                    placeholder="Buscar por motivo..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="mt-4 flex space-x-2">
                            <button
                                @click="applyFilters"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                Aplicar Filtros
                            </button>
                            <button
                                @click="clearFilters"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Movimientos -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            v-for="column in columns"
                                            :key="column.key"
                                            @click="sortBy(column.key)"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                            :class="{ 'bg-gray-100': sorting.sort_by === column.key }"
                                        >
                                            <div class="flex items-center space-x-1">
                                                <span>{{ column.label }}</span>
                                                <span v-if="sorting.sort_by === column.key">
                                                    <svg v-if="sorting.sort_direction === 'asc'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="movimiento in movimientos.data" :key="movimiento.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ movimiento.created_at }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div
                                                        :class="movimiento.tipo === 'entrada' ? 'bg-green-100' : 'bg-red-100'"
                                                        class="h-10 w-10 rounded-full flex items-center justify-center"
                                                    >
                                                        <svg
                                                            :class="movimiento.tipo === 'entrada' ? 'text-green-600' : 'text-red-600'"
                                                            class="h-5 w-5"
                                                            fill="currentColor"
                                                            :viewBox="movimiento.tipo === 'entrada' ? '0 0 20 20' : '0 0 20 20'"
                                                        >
                                                            <path v-if="movimiento.tipo === 'entrada'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/>
                                                            <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" transform="rotate(180 10 10)"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ movimiento.tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ movimiento.motivo }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                :class="movimiento.tipo === 'entrada' ? 'text-green-600' : 'text-red-600'"
                                                class="font-medium"
                                            >
                                                {{ movimiento.tipo === 'entrada' ? '+' : '-' }}{{ formatNumber(movimiento.cantidad) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div v-if="movimiento.producto">
                                                <div class="font-medium">{{ movimiento.producto.nombre }}</div>
                                                <div class="text-gray-500">{{ movimiento.producto.codigo }}</div>
                                                <div v-if="movimiento.producto.categoria" class="text-xs text-gray-400">
                                                    {{ movimiento.producto.categoria }}
                                                </div>
                                            </div>
                                            <span v-else class="text-gray-400">Producto no encontrado</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div v-if="movimiento.user">
                                                <div class="font-medium">{{ movimiento.user.name }}</div>
                                                <div class="text-gray-500">{{ movimiento.user.email }}</div>
                                            </div>
                                            <span v-else class="text-gray-400">Usuario no encontrado</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <span v-if="movimiento.referencia" class="font-medium">{{ movimiento.referencia }}</span>
                                            <span v-else class="text-gray-400">Sin referencia</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                :href="route('reportes.movimientos-inventario.show', movimiento.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Ver Detalle
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-700">Mostrar:</label>
                                    <select
                                        v-model="pagination.per_page"
                                        @change="changePerPage"
                                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    >
                                        <option
                                            v-for="option in filterOptions.per_page_options"
                                            :key="option"
                                            :value="option"
                                        >
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-700">
                                        Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
                                    </span>
                                </div>
                            </div>

                            <!-- Links de paginación -->
                            <div class="mt-4 flex justify-center">
                                <div class="flex space-x-1">
                                    <Link
                                        v-for="link in movimientos.links"
                                        :key="link.label"
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md',
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                            !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-50'
                                        ]"
                                        :disabled="!link.url"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    movimientos: Object,
    stats: Object,
    filterOptions: Object,
    filters: Object,
    sorting: Object,
    pagination: Object,
});

const filters = reactive({ ...props.filters });
const sorting = reactive({ ...props.sorting });

// Columnas de la tabla
const columns = [
    { key: 'created_at', label: 'Fecha' },
    { key: 'tipo', label: 'Tipo' },
    { key: 'cantidad', label: 'Cantidad' },
    { key: 'producto', label: 'Producto' },
    { key: 'user', label: 'Usuario' },
    { key: 'referencia', label: 'Referencia' },
];

// Aplicar filtros
const applyFilters = () => {
    router.get(route('reportes.movimientos-inventario'), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Limpiar filtros
const clearFilters = () => {
    Object.keys(filters).forEach(key => {
        filters[key] = '';
    });
    applyFilters();
};

// Ordenar por columna
const sortBy = (column) => {
    if (sorting.sort_by === column) {
        sorting.sort_direction = sorting.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        sorting.sort_by = column;
        sorting.sort_direction = 'asc';
    }

    router.get(route('reportes.movimientos-inventario'), {
        ...filters,
        sort_by: sorting.sort_by,
        sort_direction: sorting.sort_direction,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Cambiar elementos por página
const changePerPage = () => {
    router.get(route('reportes.movimientos-inventario'), {
        ...filters,
        per_page: props.pagination.per_page,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Formatear números
const formatNumber = (number) => {
    return new Intl.NumberFormat('es-MX').format(number);
};

// Observar cambios en filtros para aplicar automáticamente
watch(filters, (newFilters) => {
    // Aplicar filtros después de 500ms de inactividad
    clearTimeout(window.filterTimeout);
    window.filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}, { deep: true });
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
