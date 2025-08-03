```vue
<template>
  <Head title="Cotizaciones" />
  <div class="cotizaciones-index min-h-screen bg-gray-50 p-4 relative">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Cotizaciones</h1>
      <p class="text-gray-600">Administra y gestiona todas tus cotizaciones de manera eficiente</p>
    </div>

    <!-- Controles superiores -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <!-- Botón crear y estadísticas -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <Link
            :href="route('cotizaciones.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition-all duration-200 shadow-lg"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Cotización
          </Link>

          <div class="flex items-center gap-4 text-sm text-gray-600">
            <span class="flex items-center">
              <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
              Total: {{ cotizacionesOriginales.length }}
            </span>
            <span class="flex items-center">
              <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
              Filtradas: {{ cotizacionesFiltradas.length }}
            </span>
          </div>
        </div>

        <!-- Búsqueda y filtros -->
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
          <div class="relative">
            <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              id="searchTerm"
              v-model="searchTerm"
              type="text"
              placeholder="Buscar por cliente, ID o producto..."
              class="pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 w-full sm:w-80"
            />
            <button
              v-if="searchTerm"
              @click="limpiarBusqueda"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <select v-model="sortBy" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="fecha-desc">Más recientes</option>
            <option value="fecha-asc">Más antiguas</option>
            <option value="total-desc">Mayor valor</option>
            <option value="total-asc">Menor valor</option>
            <option value="cliente">Por cliente</option>
            <option value="id-desc">ID descendente</option>
            <option value="id-asc">ID ascendente</option>
          </select>

          <!-- Filtro por estado -->
          <select
            v-model="filtroEstado"
            class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="enviado_pedido">Enviado a Pedido</option>
            <option value="enviado_venta">Enviado a Venta</option>
            <option value="aprobado">Aprobadas</option>
            <option value="rechazado">Rechazadas</option>
            <option value="cancelado">Canceladas</option>
          </select>
        </div>
      </div>

      <!-- Indicadores de filtros activos -->
      <div v-if="hayFiltrosActivos" class="mt-4 flex flex-wrap gap-2">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          Filtros activos
        </span>
        <button
          v-if="searchTerm"
          @click="limpiarBusqueda"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200"
        >
          Búsqueda: "{{ searchTerm }}"
          <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <button
          v-if="filtroEstado"
          @click="filtroEstado = ''"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200"
        >
          Estado: {{ obtenerLabelEstado(filtroEstado) }}
          <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <button
          @click="limpiarTodosFiltros"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200"
        >
          Limpiar todos
          <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Tabla de cotizaciones -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div v-if="cotizacionesFiltradas.length" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <button @click="toggleSort('fecha')" class="flex items-center hover:text-gray-700">
                  Fecha y Hora
                  <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                </button>
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Productos/Servicios</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <button @click="toggleSort('total')" class="flex items-center hover:text-gray-700">
                  Total
                  <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                </button>
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="cotizacion in cotizacionesFiltradas"
              :key="cotizacion.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <!-- Columna Fecha y Hora -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatearFechaCompleta(cotizacion.created_at || cotizacion.fecha) }}
                  </div>
                  <div class="text-xs text-gray-500 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ formatearHoraCompleta(cotizacion.created_at || cotizacion.fecha) }}
                  </div>
                  <div class="text-xs text-gray-400 mt-1">
                    {{ obtenerTiempoTranscurrido(cotizacion.created_at || cotizacion.fecha) }}
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  #{{ cotizacion.id }}
                </span>
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                      <span class="text-sm font-medium text-white">
                        {{ cotizacion.cliente.nombre_razon_social.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ cotizacion.cliente.nombre_razon_social }}</div>
                    <div class="text-sm text-gray-500" v-if="cotizacion.cliente.email">{{ cotizacion.cliente.email }}</div>
                  </div>
                </div>
              </td>

              <!-- Columna Productos/Servicios -->
             <td
  class="px-6 py-4"
  @mouseenter="manejarMouseEnter($event, cotizacion)"
  @mouseleave="manejarMouseLeave"
  @wheel.prevent
>
                <div class="max-w-xs">
                  <div class="text-sm text-gray-900 font-medium mb-1">
                    {{ itemsDeCotizacion(cotizacion).length }} elemento(s)
                  </div>
                  <div class="space-y-1">
                    <div
                      v-for="(item, index) in itemsDeCotizacion(cotizacion).slice(0, 2)"
                      :key="`${item.tipo}-${item.id}`"
                      class="flex items-center text-xs"
                    >
                      <span
                        :class="item.tipo === 'producto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mr-2"
                      >
                        {{ item.tipo === 'producto' ? 'P' : 'S' }}
                      </span>
                      <span class="text-gray-600 truncate">{{ item.nombre }}</span>
                    </div>
                    <div v-if="itemsDeCotizacion(cotizacion).length > 2" class="text-xs text-gray-500">
                      +{{ itemsDeCotizacion(cotizacion).length - 2 }} más...
                    </div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-gray-900">${{ formatearMoneda(cotizacion.total) }}</div>
              </td>

              <!-- Columna Estado -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col items-start">
                  <span :class="obtenerClasesEstado(cotizacion.estado)"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mb-1">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5"
                          :class="obtenerColorPuntoEstado(cotizacion.estado)"></span>
                    {{ obtenerLabelEstado(cotizacion.estado) }}
                  </span>
                  <div class="text-xs text-gray-500">
                    {{ obtenerDescripcionEstado(cotizacion.estado) }}
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button
                    @click="verDetalles(cotizacion)"
                    class="text-blue-600 hover:text-blue-900 transition-colors p-1 rounded-full hover:bg-blue-50"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>

                  <button
                    @click="editarCotizacion(cotizacion.id)"
                    class="text-indigo-600 hover:text-indigo-900 transition-colors p-1 rounded-full hover:bg-indigo-50"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <button
                    @click="generarPDFVenta(cotizacion)"
                    class="text-purple-600 hover:text-purple-900 transition-colors p-1 rounded-full hover:bg-purple-50"
                    title="Generar PDF"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>

                  <button
                    @click="confirmarEliminacion(cotizacion.id)"
                    class="text-red-600 hover:text-red-900 transition-colors p-1 rounded-full hover:bg-red-50"
                    title="Eliminar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estado vacío -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay cotizaciones</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hayFiltrosActivos ? 'No se encontraron cotizaciones con los criterios seleccionados.' : 'Comienza creando tu primera cotización.' }}
        </p>
        <div class="mt-6">
          <button
            v-if="hayFiltrosActivos"
            @click="limpiarTodosFiltros"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3"
          >
            Limpiar filtros
          </button>
          <Link
            v-if="!hayFiltrosActivos"
            :href="route('cotizaciones.create')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Cotización
          </Link>
        </div>
      </div>
    </div>

    <!-- Tooltip global -->
    <!-- Tooltip global mejorado -->
<Teleport to="body">
  <Transition name="tooltip">
    <div
  v-if="showTooltip"
  :style="{ top: tooltipPosition.y + 'px', left: tooltipPosition.x + 'px' }"
  class="fixed bg-white border border-gray-200 rounded-xl shadow-2xl p-0 w-96 max-h-80 overflow-hidden z-[9999] tooltip-container custom-scrollbar"
  @mouseenter="() => { if (tooltipTimeout.value) clearTimeout(tooltipTimeout.value); }"
  @mouseleave="ocultarTooltip"
>
      <!-- Header del tooltip -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-semibold text-gray-900 flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Productos y Servicios
          </h4>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ tooltipContent.length }} items
          </span>
        </div>
      </div>

      <!-- Contenido del tooltip -->
      <div class="max-h-64 overflow-y-auto custom-scrollbar"
      @wheel.stop
  @touchmove.stop>
        <div class="p-2 space-y-1">
          <div
            v-for="(item, index) in tooltipContent"
            :key="`${item.tipo}-${item.id}`"
            class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
            :class="{ 'mb-1': index < tooltipContent.length - 1 }"
          >
            <!-- Icono/Badge del tipo -->
            <div class="flex-shrink-0 mr-3">
              <div
                :class="item.tipo === 'producto' ? 'bg-blue-500 text-white' : 'bg-green-500 text-white'"
                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm group-hover:scale-110 transition-transform duration-200"
              >
                <svg v-if="item.tipo === 'producto'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
              </div>
            </div>

            <!-- Información del item -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between mb-1">
                <h5 class="text-sm font-medium text-gray-900 truncate group-hover:text-blue-600 transition-colors duration-200">
                  {{ item.nombre }}
                </h5>
                <span
                  :class="item.tipo === 'producto' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-green-50 text-green-700 border-green-200'"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border ml-2 flex-shrink-0"
                >
                  {{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                </span>
              </div>

              <!-- Información adicional si está disponible -->
              <div class="flex items-center text-xs text-gray-500 space-x-3">
                <span v-if="item.cantidad" class="flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                  </svg>
                  Cant: {{ item.cantidad }}
                </span>
                <span v-if="item.precio" class="flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                  </svg>
                  ${{ formatearMoneda(item.precio) }}
                </span>
              </div>
            </div>

            <!-- Indicador de hover -->
            <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer si hay muchos elementos -->
      <div v-if="tooltipContent.length > 5" class="border-t border-gray-100 px-4 py-2 bg-gray-50">
        <p class="text-xs text-gray-500 text-center">
          Mostrando {{ Math.min(5, tooltipContent.length) }} de {{ tooltipContent.length }} elementos
        </p>
      </div>

      <!-- Flecha del tooltip -->
      <div
        class="absolute w-3 h-3 bg-white border-l border-t border-gray-200 transform rotate-45 tooltip-arrow"
        :style="tooltipArrowStyle"
      ></div>
    </div>
  </Transition>
</Teleport>

    <!-- Spinner de carga -->
    <Transition name="fade">
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="text-gray-700 font-medium">Procesando...</span>
        </div>
      </div>
    </Transition>

    <!-- Modal de confirmación de eliminación -->
    <Transition name="modal">
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
          <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Confirmar eliminación</h3>
            <p class="text-sm text-gray-500 text-center mb-6">
              ¿Estás seguro de que deseas eliminar esta cotización? Esta acción no se puede deshacer.
            </p>
            <div class="flex space-x-3">
              <button
                @click="cancelarEliminacion"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancelar
              </button>
              <button
                @click="eliminarCotizacion"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal de detalles -->
    <Transition name="modal">
      <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">Detalles de la Cotización</h3>
              <button
                @click="cerrarDetalles"
                class="text-gray-400 hover:text-gray-600 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <Show :cotizacion="selectedCotizacion" @convertir-a-pedido="handleConvertirAPedido" />

            <div class="flex justify-end mt-6 space-x-3">
              <button
                @click="cerrarDetalles"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue'; // Ensure nextTick is imported
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { generarPDF } from '@/Utils/pdfGenerator';
import Show from './Show.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  cotizaciones: {
    type: Array,
    default: () => []
  }
});

// Add these to existing reactive state
const tooltipArrowStyle = ref({});
const tooltipUpdateTimeout = ref(null);



const manejarMouseEnter = (event, cotizacion) => {
  // Limpiar cualquier timeout pendiente
  if (tooltipTimeout.value) {
    clearTimeout(tooltipTimeout.value);
    tooltipTimeout.value = null;
  }

  // Mostrar tooltip
  mostrarTooltip(event, cotizacion);
};

const manejarMouseLeave = () => {
  // Programar el cierre del tooltip después de 200ms
  tooltipTimeout.value = setTimeout(() => {
    ocultarTooltip();
  }, 200); // 200ms es suficiente para mover el ratón al tooltip
};

// Estado reactivo
const searchTerm = ref('');
const sortBy = ref('fecha-desc');
const filtroEstado = ref('');
const loading = ref(false);
const showConfirmationDialog = ref(false);
const cotizacionIdToDelete = ref(null);
const showDetailsDialog = ref(false);
const selectedCotizacion = ref(null);
const showTooltip = ref(false);
const tooltipContent = ref([]);
const tooltipPosition = ref({ x: 0, y: 0 });
const tooltipTimeout = ref(null);

// Configuración de notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: '#10b981',
      icon: false
    },
    {
      type: 'error',
      background: '#ef4444',
      icon: false
    }
  ]
});

const actualizarTooltipPosition = (event) => {
  if (!showTooltip.value) return;

  // Obtener la posición actual del elemento respecto a la ventana
  const tdElement = event.currentTarget;
  const rect = tdElement.getBoundingClientRect();
  const offsetX = 10; // Desplazamiento horizontal
  const offsetY = 10; // Desplazamiento vertical
  let x = rect.left + window.scrollX + offsetX;
  let y = rect.bottom + window.scrollY + offsetY;

  // Ajustar para no salirse de la ventana
  const tooltipWidth = 320; // Ancho del tooltip (w-80 = 320px)
  const tooltipHeight = 256; // Alto máximo estimado (max-h-64 = 256px)
  if (x + tooltipWidth > window.innerWidth) {
    x = window.innerWidth - tooltipWidth - 10;
  }
  if (y + tooltipHeight > window.innerHeight) {
    y = window.innerHeight - tooltipHeight - 10;
  }

  tooltipPosition.value = { x, y };
};

// Data reactiva - Mantener referencia a los datos originales
const cotizacionesOriginales = ref([...props.cotizaciones]);

// Actualizar datos cuando cambien las props
watch(() => props.cotizaciones, (newCotizaciones) => {
  cotizacionesOriginales.value = [...newCotizaciones];
}, { deep: true });

const cotizacionesFiltradas = computed(() => {
  let filtered = [...cotizacionesOriginales.value];

  // Aplicar filtro de búsqueda
  if (searchTerm.value.trim()) {
    const searchLower = searchTerm.value.trim().toLowerCase();
    filtered = filtered.filter(cotizacion => {
      const nombreCliente = cotizacion.cliente?.nombre_razon_social?.toLowerCase() || '';
      const emailCliente = cotizacion.cliente?.email?.toLowerCase() || '';
      const idMatch = cotizacion.id.toString().includes(searchLower);
      const productosMatch = cotizacion.productos?.some(item =>
        item.nombre?.toLowerCase().includes(searchLower) ||
        item.tipo?.toLowerCase().includes(searchLower)
      ) || false;

      return nombreCliente.includes(searchLower) ||
             emailCliente.includes(searchLower) ||
             idMatch ||
             productosMatch;
    });
  }

  // Aplicar filtro de estado
  if (filtroEstado.value) {
    filtered = filtered.filter(cotizacion => {
      const estado = cotizacion.estado || 'pendiente';
      return estado.toLowerCase() === filtroEstado.value.toLowerCase();
    });
  }

  // Aplicar ordenamiento
  return filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'fecha-desc':
        return new Date(b.created_at || b.fecha) - new Date(a.created_at || a.fecha);
      case 'fecha-asc':
        return new Date(a.created_at || a.fecha) - new Date(b.created_at || b.fecha);
      case 'total-desc':
        return parseFloat(b.total || 0) - parseFloat(a.total || 0);
      case 'total-asc':
        return parseFloat(a.total || 0) - parseFloat(b.total || 0);
      case 'cliente':
        const nombreA = a.cliente?.nombre_razon_social || '';
        const nombreB = b.cliente?.nombre_razon_social || '';
        return nombreA.localeCompare(nombreB);
      case 'id-desc':
        return b.id - a.id;
      case 'id-asc':
        return a.id - b.id;
      default:
        return new Date(b.created_at || b.fecha) - new Date(a.created_at || a.fecha);
    }
  });
});

// Computed para verificar si hay filtros activos
const hayFiltrosActivos = computed(() => {
  return searchTerm.value.trim() !== '' || filtroEstado.value !== '';
});

// Métodos de formateo
const formatearFechaCompleta = (fechaHora) => {
  const fecha = new Date(fechaHora);
  return fecha.toLocaleDateString('es-MX', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const formatearHoraCompleta = (fechaHora) => {
  const fecha = new Date(fechaHora);
  return fecha.toLocaleTimeString('es-MX', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
};

const obtenerTiempoTranscurrido = (fechaHora) => {
  const ahora = new Date();
  const fecha = new Date(fechaHora);
  const diferencia = ahora - fecha;

  const minutos = Math.floor(diferencia / (1000 * 60));
  const horas = Math.floor(diferencia / (1000 * 60 * 60));
  const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));

  if (dias > 0) {
    return `hace ${dias} día${dias > 1 ? 's' : ''}`;
  } else if (horas > 0) {
    return `hace ${horas} hora${horas > 1 ? 's' : ''}`;
  } else if (minutos > 0) {
    return `hace ${minutos} min${minutos > 1 ? 's' : ''}`;
  } else {
    return 'ahora mismo';
  }
};

const formatearMoneda = (amount) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

// Métodos para estados
const obtenerClasesEstado = (estado) => {
  const estados = {
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'enviado_pedido': 'bg-blue-100 text-blue-800',
    'enviado_venta': 'bg-indigo-100 text-indigo-800',
    'aprobado': 'bg-green-100 text-green-800',
    'rechazado': 'bg-red-100 text-red-800',
    'cancelado': 'bg-gray-100 text-gray-800'
  };
  return estados[estado] || estados['pendiente'];
};

const obtenerColorPuntoEstado = (estado) => {
  const colores = {
    'pendiente': 'bg-yellow-400',
    'enviado_pedido': 'bg-blue-400',
    'enviado_venta': 'bg-indigo-400',
    'aprobado': 'bg-green-400',
    'rechazado': 'bg-red-400',
    'cancelado': 'bg-gray-400'
  };
  return colores[estado] || colores['pendiente'];
};

const obtenerLabelEstado = (estado) => {
  const labels = {
    'pendiente': 'Pendiente',
    'enviado_pedido': 'Enviado a Pedido',
    'enviado_venta': 'Enviado a Venta',
    'aprobado': 'Aprobada',
    'rechazado': 'Rechazada',
    'cancelado': 'Cancelada'
  };
  return labels[estado] || labels['pendiente'];
};

const obtenerDescripcionEstado = (estado) => {
  const descripciones = {
    'pendiente': 'Esperando respuesta',
    'enviado_pedido': 'Convertida a pedido',
    'enviado_venta': 'Convertida a venta',
    'aprobado': 'Cliente aprobó',
    'rechazado': 'Cliente rechazó',
    'cancelado': 'Proceso cancelado'
  };
  return descripciones[estado] || descripciones['pendiente'];
};

// Métodos de navegación
const editarCotizacion = (id) => {
  router.get(`/cotizaciones/${id}/edit`);
};

// Métodos de filtros
const limpiarBusqueda = () => {
  searchTerm.value = '';
};

const limpiarTodosFiltros = () => {
  searchTerm.value = '';
  filtroEstado.value = '';
  sortBy.value = 'fecha-desc';
};

// Métodos de eliminación
const confirmarEliminacion = (id) => {
  cotizacionIdToDelete.value = id;
  showConfirmationDialog.value = true;
};

const cancelarEliminacion = () => {
  cotizacionIdToDelete.value = null;
  showConfirmationDialog.value = false;
};

const eliminarCotizacion = async () => {
  if (!cotizacionIdToDelete.value) return;

  loading.value = true;

  try {
    await router.delete(`/cotizaciones/${cotizacionIdToDelete.value}`, {
      onSuccess: () => {
        notyf.success('Cotización eliminada exitosamente');
        cotizacionesOriginales.value = cotizacionesOriginales.value.filter(
          c => c.id !== cotizacionIdToDelete.value
        );
        showConfirmationDialog.value = false;
        cotizacionIdToDelete.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la cotización');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');
  } finally {
    loading.value = false;
  }
};

// Métodos de detalles
const verDetalles = (cotizacion) => {
  selectedCotizacion.value = cotizacion;
  showDetailsDialog.value = true;
};

const cerrarDetalles = () => {
  selectedCotizacion.value = null;
  showDetailsDialog.value = false;
};

// Conversión a pedido
const handleConvertirAPedido = async (cotizacionData) => {
  loading.value = true;
  try {
    await router.post(`/cotizaciones/${cotizacionData.id}/convertir-a-pedido`, {
      onSuccess: () => {
        notyf.success('Cotización convertida a pedido exitosamente');
        if (confirm('¿Deseas ir al índice de pedidos?')) {
          router.get('/pedidos');
        }
        cerrarDetalles();
        const index = cotizacionesOriginales.value.findIndex(c => c.id === cotizacionData.id);
        if (index !== -1) {
          cotizacionesOriginales.value[index].estado = 'enviado_pedido';
        }
      },
      onError: (errors) => {
        console.error('Error al convertir:', errors);
        notyf.error('Error al convertir la cotización');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');
  } finally {
    loading.value = false;
  }
};

// Añade este computed
const itemsDeCotizacion = (cotizacion) => {
  const productos = cotizacion.productos?.map(p => ({ ...p, tipo: 'producto' })) || [];
  const servicios = cotizacion.servicios?.map(s => ({ ...s, tipo: 'servicio' })) || [];
  return [...productos, ...servicios];
};

// Métodos para el tooltip
const mostrarTooltip = (event, cotizacion) => {
  tooltipContent.value = itemsDeCotizacion(cotizacion);
  showTooltip.value = true;

  // Usar nextTick para asegurar que el DOM se ha actualizado
  nextTick(() => {
    posicionarTooltip(event);
  });

  // Añadir listeners de scroll y resize
  window.addEventListener('scroll', actualizarTooltipPosition);
  window.addEventListener('resize', actualizarTooltipPosition);
};

// New posicionarTooltip function
const posicionarTooltip = (event) => {
  const tdElement = event.currentTarget;
  const rect = tdElement.getBoundingClientRect();
  const tooltipWidth = 384; // w-96 = 384px
  const tooltipHeight = 320; // max-h-80 = 320px
  const arrowSize = 6;

  let x = rect.left + window.scrollX;
  let y = rect.bottom + window.scrollY + 10;
  let arrowPosition = 'top';
  let arrowLeft = '20px';

  // Verificar si el tooltip se sale por la derecha
  if (x + tooltipWidth > window.innerWidth + window.scrollX) {
    x = window.innerWidth + window.scrollX - tooltipWidth - 10;
  }

  // Verificar si el tooltip se sale por la izquierda
  if (x < window.scrollX + 10) {
    x = window.scrollX + 10;
  }

  // Calcular la posición de la flecha en el eje X
  const elementCenter = rect.left + rect.width / 2;
  const tooltipLeft = x;
  arrowLeft = Math.max(20, Math.min(tooltipWidth - 32, elementCenter - tooltipLeft)) + 'px';

  // Verificar si el tooltip se sale por abajo
  if (y + tooltipHeight > window.innerHeight + window.scrollY) {
    y = rect.top + window.scrollY - tooltipHeight - 10;
    arrowPosition = 'bottom';
  }

  tooltipPosition.value = { x, y };

  // Configurar el estilo de la flecha
  if (arrowPosition === 'top') {
    tooltipArrowStyle.value = {
      top: '-6px',
      left: arrowLeft,
      borderBottomColor: 'white',
      borderRightColor: 'transparent'
    };
  } else {
    tooltipArrowStyle.value = {
      bottom: '-6px',
      left: arrowLeft,
      borderTopColor: 'white',
      borderLeftColor: 'transparent'
    };
  }
};

// Replace ocultarTooltip
const ocultarTooltip = () => {
  showTooltip.value = false;
  tooltipContent.value = [];
  if (tooltipUpdateTimeout.value) {
    clearTimeout(tooltipUpdateTimeout.value);
    tooltipUpdateTimeout.value = null;
  }
  window.removeEventListener('scroll', actualizarTooltipPosition);
  window.removeEventListener('resize', actualizarTooltipPosition);
};

watch(showTooltip, (newValue) => {
  if (newValue) {
    window.addEventListener('resize', actualizarTooltipPosition);
  } else {
    window.removeEventListener('resize', actualizarTooltipPosition);
  }
});

// Generación de PDF
const generarPDFVenta = async (cotizacion) => {
  try {
    loading.value = true;
    await generarPDF('Cotización', cotizacion);
    notyf.success('PDF generado exitosamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error('Error al generar el PDF');
  } finally {
    loading.value = false;
  }
};

// Métodos de ordenamiento
const toggleSort = (field) => {
  if (sortBy.value === `${field}-desc`) {
    sortBy.value = `${field}-asc`;
  } else {
    sortBy.value = `${field}-desc`;
  }
};
</script>

<style scoped>
/* Transiciones generales */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Animaciones del tooltip */
.tooltip-enter-active {
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.tooltip-enter-from {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.tooltip-leave-active {
  transition: all 0.15s ease-in;
}

.tooltip-leave-to {
  opacity: 0;
  transform: translateY(-5px) scale(0.98);
}

/* Contenedor del tooltip */
.tooltip-container {
  backdrop-filter: blur(8px);
  background: rgba(255, 255, 255, 0.98);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04),
    0 0 0 1px rgba(0, 0, 0, 0.05);
  border-radius: 12px;
}

.tooltip-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
  border-radius: inherit;
  pointer-events: none;
}

/* Scrollbar personalizado para el tooltip */
.custom-scroll-container::-webkit-scrollbar {
  width: 8px;
}

.custom-scroll-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.custom-scroll-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.custom-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}
/* Scrollbar general para otros elementos */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Flecha del tooltip */
.tooltip-arrow {
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.custom-scroll-container {
  max-height: 300px; /* Define un alto máximo para el contenedor */
  overflow-y: auto; /* Permite el scroll vertical */
  border: 1px solid #ccc; /* Opcional: solo para visualizar el contenedor */
  padding: 10px;
}

/* Efectos de hover mejorados */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.group:hover .group-hover\:text-blue-600 {
  color: #2563eb;
}

.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}

/* Animación para elementos del tooltip */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.tooltip-container > div > div > div {
  animation: slideInUp 0.2s ease-out;
  animation-fill-mode: both;
}

.tooltip-container > div > div > div:nth-child(2) {
  animation-delay: 0.05s;
}

.tooltip-container > div > div > div:nth-child(3) {
  animation-delay: 0.1s;
}

.tooltip-container > div > div > div:nth-child(4) {
  animation-delay: 0.15s;
}

.tooltip-container > div > div > div:nth-child(5) {
  animation-delay: 0.2s;
}

/* Hover effects para filas de la tabla */
.table-row-hover:hover {
  background-color: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

/* Botones con efectos mejorados */
.btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* Efectos de loading */
.loading-shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Efectos de focus mejorados */
.focus-ring:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
  border-color: #3b82f6;
}

/* Animación de entrada para elementos */
.slide-up-enter-active {
  transition: all 0.4s ease;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

/* Efectos para badges */
.badge-animate {
  transition: all 0.2s ease;
}

.badge-animate:hover {
  transform: scale(1.05);
}

/* Estilos para el tooltip global */
.fixed.bg-white.border-gray-200.rounded-lg.shadow-lg {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.2s ease-in-out forwards;
}

@keyframes fadeIn {
  to {
    opacity: 1;
  }
}
</style>
