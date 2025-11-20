<template>
  <Head title="Garantías - Series Vendidas" />

  <div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Series Vendidas</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Lista de todas las series vendidas para gestión de garantías</p>
      </div>
      <div class="flex gap-3">
        <Link
          href="/garantias/create"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200"
        >
          Crear Garantía
        </Link>
        <Link
          href="/garantias?serie="
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
        >
          Buscar por Serie
        </Link>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
      <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-64">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
          <input
            type="text"
            name="search"
            :value="filters.search"
            placeholder="Número de serie, producto, cliente..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div class="min-w-32">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
          <select
            name="estado"
            :value="filters.estado"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Todos</option>
            <option value="vendido">Vendido</option>
            <option value="en_stock">En Stock</option>
          </select>
        </div>
        <div class="min-w-32">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Desde</label>
          <input
            type="date"
            name="fecha_desde"
            :value="filters.fecha_desde"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div class="min-w-32">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
          <input
            type="date"
            name="fecha_hasta"
            :value="filters.fecha_hasta"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div class="flex items-end gap-2">
          <button
            type="submit"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors duration-200"
          >
            Filtrar
          </button>
          <Link
            href="/garantias"
            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200"
          >
            Limpiar
          </Link>
        </div>
      </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Serie
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Producto
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Cliente
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Venta
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Fecha Venta
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Almacén
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Estado
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Estado Garantía
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="serie in seriesVendidas.data" :key="serie.producto_serie_id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ serie.numero_serie }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                  {{ serie.producto_nombre }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ serie.producto_codigo }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                  {{ serie.cliente_nombre }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ serie.cliente_email }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                  #{{ serie.numero_venta }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                  {{ new Date(serie.venta_fecha).toLocaleDateString('es-ES') }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                  {{ serie.almacen_nombre || 'Sin almacén' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-red-100 text-red-800': serie.estado_serie === 'vendido',
                    'bg-green-100 text-green-800': serie.estado_serie === 'en_stock'
                  }"
                >
                  {{ serie.estado_serie === 'vendido' ? 'Vendido' : 'En Stock' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="serie.cita_id" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                  📅 Enviada a Cita #{{ serie.cita_id }}
                </span>
                <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                  Pendiente
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                  <button
                    v-if="!serie.cita_id"
                    @click="crearCitaGarantia(serie.producto_serie_id)"
                    class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                    title="Crear cita de garantía"
                  >
                    📅 Crear Cita
                  </button>
                  <Link
                    v-if="serie.cita_id"
                    :href="route('citas.edit', serie.cita_id)"
                    class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                    title="Ver cita asociada"
                  >
                    👁️ Ver Cita #{{ serie.cita_id }}
                  </Link>
                  <Link
                    :href="route('ventas.show', serie.venta_id)"
                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                  >
                    Ver Venta
                  </Link>
                  <Link
                    :href="route('clientes.show', serie.cliente_id)"
                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                  >
                    Ver Cliente
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700 dark:text-gray-300">
            Mostrando {{ seriesVendidas.from }} a {{ seriesVendidas.to }} de {{ seriesVendidas.total }} resultados
          </div>
          <div class="flex gap-1">
            <Link
              v-if="seriesVendidas.prev_page_url"
              :href="seriesVendidas.prev_page_url"
              class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              Anterior
            </Link>
            <Link
              v-if="seriesVendidas.next_page_url"
              :href="seriesVendidas.next_page_url"
              class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              Siguiente
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
  layout: AppLayout,
})

const props = defineProps({
  seriesVendidas: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const crearCitaGarantia = async (serieId) => {
  try {
    const response = await fetch(`/garantias/${serieId}/crear-cita`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    const data = await response.json()

    if (response.ok && data.success) {
      // Redirigir a la página de creación de cita con los parámetros
      window.location.href = data.url
    } else {
      // Mostrar mensaje de error
      const mensaje = data.mensaje || data.error || 'No se pudo crear la cita'
      alert(mensaje)
      
      // Si ya existe una cita, recargar la página para actualizar el estado
      if (data.cita_id) {
        window.location.reload()
      }
    }
  } catch (error) {
    console.error('Error al crear cita de garantía:', error)
    alert('Error interno del servidor')
  }
}
</script>