<template>
    <Head title="Editar Mantenimiento" />
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Editar Mantenimiento</h1>
            <p class="text-gray-600 mt-2">Actualiza la información del mantenimiento del vehículo</p>
        </div>

        <!-- Formulario -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="px-8 py-6">
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Información del Vehículo -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Información del Vehículo
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Seleccionar Vehículo <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.carro_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                :class="{ 'border-red-500 ring-red-200': errors.carro_id }"
                                required
                            >
                                <option value="">Selecciona un vehículo</option>
                                <option
                                    v-for="carro in carros"
                                    :key="carro.id"
                                    :value="carro.id"
                                    class="py-2"
                                >
                                    {{ carro.marca }} {{ carro.modelo }} ({{ carro.anio }})
                                    <span v-if="carro.placa" class="text-gray-500"> - {{ carro.placa }}</span>
                                </option>
                            </select>
                            <p v-if="errors.carro_id" class="text-red-500 text-sm mt-1">{{ errors.carro_id }}</p>
                        </div>
                    </div>

                    <!-- Detalles del Servicio -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Detalles del Servicio
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Servicio <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.tipo"
                                    @change="handleServiceChange"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    :class="{ 'border-red-500 ring-red-200': errors.tipo }"
                                    required
                                >
                                    <option value="">Selecciona el tipo de servicio</option>
                                    <optgroup label="Servicios Generales">
                                        <option value="Revisión periódica">🔍 Revisión periódica</option>
                                        <option value="Cambio de aceite">🛢️ Cambio de aceite</option>
                                        <option value="Revisión de luces">💡 Revisión de luces</option>
                                    </optgroup>
                                    <optgroup label="Servicios Específicos">
                                        <option value="Servicio de frenos">🛑 Servicio de frenos</option>
                                        <option value="Servicio de llantas">🛞 Servicio de llantas</option>
                                        <option value="Servicio de batería">🔋 Servicio de batería</option>
                                        <option value="Servicio de motor">🔧 Servicio de motor</option>
                                        <option value="Servicio de transmisión">⚙️ Servicio de transmisión</option>
                                        <option value="Servicio de aire acondicionado">❄️ Aire acondicionado</option>
                                        <option value="Alineación y balanceo">📐 Alineación y balanceo</option>
                                    </optgroup>
                                    <optgroup label="Otros">
                                        <option value="Otro servicio">✨ Otro servicio</option>
                                    </optgroup>
                                </select>
                                <p v-if="errors.tipo" class="text-red-500 text-sm mt-1">{{ errors.tipo }}</p>
                            </div>

                            <!-- Campo condicional para otro servicio -->
                            <div v-if="form.tipo === 'Otro servicio'" class="transition-all duration-300">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Especifique el servicio <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.otro_servicio"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    :class="{ 'border-red-500 ring-red-200': errors.otro_servicio }"
                                    placeholder="Describe el tipo de servicio específico"
                                    required
                                >
                                <p v-if="errors.otro_servicio" class="text-red-500 text-sm mt-1">{{ errors.otro_servicio }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas y Programación -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Fechas y Programación
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha del Mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.fecha"
                                    type="date"
                                    :max="todayDate"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    :class="{ 'border-red-500 ring-red-200': errors.fecha }"
                                    required
                                >
                                <p v-if="errors.fecha" class="text-red-500 text-sm mt-1">{{ errors.fecha }}</p>
                                <p class="text-gray-500 text-xs mt-1">Fecha en que se realizó el mantenimiento</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Próximo Mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.proximo_mantenimiento"
                                    type="date"
                                    :min="todayDate"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    :class="{ 'border-red-500 ring-red-200': errors.proximo_mantenimiento }"
                                    required
                                >
                                <p v-if="errors.proximo_mantenimiento" class="text-red-500 text-sm mt-1">{{ errors.proximo_mantenimiento }}</p>
                                <p class="text-gray-500 text-xs mt-1">Fecha estimada para el siguiente mantenimiento</p>
                            </div>
                        </div>

                        <!-- Ayuda para calcular próximo mantenimiento -->
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">💡 Recomendaciones de intervalos:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-blue-700">
                                <div>• Cambio de aceite: cada 3-6 meses</div>
                                <div>• Revisión general: cada 6-12 meses</div>
                                <div>• Frenos: cada 12-18 meses</div>
                                <div>• Llantas: cada 6-12 meses</div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Observaciones y Notas
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notas del Mantenimiento
                            </label>
                            <textarea
                                v-model="form.notas"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 resize-none"
                                :class="{ 'border-red-500 ring-red-200': errors.notas }"
                                placeholder="Describe detalles del mantenimiento realizado, piezas cambiadas, observaciones importantes, etc."
                            ></textarea>
                            <p v-if="errors.notas" class="text-red-500 text-sm mt-1">{{ errors.notas }}</p>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-gray-500 text-xs">Información adicional sobre el mantenimiento</p>
                                <span class="text-xs text-gray-400">{{ form.notas?.length || 0 }}/500 caracteres</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <button
                            type="button"
                            @click="goBack"
                            class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                        >
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="px-8 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg shadow-sm hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
                        >
                            <span v-if="processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Actualizando Mantenimiento...
                            </span>
                            <span v-else class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Actualizar Mantenimiento
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
    mantenimiento: Object,
    carros: Array,
    errors: {
        type: Object,
        default: () => ({})
    }
});

// Configuración de Notyf
const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: '#10b981',
            icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' }
        },
        {
            type: 'error',
            background: '#ef4444',
            icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' }
        },
        {
            type: 'warning',
            background: '#f59e0b',
            icon: { className: 'fas fa-exclamation-triangle', tagName: 'i', color: '#fff' }
        }
    ],
});

// Variables reactivas
const form = reactive({
    carro_id: props.mantenimiento.carro_id || '',
    tipo: props.mantenimiento.tipo || '',
    otro_servicio: props.mantenimiento.otro_servicio || '',
    fecha: props.mantenimiento.fecha || '',
    proximo_mantenimiento: props.mantenimiento.proximo_mantenimiento || '',
    notas: props.mantenimiento.notas || '',
});

const processing = ref(false);
const errors = computed(() => props.errors || {});

// Fecha actual para validaciones
const todayDate = computed(() => {
    return new Date().toISOString().split('T')[0];
});

// Manejar cambio en el tipo de servicio
const handleServiceChange = () => {
    if (form.tipo !== 'Otro servicio') {
        form.otro_servicio = '';
    }
};

// Función para regresar
const goBack = () => {
    router.visit(route('mantenimientos.index'));
};

// Validar formulario antes del envío
const validateForm = () => {
    const validationErrors = [];

    if (!form.carro_id) {
        validationErrors.push('Debe seleccionar un vehículo');
    }

    if (!form.tipo) {
        validationErrors.push('Debe seleccionar un tipo de servicio');
    }

    if (form.tipo === 'Otro servicio' && !form.otro_servicio.trim()) {
        validationErrors.push('Debe especificar el tipo de servicio personalizado');
    }

    if (!form.fecha) {
        validationErrors.push('Debe seleccionar la fecha del mantenimiento');
    }

    if (!form.proximo_mantenimiento) {
        validationErrors.push('Debe seleccionar la fecha del próximo mantenimiento');
    }

    // Validar que la fecha del próximo mantenimiento sea posterior a la fecha actual
    if (form.fecha && form.proximo_mantenimiento) {
        if (new Date(form.proximo_mantenimiento) <= new Date(form.fecha)) {
            validationErrors.push('La fecha del próximo mantenimiento debe ser posterior a la fecha del mantenimiento actual');
        }
    }

    // Validar longitud de notas
    if (form.notas && form.notas.length > 500) {
        validationErrors.push('Las notas no pueden exceder 500 caracteres');
    }

    return validationErrors;
};

// Función para enviar el formulario
const submit = async () => {
    if (processing.value) return;

    // Validar formulario
    const validationErrors = validateForm();
    if (validationErrors.length > 0) {
        validationErrors.forEach(error => {
            notyf.error(error);
        });
        return;
    }

    processing.value = true;

    try {
        await router.put(route('mantenimientos.update', props.mantenimiento.id), form, {
            onSuccess: (page) => {
                notyf.success('¡El mantenimiento ha sido actualizado exitosamente!');
            },
            onError: (errors) => {
                console.error('Errores de validación:', errors);

                // Mostrar errores específicos
                const errorMessages = Object.values(errors).flat();
                if (errorMessages.length > 0) {
                    errorMessages.forEach(message => {
                        notyf.error(message);
                    });
                } else {
                    notyf.error('Hubo errores en el formulario. Por favor revisa los campos.');
                }
            },
            onFinish: () => {
                processing.value = false;
            }
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado. Por favor intenta de nuevo.');
        processing.value = false;
    }
};
</script>
