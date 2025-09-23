<template>
  <Head title="Editar Herramienta" />

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
        <li>
          <Link :href="route('herramientas.show', herramienta.id)" class="text-gray-500 hover:text-gray-700 transition-colors">
            {{ herramienta.nombre }}
          </Link>
        </li>
        <li>
          <svg class="w-5 h-5 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
        </li>
        <li class="text-gray-900 font-medium">Editar</li>
      </ol>
    </nav>

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Editar Herramienta</h1>
          <p class="text-gray-600 mt-1">Actualiza la información de la herramienta</p>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            :href="route('herramientas.show', herramienta.id)"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Ver Detalles
          </Link>
          <Link
            href="/herramientas"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Listado
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
            <span class="text-gray-600">Progreso de actualización</span>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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

                <!-- Categoría -->
                <div class="mb-6">
                  <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">
                    Categoría
                    <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <select
                      v-model="form.categoria"
                      id="categoria"
                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white"
                      :class="{ 'border-red-500 focus:ring-red-500': form.errors.categoria }"
                      @change="validateField('categoria')"
                      required
                    >
                      <option value="">Seleccionar categoría...</option>
                      <option value="electrica">Eléctrica</option>
                      <option value="manual">Manual</option>
                      <option value="medicion">Medición</option>
                      <option value="seguridad">Seguridad</option>
                      <option value="limpieza">Limpieza</option>
                      <option value="jardineria">Jardinería</option>
                      <option value="construccion">Construcción</option>
                      <option value="electronica">Electrónica</option>
                      <option value="otra">Otra</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </div>
                  </div>
                  <p v-if="form.errors.categoria" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.categoria }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">Clasifica la herramienta por su tipo de uso</p>
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
                    {{ form.tecnico_id ? 'Cambia la asignación del técnico' : 'Puedes asignar la herramienta a un técnico' }}
                  </p>
                </div>
              </div>

              <!-- Información de Mantenimiento -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Información de Mantenimiento
                </h3>

                <!-- Vida Útil -->
                <div class="mb-6">
                  <label for="vida_util_meses" class="block text-sm font-medium text-gray-700 mb-2">
                    Vida Útil (meses)
                    <span class="text-gray-400">(Opcional)</span>
                  </label>
                  <input
                    v-model="form.vida_util_meses"
                    type="number"
                    id="vida_util_meses"
                    min="1"
                    max="120"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    :class="{ 'border-red-500 focus:ring-red-500': form.errors.vida_util_meses }"
                    @input="validateField('vida_util_meses')"
                    placeholder="Ej: 24"
                  />
                  <p v-if="form.errors.vida_util_meses" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.vida_util_meses }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">Duración estimada de la herramienta en meses</p>
                </div>

                <!-- Costo de Reemplazo -->
                <div class="mb-6">
                  <label for="costo_reemplazo" class="block text-sm font-medium text-gray-700 mb-2">
                    Costo de Reemplazo
                    <span class="text-gray-400">(Opcional)</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      v-model="form.costo_reemplazo"
                      type="number"
                      id="costo_reemplazo"
                      step="0.01"
                      min="0"
                      class="block w-full pl-8 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                      :class="{ 'border-red-500 focus:ring-red-500': form.errors.costo_reemplazo }"
                      @input="validateField('costo_reemplazo')"
                      placeholder="0.00"
                    />
                  </div>
                  <p v-if="form.errors.costo_reemplazo" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.costo_reemplazo }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">Costo estimado para reemplazar la herramienta</p>
                </div>

                <!-- Requiere Mantenimiento -->
                <div class="mb-6">
                  <label class="flex items-center">
                    <input
                      v-model="form.requiere_mantenimiento"
                      type="checkbox"
                      class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500"
                      @change="validateField('requiere_mantenimiento')"
                    />
                    <span class="ml-2 text-sm text-gray-700">Requiere mantenimiento programado</span>
                  </label>
                  <p class="mt-1 text-sm text-gray-500">Indica si la herramienta necesita mantenimiento regular</p>
                </div>

                <!-- Días para Mantenimiento -->
                <div v-if="form.requiere_mantenimiento" class="mb-6">
                  <label for="dias_para_mantenimiento" class="block text-sm font-medium text-gray-700 mb-2">
                    Días entre Mantenimientos
                    <span class="text-gray-400">(Opcional)</span>
                  </label>
                  <input
                    v-model="form.dias_para_mantenimiento"
                    type="number"
                    id="dias_para_mantenimiento"
                    min="1"
                    max="365"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    :class="{ 'border-red-500 focus:ring-red-500': form.errors.dias_para_mantenimiento }"
                    @input="validateField('dias_para_mantenimiento')"
                    placeholder="Ej: 30"
                  />
                  <p v-if="form.errors.dias_para_mantenimiento" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.dias_para_mantenimiento }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">Intervalo en días para realizar mantenimiento</p>
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

                <!-- Foto actual -->
                <div v-if="currentImage && !form.remove_foto" class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Foto Actual</label>
                  <div class="relative inline-block">
                    <img :src="currentImage" alt="Foto actual de la herramienta" class="w-full max-w-xs h-48 object-cover rounded-lg shadow-md border border-gray-200" />
                    <button
                      type="button"
                      @click="removeCurrentPhoto"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                      title="Eliminar foto actual"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                  <p class="text-sm text-gray-500 mt-2">Haz clic en el ícono de eliminar para quitar la foto actual</p>
                </div>

                <!-- Zona de drop para nueva foto -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ currentImage && !form.remove_foto ? 'Cambiar Foto' : 'Nueva Foto' }}
                    <span class="text-gray-400">(Opcional)</span>
                  </label>
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

                    <!-- Preview de la nueva imagen -->
                    <div v-else class="relative">
                      <img :src="previewImage" alt="Vista previa" class="max-h-48 mx-auto rounded-lg shadow-md" />
                      <button
                        @click.stop="removeNewImage"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                        title="Eliminar nueva imagen"
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

              <!-- Información Adicional -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Información Adicional
                </h3>

                <!-- Descripción -->
                <div class="mb-6">
                  <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción
                    <span class="text-gray-400">(Opcional)</span>
                  </label>
                  <textarea
                    v-model="form.descripcion"
                    id="descripcion"
                    rows="4"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    :class="{ 'border-red-500 focus:ring-red-500': form.errors.descripcion }"
                    @input="validateField('descripcion')"
                    placeholder="Describe las características, uso, cuidados especiales de la herramienta..."
                  ></textarea>
                  <p v-if="form.errors.descripcion" class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ form.errors.descripcion }}
                  </p>
                  <p class="mt-1 text-sm text-gray-500">Información detallada sobre la herramienta</p>
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
                :href="route('herramientas.index')"
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
                {{ form.processing ? 'Actualizando...' : 'Actualizar Herramienta' }}
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
        Herramienta actualizada exitosamente
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    herramienta: {
        type: Object,
        required: true
    },
    tecnicos: {
        type: Array,
        required: true
    },
});

// Estados reactivos
const previewImage = ref(null);
const currentImage = ref(null);
const isDragOver = ref(false);
const showSuccessToast = ref(false);
const fileInput = ref(null); // Ref para el input de archivo

// Formulario con Inertia.js, inicializado con las props
const form = useForm({
    _method: 'PUT', // Especificar el método para la actualización
    nombre: props.herramienta?.nombre || '',
    numero_serie: props.herramienta?.numero_serie || '',
    categoria: props.herramienta?.categoria || '',
    tecnico_id: props.herramienta?.tecnico_id || '',
    vida_util_meses: props.herramienta?.vida_util_meses || '',
    costo_reemplazo: props.herramienta?.costo_reemplazo || '',
    requiere_mantenimiento: props.herramienta?.requiere_mantenimiento || false,
    dias_para_mantenimiento: props.herramienta?.dias_para_mantenimiento || '',
    descripcion: props.herramienta?.descripcion || '',
    foto: null,
    remove_foto: false, // Flag para indicar si se debe eliminar la foto actual
});

// Cargar la URL de la imagen actual al montar el componente
onMounted(() => {
    if (props.herramienta?.foto_url) {
        currentImage.value = props.herramienta.foto_url;
    }
});

// Propiedad computada para habilitar/deshabilitar el botón de envío
const isFormValid = computed(() => {
    return form.nombre.trim() !== '' && form.numero_serie.trim() !== '' && !form.hasErrors;
});

// Propiedad computada para la barra de progreso
const completionPercentage = computed(() => {
    let completed = 0;
    const total = 8; // nombre, numero_serie, categoria, foto, tecnico_id, vida_util, costo_reemplazo, descripcion

    if (form.nombre.trim()) completed++;
    if (form.numero_serie.trim()) completed++;
    if (form.categoria) completed++;
    // Cuenta si hay una imagen nueva O si existe una actual y no se va a eliminar
    if (form.foto || (currentImage.value && !form.remove_foto)) completed++;
    if (form.tecnico_id) completed++;
    if (form.vida_util_meses) completed++;
    if (form.costo_reemplazo) completed++;
    if (form.descripcion.trim()) completed++;

    return Math.round((completed / total) * 100);
});

// Función para enviar el formulario de actualización
const submit = () => {
    // Usamos form.post porque Laravel no maneja bien FormData con PUT.
    // El campo _method: 'PUT' se encargará de enrutar correctamente.
    form.post(route('herramientas.update', props.herramienta.id), {
        onSuccess: () => {
            showSuccessToast.value = true;
            setTimeout(() => {
                showSuccessToast.value = false;
            }, 3000);

            // Resetea el estado del formulario después de un éxito
            form.reset('foto', 'remove_foto');
            previewImage.value = null;
            if (fileInput.value) fileInput.value.value = '';

            // Mantener los valores originales de los otros campos
            form.nombre = props.herramienta?.nombre || '';
            form.numero_serie = props.herramienta?.numero_serie || '';
            form.categoria = props.herramienta?.categoria || '';
            form.tecnico_id = props.herramienta?.tecnico_id || '';
            form.vida_util_meses = props.herramienta?.vida_util_meses || '';
            form.costo_reemplazo = props.herramienta?.costo_reemplazo || '';
            form.requiere_mantenimiento = props.herramienta?.requiere_mantenimiento || false;
            form.dias_para_mantenimiento = props.herramienta?.dias_para_mantenimiento || '';
            form.descripcion = props.herramienta?.descripcion || '';

            // La prop 'herramienta' se actualizará automáticamente por Inertia,
            // y el watcher de 'props.herramienta.foto_url' actualizará currentImage.
        },
        onError: (errors) => {
            console.error('Errores de validación:', errors);
        },
    });
};

// Convierte el texto de un campo a mayúsculas al perder el foco
const convertirAMayusculas = (campo) => {
    if (form[campo]) {
        form[campo] = form[campo].toUpperCase();
    }
};

// Limpia el error de un campo cuando el usuario empieza a corregirlo
const validateField = (fieldName) => {
    if (form.errors[fieldName]) {
        form.clearErrors(fieldName);
    }
};

// Maneja la selección de un archivo desde el input
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        processFile(file);
    }
};

// Maneja el archivo soltado en la zona de drop
const handleDrop = (event) => {
    event.preventDefault();
    isDragOver.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        // Asigna el archivo al input para consistencia
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        if (fileInput.value) {
          fileInput.value.files = dataTransfer.files;
        }
        processFile(file);
    }
};

// Procesa el archivo (validación y generación de vista previa)
const processFile = (file) => {
    if (!file.type.startsWith('image/')) {
        form.setError('foto', 'El archivo debe ser una imagen (PNG, JPG, etc.).');
        return;
    }
    if (file.size > 10 * 1024 * 1024) { // Límite de 10MB
        form.setError('foto', 'La imagen no debe superar los 10MB.');
        return;
    }

    form.foto = file;
    form.remove_foto = false; // Anula la eliminación si se sube una nueva foto
    validateField('foto');

    const reader = new FileReader();
    reader.onload = (e) => {
        previewImage.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Activa el estado visual de drag-over
const handleDragOver = (event) => {
    event.preventDefault();
    isDragOver.value = true;
};

// Desactiva el estado visual de drag-over
const handleDragLeave = () => {
    isDragOver.value = false;
};

// Remueve la nueva imagen seleccionada (la que está en vista previa)
const removeNewImage = () => {
    previewImage.value = null;
    form.foto = null;
    if (fileInput.value) {
        fileInput.value.value = ''; // Resetea el input de archivo
    }
};

// Marca la foto actual para ser eliminada en el backend
const removeCurrentPhoto = () => {
    form.remove_foto = true;
    form.foto = null; // Limpia cualquier archivo nuevo seleccionado
    previewImage.value = null; // Limpia la vista previa
};

// Watcher para actualizar la imagen actual si las props cambian desde el servidor
watch(() => props.herramienta.foto_url, (newUrl) => {
    currentImage.value = newUrl;
});

// Watchers para validación en tiempo real
watch(() => form.nombre, () => validateField('nombre'));
watch(() => form.numero_serie, () => validateField('numero_serie'));
watch(() => form.categoria, () => validateField('categoria'));
watch(() => form.tecnico_id, () => validateField('tecnico_id'));
watch(() => form.vida_util_meses, () => validateField('vida_util_meses'));
watch(() => form.costo_reemplazo, () => validateField('costo_reemplazo'));
watch(() => form.requiere_mantenimiento, () => validateField('requiere_mantenimiento'));
watch(() => form.dias_para_mantenimiento, () => validateField('dias_para_mantenimiento'));
watch(() => form.descripcion, () => validateField('descripcion'));
</script>
