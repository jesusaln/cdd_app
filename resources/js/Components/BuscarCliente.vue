<template>
  <div>
    <div v-if="!clienteSeleccionado" class="buscar-cliente">
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Buscar Cliente
        </label>
        <div class="relative">
          <input
            ref="inputBusqueda"
            type="text"
            v-model="busquedaCliente"
            @input="filtrarClientes"
            @focus="mostrarListaClientes = true"
            @blur="ocultarListaConRetraso"
            @keydown="manejarTeclas"
            placeholder="Buscar por nombre, email, teléfono o RFC..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200"
            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errorBusqueda }"
            autocomplete="new-password"
          />
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <svg v-if="!cargandoBusqueda" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <div v-else class="animate-spin w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
          </div>
        </div>
        <div v-if="errorBusqueda" class="mt-2 text-sm text-red-600">
          {{ errorBusqueda }}
        </div>
        <div v-if="busquedaCliente && !mostrarListaClientes && clientesFiltrados.length === 0 && !cargandoBusqueda" class="mt-2 text-sm text-gray-500">
          No se encontraron clientes que coincidan con "{{ busquedaCliente }}"
        </div>
      </div>
    </div>

    <div v-else class="mt-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-sm">
      <div class="flex items-start justify-between mb-4">
        <h3 class="text-lg font-semibold text-blue-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Cliente Seleccionado
        </h3>
        <button type="button" @click="limpiarCliente" class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded-full transition-colors duration-200" title="Cambiar cliente">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="space-y-2">
          <div class="flex items-center text-sm font-medium text-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Nombre
          </div>
          <div class="text-lg font-semibold text-gray-900">{{ clienteSeleccionado.nombre_razon_social }}</div>
        </div>
        <div class="space-y-2" v-if="clienteSeleccionado.email">
          <div class="flex items-center text-sm font-medium text-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Email
          </div>
          <div class="text-gray-900">{{ clienteSeleccionado.email }}</div>
        </div>
        <div class="space-y-2" v-if="clienteSeleccionado.telefono">
          <div class="flex items-center text-sm font-medium text-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            Teléfono
          </div>
          <div class="text-gray-900">{{ clienteSeleccionado.telefono }}</div>
        </div>
        <div class="space-y-2" v-if="clienteSeleccionado.calle">
          <div class="flex items-center text-sm font-medium text-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Dirección
          </div>
          <div class="text-gray-900">{{ clienteSeleccionado.calle }}</div>
        </div>
        <div class="space-y-2" v-if="clienteSeleccionado.numero_exterior">
          <div class="flex items-center text-sm font-medium text-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Número Exterior
          </div>
          <div class="text-gray-900">{{ clienteSeleccionado.numero_exterior }}</div>
        </div>
        <div class="space-y-2" v-if="clienteSeleccionado.rfc">
          <div class="flex items-center text-sm font-medium text-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            RFC
          </div>
          <div class="text-gray-900 font-mono">{{ clienteSeleccionado.rfc }}</div>
        </div>
      </div>
    </div>

    <!-- Usar Teleport para renderizar fuera del componente -->
    <Teleport to="#app">
      <div
        ref="listaClientesRef"
        v-if="mostrarListaClientes && clientesFiltrados.length > 0"
        class="z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-[60vh] overflow-y-auto"
        :style="{
          position: 'absolute',
          width: inputWidth + 'px',
          top: inputPosition.top + inputPosition.height + 'px',
          left: inputPosition.left + 'px'
        }"
      >
        <div class="p-2">
          <div class="text-xs text-gray-500 mb-2 px-2">
            {{ clientesFiltrados.length }} cliente{{ clientesFiltrados.length !== 1 ? 's' : '' }} encontrado{{ clientesFiltrados.length !== 1 ? 's' : '' }}
          </div>
        </div>
        <div
          v-for="(cliente, index) in clientesFiltrados"
          :key="cliente.id"
          @click="seleccionarCliente(cliente)"
          @mouseenter="clienteSeleccionadoIndex = index"
          class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150"
          :class="{ 'bg-blue-50': clienteSeleccionadoIndex === index }"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="font-medium text-gray-900 mb-1" v-html="resaltarTexto(cliente.nombre_razon_social, busquedaCliente)"></div>
              <div class="text-sm text-gray-500 space-y-1">
                <div v-if="cliente.email" class="flex items-center" v-html="resaltarTexto(cliente.email, busquedaCliente)"></div>
                <div v-if="cliente.telefono" class="flex items-center" v-html="resaltarTexto(cliente.telefono, busquedaCliente)"></div>
                <div v-if="cliente.rfc" class="flex items-center font-mono text-xs" v-html="resaltarTexto(cliente.rfc, busquedaCliente)"></div>
              </div>
            </div>
            <div v-if="cliente.empresa" class="ml-3">
              <div class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full whitespace-nowrap" v-html="resaltarTexto(cliente.empresa, busquedaCliente)"></div>
            </div>
          </div>
        </div>
        <div v-if="busquedaCliente && mostrarOpcionNuevoCliente" class="border-t border-gray-200 p-3 bg-gray-50">
          <button @click="crearNuevoCliente" class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors duration-150 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Crear nuevo cliente: "{{ busquedaCliente }}"
          </button>
        </div>
      </div>
      <div
        ref="listaVaciaRef"
        v-if="mostrarListaClientes && clientesFiltrados.length === 0 && busquedaTermino"
        class="z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4"
        :style="{
          position: 'absolute',
          width: inputWidth + 'px',
          top: inputPosition.top + inputPosition.height + 'px',
          left: inputPosition.left + 'px'
        }"
      >
        <div class="text-center text-gray-500">
          <p class="text-sm font-medium mb-1">No se encontraron clientes</p>
          <p class="text-xs text-gray-400 mb-3">Intenta con otro término de búsqueda</p>
          <button @click="crearNuevoCliente" class="inline-flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors duration-150">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Crear nuevo cliente
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted, Teleport } from 'vue';

const props = defineProps({
  clientes: {
    type: Array,
    required: true,
    default: () => []
  },
  clienteSeleccionado: {
    type: Object,
    default: null
  },
  mostrarOpcionNuevoCliente: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['cliente-seleccionado', 'crear-nuevo-cliente']);

// Referencias reactivas
const inputBusqueda = ref(null);
const listaClientesRef = ref(null);
const listaVaciaRef = ref(null);
const busquedaCliente = ref('');
const busquedaTermino = ref('');
const mostrarListaClientes = ref(false);
const clienteSeleccionadoIndex = ref(-1);
const cargandoBusqueda = ref(false);
const errorBusqueda = ref('');
const timeoutId = ref(null);
const debounceTimeout = ref(null);
const inputWidth = ref(0);
const inputPosition = ref({ top: 0, left: 0, height: 0 });

const normalizarTexto = (texto) => {
  if (!texto) return '';
  return texto.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^\w\s@.-]/g, '').trim();
};

const dividirTerminos = (termino) => {
  return termino.split(/\s+/).filter(t => t.length > 0).map(t => normalizarTexto(t));
};

const clientesFiltrados = computed(() => {
  if (!busquedaTermino.value || busquedaTermino.value.length < 1) {
    return [];
  }
  const terminos = dividirTerminos(busquedaTermino.value);
  if (terminos.length === 0) return [];
  const resultados = props.clientes.filter(cliente => {
    const textoCombinado = normalizarTexto(`${cliente.nombre_razon_social} ${cliente.email} ${cliente.telefono} ${cliente.rfc} ${cliente.empresa} ${cliente.direccion}`);
    return terminos.every(termino => textoCombinado.includes(termino));
  });
  return resultados.map(cliente => {
    let score = 0;
    const nombre = normalizarTexto(cliente.nombre_razon_social || '');
    terminos.forEach(termino => {
      if (nombre.startsWith(termino)) score += 100;
      else if (nombre.includes(termino)) score += 50;
      else if (normalizarTexto(cliente.email || '').includes(termino)) score += 30;
      else if (normalizarTexto(cliente.rfc || '').includes(termino)) score += 40;
      else if (normalizarTexto(cliente.telefono || '').includes(termino)) score += 20;
    });
    return { ...cliente, _score: score };
  }).sort((a, b) => b._score - a._score);
});

const filtrarClientes = () => {
  errorBusqueda.value = '';
  if (debounceTimeout.value) clearTimeout(debounceTimeout.value);
  debounceTimeout.value = setTimeout(() => {
    cargandoBusqueda.value = true;
    busquedaTermino.value = busquedaCliente.value;
    setTimeout(() => { cargandoBusqueda.value = false; }, 200);
    mostrarListaClientes.value = true;
    clienteSeleccionadoIndex.value = -1;
  }, 300);
};

const seleccionarCliente = (cliente) => {
  if (timeoutId.value) clearTimeout(timeoutId.value);
  emit('cliente-seleccionado', cliente);
  mostrarListaClientes.value = false;
};

const limpiarCliente = () => {
  emit('cliente-seleccionado', null);
};

const ocultarListaConRetraso = () => {
  timeoutId.value = setTimeout(() => {
    mostrarListaClientes.value = false;
  }, 150);
};

const manejarTeclas = (event) => {
  if (!mostrarListaClientes.value || clientesFiltrados.value.length === 0) return;
  const maxIndex = clientesFiltrados.value.length - 1;
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      clienteSeleccionadoIndex.value = Math.min(clienteSeleccionadoIndex.value + 1, maxIndex);
      break;
    case 'ArrowUp':
      event.preventDefault();
      clienteSeleccionadoIndex.value = Math.max(clienteSeleccionadoIndex.value - 1, -1);
      break;
    case 'Enter':
      event.preventDefault();
      if (clienteSeleccionadoIndex.value >= 0) {
        seleccionarCliente(clientesFiltrados.value[clienteSeleccionadoIndex.value]);
      }
      break;
    case 'Escape':
      mostrarListaClientes.value = false;
      break;
  }
};

const resaltarTexto = (texto, termino) => {
  if (!texto || !termino) return texto || '';
  const terminos = dividirTerminos(termino);
  if (terminos.length === 0) return texto;
  let textoResultado = texto.toString();
  terminos.forEach(t => {
    if (t.length > 0) {
      const escapedTerm = t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const regex = new RegExp(`(${escapedTerm})`, 'gi');
      textoResultado = textoResultado.replace(regex, '<mark class="resaltar">$1</mark>');
    }
  });
  return textoResultado;
};

const crearNuevoCliente = () => {
  mostrarListaClientes.value = false;
  emit('crear-nuevo-cliente', busquedaCliente.value);
};

const limpiarBusqueda = () => {
  busquedaCliente.value = '';
  busquedaTermino.value = '';
  mostrarListaClientes.value = false;
  clienteSeleccionadoIndex.value = -1;
  errorBusqueda.value = '';
};

const actualizarPosicionLista = () => {
  if (!inputBusqueda.value) return;
  const rect = inputBusqueda.value.getBoundingClientRect();
  inputWidth.value = rect.width;
  inputPosition.value = {
    top: rect.top + window.scrollY,
    left: rect.left + window.scrollX,
    height: rect.height
  };
};

watch(() => props.clienteSeleccionado, (nuevoCliente) => {
  if (nuevoCliente) {
    busquedaCliente.value = nuevoCliente.nombre_razon_social || '';
    mostrarListaClientes.value = false;
  } else {
    limpiarBusqueda();
    nextTick(() => inputBusqueda.value?.focus());
  }
});

watch(mostrarListaClientes, (esVisible) => {
  if (esVisible) {
    nextTick(actualizarPosicionLista);
  }
});

onMounted(() => {
  window.addEventListener('resize', actualizarPosicionLista);
  window.addEventListener('scroll', actualizarPosicionLista);
});

onUnmounted(() => {
  window.removeEventListener('resize', actualizarPosicionLista);
  window.removeEventListener('scroll', actualizarPosicionLista);
  if (timeoutId.value) clearTimeout(timeoutId.value);
  if (debounceTimeout.value) clearTimeout(debounceTimeout.value);
});

defineExpose({
  limpiarBusqueda,
  focus: () => nextTick(() => inputBusqueda.value?.focus())
});
</script>

<style scoped>
.buscar-cliente {
  position: relative;
}

.resaltar {
  background-color: transparent !important;
  color: #2563eb;
  font-weight: bold;
  padding: 0;
}

.max-h-\[60vh\] {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.max-h-\[60vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[60vh\]::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.max-h-\[60vh\]::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
</style>
