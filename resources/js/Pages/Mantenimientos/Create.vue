<template>
    <Head title="Crear Mantenimiento" />
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="bg-blue-500 p-3 rounded-lg mr-4">
                    <i class="fas fa-wrench text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Crear Mantenimiento</h1>
                    <p class="text-gray-600">Registra un nuevo servicio de mantenimiento para tu vehículo</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Selección de Carro -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-gray-700 text-sm font-semibold mb-3">
                        <i class="fas fa-car mr-2"></i>Seleccionar Vehículo
                    </label>
                    <select
                        v-model="form.carro_id"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        required
                        @change="updateKilometraje"
                    >
                        <option value="" disabled>Selecciona un vehículo</option>
                        <option v-for="carro in carros" :key="carro.id" :value="carro.id">
                            {{ carro.marca }} {{ carro.modelo }} {{ carro.año || '' }} - {{ formatNumber(carro.kilometraje) }} km
                        </option>
                    </select>
                    <div v-if="selectedCarro" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>Vehículo seleccionado: <strong>{{ selectedCarro.marca }} {{ selectedCarro.modelo }}</strong></span>
                        </div>
                        <div class="text-sm text-blue-700 mt-1">
                            Kilometraje actual: <strong>{{ formatNumber(selectedCarro.kilometraje) }} km</strong>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Tipo de Servicio -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-3">
                            <i class="fas fa-cogs mr-2"></i>Tipo de Servicio
                        </label>
                        <select
                            v-model="form.tipo"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            required
                            @change="handleServiceChange"
                        >
                            <option value="" disabled>Selecciona el tipo de servicio</option>
                            <option v-for="servicio in tiposServicio" :key="servicio.value" :value="servicio.value">
                                {{ servicio.label }}
                            </option>
                        </select>

                        <!-- Campo para "Otro servicio" -->
                        <div v-if="form.tipo === 'Otro servicio'" class="mt-3">
                            <input
                                v-model="form.otro_servicio"
                                type="text"
                                placeholder="Especifica el tipo de servicio..."
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                required
                            >
                        </div>
                    </div>

                    <!-- Fecha del Servicio -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-3">
                            <i class="fas fa-calendar mr-2"></i>Fecha del Servicio
                        </label>
                        <input
                            v-model="form.fecha"
                            type="date"
                            :max="today"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            required
                        >
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Kilometraje -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-3">
                            <i class="fas fa-tachometer-alt mr-2"></i>Kilometraje del Servicio
                        </label>
                        <input
                            v-model="form.kilometraje"
                            type="number"
                            :min="selectedCarro?.kilometraje || 0"
                            placeholder="Ingresa el kilometraje actual"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            required
                        >
                        <p class="text-sm text-gray-500 mt-2 flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                            Debe ser mayor o igual al kilometraje actual del vehículo
                        </p>
                    </div>

                    <!-- Próximo Mantenimiento -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-3">
                            <i class="fas fa-calendar-plus mr-2"></i>Próximo Mantenimiento
                        </label>
                        <input
                            v-model="form.proximo_mantenimiento"
                            type="date"
                            :min="form.fecha || today"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            required
                        >
                        <div class="mt-2 flex space-x-2">
                            <button
                                type="button"
                                @click="calcularProximoMantenimiento(3)"
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors"
                            >
                                +3 meses
                            </button>
                            <button
                                type="button"
                                @click="calcularProximoMantenimiento(6)"
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors"
                            >
                                +6 meses
                            </button>
                            <button
                                type="button"
                                @click="calcularProximoMantenimiento(12)"
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors"
                            >
                                +1 año
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Costo del Servicio -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-3">
                            <i class="fas fa-dollar-sign mr-2"></i>Costo del Servicio (Opcional)
                        </label>
                        <input
                            v-model="form.costo"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        >
                    </div>

                    <!-- Taller/Lugar -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-3">
                            <i class="fas fa-map-marker-alt mr-2"></i>Taller/Lugar (Opcional)
                        </label>
                        <input
                            v-model="form.taller"
                            type="text"
                            placeholder="Nombre del taller o lugar"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        >
                    </div>
                </div>

                <!-- Notas -->
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-3">
                        <i class="fas fa-sticky-note mr-2"></i>Notas y Observaciones
                    </label>
                    <textarea
                        v-model="form.notas"
                        rows="4"
                        placeholder="Describe detalles del servicio, piezas cambiadas, observaciones, etc..."
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-y"
                    ></textarea>
                    <div class="flex justify-between text-sm text-gray-500 mt-1">
                        <span>Opcional</span>
                        <span>{{ form.notas.length }}/500 caracteres</span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <button
                        type="button"
                        @click="resetForm"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 flex items-center"
                    >
                        <i class="fas fa-undo mr-2"></i>
                        Limpiar Formulario
                    </button>

                    <div class="flex space-x-3">
                        <button
                            type="button"
                            @click="router.visit(route('mantenimientos.index'))"
                            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all duration-200 flex items-center"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-blue-300 transition-all duration-200 flex items-center min-w-[160px]"
                        >
                            <i v-if="!isSubmitting" class="fas fa-save mr-2"></i>
                            <i v-else class="fas fa-spinner fa-spin mr-2"></i>
                            {{ isSubmitting ? 'Guardando...' : 'Crear Mantenimiento' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive, computed, ref } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({ carros: Array });

const isSubmitting = ref(false);

const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: 'linear-gradient(135deg, #4caf50, #45a049)',
            icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' }
        },
        {
            type: 'error',
            background: 'linear-gradient(135deg, #f44336, #e53935)',
            icon: { className: 'fas fa-exclamation-triangle', tagName: 'i', color: '#fff' }
        },
        {
            type: 'warning',
            background: 'linear-gradient(135deg, #ff9800, #f57c00)',
            icon: { className: 'fas fa-exclamation-circle', tagName: 'i', color: '#fff' }
        },
    ],
});

const form = reactive({
    carro_id: '',
    tipo: '',
    otro_servicio: '',
    fecha: '',
    proximo_mantenimiento: '',
    notas: '',
    kilometraje: '',
    costo: '',
    taller: '',
});

// Tipos de servicio predefinidos
const tiposServicio = [
    { value: 'Cambio de aceite', label: '🛢️ Cambio de aceite' },
    { value: 'Revisión periódica', label: '🔍 Revisión periódica' },
    { value: 'Servicio de frenos', label: '🛑 Servicio de frenos' },
    { value: 'Servicio de llantas', label: '🛞 Servicio de llantas' },
    { value: 'Servicio de batería', label: '🔋 Servicio de batería' },
    { value: 'Servicio de motor', label: '⚙️ Servicio de motor' },
    { value: 'Revisión de luces', label: '💡 Revisión de luces' },
    { value: 'Alineación y balanceo', label: '⚖️ Alineación y balanceo' },
    { value: 'Cambio de filtros', label: '🔧 Cambio de filtros' },
    { value: 'Revisión de transmisión', label: '🔄 Revisión de transmisión' },
    { value: 'Otro servicio', label: '📝 Otro servicio' },
];

// Fecha de hoy
const today = computed(() => {
    return new Date().toISOString().split('T')[0];
});

// Carro seleccionado
const selectedCarro = computed(() => {
    return props.carros.find(carro => carro.id === form.carro_id);
});

// Formatear números
const formatNumber = (number) => {
    return new Intl.NumberFormat('es-ES').format(number);
};

// Función para actualizar el kilometraje cuando se selecciona un carro
const updateKilometraje = () => {
    if (selectedCarro.value) {
        form.kilometraje = selectedCarro.value.kilometraje;
    } else {
        form.kilometraje = '';
    }
};

const handleServiceChange = () => {
    if (form.tipo !== 'Otro servicio') {
        form.otro_servicio = '';
    }
};

// Calcular próximo mantenimiento
const calcularProximoMantenimiento = (meses) => {
    if (!form.fecha) {
        notyf.warning('Primero selecciona la fecha del servicio');
        return;
    }

    const fechaServicio = new Date(form.fecha);
    fechaServicio.setMonth(fechaServicio.getMonth() + meses);
    form.proximo_mantenimiento = fechaServicio.toISOString().split('T')[0];
};

// Validaciones
const validateForm = () => {
    const errors = [];

    if (!form.carro_id) errors.push('Debes seleccionar un vehículo');
    if (!form.tipo) errors.push('Debes seleccionar un tipo de servicio');
    if (form.tipo === 'Otro servicio' && !form.otro_servicio) errors.push('Debes especificar el tipo de servicio');
    if (!form.fecha) errors.push('Debes seleccionar la fecha del servicio');
    if (!form.proximo_mantenimiento) errors.push('Debes establecer la fecha del próximo mantenimiento');
    if (!form.kilometraje) errors.push('Debes ingresar el kilometraje');

    if (selectedCarro.value && parseInt(form.kilometraje) < selectedCarro.value.kilometraje) {
        errors.push(`El kilometraje debe ser mayor o igual a ${formatNumber(selectedCarro.value.kilometraje)} km`);
    }

    if (form.fecha > today.value) {
        errors.push('La fecha del servicio no puede ser futura');
    }

    if (form.proximo_mantenimiento <= form.fecha) {
        errors.push('La fecha del próximo mantenimiento debe ser posterior a la fecha del servicio');
    }

    if (form.notas.length > 500) {
        errors.push('Las notas no pueden exceder 500 caracteres');
    }

    return errors;
};

// Limpiar formulario
const resetForm = () => {
    Object.keys(form).forEach(key => {
        form[key] = '';
    });
    notyf.success('Formulario limpiado');
};

// Enviar formulario
const submit = async () => {
    const errors = validateForm();

    if (errors.length > 0) {
        errors.forEach(error => notyf.error(error));
        return;
    }

    isSubmitting.value = true;

    try {
        await router.post(route('mantenimientos.store'), form, {
            onSuccess: () => {
                notyf.success('¡Mantenimiento creado exitosamente!');
                resetForm();
            },
            onError: (error) => {
                console.error('Error al crear el mantenimiento:', error);

                if (error.message) {
                    notyf.error(error.message);
                } else {
                    notyf.error('Hubo un error al crear el mantenimiento');
                }
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado');
        isSubmitting.value = false;
    }
};

// Establecer fecha por defecto al montar el componente
if (!form.fecha) {
    form.fecha = today.value;
}
</script>

<style scoped>
/* Animaciones y estilos adicionales */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
    opacity: 0;
}

/* Efectos hover para inputs */
input:focus, select:focus, textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Estilos para botones pequeños */
button[type="button"]:hover {
    transform: translateY(-1px);
}
</style>
