<template>
  <Head title="Cotizaciones" />
  <div class="cotizaciones-index min-h-screen bg-gray-50 p-4">
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
              <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Total: {{ cotizaciones.length }}
            </span>
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Aprobadas: {{ estadisticas.aprobadas }}
            </span>
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Pendientes: {{ estadisticas.pendientes }}
            </span>
          </div>
        </div>

        <!-- Búsqueda y filtros -->
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <!-- Búsqueda -->
          <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar cotizaciones..."
              class="pl-10 pr-4 py-2.5 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <button
              v-if="searchTerm"
              @click="limpiarBusqueda"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Ordenamiento -->
          <select
            v-model="sortBy"
            class="px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="fecha-desc">Fecha descendente</option>
            <option value="fecha-asc">Fecha ascendente</option>
            <option value="total-desc">Total descendente</option>
            <option value="total-asc">Total ascendente</option>
            <option value="cliente-asc">Cliente (A-Z)</option>
            <option value="cliente-desc">Cliente (Z-A)</option>
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

          <!-- Botón limpiar filtros -->
          <button
            v-if="hayFiltrosActivos"
            @click="limpiarTodosFiltros"
            class="px-3 py-2.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla de cotizaciones -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <!-- Encabezado de tabla -->
      <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Cotizaciones</h2>
          <div class="text-sm text-gray-500">
            Mostrando {{ cotizacionesFiltradas.length }} de {{ cotizaciones.length }} cotizaciones
          </div>
        </div>
      </div>

      <!-- Cuerpo de tabla -->
      <div class="min-h-96">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" @click="toggleSort('fecha')">
                <div class="flex items-center">
                  Fecha
                  <svg v-if="sortBy.startsWith('fecha')" :class="sortBy === 'fecha-desc' ? 'rotate-180' : ''" class="w-4 h-4 ml-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" @click="toggleSort('cliente')">
                <div class="flex items-center">
                  Cliente
                  <svg v-if="sortBy.startsWith('cliente')" :class="sortBy === 'cliente-desc' ? 'rotate-180' : ''" class="w-4 h-4 ml-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" @click="toggleSort('total')">
                <div class="flex items-center">
                  Total
                  <svg v-if="sortBy.startsWith('total')" :class="sortBy === 'total-desc' ? 'rotate-180' : ''" class="w-4 h-4 ml-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Productos</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" @click="toggleSort('estado')">
                <div class="flex items-center">
                  Estado
                  <svg v-if="sortBy.startsWith('estado')" :class="sortBy === 'estado-desc' ? 'rotate-180' : ''" class="w-4 h-4 ml-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template v-if="cotizacionesFiltradas.length > 0">
              <tr
                v-for="cotizacion in cotizacionesFiltradas"
                :key="cotizacion.id"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <!-- Columna Fecha y Hora mejorada -->
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

                <!-- Columna Cliente -->
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <div class="text-sm font-medium text-gray-900">
                      {{ cotizacion.cliente?.nombre_razon_social || 'Cliente no asignado' }}
                    </div>
                    <div v-if="cotizacion.cliente?.email" class="text-xs text-gray-500 truncate max-w-xs">
                      {{ cotizacion.cliente.email }}
                    </div>
                  </div>
                </td>

                <!-- Columna Total -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    ${{ formatearMoneda(cotizacion.total) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    IVA incluido
                  </div>
                </td>

                <!-- Columna Productos -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="text-sm text-gray-600">{{ cotizacion.productos.length }} items</span>
                  </div>
                </td>

                <!-- Columna Estado mejorada -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col items-start">
                    <span
                      :class="obtenerClasesEstado(cotizacion.estado)"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mb-1"
                    >
                      <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="obtenerColorPuntoEstado(cotizacion.estado)"></span>
                      {{ obtenerLabelEstado(cotizacion.estado) }}
                    </span>
                    <div class="text-xs text-gray-500">
                      {{ obtenerDescripcionEstado(cotizacion.estado) }}
                    </div>
                  </div>
                </td>

                <!-- Columna Acciones Mejorada -->
<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
  <div class="flex items-center justify-end space-x-1">
    <!-- Ver Detalles -->
    <button
      @click="verDetalles(cotizacion)"
      class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-all duration-200 rounded-lg group relative"
      title="Ver detalles"
    >
      <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
      </svg>
    </button>
 <!-- Editar -->
   <button
      @click="$inertia.visit(`/cotizaciones/${cotizacion.id}/edit`)"
      class="inline-flex items-center justify-center w-8 h-8 text-amber-600 hover:text-amber-700 hover:bg-amber-50 transition-all duration-200 rounded-lg group relative"
      title="Editar cotización"
    >
      <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
      </svg>
    </button>

    <!-- Generar PDF -->
    <button
      @click="generarPDFVenta(cotizacion)"
      class="inline-flex items-center justify-center w-8 h-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200 rounded-lg group relative"
      title="Generar PDF"
    >
      <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
    </button>

    <!-- Eliminar -->
    <button
      @click="confirmarEliminacion(cotizacion.id)"
      class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-700 hover:bg-red-50 transition-all duration-200 rounded-lg group relative"
      title="Eliminar cotización"
    >
      <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
      </svg>
    </button>

    <!-- Menú de opciones adicionales (opcional) -->
    <div class="relative group">
      <button
        class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all duration-200 rounded-lg"
        title="Más opciones"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
      </button>

      <!-- Dropdown menu (oculto por defecto, se puede activar con hover o click) -->
      <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
        <div class="py-1">
          <button
            @click="duplicarCotizacion(cotizacion.id)"
            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            Duplicar
          </button>
          <button
            @click="exportarCotizacion(cotizacion.id)"
            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Exportar
          </button>
          <button
            @click="enviarPorEmail(cotizacion.id)"
            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Enviar por email
          </button>
        </div>
      </div>
    </div>
  </div>
</td>
</tr>
</template>
            <tr v-else>
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center">
                  <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <Transition name="modal">
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
          <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">¿Eliminar cotización?</h3>
            <p class="text-gray-600 text-center mb-6">Esta acción no se puede deshacer. ¿Estás seguro de que deseas eliminar esta cotización?</p>
            <div class="flex gap-3">
              <button
                @click="cancelarEliminacion"
                class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200"
              >
                Cancelar
              </button>
              <button
                @click="eliminarCotizacion"
                :disabled="loading"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center"
              >
                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
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

            <div v-if="selectedCotizacion" class="space-y-6">
              <!-- Información general -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg">
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Fecha</label>
                  <p class="text-sm font-medium text-gray-900">
                    {{ formatearFechaCompleta(selectedCotizacion.created_at || selectedCotizacion.fecha) }}
                  </p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                  <span :class="obtenerClasesEstado(selectedCotizacion.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="obtenerColorPuntoEstado(selectedCotizacion.estado)"></span>
                    {{ obtenerLabelEstado(selectedCotizacion.estado) }}
                  </span>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Total</label>
                  <p class="text-sm font-medium text-gray-900">$ {{ formatearMoneda(selectedCotizacion.total) }}</p>
                </div>
              </div>

              <!-- Información del cliente -->
              <div v-if="selectedCotizacion.cliente" class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Información del Cliente
                </h4>
                <div class="grid grid-cols-1 md:col-2 gap-4">
                  <div>
                    <p class="text-sm">
                      <span class="font-medium text-gray-700">Nombre:</span>
                      {{ selectedCotizacion.cliente.nombre_razon_social }}
                    </p>
                    <p v-if="selectedCotizacion.cliente.email" class="text-sm">
                      <span class="font-medium text-gray-700">Email:</span>
                      {{ selectedCotizacion.cliente.email }}
                    </p>
                    <p v-if="selectedCotizacion.cliente.telefono" class="text-sm">
                      <span class="font-medium text-gray-700">Teléfono:</span>
                      {{ selectedCotizacion.cliente.telefono }}
                    </p>
                  </div>
                  <div>
                    <p v-if="selectedCotizacion.cliente.calle" class="text-sm">
                      <span class="font-medium text-gray-700">Dirección:</span>
                      {{ selectedCotizacion.cliente.calle }}
                    </p>
                    <p v-if="selectedCotizacion.cliente.numero_exterior" class="text-sm">
                      <span class="font-medium text-gray-700">Número:</span>
                      {{ selectedCotizacion.cliente.numero_exterior }}
                    </p>
                    <p v-if="selectedCotizacion.cliente.rfc" class="text-sm">
                      <span class="font-medium text-gray-700">RFC:</span>
                      {{ selectedCotizacion.cliente.rfc }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Productos -->
              <div>
                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                  Productos y Servicios
                </h4>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="producto in selectedCotizacion.productos" :key="producto.id">
                        <td class="px-4 py-3 text-sm text-gray-900">
                          {{ producto.nombre || producto.descripcion }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                          {{ producto.pivot?.cantidad || 1 }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">
                          ${{ formatearMoneda(producto.pivot?.precio || 0) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">
                          {{ producto.pivot?.descuento || 0 }}%
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                          ${{ formatearMoneda((producto.pivot?.cantidad || 1) * (producto.pivot?.precio || 0) * (1 - (producto.pivot?.descuento || 0) / 100)) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Resumen de totales -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-3">Resumen de Totales</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-600">Subtotal:</span>
                      <span class="text-sm font-medium">$ {{ formatearMoneda(selectedCotizacion.subtotal) }}</span>
                    </div>
                    <div v-if="selectedCotizacion.descuento_items > 0" class="flex justify-between text-orange-600">
                      <span class="text-sm">Descuentos por item:</span>
                      <span class="text-sm font-medium">- $ {{ formatearMoneda(selectedCotizacion.descuento_items) }}</span>
                    </div>
                    <div v-if="selectedCotizacion.descuento_general > 0" class="flex justify-between text-orange-600">
                      <span class="text-sm">Descuento general:</span>
                      <span class="text-sm font-medium">- $ {{ formatearMoneda(selectedCotizacion.descuento_general) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-600">Subtotal con descuentos:</span>
                      <span class="text-sm font-medium">$ {{ formatearMoneda(selectedCotizacion.subtotal - selectedCotizacion.descuento_items - selectedCotizacion.descuento_general) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-600">IVA (16%):</span>
                      <span class="text-sm font-medium">$ {{ formatearMoneda(selectedCotizacion.iva) }}</span>
                    </div>
                  </div>
                  <div class="bg-white p-3 rounded-lg">
                    <div class="flex justify-between items-center text-lg font-bold text-gray-900 border-b pb-2 mb-2">
                      <span>Total Final:</span>
                      <span>$ {{ formatearMoneda(selectedCotizacion.total) }}</span>
                    </div>
                    <div class="text-xs text-gray-500 text-center">
                      {{ obtenerDescripcionEstado(selectedCotizacion.estado) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Acciones del modal -->
              <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                <button
                  @click="generarPDFVenta(selectedCotizacion)"
                  class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center justify-center"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Generar PDF
                </button>
                <button
                  @click="cerrarDetalles"
                  class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200"
                >
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { generarPDF } from '@/Utils/pdfGenerator';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  cotizaciones: {
    type: Array,
    default: () => []
  }
});

// Estado reactivo
const searchTerm = ref('');
const sortBy = ref('fecha-desc');
const filtroEstado = ref('');
const loading = ref(false);
const showConfirmationDialog = ref(false);
const cotizacionIdToDelete = ref(null);
const showDetailsDialog = ref(false);
const selectedCotizacion = ref(null);

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

// Data reactiva - Mantener referencia a los datos originales
const cotizacionesOriginales = ref([...props.cotizaciones]);

// Actualizar datos cuando cambien las props
watch(() => props.cotizaciones, (newCotizaciones) => {
  cotizacionesOriginales.value = [...newCotizaciones];
}, { deep: true });

// Computed para estadísticas
const estadisticas = computed(() => {
  const stats = {
    aprobadas: 0,
    pendientes: 0
  };

  cotizacionesOriginales.value.forEach(cotizacion => {
    const estado = cotizacion.estado || 'pendiente';
    if (estado === 'aprobado') {
      stats.aprobadas++;
    } else if (estado === 'pendiente') {
      stats.pendientes++;
    }
  });

  return stats;
});

// Computed para cotizaciones filtradas y ordenadas
const cotizacionesFiltradas = computed(() => {
  let filtered = [...cotizacionesOriginales.value];

  // Aplicar búsqueda
  if (searchTerm.value.trim() !== '') {
    const searchLower = searchTerm.value.toLowerCase().trim();
    filtered = filtered.filter(cotizacion => {
      const cliente = cotizacion.cliente?.nombre_razon_social?.toLowerCase() || '';
      const email = cotizacion.cliente?.email?.toLowerCase() || '';
      const idMatch = cotizacion.id?.toString().includes(searchLower);
      const productosMatch = cotizacion.productos?.some(producto =>
        producto.nombre?.toLowerCase().includes(searchLower) ||
        producto.descripcion?.toLowerCase().includes(searchLower)
      ) || false;

      return cliente.includes(searchLower) ||
             email.includes(searchLower) ||
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
        return (b.total || 0) - (a.total || 0);
      case 'total-asc':
        return (a.total || 0) - (b.total || 0);
      case 'cliente-asc':
        return (a.cliente?.nombre_razon_social || '').localeCompare(b.cliente?.nombre_razon_social || '');
      case 'cliente-desc':
        return (b.cliente?.nombre_razon_social || '').localeCompare(a.cliente?.nombre_razon_social || '');
      default:
        return new Date(b.created_at || b.fecha) - new Date(a.created_at || a.fecha);
    }
  });
});

// Computed para verificar si hay filtros activos
const hayFiltrosActivos = computed(() => {
  return searchTerm.value.trim() !== '' ||
         filtroEstado.value !== '';
});

// Métodos de formateo - VERSIÓN MEJORADA
const formatearFechaCompleta = (fechaHora) => {
  if (!fechaHora) return '';
  const fecha = new Date(fechaHora);
  return fecha.toLocaleDateString('es-MX', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const formatearHoraCompleta = (fechaHora) => {
  if (!fechaHora) return '';
  const fecha = new Date(fechaHora);
  return fecha.toLocaleTimeString('es-MX', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
};

const obtenerTiempoTranscurrido = (fechaHora) => {
  if (!fechaHora) return '';
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
  if (amount === undefined || amount === null) return '0.00';
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

// Métodos para estados - VERSIÓN MEJORADA
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
    'pendiente': 'Cotización pendiente de respuesta',
    'enviado_pedido': 'Cotización convertida en pedido',
    'enviado_venta': 'Cotización convertida en venta',
    'aprobado': 'Cotización aprobada por el cliente',
    'rechazado': 'Cotización rechazada por el cliente',
    'cancelado': 'Cotización cancelada'
  };
  return descripciones[estado] || descripciones['pendiente'];
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
        // Actualizar la lista local
        cotizacionesOriginales.value = cotizacionesOriginales.value.filter(c => c.id !== cotizacionIdToDelete.value);
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
/* Transiciones */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

/* Hover effects mejorados */
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
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
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
</style>
