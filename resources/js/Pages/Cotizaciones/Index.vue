<!-- /resources/js/Pages/Cotizaciones/Index.vue -->
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
  cotizaciones: {
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
const selectedCotizacion = ref(null);

// Configuración del header universal
const headerConfig = {
  module: 'cotizaciones',
  createButtonText: 'Nueva Cotización',
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
const cotizacionesOriginales = ref([...props.cotizaciones]);

// Watch para actualizar cuando cambien las props
watch(() => props.cotizaciones, (newVal) => {
  console.log('Cotizaciones recibidas:', newVal);
  cotizacionesOriginales.value = [...newVal];
}, { deep: true, immediate: true });

// Estadísticas calculadas
const estadisticas = computed(() => {
  const stats = {
    total: cotizacionesOriginales.value.length,
    aprobadas: 0,
    pendientes: 0
  };

  cotizacionesOriginales.value.forEach(c => {
    if (c.estado === 'aprobado') stats.aprobadas++;
    else if (c.estado === 'pendiente') stats.pendientes++;
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

const verDetalles = (cotizacion) => {
  console.log('Ver detalles de:', cotizacion);
  if (!cotizacion) {
    notyf.error('Cotización no válida');
    return;
  }
  selectedCotizacion.value = cotizacion;
  modalMode.value = 'details';
  showModal.value = true;
};

const editarCotizacion = (id) => {
  console.log('Editar cotización ID:', id);
  if (!id) {
    notyf.error('ID de cotización no válido');
    return;
  }
  router.visit(`/cotizaciones/${id}/edit`);
};

const duplicarCotizacion = (cotizacion) => {
  console.log('Duplicar cotización:', cotizacion);
  if (!cotizacion?.id) {
    notyf.error('No se pudo duplicar: Cotización no válida');
    return;
  }

  router.post(`/cotizaciones/${cotizacion.id}/duplicar`, {}, {
    onSuccess: (page) => {
      notyf.success('Cotización duplicada exitosamente');
      // Actualizar la lista con los nuevos datos
      if (page.props.cotizaciones) {
        cotizacionesOriginales.value = [...page.props.cotizaciones];
      }
    },
    onError: (errors) => {
      console.error('Error al duplicar:', errors);
      notyf.error('Error al duplicar la cotización');
    }
  });
};

const imprimirCotizacion = async (cotizacion) => {
  console.log('Imprimir cotización:', cotizacion);

  // Validaciones
  if (!cotizacion) {
    notyf.error('No se seleccionó ninguna cotización para imprimir');
    return;
  }
  if (!cotizacion.id) {
    notyf.error('Error: ID del documento no encontrado');
    return;
  }
  if (!cotizacion.cliente || !cotizacion.cliente.nombre) {
    notyf.error('Error: Datos del cliente no encontrados');
    return;
  }
  if (!cotizacion.productos || !Array.isArray(cotizacion.productos) || cotizacion.productos.length === 0) {
    notyf.error('Error: Lista de productos no válida');
    return;
  }

  try {
    // Mostrar loading
    notyf.success('Generando PDF...');

    await generarPDF('Cotización', cotizacion);
    notyf.success('PDF generado correctamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error(`Error al generar el PDF: ${error.message || 'Error desconocido'}`);
  }
};

const confirmarEliminacion = (id) => {
  console.log('Confirmar eliminación ID:', id);
  if (!id) {
    notyf.error('ID de cotización no válido');
    return;
  }
  selectedId.value = id;
  modalMode.value = 'confirm';
  showModal.value = true;
};

const eliminarCotizacion = async () => {
  if (!selectedId.value) {
    notyf.error('No se seleccionó ninguna cotización para eliminar');
    return;
  }

  try {
    router.delete(`/cotizaciones/${selectedId.value}`, {
      onSuccess: (page) => {
        notyf.success('Cotización eliminada exitosamente');
        // Actualizar la lista local
        cotizacionesOriginales.value = cotizacionesOriginales.value.filter(
          c => c.id !== selectedId.value
        );
        // Cerrar modal
        showModal.value = false;
        selectedId.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la cotización');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Error inesperado al eliminar');
  }
};

// Método para crear nueva cotización
const crearNuevaCotizacion = () => {
  router.visit('/cotizaciones/create');
};
</script>

<template>
  <Head title="Cotizaciones" />

  <div class="cotizaciones-index min-h-screen bg-gray-50">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              Gestión de Cotizaciones
            </h1>
            <p class="text-gray-600">
              Administra y gestiona todas tus cotizaciones de manera eficiente
            </p>
          </div>

        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Header de filtros y estadísticas -->
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

      <!-- Tabla de documentos -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="cotizacionesOriginales"
          tipo="cotizaciones"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarCotizacion"
          @duplicar="duplicarCotizacion"
          @imprimir="imprimirCotizacion"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
        />
      </div>
    </div>

    <!-- Modales -->
    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedCotizacion"
      tipo="cotizaciones"
      @close="showModal = false"
      @confirm-delete="eliminarCotizacion"
      @imprimir="imprimirCotizacion"
      @editar="editarCotizacion"
    />
  </div>
</template>

<style scoped>
.cotizaciones-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

/* Responsive */
@media (max-width: 640px) {
  .cotizaciones-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .cotizaciones-index h1 {
    font-size: 1.5rem;
  }
}

/* Animaciones suaves */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.cotizaciones-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
