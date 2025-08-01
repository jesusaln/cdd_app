<template>
  <Head title="Crear cotizaciones" />
  <div class="cotizaciones-create min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Nueva Cotización</h1>
            <p class="text-gray-600">Crea una nueva cotización para tus clientes</p>
            <!-- Estado del autoguardado -->
            <div class="flex items-center mt-2 text-sm">
              <div v-if="autoguardando" class="flex items-center text-amber-600">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Guardando borrador...
              </div>
              <div v-else-if="ultimoAutoguardado" class="flex items-center text-green-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardado automático: {{ formatearFecha(ultimoAutoguardado) }}
              </div>
            </div>
          </div>
          <div class="flex gap-3">
            <!-- Botón Vista Previa -->
            <button
              @click="mostrarVistaPrevia = true"
              :disabled="!clienteSeleccionado || selectedProducts.length === 0"
              class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              Vista Previa
            </button>

            <!-- Botón Plantillas -->
            <button
              @click="mostrarPlantillas = true"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 transition-all duration-200 shadow-sm"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Plantillas
            </button>

            <Link
              :href="route('cotizaciones.index')"
              class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Volver
            </Link>
          </div>
        </div>
      </div>

      <!-- Atajos de teclado info -->
      <div v-if="mostrarAtajos" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-sm font-medium text-blue-900 mb-2">Atajos de Teclado</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs text-blue-700">
              <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+S</kbd> Guardar</div>
              <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+P</kbd> Vista Previa</div>
              <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+F</kbd> Buscar Cliente</div>
              <div><kbd class="px-2 py-1 bg-white rounded border">Ctrl+B</kbd> Buscar Producto</div>
            </div>
          </div>
          <button @click="mostrarAtajos = false" class="text-blue-400 hover:text-blue-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <form @submit.prevent="crearCotizacion" class="space-y-8">
        <!-- Información del Cliente -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Información del Cliente
            </h2>
          </div>
          <div class="relative p-6">
            <!-- Componente de búsqueda de clientes -->
            <BuscarCliente
              ref="buscarClienteRef"
              :clientes="clientes"

              @cliente-seleccionado="onClienteSeleccionado"
              @crear-nuevo-cliente="crearNuevoCliente"
            />

            <!-- Información del cliente seleccionado -->
            <div v-if="clienteSeleccionado" class="mt-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-sm">
              <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Cliente Seleccionado
                </h3>
                <button
                  type="button"
                  @click="limpiarCliente"
                  class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded-full transition-colors duration-200"
                  title="Cambiar cliente"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Información del cliente -->
                <div class="space-y-2">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Nombre
                  </div>
                  <div class="text-lg font-semibold text-gray-900">{{ clienteSeleccionado.nombre_razon_social }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.email">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.email }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.telefono">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Teléfono
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.telefono }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.calle">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Dirección
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.calle }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.numero_exterior">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Numero Exterior
                  </div>
                  <div class="text-gray-900">{{ clienteSeleccionado.numero_exterior }}</div>
                </div>

                <div class="space-y-2" v-if="clienteSeleccionado.rfc">
                  <div class="flex items-center text-sm font-medium text-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    RFC
                  </div>
                  <div class="text-gray-900 font-mono">{{ clienteSeleccionado.rfc }}</div>
                </div>
              </div>
            </div>

            <!-- Mensaje cuando no hay cliente seleccionado -->
            <div v-else class="mt-6 p-6 border-2 border-dashed border-gray-300 rounded-lg text-center">
              <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <p class="text-gray-500 text-lg font-medium">Selecciona un cliente</p>
              <p class="text-gray-400 text-sm mt-1">Busca y selecciona un cliente para continuar con la cotización</p>
            </div>
          </div>
        </div>

        <!-- Productos y Servicios -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <div class="flex justify-between items-center">
              <h2 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Productos y Servicios
              </h2>
              <!-- Verificador de precios -->
              <button
                @click="verificarPrecios"
                type="button"
                class="text-white hover:text-green-200 transition-colors duration-200 flex items-center text-sm"
                title="Verificar precios actuales"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Verificar Precios
              </button>
            </div>
          </div>
          <div class="p-6">
            <BuscarProducto
              ref="buscarProductoRef"
              :productos="productos"
              :servicios="servicios"
              @agregar-producto="agregarProducto"
            />
            <ProductosSeleccionados
              :selectedProducts="selectedProducts"
              :productos="productos"
              :servicios="servicios"
              :quantities="quantities"
              :prices="prices"
              :discounts="discounts"
              :mostrarCalculadoraMargen="mostrarCalculadoraMargen"
              @eliminar-producto="eliminarProducto"
              @update-quantity="updateQuantity"
              @update-discount="updateDiscount"
              @calcular-total="calcularTotal"
              @mostrar-margen="mostrarMargenProducto"
            />
          </div>
        </div>

        <!-- Calculadora de Márgenes -->
        <div v-if="mostrarCalculadoraMargen" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
            <div class="flex justify-between items-center">
              <h2 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Calculadora de Márgenes
              </h2>
              <button
                @click="mostrarCalculadoraMargen = false"
                class="text-white hover:text-yellow-200"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-700 mb-2">Costo Total</div>
                <div class="text-2xl font-bold text-blue-900">
                  ${{ calculadoraMargen.costoTotal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                </div>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-green-700 mb-2">Precio de Venta</div>
                <div class="text-2xl font-bold text-green-900">
                  ${{ calculadoraMargen.precioVenta.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                </div>
              </div>
              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-purple-700 mb-2">Margen Bruto</div>
                <div class="text-2xl font-bold text-purple-900">
                  {{ calculadoraMargen.margenPorcentaje.toFixed(1) }}%
                </div>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-yellow-700 mb-2">Ganancia</div>
                <div class="text-2xl font-bold text-yellow-900">
                  ${{ calculadoraMargen.ganancia.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Descuento General -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
              Descuento General
            </h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Descuento General (%)
                </label>
                <div class="relative">
                  <input
                    type="number"
                    v-model="descuentoGeneral"
                    @input="calcularTotal"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="0.00"
                  />
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">%</span>
                  </div>
                </div>
              </div>
              <div class="flex items-end">
                <div class="text-right">
                  <div class="text-sm text-gray-600">Descuento aplicado:</div>
                  <div class="text-2xl font-bold text-orange-600">
                    ${{ calcularDescuentoGeneral().toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumen Total -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              Resumen de la Cotización
            </h2>
          </div>
          <div class="p-6">
            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ selectedProducts.length }}</div>
                <div class="text-sm text-blue-600 font-medium">Items Seleccionados</div>
              </div>
              <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                <div class="text-3xl font-bold text-green-600 mb-2">
                  {{ Object.values(quantities).reduce((sum, qty) => sum + (qty || 0), 0) }}
                </div>
                <div class="text-sm text-green-600 font-medium">Cantidad Total</div>
              </div>
              <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                <div class="text-3xl font-bold text-purple-600 mb-2">
                  ${{ totales.total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </div>
                <div class="text-sm text-purple-600 font-medium">Total Final</div>
              </div>
            </div>

            <!-- Desglose de totales -->
            <div class="bg-gray-50 rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Desglose de Precios</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center text-gray-700">
                  <span>Subtotal:</span>
                  <span class="font-semibold">${{ totales.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-orange-600" v-if="totales.descuentoGeneral > 0">
                  <span>Descuento General ({{ descuentoGeneral }}%):</span>
                  <span class="font-semibold">-${{ totales.descuentoGeneral.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-orange-600" v-if="totales.descuentoItems > 0">
                  <span>Descuentos por item:</span>
                  <span class="font-semibold">-${{ totales.descuentoItems.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-gray-700">
                  <span>Subtotal con descuentos:</span>
                  <span class="font-semibold">${{ totales.subtotalConDescuentos.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between items-center text-blue-600">
                  <span>IVA (16%):</span>
                  <span class="font-semibold">${{ totales.iva.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                </div>
                <div class="border-t border-gray-300 pt-3">
                  <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                    <span>Total Final:</span>
                    <span>${{ totales.total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row gap-4 justify-end">
          <Link
            :href="route('cotizaciones.index')"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancelar
          </Link>

          <!-- Botón Guardar como Borrador -->
          <button
            type="button"
            @click="guardarBorrador"
            :disabled="!clienteSeleccionado || selectedProducts.length === 0"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Guardar Borrador
          </button>

          <button
            type="submit"
            :disabled="!form.cliente_id || selectedProducts.length === 0 || form.processing"
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ form.processing ? 'Guardando...' : 'Crear Cotización' }}
          </button>
        </div>
      </form>

      <!-- Modal Vista Previa -->
      <div v-if="mostrarVistaPrevia" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Vista Previa de la Cotización</h3>
            <button @click="mostrarVistaPrevia = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="p-6">
            <!-- Encabezado de la cotización -->
            <div class="text-center mb-8">
              <h1 class="text-3xl font-bold text-gray-900 mb-2">COTIZACIÓN</h1>
              <p class="text-gray-600">{{ new Date().toLocaleDateString('es-MX', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
              }) }}</p>
            </div>

            <!-- Información del cliente -->
            <div v-if="clienteSeleccionado" class="mb-8 p-6 bg-gray-50 rounded-lg">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Cliente</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p><strong>Nombre:</strong> {{ clienteSeleccionado.nombre_razon_social }}</p>
                  <p v-if="clienteSeleccionado.email"><strong>Email:</strong> {{ clienteSeleccionado.email }}</p>
                  <p v-if="clienteSeleccionado.telefono"><strong>Teléfono:</strong> {{ clienteSeleccionado.telefono }}</p>
                </div>
                <div>
                  <p v-if="clienteSeleccionado.calle"><strong>Dirección:</strong> {{ clienteSeleccionado.calle }}</p>
                  <p v-if="clienteSeleccionado.rfc"><strong>RFC:</strong> {{ clienteSeleccionado.rfc }}</p>
                </div>
              </div>
            </div>

            <!-- Productos -->
            <div class="mb-8">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Productos y Servicios</h2>
              <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                  <thead>
                    <tr class="bg-gray-100">
                      <th class="border border-gray-300 px-4 py-2 text-left">Descripción</th>
                      <th class="border border-gray-300 px-4 py-2 text-center">Cantidad</th>
                      <th class="border border-gray-300 px-4 py-2 text-right">Precio Unit.</th>
                      <th class="border border-gray-300 px-4 py-2 text-right">Descuento</th>
                      <th class="border border-gray-300 px-4 py-2 text-right">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="entry in selectedProducts" :key="`${entry.tipo}-${entry.id}`">
                      <td class="border border-gray-300 px-4 py-2">
                        {{ obtenerProducto(entry.id, entry.tipo)?.nombre || obtenerProducto(entry.id, entry.tipo)?.descripcion }}
                      </td>
                      <td class="border border-gray-300 px-4 py-2 text-center">
                        {{ quantities[`${entry.tipo}-${entry.id}`] || 0 }}
                      </td>
                      <td class="border border-gray-300 px-4 py-2 text-right">
                        ${{ (prices[`${entry.tipo}-${entry.id}`] || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                      </td>
                      <td class="border border-gray-300 px-4 py-2 text-right">
                        {{ discounts[`${entry.tipo}-${entry.id}`] || 0 }}%
                      </td>
                      <td class="border border-gray-300 px-4 py-2 text-right">
                        ${{ calcularSubtotalProducto(entry).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Totales -->
            <div class="border-t border-gray-300 pt-4">
              <div class="flex justify-end">
                <div class="w-64">
                  <div class="flex justify-between py-2">
                    <span>Subtotal:</span>
                    <span>${{ totales.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
                  </div>
                  <div v-if="totales.descuentoGeneral > 0" class="flex justify-between py-2 text-orange-600">
                    <span>Descuento General:</span>
                    <span>-${{ totales.descuentoGeneral.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
                  </div>
                  <div v-if="totales.descuentoItems > 0" class="flex justify-between py-2 text-orange-600">
                    <span>Descuentos por item:</span>
                    <span>-${{ totales.descuentoItems.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
                  </div>
                  <div class="flex justify-between py-2">
                    <span>IVA (16%):</span>
                    <span>${{ totales.iva.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
                  </div>
                  <div class="flex justify-between py-2 border-t border-gray-300 font-bold text-lg">
                    <span>Total:</span>
                    <span>${{ totales.total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
            <button @click="imprimirVistaPrevia" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
              </svg>
              Imprimir
            </button>
            <button @click="mostrarVistaPrevia = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
              Cerrar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Plantillas -->
      <div v-if="mostrarPlantillas" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Plantillas de Cotización</h3>
            <button @click="mostrarPlantillas = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div v-for="plantilla in plantillas" :key="plantilla.id"
                   class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow duration-200 cursor-pointer"
                   @click="aplicarPlantilla(plantilla)">
                <div class="flex justify-between items-start mb-3">
                  <h4 class="font-semibold text-gray-900">{{ plantilla.nombre }}</h4>
                  <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                    {{ plantilla.productos.length }} items
                  </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">{{ plantilla.descripcion }}</p>
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-500">Última modificación:</span>
                  <span class="font-medium">${{ plantilla.total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
                </div>
              </div>
            </div>

            <!-- Crear nueva plantilla -->
            <div v-if="selectedProducts.length > 0" class="mt-6 p-4 border-2 border-dashed border-gray-300 rounded-lg">
              <h4 class="font-semibold text-gray-900 mb-3">Guardar como Nueva Plantilla</h4>
              <div class="flex gap-3">
                <input
                  v-model="nuevaPlantilla.nombre"
                  type="text"
                  placeholder="Nombre de la plantilla"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <input
                  v-model="nuevaPlantilla.descripcion"
                  type="text"
                  placeholder="Descripción (opcional)"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <button
                  @click="guardarPlantilla"
                  :disabled="!nuevaPlantilla.nombre"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Guardar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notificación de autoguardado -->
      <div v-if="mostrarNotificacionAutoguardado"
           class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Borrador guardado automáticamente
      </div>

      <!-- Botón ayuda/atajos -->
      <button
        @click="mostrarAtajos = !mostrarAtajos"
        class="fixed bottom-4 left-4 bg-gray-600 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 transition-colors duration-200"
        title="Mostrar atajos de teclado"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Notyf } from 'notyf';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import BuscarCliente from '@/Components/BuscarCliente.vue';
import BuscarProducto from '@/Components/BuscarProducto.vue';
import ProductosSeleccionados from '@/Components/ProductosSeleccionados.vue';

// Initialize Notyf with default configuration
const notyf = new Notyf({
  duration: 5000,
  position: { x: 'right', y: 'bottom' },
  types: [
    {
      type: 'success',
      background: '#10B981',
      icon: {
        className: 'notyf__icon--success',
        tagName: 'i',
        text: '✓'
      }
    },
    {
      type: 'error',
      background: '#EF4444',
      icon: {
        className: 'notyf__icon--error',
        tagName: 'i',
        text: '✗'
      }
    },
    {
      type: 'info',
      background: '#3B82F6',
      icon: {
        className: 'notyf__icon--info',
        tagName: 'i',
        text: 'ℹ'
      }
    }
  ]
});

// Define the dashboard layout
defineOptions({ layout: AppLayout });

// Component references
const buscarClienteRef = ref(null);
const buscarProductoRef = ref(null);

// UI states
const mostrarNotificacionAutoguardado = ref(false);
const mostrarVistaPrevia = ref(false);
const mostrarPlantillas = ref(false);
const mostrarCalculadoraMargen = ref(false);
const mostrarAtajos = ref(true);

// Autosave
const autoguardando = ref(false);
const ultimoAutoguardado = ref(null);
const intervalAutoguardado = ref(null);

// Email and validation
const emailDisponible = ref(true);
const revisandoEmail = ref(false);

// Templates
const nuevaPlantilla = ref({
  nombre: '',
  descripcion: ''
});

const plantillas = ref([
  {
    id: 1,
    nombre: 'Paquete Básico Web',
    descripcion: 'Diseño web básico con hosting',
    productos: [{ id: 1, tipo: 'producto', cantidad: 1, precio: 10000, descuento: 0 }],
    total: 15000,
    fechaModificacion: new Date().toISOString()
  },
  {
    id: 2,
    nombre: 'Consultoría TI Completa',
    descripcion: 'Auditoría y consultoría completa de sistemas',
    productos: [{ id: 1, tipo: 'servicio', cantidad: 1, precio: 40000, descuento: 0 }],
    total: 45000,
    fechaModificacion: new Date().toISOString()
  }
]);

// Props
const props = defineProps({
  clientes: Array,
  productos: {
    type: Array,
    default: () => [],
  },
  servicios: {
    type: Array,
    default: () => [],
  },
});

// Form setup
const form = useForm({
  nombre_razon_social: '',
  email: '',
  cliente_id: '',
  subtotal: 0,
  descuento_general: 0,
  descuento_items: 0,
  iva: 0,
  total: 0,
  productos: [],
});

// State variables
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const descuentoGeneral = ref(0);
const clienteSeleccionado = ref(null);

// Constants
const IVA_RATE = 0.16;

// Computed for current date
const currentDate = computed(() => {
  return new Date().toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

// Computed for totals
const totales = computed(() => {
  let subtotal = 0;
  let descuentoItems = 0;

  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    const descuentoItem = Number.parseFloat(discounts.value[key]) || 0;

    const subtotalItem = cantidad * precio;
    const descuentoItemMonto = subtotalItem * (descuentoItem / 100);

    subtotal += subtotalItem;
    descuentoItems += descuentoItemMonto;
  }

  const subtotalConDescuentoItems = subtotal - descuentoItems;
  const descuentoGeneralMonto = subtotalConDescuentoItems * (descuentoGeneral.value / 100);
  const subtotalConDescuentos = subtotalConDescuentoItems - descuentoGeneralMonto;
  const iva = subtotalConDescuentos * IVA_RATE;
  const total = subtotalConDescuentos + iva;

  return {
    subtotal,
    descuentoItems,
    descuentoGeneral: descuentoGeneralMonto,
    subtotalConDescuentos,
    iva,
    total
  };
});

// Margin calculator
const calculadoraMargen = computed(() => {
  let costoTotal = 0;
  let precioVenta = 0;

  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = parseFloat(quantities.value[key]) || 0;
    const precio = parseFloat(prices.value[key]) || 0;
    const producto = obtenerProducto(entry.id, entry.tipo);
    const costo = producto?.costo || precio * 0.7;

    costoTotal += cantidad * costo;
    precioVenta += cantidad * precio;
  }

  const ganancia = precioVenta - costoTotal;
  const margenPorcentaje = precioVenta > 0 ? (ganancia / precioVenta) * 100 : 0;

  return {
    costoTotal,
    precioVenta,
    ganancia,
    margenPorcentaje
  };
});

// Helper functions
const obtenerProducto = (id, tipo) => {
  const coleccion = tipo === 'producto' ? props.productos : props.servicios;
  return coleccion.find(item => item.id === id);
};

const calcularSubtotalProducto = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const cantidad = Number.parseFloat(quantities.value[key]) || 0;
  const precio = Number.parseFloat(prices.value[key]) || 0;
  const descuento = Number.parseFloat(discounts.value[key]) || 0;
  const subtotal = cantidad * precio;
  return subtotal - (subtotal * (descuento / 100));
};

const calcularDescuentoGeneral = () => {
  return totales.value.subtotalConDescuentos * (descuentoGeneral.value / 100);
};

const calcularTotal = () => {
  form.subtotal = totales.value.subtotal;
  form.descuento_general = totales.value.descuentoGeneral;
  form.descuento_items = totales.value.descuentoItems;
  form.iva = totales.value.iva;
  form.total = totales.value.total;
};

// Notification helper
const showNotification = (message, type = 'success') => {
  notyf.open({
    type,
    message,
    duration: 5000,
    ripple: true,
    dismissible: true
  });
};

// Client handling
const onClienteSeleccionado = (cliente) => {
  if (!cliente) {
    clienteSeleccionado.value = null;
    form.cliente_id = '';
    form.clearErrors('cliente_id');
    return;
  }

  if (clienteSeleccionado.value && clienteSeleccionado.value.id === cliente.id) {
    return;
  }

  clienteSeleccionado.value = cliente;
  form.cliente_id = cliente.id;
  form.clearErrors('cliente_id');
  showNotification(`Cliente seleccionado: ${cliente.nombre_razon_social}`);
};

const limpiarCliente = () => {
  clienteSeleccionado.value = null;
  form.cliente_id = '';
  form.clearErrors('cliente_id');
  showNotification('Selección de cliente limpiada', 'info');
};

const crearNuevoCliente = async (nombreBuscado) => {
  try {
    const response = await axios.post(route('clientes.store'), { nombre_razon_social: nombreBuscado });
    const nuevoCliente = response.data;
    if (!props.clientes.some(c => c.id === nuevoCliente.id)) {
      props.clientes.push(nuevoCliente);
    }
    onClienteSeleccionado(nuevoCliente);
    showNotification(`Cliente creado: ${nuevoCliente.nombre_razon_social}`);
  } catch (error) {
    console.error('Error creating new client:', error);
    showNotification('No se pudo crear el cliente. Inténtalo de nuevo.', 'error');
  }
};

// Product handling
const agregarProducto = (item) => {
  const itemEntry = { id: item.id, tipo: item.tipo };
  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    const key = `${item.tipo}-${item.id}`;
    quantities.value[key] = 1;
    const precio = item.tipo === 'producto' ? (item.precio_venta || 0) : (item.precio || 0);
    prices.value[key] = precio;
    discounts.value[key] = 0;
    calcularTotal();
    showNotification(`Producto añadido: ${item.nombre || item.descripcion}`);
  }
};

const eliminarProducto = (entry) => {
  const item = obtenerProducto(entry.id, entry.tipo);
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  const key = `${entry.tipo}-${entry.id}`;
  delete quantities.value[key];
  delete prices.value[key];
  delete discounts.value[key];
  calcularTotal();
  showNotification(`Producto eliminado: ${item.nombre || item.descripcion}`, 'info');
};

const updateQuantity = (key, quantity) => {
  quantities.value[key] = quantity;
  calcularTotal();
};

const updateDiscount = (key, discount) => {
  discounts.value[key] = discount;
  calcularTotal();
};

// Price verification
const verificarPrecios = async () => {
  try {
    const response = await axios.post(route('productos.verificarPrecios'), {
      productos: selectedProducts.value.map(entry => ({
        id: entry.id,
        tipo: entry.tipo
      }))
    });
    const updatedPrices = response.data;
    for (const entry of selectedProducts.value) {
      const key = `${entry.tipo}-${entry.id}`;
      if (updatedPrices[key]) {
        prices.value[key] = updatedPrices[key].precio;
      }
    }
    calcularTotal();
    showNotification('Precios verificados y actualizados');
  } catch (error) {
    console.error('Error verifying prices:', error);
    showNotification('No se pudieron verificar los precios. Inténtalo de nuevo.', 'error');
  }
};

const guardarBorrador = async () => {
  if (!clienteSeleccionado.value || selectedProducts.value.length === 0) return;

  autoguardando.value = true;
  try {
    const draftData = {
      cliente_id: form.cliente_id,
      productos: selectedProducts.value.map(entry => {
        const key = `${entry.tipo}-${entry.id}`;
        return {
          id: entry.id,
          tipo: entry.tipo,
          cantidad: quantities.value[key] || 1,
          precio: prices.value[key] || 0,
          descuento: discounts.value[key] || 0
        };
      }),
      descuento_general: descuentoGeneral.value,
      totales: totales.value
    };

    await axios.post(route('cotizaciones.draft'), draftData);
    ultimoAutoguardado.value = new Date();

    // Mostrar notificación
    mostrarNotificacionAutoguardado.value = true;
    setTimeout(() => {
      mostrarNotificacionAutoguardado.value = false;
    }, 3000);

  } catch (error) {
    console.error('Error saving draft:', error);
    showNotification('No se pudo guardar el borrador. Inténtalo de nuevo.', 'error');
  } finally {
    autoguardando.value = false;
  }
};

// Template handling
const aplicarPlantilla = (plantilla) => {
  selectedProducts.value = plantilla.productos.map(p => ({ id: p.id, tipo: p.tipo }));
  quantities.value = {};
  prices.value = {};
  discounts.value = {};
  plantilla.productos.forEach(p => {
    const key = `${p.tipo}-${p.id}`;
    quantities.value[key] = p.cantidad;
    prices.value[key] = p.precio;
    discounts.value[key] = p.descuento;
  });
  calcularTotal();
  mostrarPlantillas.value = false;
  showNotification(`Plantilla aplicada: ${plantilla.nombre}`);
};

const guardarPlantilla = async () => {
  if (!nuevaPlantilla.value.nombre) return;

  try {
    const plantillaData = {
      nombre: nuevaPlantilla.value.nombre,
      descripcion: nuevaPlantilla.value.descripcion,
      productos: selectedProducts.value.map(entry => {
        const key = `${entry.tipo}-${entry.id}`;
        return {
          id: entry.id,
          tipo: entry.tipo,
          cantidad: quantities.value[key] || 1,
          precio: prices.value[key] || 0,
          descuento: discounts.value[key] || 0
        };
      }),
      total: totales.value.total,
      fechaModificacion: new Date().toISOString()
    };

    const response = await axios.post(route('plantillas.store'), plantillaData);
    plantillas.value.push(response.data);
    nuevaPlantilla.value = { nombre: '', descripcion: '' };
    showNotification('Plantilla guardada con éxito');
  } catch (error) {
    console.error('Error saving template:', error);
    showNotification('No se pudo guardar la plantilla. Inténtalo de nuevo.', 'error');
  }
};

// Margin calculator toggle
const mostrarMargenProducto = () => {
  mostrarCalculadoraMargen.value = !mostrarCalculadoraMargen.value;
};

// Preview handling
const imprimirVistaPrevia = () => {
  window.print();
};

// Date formatting
const formatearFecha = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleString('es-MX', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Email validation
watch(() => form.email, async (nuevoEmail) => {
  if (!nuevoEmail || nuevoEmail.length < 5) {
    emailDisponible.value = true;
    return;
  }

  revisandoEmail.value = true;
  try {
    const response = await axios.get(route('clientes.checkEmail'), {
      params: { email: nuevoEmail }
    });
    emailDisponible.value = !response.data.exists;
  } catch (error) {
    console.error('Error verifying email:', error);
    emailDisponible.value = true;
  } finally {
    revisandoEmail.value = false;
  }
});

// Autosave interval
watch([selectedProducts, quantities, prices, discounts, descuentoGeneral, clienteSeleccionado], () => {
  calcularTotal();
}, { deep: true });

onMounted(() => {
  console.log('Clientes:', props.clientes);
  console.log('Productos:', props.productos);
  console.log('Servicios:', props.servicios);
  const duplicates = props.clientes.filter((cliente, index, self) =>
    index !== self.findIndex(c => c.id === cliente.id)
  );
  console.log('Clientes duplicados:', duplicates);

  intervalAutoguardado.value = setInterval(() => {
    if (clienteSeleccionado.value && selectedProducts.value.length > 0) {
      guardarBorrador();
    }
  }, 30000);

  const handleKeydown = (event) => {
    if (event.ctrlKey) {
      switch (event.key) {
        case 's':
          event.preventDefault();
          guardarBorrador();
          break;
        case 'p':
          event.preventDefault();
          if (clienteSeleccionado.value && selectedProducts.value.length > 0) {
            mostrarVistaPrevia.value = true;
          }
          break;
        case 'f':
          event.preventDefault();
          buscarClienteRef.value?.focus();
          break;
        case 'b':
          event.preventDefault();
          buscarProductoRef.value?.focus();
          break;
      }
    }
  };

  window.addEventListener('keydown', handleKeydown);

  onUnmounted(() => {
    clearInterval(intervalAutoguardado.value);
    window.removeEventListener('keydown', handleKeydown);
  });
});

const crearCotizacion = () => {
  form.clearErrors();

  if (!form.cliente_id) {
    form.setError('cliente_id', 'Debes seleccionar un cliente.');
    showNotification('Por favor, selecciona un cliente.', 'error');
    return;
  }
  if (selectedProducts.value.length === 0) {
    showNotification('Debes seleccionar al menos un producto o servicio.', 'error');
    return;
  }
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    if (!quantities.value[key] || quantities.value[key] <= 0) {
      showNotification('Todas las cantidades deben ser mayores a 0.', 'error');
      return;
    }
    if (discounts.value[key] < 0 || discounts.value[key] > 100) {
      showNotification('Los descuentos deben estar entre 0% y 100%.', 'error');
      return;
    }
  }
  if (descuentoGeneral.value < 0 || descuentoGeneral.value > 100) {
    showNotification('El descuento general debe estar entre 0% y 100%.', 'error');
    return;
  }

  form.productos = selectedProducts.value.map((entry) => {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = quantities.value[key] || 1;
    const precio = prices.value[key] || 0;
    const descuento = discounts.value[key] || 0;
    return {
      id: entry.id,
      tipo: entry.tipo,
      cantidad,
      precio,
      descuento,
      subtotal: cantidad * precio,
      descuento_monto: (cantidad * precio) * (descuento / 100)
    };
  });

  calcularTotal();

  form.post(route('cotizaciones.store'), {
    onSuccess: () => {
      selectedProducts.value = [];
      quantities.value = {};
      prices.value = {};
      discounts.value = {};
      descuentoGeneral.value = 0;
      clienteSeleccionado.value = null;
      form.reset();
      showNotification('Cotización creada con éxito');
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
      showNotification('Hubo errores de validación. Por favor, corrige los campos.', 'error');
    }
  });
};
</script>
