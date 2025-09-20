<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router, useForm, Head } from '@inertiajs/vue3'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import DatePicker from '@/Components/DatePicker.vue'
import Checkbox from '@/Components/Checkbox.vue'
import axios from 'axios'

defineOptions({ layout: AppLayout })

// Configuración de notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: '#059669',
      icon: '✓',
      className: 'font-medium'
    },
    {
      type: 'error',
      background: '#dc2626',
      icon: '✕',
      className: 'font-medium'
    },
    {
      type: 'warning',
      background: '#d97706',
      icon: '⚠',
      className: 'font-medium'
    }
  ]
})

// Estado del componente
const clientes = ref([])
const componentesDisponibles = ref([])
const loading = ref(false)
const saving = ref(false)
const selectedComponenteId = ref('')

// Formulario reactivo mejorado
const form = useForm({
  numero_contrato: '',
  cliente_id: '',
  componentes: [],
  fecha_inicio: { fechaInicio: new Date().toISOString(), fechaFin: null },
  duracion_meses: 12,
  fecha_firma: { fechaInicio: new Date().toISOString(), fechaFin: null },
  dia_pago: 1,
  estado: 'borrador',
  monto_mensual: '0.00',
  deposito_garantia: '0.00',
  renovacion_automatica: false,
  meses_prorroga: 0,
  condiciones_especiales: '',
  lugar_instalacion: '',
  fecha_instalacion: { fechaInicio: null, fechaFin: null },
  fecha_retiro: { fechaInicio: null, fechaFin: null },
  notas_instalacion: '',
  notas_retiro: '',
  responsable_entrega: '',
  responsable_recibe: '',
  observaciones: '',
  historial_cambios: '',
  forma_pago: 'transferencia',
  referencia_pago: '',
  terminos_aceptados: false
})

// Opciones para selectores
const duracionMesesOptions = [
  { value: 3, label: '3 meses' },
  { value: 6, label: '6 meses' },
  { value: 12, label: '12 meses' },
  { value: 18, label: '18 meses' },
  { value: 24, label: '24 meses' },
  { value: 36, label: '36 meses' }
]

const diaPagoOptions = Array.from({ length: 28 }, (_, i) => ({
  value: i + 1,
  label: `Día ${i + 1}`
}))

const formaPagoOptions = [
  { value: 'transferencia', label: 'Transferencia Bancaria' },
  { value: 'efectivo', label: 'Efectivo' },
  { value: 'tarjeta_credito', label: 'Tarjeta de Crédito' },
  { value: 'tarjeta_debito', label: 'Tarjeta de Débito' },
  { value: 'cheque', label: 'Cheque' },
  { value: 'deposito', label: 'Depósito Bancario' }
]

const mesesProrrogaOptions = [
  { value: 1, label: '1 mes' },
  { value: 3, label: '3 meses' },
  { value: 6, label: '6 meses' },
  { value: 12, label: '12 meses' }
]

// Computadas
const componentesDisponiblesFiltrados = computed(() => {
  return componentesDisponibles.value.filter(componente =>
    !form.componentes.some(c => c.componente_id == componente.id) && componente.estado === 'disponible'
  )
})

const montoTotalAnual = computed(() => {
  return (parseFloat(form.monto_mensual) * form.duracion_meses).toFixed(2)
})

const fechaFinCalculada = computed(() => {
  if (form.fecha_inicio.fechaInicio) {
    const inicio = new Date(form.fecha_inicio.fechaInicio)
    const fin = new Date(inicio)
    fin.setMonth(fin.getMonth() + form.duracion_meses)
    return fin.toLocaleDateString('es-ES')
  }
  return ''
})

const isFormValid = computed(() => {
  return form.numero_contrato &&
         form.cliente_id &&
         form.componentes.length > 0 &&
         form.fecha_inicio.fechaInicio &&
         form.terminos_aceptados
})

// Watchers
watch(() => form.duracion_meses, (newDuration) => {
  if (form.fecha_inicio.fechaInicio) {
    const inicio = new Date(form.fecha_inicio.fechaInicio)
    const fin = new Date(inicio)
    fin.setMonth(fin.getMonth() + newDuration)
    form.fecha_inicio.fechaFin = fin.toISOString()
  }
})

watch(() => form.renovacion_automatica, (enabled) => {
  if (!enabled) {
    form.meses_prorroga = 0
  } else if (form.meses_prorroga === 0) {
    form.meses_prorroga = 6
  }
})

// Métodos
const cargarDatos = async () => {
  loading.value = true
  try {
    const [clientesResponse, componentesResponse] = await Promise.all([
      axios.get(route('api.clientes.index')),
      axios.get(route('api.componentes-kit.index'))
    ])

    clientes.value = clientesResponse.data.data || []
    componentesDisponibles.value = componentesResponse.data.data || []

    if (clientes.value.length === 0) {
      notyf.warning('No hay clientes disponibles. Primero registra un cliente.')
    }
    if (componentesDisponibles.value.length === 0) {
      notyf.warning('No hay componentes disponibles para rentar.')
    }
  } catch (error) {
    console.error('Error al cargar datos:', error)
    notyf.error('No se pudieron cargar los datos necesarios')
    handleApiError(error)
  } finally {
    loading.value = false
  }
}

const handleApiError = (error) => {
  if (error.response?.status === 401) {
    notyf.error('Sesión expirada. Inicia sesión nuevamente.')
    router.visit(route('login'))
  } else if (error.response?.status >= 500) {
    notyf.error('Error del servidor. Intenta nuevamente.')
  }
}

const agregarComponente = () => {
  if (!selectedComponenteId.value) {
    notyf.error('Por favor selecciona un componente')
    return
  }

  const componente = componentesDisponibles.value.find(c => c.id == selectedComponenteId.value)
  if (!componente) {
    notyf.error('Componente no encontrado')
    return
  }

  if (form.componentes.some(c => c.componente_id == componente.id)) {
    notyf.error('Este componente ya fue agregado al contrato')
    return
  }

  const componenteRenta = {
    componente_id: componente.id,
    tipo: componente.tipo,
    codigo: componente.codigo || 'N/A',
    nombre: componente.nombre || 'Sin nombre',
    marca: componente.marca || 'N/A',
    modelo: componente.modelo || 'N/A',
    numero_serie: componente.numero_serie || 'N/A',
    precio_mensual: parseFloat(componente.precio_renta_mensual || 0).toFixed(2),
    estado_componente: componente.estado || 'disponible'
  }

  form.componentes.push(componenteRenta)
  calcularTotal()
  selectedComponenteId.value = ''
  notyf.success(`Componente ${componente.nombre} agregado correctamente`)
}

const eliminarComponente = (index) => {
  if (index < 0 || index >= form.componentes.length) return

  const componente = form.componentes[index]
  form.componentes.splice(index, 1)
  calcularTotal()
  notyf.success(`Componente ${componente.nombre} eliminado del contrato`)
}

const calcularTotal = () => {
  const total = form.componentes.reduce((acc, item) => {
    return acc + parseFloat(item.precio_mensual || 0)
  }, 0)
  form.monto_mensual = total.toFixed(2)
}

const generarNumeroContrato = () => {
  const año = new Date().getFullYear()
  const timestamp = Date.now().toString().slice(-6)
  form.numero_contrato = `R-${año}-${timestamp}`
  notyf.success('Número de contrato generado automáticamente')
}

const validarFormulario = () => {
  const errores = []

  if (!form.numero_contrato.trim()) {
    errores.push('El número de contrato es obligatorio')
  }

  if (!form.cliente_id) {
    errores.push('Debe seleccionar un cliente')
  }

  if (form.componentes.length === 0) {
    errores.push('Debe agregar al menos un componente')
  }

  if (!form.fecha_inicio.fechaInicio) {
    errores.push('La fecha de inicio es obligatoria')
  }

  if (parseFloat(form.deposito_garantia) < 0) {
    errores.push('El depósito de garantía no puede ser negativo')
  }

  if (!form.terminos_aceptados) {
    errores.push('Debe aceptar los términos y condiciones')
  }

  if (form.renovacion_automatica && form.meses_prorroga <= 0) {
    errores.push('Debe especificar los meses de prórroga')
  }

  return errores
}

const submit = () => {
  const errores = validarFormulario()

  if (errores.length > 0) {
    errores.forEach(error => notyf.error(error))
    return
  }

  saving.value = true
  form.clearErrors()

  // Preparar datos para envío
  const data = new FormData()

  // Agregar datos básicos del formulario
  Object.keys(form.data()).forEach(key => {
    if (key !== 'equipos') {
      data.append(key, form.data()[key])
    }
  })

  // Agregar fechas
  data.append('fecha_inicio', form.fecha_inicio.fechaInicio)
  data.append('fecha_fin', form.fecha_inicio.fechaFin)
  data.append('fecha_firma', form.fecha_firma.fechaInicio)
  data.append('fecha_instalacion', form.fecha_instalacion.fechaInicio)
  data.append('fecha_retiro', form.fecha_retiro.fechaInicio)
  data.append('monto_mensual', parseFloat(form.monto_mensual))
  data.append('deposito_garantia', parseFloat(form.deposito_garantia))

  // Agregar componentes
  form.componentes.forEach((componente, index) => {
    data.append(`componentes[${index}][componente_id]`, componente.componente_id)
    data.append(`componentes[${index}][precio_mensual]`, componente.precio_mensual)
  })

  router.post(route('rentas.store'), data, {
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    onSuccess: () => {
      notyf.success('¡Renta creada exitosamente!')
      // Opcional: redireccionar o limpiar formulario
      // form.reset()
    },
    onError: (validationErrors) => {
      console.error('Errores de validación:', validationErrors)

      // Mostrar errores específicos
      Object.entries(validationErrors).forEach(([field, messages]) => {
        if (Array.isArray(messages)) {
          messages.forEach(message => notyf.error(message))
        } else {
          notyf.error(messages)
        }
      })
    },
    onFinish: () => {
      saving.value = false
    }
  })
}

const cancelar = () => {
  if (form.isDirty) {
    if (confirm('¿Estás seguro de cancelar? Se perderán los datos no guardados.')) {
      router.visit(route('rentas.index'))
    }
  } else {
    router.visit(route('rentas.index'))
  }
}

// Lifecycle
onMounted(() => {
  cargarDatos()
})
</script>

<template>
  <div>
    <Head title="Nueva Renta" />
    <AppLayout title="Nueva Renta">
      <template #header>
        <div class="flex justify-between items-center">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva Renta
          </h2>
          <div class="text-sm text-gray-600">
            <span v-if="form.componentes.length > 0">
              {{ form.componentes.length }} componente{{ form.componentes.length !== 1 ? 's' : '' }} |
              Total: ${{ form.monto_mensual }}/mes
            </span>
          </div>
        </div>
      </template>

      <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl">

            <!-- Indicador de progreso -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Progreso del formulario</span>
                <span class="font-medium" :class="isFormValid ? 'text-green-600' : 'text-orange-600'">
                  {{ isFormValid ? 'Completo' : 'Incompleto' }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div
                  class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: isFormValid ? '100%' : '60%' }"
                ></div>
              </div>
            </div>

            <div class="p-6">
              <!-- Loading state -->
              <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <span class="ml-3 text-gray-600">Cargando datos...</span>
              </div>

              <!-- Formulario -->
              <form @submit.prevent="submit" v-else class="space-y-8">

                <!-- Sección: Información General -->
                <div class="bg-gray-50 rounded-lg p-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="condiciones_especiales" class="block text-sm font-medium text-gray-700 mb-1">
                        Condiciones Especiales
                      </label>
                      <textarea
                        id="condiciones_especiales"
                        v-model="form.condiciones_especiales"
                        rows="3"
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Condiciones específicas del contrato, descuentos, servicios adicionales..."
                      ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label for="notas_instalacion" class="block text-sm font-medium text-gray-700 mb-1">
                          Notas de Instalación
                        </label>
                        <textarea
                          id="notas_instalacion"
                          v-model="form.notas_instalacion"
                          rows="3"
                          class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                          placeholder="Instrucciones especiales para la instalación..."
                        ></textarea>
                      </div>

                      <div>
                        <label for="notas_retiro" class="block text-sm font-medium text-gray-700 mb-1">
                          Notas de Retiro
                        </label>
                        <textarea
                          id="notas_retiro"
                          v-model="form.notas_retiro"
                          rows="3"
                          class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                          placeholder="Instrucciones especiales para el retiro..."
                        ></textarea>
                      </div>
                    </div>

                    <div>
                      <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                        Observaciones Generales
                      </label>
                      <textarea
                        id="observaciones"
                        v-model="form.observaciones"
                        rows="3"
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Notas adicionales, comentarios importantes sobre la renta..."
                      ></textarea>
                      <InputError :message="form.errors.observaciones" class="mt-1" />
                    </div>

                    <div>
                      <label for="historial_cambios" class="block text-sm font-medium text-gray-700 mb-1">
                        Historial de Cambios
                      </label>
                      <textarea
                        id="historial_cambios"
                        v-model="form.historial_cambios"
                        rows="3"
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Registro de modificaciones importantes al contrato..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Sección: Componentes del Kit -->
                <div class="bg-green-50 rounded-lg p-6">
                  <h3 class="text-lg font-medium text-green-900 mb-4">Componentes del Kit</h3>

                  <!-- Selector de componentes -->
                  <div class="mb-6">
                    <label for="componente-select" class="block text-sm font-medium text-gray-700 mb-2">
                      Seleccionar Componente Disponible
                    </label>
                    <div class="flex gap-3">
                      <select
                        id="componente-select"
                        v-model="selectedComponenteId"
                        class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                      >
                        <option value="">-- Seleccionar componente --</option>
                        <option
                          v-for="componente in componentesDisponiblesFiltrados"
                          :key="componente.id"
                          :value="componente.id"
                        >
                          {{ componente.nombre }} ({{ componente.tipo }}) - ${{ componente.precio_renta_mensual }}/mes
                        </option>
                      </select>
                      <button
                        type="button"
                        @click="agregarComponente"
                        :disabled="!selectedComponenteId"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        Agregar
                      </button>
                    </div>
                  </div>

                  <!-- Lista de componentes seleccionados -->
                  <div v-if="form.componentes.length > 0" class="space-y-4">
                    <div v-for="(componente, index) in form.componentes" :key="componente.componente_id" class="bg-white rounded-lg border border-green-200 p-4">
                      <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                          <div class="flex items-center gap-3 mb-2">
                            <h4 class="font-medium text-gray-900">{{ componente.nombre }}</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                              {{ componente.tipo }}
                            </span>
                          </div>
                          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                            <div><strong>Código:</strong> {{ componente.codigo }}</div>
                            <div><strong>Marca:</strong> {{ componente.marca }}</div>
                            <div><strong>Modelo:</strong> {{ componente.modelo }}</div>
                            <div><strong>N° Serie:</strong> {{ componente.numero_serie }}</div>
                          </div>
                          <div class="mt-2 text-sm font-medium text-green-600">
                            Precio mensual: ${{ componente.precio_mensual }}
                          </div>
                        </div>
                        <button
                          type="button"
                          @click="eliminarComponente(index)"
                          class="text-red-600 hover:text-red-800 text-sm font-medium ml-4"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Mensaje cuando no hay componentes -->
                  <div v-else class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p>No has seleccionado ningún componente aún.</p>
                    <p class="text-sm">Selecciona componentes disponibles arriba para agregarlos al kit.</p>
                  </div>
                </div>

                <!-- Sección: Términos y Condiciones -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                  <h3 class="text-lg font-medium text-red-900 mb-4">Términos y Condiciones</h3>

                  <div class="space-y-4">
                    <div class="flex items-start">
                      <Checkbox
                        id="terminos_aceptados"
                        v-model:checked="form.terminos_aceptados"
                        class="mt-1"
                      />
                      <div class="ml-3">
                        <label for="terminos_aceptados" class="text-sm font-medium text-gray-700">
                          Acepto los términos y condiciones del contrato de renta
                        </label>
                        <p class="text-xs text-gray-500 mt-1">
                          Al marcar esta casilla, confirmo que he revisado y acepto todas las condiciones establecidas en este contrato de renta.
                        </p>
                      </div>
                    </div>

                    <InputError :message="form.errors.terminos_aceptados" class="mt-1" />
                  </div>
                </div>

                <!-- Resumen del contrato -->
                <div v-if="isFormValid" class="bg-green-50 border border-green-200 rounded-lg p-6">
                  <h3 class="text-lg font-medium text-green-900 mb-4">Resumen del Contrato</h3>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                      <span class="font-medium text-green-800">Cliente:</span>
                      <span class="text-green-700 ml-2">
                        {{ clientes.find(c => c.id == form.cliente_id)?.nombre || 'N/A' }}
                      </span>
                    </div>

                    <div>
                      <span class="font-medium text-green-800">Componentes:</span>
                      <span class="text-green-700 ml-2">{{ form.componentes.length }} componente(s)</span>
                    </div>

                    <div>
                      <span class="font-medium text-green-800">Duración:</span>
                      <span class="text-green-700 ml-2">{{ form.duracion_meses }} meses</span>
                    </div>

                    <div>
                      <span class="font-medium text-green-800">Monto mensual:</span>
                      <span class="text-green-700 ml-2">${{ form.monto_mensual }}</span>
                    </div>

                    <div>
                      <span class="font-medium text-green-800">Total del contrato:</span>
                      <span class="text-green-700 ml-2">${{ montoTotalAnual }}</span>
                    </div>

                    <div>
                      <span class="font-medium text-green-800">Día de pago:</span>
                      <span class="text-green-700 ml-2">{{ form.dia_pago }}</span>
                    </div>
                  </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200">
                  <button
                    type="button"
                    @click="cancelar"
                    :disabled="saving"
                    class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    Cancelar
                  </button>

                  <button
                    type="button"
                    @click="form.estado = 'borrador'; submit()"
                    :disabled="saving || !isFormValid"
                    class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    {{ saving ? 'Guardando...' : 'Guardar como Borrador' }}
                  </button>

                  <PrimaryButton
                    type="button"
                    @click="form.estado = 'activo'; submit()"
                    :disabled="saving || !isFormValid"
                    :class="{
                      'opacity-50 cursor-not-allowed': saving || !isFormValid,
                      'bg-green-600 hover:bg-green-700': isFormValid && !saving
                    }"
                  >
                    <svg v-if="saving" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ saving ? 'Creando...' : 'Crear Renta' }}
                  </PrimaryButton>
                </div>

              </form>
            </div>
          </div>

          <!-- Panel de ayuda lateral (opcional) -->
          <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="text-lg font-medium text-blue-900 mb-3">💡 Consejos</h4>
            <ul class="text-sm text-blue-800 space-y-2">
              <li>• Selecciona los componentes que necesites para armar tu kit personalizado</li>
              <li>• Cada componente tiene su propio precio de renta mensual</li>
              <li>• El depósito de garantía es opcional pero recomendado</li>
              <li>• Puedes guardar como borrador y completar después</li>
              <li>• La renovación automática facilita la gestión de contratos a largo plazo</li>
            </ul>
          </div>
        </div>
      </div>
    </AppLayout>
  </div>
</template>

<style scoped>
/* Animaciones suaves */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mejoras en el diseño de tablas */
tbody tr:hover {
  background-color: rgb(249 250 251);
}

/* Estados de formulario */
.form-section {
  transition: border-color 0.2s ease-in-out;
}

.form-section:focus-within {
  border-color: rgb(99 102 241);
}

/* Loading states */
@keyframes pulse-subtle {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

.loading-pulse {
  animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Mejores estados de validación */
.field-valid {
  border-color: rgb(34 197 94);
}

.field-invalid {
  border-color: rgb(239 68 68);
}

/* Responsive improvements */
@media (max-width: 640px) {
  .grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}
</style>
