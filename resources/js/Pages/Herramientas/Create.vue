<template>
  <Head title="Crear Herramienta" />

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
      <ol class="flex items-center space-x-2">
        <li>
          <Link href="/herramientas" class="text-gray-500 hover:text-gray-700 transition-colors">
            Herramientas
          </Link>
        </li>
        <li>
          <svg class="w-5 h-5 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
        </li>
        <li class="text-gray-900 font-medium">Crear Herramienta</li>
      </ol>
    </nav>

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Crear Nueva Herramienta</h1>
          <p class="text-gray-600 mt-1">Registra una nueva herramienta en el sistema</p>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            href="/herramientas"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
          </Link>
        </div>
      </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
      <form @submit.prevent="submit">
        <!-- Progress Bar -->
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Progreso del formulario</span>
            <span class="text-gray-900 font-medium">{{ completionPercentage }}%</span>
          </div>
          <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out"
              :style="{ width: completionPercentage + '%' }"
            ></div>
          </div>
        </div>

        <div class="px-6 py-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Columna izquierda -->
            <div class="space-y-6">
              <!-- Información básica -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Información Básica
                </h3>

                <!-- Nombre -->
                <div class="mb-6">
                  <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre de la Herramienta
                    <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.nombre"
                      type="text"
                      id="nombre"
                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                      :class="{ 'border-red-500 focus:ring-red-500': form.errors.nombre }"
                      @blur="convertirAMayusculas('nombre')"
                      @input="validateField('nombre')"
                      placeholder="Ej: TALADRO PERCUTOR"
                      required
                    />
                    <div v-if="form.errors.nombre" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>
                  <p v-if="form.errors.nombre" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.nombre }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">El nombre se convertirá automáticamente a mayúsculas</p>
                </div>

                <!-- Número de Serie -->
                <div class="mb-6">
                  <label for="numero_serie" class="block text-sm font-medium text-gray-700 mb-2">
                    Número de Serie
                    <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.numero_serie"
                      type="text"
                      id="numero_serie"
                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono"
                      :class="{ 'border-red-500 focus:ring-red-500': form.errors.numero_serie }"
                      @blur="convertirAMayusculas('numero_serie')"
                      @input="validateField('numero_serie')"
                      placeholder="Ej: TD-2024-001"
                      required
                    />
                    <div v-if="form.errors.numero_serie" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>
                  <p v-if="form.errors.numero_serie" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.numero_serie }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">Identificador único de la herramienta</p>
                </div>

                <!-- Técnico Asignado -->
                <div class="mb-6">
                  <label for="tecnico_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Técnico Asignado
                    <span class="text-gray-400">(Opcional)</span>
                  </label>
                  <div class="relative">
                    <select
                      v-model="form.tecnico_id"
                      id="tecnico_id"
                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white"
                      :class="{ 'border-red-500 focus:ring-red-500': form.errors.tecnico_id }"
                      @change="validateField('tecnico_id')"
                    >
                      <option value="">Sin asignar</option>
                      <option v-for="tecnico in tecnicos" :key="tecnico.id" :value="tecnico.id">
                        {{ tecnico.nombre }} {{ tecnico.apellido }}
                      </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </div>
                  </div>
                  <p v-if="form.errors.tecnico_id" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.tecnico_id }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">
                    Puedes asignar la herramienta a un técnico más tarde
                  </p>
                </div>
              </div>
            </div>

            <!-- Columna derecha -->
            <div class="space-y-6">
              <!-- Foto -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Imagen de la Herramienta
                </h3>

                <!-- Zona de drop -->
                <div class="mb-4">
                  <div
                    @drop="handleDrop"
                    @dragover="handleDragOver"
                    @dragleave="handleDragLeave"
                    :class="[
                      'relative border-2 border-dashed rounded-lg p-6 text-center hover:border-blue-500 transition-colors duration-200',
                      isDragOver ? 'border-blue-500 bg-blue-50' : 'border-gray-300',
                      form.errors.foto ? 'border-red-500' : ''
                    ]"
                    class="cursor-pointer"
                    @click="$refs.fileInput.click()"
                  >
                    <input
                      ref="fileInput"
                      type="file"
                      @change="handleFileChange"
                      accept="image/*"
                      class="hidden"
                    />

                    <div v-if="!previewImage" class="py-4">
                      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      <p class="mt-2 text-sm text-gray-600">
                        <span class="font-medium">Haz clic para subir</span> o arrastra y suelta
                      </p>
                      <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF hasta 10MB</p>
                    </div>

                    <!-- Preview de la imagen -->
                    <div v-else class="relative">
                      <img :src="previewImage" alt="Vista previa" class="max-h-48 mx-auto rounded-lg shadow-md" />
                      <button
                        @click.stop="removeImage"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                        title="Eliminar imagen"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <p v-if="form.errors.foto" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.foto }}
                  </p>
                </div>

                <!-- Información adicional -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                      <h4 class="text-sm font-medium text-blue-900">Consejos para la foto</h4>
                      <ul class="text-sm text-blue-700 mt-1 space-y-1">
                        <li>• Usa buena iluminación</li>
                        <li>• Incluye el número de serie si es visible</li>
                        <li>• Evita fondos que distraigan</li>
                        <li>• Mantén la herramienta como foco principal</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer del formulario -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
          <div class="flex items-center justify-between">
            <div class="flex items-center text-sm text-gray-600">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Los campos marcados con * son obligatorios
            </div>

            <div class="flex items-center space-x-3">
              <Link
                href="/herramientas"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
              >
                Cancelar
              </Link>

              <button
                type="submit"
                :disabled="form.processing || !isFormValid"
                class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ form.processing ? 'Guardando...' : 'Guardar Herramienta' }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Toast de éxito -->
    <div v-if="showSuccessToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300">
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Herramienta creada exitosamente
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Props para recibir la lista de técnicos
const props = defineProps({
  tecnicos: Array,
});

// Estados reactivos
const previewImage = ref(null);
const isDragOver = ref(false);
const showSuccessToast = ref(false);
const fileInput = ref(null);

// Formulario para crear una herramienta
const form = useForm({
  nombre: '',
  numero_serie: '',
  foto: null,
  tecnico_id: '',
});

// Computed para validación y progreso
const isFormValid = computed(() => {
  return form.nombre.trim() !== '' && form.numero_serie.trim() !== '' && !form.hasErrors;
});

const completionPercentage = computed(() => {
  let completed = 0;
  const total = 4; // Total de campos

  if (form.nombre.trim()) completed++;
  if (form.numero_serie.trim()) completed++;
  if (form.foto) completed++;
  if (form.tecnico_id) completed++;

  return Math.round((completed / total) * 100);
});

// Función para enviar el formulario
const submit = () => {
  form.post(route('herramientas.store'), {
    onSuccess: () => {
      showSuccessToast.value = true;
      setTimeout(() => {
        showSuccessToast.value = false;
      }, 3000);
      form.reset();
      previewImage.value = null;
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors);
    },
  });
};

// Método para convertir a mayúsculas
const convertirAMayusculas = (campo) => {
  if (form[campo]) {
    form[campo] = form[campo].toUpperCase();
  }
};

// Validación en tiempo real
const validateField = (fieldName) => {
  // Limpiar errores previos del campo específico
  if (form.errors[fieldName]) {
    form.clearErrors(fieldName);
  }
};

// Manejo de archivos
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    processFile(file);
  }
};

const processFile = (file) => {
  // Validar tipo de archivo
  if (!file.type.startsWith('image/')) {
    alert('Por favor selecciona un archivo de imagen válido.');
    return;
  }

  // Validar tamaño (10MB máximo)
  if (file.size > 10 * 1024 * 1024) {
    alert('El archivo es demasiado grande. El tamaño máximo es 10MB.');
    return;
  }

  form.foto = file;

  // Crear preview
  const reader = new FileReader();
  reader.onload = (e) => {
    previewImage.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Funciones de drag and drop
const handleDrop = (event) => {
  event.preventDefault();
  isDragOver.value = false;

  const files = event.dataTransfer.files;
  if (files.length > 0) {
    processFile(files[0]);
  }
};

const handleDragOver = (event) => {
  event.preventDefault();
  isDragOver.value = true;
};

const handleDragLeave = (event) => {
  event.preventDefault();
  isDragOver.value = false;
};

// Función para eliminar imagen
const removeImage = () => {
  form.foto = null;
  previewImage.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Limpiar errores cuando se modifica el formulario
watch(() => form.nombre, () => validateField('nombre'));
watch(() => form.numero_serie, () => validateField('numero_serie'));
watch(() => form.foto, () => validateField('foto'));
watch(() => form.tecnico_id, () => validateField('tecnico_id'));
</script>

<style scoped>
/* Animaciones personalizadas */
@keyframes slideInUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.slide-in-up {
  animation: slideInUp 0.3s ease-out;
}

/* Estilos para el loading spinner */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Transiciones suaves para inputs */
input:focus,
select:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Hover effect para botones */
button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Estilos para la zona de drop */
.drag-over {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

/* Mejoras en el preview de imagen */
.image-preview {
  transition: all 0.3s ease;
}

.image-preview:hover {
  transform: scale(1.05);
}

/* Estilos para el toast */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
