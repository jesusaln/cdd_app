<template>
    <Head title="Editar Marca" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-4">Editar Marca</h1>
        <!-- Formulario de edición de marcas -->
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
                <Link :href="route('marcas.index')" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition duration-300">
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
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
// Define el layout del dashboard (si es necesario)
import Dashboard from '@/Pages/Dashboard.vue';
defineOptions({ layout: Dashboard });

// Recibe los datos de la marca como prop
const props = defineProps({
    marca: Object,
});

// Inicializa el formulario con los datos de la marca
const form = useForm({
    nombre: props.marca.nombre,
    descripcion: props.marca.descripcion || '',
});

// Función para enviar el formulario
const submit = () => {
    form.put(route('marcas.update', props.marca.id), {
        onSuccess: () => {
            alert('Marca actualizada correctamente.');
        },
        onError: (errors) => {
            console.error('Error al actualizar:', errors);
        },
    });
};
</script>
