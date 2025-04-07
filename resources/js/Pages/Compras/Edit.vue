<template>
    <Head title="Editar Compra" />
    <div class="compras-edit">
      <h1 class="text-2xl font-semibold mb-6">Editar Compra</h1>
      <form @submit.prevent="guardarCambios" class="space-y-6">
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
              {{ proveedor.nombre_razon_social }}
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
              {{ producto.nombre }}
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
            <span v-else>Guardar Cambios</span>
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { Head, useForm, Link } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import { debounce } from 'lodash';

  // Define el layout del dashboard
  defineOptions({ layout: AppLayout });

  // Props recibidos desde el backend
  const props = defineProps({
    proveedores: Array,
    productos: Array,
    compra: Object,
  });

  // Formulario reactivo
  const form = useForm({
    proveedor_id: props.compra?.proveedor_id || '',
    total: props.compra?.total || 0,
    productos: [],
  });

  // Variables reactivas
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
          proveedor.nombre_razon_social.toLowerCase().includes(buscarProveedor.value.toLowerCase())
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
  const calcularTotal = debounce(() => {
    form.total = selectedProducts.value.reduce((sum, id) => {
      const cantidad = Number.parseFloat(quantities.value[id]) || 0;
      const precio = Number.parseFloat(prices.value[id]) || 0;
      return sum + (cantidad * precio);
    }, 0).toFixed(2);
  }, 300);

  // Seleccionar proveedor
  const seleccionarProveedor = (proveedor) => {
    form.proveedor_id = proveedor.id;
    buscarProveedor.value = proveedor.nombre_razon_social;
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
    if (selectedProducts.value.includes(productoId)) {
      selectedProducts.value = selectedProducts.value.filter((id) => id !== productoId);
      delete quantities.value[productoId];
      delete prices.value[productoId];
      calcularTotal();
    }
  };

  // Guardar cambios
  const guardarCambios = () => {
    form.productos = selectedProducts.value.map((id) => ({
      id,
      cantidad: quantities.value[id] || 1,
      precio: prices.value[id] || 0,
    }));

    form.put(route('compras.update', props.compra.id), {
      onSuccess: () => {
        form.reset();
        selectedProducts.value = [];
        quantities.value = {};
        prices.value = {};
      },
      onError: (errors) => {
        console.error('Errores al guardar:', errors);
      },
    });
  };

  // Ocultar listas después de un tiempo
  const ocultarProveedoresDespuesDeTiempo = debounce(() => {
    mostrarProveedores.value = false;
  }, 200);

  const ocultarProductosDespuesDeTiempo = debounce(() => {
    mostrarProductos.value = false;
  }, 200);

  // Cargar datos iniciales si se está editando una compra existente
  onMounted(() => {
    if (props.compra?.proveedor_id) {
      const proveedorSeleccionado = props.proveedores.find(
        (proveedor) => proveedor.id === props.compra.proveedor_id
      );
      if (proveedorSeleccionado) {
        form.proveedor_id = proveedorSeleccionado.id;
        buscarProveedor.value = proveedorSeleccionado.nombre_razon_social;
      }

      if (Array.isArray(props.compra.productos)) {
        for (const producto of props.compra.productos) {
          selectedProducts.value.push(producto.id);
          quantities.value[producto.id] = producto.pivot.cantidad || 1;
          prices.value[producto.id] = producto.pivot.precio || 0;
        }
        calcularTotal();
      }
    }
  });
  </script>
