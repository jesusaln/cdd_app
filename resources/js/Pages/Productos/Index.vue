<template>
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

                        <!-- <td class="px-4 py-3 text-sm text-gray-700">
                            {{ producto.categoria.nombre }}
                        </td> -->
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
                            <Link :href="route('productos.edit', producto.id)" class="flex items-center space-x-2 bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-blue-600 transition duration-300">
                                <i class="fas fa-edit"></i>
                                <span>Editar</span>
                            </Link>
                            <button @click="eliminarProducto(producto.id)" class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-red-600 transition duration-300">
                                <i class="fas fa-trash-alt"></i>
                                <span>Eliminar</span>
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

        <!-- Spinner de carga
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        Componente de confirmación
        <ConfirmDialog ref="confirmDialog" /> -->
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css'; // Importa los estilos de Notyf
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard
//import ConfirmDialog from '@/components/ConfirmDialog.vue'; // Importa el componente

// Define el layout del dashboard
defineOptions({ layout: Dashboard });

// Recibe los productos como prop
const props = defineProps({ productos: Array });

// Encabezados de la tabla
const headers = [
'Código',
'Nombre',

    //'Categoría',
    'Precio de Venta',
    'Stock',
    'Estado'
];

// Estado para el spinner de carga
const loading = ref(false);

// Término de búsqueda
const searchTerm = ref('');

// Productos filtrados
const productosFiltrados = ref(props.productos);

// Función para filtrar productos
const filtrarProductos = () => {
    const term = searchTerm.value.toLowerCase();
    productosFiltrados.value = props.productos.filter(producto => {
        return (
            producto.nombre.toLowerCase().includes(term) ||
            producto.codigo.toLowerCase().includes(term)
        );
    });
};

// Configuración personalizada de Notyf
const notyf = new Notyf({
    duration: 3000, // Duración en milisegundos
    position: {
        x: 'right', // Posición horizontal
        y: 'top',   // Posición vertical
    },
    types: [
        {
            type: 'success',
            background: '#4caf50', // Color de fondo para éxito
            icon: {
                className: 'fas fa-check-circle', // Ícono de Font Awesome
                tagName: 'i',
                color: '#fff', // Color del ícono
            },
        },
        {
            type: 'error',
            background: '#f44336', // Color de fondo para error
            icon: {
                className: 'fas fa-times-circle', // Ícono de Font Awesome
                tagName: 'i',
                color: '#fff', // Color del ícono
            },
        },
    ],
});

// Referencia al componente de confirmación
const confirmDialog = ref(null);

// Función para eliminar un producto
const eliminarProducto = async (id) => {
    // Mostrar el cuadro de confirmación
    const confirmed = await confirmDialog.value.show(
        '¿Estás seguro de eliminarlo?',
        'Esta acción no se puede deshacer.'
    );
    // Si el usuario cancela, detener la ejecución
    if (!confirmed) return;

    // Activar estado de carga
    loading.value = true;

    try {
        await router.delete(route('productos.destroy', id), {
            onSuccess: () => {
                notyf.success('El producto ha sido eliminado exitosamente.');

                // Eliminar el producto de la lista en el frontend
                productosFiltrados.value = productosFiltrados.value.filter(producto => producto.id !== id);
            },
            onError: (error) => {
                console.error('Error al eliminar el producto:', error);
                notyf.error('Hubo un error al eliminar el producto.');
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado.');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
