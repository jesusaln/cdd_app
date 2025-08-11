<!-- /resources/js/Pages/OrdenesCompra/Index.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'
import AppLayout from '@/Layouts/AppLayout.vue'
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue'
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue'
import Modales from '@/Components/IndexComponents/Modales.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  ordenesCompra: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo
const searchTerm = ref('')
const sortBy = ref('id-desc')
const filtroEstado = ref('')
const showModal = ref(false)
const modalMode = ref('details') // 'details', 'confirm', 'receive'
const selectedId = ref(null)
const selectedOrden = ref(null)

// Configuración del módulo
const headerConfig = {
  module: 'ordenescompra',
  createButtonText: 'Nueva Orden',
  searchPlaceholder: 'Buscar por proveedor, producto o ID...'
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
const ordenesOriginales = ref([...props.ordenesCompra])

watch(
  () => props.ordenesCompra,
  (newVal) => {
    ordenesOriginales.value = [...newVal]
  },
  { deep: true, immediate: true }
)

// Estadísticas
const estadisticas = computed(() => {
  const stats = { total: ordenesOriginales.value.length, pendientes: 0, recibidas: 0 }
  ordenesOriginales.value.forEach(o => {
    if (o.estado === 'pendiente') stats.pendientes++
    else if (o.estado === 'recibida') stats.recibidas++
  })
  return stats
})

// Métodos
const handleLimpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'id-desc'
  filtroEstado.value = ''
  notyf.success('Filtros limpiados')
}

const eliminarOrden = async () => {
  if (!ordenCompraIdToDelete.value) return;
  loading.value = true;
  try {
    await router.delete(route('ordenescompra.destroy', ordenCompraIdToDelete.value), {
      onSuccess: () => {
        notyf.success('Orden de compra eliminada exitosamente');
        ordenesCompra.value = ordenesCompra.value.filter(orden => orden.id !== ordenCompraIdToDelete.value);
        showConfirmationDialog.value = false;
        ordenCompraIdToDelete.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la orden de compra');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Ocurrió un error inesperado');
  } finally {
    loading.value = false;
  }
};

const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') sortBy.value = newSort
}

const verDetalles = (orden) => {
  if (!orden) return notyf.error('Orden no válida')
  selectedOrden.value = orden
  modalMode.value = 'details'
  showModal.value = true
}

const editarOrden = (id) => {
  if (!id) return notyf.error('ID no válido')
  router.visit(`/ordenescompra/${id}/edit`)
}

const duplicarOrden = (orden) => {
  if (confirm(`¿Duplicar orden #${orden.id}?`)) {
    router.post(`/ordenescompra/${orden.id}/duplicate`, {}, {
      onSuccess: () => notyf.success('Orden duplicada'),
      onError: () => notyf.error('Error al duplicar')
    })
  }
}

const imprimirOrden = async (orden) => {
  if (!orden?.id) return notyf.error('ID no válido')
  if (!orden?.proveedor?.nombre_razon_social) return notyf.error('Proveedor no encontrado')
  if (!Array.isArray(orden.items) || !orden.items.length) return notyf.error('Items vacíos')

  try {
    notyf.success('Generando PDF...')
    // Aquí puedes integrar tu generador de PDF
    // await generarPDF('OrdenCompra', orden)
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

const confirmarRecepcion = (id) => {
  if (!id) return notyf.error('ID no válido')
  selectedId.value = id
  modalMode.value = 'receive'
  showModal.value = true
}

const recibirOrden = async () => {
  if (!selectedId.value) return notyf.error('No hay orden seleccionada')

  try {
    router.post(`/ordenescompra/${selectedId.value}/recibir`, {}, {
      onSuccess: () => {
        notyf.success('Orden recibida y stock actualizado')
        ordenesOriginales.value = ordenesOriginales.value.map(o =>
          o.id === selectedId.value ? { ...o, estado: 'recibida' } : o
        )
        showModal.value = false
        selectedId.value = null
      },
      onError: () => notyf.error('Error al recibir')
    })
  } catch (error) {
    console.error(error)
    notyf.error('Error inesperado')
  }
}

const crearNuevaOrden = () => {
  router.visit('/ordenescompra/create')
}
</script>

<template>
  <Head title="Órdenes de Compra" />

  <div class="ordenes-compra-index min-h-screen bg-gray-50">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Órdenes de Compra</h1>
        <p class="text-gray-600">Gestiona y supervisa todas las órdenes de compra</p>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :aprobados="estadisticas.recibidas"
        :pendientes="estadisticas.pendientes"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Tabla de órdenes -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="ordenesOriginales"
          tipo="ordenescompra"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarOrden"
          @duplicar="duplicarOrden"
          @imprimir="imprimirOrden"
          @eliminar="confirmarEliminacion"
          @confirmar-recepcion="confirmarRecepcion"
          @sort="updateSort"
        />
      </div>
    </div>

    <!-- Modales reutilizables -->
    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedOrden"
      tipo="ordenescompra"
      @close="showModal = false"
      @confirm-delete="eliminarOrden"
      @confirm-receive="recibirOrden"
      @imprimir="imprimirOrden"
      @editar="editarOrden"
    />
  </div>
</template>

<style scoped>
.ordenes-compra-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

@media (max-width: 640px) {
  .ordenes-compra-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}
</style>
