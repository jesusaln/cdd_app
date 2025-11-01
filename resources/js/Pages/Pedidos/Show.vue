<template>
    <Head title="Mostrar Pedido" />
    <div class="pedidos-show">
      <h1 class="text-2xl font-semibold mb-6">Detalles del Pedido</h1>

      <div v-if="pedido" class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Cliente</h2>
          <p>{{ pedido.cliente.nombre_razon_social }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Productos</h2>
          <ul>
            <li v-for="producto in pedido.productos" :key="producto.id" class="mb-2">
              <strong>{{ producto.nombre }}</strong> - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
            </li>
          </ul>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Total</h2>
          <p>${{ pedido.total }}</p>
        </div>
        <div class="mb-4">
          <h2 class="text-lg font-medium text-gray-700">Estado</h2>
          <p>{{ pedido.estado }}</p>
        </div>
      </div>
      <div v-else>
        <p>Cargando detalles del pedido...</p>
      </div>

      <div v-if="pedido" class="mt-6 flex space-x-4">
        <Link :href="route('pedidos.pdf', pedido.id)" target="_blank" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
          📄 PDF
        </Link>
        <Link :href="route('pedidos.ticket', pedido.id)" target="_blank" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
          🖨️ Ticket
        </Link>
        <Link :href="route('pedidos.edit', pedido.id)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Editar
        </Link>
        <button @click="eliminarPedido(pedido.id)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
          Eliminar
        </button>
        <button @click="$emit('convertir-a-venta', pedido)" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
          Enviar a Ventas
        </button>
      </div>

      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    </div>
  </template>

  <script setup>
  import { Head, Link, router } from '@inertiajs/vue3';
  import { ref } from 'vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';

  defineProps({ pedido: Object });
  defineEmits(['convertir-a-venta']); // Declarar el evento

  const loading = ref(false);
  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const eliminarPedido = async (id) => {
    loading.value = true;
    if (confirm('¿Estás seguro de que deseas eliminar este pedido?')) {
      try {
        await router.delete(`/pedidos/${id}`, {
          onSuccess: () => {
            notyf.success('Pedido eliminado exitosamente.');
            router.visit(route('pedidos.index'));
          },
          onError: () => notyf.error('Error al eliminar el pedido.')
        });
      } catch (error) {
        notyf.error('Ocurrió un error inesperado.');
        console.error('Error al eliminar el pedido:', error);
      } finally {
        loading.value = false;
      }
    }
  };
  </script>
