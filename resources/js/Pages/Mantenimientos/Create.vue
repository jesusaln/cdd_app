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
                        <div class="grid grid-cols-2 gap-2 text-sm text-blue-700 mt-2">
                            <div>Kilometraje actual: <strong>{{ formatNumber(selectedCarro.kilometraje) }} km</strong></div>
                            <div v-if="selectedCarro.año">Año: <strong>{{ selectedCarro.año }}</strong></div>
                        </div>
                        <div v-if="selectedCarro.taller_preferido" class="text-sm text-blue-600 mt-1">
                            <i class="fas fa-wrench mr-1"></i>
                            Taller preferido: {{ selectedCarro.taller_preferido }}
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
                            v-model="form.kilometraje_actual"
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

                        <!-- Sugerencias contextuales basadas en kilometraje -->
                        <div v-if="selectedCarro && form.tipo" class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="text-sm text-yellow-800">
                                <i class="fas fa-lightbulb mr-2"></i>
                                <strong>Sugerencias para {{ selectedCarro.marca }} {{ selectedCarro.modelo }}:</strong>
                            </div>
                            <div class="text-xs text-yellow-700 mt-1">
                                {{ getSugerenciasContextuales() }}
                            </div>
                        </div>
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
                        <div class="flex gap-2">
                            <input
                                v-model="form.costo"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            >
                            <button
                                v-if="form.tipo && getCostoSugerido() > 0"
                                type="button"
                                @click="form.costo = getCostoSugerido()"
                                class="px-3 py-3 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium"
                                title="Usar costo sugerido"
                            >
                                💰 Sugerido
                            </button>
                        </div>
                        <p v-if="form.tipo && getCostoSugerido() > 0" class="text-xs text-gray-500 mt-1">
                            Costo sugerido para {{ form.tipo }}: ${{ formatNumber(getCostoSugerido()) }}
                        </p>
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

                <!-- Configuración de Alertas y Prioridad -->
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-bell mr-2"></i>
                        Configuración de Alertas y Prioridad
                    </h3>

                    <div class="grid md:grid-cols-3 gap-4">
                        <!-- Prioridad -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Prioridad
                            </label>
                            <select
                                v-model="form.prioridad"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                required
                            >
                                <option value="baja">🟢 Baja</option>
                                <option value="media">🔵 Media</option>
                                <option value="alta">🟠 Alta</option>
                                <option value="critica">🔴 Crítica</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ getDescripcionPrioridad(form.prioridad) }}
                            </p>
                        </div>

                        <!-- Días de Anticipación -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-3">
                                <i class="fas fa-clock mr-2"></i>Días de Anticipación
                            </label>
                            <input
                                v-model="form.dias_anticipacion_alerta"
                                type="number"
                                min="1"
                                max="365"
                                :placeholder="getDiasAnticipacionSugeridos()"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">
                                Días antes para enviar alerta
                            </p>
                        </div>

                        <!-- Requiere Aprobación -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-3">
                                <i class="fas fa-check-circle mr-2"></i>Requiere Aprobación
                            </label>
                            <div class="flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        v-model="form.requiere_aprobacion"
                                        type="checkbox"
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">
                                        {{ form.requiere_aprobacion ? 'Sí' : 'No' }}
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Si necesita aprobación especial
                            </p>
                        </div>
                    </div>

                    <!-- Observaciones de Alerta -->
                    <div class="mt-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-3">
                            <i class="fas fa-sticky-note mr-2"></i>Observaciones de Alerta (Opcional)
                        </label>
                        <textarea
                            v-model="form.observaciones_alerta"
                            rows="2"
                            placeholder="Notas adicionales para la alerta..."
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-y"
                            maxlength="500"
                        ></textarea>
                        <div class="flex justify-end text-sm text-gray-500 mt-1">
                            <span>{{ form.observaciones_alerta.length }}/500 caracteres</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen del Mantenimiento -->
                <div v-if="form.carro_id && form.tipo && form.fecha" class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Resumen del Mantenimiento
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <strong>Vehículo:</strong> {{ selectedCarro?.marca }} {{ selectedCarro?.modelo }}
                        </div>
                        <div>
                            <strong>Tipo de Servicio:</strong> {{ form.tipo === 'Otro servicio' ? form.otro_servicio : form.tipo }}
                        </div>
                        <div>
                            <strong>Fecha del Servicio:</strong> {{ new Date(form.fecha).toLocaleDateString('es-MX') }}
                        </div>
                        <div>
                            <strong>Próximo Mantenimiento:</strong> {{ form.proximo_mantenimiento ? new Date(form.proximo_mantenimiento).toLocaleDateString('es-MX') : 'No definido' }}
                        </div>
                        <div>
                            <strong>Prioridad:</strong>
                            <span :class="getClasesPrioridad(form.prioridad)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ml-2">
                                {{ getLabelPrioridad(form.prioridad) }}
                            </span>
                        </div>
                        <div>
                            <strong>Días de Anticipación:</strong> {{ form.dias_anticipacion_alerta }} días
                        </div>
                        <div v-if="form.costo">
                            <strong>Costo:</strong> ${{ formatNumber(form.costo) }}
                        </div>
                        <div v-if="form.taller">
                            <strong>Taller:</strong> {{ form.taller }}
                        </div>
                    </div>
                    <div v-if="form.proximo_mantenimiento" class="mt-3 p-3 bg-green-100 rounded-lg">
                        <div class="text-sm text-green-800">
                            <i class="fas fa-bell mr-2"></i>
                            <strong>Alerta programada:</strong> Se enviará una notificación {{ form.dias_anticipacion_alerta }} días antes del próximo mantenimiento
                            ({{ new Date(form.proximo_mantenimiento).toLocaleDateString('es-MX') }})
                        </div>
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
    kilometraje_actual: '',
    costo: '',
    taller: '',
    prioridad: 'media', // Valor por defecto
    dias_anticipacion_alerta: 30, // Valor por defecto
    requiere_aprobacion: false,
    observaciones_alerta: '',
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
        form.kilometraje_actual = selectedCarro.value.kilometraje;

        // Auto-llenar taller si el vehículo tiene uno preferido
        if (selectedCarro.value.taller_preferido && !form.taller) {
            form.taller = selectedCarro.value.taller_preferido;
        }
    } else {
        form.kilometraje_actual = '';
        form.taller = '';
    }
};

const handleServiceChange = () => {
    if (form.tipo !== 'Otro servicio') {
        form.otro_servicio = '';
    }

    // Auto-completar todos los campos de manera inteligente
    autoCompletarCamposInteligente();

    // Mostrar notificación de ayuda
    if (form.tipo && form.tipo !== 'Otro servicio') {
        const config = {
            'Cambio de aceite': { prioridad: 'media', dias: 90, costo: 800 },
            'Revisión periódica': { prioridad: 'media', dias: 180, costo: 1200 },
            'Servicio de frenos': { prioridad: 'alta', dias: 180, costo: 2500 },
            'Servicio de llantas': { prioridad: 'media', dias: 365, costo: 600 },
            'Servicio de batería': { prioridad: 'alta', dias: 730, costo: 1800 },
            'Servicio de motor': { prioridad: 'alta', dias: 365, costo: 3500 },
            'Revisión de luces': { prioridad: 'baja', dias: 180, costo: 300 },
            'Alineación y balanceo': { prioridad: 'media', dias: 180, costo: 800 },
            'Cambio de filtros': { prioridad: 'media', dias: 180, costo: 400 },
            'Revisión de transmisión': { prioridad: 'critica', dias: 365, costo: 2000 },
            'Otro servicio': { prioridad: 'media', dias: 30, costo: 0 }
        };

        const servicio = config[form.tipo];
        if (servicio) {
            notyf.success(`Configuración aplicada: Prioridad ${servicio.prioridad}, ${servicio.dias} días de anticipación, costo sugerido $${servicio.costo}`);
        }
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

    // Validaciones básicas de campos requeridos
    if (!form.carro_id) errors.push('Debes seleccionar un vehículo');
    if (!form.tipo) errors.push('Debes seleccionar un tipo de servicio');
    if (form.tipo === 'Otro servicio' && !form.otro_servicio) errors.push('Debes especificar el tipo de servicio');
    if (!form.fecha) errors.push('Debes seleccionar la fecha del servicio');
    if (!form.proximo_mantenimiento) errors.push('Debes establecer la fecha del próximo mantenimiento');
    if (!form.kilometraje_actual) errors.push('Debes ingresar el kilometraje actual');
    if (!form.prioridad) errors.push('Debes seleccionar una prioridad');
    if (!form.dias_anticipacion_alerta) errors.push('Debes especificar los días de anticipación para alertas');

    // Validaciones de integridad de datos
    const erroresIntegridad = verificarIntegridadDatos();
    errors.push(...erroresIntegridad);

    // Validaciones de lógica de negocio
    if (selectedCarro.value && parseInt(form.kilometraje_actual) < selectedCarro.value.kilometraje) {
        errors.push(`El kilometraje debe ser mayor o igual a ${formatNumber(selectedCarro.value.kilometraje)} km (kilometraje actual del vehículo)`);
    }

    if (form.fecha > today.value) {
        errors.push('La fecha del servicio no puede ser futura');
    }

    if (form.proximo_mantenimiento <= form.fecha) {
        errors.push('La fecha del próximo mantenimiento debe ser posterior a la fecha del servicio');
    }

    // Validar que los días de anticipación estén en un rango razonable
    const diasAnticipacion = parseInt(form.dias_anticipacion_alerta);
    if (diasAnticipacion < 1) {
        errors.push('Los días de anticipación deben ser al menos 1 día');
    }
    if (diasAnticipacion > 365) {
        errors.push('Los días de anticipación no pueden ser más de 365 días');
    }

    // Validar que el costo sea positivo si se proporciona
    if (form.costo && parseFloat(form.costo) < 0) {
        errors.push('El costo no puede ser negativo');
    }

    // Validaciones de longitud de texto
    if (form.notas.length > 500) {
        errors.push('Las notas no pueden exceder 500 caracteres');
    }

    if (form.observaciones_alerta.length > 500) {
        errors.push('Las observaciones de alerta no pueden exceder 500 caracteres');
    }

    if (form.otro_servicio.length > 100) {
        errors.push('La descripción del servicio personalizado no puede exceder 100 caracteres');
    }

    if (form.taller.length > 100) {
        errors.push('El nombre del taller no puede exceder 100 caracteres');
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

    // Log de debug para ver los datos que se envían
    const datosAEnviar = {
        ...form,
        requiere_aprobacion: Boolean(form.requiere_aprobacion),
        costo: form.costo ? Number(form.costo) : 0
    };

    console.log('Datos a enviar:', datosAEnviar);

    // Verificar campos requeridos
    const camposRequeridos = ['carro_id', 'tipo', 'fecha', 'proximo_mantenimiento', 'kilometraje_actual', 'prioridad', 'dias_anticipacion_alerta'];
    const camposFaltantes = camposRequeridos.filter(campo => !datosAEnviar[campo]);

    if (camposFaltantes.length > 0) {
        notyf.error(`Campos requeridos faltantes: ${camposFaltantes.join(', ')}`);
        isSubmitting.value = false;
        return;
    }

    // Mostrar resumen de validación antes de enviar
    const resumen = `
    📋 Resumen del mantenimiento:
    • Vehículo: ${selectedCarro.value?.marca} ${selectedCarro.value?.modelo}
    • Servicio: ${form.tipo === 'Otro servicio' ? form.otro_servicio : form.tipo}
    • Fecha: ${new Date(form.fecha).toLocaleDateString('es-MX')}
    • Próximo: ${new Date(form.proximo_mantenimiento).toLocaleDateString('es-MX')}
    • Kilometraje: ${formatNumber(form.kilometraje_actual)} km
    • Prioridad: ${getLabelPrioridad(form.prioridad)}
    • Días de alerta: ${form.dias_anticipacion_alerta}
    ${form.costo ? `• Costo: $${formatNumber(form.costo)}` : ''}
    ${form.taller ? `• Taller: ${form.taller}` : ''}
    `;

    console.log('Resumen de validación:', resumen);

    try {
        await router.post(route('mantenimientos.store'), form, {
            onSuccess: () => {
                notyf.success('¡Mantenimiento creado exitosamente!');
                resetForm();
            },
            onError: (error) => {
                console.error('Error al crear el mantenimiento:', error);

                // Mostrar errores específicos de validación
                if (error.kilometraje_actual) {
                    notyf.error('Kilometraje: ' + error.kilometraje_actual);
                }
                if (error.prioridad) {
                    notyf.error('Prioridad: ' + error.prioridad);
                }
                if (error.dias_anticipacion_alerta) {
                    notyf.error('Días de anticipación: ' + error.dias_anticipacion_alerta);
                }
                if (error.general) {
                    notyf.error('Error general: ' + error.general);
                }

                // Si no hay errores específicos, mostrar mensaje genérico
                if (!error.kilometraje_actual && !error.prioridad && !error.dias_anticipacion_alerta && !error.general) {
                    notyf.error('Hubo un error al crear el mantenimiento. Verifica los datos e intenta nuevamente.');
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

// Funciones para manejar prioridades y alertas
const getDescripcionPrioridad = (prioridad) => {
    const descripciones = {
        'baja': 'Mantenimiento rutinario, no urgente',
        'media': 'Mantenimiento importante, programar pronto',
        'alta': 'Mantenimiento crítico, requiere atención prioritaria',
        'critica': 'Mantenimiento urgente, requiere atención inmediata'
    };
    return descripciones[prioridad] || 'Selecciona una prioridad';
};

const getClasesPrioridad = (prioridad) => {
    const clases = {
        'baja': 'bg-green-100 text-green-700 border-green-200',
        'media': 'bg-blue-100 text-blue-700 border-blue-200',
        'alta': 'bg-orange-100 text-orange-700 border-orange-200',
        'critica': 'bg-red-100 text-red-700 border-red-200'
    };
    return clases[prioridad] || 'bg-gray-100 text-gray-700 border-gray-200';
};

const getLabelPrioridad = (prioridad) => {
    const labels = {
        'baja': 'Baja',
        'media': 'Media',
        'alta': 'Alta',
        'critica': 'Crítica'
    };
    return labels[prioridad] || 'Media';
};

const getDiasAnticipacionSugeridos = () => {
    const sugerencias = {
        'Cambio de aceite': 30,
        'Revisión periódica': 60,
        'Servicio de frenos': 90,
        'Servicio de llantas': 180,
        'Servicio de batería': 180,
        'Servicio de motor': 120,
        'Revisión de luces': 30,
        'Alineación y balanceo': 180,
        'Cambio de filtros': 60,
        'Revisión de transmisión': 120,
        'Otro servicio': 30
    };

    return sugerencias[form.tipo] || 30;
};

const getCostoSugerido = () => {
    const costos = {
        'Cambio de aceite': 800,
        'Revisión periódica': 1200,
        'Servicio de frenos': 2500,
        'Servicio de llantas': 600,
        'Servicio de batería': 1800,
        'Servicio de motor': 3500,
        'Revisión de luces': 300,
        'Alineación y balanceo': 800,
        'Cambio de filtros': 400,
        'Revisión de transmisión': 2000,
        'Otro servicio': 0
    };

    return costos[form.tipo] || 0;
};

// Auto-llenar costo sugerido
const autoLlenarCosto = () => {
    if (form.tipo && !form.costo) {
        const costoSugerido = getCostoSugerido();
        if (costoSugerido > 0) {
            form.costo = costoSugerido;
        }
    }
};

// Auto-ajustar días de anticipación según el tipo de servicio
const autoAjustarDiasAnticipacion = () => {
    if (form.tipo && !form.dias_anticipacion_alerta) {
        form.dias_anticipacion_alerta = getDiasAnticipacionSugeridos();
    }
};

// Auto-ajustar prioridad según el tipo de servicio
const autoAjustarPrioridad = () => {
    if (form.tipo && !form.prioridad) {
        const prioridadesAutomaticas = {
            'Cambio de aceite': 'media',
            'Revisión periódica': 'media',
            'Servicio de frenos': 'alta',
            'Servicio de llantas': 'media',
            'Servicio de batería': 'alta',
            'Servicio de motor': 'alta',
            'Revisión de luces': 'baja',
            'Alineación y balanceo': 'media',
            'Cambio de filtros': 'media',
            'Revisión de transmisión': 'critica',
            'Otro servicio': 'media'
        };

        if (prioridadesAutomaticas[form.tipo]) {
            form.prioridad = prioridadesAutomaticas[form.tipo];
        }
    }
};

// Función para verificar la integridad de los datos
const verificarIntegridadDatos = () => {
    const errores = [];

    // Verificar que el kilometraje sea numérico y válido
    if (form.kilometraje_actual && isNaN(parseFloat(form.kilometraje_actual))) {
        errores.push('El kilometraje debe ser un número válido');
    }

    // Verificar que el costo sea numérico si se proporciona
    if (form.costo && isNaN(parseFloat(form.costo))) {
        errores.push('El costo debe ser un número válido');
    }

    // Verificar que los días de anticipación sean numéricos
    if (form.dias_anticipacion_alerta && isNaN(parseInt(form.dias_anticipacion_alerta))) {
        errores.push('Los días de anticipación deben ser un número válido');
    }

    // Verificar fechas válidas
    if (form.fecha && !Date.parse(form.fecha)) {
        errores.push('La fecha del servicio no es válida');
    }

    if (form.proximo_mantenimiento && !Date.parse(form.proximo_mantenimiento)) {
        errores.push('La fecha del próximo mantenimiento no es válida');
    }

    // Verificar que la fecha del próximo mantenimiento sea posterior
    if (form.fecha && form.proximo_mantenimiento) {
        const fechaServicio = new Date(form.fecha);
        const fechaProximo = new Date(form.proximo_mantenimiento);

        if (fechaProximo <= fechaServicio) {
            errores.push('La fecha del próximo mantenimiento debe ser posterior a la fecha del servicio');
        }
    }

    return errores;
};

// Función para auto-completar todos los campos de manera inteligente
const autoCompletarCamposInteligente = () => {
    if (!form.tipo) return;

    const configuracionesServicio = {
        'Cambio de aceite': {
            prioridad: 'media',
            dias: 90,
            costo: 800,
            descripcion: 'Mantenimiento preventivo básico del motor'
        },
        'Revisión periódica': {
            prioridad: 'media',
            dias: 180,
            costo: 1200,
            descripcion: 'Inspección completa del vehículo'
        },
        'Servicio de frenos': {
            prioridad: 'alta',
            dias: 180,
            costo: 2500,
            descripcion: 'Mantenimiento crítico para seguridad'
        },
        'Servicio de llantas': {
            prioridad: 'media',
            dias: 365,
            costo: 600,
            descripcion: 'Rotación y revisión de neumáticos'
        },
        'Servicio de batería': {
            prioridad: 'alta',
            dias: 730,
            costo: 1800,
            descripcion: 'Pruebas y posible reemplazo de batería'
        },
        'Servicio de motor': {
            prioridad: 'alta',
            dias: 365,
            costo: 3500,
            descripcion: 'Mantenimiento mayor del sistema motor'
        },
        'Revisión de luces': {
            prioridad: 'baja',
            dias: 180,
            costo: 300,
            descripcion: 'Verificación del sistema eléctrico'
        },
        'Alineación y balanceo': {
            prioridad: 'media',
            dias: 180,
            costo: 800,
            descripcion: 'Ajuste de suspensión y dirección'
        },
        'Cambio de filtros': {
            prioridad: 'media',
            dias: 180,
            costo: 400,
            descripcion: 'Reemplazo de filtros de aire, aceite y combustible'
        },
        'Revisión de transmisión': {
            prioridad: 'critica',
            dias: 365,
            costo: 2000,
            descripcion: 'Mantenimiento especializado de transmisión'
        },
        'Otro servicio': {
            prioridad: 'media',
            dias: 30,
            costo: 0,
            descripcion: 'Servicio personalizado'
        }
    };

    const config = configuracionesServicio[form.tipo];
    if (config) {
        // Solo auto-completar si el campo está vacío
        if (!form.prioridad) form.prioridad = config.prioridad;
        if (!form.dias_anticipacion_alerta) form.dias_anticipacion_alerta = config.dias;
        if (!form.costo) form.costo = config.costo;

        // Agregar observaciones automáticas si no hay
        if (!form.observaciones_alerta && config.descripcion) {
            form.observaciones_alerta = `${config.descripcion}. Revisar manual del vehículo para especificaciones.`;
        }

        // Calcular fecha del próximo mantenimiento automáticamente
        if (form.fecha && !form.proximo_mantenimiento) {
            const fechaActual = new Date(form.fecha);
            fechaActual.setDate(fechaActual.getDate() + config.dias);
            form.proximo_mantenimiento = fechaActual.toISOString().split('T')[0];
        }
    }
};

// Función para obtener sugerencias contextuales basadas en el vehículo y servicio
const getSugerenciasContextuales = () => {
    if (!selectedCarro.value || !form.tipo) return '';

    const kilometraje = selectedCarro.value.kilometraje;
    const sugerencias = [];

    // Sugerencias basadas en kilometraje
    if (kilometraje < 10000) {
        sugerencias.push('Vehículo nuevo: considerar mantenimientos preventivos básicos');
    } else if (kilometraje < 50000) {
        sugerencias.push('Vehículo en período de garantía: verificar servicios recomendados');
    } else if (kilometraje < 100000) {
        sugerencias.push('Vehículo maduro: considerar revisiones más frecuentes');
    } else {
        sugerencias.push('Vehículo con alto kilometraje: priorizar mantenimientos preventivos');
    }

    // Sugerencias específicas por tipo de servicio
    const sugerenciasServicio = {
        'Cambio de aceite': 'Recomendado cada 5,000-10,000 km según el aceite utilizado',
        'Servicio de frenos': 'Revisar cada 10,000-15,000 km o si hay ruidos anormales',
        'Servicio de llantas': 'Rotación cada 8,000-10,000 km, reemplazo según desgaste',
        'Servicio de batería': 'Revisar cada 6 meses, reemplazar cada 2-3 años',
        'Revisión de transmisión': 'Servicio mayor cada 60,000-100,000 km',
        'Cambio de filtros': 'Cada 10,000-15,000 km o según condiciones de manejo'
    };

    if (sugerenciasServicio[form.tipo]) {
        sugerencias.push(sugerenciasServicio[form.tipo]);
    }

    return sugerencias.join('. ');
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
