<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  tecnico: { type: Object, required: true },
  herramientas: { type: Array, required: true },
  estadisticas: { type: Object, required: true },
})

const getEstadoColor = (estado) => {
  const colors = {
    'disponible': 'bg-green-100 text-green-800',
    'asignada': 'bg-blue-100 text-blue-800',
    'mantenimiento': 'bg-yellow-100 text-yellow-800',
    'baja': 'bg-red-100 text-red-800',
    'perdida': 'bg-red-100 text-red-800',
  }
  return colors[estado] || 'bg-gray-100 text-gray-800'
}

const getEstadoLabel = (estado) => {
  const labels = {
    'disponible': 'Disponible',
    'asignada': 'Asignada',
    'mantenimiento': 'En Mant.',
    'baja': 'De Baja',
    'perdida': 'Perdida',
  }
  return labels[estado] || estado
}

const getCondicionColor = (condicion) => {
  const colors = {
    'excelente': 'bg-green-100 text-green-800',
    'buena': 'bg-blue-100 text-blue-800',
    'regular': 'bg-yellow-100 text-yellow-800',
    'mala': 'bg-red-100 text-red-800',
    'critica': 'bg-red-100 text-red-800',
  }
  return colors[condicion] || 'bg-gray-100 text-gray-800'
}

const imprimirReporte = () => {
  window.print()
}

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A'
  return new Date(fecha).toLocaleDateString('es-MX', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <Head title="Reporte de Herramientas por Técnico" />

  <div class="max-w-4xl mx-auto p-6 bg-white">
    <!-- Encabezado del reporte -->
    <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">REPORTE DE HERRAMIENTAS ASIGNADAS</h1>
      <p class="text-lg text-gray-600">Sistema de Gestión de Herramientas</p>
      <p class="text-sm text-gray-500 mt-2">{{ formatearFecha(tecnico.fecha_exportacion) }}</p>
    </div>

    <!-- Información del técnico -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-gray-50 p-4 rounded-lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Información del Técnico</h2>
        <div class="space-y-2">
          <p><span class="font-medium">Nombre:</span> {{ tecnico.nombre_completo }}</p>
          <p><span class="font-medium">Email:</span> {{ tecnico.email || 'N/A' }}</p>
          <p><span class="font-medium">Teléfono:</span> {{ tecnico.telefono || 'N/A' }}</p>
          <p><span class="font-medium">ID del Técnico:</span> {{ tecnico.id }}</p>
        </div>
      </div>

      <div class="bg-blue-50 p-4 rounded-lg">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Estadísticas</h2>
        <div class="space-y-2">
          <p><span class="font-medium">Total de Herramientas:</span> {{ estadisticas.total_herramientas }}</p>
          <p><span class="font-medium">Valor Total:</span> ${{ estadisticas.valor_total?.toLocaleString('es-MX', { minimumFractionDigits: 2 }) || '0.00' }}</p>
          <p><span class="font-medium">Requieren Mantenimiento:</span> {{ estadisticas.herramientas_mantenimiento }}</p>
          <p><span class="font-medium">Próximas a Vencer:</span> {{ estadisticas.herramientas_por_vencer }}</p>
        </div>
      </div>
    </div>

    <!-- Tabla de herramientas -->
    <div class="overflow-hidden border border-gray-300 rounded-lg">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">ID</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Herramienta</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Categoría</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Estado</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Costo</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Último Mant.</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Condición</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">Observaciones</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="herramienta in herramientas" :key="herramienta.id" class="hover:bg-gray-50">
            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">{{ herramienta.id }}</td>
            <td class="px-3 py-4 text-sm text-gray-900">
              <div>
                <div class="font-medium">{{ herramienta.nombre }}</div>
                <div class="text-gray-500 text-xs">{{ herramienta.numero_serie }}</div>
              </div>
            </td>
            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">{{ herramienta.categoria }}</td>
            <td class="px-3 py-4 whitespace-nowrap">
              <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', getEstadoColor(herramienta.estado)]">
                {{ getEstadoLabel(herramienta.estado) }}
              </span>
            </td>
            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
              ${{ herramienta.costo_reemplazo?.toLocaleString('es-MX', { minimumFractionDigits: 2 }) || '0.00' }}
            </td>
            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">{{ herramienta.fecha_ultimo_mantenimiento || 'N/A' }}</td>
            <td class="px-3 py-4 whitespace-nowrap">
              <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', getCondicionColor(herramienta.condicion_ultima_inspeccion)]">
                {{ herramienta.condicion_ultima_inspeccion || 'Sin inspección' }}
              </span>
            </td>
            <td class="px-3 py-4 text-sm text-gray-900">
              <span v-if="herramienta.necesita_mantenimiento" class="text-red-600 font-medium">⚠ Requiere mantenimiento</span>
              <span v-else-if="herramienta.porcentaje_vida_util > 80" class="text-orange-600 font-medium">Próxima a vencer</span>
              <span v-else class="text-green-600">✓ En buen estado</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Sin herramientas -->
    <div v-if="herramientas.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
      </svg>
      <p class="text-gray-500 text-lg">No hay herramientas asignadas a este técnico</p>
    </div>

    <!-- Sección de firmas -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Firma del técnico -->
      <div class="border-t-2 border-gray-400 pt-6">
        <div class="text-center">
          <p class="text-sm text-gray-600 mb-4">Firma del Técnico</p>
          <div class="border-b-2 border-gray-400 h-16 mb-2 flex items-center justify-center">
            <p class="text-gray-500 italic">Firma electrónica</p>
          </div>
          <p class="text-sm text-gray-600">
            {{ tecnico.nombre_completo }}<br>
            Fecha: {{ new Date().toLocaleDateString('es-MX') }}
          </p>
        </div>
      </div>

      <!-- Firma del supervisor -->
      <div class="border-t-2 border-gray-400 pt-6">
        <div class="text-center">
          <p class="text-sm text-gray-600 mb-4">Firma del Supervisor</p>
          <div class="border-b-2 border-gray-400 h-16 mb-2 flex items-center justify-center">
            <p class="text-gray-500 italic">Firma electrónica</p>
          </div>
          <p class="text-sm text-gray-600">
            ___________________________<br>
            Fecha: ______________________
          </p>
        </div>
      </div>
    </div>

    <!-- Información adicional -->
    <div class="mt-8 text-xs text-gray-500 text-center">
      <p>Este reporte fue generado automáticamente por el Sistema de Gestión de Herramientas.</p>
      <p>Reporte generado el {{ formatearFecha(tecnico.fecha_exportacion) }}</p>
    </div>

    <!-- Botones de acción (solo visibles en pantalla) -->
    <div class="mt-8 flex justify-center gap-4 print:hidden">
      <button
        @click="imprimirReporte"
        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
      >
        🖨️ Imprimir Reporte
      </button>
      <Link
        :href="`/herramientas/gestion/${tecnico.id}/descargar`"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
      >
        📥 Descargar CSV
      </Link>
      <Link
        :href="`/herramientas/gestion/${tecnico.id}/edit`"
        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium"
      >
        ← Volver a Gestión
      </Link>
    </div>
  </div>
</template>

<style scoped>
@media print {
  .print\\:hidden {
    display: none !important;
  }

  body {
    font-size: 12px;
  }

  .bg-gray-50, .bg-blue-50 {
    background-color: #f9fafb !important;
    -webkit-print-color-adjust: exact;
  }

  .border-gray-300 {
    border-color: #d1d5db !important;
    -webkit-print-color-adjust: exact;
  }

  .text-gray-900 {
    color: #111827 !important;
    -webkit-print-color-adjust: exact;
  }

  .text-gray-600 {
    color: #4b5563 !important;
    -webkit-print-color-adjust: exact;
  }

  .text-gray-500 {
    color: #6b7280 !important;
    -webkit-print-color-adjust: exact;
  }
}
</style>