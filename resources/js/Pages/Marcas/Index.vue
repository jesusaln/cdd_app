<template>
    <Head title="Marcas" />
    <div class="p-6 space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Marcas</h1>
                <p class="text-gray-600 mt-1">Gestiona todas las marcas de productos</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Buscar marcas..."
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 w-64"
                        aria-label="Buscar marcas"
                        @input="filtrarMarcas"
                    />
                </div>
                <Link
                    :href="route('marcas.create')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-medium rounded-lg hover:from-teal-700 hover:to-teal-800 focus:ring-4 focus:ring-teal-300 transition-all duration-200 shadow-lg hover:shadow-xl"
                    aria-label="Crear nueva marca"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Nueva Marca
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-teal-100 text-sm font-medium">Total Marcas</p>
                        <p class="text-3xl font-bold">{{ marcas.length }}</p>
                    </div>
                    <div class="bg-teal-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-building text-2xl"></i> <!-- Icono para marcas -->
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Con Descripción</p>
                        <p class="text-3xl font-bold">{{ marcasConDescripcion }}</p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-align-left text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Promedio Descripción</p>
                        <p class="text-3xl font-bold">{{ longitudPromedioDescripcion }} car.</p>
                    </div>
                    <div class="bg-yellow-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-ruler-horizontal text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Última Creada</p>
                        <p class="text-3xl font-bold text-ellipsis overflow-hidden whitespace-nowrap max-w-[150px]">{{ ultimaMarcaCreada.nombre || 'N/A' }}</p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-calendar-plus text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section (simplified for Brands) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-wrap items-center gap-4">
                <button
                    @click="limpiarFiltros"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200"
                    aria-label="Limpiar filtros"
                >
                    <i class="fas fa-times mr-2"></i>
                    Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Brands Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="marcasFiltradas.length > 0">
                <!-- Table Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Lista de Marcas</h3>
                        <span class="text-sm text-gray-500">{{ marcasFiltradas.length }} marcas encontradas</span>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Marca
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="marca in marcasFiltradas" :key="marca.id" class="hover:bg-gray-100 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-teal-500 to-purple-600 flex items-center justify-center">
                                                <i class="fas fa-building text-white text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ marca.nombre }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-normal max-w-xs overflow-hidden text-ellipsis">
                                    <div class="text-sm text-gray-700">{{ marca.descripcion || 'Sin descripción' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-4 acciones-container">
                                        <!-- Botón Editar -->
                                        <Link
                                            :href="route('marcas.edit', marca.id)"
                                            class="inline-flex items-center px-3 py-1.5 bg-teal-100 text-teal-700 text-xs font-medium rounded-md hover:bg-teal-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition duration-200"
                                            aria-label="Editar marca"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Editar
                                        </Link>
                                        <!-- Botón Eliminar -->
                                        <button
                                            @click="confirmarEliminacion(marca)"
                                            :disabled="loading"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                            aria-label="Eliminar marca"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            <span v-if="!loading">Eliminar</span>
                                            <span v-else>Eliminando...</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                    <i class="fas fa-building text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay marcas</h3>
                <p class="text-gray-500 mb-6">
                    {{ searchTerm ? 'No se encontraron marcas que coincidan con tu búsqueda.' : 'Comienza creando tu primera marca.' }}
                </p>
                <Link
                    :href="route('marcas.create')"
                    class="inline-flex items-center px-4 py-2 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 transition-colors duration-200"
                    aria-label="Crear primera marca"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primera Marca
                </Link>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <Transition name="modal">
            <div v-if="isConfirmOpen" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cancelarEliminacion"></div>

                    <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Confirmar eliminación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Estás seguro de que deseas eliminar la marca <strong>{{ marcaAEliminar?.nombre }}</strong>?
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="eliminarMarcaConfirmado"
                                :disabled="loading"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                aria-label="Confirmar eliminación"
                            >
                                <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                                {{ loading ? 'Eliminando...' : 'Eliminar' }}
                            </button>
                            <button
                                @click="cancelarEliminacion"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:mt-0 sm:w-auto sm:text-sm"
                                aria-label="Cancelar eliminación"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    marcas: Array
});

// Reactive data
const loading = ref(false);
const searchTerm = ref('');
const isConfirmOpen = ref(false);
const marcaAEliminar = ref(null); // Changed from idMarcaAEliminar
const marcas = ref(props.marcas); // Make the prop reactive

// Computed properties
const marcasFiltradas = computed(() => {
    let filtered = marcas.value;

    // Filtro por búsqueda
    if (searchTerm.value) {
        filtered = filtered.filter(marca => {
            return marca.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                   (marca.descripcion && marca.descripcion.toLowerCase().includes(searchTerm.value.toLowerCase()));
        });
    }
    return filtered;
});

const marcasConDescripcion = computed(() => {
    return marcas.value.filter(marca => marca.descripcion && marca.descripcion.trim() !== '').length;
});

const longitudPromedioDescripcion = computed(() => {
    const marcasConDesc = marcas.value.filter(marca => marca.descripcion && marca.descripcion.trim() !== '');
    if (marcasConDesc.length === 0) return '0';
    const totalLength = marcasConDesc.reduce((sum, marca) => sum + marca.descripcion.length, 0);
    return Math.round(totalLength / marcasConDesc.length);
});

const ultimaMarcaCreada = computed(() => {
    if (marcas.value.length === 0) return {};
    // Assuming 'created_at' exists and is sortable, otherwise, the last item in the array is used
    // If you have a 'created_at' field:
    // return [...marcas.value].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];
    return marcas.value[marcas.value.length - 1]; // Fallback
});

// Notification setup
const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: 'linear-gradient(135deg, #10b981, #059669)',
            icon: {
                className: 'fas fa-check',
                tagName: 'i',
                color: 'white'
            }
        },
        {
            type: 'error',
            background: 'linear-gradient(135deg, #ef4444, #dc2626)',
            icon: {
                className: 'fas fa-exclamation-triangle',
                tagName: 'i',
                color: 'white'
            }
        }
    ]
});

const page = usePage();

// Watch for flash messages
watchEffect(() => {
    if (page.props.flash?.success) {
        notyf.success(page.props.flash.success);
    }
    if (page.props.flash?.error) {
        notyf.error(page.props.flash.error);
    }
});

// Methods
const confirmarEliminacion = (marca) => {
    marcaAEliminar.value = marca; // Store the entire brand object
    isConfirmOpen.value = true;
};

const cancelarEliminacion = () => {
    isConfirmOpen.value = false;
    marcaAEliminar.value = null;
};

const eliminarMarcaConfirmado = async () => {
    if (!marcaAEliminar.value) return;

    loading.value = true;
    try {
        await router.delete(route('marcas.destroy', marcaAEliminar.value.id), {
            onSuccess: () => {
                notyf.success('Marca eliminada exitosamente.');
                // Update the reactive array after successful deletion
                marcas.value = marcas.value.filter(m => m.id !== marcaAEliminar.value.id);
                isConfirmOpen.value = false;
                marcaAEliminar.value = null;
            },
            onError: (errors) => {
                notyf.error(errors.error || 'Error al eliminar la marca.');
            }
        });
    } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
    } finally {
        loading.value = false;
    }
};

const filtrarMarcas = () => {
    // The search is automatically applied via the 'marcasFiltradas' computed property
};

const limpiarFiltros = () => {
    searchTerm.value = '';
    // Add other filters here if they were implemented for brands
};
</script>

<style scoped>
/* Estilos para tooltips personalizados */
[data-tooltip] {
    position: relative;
}
[data-tooltip]:hover:after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    right: 0;
    background: #1f2937;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 10;
    transform: translateY(-4px);
    transition: all 0.2s ease;
}

/* Estilos para transiciones de la modal */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
    transition: all 0.3s ease;
}
.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
    transform: scale(0.95);
}

/* Diseño responsivo para la columna de acciones */
@media (max-width: 640px) {
    .acciones-container {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Mejora en la tabla para mayor legibilidad */
tbody tr:hover {
    background-color: #f9fafb;
}

/* Asegurar que los botones sean más visibles */
button:focus,
a:focus {
    outline: none;
}
</style>
