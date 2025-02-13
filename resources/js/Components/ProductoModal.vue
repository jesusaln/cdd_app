<template>
    <!-- Fondo oscuro del modal -->
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <!-- Contenido del modal -->
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-1/3 p-6">
            <!-- Encabezado del modal -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Detalles del Producto</h2>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Detalles del producto -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.nombre }}</p>
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.descripcion }}</p>
                </div>

                <!-- Código -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.codigo }}</p>
                </div>

                <!-- Código de Barras -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código de Barras</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.codigo_barras }}</p>
                </div>

                <!-- Número de Serie -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Número de Serie</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.numero_serie }}</p>
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Categoría</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.categoria?.nombre || 'No especificado' }}</p>
                </div>

                <!-- Marca -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Marca</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.marca?.nombre || 'No especificado' }}</p>
                </div>

                <!-- Proveedor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Proveedor</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.proveedor?.nombre || 'No especificado' }}</p>
                </div>

                <!-- Almacén -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Almacén</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.almacen?.nombre || 'No especificado' }}</p>
                </div>

                <!-- Stock -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.stock }}</p>
                </div>

                <!-- Precio de Compra -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio de Compra</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.precio_compra }}</p>
                </div>

                <!-- Precio de Venta -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio de Venta</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.precio_venta }}</p>
                </div>

                <!-- Tipo de Producto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Producto</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.tipo_producto }}</p>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.estado }}</p>
                </div>
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
import { ref } from 'vue';

// Props: producto y estado inicial del modal
const props = defineProps({
    producto: Object,
    isOpen: Boolean,
});

// Emit: Cerrar el modal
const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
