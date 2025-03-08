<template>
    <Head title="Panel" />
    <div>
        <h1 class="text-2xl font-bold mb-4">Bienvenido al Panel</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">Clientes ({{ clientesCount }})</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 4h-1A3 3 0 0013 2.143V4h5v16z" />
                    </svg>
                </div>
                <p class="text-gray-600">Administrar clientes registrados.</p>
                <NavLink href="/clientes" class="text-blue-500 mt-2 inline-block">Ver más</NavLink>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">Productos</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 0v2m4-2v2m-6-2v2m-4-2v2m4 0h.01M20 11a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-gray-600">Gestionar inventario y productos.</p>
                <NavLink href="/productos" class="text-blue-500 mt-2 inline-block">Ver más</NavLink>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">Proveedores</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-gray-600">Lista de proveedores registrados.</p>
                <NavLink href="/proveedores" class="text-blue-500 mt-2 inline-block">Ver más</NavLink>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">Citas Pendientes y en Proceso</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <ul>
                    <li v-for="cita in citasPendientesEnProceso" :key="cita.id" class="mb-2">
                        <strong>{{ cita.cliente.nombre_razon_social }}</strong> - {{ formatearTipoServicio(cita.tipo_servicio) }}
                        <span :class="estadoClase(cita.estado)">({{ formatearEstado(cita.estado) }})</span>
                    </li>
                </ul>
                <NavLink href="/citas" class="text-blue-500 mt-2 inline-block">Ver todas las citas</NavLink>
            </div>
        </div>
    </div>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue';
import NavLink from '@/Components/NavLink.vue';
import { computed } from 'vue';

defineOptions({ layout: Dashboard });

const props = defineProps({
    clientesCount: Number,
    citas: {
        type: Array,
        default: () => [] // Inicializa con un array vacío por defecto
    }
});

const citasPendientesEnProceso = computed(() => {
    // Verifica que citas sea un array antes de intentar filtrar
    if (!Array.isArray(props.citas)) {
        console.error('citas no es un array:', props.citas);
        return [];
    }
    return props.citas.filter(cita => cita.estado === 'pendiente' || cita.estado === 'en_proceso');
});

const formatearTipoServicio = (tipo) => {
    const tipos = {
        instalacion: 'Instalación',
        diagnostico: 'Diagnóstico',
        reparacion: 'Reparación',
        garantia: 'Garantía',
        otro_servicio: 'Otro Servicio'
    };
    return tipos[tipo] || 'Desconocido';
};

const formatearEstado = (estado) => {
    const estados = {
        pendiente: 'Pendiente',
        en_proceso: 'En Proceso',
        completado: 'Completado',
        cancelado: 'Cancelado'
    };
    return estados[estado] || 'Desconocido';
};

const estadoClase = (estado) => {
    const clases = {
        pendiente: 'text-yellow-500',
        en_proceso: 'text-blue-500',
        completado: 'text-green-500',
        cancelado: 'text-red-500'
    };
    return clases[estado] || 'text-gray-500';
};
</script>
