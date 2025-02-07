<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Crear Producto</h1>
        <!-- Formulario de creación de productos -->
        <form @submit.prevent="submit">
            <div v-if="form.errors.nombre" class="text-red-500">{{ form.errors.nombre }}</div>
            <div class="space-y-4">
                <!-- Nombre del Producto -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        id="nombre"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        @blur="convertirAMayusculas('nombre')"
                        required
                    />
                    <p v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</p>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea
                        v-model="form.descripcion"
                        id="descripcion"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        rows="3"
                    ></textarea>
                    <p v-if="form.errors.descripcion" class="text-red-500 text-sm">{{ form.errors.descripcion }}</p>
                </div>

                <!-- Precio -->
                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700">Precio</label>
                    <input
                        v-model="form.precio"
                        type="number"
                        id="precio"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    />
                    <p v-if="form.errors.precio" class="text-red-500 text-sm">{{ form.errors.precio }}</p>
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                    <input
                        v-model="form.stock"
                        type="number"
                        id="stock"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    />
                    <p v-if="form.errors.stock" class="text-red-500 text-sm">{{ form.errors.stock }}</p>
                </div>

                <!-- Categoría -->
                <div>
                    <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
                    <select
                        v-model="form.categoria"
                        id="categoria"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    >
                        <option value="" disabled>Selecciona una categoría</option>
                        <option v-for="categoria in categorias" :key="categoria" :value="categoria">{{ categoria }}</option>
                    </select>
                    <p v-if="form.errors.categoria" class="text-red-500 text-sm">{{ form.errors.categoria }}</p>
                </div>

                <!-- Código de Producto -->
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700">Código de Producto</label>
                    <input
                        v-model="form.codigo"
                        type="text"
                        id="codigo"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    />
                    <p v-if="form.errors.codigo" class="text-red-500 text-sm">{{ form.errors.codigo }}</p>
                </div>

                <!-- Imagen -->
                <div>
                    <!-- <label for="imagen" class="block text-sm font-medium text-gray-700">Imagen del Producto</label>
                    <input
                        v-model="form.imagen"
                        type="file"
                        id="imagen"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        @change="handleImageUpload"
                    /> -->
                    <p v-if="form.errors.imagen" class="text-red-500 text-sm">{{ form.errors.imagen }}</p>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

// Define el layout del dashboard
defineOptions({ layout: Dashboard });

// Listas predefinidas
const categorias = [
    'Electrónica',
    'Ropa',
    'Alimentos',
    'Accesorios',
    'Herramientas',
    'Muebles',
    // Agrega más categorías según sea necesario
];

// Formulario para crear un producto
const form = useForm({
    nombre: '',
    descripcion: '',
    precio: '',
    stock: '',
    categoria: '',
    codigo: '',
    imagen: null,
});

// Función para enviar el formulario
const submit = () => {
    form.post(route('productos.store'), {
        onSuccess: () => {
            form.reset(); // Limpia el formulario después de guardar
        },
    });
};

// Método para convertir a mayúsculas
const convertirAMayusculas = (campo) => {
    if (form[campo]) {
        form[campo] = form[campo].toUpperCase();
    }
};

// Función para manejar la carga de imágenes
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.imagen = file;
    }
};
</script>
