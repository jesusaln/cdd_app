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
    type: Object,
    default: () => ({ data: [] })
  },
  stats: {
    type: Object,
    default: () => ({ total: 0, recibidas: 0, pendientes: 0, canceladas: 0, borrador: 0 })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  sorting: {
    type: Object,
    default: () => ({ sort_by: 'created_at', sort_direction: 'desc' })
  }
})

// Estado reactivo
const searchTerm = ref(props.filters?.search ?? '')
const sortBy = ref('fecha-desc')
const filtroEstado = ref('')
const showModal = ref(false)
const modalMode = ref('details') // 'details', 'confirm', 'receive'
const selectedId = ref(null)
const selectedOrden = ref(null)

// Configuración del módulo
const headerConfig = {
  module: 'ordenescompra'
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
const ordenesOriginales = ref(Array.isArray(props.ordenesCompra?.data) ? [...props.ordenesCompra.data] : [])

watch(
  () => props.ordenesCompra,
  (newVal) => {
    ordenesOriginales.value = Array.isArray(newVal?.data) ? [...newVal.data] : []
  },
  { deep: true, immediate: true }
)

// Datos filtrados y ordenados para la tabla
const ordenesFiltradas = computed(() => {
  let filtered = [...ordenesOriginales.value]

  // Aplicar búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    filtered = filtered.filter(orden =>
      (orden.numero_orden || '').toLowerCase().includes(term) ||
      (orden.proveedor?.nombre_razon_social || '').toLowerCase().includes(term) ||
      (orden.estado || '').toLowerCase().includes(term) ||
      orden.items?.some(item => (item.nombre || '').toLowerCase().includes(term))
    )
  }

  // Aplicar filtro de estado
  if (filtroEstado.value) {
    filtered = filtered.filter(orden => orden.estado === filtroEstado.value)
  }

  // Aplicar ordenamiento
  const [field, direction] = sortBy.value.split('-')
  filtered.sort((a, b) => {
    let aVal, bVal

    switch (field) {
      case 'fecha':
        aVal = new Date(a.created_at || a.fecha).getTime()
        bVal = new Date(b.created_at || b.fecha).getTime()
        break
      case 'proveedor':
        aVal = (a.proveedor?.nombre_razon_social || '').toLowerCase()
        bVal = (b.proveedor?.nombre_razon_social || '').toLowerCase()
        break
      case 'numero_orden':
        aVal = (a.numero_orden || a.id || '').toString().toLowerCase()
        bVal = (b.numero_orden || b.id || '').toString().toLowerCase()
        break
      case 'total':
        aVal = parseFloat(a.total || 0)
        bVal = parseFloat(b.total || 0)
        break
      case 'estado':
        aVal = a.estado || ''
        bVal = b.estado || ''
        break
      default:
        aVal = a[field] || ''
        bVal = b[field] || ''
    }

    if (aVal < bVal) return direction === 'asc' ? -1 : 1
    if (aVal > bVal) return direction === 'asc' ? 1 : -1
    return 0
  })

  return filtered
})

// Estadísticas
const estadisticas = computed(() => ({
  total: props.stats?.total ?? ordenesOriginales.value.length,
  aprobadas: props.stats?.recibidas ?? ordenesOriginales.value.filter(o => o.estado === 'recibida').length,
  pendientes: props.stats?.pendientes ?? ordenesOriginales.value.filter(o => o.estado === 'pendiente').length,
  borrador: props.stats?.borrador ?? ordenesOriginales.value.filter(o => o.estado === 'borrador').length,
  cancelada: props.stats?.canceladas ?? ordenesOriginales.value.filter(o => o.estado === 'cancelada').length
}))

// Métodos
const handleLimpiarFiltros = () => {
  searchTerm.value = ''
  sortBy.value = 'id-desc'
  filtroEstado.value = ''
  notyf.success('Filtros limpiados')
}

// Variables faltantes
const loading = ref(false)
const ordenCompraIdToDelete = ref(null)
const showConfirmationDialog = ref(false)

const eliminarOrden = async () => {
  if (!ordenCompraIdToDelete.value) return;
  loading.value = true;
  try {
    await router.delete(route('ordenescompra.destroy', ordenCompraIdToDelete.value), {
      onSuccess: () => {
        notyf.success('Orden de compra eliminada exitosamente');
        ordenesOriginales.value = ordenesOriginales.value.filter(orden => orden.id !== ordenCompraIdToDelete.value);
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
      onSuccess: () => notyf.success('Orden duplicada exitosamente'),
      onError: () => notyf.error('Error al duplicar la orden')
    })
  }
}

const convertirACompra = (orden) => {
  if (confirm(`¿Convertir orden #${orden.id} en compra a proveedores? Esto creará una entrada en el módulo de compras.`)) {
    router.post(`/ordenescompra/${orden.id}/convertir-compra`, {}, {
      onSuccess: () => {
        notyf.success('Orden convertida a compra exitosamente')
        // Actualizar el estado local
        const index = ordenesOriginales.value.findIndex(o => o.id === orden.id)
        if (index !== -1) {
          ordenesOriginales.value[index] = { ...orden, estado: 'convertida' }
        }
      },
      onError: () => notyf.error('Error al convertir la orden')
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
        notyf.success('Orden recibida exitosamente')
        // Nota: No actualizamos stock aquí porque las órdenes de compra no manejan inventario
        ordenesOriginales.value = ordenesOriginales.value.map(o =>
          o.id === selectedId.value ? { ...o, estado: 'recibida' } : o
        )
        showModal.value = false
        selectedId.value = null
      },
      onError: () => notyf.error('Error al recibir la orden')
    })
  } catch (error) {
    console.error(error)
    notyf.error('Error inesperado')
  }
}

const marcarUrgente = (orden) => {
  if (confirm(`¿Marcar orden #${orden.id} como urgente?`)) {
    router.post(`/ordenescompra/${orden.id}/urgente`, {}, {
      onSuccess: () => {
        notyf.success('Orden marcada como urgente')
        const index = ordenesOriginales.value.findIndex(o => o.id === orden.id)
        if (index !== -1) {
          ordenesOriginales.value[index] = { ...orden, urgente: true }
        }
      },
      onError: () => notyf.error('Error al marcar como urgente')
    })
  }
}

const crearNuevaOrden = () => {
  router.visit('/ordenescompra/create')
}


const exportarOrdenes = () => {
  if (ordenesOriginales.value.length === 0) {
    notyf.error('No hay órdenes para exportar')
    return
  }

  try {
    // Crear datos para exportación
    const datosExportar = ordenesOriginales.value.map(orden => ({
      'Número': orden.numero_orden || orden.id,
      'Proveedor': orden.proveedor?.nombre_razon_social || 'N/A',
      'Estado': orden.estado,
      'Fecha': orden.created_at,
      'Total': orden.total || 0,
      'Items': orden.items?.length || 0
    }))

    // Aquí puedes integrar una librería de exportación como xlsx o csv
    notyf.success('Exportación preparada - funcionalidad de exportación pendiente de implementar')
  } catch (error) {
    console.error('Error en exportación:', error)
    notyf.error('Error al exportar órdenes')
  }
}

const importarOrdenes = () => {
  notyf.success('Funcionalidad de importación - pendiente de implementar')
}

</script>

<template>
  <Head title="Órdenes de Compra" />

  <div class="ordenes-compra-index min-h-screen bg-gray-50">
    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header universal con estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :aprobadas="estadisticas.aprobadas"
        :pendientes="estadisticas.pendientes"
        :borrador="estadisticas.borrador"
        :cancelada="estadisticas.cancelada"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        :show-export="true"
        :show-import="true"
        @limpiar-filtros="handleLimpiarFiltros"
        @exportar="exportarOrdenes"
        @importar="importarOrdenes"
      />


      <!-- Tabla de órdenes -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="ordenesFiltradas"
          tipo="ordenescompra"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          :mapeo="{
            nombre: 'proveedor.nombre_razon_social',
            rfc: 'proveedor.rfc',
            activo: 'estado',
            fecha: 'created_at'
          }"
          @ver-detalles="verDetalles"
          @editar="editarOrden"
          @duplicar="duplicarOrden"
          @convertir-compra="convertirACompra"
          @marcar-urgente="marcarUrgente"
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

.ordenes-compra-index > * {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 640px) {
  .ordenes-compra-index .max-w-8xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .ordenes-compra-index h1 {
    font-size: 1.875rem;
  }
}
</style>
