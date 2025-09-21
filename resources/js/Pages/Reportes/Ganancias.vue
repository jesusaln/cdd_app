<template>
    <Head title="Reporte de Ganancias" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Reporte de Ganancias</h1>
                        <p class="text-sm text-gray-600 mt-1">Análisis financiero completo del período</p>
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input
                            v-model="filtros.fecha_inicio"
                            type="date"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="filtrar"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                        <input
                            v-model="filtros.fecha_fin"
                            type="date"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="filtrar"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="filtrar"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                        >
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="p-6">
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen del Período</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Ingresos -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-green-800 mb-4">INGRESOS</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-green-700">Ventas</span>
                                    <span class="font-medium text-green-800">{{ formatCurrency(ingresos.ventas) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-green-700">Servicios</span>
                                    <span class="font-medium text-green-800">{{ formatCurrency(ingresos.servicios) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-green-700">Cobranzas</span>
                                    <span class="font-medium text-green-800">{{ formatCurrency(ingresos.cobranzas) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-green-300 pt-2">
                                    <span class="font-semibold text-green-800">TOTAL INGRESOS</span>
                                    <span class="font-bold text-green-900">{{ formatCurrency(ingresos.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Gastos -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-red-800 mb-4">GASTOS</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-red-700">Compras</span>
                                    <span class="font-medium text-red-800">{{ formatCurrency(gastos.compras) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-red-700">Costos Ventas</span>
                                    <span class="font-medium text-red-800">{{ formatCurrency(gastos.costos_ventas) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-red-300 pt-2">
                                    <span class="font-semibold text-red-800">TOTAL GASTOS</span>
                                    <span class="font-bold text-red-900">{{ formatCurrency(gastos.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Resultado -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-blue-800 mb-4">RESULTADO</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Ganancia Ventas</span>
                                    <span class="font-medium text-blue-800">{{ formatCurrency(ganancias.ventas) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Rentas Proyectadas</span>
                                    <span class="font-medium text-blue-800">{{ formatCurrency(ingresos.rentas_proyectadas) }}</span>
                                </div>
                                <div class="flex justify-between border-t-2 border-blue-300 pt-2">
                                    <span class="font-bold text-blue-900">GANANCIA NETA</span>
                                    <span :class="ganancias.neta >= 0 ? 'text-green-600' : 'text-red-600'" class="font-bold text-xl">
                                        {{ formatCurrency(ganancias.neta) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Período -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Período Analizado</h4>
                            <p class="text-sm text-gray-600">
                                Del {{ formatDate(periodo.inicio) }} al {{ formatDate(periodo.fin) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div :class="ganancias.neta >= 0 ? 'text-green-600' : 'text-red-600'" class="text-2xl font-bold">
                                {{ ganancias.neta >= 0 ? '✅ BENEFICIO' : '⚠️ PÉRDIDA' }}
                            </div>
                            <div class="text-sm text-gray-500">Estado financiero</div>
                        </div>
                    </div>
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
    periodo: Object,
    ingresos: Object,
    gastos: Object,
    ganancias: Object,
});

const filtros = ref({
    fecha_inicio: props.periodo?.inicio || '',
    fecha_fin: props.periodo?.fin || '',
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value || 0);
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-MX');
};

const filtrar = () => {
    router.get(route('reportes.ganancias'), filtros.value, {
        preserveState: true,
        replace: true,
    });
};
</script>
