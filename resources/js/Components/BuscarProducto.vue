<template>
  <div class="relative mb-6">
    <label for="buscarProducto" class="block text-sm font-medium text-gray-700 mb-2">
      Agregar Producto/Servicio
    </label>
    <div class="relative">
      <input
        id="buscarProducto"
        v-model="buscarProducto"
        type="text"
        placeholder="Buscar por nombre, código, serie o código de barras..."
        @focus="mostrarProductos = true"
        @blur="ocultarProductosDespuesDeTiempo"
        autocomplete="off"
        class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white"
      />
      <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
    </div>

    <!-- Lista de productos -->
    <ul v-if="mostrarProductos && productosFiltrados.length > 0"
        class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto">
      <li
        v-for="item in productosFiltrados"
        :key="`${item.tipo}-${item.id}`"
        @click="agregarProducto(item)"
        class="px-4 py-3 cursor-pointer hover:bg-green-50 border-b border-gray-100 last:border-b-0 transition-colors duration-150"
      >
        <div class="flex justify-between items-start">
          <div>
            <div class="font-medium text-gray-900">{{ item.nombre }}</div>
            <div class="text-sm text-gray-500">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mr-2"
                    :class="item.tipo === 'producto' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                {{ item.tipo }}
              </span>
              <span v-if="item.codigo">Código: {{ item.codigo }}</span>
            </div>
          </div>
          <div class="text-right">
            <div class="font-medium text-gray-900">
              ${{ (item.precio_venta || item.precio || 0).toLocaleString() }}
            </div>
            <div v-if="item.tipo === 'producto' && item.stock !== undefined" class="text-sm text-gray-500">
              Stock: {{ item.stock }}
            </div>
          </div>
        </div>
      </li>
    </ul>

    <div v-if="productosFiltrados.length === 0 && buscarProducto"
         class="mt-2 text-sm text-red-600 flex items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      No se encontraron productos/servicios
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  productos: {
    type: Array,
    default: () => [], // Asegúrate de que productos tenga un valor por defecto
  },
  servicios: {
    type: Array,
    default: () => [], // Asegúrate de que servicios tenga un valor por defecto
  },
});

const emit = defineEmits(['agregar-producto']);

const buscarProducto = ref('');
const mostrarProductos = ref(false);

const productosFiltrados = computed(() => {
  if (!buscarProducto.value) return [];

  // Asegúrate de que productos y servicios sean arrays antes de usar map
  const productos = Array.isArray(props.productos) ? props.productos : [];
  const servicios = Array.isArray(props.servicios) ? props.servicios : [];

  const productosYServicios = [
    ...productos.map(producto => ({
      ...producto,
      tipo: 'producto',
    })),
    ...servicios.map(servicio => ({
      ...servicio,
      tipo: 'servicio',
    })),
  ];

  return productosYServicios.filter(item =>
    item.nombre?.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
    item.codigo?.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
    item.numero_de_serie?.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
    item.codigo_barras?.toLowerCase().includes(buscarProducto.value.toLowerCase())
  );
});

const agregarProducto = (item) => {
  emit('agregar-producto', item);
  buscarProducto.value = '';
  mostrarProductos.value = false;
};

const ocultarProductosDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('producto-item')) {
      mostrarProductos.value = false;
    }
  }, 300);
};
</script>
