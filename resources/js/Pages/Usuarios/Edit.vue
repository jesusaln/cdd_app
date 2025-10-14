<template>
  <Head title="Editar Usuario" />
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          {{ isAdmin ? `Editar Usuario #${props.usuario.id}` : `Ver Usuario #${props.usuario.id}` }}
        </h1>
        <p class="text-gray-600">
          {{ isAdmin ? 'Actualiza la información del usuario' : 'Información del usuario (solo lectura)' }}
        </p>
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
              <p class="text-sm text-gray-600 mt-1">Actualiza los datos básicos del usuario</p>
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
                  :readonly="!isAdmin"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                  :class="{
                    'border-red-300 bg-red-50 focus:ring-red-500': form.errors.name,
                    'border-green-300 bg-green-50': form.name && !form.errors.name,
                    'bg-gray-100 cursor-not-allowed': !isAdmin
                  }"
                  :placeholder="isAdmin ? 'Ingresa el nombre completo' : 'Nombre del usuario'"
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
                  :readonly="!isAdmin"
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                  :class="{
                    'border-red-300 bg-red-50 focus:ring-red-500': form.errors.email,
                    'border-green-300 bg-green-50': form.email && !form.errors.email && isValidEmail,
                    'bg-gray-100 cursor-not-allowed': !isAdmin
                  }"
                  :placeholder="isAdmin ? 'correo@ejemplo.com' : 'Email del usuario'"
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
 
            <!-- Employee Information Section -->
            <div class="space-y-6">
              <div class="border-b border-gray-200 pb-4">
                <div class="flex items-center justify-between">
                  <div>
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                      <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                      </svg>
                      Información de Empleado
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Datos adicionales si el usuario es un empleado</p>
                  </div>
                  <div class="flex items-center">
                    <input
                      v-model="form.es_empleado"
                      type="checkbox"
                      id="es_empleado"
                      :disabled="!isAdmin"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      :class="{ 'cursor-not-allowed': !isAdmin }"
                    />
                    <label for="es_empleado" class="ml-2 text-sm text-gray-700">
                      Es empleado
                    </label>
                  </div>
                </div>
              </div>
 
              <!-- Información Personal del Empleado -->
              <div v-if="form.es_empleado" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="form-group">
                    <label for="telefono" class="block text-sm font-semibold text-gray-700 mb-2">
                      Teléfono
                    </label>
                    <input
                      v-model="form.telefono"
                      type="tel"
                      id="telefono"
                      :readonly="!isAdmin"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      :class="{
                        'bg-gray-100 cursor-not-allowed': !isAdmin
                      }"
                      :placeholder="isAdmin ? 'Número de teléfono' : 'Teléfono del usuario'"
                    />
                    <InputError class="mt-2" :message="form.errors.telefono" />
                  </div>
 
                  <div class="form-group">
                    <label for="fecha_nacimiento" class="block text-sm font-semibold text-gray-700 mb-2">
                      Fecha de Nacimiento
                    </label>
                    <input
                      v-model="form.fecha_nacimiento"
                      type="date"
                      id="fecha_nacimiento"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    />
                    <InputError class="mt-2" :message="form.errors.fecha_nacimiento" />
                  </div>
 
                  <div class="form-group">
                    <label for="curp" class="block text-sm font-semibold text-gray-700 mb-2">
                      CURP
                    </label>
                    <input
                      v-model="form.curp"
                      type="text"
                      id="curp"
                      maxlength="18"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="18 caracteres"
                    />
                    <InputError class="mt-2" :message="form.errors.curp" />
                  </div>
 
                  <div class="form-group">
                    <label for="rfc" class="block text-sm font-semibold text-gray-700 mb-2">
                      RFC
                    </label>
                    <input
                      v-model="form.rfc"
                      type="text"
                      id="rfc"
                      maxlength="13"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="13 caracteres"
                    />
                    <InputError class="mt-2" :message="form.errors.rfc" />
                  </div>
                </div>
 
                <div class="form-group">
                  <label for="direccion" class="block text-sm font-semibold text-gray-700 mb-2">
                    Dirección
                  </label>
                  <textarea
                    v-model="form.direccion"
                    id="direccion"
                    rows="3"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    placeholder="Dirección completa"
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.direccion" />
                </div>
 
                <div class="form-group">
                  <label for="nss" class="block text-sm font-semibold text-gray-700 mb-2">
                    Número de Seguro Social (NSS)
                  </label>
                  <input
                    v-model="form.nss"
                    type="text"
                    id="nss"
                    maxlength="11"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    placeholder="11 dígitos"
                  />
                  <InputError class="mt-2" :message="form.errors.nss" />
                </div>
              </div>
 
              <!-- Información Laboral -->
              <div v-if="form.es_empleado" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                  <h3 class="text-md font-semibold text-gray-900 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                    </svg>
                    Información Laboral
                  </h3>
                </div>
 
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="form-group">
                    <label for="puesto" class="block text-sm font-semibold text-gray-700 mb-2">
                      Puesto
                    </label>
                    <input
                      v-model="form.puesto"
                      type="text"
                      id="puesto"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Ej: Desarrollador Senior"
                    />
                    <InputError class="mt-2" :message="form.errors.puesto" />
                  </div>
 
                  <div class="form-group">
                    <label for="departamento" class="block text-sm font-semibold text-gray-700 mb-2">
                      Departamento
                    </label>
                    <input
                      v-model="form.departamento"
                      type="text"
                      id="departamento"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Ej: Tecnología"
                    />
                    <InputError class="mt-2" :message="form.errors.departamento" />
                  </div>
 
                  <div class="form-group">
                    <label for="fecha_contratacion" class="block text-sm font-semibold text-gray-700 mb-2">
                      Fecha de Contratación
                    </label>
                    <input
                      v-model="form.fecha_contratacion"
                      type="date"
                      id="fecha_contratacion"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    />
                    <InputError class="mt-2" :message="form.errors.fecha_contratacion" />
                  </div>
 
                  <div class="form-group">
                    <label for="numero_empleado" class="block text-sm font-semibold text-gray-700 mb-2">
                      Número de Empleado
                    </label>
                    <input
                      v-model="form.numero_empleado"
                      type="text"
                      id="numero_empleado"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Número único de empleado"
                    />
                    <InputError class="mt-2" :message="form.errors.numero_empleado" />
                  </div>
 
                  <div class="form-group">
                    <label for="tipo_contrato" class="block text-sm font-semibold text-gray-700 mb-2">
                      Tipo de Contrato
                    </label>
                    <select
                      v-model="form.tipo_contrato"
                      id="tipo_contrato"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    >
                      <option value="">Seleccionar tipo</option>
                      <option value="tiempo_completo">Tiempo completo</option>
                      <option value="medio_tiempo">Medio tiempo</option>
                      <option value="temporal">Temporal</option>
                      <option value="por_obra">Por obra</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.tipo_contrato" />
                  </div>
 
                  <div class="form-group">
                    <label for="salario" class="block text-sm font-semibold text-gray-700 mb-2">
                      Salario Mensual
                    </label>
                    <input
                      v-model="form.salario"
                      type="number"
                      step="0.01"
                      id="salario"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="0.00"
                    />
                    <InputError class="mt-2" :message="form.errors.salario" />
                  </div>
                </div>
              </div>
 
              <!-- Contacto de Emergencia -->
              <div v-if="form.es_empleado" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                  <h3 class="text-md font-semibold text-gray-900 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Contacto de Emergencia
                  </h3>
                </div>
 
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div class="form-group">
                    <label for="contacto_emergencia_nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                      Nombre
                    </label>
                    <input
                      v-model="form.contacto_emergencia_nombre"
                      type="text"
                      id="contacto_emergencia_nombre"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Nombre completo"
                    />
                    <InputError class="mt-2" :message="form.errors.contacto_emergencia_nombre" />
                  </div>
 
                  <div class="form-group">
                    <label for="contacto_emergencia_telefono" class="block text-sm font-semibold text-gray-700 mb-2">
                      Teléfono
                    </label>
                    <input
                      v-model="form.contacto_emergencia_telefono"
                      type="tel"
                      id="contacto_emergencia_telefono"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Número de teléfono"
                    />
                    <InputError class="mt-2" :message="form.errors.contacto_emergencia_telefono" />
                  </div>
 
                  <div class="form-group">
                    <label for="contacto_emergencia_parentesco" class="block text-sm font-semibold text-gray-700 mb-2">
                      Parentesco
                    </label>
                    <input
                      v-model="form.contacto_emergencia_parentesco"
                      type="text"
                      id="contacto_emergencia_parentesco"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Ej: Padre, Madre, Hermano"
                    />
                    <InputError class="mt-2" :message="form.errors.contacto_emergencia_parentesco" />
                  </div>
                </div>
              </div>
 
              <!-- Información Bancaria -->
              <div v-if="form.es_empleado" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                  <h3 class="text-md font-semibold text-gray-900 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Información Bancaria
                  </h3>
                </div>
 
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div class="form-group">
                    <label for="banco" class="block text-sm font-semibold text-gray-700 mb-2">
                      Banco
                    </label>
                    <input
                      v-model="form.banco"
                      type="text"
                      id="banco"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Nombre del banco"
                    />
                    <InputError class="mt-2" :message="form.errors.banco" />
                  </div>
 
                  <div class="form-group">
                    <label for="numero_cuenta" class="block text-sm font-semibold text-gray-700 mb-2">
                      Número de Cuenta
                    </label>
                    <input
                      v-model="form.numero_cuenta"
                      type="text"
                      id="numero_cuenta"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="Número de cuenta"
                    />
                    <InputError class="mt-2" :message="form.errors.numero_cuenta" />
                  </div>
 
                  <div class="form-group">
                    <label for="clabe_interbancaria" class="block text-sm font-semibold text-gray-700 mb-2">
                      CLABE Interbancaria
                    </label>
                    <input
                      v-model="form.clabe_interbancaria"
                      type="text"
                      id="clabe_interbancaria"
                      maxlength="18"
                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                      placeholder="18 dígitos"
                    />
                    <InputError class="mt-2" :message="form.errors.clabe_interbancaria" />
                  </div>
                </div>
              </div>
 
              <!-- Observaciones -->
              <div v-if="form.es_empleado" class="space-y-6">
                <div class="form-group">
                  <label for="observaciones" class="block text-sm font-semibold text-gray-700 mb-2">
                    Observaciones
                  </label>
                  <textarea
                    v-model="form.observaciones"
                    id="observaciones"
                    rows="3"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white"
                    placeholder="Observaciones adicionales del empleado"
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.observaciones" />
                </div>
              </div>
            </div>
 
            <!-- Role Section (Solo para admins) -->
          <div v-if="isAdmin" class="space-y-6">
            <div class="border-b border-gray-200 pb-4">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Permisos y Acceso
              </h2>
              <p class="text-sm text-gray-600 mt-1">Asigna un nuevo rol al usuario</p>
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
                  <option v-for="rol in props.roles" :key="rol.id" :value="rol.name">
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
                Seguridad (Opcional)
              </h2>
              <p class="text-sm text-gray-600 mt-1">Deja en blanco si no deseas cambiar la contraseña</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Contraseña -->
              <div class="form-group">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                  Nueva Contraseña
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
                  <div class="flex items-center space-x-2" v-if="form.password">
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
                  Confirmar Nueva Contraseña
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
                v-if="isAdmin"
                type="submit"
                class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:hover:shadow-none"
                :disabled="form.processing || !isFormValid"
              >
                <div v-if="form.processing" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Guardando...</span>
                </div>
                <div v-else class="flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <span>Guardar Cambios</span>
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
  usuario: Object,
  roles: Array,
  auth: Object,
});

// Reactive variables
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Form con valores iniciales
const form = useForm({
  name: props.usuario.name,
  email: props.usuario.email,
  telefono: props.usuario.telefono || '',
  fecha_nacimiento: props.usuario.fecha_nacimiento || '',
  curp: props.usuario.curp || '',
  rfc: props.usuario.rfc || '',
  direccion: props.usuario.direccion || '',
  nss: props.usuario.nss || '',
  puesto: props.usuario.puesto || '',
  departamento: props.usuario.departamento || '',
  fecha_contratacion: props.usuario.fecha_contratacion || '',
  salario: props.usuario.salario || '',
  tipo_contrato: props.usuario.tipo_contrato || '',
  numero_empleado: props.usuario.numero_empleado || '',
  contacto_emergencia_nombre: props.usuario.contacto_emergencia_nombre || '',
  contacto_emergencia_telefono: props.usuario.contacto_emergencia_telefono || '',
  contacto_emergencia_parentesco: props.usuario.contacto_emergencia_parentesco || '',
  banco: props.usuario.banco || '',
  numero_cuenta: props.usuario.numero_cuenta || '',
  clabe_interbancaria: props.usuario.clabe_interbancaria || '',
  observaciones: props.usuario.observaciones || '',
  es_empleado: props.usuario.es_empleado || false,
  role: props.usuario.roles[0]?.name || '',
  password: '',
  password_confirmation: '',
});

// Notificación
const notyf = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' },
  ripple: true,
  dismissible: true
});

// Verificar si es admin
const isAdmin = computed(() => {
  return props.auth.user?.roles.some(role => role.name === 'admin');
});

// Determinar si un campo debe ser de solo lectura
const isFieldReadonly = (fieldName) => {
  // Si no es admin, todos los campos son de solo lectura
  if (!isAdmin.value) {
    return true;
  }

  // Si es admin, puede editar todo
  return false;
};

// Determinar la clase CSS para campos de solo lectura
const getFieldClass = (fieldName) => {
  return {
    'bg-gray-100 cursor-not-allowed': isFieldReadonly(fieldName),
    'bg-gray-50 hover:bg-white': !isFieldReadonly(fieldName)
  };
};

// Validar email
const isValidEmail = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(form.email);
});

// Fortaleza de contraseña
const passwordStrength = computed(() => {
  const password = form.password;
  if (!password) return 0;

  let strength = 0;
  if (password.length >= 8) strength++;
  if (/\d/.test(password)) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
  return strength;
});

const passwordStrengthText = computed(() => {
  switch (passwordStrength.value) {
    case 0: case 1: return 'Débil';
    case 2: return 'Regular';
    case 3: return 'Buena';
    case 4: return 'Excelente';
    default: return '';
  }
});

// Formulario válido (solo si hay cambios o contraseña)
const isFormValid = computed(() => {
  const hasChanges = form.name !== props.usuario.name ||
                     form.email !== props.usuario.email ||
                     form.telefono !== (props.usuario.telefono || '') ||
                     form.fecha_nacimiento !== (props.usuario.fecha_nacimiento || '') ||
                     form.curp !== (props.usuario.curp || '') ||
                     form.rfc !== (props.usuario.rfc || '') ||
                     form.direccion !== (props.usuario.direccion || '') ||
                     form.nss !== (props.usuario.nss || '') ||
                     form.puesto !== (props.usuario.puesto || '') ||
                     form.departamento !== (props.usuario.departamento || '') ||
                     form.fecha_contratacion !== (props.usuario.fecha_contratacion || '') ||
                     form.salario !== (props.usuario.salario || '') ||
                     form.tipo_contrato !== (props.usuario.tipo_contrato || '') ||
                     form.numero_empleado !== (props.usuario.numero_empleado || '') ||
                     form.contacto_emergencia_nombre !== (props.usuario.contacto_emergencia_nombre || '') ||
                     form.contacto_emergencia_telefono !== (props.usuario.contacto_emergencia_telefono || '') ||
                     form.contacto_emergencia_parentesco !== (props.usuario.contacto_emergencia_parentesco || '') ||
                     form.banco !== (props.usuario.banco || '') ||
                     form.numero_cuenta !== (props.usuario.numero_cuenta || '') ||
                     form.clabe_interbancaria !== (props.usuario.clabe_interbancaria || '') ||
                     form.observaciones !== (props.usuario.observaciones || '') ||
                     form.es_empleado !== (props.usuario.es_empleado || false) ||
                     (isAdmin.value && form.role !== (props.usuario.roles[0]?.name || '')) ||
                     form.password;

  const validPassword = !form.password ||
                        (form.password.length >= 8 &&
                         form.password === form.password_confirmation);

  return hasChanges && isValidEmail.value && validPassword;
});

// Progreso visual (similar al create)
const formProgress = computed(() => {
  let progress = 0;
  let totalFields = 4; // nombre, email, rol (si admin), contraseña (opcional)

  // Campos básicos
  if (form.name) progress++;
  if (form.email && isValidEmail.value) progress++;
  if (isAdmin.value && form.role) progress++;
  if (form.password && form.password.length >= 8) progress++;

  // Si es empleado, agregar campos adicionales
  if (form.es_empleado) {
    totalFields += 7; // Campos adicionales de empleado (sin apellidos)

    if (form.telefono) progress++;
    if (form.fecha_nacimiento) progress++;
    if (form.curp) progress++;
    if (form.puesto) progress++;
    if (form.departamento) progress++;
    if (form.fecha_contratacion) progress++;
    if (form.numero_empleado) progress++;
  }

  return totalFields > 0 ? (progress / totalFields) * 100 : 0;
});

// Enviar formulario
const submit = () => {
  form.put(route('usuarios.update', props.usuario.id), {
    onSuccess: () => {
      const tipo = form.es_empleado ? 'empleado' : 'usuario';
      notyf.success(`${tipo.charAt(0).toUpperCase() + tipo.slice(1)} actualizado exitosamente.`);
      form.reset('password', 'password_confirmation');
    },
    onError: (errors) => {
      notyf.error('Error al actualizar el usuario.');
      const firstError = Object.keys(errors)[0];
      const el = document.getElementById(firstError);
      if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },
    onFinish: () => console.log('Actualización finalizada'),
  });
};
</script>

<style scoped>
.form-group {
  margin-bottom: 1.5rem;
}

input:focus, select:focus {
  outline: none;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:disabled {
  background-color: #d1d5db;
  cursor: not-allowed;
  transform: none;
}

select {
  background-image: none;
}

.transition-all {
  transition: all 0.2s ease-in-out;
}

input:hover:not(:focus), select:hover:not(:focus) {
  border-color: #9ca3af;
}

.border-green-300 { border-color: #86efac; }
.bg-green-50 { background-color: #f0fdf4; }
.border-red-300 { border-color: #fca5a5; }
.bg-red-50 { background-color: #fef2f2; }
</style>
