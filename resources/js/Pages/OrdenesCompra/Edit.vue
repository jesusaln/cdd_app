<template>
  <Head title="Editar Órdenes de Compra" />
  <div class="ordenes-compra-edit p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-md">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Editar Orden de Compra</h1>

    <form @submit.prevent="updatePurchaseOrder" class="space-y-8">
      <div class="form-group relative">
        <label for="supplierSearch" class="block text-sm font-medium text-gray-700 mb-1">Buscar Proveedor</label>
        <input
          id="supplierSearch"
          v-model="supplierSearchQuery"
          type="text"
          placeholder="Buscar proveedor por nombre o razón social..."
          @focus="showSuppliers = true"
          @blur="hideSuppliersAfterDelay"
          autocomplete="off"
          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
        />
        <ul v-if="showSuppliers && filteredSuppliers.length > 0" class="absolute z-10 bg-white border border-gray-300 rounded-md shadow-lg w-full max-h-60 overflow-y-auto mt-1">
          <li
            v-for="supplier in filteredSuppliers"
            :key="supplier.id"
            @mousedown.prevent="selectSupplier(supplier)"
            class="px-4 py-2 cursor-pointer hover:bg-blue-50 hover:text-blue-700 transition duration-150 ease-in-out"
          >
            {{ supplier.nombre_razon_social }}
          </li>
        </ul>
        <p v-if="filteredSuppliers.length === 0 && supplierSearchQuery.length > 2" class="text-red-500 text-sm mt-2">
          No se encontraron proveedores.
        </p>
        <p v-if="form.errors.proveedor_id" class="text-red-500 text-sm mt-2">{{ form.errors.proveedor_id }}</p>
      </div>

      <div class="form-group relative">
        <label for="itemSearch" class="block text-sm font-medium text-gray-700 mb-1">Buscar Producto/Servicio</label>
        <input
          id="itemSearch"
          v-model="itemSearchQuery"
          type="text"
          placeholder="Buscar producto/servicio por nombre, código o número de serie..."
          @focus="showItems = true"
          @blur="hideItemsAfterDelay"
          autocomplete="off"
          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
        />
        <ul v-if="showItems && filteredItems.length > 0" class="absolute z-10 bg-white border border-gray-300 rounded-md shadow-lg w-full max-h-60 overflow-y-auto mt-1">
          <li
            v-for="item in filteredItems"
            :key="`${item.tipo}-${item.id}`"
            @mousedown.prevent="addItem(item)"
            class="px-4 py-2 cursor-pointer hover:bg-blue-50 hover:text-blue-700 transition duration-150 ease-in-out"
          >
            {{ item.nombre }} ({{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }})
            <span v-if="item.tipo === 'producto' && item.stock !== undefined" class="text-gray-500 text-sm ml-2">
              (Disponible: {{ item.stock }})
            </span>
          </li>
        </ul>
        <p v-if="filteredItems.length === 0 && itemSearchQuery.length > 2" class="text-red-500 text-sm mt-2">
          No se encontraron productos/servicios.
        </p>
      </div>

      <div v-if="form.items.length > 0" class="mt-6 border-t border-gray-200 pt-6">
        <h3 class="text-xl font-semibold mb-5 text-gray-800">Productos/Servicios Seleccionados</h3>
        <div class="space-y-4">
          <div
            v-for="(entry, index) in form.items"
            :key="`${entry.tipo}-${entry.id}`"
            class="flex items-center bg-gray-50 border border-gray-200 rounded-lg p-4 shadow-sm"
          >
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 flex-grow items-center">
              <span class="font-medium text-gray-800 col-span-full md:col-span-1">
                {{ getItemDetails(entry)?.nombre || 'Ítem no encontrado' }}
              </span>
              <div class="flex flex-col">
                <label :for="`quantity-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Cantidad</label>
                <input
                  :id="`quantity-${entry.tipo}-${entry.id}`"
                  v-model.number="entry.cantidad"
                  type="number"
                  min="1"
                  @input="calculateTotal"
                  class="px-3 py-2 border border-gray-300 rounded-md mt-1 w-full text-gray-800"
                />
              </div>
              <div class="flex flex-col">
                <label :for="`price-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Precio de Compra</label>
                <input
                  :id="`price-${entry.tipo}-${entry.id}`"
                  v-model.number="entry.precio"
                  type="number"
                  min="0"
                  step="0.01"
                  @input="calculateTotal"
                  class="px-3 py-2 border border-gray-300 rounded-md mt-1 w-full text-gray-800"
                />
              </div>
              <div class="flex flex-col">
                <label :for="`subtotal-${entry.tipo}-${entry.id}`" class="text-sm font-medium text-gray-700">Subtotal</label>
                <input
                  :id="`subtotal-${entry.tipo}-${entry.id}`"
                  :value="(entry.cantidad * entry.precio || 0).toFixed(2)"
                  type="text"
                  readonly
                  class="px-3 py-2 border border-gray-300 rounded-md mt-1 w-full bg-gray-100 cursor-not-allowed text-gray-800"
                />
              </div>
            </div>
            <button
              type="button"
              @click="removeItem(entry)"
              class="ml-4 text-red-600 hover:text-red-800 transition duration-150 ease-in-out p-2 rounded-full hover:bg-red-100"
              aria-label="Eliminar item"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <p v-if="form.errors.items" class="text-red-500 text-sm mt-2">{{ form.errors.items }}</p>
      </div>

      <div class="form-group flex justify-end items-center mt-6">
        <label for="total" class="text-lg font-bold text-gray-800 mr-4">Total:</label>
        <input
          id="total"
          v-model="form.total"
          type="text"
          readonly
          class="w-48 px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed text-lg font-semibold text-gray-800"
        />
      </div>

      <div class="flex justify-end space-x-4 mt-8">
        <Link :href="route('ordenescompra.index')" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-150 ease-in-out">
          Cancelar
        </Link>
        <button
          type="submit"
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!form.proveedor_id || form.items.length === 0 || form.processing"
        >
          {{ form.processing ? 'Actualizando...' : 'Actualizar Orden de Compra' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Set the layout for the page
defineOptions({ layout: AppLayout });

// Define component properties
const props = defineProps({
  ordenCompra: {
    type: Object,
    required: true,
  },
  proveedores: {
    type: Array,
    default: () => [],
  },
  productos: {
    type: Array,
    default: () => [],
  },
  servicios: {
    type: Array,
    default: () => [],
  },
});

// Helper function to safely parse float
const safeParseFloat = (value) => {
  const parsed = parseFloat(value);
  return isNaN(parsed) ? 0 : parsed;
};

// Inertia form initialization with existing purchase order data
const form = useForm({
  proveedor_id: props.ordenCompra?.proveedor_id || null,
  total: safeParseFloat(props.ordenCompra?.total || 0).toFixed(2),
  // Initialize items directly from the existing purchase order items
  items: props.ordenCompra?.items?.map(item => ({
    id: item.id,
    // Determine the type correctly from the pivot table's item_type
    tipo: item.pivot?.item_type === 'App\\Models\\Producto' ? 'producto' : 'servicio',
    cantidad: item.pivot?.cantidad || 1,
    precio: safeParseFloat(item.pivot?.precio || 0), // Ensure price is a float
  })) || [],
});

// Reactive variables for search inputs and dropdown visibility
const supplierSearchQuery = ref(props.ordenCompra?.proveedor?.nombre_razon_social || '');
const itemSearchQuery = ref('');
const showSuppliers = ref(false);
const showItems = ref(false);

// Stores the selected supplier's name for display (optional, but good for clarity)
const selectedSupplierName = ref(props.ordenCompra?.proveedor?.nombre_razon_social || null);

// Computed property for filtering suppliers based on search query
const filteredSuppliers = computed(() => {
  if (supplierSearchQuery.value.length < 1) return []; // Require at least 1 character for search
  return props.proveedores.filter((supplier) =>
    supplier.nombre_razon_social.toLowerCase().includes(supplierSearchQuery.value.toLowerCase())
  );
});

// Computed property for filtering products/services based on search query
const filteredItems = computed(() => {
  if (itemSearchQuery.value.length < 1) return []; // Require at least 1 character for search

  const allItems = [
    ...(props.productos || []).map(product => ({
      ...product,
      tipo: 'producto',
    })),
    ...(props.servicios || []).map(service => ({
      ...service,
      tipo: 'servicio',
    })),
  ];

  return allItems.filter(item =>
    item.nombre.toLowerCase().includes(itemSearchQuery.value.toLowerCase()) ||
    (item.codigo?.toLowerCase().includes(itemSearchQuery.value.toLowerCase())) ||
    (item.numero_de_serie?.toLowerCase().includes(itemSearchQuery.value.toLowerCase())) ||
    (item.codigo_barras?.toLowerCase().includes(itemSearchQuery.value.toLowerCase()))
  );
});

/**
 * Retrieves full details of an item (product or service) by its ID and type.
 * This is crucial for displaying item names correctly.
 * @param {object} entry - An object with 'id' and 'tipo' properties (from form.items).
 * @returns {object|null} The full item object from props.productos or props.servicios, or null if not found.
 */
const getItemDetails = (entry) => {
  if (!entry || typeof entry.id === 'undefined' || !entry.tipo) {
    console.error('Entrada inválida para getItemDetails:', entry);
    return null;
  }

  // Use a more robust check for collections being arrays
  const products = Array.isArray(props.productos) ? props.productos : [];
  const services = Array.isArray(props.servicios) ? props.servicios : [];

  if (entry.tipo === 'producto') {
    return products.find((p) => p.id === entry.id) || null;
  }
  if (entry.tipo === 'servicio') {
    return services.find((s) => s.id === entry.id) || null;
  }
  console.warn(`Tipo de ítem desconocido en getItemDetails: ${entry.tipo} para ID: ${entry.id}`);
  return null;
};

/**
 * Calculates the total sum of all selected items.
 */
const calculateTotal = () => {
  let total = 0;
  for (const entry of form.items) {
    const quantity = safeParseFloat(entry.cantidad) || 0;
    const price = safeParseFloat(entry.precio) || 0;
    total += quantity * price;
  }
  form.total = total.toFixed(2);
};

// Watch for changes in the form.items array to recalculate total
watch(() => form.items, calculateTotal, { deep: true });

/**
 * Selects a supplier from the search results.
 * @param {object} supplier - The selected supplier object.
 */
const selectSupplier = (supplier) => {
  form.proveedor_id = supplier.id;
  supplierSearchQuery.value = supplier.nombre_razon_social;
  selectedSupplierName.value = supplier.nombre_razon_social;
  showSuppliers.value = false;
};

/**
 * Adds a product or service to the list of selected items.
 * Prevents adding duplicates.
 * @param {object} item - The item (product or service) to add.
 */
const addItem = (item) => {
  const exists = form.items.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );
  if (!exists) {
    form.items.push({
      id: item.id,
      tipo: item.tipo,
      cantidad: 1, // Default quantity
      precio: item.tipo === 'producto' ? (safeParseFloat(item.precio_compra) || 0) : (safeParseFloat(item.precio) || 0),
    });
    calculateTotal(); // Recalculate total after adding
  }
  itemSearchQuery.value = ''; // Clear search query
  showItems.value = false; // Hide search results
};

/**
 * Removes an item from the list of selected items.
 * @param {object} entry - The item entry to remove.
 */
const removeItem = (entryToRemove) => {
  form.items = form.items.filter(
    (entry) => !(entry.id === entryToRemove.id && entry.tipo === entryToRemove.tipo)
  );
  calculateTotal(); // Recalculate total after removal
};

// Timeout variables to manage dropdown hiding
let supplierBlurTimeout = null;
let itemBlurTimeout = null;

/**
 * Hides the supplier search results after a short delay.
 * This prevents the dropdown from disappearing immediately if a click
 * is intended for one of the list items.
 */
const hideSuppliersAfterDelay = () => {
  supplierBlurTimeout = setTimeout(() => {
    showSuppliers.value = false;
  }, 150); // Short delay
};

/**
 * Hides the product/service search results after a short delay.
 */
const hideItemsAfterDelay = () => {
  itemBlurTimeout = setTimeout(() => {
    showItems.value = false;
  }, 150); // Short delay
};

// Clear timeouts if component is unmounted
onBeforeUnmount(() => {
  clearTimeout(supplierBlurTimeout);
  clearTimeout(itemBlurTimeout);
});

// Watch for form changes to save progress to localStorage
watch(
  () => ({
    proveedor_id: form.proveedor_id,
    proveedor_nombre: selectedSupplierName.value,
    items: form.items,
  }),
  (newValue) => {
    const dataToSave = {
      proveedor_id: newValue.proveedor_id,
      proveedor_nombre: newValue.proveedor_nombre,
      items: newValue.items,
    };
    localStorage.setItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`, JSON.stringify(dataToSave));
  },
  { deep: true }
);

// On component mount, attempt to load saved progress from localStorage
onMounted(() => {
  console.log('Initial Purchase Order Data:', props.ordenCompra);
  console.log('All Suppliers (prop):', props.proveedores);
  console.log('All Products (prop):', props.productos);
  console.log('All Services (prop):', props.servicios);

  const localStorageKey = `ordenCompraEnProgreso_edit_${props.ordenCompra.id}`;
  const savedData = localStorage.getItem(localStorageKey);
  if (savedData) {
    try {
      const parsedData = JSON.parse(savedData);
      console.log('Data loaded from localStorage:', parsedData);
      form.proveedor_id = parsedData.proveedor_id;
      selectedSupplierName.value = parsedData.proveedor_nombre;
      supplierSearchQuery.value = parsedData.proveedor_nombre;

      // Ensure that loaded items are valid and correspond to available products/services
      // Filter out items that cannot be found in the current props.productos or props.servicios
      form.items = Array.isArray(parsedData.items)
        ? parsedData.items.filter(entry => {
            if (!entry || typeof entry !== 'object' || typeof entry.id === 'undefined' || !entry.tipo) {
              console.warn('Entrada de ítem inválida en localStorage:', entry);
              return false;
            }
            // Check if the item actually exists in the props data
            const itemExists = getItemDetails(entry) !== null;
            if (!itemExists) {
                console.warn(`Ítem no encontrado en las props al cargar desde localStorage: ID ${entry.id}, Tipo ${entry.tipo}. Se eliminará de la lista.`, entry);
            }
            return itemExists;
          })
        : [];
      calculateTotal();
    } catch (e) {
      console.error('Error al analizar los datos guardados de localStorage:', e);
      localStorage.removeItem(localStorageKey); // Clear corrupted data
    }
  } else {
    // If no saved data, ensure initial total is calculated from props
    calculateTotal();
  }

  // Add event listener for beforeunload to warn about unsaved changes
  window.addEventListener('beforeunload', handleBeforeUnload);
});

// On component unmount, remove the event listener
onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

/**
 * Handles the 'beforeunload' event to warn the user about unsaved changes.
 * @param {Event} event - The beforeunload event.
 */
const handleBeforeUnload = (event) => {
  if (form.isDirty) { // Inertia's built-in `isDirty` property
    const confirmationMessage = 'Tienes cambios no guardados. ¿Estás seguro de que quieres salir?';
    event.returnValue = confirmationMessage;
    return confirmationMessage;
  }
};

/**
 * Submits the form to update the purchase order.
 */
const updatePurchaseOrder = () => {
  // The `form.items` is already structured correctly for the backend
  // as it mirrors the desired payload (id, tipo, cantidad, precio).

  form.put(route('ordenescompra.update', props.ordenCompra.id), { // Cambiado a 'ordenescompra.update'
    onSuccess: () => {
      localStorage.removeItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`); // Clear saved progress
      console.log('Orden de compra actualizada con éxito!');
      // Optionally, show a success message to the user, e.g., with a flash message
    },
    onError: (errors) => {
      console.error('Error al actualizar la orden de compra:', errors);
      // Inertia handles displaying errors from the backend, but you can add custom logic here
    },
    preserveScroll: true, // Keep scroll position on redirect
  });
};

// Function to clear localStorage for debugging/development (can be removed in production)
const clearLocalStorageProgress = () => {
  localStorage.removeItem(`ordenCompraEnProgreso_edit_${props.ordenCompra.id}`);
  // Reset form fields to initial state based on props or empty
  form.proveedor_id = props.ordenCompra?.proveedor_id || null;
  selectedSupplierName.value = props.ordenCompra?.proveedor?.nombre_razon_social || null;
  supplierSearchQuery.value = props.ordenCompra?.proveedor?.nombre_razon_social || '';
  form.items = props.ordenCompra?.items?.map(item => ({
    id: item.id,
    tipo: item.pivot?.item_type === 'App\\Models\\Producto' ? 'producto' : 'servicio',
    cantidad: item.pivot?.cantidad || 1,
    precio: safeParseFloat(item.pivot?.precio || 0),
  })) || [];
  calculateTotal();
  console.log('Progreso de localStorage limpiado y formulario reseteado.');
};
</script>

<style scoped>
/* Scoped styles remain minimal as Tailwind CSS is preferred */
.form-group {
  margin-bottom: 1.5rem;
}
</style>
