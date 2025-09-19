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
// Usamos directamente los datos del paginator del backend
const productosPaginator = computed(() => props.productos)
const productosData = computed(() => productosPaginator.value?.data || [])

// Nota: Ya no necesitamos watchers para sincronizar datos locales
// porque usamos directamente el paginator del backend

// ---------- Helper para estado del stock ----------
// (Ya no se necesita para la nueva implementación simplificada)

// ---------- Estadísticas locales ----------
const estadisticas = computed(() => ({
  total: props.stats?.total ?? 0,
  aprobadas: props.stats?.activos ?? 0,   // productos activos
  pendientes: props.stats?.inactivos ?? 0 // productos inactivos
}))

// ---------- Transformación base para DocumentosTable ----------
const productosDocumentosBase = computed(() => {
  return productosData.value.map(p => {
    const stock = Number(p.stock ?? p.cantidad_disponible ?? 0)
    const price = Number(p.precio_venta ?? p.precio ?? 0)

    // Estado basado en stock y estado del producto
    // Primero priorizamos el stock, luego el estado del producto
    let estadoStr
    if (stock <= 0) {
      estadoStr = 'agotado'
    } else if (stock <= 10) {
      estadoStr = 'bajo_stock'
    } else {
      // Si hay stock suficiente, usamos el estado del producto
      estadoStr = p.estado || 'activo'
    }

    const precioTxt = price.toLocaleString('es-MX', {
      style: 'currency',
      currency: 'MXN',
      minimumFractionDigits: 2
    })

    // Información organizada para la tabla
    const stockInfo = `${stock} unidades`
    const precioInfo = precioTxt
    const skuInfo = p.sku || p.codigo_barras || 'N/A'

    return {
      id: p.id,
      titulo: p.nombre || 'Sin nombre',
      subtitulo: skuInfo !== 'N/A' ? `SKU: ${skuInfo}` : '',
      estado: estadoStr,
      extra: `${precioInfo} • Stock: ${stockInfo}`,
      fecha: p.created_at,
      // Campos adicionales para la tabla
      precio_venta: price,
      stock: stock,
      sku: skuInfo,
      raw: p
    }
  })
})

// ---------- Handlers UniversalHeader ----------
function handleLimpiarFiltros () {
  searchTerm.value = ''
  sortBy.value = 'nombre-asc'
  filtroEstado.value = ''
  perPage.value = 10

  router.get(route('productos.index'), {
    search: '',
    sort_by: 'nombre',
    sort_direction: 'asc',
    estado: '',
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
  router.get(route('productos.index'), {
    search: newSearch,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'asc',
    estado: filtroEstado.value,
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
  router.get(route('productos.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'asc',
    estado: newEstado,
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
  router.get(route('productos.index'), {
    search: searchTerm.value,
    sort_by: newSort.split('-')[0],
    sort_direction: newSort.split('-')[1] || 'asc',
    estado: filtroEstado.value,
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

// Handler para ordenamiento desde la tabla (ya definido arriba)

const verDetalles = (doc) => {
  if (!doc?.raw) return notyf.error('Producto inválido')
  selectedProducto.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

// También necesitamos actualizar el handler para usar el objeto correcto
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
      showModal.value = false
      selectedId.value = null
      // Recargar la página para actualizar la lista
      router.reload()
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
  // Buscar el producto en los datos actuales
  const producto = productosData.value.find(p => p.id === id)
  if (!producto) return notyf.error('Producto no encontrado')
  const nuevoEstado = producto.estado === 'activo' ? 'inactivo' : 'activo'
  const mensaje = nuevoEstado === 'activo' ? 'Producto activado' : 'Producto desactivado'

  router.put(route('productos.toggle', id), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success(mensaje + ' correctamente')
      // Recargar la página para actualizar los datos
      router.reload()
    },
    onError: (errors) => {
      console.error(errors)
      notyf.error('No se pudo cambiar el estado del producto')
    }
  })
}

const exportProductos = () => {
  const params = new URLSearchParams()

  if (searchTerm.value) {
    params.append('search', searchTerm.value)
  }

  if (filtroEstado.value) {
    params.append('estado', filtroEstado.value)
  }

  const queryString = params.toString()
  const url = route('productos.export') + (queryString ? `?${queryString}` : '')
  window.location.href = url
}

// ---------- Flash messages ----------
onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})

// ---------- Documentos para mostrar ----------
// Usamos directamente los datos transformados del paginator
const documentosProductos = computed(() => productosDocumentosBase.value)

// ---------- Paginación del lado del servidor ----------
// Usamos directamente el paginator del backend
const paginationData = computed(() => ({
  current_page: productosPaginator.value?.current_page || 1,
  last_page: productosPaginator.value?.last_page || 1,
  per_page: productosPaginator.value?.per_page || 10,
  from: productosPaginator.value?.from || 0,
  to: productosPaginator.value?.to || 0,
  total: productosPaginator.value?.total || 0,
  prev_page_url: productosPaginator.value?.prev_page_url,
  next_page_url: productosPaginator.value?.next_page_url,
  links: productosPaginator.value?.links || []
}))

// ---------- Paginación del lado del servidor ----------
// Manejo de paginación - Usando Inertia para navegación del lado del servidor
const handlePerPageChange = (newPerPage) => {
  router.get(route('productos.index'), {
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
  router.get(route('productos.index'), {
    ...props.filters,
    ...props.sorting,
    page: newPage
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

function mapSortingToHeader (sorting) {
  // mapping simple desde sorting del servidor a las opciones del header
  // opciones del header: 'nombre-asc', 'nombre-desc', 'fecha-asc', 'fecha-desc'
  const by = sorting?.sort_by ?? 'nombre'
  const dir = sorting?.sort_direction ?? 'asc'
  if (by === 'nombre') return `nombre-${dir}`
  if (by === 'created_at') return `fecha-${dir}`
  return 'nombre-asc'
}
</script>

<template>
  <Head title="Productos" />
  <div class="productos-index min-h-screen bg-gray-50">
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
       @update:searchTerm="handleSearchChange"
       @update:sortBy="handleSortChange"
       @update:filtroEstado="handleEstadoChange"
       @exportar="exportProductos"
     />

      <!-- Tabla de productos -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="productosDocumentosBase"
          tipo="productos"
          :search-term="props.filters?.search || ''"
          :sort-by="`${props.sorting?.sort_by || 'nombre'}-${props.sorting?.sort_direction || 'asc'}`"
          :filtro-estado="props.filters?.estado || ''"
          @ver-detalles="verDetalles"
          @editar="editarProducto"
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
