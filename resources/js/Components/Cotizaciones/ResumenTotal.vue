<template>
  <div class="bg-white rounded-2xl border border-gray-200 shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl">
    <!-- Header with animated gradient -->
    <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-600 px-6 py-5 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-purple-600/90 to-indigo-600/90 animate-pulse"></div>
      <div class="relative z-10">
        <h2 class="text-xl font-bold text-white flex items-center">
          <div class="p-2 bg-white/20 rounded-lg mr-3 backdrop-blur-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          Resumen de la Cotización
        </h2>
        <p class="text-purple-100 text-sm mt-1 opacity-90">
          Detalles completos de tu solicitud
        </p>
      </div>
    </div>

    <div class="p-8">
      <!-- Key Metrics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Items Card -->
        <div class="group relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl opacity-10 group-hover:opacity-20 transition-opacity duration-300"></div>
          <div class="relative text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:border-blue-300 transition-all duration-300 transform hover:scale-105">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
            </div>
            <div class="text-4xl font-bold text-blue-700 mb-2 transition-all duration-300">
              {{ selectedProducts.length }}
            </div>
            <div class="text-sm text-blue-700 font-semibold uppercase tracking-wide">
              Items Seleccionados
            </div>
          </div>
        </div>

        <!-- Quantity Card -->
        <div class="group relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl opacity-10 group-hover:opacity-20 transition-opacity duration-300"></div>
          <div class="relative text-center p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl border border-emerald-200 hover:border-emerald-300 transition-all duration-300 transform hover:scale-105">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-emerald-100 rounded-full mb-3">
              <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
              </svg>
            </div>
            <div class="text-4xl font-bold text-emerald-700 mb-2 transition-all duration-300">
              {{ Object.values(quantities).reduce((sum, qty) => sum + (qty || 0), 0) }}
            </div>
            <div class="text-sm text-emerald-700 font-semibold uppercase tracking-wide">
              Cantidad Total
            </div>
          </div>
        </div>

        <!-- Total Card -->
        <div class="group relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl opacity-10 group-hover:opacity-20 transition-opacity duration-300"></div>
          <div class="relative text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 hover:border-purple-300 transition-all duration-300 transform hover:scale-105">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-3">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
            </div>
            <div class="text-4xl font-bold text-purple-700 mb-2 transition-all duration-300">
              ${{ formatCurrency(totales.total) }}
            </div>
            <div class="text-sm text-purple-700 font-semibold uppercase tracking-wide">
              Total Final
            </div>
          </div>
        </div>
      </div>

      <!-- Price Breakdown -->
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8 border border-gray-200">
        <div class="flex items-center mb-6">
          <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900">Desglose de Precios</h3>
        </div>

        <div class="space-y-4">
          <!-- Subtotal -->
          <div class="flex justify-between items-center py-3 px-4 bg-white rounded-xl border border-gray-200 hover:border-gray-300 transition-colors duration-200">
            <span class="text-gray-700 font-medium flex items-center">
              <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
              Subtotal:
            </span>
            <span class="font-bold text-gray-900 text-lg">
              ${{ formatCurrency(totales.subtotal) }}
            </span>
          </div>

          <!-- General Discount -->
          <div v-if="totales.descuentoGeneral > 0"
               class="flex justify-between items-center py-3 px-4 bg-orange-50 rounded-xl border border-orange-200 hover:border-orange-300 transition-colors duration-200">
            <span class="text-orange-700 font-medium flex items-center">
              <span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>
              Descuento General ({{ descuentoGeneral }}%):
            </span>
            <span class="font-bold text-orange-700 text-lg">
              -${{ formatCurrency(totales.descuentoGeneral) }}
            </span>
          </div>

          <!-- Item Discounts -->
          <div v-if="totales.descuentoItems > 0"
               class="flex justify-between items-center py-3 px-4 bg-orange-50 rounded-xl border border-orange-200 hover:border-orange-300 transition-colors duration-200">
            <span class="text-orange-700 font-medium flex items-center">
              <span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>
              Descuentos por item:
            </span>
            <span class="font-bold text-orange-700 text-lg">
              -${{ formatCurrency(totales.descuentoItems) }}
            </span>
          </div>

          <!-- Subtotal with Discounts -->
          <div class="flex justify-between items-center py-3 px-4 bg-blue-50 rounded-xl border border-blue-200 hover:border-blue-300 transition-colors duration-200">
            <span class="text-blue-700 font-medium flex items-center">
              <span class="w-2 h-2 bg-blue-500 rounded-full mr-3"></span>
              Subtotal con descuentos:
            </span>
            <span class="font-bold text-blue-700 text-lg">
              ${{ formatCurrency(totales.subtotalConDescuentos) }}
            </span>
          </div>

          <!-- IVA -->
          <div class="flex justify-between items-center py-3 px-4 bg-indigo-50 rounded-xl border border-indigo-200 hover:border-indigo-300 transition-colors duration-200">
            <span class="text-indigo-700 font-medium flex items-center">
              <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
              IVA (16%):
            </span>
            <span class="font-bold text-indigo-700 text-lg">
              ${{ formatCurrency(totales.iva) }}
            </span>
          </div>

          <!-- Final Total -->
          <div class="border-t-2 border-gray-300 pt-4 mt-6">
            <div class="flex justify-between items-center py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl text-white shadow-lg">
              <span class="text-xl font-bold flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                Total Final:
              </span>
              <span class="text-2xl font-bold">
                ${{ formatCurrency(totales.total) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Info -->
      <div v-if="showAdditionalInfo" class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="text-sm text-blue-700">
            <p class="font-semibold mb-1">Información adicional:</p>
            <p>Los precios incluyen IVA del 16%. La validez de esta cotización es de 30 días a partir de la fecha de emisión.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  selectedProducts: {
    type: Array,
    default: () => []
  },
  quantities: {
    type: Object,
    default: () => ({})
  },
  totales: {
    type: Object,
    default: () => ({
      subtotal: 0,
      descuentoGeneral: 0,
      descuentoItems: 0,
      subtotalConDescuentos: 0,
      iva: 0,
      total: 0
    })
  },
  descuentoGeneral: {
    type: Number,
    default: 0
  },
  showAdditionalInfo: {
    type: Boolean,
    default: true
  }
});

// Helper function for currency formatting
const formatCurrency = (amount) => {
  if (typeof amount !== 'number' || isNaN(amount)) return '0.00';
  return amount.toLocaleString('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

// Computed properties for better reactivity
const totalItems = computed(() => props.selectedProducts.length);
const totalQuantity = computed(() =>
  Object.values(props.quantities).reduce((sum, qty) => sum + (qty || 0), 0)
);
const hasDiscounts = computed(() =>
  props.totales.descuentoGeneral > 0 || props.totales.descuentoItems > 0
);
</script>

<style scoped>
/* Custom animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

.animate-pulse {
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: transform, opacity, border-color, background-color, color;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar for overflow content */
.overflow-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Enhanced focus styles for accessibility */
button:focus,
input:focus,
select:focus {
  outline: 2px solid #6366f1;
  outline-offset: 2px;
}
</style>
