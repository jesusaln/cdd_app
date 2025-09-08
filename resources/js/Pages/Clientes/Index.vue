<!-- /resources/js/Pages/Clientes/Index.vue -->
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

// Reutilizables
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue'
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue'
import Modales from '@/Components/IndexComponents/Modales.vue'

defineOptions({ layout: AppLayout })

/**
 * Props esperadas desde ClienteController@index:
 * - clientes: paginator (con .data)
 * - stats: { total, activos, inactivos }
 * - catalogs (opcional)
 * - filters (opcional)
 * - sorting (opcional)
 */
const props = defineProps({
  clientes: { type: Object, required: true }, // paginator
  stats: {
    type: Object,
    default: () => ({ total: 0, activos: 0, inactivos: 0 })
  },
  catalogs: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({ sort_by: 'created_at', sort_direction: 'desc' }) },
})

// ---------- Notificaciones ----------
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false }
  ]
})

const page = usePage()

// ---------- Estado UI ----------
const showModal = ref(false)
const modalMode = ref('details') // 'details' | 'confirm'
const selectedCliente = ref(null)
const selectedId = ref(null)

// Filtros/ordenamiento (mapeados al header universal)
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref(mapSortingToHeader(props.sorting)) // 'fecha-desc' / 'fecha-asc' / 'nombre-asc' / 'nombre-desc'
const filtroEstado = ref('') // activo/inactivo (opcional)

// Header configurable
const headerConfig = {
  module: 'clientes',
  createButtonText: 'Nuevo Cliente',
  searchPlaceholder: 'Buscar por nombre, RFC o email...'
}

// ---------- Datos base (paginados) ----------
const clientesOriginales = ref(Array.isArray(props.clientes?.data) ? [...props.clientes.data] : [])

// Mantener sincronizado si cambian las props por navegación Inertia
watch(() => props.clientes, (nuevo) => {
  clientesOriginales.value = Array.isArray(nuevo?.data) ? [...nuevo.data] : []
}, { deep: true })

// ---------- Estadísticas locales ----------
const estadisticas = computed(() => ({
  total: props.stats?.total ?? clientesOriginales.value.length,
  aprobadas: props.stats?.activos ?? clientesOriginales.value.filter(c => c.activo).length,   // usamos "aprobadas" para mapear al header
  pendientes: props.stats?.inactivos ?? clientesOriginales.value.filter(c => !c.activo).length // y "pendientes" idem
}))

// ---------- Transformación para DocumentosTable ----------
// DocumentosTable es genérica; para `tipo="clientes"` asumimos formato:
// { id, titulo, subtitulo, estado, extra, fecha, meta, raw }
// Adaptamos cada cliente a ese shape sin perder el objeto original en "raw".
const documentosClientes = computed(() => {
  return clientesOriginales.value.map(c => {
    const direccion = [
      c.calle,
      c.numero_exterior,
      c.numero_interior,
      c.colonia,
      c.codigo_postal,
      c.municipio,
      c.estado,
      c.pais
    ].filter(Boolean).join(', ')

    return {
      id: c.id,
      titulo: c.nombre_razon_social,            // principal
      subtitulo: c.email || '',                 // debajo
      estado: c.activo ? 'activo' : 'inactivo', // badge/estado
      extra: c.rfc,                              // rfc como etiqueta secundaria
      fecha: c.created_at,                       // para orden por fecha
      meta: {
        telefono: c.telefono || '',
        direccion
      },
      raw: c                                     // guardamos el original
    }
  })
})

// ---------- Handlers UniversalHeader ----------
function handleLimpiarFiltros () {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  notyf.success('Filtros limpiados')
}

// El header emite v-model:sort-by (string). Lo mantenemos.
const updateSort = (nuevo) => {
  if (nuevo && typeof nuevo === 'string') {
    sortBy.value = nuevo
  }
}

// ---------- Acciones ----------
const verDetalles = (doc) => {
  if (!doc?.raw) return notyf.error('Cliente inválido')
  selectedCliente.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

const editarCliente = (id) => {
  if (!id) return notyf.error('ID inválido')
  router.visit(route('clientes.edit', id))
}

const confirmarEliminacion = (id) => {
  if (!id) return notyf.error('ID inválido')
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarCliente = () => {
  if (!selectedId.value) return notyf.error('No hay cliente seleccionado')

  router.delete(route('clientes.destroy', selectedId.value), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Cliente eliminado')
      // remover localmente sin esperar refetch
      const idx = clientesOriginales.value.findIndex(c => c.id === selectedId.value)
      if (idx !== -1) clientesOriginales.value.splice(idx, 1)

      showModal.value = false
      selectedId.value = null
    },
    onError: (errors) => {
      console.error(errors)
      notyf.error('No se pudo eliminar el cliente')
    }
  })
}

// Acciones que no aplican para clientes (pero DocumentosTable puede emitir)
const duplicarNoSoportado = () => notyf.error('Duplicar no está disponible para clientes')
const imprimirNoSoportado = () => notyf.error('Imprimir no está disponible para clientes')

// ---------- Búsqueda / Orden / Filtro estado (lado cliente) ----------
// Si tu DocumentosTable ya maneja search/sort/filters internamente, pásale estos v-models.
// Si NO, podrías filtrar/ordenar aquí y pasarle el resultado.
const clientesFiltradosYOrdenados = computed(() => {
  let arr = [...documentosClientes.value]

  // Buscar
  if (searchTerm.value) {
    const q = searchTerm.value.toLowerCase()
    arr = arr.filter(d =>
      d.titulo?.toLowerCase().includes(q) ||
      d.subtitulo?.toLowerCase().includes(q) ||
      d.extra?.toLowerCase().includes(q)
    )
  }

  // Filtro estado (opcional): 'activo' | 'inactivo'
  if (filtroEstado.value) {
    arr = arr.filter(d => (filtroEstado.value === 'activo' ? d.estado === 'activo' : d.estado === 'inactivo'))
  }

  // Orden
  const [campo, dir] = sortBy.value.split('-') // ejemplo: 'nombre-asc', 'fecha-desc'
  arr.sort((a, b) => {
    let va = '', vb = ''
    if (campo === 'nombre') { va = (a.titulo || '').toLowerCase(); vb = (b.titulo || '').toLowerCase() }
    else if (campo === 'fecha') { va = a.fecha || ''; vb = b.fecha || '' }
    else { va = (a.titulo || '').toLowerCase(); vb = (b.titulo || '').toLowerCase() }

    if (va > vb) return dir === 'asc' ? 1 : -1
    if (va < vb) return dir === 'asc' ? -1 : 1
    return 0
  })

  return arr
})

// ---------- Helpers ----------
function mapSortingToHeader (sorting) {
  // mapping simple desde sorting del servidor a las opciones del header
  // opciones del header: 'nombre-asc', 'nombre-desc', 'fecha-asc', 'fecha-desc'
  const by = sorting?.sort_by ?? 'created_at'
  const dir = sorting?.sort_direction ?? 'desc'
  if (by === 'nombre_razon_social') return `nombre-${dir}`
  if (by === 'created_at') return `fecha-${dir}`
  return 'fecha-desc'
}

// ---------- Flash messages ----------
onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})
</script>

<template>
  <Head title="Clientes" />

  <div class="clientes-index min-h-screen bg-gray-50">
    <!-- Header superior tipo Cotizaciones -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Clientes</h1>
            <p class="text-gray-600">Administra y gestiona todos tus clientes</p>
          </div>
          <div>
            <Link
              :href="route('clientes.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nuevo Cliente
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header universal reutilizable -->
      <UniversalHeader
    :total="props.stats.total"
    :aprobadas="props.stats.activos"
    :pendientes="props.stats.inactivos"
    v-model:search-term="searchTerm"
    v-model:sort-by="sortBy"
    v-model:filtro-estado="filtroEstado"
    :config="headerConfig"
    @limpiar-filtros="handleLimpiarFiltros"
  />

      <!-- Tabla (reutilizamos DocumentosTable con tipo='clientes') -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="clientesFiltradosYOrdenados"
          tipo="clientes"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarCliente"
          @duplicar="duplicarNoSoportado"
          @imprimir="imprimirNoSoportado"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
        />
      </div>
    </div>

    <!-- Modales genéricos -->
    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedCliente"
      tipo="clientes"
      @close="showModal = false"
      @confirm-delete="eliminarCliente"
      @editar="editarCliente"
    />
  </div>
</template>

<style scoped>
.clientes-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
@media (max-width: 640px) {
  .clientes-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .clientes-index h1 {
    font-size: 1.5rem;
  }
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.clientes-index > * { animation: fadeIn 0.3s ease-out; }
</style>
