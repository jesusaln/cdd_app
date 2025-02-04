<template>
    <div>
        <!-- Título de la página -->
        <h1 class="text-2xl font-semibold mb-6">Registro de Clientes</h1>

        <!-- Botón para crear un nuevo cliente -->
        <div class="mb-4">
            <Link :href="route('clientes.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Crear Cliente
            </Link>
        </div>

        <!-- Tabla de clientes -->
        <div v-if="clientes.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
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
                    <tr v-for="cliente in clientes" :key="cliente.id" class="hover:bg-gray-100">
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ cliente.nombre_razon_social }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ cliente.rfc }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ cliente.regimen_fiscal }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ cliente.uso_cfdi }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ cliente.email }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ cliente.telefono }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ cliente.calle }} {{ cliente.numero_exterior }} {{ cliente.numero_interior }},
                            {{ cliente.colonia }}, {{ cliente.codigo_postal }},
                            {{ cliente.municipio }}, {{ cliente.estado }}, {{ cliente.pais }}
                        </td>
                        <td class="px-4 py-3 flex space-x-2">
                            <Link :href="route('clientes.edit', cliente.id)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                Editar
                            </Link>
                            <button @click="eliminarCliente(cliente.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mensaje si no hay clientes -->
        <div v-else class="text-center text-gray-500 mt-4">
            No hay clientes registrados.
        </div>

        <!-- Spinner de carga -->
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout del dashboard
defineOptions({ layout: Dashboard });

// Recibe los clientes como prop
defineProps({ clientes: Array });

// Encabezados de la tabla
const headers = [
    'Nombre/Razón Social',
    'RFC',
    'Régimen Fiscal',
    'Uso CFDI',
    'Email',
    'Teléfono',
    'Dirección'
];

// Estado para el spinner de carga
const loading = ref(false);

// Función para eliminar un cliente
const eliminarCliente = async (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este cliente? Esta acción no se puede deshacer.')) {
        loading.value = true;
        try {
            await router.delete(route('clientes.destroy', id), {
                onSuccess: () => {
                    loading.value = false;
                },
                onError: () => {
                    alert('Hubo un error al eliminar el cliente. Por favor, inténtalo de nuevo.');
                    loading.value = false;
                }
            });
        } catch (error) {
            console.error('Error al eliminar el cliente:', error);
            alert('Hubo un error al eliminar el cliente.');
            loading.value = false;
        }
    }
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
