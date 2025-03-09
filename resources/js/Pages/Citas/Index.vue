<template>
    <Head title="Citas" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">Citas</h1>

        <div class="mb-4">
            <Link :href="route('citas.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Crear Cita
            </Link>
        </div>

        <div class="mb-4 flex space-x-4">
            <input v-model="filtroCliente" type="text" placeholder="Buscar por cliente" class="border rounded px-3 py-2">
            <input v-model="filtroTecnico" type="text" placeholder="Buscar por técnico" class="border rounded px-3 py-2">
            <select v-model="filtroTipoServicio" class="border rounded px-8 py-2">
                <option value="">Tipos de servicio</option>
                <option value="instalacion">Instalación</option>
                <option value="diagnostico">Diagnóstico</option>
                <option value="reparacion">Reparación</option>
                <option value="garantia">Garantía</option>
                <option value="otro_servicio">Otro Servicio</option>
            </select>
            <select v-model="filtroEstado" class="border rounded px-3 py-2">
                <option value="">Estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En Proceso</option>
                <option value="completado">Completado</option>
                <option value="cancelado">Cancelado</option>
            </select>
            <input v-model="filtroFechaTrabajo" type="date" placeholder="Fecha de trabajo" class="border rounded px-3 py-2">
        </div>

        <div v-if="citasFiltradas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">

                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha llamada</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo de Servicio</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha de trabajo</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="cita in citasFiltradas" :key="cita.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ cita.id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ formatearFechaHora(cita.created_at) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ cita.tecnico.nombre }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ cita.cliente.nombre_razon_social }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ formatearTipoServicio(cita.tipo_servicio) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ formatearFechaHora(cita.fecha_hora) }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span :class="estadoClase(cita.estado)">{{ formatearEstado(cita.estado) }}</span>
                        </td>
                        <td class="px-4 py-3 flex space-x-2">
                            <button @click="abrirModalDetalles(cita)" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition duration-300">
                                Mostrar
                            </button>
                            <Link :href="route('citas.edit', cita.id)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition duration-300">
                                Editar
                            </Link>
                            <button @click="abrirModal(cita.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-300">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="text-center text-gray-500 mt-4">
            No hay citas registradas.
        </div>

        <CitaModal :show="mostrarModalDetalles" :cita="citaSeleccionada" @close="cerrarModalDetalles" />

        <div v-if="mostrarModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h3 class="text-xl font-semibold mb-4">¿Estás seguro de eliminar esta cita?</h3>
                <p class="mb-4">Esta acción no se puede deshacer.</p>
                <div class="flex justify-end space-x-4">
                    <button @click="cerrarModal" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button @click="eliminarCita" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

import CitaModal from '@/Components/CitaModal.vue';

import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });



const { citas } = defineProps({ citas: Array });

const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const mostrarModal = ref(false);
const mostrarModalDetalles = ref(false);
const idCitaAEliminar = ref(null);
const citaSeleccionada = ref(null);
const filtroCliente = ref('');
const filtroTecnico = ref('');
const filtroTipoServicio = ref('');
const filtroEstado = ref('');
const filtroFechaTrabajo = ref('');

const citasFiltradas = computed(() => {
    return citas.filter(cita => {
        const clienteMatch = cita.cliente.nombre_razon_social.toLowerCase().includes(filtroCliente.value.toLowerCase());
        const tecnicoMatch = cita.tecnico.nombre.toLowerCase().includes(filtroTecnico.value.toLowerCase());
        const tipoServicioMatch = !filtroTipoServicio.value || cita.tipo_servicio === filtroTipoServicio.value;
        const estadoMatch = !filtroEstado.value || cita.estado === filtroEstado.value;

        // Improved date comparison
        const fechaTrabajoMatch = !filtroFechaTrabajo.value ||
            new Date(cita.fecha_hora).toISOString().split('T')[0] === filtroFechaTrabajo.value;

        return clienteMatch && tecnicoMatch && tipoServicioMatch && estadoMatch && fechaTrabajoMatch;
    });
});

const abrirModal = (id) => {
    idCitaAEliminar.value = id;
    mostrarModal.value = true;
};

const abrirModalDetalles = (cita) => {
    citaSeleccionada.value = cita;
    mostrarModalDetalles.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
    idCitaAEliminar.value = null;
};

const cerrarModalDetalles = () => {
    mostrarModalDetalles.value = false;
    citaSeleccionada.value = null;
};

const eliminarCita = async () => {
    try {
        await router.delete(route('citas.destroy', idCitaAEliminar.value), {
            onSuccess: () => {
                notyf.success('La cita ha sido eliminada exitosamente.');
                cerrarModal();
            },
            onError: (error) => {
                console.error('Error al eliminar la cita:', error);
                notyf.error('Hubo un error al eliminar la cita.');
                cerrarModal();
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
        cerrarModal();
    }
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

const formatearFechaHora = (fechaHora) => {
    const fecha = new Date(fechaHora);
    return fecha.toLocaleString();
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
