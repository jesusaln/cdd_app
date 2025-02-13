<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Detalles del Producto</h1>

        <!-- Detalles del producto -->
        <div class="space-y-4">
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
                <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.categoria?.nombre }}</p>
            </div>

            <!-- Marca -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Marca</label>
                <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.marca?.nombre }}</p>
            </div>

            <!-- Proveedor -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Proveedor</label>
                <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.proveedor?.nombre }}</p>
            </div>

            <!-- Almacén -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Almacén</label>
                <p class="mt-1 block w-full rounded-md bg-gray-100 p-2">{{ producto.almacen?.nombre }}</p>
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

        <!-- Botón para volver -->
        <div class="mt-6">
            <Link :href="route('productos.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Volver
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout
defineOptions({ layout: Dashboard });

// Recibe el producto como prop
const props = defineProps({
    producto: Object, // Recibe el producto
});
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
