<!-- /resources/js/Pages/Prestamos/Create.vue -->
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import BuscarCliente from '@/Components/CreateComponents/BuscarCliente.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  clientes: {
    type: Array,
    default: () => []
  },
  prestamo: {
    type: Object,
    default: () => ({
      cliente_id: null,
      monto_prestado: 0,
      tasa_interes_mensual: 5, // 5% mensual por defecto
      numero_pagos: 12,
      frecuencia_pago: 'mensual',
      fecha_inicio: new Date().toISOString().split('T')[0],
      descripcion: null,
      notas: null,
    })
  }
})

/* =========================
   Configuración de notificaciones
========================= */
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false },
    { type: 'warning', background: '#f59e0b', icon: false }
  ]
})

const page = usePage()
onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})

/* =========================
   Estado del formulario
========================= */
const form = ref({ ...props.prestamo })
const loading = ref(false)
const calculando = ref(false)
const clienteSeleccionado = ref(null)
const mostrarModalDetalles = ref(false)

/* =========================
   Funciones para manejo del cliente
========================= */
const onClienteSeleccionado = (cliente) => {
  clienteSeleccionado.value = cliente
  form.value.cliente_id = cliente ? cliente.id : null

  // Limpiar error cuando se selecciona un cliente
  if (cliente && errors.value.cliente_id) {
    delete errors.value.cliente_id
  }
}

const onCrearNuevoCliente = (nombreCliente) => {
  // Redirigir a crear cliente con el nombre pre-llenado
  router.visit('/clientes/create', {
    data: { nombre_razon_social: nombreCliente }
  })
}

/* =========================
   Cálculos financieros
========================= */
const calculos = ref({
  pago_periodico: 0,
  interes_total: 0,
  total_pagar: 0,
})

const calcularPagos = async () => {
  if (!form.value.monto_prestado || !form.value.numero_pagos || form.value.tasa_interes_mensual === undefined) {
    calculos.value = { pago_periodico: 0, interes_total: 0, total_pagar: 0 }
    return
  }

  calculando.value = true

  try {
    const response = await fetch('/prestamos/calcular-pagos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify({
        monto_prestado: form.value.monto_prestado,
        tasa_interes_mensual: form.value.tasa_interes_mensual,
        numero_pagos: form.value.numero_pagos,
        frecuencia_pago: form.value.frecuencia_pago,
      })
    })

    if (response.ok) {
      const data = await response.json()
      if (data.success) {
        calculos.value = data.calculos
      }
    }
  } catch (error) {
    console.error('Error calculando pagos:', error)
  } finally {
    calculando.value = false
  }
}

/* =========================
   Watchers para recálculo automático
========================= */
watch([() => form.value.monto_prestado, () => form.value.tasa_interes_mensual, () => form.value.numero_pagos], () => {
  calcularPagos()
})

/* =========================
   Validación del formulario
========================= */
const errors = ref({})
const buscarClienteRef = ref(null)

const validateForm = () => {
  errors.value = {}

  if (!clienteSeleccionado.value) {
    errors.value.cliente_id = 'Debe seleccionar un cliente'
  }

  if (!form.value.monto_prestado || form.value.monto_prestado <= 0) {
    errors.value.monto_prestado = 'El monto debe ser mayor a cero'
  }

  if (form.value.tasa_interes_mensual < 0 || form.value.tasa_interes_mensual > 100) {
    errors.value.tasa_interes_mensual = 'La tasa de interés debe estar entre 0% y 100%'
  }

  if (!form.value.numero_pagos || form.value.numero_pagos < 1) {
    errors.value.numero_pagos = 'El número de pagos debe ser mayor a cero'
  }

  if (!form.value.fecha_inicio) {
    errors.value.fecha_inicio = 'La fecha de inicio es requerida'
  }

  return Object.keys(errors.value).length === 0
}

/* =========================
   Envío del formulario
========================= */
const submitForm = () => {
  if (!validateForm()) {
    notyf.error('Por favor corrija los errores del formulario')
    return
  }

  loading.value = true

  // Crear objeto solo con los campos necesarios
  const datosPrestamo = {
    cliente_id: form.value.cliente_id,
    monto_prestado: form.value.monto_prestado,
    tasa_interes_mensual: form.value.tasa_interes_mensual,
    numero_pagos: form.value.numero_pagos,
    frecuencia_pago: form.value.frecuencia_pago,
    fecha_inicio: form.value.fecha_inicio,
    fecha_primer_pago: form.value.fecha_primer_pago,
    descripcion: form.value.descripcion,
    notas: form.value.notas,
  }

  router.post('/prestamos', datosPrestamo, {
    onStart: () => {
      notyf.success('Creando préstamo...')
    },
    onSuccess: () => {
      notyf.success('Préstamo creado correctamente')
      // Limpiar formulario después de crear
      form.value = { ...props.prestamo }
      clienteSeleccionado.value = null
      if (buscarClienteRef.value) {
        buscarClienteRef.value.limpiarBusqueda()
      }
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors)
      notyf.error('Error al crear el préstamo')
    },
    onFinish: () => {
      loading.value = false
    }
  })
}

/* =========================
   Funciones auxiliares
========================= */
const formatearMoneda = (num) => {
  const value = parseFloat(num);
  const safe = Number.isFinite(value) ? value : 0;
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(safe);
}

// Función para formatear moneda como el componente BuscarCliente
const formatearMonedaCliente = (valor) => {
  if (!valor) return '$0.00';
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(valor);
}

// Función para mostrar detalles del cálculo
const mostrarDetallesCalculo = () => {
  if (!calculos.value.detalles_calculo) return null;

  const detalles = calculos.value.detalles_calculo;
  const tasaMensual = detalles.tasa_mensual;
  const factor = calculos.value.factor_compuesto;

  return {
    paso1: `Tasa mensual = ${form.value.tasa_interes_mensual}% (tasa directa)`,
    paso2: `Factor compuesto = (1 + ${tasaMensual.toFixed(6)})^${detalles.periodos} = ${factor.toFixed(6)}`,
    paso3: `Pago = $${detalles.capital.toLocaleString('es-MX', {minimumFractionDigits: 2})} × (${tasaMensual.toFixed(6)} × ${factor.toFixed(6)}) ÷ (${factor.toFixed(6)} - 1)`,
    resultado: `Pago = $${calculos.value.pago_periodico.toLocaleString('es-MX', {minimumFractionDigits: 2})}`
  };
}

const opcionesFrecuencia = [
  { value: 'semanal', label: 'Semanal' },
  { value: 'quincenal', label: 'Quincenal' },
  { value: 'mensual', label: 'Mensual' },
]

const opcionesNumeroPagos = Array.from({ length: 60 }, (_, i) => ({
  value: i + 1,
  label: `${i + 1} pago${i > 0 ? 's' : ''}`
}))
</script>

<template>
  <Head title="Crear Préstamo" />

  <div class="prestamos-create min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Crear Nuevo Préstamo</h1>
            <p class="text-gray-600 mt-2">Configure los términos del préstamo y calcule automáticamente los pagos</p>
          </div>
          <Link
            href="/prestamos"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
          >
            ← Volver a Préstamos
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Formulario principal -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Información del Préstamo</h2>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-6">
              <!-- Cliente -->
              <div>
                <BuscarCliente
                  ref="buscarClienteRef"
                  :clientes="clientes"
                  :cliente-seleccionado="clienteSeleccionado"
                  label-busqueda="Seleccionar Cliente"
                  placeholder-busqueda="Buscar cliente por nombre, RFC o email..."
                  :requerido="true"
                  :mostrar-opcion-nuevo-cliente="true"
                  :mostrar-estado-cliente="true"
                  :mostrar-info-comercial="true"
                  titulo-cliente-seleccionado="Cliente Seleccionado para Préstamo"
                  mensaje-vacio="Selecciona un cliente para el préstamo"
                  submensaje-vacio="Busca y selecciona un cliente existente o crea uno nuevo"
                  @cliente-seleccionado="onClienteSeleccionado"
                  @crear-nuevo-cliente="onCrearNuevoCliente"
                />
                <p v-if="errors.cliente_id" class="mt-1 text-sm text-red-600">{{ errors.cliente_id }}</p>
              </div>

              <!-- Grid de información financiera -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Monto prestado -->
                <div>
                  <label for="monto_prestado" class="block text-sm font-medium text-gray-700 mb-2">
                    Monto a Prestar *
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      id="monto_prestado"
                      v-model.number="form.monto_prestado"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                      class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                      :class="{ 'border-red-300': errors.monto_prestado }"
                    />
                  </div>
                  <p v-if="errors.monto_prestado" class="mt-1 text-sm text-red-600">{{ errors.monto_prestado }}</p>
                </div>

                <!-- Tasa de interés mensual -->
                <div>
                  <label for="tasa_interes_mensual" class="block text-sm font-medium text-gray-700 mb-2">
                    Tasa de Interés Mensual (%) *
                  </label>
                  <div class="relative">
                    <input
                      id="tasa_interes_mensual"
                      v-model.number="form.tasa_interes_mensual"
                      type="number"
                      step="0.01"
                      min="0"
                      max="100"
                      placeholder="5.00"
                      class="block w-full pr-8 pl-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                      :class="{ 'border-red-300': errors.tasa_interes_mensual }"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">%</span>
                    </div>
                  </div>
                  <p v-if="errors.tasa_interes_mensual" class="mt-1 text-sm text-red-600">{{ errors.tasa_interes_mensual }}</p>
                  <p class="mt-1 text-xs text-gray-500">Tasa de interés que se aplicará cada mes</p>
                </div>

                <!-- Número de pagos -->
                <div>
                  <label for="numero_pagos" class="block text-sm font-medium text-gray-700 mb-2">
                    Número de Pagos *
                  </label>
                  <select
                    id="numero_pagos"
                    v-model="form.numero_pagos"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    :class="{ 'border-red-300': errors.numero_pagos }"
                  >
                    <option v-for="opcion in opcionesNumeroPagos" :key="opcion.value" :value="opcion.value">
                      {{ opcion.label }}
                    </option>
                  </select>
                  <p v-if="errors.numero_pagos" class="mt-1 text-sm text-red-600">{{ errors.numero_pagos }}</p>
                </div>

                <!-- Frecuencia de pago -->
                <div>
                  <label for="frecuencia_pago" class="block text-sm font-medium text-gray-700 mb-2">
                    Frecuencia de Pago *
                  </label>
                  <select
                    id="frecuencia_pago"
                    v-model="form.frecuencia_pago"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  >
                    <option v-for="opcion in opcionesFrecuencia" :key="opcion.value" :value="opcion.value">
                      {{ opcion.label }}
                    </option>
                  </select>
                </div>

                <!-- Fecha de inicio -->
                <div>
                  <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de Inicio *
                  </label>
                  <input
                    id="fecha_inicio"
                    v-model="form.fecha_inicio"
                    type="date"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    :class="{ 'border-red-300': errors.fecha_inicio }"
                  />
                  <p v-if="errors.fecha_inicio" class="mt-1 text-sm text-red-600">{{ errors.fecha_inicio }}</p>
                </div>
              </div>

              <!-- Descripción -->
              <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                  Descripción
                </label>
                <textarea
                  id="descripcion"
                  v-model="form.descripcion"
                  rows="3"
                  placeholder="Descripción del préstamo (opcional)"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                ></textarea>
              </div>

              <!-- Notas -->
              <div>
                <label for="notas" class="block text-sm font-medium text-gray-700 mb-2">
                  Notas Adicionales
                </label>
                <textarea
                  id="notas"
                  v-model="form.notas"
                  rows="3"
                  placeholder="Notas adicionales (opcional)"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                ></textarea>
              </div>

              <!-- Botones de acción -->
              <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <Link
                  href="/prestamos"
                  class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                >
                  ❌ Cancelar
                </Link>
                <button
                  type="submit"
                  :disabled="loading"
                  class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                >
                  <span v-if="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creando...
                  </span>
                  <span v-else>Crear Préstamo</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Panel de cálculos -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-8">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Cálculo de Pagos</h3>
              <p class="text-sm text-gray-600 mt-1">Se actualiza automáticamente</p>
            </div>

            <div class="p-6">
              <div v-if="calculando" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500 mx-auto"></div>
                <p class="text-sm text-gray-600 mt-2">Calculando...</p>
              </div>

              <div v-else-if="form.monto_prestado > 0 && form.numero_pagos > 0">
                <div class="space-y-4">
                  <!-- Pago periódico -->
                  <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-700">Pago {{ form.frecuencia_pago }}:</span>
                    <span class="text-lg font-bold text-green-600">
                      ${{ formatearMoneda(calculos.pago_periodico) }}
                    </span>
                  </div>

                  <!-- Interés total -->
                  <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-700">Interés Total:</span>
                    <span class="text-lg font-semibold text-blue-600">
                      ${{ formatearMoneda(calculos.interes_total) }}
                    </span>
                  </div>

                  <!-- Total a pagar -->
                  <div class="flex justify-between items-center py-3 border-b-2 border-gray-200">
                    <span class="text-sm font-medium text-gray-700">Total a Pagar:</span>
                    <span class="text-xl font-bold text-gray-900">
                      ${{ formatearMoneda(calculos.total_pagar) }}
                    </span>
                  </div>

                  <!-- Información adicional -->
                  <div class="bg-gray-50 rounded-lg p-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Detalles del Préstamo</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                      <div class="flex justify-between">
                        <span>Capital:</span>
                        <span>${{ formatearMoneda(form.monto_prestado) }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span>Tasa de interés mensual:</span>
                        <span>{{ form.tasa_interes_mensual }}%</span>
                      </div>
                      <div class="flex justify-between">
                        <span>Tasa {{ opcionesFrecuencia.find(f => f.value === form.frecuencia_pago)?.label.toLowerCase() }}:</span>
                        <span class="font-medium text-blue-600">{{ formatearMoneda(calculos.tasa_periodica) }}%</span>
                      </div>
                      <div class="flex justify-between">
                        <span>Número de pagos:</span>
                        <span>{{ form.numero_pagos }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span>Frecuencia:</span>
                        <span>{{ opcionesFrecuencia.find(f => f.value === form.frecuencia_pago)?.label }}</span>
                      </div>
                    </div>

                    <!-- Información del cálculo -->
                    <div class="mt-3 pt-3 border-t border-gray-200">
                      <div class="text-xs text-gray-500 mb-2">
                        <strong>Tipo de cálculo:</strong> Amortización francesa con interés compuesto
                      </div>
                      <div class="text-xs text-gray-500 space-y-1">
                        <div>
                          Tasa mensual directa: {{ form.tasa_interes_mensual }}%
                        </div>
                        <div class="text-gray-400">
                          Factor compuesto (1+i)^n: {{ formatearMoneda(calculos.factor_compuesto) }}
                        </div>
                      </div>

                      <!-- Botón para ver detalles del cálculo -->
                      <div class="mt-3">
                        <button
                          @click="mostrarModalDetalles = !mostrarModalDetalles"
                          class="text-xs text-blue-600 hover:text-blue-800 underline"
                        >
                          {{ mostrarModalDetalles ? 'Ocultar' : 'Ver' }} detalles del cálculo
                        </button>
                      </div>

                      <!-- Detalles del cálculo paso a paso -->
                      <div v-if="mostrarModalDetalles && calculos.detalles_calculo" class="mt-3 p-3 bg-blue-50 rounded-lg">
                        <h5 class="text-xs font-medium text-blue-900 mb-2">Cálculo paso a paso:</h5>
                        <div class="text-xs text-blue-800 space-y-1">
                          <div>{{ mostrarDetallesCalculo().paso1 }}</div>
                          <div>{{ mostrarDetallesCalculo().paso2 }}</div>
                          <div>{{ mostrarDetallesCalculo().paso3 }}</div>
                          <div class="font-medium text-blue-900 mt-2">{{ mostrarDetallesCalculo().resultado }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                  <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
                <p class="text-sm text-gray-500">Complete los datos para ver el cálculo</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.prestamos-create {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
