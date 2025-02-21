<template>

<Head title="Productos" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Registro de Productos</h1>

        <!-- Botón para crear un nuevo producto -->
        <div class="mb-4 flex justify-between items-center">
            <Link :href="route('productos.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Crear Producto
            </Link>

            <input
                v-model="searchTerm"
                type="text"
                placeholder="Buscar por nombre o código"
                class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                @input="filtrarProductos"
            />
        </div>

        <!-- Tabla de productos -->
        <div v-if="productosFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
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
                    <tr v-for="producto in productosFiltrados" :key="producto.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ producto.codigo }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ producto.nombre }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ producto.precio_venta }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ producto.stock }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ producto.estado }}
                        </td>
                        <td class="px-4 py-3 flex space-x-2">
                            <!-- Botón para mostrar el producto -->
                            <button
                                @click="openModal(producto)"
                                class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-green-600 transition duration-300">
                                <i class="fas fa-eye"></i>
                                <span>Ver</span>
                            </button>

                            <!-- Botón para editar el producto -->
                            <Link :href="route('productos.edit', producto.id)" class="flex items-center space-x-2 bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-blue-600 transition duration-300">
                                <i class="fas fa-edit"></i>
                                <span>Editar</span>
                            </Link>

                            <!-- Botón para eliminar el producto -->
                            <button
                                @click="confirmarEliminacion(producto)"
                                :disabled="loading"
                                class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-red-600 transition duration-300"
                            >
                                <i class="fas fa-trash-alt"></i>
                                <span v-if="!loading">Eliminar</span>
                                <span v-else>Eliminando...</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay productos -->
        <div v-else class="text-center text-gray-500 mt-4">
            No hay productos registrados.
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div v-if="isConfirmOpen" class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
                <p class="mb-4">¿Estás seguro de que deseas eliminar este producto?</p>
                <div class="flex space-x-4">
                    <button @click="eliminarProductoConfirmado" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Sí, eliminar
                    </button>
                    <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal para mostrar los detalles del producto -->
        <ProductoModal
            v-if="isModalOpen"
            :producto="selectedProducto"
            :isOpen="isModalOpen"
            @close="closeModal"
        />
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import Dashboard from '@/Pages/Dashboard.vue';
import ProductoModal from '@/Components/ProductoModal.vue';

defineOptions({ layout: Dashboard });

const props = defineProps({ productos: Array });
const headers = ['Código', 'Nombre', 'Precio de Venta', 'Stock', 'Estado'];
const loading = ref(false);
const searchTerm = ref('');
const selectedProducto = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const productoAEliminar = ref(null);

const productos = ref(props.productos);
const productosFiltrados = computed(() => {
    return productos.value.filter(producto => {
        return producto.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
               producto.codigo.toLowerCase().includes(searchTerm.value.toLowerCase());
    });
});

const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

const openModal = (producto) => {
    selectedProducto.value = producto;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
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
                productos.value = productos.value.filter(p => p.id !== productoAEliminar.value.id);
            },
            onError: () => notyf.error('Error al eliminar el producto.')
        });
    } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
    } finally {
        isConfirmOpen.value = false;
        loading.value = false;
    }
};
</script>
