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
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Número de Serie</label>
                <input v-model="form.numero_serie" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Combustible</label>
                <select v-model="form.combustible" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="Gasolina">Gasolina</option>
                    <option value="Diésel">Diésel</option>
                    <option value="Eléctrico">Eléctrico</option>
                    <option value="Híbrido">Híbrido</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kilometraje</label>
                <input v-model="form.kilometraje" type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Placa</label>
                <input v-model="form.placa" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Foto Actual</label>
                <img :src="props.carro.foto" alt="Foto actual" class="w-48 h-auto rounded mb-2" v-if="props.carro.foto">
                <input type="file" @change="onFileChange" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <div v-if="previewImage" class="mt-2">
                    <img :src="previewImage" alt="Vista previa" class="w-48 h-auto rounded">
                </div>
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
import { reactive, ref } from 'vue';
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

// Variables reactivas
const form = reactive({
    marca: props.carro.marca,
    modelo: props.carro.modelo,
    anio: props.carro.anio,
    color: props.carro.color,
    precio: props.carro.precio,
    numero_serie: props.carro.numero_serie,
    combustible: props.carro.combustible,
    kilometraje: props.carro.kilometraje,
    placa: props.carro.placa,
    foto: null, // Para almacenar el archivo seleccionado
});
const previewImage = ref(null); // Para mostrar la vista previa de la imagen

// Manejar la selección de archivos
const onFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.foto = file;
        previewImage.value = URL.createObjectURL(file); // Generar vista previa
    } else {
        form.foto = null;
        previewImage.value = null;
    }
};

// Función para enviar el formulario
const submit = async () => {
    try {
        const formData = new FormData();
        for (const key of Object.keys(form)) {
            if (key === 'foto' && form[key]) {
                formData.append(key, form[key]);
            } else {
                formData.append(key, form[key]);
            }
        }

        await router.put(route('carros.update', props.carro.id), formData, {
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
