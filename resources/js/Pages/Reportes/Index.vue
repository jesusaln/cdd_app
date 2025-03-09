<template>
    <Head title="Reportes" />
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold mb-4">Reportes</h1>

        <!-- Filtros de Fecha y Hora -->
        <div class="mb-4 flex space-x-4 items-center">
            <label>Desde:</label>
            <input type="datetime-local" v-model="fechaInicio" class="border px-2 py-1 rounded">

            <label>Hasta:</label>
            <input type="datetime-local" v-model="fechaFin" class="border px-2 py-1 rounded">

            <button @click="filtrarReportes" class="bg-blue-500 text-white px-4 py-2 rounded">
                Filtrar
            </button>
        </div>

        <!-- Pestañas -->
        <div class="mb-4 space-x-2">
            <button @click="activeTab = 'ventas'" :class="tabClass('ventas')" class="px-4 py-2 rounded-l-lg">Ventas</button>
            <button @click="activeTab = 'compras'" :class="tabClass('compras')" class="px-4 py-2">Compras</button>
            <button @click="activeTab = 'inventarios'" :class="tabClass('inventarios')" class="px-4 py-2 rounded-r-lg">Inventarios</button>
        </div>

        <!-- Reporte de Ventas -->
        <div v-if="activeTab === 'ventas'">
            <h2 class="text-lg font-semibold mb-2">Reporte de Ventas</h2>
            <div class="mb-6 space-y-2">
                <p class="text-lg">Corte Total: {{ formatCurrency(corteFiltrado) }}</p>
                <p class="text-lg">Utilidad Total: {{ formatCurrency(utilidadFiltrada) }}</p>
            </div>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">ID Venta</th>
                            <th class="px-4 py-2 text-left">Total Venta</th>
                            <th class="px-4 py-2 text-left">Costo Total</th>
                            <th class="px-4 py-2 text-left">Utilidad</th>
                            <th class="px-4 py-2 text-left">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="venta in ventasFiltradas" :key="venta.id" class="border-t hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2">{{ venta.id }}</td>
                            <td class="px-4 py-2">{{ formatCurrency(venta.total) }}</td>
                            <td class="px-4 py-2">{{ formatCurrency(venta.costo_total) }}</td>
                            <td class="px-4 py-2">{{ formatCurrency(calculateProfit(venta)) }}</td>
                            <td class="px-4 py-2">{{ formatDate(venta.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reporte de Compras -->
        <div v-if="activeTab === 'compras'">
            <h2 class="text-lg font-semibold mb-2">Reporte de Compras</h2>
            <p class="text-lg">Total Compras: {{ formatCurrency(totalComprasFiltrado) }}</p>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">ID Compra</th>
                            <th class="px-4 py-2 text-left">Total Compra</th>
                            <th class="px-4 py-2 text-left">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="compra in comprasFiltradas" :key="compra.id" class="border-t hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2">{{ compra.id }}</td>
                            <td class="px-4 py-2">{{ formatCurrency(compra.total) }}</td>
                            <td class="px-4 py-2">{{ formatDate(compra.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reporte de Inventarios -->
        <div v-if="activeTab === 'inventarios' && inventario.length">

            <h2 class="text-lg font-semibold mb-2">Reporte de Inventarios</h2>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">ID Producto</th>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Stock</th>
                            <th class="px-4 py-2 text-left">Precio Unitario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="producto in inventario" :key="producto.id" class="border-t hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2">{{ producto.id }}</td>
                            <td class="px-4 py-2">{{ producto.nombre }}</td>
                            <td class="px-4 py-2">{{ producto.stock }}</td>
                            <td class="px-4 py-2">{{ formatCurrency(producto.precio_venta) }}</td>
                        </tr>
                    </tbody>
                </table>
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

const ventasFiltradas = computed(() => filtrarPorFecha(props.reportesVentas));
const comprasFiltradas = computed(() => filtrarPorFecha(props.reportesCompras));
const corteFiltrado = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + Number.parseFloat(v.total), 0));
const utilidadFiltrada = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + calculateProfit(v), 0));
const totalComprasFiltrado = computed(() => comprasFiltradas.value.reduce((acc, c) => acc + Number.parseFloat(c.total), 0));

const tabClass = (tab) => ({
    'bg-blue-500 text-white': activeTab.value === tab,
    'bg-gray-300': activeTab.value !== tab,
});
</script>

<style scoped>
.container {
    max-width: 1200px;
}
</style>
