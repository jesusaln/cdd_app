<template>
  <Head title="Crear Venta" />
  <div class="ventas-create bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-7xl">
      <!-- Header con estadísticas rápidas -->
      <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Nueva Venta</h1>
            <p class="text-gray-600">Crea una nueva venta de productos y servicios</p>
          </div>
          <div class="mt-4 lg:mt-0 flex space-x-4">
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
              <div class="text-sm text-gray-500">Total Items</div>
              <div class="text-2xl font-semibold text-blue-600">{{ selectedProducts.length }}</div>
            </div>
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
              <div class="text-sm text-gray-500">Total Venta</div>
              <div class="text-2xl font-semibold text-green-600">${{ form.total }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Progreso del formulario -->
      <div class="mb-8">
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <div :class="[
              'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
              form.cliente_id ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'
            ]">
              1
            </div>
            <span class="ml-2 text-sm font-medium">Cliente</span>
          </div>
          <div class="flex-1 h-px bg-gray-300"></div>
          <div class="flex items-center">
            <div :class="[
              'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
              selectedProducts.length > 0 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'
            ]">
              2
            </div>
            <span class="ml-2 text-sm font-medium">Productos</span>
          </div>
          <div class="flex-1 h-px bg-gray-300"></div>
          <div class="flex items-center">
            <div :class="[
              'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
              form.total > 0 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'
            ]">
              3
            </div>
            <span class="ml-2 text-sm font-medium">Confirmar</span>
          </div>
        </div>
      </div>

      <form @submit.prevent="crearVenta" class="space-y-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
          <!-- Panel izquierdo: Cliente y productos -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Sección Cliente -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <div class="flex items-center mb-6">
                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Información del Cliente</h2>
              </div>

              <div class="relative">
                <label for="buscarCliente" class="block text-sm font-medium text-gray-700 mb-2">
                  Buscar Cliente *
                </label>
                <div class="relative">
                  <input
                    id="buscarCliente"
                    v-model="buscarCliente"
                    type="text"
                    placeholder="Escribe el nombre del cliente..."
                    @focus="mostrarClientes = true"
                    @blur="ocultarClientesDespuesDeTiempo"
                    @input="filtrarClientes"
                    autocomplete="off"
                    class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  />
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                  <div v-if="clienteSeleccionado" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </div>

                <!-- Dropdown de clientes -->
                <div v-if="mostrarClientes && clientesFiltrados.length > 0"
                     class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                  <div v-for="cliente in clientesFiltrados"
                       :key="cliente.id"
                       @click="seleccionarCliente(cliente)"
                       class="px-4 py-3 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0 transition-colors">
                    <div class="font-medium text-gray-900">{{ cliente.nombre_razon_social }}</div>
                    <div class="text-sm text-gray-500">{{ cliente.email || 'Sin email' }}</div>
                  </div>
                </div>

                <div v-if="clientesFiltrados.length === 0 && buscarCliente && !clienteSeleccionado"
                     class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4">
                  <div class="text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron clientes</h3>
                    <p class="mt-1 text-sm text-gray-500">Prueba con otro término de búsqueda</p>
                  </div>
                </div>
              </div>

              <!-- Información del cliente seleccionado -->
              <div v-if="clienteSeleccionado" class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <div class="flex-1">
                    <h4 class="font-medium text-green-900">Cliente seleccionado</h4>
                    <p class="text-green-700">{{ clienteSeleccionado.nombre_razon_social }}</p>
                    <p v-if="clienteSeleccionado.email" class="text-sm text-green-600">{{ clienteSeleccionado.email }}</p>
                  </div>
                  <button type="button" @click="limpiarCliente" class="text-green-600 hover:text-green-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Sección Productos -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                  <div class="bg-green-100 p-2 rounded-lg mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                  </div>
                  <h2 class="text-xl font-semibold text-gray-900">Productos y Servicios</h2>
                </div>
                <div class="flex space-x-2">
                  <button type="button"
                          @click="vistaProductos = 'productos'"
                          :class="[
                            'px-3 py-1 text-sm rounded-lg transition-colors',
                            vistaProductos === 'productos' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                          ]">
                    Productos
                  </button>
                  <button type="button"
                          @click="vistaProductos = 'servicios'"
                          :class="[
                            'px-3 py-1 text-sm rounded-lg transition-colors',
                            vistaProductos === 'servicios' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                          ]">
                    Servicios
                  </button>
                  <button type="button"
                          @click="vistaProductos = 'todos'"
                          :class="[
                            'px-3 py-1 text-sm rounded-lg transition-colors',
                            vistaProductos === 'todos' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                          ]">
                    Todos
                  </button>
                </div>
              </div>

              <div class="relative mb-6">
                <input
                  id="buscarProducto"
                  v-model="buscarProducto"
                  type="text"
                  placeholder="Buscar por nombre, código o categoría..."
                  @focus="mostrarProductos = true"
                  @blur="ocultarProductosDespuesDeTiempo"
                  @input="filtrarProductos"
                  autocomplete="off"
                  class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>

              <!-- Dropdown de productos -->
              <div v-if="mostrarProductos && productosFiltrados.length > 0"
                   class="mb-6 border border-gray-200 rounded-lg shadow-sm max-h-80 overflow-y-auto">
                <div v-for="item in productosFiltrados"
                     :key="`${item.tipo}-${item.id}`"
                     @click="agregarProducto(item)"
                     class="p-4 cursor-pointer hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <div class="flex items-center">
                        <h4 class="font-medium text-gray-900">{{ item.nombre }}</h4>
                        <span :class="[
                          'ml-2 px-2 py-1 text-xs rounded-full',
                          item.tipo === 'producto' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'
                        ]">
                          {{ item.tipo }}
                        </span>
                      </div>
                      <div class="flex items-center mt-1 space-x-4">
                        <p class="text-sm text-gray-600">Código: {{ item.codigo || 'N/A' }}</p>
                        <p v-if="item.tipo === 'producto'" class="text-sm text-gray-600">
                          Stock:
                          <span :class="item.stock > 10 ? 'text-green-600' : item.stock > 0 ? 'text-yellow-600' : 'text-red-600'">
                            {{ item.stock }}
                          </span>
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="font-semibold text-gray-900">
                        ${{ (item.precio_venta || item.precio || 0).toFixed(2) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Lista de productos seleccionados -->
              <div v-if="selectedProducts.length > 0">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Items Seleccionados</h3>
                <div class="space-y-4">
                  <div v-for="(entry, index) in selectedProducts"
                       :key="`${entry.tipo}-${entry.id}`"
                       class="bg-gray-50 border border-gray-200 rounded-lg p-4 transition-all hover:shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                      <!-- Información del producto -->
                      <div class="md:col-span-4">
                        <div class="flex items-center">
                          <div :class="[
                            'w-3 h-3 rounded-full mr-2',
                            entry.tipo === 'producto' ? 'bg-blue-500' : 'bg-purple-500'
                          ]"></div>
                          <div>
                            <h4 class="font-medium text-gray-900">
                              {{ getProductById(entry)?.nombre || 'Item no encontrado' }}
                            </h4>
                            <p class="text-sm text-gray-500">{{ entry.tipo }}</p>
                          </div>
                        </div>
                      </div>

                      <!-- Cantidad -->
                      <div class="md:col-span-2">
                        <label :for="`cantidad-${entry.tipo}-${entry.id}`" class="block text-sm font-medium text-gray-700 mb-1">
                          Cantidad
                        </label>
                        <input
                          :id="`cantidad-${entry.tipo}-${entry.id}`"
                          v-model.number="quantities[`${entry.tipo}-${entry.id}`]"
                          type="number"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          min="1"
                          :max="entry.tipo === 'producto' ? getProductById(entry)?.stock : undefined"
                          @input="calcularTotal"
                        />
                        <p v-if="entry.tipo === 'producto'" class="text-xs text-gray-500 mt-1">
                          Máx: {{ getProductById(entry)?.stock }}
                        </p>
                      </div>

                      <!-- Precio -->
                      <div class="md:col-span-2">
                        <label :for="`precio-${entry.tipo}-${entry.id}`" class="block text-sm font-medium text-gray-700 mb-1">
                          Precio
                        </label>
                        <input
                          :id="`precio-${entry.tipo}-${entry.id}`"
                          v-model.number="prices[`${entry.tipo}-${entry.id}`]"
                          type="number"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          min="0"
                          step="0.01"
                          @input="calcularTotal"
                        />
                      </div>

                      <!-- Descuento -->
                      <div class="md:col-span-2">
                        <label :for="`descuento-${entry.tipo}-${entry.id}`" class="block text-sm font-medium text-gray-700 mb-1">
                          Descuento %
                        </label>
                        <input
                          :id="`descuento-${entry.tipo}-${entry.id}`"
                          v-model.number="discounts[`${entry.tipo}-${entry.id}`]"
                          type="number"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          min="0"
                          max="100"
                          step="0.01"
                          @input="calcularTotal"
                        />
                      </div>

                      <!-- Subtotal -->
                      <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
                        <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-right font-semibold text-gray-900">
                          ${{ calcularSubtotal(entry).toFixed(2) }}
                        </div>
                      </div>

                      <!-- Botón eliminar -->
                      <div class="md:col-span-1 flex justify-center">
                        <button
                          type="button"
                          @click="eliminarProducto(entry)"
                          class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                          title="Eliminar item"
                        >
                          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Estado vacío -->
              <div v-else class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos seleccionados</h3>
                <p class="mt-1 text-sm text-gray-500">Busca y agrega productos o servicios para crear la venta</p>
              </div>
            </div>
          </div>

          <!-- Panel derecho: Resumen y acciones -->
          <div class="xl:col-span-1">
            <div class="sticky top-6 space-y-6">
              <!-- Resumen de la venta -->
              <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen de Venta</h3>

                <div class="space-y-3 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Descuentos</span>
                    <span class="font-medium text-red-600">-${{ totalDescuentos.toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">IVA ({{ ivaPercentage }}%)</span>
                    <span class="font-medium">${{ totalIva.toFixed(2) }}</span>
                  </div>
                  <hr>
                  <div class="flex justify-between text-lg font-semibold">
                    <span>Total</span>
                    <span class="text-green-600">${{ form.total }}</span>
                  </div>
                </div>

                <!-- Método de pago -->
                <div class="mt-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                  <select v-model="metodoPago"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Seleccionar método</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="credito">Crédito</option>
                  </select>
                </div>

                <!-- Notas -->
                <div class="mt-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Notas (opcional)</label>
                  <textarea
                    v-model="notas"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="Notas adicionales sobre la venta..."
                  ></textarea>
                </div>
              </div>

              <!-- Acciones -->
              <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="space-y-3">
                  <!-- Guardar venta -->
                  <button
                    type="submit"
                    :disabled="!puedeGuardar || form.processing"
                    :class="[
                      'w-full py-3 px-4 rounded-lg font-medium transition-all',
                      puedeGuardar && !form.processing
                        ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow-md'
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    ]"
                  >
                    <div v-if="form.processing" class="flex items-center justify-center">
                      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Guardando...
                    </div>
                    <div v-else class="flex items-center justify-center">
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Guardar Venta
                    </div>
                  </button>

                  <!-- Guardar como borrador -->
                  <button
                    type="button"
                    @click="guardarBorrador"
                    :disabled="form.processing"
                    class="w-full py-2 px-4 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  >
                    Guardar Borrador
                  </button>

                  <!-- Cancelar -->
                  <Link :href="route('ventas.index')"
                        class="w-full py-2 px-4 text-center block border border-red-300 rounded-lg font-medium text-red-700 hover:bg-red-50 transition-colors">
                    Cancelar
                  </Link>
                </div>
              </div>

              <!-- Atajos de teclado -->
              <div class="bg-gray-50 rounded-xl p-4 text-xs text-gray-600">
                <h4 class="font-medium mb-2">Atajos de Teclado</h4>
                <div class="space-y-1">
                  <div class="flex justify-between">
                    <span>Buscar cliente</span>
                    <kbd class="bg-gray-200 px-2 py-1 rounded">Ctrl+1</kbd>
                  </div>
                  <div class="flex justify-between">
                    <span>Buscar producto</span>
                    <kbd class="bg-gray-200 px-2 py-1 rounded">Ctrl+2</kbd>
                  </div>
                  <div class="flex justify-between">
                    <span>Guardar venta</span>
                    <kbd class="bg-gray-200 px-2 py-1 rounded">Ctrl+S</kbd>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Modal de confirmación -->
      <div v-if="mostrarModalConfirmacion"
           class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
           @click.self="mostrarModalConfirmacion = false">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 transform transition-all">
          <div class="flex items-center mb-4">
            <div class="bg-yellow-100 p-2 rounded-full mr-3">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Confirmar Venta</h3>
          </div>
          <p class="text-gray-600 mb-6">
            ¿Estás seguro de que deseas crear esta venta por un total de <strong>${{ form.total }}</strong>?
          </p>
          <div class="flex space-x-3">
            <button
              type="button"
              @click="mostrarModalConfirmacion = false"
              class="flex-1 py-2 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="button"
              @click="confirmarVenta"
              class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Confirmar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de éxito -->
      <div v-if="mostrarModalExito"
           class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 transform transition-all">
          <div class="text-center">
            <div class="bg-green-100 p-3 rounded-full w-fit mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">¡Venta Creada!</h3>
            <p class="text-gray-600 mb-6">La venta se ha registrado correctamente.</p>
            <div class="flex space-x-3">
              <button
                type="button"
                @click="crearNuevaVenta"
                class="flex-1 py-2 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
              >
                Nueva Venta
              </button>
              <Link :href="route('ventas.index')"
                    class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center">
                Ver Ventas
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  clientes: { type: Array, default: () => [] },
  productos: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
});

// Form principal
const form = useForm({
  cliente_id: '',
  total: '0.00',
  subtotal: '0.00',
  iva: '0.00',
  descuentos: '0.00',
  metodo_pago: '',
  notas: '',
  productos: [],
});

// Estados de la UI
const buscarCliente = ref('');
const buscarProducto = ref('');
const mostrarClientes = ref(false);
const mostrarProductos = ref(false);
const mostrarModalConfirmacion = ref(false);
const mostrarModalExito = ref(false);
const vistaProductos = ref('todos');
const metodoPago = ref('');
const notas = ref('');

// Estados de productos
const selectedProducts = ref([]);
const quantities = ref({});
const prices = ref({});
const discounts = ref({});
const clienteSeleccionado = ref(null);

// Configuración
const ivaPercentage = ref(16); // IVA del 16%

// Computed properties
const clientesFiltrados = computed(() => {
  if (!buscarCliente.value || buscarCliente.value.length < 2) return [];
  return props.clientes.filter((cliente) =>
    cliente.nombre_razon_social.toLowerCase().includes(buscarCliente.value.toLowerCase()) ||
    (cliente.email && cliente.email.toLowerCase().includes(buscarCliente.value.toLowerCase()))
  ).slice(0, 10); // Limitar a 10 resultados
});

const productosFiltrados = computed(() => {
  if (!buscarProducto.value || buscarProducto.value.length < 2) return [];

  let items = [];

  // Filtrar según la vista seleccionada
  if (vistaProductos.value === 'productos' || vistaProductos.value === 'todos') {
    items.push(...(props.productos || []).map(producto => ({ ...producto, tipo: 'producto' })));
  }
  if (vistaProductos.value === 'servicios' || vistaProductos.value === 'todos') {
    items.push(...(props.servicios || []).map(servicio => ({ ...servicio, tipo: 'servicio' })));
  }

  return items.filter(item =>
    (item.nombre.toLowerCase().includes(buscarProducto.value.toLowerCase()) ||
     (item.codigo && item.codigo.toLowerCase().includes(buscarProducto.value.toLowerCase()))) &&
    !selectedProducts.value.some(selected => selected.id === item.id && selected.tipo === item.tipo) &&
    (item.tipo === 'servicio' || item.stock > 0)
  ).slice(0, 20); // Limitar a 20 resultados
});

const subtotal = computed(() => {
  let total = 0;
  for (const entry of selectedProducts.value) {
    total += calcularSubtotal(entry);
  }
  return total;
});

const totalDescuentos = computed(() => {
  let total = 0;
  for (const entry of selectedProducts.value) {
    const key = `${entry.tipo}-${entry.id}`;
    const cantidad = Number.parseFloat(quantities.value[key]) || 0;
    const precio = Number.parseFloat(prices.value[key]) || 0;
    const descuento = Number.parseFloat(discounts.value[key]) || 0;
    const subtotalItem = cantidad * precio;
    total += (subtotalItem * descuento) / 100;
  }
  return total;
});

const subtotalConDescuento = computed(() => {
  return subtotal.value - totalDescuentos.value;
});

const totalIva = computed(() => {
  return (subtotalConDescuento.value * ivaPercentage.value) / 100;
});

const puedeGuardar = computed(() => {
  return form.cliente_id &&
         selectedProducts.value.length > 0 &&
         !form.processing &&
         metodoPago.value;
});

// Métodos
const getProductById = (entry) => {
  if (!entry || !entry.id || !entry.tipo) {
    return null;
  }
  if (entry.tipo === 'producto') {
    return props.productos.find((p) => p.id === entry.id) || null;
  }
  if (entry.tipo === 'servicio') {
    return props.servicios.find((s) => s.id === entry.id) || null;
  }
  return null;
};

const calcularSubtotal = (entry) => {
  const key = `${entry.tipo}-${entry.id}`;
  const cantidad = Number.parseFloat(quantities.value[key]) || 0;
  const precio = Number.parseFloat(prices.value[key]) || 0;
  const descuento = Number.parseFloat(discounts.value[key]) || 0;
  const subtotalItem = cantidad * precio;
  const descuentoAmount = (subtotalItem * descuento) / 100;
  return subtotalItem - descuentoAmount;
};

const calcularTotal = () => {
  const total = subtotalConDescuento.value + totalIva.value;
  form.total = total.toFixed(2);
  form.subtotal = subtotal.value.toFixed(2);
  form.iva = totalIva.value.toFixed(2);
  form.descuentos = totalDescuentos.value.toFixed(2);
};

const filtrarClientes = () => {
  if (clienteSeleccionado.value && buscarCliente.value !== clienteSeleccionado.value.nombre_razon_social) {
    limpiarCliente();
  }
};

const filtrarProductos = () => {
  // Lógica adicional si es necesaria
};

const seleccionarCliente = (cliente) => {
  form.cliente_id = cliente.id;
  buscarCliente.value = cliente.nombre_razon_social;
  clienteSeleccionado.value = cliente;
  mostrarClientes.value = false;
};

const limpiarCliente = () => {
  form.cliente_id = '';
  buscarCliente.value = '';
  clienteSeleccionado.value = null;
};

const agregarProducto = (item) => {
  const itemEntry = { id: item.id, tipo: item.tipo };

  if (item.tipo === 'producto' && item.stock <= 0) {
    alert(`El producto ${item.nombre} no tiene stock disponible.`);
    return;
  }

  const exists = selectedProducts.value.some(
    (entry) => entry.id === item.id && entry.tipo === item.tipo
  );

  if (!exists) {
    selectedProducts.value.push(itemEntry);
    quantities.value[`${item.tipo}-${item.id}`] = 1;
    prices.value[`${item.tipo}-${item.id}`] = item.tipo === 'producto' ?
      (item.precio_venta || 0) : (item.precio || 0);
    discounts.value[`${item.tipo}-${item.id}`] = 0;
  }

  buscarProducto.value = '';
  mostrarProductos.value = false;
  calcularTotal();
};

const eliminarProducto = (entry) => {
  selectedProducts.value = selectedProducts.value.filter(
    (item) => !(item.id === entry.id && item.tipo === entry.tipo)
  );
  delete quantities.value[`${entry.tipo}-${entry.id}`];
  delete prices.value[`${entry.tipo}-${entry.id}`];
  delete discounts.value[`${entry.tipo}-${entry.id}`];
  calcularTotal();
};

const guardarBorrador = () => {
  // Implementar lógica de guardar borrador
  const dataToSave = {
    cliente_id: form.cliente_id,
    cliente_nombre: clienteSeleccionado.value?.nombre_razon_social || '',
    selectedProducts: selectedProducts.value,
    quantities: quantities.value,
    prices: prices.value,
    discounts: discounts.value,
    metodoPago: metodoPago.value,
    notas: notas.value,
    timestamp: Date.now()
  };

  // En lugar de localStorage, mantener en memoria durante la sesión
  window.ventaBorrador = dataToSave;

  // Mostrar notificación de éxito
  alert('Borrador guardado correctamente');
};

const cargarBorrador = () => {
  const savedData = window.ventaBorrador;
  if (savedData) {
    form.cliente_id = savedData.cliente_id || '';
    buscarCliente.value = savedData.cliente_nombre || '';
    if (savedData.cliente_id) {
      clienteSeleccionado.value = props.clientes.find(c => c.id === savedData.cliente_id);
    }
    selectedProducts.value = Array.isArray(savedData.selectedProducts)
      ? savedData.selectedProducts.filter(entry => entry && typeof entry === 'object' && 'id' in entry && 'tipo' in entry)
      : [];
    quantities.value = savedData.quantities || {};
    prices.value = savedData.prices || {};
    discounts.value = savedData.discounts || {};
    metodoPago.value = savedData.metodoPago || '';
    notas.value = savedData.notas || '';
    calcularTotal();
  }
};

const ocultarClientesDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.closest('.cliente-dropdown')) {
      mostrarClientes.value = false;
    }
  }, 200);
};

const ocultarProductosDespuesDeTiempo = (event) => {
  setTimeout(() => {
    if (!event.relatedTarget || !event.relatedTarget.closest('.producto-dropdown')) {
      mostrarProductos.value = false;
    }
  }, 200);
};

const crearVenta = () => {
  if (!puedeGuardar.value) return;
  mostrarModalConfirmacion.value = true;
};

const confirmarVenta = () => {
  mostrarModalConfirmacion.value = false;

  if (selectedProducts.value.length === 0) {
    alert('Debes agregar al menos un producto o servicio.');
    return;
  }

  try {
    // Preparar datos de productos
    form.productos = selectedProducts.value.map((entry) => {
      const key = `${entry.tipo}-${entry.id}`;
      const cantidad = quantities.value[key] || 1;
      const precio = prices.value[key] || 0;
      const descuento = discounts.value[key] || 0;
      const stockDisponible = getProductById(entry)?.stock;

      if (entry.tipo === 'producto' && cantidad > stockDisponible) {
        throw new Error(`La cantidad de ${getProductById(entry).nombre} excede el stock disponible (${stockDisponible}).`);
      }

      return {
        id: entry.id,
        tipo: entry.tipo,
        cantidad,
        precio,
        descuento
      };
    });

    form.metodo_pago = metodoPago.value;
    form.notas = notas.value;

    form.post(route('ventas.store'), {
      preserveScroll: true,
      onSuccess: () => {
        // Limpiar datos guardados
        window.ventaBorrador = null;
        mostrarModalExito.value = true;
      },
      onError: (errors) => {
        console.error('Error al crear la venta:', errors);
        alert('Error al crear la venta. Por favor, revisa los datos e intenta nuevamente.');
      }
    });
  } catch (error) {
    console.error('Error en confirmarVenta:', error.message);
    alert(error.message);
  }
};

const crearNuevaVenta = () => {
  mostrarModalExito.value = false;
  // Reset form
  selectedProducts.value = [];
  quantities.value = {};
  prices.value = {};
  discounts.value = {};
  clienteSeleccionado.value = null;
  buscarCliente.value = '';
  buscarProducto.value = '';
  metodoPago.value = '';
  notas.value = '';
  form.reset();
  window.ventaBorrador = null;
};

// Atajos de teclado
const handleKeyboardShortcuts = (event) => {
  if (event.ctrlKey || event.metaKey) {
    switch (event.key) {
      case '1':
        event.preventDefault();
        document.getElementById('buscarCliente')?.focus();
        break;
      case '2':
        event.preventDefault();
        document.getElementById('buscarProducto')?.focus();
        break;
      case 's':
        event.preventDefault();
        if (puedeGuardar.value) {
          crearVenta();
        }
        break;
    }
  }
};

const handleBeforeUnload = (event) => {
  if (form.cliente_id || selectedProducts.value.length > 0) {
    event.preventDefault();
    event.returnValue = 'Tienes una venta en progreso. ¿Estás seguro de que quieres salir?';
  }
};

// Watchers
watch(quantities, calcularTotal, { deep: true });
watch(prices, calcularTotal, { deep: true });
watch(discounts, calcularTotal, { deep: true });

// Lifecycle hooks
onMounted(() => {
  cargarBorrador();
  window.addEventListener('beforeunload', handleBeforeUnload);
  window.addEventListener('keydown', handleKeyboardShortcuts);
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
  window.removeEventListener('keydown', handleKeyboardShortcuts);
});
</script>

<style scoped>
.container {
  max-width: 1400px;
}

/* Animaciones personalizadas */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.cliente-dropdown,
.producto-dropdown {
  animation: slideIn 0.2s ease-out;
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Efectos de hover mejorados */
.hover\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Estados de loading */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Responsive mejoras */
@media (max-width: 768px) {
  .grid-cols-1.md\:grid-cols-12 > * {
    margin-bottom: 1rem;
  }

  .grid-cols-1.md\:grid-cols-12 > *:last-child {
    margin-bottom: 0;
  }
}
</style>
