<template>
    <Head title="Crear Carro" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Crear Carro</h1>

        <!-- Formulario para crear un nuevo carro -->
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
                    Crear Carro
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
    marca: '',
    modelo: '',
    anio: '',
    color: '',
    precio: '',
});

// Función para enviar el formulario
const submit = async () => {
    try {
        await router.post(route('carros.store'), form, {
            onSuccess: () => {
                notyf.success('El carro ha sido creado exitosamente.');
                form.marca = '';
                form.modelo = '';
                form.anio = '';
                form.color = '';
                form.precio = '';
            },
            onError: (error) => {
                console.error('Error al crear el carro:', error);
                notyf.error('Hubo un error al crear el carro.');
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    }
};
</script>
