<template>
  <Head title="Crear Compra" />

  <div class="p-6 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-semibold mb-6">Nueva Compra</h1>

    <form @submit.prevent="crearCompra" class="space-y-6">

      <!-- Proveedor -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Proveedor</label>
        <input
          v-model="buscarProveedor"
          type="text"
          placeholder="Buscar proveedor..."
          @focus="mostrarProveedores = true"
          @blur="ocultarProveedoresDespuesDeTiempo"
          class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring focus:ring-blue-200"
        />
        <ul
          v-if="mostrarProveedores && proveedoresFiltrados.length > 0"
          class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto mt-1"
        >
          <li
            v-for="proveedor in proveedoresFiltrados"
            :key="proveedor.id"
            @click="seleccionarProveedor(proveedor)"
            class="px-4 py-2 cursor-pointer hover:bg-gray-100"
          >
            {{ proveedor.nombre_razon_social }}
          </li>
        </ul>
      </div>

      <!-- Producto -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Producto</label>
        <input
          v-model="buscarProducto"
          type="text"
          placeholder="Buscar producto..."
          @focus="mostrarProductos = true"
          @blur="ocultarProductosDespuesDeTiempo"
          class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring focus:ring-blue-200"
        />
        <ul
          v-if="mostrarProductos && productosFiltrados.length > 0"
          class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto mt-1"
        >
          <li
            v-for="producto in productosFiltrados"
            :key="producto.id"
            @click="agregarProducto(producto)"
            class="px-4 py-2 cursor-pointer hover:bg-gray-100"
          >
            {{ producto.nombre }}
          </li>
        </ul>
      </div>

      <!-- Productos seleccionados -->
      <div v-if="selectedProducts.length > 0">
        <h3 class="text-lg font-medium mb-3">Productos Seleccionados</h3>
        <div
          v-for="id in selectedProducts"
          :key="id"
          class="flex items-center gap-3 bg-gray-50 p-3 rounded-md border mb-2"
        >
          <span class="flex-1">{{ getProductById(id)?.nombre }}</span>
          <input
            v-model.number="quantities[id]"
            type="number"
            min="1"
            class="w-20 border rounded px-2"
            @input="calcularTotal"
          />
          <input
            v-model.number="prices[id]"
            type="number"
            min="0"
            step="0.01"
            class="w-24 border rounded px-2"
            @input="calcularTotal"
          />
          <span class="w-24 text-right">{{ (quantities[id] * prices[id]).toFixed(2) }}</span>
          <button type="button" @click="eliminarProducto(id)" class="text-red-500 hover:text-red-700">
            ✕
          </button>
        </div>
      </div>

      <!-- Total -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Total</label>
        <input
          v-model="form.total"
          type="text"
          readonly
          class="mt-1 block w-full px-4 py-2 border rounded-md bg-gray-100"
        />
      </div>

      <!-- Botones -->
      <div class="flex justify-end gap-3">
        <Link :href="route('compras.index')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
          Cancelar
        </Link>
        <button
          type="submit"
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
          :disabled="!form.proveedor_id || selectedProducts.length === 0 || form.processing"
        >
          Guardar
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({ proveedores: Array, productos: Array });
const form = useForm({ proveedor_id: '', total: 0, productos: [] });

const buscarProveedor = ref('');
const buscarProducto = ref('');
const mostrarProveedores = ref(false);
const mostrarProductos = ref(false);
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});

// Filtrar listas
const proveedoresFiltrados = computed(() =>
  buscarProveedor.value
    ? props.proveedores.filter((p) =>
        p.nombre_razon_social.toLowerCase().includes(buscarProveedor.value.toLowerCase())
      )
    : []
);
const productosFiltrados = computed(() =>
  buscarProducto.value
    ? props.productos.filter((p) =>
        p.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase())
      )
    : []
);

// Métodos
const getProductById = (id) => props.productos.find((p) => p.id === id);

const seleccionarProveedor = (proveedor) => {
  form.proveedor_id = proveedor.id;
  buscarProveedor.value = proveedor.nombre_razon_social;
  mostrarProveedores.value = false;
};

const agregarProducto = (producto) => {
  if (!selectedProducts.value.includes(producto.id)) {
    selectedProducts.value.push(producto.id);
    quantities.value[producto.id] = 1;
    prices.value[producto.id] = producto.precio_compra || 0;
  }
  buscarProducto.value = '';
  mostrarProductos.value = false;
  calcularTotal();
};

const eliminarProducto = (id) => {
  selectedProducts.value = selectedProducts.value.filter((pid) => pid !== id);
  delete quantities.value[id];
  delete prices.value[id];
  calcularTotal();
};

const calcularTotal = debounce(() => {
  form.total = selectedProducts.value.reduce((sum, id) => {
    return sum + (quantities.value[id] || 0) * (prices.value[id] || 0);
  }, 0).toFixed(2);
}, 200);

const crearCompra = () => {
  form.productos = selectedProducts.value.map((id) => ({
    id,
    cantidad: quantities.value[id] || 1,
    precio: prices.value[id] || 0,
  }));
  form.post(route('compras.store'), {
    onSuccess: () => {
      form.reset();
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
    },
  });
};

// Ocultar listas con delay
const ocultarProveedoresDespuesDeTiempo = debounce(() => (mostrarProveedores.value = false), 200);
const ocultarProductosDespuesDeTiempo = debounce(() => (mostrarProductos.value = false), 200);
</script>
