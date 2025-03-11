<template>
    <Head title="Almacenes" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Almacenes</h1>

        <!-- Botón para crear un nuevo almacén -->
        <div class="mb-4">
            <Link
                :href="route('almacenes.create')"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300"
            >
                Crear Almacén
            </Link>
        </div>

        <!-- Tabla de almacenes -->
        <div v-if="almacenes.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="almacen in almacenes" :key="almacen.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ almacen.nombre }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ almacen.descripcion || 'Sin descripción' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ almacen.ubicacion }}</td>
                        <td class="px-4 py-3 flex space-x-2">
                            <!-- Botón para editar -->
                            <Link
                                :href="route('almacenes.edit', almacen.id)"
                                class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition duration-300"
                            >
                                Editar
                            </Link>
                            <!-- Botón para eliminar -->
                            <button
                                @click="abrirModalEliminar(almacen.id)"
                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-300"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay almacenes -->
        <div v-else class="text-center text-gray-500 mt-4">
            No hay almacenes registrados.
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div v-if="modalVisible" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center z-50">
            <div class="bg-white rounded-lg p-6 w-96">
                <h3 class="text-lg font-semibold text-gray-800">Confirmar eliminación</h3>
                <p class="text-sm text-gray-600 mt-2">¿Estás seguro de que deseas eliminar este almacén? Esta acción no se puede deshacer.</p>
                <div class="mt-4 flex justify-end space-x-4">
                    <button @click="cerrarModal" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
                    <button @click="eliminarAlmacen" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe los almacenes como prop
defineProps({
    almacenes: Array,
});

// Obtiene los mensajes de la sesión de Inertia
const page = usePage();

// Configuración personalizada de Notyf
const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

// Estado para el modal y el almacén a eliminar
const modalVisible = ref(false);
const almacenAEliminar = ref(null);

// Función para abrir el modal
const abrirModalEliminar = (id) => {
    almacenAEliminar.value = id;
    modalVisible.value = true;
};

// Función para cerrar el modal
const cerrarModal = () => {
    modalVisible.value = false;
    almacenAEliminar.value = null;
};

// Escuchar cambios en los mensajes de sesión y mostrar notificaciones
watchEffect(() => {
    if (page.props.flash?.success) {
        notyf.success(page.props.flash.success);
    }
    if (page.props.flash?.error) {
        notyf.error(page.props.flash.error);
    }
});


// Función para eliminar el almacén
const eliminarAlmacen = async () => {
    if (!almacenAEliminar.value) return;

    try {
        await router.delete(route('almacenes.destroy', almacenAEliminar.value), {
            preserveState: true,
            onSuccess: () => {
                notyf.success('El almacén ha sido eliminado exitosamente.');
                cerrarModal();
            },
            onError: (errors) => {
                console.log('Errores:', errors); // Debug en la consola
                if (errors.error) {
                    notyf.error(errors.error);
                } else {
                    notyf.error('Hubo un error al eliminar el almacén.');
                }
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    }
};

</script>


<style scoped>
/* Estilos adicionales si es necesario */
</style>
