<template>
    <Head title="Editar Marca" />
    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900 leading-tight">Editar Marca</h1>
                    <p class="mt-2 text-lg text-gray-600">
                        Actualiza la información de la marca: <span class="font-semibold text-teal-700">{{ marca.nombre }}</span>
                    </p>
                </div>
                <Link
                    :href="route('marcas.index')"
                    class="inline-flex items-center px-5 py-2.5 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 hover:border-gray-400 transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </Link>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit" class="divide-y divide-gray-200">
                    <div class="px-8 py-5 bg-gradient-to-br from-teal-50 to-emerald-50 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-building text-teal-700 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-xl font-semibold text-gray-900">Información de la Marca</h3>
                                <p class="mt-1 text-sm text-gray-500">Modifica los detalles principales de esta marca de productos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                            <div class="space-y-6">
                                <div>
                                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-tag text-gray-400 mr-2"></i>
                                            Nombre de la Marca
                                            <span class="text-red-500 ml-1">*</span>
                                        </span>
                                    </label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        id="nombre"
                                        placeholder="Ej: L'Oréal, Revlon, Wahl"
                                        :class="[
                                            'w-full px-4 py-3 border rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-teal-300 focus:border-teal-500 shadow-sm',
                                            form.errors.nombre ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                        required
                                    />
                                    <div v-if="form.errors.nombre" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.nombre }}
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
                                        placeholder="Describe brevemente la filosofía o los productos clave de esta marca. Por ejemplo: 'Marca líder en productos para el cuidado del cabello y la piel, conocida por su innovación y calidad.'"
                                        rows="6"
                                        maxlength="500"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl transition-all duration-300 ease-in-out focus:ring-3 focus:ring-teal-300 focus:border-teal-500 hover:border-gray-400 resize-none shadow-sm"
                                    ></textarea>
                                    <div class="mt-1 text-xs text-gray-500 text-right">
                                        {{ form.descripcion.length }}/500 caracteres
                                    </div>
                                    <div v-if="form.errors.descripcion" class="mt-2 flex items-center text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                        {{ form.errors.descripcion }}
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200 shadow-inner">
                                    <h4 class="text-base font-semibold text-gray-700 mb-4 flex items-center">
                                        <i class="fas fa-eye text-gray-500 mr-2"></i>
                                        Vista Previa de la Tarjeta de Marca
                                    </h4>
                                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-md">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h5 class="font-bold text-lg text-gray-900 truncate">
                                                    {{ form.nombre || 'Nombre de la Marca' }}
                                                </h5>
                                                <p class="text-sm text-gray-600 mt-2 line-clamp-3">
                                                    {{ form.descripcion || 'Una descripción concisa de la marca, sus productos o su enfoque principal.' }}
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
                            <i class="fas fa-info-circle mr-2 text-teal-600"></i>
                            Los campos marcados con <span class="text-red-500 ml-1 font-bold">*</span> son obligatorios.
                        </div>
                        <div class="flex items-center space-x-4">
                            <Link
                                :href="route('marcas.index')"
                                class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-8 py-2.5 bg-gradient-to-r from-teal-600 to-teal-700 text-white font-semibold rounded-xl hover:from-teal-700 hover:to-teal-800 focus:ring-4 focus:ring-teal-300 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transform hover:-translate-y-0.5"
                            >
                                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                <i v-else class="fas fa-save mr-2"></i>
                                {{ form.processing ? 'Actualizando...' : 'Actualizar Marca' }}
                            </button>
                        </div>
                    </div>
                </form>
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

// Notification setup
const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: 'linear-gradient(135deg, #10b981, #059669)', // Tailwind `emerald-600` to `emerald-800`
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

// Recibe los datos de la marca como prop
const props = defineProps({
    marca: Object,
});

// Inicializa el formulario con los datos de la marca
const form = useForm({
    nombre: props.marca.nombre,
    descripcion: props.marca.descripcion || '',
});

// Función para enviar el formulario
const submit = () => {
    form.put(route('marcas.update', props.marca.id), {
        onSuccess: () => {
            notyf.success('¡Marca actualizada exitosamente!');
            // You might want to redirect after update, or just show the success message
            // router.visit(route('marcas.index'));
        },
        onError: (errors) => {
            notyf.error('Error al actualizar la marca. Por favor, revisa los datos ingresados.');
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
