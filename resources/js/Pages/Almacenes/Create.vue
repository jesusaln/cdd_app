<template>
    <Head title="Crear Almacén" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Crear Almacén</h1>

        <!-- Formulario -->
        <form @submit.prevent="crearAlmacen" class="space-y-4">
            <!-- Campo Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    v-model="form.nombre"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                />
                <p v-if="errors.nombre" class="text-red-500 text-sm">{{ errors.nombre }}</p>
            </div>

            <!-- Campo Descripción -->
            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea
                    id="descripcion"
                    v-model="form.descripcion"
                    rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                ></textarea>
                <p v-if="errors.descripcion" class="text-red-500 text-sm">{{ errors.descripcion }}</p>
            </div>

            <!-- Campo Ubicación -->
            <div>
                <label for="ubicacion" class="block text-sm font-medium text-gray-700">Ubicación</label>
                <input
                    type="text"
                    id="ubicacion"
                    v-model="form.ubicacion"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                />
                <p v-if="errors.ubicacion" class="text-red-500 text-sm">{{ errors.ubicacion }}</p>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <!-- Botón Cancelar -->
                <Link
                    :href="route('almacenes.index')"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition duration-300"
                >
                    Cancelar
                </Link>
                <!-- Botón Guardar -->
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300"
                    :disabled="form.processing"
                >
                    Guardar
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout del dashboard
defineOptions({ layout: Dashboard });

// Recibe los errores del backend
const props = defineProps({
    errors: Object,
});

// Datos del formulario
const form = reactive({
    nombre: '',
    descripcion: '',
    ubicacion: '',
});

// Función para enviar el formulario
const crearAlmacen = () => {
    router.post(route('almacenes.store'), form);
};
</script>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
