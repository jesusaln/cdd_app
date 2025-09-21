<!-- /resources/js/Pages/Reportes/CorteDiario.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
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
  ventasPagadas: { type: Array, default: () => [] },
  totalesPorMetodo: { type: Object, default: () => ({}) },
  totalGeneral: { type: Number, default: 0 },
  periodo: { type: String, default: 'diario' },
  periodoLabel: { type: String, default: 'Diario' },
  fecha: { type: String, default: '' },
  fecha_inicio: { type: String, default: '' },
  fecha_fin: { type: String, default: '' },
  fechaFormateada: { type: String, default: '' }
})

// Estado
const periodoSeleccionado = ref(props.periodo)
const fechaSeleccionada = ref(props.fecha)
const fechaInicioSeleccionada = ref(props.fecha_inicio)
const fechaFinSeleccionada = ref(props.fecha_fin)

// Helpers
const formatNumber = (num) => new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num)

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

const obtenerLabelMetodoPago = (metodo) => {
  const labels = {
    'efectivo': 'Efectivo',
    'transferencia': 'Transferencia',
    'cheque': 'Cheque',
    'tarjeta': 'Tarjeta',
    'otros': 'Otros'
  }
  return labels[metodo] || metodo
}

// Cambiar período
const cambiarPeriodo = (nuevoPeriodo) => {
  periodoSeleccionado.value = nuevoPeriodo
  const params = { periodo: nuevoPeriodo, fecha: fechaSeleccionada.value }
  if (nuevoPeriodo === 'personalizado') {
    params.fecha_inicio = fechaInicioSeleccionada.value
    params.fecha_fin = fechaFinSeleccionada.value
  }
  router.get(route('reportes.corte-diario'), params, { preserveState: true })
}

// Cambiar fecha
const cambiarFecha = (nuevaFecha) => {
  fechaSeleccionada.value = nuevaFecha
  const params = { periodo: periodoSeleccionado.value, fecha: nuevaFecha }
  if (periodoSeleccionado.value === 'personalizado') {
    params.fecha_inicio = fechaInicioSeleccionada.value
    params.fecha_fin = fechaFinSeleccionada.value
  }
  router.get(route('reportes.corte-diario'), params, { preserveState: true })
}

// Cambiar fechas personalizadas
const cambiarFechasPersonalizadas = () => {
  const params = {
    periodo: 'personalizado',
    fecha_inicio: fechaInicioSeleccionada.value,
    fecha_fin: fechaFinSeleccionada.value
  }
  router.get(route('reportes.corte-diario'), params, { preserveState: true })
}

// Navegar a período anterior
const fechaAnterior = () => {
  const fecha = new Date(fechaSeleccionada.value)
  if (periodoSeleccionado.value === 'diario') {
    fecha.setDate(fecha.getDate() - 1)
  } else if (periodoSeleccionado.value === 'semanal') {
    fecha.setDate(fecha.getDate() - 7)
  } else if (periodoSeleccionado.value === 'mensual') {
    fecha.setMonth(fecha.getMonth() - 1)
  } else if (periodoSeleccionado.value === 'anual') {
    fecha.setFullYear(fecha.getFullYear() - 1)
  }
  cambiarFecha(fecha.toISOString().split('T')[0])
}

// Navegar a período siguiente
const fechaSiguiente = () => {
  const fecha = new Date(fechaSeleccionada.value)
  if (periodoSeleccionado.value === 'diario') {
    fecha.setDate(fecha.getDate() + 1)
  } else if (periodoSeleccionado.value === 'semanal') {
    fecha.setDate(fecha.getDate() + 7)
  } else if (periodoSeleccionado.value === 'mensual') {
    fecha.setMonth(fecha.getMonth() + 1)
  } else if (periodoSeleccionado.value === 'anual') {
    fecha.setFullYear(fecha.getFullYear() + 1)
  }
  cambiarFecha(fecha.toISOString().split('T')[0])
}

// Ir a período actual
const irAFechaActual = () => {
  cambiarFecha(new Date().toISOString().split('T')[0])
}

// Exportar reporte
const exportarCorte = () => {
  const params = new URLSearchParams()
  params.append('periodo', periodoSeleccionado.value)
  params.append('fecha', fechaSeleccionada.value)
  if (periodoSeleccionado.value === 'personalizado') {
    params.append('fecha_inicio', fechaInicioSeleccionada.value)
    params.append('fecha_fin', fechaFinSeleccionada.value)
  }
  params.append('tipo', 'corte_diario')
  const url = route('reportes.export') + `?${params.toString()}`
  window.location.href = url
}
</script>

<template>
  <Head title="Corte de Pagos" />

  <div class="corte-diario min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
          <!-- Izquierda -->
          <div class="flex flex-col gap-6 w-full lg:w-auto">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-900">Corte {{ periodoLabel }} de Pagos</h1>
            </div>

            <!-- Selector de período -->
            <div class="flex items-center gap-4">
              <label class="text-sm font-medium text-slate-700">Período:</label>
              <select
                v-model="periodoSeleccionado"
                @change="cambiarPeriodo(periodoSeleccionado)"
                class="px-4 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
              >
                <option value="diario">Diario</option>
                <option value="semanal">Semanal</option>
                <option value="mensual">Mensual</option>
                <option value="anual">Anual</option>
                <option value="personalizado">Personalizado</option>
              </select>
            </div>

            <!-- Fechas personalizadas -->
            <div v-if="periodoSeleccionado === 'personalizado'" class="flex items-center gap-4">
              <label class="text-sm font-medium text-slate-700">Desde:</label>
              <input
                v-model="fechaInicioSeleccionada"
                type="date"
                class="px-4 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
              />
              <label class="text-sm font-medium text-slate-700">Hasta:</label>
              <input
                v-model="fechaFinSeleccionada"
                type="date"
                class="px-4 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
              />
              <button
                @click="cambiarFechasPersonalizadas"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Aplicar
              </button>
            </div>

            <!-- Navegación de períodos -->
            <div v-if="periodoSeleccionado !== 'personalizado'" class="flex items-center gap-4">
              <button
                @click="fechaAnterior"
                class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"
                :title="`Período anterior (${periodoLabel.toLowerCase()})`"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <div class="flex items-center gap-3">
                <input
                  v-model="fechaSeleccionada"
                  @change="cambiarFecha(fechaSeleccionada)"
                  type="date"
                  class="px-4 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                />
                <span class="text-sm text-slate-600 font-medium">{{ fechaFormateada }}</span>
              </div>

              <button
                @click="fechaSiguiente"
                class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"
                :title="`Período siguiente (${periodoLabel.toLowerCase()})`"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>

              <button
                @click="irAFechaActual"
                class="px-4 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors text-sm font-medium"
              >
                Actual
              </button>
            </div>

            <!-- Resumen de totales -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
              <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="text-xs font-medium text-green-700 uppercase tracking-wider">Total General</div>
                <div class="text-2xl font-bold text-green-900">${{ formatNumber(totalGeneral) }}</div>
              </div>

              <div v-for="(total, metodo) in totalesPorMetodo" :key="metodo" class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="text-xs font-medium text-blue-700 uppercase tracking-wider">{{ obtenerLabelMetodoPago(metodo) }}</div>
                <div class="text-lg font-bold text-blue-900">${{ formatNumber(total) }}</div>
              </div>
            </div>
          </div>

          <!-- Derecha: Acciones -->
          <div class="flex flex-col gap-3 w-full lg:w-auto">
            <button
              @click="exportarCorte"
              class="inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
              </svg>
              <span>Exportar</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla de ventas pagadas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">N° Venta</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Método de Pago</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha de Pago</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Registrado por</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="venta in ventasPagadas" :key="venta.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ venta.numero_venta }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ venta.cliente }}</div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ obtenerLabelMetodoPago(venta.metodo_pago) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatearFecha(venta.fecha_pago) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-semibold text-gray-900">${{ formatNumber(venta.total) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ venta.pagado_por }}</div>
                  <div v-if="venta.notas_pago" class="text-xs text-gray-500 mt-1">{{ venta.notas_pago }}</div>
                </td>
              </tr>
              <tr v-if="ventasPagadas.length === 0">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-gray-700 font-medium">No hay pagos registrados</p>
                      <p class="text-sm text-gray-500">No se encontraron ventas pagadas para esta fecha</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Resumen final -->
        <div v-if="ventasPagadas.length > 0" class="bg-gray-50 border-t border-gray-200 px-6 py-4">
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Total de pagos del {{ periodoLabel.toLowerCase() }}:</span>
            <span class="text-xl font-bold text-gray-900">${{ formatNumber(totalGeneral) }}</span>
          </div>
          <div class="mt-2 text-xs text-gray-500">
            {{ ventasPagadas.length }} venta{{ ventasPagadas.length !== 1 ? 's' : '' }} pagada{{ ventasPagadas.length !== 1 ? 's' : '' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.corte-diario {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media (max-width: 640px) {
  .corte-diario .max-w-8xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .corte-diario h1 {
    font-size: 1.5rem;
  }
}
</style>
