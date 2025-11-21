<template>
    <Head title="Editar Cita" />
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h1 class="text-2xl font-semibold mb-6 text-gray-800">Editar Cita #{{ cita.id }}</h1>

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
                        <h3 class="text-sm font-medium text-green-800">¡Cita actualizada exitosamente!</h3>
                        <p class="text-sm text-green-700 mt-1">Los cambios han sido guardados correctamente.</p>
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
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Detalles del Servicios</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                        <FormField
                            v-model="form.tipo_servicio"
                            label="Tipo de Servicio"
                            type="select"
                            id="tipo_servicio"
                            :options="tipoServicioOptions"
                            :error="form.errors.tipo_servicio"
                            required
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

                    </div>
                </div>

                <!-- Sección: Productos de la Cita -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Productos de la Cita</h2>
                    <div class="space-y-4">
                        <BuscarProducto
                            ref="buscarProductoRef"
                            :productos="productos || []"
                            :servicios="servicios || []"
                            :validar-stock="true"
                            label="Buscar Productos y Servicios para Venta"
                            placeholder="Buscar productos y servicios por nombre, código, categoría o descripción..."
                            texto-todos="Todos"
                            texto-productos="Productos"
                            texto-servicios="Servicios"
                            @agregar-producto="onAgregarItem"
                        />

                        <div v-if="selectedItems.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto/Servicio</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento (%)</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in selectedItems" :key="`${item.tipo}-${item.id}`">
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ getItemName(item) }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-right">
                                            <input type="number" min="1" class="w-20 text-right border rounded px-2 py-1"
                                                   v-model.number="quantities[`${item.tipo}-${item.id}`]" @input="updateQuantity(`${item.tipo}-${item.id}`, quantities[`${item.tipo}-${item.id}`])" />
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-right">
                                            <input type="number" min="0" step="0.01" class="w-24 text-right border rounded px-2 py-1"
                                                   v-model.number="prices[`${item.tipo}-${item.id}`]" @input="updatePrice(`${item.tipo}-${item.id}`, prices[`${item.tipo}-${item.id}`])" />
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-right">
                                            <input type="number" min="0" max="100" step="0.01" class="w-20 text-right border rounded px-2 py-1"
                                                   v-model.number="discounts[`${item.tipo}-${item.id}`]" @input="updateDiscount(`${item.tipo}-${item.id}`, discounts[`${item.tipo}-${item.id}`])" />
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-right">
                                            {{ calculateSubtotal(item) }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button" class="text-red-600 hover:underline" @click="removeItem(item)">Quitar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totales -->
                        <div v-if="selectedItems.length > 0" class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium">Subtotal:</span>
                                    <span>${{ totales.subtotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Descuento Items:</span>
                                    <span>-${{ totales.descuentoItems.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Subtotal con Descuentos:</span>
                                    <span>${{ totales.subtotalConDescuentos.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Descuento General:</span>
                                    <input type="number" min="0" max="100" step="0.01" class="w-20 text-right border rounded px-2 py-1"
                                           v-model.number="form.descuento_general" @input="updateDescuentoGeneral" />
                                    <span>%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">IVA (16%):</span>
                                    <span>${{ totales.iva.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total:</span>
                                    <span>${{ totales.total.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Documentación Fotográfica -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Documentación Fotográfica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Foto del Equipo -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="foto_equipo">
                                Foto del Equipo
                            </label>
                            <div class="relative">
                                <div v-if="form.foto_equipo_url" class="mb-3">
                                    <img
                                        :src="form.foto_equipo_url"
                                        alt="Foto del Equipo"
                                        class="w-full h-32 object-cover rounded-lg border border-gray-300 shadow-sm"
                                    >
                                    <button
                                        type="button"
                                        @click="clearFile('foto_equipo')"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                    >
                                        ×
                                    </button>
                                </div>
                                <input
                                    type="file"
                                    @change="handleFileChange('foto_equipo', $event)"
                                    accept="image/*"
                                    id="foto_equipo"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.foto_equipo }"
                                >
                            </div>
                            <p v-if="form.errors.foto_equipo" class="text-red-500 text-sm">{{ form.errors.foto_equipo }}</p>
                        </div>

                        <!-- Foto de la Hoja de Servicio -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="foto_hoja_servicio">
                                Hoja de Servicio Firmada
                            </label>
                            <div class="relative">
                                <div v-if="form.foto_hoja_servicio_url" class="mb-3">
                                    <img
                                        :src="form.foto_hoja_servicio_url"
                                        alt="Foto de la Hoja de Servicio"
                                        class="w-full h-32 object-cover rounded-lg border border-gray-300 shadow-sm"
                                    >
                                    <button
                                        type="button"
                                        @click="clearFile('foto_hoja_servicio')"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                    >
                                        ×
                                    </button>
                                </div>
                                <input
                                    type="file"
                                    @change="handleFileChange('foto_hoja_servicio', $event)"
                                    accept="image/*"
                                    id="foto_hoja_servicio"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.foto_hoja_servicio }"
                                >
                            </div>
                            <p v-if="form.errors.foto_hoja_servicio" class="text-red-500 text-sm">{{ form.errors.foto_hoja_servicio }}</p>
                        </div>

                        <!-- Foto de Identificación -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="foto_identificacion">
                                Identificación del Cliente
                            </label>
                            <div class="relative">
                                <div v-if="form.foto_identificacion_url" class="mb-3">
                                    <img
                                        :src="form.foto_identificacion_url"
                                        alt="Foto de Identificación del Cliente"
                                        class="w-full h-32 object-cover rounded-lg border border-gray-300 shadow-sm"
                                    >
                                    <button
                                        type="button"
                                        @click="clearFile('foto_identificacion')"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                    >
                                        ×
                                    </button>
                                </div>
                                <input
                                    type="file"
                                    @change="handleFileChange('foto_identificacion', $event)"
                                    accept="image/*"
                                    id="foto_identificacion"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.foto_identificacion }"
                                >
                            </div>
                            <p v-if="form.errors.foto_identificacion" class="text-red-500 text-sm">{{ form.errors.foto_identificacion }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sección: Notas -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Notas Adicionales</h2>
                    <div>
                        <textarea
                            v-model="form.notas"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                            rows="4"
                            placeholder="Agrega notas adicionales, términos y condiciones, o información relevante para la cita..."
                        ></textarea>
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
                            {{ isEdit ? 'Actualizar Cita' : 'Crear Cita' }}
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
import BuscarProducto from '@/Components/CreateComponents/BuscarProducto.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    cita: {
        type: Object,
        required: true
    },
    tecnicos: Array,
    clientes: Array,
    servicios: Array,
    productos: Array,
    errors: {
        type: Object,
        default: () => ({})
    },
});

// Referencias reactivas para el buscador de clientes
const selectedCliente = ref(null);
const showSuccessMessage = ref(false);

// Referencias a los componentes
const buscarClienteRef = ref(null);
const buscarProductoRef = ref(null);

// Items seleccionados para venta
const selectedItems = ref([]); // [{id, tipo, nombre?}]
const quantities = ref({});     // key: `${tipo}-${id}` => cantidad
const prices = ref({});         // key: `${tipo}-${id}` => precio
const discounts = ref({});      // key: `${tipo}-${id}` => descuento


const productosSeleccionados = computed(() => selectedItems.value);

// Totales calculados
const totales = computed(() => {
    let subtotal = 0;
    let descuentoItems = 0;

    selectedItems.value.forEach(item => {
        const key = `${item.tipo}-${item.id}`;
        const cantidad = parseFloat(quantities.value[key]) || 0;
        const precio = parseFloat(prices.value[key]) || 0;
        const descuento = parseFloat(discounts.value[key]) || 0;

        if (cantidad > 0 && precio >= 0) {
            const subtotalItem = cantidad * precio;
            descuentoItems += subtotalItem * (descuento / 100);
            subtotal += subtotalItem;
        }
    });

    const subtotalConDescuentos = Math.max(0, subtotal - descuentoItems);
    const descuentoGeneralPorc = parseFloat(form.descuento_general) || 0;
    const descuentoGeneralMonto = subtotalConDescuentos * (descuentoGeneralPorc / 100);
    const subtotalFinal = subtotalConDescuentos - descuentoGeneralMonto;
    const iva = subtotalFinal * 0.16;
    const total = subtotalFinal + iva;

    return {
        subtotal: parseFloat(subtotal.toFixed(2)),
        descuentoItems: parseFloat(descuentoItems.toFixed(2)),
        subtotalConDescuentos: parseFloat(subtotalConDescuentos.toFixed(2)),
        descuentoGeneral: parseFloat(descuentoGeneralMonto.toFixed(2)),
        iva: parseFloat(iva.toFixed(2)),
        total: parseFloat(total.toFixed(2)),
    };
});

const updateDescuentoGeneral = () => {
    // Forzar actualización de totales
    form.descuento_general = parseFloat(form.descuento_general) || 0;
};

// Opciones de selección mejoradas
const tecnicosOptions = computed(() => [
    { value: '', text: 'Selecciona un técnico', disabled: true },
    ...props.tecnicos.map(tecnico => ({
        value: tecnico.id,
        text: `${tecnico.nombre} ${tecnico.apellido}`,
        disabled: false
    }))
]);


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
    { value: 'media', text: 'Media' },
    { value: 'alta', text: 'Alta' },
    { value: 'urgente', text: 'Urgente' }
];

const tipoServicioOptions = [
    { value: '', text: 'Selecciona el tipo de servicio', disabled: true },
    { value: 'garantia', text: 'Garantía' },
    { value: 'instalacion', text: 'Instalación' },
    { value: 'reparacion', text: 'Reparación' },
    { value: 'mantenimiento', text: 'Mantenimiento' },
    { value: 'diagnostico', text: 'Diagnóstico' },
    { value: 'otro', text: 'Otro' }
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

const applyPrefillFromProps = () => {
    const prefill = props.prefill || {};
    if (!prefill || Object.keys(prefill).length === 0) return;

    if (prefill.cliente_id) {
        const id = Number(prefill.cliente_id);
        const cliente = props.clientes?.find(c => Number(c.id) === id);
        if (cliente) {
            onClienteSeleccionado(cliente);
        } else {
            form.cliente_id = id;
        }
    }

    if (prefill.numero_serie) form.numero_serie = prefill.numero_serie;
    if (prefill.descripcion) form.descripcion = prefill.descripcion;
    if (prefill.direccion_servicio) form.direccion_servicio = prefill.direccion_servicio;
    if (prefill.tipo_servicio) form.tipo_servicio = prefill.tipo_servicio;
    if (prefill.garantia) form.garantia = prefill.garantia;

    // Si es una cita de garantía, establecer prioridad media y estado programado
    if (prefill.tipo_servicio === 'garantia') {
        form.prioridad = 'media';
        form.estado = 'programado';
    }
};

const onCrearNuevoCliente = (nombreBuscado) => {
    // Abrir ventana para crear nuevo cliente
    window.open(route('clientes.create'), '_blank');
};


// Agregar producto o servicio a la lista
const onAgregarItem = (item) => {
    if (!item || typeof item.id === 'undefined') return;
    const tipo = item.tipo || 'producto'; // 'producto' o 'servicio'
    const key = `${tipo}-${item.id}`;
    const exists = selectedItems.value.some(p => p.id === item.id && p.tipo === tipo);
    if (!exists) {
        selectedItems.value.push({ id: item.id, tipo: tipo, nombre: item.nombre });
        quantities.value[key] = 1;
        const precio = typeof item.precio === 'number' ? item.precio : (tipo === 'producto' ? item.precio_venta : item.precio);
        prices.value[key] = precio || 0;
        discounts.value[key] = 0;
    }
};

const getItemName = (item) => {
    if (item?.nombre) return item.nombre;

    // Buscar en productos o servicios según el tipo
    const tipo = item?.tipo || 'producto';
    const lista = tipo === 'producto' ? (props.productos || []) : (props.servicios || []);
    const found = lista.find(p => p.id === item.id);
    return found?.nombre || `${tipo === 'producto' ? 'Producto' : 'Servicio'} #${item.id}`;
};

const removeItem = (item) => {
    const tipo = item?.tipo || 'producto';
    const key = `${tipo}-${item.id}`;

    // Eliminar del array de items seleccionados
    const idx = selectedItems.value.findIndex(p => p.id === item.id && p.tipo === tipo);
    if (idx >= 0) selectedItems.value.splice(idx, 1);

    // Limpiar cantidades, precios y descuentos
    delete quantities.value[key];
    delete prices.value[key];
    delete discounts.value[key];
};

const updateQuantity = (key, quantity) => {
    const numQuantity = parseFloat(quantity);
    if (isNaN(numQuantity) || numQuantity < 0) {
        return;
    }
    quantities.value[key] = numQuantity;
};

const updatePrice = (key, price) => {
    const numPrice = parseFloat(price);
    if (isNaN(numPrice) || numPrice < 0) {
        return;
    }
    prices.value[key] = numPrice;
};

const updateDiscount = (key, discount) => {
    const numDiscount = parseFloat(discount);
    if (isNaN(numDiscount) || numDiscount < 0 || numDiscount > 100) {
        return;
    }
    discounts.value[key] = numDiscount;
};

const calculateSubtotal = (item) => {
    const key = `${item.tipo}-${item.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    const descuento = parseFloat(discounts.value[key]) || 0;

    if (cantidad > 0 && precio >= 0) {
        const subtotalItem = cantidad * precio;
        const descuentoMonto = subtotalItem * (descuento / 100);
        return (subtotalItem - descuentoMonto).toFixed(2);
    }
    return '0.00';
};

// Inicializar formulario con datos de la cita existente
const initFormData = () => {
    // Formatear fecha_hora para input datetime-local
    let fechaHoraFormatted = '';
    if (props.cita.fecha_hora) {
        const fecha = new Date(props.cita.fecha_hora);
        // Convertir a formato YYYY-MM-DDTHH:mm para datetime-local
        const year = fecha.getFullYear();
        const month = String(fecha.getMonth() + 1).padStart(2, '0');
        const day = String(fecha.getDate()).padStart(2, '0');
        const hours = String(fecha.getHours()).padStart(2, '0');
        const minutes = String(fecha.getMinutes()).padStart(2, '0');
        fechaHoraFormatted = `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    return {
        cliente_id: props.cita.cliente_id || '',
        tecnico_id: props.cita.tecnico_id || '',
        fecha_hora: fechaHoraFormatted,
        estado: props.cita.estado || 'pendiente',
        prioridad: props.cita.prioridad || 'media',
        tipo_servicio: props.cita.tipo_servicio || '',
        descripcion: props.cita.descripcion || '',
        direccion_servicio: props.cita.direccion_servicio || '',
        observaciones: props.cita.observaciones || '',
        notas: props.cita.notas || '',
        descuento_general: props.cita.descuento_general || 0,
        subtotal: props.cita.subtotal || 0,
        descuento_items: props.cita.descuento_items || 0,
        iva: props.cita.iva || 0,
        total: props.cita.total || 0,
        // Campos de fotos
        foto_equipo: null,
        foto_hoja_servicio: null,
        foto_identificacion: null,
        foto_equipo_url: props.cita.foto_equipo ? `/storage/${props.cita.foto_equipo}` : null,
        foto_hoja_servicio_url: props.cita.foto_hoja_servicio ? `/storage/${props.cita.foto_hoja_servicio}` : null,
        foto_identificacion_url: props.cita.foto_identificacion ? `/storage/${props.cita.foto_identificacion}` : null,
    };
};

const form = useForm(initFormData());

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
        selectedItems: selectedItems.value,
        quantities: quantities.value,
        prices: prices.value,
        discounts: discounts.value,
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
                    if (parsed[key] !== undefined && key !== 'tipo_equipo' && key !== 'marca_equipo' && key !== 'modelo_equipo' && key !== 'foto_equipo' && key !== 'foto_hoja_servicio' && key !== 'foto_identificacion') {
                        form[key] = parsed[key];
                    }
                });

                // Cargar cliente seleccionado usando el nuevo componente
                if (parsed.selectedCliente) {
                    selectedCliente.value = parsed.selectedCliente;
                    // El componente BuscarCliente se actualizará automáticamente con el cliente seleccionado
                }


                // Cargar productos/servicios seleccionados
                if (parsed.selectedItems) {
                    selectedItems.value = parsed.selectedItems;
                }
                if (parsed.quantities) {
                    Object.assign(quantities.value, parsed.quantities);
                }
                if (parsed.prices) {
                    Object.assign(prices.value, parsed.prices);
                }
                if (parsed.discounts) {
                    Object.assign(discounts.value, parsed.discounts);
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
    form.prioridad = '';

    // Limpiar selección de cliente
    clearClienteSelection();


    // Limpiar productos/servicios seleccionados
    selectedItems.value.forEach(item => {
        const tipo = item?.tipo || 'producto';
        const key = `${tipo}-${item.id}`;
        delete quantities.value[key];
        delete prices.value[key];
        delete discounts.value[key];
    });
    selectedItems.value = [];

    // Limpiar totales
    form.subtotal = 0;
    form.descuento_general = 0;
    form.descuento_items = 0;
    form.iva = 0;
    form.total = 0;
    form.notas = '';

    // Limpiar los componentes de búsqueda
    if (buscarClienteRef.value) {
        buscarClienteRef.value.limpiarBusqueda();
    }
    if (buscarProductoRef.value) {
        buscarProductoRef.value.limpiarBusqueda();
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

    if (!form.tipo_servicio) {
        errors.push('Debe seleccionar el tipo de servicio');
    }

    if (!form.fecha_hora) {
        errors.push('Debe especificar la fecha y hora');
    }

    // Removido: validación de problema_reportado ya no es requerido

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
        formData.append(key, form[key] || '');
    }

    // Adjuntar items como JSON
    const items = selectedItems.value.map(item => ({
        id: item.id,
        tipo: item.tipo,
        cantidad: quantities.value[`${item.tipo}-${item.id}`] || 1,
        precio: prices.value[`${item.tipo}-${item.id}`] || 0,
        descuento: discounts.value[`${item.tipo}-${item.id}`] || 0,
        notas: item.notas || null,
    }));
    formData.append('items', JSON.stringify(items));

    // Actualizar totales en el formulario
    form.subtotal = totales.value.subtotal;
    form.descuento_items = totales.value.descuentoItems;
    formData.append('iva', totales.value.iva);
    formData.append('total', totales.value.total);

    // Adjuntar archivos de fotos si existen
    if (form.foto_equipo) {
        formData.append('foto_equipo', form.foto_equipo);
    }
    if (form.foto_hoja_servicio) {
        formData.append('foto_hoja_servicio', form.foto_hoja_servicio);
    }
    if (form.foto_identificacion) {
        formData.append('foto_identificacion', form.foto_identificacion);
    }

    // Usar router.post con _method para simular PUT
    form.post(route('citas.update', props.cita.id), {
        data: formData,
        preserveScroll: true,
        onStart: () => {
            showSuccessMessage.value = false;
        },
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 3000);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        onError: (errors) => {
            console.error('Error al actualizar la cita:', errors);
            setTimeout(() => {
                const firstErrorElement = document.querySelector('.text-red-500, .border-red-300');
                if (firstErrorElement) {
                    firstErrorElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 100);
        }
    });
};

// Función para comprimir imágenes
const compressImage = async (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Mantener aspect ratio, max 1920x1920
                let width = img.width;
                let height = img.height;
                const maxSize = 1920;
                
                if (width > height && width > maxSize) {
                    height = (height * maxSize) / width;
                    width = maxSize;
                } else if (height > maxSize) {
                    width = (width * maxSize) / height;
                    height = maxSize;
                }
                
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob((blob) => {
                    resolve(new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    }));
                }, 'image/jpeg', 0.8);
            };
            img.onerror = reject;
        };
        reader.onerror = reject;
    });
};

// Manejar cambio de archivo de foto
const handleFileChange = async (fieldName, event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validar tipo de archivo
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        showTemporaryMessage('Solo se permiten imágenes (JPG, PNG, WEBP)', 'error');
        event.target.value = '';
        return;
    }

    // Validar tamaño (máximo 10MB)
    if (file.size > 10 * 1024 * 1024) {
        showTemporaryMessage('La imagen no debe superar 10MB', 'error');
        event.target.value = '';
        return;
    }

    try {
        showTemporaryMessage('Procesando imagen...', 'info');

        // Comprimir imagen si es necesario
        let processedFile = file;
        if (file.size > 1024 * 1024) { // Si es mayor a 1MB
            processedFile = await compressImage(file);
        }

        // Limpiar URL anterior si existe
        if (form[`${fieldName}_url`] && form[`${fieldName}_url`].startsWith('blob:')) {
            URL.revokeObjectURL(form[`${fieldName}_url`]);
        }

        form[fieldName] = processedFile;
        form[`${fieldName}_url`] = URL.createObjectURL(processedFile);

        showTemporaryMessage('Imagen cargada correctamente', 'success');
    } catch (error) {
        console.error('Error al procesar imagen:', error);
        showTemporaryMessage('Error al procesar la imagen', 'error');
        event.target.value = '';
    }
};

// Limpiar archivo
const clearFile = (fieldName) => {
    // Limpiar URL de blob para evitar memory leaks
    if (form[`${fieldName}_url`] && form[`${fieldName}_url`].startsWith('blob:')) {
        URL.revokeObjectURL(form[`${fieldName}_url`]);
    }

    form[fieldName] = null;
    form[`${fieldName}_url`] = props.cita?.[fieldName] ? `/storage/${props.cita[fieldName]}` : null;

    // Limpiar el input file
    const input = document.getElementById(fieldName);
    if (input) input.value = '';
};

// Auto-guardar borrador cada 30 segundos
let autoSaveInterval;

onMounted(() => {
    // Cargar cliente seleccionado si existe
    if (props.cita.cliente_id) {
        const cliente = props.clientes.find(c => c.id === props.cita.cliente_id);
        if (cliente) {
            selectedCliente.value = cliente;
        }
    }

    // Cargar items existentes de la cita
    if (props.cita.items && Array.isArray(props.cita.items)) {
        props.cita.items.forEach(item => {
            const tipo = item.citable_type.includes('Producto') ? 'producto' : 'servicio';
            const id = item.citable_id;
            const key = `${tipo}-${id}`;
            
            // Agregar a selectedItems
            selectedItems.value.push({
                id: id,
                tipo: tipo,
                nombre: item.citable?.nombre || (tipo === 'producto' ? `Producto #${id}` : `Servicio #${id}`),
            });

            // Establecer valores
            quantities.value[key] = item.cantidad;
            prices.value[key] = parseFloat(item.precio);
            discounts.value[key] = parseFloat(item.descuento);
        });
    }
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
