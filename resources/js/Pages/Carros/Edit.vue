<template>
    <Head title="Editar Carro" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Editar Carro</h1>

        <!-- Formulario para editar un carro -->
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Marca</label>
                <input v-model="form.marca" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Modelo</label>
                <input v-model="form.modelo" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Año</label>
                <input v-model="form.anio" type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Color</label>
                <input v-model="form.color" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Precio</label>
                <input v-model="form.precio" type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Actualizar Carro
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

// Recibe el carro como prop
const props = defineProps({ carro: Object });

// Configuración personalizada de Notyf
const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

// Formulario reactivo
const form = reactive({
    marca: props.carro.marca,
    modelo: props.carro.modelo,
    anio: props.carro.anio,
    color: props.carro.color,
    precio: props.carro.precio,
});

// Función para enviar el formulario
const submit = async () => {
    try {
        await router.put(route('carros.update', props.carro.id), form, {
            onSuccess: () => {
                notyf.success('El carro ha sido actualizado exitosamente.');
            },
            onError: (error) => {
                console.error('Error al actualizar el carro:', error);
                notyf.error('Hubo un error al actualizar el carro.');
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    }
};
</script>
