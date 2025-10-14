<template>
    <Head title="Crear Cita" />
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h1 class="text-2xl font-semibold mb-6 text-gray-800">Crear Cita</h1>

            <!-- Alertas globales -->
            <div v-if="hasGlobalErrors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error en el formulario</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li v-for="error in Object.values(form.errors)" :key="error">{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notificación de éxito -->
            <div v-if="showSuccessMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">¡Cita creada exitosamente!</h3>
                        <p class="text-sm text-green-700 mt-1">La cita ha sido guardada correctamente en el sistema.</p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Sección: Información del Cliente y Técnico -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Asignación</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Buscador de Cliente Mejorado -->
                        <div class="md:col-span-2">
                            <BuscarCliente
                                ref="buscarClienteRef"
                                :clientes="clientes"
                                :cliente-seleccionado="selectedCliente"
                                @cliente-seleccionado="onClienteSeleccionado"
                                @crear-nuevo-cliente="onCrearNuevoCliente"
                                label-busqueda="Cliente"
                                placeholder-busqueda="Buscar cliente por nombre, email, teléfono o RFC..."
                                :requerido="true"
                                titulo-cliente-seleccionado="Cliente Seleccionado"
                                mensaje-vacio="No hay cliente seleccionado"
                                submensaje-vacio="Busca y selecciona un cliente para continuar"
                                :mostrar-opcion-nuevo-cliente="true"
                                :mostrar-estado-cliente="true"
                                :mostrar-info-comercial="true"
                            />
                            <p v-if="form.errors.cliente_id" class="mt-1 text-sm text-red-600">{{ form.errors.cliente_id }}</p>
                        </div>

                        <FormField
                            v-model="form.tecnico_id"
                            label="Técnico"
                            type="select"
                            id="tecnico_id"
                            :options="tecnicosOptions"
                            :error="form.errors.tecnico_id"
                            required
                        />
                    </div>
                </div>

                <!-- Sección: Detalles del Servicio -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Detalles del Servicio</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Buscador de Servicios Mejorado -->
                        <div class="md:col-span-2">
                            <BuscarServicios
                                ref="buscarServiciosRef"
                                :servicios="servicios"
                                @servicio-seleccionado="onServicioSeleccionado"
                            />
                            <p v-if="form.errors.tipo_servicio" class="mt-1 text-sm text-red-600">{{ form.errors.tipo_servicio }}</p>
                        </div>

                        <FormField
                            v-model="form.fecha_hora"
                            label="Fecha y Hora"
                            type="datetime-local"
                            id="fecha_hora"
                            :error="form.errors.fecha_hora"
                            :min="minDateTime"
                            required
                        />

                        <FormField
                            v-model="form.estado"
                            label="Estado"
                            type="select"
                            id="estado"
                            :options="estadoOptions"
                            :error="form.errors.estado"
                            required
                        />

                        <FormField
                            v-model="form.prioridad"
                            label="Prioridad"
                            type="select"
                            id="prioridad"
                            :options="prioridadOptions"
                            :error="form.errors.prioridad"
                        />

                        <div class="md:col-span-2">
                            <FormField
                                v-model="form.descripcion"
                                label="Descripción del Servicio"
                                type="textarea"
                                id="descripcion"
                                :error="form.errors.descripcion"
                                placeholder="Descripción detallada del servicio a realizar..."
                                :rows="3"
                            />
                        </div>
                    </div>
                </div>

                <!-- Sección: Información del Equipo -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Información del Equipo</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <FormField
                            v-model="form.tipo_equipo"
                            label="Tipo de Equipo"
                            type="select"
                            id="tipo_equipo"
                            :options="tipoEquipoOptions"
                            :error="form.errors.tipo_equipo"
                            required
                        />

                        <FormField
                            v-model="form.marca_equipo"
                            label="Marca del Equipo"
                            type="text"
                            id="marca_equipo"
                            :error="form.errors.marca_equipo"
                            placeholder="Ej: Samsung, LG, Whirlpool..."
                            @blur="convertirAMayusculas('marca_equipo')"
                            required
                            :datalist="marcasComunes"
                        />

                        <FormField
                            v-model="form.modelo_equipo"
                            label="Modelo del Equipo"
                            type="text"
                            id="modelo_equipo"
                            :error="form.errors.modelo_equipo"
                            placeholder="Número de modelo..."
                            @blur="convertirAMayusculas('modelo_equipo')"
                            required
                        />

                        <FormField
                            v-model="form.numero_serie"
                            label="Número de Serie"
                            type="text"
                            id="numero_serie"
                            :error="form.errors.numero_serie"
                            placeholder="Número de serie del equipo..."
                            @blur="convertirAMayusculas('numero_serie')"
                        />

                        <FormField
                            v-model="form.garantia"
                            label="¿Tiene Garantía?"
                            type="select"
                            id="garantia"
                            :options="garantiaOptions"
                            :error="form.errors.garantia"
                        />

                        <FormField
                            v-model="form.fecha_compra"
                            label="Fecha de Compra"
                            type="date"
                            id="fecha_compra"
                            :error="form.errors.fecha_compra"
                            :max="todayDate"
                        />

                        <div class="lg:col-span-3">
                            <FormField
                                v-model="form.problema_reportado"
                                label="Problema Reportado"
                                type="textarea"
                                id="problema_reportado"
                                :error="form.errors.problema_reportado"
                                placeholder="Describa detalladamente el problema reportado por el cliente..."
                                :rows="3"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Sección: Información Adicional -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Información Adicional</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField
                            v-model="form.direccion_servicio"
                            label="Dirección del Servicio"
                            type="textarea"
                            id="direccion_servicio"
                            :error="form.errors.direccion_servicio"
                            placeholder="Dirección completa donde se realizará el servicio..."
                            :rows="2"
                        />

                        <FormField
                            v-model="form.observaciones"
                            label="Observaciones"
                            type="textarea"
                            id="observaciones"
                            :error="form.errors.observaciones"
                            placeholder="Observaciones adicionales, instrucciones especiales..."
                            :rows="2"
                        />

                        <FormField
                            v-model="form.costo_estimado"
                            label="Costo Estimado"
                            type="number"
                            id="costo_estimado"
                            :error="form.errors.costo_estimado"
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                        />

                        <FormField
                            v-model="form.tiempo_estimado"
                            label="Tiempo Estimado (horas)"
                            type="number"
                            id="tiempo_estimado"
                            :error="form.errors.tiempo_estimado"
                            placeholder="0.5"
                            step="0.5"
                            min="0.5"
                        />
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <div class="flex space-x-4">
                        <button
                            type="button"
                            @click="resetForm"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Limpiar Formulario
                        </button>

                        <button
                            type="button"
                            @click="saveDraft"
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Guardar Borrador
                        </button>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || !selectedCliente"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing" class="flex items-center">
                            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Guardando...
                        </span>
                        <span v-else class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Crear Cita
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormField from '@/Components/FormField.vue';
import BuscarCliente from '@/Components/CreateComponents/BuscarCliente.vue';
import BuscarServicios from '@/Components/CreateComponents/BuscarServicios.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    tecnicos: Array,
    clientes: Array,
    servicios: Array
});

// Referencias reactivas para el buscador de clientes
const selectedCliente = ref(null);
const showSuccessMessage = ref(false);

// Referencias a los componentes
const buscarClienteRef = ref(null);
const buscarServiciosRef = ref(null);

// Opciones de selección mejoradas
const tecnicosOptions = computed(() => [
    { value: '', text: 'Selecciona un técnico', disabled: true },
    ...props.tecnicos.map(tecnico => ({
        value: tecnico.id,
        text: `${tecnico.nombre} ${tecnico.apellido}`,
        disabled: false
    }))
]);

const tipoServicioOptions = [
    { value: '', text: 'Selecciona el tipo de servicio', disabled: true },
    { value: 'instalacion', text: 'Instalación' },
    { value: 'mantenimiento', text: 'Mantenimiento' },
    { value: 'diagnostico', text: 'Diagnóstico' },
    { value: 'reparacion', text: 'Reparación' },
    { value: 'mantenimiento', text: 'Mantenimiento' },
    { value: 'garantia', text: 'Garantía' },
    { value: 'revision', text: 'Revisión' },
    { value: 'otro_servicio', text: 'Otro Servicio' }
];

const estadoOptions = [
    { value: '', text: 'Selecciona el estado', disabled: true },
    { value: 'pendiente', text: 'Pendiente' },
    { value: 'programado', text: 'Programado' },
    { value: 'en_proceso', text: 'En Proceso' },
    { value: 'completado', text: 'Completado' },
    { value: 'cancelado', text: 'Cancelado' },
    { value: 'reprogramado', text: 'Reprogramado' }
];

const prioridadOptions = [
    { value: '', text: 'Selecciona la prioridad', disabled: true },
    { value: 'baja', text: 'Baja' },
    { value: 'normal', text: 'Normal' },
    { value: 'alta', text: 'Alta' },
    { value: 'urgente', text: 'Urgente' }
];

const tipoEquipoOptions = [
    { value: '', text: 'Selecciona el tipo de equipo', disabled: true },
    { value: 'minisplit', text: 'Minisplit / Aire Acondicionado' },
    { value: 'boiler', text: 'Boiler / Calentador' },
    { value: 'refrigerador', text: 'Refrigerador' },
    { value: 'lavadora', text: 'Lavadora' },
    { value: 'secadora', text: 'Secadora' },
    { value: 'estufa', text: 'Estufa' },
    { value: 'horno', text: 'Horno' },
    { value: 'campana', text: 'Campana Extractora' },
    { value: 'horno_de_microondas', text: 'Horno de Microondas' },
    { value: 'lavavajillas', text: 'Lavavajillas' },
    { value: 'licuadora', text: 'Licuadora' },
    { value: 'ventilador', text: 'Ventilador' },
    { value: 'otro_equipo', text: 'Otro Equipo' }
];

const garantiaOptions = [
    { value: '', text: 'Selecciona opción', disabled: true },
    { value: 'si', text: 'Sí, tiene garantía' },
    { value: 'no', text: 'No tiene garantía' },
    { value: 'no_seguro', text: 'No está seguro' }
];

const marcasComunes = [
    'SAMSUNG', 'LG', 'WHIRLPOOL', 'MABE', 'FRIGIDAIRE', 'GE', 'BOSCH',
    'ELECTROLUX', 'CARRIER', 'YORK', 'TRANE', 'RHEEM', 'CALOREX'
];

// Fechas para validación
const todayDate = computed(() => {
    return new Date().toISOString().split('T')[0];
});

const minDateTime = computed(() => {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    return now.toISOString().slice(0, 16);
});

// Funciones para manejo del nuevo componente BuscarCliente
const onClienteSeleccionado = (cliente) => {
    selectedCliente.value = cliente;
    form.cliente_id = cliente ? cliente.id : '';

    // Auto-llenar dirección si existe
    if (cliente && cliente.direccion) {
        form.direccion_servicio = cliente.direccion;
    }
};

const onCrearNuevoCliente = (nombreBuscado) => {
    // Abrir ventana para crear nuevo cliente
    window.open(route('clientes.create'), '_blank');
};

// Función para manejo del componente BuscarServicios
const onServicioSeleccionado = (servicio) => {
    if (servicio) {
        // Auto-llenar campos relacionados con el servicio seleccionado
        form.tipo_servicio = servicio.nombre;
        form.descripcion = servicio.descripcion || '';

        // Si el servicio tiene precio, establecerlo como costo estimado
        if (servicio.precio) {
            form.costo_estimado = servicio.precio;
        }

        showTemporaryMessage(`Servicio seleccionado: ${servicio.nombre}`, 'success');
    }
};

// Formulario usando useForm de Inertia con campos adicionales
const form = useForm({
    tecnico_id: '',
    cliente_id: '',
    tipo_servicio: '',
    fecha_hora: '',
    descripcion: '',
    tipo_equipo: '',
    marca_equipo: '',
    modelo_equipo: '',
    numero_serie: '',
    problema_reportado: '',
    estado: 'pendiente',
    prioridad: 'normal',
    garantia: '',
    fecha_compra: '',
    direccion_servicio: '',
    observaciones: '',
    costo_estimado: '',
    tiempo_estimado: '',
    evidencias: '',
    foto_equipo: null,
    foto_hoja_servicio: null,
    foto_identificacion: null,
});

// Computed para errores globales
const hasGlobalErrors = computed(() => {
    return Object.keys(form.errors).length > 0;
});

// Función para limpiar cliente seleccionado
const clearClienteSelection = () => {
    selectedCliente.value = null;
    form.cliente_id = '';
};

// Funciones de utilidad
const convertirAMayusculas = (campo) => {
    if (form[campo]) {
        form[campo] = form[campo].toString().toUpperCase().trim();
    }
};

const saveDraft = () => {
    const draftData = {
        ...form.data(),
        selectedCliente: selectedCliente.value,
        timestamp: new Date().toISOString()
    };

    try {
        sessionStorage.setItem('citaDraft', JSON.stringify(draftData));
        showTemporaryMessage('Borrador guardado correctamente', 'success');
    } catch (error) {
        console.error('Error al guardar borrador:', error);
        showTemporaryMessage('Error al guardar borrador', 'error');
    }
};

const loadDraft = () => {
    try {
        const draftData = sessionStorage.getItem('citaDraft');
        if (draftData) {
            const parsed = JSON.parse(draftData);

            // Verificar que el borrador no sea muy antiguo (más de 24 horas)
            const draftDate = new Date(parsed.timestamp);
            const now = new Date();
            const hoursDiff = (now - draftDate) / (1000 * 60 * 60);

            if (hoursDiff < 24) {
                // Cargar datos del formulario
                Object.keys(form.data()).forEach(key => {
                    if (parsed[key] !== undefined && key !== 'foto_equipo' && key !== 'foto_hoja_servicio' && key !== 'foto_identificacion') {
                        form[key] = parsed[key];
                    }
                });

                // Cargar cliente seleccionado usando el nuevo componente
                if (parsed.selectedCliente) {
                    selectedCliente.value = parsed.selectedCliente;
                    // El componente BuscarCliente se actualizará automáticamente con el cliente seleccionado
                }

                showTemporaryMessage('Borrador cargado correctamente', 'info');
            } else {
                // Limpiar borrador antiguo
                sessionStorage.removeItem('citaDraft');
            }
        }
    } catch (error) {
        console.error('Error al cargar borrador:', error);
        sessionStorage.removeItem('citaDraft');
    }
};

const showTemporaryMessage = (message, type) => {
    // Crear elemento de notificación temporal
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
};

const resetForm = () => {
    form.reset();
    form.estado = 'pendiente';
    form.prioridad = 'normal';

    // Limpiar selección de cliente
    clearClienteSelection();

    // Limpiar los componentes de búsqueda
    if (buscarClienteRef.value) {
        buscarClienteRef.value.limpiarBusqueda();
    }
    if (buscarServiciosRef.value) {
        // El componente BuscarServicios no tiene método limpiarBusqueda, pero podemos resetear la búsqueda
        // buscarServiciosRef.value.busqueda = '';
    }

    // Restablecer fecha y hora actual
    const now = new Date();
    const offset = now.getTimezoneOffset();
    now.setMinutes(now.getMinutes() - offset);
    form.fecha_hora = now.toISOString().slice(0, 16);

    // Limpiar borrador
    sessionStorage.removeItem('citaDraft');

    showTemporaryMessage('Formulario limpiado', 'info');
};

const validateForm = () => {
    const errors = [];

    if (!selectedCliente.value || !form.cliente_id) {
        errors.push('Debe seleccionar un cliente');
    }

    if (!form.tecnico_id) {
        errors.push('Debe seleccionar un técnico');
    }

    if (!form.tipo_servicio || form.tipo_servicio.trim() === '') {
        errors.push('Debe seleccionar o especificar el tipo de servicio');
    }

    if (!form.fecha_hora) {
        errors.push('Debe especificar la fecha y hora');
    }

    if (!form.tipo_equipo) {
        errors.push('Debe seleccionar el tipo de equipo');
    }

    if (!form.marca_equipo) {
        errors.push('Debe especificar la marca del equipo');
    }

    if (!form.modelo_equipo) {
        errors.push('Debe especificar el modelo del equipo');
    }

    if (!form.problema_reportado) {
        errors.push('Debe describir el problema reportado');
    }

    // Validar fecha no sea en el pasado (excepto hoy)
    const selectedDate = new Date(form.fecha_hora);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
        errors.push('La fecha de la cita no puede ser anterior a hoy');
    }

    return errors;
};

const submit = () => {
    // Validar formulario antes de enviar
    const validationErrors = validateForm();

    if (validationErrors.length > 0) {
        showTemporaryMessage(`Errores de validación: ${validationErrors.join(', ')}`, 'error');
        return;
    }

    const formData = new FormData();

    // Agregar todos los campos del formulario
    for (const key in form.data()) {
        if (key === 'foto_equipo' || key === 'foto_hoja_servicio' || key === 'foto_identificacion') {
            // Solo agregar archivos si están seleccionados
            if (form[key]) {
                formData.append(key, form[key]);
            }
        } else {
            formData.append(key, form[key] || '');
        }
    }

    form.post(route('citas.store'), {
        data: formData,
        preserveScroll: true,
        onStart: () => {
            // Limpiar mensajes anteriores
            showSuccessMessage.value = false;
        },
        onSuccess: () => {
            showSuccessMessage.value = true;

            // Limpiar borrador después del éxito
            sessionStorage.removeItem('citaDraft');

            // Limpiar formulario después de 2 segundos
            setTimeout(() => {
                resetForm();
                showSuccessMessage.value = false;
            }, 3000);

            // Scroll hacia arriba
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        onError: (errors) => {
            console.error('Error al crear la cita:', errors);

            // Scroll hacia el primer error
            setTimeout(() => {
                const firstErrorElement = document.querySelector('.text-red-500, .border-red-300');
                if (firstErrorElement) {
                    firstErrorElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 100);

            showTemporaryMessage('Error al crear la cita. Revisa los campos marcados.', 'error');
        },
    });
};

// Auto-guardar borrador cada 30 segundos
let autoSaveInterval;

onMounted(() => {
    // Iniciar auto-guardado
    autoSaveInterval = setInterval(() => {
        if (form.isDirty && (selectedCliente.value || form.cliente_id)) {
            saveDraft();
        }
    }, 30000); // 30 segundos
});

// Limpiar interval al desmontar componente
onUnmounted(() => {
    if (autoSaveInterval) {
        clearInterval(autoSaveInterval);
    }
});

// Detectar cuando el usuario intenta salir de la página sin guardar
window.addEventListener('beforeunload', (e) => {
    if (form.isDirty && !form.processing) {
        e.preventDefault();
        e.returnValue = '¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.';
        return e.returnValue;
    }
});
</script>
