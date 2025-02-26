<template>
    <Head title="Mostrar Venta" />
    <div class="ventas-show">
      <!-- Título de la página -->
      <h1 class="text-2xl font-semibold mb-6">Detalles de la Venta</h1>

      <!-- Verificar si venta no es null -->
      <div v-if="venta" class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Cliente</h2>
          <p>{{ venta.cliente.nombre_razon_social }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Productos</h2>
          <ul>
            <li v-for="producto in venta.productos" :key="producto.id" class="mb-2">
              <strong>{{ producto.nombre }}</strong> - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
            </li>
          </ul>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Total</h2>
          <p>${{ venta.total }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Estado</h2>
          <p>{{ venta.estado }}</p>
        </div>
      </div>
      <div v-else>
        <p>Cargando detalles de la venta...</p>
      </div>

      <!-- Botones de acción -->
      <div v-if="venta" class="mt-6 flex space-x-4">
        <Link :href="route('ventas.edit', venta.id)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Editar
        </Link>
        <button @click="eliminarVenta(venta.id)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
          Eliminar
        </button>
      </div>

      <!-- Spinner de carga -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    </div>
  </template>

  <script setup>
  import { Head, Link, router } from '@inertiajs/vue3';
  import { ref, defineProps } from 'vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';

  // Propiedades
  const props = defineProps({ venta: Object });
  const loading = ref(false);

  // Configuración de Notyf para notificaciones
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  // Función para eliminar una venta
  const eliminarVenta = async (id) => {
    loading.value = true;
    if (confirm('¿Estás seguro de que deseas eliminar esta venta?')) {
      try {
        await router.delete(`/ventas/${id}`, {
          onSuccess: () => {
            notyf.success('Venta eliminada exitosamente.');
            router.visit(route('ventas.index'));  // Regresar a la lista de ventas
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
  </script>

  <style scoped>
  /* Aquí van tus estilos personalizados */
  </style>
