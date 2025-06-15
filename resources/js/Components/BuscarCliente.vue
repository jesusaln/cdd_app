<template>
  <div class="relative">
    <label for="buscarCliente" class="block text-sm font-medium text-gray-700 mb-2">
      Cliente *
    </label>
    <div class="relative">
      <input
        id="buscarCliente"
        v-model="buscarCliente"
        type="text"
        placeholder="Buscar cliente por nombre o razón social..."
        @focus="mostrarClientes = true"
        @blur="ocultarClientesDespuesDeTiempo"
        autocomplete="off"
        class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
        :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': form.errors.cliente_id }"
      />
      <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
    </div>

    <!-- Lista de clientes -->
    <ul v-if="mostrarClientes && clientesFiltrados.length > 0"
        class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto">
      <li
        v-for="cliente in clientesFiltrados"
        :key="cliente.id"
        @click="seleccionarCliente(cliente)"
        class="px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0 transition-colors duration-150"
      >
        <div class="font-medium text-gray-900">{{ cliente.nombre_razon_social }}</div>
        <div class="text-sm text-gray-500" v-if="cliente.email">{{ cliente.email }}</div>
      </li>
    </ul>

    <!-- Cliente seleccionado -->
    <div v-if="clienteSeleccionado" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
      <div class="flex items-center text-green-800">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Cliente seleccionado: <span class="font-medium ml-1">{{ clienteSeleccionado }}</span>
      </div>
    </div>

    <!-- Error de cliente -->
    <div v-if="clientesFiltrados.length === 0 && buscarCliente && !clienteSeleccionado"
         class="mt-2 text-sm text-red-600 flex items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      No se encontraron clientes
    </div>

    <div v-if="form.errors.cliente_id" class="mt-2 text-sm text-red-600">
      {{ form.errors.cliente_id }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  clientes: Array,
  form: Object,
});

const buscarCliente = ref('');
const mostrarClientes = ref(false);
const clienteSeleccionado = ref(null);

const clientesFiltrados = computed(() => {
  if (!buscarCliente.value) return [];
  return props.clientes.filter((cliente) =>
    cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase())
  );
});

const seleccionarCliente = (cliente) => {
  props.form.cliente_id = cliente.id;
  buscarCliente.value = cliente.nombre_razon_social;
  clienteSeleccionado.value = cliente.nombre_razon_social;
  mostrarClientes.value = false;
};

const ocultarClientesDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('cliente-item')) {
      mostrarClientes.value = false;
    }
  }, 300);
};
</script>
