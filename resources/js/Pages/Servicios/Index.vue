<!-- /resources/js/Pages/Servicios/Index.vue -->
<template>
    <Head title="Servicios" />
    <div class="servicios-index min-h-screen bg-gray-50">
        <div class="max-w-8xl mx-auto px-6 py-8">
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
              @exportar="exportServicios"
            />


            <div class="mt-6">
                <DocumentosTable
                  :documentos="documentosServicios"
                  tipo="servicios"
                  :search-term="searchTerm"
                  :sort-by="sortBy"
                  :filtro-estado="filtroEstado"
                  @ver-detalles="verDetalles"
                  @editar="editarServicio"
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

        <!-- Modales -->
        <Modales
            :show="showModal"
            :mode="modalMode"
            :selected="selectedServicio"
            tipo="servicios"
            @close="showModal = false"
            @confirm-delete="eliminarServicio"
            @editar="editarServicio"
        />
    </div>
</template>

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
  servicios: { type: [Object, Array], required: true },
  stats: { type: Object, default: () => ({}) },
  catalogs: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({ sort_by: 'nombre', sort_direction: 'asc' }) },
})

// ---------- Estado UI ----------
const showModal = ref(false)
const modalMode = ref('details')
const selectedServicio = ref(null)
const selectedId = ref(null)

// Filtros/ordenamiento
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref(mapSortingToHeader(props.sorting))
const filtroEstado = ref('')

// Header
const headerConfig = {
    module: 'servicios',
    createButtonText: 'Nuevo Servicio',
    searchPlaceholder: 'Buscar por nombre, código o descripción...'
}

// ---------- Datos base ----------
// Usamos directamente los datos del paginator del backend
const serviciosPaginator = computed(() => props.servicios)
const serviciosData = computed(() => serviciosPaginator.value?.data || [])

// ---------- Estadísticas locales ----------
const estadisticas = computed(() => ({
    total: props.stats?.total ?? 0,
    activos: props.stats?.activos ?? 0,   // servicios activos
    inactivos: props.stats?.inactivos ?? 0 // servicios inactivos
}))

// ---------- Transformación base para DocumentosTable ----------
const serviciosDocumentosBase = computed(() => {
    return serviciosData.value.map(s => {
        const precio = Number(s.precio_venta ?? s.precio ?? 0)

        return {
            id: s.id,
            titulo: s.nombre || 'Sin nombre',
            subtitulo: s.descripcion || '',
            estado: s.estado || 'inactivo',
            codigo: s.codigo || 'N/A',
            precio_venta: precio,
            fecha: s.created_at,
            raw: s
        }
    })
})

// ---------- Documentos para mostrar ----------
const documentosServicios = computed(() => serviciosDocumentosBase.value)

// ---------- Paginación del lado del servidor ----------
const paginationData = computed(() => ({
    current_page: serviciosPaginator.value?.current_page || 1,
    last_page: serviciosPaginator.value?.last_page || 1,
    per_page: serviciosPaginator.value?.per_page || 10,
    from: serviciosPaginator.value?.from || 0,
    to: serviciosPaginator.value?.to || 0,
    total: serviciosPaginator.value?.total || 0,
    prev_page_url: serviciosPaginator.value?.prev_page_url,
    next_page_url: serviciosPaginator.value?.next_page_url,
    links: serviciosPaginator.value?.links || []
}))

// ---------- Paginación del lado del servidor ----------
const handlePerPageChange = (newPerPage) => {
    router.get(route('servicios.index'), {
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
    router.get(route('servicios.index'), {
        ...props.filters,
        ...props.sorting,
        page: newPage
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

// ---------- Handlers UniversalHeader ----------
function handleLimpiarFiltros () {
    searchTerm.value = ''
    sortBy.value = 'nombre-asc'
    filtroEstado.value = ''

    router.get(route('servicios.index'), {
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
    router.get(route('servicios.index'), {
        search: newSearch,
        sort_by: sortBy.value.split('-')[0],
        sort_direction: sortBy.value.split('-')[1] || 'asc',
        estado: filtroEstado.value,
        per_page: serviciosPaginator.value?.per_page || 10,
        page: 1 // Reset to first page
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

// Handler para filtro de estado
function handleEstadoChange(newEstado) {
    filtroEstado.value = newEstado
    router.get(route('servicios.index'), {
        search: searchTerm.value,
        sort_by: sortBy.value.split('-')[0],
        sort_direction: sortBy.value.split('-')[1] || 'asc',
        estado: newEstado,
        per_page: serviciosPaginator.value?.per_page || 10,
        page: 1 // Reset to first page
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

// Handler para ordenamiento
function handleSortChange(newSort) {
    sortBy.value = newSort
    router.get(route('servicios.index'), {
        search: searchTerm.value,
        sort_by: newSort.split('-')[0],
        sort_direction: newSort.split('-')[1] || 'asc',
        estado: filtroEstado.value,
        per_page: serviciosPaginator.value?.per_page || 10,
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

const exportServicios = () => {
    const params = new URLSearchParams()

    if (searchTerm.value) {
        params.append('search', searchTerm.value)
    }

    if (filtroEstado.value) {
        params.append('estado', filtroEstado.value)
    }

    const queryString = params.toString()
    const url = route('servicios.export') + (queryString ? `?${queryString}` : '')
    window.location.href = url
}

const verDetalles = (doc) => {
    if (!doc?.raw) return notyf.error('Servicio inválido')
    selectedServicio.value = doc.raw
    modalMode.value = 'details'
    showModal.value = true
}

const editarServicio = (id) => {
    if (!id) return notyf.error('ID inválido')
    router.visit(route('servicios.edit', id))
}

const confirmarEliminacion = (id) => {
    if (!id) return notyf.error('ID inválido')
    selectedId.value = id
    modalMode.value = 'confirm'
    showModal.value = true
}

const eliminarServicio = () => {
    if (!selectedId.value) return notyf.error('No hay servicio seleccionado')

    router.delete(route('servicios.destroy', selectedId.value), {
        preserveScroll: true,
        onSuccess: () => {
            notyf.success('Servicio eliminado')
            showModal.value = false
            selectedId.value = null
            // Recargar la página para actualizar la lista
            router.reload()
        },
        onError: (errors) => {
            console.error(errors)
            notyf.error('No se pudo eliminar el servicio')
        }
    })
}

function mapSortingToHeader(sorting) {
    const by = sorting?.sort_by ?? 'nombre'
    const dir = sorting?.sort_direction ?? 'asc'
    if (by === 'nombre') return `nombre-${dir}`
    if (by === 'created_at') return `fecha-${dir}`
    if (by === 'precio') return `precio-${dir}`
    if (by === 'duracion') return `duracion-${dir}`
    return 'nombre-asc'
}
</script>

<style scoped>
.servicios-index {
    min-height: 100vh;
    background-color: #f9fafb;
}
@media (max-width: 640px) {
    .servicios-index .max-w-8xl {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .servicios-index .grid-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.servicios-index > * { animation: fadeIn 0.3s ease-out; }

/* Animaciones para las tarjetas de estadísticas */
.servicios-index .grid > div {
    transition: all 0.2s ease-in-out;
}
.servicios-index .grid > div:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.15);
}
</style>
