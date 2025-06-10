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
                            <th v-for="header in headers" :key="header" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ header }}
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
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

            <LoadingSpinner :loading="loading" />

            <ClienteModal :cliente="clienteSeleccionado" :isOpen="isModalOpen" @close="closeModal" />

            <ConfirmModal
                :isOpen="isConfirmOpen"
                title="Confirmar eliminación"
                message="¿Estás seguro de que deseas eliminar el cliente?"
                :itemName="clienteAEliminar?.nombre_razon_social"
                @cancel="isConfirmOpen = false"
                @confirm="eliminarClienteConfirmado"
            />
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import ClienteModal from '@/Components/ClienteModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

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

const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    types: [
        { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
        { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
    ],
});

const clientesFiltrados = computed(() => {
    if (props.clientes?.data) {
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
            notyf.success('Cliente eliminado exitosamente.');
            isConfirmOpen.value = false;
            loading.value = false;
        },
        onError: () => {
            notyf.error('Error al eliminar el cliente.');
            isConfirmOpen.value = false;
            loading.value = false;
        },
    });
};
</script>
