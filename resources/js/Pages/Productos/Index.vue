<template>
    <Head title="Productos" />
    <div class="p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Productos</h1>
                <p class="text-gray-600 mt-1">Gestiona todos los productos disponibles en tu inventario</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Buscar productos..."
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 w-64"
                        aria-label="Buscar productos"
                        @input="aplicarFiltros"
                    />
                </div>
                <Link
                    :href="route('productos.create')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl"
                    aria-label="Crear nuevo producto"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Nuevo Producto
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Productos</p>
                        <p class="text-3xl font-bold">{{ productos.length }}</p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-box-open text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">En Stock</p>
                        <p class="text-3xl font-bold">{{ productosEnStock }}</p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-boxes text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Precio Promedio</p>
                        <p class="text-3xl font-bold">${{ precioPromedio }}</p>
                    </div>
                    <div class="bg-yellow-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-dollar-sign text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Unidades Vendidas</p>
                        <p class="text-3xl font-bold">{{ unidadesVendidasTotal }}</p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-lg p-3">
                        <i class="fas fa-truck-ramp-box text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label for="filtro-estado" class="text-sm font-medium text-gray-700">Estado:</label>
                    <select
                        id="filtro-estado"
                        v-model="filtroEstado"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        aria-label="Filtrar por estado"
                        @change="aplicarFiltros"
                    >
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="agotado">Agotado</option>
                        <option value="descontinuado">Descontinuado</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label for="filtro-precio" class="text-sm font-medium text-gray-700">Precio:</label>
                    <select
                        id="filtro-precio"
                        v-model="filtroPrecio"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        aria-label="Filtrar por precio"
                        @change="aplicarFiltros"
                    >
                        <option value="">Todos</option>
                        <option value="bajo">Hasta $50</option>
                        <option value="medio">$50 - $200</option>
                        <option value="alto">Más de $200</option>
                    </select>
                </div>

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
            <div v-if="productosFiltrados.length > 0">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Lista de Productos</h3>
                        <span class="text-sm text-gray-500">{{ productosFiltrados.length }} productos encontrados</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Producto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Precio
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="producto in productosFiltrados" :key="producto.id" class="hover:bg-gray-100 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                                <i class="fas fa-box text-white text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ producto.nombre }}</div>
                                            <div class="text-sm text-gray-500">SKU: {{ producto.codigo_barras || 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${{ formatearPrecio(producto.precio_venta) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-900">
                                        <i class="fas fa-warehouse text-gray-400 mr-2"></i>
                                        {{ producto.stock || 0 }} unidades
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getEstadoClases(producto.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        <span :class="getEstadoPunto(producto.estado)" class="w-1.5 h-1.5 rounded-full mr-1.5"></span>
                                        {{ producto.estado || 'Desconocido' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-4 acciones-container">
                                        <button
                                            @click="openModal(producto)"
                                            class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Ver
                                        </button>
                                        <Link
                                            :href="route('productos.edit', producto.id)"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Editar
                                        </Link>
                                        <button
                                            @click="confirmarEliminacion(producto)"
                                            :disabled="loading"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 2 0 00-1 1v3M4 7h16"></path>
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
                    <i class="fas fa-box-open text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay productos</h3>
                <p class="text-gray-500 mb-6">
                    {{ searchTerm || filtroEstado || filtroPrecio ? 'No se encontraron productos que coincidan con tu búsqueda o filtros.' : 'Comienza creando tu primer producto para empezar a gestionarlos.' }}
                </p>
                <Link
                    :href="route('productos.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200"
                    aria-label="Crear primer producto"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primer Producto
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
                                        ¿Estás seguro de que deseas eliminar el producto <strong>{{ productoAEliminar?.nombre }}</strong>?
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                @click="eliminarProductoConfirmado"
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

        <ProductoModal
            v-if="isModalOpen"
            :producto="selectedProducto"
            :isOpen="isModalOpen"
            @close="closeModal"
        />
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import ProductoModal from '@/Components/ProductoModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    productos: {
        type: Array,
        default: () => []
    }
});

// Reactive data
const loading = ref(false);
const searchTerm = ref('');
const filtroEstado = ref('');
const filtroPrecio = ref('');
const selectedProducto = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const productoAEliminar = ref(null);

// Use a ref for `productos` so it can be updated after deletion
const productos = ref(props.productos);

// Computed properties
const productosFiltrados = computed(() => {
    let filtered = productos.value;

    // Filtro por búsqueda (nombre o SKU)
    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(producto => {
            return (producto.nombre && producto.nombre.toLowerCase().includes(term)) ||
                   (producto.sku && producto.sku.toLowerCase().includes(term));
        });
    }

    // Filtro por estado
    if (filtroEstado.value) {
        filtered = filtered.filter(producto => producto.estado && producto.estado.toLowerCase() === filtroEstado.value);
    }

    // Filtro por precio
    if (filtroPrecio.value) {
        filtered = filtered.filter(producto => {
            const precio = parseFloat(producto.precio || 0); // Asegura que el precio sea un número
            switch (filtroPrecio.value) {
                case 'bajo': return precio <= 50;
                case 'medio': return precio > 50 && precio <= 200;
                case 'alto': return precio > 200;
                default: return true;
            }
        });
    }

    return filtered;
});

const productosEnStock = computed(() => {
    return productos.value.filter(p => (p.cantidad_disponible && p.cantidad_disponible > 0)).length;
});

const precioPromedio = computed(() => {
    if (productos.value.length === 0) return '0.00';
    const total = productos.value.reduce((sum, p) => sum + parseFloat(p.precio || 0), 0);
    return (total / productos.value.length).toFixed(2);
});

const unidadesVendidasTotal = computed(() => {
    if (productos.value.length === 0) return '0';
    const total = productos.value.reduce((sum, p) => sum + parseInt(p.unidades_vendidas || 0), 0);
    return total;
});

// Notyf Notification setup
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

// Watch for flash messages from Inertia
watchEffect(() => {
    if (page.props.flash?.success) {
        notyf.success(page.props.flash.success);
    }
    if (page.props.flash?.error) {
        notyf.error(page.props.flash.error);
    }
});

// Helper methods
const formatearPrecio = (precio) => {
    const numericPrecio = parseFloat(precio);
    if (isNaN(numericPrecio)) {
        return '0.00'; // Retorna 0.00 si el precio no es un número válido
    }
    return numericPrecio.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getEstadoClases = (estado) => {
    switch (estado?.toLowerCase()) {
        case 'disponible': return 'bg-green-100 text-green-800';
        case 'agotado': return 'bg-yellow-100 text-yellow-800';
        case 'descontinuado': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getEstadoPunto = (estado) => {
    switch (estado?.toLowerCase()) {
        case 'disponible': return 'bg-green-400';
        case 'agotado': return 'bg-yellow-400';
        case 'descontinuado': return 'bg-red-400';
        default: return 'bg-gray-400';
    }
};

// Modals and Deletion Logic
const openModal = (producto) => {
    selectedProducto.value = producto;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedProducto.value = null;
};

const confirmarEliminacion = (producto) => {
    productoAEliminar.value = producto;
    isConfirmOpen.value = true;
};

const cancelarEliminacion = () => {
    isConfirmOpen.value = false;
    productoAEliminar.value = null;
};

const eliminarProductoConfirmado = async () => {
    if (!productoAEliminar.value) return;

    loading.value = true;
    try {
        await router.delete(route('productos.destroy', productoAEliminar.value.id), {
            onSuccess: () => {
                notyf.success('Producto eliminado exitosamente.');
                // Actualiza la lista de productos reactivamente
                productos.value = productos.value.filter(p => p.id !== productoAEliminar.value.id);
                isConfirmOpen.value = false;
                productoAEliminar.value = null;
            },
            onError: (errors) => {
                console.error('Errors:', errors);
                notyf.error(errors.error || 'Error al eliminar el producto.');
            },
            onFinish: () => {
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Unexpected error:', error);
        notyf.error('Ocurrió un error inesperado.');
    } finally {
        loading.value = false;
    }
};

// Filter/Search Handlers (computed properties handle the actual filtering)
const aplicarFiltros = () => {
    // Las propiedades computadas (productosFiltrados) se encargarán de la lógica de filtrado
    // automáticamente cuando searchTerm, filtroEstado o filtroPrecio cambien.
};

const limpiarFiltros = () => {
    searchTerm.value = '';
    filtroEstado.value = '';
    filtroPrecio.value = '';
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
