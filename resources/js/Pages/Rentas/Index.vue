<!-- /resources/js/Pages/Rentas/Index.vue -->
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
import { estadosConfig } from '@/Config/estados';

defineOptions({ layout: AppLayout });

const props = defineProps({
  rentas: {
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
const selectedRenta = ref(null);

// Configuración del header universal
const headerConfig = {
  module: 'rentas',
  createButtonText: 'Nueva Renta',
  searchPlaceholder: 'Buscar por cliente, equipo, número...'
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
const rentasOriginales = ref([...props.rentas]);

// Watch para actualizar cuando cambien las props
watch(() => props.rentas, (newVal) => {
  console.log('Rentas recibidas:', newVal);
  rentasOriginales.value = [...newVal];
}, { deep: true, immediate: true });

// Estadísticas calculadas
const estadisticas = computed(() => {
  const stats = {
    total: rentasOriginales.value.length,
    activas: 0,
    vencidas: 0
  };

  rentasOriginales.value.forEach(r => {
    if (r.estado === 'activo') stats.activas++;
    else if (r.estado === 'vencido' || r.estado === 'moroso') stats.vencidas++;
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

// === FUNCIONES AUXILIARES ===

// Función auxiliar para validar si una renta puede ser renovada
function puedeRenovar(renta) {
  // Permitir renovación si está activa o próxima a vencer
  const estadosValidos = ['activo', 'proximo_vencimiento', 'vencido'];
  return estadosValidos.includes(renta.estado);
}

// === MÉTODOS DE ACCIONES ===

const verDetalles = (renta) => {
  console.log('Ver detalles de:', renta);
  if (!renta) {
    notyf.error('Renta no válida');
    return;
  }
  selectedRenta.value = renta;
  modalMode.value = 'details';
  showModal.value = true;
};

const editarRenta = (id) => {
  console.log('Editar renta ID:', id);
  if (!id) {
    notyf.error('ID de renta no válido');
    return;
  }
  router.visit(`/rentas/${id}/edit`);
};

const duplicarRenta = (renta) => {
  if (confirm(`¿Duplicar renta #${renta.id} del cliente ${renta.cliente?.nombre || 'N/A'}?`)) {
    router.post(`/rentas/${renta.id}/duplicate`, {}, {
      onSuccess: (page) => {
        notyf.success('Renta duplicada');
        // Actualiza tu lista si es necesario
      },
      onError: (errors) => {
        notyf.error('Error al duplicar');
      }
    });
  }
};

const imprimirRenta = async (renta) => {
  console.log('Imprimir renta:', renta);

  // ✅ Aseguramos que tenga fecha
  const rentaConFecha = {
    ...renta,
    fecha: renta.fecha || renta.fecha_inicio || renta.created_at || new Date().toISOString()
  };

  // Validaciones
  if (!rentaConFecha.id) {
    notyf.error('Error: ID del documento no encontrado');
    return;
  }
  if (!rentaConFecha.cliente || !rentaConFecha.cliente.nombre) {
    notyf.error('Error: Datos del cliente no encontrados');
    return;
  }
  if (!rentaConFecha.equipos || !Array.isArray(rentaConFecha.equipos) || rentaConFecha.equipos.length === 0) {
    notyf.error('Error: Lista de equipos no válida');
    return;
  }
  if (!rentaConFecha.fecha) {
    notyf.error('Error: Fecha no especificada');
    return;
  }

  try {
    notyf.success('Generando PDF...');
    await generarPDF('Contrato de Renta', rentaConFecha);
    notyf.success('PDF generado correctamente');
  } catch (error) {
    console.error('Error al generar PDF:', error);
    notyf.error(`Error al generar el PDF: ${error.message}`);
  }
};

const confirmarEliminacion = (id) => {
  console.log('Confirmar eliminación ID:', id);
  if (!id) {
    notyf.error('ID de renta no válido');
    return;
  }
  selectedId.value = id;
  modalMode.value = 'confirm';
  showModal.value = true;
};

const eliminarRenta = async () => {
  if (!selectedId.value) {
    notyf.error('No se seleccionó ninguna renta para eliminar');
    return;
  }

  try {
    router.delete(`/rentas/${selectedId.value}`, {
      onSuccess: (page) => {
        notyf.success('Renta eliminada exitosamente');
        // Actualizar la lista local
        rentasOriginales.value = rentasOriginales.value.filter(
          r => r.id !== selectedId.value
        );
        // Cerrar modal
        showModal.value = false;
        selectedId.value = null;
      },
      onError: (errors) => {
        console.error('Error al eliminar:', errors);
        notyf.error('Error al eliminar la renta');
      }
    });
  } catch (error) {
    console.error('Error inesperado:', error);
    notyf.error('Error inesperado al eliminar');
  }
};

// Función para renovar renta
const renovarRenta = async (rentaData) => {
  // Validación previa
  if (!puedeRenovar(rentaData)) {
    const mensaje = `No se puede renovar la renta en estado: ${rentaData.estado}`;
    notyf.error(mensaje);
    return { success: false, error: mensaje };
  }

  try {
    // Mostrar notificación de proceso iniciado
    notyf.success('Procesando renovación de renta...');

    const response = await axios.post(`/rentas/${rentaData.id}/renovar`, {
      meses_renovacion: rentaData.meses_renovacion || 12
    });

    // Si la respuesta es exitosa
    if (response.data && response.data.success) {
      notyf.success(response.data.message || 'Renta renovada exitosamente');

      // Actualizar el estado local de la renta
      const index = rentasOriginales.value.findIndex(r => r.id === rentaData.id);
      if (index !== -1) {
        rentasOriginales.value[index] = {
          ...rentasOriginales.value[index],
          ...response.data.renta // Datos actualizados desde el servidor
        };
      }

      // Cerrar modal si está abierto
      showModal.value = false;

      return response.data;
    }

  } catch (error) {
    console.error('Error al renovar renta:', error);

    let errorMessage = 'Error desconocido al renovar renta';

    // Manejar diferentes tipos de errores
    if (error.response) {
      // Error del servidor (400, 500, etc.)
      if (error.response.data?.error) {
        errorMessage = error.response.data.error;
      } else if (error.response.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error.response.status === 400) {
        errorMessage = 'La renta no está en estado válido para renovación';
      } else if (error.response.status === 404) {
        errorMessage = 'Renta no encontrada';
      } else if (error.response.status === 422) {
        errorMessage = 'Datos de renta no válidos';
      } else if (error.response.status >= 500) {
        errorMessage = 'Error del servidor. Intenta nuevamente.';
      }
    } else if (error.request) {
      // Error de red
      errorMessage = 'Error de conexión. Verifica tu conexión a internet.';
    } else if (error.message) {
      // Error de configuración u otro
      errorMessage = error.message;
    }

    // Mostrar error al usuario
    notyf.error(errorMessage);

    // Re-lanzar el error para que el componente que llama pueda manejarlo si es necesario
    throw new Error(errorMessage);
  }
};

// Función para suspender renta
const suspenderRenta = async (renta) => {
  if (!confirm(`¿Confirmas suspender la renta #${renta.id} del cliente ${renta.cliente?.nombre || 'N/A'}?`)) {
    return;
  }

  try {
    notyf.success('Suspendiendo renta...');

    const response = await axios.post(`/rentas/${renta.id}/suspender`);

    if (response.data && response.data.success) {
      notyf.success(response.data.message || 'Renta suspendida exitosamente');

      // Actualizar el estado local
      const index = rentasOriginales.value.findIndex(r => r.id === renta.id);
      if (index !== -1) {
        rentasOriginales.value[index] = {
          ...rentasOriginales.value[index],
          estado: 'suspendido'
        };
      }
    }
  } catch (error) {
    console.error('Error al suspender renta:', error);
    notyf.error('Error al suspender la renta');
  }
};

// Función para reactivar renta
const reactivarRenta = async (renta) => {
  if (!confirm(`¿Confirmas reactivar la renta #${renta.id} del cliente ${renta.cliente?.nombre || 'N/A'}?`)) {
    return;
  }

  try {
    notyf.success('Reactivando renta...');

    const response = await axios.post(`/rentas/${renta.id}/reactivar`);

    if (response.data && response.data.success) {
      notyf.success(response.data.message || 'Renta reactivada exitosamente');

      // Actualizar el estado local
      const index = rentasOriginales.value.findIndex(r => r.id === renta.id);
      if (index !== -1) {
        rentasOriginales.value[index] = {
          ...rentasOriginales.value[index],
          estado: 'activo'
        };
      }
    }
  } catch (error) {
    console.error('Error al reactivar renta:', error);
    notyf.error('Error al reactivar la renta');
  }
};

// Método para crear nueva renta
const crearNuevaRenta = () => {
  router.visit('/rentas/create');
};
</script>

<template>
  <Head title="Rentas" />

  <div class="rentas-index min-h-screen bg-gray-50">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
              Gestión de Rentas
            </h1>
            <p class="text-gray-600">
              Administra y gestiona todas las rentas de puntos de venta de manera eficiente
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
        :aprobadas="estadisticas.activas"
        :pendientes="estadisticas.vencidas"
        v-model:search-term="searchTerm"
        v-model:sort-by="sortBy"
        v-model:filtro-estado="filtroEstado"
        :config="headerConfig"
        @limpiar-filtros="handleLimpiarFiltros"
      />

      <!-- Tabla de documentos -->
      <div class="mt-6">
        <DocumentosTable
          :documentos="rentasOriginales"
          tipo="rentas"
          :search-term="searchTerm"
          :sort-by="sortBy"
          :filtro-estado="filtroEstado"
          @ver-detalles="verDetalles"
          @editar="editarRenta"
          @duplicar="duplicarRenta"
          @imprimir="imprimirRenta"
          @eliminar="confirmarEliminacion"
          @sort="updateSort"
          @renovar="renovarRenta"
          @suspender="suspenderRenta"
          @reactivar="reactivarRenta"
        />
      </div>
    </div>

    <!-- Modales -->
    <Modales
      :show="showModal"
      :mode="modalMode"
      :selected="selectedRenta"
      tipo="rentas"
      @close="showModal = false"
      @confirm-delete="eliminarRenta"
      @imprimir="imprimirRenta"
      @editar="editarRenta"
      @renovar="renovarRenta"
      @suspender="suspenderRenta"
      @reactivar="reactivarRenta"
    />
  </div>
</template>

<style scoped>
.rentas-index {
  min-height: 100vh;
  background-color: #f9fafb;
}

/* Responsive */
@media (max-width: 640px) {
  .rentas-index .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .rentas-index h1 {
    font-size: 1.5rem;
  }
}

/* Animaciones suaves */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.rentas-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
