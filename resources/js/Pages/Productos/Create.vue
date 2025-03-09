<template>
    <Head title="Crear Producto" />
    <div>
        <h1 class="text-2xl font-semibold mb-4">Crear Producto</h1>
        <form @submit.prevent="submit">
            <div class="space-y-4">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input v-model="form.nombre" type="text" id="nombre" placeholder="Nombre" class="input" required />
                    <div v-if="form.errors.nombre" class="text-red-500">{{ form.errors.nombre }}</div>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea v-model="form.descripcion" id="descripcion" placeholder="Descripción" class="input"></textarea>
                </div>

                <!-- Código -->
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700">Código</label>
                    <input v-model="form.codigo" type="text" id="codigo" placeholder="Código" class="input" required />
                    <div v-if="form.errors.codigo" class="text-red-500">{{ form.errors.codigo }}</div>
                </div>

                <!-- Código de Barras -->
                <div>
                    <label for="codigo_barras" class="block text-sm font-medium text-gray-700">Código de Barras</label>
                    <input v-model="form.codigo_barras" type="text" id="codigo_barras" placeholder="Código de Barras" class="input" required />
                    <div v-if="form.errors.codigo_barras" class="text-red-500">{{ form.errors.codigo_barras }}</div>
                </div>

                <!-- Número de Serie -->
                <div>
                    <label for="numero_serie" class="block text-sm font-medium text-gray-700">Número de Serie</label>
                    <input v-model="form.numero_serie" type="text" id="numero_serie" placeholder="Número de Serie" class="input" />
                    <div v-if="form.errors.numero_serie" class="text-red-500">{{ form.errors.numero_serie }}</div>
                </div>

                <!-- Categoría -->
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700">Categoría</label>
                    <select v-model="form.categoria_id" id="categoria_id" class="input" required>
                        <option value="">Selecciona una categoría</option>
                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                            {{ categoria.nombre }}
                        </option>
                    </select>
                    <div v-if="form.errors.categoria_id" class="text-red-500">{{ form.errors.categoria_id }}</div>
                </div>

                <!-- Marca -->
                <div>
                    <label for="marca_id" class="block text-sm font-medium text-gray-700">Marca</label>
                    <select v-model="form.marca_id" id="marca_id" class="input" required>
                        <option value="">Selecciona una marca</option>
                        <option v-for="marca in marcas" :key="marca.id" :value="marca.id">
                            {{ marca.nombre }}
                        </option>
                    </select>
                    <div v-if="form.errors.marca_id" class="text-red-500">{{ form.errors.marca_id }}</div>
                </div>

                <!-- Proveedor -->
                <div>
                    <label for="proveedor_id" class="block text-sm font-medium text-gray-700">Proveedor</label>
                    <select v-model="form.proveedor_id" id="proveedor_id" class="input">
                        <option value="">Selecciona un proveedor</option>
                        <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                            {{ proveedor.nombre }}
                        </option>
                    </select>
                    <div v-if="form.errors.proveedor_id" class="text-red-500">{{ form.errors.proveedor_id }}</div>
                </div>

                <!-- Almacén -->
                <div>
                    <label for="almacen_id" class="block text-sm font-medium text-gray-700">Almacén</label>
                    <select v-model="form.almacen_id" id="almacen_id" class="input">
                        <option value="">Selecciona un almacén</option>
                        <option v-for="almacen in almacenes" :key="almacen.id" :value="almacen.id">
                            {{ almacen.nombre }}
                        </option>
                    </select>
                    <div v-if="form.errors.almacen_id" class="text-red-500">{{ form.errors.almacen_id }}</div>
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                    <input v-model="form.stock" type="number" id="stock" placeholder="Stock" class="input" required />
                    <div v-if="form.errors.stock" class="text-red-500">{{ form.errors.stock }}</div>
                </div>

                <!-- Stock Mínimo -->
                <div>
                    <label for="stock_minimo" class="block text-sm font-medium text-gray-700">Stock Mínimo</label>
                    <input v-model="form.stock_minimo" type="number" id="stock_minimo" placeholder="Stock Mínimo" class="input" required />
                    <div v-if="form.errors.stock_minimo" class="text-red-500">{{ form.errors.stock_minimo }}</div>
                </div>

                <!-- Precio Compra -->
                <div>
                    <label for="precio_compra" class="block text-sm font-medium text-gray-700">Precio de Compra</label>
                    <input v-model="form.precio_compra" type="number" step="0.01" id="precio_compra" placeholder="Precio de Compra" class="input" required />
                    <div v-if="form.errors.precio_compra" class="text-red-500">{{ form.errors.precio_compra }}</div>
                </div>

                <!-- Precio Venta -->
                <div>
                    <label for="precio_venta" class="block text-sm font-medium text-gray-700">Precio de Venta</label>
                    <input v-model="form.precio_venta" type="number" step="0.01" id="precio_venta" placeholder="Precio de Venta" class="input" required />
                    <div v-if="form.errors.precio_venta" class="text-red-500">{{ form.errors.precio_venta }}</div>
                </div>

                <!-- Impuesto -->
                <div>
                    <label for="impuesto" class="block text-sm font-medium text-gray-700">Impuesto %</label>
                    <input v-model="form.impuesto" type="number" step="0.01" id="impuesto" placeholder="Impuesto" class="input" required />
                    <div v-if="form.errors.impuesto" class="text-red-500">{{ form.errors.impuesto }}</div>
                </div>

                <!-- Unidad de Medida -->
                <div>
    <label for="unidad_medida" class="block text-sm font-medium text-gray-700">Unidad de Medida</label>
    <select v-model="form.unidad_medida" id="unidad_medida" class="input" required>
        <option disabled value="">Seleccione una unidad</option>
        <option v-for="unidad in unidadesMedida" :key="unidad" :value="unidad">
            {{ unidad }}
        </option>
    </select>
    <div v-if="form.errors.unidad_medida" class="text-red-500">{{ form.errors.unidad_medida }}</div>
</div>


                <!-- Fecha de Vencimiento -->
                <div>
                    <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700">Fecha de Vencimiento</label>
                    <input v-model="form.fecha_vencimiento" type="date" id="fecha_vencimiento" class="input" />
                    <div v-if="form.errors.fecha_vencimiento" class="text-red-500">{{ form.errors.fecha_vencimiento }}</div>
                </div>

                <!-- Tipo de Producto -->
                <div>
                    <label for="tipo_producto" class="block text-sm font-medium text-gray-700">Tipo de Producto</label>
                    <select v-model="form.tipo_producto" id="tipo_producto" class="input" required>
                        <option value="fisico">Físico</option>
                        <option value="digital">Digital</option>
                    </select>
                    <div v-if="form.errors.tipo_producto" class="text-red-500">{{ form.errors.tipo_producto }}</div>
                </div>

                <!-- Imagen -->
                <div>
                    <label for="imagen" class="block text-sm font-medium text-gray-700">Imagen</label>
                    <input type="file" @change="handleImageUpload" id="imagen" class="input" />
                    <div v-if="form.errors.imagen" class="text-red-500">{{ form.errors.imagen }}</div>
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select v-model="form.estado" id="estado" class="input" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                    <div v-if="form.errors.estado" class="text-red-500">{{ form.errors.estado }}</div>
                </div>

                <!-- Botón de Envío -->
                <div class="mt-6">
                    <button type="submit" class="btn">Guardar Producto</button>
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

// Recibir las relaciones desde el backend
const props = defineProps({
    categorias: Array,
    marcas: Array,
    proveedores: Array,
    almacenes: Array,
});

// Lista de unidades de medida
const unidadesMedida = [
    "Kilogramos",
    "Gramos",
    "Litros",
    "Mililitros",
    "Piezas",
    "Metros",
    "Centímetros",
    "Milímetros",
    "Unidades"
];

// Formulario para crear un producto
const form = useForm({
    nombre: '',
    descripcion: '',
    codigo: '',
    codigo_barras: '',
    numero_serie: '',
    categoria_id: '',
    marca_id: '',
    proveedor_id: '',
    almacen_id: '',
    stock: '',
    stock_minimo: '',
    precio_compra: '',
    precio_venta: '',
    impuesto: '16',
    unidad_medida: '',  // Se mantiene vacío para que sea seleccionado por el usuario
    fecha_vencimiento: '',
    tipo_producto: 'fisico',
    imagen: null,
    estado: 'activo',
});

// Enviar formulario
const submit = () => {
    form.post(route('productos.store'), {
        onSuccess: () => form.reset(),
    });
};

// Manejar carga de imágenes
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) form.imagen = file;
};
</script>

<style>
.input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.btn {
    background-color: blue;
    color: white;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
}
</style>
