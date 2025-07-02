<template>
  <div class="buscar-cliente">
    <!-- Campo de búsqueda -->
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
        />
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
          <svg v-if="!cargandoBusqueda" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <div v-else class="animate-spin w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
        </div>
      </div>

      <!-- Mensaje de error -->
      <div v-if="errorBusqueda" class="mt-2 text-sm text-red-600">
        {{ errorBusqueda }}
      </div>

      <!-- Mensaje informativo -->
      <div v-if="busquedaCliente && !mostrarListaClientes && clientesFiltrados.length === 0" class="mt-2 text-sm text-gray-500">
        No se encontraron clientes que coincidan con "{{ busquedaCliente }}"
      </div>
    </div>

    <!-- Lista de clientes filtrados -->
    <div v-if="mostrarListaClientes && clientesFiltrados.length > 0"
         class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
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
            <!-- Nombre del cliente -->
            <div class="font-medium text-gray-900 mb-1">
              <span v-html="resaltarTexto(cliente.nombre_razon_social, busquedaCliente)"></span>
            </div>

            <!-- Información adicional -->
            <div class="text-sm text-gray-500 space-y-1">
              <div v-if="cliente.email" class="flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span v-html="resaltarTexto(cliente.email, busquedaCliente)"></span>
              </div>

              <div v-if="cliente.telefono" class="flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span v-html="resaltarTexto(cliente.telefono, busquedaCliente)"></span>
              </div>

              <div v-if="cliente.rfc" class="flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="font-mono text-xs" v-html="resaltarTexto(cliente.rfc, busquedaCliente)"></span>
              </div>
            </div>
          </div>

          <!-- Badge de empresa -->
          <div v-if="cliente.empresa" class="ml-3">
            <div class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full whitespace-nowrap">
              <span v-html="resaltarTexto(cliente.empresa, busquedaCliente)"></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Opción para crear nuevo cliente -->
      <div v-if="busquedaCliente && mostrarOpcionNuevoCliente"
           class="border-t border-gray-200 p-3 bg-gray-50">
        <button
          @click="crearNuevoCliente"
          class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors duration-150 flex items-center"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Crear nuevo cliente: "{{ busquedaCliente }}"
        </button>
      </div>
    </div>

    <!-- Lista vacía con sugerencias -->
    <div v-if="mostrarListaClientes && clientesFiltrados.length === 0 && busquedaTermino"
         class="fixed z-99999 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4">
      <div class="text-center text-gray-500">
        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <p class="text-sm font-medium mb-1">No se encontraron clientes</p>
        <p class="text-xs text-gray-400 mb-3">Intenta con otro término de búsqueda</p>

        <!-- Botón para crear nuevo cliente -->
        <button
          @click="crearNuevoCliente"
          class="inline-flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors duration-150"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Crear nuevo cliente
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onUnmounted } from 'vue';

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
const busquedaCliente = ref('');
const busquedaTermino = ref('');
const mostrarListaClientes = ref(false);
const clienteSeleccionadoIndex = ref(-1);
const cargandoBusqueda = ref(false);
const errorBusqueda = ref('');
const timeoutId = ref(null);
const debounceTimeout = ref(null);

// Función para limpiar caracteres especiales y normalizar texto
const normalizarTexto = (texto) => {
  if (!texto) return '';
  return texto
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // Quitar acentos
    .replace(/[^\w\s@.-]/g, '') // Mantener solo alfanuméricos, espacios, @, ., -
    .trim();
};

// Función para dividir términos de búsqueda
const dividirTerminos = (termino) => {
  return termino
    .split(/\s+/)
    .filter(t => t.length > 0)
    .map(t => normalizarTexto(t));
};

// Computed para filtrar clientes con búsqueda mejorada
const clientesFiltrados = computed(() => {
  if (!busquedaTermino.value || busquedaTermino.value.length < 1) {
    return props.clientes.slice(0, 10);
  }

  const terminos = dividirTerminos(busquedaTermino.value);
  if (terminos.length === 0) return [];

  const resultados = props.clientes.filter(cliente => {
    // Normalizar campos del cliente
    const nombre = normalizarTexto(cliente.nombre_razon_social || '');
    const email = normalizarTexto(cliente.email || '');
    const telefono = normalizarTexto(cliente.telefono || '');
    const rfc = normalizarTexto(cliente.rfc || '');
    const empresa = normalizarTexto(cliente.empresa || '');
    const direccion = normalizarTexto(cliente.direccion || '');

    // Crear un texto combinado para búsqueda
    const textoCombinado = `${nombre} ${email} ${telefono} ${rfc} ${empresa} ${direccion}`;

    // Verificar que todos los términos estén presentes
    return terminos.every(termino =>
      textoCombinado.includes(termino) ||
      nombre.includes(termino) ||
      email.includes(termino) ||
      telefono.includes(termino) ||
      rfc.includes(termino) ||
      empresa.includes(termino)
    );
  });

  // Ordenar por relevancia
  return resultados
    .map(cliente => {
      let score = 0;
      const nombre = normalizarTexto(cliente.nombre_razon_social || '');

      terminos.forEach(termino => {
        // Coincidencia exacta al inicio del nombre
        if (nombre.startsWith(termino)) score += 100;
        // Coincidencia en el nombre
        else if (nombre.includes(termino)) score += 50;
        // Coincidencia en email
        else if (normalizarTexto(cliente.email || '').includes(termino)) score += 30;
        // Coincidencia en RFC
        else if (normalizarTexto(cliente.rfc || '').includes(termino)) score += 40;
        // Coincidencia en teléfono
        else if (normalizarTexto(cliente.telefono || '').includes(termino)) score += 20;
      });

      return { ...cliente, _score: score };
    })
    .sort((a, b) => b._score - a._score)
    .slice(0, 20);
});

// Métodos
const filtrarClientes = () => {
  errorBusqueda.value = '';

  // Debounce para evitar muchas búsquedas
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value);
  }

  debounceTimeout.value = setTimeout(() => {
    cargandoBusqueda.value = true;
    busquedaTermino.value = busquedaCliente.value;

    setTimeout(() => {
      cargandoBusqueda.value = false;
    }, 200);

    mostrarListaClientes.value = true;
    clienteSeleccionadoIndex.value = -1;
  }, 300);
};

const seleccionarCliente = (cliente) => {
  busquedaCliente.value = cliente.nombre_razon_social || '';
  busquedaTermino.value = '';
  mostrarListaClientes.value = false;
  clienteSeleccionadoIndex.value = -1;

  // Limpiar timeouts
  if (timeoutId.value) {
    clearTimeout(timeoutId.value);
  }

  emit('cliente-seleccionado', cliente);
};

const ocultarListaConRetraso = () => {
  timeoutId.value = setTimeout(() => {
    mostrarListaClientes.value = false;
  }, 150);
};

const manejarTeclas = (event) => {
  if (!mostrarListaClientes.value || clientesFiltrados.value.length === 0) return;

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      clienteSeleccionadoIndex.value = Math.min(
        clienteSeleccionadoIndex.value + 1,
        clientesFiltrados.value.length - 1
      );
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
      clienteSeleccionadoIndex.value = -1;
      break;
  }
};

const resaltarTexto = (texto, termino) => {
  if (!termino || !texto) return texto;

  const terminos = dividirTerminos(termino);
  if (terminos.length === 0) return texto;

  let textoResultado = texto;

  terminos.forEach(t => {
    if (t.length > 0) {
      // Crear regex más flexible para encontrar el término
      const regex = new RegExp(`(${t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
      textoResultado = textoResultado.replace(regex, '<mark class="bg-yellow-200 px-1 rounded font-semibold">$1</mark>');
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

// Watchers
watch(() => props.clienteSeleccionado, (nuevoCliente) => {
  if (nuevoCliente) {
    busquedaCliente.value = nuevoCliente.nombre_razon_social || '';
    busquedaTermino.value = '';
    mostrarListaClientes.value = false;
  } else {
    limpiarBusqueda();
  }
});

// Limpiar timeouts al desmontar
onUnmounted(() => {
  if (timeoutId.value) {
    clearTimeout(timeoutId.value);
  }
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value);
  }
});

// Exponer métodos para uso externo
defineExpose({
  limpiarBusqueda,
  enfocarInput: () => {
    nextTick(() => {
      if (inputBusqueda.value) {
        inputBusqueda.value.focus();
      }
    });
  }
});
</script>

<style scoped>
.buscar-cliente {
  position: relative;
}

/* Estilos para el resaltado de texto */
:deep(mark) {
  background-color: #fef08a;
  padding: 2px 4px;
  border-radius: 4px;
  font-weight: 600;
}

/* Animaciones */
.buscar-cliente input:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Scroll suave en la lista */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
