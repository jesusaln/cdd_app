<script setup>
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import { generarPDF } from '@/Utils/pdfGenerator';
import AppLayout from '@/Layouts/AppLayout.vue';
import UniversalHeader from '@/Components/IndexComponents/UniversalHeader.vue';
import DocumentosTable from '@/Components/IndexComponents/DocumentosTable.vue';
import Modales from '@/Components/IndexComponents/Modales.vue';
import { estadosConfig } from '@/Config/estados';
import axios from 'axios';

defineOptions({ layout: AppLayout });

// === PROPS ===
const props = defineProps({
  equipos: {
    type: Object, // Es un objeto paginado: { data: [], links: [], meta: {} }
    required: true
  }
});

// === REACTIVIDAD ===
const searchTerm = ref('');
const sortBy = ref('fecha-desc');
const filtroEstado = ref('');
const showModal = ref(false);
const modalMode = ref('details');
const selectedId = ref(null);
const selectedEquipo = ref(null);

// Configuración del header
const headerConfig = {
  module: 'equipos',
  createButtonText: 'Nuevo Equipo',
  searchPlaceholder: 'Buscar por nombre, modelo, serie...'
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

// === DATOS FILTRADOS Y ORDENADOS (Computados) ===
const documentosFiltrados = computed(() => {
  let list = props.equipos.data || [];

  // Filtro por búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    list = list.filter(eq => {
      return (
        eq.nombre?.toLowerCase().includes(term) ||
        eq.modelo?.toLowerCase().includes(term) ||
        eq.numero_serie?.toLowerCase().includes(term) ||
        eq.marca?.toLowerCase().includes(term) ||
        eq.categoria?.toLowerCase().includes(term) ||
        eq.codigo_interno?.toLowerCase().includes(term)
      );
    });
  }

  // Filtro por estado
  if (filtroEstado.value) {
    list = list.filter(eq => eq.estado === filtroEstado.value);
  }

  // Ordenamiento
  list = [...list]; // Copia para no mutar
  switch (sortBy.value) {
    case 'fecha-asc':
      list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      break;
    case 'fecha-desc':
      list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    case 'nombre-asc':
      list.sort((a, b) => (a.nombre || '').localeCompare(b.nombre || ''));
      break;
    case 'nombre-desc':
      list.sort((a, b) => (b.nombre || '').localeCompare(a.nombre || ''));
      break;
    case 'modelo-asc':
      list.sort((a, b) => (a.modelo || '').localeCompare(b.modelo || ''));
      break;
    case 'modelo-desc':
      list.sort((a, b) => (b.modelo || '').localeCompare(a.modelo || ''));
      break;
    default:
      break;
  }

  return list;
});

// === ESTADÍSTICAS ===
const estadisticas = computed(() => {
  const total = props.equipos.data?.length || 0;
  const disponibles = props.equipos.data?.filter(eq => eq.estado === 'disponible').length || 0;
  const rentados = props.equipos.data?.filter(eq => eq.estado === 'rentado').length || 0;
  const mantenimiento = props.equipos.data?.filter(eq =>
    ['mantenimiento', 'reparacion', 'fuera_servicio'].includes(eq.estado)
  ).length || 0;

  return {
    total,
    aprobadas: disponibles, // Usando 'aprobadas' para compatibilidad con UniversalHeader
    pendientes: mantenimiento // Usando 'pendientes' para compatibilidad
  };
});

// === MÉTODOS ===
const handleLimpiarFiltros = () => {
  searchTerm.value = '';
  sortBy.value = 'fecha-desc';
  filtroEstado.value = '';
  notyf.success('Filtros limpiados');
};

const updateSort = (newSort) => {
  const validSorts = ['fecha-asc', 'fecha-desc', 'nombre-asc', 'nombre-desc', 'modelo-asc', 'modelo-desc'];
  if (validSorts.includes(newSort)) {
    sortBy.value = newSort;
  }
};

// Acciones
const verDetalles = (equipo) => {
  if (!equipo) return notyf.error('Equipo no válido');
  selectedEquipo.value = equipo;
  modalMode.value = 'details';
  showModal.value = true;
};

const editarEquipo = (id) => {
  if (!id) return notyf.error('ID inválido');
  router.visit(`/equipos/${id}/edit`);
};

const duplicarEquipo = (equipo) => {
  if (confirm(`¿Duplicar equipo "${equipo.nombre}"?`)) {
    router.post(`/equipos/${equipo.id}/duplicate`, {}, {
      onSuccess: () => notyf.success('Equipo duplicado correctamente'),
      onError: () => notyf.error('Error al duplicar equipo')
    });
  }
};

const imprimirEquipo = async (equipo) => {
  const equipoConFecha = {
    ...equipo,
    fecha: equipo.created_at || new Date().toISOString()
  };

  const validaciones = [
    [equipoConFecha.id, 'ID del equipo no encontrado'],
    [equipoConFecha.nombre, 'Nombre del equipo no encontrado'],
    [equipoConFecha.fecha, 'Fecha no especificada']
  ];

  for (const [cond, msg] of validaciones) {
    if (!cond) return notyf.error(`Error: ${msg}`);
  }

  try {
    notyf.success('Generando PDF...');
    await generarPDF('Ficha de Equipo', equipoConFecha);
    notyf.success('PDF generado correctamente');
  } catch (error) {
    notyf.error(`Error al generar PDF: ${error.message}`);
  }
};

const confirmarEliminacion = (id) => {
  if (!id) return notyf.error('ID no válido');
  selectedId.value = id;
  modalMode.value = 'confirm';
  showModal.value = true;
};

const eliminarEquipo = () => {
  if (!selectedId.value) return notyf.error('No se seleccionó ningún equipo');

  router.delete(`/equipos/${selectedId.value}`, {
    onSuccess: () => {
      notyf.success('Equipo eliminado correctamente');
      showModal.value = false;
      selectedId.value = null;
    },
    onError: () => notyf.error('Error al eliminar equipo')
  });
};

const cambiarEstadoEquipo = async (equipo, nuevoEstado) => {
  const estadosValidos = ['disponible', 'rentado', 'mantenimiento', 'reparacion', 'fuera_servicio'];
  if (!estadosValidos.includes(nuevoEstado)) {
    return notyf.error(`Estado no válido: ${nuevoEstado}`);
  }

  if (!confirm(`¿Cambiar estado del equipo "${equipo.nombre}" a "${nuevoEstado}"?`)) return;

  try {
    const response = await axios.patch(`/equipos/${equipo.id}/estado`, {
      estado: nuevoEstado
    });

    if (response.data.success) {
      notyf.success(response.data.message || `Estado cambiado a ${nuevoEstado}`);
      showModal.value = false;
      // Recargar la página para actualizar los datos
      router.reload({ only: ['equipos'] });
    }
  } catch (error) {
    const msg = error.response?.data?.message || `Error al cambiar estado a ${nuevoEstado}`;
    notyf.error(msg);
  }
};

const programarMantenimiento = async (equipo) => {
  if (equipo.estado === 'mantenimiento') {
    return notyf.error('El equipo ya está en mantenimiento');
  }

  if (!confirm(`¿Programar mantenimiento para "${equipo.nombre}"?`)) return;

  try {
    const response = await axios.post(`/equipos/${equipo.id}/mantenimiento`);
    if (response.data.success) {
      notyf.success('Mantenimiento programado correctamente');
      showModal.value = false;
      router.reload({ only: ['equipos'] });
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Error al programar mantenimiento';
    notyf.error(msg);
  }
};

const marcarComoDisponible = async (equipo) => {
  await cambiarEstadoEquipo(equipo, 'disponible');
};

const marcarEnReparacion = async (equipo) => {
  await cambiarEstadoEquipo(equipo, 'reparacion');
};

const marcarFueraServicio = async (equipo) => {
  await cambiarEstadoEquipo(equipo, 'fuera_servicio');
};

const crearNuevoEquipo = () => {
  router.visit('/equipos/create');
};

// === WATCHERS ===
watch([searchTerm, sortBy, filtroEstado], () => {
  // Aquí podrías agregar lógica adicional cuando cambien los filtros
}, { deep: true });
</script>

<template>
  <Head title="Equipos" />

  <div class="equipos-index bg-gray-50 min-h-screen">
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Equipos</h1>
            <p class="text-gray-600">Administra todo el inventario de equipos y puntos de venta.</p>
          </div>

        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-8xl mx-auto px-6 py-8">
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

      <!-- Tabla de equipos -->
      <DocumentosTable
        :documentos="documentosFiltrados"
        tipo="equipos"
        :search-term="searchTerm"
        :sort-by="sortBy"
        :filtro-estado="filtroEstado"
        @ver-detalles="verDetalles"
        @editar="editarEquipo"
        @duplicar="duplicarEquipo"
        @imprimir="imprimirEquipo"
        @eliminar="confirmarEliminacion"
        @sort="updateSort"
        @cambiar-estado="cambiarEstadoEquipo"
        @programar-mantenimiento="programarMantenimiento"
        @marcar-disponible="marcarComoDisponible"
        @marcar-reparacion="marcarEnReparacion"
        @marcar-fuera-servicio="marcarFueraServicio"
      />

      <!-- Paginación -->
      <div v-if="equipos.links && equipos.links.length > 2" class="mt-6 flex justify-center">
        <div class="inline-flex rounded-md shadow-sm">
          <template v-for="link in equipos.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              v-html="link.label"
              :class="[
                'px-3 py-2 text-sm border border-gray-300',
                link.active
                  ? 'bg-indigo-600 text-white border-indigo-600'
                  : 'bg-white text-gray-700 hover:bg-gray-50',
                'first:rounded-l-md last:rounded-r-md'
              ]"
              preserve-scroll
            />
            <span
              v-else
              v-html="link.label"
              class="px-3 py-2 text-sm border border-gray-300 text-gray-400 cursor-not-allowed bg-gray-50 first:rounded-l-md last:rounded-r-md"
            />
          </template>
        </div>
      </div>

      <!-- Modales -->
      <Modales
        :show="showModal"
        :mode="modalMode"
        :selected="selectedEquipo"
        tipo="equipos"
        @close="showModal = false"
        @confirm-delete="eliminarEquipo"
        @imprimir="imprimirEquipo"
        @editar="editarEquipo"
        @cambiar-estado="cambiarEstadoEquipo"
        @programar-mantenimiento="programarMantenimiento"
        @marcar-disponible="marcarComoDisponible"
        @marcar-reparacion="marcarEnReparacion"
        @marcar-fuera-servicio="marcarFueraServicio"
      />
    </div>
  </div>
</template>

<style scoped>
.equipos-index {
  background-color: #f9fafb;
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

.equipos-index > * {
  animation: fadeIn 0.3s ease-out;
}

/* Estilos adicionales para mejorar la apariencia */
.equipos-index .bg-white {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Hover effects para botones */
.equipos-index button:hover {
  transform: translateY(-1px);
  transition: all 0.2s ease-in-out;
}

/* Estilo para la paginación */
.equipos-index .inline-flex a,
.equipos-index .inline-flex span {
  transition: all 0.2s ease-in-out;
}

.equipos-index .inline-flex a:hover {
  transform: translateY(-1px);
}
</style>
