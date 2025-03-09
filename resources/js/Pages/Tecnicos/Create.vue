<template>
    <Head title="Crear Técnicos" />
    <div>
      <h1 class="text-2xl font-semibold mb-4">Crear Técnico</h1>
      <!-- Formulario de creación de técnicos -->
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
            Guardar Técnico
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { Head, useForm } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

  // Formulario para crear un técnico
  const form = useForm({
    nombre: '',
    apellido: '',
    email: '',
    telefono: '',
    direccion: ''
  });

  // Función para enviar el formulario
  const submit = () => {
    form.post(route('tecnicos.store'), {
      onSuccess: () => {
        form.reset(); // Limpia el formulario después de guardar
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
