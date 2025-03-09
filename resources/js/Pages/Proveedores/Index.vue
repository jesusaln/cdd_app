<template>
    <Head title="Proveedores" />
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Registro de Proveedores</h1>

        <!-- Botón para crear un nuevo proveedor -->
        <div class="mb-4 flex justify-between items-center">
            <Link :href="route('proveedores.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Crear Proveedor
            </Link>

            <input
                v-model="searchTerm"
                type="text"
                placeholder="Buscar por nombre o RFC"
                class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                @input="filtrarProveedores"
            />
        </div>

        <!-- Tabla de proveedores -->
        <div v-if="proveedoresFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
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
                    <tr v-for="proveedor in proveedoresFiltrados" :key="proveedor.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ proveedor.nombre }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ proveedor.rfc }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ proveedor.contacto }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ proveedor.telefono }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ proveedor.email }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ proveedor.direccion }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ proveedor.municipio }}, {{ proveedor.estado }}, {{ proveedor.pais }}
                        </td>
                        <td class="px-4 py-3 flex space-x-2">
                            <Link :href="route('proveedores.edit', proveedor.id)" class="flex items-center space-x-2 bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-blue-600 transition duration-300">
                                <i class="fas fa-edit"></i>
                                <span>Editar</span>
                            </Link>
                            <button @click="eliminarProveedor(proveedor.id)" class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-red-600 transition duration-300">
                                <i class="fas fa-trash-alt"></i>
                                <span>Eliminar</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay proveedores -->
        <div v-else class="text-center text-gray-500 mt-4">
            No hay proveedores registrados.
        </div>

        <!-- Spinner de carga -->
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <!-- Componente de confirmación -->
        <ConfirmDialog ref="confirmDialog" />
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css'; // Importa los estilos de Notyf

import ConfirmDialog from '@/Components/ConfirmDialog.vue'; // Importa el componente
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe los proveedores como prop
const props = defineProps({ proveedores: Array });

// Encabezados de la tabla
const headers = [
    'Nombre',
    'RFC',
    'Contacto',
    'Teléfono',
    'Email',
    'Dirección',
    'Ubicación'
];

// Estado para el spinner de carga
const loading = ref(false);

// Término de búsqueda
const searchTerm = ref('');

// Proveedores filtrados
const proveedoresFiltrados = ref(props.proveedores);

// Función para filtrar proveedores
const filtrarProveedores = () => {
    const term = searchTerm.value.toLowerCase();
    proveedoresFiltrados.value = props.proveedores.filter(proveedor => {
        return (
            proveedor.nombre.toLowerCase().includes(term) ||
            proveedor.rfc.toLowerCase().includes(term)
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

// Definir proveedores como una referencia reactiva
const proveedores = ref([]);

// Función para eliminar un proveedor
const eliminarProveedor = async (id) => {
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
        await router.delete(route('proveedores.destroy', id), {
            onSuccess: () => {
                notyf.success('El proveedor ha sido eliminado exitosamente.');

                // Eliminar el proveedor de la lista en el frontend
                proveedores.value = proveedores.value.filter(proveedor => proveedor.id !== id);

                // Recargar la lista de proveedores desde el servidor
                const fetchProveedores = async () => {
                    const response = await axios.get(route('proveedores.index'));
                    proveedores.value = response.data;
                };
                fetchProveedores();
            },
            onError: (error) => {
                console.error('Error al eliminar el proveedor:', error);
                notyf.error('Hubo un error al eliminar el proveedor.');
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
