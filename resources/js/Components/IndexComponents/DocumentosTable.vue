<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100/50 px-6 py-4 border-b border-gray-200/60">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 tracking-tight">{{ config.titulo }}</h2>
        <div class="text-sm text-gray-600 bg-white/70 px-3 py-1 rounded-full border border-gray-200/50">
          {{ items.length }} de {{ total }} {{ config.titulo.toLowerCase() }}
        </div>
      </div>
    </div>

    <!-- Tooltip -->
    <Teleport to="body">
      <div
        v-if="showTooltip && hoveredDoc"
        class="fixed z-[9999] bg-white rounded-xl shadow-xl border border-gray-200/50 backdrop-blur-sm w-80 max-h-96 pointer-events-none transform transition-all duration-200 ease-out"
        :style="tooltipStyle"
      >
        <div class="p-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Productos</h3>
            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-medium">
              {{ (props.tipo === 'ordenescompra' ? hoveredDoc.items : hoveredDoc.productos)?.length || 0 }}
            </span>
          </div>
        </div>

        <div class="max-h-72 overflow-y-auto px-4 pb-4 custom-scrollbar">
          <div v-if="(props.tipo === 'ordenescompra' ? hoveredDoc.items : hoveredDoc.productos)?.length" class="space-y-2 pt-2">
            <div
              v-for="(producto, index) in (props.tipo === 'ordenescompra' ? hoveredDoc.items : hoveredDoc.productos)"
              :key="index"
              class="group p-3 bg-gray-50/70 rounded-lg hover:bg-gray-100/70 hover:shadow-sm transition-all duration-150"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0 mr-3">
                  <p class="text-sm font-medium text-gray-900 truncate group-hover:text-gray-800">
                    {{ producto.nombre || 'Sin nombre' }}
                  </p>
                  <div class="flex items-center mt-1.5 space-x-2 text-xs">
                    <span class="text-gray-600 bg-white/60 px-2 py-0.5 rounded-md">
                      {{ producto.cantidad || 0 }} und
                    </span>
                    <span class="text-gray-400">•</span>
                    <span class="text-gray-600">
                      ${{ formatearMoneda(producto.precio || 0) }}
                    </span>
                  </div>
                  <p v-if="producto.descripcion" class="text-xs text-gray-500 mt-1.5 line-clamp-2">
                    {{ producto.descripcion }}
                  </p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-sm font-semibold text-gray-900">
                    ${{ formatearMoneda((producto.cantidad || 0) * (producto.precio || 0)) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m2 2v4" />
              </svg>
            </div>
            <p class="text-sm text-gray-500">Sin productos registrados</p>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200/60">
        <thead class="bg-gray-50/60">
          <tr>
            <!-- Fecha -->
            <th
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort('fecha')"
            >
              <div class="flex items-center space-x-1">
                <span>Fecha</span>
                <svg
                  v-if="sortBy.startsWith('fecha')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === 'fecha-desc' ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>

            <!-- Cliente/Proveedor | Equipo | Clientes | Productos | Herramientas -->
            <th
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort(props.tipo === 'herramientas' ? 'nombre' : (isEquipos ? 'nombre' : (isCompra ? 'proveedor' : (isClientes || isProductos ? 'nombre' : 'cliente'))))"
            >
              <div class="flex items-center space-x-1">
                <span>{{ props.tipo === 'herramientas' ? 'Herramienta' : (isEquipos ? 'Equipo' : (isCompra ? 'Proveedor' : (isClientes ? 'Cliente' : (isProductos ? 'Producto' : 'Cliente')))) }}</span>
                <svg
                  v-if="sortBy.startsWith(props.tipo === 'herramientas' ? 'nombre' : (isEquipos ? 'nombre' : (isCompra ? 'proveedor' : ((isClientes || isProductos) ? 'nombre' : 'cliente'))))"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === `${props.tipo === 'herramientas' ? 'nombre' : (isEquipos ? 'nombre' : (isCompra ? 'proveedor' : ((isClientes || isProductos) ? 'nombre' : 'cliente')))}-desc` ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>

            <!-- Foto (solo herramientas) -->
            <th
              v-if="config.mostrarFoto"
              class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Foto
            </th>

            <!-- Campo extra -->
            <th
              v-if="config.mostrarCampoExtra"
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort(config.campoExtra.key)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ config.campoExtra.label }}</span>
                <svg
                  v-if="sortBy.startsWith(config.campoExtra.key)"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === `${config.campoExtra.key}-desc` ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>

            <!-- Técnico (solo herramientas) -->
            <th
              v-if="config.mostrarTecnico"
              class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Técnico Asignado
            </th>

            <!-- Precio (solo productos) -->
            <th
              v-if="config.mostrarPrecio"
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort('precio')"
            >
              <div class="flex items-center space-x-1">
                <span>Precio</span>
                <svg
                  v-if="sortBy.startsWith('precio')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === 'precio-desc' ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>

            <!-- Total -->
            <th
              v-if="config.mostrarTotal !== false"
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort('total')"
            >
              <div class="flex items-center space-x-1">
                <span>Total</span>
                <svg
                  v-if="sortBy.startsWith('total')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === 'total-desc' ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>

            <!-- Productos -->
            <th
              v-if="config.mostrarProductos !== false"
              class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Productos
            </th>

            <!-- Estado -->
            <th
              class="group px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/60 transition-colors duration-150"
              @click="onSort('estado')"
            >
              <div class="flex items-center space-x-1">
                <span>Estado</span>
                <svg
                  v-if="sortBy.startsWith('estado')"
                  :class="['w-4 h-4 transition-transform duration-200', sortBy === 'estado-desc' ? 'rotate-180' : '']"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </th>

            <!-- Estado de Pago (solo ventas) -->
            <th
              v-if="config.mostrarEstadoPago"
              class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Pago
            </th>

            <!-- Acciones -->
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200/40">
          <template v-if="items.length > 0">
            <tr
              v-for="doc in items"
              :key="doc.id"
              :class="[
                'group hover:bg-gray-50/60 transition-all duration-150 hover:shadow-sm',
                doc.estado === 'cancelado' ? 'opacity-50' : ''
              ]"
            >
              <!-- Fecha -->
              <td class="px-6 py-4">
                <div class="flex flex-col space-y-0.5">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatearFecha(doc.created_at || doc.fecha) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatearHora(doc.created_at || doc.fecha) }}
                  </div>
                </div>
              </td>

              <!-- Cliente/Proveedor | Clientes | Equipos | Productos | Herramientas -->
              <td class="px-6 py-4">
                <div class="flex flex-col space-y-0.5">
                  <!-- Para productos -->
                  <div v-if="isProductos" class="space-y-1">
                    <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800">
                      {{ doc.nombre || doc.titulo || 'Sin nombre' }}
                    </div>
                    <div v-if="doc.descripcion" class="text-xs text-gray-500 line-clamp-2 max-w-48">
                      {{ doc.descripcion }}
                    </div>
                    <div v-if="doc.categoria" class="text-xs text-gray-500">
                      Categoría: {{ doc.categoria }}
                    </div>
                  </div>

                  <!-- Para herramientas -->
                  <div v-else-if="props.tipo === 'herramientas'" class="space-y-1">
                    <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800">
                      {{ doc.titulo || 'Sin nombre' }}
                    </div>
                  </div>

                  <!-- Para servicios -->
                  <div v-else-if="props.tipo === 'servicios'" class="space-y-1">
                    <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800">
                      {{ doc.nombre || doc.titulo || 'Sin nombre' }}
                    </div>
                    <div v-if="doc.descripcion" class="text-xs text-gray-500 line-clamp-2 max-w-48">
                      {{ doc.descripcion }}
                    </div>
                  </div>

                  <!-- Otros tipos -->
                  <template v-else>
                    <!-- ventas/pedidos/compras -->
                    <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800" v-if="!isEquipos && !isClientes">
                      {{ isCompra ? (doc.proveedor?.nombre_razon_social || 'Sin proveedor') : (doc.cliente?.nombre || 'Sin cliente') }}
                    </div>
                    <!-- clientes -->
                    <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800" v-else-if="isClientes">
                      {{ doc.titulo || 'Sin nombre' }}
                    </div>
                    <!-- equipos -->
                    <div class="text-sm font-medium text-gray-900 group-hover:text-gray-800" v-else>
                      {{ doc.nombre || 'Sin nombre' }}
                    </div>

                    <!-- sublíneas -->
                    <div
                      v-if="!isEquipos && !isClientes && (isCompra ? doc.proveedor?.email : doc.cliente?.email)"
                      class="text-xs text-gray-500 truncate max-w-48"
                    >
                      {{ isCompra ? doc.proveedor?.email : doc.cliente?.email }}
                    </div>

                    <div v-else-if="isClientes && doc.subtitulo" class="text-xs text-gray-500 truncate max-w-48">
                      {{ doc.subtitulo }}
                    </div>

                    <div v-if="isEquipos && (doc.modelo || doc.marca)" class="text-xs text-gray-500 truncate max-w-48">
                      {{ [doc.marca, doc.modelo].filter(Boolean).join(' · ') }}
                    </div>
                  </template>
                </div>
              </td>

              <!-- Foto (solo herramientas) -->
              <td v-if="config.mostrarFoto" class="px-6 py-4">
                <div class="flex items-center">
                  <img
                    v-if="doc.meta?.foto"
                    :src="doc.meta.foto"
                    alt="Foto de la herramienta"
                    class="h-12 w-12 rounded-lg object-cover border-2 border-gray-200 cursor-pointer hover:border-blue-500 transition-colors"
                    @click="openImageModal(doc.meta.foto)"
                  />
                  <div
                    v-else
                    class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center"
                  >
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>
              </td>

              <!-- Campo extra -->
              <td v-if="config.mostrarCampoExtra" class="px-6 py-4">
                <!-- Productos: Stock + SKU/Código -->
                <div v-if="isProductos" class="space-y-1.5">
                  <div class="flex items-center space-x-2">
                    <div class="text-xs font-medium text-gray-600 bg-blue-50 px-2 py-1 rounded-md">
                      Stock: {{ (doc.stock ?? doc.cantidad ?? doc.existencia ?? doc.meta?.stock ?? 0) }} und
                    </div>
                  </div>
                  <div v-if="doc.sku || doc.codigo_barras" class="text-xs font-mono text-gray-500 bg-gray-100/60 px-2 py-0.5 rounded">
                    {{ doc.sku || doc.codigo_barras }}
                  </div>
                </div>

                <!-- Órdenes de compra: Número de orden -->
                <div v-else-if="props.tipo === 'ordenescompra'" class="text-sm font-mono font-medium text-gray-700 bg-gray-100/60 px-2 py-1 rounded-md inline-block">
                  {{ doc.numero_orden || doc.id || 'N/A' }}
                </div>

                <!-- Cotizaciones: Usar número formateado -->
                <div v-else-if="props.tipo === 'cotizaciones'" class="text-sm font-mono font-medium text-gray-700 bg-gray-100/60 px-2 py-1 rounded-md inline-block">
                  {{ doc.numero_cotizacion_display || doc.numero_cotizacion || doc.id || 'N/A' }}
                </div>

                <!-- Otros tipos -->
                <div v-else class="text-sm font-mono font-medium text-gray-700 bg-gray-100/60 px-2 py-1 rounded-md inline-block">
                  {{ isClientes ? (doc.extra || 'N/A') : (doc[config.campoExtra.key] || doc.codigo_barras || 'N/A') }}
                </div>
              </td>

              <!-- Técnico (solo herramientas) -->
              <td v-if="config.mostrarTecnico" class="px-6 py-4">
                <div v-if="doc.meta?.tecnico" class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                      <span class="text-green-600 font-medium text-sm">
                        {{ doc.meta.tecnico.nombre.charAt(0) }}{{ doc.meta.tecnico.apellido.charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">
                      {{ doc.meta.tecnico.nombre }} {{ doc.meta.tecnico.apellido }}
                    </div>
                  </div>
                </div>
                <div v-else class="text-sm text-gray-500 italic">Sin asignar</div>
              </td>

              <!-- Precio (solo productos) -->
              <td v-if="config.mostrarPrecio" class="px-6 py-4">
                <div class="text-sm font-semibold text-gray-900">
                  ${{ formatearMoneda(obtenerPrecio(doc)) }}
                </div>
              </td>

              <!-- Total -->
              <td v-if="config.mostrarTotal !== false" class="px-6 py-4">
                <div class="text-sm font-semibold text-gray-900">
                  <template v-if="typeof doc.total !== 'undefined' && doc.total !== null">
                    ${{ formatearMoneda(doc.total) }}
                  </template>
                  <template v-else>-</template>
                </div>
              </td>

              <!-- Productos -->
              <td
                v-if="config.mostrarProductos !== false"
                class="px-6 py-4 relative"
                @mouseenter="(props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.length ? showProductTooltip(doc, $event) : null"
                @mouseleave="hideProductTooltip"
                @mousemove="(props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.length ? updateTooltipPosition($event) : null"
              >
                <div class="flex items-center text-sm text-gray-600" :class="(props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.length ? 'cursor-help hover:text-gray-800 transition-colors duration-150' : 'opacity-60'">
                  <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center mr-2 group-hover:bg-blue-100 transition-colors duration-150">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                      />
                    </svg>
                  </div>
                  <span class="font-medium">{{ (props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.length || 0 }}</span>
                  <span class="text-gray-400 ml-1">items</span>
                </div>
              </td>

              <!-- Estado -->
              <td class="px-6 py-4">
                <span
                  :class="obtenerClasesEstado(doc.estado)"
                  class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-150 hover:shadow-sm"
                >
                  <span
                    class="w-2 h-2 rounded-full mr-2 transition-all duration-150"
                    :class="obtenerColorPuntoEstado(doc.estado)"
                  ></span>
                  {{ obtenerLabelEstado(doc.estado) }}
                </span>
              </td>

              <!-- Estado de Pago (solo ventas) -->
              <td v-if="config.mostrarEstadoPago" class="px-6 py-4">
                <div class="flex items-center">
                  <span
                    :class="doc.pagado ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                  >
                    <svg
                      :class="doc.pagado ? 'text-green-500' : 'text-yellow-500'"
                      class="w-3 h-3 mr-1"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path v-if="doc.pagado" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    {{ doc.pagado ? 'Pagado' : 'Pendiente' }}
                  </span>
                  <span v-if="doc.pagado && doc.metodo_pago" class="ml-2 text-xs text-gray-500">
                    ({{ obtenerLabelMetodoPago(doc.metodo_pago) }})
                  </span>
                </div>
              </td>

              <!-- Acciones -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-end space-x-1">
                  <button
                    @click="onVerDetalles(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-1"
                    title="Ver detalles"
                  >
                    <font-awesome-icon icon="eye" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <button
                    v-if="config.acciones.editar && doc.estado !== 'cancelado'"
                    @click="onEditar(doc.id)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-1"
                    title="Editar"
                  >
                    <font-awesome-icon icon="edit" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <button
                    v-if="config.acciones.duplicar && (tipo === 'cotizaciones' || tipo === 'pedidos' || tipo === 'ventas') && doc.estado !== 'cancelado'"
                    @click="onDuplicar(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:ring-offset-1"
                    title="Duplicar"
                  >
                    <font-awesome-icon icon="copy" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <!-- Enviar a Venta (solo pedidos) -->
                  <button
                    v-if="tipo === 'pedidos' && doc.estado !== 'cancelado'"
                    @click="onEnviarVenta(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-1"
                    title="Enviar a Venta"
                  >
                    <font-awesome-icon icon="paper-plane" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <button
                    v-if="config.acciones.imprimir && doc.estado !== 'cancelado'"
                    @click="onImprimir(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:ring-offset-1"
                    title="Imprimir"
                  >
                    <font-awesome-icon icon="print" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <!-- Botones de rentas -->
                  <button
                    v-if="config.acciones.renovar && doc.estado !== 'suspendido' && ['activo', 'proximo_vencimiento', 'vencido'].includes(doc.estado)"
                    @click="onRenovar(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-1"
                    title="Renovar contrato"
                  >
                    <font-awesome-icon icon="sync-alt" class="w-4 h-4 transition-transform duration-200 group-hover/btn:rotate-180" />
                  </button>

                  <button
                    v-if="config.acciones.suspender && doc.estado === 'activo'"
                    @click="onSuspender(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 hover:text-orange-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:ring-offset-1"
                    title="Suspender contrato"
                  >
                    <font-awesome-icon icon="pause" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <button
                    v-if="config.acciones.reactivar && doc.estado === 'suspendido'"
                    @click="onReactivar(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:ring-offset-1"
                    title="Reactivar contrato"
                  >
                    <font-awesome-icon icon="play" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <!-- Marcar como Pagado (solo ventas no pagadas) -->
                  <button
                    v-if="tipo === 'ventas' && config.acciones.marcar_pagado && !doc.pagado && doc.estado !== 'cancelado'"
                    @click="onMarcarPagado(doc)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:ring-offset-1"
                    title="Marcar como Pagado"
                  >
                    <font-awesome-icon icon="check-circle" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>

                  <button
                    v-if="config.acciones.eliminar && doc.estado !== 'cancelado'"
                    @click="onEliminar(doc.id)"
                    class="group/btn relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-1"
                    title="Eliminar"
                  >
                    <font-awesome-icon icon="trash" class="w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110" />
                  </button>
                </div>
              </td>
            </tr>
          </template>

          <!-- Empty State -->
          <tr v-else>
            <td :colspan="getColspan()" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center space-y-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                  <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div class="space-y-1">
                  <p class="text-gray-700 font-medium">No hay {{ config.titulo.toLowerCase() }}</p>
                  <p class="text-sm text-gray-500">Los documentos aparecerán aquí cuando se creen</p>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal para imagen ampliada -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showImageModal"
        class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4"
        @click.self="closeImageModal"
      >
        <div class="bg-white rounded-lg shadow-xl max-w-2xl max-h-[90vh] overflow-auto p-4">
          <img
            :src="selectedImage"
            alt="Imagen ampliada"
            class="max-w-full h-auto rounded-lg"
          />
          <button
            @click="closeImageModal"
            class="absolute top-2 right-2 text-white bg-black/50 rounded-full p-2 hover:bg-black/70 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  documentos: { type: Array, default: () => [] },
  tipo: {
    type: String,
    required: true,
    validator: (value) => ['cotizaciones', 'pedidos', 'ventas', 'compras', 'ordenescompra', 'rentas', 'equipos', 'clientes', 'productos', 'herramientas', 'servicios'].includes(value)
  },
  searchTerm: { type: String, default: '' },
  sortBy: { type: String, default: 'fecha-desc' },
  filtroEstado: { type: String, default: '' },
  mapeo: {
    type: Object,
    default: () => ({
      nombre: 'nombre_razon_social',
      rfc: 'rfc',
      activo: 'activo',
      fecha: 'created_at'
    })
  }
});

const emit = defineEmits([
  'ver-detalles','editar','eliminar','duplicar','imprimir','sort','renovar','suspender','reactivar','enviar-venta','marcar-pagado','open-image-modal'
]);

// Flags
const isCompra    = computed(() => props.tipo === 'compras' || props.tipo === 'ordenescompra');
const isEquipos   = computed(() => props.tipo === 'equipos');
const isClientes  = computed(() => props.tipo === 'clientes');
const isProductos = computed(() => props.tipo === 'productos');

// Tooltip
const showTooltip = ref(false);
const hoveredDoc = ref(null);
const tooltipPosition = ref({ x: 0, y: 0 });
let tooltipTimeout = null;

// Modal de imagen
const showImageModal = ref(false);
const selectedImage = ref('');

const getViewport = () => {
  if (typeof window === 'undefined') return { w: 1280, h: 800 };
  return { w: window.innerWidth, h: window.innerHeight };
};

const tooltipStyle = computed(() => {
  const OFFSET = 20, TOOLTIP_WIDTH = 320, TOOLTIP_HEIGHT = 384, VIEWPORT_PADDING = 16;
  const { w, h } = getViewport();

  let x = tooltipPosition.value.x + OFFSET;
  let y = tooltipPosition.value.y - (TOOLTIP_HEIGHT / 2);

  if (x + TOOLTIP_WIDTH > w - VIEWPORT_PADDING) x = tooltipPosition.value.x - TOOLTIP_WIDTH - OFFSET;
  if (x < VIEWPORT_PADDING) x = VIEWPORT_PADDING;
  if (y < VIEWPORT_PADDING) y = VIEWPORT_PADDING;
  else if (y + TOOLTIP_HEIGHT > h - VIEWPORT_PADDING) y = h - TOOLTIP_HEIGHT - VIEWPORT_PADDING;

  return {
    left: `${x}px`,
    top: `${y}px`,
    transform: showTooltip.value ? 'scale(1) translateY(0)' : 'scale(0.95) translateY(-10px)',
    opacity: showTooltip.value ? '1' : '0'
  };
});

const showProductTooltip = (doc, event) => {
  const items = props.tipo === 'ordenescompra' ? doc?.items : doc?.productos;
  if (!items?.length) return;
  clearTimeout(tooltipTimeout);
  hoveredDoc.value = doc;
  updateTooltipPosition(event);
  tooltipTimeout = setTimeout(() => { showTooltip.value = true; }, 500);
};

const hideProductTooltip = () => {
  clearTimeout(tooltipTimeout);
  tooltipTimeout = setTimeout(() => {
    showTooltip.value = false;
    hoveredDoc.value = null;
  }, 150);
};

const updateTooltipPosition = (event) => {
  tooltipPosition.value = { x: event.clientX, y: event.clientY };
};

// Config por tipo
const configCache = new Map();

const config = computed(() => {
  if (configCache.has(props.tipo)) return configCache.get(props.tipo);

  const configs = {
    cotizaciones: {
      titulo: 'Cotizaciones',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_cotizacion', label: 'N° Cotización' },
      acciones: { editar: true, duplicar: true, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'aprobada': { label: 'Aprobada', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'rechazada': { label: 'Rechazada', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' },
        'enviada': { label: 'Enviada', classes: 'bg-purple-100 text-purple-700', color: 'bg-purple-400' },
        'enviado_pedido': { label: 'Enviado a Pedido', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'convertida_pedido': { label: 'Convertida a Pedido', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'enviado_pedido': { label: 'Enviado a Pedido', classes: 'bg-indigo-100 text-indigo-700', color: 'bg-indigo-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' },
        'sin_estado': { label: 'Sin Estado', classes: 'bg-gray-100 text-gray-500', color: 'bg-gray-400' }
      }
    },
    pedidos: {
      titulo: 'Pedidos',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_pedido', label: 'N° Pedido' },
      acciones: { editar: true, duplicar: true, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'confirmado': { label: 'Confirmado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'en_preparacion': { label: 'En Preparación', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'listo_entrega': { label: 'Listo para Entrega', classes: 'bg-purple-100 text-purple-700', color: 'bg-purple-400' },
        'entregado': { label: 'Entregado', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'enviado_venta': { label: 'Enviado a Venta', classes: 'bg-indigo-100 text-indigo-700', color: 'bg-indigo-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' }
      }
    },
    ventas: {
      titulo: 'Ventas',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_venta', label: 'N° Venta' },
      acciones: { editar: true, duplicar: true, imprimir: true, eliminar: true, marcar_pagado: true },
      mostrarEstadoPago: true,
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'aprobado': { label: 'Aprobado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'facturado': { label: 'Facturado', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'pagado': { label: 'Pagado', classes: 'bg-emerald-100 text-emerald-700', color: 'bg-emerald-400' },
        'vencido': { label: 'Vencido', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' },
        'anulado': { label: 'Anulado', classes: 'bg-gray-100 text-gray-500', color: 'bg-gray-400' },
        'cancelada': { label: 'Cancelada', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' }
      }
    },
    compras: {
      titulo: 'Compras',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_compra', label: 'N° Compra' },
      acciones: { editar: true, duplicar: false, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'confirmado': { label: 'Confirmado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'en_preparacion': { label: 'En Preparación', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'listo_entrega': { label: 'Listo para Entrega', classes: 'bg-purple-100 text-purple-700', color: 'bg-purple-400' },
        'entregado': { label: 'Entregado', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' }
      }
    },
    ordenescompra: {
      titulo: 'Órdenes de Compra',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_orden', label: 'N° Orden' },
      acciones: { editar: true, duplicar: false, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'aprobado': { label: 'Aprobado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'recibida': { label: 'Recibida', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'cancelada': { label: 'Cancelada', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' }
      }
    },
    equipos: {
      titulo: 'Equipos',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_serie', label: 'Serie' },
      acciones: { editar: true, duplicar: false, imprimir: true, eliminar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'pendiente': { label: 'Pendiente', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'confirmado': { label: 'Confirmado', classes: 'bg-blue-100 text-blue-700', color: 'bg-blue-400' },
        'en_preparacion': { label: 'En Preparación', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'listo_entrega': { label: 'Listo para Entrega', classes: 'bg-purple-100 text-purple-700', color: 'bg-purple-400' },
        'entregado': { label: 'Entregado', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'cancelado': { label: 'Cancelado', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' }
      }
    },
    rentas: {
      titulo: 'Rentas',
      mostrarCampoExtra: true,
      campoExtra: { key: 'numero_contrato', label: 'N° Contrato' },
      acciones: { editar: true, duplicar: true, imprimir: true, eliminar: true, renovar: true, suspender: true, reactivar: true },
      estados: {
        'borrador': { label: 'Borrador', classes: 'bg-gray-100 text-gray-700', color: 'bg-gray-400' },
        'activo': { label: 'Activo', classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'proximo_vencimiento': { label: 'Próximo Vencimiento', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' },
        'vencido': { label: 'Vencido', classes: 'bg-red-100 text-red-700', color: 'bg-red-400' },
        'moroso': { label: 'Moroso', classes: 'bg-red-200 text-red-800', color: 'bg-red-500' },
        'suspendido': { label: 'Suspendido', classes: 'bg-yellow-100 text-yellow-700', color: 'bg-yellow-400' },
        'finalizado': { label: 'Finalizado', classes: 'bg-gray-100 text-gray-600', color: 'bg-gray-400' },
        'anulado': { label: 'Anulado', classes: 'bg-gray-100 text-gray-500', color: 'bg-gray-400' },
        'sin_estado': { label: 'Sin Estado', classes: 'bg-gray-100 text-gray-500', color: 'bg-gray-400' }
      }
    },
    clientes: {
      titulo: 'Clientes',
      mostrarCampoExtra: true,
      mostrarTotal: false,
      mostrarProductos: false,
      campoExtra: { key: 'rfc', label: 'RFC' },
      acciones: { editar: true, duplicar: false, imprimir: false, eliminar: true },
      estados: {
        'activo': { label: 'Activo',   classes: 'bg-emerald-100 text-emerald-700', color: 'bg-emerald-400' },
        'inactivo': { label: 'Inactivo', classes: 'bg-red-100 text-red-700',        color: 'bg-red-400' },
      }
    },
    // 👇 CONFIG MEJORADA PARA PRODUCTOS
    productos: {
      titulo: 'Productos',
      mostrarCampoExtra: true,   // Columna "Campo extra" visible
      mostrarPrecio: true,       // Columna "Precio" visible
      mostrarTotal: false,       // Puedes cambiar a true si necesitas "Total"
      mostrarProductos: false,   // No aplica columna "Productos" para el módulo productos
      campoExtra: { key: 'stock', label: 'Stock' }, // El header de la columna extra dirá "Stock"
      acciones: { editar: true, duplicar: false, imprimir: false, eliminar: true },
      estados: {
        'disponible':    { label: 'Disponible',    classes: 'bg-emerald-100 text-emerald-700', color: 'bg-emerald-400' },
        'bajo_stock':    { label: 'Bajo Stock',    classes: 'bg-orange-100 text-orange-700',   color: 'bg-orange-400' },
        'agotado':       { label: 'Agotado',       classes: 'bg-yellow-100 text-yellow-700',   color: 'bg-yellow-400' },
        'descontinuado': { label: 'Descontinuado', classes: 'bg-red-100 text-red-700',         color: 'bg-red-400' },
        'activo':        { label: 'Activo',        classes: 'bg-emerald-100 text-emerald-700', color: 'bg-emerald-400' },
        'inactivo':      { label: 'Inactivo',      classes: 'bg-gray-100 text-gray-600',       color: 'bg-gray-400' }
      }
    },
    herramientas: {
      titulo: 'Herramientas',
      mostrarCampoExtra: false,
      mostrarPrecio: false,
      mostrarTotal: false,
      mostrarProductos: false,
      mostrarTecnico: true,
      mostrarFoto: true,
      campoExtra: { key: 'numero_serie', label: 'N° Serie' },
      acciones: { editar: true, duplicar: false, imprimir: false, eliminar: true },
      estados: {
        'asignada':    { label: 'Asignada',    classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'sin_asignar': { label: 'Sin asignar', classes: 'bg-orange-100 text-orange-700', color: 'bg-orange-400' }
      }
    },
    servicios: {
      titulo: 'Servicios',
      mostrarCampoExtra: true,
      mostrarPrecio: true,
      mostrarTotal: false,
      mostrarProductos: false,
      mostrarTecnico: false,
      mostrarFoto: false,
      campoExtra: { key: 'codigo', label: 'Código' },
      acciones: { editar: true, duplicar: false, imprimir: false, eliminar: true },
      estados: {
        'activo':   { label: 'Activo',   classes: 'bg-green-100 text-green-700', color: 'bg-green-400' },
        'inactivo': { label: 'Inactivo', classes: 'bg-red-100 text-red-700',    color: 'bg-red-400' }
      }
    }
  };

  const result = configs[props.tipo] || configs.cotizaciones;
  configCache.set(props.tipo, result);
  return result;
});

// Cache de formatos
const formatCache = new Map();

const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible';
  const cacheKey = `fecha-${date}`;
  if (formatCache.has(cacheKey)) return formatCache.get(cacheKey);
  try {
    const time = new Date(date).getTime();
    if (Number.isNaN(time)) return 'Fecha inválida';
    const formatted = new Date(time).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    formatCache.set(cacheKey, formatted);
    return formatted;
  } catch {
    return 'Fecha inválida';
  }
};

const formatearHora = (date) => {
  if (!date) return '';
  const cacheKey = `hora-${date}`;
  if (formatCache.has(cacheKey)) return formatCache.get(cacheKey);
  try {
    const time = new Date(date).getTime();
    if (Number.isNaN(time)) return '';
    const formatted = new Date(time).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    formatCache.set(cacheKey, formatted);
    return formatted;
  } catch {
    return '';
  }
};

const formatearMoneda = (num) => {
  const value = parseFloat(num);
  const safe = Number.isFinite(value) ? value : 0;
  const cacheKey = `moneda-${safe}`;
  if (formatCache.has(cacheKey)) return formatCache.get(cacheKey);
  const formatted = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(safe);
  formatCache.set(cacheKey, formatted);
  return formatted;
};

const obtenerPrecio = (doc) => {
  const p = parseFloat(doc.precio ?? doc.precio_venta ?? doc.pv ?? 0);
  return Number.isFinite(p) ? p : 0;
};

// Estados
const obtenerClasesEstado = (estado) => config.value.estados[estado]?.classes || 'bg-gray-100 text-gray-700';
const obtenerColorPuntoEstado = (estado) => config.value.estados[estado]?.color || 'bg-gray-400';
const obtenerLabelEstado = (estado) => config.value.estados[estado]?.label || 'Pendiente';

// Métodos de pago
const obtenerLabelMetodoPago = (metodo) => {
  const labels = {
    'efectivo': 'Efectivo',
    'transferencia': 'Transferencia',
    'cheque': 'Cheque',
    'tarjeta': 'Tarjeta',
    'otros': 'Otros'
  };
  return labels[metodo] || metodo;
};

// Items filtrados y ordenados
const items = computed(() => {
  if (!Array.isArray(props.documentos)) {
    console.warn('⚠️ Documentos is not an array:', props.documentos);
    return [];
  }

  let filtered = props.documentos.slice();

  if (props.searchTerm) {
    const term = props.searchTerm.toLowerCase();
    filtered = filtered.filter(doc => {
      if (isClientes.value) {
        return (
          (doc.titulo || '').toLowerCase().includes(term) ||
          (doc.extra || '').toLowerCase().includes(term) ||
          (doc.subtitulo || '').toLowerCase().includes(term) ||
          (doc.meta?.direccion || '').toLowerCase().includes(term)
        );
      }
      if (isProductos.value) {
        return (
          (doc.nombre || '').toLowerCase().includes(term) ||
          (doc.sku || doc.codigo_barras || '').toLowerCase().includes(term) ||
          (doc.estado || '').toLowerCase().includes(term) ||
          (doc.descripcion || '').toLowerCase().includes(term)
        );
      }
      if (props.tipo === 'herramientas') {
        return (
          (doc.titulo || '').toLowerCase().includes(term) ||
          (doc.subtitulo || '').toLowerCase().includes(term) ||
          (doc.meta?.tecnico?.nombre || '').toLowerCase().includes(term) ||
          (doc.meta?.tecnico?.apellido || '').toLowerCase().includes(term)
        );
      }
      if (isEquipos.value) {
        return (
          (doc.nombre || '').toLowerCase().includes(term) ||
          (doc.modelo || '').toLowerCase().includes(term) ||
          (doc.marca || '').toLowerCase().includes(term) ||
          (doc.numero_serie || '').toLowerCase().includes(term)
        );
      }
      if (isCompra.value) {
        const items = props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos;
        return (
          (doc.proveedor?.nombre_razon_social || '').toLowerCase().includes(term) ||
          items?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
          (doc.numero_compra || doc.id || '').toString().toLowerCase().includes(term)
        );
      }
      if (props.tipo === 'ordenescompra') {
        return (
          (doc.proveedor?.nombre_razon_social || '').toLowerCase().includes(term) ||
          doc.items?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
          (doc.numero_orden || doc.id || '').toString().toLowerCase().includes(term)
        );
      }
      if (props.tipo === 'cotizaciones') {
        return (
          (doc.cliente?.nombre || '').toLowerCase().includes(term) ||
          (props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
          (doc.numero || doc.id || '').toString().toLowerCase().includes(term)
        );
      }
      if (props.tipo === 'pedidos') {
        return (
          (doc.cliente?.nombre || '').toLowerCase().includes(term) ||
          (props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
          (doc.numero_pedido || doc.id || '').toString().toLowerCase().includes(term) ||
          (doc.estado === 'enviado_venta' ? 'enviado a venta' : '').toLowerCase().includes(term)
        );
      }
      if (props.tipo === 'ventas') {
        return (
          (doc.cliente?.nombre || '').toLowerCase().includes(term) ||
          (props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
          (doc.numero_venta || doc.id || '').toString().toLowerCase().includes(term)
        );
      }
      if (props.tipo === 'servicios') {
        return (
          (doc.nombre || doc.titulo || '').toLowerCase().includes(term) ||
          (doc.codigo || '').toLowerCase().includes(term) ||
          (doc.descripcion || '').toLowerCase().includes(term)
        );
      }
      return (
        (doc.cliente?.nombre_razon_social || '').toLowerCase().includes(term) ||
        (props.tipo === 'ordenescompra' ? doc.items : props.tipo === 'ordenescompra' ? doc.items : doc.productos)?.some(p => (p.nombre || '').toLowerCase().includes(term)) ||
        (doc.numero_pedido || doc.numero_factura || doc.id || '').toString().toLowerCase().includes(term)
      );
    });
  }

  if (props.filtroEstado) {
    filtered = filtered.filter(doc => {
      if (isClientes.value) {
        // Mapear filtroEstado ('1', '0') a estado del doc ('activo', 'inactivo')
        const estadoFiltro = props.filtroEstado === '1' ? 'activo' : (props.filtroEstado === '0' ? 'inactivo' : '');
        return doc.estado === estadoFiltro;
      }
      return doc.estado === props.filtroEstado;
    });
  }

  const [field, direction] = props.sortBy.split('-');

  const getDate = (d) => {
    const t = new Date(d).getTime();
    return Number.isNaN(t) ? 0 : t;
  };

  return filtered.sort((a, b) => {
    let aVal, bVal;

    switch (field) {
      case 'fecha':
        aVal = getDate(a.created_at || a.fecha);
        bVal = getDate(b.created_at || b.fecha);
        break;
      case 'cliente':
        if (props.tipo === 'pedidos' || props.tipo === 'ventas') {
          aVal = (a.cliente?.nombre || '').toLowerCase();
          bVal = (b.cliente?.nombre || '').toLowerCase();
        } else {
          aVal = (a.cliente?.nombre_razon_social || '').toLowerCase();
          bVal = (b.cliente?.nombre_razon_social || '').toLowerCase();
        }
        break;
      case 'proveedor':
        aVal = (a.proveedor?.nombre_razon_social || '').toLowerCase();
        bVal = (b.proveedor?.nombre_razon_social || '').toLowerCase();
        break;
      case 'numero_orden':
        aVal = (a.numero_orden || a.id || '').toString().toLowerCase();
        bVal = (b.numero_orden || b.id || '').toString().toLowerCase();
        break;
      case 'numero_compra':
        aVal = (a.numero_compra || a.id || '').toString().toLowerCase();
        bVal = (b.numero_compra || b.id || '').toString().toLowerCase();
        break;
      case 'nombre': // clientes/equipos/productos/herramientas
        if (props.tipo === 'herramientas') {
          aVal = (a.titulo || '').toLowerCase();
          bVal = (b.titulo || '').toLowerCase();
        } else {
          aVal = (isClientes.value ? (a.titulo || '') : (a.nombre || a.titulo || '')).toLowerCase();
          bVal = (isClientes.value ? (b.titulo || '') : (b.nombre || b.titulo || '')).toLowerCase();
        }
        break;
      case 'rfc':
        aVal = (isClientes.value ? (a.extra || '') : (a.rfc || '')).toLowerCase();
        bVal = (isClientes.value ? (b.extra || '') : (b.rfc || '')).toLowerCase();
        break;
      case 'codigo':
        aVal = (a.codigo || '').toLowerCase();
        bVal = (b.codigo || '').toLowerCase();
        break;
      case 'email':
        aVal = (a.subtitulo || '').toLowerCase();
        bVal = (b.subtitulo || '').toLowerCase();
        break;
      case 'precio':
        aVal = obtenerPrecio(a);
        bVal = obtenerPrecio(b);
        break;
      case 'stock': // 👈 ordenar por stock cuando config.campoExtra.key === 'stock'
        aVal = parseInt(a.stock ?? a.cantidad ?? a.existencia ?? a.meta?.stock ?? 0);
        bVal = parseInt(b.stock ?? b.cantidad ?? b.existencia ?? b.meta?.stock ?? 0);
        aVal = Number.isFinite(aVal) ? aVal : 0;
        bVal = Number.isFinite(bVal) ? bVal : 0;
        break;
      case 'total':
        aVal = parseFloat(a.total); aVal = Number.isFinite(aVal) ? aVal : 0;
        bVal = parseFloat(b.total); bVal = Number.isFinite(bVal) ? bVal : 0;
        break;
      case 'estado':
        if (isClientes.value) {
          aVal = a.estado || '';
          bVal = b.estado || '';
        } else if (props.tipo === 'herramientas') {
          aVal = a.meta?.tecnico ? 'asignada' : 'sin_asignar';
          bVal = b.meta?.tecnico ? 'asignada' : 'sin_asignar';
        } else {
          aVal = a.estado || '';
          bVal = b.estado || '';
        }
        break;
      default:
        aVal = a?.[field] ?? '';
        bVal = b?.[field] ?? '';
    }

    const comparison = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
    return direction === 'desc' ? -comparison : comparison;
  });
});

const total = computed(() => props.documentos?.length || 0);

// Emits helpers
const onVerDetalles = (doc) => emit('ver-detalles', doc);
const onEditar = (id) => emit('editar', id);
const onEliminar = (id) => emit('eliminar', id);
const onDuplicar = (doc) => emit('duplicar', doc);
const onImprimir = (doc) => emit('imprimir', doc);
const onRenovar = (doc) => emit('renovar', doc);
const onSuspender = (doc) => emit('suspender', doc);
const onReactivar = (doc) => emit('reactivar', doc);
const onEnviarVenta = (doc) => emit('enviar-venta', doc);
const onMarcarPagado = (doc) => emit('marcar-pagado', doc);

const onSort = (field) => {
  const current = props.sortBy.startsWith(field) ? props.sortBy : `${field}-desc`;
  const newOrder = current === `${field}-desc` ? `${field}-asc` : `${field}-desc`;
  emit('sort', newOrder);
};

const getColspan = () => {
  let count = 4; // Fecha, Nombre, Estado, Acciones (siempre presentes)

  if (config.value.mostrarCampoExtra) count++;
  if (config.value.mostrarTecnico) count++;
  if (config.value.mostrarFoto) count++;
  if (config.value.mostrarPrecio) count++;
  if (config.value.mostrarTotal !== false) count++;
  if (config.value.mostrarProductos !== false) count++;
  if (config.value.mostrarEstadoPago) count++;

  return count;
};

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl;
  showImageModal.value = true;
};

const closeImageModal = () => {
  showImageModal.value = false;
  selectedImage.value = '';
};
</script>

<style scoped>
.custom-scrollbar { scrollbar-width: thin; scrollbar-color: #d1d5db #f3f4f6; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }

@media (prefers-contrast: high) {
  .bg-gray-50 { background-color: #f9fafb; }
  .border-gray-200 { border-color: #d1d5db; }
}

button:focus-visible { outline: 2px solid; outline-offset: 2px; }

@media (hover: none) {
  .hover\:bg-gray-50:hover { background-color: transparent; }
  .group:hover { transform: none; }
}
</style>
