<!-- /resources/js/Pages/Herramientas/Index.vue -->
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

/**
 * Props esperadas desde HerramientaController@index:
 * - herramientas: objeto de paginación de Laravel
 * - stats: { total, asignadas, sin_asignar }
 * - catalogs (opcional)
 * - filters (opcional)
 * - sorting (opcional)
 */
const props = defineProps({
  herramientas: { type: Array, required: true }, // array directo para paginación del lado del cliente
  stats: {
    type: Object,
    default: () => ({ total: 0, asignadas: 0, sin_asignar: 0 })
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
const selectedHerramienta = ref(null)
const selectedId = ref(null)

// Modal de imagen
const showImageModal = ref(false)
const selectedImage = ref('')

// Filtros/ordenamiento (mapeados al header universal)
const searchTerm = ref('')
const sortBy = ref('fecha-desc')
const filtroEstado = ref('')

// Paginación del lado del cliente
const currentPage = ref(1)
const perPage = ref(10)

// Header configurable
const headerConfig = {
  module: 'herramientas',
  createButtonText: 'Nueva Herramienta',
  searchPlaceholder: 'Buscar por nombre, número de serie, técnico...'
}

// ---------- Datos base ----------
// Función para transformar URLs de fotos
const transformarHerramientas = (herramientas) => {
  return Array.isArray(herramientas) ? herramientas.map(h => ({
    ...h,
    foto: h.foto ? `/storage/${h.foto}` : null
  })) : []
}

// Estadísticas calculadas
const estadisticas = computed(() => props.stats)

// Transformación base para DocumentosTable
const documentosHerramientasBase = computed(() => {
  const herramientasArray = props.herramientas || []
  const transformadas = transformarHerramientas(herramientasArray)

  return transformadas.map(h => ({
    id: h.id,
    titulo: h.nombre || 'Sin nombre',
    subtitulo: h.numero_serie || 'N/A',
    estado: h.tecnico ? 'asignada' : 'sin_asignar',
    extra: h.tecnico ? `${h.tecnico.nombre} ${h.tecnico.apellido}` : 'Sin asignar',
    fecha: h.created_at,
    meta: { tecnico: h.tecnico, foto: h.foto },
    raw: h
  }))
})

// Herramientas filtradas y ordenadas (sin paginación)
const herramientasFiltradasYOrdenadas = computed(() => {
  let arr = [...documentosHerramientasBase.value]

  // Buscar
  if (searchTerm.value) {
    const q = searchTerm.value.toLowerCase()
    arr = arr.filter(d =>
      d.titulo?.toLowerCase().includes(q) ||
      d.subtitulo?.toLowerCase().includes(q) ||
      d.extra?.toLowerCase().includes(q)
    )
  }

  // Filtro estado
  if (filtroEstado.value) {
    arr = arr.filter(d => d.estado === filtroEstado.value)
  }

  // Orden
  const [campo, dir] = sortBy.value.split('-')
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

// Documentos para mostrar (con paginación del lado del cliente)
const documentosHerramientas = computed(() => {
  const startIndex = (currentPage.value - 1) * perPage.value
  const endIndex = startIndex + perPage.value
  return herramientasFiltradasYOrdenadas.value.slice(startIndex, endIndex)
})

// Información de paginación
const totalPages = computed(() => Math.ceil(herramientasFiltradasYOrdenadas.value.length / perPage.value))
const totalFiltered = computed(() => herramientasFiltradasYOrdenadas.value.length)

// Datos de paginación simulados para el componente Pagination
const paginationData = computed(() => ({
  current_page: currentPage.value,
  last_page: totalPages.value,
  per_page: perPage.value,
  from: totalFiltered.value > 0 ? ((currentPage.value - 1) * perPage.value) + 1 : 0,
  to: Math.min(currentPage.value * perPage.value, totalFiltered.value),
  total: totalFiltered.value,
  prev_page_url: currentPage.value > 1 ? '#' : null,
  next_page_url: currentPage.value < totalPages.value ? '#' : null,
  links: [] // No necesitamos links para client-side
}))

// Watch para resetear página cuando cambien filtros
watch([searchTerm, sortBy, filtroEstado, perPage], () => {
  currentPage.value = 1
}, { deep: true })

// Handlers UniversalHeader
function handleLimpiarFiltros() {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  perPage.value = 10

  router.get(route('herramientas.index'), {}, {
    preserveState: true,
    preserveScroll: true
  })

  notyf.success('Filtros limpiados correctamente')
}

const updateSort = (nuevo) => {
  if (nuevo && typeof nuevo === 'string') {
    sortBy.value = nuevo
  }
}

// Manejo de paginación
const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage
  currentPage.value = 1 // Reset to first page when changing per_page
}

const handlePageChange = (newPage) => {
  currentPage.value = newPage
}

// ---------- Acciones ----------
const verDetalles = (doc) => {
  if (!doc?.raw) return notyf.error('Herramienta inválida')
  selectedHerramienta.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

const editarHerramienta = (id) => {
  if (!id) return notyf.error('ID inválido')
  router.visit(route('herramientas.edit', id))
}

const confirmarEliminacion = (id) => {
  if (!id) return notyf.error('ID inválido')
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarHerramienta = () => {
  if (!selectedId.value) return notyf.error('No hay herramienta seleccionada')

  router.delete(route('herramientas.destroy', selectedId.value), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Herramienta eliminada')
      showModal.value = false
      selectedId.value = null
      // Recargar la página actual para actualizar los datos
      router.reload({ preserveScroll: true })
    },
    onError: (errors) => {
      console.error(errors)
      notyf.error('No se pudo eliminar la herramienta')
    }
  })
}


function mapSortingToHeader(sorting) {
  const by = sorting?.sort_by ?? 'created_at'
  const dir = sorting?.sort_direction ?? 'desc'
  if (by === 'nombre') return `nombre-${dir}`
  if (by === 'created_at') return `fecha-${dir}`
  return 'fecha-desc'
}

const handleOpenImageModal = (imageUrl) => {
  selectedImage.value = imageUrl
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  selectedImage.value = ''
}

onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})
</script>

<template>
  <Head title="Herramientas" />
  <div class="herramientas-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <UniversalHeader
        :total="estadisticas.total"
        :aprobadas="estadisticas.asignadas"
        :pendientes="estadisticas.sin_asignar"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <div class="mt-6">
        <DocumentosTable
          :documentos="documentosHerramientas"
          tipo="herramientas"
          :mapeo="{
            nombre: 'herramienta.nombre',
            numero_serie: 'herramienta.numero_serie',
            asignada: 'herramienta.tecnico',
            fecha: 'herramienta.created_at'
          }"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarHerramienta"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
          @open-image-modal="handleOpenImageModal"
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
      :selected="selectedHerramienta"
      tipo="herramientas"
      @close="showModal = false"
      @confirm-delete="eliminarHerramienta"
      @editar="editarHerramienta"
    />

    <!-- Modal para imagen ampliada -->
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4" @click.self="closeImageModal">
      <div class="bg-white p-4 rounded-lg max-w-2xl max-h-[90vh] overflow-auto relative">
        <img :src="selectedImage" alt="Imagen ampliada" class="max-w-full h-auto rounded-lg" />
        <button @click="closeImageModal" class="absolute top-2 right-2 text-white bg-black/50 rounded-full p-2 hover:bg-black/70 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.herramientas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
@media (max-width: 640px) {
  .herramientas-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .herramientas-index h1 {
    font-size: 1.5rem;
  }
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.herramientas-index > * { animation: fadeIn 0.3s ease-out; }
</style>
