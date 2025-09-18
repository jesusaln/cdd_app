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

defineOptions({ layout: AppLayout })

/**
 * Props esperadas desde HerramientaController@index:
 * - herramientas: paginator (con .data)
 * - stats: { total, asignadas, sin_asignar }
 * - catalogs (opcional)
 * - filters (opcional)
 * - sorting (opcional)
 */
const props = defineProps({
  herramientas: { type: Object, required: true }, // paginator
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
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref(mapSortingToHeader(props.sorting)) // 'fecha-desc' / 'fecha-asc' / 'nombre-asc' / 'nombre-desc'
const filtroEstado = ref('') // asignada/sin_asignar

// Header configurable
const headerConfig = {
  module: 'herramientas',
  createButtonText: 'Nueva Herramienta',
  searchPlaceholder: 'Buscar por nombre, número de serie, técnico...'
}

// ---------- Datos base (paginados) ----------
const herramientasOriginales = ref(Array.isArray(props.herramientas?.data) ? [...props.herramientas.data] : [])

// Transformar URLs de fotos
herramientasOriginales.value = herramientasOriginales.value.map(h => ({
  ...h,
  foto: h.foto ? `/storage/${h.foto}` : null
}))

// Mantener sincronizado si cambian las props por navegación Inertia
watch(() => props.herramientas, (nuevo) => {
  herramientasOriginales.value = Array.isArray(nuevo?.data) ? [...nuevo.data] : []
  // Transformar URLs de fotos en cada cambio
  herramientasOriginales.value = herramientasOriginales.value.map(h => ({
    ...h,
    foto: h.foto ? `/storage/${h.foto}` : null
  }))
}, { deep: true })

// ---------- Estadísticas locales ----------
const estadisticas = computed(() => ({
  total: props.stats?.total ?? herramientasOriginales.value.length,
  aprobadas: props.stats?.asignadas ?? herramientasOriginales.value.filter(h => h.tecnico).length,   // usamos "aprobadas" para mapear al header
  pendientes: props.stats?.sin_asignar ?? herramientasOriginales.value.filter(h => !h.tecnico).length // y "pendientes" idem
}))

// ---------- Transformación para DocumentosTable ----------
// DocumentosTable es genérica; para `tipo="herramientas"` asumimos formato:
// { id, titulo, subtitulo, estado, extra, fecha, meta, raw }
// Adaptamos cada herramienta a ese shape sin perder el objeto original en "raw".
const documentosHerramientas = computed(() => {
  return herramientasOriginales.value.map(h => ({
    id: h.id,
    titulo: h.nombre || 'Sin nombre',        // principal
    subtitulo: h.numero_serie || 'N/A',      // número de serie
    estado: h.tecnico ? 'asignada' : 'sin_asignar', // badge/estado
    extra: h.tecnico ? `${h.tecnico.nombre} ${h.tecnico.apellido}` : 'Sin asignar', // técnico asignado
    created_at: h.created_at,                // fecha de creación para mostrar
    fecha: h.created_at,                     // para orden por fecha
    meta: {
      tecnico: h.tecnico,
      foto: h.foto
    },
    raw: h                               // guardamos el original
  }))
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
      // remover localmente sin esperar refetch
      const idx = herramientasOriginales.value.findIndex(h => h.id === selectedId.value)
      if (idx !== -1) herramientasOriginales.value.splice(idx, 1)

      showModal.value = false
      selectedId.value = null
    },
    onError: (errors) => {
      console.error(errors)
      notyf.error('No se pudo eliminar la herramienta')
    }
  })
}

// ---------- Búsqueda / Orden / Filtro estado (lado cliente) ----------
// Si tu DocumentosTable ya maneja search/sort/filters internamente, pásale estos v-models.
// Si NO, podrías filtrar/ordenar aquí y pasarle el resultado.
const herramientasFiltradasYOrdenadas = computed(() => {
  let arr = [...documentosHerramientas.value]

  // Buscar
  if (searchTerm.value) {
    const q = searchTerm.value.toLowerCase()
    arr = arr.filter(d =>
      d.titulo?.toLowerCase().includes(q) ||
      d.subtitulo?.toLowerCase().includes(q) ||
      d.extra?.toLowerCase().includes(q)
    )
  }

  // Filtro estado (opcional): 'asignada' | 'sin_asignar'
  if (filtroEstado.value) {
    arr = arr.filter(d => d.estado === filtroEstado.value)
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
  if (by === 'nombre') return `nombre-${dir}`
  if (by === 'created_at') return `fecha-${dir}`
  return 'fecha-desc'
}

// ---------- Handlers para modales ----------
const handleOpenImageModal = (imageUrl) => {
  selectedImage.value = imageUrl
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  selectedImage.value = ''
}

// ---------- Flash messages ----------
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
      <!-- Header universal reutilizable -->
      <UniversalHeader
        :total="props.stats.total"
        :aprobadas="props.stats.asignadas"
        :pendientes="props.stats.sin_asignar"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Tabla (reutilizamos DocumentosTable con tipo='herramientas') -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="herramientasFiltradasYOrdenadas"
          tipo="herramientas"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarHerramienta"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
          @open-image-modal="handleOpenImageModal"
        />

        <!-- Paginación -->
        <div class="mt-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Mostrando {{ props.herramientas.from }} a {{ props.herramientas.to }} de {{ props.herramientas.total }} resultados
            </div>
            <div class="flex space-x-1">
              <Link
                v-if="props.herramientas.prev_page_url"
                :href="props.herramientas.prev_page_url"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                preserve-scroll
              >
                Anterior
              </Link>

              <Link
                v-for="link in props.herramientas.links.filter(l => l.url)"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm font-medium border rounded-md',
                  link.active
                    ? 'text-blue-600 bg-blue-50 border-blue-500'
                    : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'
                ]"
                :preserve-scroll="true"
                v-html="link.label"
              />

              <Link
                v-if="props.herramientas.next_page_url"
                :href="props.herramientas.next_page_url"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                preserve-scroll
              >
                Siguiente
              </Link>
            </div>
          </div>
        </div>
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
      <div class="bg-white p-4 rounded-lg max-w-2xl max-h-[90vh] overflow-auto">
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
