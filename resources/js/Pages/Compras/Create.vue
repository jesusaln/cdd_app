<template>
    <Head title="Crear Compra" />
    <div class="compras-create">
      <h1 class="text-2xl font-semibold mb-6">Crear Nueva Compra</h1>
      <form @submit.prevent="crearCompra" class="space-y-6">
        <!-- Búsqueda de proveedor -->
        <div class="form-group">
          <label for="buscarProveedor" class="block text-sm font-medium text-gray-700">Buscar Proveedor</label>
          <input
            v-model="buscarProveedor"
            type="text"
            placeholder="Buscar proveedor..."
            @focus="mostrarProveedores = true"
            @blur="ocultarProveedoresDespuesDeTiempo"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
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
              {{ proveedor.nombre }}
            </li>
          </ul>
          <p v-if="!proveedoresFiltrados.length && buscarProveedor && mostrarProveedores" class="text-red-500 text-sm mt-1">
            No se encontraron proveedores.
          </p>
        </div>

        <!-- Búsqueda de productos -->
        <div class="form-group">
          <label for="buscarProducto" class="block text-sm font-medium text-gray-700">Buscar Producto</label>
          <input
            v-model="buscarProducto"
            type="text"
            placeholder="Buscar producto..."
            @focus="mostrarProductos = true"
            @blur="ocultarProductosDespuesDeTiempo"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
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
              {{ producto.nombre }} (Disponible: {{ producto.stock }})
            </li>
          </ul>
          <p v-if="!productosFiltrados.length && buscarProducto && mostrarProductos" class="text-red-500 text-sm mt-1">
            No se encontraron productos.
          </p>
        </div>

        <!-- Lista de productos seleccionados -->
        <div v-if="selectedProducts.length > 0" class="mt-4">
          <h3 class="text-lg font-medium mb-4">Productos Seleccionados</h3>
          <div
            v-for="(productoId, index) in selectedProducts"
            :key="index"
            class="flex items-center justify-between bg-white border rounded-md shadow-sm p-4 mb-4"
          >
            <span class="font-medium text-gray-800">{{ getProductById(productoId)?.nombre }}</span>
            <input
              v-model.number="quantities[productoId]"
              type="number"
              class="px-4 py-2 border rounded-md w-16"
              min="1"
              @input="calcularTotal"
            />
            <input
              v-model.number="prices[productoId]"
              type="number"
              class="px-4 py-2 border rounded-md w-20"
              min="0"
              @input="calcularTotal"
            />
            <input
              :value="(quantities[productoId] * prices[productoId]).toFixed(2)"
              type="text"
              class="px-4 py-2 border rounded-md w-24 bg-gray-100 cursor-not-allowed"
              readonly
            />
            <button
              type="button"
              @click="eliminarProducto(productoId)"
              class="text-red-500 hover:text-red-700"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Total -->
        <div class="form-group">
          <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
          <input
            v-model="form.total"
            type="text"
            readonly
            class="mt-1 block w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed"
          />
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-4">
          <Link :href="route('compras.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
            Cancelar
          </Link>
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
            :disabled="!form.proveedor_id || selectedProducts.length === 0 || form.processing"
          >
            <span v-if="form.processing">Guardando...</span>
            <span v-else>Guardar Compra</span>
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { Head, useForm, Link } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
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

  // Filtrar proveedores
  const proveedoresFiltrados = computed(() =>
    buscarProveedor.value
      ? props.proveedores.filter((proveedor) =>
          proveedor.nombre.toLowerCase().includes(buscarProveedor.value.toLowerCase())
        )
      : []
  );

  // Filtrar productos
  const productosFiltrados = computed(() =>
    buscarProducto.value
      ? props.productos.filter((producto) =>
          producto.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase())
        )
      : []
  );

  // Obtener producto por ID
  const getProductById = (id) => props.productos.find((producto) => producto.id === id);

  // Calcular el total
  const calcularTotal = () => {
    form.total = selectedProducts.value.reduce(
      (sum, id) => sum + (quantities.value[id] * prices.value[id] || 0),
      0
    ).toFixed(2);
  };

  // Seleccionar proveedor
  const seleccionarProveedor = (proveedor) => {
    form.proveedor_id = proveedor.id;
    buscarProveedor.value = proveedor.nombre;
    mostrarProveedores.value = false;
  };

  // Agregar producto
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

  // Eliminar producto
  const eliminarProducto = (productoId) => {
    selectedProducts.value = selectedProducts.value.filter((id) => id !== productoId);
    delete quantities.value[productoId];
    delete prices.value[productoId];
    calcularTotal();
  };

  // Crear compra
  const crearCompra = () => {
    form.productos = selectedProducts.value.map((id) => ({
      id,
      cantidad: quantities.value[id] || 1,
      precio: prices.value[id] || 0,
    }));
    form.post(route('compras.store'), {
      onSuccess: () => {
        // Limpiar el formulario después de guardar
        form.reset();
        selectedProducts.value = [];
        quantities.value = {};
        prices.value = {};
      },
    });
  };
  </script>

  <style scoped>
  /* Estilos personalizados */
  .form-group {
    position: relative;
  }
  </style>
