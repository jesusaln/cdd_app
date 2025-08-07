<!-- /resources/js/Pages/Ventas/Index.vue -->
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
  ventas: {
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
const selectedVenta = ref(null);

// Configuración del header universal
const headerConfig = {
  module: 'ventas',
  createButtonText: 'Nueva Venta',
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
const ventasOriginales = ref([...props.ventas]);

// En el watch, cambia por esto para debug
watch(() => props.ventas, (newVal) => {
  console.log('🔍 Debug ventas recibidas:');
  console.log('- Total ventas:', newVal?.length || 0);
  console.log('- Primera venta:', newVal?.[0]);
  console.log('- Estado primera venta:', newVal?.[0]?.estado);
  console.log('- Cliente primera venta:', newVal?.[0]?.cliente);
  console.log('- Productos primera venta:', newVal?.[0]?.productos);

  ventasOriginales.value = [...(newVal || [])];
}, { deep: true, immediate: true });

// Estadísticas calculadas
const estadisticas = computed(() => {
  const stats = {
    total: ventasOriginales.value.length,
    aprobados: 0,
    pendientes: 0
  };

  ventasOriginales.value.forEach(v => {
    if (v.estado === 'aprobado') stats.aprobados++;
    else if (v.estado === 'pendiente') stats.pendientes++;
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

const verDetalles = (venta) => {
  console.log('Ver detalles de:', venta);
  if (!venta) {
    notyf.error('Venta no válida');
    return;
  }
  selectedVenta.value = venta;
  modalMode.value = 'details';
  showModal.value = true;
};

const editarVenta = (id) => {
  console.log('Editar venta ID:', id);
  if (!id) {
    notyf.error('ID de venta no válido');
    return;
  }
  router.visit(`/ventas/${id}/edit`);
};

const duplicarVenta = (venta) => {
  if (confirm(`¿Duplicar venta #${venta.id}?`)) {
    router.post(`/ventas/${venta.id}/duplicate`, {}, {
      onSuccess: () => {
        notyf.success('Venta duplicada');
      },
      onError: (errors) => {
        notyf.error('Error al duplicar');
      }
    });
  }
};

const imprimirVenta = async (venta) => {
  console.log('Imprimir venta:', venta);

  // Aseguramos que tenga fecha
  const ventaConFecha = {
    ...venta,
    fecha: venta.fecha || venta.created_at || new Date().toISOString()
  };

  // Validaciones
  if (!ventaConFecha.id) {
    notyf.error('Error: ID del documento no encontrado');
    return;
  }
  if (!ventaConFecha.cliente || !ventaConFecha.cliente.nombre) {
    notyf.error('Error: Datos del cliente no encontrados');
    return;
  }
  if (!ventaConFecha.productos || !Array.isArray(ventaConFecha.productos) || ventaConFecha.productos.length === 0) {
    notyf.error('Error: Lista de productos no válida');
    return;
  }
  if (!ventaConFecha.fecha) {
    notyf.error('Error: Fecha no especificada');
    return;
  }

  try {
    notyf.success('Generando PDF...');
    await generarPDF('Venta', ventaConFecha);
    notyf.success('PDF generado correctamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error(`Error al generar el PDF: ${error.message}`);
  }
};

const confirmarEliminacion = (id) => {
  console.log('Confirmar eliminación ID:', id);
  if (!id) {
    notyf.error('ID de venta no válido');
    return;
  }
  selectedId.value = id;
  modalMode.value = 'confirm';
  showModal.value = true;
};

const eliminarVenta = async () => {
  if (!selectedId.value) {
    notyf.error('No se seleccionó ninguna venta para eliminar');
    return;
  }

  try {
    router.delete(`/ventas/${selectedId.value}`, {
      onSuccess: () => {
        notyf.success('Venta eliminada exitosamente');
        ventasOriginales.value = ventasOriginales.value.filter(
          v => v.id !== selectedId.value
        );
        showModal.value = false;
        selectedId.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la venta');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Error inesperado al eliminar');
  }
};

// Método para crear nueva venta
const crearNuevaVenta = () => {
  router.visit('/ventas/create');
};
</script>

<template>
  <Head title="Ventas" />

  <div class="ventas-index min-h-screen bg-gray-50">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              Gestión de Ventas
            </h1>
            <p class="text-gray-600">
              Administra y gestiona todas tus ventas de manera eficiente
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
          :documentos="ventasOriginales"
          tipo="ventas"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarVenta"
          @duplicar="duplicarVenta"
          @imprimir="imprimirVenta"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
        />
      </div>
    </div>

    <!-- Modales -->
    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedVenta"
      tipo="ventas"
      @close="showModal = false"
      @confirm-delete="eliminarVenta"
      @imprimir="imprimirVenta"
      @editar="editarVenta"
    />
  </div>
</template>

<style scoped>
.ventas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

/* Responsive */
@media (max-width: 640px) {
  .ventas-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .ventas-index h1 {
    font-size: 1.5rem;
  }
}

/* Animaciones suaves */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.ventas-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
