<template>
  <Head title="Órdenes de Compra" />
  <div class="ordenes-compra-index p-6 max-w-7xl mx-auto">
    <!-- Header con título y estadísticas -->
    <div class="mb-8">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Órdenes de Compra</h1>
          <p class="text-gray-600">Gestiona y supervisa todas las órdenes de compra</p>
        </div>
        <div class="flex gap-4">
          <div class="text-center bg-blue-50 p-3 rounded-lg min-w-[80px]">
            <div class="text-2xl font-bold text-blue-600">{{ estadisticas.total }}</div>
            <div class="text-xs text-blue-600">Total</div>
          </div>
          <div class="text-center bg-yellow-50 p-3 rounded-lg min-w-[80px]">
            <div class="text-2xl font-bold text-yellow-600">{{ estadisticas.pendientes }}</div>
            <div class="text-xs text-yellow-600">Pendientes</div>
          </div>
          <div class="text-center bg-green-50 p-3 rounded-lg min-w-[80px]">
            <div class="text-2xl font-bold text-green-600">{{ estadisticas.recibidas }}</div>
            <div class="text-xs text-green-600">Recibidas</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Controles: Crear, Buscar y Filtros -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <!-- Botón crear y filtros -->
        <div class="flex flex-wrap gap-3 items-center">
          <Link
            :href="route('ordenescompra.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all duration-200 shadow-sm"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Orden
          </Link>

          <!-- Filtro por estado -->
          <select
            v-model="filtroEstado"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
          >
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="recibida">Recibidas</option>
            <option value="cancelada">Canceladas</option>
          </select>
        </div>

        <!-- Búsqueda -->
        <div class="relative w-full lg:w-80">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input
            id="searchTerm"
            v-model="searchTerm"
            type="text"
            placeholder="Buscar por proveedor, producto o ID..."
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
          />
          <button
            v-if="searchTerm"
            @click="searchTerm = ''"
            class="absolute inset-y-0 right-0 pr-3 flex items-center"
          >
            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla de órdenes de compra -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="ordenesCompraFiltradas.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <button @click="ordenarPor('id')" class="flex items-center hover:text-gray-700">
                  ID
                  <svg v-if="ordenActual === 'id'" class="w-4 h-4 ml-1" :class="{ 'rotate-180': direccionOrden === 'desc' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  </svg>
                </button>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <button @click="ordenarPor('total')" class="flex items-center hover:text-gray-700">
                  Total
                  <svg v-if="ordenActual === 'total'" class="w-4 h-4 ml-1" :class="{ 'rotate-180': direccionOrden === 'desc' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  </svg>
                </button>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="orden in ordenesCompraFiltradas" :key="orden.id" class="hover:bg-gray-50 transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                #{{ String(orden.id).padStart(4, '0') }}
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">
                      {{ orden.proveedor?.nombre_razon_social || 'Proveedor no especificado' }}
                    </div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  <div class="flex flex-wrap gap-1 max-w-xs">
                    <span
                      v-for="(item, index) in orden.items?.slice(0, 2)"
                      :key="item.id + item.tipo"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="item.tipo === 'producto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                    >
                      {{ item.nombre }}
                    </span>
                    <span
                      v-if="orden.items && orden.items.length > 2"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                    >
                      +{{ orden.items.length - 2 }} más
                    </span>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                ${{ formatearMoneda(orden.total) }}
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getEstadoClases(orden.estado)">
                  <span class="w-1.5 h-1.5 mr-1.5 rounded-full" :class="getEstadoPunto(orden.estado)"></span>
                  {{ formatearEstado(orden.estado) }}
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatearFecha(orden.created_at) }}
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <!-- Botón Ver Detalles -->
                  <button
                    @click="verDetallesOrdenCompra(orden)"
                    class="inline-flex items-center p-1.5 text-gray-400 hover:text-blue-600 transition-colors duration-150"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>

                  <!-- Botón Editar -->
                  <button
                    @click="editarOrdenCompra(orden.id)"
                    class="inline-flex items-center p-1.5 text-gray-400 hover:text-blue-600 transition-colors duration-150"
                    title="Editar orden"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>

                  <!-- Botón Recibir (solo para pendientes) -->
                  <button
                    v-if="orden.estado === 'pendiente'"
                    @click="confirmarRecepcionOrden(orden.id)"
                    class="inline-flex items-center p-1.5 text-gray-400 hover:text-green-600 transition-colors duration-150"
                    title="Marcar como recibida"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </button>

                  <!-- Menú desplegable para más acciones -->
                  <div class="relative" v-click-outside="() => closeDropdown(orden.id)">
                    <button
                      @click="toggleDropdown(orden.id)"
                      class="inline-flex items-center p-1.5 text-gray-400 hover:text-gray-600 transition-colors duration-150"
                    >
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                      </svg>
                    </button>

                    <div
                      v-if="dropdownOpen === orden.id"
                      class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200"
                    >
                      <div class="py-1">
                        <button
                          @click="duplicarOrden(orden)"
                          class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                          <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                          </svg>
                          Duplicar orden
                        </button>
                        <button
                          @click="exportarOrden(orden)"
                          class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                          <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                          </svg>
                          Exportar PDF
                        </button>
                        <hr class="my-1">
                        <button
                          @click="confirmarEliminacionOrden(orden.id)"
                          class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                        >
                          <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                          </svg>
                          Eliminar orden
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estado vacío mejorado -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay órdenes de compra</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ searchTerm || filtroEstado ? 'No se encontraron órdenes con los filtros aplicados.' : 'Comienza creando tu primera orden de compra.' }}
        </p>
        <div class="mt-6">
          <Link
            v-if="!searchTerm && !filtroEstado"
            :href="route('ordenescompra.create')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Orden de Compra
          </Link>
          <button
            v-else
            @click="limpiarFiltros"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Limpiar filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Paginación (si es necesaria) -->
    <div v-if="ordenesCompraFiltradas.length > 0" class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Mostrando {{ ordenesCompraFiltradas.length }} de {{ ordenesCompra.length }} órdenes
      </div>
    </div>

    <!-- Modales -->
    <!-- Modal de confirmación de eliminación -->
    <Modal :show="showConfirmationDialog" @close="cancelarEliminacionOrden">
      <div class="p-6">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Confirmar eliminación</h3>
        <p class="text-sm text-gray-500 text-center mb-6">
          ¿Estás seguro de que deseas eliminar esta orden de compra? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="cancelarEliminacionOrden"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancelar
          </button>
          <button
            @click="eliminarOrdenCompra"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Eliminando...
            </span>
            <span v-else>Eliminar</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- Modal de confirmación de recepción -->
    <Modal :show="showReceiveConfirmationDialog" @close="cancelarRecepcionOrden">
      <div class="p-6">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full mb-4">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Confirmar recepción</h3>
        <p class="text-sm text-gray-500 text-center mb-6">
          ¿Confirmas la recepción de esta orden de compra? Se actualizará el inventario automáticamente.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="cancelarRecepcionOrden"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancelar
          </button>
          <button
            @click="recibirOrdenCompra"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Procesando...
            </span>
            <span v-else>Confirmar Recepción</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- Modal de detalles mejorado -->
    <Modal :show="showDetailsDialog" @close="cerrarDetallesOrdenCompra" max-width="4xl">
      <div class="p-6" v-if="selectedOrdenCompra">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-xl font-semibold text-gray-900">
              Orden de Compra #{{ String(selectedOrdenCompra.id).padStart(4, '0') }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">{{ formatearFecha(selectedOrdenCompra.created_at) }}</p>
          </div>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="getEstadoClases(selectedOrdenCompra.estado)">
            <span class="w-2 h-2 mr-2 rounded-full" :class="getEstadoPunto(selectedOrdenCompra.estado)"></span>
            {{ formatearEstado(selectedOrdenCompra.estado) }}
          </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Información del proveedor -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-900 mb-3">Información del Proveedor</h4>
            <div class="space-y-2">
              <div class="flex items-center">
                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">
                  {{ selectedOrdenCompra.proveedor?.nombre_razon_social || 'No especificado' }}
                </span>
              </div>
              <div v-if="selectedOrdenCompra.proveedor?.email" class="flex items-center">
                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm text-gray-600">{{ selectedOrdenCompra.proveedor.email }}</span>
              </div>
              <div v-if="selectedOrdenCompra.proveedor?.telefono" class="flex items-center">
                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <span class="text-sm text-gray-600">{{ selectedOrdenCompra.proveedor.telefono }}</span>
              </div>
            </div>
          </div>

          <!-- Resumen financiero -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-900 mb-3">Resumen Financiero</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Subtotal:</span>
                <span class="text-sm font-medium">${{ formatearMoneda(calcularSubtotal(selectedOrdenCompra)) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Impuestos:</span>
                <span class="text-sm font-medium">${{ formatearMoneda(calcularImpuestos(selectedOrdenCompra)) }}</span>
              </div>
              <div class="border-t border-gray-200 pt-2">
                <div class="flex justify-between">
                  <span class="text-base font-medium text-gray-900">Total:</span>
                  <span class="text-base font-bold text-gray-900">${{ formatearMoneda(selectedOrdenCompra.total) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de items -->
        <div class="mt-6">
          <h4 class="font-medium text-gray-900 mb-4">Items de la Orden</h4>
          <div class="overflow-hidden bg-white border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="item in selectedOrdenCompra.items" :key="item.id + item.tipo" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ item.nombre }}</div>
                    <div v-if="item.descripcion" class="text-sm text-gray-500">{{ item.descripcion }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="item.tipo === 'producto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                      {{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right text-sm text-gray-900">
                    {{ item.pivot?.cantidad || '0' }}
                  </td>
                  <td class="px-4 py-3 text-right text-sm text-gray-900">
                    ${{ formatearMoneda(item.pivot?.precio || 0) }}
                  </td>
                  <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                    ${{ formatearMoneda((item.pivot?.cantidad || 0) * (item.pivot?.precio || 0)) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
          <div class="flex space-x-3">
            <button
              v-if="selectedOrdenCompra.estado === 'pendiente'"
              @click="confirmarRecepcionOrden(selectedOrdenCompra.id); cerrarDetallesOrdenCompra()"
              class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Marcar como Recibida
            </button>

            <button
              @click="editarOrdenCompra(selectedOrdenCompra.id); cerrarDetallesOrdenCompra()"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Editar Orden
            </button>
          </div>

          <button
            @click="cerrarDetallesOrdenCompra"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cerrar
          </button>
        </div>
      </div>
    </Modal>

    <!-- Loading overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
        <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-900">Procesando...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue'; // Asume que tienes un componente Modal

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Reemplaza la línea actual de defineProps con esta:
const props = defineProps({
  ordenesCompra: Array,
  errors: Object,
  jetstream: Object,
  auth: Object,
  errorBags: Object,
  flash: Object
});

// Estado reactivo
const searchTerm = ref('');
const filtroEstado = ref('');
const loading = ref(false);
const dropdownOpen = ref(null);

// Ordenamiento
const ordenActual = ref('id');
const direccionOrden = ref('desc');

// Control de modales
const showConfirmationDialog = ref(false);
const ordenCompraIdToDelete = ref(null);
const showReceiveConfirmationDialog = ref(false);
const ordenCompraIdToReceive = ref(null);
const showDetailsDialog = ref(false);
const selectedOrdenCompra = ref(null);

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

// Variable reactiva local para almacenar las órdenes de compra
const ordenesCompra = ref([...props.ordenesCompra]);

// Computadas
const estadisticas = computed(() => ({
  total: ordenesCompra.value.length,
  pendientes: ordenesCompra.value.filter(o => o.estado === 'pendiente').length,
  recibidas: ordenesCompra.value.filter(o => o.estado === 'recibida').length,
  canceladas: ordenesCompra.value.filter(o => o.estado === 'cancelada').length
}));

const ordenesCompraFiltradas = computed(() => {
  let ordenes = [...ordenesCompra.value];

  // Filtro por búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    ordenes = ordenes.filter(orden => {
      const proveedorMatch = orden.proveedor?.nombre_razon_social?.toLowerCase().includes(term);
      const idMatch = orden.id.toString().includes(term);
      const itemMatch = orden.items?.some(item =>
        item.nombre?.toLowerCase().includes(term)
      );
      return proveedorMatch || idMatch || itemMatch;
    });
  }

  // Filtro por estado
  if (filtroEstado.value) {
    ordenes = ordenes.filter(orden => orden.estado === filtroEstado.value);
  }

  // Ordenamiento
  ordenes.sort((a, b) => {
    let aVal = a[ordenActual.value];
    let bVal = b[ordenActual.value];

    if (ordenActual.value === 'total') {
      aVal = parseFloat(aVal) || 0;
      bVal = parseFloat(bVal) || 0;
    }

    if (direccionOrden.value === 'asc') {
      return aVal > bVal ? 1 : -1;
    } else {
      return aVal < bVal ? 1 : -1;
    }
  });

  return ordenes;
});

// Métodos de utilidad
const formatearMoneda = (valor) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(valor || 0);
};

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A';
  return new Date(fecha).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatearEstado = (estado) => {
  const estados = {
    'pendiente': 'Pendiente',
    'recibida': 'Recibida',
    'cancelada': 'Cancelada'
  };
  return estados[estado] || 'Desconocido';
};

const getEstadoClases = (estado) => {
  const clases = {
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'recibida': 'bg-green-100 text-green-800',
    'cancelada': 'bg-red-100 text-red-800'
  };
  return clases[estado] || 'bg-gray-100 text-gray-800';
};

const getEstadoPunto = (estado) => {
  const clases = {
    'pendiente': 'bg-yellow-400',
    'recibida': 'bg-green-400',
    'cancelada': 'bg-red-400'
  };
  return clases[estado] || 'bg-gray-400';
};

const calcularSubtotal = (orden) => {
  if (!orden.items) return 0;
  return orden.items.reduce((sum, item) => {
    return sum + ((item.pivot?.cantidad || 0) * (item.pivot?.precio || 0));
  }, 0) * 0.84; // Asumiendo 16% de IVA
};

const calcularImpuestos = (orden) => {
  return calcularSubtotal(orden) * 0.16;
};

// Métodos de ordenamiento
const ordenarPor = (campo) => {
  if (ordenActual.value === campo) {
    direccionOrden.value = direccionOrden.value === 'asc' ? 'desc' : 'asc';
  } else {
    ordenActual.value = campo;
    direccionOrden.value = 'desc';
  }
};

// Métodos de filtrado
const limpiarFiltros = () => {
  searchTerm.value = '';
  filtroEstado.value = '';
};

// Métodos de dropdown
const toggleDropdown = (id) => {
  dropdownOpen.value = dropdownOpen.value === id ? null : id;
};

const closeDropdown = (id) => {
  if (dropdownOpen.value === id) {
    dropdownOpen.value = null;
  }
};

// Métodos de acciones
const editarOrdenCompra = (id) => {
  router.get(route('ordenescompra.edit', id));
};

const duplicarOrden = (orden) => {
  // Implementar lógica de duplicación
  notyf.success('Funcionalidad de duplicación en desarrollo');
  dropdownOpen.value = null;
};

const exportarOrden = (orden) => {
  // Implementar lógica de exportación
  notyf.success('Funcionalidad de exportación en desarrollo');
  dropdownOpen.value = null;
};

// Métodos de eliminación
const confirmarEliminacionOrden = (id) => {
  ordenCompraIdToDelete.value = id;
  showConfirmationDialog.value = true;
  dropdownOpen.value = null;
};

const cancelarEliminacionOrden = () => {
  ordenCompraIdToDelete.value = null;
  showConfirmationDialog.value = false;
};

const eliminarOrdenCompra = async () => {
  if (!ordenCompraIdToDelete.value) return;

  loading.value = true;
  try {
    await router.delete(route('ordenescompra.destroy', ordenCompraIdToDelete.value), {
      onSuccess: () => {
        notyf.success('Orden de compra eliminada exitosamente');
        ordenesCompra.value = ordenesCompra.value.filter(orden => orden.id !== ordenCompraIdToDelete.value);
        showConfirmationDialog.value = false;
        ordenCompraIdToDelete.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la orden de compra');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');
  } finally {
    loading.value = false;
  }
};

// Métodos de recepción
const confirmarRecepcionOrden = (id) => {
  ordenCompraIdToReceive.value = id;
  showReceiveConfirmationDialog.value = true;
};



const recibirOrdenCompra = async () => {
  if (!ordenCompraIdToReceive.value) return;

  loading.value = true;

  try {
    // Usar la forma correcta de Inertia para peticiones POST
    router.post(route('ordenescompra.recibir', ordenCompraIdToReceive.value), {}, {
      onSuccess: (page) => {
        notyf.success('Orden recibida y stock actualizado exitosamente');

        // Actualizar el estado local
        const index = ordenesCompra.value.findIndex(o => o.id === ordenCompraIdToReceive.value);
        if (index !== -1) {
          ordenesCompra.value[index].estado = 'recibida';
        }

        // Cerrar modal
        showReceiveConfirmationDialog.value = false;
        ordenCompraIdToReceive.value = null;
      },
      onError: (errors) => {
        console.error('Error al recibir orden:', errors);
        notyf.error('Error al recibir la orden de compra');

        // Cerrar modal incluso en error
        showReceiveConfirmationDialog.value = false;
        ordenCompraIdToReceive.value = null;
      },
      onFinish: () => {
        loading.value = false;
      }
    });

  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');

    // Cerrar modal
    showReceiveConfirmationDialog.value = false;
    ordenCompraIdToReceive.value = null;
    loading.value = false;
  }
};

const cancelarRecepcionOrden = () => {
  loading.value = false;
  ordenCompraIdToReceive.value = null;
  showReceiveConfirmationDialog.value = false;
  dropdownOpen.value = null; // Agregar esta línea
};

// Métodos de detalles
const verDetallesOrdenCompra = (orden) => {
  selectedOrdenCompra.value = orden;
  showDetailsDialog.value = true;
  dropdownOpen.value = null;
};

const cerrarDetallesOrdenCompra = () => {
  selectedOrdenCompra.value = null;
  showDetailsDialog.value = false;
};

// Directiva personalizada para cerrar dropdown al hacer clic fuera
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = function(event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  }
};

// Lifecycle
onMounted(() => {
  // Cualquier inicialización adicional
});
</script>

<style scoped>
/* Animaciones personalizadas */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Mejoras visuales adicionales */
.ordenes-compra-index {
  min-height: calc(100vh - 2rem);
}

/* Estilos para estados de hover mejorados */
.hover\:bg-gray-50:hover {
  background-color: #f9fafb;
  transition: background-color 0.15s ease-in-out;
}

/* Estilos para botones de acción */
/*
.action-button, .action-button-primary, .action-button-secondary, .action-button-danger {
  These classes have been replaced by directly using Tailwind utility classes in the template.
}
*/

/* Mejoras para la tabla responsiva */
@media (max-width: 1024px) {
  .ordenes-compra-index {
    padding: 1rem;
  }

  table {
    font-size: 0.875rem;
  }

  th, td {
    padding: 0.75rem 0.5rem;
  }
}

@media (max-width: 768px) {
  .ordenes-compra-index {
    padding: 0.5rem;
  }

  .flex.flex-col.lg\\:flex-row {
    flex-direction: column;
    align-items: stretch;
  }

  .w-full.lg\\:w-80 {
    width: 100%;
  }
}
</style>
