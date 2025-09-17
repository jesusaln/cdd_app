<template>
  <div class="relative">
    <!-- Campo de búsqueda -->
    <div v-if="!proveedorSeleccionado" class="buscar-proveedor">
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          {{ labelBusqueda }}
          <span v-if="requerido" class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input
            ref="inputBusqueda"
            type="text"
            v-model="busquedaProveedor"
            @input="handleInput"
            @focus="handleFocus"
            @blur="ocultarListaConRetraso"
            @keydown="manejarTeclas"
            :placeholder="placeholderBusqueda"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200"
            :class="{
              'border-red-300 focus:ring-red-500 focus:border-red-500': errorBusqueda || (requerido && validacionError),
              'pl-10': iconoBusqueda
            }"
            autocomplete="new-password"
            :disabled="deshabilitado"
          />
          <!-- Icono de búsqueda -->
          <div v-if="iconoBusqueda" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <!-- Indicador de carga / limpiar -->
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button
              v-if="busquedaProveedor && !cargandoBusqueda"
              @click="limpiarBusqueda"
              type="button"
              class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors duration-200"
              title="Limpiar búsqueda"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
            <div v-else-if="cargandoBusqueda" class="animate-spin w-5 h-5 border-2 border-green-500 border-t-transparent rounded-full"></div>
            <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
        </div>
        <!-- Mensajes de error y ayuda -->
        <div v-if="errorBusqueda || (requerido && touched && validacionError)" class="mt-2 text-sm text-red-600">
          {{ errorBusqueda || validacionError }}
        </div>
        <div v-else-if="mensajeAyuda && !busquedaProveedor" class="mt-2 text-sm text-gray-500">
          {{ mensajeAyuda }}
        </div>
        <div v-if="busquedaProveedor && !mostrarListaProveedores && proveedoresFiltrados.length === 0 && !cargandoBusqueda" class="mt-2 text-sm text-gray-500">
          No se encontraron proveedores que coincidan con "{{ busquedaProveedor }}"
        </div>
        <!-- Filtros rápidos -->
        <div v-if="mostrarFiltrosRapidos && !proveedorSeleccionado" class="mt-3 flex flex-wrap gap-2">
          <button
            v-for="filtro in filtrosRapidos"
            :key="filtro.value"
            @click="aplicarFiltroRapido(filtro)"
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors duration-200"
            :class="filtroActivo === filtro.value
              ? 'bg-green-100 text-green-800 border border-green-300'
              : 'bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200'"
          >
            <component :is="filtro.icon" class="w-3 h-3 mr-1" v-if="filtro.icon"/>
            {{ filtro.label }}
          </button>
        </div>
      </div>
    </div>
    <!-- Información del proveedor seleccionado -->
    <div v-if="proveedorSeleccionado" class="mt-6 p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg shadow-sm">
      <div class="flex items-start justify-between mb-4">
        <h3 class="text-lg font-semibold text-green-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ tituloProveedorSeleccionado }}
        </h3>
        <div class="flex items-center space-x-2">
          <!-- Botón de historial -->
          <button
            v-if="mostrarHistorial"
            @click="verHistorial"
            type="button"
            class="text-green-500 hover:text-green-700 hover:bg-green-100 p-1 rounded-full transition-colors duration-200"
            title="Ver historial"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </button>
          <!-- Botón de editar -->
          <button
            v-if="mostrarEditar"
            @click="editarProveedor"
            type="button"
            class="text-blue-500 hover:text-blue-700 hover:bg-blue-100 p-1 rounded-full transition-colors duration-200"
            title="Editar proveedor"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <!-- Botón cambiar proveedor -->
          <button
            type="button"
            @click="limpiarProveedor"
            class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded-full transition-colors duration-200"
            :title="`Cambiar ${tipoDocumento.toLowerCase()}`"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
      <!-- Información básica del proveedor -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="space-y-2">
          <div class="flex items-center text-sm font-medium text-green-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Razón Social
          </div>
          <div class="text-lg font-semibold text-gray-900">{{ proveedorSeleccionado.nombre_razon_social }}</div>
        </div>
        <div class="space-y-2" v-if="proveedorSeleccionado.email">
          <div class="flex items-center text-sm font-medium text-green-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Email
          </div>
          <div class="text-gray-900">{{ proveedorSeleccionado.email }}</div>
        </div>
        <div class="space-y-2" v-if="proveedorSeleccionado.telefono">
          <div class="flex items-center text-sm font-medium text-green-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            Teléfono
          </div>
          <div class="text-gray-900">{{ proveedorSeleccionado.telefono }}</div>
        </div>
        <div class="space-y-2" v-if="proveedorSeleccionado.rfc">
          <div class="flex items-center text-sm font-medium text-green-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            RFC
          </div>
          <div class="text-gray-900 font-mono">{{ proveedorSeleccionado.rfc }}</div>
        </div>
        <!-- Estado del proveedor -->
        <div class="space-y-2" v-if="mostrarEstadoProveedor">
          <div class="flex items-center text-sm font-medium text-green-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Estado
          </div>
          <div class="flex items-center">
            <div
              class="w-3 h-3 rounded-full mr-2"
              :class="{
                'bg-green-500': proveedorSeleccionado.estado === 'activo',
                'bg-yellow-500': proveedorSeleccionado.estado === 'suspendido',
                'bg-red-500': proveedorSeleccionado.estado === 'inactivo',
                'bg-gray-500': !proveedorSeleccionado.estado
              }"
            ></div>
            <span class="text-gray-900 font-medium capitalize">
              {{ proveedorSeleccionado.estado || 'Pendiente' }}
            </span>
          </div>
        </div>
          <!-- Dirección completa -->
          <div class="space-y-2" v-if="direccionCompleta">
            <div class="flex items-center text-sm font-medium text-green-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Dirección
            </div>
            <div class="text-gray-900 text-sm">{{ direccionCompleta }}</div>
          </div>
          <!-- Información comercial -->
          <div class="space-y-2" v-if="proveedorSeleccionado.credito_limite && mostrarInfoComercial">
            <div class="flex items-center text-sm font-medium text-green-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              Límite de Crédito
            </div>
            <div class="text-gray-900 font-semibold">{{ formatearMoneda(proveedorSeleccionado.credito_limite) }}</div>
          </div>
          <div class="space-y-2" v-if="proveedorSeleccionado.tipo_proveedor">
            <div class="flex items-center text-sm font-medium text-green-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
              Tipo de Proveedor
            </div>
            <div class="text-gray-900 capitalize">{{ proveedorSeleccionado.tipo_proveedor }}</div>
          </div>
      </div>
      <!-- Alertas del proveedor -->
      <div v-if="alertasProveedor.length > 0" class="mt-4 space-y-2">
        <div
          v-for="alerta in alertasProveedor"
          :key="alerta.id"
          class="flex items-center p-3 rounded-lg text-sm"
          :class="{
            'bg-red-50 text-red-800 border border-red-200': alerta.tipo === 'error',
            'bg-yellow-50 text-yellow-800 border border-yellow-200': alerta.tipo === 'warning',
            'bg-blue-50 text-blue-800 border border-blue-200': alerta.tipo === 'info'
          }"
        >
          <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          {{ alerta.mensaje }}
        </div>
      </div>
    </div>
    <!-- Estado vacío mejorado -->
    <div v-else class="mt-6 p-6 border-2 border-dashed border-gray-300 rounded-lg text-center">
      <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
      </svg>
      <p class="text-gray-500 text-lg font-medium">{{ mensajeVacio }}</p>
      <p class="text-gray-400 text-sm mt-1">{{ submensajeVacio }}</p>
      <!-- Botón de acción rápida -->
      <div v-if="mostrarAccionRapida" class="mt-4">
        <button
          @click="crearNuevoProveedor"
          class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Crear Nuevo Proveedor
        </button>
      </div>
    </div>
    <!-- Lista de proveedores filtrados usando Teleport -->
    <Teleport to="#app">
      <div
        ref="listaProveedoresRef"
        v-if="mostrarListaProveedores && proveedoresFiltrados.length > 0"
        class="z-[9999] mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-[60vh] overflow-y-auto pointer-events-auto"
        :style="{
          position: 'absolute',
          width: inputWidth + 'px',
          top: inputPosition.top + inputPosition.height + 'px',
          left: inputPosition.left + 'px',
          pointerEvents: 'auto'
        }"
      >
        <div class="p-2">
          <div class="text-xs text-gray-500 mb-2 px-2 flex items-center justify-between">
            <span>{{ proveedoresFiltrados.length }} proveedor{{ proveedoresFiltrados.length !== 1 ? 'es' : '' }} encontrado{{ proveedoresFiltrados.length !== 1 ? 's' : '' }}</span>
            <span v-if="tiempoRespuesta" class="text-gray-400">({{ tiempoRespuesta }}ms)</span>
          </div>
        </div>
        <div
          v-for="(proveedor, index) in proveedoresFiltrados"
          :key="proveedor.id"
          @click="seleccionarProveedor(proveedor, $event)"
          @mouseenter="proveedorSeleccionadoIndex = index"
          class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150 pointer-events-auto"
          :class="{ 'bg-green-50': proveedorSeleccionadoIndex === index }"
          :style="{ pointerEvents: 'auto' }"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="font-medium text-gray-900 mb-1 flex items-center">
                <span v-html="resaltarTexto(proveedor.nombre_razon_social, busquedaProveedor)"></span>
                <!-- Indicadores de estado -->
                <div class="ml-2 flex items-center space-x-1">
                  <div v-if="proveedor.estado"
                       class="w-2 h-2 rounded-full"
                       :class="{
                         'bg-green-500': proveedor.estado === 'activo',
                         'bg-yellow-500': proveedor.estado === 'suspendido',
                         'bg-red-500': proveedor.estado === 'inactivo'
                       }"
                       :title="proveedor.estado">
                  </div>
                </div>
              </div>
              <div class="text-sm text-gray-500 space-y-1">
                <div v-if="proveedor.email" class="flex items-center" v-html="resaltarTexto(proveedor.email, busquedaProveedor)"></div>
                <div v-if="proveedor.telefono" class="flex items-center" v-html="resaltarTexto(proveedor.telefono, busquedaProveedor)"></div>
                <div v-if="proveedor.rfc" class="flex items-center font-mono text-xs" v-html="resaltarTexto(proveedor.rfc, busquedaProveedor)"></div>
                <!-- Tipo de proveedor -->
                <div v-if="proveedor.tipo_proveedor" class="text-xs text-green-600 capitalize">
                  Tipo: {{ proveedor.tipo_proveedor }}
                </div>
              </div>
            </div>
            <div v-if="proveedor.categoria" class="ml-3">
              <div class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full whitespace-nowrap" v-html="resaltarTexto(proveedor.categoria, busquedaProveedor)"></div>
            </div>
          </div>
        </div>
        <!-- Opción para crear nuevo proveedor -->
        <div v-if="busquedaProveedor && mostrarOpcionNuevoProveedor" class="border-t border-gray-200 p-3 bg-gray-50">
          <button
            @click="crearNuevoProveedor"
            class="w-full text-left px-3 py-2 text-sm text-green-600 hover:text-green-800 hover:bg-green-50 rounded-md transition-colors duration-150 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Crear nuevo proveedor: "{{ busquedaProveedor }}"
          </button>
        </div>
      </div>
      <!-- Lista vacía -->
      <div
        ref="listaVaciaRef"
        v-if="mostrarListaProveedores && proveedoresFiltrados.length === 0 && busquedaTermino"
        class="z-[9999] mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4"
        :style="{
          position: 'absolute',
          width: inputWidth + 'px',
          top: inputPosition.top + inputPosition.height + 'px',
          left: inputPosition.left + 'px'
        }"
      >
        <div class="text-center text-gray-500">
          <p class="text-sm font-medium mb-1">No se encontraron proveedores</p>
          <p class="text-xs text-gray-400 mb-3">Intenta con otro término de búsqueda</p>
          <button
            @click="crearNuevoProveedor"
            class="inline-flex items-center px-3 py-2 text-sm text-green-600 hover:text-green-800 hover:bg-green-50 rounded-md transition-colors duration-150"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Crear nuevo proveedor
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';

// Props del componente
const props = defineProps({
  proveedores: {
    type: Array,
    required: true,
    default: () => []
  },
  proveedorSeleccionado: {
    type: Object,
    default: null
  },
  mostrarOpcionNuevoProveedor: {
    type: Boolean,
    default: true
  },
  labelBusqueda: {
    type: String,
    default: 'Buscar Proveedor'
  },
  placeholderBusqueda: {
    type: String,
    default: 'Escribe para buscar proveedores...'
  },
  requerido: {
    type: Boolean,
    default: false
  },
  iconoBusqueda: {
    type: Boolean,
    default: true
  },
  deshabilitado: {
    type: Boolean,
    default: false
  },
  mensajeAyuda: {
    type: String,
    default: ''
  },
  tipoDocumento: {
    type: String,
    default: 'Proveedor'
  },
  tituloProveedorSeleccionado: {
    type: String,
    default: 'Proveedor Seleccionado'
  },
  mensajeVacio: {
    type: String,
    default: 'No hay proveedor seleccionado'
  },
  submensajeVacio: {
    type: String,
    default: 'Busca y selecciona un proveedor para continuar'
  },
  mostrarAccionRapida: {
    type: Boolean,
    default: true
  },
  mostrarEstadoProveedor: {
    type: Boolean,
    default: true
  },
  mostrarHistorial: {
    type: Boolean,
    default: false
  },
  mostrarEditar: {
    type: Boolean,
    default: false
  },
  mostrarInfoComercial: {
    type: Boolean,
    default: true
  },
  mostrarFiltrosRapidos: {
    type: Boolean,
    default: false
  },
  filtrosRapidos: {
    type: Array,
    default: () => []
  }
});

// Función auxiliar para dirección completa
const getDireccionCompleta = () => {
  if (!props.proveedorSeleccionado) return null;

  const proveedor = props.proveedorSeleccionado;
  const partesDireccion = [
    proveedor.calle,
    proveedor.numero_exterior,
    proveedor.numero_interior,
    proveedor.colonia,
    proveedor.codigo_postal,
    proveedor.municipio,
    proveedor.estado,
    proveedor.pais
  ].filter(Boolean);

  return partesDireccion.length > 0 ? partesDireccion.join(', ') : null;
};

// Computada para dirección completa del proveedor
const direccionCompleta = computed(getDireccionCompleta);

// Eventos que emite el componente
const emit = defineEmits([
  'proveedor-seleccionado',
  'crear-nuevo-proveedor',
  'ver-historial',
  'editar-proveedor'
]);

// Referencias reactivas
const inputBusqueda = ref(null);
const listaProveedoresRef = ref(null);
const listaVaciaRef = ref(null);
const busquedaProveedor = ref('');
const busquedaTermino = ref('');
const mostrarListaProveedores = ref(false);
const proveedorSeleccionadoIndex = ref(-1);
const cargandoBusqueda = ref(false);
const errorBusqueda = ref('');
const validacionError = ref('');
const touched = ref(false);
const timeoutId = ref(null);
const debounceTimeout = ref(null);
const inputWidth = ref(0);
const inputPosition = ref({ top: 0, left: 0, height: 0 });
const filtroActivo = ref(null);
const tiempoRespuesta = ref(null);

// Función para normalizar texto (quitar acentos y caracteres especiales)
const normalizarTexto = (texto) => {
  if (!texto) return '';
  return texto.toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^\w\s@.-]/g, '')
    .trim();
};

// Función para dividir términos de búsqueda
const dividirTerminosBusqueda = (termino) => {
  return normalizarTexto(termino)
    .split(/\s+/)
    .filter(t => t.length > 0);
};

// Computada para filtrar proveedores
const proveedoresFiltrados = computed(() => {
  if (!busquedaTermino.value || busquedaTermino.value.length < 1) {
    return [];
  }
  const terminos = dividirTerminosBusqueda(busquedaTermino.value);
  if (terminos.length === 0) return [];
  const inicioTiempo = performance.now();
  let proveedoresFilt = props.proveedores.filter(proveedor => {
    const textoNormalizado = [
      proveedor.nombre_razon_social,
      proveedor.email,
      proveedor.telefono,
      proveedor.rfc,
      proveedor.categoria,
      proveedor.tipo_proveedor
    ].map(normalizarTexto).join(' ');
    return terminos.every(termino => textoNormalizado.includes(termino));
  });
  // Aplicar filtro rápido si está activo
  if (filtroActivo.value) {
    const filtro = props.filtrosRapidos.find(f => f.value === filtroActivo.value);
    if (filtro && filtro.filter) {
      proveedoresFilt = proveedoresFilt.filter(filtro.filter);
    }
  }
  // Ordenar por relevancia
  proveedoresFilt = proveedoresFilt.sort((a, b) => {
    const nombreA = normalizarTexto(a.nombre_razon_social);
    const nombreB = normalizarTexto(b.nombre_razon_social);
    const primerTermino = terminos[0];
    const coincideInicioA = nombreA.startsWith(primerTermino);
    const coincideInicioB = nombreB.startsWith(primerTermino);
    if (coincideInicioA && !coincideInicioB) return -1;
    if (!coincideInicioA && coincideInicioB) return 1;
    return nombreA.localeCompare(nombreB);
  });
  const finTiempo = performance.now();
  tiempoRespuesta.value = Math.round(finTiempo - inicioTiempo);
  return proveedoresFilt.slice(0, 50); // Limitar resultados
});


// Computada para alertas del proveedor
const alertasProveedor = computed(() => {
  if (!props.proveedorSeleccionado) return [];
  const alertas = [];
  const proveedor = props.proveedorSeleccionado;
  if (proveedor.estado === 'suspendido') {
    alertas.push({
      id: 'suspendido',
      tipo: 'warning',
      mensaje: 'Este proveedor está suspendido temporalmente'
    });
  }
  if (proveedor.estado === 'inactivo') {
    alertas.push({
      id: 'inactivo',
      tipo: 'error',
      mensaje: 'Este proveedor está inactivo'
    });
  }
  if (proveedor.credito_limite && proveedor.saldo_pendiente > proveedor.credito_limite) {
    alertas.push({
      id: 'credito_excedido',
      tipo: 'error',
      mensaje: 'El proveedor ha excedido su límite de crédito'
    });
  }
  return alertas;
});

// Función para calcular posición del dropdown
const calcularPosicionInput = () => {
  if (!inputBusqueda.value) return;
  const rect = inputBusqueda.value.getBoundingClientRect();
  const scrollY = window.scrollY || document.documentElement.scrollTop;
  inputWidth.value = rect.width;
  inputPosition.value = {
    top: rect.top + scrollY,
    left: rect.left + window.scrollX,
    height: rect.height
  };
};

// Función para manejar input
const handleInput = () => {
  touched.value = true;
  filtrarProveedores();
};

// Función para manejar focus
const handleFocus = () => {
  touched.value = true;
  mostrarListaProveedores.value = true;
};

// Función para filtrar proveedores con debounce
const filtrarProveedores = () => {
  clearTimeout(debounceTimeout.value);
  debounceTimeout.value = setTimeout(() => {
    busquedaTermino.value = busquedaProveedor.value;
    errorBusqueda.value = '';
    if (busquedaProveedor.value.length >= 1) {
      calcularPosicionInput();
      mostrarListaProveedores.value = true;
      proveedorSeleccionadoIndex.value = -1;
    } else {
      mostrarListaProveedores.value = false;
    }
  }, 300);
};

// Función para ocultar lista con retraso
const ocultarListaConRetraso = () => {
  timeoutId.value = setTimeout(() => {
    mostrarListaProveedores.value = false;
  }, 150);
};

// Función para manejar teclas
const manejarTeclas = (event) => {
  if (!mostrarListaProveedores.value || proveedoresFiltrados.value.length === 0) return;
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      proveedorSeleccionadoIndex.value = Math.min(
        proveedorSeleccionadoIndex.value + 1,
        proveedoresFiltrados.value.length - 1
      );
      break;
    case 'ArrowUp':
      event.preventDefault();
      proveedorSeleccionadoIndex.value = Math.max(
        proveedorSeleccionadoIndex.value - 1,
        -1
      );
      break;
    case 'Enter':
      event.preventDefault();
      if (proveedorSeleccionadoIndex.value >= 0) {
        seleccionarProveedor(proveedoresFiltrados.value[proveedorSeleccionadoIndex.value], event);
      }
      break;
    case 'Escape':
      mostrarListaProveedores.value = false;
      inputBusqueda.value?.blur();
      break;
  }
};

// Función para seleccionar proveedor
const seleccionarProveedor = (proveedor, event) => {
  // Prevenir comportamiento por defecto
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }

  if (timeoutId.value) {
    clearTimeout(timeoutId.value);
  }
  mostrarListaProveedores.value = false;
  busquedaProveedor.value = proveedor.nombre_razon_social;
  validacionError.value = '';
  touched.value = true;
  emit('proveedor-seleccionado', proveedor);
};

// Función para limpiar búsqueda
const limpiarBusqueda = () => {
  busquedaProveedor.value = '';
  busquedaTermino.value = '';
  mostrarListaProveedores.value = false;
  errorBusqueda.value = '';
  validacionError.value = '';
  filtroActivo.value = null;
  touched.value = false;
};

// Función para limpiar proveedor seleccionado
const limpiarProveedor = () => {
  limpiarBusqueda();
  emit('proveedor-seleccionado', null); // null = limpiado
};


// Función para aplicar filtro rápido
const aplicarFiltroRapido = (filtro) => {
  if (filtroActivo.value === filtro.value) {
    filtroActivo.value = null;
  } else {
    filtroActivo.value = filtro.value;
  }
  if (busquedaTermino.value) {
    // Refiltrar con el nuevo filtro
    filtrarProveedores();
  }
};

// Función para resaltar texto de búsqueda
const resaltarTexto = (texto, busqueda) => {
  if (!texto || !busqueda) return texto;
  const terminos = dividirTerminosBusqueda(busqueda);
  let resultado = texto;
  terminos.forEach(termino => {
    if (termino.length > 1) {
      const regex = new RegExp(`(${termino})`, 'gi');
      resultado = resultado.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
    }
  });
  return resultado;
};

// Función para formatear moneda
const formatearMoneda = (valor) => {
  if (!valor) return '$0.00';
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(valor);
};

// Función para crear nuevo proveedor
const crearNuevoProveedor = () => {
  mostrarListaProveedores.value = false;
  emit('crear-nuevo-proveedor', {
    nombre_sugerido: busquedaProveedor.value
  });
};

// Función para ver historial
const verHistorial = () => {
  emit('ver-historial', props.proveedorSeleccionado);
};

// Función para editar proveedor
const editarProveedor = () => {
  emit('editar-proveedor', props.proveedorSeleccionado);
};

// Función para validar campos requeridos
const validarCamposRequeridos = () => {
  if (props.requerido && !props.proveedorSeleccionado) {
    validacionError.value = 'Este campo es requerido';
    return false;
  }
  validacionError.value = '';
  return true;
};

// Función para manejar clicks fuera del componente
const manejarClickFuera = (event) => {
  const elemento = event.target;
  const dentroInput = inputBusqueda.value?.contains(elemento);
  const dentroLista = listaProveedoresRef.value?.contains(elemento) ||
                     listaVaciaRef.value?.contains(elemento);
  if (!dentroInput && !dentroLista) {
    mostrarListaProveedores.value = false;
  }
};

// Función para manejar redimensionamiento de ventana
const manejarRedimension = () => {
  if (mostrarListaProveedores.value) {
    calcularPosicionInput();
  }
};

// Watchers
watch(
  () => props.proveedorSeleccionado,
  (nuevo) => {
    if (nuevo) {
      busquedaProveedor.value = nuevo.nombre_razon_social || '';
      validacionError.value = '';
    } else {
      limpiarBusqueda();
    }
  },
  { immediate: true }
);

watch(
  () => props.requerido,
  () => {
    nextTick(() => {
      validarCamposRequeridos();
    });
  }
);

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', manejarClickFuera);
  window.addEventListener('resize', manejarRedimension);
  window.addEventListener('scroll', manejarRedimension);
  // No validar estado inicial para evitar mostrar errores prematuramente
});

onUnmounted(() => {
  document.removeEventListener('click', manejarClickFuera);
  window.removeEventListener('resize', manejarRedimension);
  window.removeEventListener('scroll', manejarRedimension);
  if (timeoutId.value) {
    clearTimeout(timeoutId.value);
  }
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value);
  }
});

// Exponer funciones para uso del componente padre
defineExpose({
  validarCamposRequeridos,
  limpiarProveedor,
  enfocarBusqueda: () => inputBusqueda.value?.focus()
});
</script>
