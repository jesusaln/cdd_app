<template>
    <Head title="Crear Producto" />
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">Crear Producto</h1>
                <p class="text-sm text-gray-600 mt-1">Complete la información del producto</p>
            </div>

            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button
                        @click="activeTab = 'general'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'general'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        type="button"
                    >
                        Información General
                    </button>
                    <button
                        @click="activeTab = 'pricing'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'pricing'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        type="button"
                    >
                        Precios e Inventario
                    </button>
                    <button
                        @click="activeTab = 'additional'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'additional'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        type="button"
                    >
                        Información Adicional
                    </button>
                </nav>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="submit" class="p-6">
                <!-- Información General Tab -->
                <div v-show="activeTab === 'general'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Producto <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.nombre"
                                type="text"
                                id="nombre"
                                placeholder="Ingrese el nombre del producto"
                                class="input-field"
                                required
                            />
                            <div v-if="form.errors.nombre" class="error-message">{{ form.errors.nombre }}</div>
                        </div>

                        <!-- Código -->
                        <div>
                            <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                                Código <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.codigo"
                                type="text"
                                id="codigo"
                                placeholder="Código único del producto"
                                class="input-field"
                                required
                            />
                            <div v-if="form.errors.codigo" class="error-message">{{ form.errors.codigo }}</div>
                        </div>

                        <!-- Código de Barras -->
                        <div>
                            <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">
                                Código de Barras <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.codigo_barras"
                                type="text"
                                id="codigo_barras"
                                placeholder="Código de barras"
                                class="input-field"
                                required
                            />
                            <div v-if="form.errors.codigo_barras" class="error-message">{{ form.errors.codigo_barras }}</div>
                        </div>

                        <!-- Número de Serie -->
                        <div>
                            <label for="numero_serie" class="block text-sm font-medium text-gray-700 mb-2">
                                Número de Serie
                            </label>
                            <input
                                v-model="form.numero_serie"
                                type="text"
                                id="numero_serie"
                                placeholder="Número de serie (opcional)"
                                class="input-field"
                            />
                            <div v-if="form.errors.numero_serie" class="error-message">{{ form.errors.numero_serie }}</div>
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.categoria_id" id="categoria_id" class="input-field" required>
                                <option value="">Seleccione una categoría</option>
                                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                    {{ categoria.nombre }}
                                </option>
                            </select>
                            <div v-if="form.errors.categoria_id" class="error-message">{{ form.errors.categoria_id }}</div>
                        </div>

                        <!-- Marca -->
                        <div>
                            <label for="marca_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Marca <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.marca_id" id="marca_id" class="input-field" required>
                                <option value="">Seleccione una marca</option>
                                <option v-for="marca in marcas" :key="marca.id" :value="marca.id">
                                    {{ marca.nombre }}
                                </option>
                            </select>
                            <div v-if="form.errors.marca_id" class="error-message">{{ form.errors.marca_id }}</div>
                        </div>

                        <!-- Proveedor -->
                        <div>
                            <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Proveedor
                            </label>
                            <select v-model="form.proveedor_id" id="proveedor_id" class="input-field">
                                <option value="">Seleccione un proveedor</option>
                                <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                    {{ proveedor.nombre_razon_social }}
                                </option>
                            </select>
                            <div v-if="form.errors.proveedor_id" class="error-message">{{ form.errors.proveedor_id }}</div>
                        </div>

                        <!-- Almacén -->
                        <div>
                            <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Almacén
                            </label>
                            <select v-model="form.almacen_id" id="almacen_id" class="input-field">
                                <option value="">Seleccione un almacén</option>
                                <option v-for="almacen in almacenes" :key="almacen.id" :value="almacen.id">
                                    {{ almacen.nombre }}
                                </option>
                            </select>
                            <div v-if="form.errors.almacen_id" class="error-message">{{ form.errors.almacen_id }}</div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea
                            v-model="form.descripcion"
                            id="descripcion"
                            rows="4"
                            placeholder="Descripción detallada del producto"
                            class="input-field resize-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Precios e Inventario Tab -->
                <div v-show="activeTab === 'pricing'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Stock -->
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                                Stock Actual <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.stock"
                                type="number"
                                id="stock"
                                placeholder="Cantidad en stock"
                                class="input-field"
                                min="0"
                                required
                            />
                            <div v-if="form.errors.stock" class="error-message">{{ form.errors.stock }}</div>
                        </div>

                        <!-- Stock Mínimo -->
                        <div>
                            <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-2">
                                Stock Mínimo <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.stock_minimo"
                                type="number"
                                id="stock_minimo"
                                placeholder="Stock mínimo requerido"
                                class="input-field"
                                min="0"
                                required
                            />
                            <div v-if="form.errors.stock_minimo" class="error-message">{{ form.errors.stock_minimo }}</div>
                        </div>

                        <!-- Precio de Compra -->
                        <div>
                            <label for="precio_compra" class="block text-sm font-medium text-gray-700 mb-2">
                                Precio de Compra <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
    <input
        v-model="form.precio_compra"
        type="number"
        step="0.01"
        id="precio_compra"
        placeholder="$ 0.00"
        class="input-field"
        min="0"
        required
    />
</div>
                            <div v-if="form.errors.precio_compra" class="error-message">{{ form.errors.precio_compra }}</div>
                        </div>

                        <!-- Precio de Venta -->
                        <div>
                            <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-2">
                                Precio de Venta <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
    <input
        v-model="form.precio_venta"
        type="number"
        step="0.01"
        id="precio_venta"
        placeholder="$ 0.00"
        class="input-field"
        min="0"
        required
    />
</div>
                            <div v-if="form.errors.precio_venta" class="error-message">{{ form.errors.precio_venta }}</div>
                        </div>

                        <!-- Impuesto -->
                        <div>
                            <label for="impuesto" class="block text-sm font-medium text-gray-700 mb-2">
                                Impuesto (%) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.impuesto"
                                    type="number"
                                    step="0.01"
                                    id="impuesto"
                                    placeholder="16.00"
                                    class="input-field pr-8"
                                    min="0"
                                    max="100"
                                    required
                                />
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">%</span>
                            </div>
                            <div v-if="form.errors.impuesto" class="error-message">{{ form.errors.impuesto }}</div>
                        </div>

                        <!-- Unidad de Medida -->
                        <div>
                            <label for="unidad_medida" class="block text-sm font-medium text-gray-700 mb-2">
                                Unidad de Medida <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.unidad_medida" id="unidad_medida" class="input-field" required>
                                <option value="">Seleccione una unidad</option>
                                <option v-for="unidad in unidadesMedida" :key="unidad" :value="unidad">
                                    {{ unidad }}
                                </option>
                            </select>
                            <div v-if="form.errors.unidad_medida" class="error-message">{{ form.errors.unidad_medida }}</div>
                        </div>

                        <!-- Comisión Vendedor -->
                        <div>
                            <label for="comision_vendedor" class="block text-sm font-medium text-gray-700 mb-2">
                                Comisión Vendedor (%)
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.comision_vendedor"
                                    type="number"
                                    step="0.01"
                                    id="comision_vendedor"
                                    placeholder="0.00"
                                    class="input-field pr-8"
                                    min="0"
                                    max="100"
                                />
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Porcentaje adicional que recibe el vendedor por cada venta de este producto
                            </p>
                            <div v-if="form.errors.comision_vendedor" class="error-message">{{ form.errors.comision_vendedor }}</div>
                        </div>
                    </div>

                    <!-- Margen de Ganancia (calculado automáticamente) -->
                    <div v-if="form.precio_compra && form.precio_venta" class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Análisis de Rentabilidad</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Margen:</span>
                                <span class="font-medium text-green-600 ml-2">
                                    ${{ (parseFloat(form.precio_venta) - parseFloat(form.precio_compra)).toFixed(2) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Porcentaje:</span>
                                <span class="font-medium text-blue-600 ml-2">
                                    {{ ((parseFloat(form.precio_venta) - parseFloat(form.precio_compra)) / parseFloat(form.precio_compra) * 100).toFixed(1) }}%
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Precio Final:</span>
                                <span class="font-medium text-gray-900 ml-2">
                                    ${{ (parseFloat(form.precio_venta) * (1 + parseFloat(form.impuesto || 0) / 100)).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Adicional Tab -->
                <div v-show="activeTab === 'additional'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tipo de Producto -->
                        <div>
                            <label for="tipo_producto" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Producto <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.tipo_producto" id="tipo_producto" class="input-field" required>
                                <option value="fisico">Producto Físico</option>
                                <option value="digital">Producto Digital</option>
                            </select>
                            <div v-if="form.errors.tipo_producto" class="error-message">{{ form.errors.tipo_producto }}</div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.estado" id="estado" class="input-field" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <div v-if="form.errors.estado" class="error-message">{{ form.errors.estado }}</div>
                        </div>
                    </div>

                    <!-- Imagen del Producto -->
                    <div>
                        <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">
                            Imagen del Producto
                        </label>
                        <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                            <div class="space-y-2 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="text-sm text-gray-600">
                                    <label for="imagen" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Subir imagen</span>
                                        <input @change="handleImageUpload" id="imagen" name="imagen" type="file" accept="image/*" class="sr-only" />
                                    </label>
                                    <p class="pl-1">o arrastrar y soltar</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 10MB</p>
                                <div v-if="imagePreview" class="mt-4">
                                    <img :src="imagePreview" alt="Vista previa" class="mx-auto h-32 w-32 object-cover rounded-lg border" />
                                </div>
                            </div>
                        </div>
                        <div v-if="form.errors.imagen" class="error-message">{{ form.errors.imagen }}</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button
                        type="button"
                        @click="$inertia.visit(route('productos.index'))"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else>Guardar Producto</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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

// Estado para las pestañas
const activeTab = ref('general');

// Vista previa de imagen
const imagePreview = ref(null);

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
    unidad_medida: 'Piezas',
    fecha_vencimiento: '',
    tipo_producto: 'fisico',
    imagen: null,
    estado: 'activo',
    comision_vendedor: '',
});

// Enviar formulario
const submit = () => {
    form.post(route('productos.store'), {
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
        },
    });
};

// Manejar carga de imágenes
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.imagen = file;

        // Crear vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<style scoped>
.input-field {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900;
}

.input-field option {
    @apply text-gray-900 bg-white;
}

.error-message {
    @apply mt-1 text-sm text-red-600;
}
</style>
