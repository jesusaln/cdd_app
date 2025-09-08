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
import axios from 'axios'; // Asegúrate de importar axios si lo estás usando

defineOptions({ layout: AppLayout });

// === PROPS ===
const props = defineProps({
  rentas: {
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
const selectedRenta = ref(null);

// Configuración del header
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

// === DATOS FILTRADOS Y ORDENADOS (Computados) ===
const documentosFiltrados = computed(() => {
  let list = props.rentas.data || [];
  // Filtro por búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    list = list.filter(r => {
      return (
        r.numero_contrato?.toLowerCase().includes(term) ||
        r.cliente?.nombre?.toLowerCase().includes(term) ||
        r.cliente?.email?.toLowerCase().includes(term) ||
        r.equipos?.some(eq => eq.nombre?.toLowerCase().includes(term))
      );
    });
  }
  // Filtro por estado
  if (filtroEstado.value) {
    list = list.filter(r => r.estado === filtroEstado.value);
  }
  // Ordenamiento
  list = [...list]; // Copia para no mutar
  switch (sortBy.value) {
    case 'fecha-asc':
      list.sort((a, b) => new Date(a.fecha_inicio) - new Date(b.fecha_inicio));
      break;
    case 'fecha-desc':
      list.sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio));
      break;
    case 'cliente-asc':
      list.sort((a, b) => (a.cliente?.nombre || '').localeCompare(b.cliente?.nombre || ''));
      break;
    case 'cliente-desc':
      list.sort((a, b) => (b.cliente?.nombre || '').localeCompare(a.cliente?.nombre || ''));
      break;
    default:
      break;
  }
  return list;
});

// === ESTADÍSTICAS ===
const estadisticas = computed(() => {
  const total = props.rentas.data?.length || 0;
  const activas = props.rentas.data?.filter(r => r.estado === 'activo').length || 0;
  const vencidas = props.rentas.data?.filter(r =>
    ['vencido', 'moroso', 'proximo_vencimiento'].includes(r.estado)
  ).length || 0;
  return { total, activas, vencidas };
});

// === MÉTODOS ===
const handleLimpiarFiltros = () => {
  searchTerm.value = '';
  sortBy.value = 'fecha-desc';
  filtroEstado.value = '';
  notyf.success('Filtros limpiados');
};

const updateSort = (newSort) => {
  if (['fecha-asc', 'fecha-desc', 'cliente-asc', 'cliente-desc'].includes(newSort)) {
    sortBy.value = newSort;
  }
};

// Acciones
const verDetalles = (renta) => {
  if (!renta) return notyf.error('Renta no válida');
  selectedRenta.value = renta;
  modalMode.value = 'details';
  showModal.value = true;
};

const editarRenta = (id) => {
  if (!id) return notyf.error('ID inválido');
  router.visit(`/rentas/${id}/edit`);
};

const duplicarRenta = (renta) => {
  if (confirm(`¿Duplicar renta #${renta.numero_contrato}?`)) {
    router.post(`/rentas/${renta.id}/duplicate`, {}, {
      onSuccess: () => notyf.success('Renta duplicada'),
      onError: () => notyf.error('Error al duplicar')
    });
  }
};

const imprimirRenta = async (renta) => {
  const rentaConFecha = {
    ...renta,
    fecha: renta.fecha_inicio || renta.created_at || new Date().toISOString()
  };
  const validaciones = [
    [rentaConFecha.id, 'ID del documento no encontrado'],
    [rentaConFecha.cliente?.nombre, 'Datos del cliente no encontrados'],
    [Array.isArray(rentaConFecha.equipos) && rentaConFecha.equipos.length > 0, 'Lista de equipos no válida'],
    [rentaConFecha.fecha, 'Fecha no especificada']
  ];
  for (const [cond, msg] of validaciones) {
    if (!cond) return notyf.error(`Error: ${msg}`);
  }
  try {
    notyf.success('Generando PDF...');
    await generarPDF('Contrato de Renta', rentaConFecha);
    notyf.success('PDF generado');
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

const eliminarRenta = () => {
  if (!selectedId.value) return notyf.error('No se seleccionó ninguna renta');
  router.delete(`/rentas/${selectedId.value}`, {
    onSuccess: () => {
      notyf.success('Renta eliminada');
      showModal.value = false;
      selectedId.value = null;
    },
    onError: () => notyf.error('Error al eliminar')
  });
};

const renovarRenta = async (renta) => {
  if (!['activo', 'proximo_vencimiento', 'vencido'].includes(renta.estado)) {
    return notyf.error(`No se puede renovar en estado: ${renta.estado}`);
  }
  try {
    const response = await axios.post(`/rentas/${renta.id}/renovar`, { meses_renovacion: 12 });
    if (response.data.success) {
      notyf.success(response.data.message || 'Renta renovada');
      showModal.value = false;
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Error al renovar';
    notyf.error(msg);
  }
};

const suspenderRenta = async (renta) => {
  if (!confirm(`¿Suspender renta #${renta.numero_contrato}?`)) return;
  try {
    const response = await axios.post(`/rentas/${renta.id}/suspender`);
    if (response.data.success) {
      notyf.success('Renta suspendida');
    }
  } catch (error) {
    notyf.error('Error al suspender');
  }
};

const reactivarRenta = async (renta) => {
  if (!confirm(`¿Reactivar renta #${renta.numero_contrato}?`)) return;
  try {
    const response = await axios.post(`/rentas/${renta.id}/reactivar`);
    if (response.data.success) {
      notyf.success('Renta reactivada');
    }
  } catch (error) {
    notyf.error('Error al reactivar');
  }
};

const crearNuevaRenta = () => {
  router.visit('/rentas/create');
};
</script>

<template>
  <div class="rentas-index bg-gray-50 min-h-screen">
    <Head title="Rentas" />
    <!-- Header principal -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Rentas</h1>
            <p class="text-gray-600">Administra todas las rentas de puntos de venta.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-8xl mx-auto px-6 py-8">

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
      <!-- Tabla -->
      <DocumentosTable
        :documentos="documentosFiltrados"
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
      <div v-if="rentas.links && rentas.links.length > 2" class="mt-6 flex justify-center">
        <div class="inline-flex rounded-md shadow-sm">
          <template v-for="link in rentas.links">
            <Link
              v-if="link.url"
              :key="link.label + '-link'"
              :href="link.url"
              v-html="link.label"
              :class="[
                'px-3 py-2 text-sm border',
                link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                'first:rounded-l-md last:rounded-r-md'
              ]"
              preserve-scroll
            />
            <span
              v-else
              :key="link.label + '-span'"
              v-html="link.label"
              class="px-3 py-2 text-sm border text-gray-400 cursor-not-allowed"
            ></span>
          </template>
        </div>
      </div>

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
  </div>
</template>

<style scoped>
.rentas-index {
  background-color: #f9fafb;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.rentas-index > * {
  animation: fadeIn 0.3s ease-out;
}
</style>
