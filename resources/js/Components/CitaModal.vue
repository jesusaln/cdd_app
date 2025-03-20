<template>
    <div v-if="show" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-xl font-semibold mb-4">Detalles de la Cita</h3>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Cliente</label>
                <p>{{ cita.cliente.nombre_razon_social }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Tipo de Servicio</label>
                <p>{{ formatearTipoServicio(cita.tipo_servicio) }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Fecha y Hora</label>
                <p>{{ formatearFechaHora(cita.fecha_hora) }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Descripción</label>
                <p>{{ cita.descripcion }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Tipo de Equipo</label>
                <p>{{ formatearTipoEquipo(cita.tipo_equipo) }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Marca del Equipo</label>
                <p>{{ cita.marca_equipo }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Modelo del Equipo</label>
                <p>{{ cita.modelo_equipo }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Problema Reportado</label>
                <p>{{ cita.problema_reportado }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Estado</label>
                <p>{{ formatearEstado(cita.estado) }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Técnico</label>
                <p>{{ cita.tecnico.nombre }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Evidencias</label>
                <p>{{ cita.evidencias || 'No hay evidencias disponibles' }}</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Foto del Equipo</label>
                <img v-if="cita.foto_equipo" :src="cita.foto_equipo" alt="Foto del Equipo" class="w-full h-auto mt-2">
                <p v-else class="text-gray-700">No hay foto del equipo disponible</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Foto de la Hoja de Servicio</label>
                <img v-if="cita.foto_hoja_servicio" :src="cita.foto_hoja_servicio" alt="Foto de la Hoja de Servicio" class="w-full h-auto mt-2">
                <p v-else class="text-gray-700">No hay foto de la hoja de servicio disponible</p>
            </div>
            <div class="mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-1">Foto de Identificación del Cliente</label>
                <img v-if="cita.foto_identificacion" :src="cita.foto_identificacion" alt="Foto de Identificación del Cliente" class="w-full h-auto mt-2">
                <p v-else class="text-gray-700">No hay foto de identificación disponible</p>
            </div>
            <div class="flex justify-end mt-4">
                <button @click="close" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</template>


<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    cita: Object,
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

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
