<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import EmpresaModal from '@/Components/EmpresaModal.vue';
import { defineProps } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
  titulo: String,  // Recibes el título desde el controlador
  empresas: Array
});

document.title = props.titulo || 'Empresas';  // Asegúrate de que el título tenga un valor predeterminado

const headers = ['Nombre/Razón Social', 'RFC', 'Régimen Fiscal', 'Uso CFDI', 'Email', 'Teléfono', 'Dirección'];
const loading = ref(false);
const searchTerm = ref('');
const empresaSeleccionada = ref(null);
const isModalOpen = ref(false);
const isConfirmOpen = ref(false);
const empresaAEliminar = ref(null);

const empresas = ref(props.empresas || []); // Asegúrate de que empresas tenga un valor predeterminado

const empresasFiltradas = computed(() => {
  return empresas.value.filter(empresa => {
    return (empresa.nombre_razon_social || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
           (empresa.rfc || '').toLowerCase().includes(searchTerm.value.toLowerCase());
  });
});

const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

const openModal = (empresa) => {
  if (empresa) {
    empresaSeleccionada.value = empresa;
    isModalOpen.value = true;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
};

const confirmarEliminacion = (empresa) => {
  if (empresa) {
    empresaAEliminar.value = empresa;
    isConfirmOpen.value = true;
  }
};

const eliminarEmpresaConfirmado = async () => {
  if (!empresaAEliminar.value) return;

  loading.value = true;
  try {
    await router.delete(route('empresas.destroy', empresaAEliminar.value.id), {
      onSuccess: () => {
        notyf.success('Empresa eliminada exitosamente.');
        empresas.value = empresas.value.filter(e => e.id !== empresaAEliminar.value.id);
      },
      onError: () => notyf.error('Error al eliminar la empresa.')
    });
  } catch (error) {
    notyf.error('Ocurrió un error inesperado.');
  } finally {
    isConfirmOpen.value = false;
    loading.value = false;
  }
};
</script>
<template>
    <Head title="Empresa" />
    Empresas
</template>
