<template>
    <Head title="Crear Usuario" />
    <div class="usuarios-create max-w-lg mx-auto p-6 bg-gray-50 rounded-lg shadow-md">
      <h1 class="text-2xl font-semibold mb-6 text-center">Crear Nuevo Usuario</h1>
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Nombre -->
        <div class="form-group">
          <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
          <input
            v-model="form.name"
            type="text"
            id="name"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="Nombre completo"
            :class="{ 'border-red-500': form.errors.name }"
          />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div class="form-group">
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            id="email"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="correo@ejemplo.com"
            :class="{ 'border-red-500': form.errors.email }"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- Rol -->
        <div class="form-group">
          <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
          <select
            v-model="form.role"
            id="role"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': form.errors.role }"
          >
            <option value="" disabled>Selecciona un rol</option>
            <option v-for="rol in props.roles" :key="rol.id" :value="rol.name">
              {{ rol.label || rol.name }}
            </option>
          </select>
          <InputError class="mt-2" :message="form.errors.role" />
        </div>

        <!-- Contraseña -->
        <div class="form-group">
          <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input
            v-model="form.password"
            type="password"
            id="password"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="••••••••"
            :class="{ 'border-red-500': form.errors.password }"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <!-- Confirmar Contraseña -->
        <div class="form-group">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            id="password_confirmation"
            class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="••••••••"
            :class="{ 'border-red-500': form.errors.password_confirmation }"
          />
          <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-4">
          <Link :href="route('usuarios.index')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
            Cancelar
          </Link>
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
            :disabled="form.processing"
          >
            <span v-if="form.processing">Creando...</span>
            <span v-else>Crear Usuario</span>
          </button>
        </div>
      </form>
    </div>
  </template>

  <script setup>
  import { Head, useForm, Link } from '@inertiajs/vue3';
  import InputError from '@/Components/InputError.vue';
  import { Notyf } from 'notyf';
  import 'notyf/notyf.min.css';
  import AppLayout from '@/Layouts/AppLayout.vue';

  defineOptions({ layout: AppLayout });

  const props = defineProps({
    roles: Array, // Lista de roles desde el backend
  });

  const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '', // Sin valor por defecto para forzar selección
  });

  const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

  const submit = () => {
    form.post(route('usuarios.store'), {
      onSuccess: () => {
        notyf.success('Usuario creado exitosamente.');
        form.reset();
      },
      onError: () => {
        notyf.error('Error al crear el usuario. Revisa los campos.');
      },
    });
  };
  </script>

  <style scoped>
  .form-group {
    margin-bottom: 1.5rem;
  }

  button:disabled {
    background-color: #d1d5db;
    cursor: not-allowed;
  }
  </style>
