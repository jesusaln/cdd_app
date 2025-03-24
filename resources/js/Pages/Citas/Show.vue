<template>
    <Head title="Ver Cita" />
    <div class="citas-show max-w-4xl mx-auto p-6 bg-gray-50 rounded-lg shadow-md">
      <h1 class="text-2xl font-semibold mb-6 text-center">Detalles de la Cita #{{ props.cita.id }}</h1>

      <div class="space-y-6">
        <!-- Cliente -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Cliente</label>
          <p class="text-gray-700">{{ clienteNombre }}</p>
        </div>

        <!-- Tipo de Servicio -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Servicio</label>
          <p class="text-gray-700">{{ formatearTipoServicio(cita.tipo_servicio) }}</p>
        </div>

        <!-- Fecha y Hora -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Fecha y Hora</label>
          <p class="text-gray-700">{{ formatearFechaHora(cita.fecha_hora) }}</p>
        </div>

        <!-- Descripción -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
          <p class="text-gray-700">{{ cita.descripcion || 'Sin descripción' }}</p>
        </div>

        <!-- Tipo de Equipo -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Equipo</label>
          <p class="text-gray-700">{{ formatearTipoEquipo(cita.tipo_equipo) }}</p>
        </div>

        <!-- Marca del Equipo -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Marca del Equipo</label>
          <p class="text-gray-700">{{ cita.marca_equipo || 'Sin marca' }}</p>
        </div>

        <!-- Modelo del Equipo -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Modelo del Equipo</label>
          <p class="text-gray-700">{{ cita.modelo_equipo || 'Sin modelo' }}</p>
        </div>

        <!-- Problema Reportado -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Problema Reportado</label>
          <p class="text-gray-700">{{ cita.problema_reportado || 'Sin problema reportado' }}</p>
        </div>

        <!-- Estado -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
          <p class="text-gray-700">{{ formatearEstado(cita.estado) }}</p>
        </div>

        <!-- Técnico -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Técnico</label>
          <p class="text-gray-700">{{ tecnicoNombre }}</p>
        </div>

        <!-- Evidencias -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Evidencias</label>
          <p class="text-gray-700">{{ cita.evidencias || 'No hay evidencias disponibles' }}</p>
        </div>

        <!-- Foto del Equipo -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Foto del Equipo</label>
          <img v-if="cita.foto_equipo" :src="cita.foto_equipo" alt="Foto del Equipo" class="max-w-full h-auto rounded-md shadow-sm">
          <p v-else class="text-gray-500 italic">No hay foto del equipo disponible</p>
        </div>

        <!-- Foto de la Hoja de Servicio -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Foto de la Hoja de Servicio</label>
          <img v-if="cita.foto_hoja_servicio" :src="cita.foto_hoja_servicio" alt="Foto de la Hoja de Servicio" class="max-w-full h-auto rounded-md shadow-sm">
          <p v-else class="text-gray-500 italic">No hay foto de la hoja de servicio disponible</p>
        </div>

        <!-- Foto de Identificación del Cliente -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Foto de Identificación del Cliente</label>
          <img v-if="cita.foto_identificacion" :src="cita.foto_identificacion" alt="Foto de Identificación del Cliente" class="max-w-full h-auto rounded-md shadow-sm">
          <p v-else class="text-gray-500 italic">No hay foto de identificación disponible</p>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { Head } from '@inertiajs/vue3';
  import { computed, onMounted } from 'vue'; // Corregido: onMounted desde vue
  import AppLayout from '@/Layouts/AppLayout.vue';

  defineOptions({ layout: AppLayout });

  const props = defineProps({
    cita: Object,
    tecnicos: Array,
    clientes: Array,
  });

  const clienteNombre = computed(() => {
    const cliente = props.clientes.find(c => c.id === props.cita.cliente_id);
    return cliente ? cliente.nombre_razon_social : 'Desconocido';
  });

  const tecnicoNombre = computed(() => {
    const tecnico = props.tecnicos.find(t => t.id === props.cita.tecnico_id);
    return tecnico ? tecnico.nombre : 'Desconocido';
  });

  const formatearTipoServicio = (tipo) => {
    const tipos = {
      instalacion: 'Instalación',
      diagnostico: 'Diagnóstico',
      reparacion: 'Reparación',
      garantia: 'Garantía',
      otro_servicio: 'Otro Servicio',
    };
    return tipos[tipo] || 'Desconocido';
  };

  const formatearTipoEquipo = (tipo) => {
    const tipos = {
      minisplit: 'Minisplit',
      boiler: 'Boiler',
      refrigerador: 'Refrigerador',
      lavadora: 'Lavadora',
      secadora: 'Secadora',
      estufa: 'Estufa',
      campana: 'Campana',
      horno_de_microondas: 'Horno de Microondas',
      licuadora: 'Licuadora',
      otro_equipo: 'Otro Equipo',
    };
    return tipos[tipo] || 'Desconocido';
  };

  const formatearEstado = (estado) => {
    const estados = {
      pendiente: 'Pendiente',
      en_proceso: 'En Proceso',
      completado: 'Completado',
      cancelado: 'Cancelado',
    };
    return estados[estado] || 'Desconocido';
  };

  const formatearFechaHora = (fechaHora) => {
    const fecha = new Date(fechaHora);
    return fecha.toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' });
  };

  onMounted(() => {
    console.log('Datos de la cita:', props.cita);
    console.log('Foto del equipo:', props.cita.foto_equipo);
    console.log('Foto de la hoja de servicio:', props.cita.foto_hoja_servicio);
    console.log('Foto de identificación:', props.cita.foto_identificacion);
  });
  </script>

  <style scoped>
  /* Estilos personalizados */
  .citas-show {
    margin-top: 1rem;
  }

  img {
    max-width: 300px; /* Limita el ancho máximo de las imágenes */
  }
  </style>
