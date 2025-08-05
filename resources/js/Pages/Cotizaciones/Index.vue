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
import Table from '@/Components/IndexComponents/Table.vue';
import Modales from '@/Components/IndexComponents/Modales.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  cotizaciones: { type: Array, default: () => [] }
});

// Estado reactivo
const searchTerm = ref('');
const sortBy = ref('fecha-desc');
const filtroEstado = ref('');
const showConfirm = ref(false);
const showDetails = ref(false);
const selectedId = ref(null);
const selectedCotizacion = ref(null);

// Configuración del header universal para cotizaciones
const headerConfig = {
  module: 'cotizaciones',
  // Puedes personalizar textos si lo deseas:
  // createButtonText: 'Nueva Cotización',
  // searchPlaceholder: 'Buscar por cliente, número...'
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
  cotizacionesOriginales.value = [...newVal];
}, { deep: true });

// Estadísticas calculadas dinámicamente
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

// ✅ Método mejorado que se llama desde el Header
const handleLimpiarFiltros = () => {
  searchTerm.value = '';
  sortBy.value = 'fecha-desc';
  filtroEstado.value = '';

  // Opcional: Lógica adicional cuando se limpian filtros
  //console.log('Filtros de cotizaciones limpiados');

  // Si necesitas hacer alguna petición adicional al servidor:
  // fetchCotizaciones();
};

// Watcher para reaccionar a cambios en filtros (opcional)
watch([searchTerm, sortBy, filtroEstado], ([newSearch, newSort, newEstado]) => {
  // Aquí puedes agregar lógica adicional si necesitas
  // Por ejemplo, logging para debugging:
  console.log('Filtros cambiaron:', {
    search: newSearch,
    sort: newSort,
    estado: newEstado
  });
}, { deep: true });

// Métodos existentes (sin cambios)
const verDetalles = (c) => {
  selectedCotizacion.value = c;
  showDetails.value = true;
};

const confirmarEliminacion = (id) => {
  selectedId.value = id;
  showConfirm.value = true;
};

const eliminarCotizacion = async () => {
  if (!selectedId.value) return;

  try {
    await router.delete(`/cotizaciones/${selectedId.value}`, {
      onSuccess: () => {
        notyf.success('Cotización eliminada exitosamente');
        cotizacionesOriginales.value = cotizacionesOriginales.value.filter(c => c.id !== selectedId.value);
        showConfirm.value = false;
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

const generarPDFVenta = async (c) => {
  try {
    await generarPDF('Cotización', c);
    notyf.success('PDF generado correctamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error('Error al generar el PDF');
  }
};

// Método para navegación (mejorado)
const editarCotizacion = (id) => {
  router.visit(`/cotizaciones/${id}/edit`);
};
</script>

<template>
  <Head title="Cotizaciones" />

  <div class="cotizaciones-index min-h-screen bg-gray-50 p-4">
    <!-- Título y descripción -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">
        Gestión de Cotizaciones
      </h1>
      <p class="text-gray-600">
        Administra y gestiona todas tus cotizaciones de manera eficiente
      </p>
    </div>

    <!-- Header Universal -->
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

    <!-- Tabla de cotizaciones -->
    <Table
      :cotizaciones="cotizacionesOriginales"
      :search-term="searchTerm"
      :sort-by="sortBy"
      :filtro-estado="filtroEstado"
      @ver-detalles="verDetalles"
      @editar="editarCotizacion"
      @eliminar="confirmarEliminacion"
      @generar-pdf="generarPDFVenta"
    />

    <!-- Modales -->
    <Modales
      :show-confirm="showConfirm"
      :show-details="showDetails"
      :selected="selectedCotizacion"
      @close-confirm="showConfirm = false"
      @confirm-delete="eliminarCotizacion"
      @close-details="showDetails = false"
    />
  </div>
</template>

<style scoped>
/* Estilos opcionales específicos para cotizaciones */
.cotizaciones-index {
  /* Puedes agregar estilos específicos aquí si los necesitas */
}
</style>
