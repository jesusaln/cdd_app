<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-2/3 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Detalles del Producto</h2>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <!-- Usamos una grid para mostrar los detalles en dos columnas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.nombre }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.descripcion }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.codigo }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Numero de Serie</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.numero_serie }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código Barras</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.codigo_barras }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Categoría</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.categoria_id || 'No disponible' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Marca</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.marca_id || 'No disponible' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Proveedor</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.proveedor_id || 'No disponible' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Almacén</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.almacen_id || 'No disponible' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.stock }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio de Compra</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.precio_compra }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio de Venta</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.precio_venta }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.estado }}</p>
                </div>

                <!-- Impuestos -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Impuestos</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.impuesto || 'No disponible' }}</p>
                </div>

                <!-- Fecha de Vencimiento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de Vencimiento</label>
                    <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.fecha_vencimiento || 'No disponible' }}</p>
                </div>

                <!-- Imagen -->
                <div>
            <label class="block text-sm font-medium text-gray-700">Imagen</label>
            <div class="mt-1 block w-full rounded-md bg-gray-100 p-2">
                <img v-if="producto.imagen_url" :src="producto.imagen_url" alt="Imagen del Producto" class="w-full h-auto rounded-md"/>
                <span v-else>No disponible</span>
            </div>
        </div>

            </div>
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

const props = defineProps({
    producto: Object,
    isOpen: Boolean
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

// Depuración: Verifica la estructura del objeto producto
onMounted(() => {
    console.log("Producto recibido:", props.producto);
});

</script>
