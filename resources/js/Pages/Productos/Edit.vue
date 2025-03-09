<template>
    <Head title="Editar Productos" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Editar Producto</h1>
        <!-- Formulario de edición de producto -->
        <form @submit.prevent="submit">
            <div class="space-y-4">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input v-model="form.nombre" type="text" id="nombre" placeholder="Nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.nombre" class="text-red-500">{{ form.errors.nombre }}</div>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea v-model="form.descripcion" id="descripcion" placeholder="Descripción" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    <div v-if="form.errors.descripcion" class="text-red-500">{{ form.errors.descripcion }}</div>
                </div>

                <!-- Código -->
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700">Código</label>
                    <input v-model="form.codigo" type="text" id="codigo" placeholder="Código" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.codigo" class="text-red-500">{{ form.errors.codigo }}</div>
                </div>

                <!-- Código de Barras -->
                <div>
                    <label for="codigo_barras" class="block text-sm font-medium text-gray-700">Código de Barras</label>
                    <input v-model="form.codigo_barras" type="text" id="codigo_barras" placeholder="Código de Barras" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.codigo_barras" class="text-red-500">{{ form.errors.codigo_barras }}</div>
                </div>

                <!-- Número de Serie -->
                <div>
                    <label for="numero_serie" class="block text-sm font-medium text-gray-700">Número de Serie</label>
                    <input v-model="form.numero_serie" type="text" id="numero_serie" placeholder="Número de Serie" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    <div v-if="form.errors.numero_serie" class="text-red-500">{{ form.errors.numero_serie }}</div>
                </div>

                <!-- Categoría -->
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700">Categoría</label>
                    <select v-model="form.categoria_id" id="categoria_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="">Selecciona una categoría</option>
                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">{{ categoria.nombre }}</option>
                    </select>
                    <div v-if="form.errors.categoria_id" class="text-red-500">{{ form.errors.categoria_id }}</div>
                </div>

                <!-- Marca -->
                <div>
                    <label for="marca_id" class="block text-sm font-medium text-gray-700">Marca</label>
                    <select v-model="form.marca_id" id="marca_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="">Selecciona una marca</option>
                        <option v-for="marca in marcas" :key="marca.id" :value="marca.id">{{ marca.nombre }}</option>
                    </select>
                    <div v-if="form.errors.marca_id" class="text-red-500">{{ form.errors.marca_id }}</div>
                </div>

                <!-- Proveedor -->
                <div>
                    <label for="proveedor_id" class="block text-sm font-medium text-gray-700">Proveedor</label>
                    <select v-model="form.proveedor_id" id="proveedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Selecciona un proveedor</option>
                        <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">{{ proveedor.nombre }}</option>
                    </select>
                    <div v-if="form.errors.proveedor_id" class="text-red-500">{{ form.errors.proveedor_id }}</div>
                </div>

                <!-- Almacén -->
                <div>
                    <label for="almacen_id" class="block text-sm font-medium text-gray-700">Almacén</label>
                    <select v-model="form.almacen_id" id="almacen_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Selecciona un almacen</option>
                        <option v-for="almacen in almacenes" :key="almacen.id" :value="almacen.id">{{ almacen.nombre }}</option>
                    </select>
                    <div v-if="form.errors.almacen_id" class="text-red-500">{{ form.errors.almacen_id }}</div>
                </div>
                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                    <input v-model="form.stock" type="number" id="stock" placeholder="Stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.stock" class="text-red-500">{{ form.errors.stock }}</div>
                </div>

                <!-- Precio de Compra -->
                <div>
                    <label for="precio_compra" class="block text-sm font-medium text-gray-700">Precio de Compra</label>
                    <input v-model="form.precio_compra" type="number" step="0.01" id="precio_compra" placeholder="Precio de Compra" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.precio_compra" class="text-red-500">{{ form.errors.precio_compra }}</div>
                </div>

                <!-- Precio de Venta -->
                <div>
                    <label for="precio_venta" class="block text-sm font-medium text-gray-700">Precio de Venta</label>
                    <input v-model="form.precio_venta" type="number" step="0.01" id="precio_venta" placeholder="Precio de Venta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.precio_venta" class="text-red-500">{{ form.errors.precio_venta }}</div>
                </div>

                <!-- Tipo de Producto -->
                <div>
                    <label for="tipo_producto" class="block text-sm font-medium text-gray-700">Tipo de Producto</label>
                    <select v-model="form.tipo_producto" id="tipo_producto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="fisico">Físico</option>
                        <option value="digital">Digital</option>
                    </select>
                    <div v-if="form.errors.tipo_producto" class="text-red-500">{{ form.errors.tipo_producto }}</div>
                </div>

                <!-- Imagen -->
                <div>
                    <label for="imagen" class="block text-sm font-medium text-gray-700">Imagen</label>
                    <input type="file" @change="handleImageUpload" id="imagen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    <div v-if="form.errors.imagen" class="text-red-500">{{ form.errors.imagen }}</div>
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select v-model="form.estado" id="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                    <div v-if="form.errors.estado" class="text-red-500">{{ form.errors.estado }}</div>
                </div>

                <!-- Botón de Envío -->
                <div class="mt-6">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Actualizar Cliente
                </button>
            </div>
            </div>
        </form>
    </div>
</template>
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe el producto, categorías, marcas y proveedores como props
const props = defineProps({
    producto: Object,
    categorias: { type: Array, default: () => [] },
    marcas: { type: Array, default: () => [] },
    proveedores: { type: Array, default: () => [] },
    almacenes: { type: Array, default: () => [] }, // Evita el error si no se recibe
});

// Inicializa el formulario con los datos del producto
const form = useForm({
    id: props.producto?.id || '', // Si no hay producto, usa un valor vacío
    nombre: props.producto?.nombre || '',
    categoria_id: props.producto?.categoria_id || '',
    marca_id: props.producto?.marca_id || '',
    proveedor_id: props.producto?.proveedor_id || '',
    almacen_id: props.producto?.almacen_id || '',

    descripcion: props.producto?.descripcion || '',
    codigo: props.producto?.codigo || '',
    codigo_barras: props.producto?.codigo_barras || '',
    numero_serie: props.producto?.numero_serie || '',
    stock: props.producto?.stock || '',
    precio_compra: props.producto?.precio_compra || '',
    precio_venta: props.producto?.precio_venta || '',
    tipo_producto: props.producto?.tipo_producto || '',
    //imagen: props.producto?.imagen || null, // Imagen es opcional y se envía como null si no hay imagen subida
    estado: props.producto?.estado || '',
});

// Función para enviar el formulario de actualización de producto
const submit = () => {
    form.put(route('productos.update', props.producto.id), {
        onSuccess: () => {
            alert('Producto actualizado correctamente.');
        },
        onError: (errors) => {
            console.error('Error al actualizar el producto:', errors);
        },
        onFinish: () => {
            console.log('Actualización de producto completada');
        },
    });
};

</script>
