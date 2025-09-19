<!-- /resources/js/Pages/Servicios/Index.vue -->
<template>
    <Head title="Servicios" />
    <div class="servicios-index min-h-screen bg-gray-50">
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

            <!-- Estadísticas detalladas -->
            <div class="mt-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Servicios</p>
                            <p class="text-2xl font-bold text-gray-900">{{ estadisticas.total }}</p>
                        </div>
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0V8a2 2 0 01-2 2H8a2 2 0 01-2-2V6m8 0H8"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Activos</p>
                            <p class="text-2xl font-bold text-green-600">{{ estadisticas.aprobadas }}</p>
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
                            <p class="text-sm font-medium text-gray-600">Precio Promedio</p>
                            <p class="text-2xl font-bold text-blue-600">${{ estadisticas.precioPromedio }}</p>
                        </div>
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Duración Promedio</p>
                            <p class="text-2xl font-bold text-purple-600">{{ estadisticas.duracionPromedio }} min</p>
                        </div>
                        <div class="p-2 bg-purple-50 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <DocumentosTable
                    :documentos="documentosServicios"
                    tipo="servicios"
                    :mapeo="{
                        nombre: 'titulo',
                        codigo: 'codigo',
                        precio: 'precio',
                        estado: 'estado',
                        fecha: 'fecha'
                    }"
                    :search-term="searchTerm"
                    :sort-by="sortBy"
                    :filtro-estado="filtroEstado"
                    @ver-detalles="verDetalles"
                    @editar="editarServicio"
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

// Paginación del lado del cliente
const currentPage = ref(1)
const perPage = ref(10)

// Header
const headerConfig = {
    module: 'servicios',
    createButtonText: 'Nuevo Servicio',
    searchPlaceholder: 'Buscar por nombre, código o descripción...'
}

// ---------- Datos base ----------
const serviciosOriginales = ref(
    Array.isArray(props.servicios?.data)
        ? [...props.servicios.data]
        : Array.isArray(props.servicios)
            ? [...props.servicios]
            : []
)

watch(() => props.servicios, (nuevo) => {
    serviciosOriginales.value =
        Array.isArray(nuevo?.data)
            ? [...nuevo.data]
            : Array.isArray(nuevo)
                ? [...nuevo]
                : []
}, { deep: true })

// ---------- Estadísticas ----------
const estadisticas = computed(() => {
    const data = serviciosOriginales.value
    const total = data.length
    const aprobadas = data.filter(s => s.estado === 'activo').length
    const pendientes = data.filter(s => s.estado === 'inactivo').length

    const precios = data.map(s => parseFloat(s.precio || 0)).filter(p => p > 0)
    const precioPromedio = precios.length > 0 ? (precios.reduce((a, b) => a + b, 0) / precios.length).toFixed(2) : '0'

    const duraciones = data.map(s => parseInt(s.duracion || 0)).filter(d => d > 0)
    const duracionPromedio = duraciones.length > 0 ? Math.round(duraciones.reduce((a, b) => a + b, 0) / duraciones.length) : 0

    return {
        total,
        aprobadas,
        pendientes,
        precioPromedio,
        duracionPromedio
    }
})

// ---------- Mapeo base para DocumentosTable ----------
const serviciosDocumentosBase = computed(() => {
    return serviciosOriginales.value.map(s => ({
        id: s.id,
        titulo: s.nombre || 'Sin nombre',
        subtitulo: s.descripcion || '',
        estado: s.estado || 'inactivo',
        codigo: s.codigo || 'N/A',
        precio: parseFloat(s.precio || 0),
        duracion: parseInt(s.duracion || 0),
        extra: `$${parseFloat(s.precio || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 })} • ${s.duracion || 0} min`,
        fecha: s.created_at,
        meta: {
            precio: parseFloat(s.precio || 0),
            duracion: parseInt(s.duracion || 0),
            descripcion: s.descripcion || ''
        },
        raw: s
    }))
})

// Servicios filtrados y ordenados (sin paginación)
const serviciosFiltradosYOrdenados = computed(() => {
    let arr = [...serviciosDocumentosBase.value]

    if (searchTerm.value) {
        const q = searchTerm.value.toLowerCase()
        arr = arr.filter(d =>
            d.titulo?.toLowerCase().includes(q) ||
            d.subtitulo?.toLowerCase().includes(q) ||
            d.raw?.descripcion?.toLowerCase().includes(q)
        )
    }

    if (filtroEstado.value) {
        arr = arr.filter(d => d.estado === filtroEstado.value)
    }

    const [campo, dir] = sortBy.value.split('-')
    arr.sort((a, b) => {
        let va = '', vb = ''
        if (campo === 'nombre') {
            va = (a.titulo || '').toLowerCase()
            vb = (b.titulo || '').toLowerCase()
        } else if (campo === 'fecha') {
            va = new Date(a.fecha || '').getTime()
            vb = new Date(b.fecha || '').getTime()
            return dir === 'asc' ? va - vb : vb - va
        } else if (campo === 'precio') {
            va = a.meta?.precio || 0
            vb = b.meta?.precio || 0
        } else if (campo === 'duracion') {
            va = a.meta?.duracion || 0
            vb = b.meta?.duracion || 0
        } else {
            va = (a.titulo || '').toLowerCase()
            vb = (b.titulo || '').toLowerCase()
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
const documentosServicios = computed(() => {
    const startIndex = (currentPage.value - 1) * perPage.value
    const endIndex = startIndex + perPage.value
    return serviciosFiltradosYOrdenados.value.slice(startIndex, endIndex)
})

// Información de paginación
const totalPages = computed(() => Math.ceil(serviciosFiltradosYOrdenados.value.length / perPage.value))
const totalFiltered = computed(() => serviciosFiltradosYOrdenados.value.length)

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

// ---------- Handlers ----------
function handleLimpiarFiltros() {
    searchTerm.value = ''
    sortBy.value = 'nombre-asc'
    filtroEstado.value = ''
    perPage.value = 10

    router.get(route('servicios.index'), {}, {
        preserveState: true,
        preserveScroll: true
    })

    notyf.success('Filtros limpiados')
}

const updateSort = (nuevo) => {
    if (nuevo && typeof nuevo === 'string') sortBy.value = nuevo
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
            const idx = serviciosOriginales.value.findIndex(s => s.id === selectedId.value)
            if (idx !== -1) serviciosOriginales.value.splice(idx, 1)

            showModal.value = false
            selectedId.value = null
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
