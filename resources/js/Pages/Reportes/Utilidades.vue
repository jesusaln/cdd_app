<template>
    <Head title="Reporte de Utilidades" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">Reporte de Utilidades</h1>
                <p class="text-sm text-gray-600 mt-1">Análisis de ganancias y pérdidas por período</p>
            </div>

            <!-- Filtros -->
            <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="periodo" class="block text-sm font-medium text-gray-700 mb-1">
                            Período
                        </label>
                        <select
                            v-model="periodoSeleccionado"
                            id="periodo"
                            class="input-field"
                            @change="cambiarPeriodo"
                        >
                            <option value="dia">Hoy</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                            <option value="trimestre">Este trimestre</option>
                            <option value="ano">Este año</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>

                    <div v-show="periodoSeleccionado === 'personalizado'">
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

                    <div v-show="periodoSeleccionado === 'personalizado'">
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
                            @click="exportarUtilidades"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                        >
                            Exportar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(totalVentas) }}</div>
                        <div class="text-sm text-blue-600">Total Ventas</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalCompras) }}</div>
                        <div class="text-sm text-red-600">Total Compras</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(utilidadBruta) }}</div>
                        <div class="text-sm text-green-600">Utilidad Bruta</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(margenUtilidad) }}%</div>
                        <div class="text-sm text-purple-600">Margen de Utilidad</div>
                    </div>
                </div>

                <!-- Gráfico de Utilidades (placeholder) -->
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tendencia de Utilidades</h3>
                    <div class="h-64 flex items-center justify-center">
                        <p class="text-gray-500">Gráfico de utilidades por período (implementar con Chart.js)</p>
                    </div>
                </div>

                <!-- Tabla Detallada -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Compras</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilidad Bruta</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="periodo in datosPorPeriodo" :key="periodo.periodo">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ periodo.periodo }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(periodo.ventas) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(periodo.compras) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">{{ formatCurrency(periodo.utilidad) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600">{{ periodo.margen }}%</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ formatCurrency(totalVentas) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ formatCurrency(totalCompras) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">{{ formatCurrency(utilidadBruta) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-600">{{ formatCurrency(margenUtilidad) }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
    ventas: { type: Array, default: () => [] },
    compras: { type: Array, default: () => [] },
});

// Estado
const periodoSeleccionado = ref('mes');
const fechaInicio = ref('');
const fechaFin = ref('');

// Helpers
const formatCurrency = (value) => `$${(Number.parseFloat(value) || 0).toFixed(2)}`;

// Calcular totales
const totalVentas = computed(() => props.ventas.reduce((sum, v) => sum + Number.parseFloat(v.total || 0), 0));
const totalCompras = computed(() => props.compras.reduce((sum, c) => sum + Number.parseFloat(c.total || 0), 0));
const utilidadBruta = computed(() => totalVentas.value - totalCompras.value);
const margenUtilidad = computed(() => totalVentas.value > 0 ? ((utilidadBruta.value / totalVentas.value) * 100).toFixed(2) : 0);

// Datos por período (simplificado)
const datosPorPeriodo = computed(() => {
    // Aquí se agruparían los datos por período, por ahora devolver un ejemplo
    return [
        {
            periodo: 'Esta semana',
            ventas: totalVentas.value * 0.3,
            compras: totalCompras.value * 0.25,
            utilidad: (totalVentas.value * 0.3) - (totalCompras.value * 0.25),
            margen: (((totalVentas.value * 0.3) - (totalCompras.value * 0.25)) / (totalVentas.value * 0.3) * 100).toFixed(2)
        },
        {
            periodo: 'Semana anterior',
            ventas: totalVentas.value * 0.7,
            compras: totalCompras.value * 0.75,
            utilidad: (totalVentas.value * 0.7) - (totalCompras.value * 0.75),
            margen: (((totalVentas.value * 0.7) - (totalCompras.value * 0.75)) / (totalVentas.value * 0.7) * 100).toFixed(2)
        }
    ];
});

// Funciones
const cambiarPeriodo = () => {
    if (periodoSeleccionado.value !== 'personalizado') {
        fechaInicio.value = '';
        fechaFin.value = '';
    }
    filtrarDatos();
};

const filtrarDatos = () => {
    // Recargar con filtros
    router.get(route('reportes.utilidades'), {
        periodo: periodoSeleccionado.value,
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value
    }, { preserveState: true });
};

const exportarUtilidades = () => {
    const params = new URLSearchParams({
        periodo: periodoSeleccionado.value,
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
        tipo: 'utilidades'
    });
    window.location.href = route('reportes.export') + `?${params.toString()}`;
};
</script>

<style scoped>
.input-field {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900;
}
</style>
