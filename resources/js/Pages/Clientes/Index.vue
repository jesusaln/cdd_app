<template>
    <Head title="Clientes" />
    <div>
        <h1 class="text-2xl font-semibold mb-6">{{ titulo }}</h1>
        <div>
            <h1 class="text-3xl font-bold mb-6 text-center">Registro de Clientes</h1>

            <div class="mb-4 flex justify-between items-center">
                <Link :href="route('clientes.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Crear Cliente
                </Link>
                <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Buscar por nombre o RFC"
                    class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div v-if="clientesFiltrados.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
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
                        <tr v-for="cliente in clientesFiltrados" :key="cliente.id" class="hover:bg-gray-100">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.nombre_razon_social }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.rfc }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.regimen_fiscal }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.uso_cfdi }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ cliente.telefono }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ cliente.calle }} {{ cliente.numero_exterior }} {{ cliente.numero_interior }},
                                {{ cliente.colonia }}, {{ cliente.codigo_postal }},
                                {{ cliente.municipio }}, {{ cliente.estado }}, {{ cliente.pais }}
                            </td>
                            <td class="px-4 py-3 flex space-x-2">
                                <button @click="openModal(cliente)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                                    Ver
                                </button>
                                <Link :href="route('clientes.edit', cliente.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                    Editar
                                </Link>
                                <button @click="confirmarEliminacion(cliente)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="text-center text-gray-500 mt-4">
                No hay clientes registrados.
            </div>

            <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
            </div>

            <ClienteModal :cliente="clienteSeleccionado" :isOpen="isModalOpen" @close="closeModal" />

            <div v-if="isConfirmOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                    <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
                    <p class="text-gray-700 mb-4">¿Estás seguro de que deseas eliminar el cliente <strong>{{ clienteAEliminar?.nombre_razon_social }}</strong>?</p>
                    <div class="flex justify-end space-x-4">
                        <button @click="isConfirmOpen = false" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                            Cancelar
                        </button>
                        <button @click="eliminarClienteConfirmado" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import ClienteModal from '@/Components/ClienteModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { watchEffect } from 'vue';


defineOptions({ layout: AppLayout });

const props = defineProps({
    titulo: String,
    clientes: Object,
});

const page = usePage();

document.title = props.titulo;

const headers = ['Nombre/Razón Social', 'RFC', 'Régimen Fiscal', 'Uso CFDI', 'Email', 'Teléfono', 'Dirección'];
const loading = ref(false);
const searchTerm = ref('');
const clienteSeleccionado = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const clienteAEliminar = ref(null);

// Configuración de Notyf
const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

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



const clientesFiltrados = computed(() => {
    if (props.clientes?.data) { // Ajustado para paginación
        return props.clientes.data.filter(cliente => {
            return cliente.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                   cliente.rfc?.toLowerCase().includes(searchTerm.value.toLowerCase());
        });
    }
    return [];
});

const openModal = (cliente) => {
    clienteSeleccionado.value = cliente;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const confirmarEliminacion = (cliente) => {
    clienteAEliminar.value = cliente;
    isConfirmOpen.value = true;
};

const eliminarClienteConfirmado = () => {
    if (!clienteAEliminar.value) return;

    loading.value = true;
    router.delete(route('clientes.destroy', clienteAEliminar.value.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            if (!page.props.flash?.success) { // Verifica si el mensaje de éxito ya está configurado
                notyf.success('Cliente eliminado exitosamente.');
            }
            isConfirmOpen.value = false;
            loading.value = false;
        },
        onError: () => {
            if (!page.props.flash?.error) { // Verifica si el mensaje de error ya está configurado
                notyf.error('Error al eliminar el cliente.');
            }
            isConfirmOpen.value = false;
            loading.value = false;
        },
    });
};

</script>
