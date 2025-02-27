<template>
    <Head title="Ventas" />
    <div class="ventas-index">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Listado de Ventas</h1>

      <!-- Botón de crear venta y campo de búsqueda -->
      <div class="mb-4 flex justify-between items-center">
        <Link :href="route('ventas.create')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
          Crear Venta
        </Link>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por cliente o producto"
          class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Tabla de ventas -->
      <div v-if="ventasFiltradas.length > 0" class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Productos</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="venta in ventasFiltradas" :key="venta.id" class="hover:bg-gray-100">
              <td class="px-4 py-3 text-sm text-gray-700">{{ venta.id }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ venta.cliente.nombre_razon_social }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <ul>
                  <li v-for="producto in venta.productos" :key="producto.id">
                    {{ producto.nombre }} - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
                  </li>
                </ul>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">${{ venta.total }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ venta.estado }}</td>
              <td class="px-4 py-3 flex space-x-2">
                <button @click="editarVenta(venta.id)" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                  Editar
                </button>
                <button @click="confirmarEliminacion(venta.id)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                  Eliminar
                </button>
                <button @click="verDetalles(venta)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                  Ver Detalles
                </button>
                <button @click="generarPDF(venta)" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600">
                  Generar PDF
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mensaje si no hay ventas -->
      <div v-else class="text-center text-gray-500 mt-4">
        No hay ventas registradas.
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Diálogo de confirmación de eliminación -->
      <div v-if="showConfirmationDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <p class="mb-4">¿Estás seguro de que deseas eliminar esta venta?</p>
          <div class="flex justify-end">
            <button @click="cancelarEliminacion" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 mr-2">
              Cancelar
            </button>
            <button @click="eliminarVenta" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Diálogo para mostrar detalles de la venta -->
      <div v-if="showDetailsDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
          <Show :venta="selectedVenta" />
          <div class="flex justify-end mt-4">
            <button @click="cerrarDetalles" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { Head, Link, router } from '@inertiajs/vue3';
  import { ref, computed } from 'vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';
  import jsPDF from 'jspdf';
  import Dashboard from '@/Pages/Dashboard.vue';
  import Show from './Show.vue'; // Asegúrate de que la ruta sea correcta

  // Define el layout del dashboard
  defineOptions({ layout: Dashboard });

  // Propiedades
  const props = defineProps({ ventas: Array });
  const searchTerm = ref('');
  const loading = ref(false);
  const showConfirmationDialog = ref(false);
  const ventaIdToDelete = ref(null);
  const showDetailsDialog = ref(false);
  const selectedVenta = ref(null);

  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Variable reactiva local para almacenar las ventas
  const ventas = ref([...props.ventas]);

  // Filtrado de ventas
  const ventasFiltradas = computed(() => {
    return ventas.value.filter(venta => {
      return venta.cliente.nombre_razon_social.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
             venta.productos.some(producto => producto.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()));
    });
  });

  // Función para editar una venta
  const editarVenta = (id) => {
    router.get(`/ventas/${id}/edit`);
  };

  // Función para confirmar la eliminación de una venta
  const confirmarEliminacion = (id) => {
    ventaIdToDelete.value = id;
    showConfirmationDialog.value = true;
  };

  // Función para cancelar la eliminación
  const cancelarEliminacion = () => {
    ventaIdToDelete.value = null;
    showConfirmationDialog.value = false;
  };

  // Función para eliminar una venta
  const eliminarVenta = async () => {
    if (ventaIdToDelete.value) {
      loading.value = true;
      try {
        await router.delete(`/ventas/${ventaIdToDelete.value}`, {
          onSuccess: () => {
            notyf.success('Venta eliminada exitosamente.');
            ventas.value = ventas.value.filter(venta => venta.id !== ventaIdToDelete.value);
            showConfirmationDialog.value = false;
          },
          onError: () => notyf.error('Error al eliminar la venta.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
      } finally {
        loading.value = false;
      }
    }
  };

  // Función para mostrar detalles de la venta
  const verDetalles = (venta) => {
    selectedVenta.value = venta;
    showDetailsDialog.value = true;
  };

  // Función para cerrar el diálogo de detalles
  const cerrarDetalles = () => {
    selectedVenta.value = null;
    showDetailsDialog.value = false;
  };

  // Función para generar el PDF de la venta
  const generarPDF = (venta) => {
    const doc = new jsPDF();
    doc.setFont("helvetica", "normal");

    // Encabezado de la empresa mejorado
doc.setFontSize(20);
doc.setFont("helvetica", "bold");
doc.text("CLIMAS DEL DESIERTO", 105, 18, { align: "center" });

doc.setFontSize(12);
doc.setFont("helvetica", "normal");
doc.text("JESUS ALBERTO LOPEZ NORIEGA", 105, 24, { align: "center" });

doc.setFontSize(10);
doc.text("Dirección: Av. Paseo de la Reina, 2345", 105, 28, { align: "center" });
doc.text("Teléfono: (55) 5555-5555", 105, 32, { align: "center" });
doc.text("Email: jesus@climasdeldesierto.com", 105, 36, { align: "center" });
doc.text("Página Web: www.climasdeldesierto.com", 105, 40, { align: "center" });

    // Título del documento
    doc.setFontSize(14);
    doc.setFont("helvetica", "bold");
    doc.text("Nota de Venta", 14, 18);

    // Información de la venta
    doc.setFontSize(10);
    doc.setFont("helvetica", "bold");
    doc.text("Número:", 14, 84);
    doc.setFont("helvetica", "normal");
    doc.text(`${venta.id}`, 40, 84);
    doc.setFont("helvetica", "bold");
    doc.text("Fecha:", 140, 84);
    doc.setFont("helvetica", "normal");
    doc.text(new Date().toLocaleDateString(), 160, 84);
    doc.line(14, 90, 190, 90); // Línea divisoria



// Tabla invisible para la información del cliente
const clienteInfo = [
    ["Nombre", venta.cliente.nombre_razon_social],
    //["Dirección", venta.cliente.direccion], // Asegúrate de que la propiedad 'direccion' exista en el objeto 'cliente'
    //["Teléfono", venta.cliente.telefono],  // Asegúrate de que la propiedad 'telefono' exista en el objeto 'cliente'
    //["Correo", venta.cliente.email]        // Asegúrate de que la propiedad 'email' exista en el objeto 'cliente'
];

// Establecer las posiciones de las filas
let yPos = 104;
const columnWidth = 60; // Ancho de la columna para las etiquetas

// Dibuja las celdas invisibles de la tabla
clienteInfo.forEach((row, index) => {
    doc.text(row[0], 14, yPos);  // Mostrar el nombre del campo
    doc.text(row[1], 14 + columnWidth, yPos);  // Mostrar el valor correspondiente
    yPos += 8;  // Espaciado entre las filas
});

// Línea divisoria después de la tabla
doc.line(14, yPos, 190, yPos); // Línea divisoria debajo de la tabla

    // Productos
    let yOffset = 110;
    venta.productos.forEach((producto, index) => {
      const cantidad = producto.pivot.cantidad || 1;
      const precio = producto.pivot.precio || 0;
      const subtotal = (cantidad * precio).toFixed(2);
      doc.text(`${producto.nombre} - ${cantidad} x $${precio} = $${subtotal}`, 14, yOffset);
      yOffset += 8; // Ajusta el espacio entre líneas
    });

    // Total
    doc.setFont("helvetica", "bold");
    doc.text(`Total: $${venta.total}`, 14, yOffset + 10);

    // Guardar o abrir el PDF
    doc.save(`Nota_${venta.id}.pdf`);
  };
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
