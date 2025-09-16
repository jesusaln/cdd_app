<!-- /resources/js/Pages/Productos/Index.vue -->
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

// Props (compatible con array o paginator)
const props = defineProps({
  productos: { type: [Object, Array], required: true }, // Array o paginator
  stats: { type: Object, default: () => ({}) }, // Opcional; computamos local si no
  catalogs: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({ sort_by: 'nombre', sort_direction: 'asc' }) },
})

// ---------- Estado UI ----------
const showModal = ref(false)
const modalMode = ref('details')
const selectedProducto = ref(null)
const selectedId = ref(null)

// Filtros/ordenamiento
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref(mapSortingToHeader(props.sorting))
const filtroEstado = ref('') // Sin default para mostrar todos

// Header configurable
const headerConfig = {
  module: 'productos',
  createButtonText: 'Nuevo Producto',
  searchPlaceholder: 'Buscar por nombre, SKU o descripción...'
}

// ---------- Datos base ----------
const productosOriginales = ref(
  Array.isArray(props.productos?.data)
    ? [...props.productos.data]
    : Array.isArray(props.productos)
      ? [...props.productos]
      : []
)

// Watch para updates Inertia
watch(() => props.productos, (nuevo) => {
  productosOriginales.value =
    Array.isArray(nuevo?.data)
      ? [...nuevo.data]
      : Array.isArray(nuevo)
        ? [...nuevo]
        : []
}, { deep: true })

// ---------- Estadísticas (locales si no del backend) ----------
const estadisticas = computed(() => {
  const data = productosOriginales.value
  const total = data.length
  const activos = data.filter(p => p.estado === 'activo' || (p.stock || 0) > 0).length
  const inactivos = total - activos
  return props.stats.total ? props.stats : { total, activos, inactivos }
})

// ---------- Transformación para DocumentosTable (sin duplicación de SKU) ----------
const productosDocumentos = computed(() => {
  return productosOriginales.value.map(p => {
    const stock = p.stock || 0
    const estadoStr = p.estado === 'activo' ? 'disponible' : (stock > 0 ? 'agotado' : 'descontinuado')
    const price = p.precio_venta || 0
    const priceFormatted = price.toLocaleString('es-MX', {minimumFractionDigits: 2})

    return {
      id: p.id,
      nombre: p.nombre || 'Sin nombre', // Para render/search
      titulo: p.nombre || 'Sin nombre', // Fallback genérico
      subtitulo: (p.descripcion || '').substring(0, 100) + (p.descripcion && p.descripcion.length > 100 ? '...' : ''),
      estado: estadoStr,
      sku: p.codigo_barras || 'N/A', // Value plain para columna extra y search
      codigo_barras: p.codigo_barras || 'N/A', // Para search
      precio_venta: price, // Para columna precio
      extra: `$${priceFormatted}`, // ← Solo precio, sin SKU para evitar duplicación
      fecha: p.created_at,
      meta: {
        stock: stock,
        precio: price,
        sku: p.codigo_barras || 'N/A',
        descripcion: p.descripcion || ''
      },
      raw: p
    }
  })
})

// ---------- Handlers (sin cambios) ----------
function handleLimpiarFiltros () {
  searchTerm.value = ''
  sortBy.value = 'nombre-asc'
  filtroEstado.value = ''
  notyf.success('Filtros limpiados')
}

const updateSort = (nuevo) => {
  if (nuevo && typeof nuevo === 'string') sortBy.value = nuevo
}

const verDetalles = (doc) => {
  if (!doc?.raw) return notyf.error('Producto inválido')
  selectedProducto.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

const editarProducto = (id) => {
  if (!id) return notyf.error('ID inválido')
  router.visit(route('productos.edit', id))
}

const confirmarEliminacion = (id) => {
  if (!id) return notyf.error('ID inválido')
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarProducto = () => {
  if (!selectedId.value) return notyf.error('No hay producto seleccionado')

  router.delete(route('productos.destroy', selectedId.value), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Producto eliminado')
      const idx = productosOriginales.value.findIndex(p => p.id === selectedId.value)
      if (idx !== -1) productosOriginales.value.splice(idx, 1)
      showModal.value = false
      selectedId.value = null
    },
    onError: (errors) => {
      console.error(errors)
      notyf.error('No se pudo eliminar el producto')
    }
  })
}

const duplicarNoSoportado = () => notyf.error('Duplicar no está disponible para productos')
const imprimirNoSoportado = () => notyf.error('Imprimir no está disponible para productos')

const toggleProducto = (id) => {
  if (!id) return notyf.error('ID inválido')
  const producto = productosOriginales.value.find(p => p.id === id)
  if (!producto) return notyf.error('Producto no encontrado')
  const nuevoEstado = producto.estado === 'activo' ? 'inactivo' : 'activo'
  const mensaje = nuevoEstado === 'activo' ? 'Producto activado' : 'Producto desactivado'

  router.put(route('productos.toggle', id), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success(mensaje + ' correctamente')
      const idx = productosOriginales.value.findIndex(p => p.id === id)
      if (idx !== -1) productosOriginales.value[idx].estado = nuevoEstado
    },
    onError: (errors) => {
      console.error(errors)
      notyf.error('No se pudo cambiar el estado del producto')
    }
  })
}

const exportProductos = () => {
  const params = new URLSearchParams({
    search: searchTerm.value,
    estado: filtroEstado.value,
  }).toString()
  const url = route('productos.export') + (params ? `?${params}` : '')
  window.location.href = url
}

// ---------- Filtrado/Ordenamiento (sin cambios) ----------
const productosFiltradosYOrdenados = computed(() => {
  let arr = [...productosDocumentos.value]

  if (searchTerm.value) {
    const q = searchTerm.value.toLowerCase()
    arr = arr.filter(d =>
      d.nombre?.toLowerCase().includes(q) ||
      d.subtitulo?.toLowerCase().includes(q) ||
      (d.sku || d.codigo_barras || '').toLowerCase().includes(q) ||
      d.raw?.descripcion?.toLowerCase().includes(q)
    )
  }

  if (filtroEstado.value) {
    arr = arr.filter(d => d.estado === filtroEstado.value)
  }

  const [campo, dir] = sortBy.value.split('-')
  arr.sort((a, b) => {
    let va = '', vb = ''
    if (campo === 'nombre') { va = (a.nombre || a.titulo || '').toLowerCase(); vb = (b.nombre || b.titulo || '').toLowerCase() }
    else if (campo === 'fecha') {
      va = new Date(a.fecha || ''); vb = new Date(b.fecha || '')
      return dir === 'asc' ? (va > vb ? 1 : -1) : (vb > va ? 1 : -1)
    }
    else if (campo === 'precio') { va = a.precio_venta || 0; vb = b.precio_venta || 0 }
    else if (campo === 'estado') { va = a.estado || ''; vb = b.estado || '' }
    else { va = (a.nombre || a.titulo || '').toLowerCase(); vb = (b.nombre || b.titulo || '').toLowerCase() }

    if (typeof va === 'number' && typeof vb === 'number') {
      return dir === 'asc' ? va - vb : vb - va
    }
    if (va > vb) return dir === 'asc' ? 1 : -1
    if (va < vb) return dir === 'asc' ? -1 : 1
    return 0
  })

  return arr
})

function mapSortingToHeader (sorting) {
  const by = sorting?.sort_by ?? 'nombre'
  const dir = sorting?.sort_direction ?? 'asc'
  if (by === 'nombre') return `nombre-${dir}`
  if (by === 'created_at') return `fecha-${dir}`
  if (by === 'precio_venta') return `precio-${dir}`
  return 'nombre-asc'
}
</script>

<template>
  <Head title="Productos" />
  <div class="productos-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <UniversalHeader
        :total="estadisticas.total"
        :aprobadas="estadisticas.activos"
        :pendientes="estadisticas.inactivos"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <div class="mt-4 flex justify-end">
        <button
          @click="exportProductos"
          class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Exportar CSV
        </button>
      </div>

      <div class="mt-6">
        <DocumentosTable
          :documentos="productosFiltradosYOrdenados"
          tipo="productos"
          :mapeo="{
            nombre: 'nombre',
            subtitulo: 'subtitulo',
            extra: 'extra',
            estado: 'estado',
            fecha: 'fecha'
          }"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarProducto"
          @duplicar="duplicarNoSoportado"
          @imprimir="imprimirNoSoportado"
          @eliminar="confirmarEliminacion"
          @toggle="toggleProducto"
          @sort="updateSort"
        />
      </div>
    </div>

    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedProducto"
      tipo="productos"
      @close="showModal = false"
      @confirm-delete="eliminarProducto"
      @editar="editarProducto"
    />
  </div>
</template>

<style scoped>
.productos-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
@media (max-width: 640px) {
  .productos-index .max-w-8xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.productos-index > * { animation: fadeIn 0.3s ease-out; }
</style>
