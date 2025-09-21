<template>
    <Head title="Reporte de Ganancias por Técnico" />

    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">Reporte de Ganancias por Técnico</h1>
                <p class="text-sm text-gray-600 mt-1">Análisis detallado de márgenes y comisiones</p>
            </div>

            <!-- Filtros -->
            <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Técnico -->
                    <div>
                        <label for="tecnico_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Técnico
                        </label>
                        <select
                            v-model="filtros.tecnico_id"
                            id="tecnico_id"
                            class="input-field"
                            @change="aplicarFiltros"
                        >
                            <option value="">Todos los técnicos</option>
                            <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
                                {{ tecnico.nombre }} {{ tecnico.apellido }}
                            </option>
                        </select>
                    </div>

                    <!-- Período -->
                    <div>
                        <label for="periodo" class="block text-sm font-medium text-gray-700 mb-1">
                            Período
                        </label>
                        <select
                            v-model="filtros.periodo"
                            id="periodo"
                            class="input-field"
                            @change="cambiarPeriodo"
                        >
                            <option value="dia">Hoy</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>

                    <!-- Fecha Inicio -->
                    <div v-show="filtros.periodo === 'personalizado'">
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha Inicio
                        </label>
                        <input
                            v-model="filtros.fecha_inicio"
                            type="date"
                            id="fecha_inicio"
                            class="input-field"
                            @change="aplicarFiltros"
                        />
                    </div>

                    <!-- Fecha Fin -->
                    <div v-show="filtros.periodo === 'personalizado'">
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha Fin
                        </label>
                        <input
                            v-model="filtros.fecha_fin"
                            type="date"
                            id="fecha_fin"
                            class="input-field"
                            @change="aplicarFiltros"
                        />
                    </div>
                </div>
            </div>

            <!-- Contenido del Reporte -->
            <div class="p-6">
                <!-- Resumen General -->
                <div v-if="reporte.length > 0" class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen General</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">
                                ${{ totalesGenerales.ventas.toFixed(2) }}
                            </div>
                            <div class="text-sm text-blue-600">Total Ventas</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">
                                ${{ totalesGenerales.ganancia_base.toFixed(2) }}
                            </div>
                            <div class="text-sm text-green-600">Ganancia Base</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">
                                ${{ totalesGenerales.comisiones.toFixed(2) }}
                            </div>
                            <div class="text-sm text-purple-600">Comisiones</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">
                                ${{ totalesGenerales.ganancia_total.toFixed(2) }}
                            </div>
                            <div class="text-sm text-yellow-600">Ganancia Total</div>
                        </div>
                    </div>
                </div>

                <!-- Reporte por Técnico -->
                <div v-if="reporte.length > 0">
                    <div v-for="tecnicoData in reporte" :key="tecnicoData.tecnico.id" class="mb-8">
                        <!-- Header del Técnico -->
                        <div class="bg-gray-100 p-4 rounded-lg mb-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ tecnicoData.tecnico.nombre }}
                                    </h4>
                                    <p class="text-sm text-gray-600">{{ tecnicoData.tecnico.email }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-green-600">
                                        ${{ tecnicoData.totales.ganancia_total.toFixed(2) }}
                                    </div>
                                    <div class="text-sm text-gray-600">Ganancia Total</div>
                                </div>
                            </div>

                            <!-- Configuración del Técnico -->
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Margen Productos:</span>
                                    <span class="font-medium ml-2">{{ tecnicoData.tecnico.margen_productos }}%</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Margen Servicios:</span>
                                    <span class="font-medium ml-2">{{ tecnicoData.tecnico.margen_servicios }}%</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Comisión Instalación:</span>
                                    <span class="font-medium ml-2">${{ tecnicoData.tecnico.comision_instalacion }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Ventas -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Venta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Productos/Servicios
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Venta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ganancia Base
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Márgen Técnico
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Comisiones
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ganancia Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="venta in tecnicoData.ventas" :key="venta.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ venta.numero_venta }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ venta.fecha }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ venta.cliente }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ venta.productos_count }}P / {{ venta.servicios_count }}S
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ venta.total_venta.toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                            ${{ venta.ganancia_base.toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600">
                                            ${{ venta.margen_tecnico.toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">
                                            ${{ (venta.comision_productos + venta.comision_servicios + venta.comision_instalacion).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                            ${{ venta.ganancia_total.toFixed(2) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-sm font-medium text-gray-900">
                                            Total {{ tecnicoData.tecnico.nombre }}:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            ${{ tecnicoData.totales.ventas_total.toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                                            ${{ tecnicoData.totales.ganancia_base.toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-600">
                                            ${{ tecnicoData.totales.margen_tecnico.toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-orange-600">
                                            ${{ (tecnicoData.totales.comision_productos + tecnicoData.totales.comision_servicios + tecnicoData.totales.comision_instalacion).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                            ${{ tecnicoData.totales.ganancia_total.toFixed(2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sin datos -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay datos</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        No se encontraron ventas para el período seleccionado.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue'


defineOptions({ layout: AppLayout })

// Props del servidor
const props = defineProps({
    tecnicos: Array,
    reporte: Array,
    filtros: Object,
});

// Estado reactivo
const filtros = ref({
    tecnico_id: props.filtros.tecnico_id || '',
    periodo: props.filtros.periodo || 'mes',
    fecha_inicio: props.filtros.fecha_inicio || '',
    fecha_fin: props.filtros.fecha_fin || '',
});

// Calcular totales generales
const totalesGenerales = computed(() => {
    return props.reporte.reduce((totales, tecnico) => {
        return {
            ventas: totales.ventas + tecnico.totales.ventas_total,
            ganancia_base: totales.ganancia_base + tecnico.totales.ganancia_base,
            comisiones: totales.comisiones + tecnico.totales.comision_productos + tecnico.totales.comision_servicios + tecnico.totales.comision_instalacion,
            ganancia_total: totales.ganancia_total + tecnico.totales.ganancia_total,
        };
    }, {
        ventas: 0,
        ganancia_base: 0,
        comisiones: 0,
        ganancia_total: 0,
    });
});

// Aplicar filtros
const aplicarFiltros = () => {
    // Recargar la página con los nuevos filtros
    window.location.href = route('reportes.tecnicos', filtros.value);
};

// Cambiar período
const cambiarPeriodo = () => {
    if (filtros.value.periodo !== 'personalizado') {
        filtros.value.fecha_inicio = '';
        filtros.value.fecha_fin = '';
    }
    aplicarFiltros();
};
</script>

<style scoped>
.input-field {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900;
}

.input-field option {
    @apply text-gray-900 bg-white;
}
</style>
