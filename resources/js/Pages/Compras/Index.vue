<!-- /resources/js/Pages/Compras/Index.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import { generarPDF } from '@/Utils/pdfGenerator'
import AppLayout from '@/Layouts/AppLayout.vue'
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue'
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue'
import Modales from '@/Components/IndexComponents/Modales.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  compras: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo
const searchTerm = ref('')
const sortBy = ref('fecha-desc')
const filtroEstado = ref('')
const showModal = ref(false)
const modalMode = ref('details')
const selectedId = ref(null)
const selectedCompra = ref(null)

// Configuración del header universal
const headerConfig = {
  module: 'compras',
  createButtonText: 'Nueva Compra',
  searchPlaceholder: 'Buscar por proveedor, producto...'
}

// Notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false }
  ]
})

// Datos originales
const comprasOriginales = ref([...props.compras])

watch(
  () => props.compras,
  (newVal) => {
    comprasOriginales.value = [...newVal]
  },
  { deep: true, immediate: true }
)

// Estadísticas calculadas
const estadisticas = computed(() => {
  const stats = { total: comprasOriginales.value.length, pagadas: 0, pendientes: 0 }
  comprasOriginales.value.forEach(c => {
    if (c.estado === 'pagada') stats.pagadas++
    else if (c.estado === 'pendiente') stats.pendientes++
  })
  return stats
})

// Métodos
const handleLimpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'fecha-desc'
  filtroEstado.value = ''
  notyf.success('Filtros limpiados correctamente')
}

const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') sortBy.value = newSort
}

const verDetalles = (compra) => {
  if (!compra) return notyf.error('Compra no válida')
  selectedCompra.value = compra
  modalMode.value = 'details'
  showModal.value = true
}

const editarCompra = (id) => {
  if (!id) return notyf.error('ID no válido')
  router.visit(`/compras/${id}/edit`)
}

const duplicarCompra = (compra) => {
  if (confirm(`¿Duplicar compra #${compra.id}?`)) {
    router.post(`/compras/${compra.id}/duplicate`, {}, {
      onSuccess: () => notyf.success('Compra duplicada'),
      onError: () => notyf.error('Error al duplicar')
    })
  }
}

const imprimirCompra = async (compra) => {
  if (!compra?.id) return notyf.error('ID no válido')
  if (!compra?.proveedor?.nombre_razon_social) return notyf.error('Proveedor no encontrado')
  if (!Array.isArray(compra.productos) || !compra.productos.length) return notyf.error('Lista de productos vacía')

  try {
    notyf.success('Generando PDF...')
    await generarPDF('Compra', compra)
    notyf.success('PDF generado correctamente')
  } catch (error) {
    console.error(error)
    notyf.error(`Error al generar PDF: ${error.message}`)
  }
}

const confirmarEliminacion = (id) => {
  if (!id) return notyf.error('ID no válido')
  selectedId.value = id
  modalMode.value = 'confirm'
  showModal.value = true
}

const eliminarCompra = async () => {
  if (!selectedId.value) return notyf.error('No hay compra seleccionada')

  try {
    router.delete(`/compras/${selectedId.value}`, {
      onSuccess: () => {
        notyf.success('Compra eliminada exitosamente')
        comprasOriginales.value = comprasOriginales.value.filter(c => c.id !== selectedId.value)
        showModal.value = false
        selectedId.value = null
      },
      onError: () => notyf.error('Error al eliminar')
    })
  } catch (error) {
    console.error(error)
    notyf.error('Error inesperado al eliminar')
  }
}

const crearNuevaCompra = () => {
  router.visit('/compras/create')
}
</script>

<template>
  <Head title="Compras" />

  <div class="compras-index min-h-screen bg-gray-50">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Compras</h1>
        <p class="text-gray-600">Administra y gestiona todas tus compras de manera eficiente</p>
      </div>
    </div>

    <!-- Contenido -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :aprobados="estadisticas.pagadas"
        :pendientes="estadisticas.pendientes"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Tabla -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="comprasOriginales"
          tipo="compras"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarCompra"
          @duplicar="duplicarCompra"
          @imprimir="imprimirCompra"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
        />
      </div>
    </div>

    <!-- Modales -->
    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedCompra"
      tipo="compras"
      @close="showModal = false"
      @confirm-delete="eliminarCompra"
      @imprimir="imprimirCompra"
      @editar="editarCompra"
    />
  </div>
</template>

<style scoped>
.compras-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media (max-width: 640px) {
  .compras-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .compras-index h1 {
    font-size: 1.5rem;
  }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.compras-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
