<template>
    <Head title="Editar Almacén" />
    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900 leading-tight">Editar Almacén</h1>
                    <p class="mt-2 text-lg text-gray-600">Modifica la información de este almacén existente.</p>
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

            <div class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit" class="divide-y divide-gray-200">
                    <div class="px-8 py-5 bg-gradient-to-br from-blue-50 to-indigo-50 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-edit text-blue-700 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-xl font-semibold text-gray-900">Información del Almacén</h3>
                                <p class="mt-1 text-sm text-gray-500">Actualiza los detalles del almacén seleccionado.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                            <div class="space-y-6">
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
                                        placeholder="Ej: Almacén Principal, Bodega Sur"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-blue-300 focus:border-blue-500 shadow-sm',
                                            form.errors.nombre ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                        aria-required="true"
                                        aria-invalid="true"
                                        :aria-describedby="form.errors.nombre ? 'nombre-error' : null"
                                    />
                                    <div v-if="form.errors.nombre" id="nombre-error" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.nombre }}
                                    </div>
                                </div>

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
                                        placeholder="Ej: Calle Principal #123, Ciudad"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-blue-300 focus:border-blue-500 shadow-sm',
                                            form.errors.ubicacion ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                        aria-required="true"
                                        aria-invalid="true"
                                        :aria-describedby="form.errors.ubicacion ? 'ubicacion-error' : null"
                                    />
                                    <div v-if="form.errors.ubicacion" id="ubicacion-error" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.ubicacion }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-align-left text-gray-400 mr-2"></i>
                                            Descripción
                                        </span>
                                    </label>
                                    <textarea
                                        v-model="form.descripcion"
                                        id="descripcion"
                                        placeholder="Detalles sobre el propósito o características especiales del almacén, por ejemplo: 'Almacén principal para productos terminados, con control de temperatura'."
                                        rows="6"
                                        maxlength="500"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-blue-300 focus:border-blue-500 hover:border-gray-400 resize-none shadow-sm"
                                        :aria-describedby="form.errors.descripcion ? 'descripcion-error' : null"
                                    ></textarea>
                                    <div class="mt-1 text-xs text-gray-500 text-right">
                                        {{ form.descripcion.length }}/500 caracteres
                                    </div>
                                    <div v-if="form.errors.descripcion" id="descripcion-error" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.descripcion }}
                                    </div>
                                </div>

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
                                :disabled="form.processing"
                                class="px-8 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transform hover:-translate-y-0.5"
                                aria-label="Actualizar almacén"
                            >
                                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                <i v-else class="fas fa-save mr-2"></i>
                                {{ form.processing ? 'Actualizando...' : 'Actualizar Almacén' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-10 bg-blue-50 rounded-3xl p-7 border border-blue-200 shadow-md">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-lightbulb text-blue-600 text-2xl mt-1"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-xl font-bold text-blue-900 mb-3">Consejos para editar un almacén</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-base text-blue-800">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Asegúrate de que los cambios reflejen el propósito actual del almacén.</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Considera cómo esta edición impactará los productos almacenados.</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Mantén la descripción concisa y relevante.</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-2 mt-1 flex-shrink-0"></i>
                                <span>Revisa la vista previa antes de guardar los cambios.</span>
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
import { watchEffect } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Notification setup (customized for blue/indigo theme)
const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: 'linear-gradient(135deg, #3b82f6, #2563eb)', // Tailwind `blue-500` to `blue-700`
            icon: {
                className: 'fas fa-check',
                tagName: 'i',
                color: 'white'
            }
        },
        {
            type: 'error',
            background: 'linear-gradient(135deg, #ef4444, #dc2626)', // Tailwind `red-500` to `red-700`
            icon: {
                className: 'fas fa-exclamation-triangle',
                tagName: 'i',
                color: 'white'
            }
        }
    ]
});

// Access Inertia page props for flash messages
const page = usePage();

// Watch for flash messages and show notifications
watchEffect(() => {
    if (page.props.flash?.success) {
        notyf.success(page.props.flash.success);
    }
    if (page.props.flash?.error) {
        notyf.error(page.props.flash.error);
    }
});

// Recibe los datos del almacén como prop
const props = defineProps({
    almacen: Object,
});

// Inicializa el formulario con los datos del almacén
const form = useForm({
    nombre: props.almacen.nombre,
    descripcion: props.almacen.descripcion || '',
    ubicacion: props.almacen.ubicacion,
});

// Función para enviar el formulario
const submit = () => {
    form.put(route('almacenes.update', props.almacen.id), {
        onSuccess: () => {
            // Notyf will handle success message via watchEffect
        },
        onError: () => {
            // Notyf will handle error message via watchEffect based on form.errors
            // If there's a generic error not tied to a specific field,
            // you might want a fallback here or handle it in the backend flash message.
        },
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
</style>
