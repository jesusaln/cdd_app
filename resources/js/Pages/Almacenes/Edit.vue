<template>
    <Head title="Editar Almacén" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-4">Editar Almacén</h1>

        <!-- Formulario de edición de almacenes -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Campo Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input
                    v-model="form.nombre"
                    type="text"
                    id="nombre"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                />
                <div v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</div>
            </div>

            <!-- Campo Descripción -->
            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea
                    v-model="form.descripcion"
                    id="descripcion"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                ></textarea>
                <div v-if="form.errors.descripcion" class="text-red-500 text-sm">{{ form.errors.descripcion }}</div>
            </div>

            <!-- Campo Ubicación -->
            <div>
                <label for="ubicacion" class="block text-sm font-medium text-gray-700">Ubicación</label>
                <input
                    v-model="form.ubicacion"
                    type="text"
                    id="ubicacion"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                />
                <div v-if="form.errors.ubicacion" class="text-red-500 text-sm">{{ form.errors.ubicacion }}</div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4">
                <Link
                    :href="route('almacenes.index')"
                    class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition duration-300"
                >
                    Cancelar
                </Link>
                <button
                    type="submit"
                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-300"
                    :disabled="form.processing"
                >
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
//import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe los datos del almacén como prop
const props = defineProps({
    almacen: Object,
});

// Inicializa el formulario con los datos del almacén
const form = useForm({
    nombre: props.almacen.nombre,
    descripcion: props.almacen.descripcion || '',
    ubicacion: props.almacen.ubicacion,
});

// Función para enviar el formulario
const submit = () => {
    form.put(route('almacenes.update', props.almacen.id), {
        onSuccess: () => {
            alert('Almacén actualizado correctamente.');
        },
        onError: (errors) => {
            console.error('Error al actualizar:', errors);
        },
    });
};
</script>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
