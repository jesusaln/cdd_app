<template>
    <Head title="Editar Mantenimiento" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">Editar Mantenimiento</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Carro</label>
                <select v-model="form.carro_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option v-for="carro in carros" :key="carro.id" :value="carro.id">{{ carro.marca }} {{ carro.modelo }}</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Servicio</label>
                <select v-model="form.tipo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required @change="handleServiceChange">
                    <option value="Revisión periódica">Revisión periódica</option>
                    <option value="Servicio de frenos">Servicio de frenos</option>
                    <option value="Revisión de luces">Revisión de luces</option>
                    <option value="Servicio de llantas">Servicio de llantas</option>
                    <option value="Servicio de batería">Servicio de batería</option>
                    <option value="Servicio de motor">Servicio de motor</option>
                    <option value="Cambio de aceite">Cambio de aceite</option>
                    <option value="Otro servicio">Otro servicio</option>
                </select>
            </div>
            <div v-if="form.tipo === 'Otro servicio'" class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Especifique el servicio</label>
                <input v-model="form.otro_servicio" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Fecha</label>
                <input v-model="form.fecha" type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Próximo Mantenimiento (Aproximado)</label>
                <input v-model="form.proximo_mantenimiento" type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Notas</label>
                <textarea v-model="form.notas" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Actualizar Mantenimiento
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    mantenimiento: Object,
    carros: Array
});

const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const form = reactive({
    carro_id: props.mantenimiento.carro_id,
    tipo: props.mantenimiento.tipo, // Asegúrate de que este campo esté presente
    otro_servicio: props.mantenimiento.otro_servicio || '',
    fecha: props.mantenimiento.fecha,
    proximo_mantenimiento: props.mantenimiento.proximo_mantenimiento,
    notas: props.mantenimiento.notas,
});

const handleServiceChange = () => {
    if (form.tipo !== 'Otro servicio') {
        form.otro_servicio = '';
    }
};

const submit = async () => {
    try {
        await router.put(route('mantenimientos.update', props.mantenimiento.id), form, {
            onSuccess: () => {
                notyf.success('El mantenimiento ha sido actualizado exitosamente.');
            },
            onError: (error) => {
                console.error('Error al actualizar el mantenimiento:', error);
                notyf.error('Hubo un error al actualizar el mantenimiento.');
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    }
};
</script>
