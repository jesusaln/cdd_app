<template>
    <Head title="Centro de Reportes" />

    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">Centro de Reportes</h1>
                <p class="text-sm text-gray-600 mt-1">Accede a todos los reportes del sistema organizados por categorías</p>

                <!-- Selector de período -->
                <div class="mt-4 flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Período:</label>
                        <select
                            v-model="selectedPeriod"
                            @change="changePeriod"
                            class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="dia">Hoy</option>
                            <option value="semana">Esta Semana</option>
                            <option value="mes">Este Mes</option>
                            <option value="trimestre">Este Trimestre</option>
                            <option value="año">Este Año</option>
                        </select>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">{{ periodo_label }}</span>
                        ({{ fecha_inicio }} - {{ fecha_fin }})
                    </div>
                </div>
            </div>

            <!-- Contenido -->
            <div class="p-6 space-y-6">
                <!-- Categorías de reportes -->
                <div v-for="(categoria, key) in categorias" :key="key" class="border border-gray-200 rounded-lg">
                    <button
                        @click="toggleAccordion(key)"
                        class="w-full px-6 py-4 text-left bg-gray-50 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getCategoriaColor(key)">
                                    <component :is="getCategoriaIcon(key)" class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ categoria.titulo }}</h3>
                                    <p class="text-sm text-gray-600">{{ categoria.descripcion }}</p>
                                </div>
                            </div>
                            <svg
                                :class="['w-5 h-5 text-gray-500 transform transition-transform', { 'rotate-180': activeAccordion === key }]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div v-show="activeAccordion === key" class="px-6 pb-4">
                        <!-- Estadísticas de la categoría -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                            <div v-for="(stat, statKey) in categoria.estadisticas" :key="statKey" class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ formatNumber(stat) }}</div>
                                <div class="text-sm text-gray-600 capitalize">{{ statKey.replace('_', ' ') }}</div>
                            </div>
                        </div>

                        <!-- Reportes de la categoría -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="reporte in categoria.reportes"
                                :key="reporte.nombre"
                                class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getCategoriaColor(key)">
                                        <component :is="getCategoriaIcon(key)" class="w-6 h-6 text-white" />
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-900">{{ reporte.nombre }}</h4>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">{{ getReporteDescription(reporte.nombre) }}</p>
                                <Link
                                    :href="route(reporte.ruta)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                                >
                                    Ver Reporte
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

// Props del controlador
const props = defineProps({
    categorias: Object,
    periodo: String,
    periodo_label: String,
    fecha_inicio: String,
    fecha_fin: String,
});

// Estado reactivo
const selectedPeriod = ref(props.periodo || 'mes');

// Estado del acordeón activo
const activeAccordion = ref(null);

// Función para alternar acordeones
const toggleAccordion = (section) => {
    activeAccordion.value = activeAccordion.value === section ? null : section;
};

// Función para cambiar período
const changePeriod = () => {
    // Redirigir con el nuevo período
    window.location.href = route('reportes.index', { periodo: selectedPeriod.value });
};

// Función para formatear números
const formatNumber = (value) => {
    if (typeof value === 'number') {
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(1) + 'K';
        }
        return value.toLocaleString();
    }
    return value;
};

// Función para obtener el color de la categoría
const getCategoriaColor = (categoria) => {
    const colores = {
        ventas: 'bg-blue-600',
        pagos: 'bg-green-600',
        clientes: 'bg-purple-600',
        inventario: 'bg-orange-600',
        servicios: 'bg-indigo-600',
        rentas: 'bg-teal-600',
        finanzas: 'bg-yellow-600',
        personal: 'bg-red-600',
        auditoria: 'bg-gray-600',
    };
    return colores[categoria] || 'bg-gray-600';
};

// Función para obtener el ícono de la categoría
const getCategoriaIcon = (categoria) => {
    const iconos = {
        ventas: 'ShoppingCartIcon',
        pagos: 'CashIcon',
        clientes: 'UsersIcon',
        inventario: 'ArchiveIcon',
        servicios: 'WrenchIcon',
        rentas: 'HandIcon',
        finanzas: 'ChartBarIcon',
        personal: 'UserGroupIcon',
        auditoria: 'DocumentIcon',
    };
    return iconos[categoria] || 'DocumentIcon';
};

// Función para obtener descripción del reporte
const getReporteDescription = (nombre) => {
    const descripciones = {
        'Ventas Generales': 'Análisis completo de todas las ventas realizadas',
        'Productos Más Vendidos': 'Ranking de productos por volumen de ventas',
        'Corte de Pagos': 'Resumen diario/semanal de ingresos y pagos',
        'Cobranzas': 'Estado de pagos pendientes y realizados',
        'Clientes Activos': 'Clientes con actividad reciente',
        'Clientes Deudores': 'Clientes con pagos pendientes',
        'Productos en Stock': 'Estado actual del inventario',
        'Productos Bajos': 'Productos por debajo del stock mínimo',
        'Servicios Más Vendidos': 'Servicios más demandados',
        'Citas Programadas': 'Calendario de citas agendadas',
        'Mantenimientos': 'Historial de trabajos de mantenimiento',
        'Rentas Activas': 'Equipos actualmente rentados',
        'Ganancias Generales': 'Análisis financiero completo',
        'Compras': 'Historial de compras realizadas',
        'Proveedores': 'Análisis de proveedores y compras',
        'Técnicos': 'Rendimiento y comisiones por técnico',
        'Empleados': 'Información general del personal',
        'Bitácora de Actividades': 'Registro de todas las operaciones',
    };
    return descripciones[nombre] || 'Descripción no disponible';
};

// Componentes de íconos (usando Heroicons)
const ShoppingCartIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13v8a2 2 0 002 2h10a2 2 0 002-2v-3"/></svg>`
};

const CashIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7l4 0m0 4l4 0"/></svg>`
};

const UsersIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>`
};

const ArchiveIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>`
};

const WrenchIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`
};

const HandIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6v2.5m0-2.5c1.5 0 3-1.5 3-3.5s-1.5-3.5-3-3.5-3 1.5-3 3.5 1.5 3.5 3 3.5z"/></svg>`
};

const ChartBarIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`
};

const UserGroupIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`
};

const DocumentIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
