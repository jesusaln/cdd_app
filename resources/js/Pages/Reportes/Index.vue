<template>
    <Head title="Reportes" />
    <div class="container mx-auto p-6">
      <!-- Encabezado -->
      <h1 class="text-2xl font-semibold mb-4">Reportes de Ventas</h1>

      <!-- Resumen -->
      <div class="mb-6 space-y-2">
        <p class="text-lg">Corte Total: {{ formatCurrency(corte) }}</p>
        <p class="text-lg">Utilidad Total: {{ formatCurrency(utilidad) }}</p>
      </div>

      <!-- Tabla de Reportes -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-4 py-2 text-left">ID Venta</th>
              <th scope="col" class="px-4 py-2 text-left">Total Venta</th>
              <th scope="col" class="px-4 py-2 text-left">Costo Total</th>
              <th scope="col" class="px-4 py-2 text-left">Utilidad</th>
              <th scope="col" class="px-4 py-2 text-left">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="reporte in reportes" :key="reporte.id" class="border-t hover:bg-gray-50 transition-colors">
              <td class="px-4 py-2">{{ reporte.id }}</td>
              <td class="px-4 py-2">{{ formatCurrency(reporte.total) }}</td>
              <td class="px-4 py-2">{{ formatCurrency(reporte.costo_total) }}</td>
              <td class="px-4 py-2">{{ formatCurrency(calculateProfit(reporte)) }}</td>
              <td class="px-4 py-2">{{ formatDate(reporte.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>

  <script setup>
  import { Head } from '@inertiajs/vue3';
  import Dashboard from '@/Pages/Dashboard.vue';
  import { format } from 'date-fns';
  import { es } from 'date-fns/locale';

  // Configuración del layout
  defineOptions({ layout: Dashboard });

  // Props recibidos
  defineProps({
    reportes: {
      type: Array,
      default: () => [],
    },
    corte: {
      type: Number,
      default: 0,
    },
    utilidad: {
      type: Number,
      default: 0,
    },
  });

  // Métodos auxiliares
  const formatCurrency = (value) => {
    const numericValue = Number.parseFloat(value) || 0;
    return `$${numericValue.toFixed(2)}`;
  };

  const calculateProfit = (reporte) => {
    const total = Number.parseFloat(reporte.total) || 0;
    const costoTotal = Number.parseFloat(reporte.costo_total) || 0;
    return total - costoTotal;
  };

  const formatDate = (date) => {
    if (!date) return 'Fecha no disponible';
    try {
      return format(new Date(date), 'MMM d, yyyy h:mm a', { locale: es });
    } catch (error) {
      console.error('Error al formatear la fecha:', error);
      return 'Fecha inválida';
    }
  };
  </script>

  <style scoped>
  /* Estilos adicionales */
  .container {
    max-width: 1200px;
  }

  .hover\:bg-gray-50:hover {
    background-color: #f9fafb;
  }
  </style>
