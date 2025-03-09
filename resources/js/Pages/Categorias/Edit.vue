<template>
    <Head title="Editar categorias" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-4">Editar Categoría</h1>

        <!-- Formulario de edición de categorías -->
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

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4">
                <Link :href="route('categorias.index')" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition duration-300">
                    Cancelar
                </Link>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-300">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe los datos de la categoría como prop
const props = defineProps({
    categoria: Object,
});

// Inicializa el formulario con los datos de la categoría
const form = useForm({
    nombre: props.categoria.nombre,
    descripcion: props.categoria.descripcion || '',
});

// Función para enviar el formulario
const submit = () => {
    form.put(route('categorias.update', props.categoria.id), {
        onSuccess: () => {
            alert('Categoría actualizada correctamente.');
        },
        onError: (errors) => {
            console.error('Error al actualizar:', errors);
        },
    });
};
</script>
