<template>
  <Head title="Crear Usuario" />
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Crear Nuevo Usuario</h1>
        <p class="text-gray-600">Completa la información para registrar un nuevo usuario en el sistema</p>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <!-- Progress Bar -->
        <div class="h-1 bg-gray-100">
          <div class="h-1 bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-300"
               :style="`width: ${formProgress}%`"></div>
        </div>

        <form @submit.prevent="submit" class="p-8 space-y-8">
          <!-- Personal Information Section -->
          <div class="space-y-6">
            <div class="border-b border-gray-200 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Información Personal
              </h2>
              <p class="text-sm text-gray-600 mt-1">Datos básicos del usuario</p>
            </div>

            <!-- Nombre -->
            <div class="form-group">
              <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                Nombre Completo *
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <input
                  v-model="form.name"
                  type="text"
                  id="name"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                  placeholder="Ingresa el nombre completo"
                  :class="{
                    'border-red-300 bg-red-50 focus:ring-red-500': form.errors.name,
                    'border-green-300 bg-green-50': form.name && !form.errors.name
                  }"
                  autocomplete="name"
                />
                <div v-if="form.name && !form.errors.name" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
              <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="form-group">
              <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                Correo Electrónico *
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                  </svg>
                </div>
                <input
                  v-model="form.email"
                  type="email"
                  id="email"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                  placeholder="correo@ejemplo.com"
                  :class="{
                    'border-red-300 bg-red-50 focus:ring-red-500': form.errors.email,
                    'border-green-300 bg-green-50': form.email && !form.errors.email && isValidEmail
                  }"
                  autocomplete="email"
                />
                <div v-if="form.email && !form.errors.email && isValidEmail" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
              <InputError class="mt-2" :message="form.errors.email" />
            </div>
          </div>

          <!-- Role Section -->
          <div class="space-y-6">
            <div class="border-b border-gray-200 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Permisos y Acceso
              </h2>
              <p class="text-sm text-gray-600 mt-1">Define el rol y permisos del usuario</p>
            </div>

            <!-- Rol -->
            <div class="form-group">
              <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                Rol del Usuario *
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </div>
                <select
                  v-model="form.role"
                  id="role"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white appearance-none"
                  :class="{
                    'border-red-300 bg-red-50 focus:ring-red-500': form.errors.role,
                    'border-green-300 bg-green-50': form.role && !form.errors.role
                  }"
                >
                  <option value="" disabled>Selecciona un rol</option>
                  <option v-for="rol in roles" :key="rol.id" :value="rol.name">
                    {{ rol.label || rol.name }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>
              <InputError class="mt-2" :message="form.errors.role" />
              <div v-if="form.role" class="mt-2 text-sm text-blue-600">
                <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Rol seleccionado: {{ form.role }}
              </div>
            </div>
          </div>

          <!-- Security Section -->
          <div class="space-y-6">
            <div class="border-b border-gray-200 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Seguridad
              </h2>
              <p class="text-sm text-gray-600 mt-1">Configuración de contraseña de acceso</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Contraseña -->
              <div class="form-group">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                  Contraseña *
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                  </div>
                  <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    placeholder="Mínimo 8 caracteres"
                    :class="{
                      'border-red-300 bg-red-50 focus:ring-red-500': form.errors.password,
                      'border-green-300 bg-green-50': form.password && form.password.length >= 8 && !form.errors.password
                    }"
                    autocomplete="new-password"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                  >
                    <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
                <div class="mt-2">
                  <div class="flex items-center space-x-2">
                    <div class="flex space-x-1">
                      <div v-for="i in 4" :key="i"
                           class="h-1 w-6 rounded-full transition-all duration-200"
                           :class="passwordStrength >= i ? 'bg-green-500' : 'bg-gray-200'"></div>
                    </div>
                    <span class="text-xs text-gray-600">{{ passwordStrengthText }}</span>
                  </div>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
              </div>

              <!-- Confirmar Contraseña -->
              <div class="form-group">
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                  Confirmar Contraseña *
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <input
                    v-model="form.password_confirmation"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    id="password_confirmation"
                    class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    placeholder="Repite la contraseña"
                    :class="{
                      'border-red-300 bg-red-50 focus:ring-red-500': form.errors.password_confirmation || (form.password_confirmation && form.password !== form.password_confirmation),
                      'border-green-300 bg-green-50': form.password_confirmation && form.password === form.password_confirmation && !form.errors.password_confirmation
                    }"
                    autocomplete="new-password"
                  />
                  <button
                    type="button"
                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                  >
                    <svg v-if="showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
                <div v-if="form.password_confirmation && form.password !== form.password_confirmation" class="mt-2 text-sm text-red-600 flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  Las contraseñas no coinciden
                </div>
                <div v-else-if="form.password_confirmation && form.password === form.password_confirmation" class="mt-2 text-sm text-green-600 flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  Las contraseñas coinciden
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="pt-6 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
              <Link :href="route('usuarios.index')"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-gray-300 rounded-xl text-gray-700 bg-white hover:bg-gray-50 font-semibold transition-all duration-200 hover:shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancelar
              </Link>
              <button
                type="submit"
                class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:hover:shadow-none"
                :disabled="form.processing || !isFormValid"
              >
                <div v-if="form.processing" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Creando Usuario...</span>
                </div>
                <div v-else class="flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                  <span>Crear Usuario</span>
                </div>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  roles: Array, // Lista de roles desde el backend
});

// Reactive variables para mostrar/ocultar contraseñas
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Form usando Inertia
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '', // Sin valor por defecto para forzar selección
});

// Instancia de Notyf para notificaciones
const notyf = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' },
  ripple: true,
  dismissible: true
});

// Computed para validar email
const isValidEmail = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(form.email);
});

// Computed para calcular la fortaleza de la contraseña
const passwordStrength = computed(() => {
  const password = form.password;
  if (!password) return 0;

  let strength = 0;

  // Longitud mínima
  if (password.length >= 8) strength++;

  // Contiene números
  if (/\d/.test(password)) strength++;

  // Contiene letras minúsculas y mayúsculas
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;

  // Contiene caracteres especiales
  if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

  return strength;
});

// Computed para el texto de fortaleza de contraseña
const passwordStrengthText = computed(() => {
  switch (passwordStrength.value) {
    case 0:
    case 1:
      return 'Débil';
    case 2:
      return 'Regular';
    case 3:
      return 'Buena';
    case 4:
      return 'Excelente';
    default:
      return '';
  }
});

// Computed para verificar si el formulario es válido
const isFormValid = computed(() => {
  return form.name &&
         form.email &&
         isValidEmail.value &&
         form.password &&
         form.password.length >= 8 &&
         form.password_confirmation &&
         form.password === form.password_confirmation &&
         form.role;
});

// Computed para el progreso del formulario
const formProgress = computed(() => {
  let progress = 0;
  const totalFields = 5;

  if (form.name) progress++;
  if (form.email && isValidEmail.value) progress++;
  if (form.password && form.password.length >= 8) progress++;
  if (form.password_confirmation && form.password === form.password_confirmation) progress++;
  if (form.role) progress++;

  return (progress / totalFields) * 100;
});

// Función para enviar el formulario
const submit = () => {
  form.post(route('usuarios.store'), {
    onSuccess: () => {
      notyf.success('Usuario creado exitosamente.');
      form.reset();
    },
    onError: (errors) => {
      console.log('Errores:', errors);
      notyf.error('Error al crear el usuario. Revisa los campos.');

      // Scroll al primer error
      const firstErrorField = Object.keys(errors)[0];
      if (firstErrorField) {
        const element = document.getElementById(firstErrorField);
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'center' });
          element.focus();
        }
      }
    },
    onFinish: () => {
      // Se ejecuta siempre al terminar la petición
      console.log('Petición terminada');
    }
  });
};
</script>

<style scoped>
.form-group {
  margin-bottom: 1.5rem;
}

/* Estilos para el input focus */
input:focus, select:focus {
  outline: none;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Animación para los botones */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:disabled {
  background-color: #d1d5db;
  cursor: not-allowed;
  transform: none;
}

/* Estilos para el select personalizado */
select {
  background-image: none;
}

/* Animaciones suaves */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Efecto hover para los inputs */
input:hover:not(:focus), select:hover:not(:focus) {
  border-color: #9ca3af;
}

/* Estilo para campos válidos */
.border-green-300 {
  border-color: #86efac;
}

.bg-green-50 {
  background-color: #f0fdf4;
}

/* Estilo para campos con error */
.border-red-300 {
  border-color: #fca5a5;
}

.bg-red-50 {
  background-color: #fef2f2;
}
</style>
