<template>
  <Head title="Productos Más Vendidos" />

  <div class="min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-6 py-8">
      <!-- Header Principal -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl shadow-xl p-8 mb-8 text-white">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <div class="p-3 bg-white/20 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
              <div>
                <h1 class="text-3xl font-bold">Productos Más Vendidos</h1>
                <p class="text-blue-100 text-lg">Análisis detallado de rendimiento y ganancias</p>
              </div>
            </div>
          </div>

          <div class="flex gap-3">
            <Link
              href="/reportes"
              class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition-all duration-200 border border-white/20"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Dashboard
            </Link>
          </div>
        </div>
      </div>

      <!-- Panel de Control -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8">
        <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">
          <!-- Filtros -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio</label>
              <input
                v-model="filtros.fecha_inicio"
                type="date"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                @change="filtrar"
                placeholder="Sin límite"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Fin</label>
              <input
                v-model="filtros.fecha_fin"
                type="date"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                @change="filtrar"
                placeholder="Sin límite"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Categoría</label>
              <select
                v-model="filtros.categoria_id"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                @change="filtrar"
              >
                <option value="">Todas las categorías</option>
                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                  {{ categoria.nombre }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Marca</label>
              <select
                v-model="filtros.marca_id"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                @change="filtrar"
              >
                <option value="">Todas las marcas</option>
                <option v-for="marca in marcas" :key="marca.id" :value="marca.id">
                  {{ marca.nombre }}
                </option>
              </select>
            </div>
          </div>

          <!-- Acciones -->
          <div class="flex gap-3">
            <button
              @click="limpiarFiltros"
              class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Limpiar Filtros
            </button>
            <button
              @click="exportar"
              class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Exportar Excel
            </button>
          </div>
        </div>
      </div>

      <!-- KPIs Principales -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Total Productos</p>
              <p class="text-3xl font-bold text-gray-900">{{ formatNumber(estadisticas.total_productos) }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-xl">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Productos Activos</p>
              <p class="text-3xl font-bold text-green-600">{{ formatNumber(estadisticas.productos_vendidos) }}</p>
              <p class="text-xs text-green-600 mt-1">
                {{ estadisticas.total_productos > 0 ? Math.round((estadisticas.productos_vendidos / estadisticas.total_productos) * 100) : 0 }}% del total
              </p>
            </div>
            <div class="p-3 bg-green-100 rounded-xl">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Ingresos Totales</p>
              <p class="text-3xl font-bold text-yellow-600">{{ formatCurrency(estadisticas.total_ingresos) }}</p>
              <p class="text-xs text-gray-500 mt-1">Por ventas realizadas</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-xl">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Ganancias Netas</p>
              <p class="text-3xl font-bold text-purple-600">{{ formatCurrency(estadisticas.total_ganancias) }}</p>
              <p class="text-xs text-purple-600 mt-1">
                {{ estadisticas.total_ingresos > 0 ? Math.round((estadisticas.total_ganancias / estadisticas.total_ingresos) * 100) : 0 }}% margen
              </p>
            </div>
            <div class="p-3 bg-purple-100 rounded-xl">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de Productos -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900">Ranking de Productos</h3>
          <p class="text-sm text-gray-600">Ordenados por volumen de ventas</p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ranking</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoría</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unidades</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ingresos</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ganancia</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Margen</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock Actual</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
              <tr
                v-for="(producto, index) in productos"
                :key="producto.id"
                :class="[
                  'hover:bg-gray-50 transition-colors duration-150',
                  index < 3 ? 'bg-gradient-to-r from-yellow-50 to-transparent' : ''
                ]"
              >
                <!-- Ranking con medalla -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div :class="[
                      'flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm',
                      index === 0 ? 'bg-yellow-400 text-yellow-900' :
                      index === 1 ? 'bg-gray-300 text-gray-800' :
                      index === 2 ? 'bg-orange-400 text-orange-900' :
                      'bg-gray-100 text-gray-600'
                    ]">
                      {{ index + 1 }}
                    </div>
                  </div>
                </td>

                <!-- Nombre del producto -->
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                      </svg>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ producto.nombre }}</div>
                      <div class="text-sm text-gray-500">{{ producto.codigo }}</div>
                    </div>
                  </div>
                </td>

                <!-- Categoría -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ producto.categoria || 'Sin categoría' }}
                  </span>
                </td>

                <!-- Stock -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center">
                    <span :class="[
                      producto.stock <= (producto.stock_minimo || 0) ? 'text-red-600' :
                      producto.stock <= (producto.stock_minimo || 0) * 2 ? 'text-yellow-600' :
                      'text-green-600'
                    ]">
                      {{ formatNumber(producto.stock) }}
                    </span>
                    <span v-if="producto.stock <= (producto.stock_minimo || 0)" class="ml-2">
                      <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                      </svg>
                    </span>
                  </div>
                </td>

                <!-- Precio -->
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ formatCurrency(producto.precio_venta) }}
                </td>

                <!-- Unidades vendidas -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatNumber(producto.cantidad_vendida) }}</div>
                  <div class="text-xs text-gray-500">{{ producto.numero_ventas }} ventas</div>
                </td>

                <!-- Ingresos -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-green-600">{{ formatCurrency(producto.total_vendido) }}</div>
                </td>

                <!-- Ganancia -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div :class="[
                    'text-sm font-medium',
                    producto.ganancia > 0 ? 'text-blue-600' : 'text-red-600'
                  ]">
                    {{ formatCurrency(producto.ganancia) }}
                  </div>
                </td>

                <!-- Margen de ganancia -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                      <div
                        :class="[
                          'h-2 rounded-full',
                          calcularMargen(producto) >= 30 ? 'bg-green-500' :
                          calcularMargen(producto) >= 15 ? 'bg-yellow-500' :
                          'bg-red-500'
                        ]"
                        :style="{ width: Math.min(calcularMargen(producto), 100) + '%' }"
                      ></div>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ calcularMargen(producto) }}%</span>
                  </div>
                </td>

                <!-- Stock Actual -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatNumber(producto.stock) }}</div>
                  <div class="text-xs text-gray-500">Actualizado automáticamente</div>
                </td>
              </tr>

              <!-- Mensaje cuando no hay productos -->
              <tr v-if="productos.length === 0">
                <td colspan="10" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center space-y-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                      </svg>
                    </div>
                    <div class="space-y-1">
                      <p class="text-gray-700 font-medium">No hay productos vendidos</p>
                      <p class="text-sm text-gray-500">No se encontraron ventas en el período seleccionado</p>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    productos: Array,
    estadisticas: Object,
    categorias: Array,
    marcas: Array,
    filtros: Object,
});

const filtros = ref({ ...props.filtros });

// Funciones de formato
const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value || 0);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('es-ES').format(value || 0);
};

// Calcular margen de ganancia
const calcularMargen = (producto) => {
    if (producto.total_vendido <= 0) return 0;
    return Math.round((producto.ganancia / producto.total_vendido) * 100);
};

// Funciones de navegación
const filtrar = () => {
    router.get(route('reportes.productos'), filtros.value, {
        preserveState: true,
        replace: true,
    });
};

const limpiarFiltros = () => {
    filtros.value = {
        fecha_inicio: '',
        fecha_fin: '',
        categoria_id: '',
        marca_id: ''
    };
    router.get(route('reportes.productos'), filtros.value, {
        preserveState: true,
        replace: true,
    });
};

const exportar = () => {
    window.open(route('reportes.productos.export', filtros.value), '_blank');
};
</script>
