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
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: AppLayout })

// ---------- Notificaciones ----------
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

// Props (array o paginator)
const props = defineProps({
  productos: { type: [Object, Array], required: true },
  stats: { type: Object, default: () => ({}) },
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
const filtroEstado = ref('')

// Paginación del lado del cliente
const currentPage = ref(1)
const perPage = ref(10)

// Header
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

watch(() => props.productos, (nuevo) => {
  productosOriginales.value =
    Array.isArray(nuevo?.data)
      ? [...nuevo.data]
      : Array.isArray(nuevo)
        ? [...nuevo]
        : []
}, { deep: true })

// ---------- Helper para estado del stock ----------
const getStockStatus = (stock) => {
  if (stock <= 0) return { status: 'agotado', color: 'text-red-600', bg: 'bg-red-50' }
  if (stock <= 10) return { status: 'bajo', color: 'text-amber-600', bg: 'bg-amber-50' }
  return { status: 'disponible', color: 'text-green-600', bg: 'bg-green-50' }
}

// ---------- Stats (total, en stock, bajo stock, agotado) ----------
const estadisticas = computed(() => {
  const data = productosOriginales.value
  const total = data.length
  const enStock = data.filter(p => Number(p.stock ?? p.cantidad_disponible ?? 0) > 10).length
  const bajoStock = data.filter(p => {
    const stock = Number(p.stock ?? p.cantidad_disponible ?? 0)
    return stock > 0 && stock <= 10
  }).length
  const agotado = data.filter(p => Number(p.stock ?? p.cantidad_disponible ?? 0) <= 0).length

  // UniversalHeader espera { total, aprobadas, pendientes }
  // Usamos aprobadas para "en stock" y pendientes para "necesita atención" (bajo stock + agotado)
  return {
    total,
    aprobadas: enStock,
    pendientes: bajoStock + agotado,
    detalles: {
      enStock,
      bajoStock,
      agotado
    }
  }
})

// ---------- Mapeo base para DocumentosTable ----------
const productosDocumentosBase = computed(() => {
  return productosOriginales.value.map(p => {
    const stock = Number(p.stock ?? p.cantidad_disponible ?? 0)
    const price = Number(p.precio_venta ?? p.precio ?? 0)
    const stockStatus = getStockStatus(stock)

    // Estado más detallado basado en stock
    let estadoStr = 'activo'
    if (p.estado !== 'activo') {
      estadoStr = 'inactivo'
    } else if (stock <= 0) {
      estadoStr = 'agotado'
    } else if (stock <= 10) {
      estadoStr = 'bajo-stock'
    }

    const precioTxt = price.toLocaleString('es-MX', {
      style: 'currency',
      currency: 'MXN',
      minimumFractionDigits: 2
    })

    // Stock más prominente en la información extra
    const stockTxt = `${stock} unidades`
    const extraTxt = `${precioTxt} • Stock: ${stockTxt}`

    return {
      id: p.id,
      nombre: p.nombre || 'Sin nombre',
      titulo: p.nombre || 'Sin nombre',
      // SKU como sublínea
      subtitulo: (p.sku || p.codigo_barras) ? `SKU: ${p.sku || p.codigo_barras}` : '',
      estado: estadoStr,
      sku: p.sku || p.codigo_barras || 'N/A',
      codigo_barras: p.codigo_barras || p.sku || 'N/A',

      // columnas de la tabla
      precio_venta: price,
      stock: stock,
      stock_status: stockStatus,
      extra: extraTxt,
      fecha: p.created_at,

      meta: {
        stock,
        precio: price,
        sku: p.sku || p.codigo_barras || 'N/A',
        descripcion: p.descripcion || '',
        stockStatus
      },
      raw: p
    }
  })
})

// ---------- Handlers ----------
function handleLimpiarFiltros () {
  searchTerm.value = ''
  sortBy.value = 'nombre-asc'
  filtroEstado.value = ''
  perPage.value = 10

  router.get(route('productos.index'), {}, {
    preserveState: true,
    preserveScroll: true
  })

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

// Función para alertar sobre productos con bajo stock
const verificarBajoStock = () => {
  const productosConBajoStock = productosOriginales.value.filter(p => {
    const stock = Number(p.stock ?? p.cantidad_disponible ?? 0)
    return stock > 0 && stock <= 10
  })

  if (productosConBajoStock.length > 0) {
    notyf.warning(`${productosConBajoStock.length} productos con stock bajo`)
  }
}

onMounted(() => {
  // Verificar stock bajo al cargar
  verificarBajoStock()
})

// Productos filtrados y ordenados (sin paginación)
const productosFiltradosYOrdenados = computed(() => {
  let arr = [...productosDocumentosBase.value]

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
    if (filtroEstado.value === 'bajo-stock') {
      arr = arr.filter(d => d.estado === 'bajo-stock')
    } else if (filtroEstado.value === 'agotado') {
      arr = arr.filter(d => d.estado === 'agotado')
    } else {
      arr = arr.filter(d => (d.estado || '').toLowerCase() === filtroEstado.value)
    }
  }

  const [campo, dir] = sortBy.value.split('-')
  arr.sort((a, b) => {
    let va = '', vb = ''
    if (campo === 'nombre') {
      va = (a.nombre || a.titulo || '').toLowerCase()
      vb = (b.nombre || b.titulo || '').toLowerCase()
    }
    else if (campo === 'fecha') {
      va = new Date(a.fecha || '').getTime()
      vb = new Date(b.fecha || '').getTime()
      return dir === 'asc' ? va - vb : vb - va
    }
    else if (campo === 'precio') {
      va = a.precio_venta || 0
      vb = b.precio_venta || 0
    }
    else if (campo === 'stock') {
      va = a.stock || 0
      vb = b.stock || 0
    }
    else if (campo === 'estado') {
      va = (a.estado || '').toLowerCase()
      vb = (b.estado || '').toLowerCase()
    }
    else {
      va = (a.nombre || a.titulo || '').toLowerCase()
      vb = (b.nombre || b.titulo || '').toLowerCase()
    }

    if (typeof va === 'number' && typeof vb === 'number') {
      return dir === 'asc' ? va - vb : vb - va
    }
    if (va > vb) return dir === 'asc' ? 1 : -1
    if (va < vb) return dir === 'asc' ? -1 : 1
    return 0
  })

  return arr
})

// Documentos para mostrar (con paginación del lado del cliente)
const documentosProductos = computed(() => {
  const startIndex = (currentPage.value - 1) * perPage.value
  const endIndex = startIndex + perPage.value
  return productosFiltradosYOrdenados.value.slice(startIndex, endIndex)
})

// Información de paginación
const totalPages = computed(() => Math.ceil(productosFiltradosYOrdenados.value.length / perPage.value))
const totalFiltered = computed(() => productosFiltradosYOrdenados.value.length)

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

// Manejo de paginación
const handlePerPageChange = (newPerPage) => {
  perPage.value = newPerPage
  currentPage.value = 1 // Reset to first page when changing per_page
}

const handlePageChange = (newPage) => {
  currentPage.value = newPage
}

function mapSortingToHeader (sorting) {
  const by = sorting?.sort_by ?? 'nombre'
  const dir = sorting?.sort_direction ?? 'asc'
  if (by === 'nombre') return `nombre-${dir}`
  if (by === 'created_at') return `fecha-${dir}`
  if (by === 'precio_venta') return `precio-${dir}`
  if (by === 'stock') return `stock-${dir}`
  return 'nombre-asc'
}
</script>

<template>
  <Head title="Productos" />
  <div class="productos-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
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

      <!-- Estadísticas detalladas del stock -->
      <div class="mt-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Productos</p>
              <p class="text-2xl font-bold text-gray-900">{{ estadisticas.total }}</p>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">En Stock</p>
              <p class="text-2xl font-bold text-green-600">{{ estadisticas.detalles.enStock }}</p>
            </div>
            <div class="p-2 bg-green-50 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Stock Bajo</p>
              <p class="text-2xl font-bold text-amber-600">{{ estadisticas.detalles.bajoStock }}</p>
            </div>
            <div class="p-2 bg-amber-50 rounded-lg">
              <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Agotado</p>
              <p class="text-2xl font-bold text-red-600">{{ estadisticas.detalles.agotado }}</p>
            </div>
            <div class="p-2 bg-red-50 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
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
          :documentos="documentosProductos"
          tipo="productos"
          :mapeo="{
            nombre: 'nombre',
            subtitulo: 'subtitulo',
            extra: 'extra',
            estado: 'estado',
            fecha: 'fecha',
            stock: 'stock'
          }"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarProducto"
          @duplicar="duplicarNoSoportado"
          @imprimir="imprimirNoSoportado"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
        />

        <!-- Componente de paginación -->
        <Pagination
          :pagination-data="paginationData"
          @per-page-change="handlePerPageChange"
          @page-change="handlePageChange"
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
  .productos-index .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.productos-index > * {
  animation: fadeIn 0.3s ease-out;
}

/* Animaciones para las tarjetas de estadísticas */
.productos-index .grid > div {
  transition: all 0.2s ease-in-out;
}
.productos-index .grid > div:hover {
  transform: translateY(-2px);
  shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.15);
}
</style>
