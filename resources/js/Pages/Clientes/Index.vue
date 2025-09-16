<!-- /resources/js/Pages/Clientes/Index.vue -->
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

// Reutilizables
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue'
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue'
import Modales from '@/Components/IndexComponents/Modales.vue'

defineOptions({ layout: AppLayout })


// onMounted(() => {
//   console.log('Clientes recibidos:', props.clientes.data)
// })

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

// ---------- Flash messages ----------
onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})
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

// ---------- Estado UI ----------
const showModal = ref(false)
const timeoutRef = ref(null)
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
      (c.numero_interior ? `Int. ${c.numero_interior}` : ''),
      c.colonia,
      `${c.codigo_postal} ${c.municipio}`,
      `${c.estado_nombre}, ${c.pais}`
    ].filter(Boolean).join(', ')

    return {
      id: c.id,
      titulo: c.nombre_razon_social,
      subtitulo: c.email,
      estado: c.activo ? 'activo' : 'inactivo',
      extra: `RFC: ${c.rfc}`,
      fecha: c.created_at,
      meta: {
        telefono: c.telefono || 'N/A',
        direccion: direccion || 'Sin dirección',
        estado_nombre: c.estado_nombre || c.estado
      },
      raw: c
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

// Toggle activo/inactivo
const toggleCliente = (id) => {
  if (!id) return notyf.error('ID inválido')

  const cliente = clientesOriginales.value.find(c => c.id === id)
  if (!cliente) return notyf.error('Cliente no encontrado')

  const nuevoEstado = !cliente.activo
  const mensaje = nuevoEstado ? 'Cliente activado' : 'Cliente desactivado'

  router.put(route('clientes.toggle', id), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success(mensaje + ' correctamente')
      // Update local
      const idx = clientesOriginales.value.findIndex(c => c.id === id)
      if (idx !== -1) {
        clientesOriginales.value[idx].activo = nuevoEstado
        clientesOriginales.value[idx].estado_texto = nuevoEstado ? 'Activo' : 'Inactivo'
      }
    },
    onError: (errors) => {
      console.error(errors)
      notyf.error('No se pudo cambiar el estado del cliente')
    }
  })
}

// Exportar clientes
const exportClientes = () => {
  // Construir query string con filtros actuales para export consistente
  const params = new URLSearchParams({
    search: searchTerm.value,
    tipo_persona: props.filters?.tipo_persona || '',
    regimen_fiscal: props.filters?.regimen_fiscal || '',
    uso_cfdi: props.filters?.uso_cfdi || '',
    activo: filtroEstado.value ? (filtroEstado.value === 'activo' ? '1' : '0') : '',
    estado: props.filters?.estado || ''
  }).toString()

  const url = route('clientes.export') + (params ? `?${params}` : '')
  window.location.href = url // Download directo
}

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
    else if (campo === 'fecha') {
      va = new Date(a.fecha || '1970-01-01').getTime();
      vb = new Date(b.fecha || '1970-01-01').getTime();
    }
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

// ---------- Server-side filtering ----------
function applyFilters() {
  const params = {
    search: searchTerm.value || null,
    sort_by: sortBy.value.startsWith('nombre-') ? 'nombre_razon_social' : 'created_at',
    sort_direction: sortBy.value.endsWith('-asc') ? 'asc' : 'desc',
    activo: filtroEstado.value === 'activo' ? 1 : (filtroEstado.value === 'inactivo' ? 0 : null)
  };

  // Remove null/undefined values
  Object.keys(params).forEach(key => {
    if (params[key] == null) delete params[key];
  });

  router.get(route('clientes.index'), params, {
    preserveState: true,
    preserveScroll: true,
    only: ['clientes', 'stats', 'filters', 'sorting']
  });
}

// Watch changes to sync with server (debounce manual for search)
watch([searchTerm, sortBy, filtroEstado], () => {
  // Simple debounce: 500ms delay
  if (timeoutRef.value) clearTimeout(timeoutRef.value);
  timeoutRef.value = setTimeout(applyFilters, 500);
});
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
    :aprobadas="estadisticas.aprobadas"
    :pendientes="estadisticas.pendientes"
    v-model:search-term="searchTerm"
    v-model:sort-by="sortBy"
    v-model:filtro-estado="filtroEstado"
    :config="headerConfig"
    @limpiar-filtros="handleLimpiarFiltros"
  />

  <!-- Botón Exportar -->
  <div class="mt-4 flex justify-end">
    <button
      @click="exportClientes"
      class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      Exportar CSV
    </button>
  </div>

      <!-- Tabla (reutilizamos DocumentosTable con tipo='clientes') -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="clientesFiltradosYOrdenados"
          tipo="clientes"
          :mapeo="{
    nombre: 'titulo',
    subtitulo: 'subtitulo',
    extra: 'extra',
    estado: 'estado',
    fecha: 'fecha'
  }"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarCliente"
          @duplicar="duplicarNoSoportado"
          @imprimir="imprimirNoSoportado"
          @eliminar="confirmarEliminacion"
          @toggle="toggleCliente"
          @sort="updateSort"
        />
      </div>

      <!-- Paginación -->
      <div class="mt-8 flex justify-center">
        <nav v-if="props.clientes.last_page > 1" class="flex items-center space-x-2">
          <!-- Prev -->
          <button
            v-if="props.clientes.prev_page_url"
            @click="router.visit(props.clientes.prev_page_url)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
          >
            Anterior
          </button>
          <span class="px-4 py-2 text-gray-700">{{ props.clientes.from }} - {{ props.clientes.to }} de {{ props.clientes.total }}</span>
          <!-- Next -->
          <button
            v-if="props.clientes.next_page_url"
            @click="router.visit(props.clientes.next_page_url)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
          >
            Siguiente
          </button>
        </nav>
        <span v-else class="px-4 py-2 text-gray-700">{{ props.clientes.total }} clientes</span>
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
