<template>
  <Head title="Ventas Pendientes de Pago" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Ventas Pendientes de Pago</h1>
            <p class="text-sm text-gray-600 mt-1">Ventas que están en proceso pero aún no han sido pagadas</p>
          </div>
          <Link
            href="/reportes"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a Reportes
          </Link>
        </div>
      </div>

      <!-- Estadísticas -->
      <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600">{{ formatNumber(estadisticas.total) }}</div>
            <div class="text-sm text-yellow-600">Ventas Sin Pagar</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-red-600">{{ formatCurrency(estadisticas.totalMonto) }}</div>
            <div class="text-sm text-red-600">Monto por Cobrar</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ formatNumber(estadisticas.aprobadas) }}</div>
            <div class="text-sm text-blue-600">Listas para Pago</div>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-gray-600">{{ formatNumber(estadisticas.borrador) }}</div>
            <div class="text-sm text-gray-600">En Proceso</div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <!-- Búsqueda -->
          <div class="relative flex-1 max-w-md">
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar por cliente o número de venta..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              @input="handleSearch"
            />
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Filtro de Estado -->
          <select
            v-model="filtroEstado"
            @change="handleEstadoChange"
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos los Estados</option>
            <option value="borrador">Borrador</option>
            <option value="pendiente">Pendiente</option>
            <option value="aprobada">Aprobada</option>
          </select>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número Venta</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="venta in ventasFiltradas" :key="venta.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(venta.fecha || venta.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ venta.numero_venta }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ venta.cliente?.nombre_razon_social || 'Sin cliente' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatCurrency(venta.total) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getEstadoClass(venta.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ getEstadoLabel(venta.estado) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex space-x-2">
                  <Link
                    :href="route('ventas.show', venta.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Ver
                  </Link>
                  <Link
                    v-if="venta.estado === 'borrador' || venta.estado === 'pendiente'"
                    :href="route('ventas.edit', venta.id)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Editar
                  </Link>
                </div>
              </td>
            </tr>
            <tr v-if="ventasFiltradas.length === 0">
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center space-y-4">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <div class="space-y-1">
                    <p class="text-gray-700 font-medium">No hay ventas pendientes de pago</p>
                    <p class="text-sm text-gray-500">Todas las ventas han sido procesadas</p>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="paginationData.lastPage > 1" class="bg-white border-t border-gray-200 px-4 py-3 sm:px-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <p class="text-sm text-gray-700">
              Mostrando {{ paginationData.from }} - {{ paginationData.to }} de {{ paginationData.total }} resultados
            </p>
          </div>

          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
            <button
              v-if="paginationData.prevPageUrl"
              @click="handlePageChange(paginationData.currentPage - 1)"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>

            <button
              v-for="page in [paginationData.currentPage - 1, paginationData.currentPage, paginationData.currentPage + 1].filter(p => p > 0 && p <= paginationData.lastPage)"
              :key="page"
              @click="handlePageChange(page)"
              :class="page === paginationData.currentPage ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
              class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
            >
              {{ page }}
            </button>

            <button
              v-if="paginationData.nextPageUrl"
              @click="handlePageChange(paginationData.currentPage + 1)"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  ventas: { type: [Object, Array], required: true },
  estadisticas: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

// Estado local
const searchTerm = ref(props.filters?.search || '')
const filtroEstado = ref(props.filters?.estado || '')

// Datos
const ventasPaginator = computed(() => props.ventas)
const ventasData = computed(() => ventasPaginator.value?.data || [])

// Estadísticas
const estadisticas = computed(() => ({
  total: props.estadisticas?.total || 0,
  totalMonto: props.estadisticas?.total_monto || 0,
  aprobadas: props.estadisticas?.aprobadas || 0,
  borrador: props.estadisticas?.borrador || 0,
}))

// Filtrado del lado del cliente (básico)
const ventasFiltradas = computed(() => {
  let result = [...ventasData.value]

  if (searchTerm.value.trim()) {
    const search = searchTerm.value.toLowerCase().trim()
    result = result.filter(venta => {
      const cliente = venta.cliente?.nombre_razon_social?.toLowerCase() || ''
      const numero = String(venta.numero_venta || '').toLowerCase()
      return cliente.includes(search) || numero.includes(search)
    })
  }

  if (filtroEstado.value) {
    result = result.filter(venta => venta.estado === filtroEstado.value)
  }

  return result
})

// Paginación
const paginationData = computed(() => {
  const p = ventasPaginator.value || {}
  return {
    currentPage: p.current_page ?? 1,
    lastPage: p.last_page ?? 1,
    perPage: p.per_page ?? 10,
    from: p.from ?? 0,
    to: p.to ?? 0,
    total: p.total ?? 0,
    prevPageUrl: p.prev_page_url ?? null,
    nextPageUrl: p.next_page_url ?? null,
    links: p.links ?? []
  }
})

// Handlers
const handleSearch = () => {
  router.get(route('reportes.ventas-pendientes'), {
    search: searchTerm.value,
    estado: filtroEstado.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const handleEstadoChange = () => {
  router.get(route('reportes.ventas-pendientes'), {
    search: searchTerm.value,
    estado: filtroEstado.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const handlePageChange = (page) => {
  router.get(route('reportes.ventas-pendientes'), {
    search: searchTerm.value,
    estado: filtroEstado.value,
    page: page
  }, { preserveState: true, preserveScroll: true })
}

// Helpers
const formatNumber = (num) => {
  return new Intl.NumberFormat('es-ES').format(num || 0)
}

const formatCurrency = (num) => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(num || 0)
}

const formatDate = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const d = new Date(date)
    return d.toLocaleDateString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch {
    return 'Fecha inválida'
  }
}

const getEstadoClass = (estado) => {
  const clases = {
    'borrador': 'bg-gray-100 text-gray-700',
    'pendiente': 'bg-yellow-100 text-yellow-700',
    'aprobada': 'bg-blue-100 text-blue-700',
    'cancelada': 'bg-red-100 text-red-700'
  }
  return clases[estado] || 'bg-gray-100 text-gray-700'
}

const getEstadoLabel = (estado) => {
  const labels = {
    'borrador': 'Borrador',
    'pendiente': 'Pendiente',
    'aprobada': 'Aprobada',
    'cancelada': 'Cancelada'
  }
  return labels[estado] || 'Desconocido'
}
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
