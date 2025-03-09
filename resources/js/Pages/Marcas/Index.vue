<template>
    <Head title="Marcas" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Marcas</h1>

        <!-- Botón para crear una nueva marca -->
        <div class="mb-4">
            <Link :href="route('marcas.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Crear Marca
            </Link>
        </div>

        <!-- Tabla de marcas -->
        <div v-if="marcas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="marca in marcas" :key="marca.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ marca.nombre }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ marca.descripcion || 'Sin descripción' }}</td>
                        <td class="px-4 py-3 flex space-x-2">
                            <Link :href="route('marcas.edit', marca.id)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition duration-300">
                                Editar
                            </Link>
                            <button @click="abrirModal(marca.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-300">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay marcas -->
        <div v-else class="text-center text-gray-500 mt-4">
            No hay marcas registradas.
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div v-if="mostrarModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h3 class="text-xl font-semibold mb-4">¿Estás seguro de eliminar esta marca?</h3>
                <p class="mb-4">Esta acción no se puede deshacer.</p>
                <div class="flex justify-end space-x-4">
                    <button @click="cerrarModal" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button @click="eliminarMarca" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe las marcas como prop
defineProps({ marcas: Array });

// Configuración personalizada de Notyf
const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

// Estado reactivo para manejar la visibilidad del modal y el id de la marca a eliminar
const mostrarModal = ref(false);
const idMarcaAEliminar = ref(null);

// Función para abrir el modal de confirmación
const abrirModal = (id) => {
    idMarcaAEliminar.value = id;
    mostrarModal.value = true;
};

// Función para cerrar el modal
const cerrarModal = () => {
    mostrarModal.value = false;
    idMarcaAEliminar.value = null;
};

// Función para eliminar la marca
const eliminarMarca = async () => {
    try {
        await router.delete(route('marcas.destroy', idMarcaAEliminar.value), {
            onSuccess: () => {
                notyf.success('La marca ha sido eliminada exitosamente.');
                cerrarModal(); // Cierra el modal después de la eliminación exitosa
            },
            onError: (error) => {
                console.error('Error al eliminar la marca:', error);
                notyf.error('Hubo un error al eliminar la marca.');
                cerrarModal(); // Cierra el modal en caso de error
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
        cerrarModal();
    }
};
</script>
