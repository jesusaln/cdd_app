<template>
  <AppLayout>
    <Head title="Cotizaciones" />
    <DataTable
      title="Gestión de Cotizaciones"
      description="Administra todas tus cotizaciones"
      :data="cotizaciones"
      :headers="[
  { key: 'created_at', label: 'Fecha', sortable: true, type: 'date', dateFormat: 'long' },
  { key: 'id', label: 'ID', sortable: true },
  { key: 'cliente.nombre_razon_social', label: 'Cliente', sortable: true },
  { key: 'total', label: 'Total', sortable: true },
  { key: 'estado', label: 'Estado', sortable: true }
]"
      :status-options="[
        { value: 'pendiente', label: 'Pendiente' },
        { value: 'aprobado', label: 'Aprobado' },
        { value: 'rechazado', label: 'Rechazado' }
      ]"
      create-url="/cotizaciones/create"
      :sort-options="[
        { value: 'created_at-desc', label: 'Más recientes' },
        { value: 'created_at-asc', label: 'Más antiguos' },
        { value: 'total-desc', label: 'Mayor valor' }
      ]"
      has-actions
    >
      <!-- Celda personalizada para estado -->
      <template #cell-estado="{ value }">
        <span :class="getEstadoClass(value)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
          {{ getEstadoLabel(value) }}
        </span>
      </template>

      <!-- Celda personalizada para total -->
      <template #cell-total="{ value }">
        ${{ Number(value).toFixed(2) }}
      </template>

      <!-- Acciones -->
      <template #actions="{ item }">
        <div class="flex space-x-2 justify-end">
          <button @click="ver(item)" class="text-blue-600 hover:text-blue-900">Ver</button>
          <button @click="editar(item.id)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
        </div>
      </template>
    </DataTable>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/Datatable.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
  cotizaciones: Array
});

const getEstadoLabel = (estado) => ({
  pendiente: 'Pendiente',
  aprobado: 'Aprobado',
  rechazado: 'Rechazado'
}[estado] || estado);

const getEstadoClass = (estado) => ({
  pendiente: 'bg-yellow-100 text-yellow-800',
  aprobado: 'bg-green-100 text-green-800',
  rechazado: 'bg-red-100 text-red-800'
}[estado] || 'bg-gray-100 text-gray-800');

const ver = (item) => alert('Ver ID: ' + item.id);
const editar = (id) => router.get(`/cotizaciones/${id}/edit`);
</script>
