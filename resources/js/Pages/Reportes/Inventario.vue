<template>
    <Head title="Reporte de Inventario" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Reporte de Inventario</h1>
                        <p class="text-sm text-gray-600 mt-1">Control de stock y productos</p>
                    </div>
                    <Link
                        href="/reportes"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver al Dashboard
                    </Link>
                </div>
            </div>

            <!-- Filtros -->
            <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select
                            v-model="filtros.categoria_id"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="filtrar"
                        >
                            <option value="">Todas las categorías</option>
                            <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                {{ categoria.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                        <select
                            v-model="filtros.marca_id"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="filtrar"
                        >
                            <option value="">Todas las marcas</option>
                            <option v-for="marca in marcas" :key="marca.id" :value="marca.id">
                                {{ marca.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select
                            v-model="filtros.tipo"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="filtrar"
                        >
                            <option value="todos">Todos</option>
                            <option value="bajos">Bajo Stock</option>
                            <option value="sin_stock">Sin Stock</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="exportar"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                        >
                            Exportar Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ estadisticas.total_productos }}</div>
                        <div class="text-sm text-blue-600">Total Productos</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ estadisticas.productos_bajos }}</div>
                        <div class="text-sm text-green-600">Bajo Stock</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ estadisticas.productos_sin_stock }}</div>
                        <div class="text-sm text-red-600">Sin Stock</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(estadisticas.valor_inventario) }}</div>
                        <div class="text-sm text-purple-600">Valor Inventario</div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Venta</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="producto in productos" :key="producto.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ producto.nombre }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ producto.codigo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ producto.categoria }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ producto.marca }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ producto.stock }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatCurrency(producto.precio_venta) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getEstadoClass(producto.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ producto.estado === 'sin_stock' ? 'Sin Stock' : producto.estado === 'bajo' ? 'Bajo Stock' : 'Normal' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    productos: Array,
    estadisticas: Object,
    categorias: Array,
    marcas: Array,
    filtros: Object,
});

const filtros = ref({ ...props.filtros });

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value || 0);
};

const getEstadoClass = (estado) => {
    switch (estado) {
        case 'sin_stock':
            return 'bg-red-100 text-red-800';
        case 'bajo':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-green-100 text-green-800';
    }
};

const filtrar = () => {
    router.get(route('reportes.inventario'), filtros.value, {
        preserveState: true,
        replace: true,
    });
};

const exportar = () => {
    window.open(route('reportes.inventario.export', filtros.value), '_blank');
};
</script>
