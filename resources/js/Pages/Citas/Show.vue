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

        <!-- Productos Utilizados -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Productos Utilizados</label>
          <div v-if="cita.productos_utilizados && cita.productos_utilizados.length > 0" class="bg-white p-4 rounded-md shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Uso</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="producto in cita.productos_utilizados" :key="producto.id">
                    <td class="px-3 py-2 text-sm text-gray-900">{{ getProductoNombre(producto) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ producto.pivot.cantidad }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">${{ formatearPrecio(producto.pivot.precio_unitario) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900">{{ formatearTipoUso(producto.pivot.tipo_uso) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900">{{ producto.pivot.notas || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">No se utilizaron productos en esta cita</p>
        </div>

        <!-- Productos Vendidos -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Productos Vendidos</label>
          <div v-if="cita.productos_vendidos && cita.productos_vendidos.length > 0" class="bg-white p-4 rounded-md shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Venta</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="producto in cita.productos_vendidos" :key="producto.id">
                    <td class="px-3 py-2 text-sm text-gray-900">{{ getProductoNombre(producto) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ producto.pivot.cantidad }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">${{ formatearPrecio(producto.pivot.precio_venta) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">${{ formatearPrecio(producto.pivot.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">No se vendieron productos en esta cita</p>
        </div>

        <!-- Servicios Realizados -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Servicios Realizados</label>
          <div v-if="cita.servicios_realizados && cita.servicios_realizados.length > 0" class="bg-white p-4 rounded-md shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="servicio in cita.servicios_realizados" :key="servicio.id">
                    <td class="px-3 py-2 text-sm text-gray-900">{{ servicio.nombre }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ servicio.pivot.cantidad }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">${{ formatearPrecio(servicio.pivot.precio) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 text-right">${{ formatearPrecio(servicio.pivot.subtotal) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-900">{{ servicio.pivot.notas || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <p v-else class="text-gray-500 italic">No se realizaron servicios adicionales en esta cita</p>
        </div>

        <!-- Información de Venta -->
        <div v-if="cita.venta_id" class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Venta Asociada</label>
          <p class="text-gray-700">Esta cita generó la venta #{{ cita.venta_id }}</p>
        </div>

        <!-- Información de Requerimiento de Venta -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">¿Requiere Venta?</label>
          <p class="text-gray-700">{{ cita.requiere_venta ? 'Sí' : 'No' }}</p>
        </div>

        <!-- Monto de Productos Vendidos -->
        <div v-if="cita.monto_productos_vendidos" class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Monto de Productos Vendidos</label>
          <p class="text-gray-700">${{ formatearPrecio(cita.monto_productos_vendidos) }}</p>
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

  const formatearPrecio = (precio) => {
    return parseFloat(precio || 0).toFixed(2);
  };

  const formatearTipoUso = (tipoUso) => {
    const tipos = {
      'repuesto': 'Repuesto',
      'consumible': 'Consumible',
      'herramienta': 'Herramienta',
      'otro': 'Otro'
    };
    return tipos[tipoUso] || tipoUso || 'No especificado';
  };

  const getProductoNombre = (producto) => {
    return producto.nombre || `Producto #${producto.id}`;
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
