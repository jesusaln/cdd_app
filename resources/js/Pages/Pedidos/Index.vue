<!-- /resources/js/Pages/Pedidos/Index.vue -->
<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { generarPDF } from '@/Utils/pdfGenerator';
import AppLayout from '@/Layouts/AppLayout.vue';
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue';
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue';
import Modales from '@/Components/IndexComponents/Modales.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  pedidos: {
    type: Array,
    default: () => []
  }
});

// Estado reactivo
const searchTerm = ref('');
const sortBy = ref('fecha-desc');
const filtroEstado = ref('');
const showModal = ref(false);
const modalMode = ref('details');
const selectedId = ref(null);
const selectedPedido = ref(null);

// Configuración del header universal
const headerConfig = {
  module: 'pedidos',
  createButtonText: 'Nuevo Pedido',
  searchPlaceholder: 'Buscar por cliente, número...'
};

// Notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false }
  ]
});

// Datos originales - asegúrate de que sean reactivos
const pedidosOriginales = ref([...props.pedidos]);

// Watch para actualizar cuando cambien las props
watch(() => props.pedidos, (newVal) => {
  console.log('Pedidos recibidos:', newVal);
  pedidosOriginales.value = [...newVal];
}, { deep: true, immediate: true });

// Estadísticas calculadas
const estadisticas = computed(() => {
  const stats = {
    total: pedidosOriginales.value.length,
    aprobados: 0,
    pendientes: 0
  };

  pedidosOriginales.value.forEach(p => {
    if (p.estado === 'aprobado') stats.aprobados++;
    else if (p.estado === 'pendiente') stats.pendientes++;
  });

  return stats;
});

// Método para limpiar filtros
const handleLimpiarFiltros = () => {
  searchTerm.value = '';
  sortBy.value = 'fecha-desc';
  filtroEstado.value = '';
  notyf.success('Filtros limpiados correctamente');
};

// Watch para debug de filtros
watch([searchTerm, sortBy, filtroEstado], ([newSearch, newSort, newEstado]) => {
  console.log('Filtros cambiaron:', {
    search: newSearch,
    sort: newSort,
    estado: newEstado
  });
}, { deep: true });

// Actualizar ordenamiento
const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') {
    sortBy.value = newSort;
    console.log('Ordenamiento actualizado:', newSort);
  }
};

// === MÉTODOS DE ACCIONES ===

const verDetalles = (pedido) => {
  console.log('Ver detalles de:', pedido);
  if (!pedido) {
    notyf.error('Pedido no válido');
    return;
  }
  selectedPedido.value = pedido;
  modalMode.value = 'details';
  showModal.value = true;
};

const editarPedido = (id) => {
  console.log('Editar pedido ID:', id);
  if (!id) {
    notyf.error('ID de pedido no válido');
    return;
  }
  router.visit(`/pedidos/${id}/edit`);
};

const duplicarPedido = (pedido) => {
  if (confirm(`¿Duplicar pedido #${pedido.id}?`)) {
    router.post(`/pedidos/${pedido.id}/duplicate`, {}, {
      onSuccess: (page) => {
        notyf.success('Pedido duplicado');
      },
      onError: (errors) => {
        notyf.error('Error al duplicar');
      }
    });
  }
};

const imprimirPedido = async (pedido) => {
  console.log('Imprimir pedido:', pedido);

  // Aseguramos que tenga fecha
  const pedidoConFecha = {
    ...pedido,
    fecha: pedido.fecha || pedido.created_at || new Date().toISOString()
  };

  // Validaciones
  if (!pedidoConFecha.id) {
    notyf.error('Error: ID del documento no encontrado');
    return;
  }
  if (!pedidoConFecha.cliente || !pedidoConFecha.cliente.nombre) {
    notyf.error('Error: Datos del cliente no encontrados');
    return;
  }
  if (!pedidoConFecha.productos || !Array.isArray(pedidoConFecha.productos) || pedidoConFecha.productos.length === 0) {
    notyf.error('Error: Lista de productos no válida');
    return;
  }
  if (!pedidoConFecha.fecha) {
    notyf.error('Error: Fecha no especificada');
    return;
  }

  try {
    notyf.success('Generando PDF...');
    await generarPDF('Pedido', pedidoConFecha);
    notyf.success('PDF generado correctamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error(`Error al generar el PDF: ${error.message}`);
  }
};

const confirmarEliminacion = (id) => {
  console.log('Confirmar eliminación ID:', id);
  if (!id) {
    notyf.error('ID de pedido no válido');
    return;
  }
  selectedId.value = id;
  modalMode.value = 'confirm';
  showModal.value = true;
};

const eliminarPedido = async () => {
  if (!selectedId.value) {
    notyf.error('No se seleccionó ningún pedido para eliminar');
    return;
  }

  try {
    router.delete(`/pedidos/${selectedId.value}`, {
      onSuccess: (page) => {
        notyf.success('Pedido eliminado exitosamente');
        pedidosOriginales.value = pedidosOriginales.value.filter(
          p => p.id !== selectedId.value
        );
        showModal.value = false;
        selectedId.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar el pedido');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Error inesperado al eliminar');
  }
};

// Método para crear nuevo pedido
const crearNuevoPedido = () => {
  router.visit('/pedidos/create');
};
</script>

<template>
  <Head title="Pedidos" />

  <div class="pedidos-index min-h-screen bg-gray-50">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              Gestión de Pedidos
            </h1>
            <p class="text-gray-600">
              Administra y gestiona todos tus pedidos de manera eficiente
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
      <UniversalHeader
        :total="estadisticas.total"
        :aprobados="estadisticas.aprobados"
        :pendientes="estadisticas.pendientes"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Tabla de documentos -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="pedidosOriginales"
          tipo="pedidos"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarPedido"
          @duplicar="duplicarPedido"
          @imprimir="imprimirPedido"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
        />
      </div>
    </div>

    <!-- Modales -->
    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedPedido"
      tipo="pedidos"
      @close="showModal = false"
      @confirm-delete="eliminarPedido"
      @imprimir="imprimirPedido"
      @editar="editarPedido"
    />
  </div>
</template>

<style scoped>
.pedidos-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

/* Responsive */
@media (max-width: 640px) {
  .pedidos-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .pedidos-index h1 {
    font-size: 1.5rem;
  }
}

/* Animaciones suaves */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.pedidos-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
