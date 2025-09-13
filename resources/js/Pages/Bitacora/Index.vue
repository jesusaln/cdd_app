<template>
  <Head title="Bitácora de Actividades" />
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50/20 to-indigo-50/30 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 bg-clip-text text-transparent tracking-tight">
              Bitácora de Actividades
            </h1>
            <p class="mt-2 text-sm text-gray-600">Registra y consulta lo que hacen tus empleados día a día.</p>
          </div>
          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3">
            <button
              @click="toggleView"
              class="group inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white/80 backdrop-blur-sm hover:bg-white hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-md"
            >
              <svg v-if="vistaTabla" class="w-4 h-4 mr-2 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
              </svg>
              <svg v-else class="w-4 h-4 mr-2 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
              {{ vistaTabla ? 'Vista Tarjetas' : 'Vista Tabla' }}
            </button>
            <Link
              :href="route('bitacora.create')"
              class="group inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105"
            >
              <svg class="w-4 h-4 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Registrar Actividad
            </Link>
          </div>
        </div>

        <!-- Enhanced Filters with better mobile responsiveness -->
        <div class="mt-6">
          <!-- Mobile filter toggle -->
          <div class="md:hidden mb-4">
            <button
              @click="mostrarFiltrosMobile = !mostrarFiltrosMobile"
              class="w-full flex items-center justify-between px-4 py-2 bg-white/80 backdrop-blur-sm border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-white transition-all duration-200"
            >
              <span>Filtros</span>
              <svg :class="['w-4 h-4 transition-transform', mostrarFiltrosMobile ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
          </div>

          <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-screen"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 max-h-screen"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-show="mostrarFiltrosMobile || !isMobile" class="grid grid-cols-1 md:grid-cols-12 gap-4 overflow-hidden">
              <!-- Search with enhanced styling -->
              <div class="md:col-span-3 relative group">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                  v-model="form.q"
                  type="text"
                  placeholder="Buscar por título, descripción o cliente..."
                  @input="debounceSearch"
                  class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:bg-white group-focus-within:shadow-lg"
                >
                <div v-if="loading && form.q" class="absolute right-3 top-1/2 -translate-y-1/2">
                  <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
              </div>

              <!-- Enhanced selects with consistent styling -->
              <select
                v-model="form.usuario"
                @change="apply"
                class="md:col-span-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:bg-white hover:border-gray-400"
              >
                <option value="">Todos los empleados</option>
                <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>

              <select
                v-model="form.cliente"
                @change="apply"
                class="md:col-span-3 px-4 py-2.5 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:bg-white hover:border-gray-400"
              >
                <option value="">Todos los clientes</option>
                <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre_razon_social }}</option>
              </select>

              <select
                v-model="form.tipo"
                @change="apply"
                class="md:col-span-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:bg-white hover:border-gray-400"
              >
                <option value="">Todos los tipos</option>
                <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
              </select>

              <select
                v-model="form.estado"
                @change="apply"
                class="md:col-span-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:bg-white hover:border-gray-400"
              >
                <option value="">Por defecto (pendientes + en proceso)</option>
                <option value="todos">Ver todos los estados</option>
                <option value="pendiente">Solo pendientes</option>
                <option value="en_proceso">Solo en proceso</option>
                <option value="completado">Solo completados</option>
                <option value="cancelado">Solo cancelados</option>
              </select>

              <input
                type="date"
                v-model="form.desde"
                @change="apply"
                class="md:col-span-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:bg-white hover:border-gray-400"
              >

              <input
                type="date"
                v-model="form.hasta"
                @change="apply"
                class="md:col-span-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:bg-white hover:border-gray-400"
              >

              <div class="md:col-span-2 flex gap-2">
                <button
                  @click="apply"
                  :disabled="loading"
                  class="flex-1 px-4 py-2.5 rounded-lg bg-gradient-to-r from-gray-900 to-gray-800 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:from-gray-800 hover:to-gray-700 transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg"
                >
                  <div v-if="loading" class="flex items-center justify-center">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                    Filtrando...
                  </div>
                  <span v-else>Filtrar</span>
                </button>
                <button
                  @click="limpiarFiltros"
                  class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 bg-white/80 backdrop-blur-sm text-gray-700 font-medium hover:bg-white hover:border-gray-400 transition-all duration-200 transform hover:-translate-y-0.5"
                >
                  Limpiar
                </button>
              </div>
            </div>
          </Transition>

          <!-- Active filters display -->
          <div v-if="tieneFiltros" class="mt-4 flex flex-wrap gap-2">
            <span class="text-xs text-gray-500 mr-2">Filtros activos:</span>
            <span v-if="form.q" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-md">
              Búsqueda: "{{ form.q }}"
              <button @click="form.q = ''; apply()" class="ml-1 hover:text-blue-900">×</button>
            </span>
            <span v-if="form.usuario" class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded-md">
              Empleado: {{ usuarios.find(u => u.id == form.usuario)?.name }}
              <button @click="form.usuario = ''; apply()" class="ml-1 hover:text-green-900">×</button>
            </span>
            <span v-if="form.cliente" class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-md">
              Cliente: {{ clientes.find(c => c.id == form.cliente)?.nombre_razon_social }}
              <button @click="form.cliente = ''; apply()" class="ml-1 hover:text-purple-900">×</button>
            </span>
            <!-- Add more filter chips as needed -->
          </div>
        </div>
      </div>

      <!-- Enhanced Stats Cards with animations -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="group bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg hover:bg-white transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Actividades (página)</p>
              <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ actividadesPagina }}</p>
            </div>
          </div>
        </div>

        <div class="group bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg hover:bg-white transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Completadas</p>
              <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ completadasPagina }}</p>
            </div>
          </div>
        </div>

        <div class="group bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg hover:bg-white transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Horas registradas</p>
              <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ horasRegistradasPagina }}</p>
            </div>
          </div>
        </div>

        <div class="group bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg hover:bg-white transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Clientes únicos</p>
              <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ clientesUnicosPagina }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Area with enhanced transitions -->
      <Transition
        mode="out-in"
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
      >
        <div v-if="actividadesData.length > 0" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200/50 overflow-hidden hover:shadow-lg transition-all duration-300">
          <!-- Enhanced Table View -->
          <div v-if="vistaTabla" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Fecha</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Empleado</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Cliente</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actividad</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tipo</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Estado</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Duración</th>
                  <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr
                  v-for="(a, index) in actividadesData"
                  :key="a.id"
                  class="group hover:bg-blue-50/50 transition-all duration-200 hover:shadow-sm"
                  :style="{ animationDelay: `${index * 50}ms` }"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDate(a.fecha) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ a.usuario?.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ a.cliente?.nombre_razon_social || '—' }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ a.titulo }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 capitalize">{{ a.tipo }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold', claseEstado(a.estado)]">
                      {{ a.estado.replace('_', ' ') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 tabular-nums">{{ formatearDuracion(a.inicio_at, a.fin_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex justify-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                      <button
                        @click="abrirModal(a)"
                        class="inline-flex items-center p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-100 rounded-lg transition-all duration-200 transform hover:scale-110"
                        title="Ver detalles"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </button>
                      <Link
                        :href="route('bitacora.edit', a.id)"
                        class="inline-flex items-center p-2 text-amber-600 hover:text-amber-900 hover:bg-amber-100 rounded-lg transition-all duration-200 transform hover:scale-110"
                        title="Editar"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Enhanced Card View with better animations -->
          <div v-else class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <div
                v-for="(a, index) in actividadesData"
                :key="a.id"
                class="group bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:rotate-1 overflow-hidden"
                :style="{ animationDelay: `${index * 100}ms` }"
              >
                <div class="p-5 relative">
                  <!-- Subtle gradient overlay -->
                  <div class="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-blue-50/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl"></div>

                  <div class="relative z-10">
                    <div class="flex items-start justify-between mb-3">
                      <div class="flex-1 mr-3">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-900 transition-colors line-clamp-2">{{ a.titulo }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ formatDate(a.fecha) }} — {{ a.usuario?.name }}</p>
                      </div>
                      <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold capitalize transition-all duration-200 group-hover:scale-105', claseEstado(a.estado)]">
                        {{ a.estado.replace('_', ' ') }}
                      </span>
                    </div>

                    <div class="space-y-2 mb-4 text-sm">
                      <div class="flex justify-between items-center">
                        <span class="text-gray-600">Cliente:</span>
                        <span class="font-medium text-gray-900 text-right flex-1 ml-2 truncate">{{ a.cliente?.nombre_razon_social || '—' }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Tipo:</span>
                        <span class="font-medium text-gray-900 capitalize">{{ a.tipo }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Duración:</span>
                        <span class="font-medium text-gray-900 tabular-nums">{{ formatearDuracion(a.inicio_at, a.fin_at) }}</span>
                      </div>
                    </div>

                    <div class="flex gap-2">
                      <button
                        @click="abrirModal(a)"
                        class="flex-1 bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium hover:from-blue-100 hover:to-blue-200 transition-all duration-200 transform hover:-translate-y-0.5"
                      >
                        Ver
                      </button>
                      <Link
                        :href="route('bitacora.edit', a.id)"
                        class="flex-1 bg-gradient-to-r from-amber-50 to-amber-100 text-amber-700 px-3 py-2 rounded-lg text-sm font-medium hover:from-amber-100 hover:to-amber-200 transition-all duration-200 transform hover:-translate-y-0.5 text-center"
                      >
                        Editar
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Enhanced Pagination -->
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200 flex items-center justify-between text-sm">
            <div class="text-gray-700">
              Mostrando <span class="font-semibold">{{ actividades.from }}</span>-<span class="font-semibold">{{ actividades.to }}</span> de <span class="font-semibold">{{ actividades.total }}</span>
            </div>
            <div class="flex gap-1">
              <Link
                v-for="link in actividades.links"
                :key="(link.url || '') + '-' + link.label"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                  'px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:-translate-y-0.5',
                  link.active
                    ? 'bg-gradient-to-r from-gray-900 to-gray-800 text-white shadow-md'
                    : 'border border-gray-300 bg-white/80 text-gray-700 hover:bg-white hover:border-gray-400 hover:shadow-md'
                ]"
              />
            </div>
          </div>
        </div>

        <!-- Enhanced Empty State -->
        <div v-else class="text-center py-16">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-200/50 p-16 max-w-2xl mx-auto">
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 rounded-2xl transform rotate-1"></div>
              <div class="relative bg-white rounded-2xl p-12">
                <div class="mx-auto w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6">
                  <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                  {{ tieneFiltros ? 'No se encontraron resultados' : 'No hay actividades registradas' }}
                </h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
                  {{ tieneFiltros
                    ? 'No se encontraron actividades con los filtros aplicados. Intenta ajustar los criterios de búsqueda.'
                    : 'Comienza registrando tu primera actividad para llevar un control detallado del trabajo diario.'
                  }}
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                  <Link
                    v-if="!tieneFiltros"
                    :href="route('bitacora.create')"
                    class="group inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105"
                  >
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Registrar Primera Actividad
                  </Link>
                  <button
                    v-else
                    @click="limpiarFiltros"
                    class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:from-gray-200 hover:to-gray-300 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105"
                  >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Limpiar Filtros
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Enhanced Modal with better animations and design -->
      <Teleport to="body">
        <Transition name="modal" appear>
          <div
            v-if="mostrarModalDetalles"
            class="fixed inset-0 bg-black/60 backdrop-blur-md flex justify-center items-center z-50 p-4"
            @click.self="cerrarModal"
          >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 max-h-[90vh] overflow-hidden">
              <!-- Modal Header -->
              <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 px-8 py-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 bg-clip-text text-transparent">
                      Detalle de Actividad
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">{{ actividadSeleccionada?.titulo }}</p>
                  </div>
                  <button
                    @click="cerrarModal"
                    class="p-2 hover:bg-white/50 rounded-xl transition-all duration-200 transform hover:scale-110"
                  >
                    <svg class="w-6 h-6 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Modal Body -->
              <div class="p-8 overflow-y-auto max-h-[calc(90vh-200px)]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                  <!-- Left Column -->
                  <div class="space-y-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                      <span class="text-gray-500 text-xs uppercase tracking-wide font-semibold">Información Básica</span>
                      <div class="mt-3 space-y-3">
                        <div class="flex justify-between">
                          <span class="text-gray-600">Fecha:</span>
                          <span class="font-semibold text-gray-900">{{ formatDate(actividadSeleccionada?.fecha) }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-600">Hora:</span>
                          <span class="font-semibold text-gray-900">{{ actividadSeleccionada?.hora || '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-600">Empleado:</span>
                          <span class="font-semibold text-gray-900">{{ actividadSeleccionada?.usuario?.name }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="bg-blue-50 rounded-xl p-4">
                      <span class="text-blue-600 text-xs uppercase tracking-wide font-semibold">Cliente y Proyecto</span>
                      <div class="mt-3 space-y-3">
                        <div class="flex justify-between">
                          <span class="text-blue-700">Cliente:</span>
                          <span class="font-semibold text-blue-900 text-right flex-1 ml-4">{{ actividadSeleccionada?.cliente?.nombre_razon_social || '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-blue-700">Tipo:</span>
                          <span class="font-semibold text-blue-900 capitalize">{{ actividadSeleccionada?.tipo }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Right Column -->
                  <div class="space-y-4">
                    <div class="bg-green-50 rounded-xl p-4">
                      <span class="text-green-600 text-xs uppercase tracking-wide font-semibold">Estado y Progreso</span>
                      <div class="mt-3 space-y-3">
                        <div class="flex justify-between items-center">
                          <span class="text-green-700">Estado:</span>
                          <span :class="['font-semibold capitalize px-3 py-1 rounded-full text-xs', claseEstado(actividadSeleccionada?.estado)]">
                            {{ actividadSeleccionada?.estado?.replace('_', ' ') }}
                          </span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-green-700">Duración:</span>
                          <span class="font-bold text-green-900 tabular-nums text-lg">{{ formatearDuracion(actividadSeleccionada?.inicio_at, actividadSeleccionada?.fin_at) }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="bg-purple-50 rounded-xl p-4">
                      <span class="text-purple-600 text-xs uppercase tracking-wide font-semibold">Ubicación</span>
                      <div class="mt-3">
                        <p class="text-purple-900 font-medium">{{ actividadSeleccionada?.ubicacion || 'No especificada' }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Full Width Description -->
                  <div class="md:col-span-2 bg-gray-50 rounded-xl p-4">
                    <span class="text-gray-600 text-xs uppercase tracking-wide font-semibold">Descripción de la Actividad</span>
                    <div class="mt-3">
                      <p class="text-gray-900 leading-relaxed whitespace-pre-line">
                        {{ actividadSeleccionada?.descripcion || 'Sin descripción proporcionada.' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal Footer -->
              <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex justify-end gap-3">
                <button
                  @click="cerrarModal"
                  class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 transform hover:-translate-y-0.5 font-medium"
                >
                  Cerrar
                </button>
                <Link
                  :href="route('bitacora.edit', actividadSeleccionada?.id)"
                  class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:-translate-y-0.5 font-medium shadow-lg hover:shadow-xl"
                >
                  Editar Actividad
                </Link>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })

const props = defineProps({
  actividades: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  usuarios: { type: Array, default: () => [] },
  clientes: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
})

// Responsive utilities
const isMobile = ref(false)
const mostrarFiltrosMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
  if (!isMobile.value) mostrarFiltrosMobile.value = false
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})

// Date formatting with better error handling
function formatDate(dateString) {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return '—'
    return date.toLocaleDateString('es-ES', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      weekday: 'short'
    })
  } catch (error) {
    return '—'
  }
}

const vistaTabla = ref(true)
const form = ref({
  q: props.filters?.q ?? '',
  usuario: props.filters?.usuario ?? '',
  cliente: props.filters?.cliente ?? '',
  desde: props.filters?.desde ?? '',
  hasta: props.filters?.hasta ?? '',
  tipo: props.filters?.tipo ?? '',
  estado: props.filters?.estado ?? '',
})

const loading = ref(false)

// Enhanced debounce with loading state
let timeoutId = null
function debounceSearch() {
  loading.value = true
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    apply()
  }, 300) // Reduced delay for better UX
}

// Watcher for programmatic filter changes
watch(() => form.value, (newValue, oldValue) => {
  if (oldValue && JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
    const hasTextSearchChanged = newValue.q !== oldValue.q
    if (!hasTextSearchChanged) {
      apply()
    }
  }
}, { deep: true })

// Computed properties with memoization
const actividadesData = computed(() => props.actividades?.data ?? [])
const actividadesPagina = computed(() => actividadesData.value.length)
const completadasPagina = computed(() => actividadesData.value.filter(a => a.estado === 'completado').length)
const clientesUnicosPagina = computed(() => new Set(actividadesData.value.map(a => a.cliente?.id).filter(Boolean)).size)
const horasRegistradasPagina = computed(() => {
  const mins = actividadesData.value.reduce((acc, a) => acc + diffMinutos(a.inicio_at, a.fin_at), 0)
  const horas = (mins / 60)
  return horas.toFixed(1)
})

const tieneFiltros = computed(() => {
  const { q, usuario, cliente, desde, hasta, tipo, estado } = form.value
  return !!(q || usuario || cliente || desde || hasta || tipo || estado)
})

function toggleView() {
  vistaTabla.value = !vistaTabla.value
  // Close mobile filters when switching views
  if (isMobile.value) mostrarFiltrosMobile.value = false
}

function apply() {
  loading.value = true
  router.get(route('bitacora.index'), {
    ...form.value,
    page: 1
  }, {
    preserveState: true,
    replace: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

function limpiarFiltros() {
  form.value = { q: '', usuario: '', cliente: '', desde: '', hasta: '', tipo: '', estado: '' }
  mostrarFiltrosMobile.value = false
  apply()
}

// Enhanced status styling
function claseEstado(est) {
  const statusClasses = {
    'completado': 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-200',
    'en_proceso': 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-200',
    'pendiente': 'bg-gradient-to-r from-yellow-100 to-amber-200 text-yellow-800 border border-yellow-200',
    'cancelado': 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-200'
  }
  return statusClasses[est] || 'bg-gray-100 text-gray-800 border border-gray-200'
}

// Time utilities with better error handling
function diffMinutos(inicio, fin) {
  if (!inicio || !fin) return 0
  try {
    const i = new Date(inicio)
    const f = new Date(fin)
    const ms = f - i
    if (isNaN(ms) || ms < 0) return 0
    return Math.round(ms / 60000)
  } catch (error) {
    return 0
  }
}

function formatearDuracion(inicio, fin) {
  const m = diffMinutos(inicio, fin)
  if (!m) return '—'
  const h = Math.floor(m / 60)
  const mm = m % 60
  if (h === 0) return `${mm}min`
  if (mm === 0) return `${h}h`
  return `${h}h ${mm}min`
}

// Enhanced modal functionality
const mostrarModalDetalles = ref(false)
const actividadSeleccionada = ref(null)

function abrirModal(a) {
  actividadSeleccionada.value = a
  mostrarModalDetalles.value = true
  // Prevent body scroll when modal is open
  document.body.style.overflow = 'hidden'
}

function cerrarModal() {
  actividadSeleccionada.value = null
  mostrarModalDetalles.value = false
  // Restore body scroll
  document.body.style.overflow = 'auto'
}

// Keyboard navigation for modal
onMounted(() => {
  const handleKeydown = (e) => {
    if (e.key === 'Escape' && mostrarModalDetalles.value) {
      cerrarModal()
    }
  }
  document.addEventListener('keydown', handleKeydown)

  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
    document.body.style.overflow = 'auto' // Ensure cleanup
  })
})
</script>

<style scoped>
/* Enhanced transitions for modals */
.modal-enter-active, .modal-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(-20px);
}

/* Improved scrollbar styling */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-track {
  background: #f8fafc;
  border-radius: 3px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
  background: linear-gradient(90deg, #cbd5e1, #94a3b8);
  border-radius: 3px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(90deg, #94a3b8, #64748b);
}

/* Subtle animations for cards */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.group:hover .group-hover\:animate-pulse {
  animation: pulse 2s infinite;
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth focus transitions */
input:focus, select:focus, button:focus {
  outline: none;
  transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* Enhanced backdrop blur support */
@supports (backdrop-filter: blur(12px)) {
  .backdrop-blur-sm {
    backdrop-filter: blur(12px);
  }
  .backdrop-blur-md {
    backdrop-filter: blur(16px);
  }
}

/* Tabular numbers for consistent alignment */
.tabular-nums {
  font-feature-settings: "tnum";
  font-variant-numeric: tabular-nums;
}
</style>
