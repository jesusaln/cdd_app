<template>
    <Head title="Ver Cita" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">Detalles de la Cita</h1>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Cliente</label>
            <p class="text-gray-700">{{ clienteNombre }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Servicio</label>
            <p class="text-gray-700">{{ formatearTipoServicio(cita.tipo_servicio) }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Fecha y Hora</label>
            <p class="text-gray-700">{{ formatearFechaHora(cita.fecha_hora) }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
            <p class="text-gray-700">{{ cita.descripcion }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Equipo</label>
            <p class="text-gray-700">{{ formatearTipoEquipo(cita.tipo_equipo) }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Marca del Equipo</label>
            <p class="text-gray-700">{{ cita.marca_equipo }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Modelo del Equipo</label>
            <p class="text-gray-700">{{ cita.modelo_equipo }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Problema Reportado</label>
            <p class="text-gray-700">{{ cita.problema_reportado }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
            <p class="text-gray-700">{{ formatearEstado(cita.estado) }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Técnico</label>
            <p class="text-gray-700">{{ tecnicoNombre }}</p>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Dashboard from '@/Pages/Dashboard.vue';

defineOptions({ layout: Dashboard });

const props = defineProps({
    cita: Object,
    tecnicos: Array,
    clientes: Array
});

const clienteNombre = computed(() => {
    const cliente = props.clientes.find(c => c.id === props.cita.cliente_id);
    return cliente ? cliente.nombre_razon_social : 'Desconocido';
});

const tecnicoNombre = computed(() => {
    const tecnico = props.tecnicos.find(t => t.id === props.cita.tecnico_id);
    return tecnico ? tecnico.nombre : 'Desconocido';
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

const formatearTipoEquipo = (tipo) => {
    const tipos = {
        minisplit: 'Minisplit',
        boiler: 'Boiler',
        refrigerador: 'Refrigerador',
        lavadora: 'Lavadora',
        secadora: 'Secadora',
        estufa: 'Estufa',
        campana: 'Campana',
        horno_de_microondas: 'Horno de Microondas',
        licuadora: 'Licuadora',
        otro_equipo: 'Otro Equipo'
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

const formatearFechaHora = (fechaHora) => {
    const fecha = new Date(fechaHora);
    return fecha.toLocaleString();
};
</script>
