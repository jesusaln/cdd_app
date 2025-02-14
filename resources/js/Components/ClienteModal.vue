<template>
    <!-- Modal -->
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-2/3 p-6">
            <!-- Encabezado del modal -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Detalles del Cliente</h2>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Contenido del modal -->
            <div v-if="cliente" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Campos del cliente -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.nombre_razon_social }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">RFC</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.rfc }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.telefono }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dirección</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">
                        {{ cliente.calle }} {{ cliente.numero_exterior }} {{ cliente.numero_interior }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Colonia</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.colonia }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ciudad</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.municipio }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código Postal</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.codigo_postal }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.estado }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Regimen Fiscal</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.regimen_fiscal }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Uso de CFDI</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.uso_cfdi }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">País</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ cliente.pais }}</p>
                </div>
            </div>

            <!-- Mensaje si no hay cliente -->
            <div v-else class="text-center text-gray-500">
                No se ha proporcionado información del cliente.
            </div>

            <!-- Botón para cerrar el modal -->
            <div class="mt-6 flex justify-end">
                <button @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, onMounted } from 'vue';

// Definición de props
const props = defineProps({
    cliente: {
        type: Object,
        default: () => ({
            nombre_razon_social: '',
            rfc: '',
            email: '',
            telefono: '',
            calle: '',
            numero_exterior: '',
            numero_interior: '',
            colonia: '',
            municipio: '',
            codigo_postal: '',
            estado: '',
            regimen_fiscal: '',
            uso_cfdi: '',
            pais: ''
        })
    },
    isOpen: Boolean
});

// Definición de emisiones
const emit = defineEmits(['close']);

// Función para cerrar el modal
const closeModal = () => {
    emit('close');
};

// Depuración en mounted
onMounted(() => {
    //console.log("Cliente recibido:", props.cliente);
});
</script>
