<template>
  <Head title="Bitácora de Actividades" />
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Bitácora de Actividades</h1>
            <p class="mt-2 text-sm text-gray-600">Registra y consulta lo que hacen tus empleados día a día.</p>
          </div>
          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3">
            <button
              @click="toggleView"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
            >
              <svg v-if="vistaTabla" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
              {{ vistaTabla ? 'Vista Tarjetas' : 'Vista Tabla' }}
            </button>
            <Link
              :href="route('bitacora.create')"
              class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Registrar Actividad
            </Link>
          </div>
        </div>
        <!-- Filtros -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-3">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input v-model="form.q" type="text" placeholder="Buscar por título o descripción..."
                     class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
            </div>
          </div>
          <select v-model="form.usuario" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Todos los empleados</option>
            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
          <select v-model="form.cliente" class="md:col-span-3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Todos los clientes</option>
            <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre_razon_social }}</option>
          </select>
          <select v-model="form.tipo" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Todos los tipos</option>
            <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
          </select>
          <select v-model="form.estado" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Todos los estados</option>
            <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
          </select>
          <input type="date" v-model="form.desde" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <input type="date" v-model="form.hasta" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <div class="md:col-span-2 flex gap-2">
            <button @click="apply" class="flex-1 px-4 py-2 rounded-lg bg-gray-900 text-white">Filtrar</button>
            <button @click="limpiarFiltros" class="flex-1 px-4 py-2 rounded-lg border">Limpiar</button>
          </div>
        </div>
      </div>
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Actividades (página)</p>
              <p class="text-2xl font-semibold text-gray-900">{{ actividadesPagina }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Completadas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ completadasPagina }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Horas registradas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ horasRegistradasPagina }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Clientes únicos</p>
              <p class="text-2xl font-semibold text-gray-900">{{ clientesUnicosPagina }}</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Content Area -->
      <div v-if="actividadesData.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table View -->
        <div v-if="vistaTabla" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Empleado</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actividad</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Duración</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="a in actividadesData" :key="a.id" class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(a.fecha) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ a.usuario?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ a.cliente?.nombre_razon_social || '—' }}</td>
                <td class="px-6 py-4">{{ a.titulo }}</td>
                <td class="px-6 py-4 whitespace-nowrap capitalize">{{ a.tipo }}</td>
                <td class="px-6 py-4 whitespace-nowrap capitalize">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', claseEstado(a.estado)]">{{ a.estado }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatearDuracion(a.inicio_at, a.fin_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="flex justify-center gap-2">
                    <button @click="abrirModal(a)" class="inline-flex items-center p-2 text-sm text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200" title="Ver detalles">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                    <Link :href="route('bitacora.edit', a.id)" class="inline-flex items-center p-2 text-sm text-amber-600 hover:text-amber-900 hover:bg-amber-50 rounded-lg transition-all duration-200" title="Editar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- Paginación -->
          <div class="p-4 flex items-center justify-between text-sm">
            <div>Mostrando {{ actividades.from }}-{{ actividades.to }} de {{ actividades.total }}</div>
            <div class="flex gap-2">
              <Link v-for="link in actividades.links" :key="link.label" :href="link.url || '#'" v-html="link.label" :class="['px-3 py-1 rounded', link.active ? 'bg-gray-900 text-white' : 'border']"/>
            </div>
          </div>
        </div>
        <!-- Card View -->
        <div v-else class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="a in actividadesData" :key="a.id" class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
              <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ a.titulo }}</h3>
                    <p class="text-sm text-gray-600">{{ formatDate(a.fecha) }} — {{ a.usuario?.name }}</p>
                  </div>
                  <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium capitalize', claseEstado(a.estado)]">{{ a.estado }}</span>
                </div>
                <div class="space-y-2 mb-4 text-sm">
                  <div class="flex justify-between"><span class="text-gray-600">Cliente:</span><span class="font-medium text-gray-900">{{ a.cliente?.nombre_razon_social || '—' }}</span></div>
                  <div class="flex justify-between"><span class="text-gray-600">Tipo:</span><span class="font-medium text-gray-900 capitalize">{{ a.tipo }}</span></div>
                  <div class="flex justify-between"><span class="text-gray-600">Duración:</span><span class="font-medium text-gray-900">{{ formatearDuracion(a.inicio_at, a.fin_at) }}</span></div>
                </div>
                <div class="flex gap-2">
                  <button @click="abrirModal(a)" class="flex-1 bg-blue-50 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors duration-200">Ver</button>
                  <Link :href="route('bitacora.edit', a.id)" class="flex-1 bg-amber-50 text-amber-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-amber-100 transition-colors duration-200 text-center">Editar</Link>
                </div>
              </div>
            </div>
          </div>
          <!-- Paginación -->
          <div class="p-4 flex items-center justify-between text-sm">
            <div>Mostrando {{ actividades.from }}-{{ actividades.to }} de {{ actividades.total }}</div>
            <div class="flex gap-2">
              <Link v-for="link in actividades.links" :key="link.label" :href="link.url || '#'" v-html="link.label" :class="['px-3 py-1 rounded', link.active ? 'bg-gray-900 text-white' : 'border']"/>
            </div>
          </div>
        </div>
      </div>
      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
          <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <h3 class="mt-6 text-lg font-medium text-gray-900">No hay actividades registradas</h3>
          <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
            {{ tieneFiltros ? 'No se encontraron actividades con los filtros aplicados' : 'Comienza registrando tu primera actividad' }}
          </p>
          <div class="mt-6">
            <Link v-if="!tieneFiltros" :href="route('bitacora.create')" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              Registrar Actividad
            </Link>
            <button v-else @click="limpiarFiltros" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-all duration-200">Limpiar Filtros</button>
          </div>
        </div>
      </div>
      <!-- Modal Detalles -->
      <Transition name="modal" appear>
        <div v-if="mostrarModalDetalles" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex justify-center items-center z-50 p-4">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all duration-300">
            <div class="p-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Detalle de actividad</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Fecha:</span> <span class="font-medium">{{ formatDate(actividadSeleccionada?.fecha) }}</span></div>
                <div><span class="text-gray-500">Hora:</span> <span class="font-medium">{{ actividadSeleccionada?.hora || '—' }}</span></div>
                <div><span class="text-gray-500">Empleado:</span> <span class="font-medium">{{ actividadSeleccionada?.usuario?.name }}</span></div>
                <div><span class="text-gray-500">Cliente:</span> <span class="font-medium">{{ actividadSeleccionada?.cliente?.nombre_razon_social || '—' }}</span></div>
                <div><span class="text-gray-500">Tipo:</span> <span class="font-medium capitalize">{{ actividadSeleccionada?.tipo }}</span></div>
                <div><span class="text-gray-500">Estado:</span> <span :class="['font-medium capitalize', claseEstado(actividadSeleccionada?.estado)]">{{ actividadSeleccionada?.estado }}</span></div>
                <div class="md:col-span-2"><span class="text-gray-500">Ubicación:</span> <span class="font-medium">{{ actividadSeleccionada?.ubicacion || '—' }}</span></div>
                <div class="md:col-span-2"><span class="text-gray-500">Duración:</span> <span class="font-medium">{{ formatearDuracion(actividadSeleccionada?.inicio_at, actividadSeleccionada?.fin_at) }}</span></div>
                <div class="md:col-span-2"><span class="text-gray-500">Descripción:</span>
                  <p class="mt-1 whitespace-pre-line">{{ actividadSeleccionada?.descripcion || '—' }}</p>
                </div>
              </div>
              <div class="mt-6 flex justify-end gap-2">
                <button @click="cerrarModal" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cerrar</button>
                <Link :href="route('bitacora.edit', actividadSeleccionada?.id)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Editar</Link>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
const props = defineProps({
  actividades: { type: Object, required: true }, // Paginado Laravel
  filters: { type: Object, default: () => ({}) },
  usuarios: { type: Array, default: () => [] },
  clientes: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
})

function formatDate(dateString) {
  if (!dateString) return '—'

  // Parsear la fecha, asumiendo que el formato es ISO (como "2025-09-10T07:00:00.000000Z")
  const date = new Date(dateString)

  // Verificar si la fecha es válida
  if (isNaN(date.getTime())) {
    return '—'
  }

  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
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

function toggleView() { vistaTabla.value = !vistaTabla.value }
function apply() { router.get(route('bitacora.index'), form.value, { preserveState: true, replace: true }) }
function limpiarFiltros() {
  form.value = { q: '', usuario: '', cliente: '', desde: '', hasta: '', tipo: '', estado: '' }
  apply()
}

function claseEstado(est) {
  switch(est) {
    case 'completado': return 'bg-green-100 text-green-800'
    case 'en_proceso': return 'bg-blue-100 text-blue-800'
    case 'pendiente': return 'bg-yellow-100 text-yellow-800'
    case 'cancelado': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

function diffMinutos(inicio, fin) {
  if(!inicio || !fin) return 0
  const i = new Date(inicio)
  const f = new Date(fin)
  const ms = f - i
  if(isNaN(ms) || ms < 0) return 0
  return Math.round(ms / 60000)
}

function formatearDuracion(inicio, fin) {
  const m = diffMinutos(inicio, fin)
  if(!m) return '—'
  const h = Math.floor(m / 60)
  const mm = m % 60
  return h ? `${h}h ${mm}m` : `${mm}m`
}

// Modal
const mostrarModalDetalles = ref(false)
const actividadSeleccionada = ref(null)
function abrirModal(a) { actividadSeleccionada.value = a; mostrarModalDetalles.value = true }
function cerrarModal() { actividadSeleccionada.value = null; mostrarModalDetalles.value = false }
</script>
<style scoped>
/* Transiciones para modales */
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
/* Scrollbar para tablas */
.overflow-x-auto::-webkit-scrollbar { height: 8px; }
.overflow-x-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.overflow-x-auto::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
.overflow-x-auto::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>
