<!-- /resources/js/Pages/Herramientas/IndexNew.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

defineOptions({ layout: AppLayout })

// Notificaciones
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

// Props
const props = defineProps({
  herramientas: { type: [Object, Array], required: true },
  tecnicos: { type: Array, default: () => [] },
  categorias: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  sorting: { type: Object, default: () => ({ sort_by: 'created_at', sort_direction: 'desc' }) },
})

// Estado UI
const showModal = ref(false)
const modalMode = ref('details')
const selectedHerramienta = ref(null)
const selectedId = ref(null)
const showImageModal = ref(false)
const selectedImage = ref('')

// Filtros
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref('created_at-desc')
const filtroEstado = ref('')

// Paginación
const perPage = ref(10)

// Header config
const headerConfig = {
  module: 'herramientas',
  createButtonText: 'Nueva Herramienta',
  searchPlaceholder: 'Buscar por nombre, número de serie, técnico...'
}

// Estados de herramientas
const estadosHerramientas = {
  'disponible': { label: 'Disponible', color: 'green', icon: 'check-circle' },
  'asignada': { label: 'Asignada', color: 'blue', icon: 'user' },
  'mantenimiento': { label: 'Mantenimiento', color: 'yellow', icon: 'wrench' },
  'baja': { label: 'De Baja', color: 'red', icon: 'x-circle' },
  'perdida': { label: 'Perdida', color: 'red', icon: 'exclamation-triangle' }
}

// Categorías de herramientas (dinámicas desde props)
const categoriasHerramientas = computed(() => {
  const categoriasMap = {}
  props.categorias.forEach(cat => {
    categoriasMap[cat.slug] = cat.nombre
  })
  return categoriasMap
})

// Datos
const herramientasPaginator = computed(() => props.herramientas)
const herramientasData = computed(() => herramientasPaginator.value?.data || [])

// Estadísticas
const estadisticas = computed(() => {
  const total = props.stats?.total ?? 0
  const asignadas = props.stats?.asignadas ?? 0
  const sinAsignar = props.stats?.sin_asignar ?? 0

  // Calcular estadísticas adicionales basadas en los datos de herramientas
  const herramientasConAtencion = herramientasData.value.filter(h => h.necesitaAtencion).length
  const herramientasMantenimiento = herramientasData.value.filter(h => h.alertaMantenimiento).length
  const herramientasEnMantenimiento = herramientasData.value.filter(h => h.estado === 'mantenimiento').length

  return {
    total,
    asignadas,
    sin_asignar: sinAsignar,
    asignadasPorcentaje: asignadas > 0 ? Math.round((asignadas / total) * 100) : 0,
    sinAsignarPorcentaje: sinAsignar > 0 ? Math.round((sinAsignar / total) * 100) : 0,
    necesitanAtencion: herramientasConAtencion,
    necesitanAtencionPorcentaje: herramientasConAtencion > 0 ? Math.round((herramientasConAtencion / total) * 100) : 0,
    mantenimientoProximo: herramientasMantenimiento,
    mantenimientoProximoPorcentaje: herramientasMantenimiento > 0 ? Math.round((herramientasMantenimiento / total) * 100) : 0,
    enMantenimiento: herramientasEnMantenimiento,
    enMantenimientoPorcentaje: herramientasEnMantenimiento > 0 ? Math.round((herramientasEnMantenimiento / total) * 100) : 0
  }
})

// Transformación de datos
const herramientasDocumentos = computed(() => {
  return herramientasData.value.map(h => {
    const estadoInfo = estadosHerramientas[h.estado] || estadosHerramientas['disponible']
    const categoriaLabel = h.categoriaHerramienta?.nombre || 'Sin Categoría'

    // Calcular indicadores de alerta
    const necesitaAtencion = h.requiere_mantenimiento ||
                            (h.ultimo_estado && h.ultimo_estado.porcentaje_desgaste > 70) ||
                            (h.estado === 'mantenimiento')

    const diasParaMantenimiento = h.dias_para_proximo_mantenimiento
    const alertaMantenimiento = diasParaMantenimiento !== null && diasParaMantenimiento <= 7

    return {
      id: h.id,
      titulo: h.nombre || 'Sin nombre',
      subtitulo: h.numero_serie || 'N/A',
      estado: h.estado,
      estadoLabel: estadoInfo.label,
      estadoColor: estadoInfo.color,
      categoria: categoriaLabel,
      tecnico: h.tecnico ? `${h.tecnico.nombre} ${h.tecnico.apellido}` : 'Sin asignar',
      fecha: h.created_at,
      necesitaAtencion,
      alertaMantenimiento,
      diasParaMantenimiento,
      porcentajeDesgaste: h.ultimo_estado?.porcentaje_desgaste || 0,
      prioridadMantenimiento: h.ultimo_estado?.prioridad_mantenimiento || 'baja',
      vidaUtilRestante: h.porcentaje_vida_util ? (100 - h.porcentaje_vida_util) : null,
      raw: h
    }
  })
})

// Handlers
function handleSearchChange(newSearch) {
  searchTerm.value = newSearch
  router.get(route('herramientas.index'), {
    search: newSearch,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    filtro_estado: filtroEstado.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleEstadoChange(newEstado) {
  filtroEstado.value = newEstado
  router.get(route('herramientas.index'), {
    search: searchTerm.value,
    sort_by: sortBy.value.split('-')[0],
    sort_direction: sortBy.value.split('-')[1] || 'desc',
    filtro_estado: newEstado,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

function handleSortChange(newSort) {
  sortBy.value = newSort
  router.get(route('herramientas.index'), {
    search: searchTerm.value,
    sort_by: newSort.split('-')[0],
    sort_direction: newSort.split('-')[1] || 'desc',
    filtro_estado: filtroEstado.value,
    per_page: perPage.value,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const verDetalles = (doc) => {
  selectedHerramienta.value = doc.raw
  modalMode.value = 'details'
  showModal.value = true
}

const editarHerramienta = (id) => {
  router.visit(route('herramientas.edit', id))
}

const confirmarEliminacion = (id) => {
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarHerramienta = () => {
  router.delete(route('herramientas.destroy', selectedId.value), {
    preserveScroll: true,
    onSuccess: () => {
      notyf.success('Herramienta eliminada correctamente')
      showModal.value = false
      selectedId.value = null
      router.reload()
    },
    onError: (errors) => {
      notyf.error('No se pudo eliminar la herramienta')
    }
  })
}

const exportHerramientas = () => {
  const params = new URLSearchParams()
  if (searchTerm.value) params.append('search', searchTerm.value)
  if (filtroEstado.value) params.append('filtro_estado', filtroEstado.value)
  const queryString = params.toString()
  const url = route('herramientas.export') + (queryString ? `?${queryString}` : '')
  window.location.href = url
}

const handleOpenImageModal = (imageUrl) => {
  selectedImage.value = imageUrl
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  selectedImage.value = ''
}

// Estado para modal de asignación
const showAsignarModal = ref(false)
const herramientaAAsignar = ref(null)
const tecnicos = ref([])
const tecnicoSeleccionado = ref('')

// Cargar técnicos disponibles
const cargarTecnicos = async () => {
  try {
    // Usar técnicos desde props si existen
    if (props.tecnicos && props.tecnicos.length > 0) {
      tecnicos.value = props.tecnicos
      console.log('Técnicos cargados desde props:', tecnicos.value)
      return
    }

    // Si no hay props, intentar cargar desde API
    const response = await fetch(route('api.tecnicos.index'), {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    tecnicos.value = data.data || []
    console.log('Técnicos cargados desde API:', tecnicos.value)

    // Si no hay técnicos, mostrar mensaje
    if (tecnicos.value.length === 0) {
      notyf.warning('No hay técnicos disponibles para asignación')
    }
  } catch (error) {
    console.error('Error al cargar técnicos:', error)
    notyf.error('Error al cargar la lista de técnicos')
    tecnicos.value = []
  }
}

// Funciones para asignaciones
const asignarHerramienta = (herramienta) => {
  herramientaAAsignar.value = herramienta
  showAsignarModal.value = true
  // Cargar técnicos después de mostrar el modal
  setTimeout(() => {
    cargarTecnicos()
  }, 100)
}

const confirmarAsignacion = () => {
  if (!tecnicoSeleccionado.value) {
    notyf.error('Por favor selecciona un técnico')
    return
  }

  router.post(route('herramientas.asignar', herramientaAAsignar.value.id), {
    tecnico_id: tecnicoSeleccionado.value,
    observaciones: 'Asignación desde listado'
  }, {
    onSuccess: () => {
      notyf.success('Herramienta asignada correctamente')
      showAsignarModal.value = false
      herramientaAAsignar.value = null
      router.reload()
    },
    onError: (errors) => {
      notyf.error('Error al asignar la herramienta: ' + Object.values(errors)[0])
    }
  })
}

const recibirHerramienta = (herramienta) => {
  if (confirm('¿Está seguro de que desea recibir esta herramienta?')) {
    router.post(route('herramientas.recibir', herramienta.id), {
      observaciones: 'Recepción desde listado'
    }, {
      onSuccess: () => {
        notyf.success('Herramienta recibida correctamente')
        router.reload()
      },
      onError: (errors) => {
        notyf.error('Error al recibir la herramienta: ' + Object.values(errors)[0])
      }
    })
  }
}

const verAsignaciones = (herramienta) => {
  router.visit(route('herramientas.asignaciones.index', { search: herramienta.numero_serie }))
}

// Funciones para estados
const inspeccionarHerramienta = (herramienta) => {
  router.visit(route('herramientas.estados.create', { herramienta_id: herramienta.id }))
}

const verEstados = (herramienta) => {
  router.visit(route('herramientas.estados.index', { search: herramienta.numero_serie }))
}

// Funciones para historial
const verHistorial = (herramienta) => {
  // Por ahora redirigir a asignaciones que incluyen el historial
  router.visit(route('herramientas.asignaciones.index', { search: herramienta.numero_serie }))
}

// Funciones para modal
const asignarHerramientaModal = (herramienta) => {
  herramientaAAsignar.value = herramienta
  showAsignarModal.value = true
  showModal.value = false // Cerrar el modal de detalles
  // Cargar técnicos después de mostrar el modal
  setTimeout(() => {
    cargarTecnicos()
  }, 100)
}

const recibirHerramientaModal = (herramienta) => {
  if (confirm('¿Está seguro de que desea recibir esta herramienta?')) {
    router.post(route('herramientas.recibir', herramienta.id), {
      observaciones: 'Recepción desde modal de detalles'
    }, {
      onSuccess: () => {
        notyf.success('Herramienta recibida correctamente')
        showModal.value = false
        router.reload()
      },
      onError: (errors) => {
        notyf.error('Error al recibir la herramienta: ' + Object.values(errors)[0])
      }
    })
  }
}

const inspeccionarHerramientaModal = (herramienta) => {
  router.visit(route('herramientas.estados.create', { herramienta_id: herramienta.id }))
  showModal.value = false
}

const verHistorialModal = (herramienta) => {
  router.visit(route('herramientas.asignaciones.index', { search: herramienta.numero_serie }))
  showModal.value = false
}

const handleTecnicoChange = (event) => {
  tecnicoSeleccionado.value = event.target.value
}

// Funciones auxiliares para estados y prioridades
const getEstadoClasses = (estado) => {
  const estadoInfo = estadosHerramientas[estado] || estadosHerramientas['disponible']
  return `bg-${estadoInfo.color}-100 text-${estadoInfo.color}-700`
}

const getPrioridadClasses = (prioridad) => {
  const clases = {
    'baja': 'bg-green-100 text-green-700',
    'media': 'bg-yellow-100 text-yellow-700',
    'alta': 'bg-orange-100 text-orange-700',
    'urgente': 'bg-red-100 text-red-700'
  }
  return clases[prioridad] || 'bg-gray-100 text-gray-700'
}

const getPrioridadLabel = (prioridad) => {
  const labels = {
    'baja': 'Baja',
    'media': 'Media',
    'alta': 'Alta',
    'urgente': 'Urgente'
  }
  return labels[prioridad] || 'Sin prioridad'
}

const formatPorcentaje = (porcentaje) => {
  return porcentaje ? `${porcentaje}%` : 'N/A'
}

const formatDias = (dias) => {
  if (dias === null) return 'N/A'
  if (dias <= 0) return 'Vencido'
  return `${dias} días`
}

// Paginación
const paginationData = computed(() => ({
  current_page: herramientasPaginator.value?.current_page || 1,
  last_page: herramientasPaginator.value?.last_page || 1,
  per_page: herramientasPaginator.value?.per_page || 10,
  from: herramientasPaginator.value?.from || 0,
  to: herramientasPaginator.value?.to || 0,
  total: herramientasPaginator.value?.total || 0,
  prev_page_url: herramientasPaginator.value?.prev_page_url,
  next_page_url: herramientasPaginator.value?.next_page_url,
  links: herramientasPaginator.value?.links || []
}))

const handlePerPageChange = (newPerPage) => {
  router.get(route('herramientas.index'), {
    ...props.filters,
    ...props.sorting,
    per_page: newPerPage,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const handlePageChange = (newPage) => {
  router.get(route('herramientas.index'), {
    ...props.filters,
    ...props.sorting,
    page: newPage
  }, { preserveState: true, preserveScroll: true })
}

// Helpers
const formatNumber = (num) => new Intl.NumberFormat('es-ES').format(num)
const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const d = new Date(date)
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
  } catch {
    return 'Fecha inválida'
  }
}

const obtenerClasesEstado = (estado) => {
  const clases = {
    'asignada': 'bg-green-100 text-green-700',
    'sin_asignar': 'bg-gray-100 text-gray-700'
  }
  return clases[estado] || 'bg-gray-100 text-gray-700'
}

const obtenerLabelEstado = (estado) => {
  const labels = {
    'asignada': 'Asignada',
    'sin_asignar': 'Sin Asignar'
  }
  return labels[estado] || 'Pendiente'
}
</script>

<template>
  <Head title="Herramientas" />
  <div class="herramientas-index min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
          <!-- Izquierda -->
          <div class="flex flex-col gap-6 w-full lg:w-auto">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-900">Herramientas</h1>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <Link
                :href="route('herramientas.create')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ headerConfig.createButtonText }}</span>
              </Link>

              <Link
                :href="route('herramientas.asignaciones.index')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-purple-50 text-purple-700 rounded-xl hover:bg-purple-100 transition-all duration-200 border border-purple-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-sm font-medium">Asignaciones</span>
              </Link>

              <Link
                :href="route('herramientas.asignaciones-masivas.index')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 transition-all duration-200 border border-indigo-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-sm font-medium">Asign. Masivas</span>
              </Link>

              <Link
                :href="route('herramientas.tecnicos-herramientas.index')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-teal-50 text-teal-700 rounded-xl hover:bg-teal-100 transition-all duration-200 border border-teal-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="text-sm font-medium">Por Técnico</span>
              </Link>

              <Link
                :href="route('herramientas.estados.index')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-yellow-50 text-yellow-700 rounded-xl hover:bg-yellow-100 transition-all duration-200 border border-yellow-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm font-medium">Estados</span>
              </Link>

              <Link
                :href="route('herramientas.estados.reporte-atencion')"
                class="inline-flex items-center gap-2 px-4 py-3 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-all duration-200 border border-red-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="text-sm font-medium">Alertas</span>
              </Link>

              <button
                @click="exportHerramientas"
                class="inline-flex items-center gap-2 px-4 py-3 bg-green-50 text-green-700 rounded-xl hover:bg-green-100 transition-all duration-200 border border-green-200"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium">Exportar</span>
              </button>
            </div>

            <!-- Estadísticas con barras de progreso -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 rounded-xl border border-slate-200">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium text-slate-700">Total:</span>
                <span class="font-bold text-slate-900 text-lg">{{ formatNumber(estadisticas.total) }}</span>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-green-50 rounded-xl border border-green-200">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-slate-700">Asignadas:</span>
                <span class="font-bold text-green-700 text-lg">{{ formatNumber(estadisticas.asignadas) }}</span>
                <div class="ml-2 flex items-center gap-2">
                  <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-green-500 transition-all duration-300"
                      :style="{ width: estadisticas.asignadasPorcentaje + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-green-600 font-medium">{{ estadisticas.asignadasPorcentaje }}%</span>
                </div>
              </div>

              <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 rounded-xl border border-gray-200">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="font-medium text-slate-700">Disponibles:</span>
                <span class="font-bold text-gray-700 text-lg">{{ formatNumber(estadisticas.sin_asignar) }}</span>
                <div class="ml-2 flex items-center gap-2">
                  <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-gray-500 transition-all duration-300"
                      :style="{ width: estadisticas.sinAsignarPorcentaje + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-gray-600 font-medium">{{ estadisticas.sinAsignarPorcentaje }}%</span>
                </div>
              </div>

              <div v-if="estadisticas.necesitanAtencion > 0" class="flex items-center gap-2 px-4 py-3 bg-red-50 rounded-xl border border-red-200">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="font-medium text-slate-700">Necesitan Atención:</span>
                <span class="font-bold text-red-700 text-lg">{{ formatNumber(estadisticas.necesitanAtencion) }}</span>
                <div class="ml-2 flex items-center gap-2">
                  <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-red-500 transition-all duration-300"
                      :style="{ width: estadisticas.necesitanAtencionPorcentaje + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-red-600 font-medium">{{ estadisticas.necesitanAtencionPorcentaje }}%</span>
                </div>
              </div>

              <div v-if="estadisticas.mantenimientoProximo > 0" class="flex items-center gap-2 px-4 py-3 bg-yellow-50 rounded-xl border border-yellow-200">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-medium text-slate-700">Mant. Próximo:</span>
                <span class="font-bold text-yellow-700 text-lg">{{ formatNumber(estadisticas.mantenimientoProximo) }}</span>
                <div class="ml-2 flex items-center gap-2">
                  <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-yellow-500 transition-all duration-300"
                      :style="{ width: estadisticas.mantenimientoProximoPorcentaje + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-yellow-600 font-medium">{{ estadisticas.mantenimientoProximoPorcentaje }}%</span>
                </div>
              </div>

              <div v-if="estadisticas.enMantenimiento > 0" class="flex items-center gap-2 px-4 py-3 bg-orange-50 rounded-xl border border-orange-200">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-medium text-slate-700">En Mant.:</span>
                <span class="font-bold text-orange-700 text-lg">{{ formatNumber(estadisticas.enMantenimiento) }}</span>
                <div class="ml-2 flex items-center gap-2">
                  <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-orange-500 transition-all duration-300"
                      :style="{ width: estadisticas.enMantenimientoPorcentaje + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-orange-600 font-medium">{{ estadisticas.enMantenimientoPorcentaje }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Derecha: Filtros -->
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto lg:flex-shrink-0">
            <!-- Búsqueda -->
            <div class="relative">
              <input
                v-model="searchTerm"
                @input="handleSearchChange($event.target.value)"
                type="text"
                :placeholder="headerConfig.searchPlaceholder"
                class="w-full sm:w-64 lg:w-80 pl-4 pr-10 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
              />
              <svg class="absolute right-3 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Estado -->
            <select
              v-model="filtroEstado"
              @change="handleEstadoChange($event.target.value)"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="">Todos los Estados</option>
              <option value="asignada">Asignadas</option>
              <option value="sin_asignar">Sin Asignar</option>
            </select>

            <!-- Orden -->
            <select
              v-model="sortBy"
              @change="handleSortChange($event.target.value)"
              class="px-4 py-3 border border-slate-300 rounded-xl bg-white text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            >
              <option value="created_at-desc">Más Recientes</option>
              <option value="created_at-asc">Más Antiguos</option>
              <option value="nombre-asc">Nombre A-Z</option>
              <option value="nombre-desc">Nombre Z-A</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Herramienta</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoría</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Condición</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Asignación</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="herramienta in herramientasDocumentos" :key="herramienta.id" class="hover:bg-gray-50 transition-colors duration-150" :class="{ 'bg-red-50': herramienta.necesitaAtencion, 'bg-yellow-50': herramienta.alertaMantenimiento }">
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatearFecha(herramienta.fecha) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div v-if="herramienta.necesitaAtencion" class="w-2 h-2 bg-red-500 rounded-full animate-pulse" title="Necesita atención"></div>
                    <div v-else-if="herramienta.alertaMantenimiento" class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse" title="Mantenimiento próximo"></div>
                    <div class="flex flex-col">
                      <div class="text-sm font-medium text-gray-900">{{ herramienta.titulo }}</div>
                      <div class="text-xs text-gray-500">{{ herramienta.subtitulo }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                    {{ herramienta.categoria }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="getEstadoClasses(herramienta.estado)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ herramienta.estadoLabel }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span class="text-xs text-gray-600">Desgaste:</span>
                      <span class="text-xs font-medium" :class="herramienta.porcentajeDesgaste > 70 ? 'text-red-600' : herramienta.porcentajeDesgaste > 40 ? 'text-yellow-600' : 'text-green-600'">
                        {{ formatPorcentaje(herramienta.porcentajeDesgaste) }}
                      </span>
                    </div>
                    <div v-if="herramienta.prioridadMantenimiento !== 'baja'" class="flex items-center gap-2">
                      <span class="text-xs text-gray-600">Prioridad:</span>
                      <span :class="getPrioridadClasses(herramienta.prioridadMantenimiento)" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium">
                        {{ getPrioridadLabel(herramienta.prioridadMantenimiento) }}
                      </span>
                    </div>
                    <div v-if="herramienta.diasParaMantenimiento !== null" class="flex items-center gap-2">
                      <span class="text-xs text-gray-600">Mantenimiento:</span>
                      <span class="text-xs font-medium" :class="herramienta.diasParaMantenimiento <= 0 ? 'text-red-600' : herramienta.diasParaMantenimiento <= 7 ? 'text-yellow-600' : 'text-green-600'">
                        {{ formatDias(herramienta.diasParaMantenimiento) }}
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700">
                    <div v-if="herramienta.estado === 'asignada'" class="font-medium text-blue-700">
                      {{ herramienta.tecnico }}
                    </div>
                    <div v-else class="text-gray-500">
                      {{ herramienta.estadoLabel }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end space-x-1">
                    <!-- Foto -->
                    <button v-if="herramienta.raw.foto" @click="handleOpenImageModal('/storage/' + herramienta.raw.foto)" class="w-8 h-8 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors duration-150" :title="'Ver foto: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </button>

                    <!-- Asignar/Recibir -->
                    <button v-if="herramienta.estado === 'disponible'" @click="asignarHerramienta(herramienta)" class="w-8 h-8 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors duration-150" :title="'Asignar: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </button>
                    <button v-if="herramienta.estado === 'asignada'" @click="recibirHerramienta(herramienta)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" :title="'Recibir: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>

                    <!-- Inspeccionar -->
                    <button @click="inspeccionarHerramienta(herramienta)" class="w-8 h-8 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition-colors duration-150" :title="'Inspeccionar: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>

                    <!-- Historial -->
                    <button @click="verHistorial(herramienta)" class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors duration-150" :title="'Historial: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </button>

                    <!-- Detalles -->
                    <button @click="verDetalles(herramienta)" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150" :title="'Detalles: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>

                    <!-- Editar -->
                    <button @click="editarHerramienta(herramienta.id)" class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors duration-150" :title="'Editar: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>

                    <!-- Eliminar -->
                    <button @click="confirmarEliminacion(herramienta.id)" class="w-8 h-8 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150" :title="'Eliminar: ' + herramienta.titulo">
                      <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="herramientasDocumentos.length === 0">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-gray-700 font-medium">No hay herramientas</p>
                      <p class="text-sm text-gray-500">Las herramientas aparecerán aquí cuando se creen</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="paginationData.lastPage > 1" class="bg-white border-t border-gray-200 px-4 py-3 sm:px-6">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <p class="text-sm text-gray-700">
                Mostrando {{ paginationData.from }} - {{ paginationData.to }} de {{ paginationData.total }} resultados
              </p>
              <select
                :value="paginationData.perPage"
                @change="handlePerPageChange(parseInt($event.target.value))"
                class="border border-gray-300 rounded-md text-sm py-1 px-2 bg-white"
              >
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
            </div>

            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                v-if="paginationData.prevPageUrl"
                @click="handlePageChange(paginationData.currentPage - 1)"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>

              <span v-else class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </span>

              <button
                v-for="page in [paginationData.currentPage - 1, paginationData.currentPage, paginationData.currentPage + 1].filter(p => p > 0 && p <= paginationData.lastPage)"
                :key="page"
                @click="handlePageChange(page)"
                :class="page === paginationData.currentPage ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              >
                {{ page }}
              </button>

              <button
                v-if="paginationData.nextPageUrl"
                @click="handlePageChange(paginationData.currentPage + 1)"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </button>

              <span v-else class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </span>
            </nav>
          </div>
        </div>
      </div>

      <!-- Modal mejorado -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <!-- Header del modal -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              {{ modalMode === 'details' ? 'Detalles de la Herramienta' : 'Confirmar Eliminación' }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="modalMode === 'details' && selectedHerramienta" class="space-y-6">
              <div class="space-y-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Nombre</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedHerramienta.nombre || 'Sin nombre' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Número de Serie</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedHerramienta.numero_serie || 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Categoría</label>
                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 mt-1">
                        {{ selectedHerramienta.categoriaHerramienta?.nombre || 'Sin categoría' }}
                      </span>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Estado</label>
                      <span :class="getEstadoClasses(selectedHerramienta.estado)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1">
                        {{ selectedHerramienta.estadoLabel || 'Sin estado' }}
                      </span>
                    </div>
                  </div>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Técnico Asignado</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ selectedHerramienta.tecnico || 'Sin asignar' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha de creación</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedHerramienta.created_at) }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Última actualización</label>
                      <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ formatearFecha(selectedHerramienta.updated_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Información de mantenimiento y desgaste -->
                <div class="border-t pt-4">
                  <h4 class="text-md font-medium text-gray-900 mb-3">Estado y Mantenimiento</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Porcentaje de Desgaste</label>
                        <div class="mt-1 flex items-center gap-2">
                          <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                              class="h-full bg-gradient-to-r from-green-500 to-red-500 transition-all duration-300"
                              :style="{ width: (selectedHerramienta.porcentajeDesgaste || 0) + '%' }"
                            ></div>
                          </div>
                          <span class="text-sm font-medium" :class="selectedHerramienta.porcentajeDesgaste > 70 ? 'text-red-600' : selectedHerramienta.porcentajeDesgaste > 40 ? 'text-yellow-600' : 'text-green-600'">
                            {{ formatPorcentaje(selectedHerramienta.porcentajeDesgaste) }}
                          </span>
                        </div>
                      </div>
                      <div v-if="selectedHerramienta.prioridadMantenimiento !== 'baja'">
                        <label class="block text-sm font-medium text-gray-700">Prioridad de Mantenimiento</label>
                        <span :class="getPrioridadClasses(selectedHerramienta.prioridadMantenimiento)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1">
                          {{ getPrioridadLabel(selectedHerramienta.prioridadMantenimiento) }}
                        </span>
                      </div>
                      <div v-if="selectedHerramienta.diasParaMantenimiento !== null">
                        <label class="block text-sm font-medium text-gray-700">Próximo Mantenimiento</label>
                        <p class="mt-1 text-sm bg-gray-50 px-3 py-2 rounded-md" :class="selectedHerramienta.diasParaMantenimiento <= 0 ? 'text-red-600' : selectedHerramienta.diasParaMantenimiento <= 7 ? 'text-yellow-600' : 'text-green-600'">
                          {{ formatDias(selectedHerramienta.diasParaMantenimiento) }}
                        </p>
                      </div>
                    </div>
                    <div class="space-y-3">
                      <div v-if="selectedHerramienta.raw && selectedHerramienta.raw.vida_util_meses">
                        <label class="block text-sm font-medium text-gray-700">Vida Útil</label>
                        <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                          {{ selectedHerramienta.raw.vida_util_meses }} meses
                          <span v-if="selectedHerramienta.vidaUtilRestante !== null" class="text-xs text-gray-500">
                            ({{ selectedHerramienta.vidaUtilRestante }}% restante)
                          </span>
                        </p>
                      </div>
                      <div v-if="selectedHerramienta.raw && selectedHerramienta.raw.costo_reemplazo">
                        <label class="block text-sm font-medium text-gray-700">Costo de Reemplazo</label>
                        <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                          ${{ formatNumber(selectedHerramienta.raw.costo_reemplazo) }}
                        </p>
                      </div>
                      <div v-if="selectedHerramienta.raw && selectedHerramienta.raw.fecha_ultimo_mantenimiento">
                        <label class="block text-sm font-medium text-gray-700">Último Mantenimiento</label>
                        <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                          {{ formatearFecha(selectedHerramienta.raw.fecha_ultimo_mantenimiento) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Descripción -->
                <div v-if="selectedHerramienta.raw && selectedHerramienta.raw.descripcion">
                  <label class="block text-sm font-medium text-gray-700">Descripción</label>
                  <p class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md whitespace-pre-wrap">{{ selectedHerramienta.raw.descripcion }}</p>
                </div>

                <!-- Foto -->
                <div v-if="selectedHerramienta.raw && selectedHerramienta.raw.foto">
                  <label class="block text-sm font-medium text-gray-700">Foto</label>
                  <img :src="'/storage/' + selectedHerramienta.raw.foto" alt="Foto de la herramienta" class="mt-2 max-w-full h-auto rounded-md cursor-pointer" @click="handleOpenImageModal('/storage/' + selectedHerramienta.raw.foto)" />
                </div>

                <!-- Alertas -->
                <div v-if="selectedHerramienta.necesitaAtencion || selectedHerramienta.alertaMantenimiento" class="border-t pt-4">
                  <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Alertas de Mantenimiento</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                          <ul class="list-disc list-inside space-y-1">
                            <li v-if="selectedHerramienta.necesitaAtencion">La herramienta requiere atención inmediata</li>
                            <li v-if="selectedHerramienta.alertaMantenimiento">Mantenimiento próximo o vencido</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="modalMode === 'confirm'">
              <div class="text-center">
                <div class="w-12 h-12 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                  <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">¿Eliminar Herramienta?</h3>
                <p class="text-sm text-gray-500 mb-4">
                  ¿Estás seguro de que deseas eliminar la herramienta <strong>{{ selectedHerramienta?.nombre }}</strong>?
                  Esta acción no se puede deshacer.
                </p>
              </div>
            </div>
          </div>

          <!-- Footer del modal -->
          <div class="flex justify-between gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex gap-2">
              <button v-if="modalMode === 'details' && selectedHerramienta && selectedHerramienta.estado === 'disponible'" @click="asignarHerramientaModal(selectedHerramienta)" class="px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">
                Asignar
              </button>
              <button v-if="modalMode === 'details' && selectedHerramienta && selectedHerramienta.estado === 'asignada'" @click="recibirHerramientaModal(selectedHerramienta)" class="px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                Recibir
              </button>
              <button v-if="modalMode === 'details' && selectedHerramienta" @click="inspeccionarHerramientaModal(selectedHerramienta)" class="px-3 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors">
                Inspeccionar
              </button>
              <button v-if="modalMode === 'details' && selectedHerramienta" @click="verHistorialModal(selectedHerramienta)" class="px-3 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition-colors">
                Historial
              </button>
            </div>
            <div class="flex gap-2">
              <button @click="showModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                {{ modalMode === 'details' ? 'Cerrar' : 'Cancelar' }}
              </button>
              <button v-if="modalMode === 'details' && selectedHerramienta" @click="editarHerramienta(selectedHerramienta.id)" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                Editar
              </button>
              <button v-if="modalMode === 'confirm'" @click="eliminarHerramienta" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal para imagen ampliada -->
      <div v-if="showImageModal" class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4" @click.self="closeImageModal">
        <div class="bg-white p-4 rounded-lg max-w-4xl max-h-[90vh] overflow-auto relative">
          <img :src="selectedImage" alt="Imagen ampliada" class="max-w-full h-auto rounded-lg" />
          <button @click="closeImageModal" class="absolute top-2 right-2 text-white bg-black/50 rounded-full p-2 hover:bg-black/70 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Modal para asignar herramienta -->
      <div v-if="showAsignarModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showAsignarModal = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
          <!-- Header del modal -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-medium text-gray-900">
                  Asignar Herramienta
                </h3>
                <p class="text-sm text-gray-500">
                  Selecciona el técnico que recibirá la herramienta
                </p>
              </div>
            </div>
            <button @click="showAsignarModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div v-if="herramientaAAsignar" class="space-y-6">
              <!-- Información de la herramienta -->
              <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-start space-x-3">
                  <div v-if="herramientaAAsignar.raw && herramientaAAsignar.raw.foto" class="flex-shrink-0">
                    <img :src="'/storage/' + herramientaAAsignar.raw.foto" alt="Foto herramienta" class="w-12 h-12 rounded-lg object-cover border border-gray-300" />
                  </div>
                  <div v-else class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="text-lg font-semibold text-gray-900">
                      {{ herramientaAAsignar.titulo || herramientaAAsignar.nombre }}
                    </h4>
                    <p class="text-sm text-gray-600 mt-1">
                      {{ herramientaAAsignar.subtitulo }}
                    </p>
                    <div class="flex items-center space-x-2 mt-2">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        {{ herramientaAAsignar.categoriaHerramienta?.nombre || herramientaAAsignar.categoria }}
                      </span>
                      <span :class="herramientaAAsignar.estadoColor === 'green' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                        {{ herramientaAAsignar.estadoLabel }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Selector de técnico -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Técnico Asignado
                  </span>
                </label>
                <div class="relative">
                  <select
                    id="tecnico-select"
                    @change="handleTecnicoChange"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white text-gray-900"
                  >
                    <option value="">Selecciona un técnico...</option>
                    <option
                      v-for="tecnico in tecnicos"
                      :key="tecnico.id"
                      :value="tecnico.id"
                      class="text-gray-900"
                    >
                      {{ tecnico.nombre }} {{ tecnico.apellido }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                  El técnico seleccionado recibirá la herramienta y será responsable de su cuidado
                </p>
              </div>

              <!-- Debug info (solo para desarrollo) -->
              <div v-if="false" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-xs text-yellow-800">
                  <strong>Debug:</strong><br>
                  Herramienta: {{ herramientaAAsignar?.titulo }}<br>
                  Técnicos cargados: {{ tecnicos.length }}<br>
                  Técnico seleccionado: {{ tecnicoSeleccionado }}
                </p>
              </div>

              <!-- Información adicional -->
              <div v-if="tecnicos.length === 0" class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </div>
                <p class="text-gray-500 text-sm">Cargando lista de técnicos...</p>
                <p class="text-xs text-gray-400 mt-2">Esto puede tomar unos segundos</p>
              </div>

              <!-- Mensaje si no hay técnicos -->
              <div v-else-if="tecnicos.length > 0 && !tecnicoSeleccionado" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="text-sm text-blue-800">
                    <strong>{{ tecnicos.length }}</strong> técnico{{ tecnicos.length > 1 ? 's' : '' }} disponible{{ tecnicos.length > 1 ? 's' : '' }} para asignación
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer del modal -->
          <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button
              @click="showAsignarModal = false"
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              @click="confirmarAsignacion"
              :disabled="!tecnicos.length"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Asignar Herramienta
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.herramientas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
