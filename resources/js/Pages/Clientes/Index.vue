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
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: AppLayout })


// onMounted(() => {
//   console.log('Clientes recibidos:', props.clientes.data)
//   const flash = page.props.flash
//   if (flash?.success) notyf.success(flash.success)
//   if (flash?.error) notyf.error(flash.error)
// })
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

// Mapeo de claves SAT a nombres completos de estados
const estadoMapping = {
  'AGU': 'Aguascalientes',
  'BCN': 'Baja California',
  'BCS': 'Baja California Sur',
  'CAM': 'Campeche',
  'CHH': 'Chihuahua',
  'CHP': 'Chiapas',
  'CMX': 'Ciudad de México',
  'COA': 'Coahuila',
  'COL': 'Colima',
  'DUR': 'Durango',
  'GRO': 'Guerrero',
  'GUA': 'Guanajuato',
  'HID': 'Hidalgo',
  'JAL': 'Jalisco',
  'MEX': 'Estado de México',
  'MIC': 'Michoacán',
  'MOR': 'Morelos',
  'NAY': 'Nayarit',
  'NLE': 'Nuevo León',
  'OAX': 'Oaxaca',
  'PUE': 'Puebla',
  'QUE': 'Querétaro',
  'ROO': 'Quintana Roo',
  'SIN': 'Sinaloa',
  'SLP': 'San Luis Potosí',
  'SON': 'Sonora',
  'TAB': 'Tabasco',
  'TAM': 'Tamaulipas',
  'TLA': 'Tlaxcala',
  'VER': 'Veracruz',
  'YUC': 'Yucatán',
  'ZAC': 'Zacatecas'
}

const page = usePage()

// ---------- Estado UI ----------
const showModal = ref(false)
const modalMode = ref('details') // 'details' | 'confirm'
const selectedCliente = ref(null)
const selectedId = ref(null)

// ---------- Datos locales para manipulación ----------
const clientesOriginales = ref([])

// Filtros/ordenamiento (mapeados al header universal)
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref(mapSortingToHeader(props.sorting)) // 'fecha-desc' / 'fecha-asc' / 'nombre-asc' / 'nombre-desc'
const filtroEstado = ref(props.filters?.activo ?? '') // activo/inactivo (opcional) - usar el valor del backend

// Paginación del lado del cliente
const currentPage = ref(1)
const perPage = ref(10)

// Header configurable
const headerConfig = {
  module: 'clientes',
  createButtonText: 'Nuevo Cliente',
  searchPlaceholder: 'Buscar por nombre, RFC o email...'
}

// ---------- Datos base ----------
// Usamos directamente los datos del paginator del backend
const clientesPaginator = computed(() => props.clientes)
const clientesData = computed(() => clientesPaginator.value?.data || [])

// Nota: Ya no necesitamos watchers para sincronizar datos locales
// porque usamos directamente el paginator del backend

// ---------- Estadísticas locales ----------
const estadisticas = computed(() => ({
  total: props.stats?.total ?? 0,
  activos: props.stats?.activos ?? 0,   // usamos "activos" para mapear al header
  inactivos: props.stats?.inactivos ?? 0 // y "inactivos" idem
}))

// ---------- Transformación base para DocumentosTable ----------
console.log('Transformando clientes para DocumentosTable...')
// DocumentosTable es genérica; para `tipo="clientes"` asumimos formato:
// { id, titulo, subtitulo, estado, extra, fecha, meta, raw }
// Adaptamos cada cliente a ese shape sin perder el objeto original en "raw".
const documentosClientesBase = computed(() => {
  return clientesData.value.map(c => {
    const estadoNombre = estadoMapping[c.estado] || c.estado
    const direccion = [
      c.calle,
      c.numero_exterior,
      c.numero_interior,
      c.colonia,
      c.codigo_postal,
      c.municipio,
      estadoNombre,
      c.pais
    ].filter(Boolean).join(', ')

    return {
      id: c.id,
      titulo: c.nombre_razon_social,            // principal
      subtitulo: c.email || '',                 // debajo
      estado: c.activo ? 'activo' : 'inactivo', // badge/estado (activo/inactivo)
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
  perPage.value = 10

  router.get(route('clientes.index'), {
    search: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    activo: '',
    per_page: 10,
    page: 1
  }, {
    preserveState: true,
    preserveScroll: true
  })

  notyf.success('Filtros limpiados')
}

// Handler para búsqueda
function handleSearchChange(newSearch) {
  searchTerm.value = newSearch
  router.get(route('clientes.index'), {
    search: newSearch,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    activo: filtroEstado.value,
    per_page: perPage.value,
    page: 1 // Reset to first page
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

// Handler para filtro de estado
function handleEstadoChange(newEstado) {
  filtroEstado.value = newEstado
  router.get(route('clientes.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    activo: newEstado,
    per_page: perPage.value,
    page: 1 // Reset to first page
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

// Handler para ordenamiento
function handleSortChange(newSort) {
  sortBy.value = newSort
  router.get(route('clientes.index'), {
    search: searchTerm.value,
    sort_by: newSort.split('-')[0],
    sort_direction: newSort.split('-')[1] || 'desc',
    activo: filtroEstado.value,
    per_page: perPage.value,
    page: 1 // Reset to first page
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

// Handler para ordenamiento desde la tabla
function handleSortFromTable(newSort) {
  handleSortChange(newSort)
}

// El header emite v-model:sort-by (string). Lo mantenemos.
const updateSort = (newSort) => {
  const [field, direction] = newSort.split('-')
  router.get(route('clientes.index'), {
    ...props.filters,
    sort_by: field,
    sort_direction: direction
  }, {
    preserveState: true,
    preserveScroll: true
  })
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

const exportClientes = () => {
  const params = new URLSearchParams({
    search: searchTerm.value,
    activo: filtroEstado.value === 'activo' ? '1' : (filtroEstado.value === 'inactivo' ? '0' : ''),
  }).toString()
  const url = route('clientes.export') + (params ? `?${params}` : '')
  window.location.href = url
}

// ---------- Para DocumentosTable ----------
// Usamos directamente los datos transformados del paginator
const documentosClientes = computed(() => documentosClientesBase.value)

// ---------- Paginación del lado del servidor ----------
// Usamos directamente el paginator del backend
const paginationData = computed(() => ({
  current_page: clientesPaginator.value?.current_page || 1,
  last_page: clientesPaginator.value?.last_page || 1,
  per_page: clientesPaginator.value?.per_page || 10,
  from: clientesPaginator.value?.from || 0,
  to: clientesPaginator.value?.to || 0,
  total: clientesPaginator.value?.total || 0,
  prev_page_url: clientesPaginator.value?.prev_page_url,
  next_page_url: clientesPaginator.value?.next_page_url,
  links: clientesPaginator.value?.links || []
}))

// Watch para resetear página cuando cambien filtros
watch([searchTerm, sortBy, filtroEstado, perPage], () => {
  currentPage.value = 1
}, { deep: true })

// Manejo de paginación - Usando Inertia para navegación del lado del servidor
const handlePerPageChange = (newPerPage) => {
  router.get(route('clientes.index'), {
    ...props.filters,
    ...props.sorting,
    per_page: newPerPage,
    page: 1 // Reset to first page
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const handlePageChange = (newPage) => {
  router.get(route('clientes.index'), {
    ...props.filters,
    ...props.sorting,
    page: newPage
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

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


    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header universal reutilizable -->
      <UniversalHeader
     :total="estadisticas.total"
     :activos="estadisticas.activos"
     :inactivos="estadisticas.inactivos"
     v-model:search-term="searchTerm"
     v-model:sort-by="sortBy"
     v-model:filtro-estado="filtroEstado"
     :config="headerConfig"
     @limpiar-filtros="handleLimpiarFiltros"
     @update:searchTerm="handleSearchChange"
     @update:sortBy="handleSortChange"
     @update:filtroEstado="handleEstadoChange"
     @exportar="exportClientes"
   />



      <!-- Tabla (reutilizamos DocumentosTable con tipo='clientes') -->
      <div class="mt-6">

        <DocumentosTable
          :documentos="documentosClientes"
          tipo="clientes"
          :mapeo="{
            titulo: 'titulo',
            subtitulo: 'subtitulo',
            extra: 'extra',
            estado: 'estado',
            fecha: 'fecha'
          }"
          :search-term="props.filters?.search || ''"
          :sort-by="`${props.sorting?.sort_by || 'created_at'}-${props.sorting?.sort_direction || 'desc'}`"
          :filtro-estado="props.filters?.activo || ''"
          @ver-detalles="verDetalles"
          @editar="editarCliente"
          @duplicar="duplicarNoSoportado"
          @imprimir="imprimirNoSoportado"
          @eliminar="confirmarEliminacion"
          @sort="handleSortFromTable"
        />


        <!-- Componente de paginación -->
        <Pagination
          :pagination-data="paginationData"
          @per-page-change="handlePerPageChange"
          @page-change="handlePageChange"
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
  .clientes-index .max-w-8xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .clientes-index .grid-cols-3 {
    grid-template-columns: repeat(1, 1fr);
  }
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.clientes-index > * { animation: fadeIn 0.3s ease-out; }

/* Animaciones para las tarjetas de estadísticas */
.clientes-index .grid > div {
  transition: all 0.2s ease-in-out;
}
.clientes-index .grid > div:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.15);
}
</style>
