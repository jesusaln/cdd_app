<template>
  <Head title="Citas" />
  <div class="citas-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
          <!-- Izquierda -->
          <div class="flex flex-col gap-6 w-full lg:w-auto">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-900">Citas</h1>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <Link
                :href="route('citas.create')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ headerConfig.createButtonText }}</span>
              </Link>

              <button
                @click="exportarCitas"
                class="inline-flex items-center gap-2 px-4 py-3 bg-green-50 text-green-700 rounded-xl hover:bg-green-100 transition-all duration-200 border border-green-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium">Exportar</span>
              </button>
            </div>

            <!-- Estadísticas con barras de progreso -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 rounded-xl border border-slate-200">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium text-slate-700">Total:</span>
                <span class="font-bold text-slate-900 text-lg">{{ formatNumber(estadisticas.total) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-green-50 rounded-xl border border-green-200">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Completadas:</span>
                <span class="font-bold text-green-700 text-lg">{{ formatNumber(estadisticas.completadas) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-yellow-50 rounded-xl border border-yellow-200">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Pendientes:</span>
                <span class="font-bold text-yellow-700 text-lg">{{ formatNumber(estadisticas.pendientes) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 rounded-xl border border-blue-200">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="font-medium text-slate-700">En Proceso:</span>
                <span class="font-bold text-blue-700 text-lg">{{ formatNumber(estadisticas.enProceso) }}</span>
              </div>
            </div>
          </div>

          <!-- Derecha: Filtros -->
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto lg:flex-shrink-0">
            <!-- Búsqueda -->
            <div class="relative">
              <input
                v-model="filtroCliente"
                type="text"
                :placeholder="headerConfig.searchPlaceholder"
                class="w-full sm:w-64 lg:w-80 pl-4 pr-10 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
              />
              <svg class="absolute right-3 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Estado -->
            <select
              v-model="filtroEstado"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="">Todos los Estados</option>
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En Proceso</option>
              <option value="completado">Completado</option>
              <option value="cancelado">Cancelado</option>
            </select>

            <!-- Tipo de Servicio -->
            <select
              v-model="filtroTipoServicio"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="">Tipo de Servicio</option>
              <option value="instalacion">Instalación</option>
              <option value="diagnostico">Diagnóstico</option>
              <option value="reparacion">Reparación</option>
              <option value="garantia">Garantía</option>
              <option value="otro_servicio">Otro Servicio</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Debug Info (temporal) -->
      <div class="bg-green-100 border border-green-300 rounded-lg p-4 mb-6">
        <h3 class="text-sm font-medium text-green-800 mb-2">✅ Estado: Datos cargados correctamente</h3>
        <div class="text-xs text-green-700 space-y-1">
          <div>📊 Total de citas: <strong>{{ citasData.length }}</strong></div>
          <div>🔍 Citas visibles: <strong>{{ citasFiltradas.length }}</strong></div>
          <div v-if="filtroEstado" class="text-orange-600">🎯 Filtro activo: "{{ filtroEstado }}"</div>
        </div>
      </div>

      <!-- Tabla de Citas Activas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
          <h3 class="text-lg font-semibold text-blue-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            Citas Activas
            <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-blue-100 text-blue-600">{{ citasActivas.length }}</span>
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Cita</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Técnico</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Servicio</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-blue-700 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="cita in citasActivas" :key="cita.id" class="hover:bg-blue-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatearFecha(cita.fecha_hora) }}</div>
                  <div class="text-xs text-gray-500">{{ formatearHora(cita.fecha_hora) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">#{{ cita.id }}</div>
                  <div class="text-xs text-gray-500">{{ formatearFechaHora(cita.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ cita.cliente?.nombre_razon_social || 'Sin cliente' }}</div>
                  <div v-if="cita.cliente?.telefono" class="text-xs text-gray-500">{{ cita.cliente.telefono }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">{{ cita.tecnico?.nombre || 'Sin técnico' }} {{ cita.tecnico?.apellido || '' }}</div>
                </td>
                <td class="px-6 py-4">
                  <span :class="tipoServicioClase(cita.tipo_servicio)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ formatearTipoServicio(cita.tipo_servicio) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <select
                    v-model="cita.estado"
                    @change="cambiarEstado(cita)"
                    :class="estadoClase(cita.estado)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                  >
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                  </select>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <button @click="verDetalles(cita)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" title="Ver detalles">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button @click="editarCita(cita.id)" class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors duration-150" title="Editar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="confirmarEliminacion(cita.id)" class="w-8 h-8 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150" title="Eliminar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="citasActivas.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-blue-700 font-medium">No hay citas activas</p>
                      <p class="text-sm text-blue-500">Las citas pendientes y en proceso aparecerán aquí</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tabla de Citas Completadas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
          <h3 class="text-lg font-semibold text-green-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Citas Completadas
            <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-green-100 text-green-600">{{ citasCompletadas.length }}</span>
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-green-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-green-700 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-green-700 uppercase tracking-wider">Cita</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-green-700 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-green-700 uppercase tracking-wider">Técnico</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-green-700 uppercase tracking-wider">Servicio</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-green-700 uppercase tracking-wider">Completado</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-green-700 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="cita in citasCompletadas" :key="cita.id" class="hover:bg-green-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatearFecha(cita.fecha_hora) }}</div>
                  <div class="text-xs text-gray-500">{{ formatearHora(cita.fecha_hora) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">#{{ cita.id }}</div>
                  <div class="text-xs text-gray-500">{{ formatearFechaHora(cita.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ cita.cliente?.nombre_razon_social || 'Sin cliente' }}</div>
                  <div v-if="cita.cliente?.telefono" class="text-xs text-gray-500">{{ cita.cliente.telefono }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">{{ cita.tecnico?.nombre || 'Sin técnico' }} {{ cita.tecnico?.apellido || '' }}</div>
                </td>
                <td class="px-6 py-4">
                  <span :class="tipoServicioClase(cita.tipo_servicio)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ formatearTipoServicio(cita.tipo_servicio) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Completado
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <button @click="verDetalles(cita)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" title="Ver detalles">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button @click="editarCita(cita.id)" class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors duration-150" title="Editar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="citasCompletadas.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-green-700 font-medium">No hay citas completadas</p>
                      <p class="text-sm text-green-500">Las citas completadas aparecerán aquí</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tabla de Citas Canceladas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
          <h3 class="text-lg font-semibold text-red-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Citas Canceladas
            <span class="ml-2 py-0.5 px-2 rounded-full text-xs bg-red-100 text-red-600">{{ citasCanceladas.length }}</span>
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-red-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">Cita</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">Técnico</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">Servicio</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">Cancelado</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-red-700 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="cita in citasCanceladas" :key="cita.id" class="hover:bg-red-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatearFecha(cita.fecha_hora) }}</div>
                  <div class="text-xs text-gray-500">{{ formatearHora(cita.fecha_hora) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">#{{ cita.id }}</div>
                  <div class="text-xs text-gray-500">{{ formatearFechaHora(cita.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ cita.cliente?.nombre_razon_social || 'Sin cliente' }}</div>
                  <div v-if="cita.cliente?.telefono" class="text-xs text-gray-500">{{ cita.cliente.telefono }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">{{ cita.tecnico?.nombre || 'Sin técnico' }} {{ cita.tecnico?.apellido || '' }}</div>
                </td>
                <td class="px-6 py-4">
                  <span :class="tipoServicioClase(cita.tipo_servicio)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ formatearTipoServicio(cita.tipo_servicio) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Cancelado
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <button @click="verDetalles(cita)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" title="Ver detalles">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button @click="editarCita(cita.id)" class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors duration-150" title="Editar">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="citasCanceladas.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-red-700 font-medium">No hay citas canceladas</p>
                      <p class="text-sm text-red-500">Las citas canceladas aparecerán aquí</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal mejorado -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <!-- Header del modal -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              {{ modalMode === 'details' ? 'Detalles de la Cita' : 'Confirmar Eliminación' }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="modalMode === 'details' && selectedCita">
              <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Cita #{{ selectedCita.id }}</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFechaHora(selectedCita.created_at) }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Cliente</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCita.cliente.nombre_razon_social }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Estado</label>
                      <span :class="obtenerClasesEstado(selectedCita.estado)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1">
                        {{ obtenerLabelEstado(selectedCita.estado) }}
                      </span>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Técnico</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedCita.tecnico.nombre }} {{ selectedCita.tecnico.apellido }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Tipo de Servicio</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearTipoServicio(selectedCita.tipo_servicio) }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha y Hora</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedCita.fecha_hora) }} {{ formatearHora(selectedCita.fecha_hora) }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="selectedCita.descripcion">
                  <label class="block text-sm font-medium text-gray-700">Descripción</label>
                  <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md whitespace-pre-wrap">{{ selectedCita.descripcion }}</p>
                </div>
              </div>
            </div>

            <div v-if="modalMode === 'confirm'">
              <div class="text-center">
                <div class="w-12 h-12 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                  <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.994-.833-2.764 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">¿Eliminar Cita?</h3>
                <p class="text-sm text-gray-500 mb-4">
                  ¿Estás seguro de que deseas eliminar la cita <strong>#{{ selectedCita?.id }}</strong>?
                  Esta acción no se puede deshacer.
                </p>
              </div>
            </div>
          </div>

          <!-- Footer del modal -->
          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button @click="showModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
              {{ modalMode === 'details' ? 'Cerrar' : 'Cancelar' }}
            </button>
            <div v-if="modalMode === 'details'" class="flex gap-2">
              <button @click="cambiarEstado(selectedCita)" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                Cambiar Estado
              </button>
              <button @click="editarCita(selectedCita.id)" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                Editar
              </button>
            </div>
            <button v-if="modalMode === 'confirm'" @click="eliminarCita" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import CitaModal from '@/Components/CitaModal.vue'

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

const page = usePage()
onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})

// Props
const props = defineProps({
  citas: { type: [Object, Array], required: true },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({ sort_by: 'created_at', sort_direction: 'desc' }) },
})

// Estado UI
const showModal = ref(false)
const modalMode = ref('details')
const selectedCita = ref(null)
const selectedId = ref(null)
const ordenPor = ref('created_at')
const ordenDireccion = ref('desc')
const pestanaActiva = ref('activas')

// Filtros
const filtroCliente = ref('')
const filtroTecnico = ref('')
const filtroTipoServicio = ref('')
const filtroEstado = ref('')
const filtroFechaTrabajo = ref('')
const filtroRapido = ref('')
const searchTerm = ref('')
const sortBy = ref('created_at-desc')

// Paginación
const perPage = ref(10)

// Header config
const headerConfig = {
  module: 'citas',
  createButtonText: 'Nueva Cita',
  searchPlaceholder: 'Buscar por cliente o técnico...'
}

// Datos - simplificado para debug
const citasData = computed(() => {
  if (!props.citas) return []

  // Si es array directo
  if (Array.isArray(props.citas)) {
    return props.citas
  }

  // Si es objeto con data (paginado)
  if (props.citas.data && Array.isArray(props.citas.data)) {
    return props.citas.data
  }

  // Si es objeto pero no tiene data, devolver array vacío
  return []
})

// Estadísticas
const estadisticas = computed(() => ({
  total: props.stats?.total ?? 0,
  completadas: props.stats?.completadas ?? 0,
  pendientes: props.stats?.pendientes ?? 0,
  enProceso: props.stats?.enProceso ?? 0,
  activosPorcentaje: props.stats?.activos > 0 ? Math.round((props.stats.activos / props.stats.total) * 100) : 0,
  inactivosPorcentaje: props.stats?.inactivos > 0 ? Math.round((props.stats.inactivos / props.stats.total) * 100) : 0
}))

// Computed properties avanzadas
const tieneFactoresFiltro = computed(() => {
  return filtroCliente.value || filtroTecnico.value || filtroTipoServicio.value ||
         filtroEstado.value || filtroFechaTrabajo.value || filtroRapido.value
})

// Separar citas por estado
const citasActivas = computed(() => {
  return citasData.value.filter(cita => cita.estado === 'pendiente' || cita.estado === 'en_proceso')
})

const citasCompletadas = computed(() => {
  return citasData.value.filter(cita => cita.estado === 'completado')
})

const citasCanceladas = computed(() => {
  return citasData.value.filter(cita => cita.estado === 'cancelado')
})

const citasFiltradas = computed(() => {
  let citas = [...citasData.value]

  // Aplicar filtros
  if (filtroEstado.value) {
    citas = citas.filter(cita => cita.estado === filtroEstado.value)
  }

  // Aplicar ordenamiento
  citas.sort((a, b) => {
    if (ordenPor.value === 'id') {
      return ordenDireccion.value === 'asc' ? a.id - b.id : b.id - a.id
    }
    return 0
  })

  return citas
})

// Handlers
function handleSearchChange(newSearch) {
  searchTerm.value = newSearch
  router.get(route('citas.index'), {
    search: newSearch,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    activo: filtroEstado.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleEstadoChange(newEstado) {
  filtroEstado.value = newEstado
  router.get(route('citas.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    activo: newEstado,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleSortChange(newSort) {
  sortBy.value = newSort
  router.get(route('citas.index'), {
    search: searchTerm.value,
    sort_by: newSort.split('-')[0],
    sort_direction: newSort.split('-')[1] || 'desc',
    activo: filtroEstado.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const verDetalles = (doc) => {
  selectedCita.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

const editarCita = (id) => {
  router.visit(route('citas.edit', id))
}

const confirmarEliminacion = (id) => {
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarCita = () => {
  router.delete(route('citas.destroy', selectedId.value), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Cita eliminada correctamente')
      showModal.value = false
      selectedId.value = null
      router.reload()
    },
    onError: (errors) => {
      notyf.error('No se pudo eliminar la cita')
    }
  })
}

const cambiarEstado = (cita) => {
  router.patch(route('citas.updateIndex', cita.id), { estado: cita.estado }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Estado actualizado correctamente')
    },
    onError: () => {
      notyf.error('Error al actualizar el estado de la cita.')
      const citaOriginal = citasData.value.find(c => c.id === cita.id)
      if (citaOriginal) {
        cita.estado = citaOriginal.estado
      }
    }
  })
}

const limpiarFiltros = () => {
  filtroCliente.value = ''
  filtroTecnico.value = ''
  filtroTipoServicio.value = ''
  filtroEstado.value = ''
  filtroFechaTrabajo.value = ''
  filtroRapido.value = ''
}

const exportarCitas = () => {
  const params = new URLSearchParams()
  if (filtroCliente.value) params.append('cliente', filtroCliente.value)
  if (filtroTecnico.value) params.append('tecnico', filtroTecnico.value)
  if (filtroTipoServicio.value) params.append('tipo_servicio', filtroTipoServicio.value)
  if (filtroEstado.value) params.append('estado', filtroEstado.value)
  if (filtroFechaTrabajo.value) params.append('fecha', filtroFechaTrabajo.value)
  const queryString = params.toString()
  const url = route('citas.export') + (queryString ? `?${queryString}` : '')
  window.location.href = url
}

// Paginación
const paginationData = computed(() => {
  // Si props.citas es un objeto paginado, usar sus propiedades
  if (props.citas && typeof props.citas === 'object' && !Array.isArray(props.citas)) {
    return {
      current_page: props.citas.current_page || 1,
      last_page: props.citas.last_page || 1,
      per_page: props.citas.per_page || 10,
      from: props.citas.from || 0,
      to: props.citas.to || 0,
      total: props.citas.total || citasData.value.length,
      prev_page_url: props.citas.prev_page_url,
      next_page_url: props.citas.next_page_url,
      links: props.citas.links || []
    }
  }

  // Si es array directo, crear paginación básica
  return {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    from: 1,
    to: citasData.value.length,
    total: citasData.value.length,
    prev_page_url: null,
    next_page_url: null,
    links: []
  }
})

const handlePerPageChange = (newPerPage) => {
  router.get(route('citas.index'), {
    ...props.filters,
    ...props.sorting,
    per_page: newPerPage,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const handlePageChange = (newPage) => {
  router.get(route('citas.index'), {
    ...props.filters,
    ...props.sorting,
    page: newPage
  }, { preserveState: true, preserveScroll: true })
}

// Helpers
const formatNumber = (num) => new Intl.NumberFormat('es-ES').format(num)
const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const d = new Date(date)
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
  } catch {
    return 'Fecha inválida'
  }
}

const formatearHora = (fechaHora) => {
  const fecha = new Date(fechaHora)
  return fecha.toLocaleTimeString('es-MX', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatearFechaHora = (fechaHora) => {
  const fecha = new Date(fechaHora)
  return fecha.toLocaleString('es-MX', {
    dateStyle: 'medium',
    timeStyle: 'short'
  })
}

const obtenerClasesEstado = (estado) => {
  const clases = {
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'en_proceso': 'bg-blue-100 text-blue-800',
    'completado': 'bg-green-100 text-green-800',
    'cancelado': 'bg-red-100 text-red-800'
  }
  return clases[estado] || 'bg-gray-100 text-gray-700'
}

const obtenerLabelEstado = (estado) => {
  const labels = {
    'pendiente': 'Pendiente',
    'en_proceso': 'En Proceso',
    'completado': 'Completado',
    'cancelado': 'Cancelado'
  }
  return labels[estado] || 'Pendiente'
}

const esUrgente = (cita) => {
  const fechaCita = new Date(cita.fecha_hora)
  const ahora = new Date()
  const diferencia = fechaCita - ahora
  const horasHastaLaCita = diferencia / (1000 * 60 * 60)
  return horasHastaLaCita <= 24 && horasHastaLaCita > 0 && cita.estado === 'pendiente'
}

const formatearTipoServicio = (tipo) => {
  const tipos = {
    instalacion: 'Instalación',
    diagnostico: 'Diagnóstico',
    reparacion: 'Reparación',
    garantia: 'Garantía',
    otro_servicio: 'Otro Servicio'
  }
  return tipos[tipo] || 'Desconocido'
}

const estadoClase = (estado) => {
  const clases = {
    pendiente: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    en_proceso: 'bg-blue-100 text-blue-800 border-blue-200',
    completado: 'bg-green-100 text-green-800 border-green-200',
    cancelado: 'bg-red-100 text-red-800 border-red-200'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800 border-gray-200'
}

const tipoServicioClase = (tipo) => {
  const clases = {
    instalacion: 'bg-blue-100 text-blue-800',
    diagnostico: 'bg-purple-100 text-purple-800',
    reparacion: 'bg-orange-100 text-orange-800',
    garantia: 'bg-green-100 text-green-800',
    otro_servicio: 'bg-gray-100 text-gray-800'
  }
  return clases[tipo] || 'bg-gray-100 text-gray-800'
}
</script>

<style scoped>
.citas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
