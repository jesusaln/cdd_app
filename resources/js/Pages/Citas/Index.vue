<template>
  <Head title="Citas" />
  <div class="citas-index min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <!-- Header con estadísticas -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
          <div class="flex items-center space-x-4">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-3 rounded-xl shadow-lg">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Gestión de Citas</h1>
              <p class="text-gray-600 mt-1">Administra y monitorea todas las citas de servicio</p>
            </div>
          </div>

          <!-- Estadísticas rápidas -->
          <div class="mt-6 lg:mt-0 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-4 text-white shadow-lg">
              <div class="text-2xl font-bold">{{ estadisticas.completadas }}</div>
              <div class="text-sm opacity-90">Completadas</div>
            </div>
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl p-4 text-white shadow-lg">
              <div class="text-2xl font-bold">{{ estadisticas.pendientes }}</div>
              <div class="text-sm opacity-90">Pendientes</div>
            </div>
            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl p-4 text-white shadow-lg">
              <div class="text-2xl font-bold">{{ estadisticas.enProceso }}</div>
              <div class="text-sm opacity-90">En Proceso</div>
            </div>
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-4 text-white shadow-lg">
              <div class="text-2xl font-bold">{{ estadisticas.total }}</div>
              <div class="text-sm opacity-90">Total</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Controles superiores -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
          <div class="flex items-center space-x-4">
            <Link
              :href="route('citas.create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Nueva Cita
            </Link>

            <button
              @click="exportarCitas"
              class="inline-flex items-center px-4 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium rounded-xl shadow-md hover:from-emerald-600 hover:to-green-700 transform hover:scale-105 transition-all duration-200"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Exportar
            </button>
          </div>

          <div class="flex items-center space-x-3">
            <!-- Toggle vista -->
            <div class="flex bg-gray-100 p-1 rounded-lg">
              <button
                @click="vistaActual = 'tabla'"
                :class="vistaActual === 'tabla' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                class="px-3 py-2 rounded-md text-sm font-medium transition-all duration-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9"></path>
                </svg>
              </button>
              <button
                @click="vistaActual = 'tarjetas'"
                :class="vistaActual === 'tarjetas' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                class="px-3 py-2 rounded-md text-sm font-medium transition-all duration-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
              </button>
            </div>

            <!-- Filtro rápido por estado -->
            <select
              v-model="filtroRapido"
              class="px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Filtro rápido</option>
              <option value="hoy">Citas de hoy</option>
              <option value="semana">Esta semana</option>
              <option value="pendientes">Solo pendientes</option>
              <option value="urgentes">Urgentes</option>
            </select>
          </div>
        </div>

        <!-- Filtros avanzados -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              v-model="filtroCliente"
              type="text"
              placeholder="Buscar cliente..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            />
          </div>

          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <input
              v-model="filtroTecnico"
              type="text"
              placeholder="Buscar técnico..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            />
          </div>

          <select
            v-model="filtroTipoServicio"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
          >
            <option value="">Tipo de servicio</option>
            <option value="instalacion">Instalación</option>
            <option value="diagnostico">Diagnóstico</option>
            <option value="reparacion">Reparación</option>
            <option value="garantia">Garantía</option>
            <option value="otro_servicio">Otro Servicio</option>
          </select>

          <select
            v-model="filtroEstado"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
          >
            <option value="">Estado</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En Proceso</option>
            <option value="completado">Completado</option>
            <option value="cancelado">Cancelado</option>
          </select>

          <input
            v-model="filtroFechaTrabajo"
            type="date"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
          />
        </div>

        <!-- Resultados de búsqueda -->
        <div class="mt-4 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Mostrando {{ citasFiltradas.length }} de {{ props.citas.length }} citas
          </div>
          <button
            v-if="tieneFactoresFiltro"
            @click="limpiarFiltros"
            class="text-sm text-blue-600 hover:text-blue-800 font-medium"
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Vista de tabla -->
      <div v-if="vistaActual === 'tabla'" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div v-if="citasFiltradas.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  <button @click="ordenarPor('id')" class="flex items-center space-x-1 hover:text-gray-900">
                    <span>Folio</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Técnico</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Servicio</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  <button @click="ordenarPor('fecha_hora')" class="flex items-center space-x-1 hover:text-gray-900">
                    <span>Fecha</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="cita in citasFiltradas"
                :key="cita.id"
                class="hover:bg-blue-50 transition-colors duration-200"
                :class="{ 'bg-red-50': esUrgente(cita) }"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="text-sm font-bold text-gray-900">#{{ cita.id }}</div>
                    <div v-if="esUrgente(cita)" class="ml-2">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Urgente
                      </span>
                    </div>
                  </div>
                  <div class="text-xs text-gray-500">{{ formatearFechaHora(cita.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ cita.cliente.nombre_razon_social }}</div>
                  <div v-if="cita.cliente.telefono" class="text-xs text-gray-500">{{ cita.cliente.telefono }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                        <span class="text-xs font-medium text-white">
                          {{ (cita.tecnico.nombre.charAt(0) + cita.tecnico.apellido.charAt(0)).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">
                        {{ cita.tecnico.nombre + ' ' + cita.tecnico.apellido }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                        :class="tipoServicioClase(cita.tipo_servicio)">
                    {{ formatearTipoServicio(cita.tipo_servicio) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatearFecha(cita.fecha_hora) }}</div>
                  <div class="text-xs text-gray-500">{{ formatearHora(cita.fecha_hora) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <select
                    v-model="cita.estado"
                    @change="cambiarEstado(cita)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    :class="estadoClase(cita.estado)"
                  >
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                  </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="flex justify-center space-x-2">
                    <button
                      @click="abrirModalDetalles(cita)"
                      class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-xs font-medium rounded-lg hover:from-emerald-600 hover:to-green-700 transform hover:scale-105 transition-all duration-200 shadow-md"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      Ver
                    </button>
                    <Link
                      :href="route('citas.edit', cita.id)"
                      class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-medium rounded-lg hover:from-amber-600 hover:to-orange-700 transform hover:scale-105 transition-all duration-200 shadow-md"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                      Editar
                    </Link>
                    <button
                      @click="abrirModal(cita.id)"
                      class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-red-500 to-pink-600 text-white text-xs font-medium rounded-lg hover:from-red-600 hover:to-pink-700 transform hover:scale-105 transition-all duration-200 shadow-md"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Vista de tarjetas -->
      <div v-else-if="vistaActual === 'tarjetas'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="cita in citasFiltradas"
          :key="cita.id"
          class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
          :class="{ 'ring-2 ring-red-300 bg-red-50': esUrgente(cita) }"
        >
          <!-- Header de la tarjeta -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg p-2">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900">#{{ cita.id }}</h3>
                <p class="text-sm text-gray-500">{{ formatearFechaHora(cita.created_at) }}</p>
              </div>
            </div>
            <div v-if="esUrgente(cita)" class="flex-shrink-0">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Urgente
              </span>
            </div>
          </div>

          <!-- Información del cliente -->
          <div class="mb-4">
            <h4 class="font-semibold text-gray-900 mb-1">{{ cita.cliente.nombre_razon_social }}</h4>
            <p v-if="cita.cliente.telefono" class="text-sm text-gray-600">📞 {{ cita.cliente.telefono }}</p>
          </div>

          <!-- Información del técnico -->
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0 h-10 w-10">
              <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                <span class="text-sm font-medium text-white">
                  {{ (cita.tecnico.nombre.charAt(0) + cita.tecnico.apellido.charAt(0)).toUpperCase() }}
                </span>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">{{ cita.tecnico.nombre + ' ' + cita.tecnico.apellido }}</p>
              <p class="text-xs text-gray-500">Técnico asignado</p>
            </div>
          </div>

          <!-- Tipo de servicio -->
          <div class="mb-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                  :class="tipoServicioClase(cita.tipo_servicio)">
              {{ formatearTipoServicio(cita.tipo_servicio) }}
            </span>
          </div>

          <!-- Fecha y hora -->
          <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ formatearFecha(cita.fecha_hora) }}</p>
                <p class="text-xs text-gray-500">{{ formatearHora(cita.fecha_hora) }}</p>
              </div>
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>

          <!-- Estado -->
          <div class="mb-4">
            <select
              v-model="cita.estado"
              @change="cambiarEstado(cita)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
              :class="estadoClase(cita.estado)"
            >
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En Proceso</option>
              <option value="completado">Completado</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>

          <!-- Acciones -->
          <div class="flex space-x-2">
            <button
              @click="abrirModalDetalles(cita)"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-lg hover:from-emerald-600 hover:to-green-700 transition-all duration-200"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              Ver
            </button>
            <Link
              :href="route('citas.edit', cita.id)"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-medium rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-200"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Editar
            </Link>
            <button
              @click="abrirModal(cita.id)"
              class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-medium rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-200"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Mensaje cuando no hay citas -->
      <div v-if="citasFiltradas.length === 0" class="text-center py-12">
        <div class="mx-auto h-24 w-24 text-gray-300">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </div>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No hay citas registradas</h3>
        <p class="mt-2 text-gray-500">Comienza creando tu primera cita de servicio.</p>
        <div class="mt-6">
          <Link
            :href="route('citas.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Cita
          </Link>
        </div>
      </div>
    </div>

    <!-- Modal de detalles -->
    <CitaModal :show="mostrarModalDetalles" :cita="citaSeleccionada" @close="cerrarModalDetalles" />

    <!-- Modal de eliminación -->
    <div v-if="mostrarModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl w-96 mx-4">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">¿Eliminar cita?</h3>
          <p class="text-gray-600 mb-6">Esta acción no se puede deshacer. La cita será eliminada permanentemente.</p>
          <div class="flex justify-center space-x-4">
            <button
              @click="cerrarModal"
              class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 font-medium"
            >
              Cancelar
            </button>
            <button
              @click="eliminarCita"
              class="px-6 py-2 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-lg hover:from-red-600 hover:to-pink-700 transition-all duration-200 font-medium"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import CitaModal from '@/Components/CitaModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  citas: Array,
  flash: Object,
});

const notyf = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#4caf50', icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' } },
    { type: 'error', background: '#f44336', icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' } },
  ],
});

// Estados reactivos
const mostrarModal = ref(false);
const mostrarModalDetalles = ref(false);
const idCitaAEliminar = ref(null);
const citaSeleccionada = ref(null);
const vistaActual = ref('tabla');
const ordenPor = ref('id');
const ordenDireccion = ref('desc');

// Filtros
const filtroCliente = ref('');
const filtroTecnico = ref('');
const filtroTipoServicio = ref('');
const filtroEstado = ref('');
const filtroFechaTrabajo = ref('');
const filtroRapido = ref('');

// Computed properties
const estadisticas = computed(() => {
  return {
    total: props.citas.length,
    pendientes: props.citas.filter(c => c.estado === 'pendiente').length,
    enProceso: props.citas.filter(c => c.estado === 'en_proceso').length,
    completadas: props.citas.filter(c => c.estado === 'completado').length,
  };
});

const tieneFactoresFiltro = computed(() => {
  return filtroCliente.value || filtroTecnico.value || filtroTipoServicio.value ||
         filtroEstado.value || filtroFechaTrabajo.value || filtroRapido.value;
});

const citasFiltradas = computed(() => {
  let citas = [...props.citas];

  // Aplicar filtros
  if (filtroCliente.value) {
    citas = citas.filter(cita =>
      cita.cliente.nombre_razon_social.toLowerCase().includes(filtroCliente.value.toLowerCase())
    );
  }

  if (filtroTecnico.value) {
    citas = citas.filter(cita =>
      (cita.tecnico.nombre + ' ' + cita.tecnico.apellido).toLowerCase().includes(filtroTecnico.value.toLowerCase())
    );
  }

  if (filtroTipoServicio.value) {
    citas = citas.filter(cita => cita.tipo_servicio === filtroTipoServicio.value);
  }

  if (filtroEstado.value) {
    citas = citas.filter(cita => cita.estado === filtroEstado.value);
  }

  if (filtroFechaTrabajo.value) {
    citas = citas.filter(cita =>
      new Date(cita.fecha_hora).toISOString().split('T')[0] === filtroFechaTrabajo.value
    );
  }

  // Aplicar filtro rápido
  if (filtroRapido.value) {
    const hoy = new Date();
    const inicioDeSemana = new Date(hoy.setDate(hoy.getDate() - hoy.getDay()));

    switch (filtroRapido.value) {
      case 'hoy':
        citas = citas.filter(cita => {
          const fechaCita = new Date(cita.fecha_hora);
          const hoy = new Date();
          return fechaCita.toDateString() === hoy.toDateString();
        });
        break;
      case 'semana':
        citas = citas.filter(cita => {
          const fechaCita = new Date(cita.fecha_hora);
          return fechaCita >= inicioDeSemana;
        });
        break;
      case 'pendientes':
        citas = citas.filter(cita => cita.estado === 'pendiente');
        break;
      case 'urgentes':
        citas = citas.filter(cita => esUrgente(cita));
        break;
    }
  }

  // Aplicar ordenamiento
  citas.sort((a, b) => {
    let valorA, valorB;

    switch (ordenPor.value) {
      case 'id':
        valorA = a.id;
        valorB = b.id;
        break;
      case 'fecha_hora':
        valorA = new Date(a.fecha_hora);
        valorB = new Date(b.fecha_hora);
        break;
      default:
        valorA = a[ordenPor.value];
        valorB = b[ordenPor.value];
    }

    if (valorA < valorB) return ordenDireccion.value === 'asc' ? -1 : 1;
    if (valorA > valorB) return ordenDireccion.value === 'asc' ? 1 : -1;
    return 0;
  });

  return citas;
});

// Métodos
const abrirModal = (id) => {
  idCitaAEliminar.value = id;
  mostrarModal.value = true;
};

const abrirModalDetalles = (cita) => {
  citaSeleccionada.value = cita;
  mostrarModalDetalles.value = true;
};

const cerrarModal = () => {
  mostrarModal.value = false;
  idCitaAEliminar.value = null;
};

const cerrarModalDetalles = () => {
  mostrarModalDetalles.value = false;
  citaSeleccionada.value = null;
};

const eliminarCita = () => {
  router.delete(route('citas.destroy', idCitaAEliminar.value), {
    onSuccess: () => {
      notyf.success('La cita ha sido eliminada exitosamente.');
      cerrarModal();
    },
    onError: () => {
      notyf.error('Hubo un error al eliminar la cita.');
      cerrarModal();
    },
  });
};

const cambiarEstado = (cita) => {
  router.patch(route('citas.updateIndex', cita.id), { estado: cita.estado }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Estado actualizado correctamente');
    },
    onError: () => {
      notyf.error('Error al actualizar el estado de la cita.');
      // Revertir el cambio en caso de error
      const citaOriginal = props.citas.find(c => c.id === cita.id);
      if (citaOriginal) {
        cita.estado = citaOriginal.estado;
      }
    },
  });
};

const ordenarPor = (campo) => {
  if (ordenPor.value === campo) {
    ordenDireccion.value = ordenDireccion.value === 'asc' ? 'desc' : 'asc';
  } else {
    ordenPor.value = campo;
    ordenDireccion.value = 'asc';
  }
};

const limpiarFiltros = () => {
  filtroCliente.value = '';
  filtroTecnico.value = '';
  filtroTipoServicio.value = '';
  filtroEstado.value = '';
  filtroFechaTrabajo.value = '';
  filtroRapido.value = '';
};

const exportarCitas = () => {
  // Implementar lógica de exportación
  notyf.success('Función de exportación en desarrollo');
};

const esUrgente = (cita) => {
  const fechaCita = new Date(cita.fecha_hora);
  const ahora = new Date();
  const diferencia = fechaCita - ahora;
  const horasHastaLaCita = diferencia / (1000 * 60 * 60);

  return horasHastaLaCita <= 24 && horasHastaLaCita > 0 && cita.estado === 'pendiente';
};

const formatearTipoServicio = (tipo) => {
  const tipos = {
    instalacion: 'Instalación',
    diagnostico: 'Diagnóstico',
    reparacion: 'Reparación',
    garantia: 'Garantía',
    otro_servicio: 'Otro Servicio',
  };
  return tipos[tipo] || 'Desconocido';
};

const formatearFecha = (fechaHora) => {
  const fecha = new Date(fechaHora);
  return fecha.toLocaleDateString('es-MX', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatearHora = (fechaHora) => {
  const fecha = new Date(fechaHora);
  return fecha.toLocaleTimeString('es-MX', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatearFechaHora = (fechaHora) => {
  const fecha = new Date(fechaHora);
  return fecha.toLocaleString('es-MX', {
    dateStyle: 'medium',
    timeStyle: 'short'
  });
};

const estadoClase = (estado) => {
  const clases = {
    pendiente: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    en_proceso: 'bg-blue-100 text-blue-800 border-blue-200',
    completado: 'bg-green-100 text-green-800 border-green-200',
    cancelado: 'bg-red-100 text-red-800 border-red-200',
  };
  return clases[estado] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const tipoServicioClase = (tipo) => {
  const clases = {
    instalacion: 'bg-blue-100 text-blue-800',
    diagnostico: 'bg-purple-100 text-purple-800',
    reparacion: 'bg-orange-100 text-orange-800',
    garantia: 'bg-green-100 text-green-800',
    otro_servicio: 'bg-gray-100 text-gray-800',
  };
  return clases[tipo] || 'bg-gray-100 text-gray-800';
};

// Mostrar mensaje flash si existe
onMounted(() => {

     ordenPor.value = 'created_at';
  ordenDireccion.value = 'desc';

  if (props.flash?.success) {
    notyf.success(props.flash.success);
  }
  if (props.flash?.error) {
    notyf.error(props.flash.error);
  }
});
</script>

<style scoped>
.citas-index {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Animaciones personalizadas */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Responsive mejoras */
@media (max-width: 768px) {
  .grid-cols-2 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}
</style>
