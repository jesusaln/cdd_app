<template>
    <Head title="Editar Técnico" />
    <div>
      <h1 class="text-2xl font-semibold mb-4">Editar Técnico</h1>
      <!-- Formulario de edición de técnicos -->
      <form @submit.prevent="submit">
        <div v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</div>
        <div class="space-y-4">
          <!-- Nombre -->
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input
              v-model="form.nombre"
              type="text"
              id="nombre"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              @blur="convertirAMayusculas('nombre')"
              required
            />
            <p v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</p>
          </div>

          <!-- Apellido -->
          <div>
            <label for="apellido" class="block text-sm font-medium text-gray-700">Apellido</label>
            <input
              v-model="form.apellido"
              type="text"
              id="apellido"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              @blur="convertirAMayusculas('apellido')"
              required
            />
            <p v-if="form.errors.apellido" class="text-red-500 text-sm">{{ form.errors.apellido }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              id="email"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              required
            />
            <p v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</p>
          </div>

          <!-- Teléfono -->
          <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input
              v-model="form.telefono"
              type="text"
              id="telefono"
              maxlength="10"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              @input="validarTelefono"
            />
            <p v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono }}</p>
          </div>

          <!-- Dirección -->
          <div>
            <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
            <input
              v-model="form.direccion"
              type="text"
              id="direccion"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              @blur="convertirAMayusculas('direccion')"
            />
            <p v-if="form.errors.direccion" class="text-red-500 text-sm">{{ form.errors.direccion }}</p>
          </div>
        </div>
        <div class="mt-6">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
            Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { Head, useForm } from '@inertiajs/vue3';
  import Dashboard from '@/Pages/Dashboard.vue'; // Importa el layout del dashboard

  // Define el layout del dashboard
  defineOptions({ layout: Dashboard });

  // Props para recibir los datos del técnico a editar
  const props = defineProps({
    tecnico: Object
  });

  // Formulario para editar un técnico
  const form = useForm({
    nombre: props.tecnico.nombre,
    apellido: props.tecnico.apellido,
    email: props.tecnico.email,
    telefono: props.tecnico.telefono,
    direccion: props.tecnico.direccion
  });

  // Función para enviar el formulario
  const submit = () => {
    form.put(route('tecnicos.update', props.tecnico.id), {
      onSuccess: () => {
        // Redirigir o mostrar un mensaje de éxito
      },
    });
  };

  // Método para convertir a mayúsculas
  const convertirAMayusculas = (campo) => {
    if (form[campo]) {
      form[campo] = form[campo].toUpperCase();
    }
  };

  // Validación del teléfono
  const validarTelefono = () => {
    const telefonoRegex = /^\d{10}$/;
    if (!telefonoRegex.test(form.telefono)) {
      form.setError('telefono', 'El teléfono debe tener 10 dígitos.');
    } else {
      form.clearErrors('telefono');
    }
  };
  </script>
