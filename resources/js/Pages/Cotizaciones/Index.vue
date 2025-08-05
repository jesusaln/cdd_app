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
  cotizaciones: { type: Array, default: () => [] }
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

// Datos originales
const cotizacionesOriginales = ref([...props.cotizaciones]);
watch(() => props.cotizaciones, (newVal) => {
  console.log('Cotizaciones recibidas:', newVal);
  cotizacionesOriginales.value = [...newVal];
}, { deep: true });

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

// Métodos de filtros
const handleLimpiarFiltros = () => {
  searchTerm.value = '';
  sortBy.value = 'fecha-desc';
  filtroEstado.value = '';
  notyf.success('Filtros limpiados correctamente');
};

watch([searchTerm, sortBy, filtroEstado], ([newSearch, newSort, newEstado]) => {
  console.log('Filtros cambiaron:', { search: newSearch, sort: newSort, estado: newEstado });
}, { deep: true });

const updateSort = (newSort) => {
  if (newSort && typeof newSort === 'string') {
    sortBy.value = newSort;
    console.log('Ordenamiento actualizado:', newSort);
  }
};

// Métodos de acciones
const verDetalles = (cotizacion) => {
  if (!cotizacion) {
    notyf.error('Cotización no válida');
    return;
  }
  selectedCotizacion.value = cotizacion;
  modalMode.value = 'details';
  showModal.value = true;
};

const editarCotizacion = (id) => {
  if (!id) {
    notyf.error('ID de cotización no válido');
    return;
  }
  router.visit(`/cotizaciones/${id}/edit`);
};

const duplicarCotizacion = (cotizacion) => {
  if (!cotizacion?.id) {
    notyf.error('No se pudo duplicar: Cotización no válida');
    return;
  }
  router.post(`/cotizaciones/${cotizacion.id}/duplicar`, {}, {
    onSuccess: () => {
      notyf.success('Cotización duplicada exitosamente');
    },
    onError: (errors) => {
      console.error('Error al duplicar:', errors);
      notyf.error('Error al duplicar la cotización');
    }
  });
};

const imprimirCotizacion = async (cotizacion) => {
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
    await generarPDF('Cotización', cotizacion);
    notyf.success('PDF generado correctamente');
  } catch (error) {
    console.error('Error al generar PDF:', error.message);
    notyf.error(`Error al generar el PDF: ${error.message}`);
  }
};

const confirmarEliminacion = (id) => {
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
    await router.delete(`/cotizaciones/${selectedId.value}`, {
      onSuccess: () => {
        notyf.success('Cotización eliminada exitosamente');
        cotizacionesOriginales.value = cotizacionesOriginales.value.filter(c => c.id !== selectedId.value);
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
</script>

<template>
  <Head title="Cotizaciones" />

  <div class="cotizaciones-index min-h-screen bg-gray-50 p-4">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">
        Gestión de Cotizaciones
      </h1>
      <p class="text-gray-600">
        Administra y gestiona todas tus cotizaciones de manera eficiente
      </p>
    </div>

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
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

@media (max-width: 640px) {
  .cotizaciones-index {
    padding: 1rem;
  }
}
</style>
