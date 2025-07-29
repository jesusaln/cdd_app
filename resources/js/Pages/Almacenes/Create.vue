<template>
    <Head title="Crear Almacén" />
    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-10 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900 leading-tight">Crear Nuevo Almacén</h1>
                    <p class="mt-2 text-lg text-gray-600">Define un nuevo espacio físico para gestionar tu inventario.</p>
                </div>
                <Link
                    :href="route('almacenes.index')"
                    class="inline-flex items-center px-5 py-2.5 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 hover:border-gray-400 transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    aria-label="Volver a la lista de almacenes"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </Link>
            </div>

            <!-- Error Alert for General Errors -->
            <div v-if="hasGeneralError" class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">Error al procesar la solicitud</h3>
                        <p class="mt-1 text-sm text-red-700">{{ generalErrorMessage }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm" class="divide-y divide-gray-200" novalidate>
                    <!-- Form Header -->
                    <div class="px-8 py-5 bg-gradient-to-br from-blue-50 to-indigo-50 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-warehouse text-blue-700 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-xl font-semibold text-gray-900">Información del Almacén</h3>
                                <p class="mt-1 text-sm text-gray-500">Ingresa los detalles principales para tu nuevo centro de almacenamiento.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <!-- Nombre Field -->
                                <div>
                                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-warehouse-alt text-gray-400 mr-2"></i>
                                            Nombre del Almacén
                                            <span class="text-red-500 ml-1">*</span>
                                        </span>
                                    </label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        id="nombre"
                                        name="nombre"
                                        placeholder="Ej: Almacén Principal, Bodega Sur"
                                        maxlength="100"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-blue-300 focus:border-blue-500 shadow-sm',
                                            form.errors.nombre ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                        aria-required="true"
                                        :aria-invalid="!!form.errors.nombre"
                                        :aria-describedby="form.errors.nombre ? 'nombre-error' : 'nombre-help'"
                                        @input="clearFieldError('nombre')"
                                    />
                                    <div id="nombre-help" class="mt-1 text-xs text-gray-500">
                                        {{ form.nombre.length }}/100 caracteres. Solo letras, números, espacios, guiones y guión bajo.
                                    </div>
                                    <div v-if="form.errors.nombre" id="nombre-error" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.nombre }}
                                    </div>
                                </div>

                                <!-- Ubicación Field -->
                                <div>
                                    <label for="ubicacion" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                            Ubicación
                                            <span class="text-red-500 ml-1">*</span>
                                        </span>
                                    </label>
                                    <input
                                        v-model="form.ubicacion"
                                        type="text"
                                        id="ubicacion"
                                        name="ubicacion"
                                        placeholder="Ej: Calle Principal #123, Ciudad, Estado"
                                        maxlength="255"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-blue-300 focus:border-blue-500 shadow-sm',
                                            form.errors.ubicacion ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                        aria-required="true"
                                        :aria-invalid="!!form.errors.ubicacion"
                                        :aria-describedby="form.errors.ubicacion ? 'ubicacion-error' : 'ubicacion-help'"
                                        @input="clearFieldError('ubicacion')"
                                    />
                                    <div id="ubicacion-help" class="mt-1 text-xs text-gray-500">
                                        {{ form.ubicacion.length }}/255 caracteres. Mínimo 5 caracteres.
                                    </div>
                                    <div v-if="form.errors.ubicacion" id="ubicacion-error" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.ubicacion }}
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- Descripción Field -->
                                <div>
                                    <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-align-left text-gray-400 mr-2"></i>
                                            Descripción
                                            <span class="text-gray-400 text-xs ml-2">(Opcional)</span>
                                        </span>
                                    </label>
                                    <textarea
                                        v-model="form.descripcion"
                                        id="descripcion"
                                        name="descripcion"
                                        placeholder="Detalles sobre el propósito o características especiales del almacén, por ejemplo: 'Almacén principal para productos terminados, con control de temperatura'."
                                        rows="6"
                                        maxlength="1000"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-blue-300 focus:border-blue-500 hover:border-gray-400 resize-none shadow-sm',
                                            form.errors.descripcion ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                        ]"
                                        :aria-invalid="!!form.errors.descripcion"
                                        :aria-describedby="form.errors.descripcion ? 'descripcion-error' : 'descripcion-help'"
                                        @input="clearFieldError('descripcion')"
                                    ></textarea>
                                    <div id="descripcion-help" class="mt-1 text-xs text-gray-500 text-right">
                                        {{ form.descripcion.length }}/1000 caracteres
                                    </div>
                                    <div v-if="form.errors.descripcion" id="descripcion-error" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.descripcion }}
                                    </div>
                                </div>

                                <!-- Preview Card -->
                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200 shadow-inner">
                                    <h4 class="text-base font-semibold text-gray-700 mb-4 flex items-center">
                                        <i class="fas fa-eye text-gray-500 mr-2"></i>
                                        Vista Previa de la Tarjeta de Almacén
                                    </h4>
                                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-md">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h5 class="font-bold text-lg text-gray-900 truncate">
                                                    {{ form.nombre || 'Nombre del Almacén' }}
                                                </h5>
                                                <p class="text-sm text-gray-600 mt-2 line-clamp-3">
                                                    {{ form.descripcion || 'Una descripción sobre los productos que contendrá este almacén y su propósito. Ayuda a organizar tu inventario.' }}
                                                </p>
                                                <p class="text-sm text-gray-500 mt-2 flex items-center">
                                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                                    {{ form.ubicacion || 'Ubicación del Almacén' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div class="px-8 py-5 bg-gray-50 flex items-center justify-between border-t border-gray-200">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                            Los campos marcados con <span class="text-red-500 ml-1 font-bold">*</span> son obligatorios.
                        </div>
                        <div class="flex items-center space-x-4">
                            <Link
                                :href="route('almacenes.index')"
                                class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                                aria-label="Cancelar y volver"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing || !isFormValid"
                                class="px-8 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transform hover:-translate-y-0.5"
                                aria-label="Crear almacén"
                            >
                                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                <i v-else class="fas fa-save mr-2"></i>
                                {{ form.processing ? 'Guardando...' : 'Crear Almacén' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tips Section -->
            <div class="mt-10 bg-blue-50 rounded-3xl p-7 border border-blue-200 shadow-md">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-blue-600 text-2xl mt-1"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-xl font-bold text-blue-900 mb-3">Consejos para crear un buen almacén</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-base text-blue-800">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Asigna un nombre claro que lo distinga de otros almacenes.</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Proporciona una descripción concisa de su función o tipo de productos.</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Registra la ubicación física exacta para facilitar la logística.</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Considera si será un almacén temporal o permanente.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Notification setup
const notyf = new Notyf({
    duration: 4500,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: 'linear-gradient(135deg, #10b981, #059669)',
            icon: { className: 'fas fa-check', tagName: 'i', color: 'white' }
        },
        {
            type: 'error',
            background: 'linear-gradient(135deg, #ef4444, #dc2626)',
            icon: { className: 'fas fa-exclamation-triangle', tagName: 'i', color: 'white' }
        },
        {
            type: 'warning',
            background: 'linear-gradient(135deg, #f59e0b, #d97706)',
            icon: { className: 'fas fa-exclamation-circle', tagName: 'i', color: 'white' }
        }
    ]
});

// Access Inertia page props
const page = usePage();

// Form setup with proper defaults
const form = useForm({
    nombre: '',
    descripcion: '',
    ubicacion: '',
});

// Computed properties
const isFormValid = computed(() => {
    return form.nombre.trim().length >= 2 &&
           form.ubicacion.trim().length >= 5 &&
           form.nombre.length <= 100 &&
           form.ubicacion.length <= 255 &&
           form.descripcion.length <= 1000;
});

const hasGeneralError = computed(() => {
    return form.errors.error || page.props.errors?.error;
});

const generalErrorMessage = computed(() => {
    return form.errors.error || page.props.errors?.error || 'Ha ocurrido un error inesperado.';
});

// Watch for flash messages
watchEffect(() => {
    if (page.props.flash?.success) {
        notyf.success(page.props.flash.success);
    }
    if (page.props.flash?.error) {
        notyf.error(page.props.flash.error);
    }
});

// Methods
const clearFieldError = (field) => {
    if (form.errors[field]) {
        form.clearErrors(field);
    }
};

const validateForm = () => {
    let isValid = true;
    const errors = {};

    // Validate nombre
    const nombre = form.nombre.trim();
    if (!nombre) {
        errors.nombre = 'El nombre del almacén es obligatorio.';
        isValid = false;
    } else if (nombre.length < 2) {
        errors.nombre = 'El nombre debe tener al menos 2 caracteres.';
        isValid = false;
    } else if (nombre.length > 100) {
        errors.nombre = 'El nombre no puede exceder los 100 caracteres.';
        isValid = false;
    } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-_]+$/.test(nombre)) {
        errors.nombre = 'El nombre solo puede contener letras, números, espacios, guiones y guión bajo.';
        isValid = false;
    }

    // Validate ubicacion
    const ubicacion = form.ubicacion.trim();
    if (!ubicacion) {
        errors.ubicacion = 'La ubicación es obligatoria.';
        isValid = false;
    } else if (ubicacion.length < 5) {
        errors.ubicacion = 'La ubicación debe tener al menos 5 caracteres.';
        isValid = false;
    } else if (ubicacion.length > 255) {
        errors.ubicacion = 'La ubicación no puede exceder los 255 caracteres.';
        isValid = false;
    }

    // Validate descripcion (optional)
    if (form.descripcion && form.descripcion.length > 1000) {
        errors.descripcion = 'La descripción no puede exceder los 1000 caracteres.';
        isValid = false;
    }

    if (!isValid) {
        form.clearErrors();
        Object.keys(errors).forEach(key => {
            form.setError(key, errors[key]);
        });
    }

    return isValid;
};

const submitForm = () => {
    // Clear any existing errors
    form.clearErrors();

    // Client-side validation
    if (!validateForm()) {
        notyf.error('Por favor, corrige los errores en el formulario.');
        return;
    }

    // Submit the form
    form.post(route('almacenes.store'), {
        onStart: () => {
            // Optional: Show loading state
        },
        onSuccess: () => {
            // Success will be handled by flash message watcher
            form.reset();
        },
        onError: (errors) => {
            // Handle validation errors from server
            if (errors.error) {
                notyf.error(errors.error);
            } else {
                // Count field errors
                const fieldErrors = Object.keys(errors).filter(key => key !== 'error').length;
                if (fieldErrors > 0) {
                    notyf.error(`Se encontraron ${fieldErrors} error${fieldErrors !== 1 ? 'es' : ''} en el formulario.`);
                }
            }
        },
        onFinish: () => {
            // Optional: Hide loading state
        }
    });
};
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Loading state for form submission */
.form-submitting {
    pointer-events: none;
    opacity: 0.7;
}

/* Smooth transitions for error states */
.error-transition {
    transition: border-color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}
</style>
