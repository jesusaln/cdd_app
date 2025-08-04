<template>
  <div class="data-table">
    <!-- Header con título, estadísticas y botón crear -->
    <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
      <div class="flex-1">
        <h2 class="text-3xl font-bold text-gray-900">{{ title }}</h2>
        <p v-if="description" class="text-gray-600 mt-1">{{ description }}</p>

        <!-- Estadísticas rápidas -->
        <div v-if="showStats" class="flex flex-wrap gap-4 mt-3">
          <div class="flex items-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Total: {{ data.length }}
          </div>
          <div v-if="filteredData.length !== data.length" class="flex items-center text-sm text-blue-600">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
            </svg>
            Filtrados: {{ filteredData.length }}
          </div>
          <div v-if="selectedItems.length > 0" class="flex items-center text-sm text-green-600">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Seleccionados: {{ selectedItems.length }}
          </div>
        </div>
      </div>

      <!-- Botones de acciones -->
      <div class="flex flex-col sm:flex-row gap-2">
        <!-- Acciones masivas -->
        <div v-if="bulkActions.length && selectedItems.length > 0" class="relative">
          <button
            @click="showBulkMenu = !showBulkMenu"
            class="inline-flex items-center px-4 py-2.5 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-all duration-200"
          >
            Acciones ({{ selectedItems.length }})
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown de acciones masivas -->
          <div v-if="showBulkMenu" v-click-outside="() => showBulkMenu = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border z-50">
            <div class="py-1">
              <button
                v-for="action in bulkActions"
                :key="action.key"
                @click="handleBulkAction(action.key)"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                :class="action.danger ? 'text-red-600 hover:bg-red-50' : ''"
              >
                <component v-if="action.icon" :is="action.icon" class="w-4 h-4 mr-3" />
                {{ action.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Botón exportar -->
        <button
          v-if="exportable"
          @click="exportData"
          class="inline-flex items-center px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Exportar
        </button>

        <!-- Botón crear -->
        <button
          v-if="createUrl"
          @click="handleCreate"
          class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ createLabel }}
        </button>
      </div>
    </div>

    <!-- Panel de controles avanzados -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
      <div class="flex flex-col space-y-4">
        <!-- Primera fila: Búsqueda y filtros principales -->
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
          <!-- Búsqueda avanzada -->
          <div class="relative flex-1 max-w-md">
            <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchTerm"
              type="text"
              :placeholder="searchPlaceholder"
              class="pl-10 pr-10 py-3 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              @keyup.enter="performSearch"
            />
            <button
              v-if="searchTerm"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Filtros -->
          <div class="flex flex-wrap gap-3">
            <!-- Filtro de estado -->
            <select
              v-if="statusOptions.length"
              v-model="filterStatus"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white min-w-[150px]"
            >
              <option value="">{{ statusLabelAll }}</option>
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>

            <!-- Filtros personalizados -->
            <select
              v-for="filter in customFilters"
              :key="filter.key"
              v-model="filterValues[filter.key]"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white min-w-[150px]"
            >
              <option value="">{{ filter.placeholder || `Todos ${filter.label}` }}</option>
              <option v-for="option in filter.options" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>

            <!-- Filtro de fecha -->
            <div v-if="dateFilter" class="flex gap-2">
              <input
                v-model="dateFrom"
                type="date"
                class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :placeholder="dateFilter.fromLabel || 'Desde'"
              />
              <input
                v-model="dateTo"
                type="date"
                class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :placeholder="dateFilter.toLabel || 'Hasta'"
              />
            </div>

            <!-- Ordenamiento -->
            <select
              v-model="sortBy"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white min-w-[150px]"
            >
              <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>

            <!-- Items por página -->
            <select
              v-if="paginated"
              v-model="itemsPerPage"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option v-for="size in pageSizes" :key="size" :value="size">
                {{ size }} por página
              </option>
            </select>
          </div>
        </div>

        <!-- Segunda fila: Indicadores de filtros activos -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 pt-2 border-t border-gray-100">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
            </svg>
            Filtros activos
          </span>

          <!-- Tags de filtros -->
          <button
            v-if="searchTerm"
            @click="clearSearch"
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 transition-colors"
          >
            Búsqueda: "{{ truncateText(searchTerm, 20) }}"
            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <button
            v-if="filterStatus"
            @click="filterStatus = ''"
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 transition-colors"
          >
            Estado: {{ getStatusLabel(filterStatus) }}
            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Filtros personalizados activos -->
          <button
            v-for="(value, key) in activeCustomFilters"
            :key="key"
            @click="clearCustomFilter(key)"
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 transition-colors"
          >
            {{ getCustomFilterLabel(key) }}: {{ getCustomFilterValueLabel(key, value) }}
            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Filtro de fecha activo -->
          <button
            v-if="dateFrom || dateTo"
            @click="clearDateFilter"
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 transition-colors"
          >
            Fecha: {{ formatDateRange }}
            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Botón limpiar todo -->
          <button
            @click="clearAllFilters"
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition-colors"
          >
            Limpiar todo
            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
      <div v-if="paginatedData.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
            <tr>
              <!-- Checkbox para seleccionar todo -->
              <th v-if="selectable" class="px-6 py-4 text-left">
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  :indeterminate="isIndeterminate"
                  @change="toggleSelectAll"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
              </th>

              <!-- Headers de columnas -->
              <th
                v-for="header in headers"
                :key="header.key"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                :class="{ 'cursor-pointer select-none': header.sortable }"
              >
                <button
                  v-if="header.sortable"
                  @click="toggleSort(header.key)"
                  class="flex items-center hover:text-gray-700 transition-colors group"
                >
                  {{ header.label }}
                  <div class="ml-2 flex flex-col">
                    <svg class="w-3 h-3 -mb-1" :class="getSortIconClass(header.key, 'asc')" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    <svg class="w-3 h-3" :class="getSortIconClass(header.key, 'desc')" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </button>
                <span v-else>{{ header.label }}</span>
              </th>

              <!-- Columna de acciones -->
              <th v-if="hasActions" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(item, index) in paginatedData"
              :key="item[idField] || index"
              class="hover:bg-blue-50 transition-colors duration-150"
              :class="{ 'bg-blue-25': selectedItems.includes(item[idField]) }"
            >
              <!-- Checkbox de selección -->
              <td v-if="selectable" class="px-6 py-4 whitespace-nowrap">
                <input
                  type="checkbox"
                  :checked="selectedItems.includes(item[idField])"
                  @change="toggleSelectItem(item[idField])"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
              </td>

              <!-- Celdas de datos -->
              <td
                v-for="header in headers"
                :key="header.key"
                class="px-6 py-4 whitespace-nowrap text-sm"
                :class="header.cellClass || 'text-gray-900'"
              >
                <slot
                  :name="`cell-${header.key}`"
                  :item="item"
                  :value="getValue(item, header.key)"
                  :index="index"
                >
                  <span v-if="header.type === 'badge'" :class="getBadgeClass(getValue(item, header.key))">
                    {{ getValue(item, header.key) }}
                  </span>
                  <span v-else-if="header.type === 'currency'">
                    {{ formatCurrency(getValue(item, header.key)) }}
                  </span>
              <span v-else-if="header.type === 'date'" v-html="formatDate(getValue(item, header.key), {
  format: 'stacked',
  showRelative: true
})"></span>
                  <span v-else-if="header.type === 'boolean'">
                    <span :class="getValue(item, header.key) ? 'text-green-600' : 'text-red-600'">
                      {{ getValue(item, header.key) ? 'Sí' : 'No' }}
                    </span>
                  </span>
                  <span v-else>
                    {{ getValue(item, header.key) }}
                  </span>
                </slot>
              </td>

              <!-- Celdas de acciones -->
              <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <slot name="actions" :item="item" :index="index">
                  <!-- Menú de acciones por defecto -->
                  <div class="relative inline-block text-left">
                    <button
                      @click="toggleActionMenu(index)"
                      class="inline-flex items-center p-2 text-gray-400 bg-white rounded-full hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                      </svg>
                    </button>
                  </div>
                </slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estado vacío mejorado -->
      <div v-else class="text-center py-16">
        <div class="mx-auto h-24 w-24 text-gray-300">
          <svg v-if="hasActiveFilters" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>

        <h3 class="mt-4 text-lg font-medium text-gray-900">
          {{ hasActiveFilters ? 'No se encontraron resultados' : 'No hay datos disponibles' }}
        </h3>

        <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
          {{ hasActiveFilters
            ? 'Intenta ajustar los filtros para encontrar lo que buscas.'
            : 'Comienza creando tu primer registro.'
          }}
        </p>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
          <button
            v-if="hasActiveFilters"
            @click="clearAllFilters"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Limpiar filtros
          </button>

          <button
            v-if="createUrl"
            @click="handleCreate"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ createLabel }}
          </button>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="paginated && filteredData.length > 0" class="bg-white px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="text-sm text-gray-700">
            Mostrando {{ startIndex + 1 }} a {{ Math.min(endIndex, filteredData.length) }} de {{ filteredData.length }} resultados
          </div>

          <div class="flex items-center space-x-2">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage <= 1"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>

            <div class="flex space-x-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                  page === currentPage
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
            </div>

            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage >= totalPages"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-8 rounded-xl shadow-2xl flex items-center space-x-4">
        <div class="animate-spin h-8 w-8 border-3 border-blue-600 border-t-transparent rounded-full"></div>
        <span class="text-gray-700 font-medium">{{ loadingText }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';

// Emits
const emit = defineEmits([
  'sort',
  'filter',
  'select',
  'bulk-action',
  'create',
  'export',
  'action'
]);

// Props
const props = defineProps({
  // Datos básicos
  title: { type: String, default: 'Datos' },
  description: { type: String, default: '' },
  data: { type: Array, required: true },
  headers: { type: Array, required: true },

  // Configuración de funcionalidades
  selectable: { type: Boolean, default: false },
  exportable: { type: Boolean, default: false },
  paginated: { type: Boolean, default: true },
  showStats: { type: Boolean, default: true },
  hasActions: { type: Boolean, default: false },

  // Filtros y búsqueda
  statusOptions: { type: Array, default: () => [] },
  statusLabelAll: { type: String, default: 'Todos los estados' },
  customFilters: { type: Array, default: () => [] },
  dateFilter: { type: Object, default: null },
  searchPlaceholder: { type: String, default: 'Buscar en todos los campos...' },

  // Ordenamiento
  sortOptions: { type: Array, default: () => [
    { value: 'created_at-desc', label: 'Más recientes' },
    { value: 'created_at-asc', label: 'Más antiguos' },
    { value: 'name-asc', label: 'Nombre A-Z' },
    { value: 'name-desc', label: 'Nombre Z-A' }
  ]},

  // Creación
  createUrl: { type: String, default: null },
  createLabel: { type: String, default: 'Crear nuevo' },

  // Acciones masivas
  bulkActions: { type: Array, default: () => [] },

  // Paginación
  pageSizes: { type: Array, default: () => [10, 25, 50, 100] },
  defaultPageSize: { type: Number, default: 25 },

  // Configuración
  idField: { type: String, default: 'id' },
  loading: { type: Boolean, default: false },
  loadingText: { type: String, default: 'Cargando...' }
});

// Estados reactivos
const searchTerm = ref('');
const filterStatus = ref('');
const filterValues = ref({});
const dateFrom = ref('');
const dateTo = ref('');
const sortBy = ref(props.sortOptions.length ? props.sortOptions[0].value : '');
const sortOrder = ref('asc');

// Paginación
const currentPage = ref(1);
const itemsPerPage = ref(props.defaultPageSize);

// Selección
const selectedItems = ref([]);
const showBulkMenu = ref(false);

// Watchers para resetear página al filtrar
watch([searchTerm, filterStatus, filterValues, dateFrom, dateTo], () => {
  currentPage.value = 1;
}, { deep: true });

// Computed: filtros activos
const hasActiveFilters = computed(() => {
  return searchTerm.value ||
         filterStatus.value ||
         Object.values(filterValues.value).some(v => v) ||
         dateFrom.value ||
         dateTo.value;
});

const activeCustomFilters = computed(() => {
  const active = {};
  Object.entries(filterValues.value).forEach(([key, value]) => {
    if (value) active[key] = value;
  });
  return active;
});

// Computed: datos filtrados
const filteredData = computed(() => {
  let result = [...props.data];

  // Búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    result = result.filter(item =>
      Object.values(item).some(val =>
        val && String(val).toLowerCase().includes(term)
      )
    );
  }

  // Filtro por estado
  if (filterStatus.value) {
    result = result.filter(item => item.estado === filterStatus.value);
  }

  // Filtros personalizados
  Object.entries(filterValues.value).forEach(([key, value]) => {
    if (value) {
      result = result.filter(item => getNestedValue(item, key) === value);
    }
  });

  // Filtro de fecha
  if (dateFrom.value || dateTo.value) {
    const dateField = props.dateFilter?.field || 'created_at';
    result = result.filter(item => {
      const itemDate = new Date(getNestedValue(item, dateField));
      const from = dateFrom.value ? new Date(dateFrom.value) : null;
      const to = dateTo.value ? new Date(dateTo.value) : null;

      if (from && itemDate < from) return false;
      if (to && itemDate > to) return false;
      return true;
    });
  }

  // Ordenamiento
  if (sortBy.value) {
    const [key, order] = sortBy.value.split('-');
    result.sort((a, b) => {
      const aVal = getNestedValue(a, key);
      const bVal = getNestedValue(b, key);

      // Manejo especial para números y fechas
      let comparison = 0;
      if (typeof aVal === 'number' && typeof bVal === 'number') {
        comparison = aVal - bVal;
      } else if (Date.parse(aVal) && Date.parse(bVal)) {
        comparison = new Date(aVal) - new Date(bVal);
      } else {
        comparison = String(aVal).localeCompare(String(bVal));
      }

      return order === 'desc' ? -comparison : comparison;
    });
  }

  return result;
});

// Computed: paginación
const totalPages = computed(() => {
  if (!props.paginated) return 1;
  return Math.ceil(filteredData.value.length / itemsPerPage.value);
});

const startIndex = computed(() => {
  if (!props.paginated) return 0;
  return (currentPage.value - 1) * itemsPerPage.value;
});

const endIndex = computed(() => {
  if (!props.paginated) return filteredData.value.length;
  return startIndex.value + itemsPerPage.value;
});

const paginatedData = computed(() => {
  if (!props.paginated) return filteredData.value;
  return filteredData.value.slice(startIndex.value, endIndex.value);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i);
      pages.push('...');
      pages.push(total);
    } else if (current >= total - 3) {
      pages.push(1);
      pages.push('...');
      for (let i = total - 4; i <= total; i++) pages.push(i);
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) pages.push(i);
      pages.push('...');
      pages.push(total);
    }
  }

  return pages.filter(page => page !== '...' || pages.indexOf(page) === pages.lastIndexOf(page));
});

// Computed: selección
const isAllSelected = computed(() => {
  if (paginatedData.value.length === 0) return false;
  return paginatedData.value.every(item => selectedItems.value.includes(item[props.idField]));
});

const isIndeterminate = computed(() => {
  const selected = paginatedData.value.filter(item => selectedItems.value.includes(item[props.idField]));
  return selected.length > 0 && selected.length < paginatedData.value.length;
});

// Métodos de utilidad
const getValue = (item, key) => {
  return getNestedValue(item, key);
};

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((acc, part) => acc?.[part], obj) || '';
};

const truncateText = (text, length) => {
  return text.length > length ? text.substring(0, length) + '...' : text;
};

// Métodos de formato
const formatCurrency = (value) => {
  if (!value) return '$0.00';
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR'
  }).format(value);
};

const formatDate = (value, options = {}) => {
  const {
    format = 'short',        // 'short', 'long', 'datetime', 'relative', 'stacked'
    showRelative = false,     // mostrar "· hace 3h" o "· ayer"
    fallback = '—'
  } = options;

  if (!value) return fallback;

  let date;
  try {
    date = new Date(value);
    if (isNaN(date)) throw new Error('Invalid date');
  } catch (e) {
    console.warn('Fecha inválida:', value);
    return fallback;
  }

  const now = new Date();
  const diffInHours = (now - date) / (1000 * 60 * 60);

  // Formateadores
  const formatters = {
    short: () => {
      return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: '2-digit' // 01/01/25
      });
    },
    long: () => {
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'long',
        year: 'numeric' // 1 de enero de 2025
      });
    },
    datetime: () => {
      const fecha = formatters.short();
      const hora = date.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
      return `${fecha} · ${hora}`; // 01/01/25 · 10:30 AM
    },
    stacked: () => {
      const fecha = formatters.short();
      const hora = date.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
      return `${fecha}<br><small class="text-gray-500 text-xs">${hora}</small>`;
    },
    relative: () => {
      if (diffInHours < 1) return 'ahora';
      if (diffInHours < 24) return `${Math.floor(diffInHours)}h`;
      if (diffInHours < 48) return 'ayer';
      return formatters.short();
    }
  };

  // Obtener formato base
  let base = formatters[format] ? formatters[format]() : formatters.short();

  // Añadir etiqueta relativa si se solicita y no es un formato especial
  if (showRelative && ['short', 'long', 'datetime'].includes(format)) {
    if (diffInHours < 1) {
      base += ' · ahora';
    } else if (diffInHours < 24) {
      base += ` · ${Math.floor(diffInHours)}h`;
    } else if (diffInHours < 48) {
      base += ' · ayer';
    }
  }

  return base;
};

const formatDateRange = computed(() => {
  if (dateFrom.value && dateTo.value) {
    return `${formatDate(dateFrom.value)} - ${formatDate(dateTo.value)}`;
  } else if (dateFrom.value) {
    return `Desde ${formatDate(dateFrom.value)}`;
  } else if (dateTo.value) {
    return `Hasta ${formatDate(dateTo.value)}`;
  }
  return '';
});

// Métodos de filtros
const getStatusLabel = (value) => {
  const option = props.statusOptions.find(opt => opt.value === value);
  return option ? option.label : value;
};

const getCustomFilterLabel = (key) => {
  const filter = props.customFilters.find(f => f.key === key);
  return filter ? filter.label : key;
};

const getCustomFilterValueLabel = (key, value) => {
  const filter = props.customFilters.find(f => f.key === key);
  if (!filter) return value;
  const option = filter.options.find(opt => opt.value === value);
  return option ? option.label : value;
};

const getBadgeClass = (value) => {
  const classes = {
    'activo': 'inline-flex px-2 py-1 text-xs rounded-full bg-green-100 text-green-800',
    'inactivo': 'inline-flex px-2 py-1 text-xs rounded-full bg-red-100 text-red-800',
    'pendiente': 'inline-flex px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800',
    'completado': 'inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800'
  };
  return classes[value] || 'inline-flex px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800';
};

// Métodos de ordenamiento
const toggleSort = (key) => {
  const current = sortBy.value;
  const order = current === `${key}-asc` ? 'desc' : 'asc';
  sortBy.value = `${key}-${order}`;
  emit('sort', { key, order });
};

const getSortIconClass = (key, direction) => {
  const current = sortBy.value;
  const isActive = current === `${key}-${direction}`;
  return isActive ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600';
};

// Métodos de selección
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    // Deseleccionar todos los de la página actual
    const currentIds = paginatedData.value.map(item => item[props.idField]);
    selectedItems.value = selectedItems.value.filter(id => !currentIds.includes(id));
  } else {
    // Seleccionar todos los de la página actual
    const currentIds = paginatedData.value.map(item => item[props.idField]);
    selectedItems.value = [...new Set([...selectedItems.value, ...currentIds])];
  }
  emit('select', selectedItems.value);
};

const toggleSelectItem = (id) => {
  const index = selectedItems.value.indexOf(id);
  if (index > -1) {
    selectedItems.value.splice(index, 1);
  } else {
    selectedItems.value.push(id);
  }
  emit('select', selectedItems.value);
};

// Métodos de acciones
const handleCreate = () => {
  emit('create');
  if (props.createUrl && window.$inertia) {
    window.$inertia.get(props.createUrl);
  }
};

const handleBulkAction = (actionKey) => {
  emit('bulk-action', {
    action: actionKey,
    items: selectedItems.value
  });
  showBulkMenu.value = false;
};

const exportData = () => {
  emit('export', filteredData.value);

  // Exportación CSV básica por defecto
  if (!emit.export) {
    const csv = convertToCSV(filteredData.value);
    downloadCSV(csv, `${props.title}_export.csv`);
  }
};

const convertToCSV = (data) => {
  if (!data.length) return '';

  const headers = props.headers.map(h => h.label).join(',');
  const rows = data.map(item =>
    props.headers.map(h => {
      const value = getValue(item, h.key);
      return `"${String(value).replace(/"/g, '""')}"`;
    }).join(',')
  ).join('\n');

  return `${headers}\n${rows}`;
};

const downloadCSV = (csv, filename) => {
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Métodos de paginación
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Métodos de limpieza
const clearSearch = () => {
  searchTerm.value = '';
};

const clearCustomFilter = (key) => {
  filterValues.value[key] = '';
};

const clearDateFilter = () => {
  dateFrom.value = '';
  dateTo.value = '';
};

const clearAllFilters = () => {
  searchTerm.value = '';
  filterStatus.value = '';
  filterValues.value = {};
  dateFrom.value = '';
  dateTo.value = '';
  currentPage.value = 1;
  emit('filter', { cleared: true });
};

const performSearch = () => {
  currentPage.value = 1;
  emit('filter', { search: searchTerm.value });
};

// Inicialización de filtros personalizados
if (props.customFilters.length) {
  props.customFilters.forEach(filter => {
    filterValues.value[filter.key] = '';
  });
}

// Directiva para cerrar menús al hacer clic fuera
const clickOutside = {
  beforeMount(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  }
};

// Registrar directiva
const vClickOutside = clickOutside;
</script>

<style scoped>
.data-table {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 1.5rem;
}

/* Animaciones personalizadas */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.data-table > * {
  animation: fadeIn 0.3s ease-out;
}

/* Estados de hover mejorados */
.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Scrollbar personalizada */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Efectos de transición suaves */
* {
  transition: all 0.2s ease;
}

/* Mejoras visuales para badges */
.inline-flex.px-2.py-1 {
  font-weight: 600;
  letter-spacing: 0.025em;
}

/* Estilos para estados de carga */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Mejoras para botones */
button:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Estados de selección */
.bg-blue-25 {
  background-color: #eff6ff;
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
