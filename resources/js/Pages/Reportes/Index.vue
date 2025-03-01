<template>
    <Head title="Reportes" />
    <div class="container mx-auto p-6">
      <h1 class="text-2xl font-semibold mb-4">Reportes</h1>

      <!-- Pestañas para navegar entre diferentes reportes -->
      <div class="mb-4 space-x-2">
        <button
          @click="activeTab = 'ventas'"
          :class="{'bg-blue-500 text-white': activeTab === 'ventas', 'bg-gray-300': activeTab !== 'ventas'}"
          class="px-4 py-2 rounded-l-lg"
        >
          Ventas
        </button>
        <button
          @click="activeTab = 'compras'"
          :class="{'bg-blue-500 text-white': activeTab === 'compras', 'bg-gray-300': activeTab !== 'compras'}"
          class="px-4 py-2"
        >
          Compras
        </button>
        <button
          @click="activeTab = 'inventarios'"
          :class="{'bg-blue-500 text-white': activeTab === 'inventarios', 'bg-gray-300': activeTab !== 'inventarios'}"
          class="px-4 py-2 rounded-r-lg"
        >
          Inventarios
        </button>
      </div>

      <!-- Contenido de las pestañas -->
      <div v-if="activeTab === 'ventas'">
        <h2 class="text-lg font-semibold mb-2">Reporte de Ventas</h2>
        <div class="mb-6 space-y-2">
          <p class="text-lg">Corte Total: {{ formatCurrency(corteVentas) }}</p>
          <p class="text-lg">Utilidad Total: {{ formatCurrency(utilidadVentas) }}</p>
        </div>
        <!-- Tabla de Ventas -->
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left">ID Venta</th>
                <th class="px-4 py-2 text-left">Total Venta</th>
                <th class="px-4 py-2 text-left">Costo Total</th>
                <th class="px-4 py-2 text-left">Utilidad</th>
                <th class="px-4 py-2 text-left">Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="venta in reportesVentas" :key="venta.id" class="border-t hover:bg-gray-50 transition-colors">
                <td class="px-4 py-2">{{ venta.id }}</td>
                <td class="px-4 py-2">{{ formatCurrency(venta.total) }}</td>
                <td class="px-4 py-2">{{ formatCurrency(venta.costo_total) }}</td>
                <td class="px-4 py-2">{{ formatCurrency(calculateProfit(venta)) }}</td>
                <td class="px-4 py-2">{{ formatDate(venta.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="activeTab === 'compras'">
        <h2 class="text-lg font-semibold mb-2">Reporte de Compras</h2>
        <p class="text-lg">Total Compras: {{ formatCurrency(totalCompras) }}</p>
        <!-- Tabla de Compras -->
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left">ID Compra</th>
                <th class="px-4 py-2 text-left">Total Compra</th>
                <th class="px-4 py-2 text-left">Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="compra in reportesCompras" :key="compra.id" class="border-t hover:bg-gray-50 transition-colors">
                <td class="px-4 py-2">{{ compra.id }}</td>
                <td class="px-4 py-2">{{ formatCurrency(compra.total) }}</td>
                <td class="px-4 py-2">{{ formatDate(compra.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="activeTab === 'inventarios'">
        <h2 class="text-lg font-semibold mb-2">Reporte de Inventarios</h2>
        <!-- Tabla de Inventarios -->
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left">ID Producto</th>
                <th class="px-4 py-2 text-left">Nombre</th>
                <th class="px-4 py-2 text-left">Stock</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inventario in reportesInventarios" :key="inventario.id" class="border-t hover:bg-gray-50 transition-colors">
                <td class="px-4 py-2">{{ inventario.id }}</td>
                <td class="px-4 py-2">{{ inventario.nombre }}</td>
                <td class="px-4 py-2">{{ inventario.stock }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue';
  import { Head } from '@inertiajs/vue3';
  import Dashboard from '@/Pages/Dashboard.vue';
  import { format } from 'date-fns';
  import { es } from 'date-fns/locale';

  defineOptions({ layout: Dashboard });

  const props = defineProps({
    reportesVentas: {
      type: Array,
      default: () => [],
    },
    corteVentas: {
      type: Number,
      default: 0,
    },
    utilidadVentas: {
      type: Number,
      default: 0,
    },
    reportesCompras: {
      type: Array,
      default: () => [],
    },
    totalCompras: {
      type: Number,
      default: 0,
    },
    reportesInventarios: {
      type: Array,
      default: () => [],
    },
  });

  const activeTab = ref('ventas');

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
  .container {
    max-width: 1200px;
  }

  .hover\:bg-gray-50:hover {
    background-color: #f9fafb;
  }
  </style>
