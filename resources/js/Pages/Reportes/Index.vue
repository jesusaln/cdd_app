<template>
    <Head title="Reportes" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">Todos los Reportes</h1>
                <p class="text-sm text-gray-600 mt-1">Vista completa de ventas, compras, inventario y cortes diarios</p>
            </div>

            <!-- Filtros de Fecha -->
            <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="fechaInicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha Inicio
                        </label>
                        <input
                            v-model="fechaInicio"
                            type="date"
                            id="fechaInicio"
                            class="input-field"
                            @change="filtrarDatos"
                        />
                    </div>
                    <div>
                        <label for="fechaFin" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha Fin
                        </label>
                        <input
                            v-model="fechaFin"
                            type="date"
                            id="fechaFin"
                            class="input-field"
                            @change="filtrarDatos"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="limpiarFiltros"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex">
                    <button
                        @click="activeTab = 'ventas'"
                        :class="tabClass('ventas')"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                    >
                        Ventas
                    </button>
                    <button
                        @click="activeTab = 'compras'"
                        :class="tabClass('compras')"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                    >
                        Compras
                    </button>
                    <button
                        @click="activeTab = 'inventario'"
                        :class="tabClass('inventario')"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                    >
                        Inventario
                    </button>
                    <button
                        @click="activeTab = 'corte'"
                        :class="tabClass('corte')"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                    >
                        Corte Diario
                    </button>
                </nav>
            </div>

            <!-- Contenido de Tabs -->
            <div class="p-6">
                <!-- Tab Ventas -->
                <div v-show="activeTab === 'ventas'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen de Ventas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(corteFiltrado) }}</div>
                                <div class="text-sm text-blue-600">Total Ventas</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(utilidadFiltrada) }}</div>
                                <div class="text-sm text-green-600">Utilidad Total</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ ventasFiltradas.length }}</div>
                                <div class="text-sm text-purple-600">Número de Ventas</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilidad</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="venta in ventasFiltradas" :key="venta.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(venta.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ venta.cliente || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(venta.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(venta.costo_total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">{{ formatCurrency(calculateProfit(venta)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Compras -->
                <div v-show="activeTab === 'compras'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen de Compras</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalComprasFiltrado) }}</div>
                                <div class="text-sm text-red-600">Total Compras</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ comprasFiltradas.length }}</div>
                                <div class="text-sm text-orange-600">Número de Compras</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="compra in comprasFiltradas" :key="compra.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(compra.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ compra.proveedor || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(compra.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="compra.estado === 'completada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ compra.estado }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Inventario -->
                <div v-show="activeTab === 'inventario'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Estado del Inventario</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ inventarioFiltrado.length }}</div>
                                <div class="text-sm text-blue-600">Total Productos</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ productosEnStock }}</div>
                                <div class="text-sm text-green-600">En Stock</div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ productosBajoStock }}</div>
                                <div class="text-sm text-yellow-600">Bajo Stock</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ productosAgotados }}</div>
                                <div class="text-sm text-red-600">Agotados</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="producto in inventarioFiltrado" :key="producto.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.categoria || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.stock }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(producto.precio) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getEstadoClass(producto.stock, producto.stock_minimo)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ getEstadoText(producto.stock, producto.stock_minimo) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Corte Diario -->
                <div v-show="activeTab === 'corte'">
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Corte Diario</h3>
                        <p class="text-sm text-gray-500 mb-4">Accede al reporte detallado de pagos diarios</p>
                        <a
                            href="/reportes/corte-diario"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                        >
                            Ver Corte Diario Completo
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { format, isWithinInterval } from 'date-fns';
import { es } from 'date-fns/locale';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    reportesVentas: { type: Array, default: () => [] },
    corteVentas: { type: Number, default: 0 },
    utilidadVentas: { type: Number, default: 0 },
    reportesCompras: { type: Array, default: () => [] },
    totalCompras: { type: Number, default: 0 },
    inventario: { type: Array, default: () => [] },
});

const activeTab = ref('ventas');
const fechaInicio = ref('');
const fechaFin = ref('');

const formatCurrency = (value) => `$${(Number.parseFloat(value) || 0).toFixed(2)}`;
const calculateProfit = (venta) => (Number.parseFloat(venta.total) || 0) - (Number.parseFloat(venta.costo_total) || 0);
const formatDate = (date) => {
    if (!date) return 'Fecha no disponible';
    try {
        return format(new Date(date), 'MMM d, yyyy h:mm a', { locale: es });
    } catch {
        return 'Fecha inválida';
    }
};

const filtrarPorFecha = (items) => {
    if (!fechaInicio.value || !fechaFin.value) return items;

    const start = new Date(fechaInicio.value);
    const end = new Date(fechaFin.value);

    return items.filter(item => {
        const fecha = new Date(item.created_at);
        return isWithinInterval(fecha, { start, end });
    });
};

const filtrarDatos = () => {
    // Los computed se actualizan automáticamente
};

const limpiarFiltros = () => {
    fechaInicio.value = '';
    fechaFin.value = '';
};

const ventasFiltradas = computed(() => filtrarPorFecha(props.reportesVentas));
const comprasFiltradas = computed(() => filtrarPorFecha(props.reportesCompras));
const inventarioFiltrado = computed(() => props.inventario); // Por ahora sin filtro de fecha, ya que inventario no tiene fecha

const corteFiltrado = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + Number.parseFloat(v.total), 0));
const utilidadFiltrada = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + calculateProfit(v), 0));
const totalComprasFiltrado = computed(() => comprasFiltradas.value.reduce((acc, c) => acc + Number.parseFloat(c.total), 0));

const productosEnStock = computed(() => inventarioFiltrado.value.filter(p => p.stock > 0).length);
const productosBajoStock = computed(() => inventarioFiltrado.value.filter(p => p.stock > 0 && p.stock <= (p.stock_minimo || 0)).length);
const productosAgotados = computed(() => inventarioFiltrado.value.filter(p => p.stock <= 0).length);

const getEstadoClass = (stock, minimo) => {
    if (stock <= 0) return 'bg-red-100 text-red-800';
    if (stock <= (minimo || 0)) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
};

const getEstadoText = (stock, minimo) => {
    if (stock <= 0) return 'Agotado';
    if (stock <= (minimo || 0)) return 'Bajo Stock';
    return 'En Stock';
};

const tabClass = (tab) => ({
    'border-blue-500 text-blue-600': activeTab.value === tab,
    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab.value !== tab,
});
</script>

<style scoped>
.container {
    max-width: 1200px;
}
</style>
