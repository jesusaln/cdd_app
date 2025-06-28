<template>
    <Head title="Almacenes" />
    <div class="p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Almacenes</h1>
                <p class="text-gray-600 mt-1">Gestiona todos los almacenes de productos</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Buscar almacenes..."
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 w-64"
                        aria-label="Buscar almacenes"
                        @input="filtrarAlmacenes"
                    />
                </div>
                <Link
                    :href="route('almacenes.create')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl"
                    aria-label="Crear nuevo almacén"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Nuevo Almacén
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Almacenes</p>
                        <p class="text-3xl font-bold">{{ almacenes.length }}</p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-warehouse text-2xl"></i> </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Con Descripción</p>
                        <p class="text-3xl font-bold">{{ almacenesConDescripcion }}</p>
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
                        <p class="text-purple-100 text-sm font-medium">Último Creado</p>
                        <p class="text-3xl font-bold text-ellipsis overflow-hidden whitespace-nowrap max-w-[150px]">{{ ultimoAlmacenCreado.nombre || 'N/A' }}</p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-calendar-plus text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

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

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="almacenesFiltrados.length > 0">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Lista de Almacenes</h3>
                        <span class="text-sm text-gray-500">{{ almacenesFiltrados.length }} almacenes encontrados</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Almacén
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ubicación
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="almacen in almacenesFiltrados" :key="almacen.id" class="hover:bg-gray-100 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                                <i class="fas fa-warehouse text-white text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ almacen.nombre }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-normal max-w-xs overflow-hidden text-ellipsis">
                                    <div class="text-sm text-gray-700">{{ almacen.descripcion || 'Sin descripción' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">{{ almacen.ubicacion }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-4 acciones-container">
                                        <Link
                                            :href="route('almacenes.edit', almacen.id)"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200"
                                            aria-label="Editar almacén"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Editar
                                        </Link>
                                        <button
                                            @click="confirmarEliminacion(almacen)"
                                            :disabled="loading"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                            aria-label="Eliminar almacén"
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

            <div v-else class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                    <i class="fas fa-warehouse text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay almacenes</h3>
                <p class="text-gray-500 mb-6">
                    {{ searchTerm ? 'No se encontraron almacenes que coincidan con tu búsqueda.' : 'Comienza creando tu primer almacén.' }}
                </p>
                <Link
                    :href="route('almacenes.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200"
                    aria-label="Crear primer almacén"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primer Almacén
                </Link>
            </div>
        </div>

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
                                        ¿Estás seguro de que deseas eliminar el almacén <strong>{{ almacenAEliminar?.nombre }}</strong>?
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="eliminarAlmacenConfirmado"
                                :disabled="loading"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                aria-label="Confirmar eliminación"
                            >
                                <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                                {{ loading ? 'Eliminando...' : 'Eliminar' }}
                            </button>
                            <button
                                @click="cancelarEliminacion"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
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

// Recibe los almacenes como prop y lo hace reactivo
const props = defineProps({
    almacenes: Array
});

const loading = ref(false);
const searchTerm = ref('');
const isConfirmOpen = ref(false);
const almacenAEliminar = ref(null); // Almacena el objeto completo del almacén

// Usa una referencia reactiva para la lista de almacenes para poder actualizarla localmente
const almacenes = ref(props.almacenes);

// Computed properties
const almacenesFiltrados = computed(() => {
    let filtered = almacenes.value;

    // Filtro por búsqueda
    if (searchTerm.value) {
        filtered = filtered.filter(almacen => {
            return almacen.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                   (almacen.descripcion && almacen.descripcion.toLowerCase().includes(searchTerm.value.toLowerCase())) ||
                   (almacen.ubicacion && almacen.ubicacion.toLowerCase().includes(searchTerm.value.toLowerCase()));
        });
    }
    return filtered;
});

const almacenesConDescripcion = computed(() => {
    return almacenes.value.filter(almacen => almacen.descripcion && almacen.descripcion.trim() !== '').length;
});

const longitudPromedioDescripcion = computed(() => {
    const almacenesConDesc = almacenes.value.filter(almacen => almacen.descripcion && almacen.descripcion.trim() !== '');
    if (almacenesConDesc.length === 0) return '0';
    const totalLength = almacenesConDesc.reduce((sum, almacen) => sum + almacen.descripcion.length, 0);
    return Math.round(totalLength / almacenesConDesc.length);
});

const ultimoAlmacenCreado = computed(() => {
    if (almacenes.value.length === 0) return {};
    // Assuming 'created_at' exists and is sortable, otherwise, the last item in the array is used
    // If you have a 'created_at' field:
    // return [...almacenes.value].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];
    return almacenes.value[almacenes.value.length - 1]; // Fallback if no created_at
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
const confirmarEliminacion = (almacen) => {
    almacenAEliminar.value = almacen; // Store the entire warehouse object
    isConfirmOpen.value = true;
};

const cancelarEliminacion = () => {
    isConfirmOpen.value = false;
    almacenAEliminar.value = null;
};

const eliminarAlmacenConfirmado = async () => {
    if (!almacenAEliminar.value) return;

    loading.value = true;
    try {
        await router.delete(route('almacenes.destroy', almacenAEliminar.value.id), {
            onSuccess: () => {
                notyf.success('Almacén eliminado exitosamente.');
                // Update the reactive array after successful deletion
                almacenes.value = almacenes.value.filter(a => a.id !== almacenAEliminar.value.id);
                isConfirmOpen.value = false;
                almacenAEliminar.value = null;
            },
            onError: (errors) => {
                notyf.error(errors.error || 'Error al eliminar el almacén.');
            }
        });
    } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
    } finally {
        loading.value = false;
    }
};

const filtrarAlmacenes = () => {
    // The search is automatically applied via the 'almacenesFiltrados' computed property
};

const limpiarFiltros = () => {
    searchTerm.value = '';
    // Add other filters here if they were implemented for warehouses
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
