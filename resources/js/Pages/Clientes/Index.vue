<!-- /resources/js/Pages/Clientes/IndexNew.vue -->
<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

import ClientesHeader from '@/Components/IndexComponents/ClientesHeader.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  clientes: {
    type: Object,
    default: () => ({ data: [] })
  },
  estadisticas: {
    type: Object,
    default: () => ({
      total: 0,
      activos: 0,
      inactivos: 0,
      personas_fisicas: 0,
      personas_morales: 0,
      nuevos_mes: 0
    })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  sorting: {
    type: Object,
    default: () => ({ sort_by: 'created_at', sort_direction: 'desc' })
  },
  pagination: {
    type: Object,
    default: () => ({})
  },
})

/* =========================
   Configuración de notificaciones
========================= */
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

/* =========================
    Estado local y modal
 ========================= */
const showModal = ref(false)
const modalMode = ref('details')
const selectedCliente = ref(null)
const selectedId = ref(null)
const loading = ref(false)
const clientesCanDelete = ref(new Map())
const clientesPrestamos = ref(new Map())

/* =========================
   Filtros, orden y datos
========================= */
const searchTerm = ref('')
const sortBy = ref('fecha-desc')
const filtroEstado = ref('')

/* =========================
   Auditoría segura para el modal
========================= */
const auditoriaForModal = computed(() => {
  const r = selectedCliente.value
  if (!r) return null

  const meta = r.metadata || {}
  return {
    creado_por: r.creado_por_nombre || r.created_by_user_name || meta.creado_por || 'N/A',
    actualizado_por: r.actualizado_por_nombre || r.updated_by_user_name || meta.actualizado_por || 'N/A',
    eliminado_por: r.eliminado_por_nombre || r.deleted_by_user_name || meta.eliminado_por || null,
    creado_en: r.created_at || meta.creado_en || null,
    actualizado_en: r.updated_at || meta.actualizado_en || null,
    eliminado_en: r.deleted_at || meta.eliminado_en || null,
  }
})

/* =========================
   Paginación del servidor
========================= */
const paginationData = computed(() => ({
  current_page: props.pagination?.current_page || 1,
  last_page: props.pagination?.last_page || 1,
  per_page: props.pagination?.per_page || 10,
  from: props.pagination?.from || 0,
  to: props.pagination?.to || 0,
  total: props.pagination?.total || 0,
}))

const goToPage = (page) => {
  const query = {
    page,
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/clientes', { data: query })
}

const nextPage = () => {
  const currentPage = props.pagination?.current_page || 1
  const lastPage = props.pagination?.last_page || 1

  if (currentPage < lastPage) {
    goToPage(currentPage + 1)
  }
}

const prevPage = () => {
  const currentPage = props.pagination?.current_page || 1

  if (currentPage > 1) {
    goToPage(currentPage - 1)
  }
}

const handleLimpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  router.visit('/clientes')
  notyf.success('Filtros limpiados correctamente')
}

const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') {
    sortBy.value = newSort
    const query = {
      sort_by: newSort.split('-')[0],
      sort_direction: newSort.split('-')[1] || 'desc',
      search: searchTerm.value,
      estado: filtroEstado.value
    }
    router.visit('/clientes', { data: query })
  }
}

const changePerPage = (event) => {
  const perPage = event.target.value
  const query = {
    per_page: perPage,
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/clientes', { data: query })
}

const handleSearch = () => {
  const query = {
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/clientes', { data: query })
}

const handleFilter = () => {
  const query = {
    search: searchTerm.value,
    estado: filtroEstado.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc'
  }
  router.visit('/clientes', { data: query })
}

/* =========================
   Validaciones y utilidades
========================= */
function validarCliente(cliente) {
  if (!cliente?.id) {
    throw new Error('ID de cliente no válido')
  }
  return true
}

/* =========================
   Acciones CRUD
========================= */
const verDetalles = (cliente) => {
  try {
    validarCliente(cliente)
    selectedCliente.value = cliente
    modalMode.value = 'details'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarCliente = (id) => {
  try {
    const clienteId = id || selectedCliente.value?.id
    if (!clienteId) throw new Error('ID de cliente no válido')

    router.visit(`/clientes/${clienteId}/edit`)
  } catch (error) {
    notyf.error(error.message)
  }
}

const editarFila = (id) => {
  editarCliente(id)
}

const confirmarEliminacion = async (cliente) => {
  try {
    if (!cliente?.id) throw new Error('ID de cliente no válido')

    // Verificar si el cliente puede ser eliminado
    const canDelete = await checkCanDelete(cliente.id)

    if (!canDelete) {
      notyf.error('No se puede eliminar este cliente porque tiene documentos relacionados')
      return
    }

    selectedId.value = cliente.id
    modalMode.value = 'confirm'
    showModal.value = true
  } catch (error) {
    notyf.error(error.message)
  }
}

const checkCanDelete = async (clienteId) => {
  try {
    const response = await fetch(`/clientes/${clienteId}/can-delete`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    })

    const data = await response.json()

    if (data.success) {
      return data.can_delete
    } else {
      console.error('Error verificando si cliente puede ser eliminado:', data.message)
      return false
    }
  } catch (error) {
    console.error('Error en verificación de eliminación:', error)
    return false
  }
}

const checkHasPrestamos = async (clienteId) => {
  try {
    const response = await fetch(`/clientes/${clienteId}/has-prestamos`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    })

    const data = await response.json()

    if (data.success) {
      return data.has_prestamos
    } else {
      console.error('Error verificando préstamos del cliente:', data.message)
      return false
    }
  } catch (error) {
    console.error('Error en verificación de préstamos:', error)
    return false
  }
}

const eliminarCliente = async () => {
  try {
    if (!selectedId.value) throw new Error('No se seleccionó ningún cliente')

    loading.value = true

    // Usar la ruta nombrada correcta para eliminar
    router.delete(route('clientes.destroy', selectedId.value), {}, {
      onStart: () => {
        notyf.success('Eliminando cliente...')
      },
      onSuccess: (response) => {
        notyf.success('Cliente eliminado exitosamente')

        showModal.value = false
        selectedId.value = null
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors)
        notyf.error('Error al eliminar el cliente')
      },
      onFinish: () => {
        loading.value = false
      }
    })
  } catch (error) {
    notyf.error(error.message)
    loading.value = false
  }
}

const crearNuevoCliente = () => {
  router.visit('/clientes/create')
}

// Funciones para UniversalHeader
const focusSearch = () => { searchInput.value?.focus(); }
const handleSearchFocus = () => { isSearchFocused.value = true; }
const handleSearchBlur  = () => { isSearchFocused.value = false; }

const handleKeydown = (event) => {
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') { event.preventDefault(); focusSearch(); }
  if (event.key === 'Escape' && isSearchFocused.value) searchTerm.value = '';
}

const hayFiltrosActivos = computed(() => !!searchTerm.value || !!filtroEstado.value)

const limpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  handleLimpiarFiltros()
}

// Iconos para UniversalHeader
const icons = {
  document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  'document-text': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  'paper-airplane': 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
  'check-circle': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  'x-circle': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
  clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  plus: 'M12 4v16m8-8H4',
  search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
  x: 'M6 18L18 6M6 6l12 12',
  'filter-x': 'M6 18L18 6M6 6l12 12',
  'chevron-down': 'M19 9l-7 7-7-7',
  'arrow-down': 'M19 14l-7 7m0 0l-7-7m7 7V3',
  'arrow-up': 'M5 10l7-7m0 0l7 7m-7-7v18',
  'currency-dollar': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
  'sort-ascending': 'M3 4h6m0 0l4-4m-4 4l4 4M3 8h11m-11 4h14m-14 4h11m-11 4h6',
  'sort-descending': 'M3 4h14m-14 4h11m-11 4h6m0 0l-4 4m4-4l4-4M3 16h11m-11 4h14',
  export: 'M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z',
  import: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10',
  loading: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
  identification: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2',
  'status-online': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
}

const getIconPath = (iconName) => icons[iconName] || icons.document

// Estadísticas con porcentajes para clientes
const estadisticasConPorcentaje = computed(() => {
  const total = estadisticas.value.total || 1;

  return {
    activos: { ...{ label: 'Activos', icon: 'check-circle', color: 'green', description: 'Clientes activos' }, porcentaje: Math.round(((estadisticas.value.activos || 0) / total) * 100) },
    inactivos: { ...{ label: 'Inactivos', icon: 'x-circle', color: 'red', description: 'Clientes inactivos' }, porcentaje: Math.round(((estadisticas.value.inactivos || 0) / total) * 100) },
  };
})

// Variables para DocumentosTable
const showTooltip = ref(false)
const hoveredDoc = ref(null)
const tooltipPosition = ref({ x: 0, y: 0 })
let tooltipTimeout = null

// Tooltip optimizado
const showProductTooltip = (doc, event) => {
  if (!doc) return;
  clearTimeout(tooltipTimeout);
  hoveredDoc.value = doc;
  updateTooltipPosition(event);
  tooltipTimeout = setTimeout(() => { showTooltip.value = true; }, 500);
}

const hideProductTooltip = () => {
  clearTimeout(tooltipTimeout);
  tooltipTimeout = setTimeout(() => {
    showTooltip.value = false;
    hoveredDoc.value = null;
  }, 300);
}

const clearHideTimeout = () => {
  clearTimeout(tooltipTimeout);
}

const updateTooltipPosition = (event) => {
  tooltipPosition.value = { x: event.clientX, y: event.clientY };
}

// Tooltip style computed
const tooltipStyle = computed(() => {
  const OFFSET = 20, TOOLTIP_WIDTH = 320, TOOLTIP_HEIGHT = 384, VIEWPORT_PADDING = 16;
  const { w, h } = getViewport();

  let x = tooltipPosition.value.x + OFFSET;
  let y = tooltipPosition.value.y - (TOOLTIP_HEIGHT / 2);

  if (x + TOOLTIP_WIDTH > w - VIEWPORT_PADDING) x = tooltipPosition.value.x - TOOLTIP_WIDTH - OFFSET;
  if (x < VIEWPORT_PADDING) x = VIEWPORT_PADDING;
  if (y < VIEWPORT_PADDING) y = VIEWPORT_PADDING;
  else if (y + TOOLTIP_HEIGHT > h - VIEWPORT_PADDING) y = h - TOOLTIP_HEIGHT - VIEWPORT_PADDING;

  return {
    left: `${x}px`,
    top: `${y}px`,
    transform: showTooltip.value ? 'scale(1) translateY(0)' : 'scale(0.95) translateY(-10px)',
    opacity: showTooltip.value ? '1' : '0'
  };
})

const getViewport = () => {
  if (typeof window === 'undefined') return { w: 1280, h: 800 };
  return { w: window.innerWidth, h: window.innerHeight };
}

// Cache de formatos para mejor rendimiento
const formatCache = new Map();

const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible';
  const cacheKey = `fecha-${date}`;
  if (formatCache.has(cacheKey)) return formatCache.get(cacheKey);

  try {
    const time = new Date(date).getTime();
    if (Number.isNaN(time)) return 'Fecha inválida';
    const formatted = new Date(time).toLocaleDateString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
    formatCache.set(cacheKey, formatted);
    return formatted;
  } catch {
    return 'Fecha inválida';
  }
}

const formatearHora = (date) => {
  if (!date) return '';
  const cacheKey = `hora-${date}`;
  if (formatCache.has(cacheKey)) return formatCache.get(cacheKey);

  try {
    const time = new Date(date).getTime();
    if (Number.isNaN(time)) return '';
    const formatted = new Date(time).toLocaleTimeString('es-MX', {
      hour: '2-digit',
      minute: '2-digit'
    });
    formatCache.set(cacheKey, formatted);
    return formatted;
  } catch {
    return '';
  }
}

const formatearMoneda = (num) => {
  const value = parseFloat(num);
  const safe = Number.isFinite(value) ? value : 0;
  const cacheKey = `moneda-${safe}`;
  if (formatCache.has(cacheKey)) return formatCache.get(cacheKey);

  const formatted = new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(safe);
  formatCache.set(cacheKey, formatted);
  return formatted;
}

// Configuración de estados para clientes
const configEstados = {
  'activo': {
    label: 'Activo',
    classes: 'bg-green-100 text-green-700',
    color: 'bg-green-400'
  },
  'inactivo': {
    label: 'Inactivo',
    classes: 'bg-red-100 text-red-700',
    color: 'bg-red-400'
  }
};

const obtenerClasesEstado = (estado) => {
  return configEstados[estado]?.classes || 'bg-gray-100 text-gray-700';
}

const obtenerColorPuntoEstado = (estado) => {
  return configEstados[estado]?.color || 'bg-gray-400';
}

const obtenerLabelEstado = (estado) => {
  return configEstados[estado]?.label || 'Pendiente';
}

// Items filtrados y ordenados
const items = computed(() => {
  if (!Array.isArray(props.clientes.data)) {
    return [];
  }

  let filtered = props.clientes.data.slice();

  // Filtro de búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase().trim();
    filtered = filtered.filter(doc => {
      const nombre = doc.nombre_razon_social?.toLowerCase() || '';
      const telefono = (doc.telefono ?? '').toString().toLowerCase();
      const email = doc.email?.toLowerCase() || '';

      return nombre.includes(term) ||
             telefono.includes(term) ||
             email.includes(term);
    });
  }

  // Filtro de estado
  if (filtroEstado.value) {
    filtered = filtered.filter(doc => {
      if (filtroEstado.value === '1') return doc.activo === true || doc.activo === null;
      if (filtroEstado.value === '0') return doc.activo === false;
      return true;
    });
  }

  // Ordenamiento
  if (sortBy.value) {
    const [field, direction] = sortBy.value.split('-');

    filtered.sort((a, b) => {
      let aVal, bVal;

      switch (field) {
        case 'fecha':
          aVal = new Date(a.created_at || a.fecha).getTime() || 0;
          bVal = new Date(b.created_at || b.fecha).getTime() || 0;
          break;
        case 'cliente':
          aVal = (a.nombre_razon_social || '').toLowerCase();
          bVal = (b.nombre_razon_social || '').toLowerCase();
          break;
        case 'total':
          aVal = parseFloat(a.total) || 0;
          bVal = parseFloat(b.total) || 0;
          break;
        case 'estado':
          aVal = obtenerLabelEstado(a.activo ? 'activo' : 'inactivo').toLowerCase();
          bVal = obtenerLabelEstado(b.activo ? 'activo' : 'inactivo').toLowerCase();
          break;
        default:
          aVal = (a[field] || '').toString().toLowerCase();
          bVal = (b[field] || '').toString().toLowerCase();
      }

      const comparison = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
      return direction === 'desc' ? -comparison : comparison;
    });
  }

  return filtered;
});

// Sort function
const onSort = (field) => {
  const current = sortBy.value.startsWith(field) ? sortBy.value : `${field}-desc`;
  const newOrder = current === `${field}-desc` ? `${field}-asc` : `${field}-desc`;
  updateSort(newOrder);
}

// Funciones para Modal
const modalRef = ref(null)

const focusFirst = () => { try { modalRef.value?.focus() } catch {} }
watch(() => showModal, (v) => { if (v) setTimeout(focusFirst, 0) })

const onKey = (e) => { if (e.key === 'Escape' && showModal.value) onClose() }
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

const onCancel = () => { showModal.value = false; selectedCliente.value = null; selectedId.value = null; }
const onConfirm = () => { eliminarCliente() }
const onClose = () => { showModal.value = false; selectedCliente.value = null; selectedId.value = null; }
const onEditarFila = () => { editarCliente(selectedCliente.value?.id) }

// Helper functions
const isNumber = (n) => Number.isFinite(parseFloat(n))
</script>

<template>
  <Head title="Clientes" />

  <div class="clientes-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header específico de clientes -->
      <ClientesHeader
        :total="estadisticas.total"
        :activos="estadisticas.activos"
        :inactivos="estadisticas.inactivos"
        :personas_fisicas="estadisticas.personas_fisicas"
        :personas_morales="estadisticas.personas_morales"
        :nuevos_mes="estadisticas.nuevos_mes"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        @crear-nueva="crearNuevoCliente"
        @search-change="handleSearch"
        @filtro-estado-change="handleFilter"
        @sort-change="updateSort"
        @limpiar-filtros="limpiarFiltros"
      />

      <!-- Información de paginación -->
      <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
        <div>
          Mostrando {{ paginationData.from }} - {{ paginationData.to }} de {{ paginationData.total }} clientes
        </div>
        <div class="flex items-center space-x-2">
          <span>Elementos por página:</span>
          <select
            :value="paginationData.per_page"
            @change="changePerPage"
            class="border border-gray-300 rounded px-2 py-1 text-sm"
          >
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>

      <!-- Tabla de clientes -->
      <div class="mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <!-- Header -->
          <div class="bg-gradient-to-r from-gray-50 to-gray-100/50 px-6 py-4 border-b border-gray-200/60">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900 tracking-tight">Clientes</h2>
              <div class="text-sm text-gray-600 bg-white/70 px-3 py-1 rounded-full border border-gray-200/50">
                {{ items.length }} de {{ paginationData.total }} clientes
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200/60">
              <thead class="bg-gray-50/60">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Teléfono</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200/40">
                <template v-if="items.length > 0">
                  <tr
                    v-for="cliente in items"
                    :key="cliente.id"
                    class="group hover:bg-gray-50/60 transition-all duration-150 hover:shadow-sm"
                  >
                    <!-- Fecha -->
                    <td class="px-6 py-4">
                      <div class="flex flex-col space-y-0.5">
                        <div class="text-sm font-medium text-gray-900">
                          {{ formatearFecha(cliente.created_at || cliente.fecha) }}
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ formatearHora(cliente.created_at || cliente.fecha) }}
                        </div>
                      </div>
                    </td>

                    <!-- Cliente -->
                    <td class="px-6 py-4">
                      <div class="flex flex-col space-y-0.5">
                        <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800">
                          {{ cliente.nombre_razon_social || 'Sin nombre' }}
                        </div>
                      </div>
                    </td>

                    <!-- Email -->
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-700">{{ cliente.email || 'N/A' }}</div>
                    </td>

                    <!-- Teléfono -->
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-700">{{ cliente.telefono || 'N/A' }}</div>
                    </td>

                    <!-- Estado -->
                    <td class="px-6 py-4">
                      <span
                        :class="obtenerClasesEstado(cliente.activo ? 'activo' : 'inactivo')"
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-150 hover:shadow-sm"
                      >
                        <span
                          class="w-2 h-2 rounded-full mr-2 transition-all duration-150"
                          :class="obtenerColorPuntoEstado(cliente.activo ? 'activo' : 'inactivo')"
                        ></span>
                        {{ obtenerLabelEstado(cliente.activo ? 'activo' : 'inactivo') }}
                      </span>
                    </td>

                    <!-- Acciones -->
                    <td class="px-6 py-4">
                      <div class="flex items-center justify-end space-x-2">
                        <!-- Ver detalles -->
                        <button
                          @click="verDetalles(cliente)"
                          class="group/btn relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1"
                          title="Ver detalles"
                        >
                          <svg class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </button>

                        <!-- Ver préstamos del cliente (solo si tiene préstamos) -->
                        <Link
                          v-if="cliente.prestamos_count > 0"
                          :href="`/reportes/prestamos-por-cliente?cliente_id=${cliente.id}`"
                          class="group/btn relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:ring-offset-1"
                          :title="`Ver ${cliente.prestamos_count} préstamo(s) del cliente`"
                        >
                          <svg class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                        </Link>

                        <!-- Editar -->
                        <button
                          @click="editarCliente(cliente.id)"
                          class="group/btn relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-1"
                          title="Editar cliente"
                        >
                          <svg class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>

                        <!-- Eliminar (solo si no tiene documentos relacionados) -->
                        <button
                          @click="confirmarEliminacion(cliente)"
                          class="group/btn relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-1"
                          title="Eliminar cliente (solo si no tiene documentos relacionados)"
                        >
                          <svg class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>

                <!-- Empty State -->
                <tr v-else>
                  <td :colspan="6" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center space-y-4">
                      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                      </div>
                      <div class="space-y-1">
                        <p class="text-gray-700 font-medium">No hay clientes</p>
                        <p class="text-sm text-gray-500">Los clientes aparecerán aquí cuando se creen</p>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Controles de paginación -->
      <div v-if="paginationData.last_page > 1" class="flex justify-center items-center space-x-2 mt-6">
        <button
          @click="prevPage"
          :disabled="paginationData.current_page === 1"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Anterior
        </button>

        <div class="flex space-x-1">
          <button
            v-for="page in [paginationData.current_page - 1, paginationData.current_page, paginationData.current_page + 1].filter(p => p > 0 && p <= paginationData.last_page)"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-2 text-sm font-medium border border-gray-300 rounded-md',
              page === paginationData.current_page
                ? 'bg-blue-500 text-white border-blue-500'
                : 'text-gray-700 bg-white hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="nextPage"
          :disabled="paginationData.current_page === paginationData.last_page"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Siguiente
        </button>
      </div>
    </div>

    <!-- Modal de detalles / confirmación -->
    <Transition name="modal">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        @click.self="onClose"
      >
        <div
          class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 outline-none"
          role="dialog"
          aria-modal="true"
          :aria-label="`Modal de Cliente`"
          tabindex="-1"
          ref="modalRef"
          @keydown.esc.prevent="onClose"
        >
          <!-- Modo: Confirmación de eliminación -->
          <div v-if="modalMode === 'confirm'" class="text-center">
            <div class="w-12 h-12 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
            </div>
            <h3 class="text-lg font-medium mb-2">
              ¿Eliminar cliente?
            </h3>
            <p class="text-gray-600 mb-6">
              Esta acción no se puede deshacer.
            </p>
            <div class="flex gap-3">
              <button
                @click="onCancel"
                class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
              >
                Cancelar
              </button>
              <button
                @click="onConfirm"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
              >
                Eliminar
              </button>
            </div>
          </div>

          <!-- Modo: Detalles -->
          <div v-else-if="modalMode === 'details'" class="space-y-4">
            <h3 class="text-lg font-medium mb-1 flex items-center gap-2">
              Detalles de Cliente
              <span v-if="selectedCliente?.id" class="text-sm text-gray-500">#{{ selectedCliente.id }}</span>
            </h3>

            <div v-if="selectedCliente" class="space-y-4">
              <!-- Información general -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-600">
                    <strong>Nombre:</strong> {{ selectedCliente.nombre_razon_social || 'Sin nombre' }}
                  </p>
                  <p class="text-sm text-gray-600">
                    <strong>Email:</strong> {{ selectedCliente.email || 'N/A' }}
                  </p>
                  <p class="text-sm text-gray-600">
                    <strong>RFC:</strong> {{ selectedCliente.rfc || 'N/A' }}
                  </p>
                  <p class="text-sm text-gray-600">
                    <strong>Estado:</strong>
                    <span
                      :class="obtenerClasesEstado(selectedCliente.activo ? 'activo' : 'inactivo')"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    >
                      {{ obtenerLabelEstado(selectedCliente.activo ? 'activo' : 'inactivo') }}
                    </span>
                  </p>
                </div>

                <div>
                  <p class="text-sm text-gray-600">
                    <strong>Teléfono:</strong> {{ selectedCliente.telefono || 'N/A' }}
                  </p>
                  <p class="text-sm text-gray-600">
                    <strong>Fecha de creación:</strong> {{ formatearFecha(selectedCliente.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-wrap justify-end gap-2 mt-6">
              <button
                @click="editarFila"
                class="px-3 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors text-sm"
              >
                ✏️ Editar
              </button>

              <button
                @click="onClose"
                class="px-3 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors text-sm"
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Loading overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          <span class="text-gray-700">Procesando...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.clientes-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.97);
}
.modal-enter-to,
.modal-leave-from {
  opacity: 1;
  transform: scale(1);
}
</style>
