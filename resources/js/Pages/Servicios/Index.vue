<template>
    <Head title="Servicios" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Registro de Servicios</h1>

        <!-- Botón para crear un nuevo servicio -->
        <div class="mb-4 flex justify-between items-center">
            <Link :href="route('servicios.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Crear Servicio
            </Link>

            <input
                v-model="searchTerm"
                type="text"
                placeholder="Buscar por nombre o código"
                class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                @input="filtrarServicios"
            />
        </div>

        <!-- Tabla de servicios -->
        <div v-if="serviciosFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th v-for="header in headers" :key="header" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            {{ header }}
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="servicio in serviciosFiltrados" :key="servicio.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ servicio.codigo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ servicio.nombre }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ servicio.precio }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ servicio.duracion }} min</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ servicio.estado }}</td>
                        <td class="px-4 py-3 flex space-x-2">
                            <!-- Botón para mostrar el servicio -->
                            <button @click="openModal(servicio)" class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-green-600 transition duration-300">
                                <i class="fas fa-eye"></i>
                                <span>Ver</span>
                            </button>

                            <!-- Botón para editar el servicio -->
                            <Link :href="route('servicios.edit', servicio.id)" class="flex items-center space-x-2 bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-blue-600 transition duration-300">
                                <i class="fas fa-edit"></i>
                                <span>Editar</span>
                            </Link>

                            <!-- Botón para eliminar el servicio -->
                            <button @click="confirmarEliminacion(servicio)" :disabled="loading" class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-red-600 transition duration-300">
                                <i class="fas fa-trash-alt"></i>
                                <span v-if="!loading">Eliminar</span>
                                <span v-else>Eliminando...</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay servicios -->
        <div v-else class="text-center text-gray-500 mt-4">
            No hay servicios registrados.
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div v-if="isConfirmOpen" class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
                <p class="mb-4">¿Estás seguro de que deseas eliminar este servicio?</p>
                <div class="flex space-x-4">
                    <button @click="eliminarServicioConfirmado" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Sí, eliminar
                    </button>
                    <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal para mostrar los detalles del servicio -->
        <ServicioModal v-if="isModalOpen" :servicio="selectedServicio" :isOpen="isModalOpen" @close="closeModal" />
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import ServicioModal from '@/Components/Modal/ServicioModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({ servicios: Array });
const headers = ['Código', 'Nombre', 'Precio', 'Duración', 'Estado'];
const loading = ref(false);
const searchTerm = ref('');
const selectedServicio = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const servicioAEliminar = ref(null);

const servicios = ref(props.servicios);
const serviciosFiltrados = computed(() => {
    return servicios.value.filter(servicio => {
        return servicio.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
               servicio.codigo.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
});

const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

const page = usePage();

watchEffect(() => {
    if (page.props.flash?.success) {
        console.log('Mensaje de éxito:', page.props.flash.success);
        notyf.success(page.props.flash.success);
    }
    if (page.props.flash?.error) {
        console.log('Mensaje de error:', page.props.flash.error);
        notyf.error(page.props.flash.error);
    }
});

const openModal = (servicio) => {
    selectedServicio.value = servicio;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const confirmarEliminacion = (servicio) => {
    servicioAEliminar.value = servicio;
    isConfirmOpen.value = true;
};

const cancelarEliminacion = () => {
    isConfirmOpen.value = false;
    servicioAEliminar.value = null;
};

const eliminarServicioConfirmado = async () => {
    if (!servicioAEliminar.value) return;

    loading.value = true;
    try {
        await router.delete(route('servicios.destroy', servicioAEliminar.value.id), {
            onSuccess: () => {
                notyf.success('Servicio eliminado exitosamente.');
                servicios.value = servicios.value.filter(s => s.id !== servicioAEliminar.value.id);
            },
            onError: () => notyf.error('Error al eliminar el servicio.')
        });
    } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
    } finally {
        isConfirmOpen.value = false;
        loading.value = false;
    }
};
</script>
