<template>
    <Head title="Crear Servicio" />
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo Servicio</h1>
                        <p class="mt-2 text-gray-600">Completa la información para agregar un nuevo servicio</p>
                    </div>
                    <Link
                        :href="route('servicios.index')"
                        class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 shadow-sm"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </Link>
                </div>
            </div>

            <!-- Form Container -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit" class="divide-y divide-gray-200">
                    <!-- Form Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-concierge-bell text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Información del Servicio</h3>
                                <p class="text-sm text-gray-500">Ingresa los detalles del nuevo servicio</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Columna Izquierda -->
                            <div class="space-y-6">
                                <!-- Nombre -->
                                <div>
                                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-tag text-gray-400 mr-2"></i>
                                            Nombre del Servicio
                                            <span class="text-red-500 ml-1">*</span>
                                        </span>
                                    </label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        id="nombre"
                                        placeholder="Ej: Corte de cabello clásico"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                            form.errors.nombre ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                    />
                                    <div v-if="form.errors.nombre" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.nombre }}
                                    </div>
                                </div>

                                <!-- Código -->
                                <div>
                                    <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-barcode text-gray-400 mr-2"></i>
                                            Código del Servicio
                                            <span class="text-red-500 ml-1">*</span>
                                        </span>
                                    </label>
                                    <input
                                        v-model="form.codigo"
                                        type="text"
                                        id="codigo"
                                        placeholder="Ej: CORTE-001"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                            form.errors.codigo ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                        @input="generarCodigo"
                                    />
                                    <div v-if="form.errors.codigo" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.codigo }}
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">El código debe ser único e identificativo</p>
                                </div>

                                <!-- Categoría -->
                                <div>
                                    <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-layer-group text-gray-400 mr-2"></i>
                                            Categoría
                                            <span class="text-red-500 ml-1">*</span>
                                        </span>
                                    </label>
                                    <select
                                        v-model="form.categoria_id"
                                        id="categoria_id"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                            form.errors.categoria_id ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                    >
                                        <option value="">Selecciona una categoría</option>
                                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                            {{ categoria.nombre }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.categoria_id" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.categoria_id }}
                                    </div>
                                </div>

                                <!-- Precio y Duración en una fila -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Precio -->
                                    <div>
                                        <label for="precio" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center">
                                                <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>
                                                Precio
                                                <span class="text-red-500 ml-1">*</span>
                                            </span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                            <input
                                                v-model="form.precio"
                                                type="number"
                                                step="0.01"
                                                id="precio"
                                                placeholder="0.00"
                                                :class="[
                                                    'w-full pl-8 pr-4 py-3 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                                    form.errors.precio ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                                ]"
                                                required
                                                min="0"
                                            />
                                        </div>
                                        <div v-if="form.errors.precio" class="mt-2 flex items-center text-sm text-red-600">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            {{ form.errors.precio }}
                                        </div>
                                    </div>

                                    <!-- Duración -->
                                    <div>
                                        <label for="duracion" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center">
                                                <i class="fas fa-clock text-gray-400 mr-2"></i>
                                                Duración
                                                <span class="text-red-500 ml-1">*</span>
                                            </span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="form.duracion"
                                                type="number"
                                                id="duracion"
                                                placeholder="60"
                                                :class="[
                                                    'w-full px-4 py-3 pr-16 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                                    form.errors.duracion ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                                ]"
                                                required
                                                min="1"
                                            />
                                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">min</span>
                                        </div>
                                        <div v-if="form.errors.duracion" class="mt-2 flex items-center text-sm text-red-600">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            {{ form.errors.duracion }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha -->
                            <div class="space-y-6">
                                <!-- Descripción -->
                                <div>
                                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-align-left text-gray-400 mr-2"></i>
                                            Descripción
                                        </span>
                                    </label>
                                    <textarea
                                        v-model="form.descripcion"
                                        id="descripcion"
                                        placeholder="Describe detalladamente el servicio que se ofrece..."
                                        rows="6"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent hover:border-gray-400 resize-none"
                                    ></textarea>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ form.descripcion.length }}/500 caracteres
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-toggle-on text-gray-400 mr-2"></i>
                                            Estado del Servicio
                                            <span class="text-red-500 ml-1">*</span>
                                        </span>
                                    </label>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <input
                                                v-model="form.estado"
                                                type="radio"
                                                id="activo"
                                                value="activo"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                            />
                                            <label for="activo" class="ml-3 flex items-center cursor-pointer">
                                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                                <span class="text-sm font-medium text-gray-700">Activo</span>
                                                <span class="ml-2 text-xs text-gray-500">El servicio estará disponible para reservas</span>
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input
                                                v-model="form.estado"
                                                type="radio"
                                                id="inactivo"
                                                value="inactivo"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                                            />
                                            <label for="inactivo" class="ml-3 flex items-center cursor-pointer">
                                                <span class="w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                                                <span class="text-sm font-medium text-gray-700">Inactivo</span>
                                                <span class="ml-2 text-xs text-gray-500">El servicio no estará disponible</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.estado" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.estado }}
                                    </div>
                                </div>

                                <!-- Vista Previa -->
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-eye text-gray-400 mr-2"></i>
                                        Vista Previa
                                    </h4>
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h5 class="font-medium text-gray-900">
                                                    {{ form.nombre || 'Nombre del servicio' }}
                                                </h5>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ form.codigo || 'CODIGO-000' }}
                                                </p>
                                                <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                                                    {{ form.descripcion || 'Descripción del servicio...' }}
                                                </p>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <div class="text-lg font-bold text-gray-900">
                                                    ${{ form.precio || '0.00' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ form.duracion || '0' }} min
                                                </div>
                                                <span :class="form.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-2">
                                                    {{ form.estado || 'activo' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            Los campos marcados con <span class="text-red-500">*</span> son obligatorios
                        </div>
                        <div class="flex items-center space-x-4">
                            <Link
                                :href="route('servicios.index')"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                <i v-else class="fas fa-save mr-2"></i>
                                {{ form.processing ? 'Guardando...' : 'Crear Servicio' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tips Section -->
            <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-blue-900 mb-2">Consejos para crear un buen servicio</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                            <div class="flex items-start">
                                <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                <span>Usa nombres descriptivos y claros</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                <span>Incluye detalles importantes en la descripción</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                <span>Asigna códigos únicos y organizados</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                <span>Define precios competitivos y justos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
    categorias: Array,
});

// Notification setup
const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: 'linear-gradient(135deg, #10b981, #059669)',
            icon: {
                className: 'fas fa-check',
                tagName: 'i',
                color: 'white'
            }
        },
        {
            type: 'error',
            background: 'linear-gradient(135deg, #ef4444, #dc2626)',
            icon: {
                className: 'fas fa-exclamation-triangle',
                tagName: 'i',
                color: 'white'
            }
        }
    ]
});

// Form setup
const form = useForm({
    nombre: '',
    descripcion: '',
    codigo: '',
    categoria_id: '',
    precio: '',
    duracion: '',
    estado: 'activo',
});

// Methods
const generarCodigo = () => {
    // Auto-generate code based on name if code is empty
    if (!form.codigo && form.nombre) {
        const codigo = form.nombre
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '')
            .substring(0, 10) + '-' + Math.floor(Math.random() * 100).toString().padStart(3, '0');
        form.codigo = codigo;
    }
};

const submit = () => {
    form.post(route('servicios.store'), {
        onSuccess: () => {
            notyf.success('¡Servicio creado exitosamente!');
            form.reset();
        },
        onError: () => {
            notyf.error('Error al crear el servicio. Revisa los datos ingresados.');
        }
    });
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
