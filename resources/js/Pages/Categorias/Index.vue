<template>
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Categorías</h1>

        <!-- Botón para crear una nueva categoría -->
        <div class="mb-4">
            <Link :href="route('categorias.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Crear Categoría
            </Link>
        </div>

        <!-- Tabla de categorías -->
        <div v-if="categorias.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="categoria in categorias" :key="categoria.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ categoria.nombre }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ categoria.descripcion || 'Sin descripción' }}</td>
                        <td class="px-4 py-3 flex space-x-2">
                            <Link :href="route('categorias.edit', categoria.id)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition duration-300">
                                Editar
                            </Link>
                            <button @click="confirmarEliminacion(categoria)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-300">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay categorías -->
        <div v-else class="text-center text-gray-500 mt-4">
            No hay categorías registradas.
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div v-if="isConfirmOpen" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
                <p class="mb-4">¿Estás seguro de que deseas eliminar esta Categoria?</p>
                <div class="mt-4 flex justify-end space-x-4">
                    <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button @click="eliminarCategoriaConfirmado" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout del dashboard
defineOptions({ layout: Dashboard });

// Recibe las categorías como prop
defineProps({ categorias: Array });

// Configuración personalizada de Notyf
const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const isConfirmOpen = ref(false);
const categoriaAEliminar = ref(null);

// Función para abrir el modal de confirmación
const confirmarEliminacion = (categoria) => {
    categoriaAEliminar.value = categoria;
    isConfirmOpen.value = true;
};

// Función para cancelar la eliminación
const cancelarEliminacion = () => {
    isConfirmOpen.value = false;
    categoriaAEliminar.value = null;
};

// Función para eliminar la categoría confirmada
const eliminarCategoriaConfirmado = async () => {
    if (!categoriaAEliminar.value) return;

    try {
        await router.delete(route('categorias.destroy', categoriaAEliminar.value.id), {
            onSuccess: () => {
                notyf.success('La categoría ha sido eliminada exitosamente.');
                // Filtrar la categoría eliminada de la lista
                categorias.value = categorias.value.filter(c => c.id !== categoriaAEliminar.value.id);
            },
            onError: (error) => {
                console.error('Error al eliminar la categoría:', error);
                notyf.error('Hubo un error al eliminar la categoría.');
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    } finally {
        isConfirmOpen.value = false;
    }
};
</script>
