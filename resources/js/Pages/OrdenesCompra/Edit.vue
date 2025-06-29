<template>
  <Head title="Editar Órdenes de Compra" />
  <div class="ordenes-compra-edit">
    <h1 class="text-2xl font-semibold mb-6">Editar Orden de Compra</h1>
    <form @submit.prevent="actualizarOrdenCompra" class="space-y-6">
      <!-- Búsqueda de proveedor -->
      <div class="form-group">
        <label for="buscarProveedor" class="block text-sm font-medium text-gray-700">Buscar Proveedor</label>
        <input
          id="buscarProveedor"
          v-model="buscarProveedor"
          type="text"
          placeholder="Buscar proveedor..."
          @focus="mostrarProveedores = true"
          @blur="ocultarProveedoresDespuesDeTiempo"
          autocomplete="off"
          class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
        />
        <ul v-if="mostrarProveedores && proveedoresFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto">
          <li
            v-for="proveedor in proveedoresFiltrados"
            :key="proveedor.id"
            @click="seleccionarProveedor(proveedor)"
            class="px-4 py-2 cursor-pointer hover:bg-gray-100 proveedor-item"
          >
            {{ proveedor.nombre_razon_social }}
          </li>
        </ul>
        <div v-if="proveedoresFiltrados.length === 0 && buscarProveedor" class="text-red-500 text-sm mt-2">
          No se encontraron proveedores.
        </div>
      </div>

      <!-- Búsqueda de productos y servicios -->
      <div class="form-group">
        <label for="buscarProducto" class="block text-sm font-medium text-gray-700">Buscar Producto/Servicio</label>
        <input
          id="buscarProducto"
          v-model="buscarProducto"
          type="text"
          placeholder="Buscar producto/servicio..."
          @focus="mostrarProductos = true"
          @blur="ocultarProductosDespuesDeTiempo"
          autocomplete="off"
          class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
        />
        <ul v-if="mostrarProductos && productosFiltrados.length > 0" class="absolute z-10 bg-white border rounded-md shadow-md w-full max-h-48 overflow-y-auto">
          <li
            v-for="item in productosFiltrados"
            :key="item.id"
            @click="agregarItem(item)"
            class="px-4 py-2 cursor-pointer hover:bg-gray-100 producto-item"
          >
            {{ item.nombre }} ({{ item.tipo }}) (Disponible: {{ item.stock || 'N/A' }})
          </li>
        </ul>
        <div v-if="productosFiltrados.length === 0 && buscarProducto" class="text-red-500 text-sm mt-2">
          No se encontraron productos/servicios.
        </div>
      </div>

      <!-- Lista de productos y servicios seleccionados -->
      <div v-if="selectedItems.length > 0 && props.productos && props.servicios" class="mt-4">
        <h3 class="text-lg font-medium mb-4">Productos/Servicios Seleccionados</h3>
        <div v-for="(entry, index) in selectedItems" :key="index" class="flex items-center justify-between bg-white border rounded-md shadow-sm p-4 mb-4">
          <div class="flex items-center space-x-4 w-full">
            <span class="font-medium text-gray-800 w-1/3">{{ getItemById(entry)?.nombre || 'Item no encontrado' }}</span>
            <div class="flex flex-col w-1/4">
              <label :for="`cantidad-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Cantidad</label>
              <input
                :id="`cantidad-${entry.tipo}-${entry.id}`"
                v-model.number="quantities[`${entry.tipo}-${entry.id}`]"
                type="number"
                class="px-4 py-2 border rounded-md mt-1 w-full"
                min="1"
                @input="calcularTotal"
              />
            </div>
            <div class="flex flex-col w-1/4">
              <label :for="`precio-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Precio de Compra</label>
              <input
                :id="`precio-${entry.tipo}-${entry.id}`"
                v-model.number="prices[`${entry.tipo}-${entry.id}`]"
                type="number"
                class="px-4 py-2 border rounded-md mt-1 w-full"
                min="0"
                @input="calcularTotal"
              />
            </div>
            <div class="flex flex-col w-1/4">
              <label :for="`subtotal-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Subtotal</label>
              <input
                :id="`subtotal-${entry.tipo}-${entry.id}`"
                :value="(quantities[`${entry.tipo}-${entry.id}`] * prices[`${entry.tipo}-${entry.id}`]).toFixed(2)"
                type="text"
                class="px-4 py-2 border rounded-md mt-1 w-full bg-gray-100 cursor-not-allowed"
                readonly
              />
            </div>
          </div>
          <button
            type="button"
            @click="eliminarItem(entry)"
            class="text-red-500 hover:text-red-700 ml-4"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Total -->
      <div class="form-group">
        <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
        <input
          id="total"
          v-model="form.total"
          type="text"
          readonly
          class="mt-1 block w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed"
        />
      </div>

      <!-- Botones -->
      <div class="flex justify-end space-x-4">
        <Link :href="route('ordenes-compra.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
          Cancelar
        </Link>
        <button
          type="submit"
          class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 disabled:bg-gray-400"
          :disabled="!form.proveedor_id || selectedItems.length === 0"
        >
          Actualizar Orden de Compra
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
  ordenCompra: Object, // Propiedad principal: la orden de compra a editar
  proveedores: { // Lista de todos los proveedores disponibles
    type: Array,
    default: () => [],
  },
  productos: { // Lista de todos los productos disponibles
    type: Array,
    default: () => [],
  },
  servicios: { // Lista de todos los servicios disponibles
    type: Array,
    default: () => [],
  },
});

// Inicialización del formulario de Inertia con los datos de la orden de compra existente
const form = useForm({
  proveedor_id: props.ordenCompra?.proveedor_id || '',
  total: props.ordenCompra?.total || 0,
  items: [], // Se llenará con los ítems de la orden de compra existente
});

// Variables reactivas para la búsqueda y visualización de proveedores y productos
const buscarProveedor = ref(props.ordenCompra?.proveedor?.nombre_razon_social || '');
const buscarProducto = ref('');
const mostrarProveedores = ref(false);
const mostrarProductos = ref(false);

// selectedItems contendrá los ítems (productos/servicios) de la orden de compra actual
const selectedItems = ref(
  props.ordenCompra?.items?.map(item => ({
    id: item.id,
    tipo: item.tipo, // Asegúrate de que el backend envíe 'tipo'
  })) || []
);

// quantities y prices contendrán las cantidades y precios de los ítems de la orden de compra actual
const quantities = ref(
  props.ordenCompra?.items?.reduce((acc, item) => {
    acc[`${item.tipo}-${item.id}`] = item.pivot?.cantidad || item.cantidad || 1;
    return acc;
  }, {}) || {}
);

const prices = ref(
  props.ordenCompra?.items?.reduce((acc, item) => {
    acc[`${item.tipo}-${item.id}`] = item.pivot?.precio || item.precio || 0; // Utiliza el precio de compra del pivot
    return acc;
  }, {}) || {}
);

const proveedorSeleccionado = ref(props.ordenCompra?.proveedor?.nombre_razon_social || null);

// Propiedad computada para filtrar proveedores en el campo de búsqueda
const proveedoresFiltrados = computed(() => {
  if (!buscarProveedor.value) return [];
  return props.proveedores.filter((proveedor) =>
    proveedor.nombre_razon_social.toLowerCase().includes(buscarProveedor.value.toLowerCase())
  );
});

// Propiedad computada para filtrar productos/servicios en el campo de búsqueda
const productosFiltrados = computed(() => {
  if (!buscarProducto.value) return [];
  const productosYServicios = [
    ...(props.productos || []).map(producto => ({
      ...producto,
      tipo: 'producto',
    })),
    ...(props.servicios || []).map(servicio => ({
      ...servicio,
      tipo: 'servicio',
    })),
  ];

  return productosYServicios.filter(item =>
    item.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
    (item.codigo?.toLowerCase().includes(buscarProducto.value.toLowerCase())) ||
    (item.numero_de_serie?.toLowerCase().includes(buscarProducto.value.toLowerCase())) ||
    (item.codigo_barras?.toLowerCase().includes(buscarProducto.value.toLowerCase()))
  );
});

// Función para obtener los datos completos de un ítem por su ID y tipo
const getItemById = (entry) => {
  if (!entry || !entry.id || !entry.tipo) {
    console.error('Entrada inválida para getItemById:', entry);
    return null;
  }
  if (entry.tipo === 'producto') {
    if (!Array.isArray(props.productos)) {
      console.error('props.productos no es un array:', props.productos);
      return null;
    }
    const producto = props.productos.find((p) => p.id === entry.id);
    return producto || null;
  }
  if (entry.tipo === 'servicio') {
    if (!Array.isArray(props.servicios)) {
      console.error('props.servicios no es un array:', props.servicios);
      return null;
    }
    const servicio = props.servicios.find((s) => s.id === entry.id);
    return servicio || null;
  }
  console.error(`No se encontró item con ID: ${entry.id} y tipo: ${entry.tipo}`);
  return null;
};

// Función para calcular el total de la orden de compra
const calcularTotal = () => {
  let total = 0;
  for (const entry of selectedItems.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    total += cantidad * precio;
  }
  form.total = total.toFixed(2);
};

// Observadores para recalcular el total cuando las cantidades o precios cambian
watch(quantities, calcularTotal, { deep: true });
watch(prices, calcularTotal, { deep: true });

// Función para seleccionar un proveedor de la lista de búsqueda
const seleccionarProveedor = (proveedor) => {
  form.proveedor_id = proveedor.id;
  buscarProveedor.value = proveedor.nombre_razon_social;
  proveedorSeleccionado.value = proveedor.nombre_razon_social;
  mostrarProveedores.value = false;
};

// Función para agregar un producto/servicio a la lista de ítems seleccionados
const agregarItem = (item) => {
  console.log('Item seleccionado:', item);
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedItems.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );
  if (!exists) {
    selectedItems.value.push(itemEntry);
    quantities.value[`${item.tipo}-${item.id}`] = 1;
    // Para órdenes de compra, el precio inicial es el precio de compra
    prices.value[`${item.tipo}-${item.id}`] = item.tipo === 'producto' ? (item.precio_compra || 0) : (item.precio || 0);
    console.log('Precio asignado:', prices.value[`${item.tipo}-${item.id}`]);
  }
  buscarProducto.value = '';
  mostrarProductos.value = false;
  calcularTotal();
};

// Función para eliminar un ítem de la lista de seleccionados
const eliminarItem = (entry) => {
  selectedItems.value = selectedItems.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[`${entry.tipo}-${entry.id}`];
  delete prices.value[`${entry.tipo}-${entry.id}`];
  calcularTotal();
};

// Funciones para ocultar las listas de búsqueda después de un breve retraso
const ocultarProveedoresDespuesDeTiempo = (event) => {
  setTimeout(() => {
    // Asegura que el blur no se dispare si el foco va a un ítem de la lista
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('proveedor-item')) {
      mostrarProveedores.value = false;
    }
  }, 300);
};

const ocultarProductosDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.classList.contains('producto-item')) {
      mostrarProductos.value = false;
    }
  }, 300);
};

// Observador para guardar el progreso en localStorage
watch(
  [() => form.proveedor_id, selectedItems, quantities, prices, proveedorSeleccionado],
  () => {
    const dataToSave = {
      proveedor_id: form.proveedor_id,
      proveedor_nombre: proveedorSeleccionado.value,
      selectedItems: selectedItems.value,
      quantities: quantities.value,
      prices: prices.value,
    };
    localStorage.setItem(`ordenCompraEnProgreso_${props.ordenCompra?.id || 'new'}`, JSON.stringify(dataToSave));
  },
  { deep: true }
);

// Al montar el componente, intenta cargar datos desde localStorage o inicializa con props
onMounted(() => {
  console.log('Orden de compra inicial:', props.ordenCompra);
  console.log('Proveedores:', props.proveedores);
  console.log('Productos:', props.productos);
  console.log('Servicios:', props.servicios);

  // Intentar cargar progreso de localStorage específico para esta orden o para una nueva
  const localStorageKey = `ordenCompraEnProgreso_${props.ordenCompra?.id || 'new'}`;
  const savedData = localStorage.getItem(localStorageKey);
  if (savedData) {
    const parsedData = JSON.parse(savedData);
    console.log('Datos cargados desde localStorage:', parsedData);
    form.proveedor_id = parsedData.proveedor_id;
    proveedorSeleccionado.value = parsedData.proveedor_nombre;
    buscarProveedor.value = parsedData.proveedor_nombre;
    selectedItems.value = Array.isArray(parsedData.selectedItems)
      ? parsedData.selectedItems.filter(
          (entry) => entry && typeof entry === 'object' && 'id' in entry && 'tipo' in entry
        )
      : selectedItems.value; // Si no hay datos guardados, mantiene los de la prop inicial
    quantities.value = parsedData.quantities || quantities.value;
    prices.value = parsedData.prices || prices.value;
    calcularTotal();
  } else {
    // Si no hay datos en localStorage, asegurar que el formulario refleja la orden inicial
    calcularTotal(); // Calcular total basado en los ítems y precios iniciales de la prop
  }

  window.addEventListener('beforeunload', handleBeforeUnload);
});

// Al desmontar el componente, elimina el listener
onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

// Advertencia de cambios no guardados al intentar salir
const handleBeforeUnload = (event) => {
  if (form.dirty) {
    const confirmationMessage = 'Tienes cambios no guardados. ¿Estás seguro de que quieres salir?';
    event.returnValue = confirmationMessage;
    return confirmationMessage;
  }
};

// Función para actualizar la orden de compra
const actualizarOrdenCompra = () => {
  form.items = selectedItems.value.map((entry) => ({
    id: entry.id,
    tipo: entry.tipo,
    cantidad: quantities.value[`${entry.tipo}-${entry.id}`] || 1,
    precio: prices.value[`${entry.tipo}-${entry.id}`] || 0,
  }));

  form.put(route('ordenes-compra.update', props.ordenCompra.id), {
    onSuccess: () => {
      localStorage.removeItem(`ordenCompraEnProgreso_${props.ordenCompra.id}`); // Elimina el progreso guardado
      console.log('Orden de compra actualizada con éxito');
    },
    onError: (error) => {
      console.error('Error al actualizar la orden de compra:', error);
    },
  });
};

// Función para limpiar el localStorage (útil para desarrollo/depuración)
const formatearLocalStorage = () => {
  localStorage.removeItem(`ordenCompraEnProgreso_${props.ordenCompra?.id || 'new'}`);
  form.proveedor_id = '';
  proveedorSeleccionado.value = null;
  buscarProveedor.value = '';
  selectedItems.value = [];
  quantities.value = {};
  prices.value = {};
  calcularTotal();
};
</script>

<style scoped>
.form-group {
  margin-bottom: 1rem;
}
</style>
