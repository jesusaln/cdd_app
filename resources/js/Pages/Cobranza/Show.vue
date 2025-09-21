<!-- /resources/js/Pages/Cobranza/Show.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

defineOptions({ layout: AppLayout })

// Notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false },
    { type: 'warning', background: '#f59e0b', icon: false }
  ]
})

// Props
const props = defineProps({
  cobranza: { type: Object, required: true }
})

// Estado
const showModal = ref(false)
const modalMode = ref('details')

// Computed
const estadoColor = computed(() => {
  const colores = {
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'pagado': 'bg-green-100 text-green-800',
    'parcial': 'bg-blue-100 text-blue-800',
    'vencido': 'bg-red-100 text-red-800',
    'cancelado': 'bg-gray-100 text-gray-800'
  }
  return colores[props.cobranza.estado] || 'bg-gray-100 text-gray-800'
})

const estadoLabel = computed(() => {
  const labels = {
    'pendiente': 'Pendiente',
    'pagado': 'Pagado',
    'parcial': 'Parcial',
    'vencido': 'Vencido',
    'cancelado': 'Cancelado'
  }
  return labels[props.cobranza.estado] || 'Pendiente'
})

// Métodos
const editarCobranza = () => {
  router.visit(route('cobranza.edit', props.cobranza.id))
}

const volver = () => {
  router.visit(route('cobranza.index'))
}

const marcarPagada = () => {
  const fechaPago = prompt('Fecha de pago:', new Date().toISOString().split('T')[0])
  if (!fechaPago) return

  const montoPagado = prompt('Monto pagado:', props.cobranza.monto_cobrado.toString())
  if (!montoPagado || isNaN(montoPagado)) return

  const metodoPago = prompt('Método de pago (efectivo, transferencia, tarjeta, cheque):', 'transferencia')
  if (!metodoPago) return

  const referenciaPago = prompt('Referencia de pago (opcional):', '')

  router.post(route('cobranza.marcar-pagada', props.cobranza.id), {
    fecha_pago: fechaPago,
    monto_pagado: parseFloat(montoPagado),
    metodo_pago: metodoPago,
    referencia_pago: referenciaPago,
  }, {
    onSuccess: () => {
      notyf.success('Cobranza marcada como pagada')
      window.location.reload()
    },
    onError: (errors) => {
      notyf.error('Error al marcar como pagada')
    }
  })
}
</script>

<template>
  <Head title="Detalles de Cobranza" />

  <div class="cobranza-show min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button
              @click="volver"
              class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <div>
              <h1 class="text-2xl font-bold text-slate-900">Detalles de Cobranza</h1>
              <p class="text-slate-600 mt-1">Información completa de la cobranza</p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <span :class="estadoColor" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
              {{ estadoLabel }}
            </span>
          </div>
        </div>
      </div>

      <!-- Contenido -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información del Cobro -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Cobro</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Concepto</label>
                  <p class="mt-1 text-sm text-gray-900">{{ props.cobranza.concepto }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Fecha de Cobro</label>
                  <p class="mt-1 text-sm text-gray-900">{{ formatearFecha(props.cobranza.fecha_cobro) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Monto a Cobrar</label>
                  <p class="mt-1 text-lg font-semibold text-gray-900">${{ formatNumber(props.cobranza.monto_cobrado) }}</p>
                </div>
              </div>
              <div class="space-y-4">
                <div v-if="props.cobranza.fecha_pago">
                  <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                  <p class="mt-1 text-sm text-gray-900">{{ formatearFecha(props.cobranza.fecha_pago) }}</p>
                </div>
                <div v-if="props.cobranza.monto_pagado">
                  <label class="block text-sm font-medium text-gray-700">Monto Pagado</label>
                  <p class="mt-1 text-lg font-semibold text-green-600">${{ formatNumber(props.cobranza.monto_pagado) }}</p>
                </div>
                <div v-if="props.cobranza.metodo_pago">
                  <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                  <p class="mt-1 text-sm text-gray-900 capitalize">{{ props.cobranza.metodo_pago }}</p>
                </div>
                <div v-if="props.cobranza.referencia_pago">
                  <label class="block text-sm font-medium text-gray-700">Referencia</label>
                  <p class="mt-1 text-sm text-gray-900">{{ props.cobranza.referencia_pago }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Información de la Renta -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información de la Renta</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Número de Contrato</label>
                  <p class="mt-1 text-sm text-gray-900">{{ props.cobranza.renta?.numero_contrato }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Cliente</label>
                  <p class="mt-1 text-sm text-gray-900">{{ props.cobranza.renta?.cliente?.nombre_razon_social }}</p>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Monto Mensual</label>
                  <p class="mt-1 text-sm text-gray-900">${{ formatNumber(props.cobranza.renta?.monto_mensual) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Estado de la Renta</label>
                  <p class="mt-1 text-sm text-gray-900 capitalize">{{ props.cobranza.renta?.estado }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notas -->
          <div v-if="props.cobranza.notas" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Notas</h2>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ props.cobranza.notas }}</p>
          </div>
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
          <!-- Acciones -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Acciones</h2>
            <div class="space-y-3">
              <button
                @click="editarCobranza"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar Cobranza
              </button>

              <button
                v-if="props.cobranza.estado === 'pendiente' || props.cobranza.estado === 'parcial'"
                @click="marcarPagada"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Marcar como Pagada
              </button>

              <button
                @click="volver"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-medium"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver al Listado
              </button>
            </div>
          </div>

          <!-- Información del Sistema -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Sistema</h2>
            <div class="space-y-3 text-sm">
              <div>
                <span class="text-gray-500">ID:</span>
                <span class="text-gray-900 ml-2">#{{ props.cobranza.id }}</span>
              </div>
              <div>
                <span class="text-gray-500">Creado:</span>
                <span class="text-gray-900 ml-2">{{ formatearFecha(props.cobranza.created_at) }}</span>
              </div>
              <div v-if="props.cobranza.updated_at !== props.cobranza.created_at">
                <span class="text-gray-500">Actualizado:</span>
                <span class="text-gray-900 ml-2">{{ formatearFecha(props.cobranza.updated_at) }}</span>
              </div>
              <div v-if="props.cobranza.responsable_cobro">
                <span class="text-gray-500">Responsable:</span>
                <span class="text-gray-900 ml-2">{{ props.cobranza.responsable_cobro }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const formatNumber = (num) => {
  if (!num) return '0'
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const d = new Date(date)
    return d.toLocaleDateString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return 'Fecha inválida'
  }
}
</script>

<style scoped>
.cobranza-show {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
