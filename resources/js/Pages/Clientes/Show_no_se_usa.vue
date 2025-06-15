<template>
    <Head title="Detalles del Cliente" />
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header con navegación -->
            <div class="mb-8">
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                    <Link href="/clientes" class="hover:text-gray-700">Clientes</Link>
                    <span>/</span>
                    <span class="text-gray-900">{{ cliente.nombre }}</span>
                </nav>

                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Detalles del Cliente
                    </h1>
                    <div class="flex space-x-3">
                        <Link
                            :href="`/clientes/${cliente.id}/edit`"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </Link>
                        <button
                            @click="openModal"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Ver Detalles Completos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tarjeta principal con información del cliente -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold text-gray-900">{{ cliente.nombre }}</h2>
                            <p class="text-sm text-gray-500">Cliente ID: #{{ cliente.id }}</p>
                        </div>
                        <div class="ml-auto">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="cliente.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            >
                                {{ cliente.activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div v-if="cliente.email">
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a :href="`mailto:${cliente.email}`" class="text-blue-600 hover:text-blue-500">
                                    {{ cliente.email }}
                                </a>
                            </dd>
                        </div>

                        <div v-if="cliente.telefono">
                            <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a :href="`tel:${cliente.telefono}`" class="text-blue-600 hover:text-blue-500">
                                    {{ cliente.telefono }}
                                </a>
                            </dd>
                        </div>

                        <div v-if="cliente.direccion">
                            <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ cliente.direccion }}</dd>
                        </div>

                        <div v-if="cliente.rfc">
                            <dt class="text-sm font-medium text-gray-500">RFC</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ cliente.rfc }}</dd>
                        </div>

                        <div v-if="cliente.created_at">
                            <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ new Date(cliente.created_at).toLocaleDateString('es-ES', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                }) }}
                            </dd>
                        </div>

                        <div v-if="cliente.updated_at">
                            <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ new Date(cliente.updated_at).toLocaleDateString('es-ES', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                }) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Sección de estadísticas o información adicional -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Pedidos</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ cliente.total_pedidos || 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Valor Total</dt>
                                    <dd class="text-lg font-medium text-gray-900">${{ cliente.valor_total || '0.00' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 9l-3-3m0 0l3-3m-3 3h12.5M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Último Pedido</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ cliente.ultimo_pedido ?
                                            new Date(cliente.ultimo_pedido).toLocaleDateString('es-ES') :
                                            'N/A'
                                        }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-3">
                <Link
                    href="/clientes"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                >
                    Volver a la Lista
                </Link>
                <button
                    @click="confirmDelete"
                    class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar Cliente
                </button>
            </div>
        </div>

        <!-- Modal para mostrar detalles completos del cliente -->
        <ClienteModal
            :cliente="cliente"
            :isOpen="isModalOpen"
            @close="closeModal"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ClienteModal from '@/Components/ClienteModal.vue';

// Recibe el cliente como prop
const props = defineProps({
    cliente: {
        type: Object,
        required: true
    }
});

// Estado para controlar la visibilidad del modal
const isModalOpen = ref(false);

// Funciones para abrir y cerrar el modal
const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

// Función para confirmar eliminación
const confirmDelete = () => {
    if (confirm(`¿Estás seguro de que deseas eliminar al cliente "${props.cliente.nombre}"?`)) {
        // Aquí puedes implementar la lógica de eliminación
        // Por ejemplo, usando Inertia.js:
        // router.delete(`/clientes/${props.cliente.id}`);
        console.log('Eliminando cliente...', props.cliente.id);
    }
};
</script>

<style scoped>
/* Los estilos están manejados principalmente por Tailwind CSS */
.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>
