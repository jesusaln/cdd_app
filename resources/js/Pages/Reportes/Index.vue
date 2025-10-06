<template>
    <Head title="Centro de Reportes" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Centro de Reportes Detallado</h1>
                        <p class="text-sm text-gray-600 mt-1">Vista completa de ventas, compras, inventario, movimientos y cortes diarios</p>
                    </div>
                    <Link
                        href="/reportes"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver al Dashboard
                    </Link>
                </div>
            </div>

            <!-- Filtros de Fecha -->
            <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="fechaInicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha Inicio
                        </label>
                        <input
                            v-model="fechaInicio"
                            type="date"
                            id="fechaInicio"
                            class="input-field"
                            @change="filtrarDatos"
                        />
                    </div>
                    <div>
                        <label for="fechaFin" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha Fin
                        </label>
                        <input
                            v-model="fechaFin"
                            type="date"
                            id="fechaFin"
                            class="input-field"
                            @change="filtrarDatos"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="limpiarFiltros"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- No Tabs - Reports Disabled -->
            <div class="border-b border-gray-200 hidden">
                <div class="px-6 py-4 text-center">
                    <p class="text-gray-500">Los reportes estÃ¡n temporalmente deshabilitados</p>
                </div>
            </div>

            <!-- AcordeÃ³n de Reportes -->
            <div class="border-b border-gray-200 bg-white">
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            type="button"
                            @click="activeTab = tab.key"
                            :class="[
                                'w-full text-left px-3 py-2 rounded-md border transition-colors',
                                activeTab === tab.key
                                    ? 'bg-blue-50 border-blue-200 text-blue-700'
                                    : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'
                            ]"
                            :title="tab.tooltip || tab.label"
                        >
                            <span class="font-medium">{{ tab.label }}</span>
                        </button>
                    </div>
                    <div class="text-xs text-gray-500 pt-2">
                        Todos los reportes estÃ¡n centralizados aquÃ­. Usa el acordeÃ³n para navegar.
                    </div>
                </div>
            </div>

            <!-- Contenido de Tabs -->
            <div class="p-6">
                <!-- Tab Ventas -->
                <div v-show="activeTab === 'ventas'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen de Ventas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(corteFiltrado) }}</div>
                                <div class="text-sm text-blue-600">Total Ventas</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(utilidadFiltrada) }}</div>
                                <div class="text-sm text-green-600">Utilidad Total</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ ventasFiltradas.length }}</div>
                                <div class="text-sm text-purple-600">NÃºmero de Ventas</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ clientesUnicosVentas }}</div>
                                <div class="text-sm text-orange-600">Clientes Atendidos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Accesos directos a reportes avanzados de inventario -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        <Link :href="route('reportes.inventario.stock-por-almacen')" class="inline-flex items-center px-3 py-2 text-sm rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                            Stock por almacÃ©n
                        </Link>
                        <Link :href="route('reportes.inventario.productos-bajo-stock')" class="inline-flex items-center px-3 py-2 text-sm rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                            Productos bajo stock
                        </Link>
                        <Link :href="route('reportes.inventario.movimientos-por-periodo')" class="inline-flex items-center px-3 py-2 text-sm rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                            Movimientos por perÃ­odo
                        </Link>
                        <Link :href="route('reportes.inventario.costos')" class="inline-flex items-center px-3 py-2 text-sm rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                            Costos de inventario
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NÂ° Venta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="venta in ventasFiltradas" :key="venta.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(venta.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ venta.cliente?.nombre_razon_social || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ venta.numero_venta || venta.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(venta.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(venta.costo_total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="calculateProfit(venta) >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(calculateProfit(venta)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': venta.pagado && venta.estado === 'aprobada',
                                            'bg-blue-100 text-blue-800': venta.estado === 'aprobada' && !venta.pagado,
                                            'bg-yellow-100 text-yellow-800': venta.estado === 'pendiente',
                                            'bg-gray-100 text-gray-800': venta.estado === 'borrador',
                                            'bg-red-100 text-red-800': venta.estado === 'cancelada'
                                        }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ venta.pagado ? 'Pagada' : 'Pendiente' }} - {{ venta.estado || 'Borrador' }}
                                        </span>
                                        <button
                                            class="ml-3 text-xs text-blue-600 hover:text-blue-800 underline"
                                            @click="toggleVentaDetails(venta.id)"
                                        >
                                            {{ expandedVentas[venta.id] ? 'Ocultar detalles' : 'Ver detalles' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="expandedVentas[venta.id]" class="bg-gray-50">
                                    <td colspan="7" class="px-6 py-4">
                                        <div class="text-sm text-gray-700 font-medium mb-2">Detalle de productos y costos</div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Cant.</th>
                                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Precio Venta</th>
                                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Costo Unitario</th>
                                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Costo Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="item in (venta.items || [])" :key="item.id">
                                                        <td class="px-4 py-2 whitespace-nowrap text-gray-600">{{ item.ventable_type?.includes('Producto') ? 'Producto' : 'Servicio' }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                                                            {{ item.ventable?.nombre || 'N/A' }}
                                                        </td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-right">{{ item.cantidad || 0 }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-right">{{ formatCurrency(item.precio || 0) }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-right">
                                                            {{ formatCurrency(item.costo_unitario || 0) }}
                                                        </td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-right">
                                                            {{ formatCurrency(((item.costo_unitario || 0) * (item.cantidad || 0))) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                </template>
                                <tr v-if="ventasFiltradas.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No hay ventas en el perÃ­odo seleccionado
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen adicional -->
                    <div v-if="ventasFiltradas.length > 0" class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Ventas por estado -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Ventas por Estado</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Pagadas y Aprobadas:</span>
                                    <span class="font-medium">{{ ventasPagadasYAprobadas }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Pendientes de Pago:</span>
                                    <span class="font-medium">{{ ventasPendientesPago }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">En Borrador:</span>
                                    <span class="font-medium">{{ ventasBorrador }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Top clientes -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Top Clientes</h4>
                            <div class="space-y-3">
                                <div v-for="cliente in topClientes" :key="cliente.nombre" class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 truncate mr-2">{{ cliente.nombre }}:</span>
                                    <span class="font-medium">{{ formatCurrency(cliente.total) }}</span>
                                </div>
                                <div v-if="topClientes.length === 0" class="text-sm text-gray-500 text-center">
                                    No hay datos de clientes
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Compras -->
                <div v-show="activeTab === 'compras'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen de Compras</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalComprasFiltrado) }}</div>
                                <div class="text-sm text-red-600">Total Compras</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ comprasFiltradas.length }}</div>
                                <div class="text-sm text-orange-600">NÃºmero de Compras</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ proveedoresUnicos }}</div>
                                <div class="text-sm text-blue-600">Proveedores</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ productosComprados }}</div>
                                <div class="text-sm text-green-600">Productos Comprados</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="compra in comprasFiltradas" :key="compra.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(compra.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ compra.proveedor?.nombre_razon_social || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ compra.factura || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(compra.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': compra.estado === 'completada',
                                            'bg-yellow-100 text-yellow-800': compra.estado === 'pendiente',
                                            'bg-red-100 text-red-800': compra.estado === 'cancelada'
                                        }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ compra.estado || 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ compra.productos?.length || 0 }} productos
                                    </td>
                                </tr>
                                <tr v-if="comprasFiltradas.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No hay compras en el perÃ­odo seleccionado
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen por proveedor -->
                    <div v-if="comprasPorProveedor.length > 0" class="mt-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Compras por Proveedor</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="proveedor in comprasPorProveedor" :key="proveedor.nombre" class="bg-gray-50 p-4 rounded-lg">
                                <div class="font-medium text-gray-900">{{ proveedor.nombre }}</div>
                                <div class="text-sm text-gray-600">{{ proveedor.compras }} compras</div>
                                <div class="text-lg font-bold text-red-600">{{ formatCurrency(proveedor.total) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Inventario -->
                <div v-show="activeTab === 'inventario'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Estado del Inventario</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ inventarioFiltrado.length }}</div>
                                <div class="text-sm text-blue-600">Total Productos</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ productosEnStock }}</div>
                                <div class="text-sm text-green-600">En Stock</div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ productosBajoStock }}</div>
                                <div class="text-sm text-yellow-600">Bajo Stock</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ productosAgotados }}</div>
                                <div class="text-sm text-red-600">Agotados</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CategorÃ­a</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Compra</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Venta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="producto in inventarioFiltrado" :key="producto.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.categoria?.nombre || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.stock }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(producto.precio_compra) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(producto.precio_venta) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="(producto.precio_venta || 0) - (producto.precio_compra || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency((producto.precio_venta || 0) - (producto.precio_compra || 0)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getEstadoClass(producto.stock, producto.stock_minimo)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ getEstadoText(producto.stock, producto.stock_minimo) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Corte Diario -->
                <div v-show="activeTab === 'corte'">
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Corte Diario</h3>
                        <p class="text-sm text-gray-500 mb-4">Accede al reporte detallado de pagos diarios</p>
                        <a
                            href="/reportes/corte-diario"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                        >
                            Ver Corte Diario Completo
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tab Clientes -->
                <div v-show="activeTab === 'clientes'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reporte de Clientes</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ clientesActivos.length }}</div>
                                <div class="text-sm text-blue-600">Clientes Activos</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ clientesConCompras }}</div>
                                <div class="text-sm text-green-600">Con Compras</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ clientesConRentas }}</div>
                                <div class="text-sm text-orange-600">Con Rentas</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ clientesDeudores }}</div>
                                <div class="text-sm text-red-600">Deudores</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TelÃ©fono</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rentas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="cliente in clientesActivos" :key="cliente.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ cliente.nombre_razon_social }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ cliente.email || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ cliente.telefono || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ cliente.ventas_count || 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ cliente.rentas_count || 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': (cliente.ventas_count || 0) > 0 || (cliente.rentas_count || 0) > 0,
                                            'bg-gray-100 text-gray-800': (cliente.ventas_count || 0) === 0 && (cliente.rentas_count || 0) === 0
                                        }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ (cliente.ventas_count || 0) > 0 || (cliente.rentas_count || 0) > 0 ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="clientesActivos.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No hay clientes registrados
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Servicios -->
                <div v-show="activeTab === 'servicios'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reporte de Servicios</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ serviciosTotales }}</div>
                                <div class="text-sm text-blue-600">Servicios Registrados</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ serviciosVendidos }}</div>
                                <div class="text-sm text-green-600">Servicios Vendidos</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ ingresosServicios }}</div>
                                <div class="text-sm text-purple-600">Ingresos por Servicios</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Servicios MÃ¡s Vendidos</h3>
                        <p class="text-sm text-gray-500 mb-4">AnÃ¡lisis de servicios por volumen de ventas</p>
                        <div class="bg-gray-50 p-6 rounded-lg max-w-2xl mx-auto">
                            <div class="text-center">
                                <p class="text-gray-600">Los datos de servicios se cargan desde el controlador especÃ­fico</p>
                                <a
                                    href="/reportes/servicios"
                                    class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                                >
                                    Ver Reporte Detallado de Servicios
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Citas -->
                <div v-show="activeTab === 'citas'">
                    <div class="text-center py-12">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Reporte de Citas</h3>
                        <p class="text-sm text-gray-500">Calendario de citas agendadas</p>
                    </div>
                </div>

                <!-- Tab Mantenimientos -->
                <div v-show="activeTab === 'mantenimientos'">
                    <div class="text-center py-12">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Reporte de Mantenimientos</h3>
                        <p class="text-sm text-gray-500">Historial de trabajos de mantenimiento</p>
                    </div>
                </div>

                <!-- Tab Rentas -->
                <div v-show="activeTab === 'rentas'">
                    <div class="text-center py-12">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Reporte de Rentas</h3>
                        <p class="text-sm text-gray-500">Equipos actualmente rentados</p>
                    </div>
                </div>

                <!-- Tab Ganancias -->
                <div v-show="activeTab === 'ganancias'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">AnÃ¡lisis Financiero</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(totalIngresos) }}</div>
                                <div class="text-sm text-green-600">Ingresos Totales</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalGastos) }}</div>
                                <div class="text-sm text-red-600">Gastos Totales</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(gananciaNeta) }}</div>
                                <div class="text-sm text-blue-600">Ganancia Neta</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ margenGanancia }}%</div>
                                <div class="text-sm text-purple-600">Margen de Ganancia</div>
                            </div>
                        </div>
                    </div>

                    <!-- GrÃ¡ficos de ingresos vs gastos -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Ingresos por CategorÃ­a</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Ventas de Productos:</span>
                                    <span class="font-medium">{{ formatCurrency(ingresosProductos) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Servicios:</span>
                                    <span class="font-medium">{{ formatCurrency(ingresosServicios) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Cobranzas:</span>
                                    <span class="font-medium">{{ formatCurrency(ingresosCobranzas) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Gastos por CategorÃ­a</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Costo de Ventas:</span>
                                    <span class="font-medium">{{ formatCurrency(costoVentas) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Compras:</span>
                                    <span class="font-medium">{{ formatCurrency(gastosCompras) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Otros Gastos:</span>
                                    <span class="font-medium">{{ formatCurrency(otrosGastos) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Reporte Financiero Detallado</h3>
                        <p class="text-sm text-gray-500 mb-4">AnÃ¡lisis completo de ingresos, gastos y ganancias</p>
                        <a
                            href="/reportes/ganancias"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                        >
                            Ver Reporte Financiero Completo
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tab Proveedores -->
                <div v-show="activeTab === 'proveedores'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">AnÃ¡lisis de Proveedores</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ proveedoresTotales }}</div>
                                <div class="text-sm text-blue-600">Proveedores Activos</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ comprasTotalesProveedores }}</div>
                                <div class="text-sm text-green-600">Compras Realizadas</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ montoTotalProveedores }}</div>
                                <div class="text-sm text-purple-600">Monto Total Comprado</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Proveedores MÃ¡s Utilizados</h3>
                        <p class="text-sm text-gray-500 mb-4">AnÃ¡lisis de proveedores por volumen de compras</p>
                        <div class="bg-gray-50 p-6 rounded-lg max-w-2xl mx-auto">
                            <div class="text-center">
                                <p class="text-gray-600">Los datos de proveedores se cargan desde el controlador especÃ­fico</p>
                                <a
                                    href="/reportes/proveedores"
                                    class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                                >
                                    Ver Reporte Detallado de Proveedores
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Personal -->
                <div v-show="activeTab === 'personal'">
                    <div class="text-center py-12">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Reporte de Personal</h3>
                        <p class="text-sm text-gray-500">Empleados, tÃ©cnicos y rendimiento</p>
                    </div>
                </div>

                <!-- Tab AuditorÃ­a -->
                <div v-show="activeTab === 'auditoria'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Registro de Actividades</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ actividadesHoy }}</div>
                                <div class="text-sm text-blue-600">Actividades Hoy</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ usuariosActivos }}</div>
                                <div class="text-sm text-green-600">Usuarios Activos</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ actividadesTotales }}</div>
                                <div class="text-sm text-purple-600">Total Actividades</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ actividadesLogin }}</div>
                                <div class="text-sm text-orange-600">Logins Hoy</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">BitÃ¡cora de Actividades</h3>
                        <p class="text-sm text-gray-500 mb-4">Registro completo de todas las operaciones del sistema</p>
                        <div class="bg-gray-50 p-6 rounded-lg max-w-2xl mx-auto">
                            <div class="text-center">
                                <p class="text-gray-600">Los datos de auditorÃ­a se cargan desde el controlador especÃ­fico</p>
                                <a
                                    href="/reportes/auditoria"
                                    class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                                >
                                    Ver BitÃ¡cora Completa
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Productos -->
                <div v-show="activeTab === 'productos'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Productos MÃ¡s Vendidos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ productosTotales }}</div>
                                <div class="text-sm text-blue-600">Productos Registrados</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ productosVendidos }}</div>
                                <div class="text-sm text-green-600">Productos Vendidos</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ unidadesVendidas }}</div>
                                <div class="text-sm text-purple-600">Unidades Vendidas</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ ingresosProductos }}</div>
                                <div class="text-sm text-orange-600">Ingresos por Productos</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Productos MÃ¡s Vendidos</h3>
                        <p class="text-sm text-gray-500 mb-4">Ranking detallado por volumen y ganancias</p>
                        <div class="bg-gray-50 p-6 rounded-lg max-w-2xl mx-auto">
                            <div class="text-center">
                                <p class="text-gray-600">Los datos de productos se cargan desde el controlador especÃ­fico</p>
                                <a
                                    href="/reportes/productos"
                                    class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                                >
                                    Ver Reporte Detallado de Productos
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Cobranzas -->
                <div v-show="activeTab === 'cobranzas'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Estado de Cobranzas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ cobranzasTotales }}</div>
                                <div class="text-sm text-green-600">Total Cobranzas</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ cobranzasPagadas }}</div>
                                <div class="text-sm text-blue-600">Pagadas</div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ cobranzasPendientes }}</div>
                                <div class="text-sm text-yellow-600">Pendientes</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ montoCobrado }}</div>
                                <div class="text-sm text-purple-600">Monto Cobrado</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Cobranzas por Renta</h3>
                        <p class="text-sm text-gray-500 mb-4">Estado detallado de pagos de rentas</p>
                        <div class="bg-gray-50 p-6 rounded-lg max-w-2xl mx-auto">
                            <div class="text-center">
                                <p class="text-gray-600">Los datos de cobranzas se cargan desde el controlador especÃ­fico</p>
                                <Link
                                    href="/reportes?tab=cobranzas"
                                    class="inline-flex items-center mt-4 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                                >
                                    Ver Reporte Detallado de Cobranzas
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Movimientos Inventario -->
                <div v-show="activeTab === 'movimientos'">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen de Movimientos de Inventario</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ totalEntradas }}</div>
                                <div class="text-sm text-green-600">Total Entradas</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ totalSalidas }}</div>
                                <div class="text-sm text-red-600">Total Salidas</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ movimientosFiltrados.length }}</div>
                                <div class="text-sm text-blue-600">NÃºmero de Movimientos</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="movimiento in movimientosFiltrados" :key="movimiento.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(movimiento.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="movimiento.tipo === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ movimiento.tipo }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ movimiento.producto?.nombre || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ movimiento.cantidad }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ movimiento.motivo || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { format, isWithinInterval } from 'date-fns';
import { es } from 'date-fns/locale';
import AppLayout from '@/Layouts/AppLayout.vue';


// Define el layout del dashboard
defineOptions({ layout: AppLayout });

const props = defineProps({
    reportesVentas: { type: Array, default: () => [] },
    corteVentas: { type: Number, default: 0 },
    utilidadVentas: { type: Number, default: 0 },
    reportesCompras: { type: Array, default: () => [] },
    totalCompras: { type: Number, default: 0 },
    inventario: { type: Array, default: () => [] },
    movimientosInventario: { type: Array, default: () => [] },
});

const page = usePage();
const urlParams = new URLSearchParams(page.url.split('?')[1] || '');
const activeTab = ref(urlParams.get('tab') || 'ventas');
// Ventas: control de filas expandibles para ver costo por producto
const expandedVentas = ref({});
const toggleVentaDetails = (id) => {
    expandedVentas.value[id] = !expandedVentas.value[id];
};
// DefiniciÃ³n del acordeÃ³n (pestaÃ±as centralizadas)
const tabs = [
    { key: 'ventas', label: 'Ventas' },
    { key: 'compras', label: 'Compras' },
    { key: 'inventario', label: 'Inventario' },
    { key: 'movimientos', label: 'Movimientos Inventario' },
    { key: 'corte', label: 'Corte Diario' },
    { key: 'clientes', label: 'Clientes' },
    { key: 'servicios', label: 'Servicios' },
    { key: 'citas', label: 'Citas' },
    { key: 'mantenimientos', label: 'Mantenimientos' },
    { key: 'rentas', label: 'Rentas' },
    { key: 'ganancias', label: 'Ganancias' },
    { key: 'proveedores', label: 'Proveedores' },
    { key: 'personal', label: 'Personal' },
    { key: 'auditoria', label: 'AuditorÃ­a' },
    { key: 'productos', label: 'Productos' },
    { key: 'cobranzas', label: 'Cobranzas' },
];
const fechaInicio = ref('');
const fechaFin = ref('');

const formatCurrency = (value) => `$${(Number.parseFloat(value) || 0).toFixed(2)}`;
const calculateProfit = (venta) => (Number.parseFloat(venta.total) || 0) - (Number.parseFloat(venta.costo_total) || 0);
const formatDate = (date) => {
    if (!date) return 'Fecha no disponible';
    try {
        return format(new Date(date), 'MMM d, yyyy h:mm a', { locale: es });
    } catch {
        return 'Fecha invÃ¡lida';
    }
};

const filtrarPorFecha = (items) => {
    if (!fechaInicio.value || !fechaFin.value) return items;

    const start = new Date(fechaInicio.value);
    const end = new Date(fechaFin.value);

    return items.filter(item => {
        const fecha = new Date(item.created_at);
        return isWithinInterval(fecha, { start, end });
    });
};

const filtrarDatos = () => {
    // Los computed se actualizan automÃ¡ticamente
};

const limpiarFiltros = () => {
    fechaInicio.value = '';
    fechaFin.value = '';
};

const ventasFiltradas = computed(() => filtrarPorFecha(props.reportesVentas));
const comprasFiltradas = computed(() => filtrarPorFecha(props.reportesCompras));
const inventarioFiltrado = computed(() => props.inventario); // Por ahora sin filtro de fecha, ya que inventario no tiene fecha
const movimientosFiltrados = computed(() => filtrarPorFecha(props.movimientosInventario));

const corteFiltrado = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + Number.parseFloat(v.total), 0));
const utilidadFiltrada = computed(() => ventasFiltradas.value.reduce((acc, v) => acc + calculateProfit(v), 0));
const totalComprasFiltrado = computed(() => comprasFiltradas.value.reduce((acc, c) => acc + Number.parseFloat(c.total), 0));

const productosEnStock = computed(() => inventarioFiltrado.value.filter(p => p.stock > 0).length);
const productosBajoStock = computed(() => inventarioFiltrado.value.filter(p => p.stock > 0 && p.stock <= (p.stock_minimo || 0)).length);
const productosAgotados = computed(() => inventarioFiltrado.value.filter(p => p.stock <= 0).length);

const totalEntradas = computed(() => movimientosFiltrados.value.filter(m => m.tipo === 'entrada').reduce((acc, m) => acc + Number.parseFloat(m.cantidad), 0));
const totalSalidas = computed(() => movimientosFiltrados.value.filter(m => m.tipo === 'salida').reduce((acc, m) => acc + Number.parseFloat(m.cantidad), 0));

// Nuevos computed para compras mejoradas
const proveedoresUnicos = computed(() => {
    const proveedores = new Set(comprasFiltradas.value.map(c => c.proveedor?.nombre_razon_social).filter(Boolean));
    return proveedores.size;
});

const productosComprados = computed(() => {
    return comprasFiltradas.value.reduce((acc, compra) => {
        return acc + (compra.productos?.length || 0);
    }, 0);
});

const comprasPorProveedor = computed(() => {
    const proveedoresMap = {};

    comprasFiltradas.value.forEach(compra => {
        const proveedorNombre = compra.proveedor?.nombre_razon_social || 'Sin proveedor';
        if (!proveedoresMap[proveedorNombre]) {
            proveedoresMap[proveedorNombre] = {
                nombre: proveedorNombre,
                compras: 0,
                total: 0
            };
        }
        proveedoresMap[proveedorNombre].compras++;
        proveedoresMap[proveedorNombre].total += Number.parseFloat(compra.total || 0);
    });

    return Object.values(proveedoresMap).sort((a, b) => b.total - a.total);
});

// Nuevos computed para ventas mejoradas
const clientesUnicosVentas = computed(() => {
    const clientes = new Set(ventasFiltradas.value.map(v => v.cliente?.nombre_razon_social).filter(Boolean));
    return clientes.size;
});

const ventasPagadasYAprobadas = computed(() => {
    return ventasFiltradas.value.filter(v => v.pagado && v.estado === 'aprobada').length;
});

const ventasPendientesPago = computed(() => {
    return ventasFiltradas.value.filter(v => !v.pagado).length;
});

const ventasBorrador = computed(() => {
    return ventasFiltradas.value.filter(v => v.estado === 'borrador').length;
});

const topClientes = computed(() => {
    const clientesMap = {};

    ventasFiltradas.value.forEach(venta => {
        const clienteNombre = venta.cliente?.nombre_razon_social || 'Sin cliente';
        if (!clientesMap[clienteNombre]) {
            clientesMap[clienteNombre] = {
                nombre: clienteNombre,
                total: 0,
                ventas: 0
            };
        }
        clientesMap[clienteNombre].total += Number.parseFloat(venta.total || 0);
        clientesMap[clienteNombre].ventas++;
    });

    return Object.values(clientesMap)
        .sort((a, b) => b.total - a.total)
        .slice(0, 5); // Top 5 clientes
});

// Datos de clientes (simulados por ahora, en producciÃ³n vendrÃ­an del controlador)
const clientesActivos = computed(() => {
    // Por ahora devolveremos datos simulados basados en las ventas
    const clientesUnicos = {};

    ventasFiltradas.value.forEach(venta => {
        if (venta.cliente) {
            const nombre = venta.cliente.nombre_razon_social;
            if (!clientesUnicos[nombre]) {
                clientesUnicos[nombre] = {
                    id: venta.cliente.id,
                    nombre_razon_social: nombre,
                    email: venta.cliente.email,
                    telefono: venta.cliente.telefono,
                    ventas_count: 0,
                    rentas_count: 0
                };
            }
            clientesUnicos[nombre].ventas_count++;
        }
    });

    return Object.values(clientesUnicos);
});

const clientesConCompras = computed(() => {
    return clientesActivos.value.filter(c => c.ventas_count > 0).length;
});

const clientesConRentas = computed(() => {
    return clientesActivos.value.filter(c => c.rentas_count > 0).length;
});

const clientesDeudores = computed(() => {
    // Por ahora devolveremos 0, en producciÃ³n vendrÃ­a del controlador
    return 0;
});

// Datos de servicios (simulados por ahora)
const serviciosTotales = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const serviciosVendidos = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const ingresosServicios = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return formatCurrency(0);
});

// Datos financieros (simulados por ahora)
const totalIngresos = computed(() => {
    return Number.parseFloat(corteFiltrado.value) + Number.parseFloat(ingresosServicios.value.replace('$', '').replace(',', ''));
});

const totalGastos = computed(() => {
    return Number.parseFloat(costoVentas.value) + Number.parseFloat(gastosCompras.value);
});

const gananciaNeta = computed(() => {
    return totalIngresos.value - totalGastos.value;
});

const margenGanancia = computed(() => {
    if (totalIngresos.value === 0) return '0.00';
    return ((gananciaNeta.value / totalIngresos.value) * 100).toFixed(2);
});

const ingresosProductos = computed(() => {
    return corteFiltrado.value;
});

const ingresosCobranzas = computed(() => {
    return formatCurrency(0); // En producciÃ³n vendrÃ­a del controlador
});

const costoVentas = computed(() => {
    return ventasFiltradas.value.reduce((acc, venta) => acc + Number.parseFloat(venta.costo_total || 0), 0);
});

const gastosCompras = computed(() => {
    return totalComprasFiltrado.value;
});

const otrosGastos = computed(() => {
    return formatCurrency(0); // En producciÃ³n vendrÃ­a del controlador
});

// Datos de productos (simulados por ahora)
const productosTotales = computed(() => {
    return inventarioFiltrado.value.length;
});

const productosVendidos = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const unidadesVendidas = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

// Datos de proveedores (simulados por ahora)
const proveedoresTotales = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return proveedoresUnicos.value;
});

const comprasTotalesProveedores = computed(() => {
    return comprasFiltradas.value.length;
});

const montoTotalProveedores = computed(() => {
    return totalComprasFiltrado.value;
});

// Datos de cobranzas (simulados por ahora)
const cobranzasTotales = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const cobranzasPagadas = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const cobranzasPendientes = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const montoCobrado = computed(() => {
    return formatCurrency(0); // En producciÃ³n vendrÃ­a del controlador
});

// Datos de auditorÃ­a (simulados por ahora)
const actividadesHoy = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const usuariosActivos = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const actividadesTotales = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const actividadesLogin = computed(() => {
    // En producciÃ³n esto vendrÃ­a del controlador
    return 0;
});

const getEstadoClass = (stock, minimo) => {
    if (stock <= 0) return 'bg-red-100 text-red-800';
    if (stock <= (minimo || 0)) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
};

const getEstadoText = (stock, minimo) => {
    if (stock <= 0) return 'Agotado';
    if (stock <= (minimo || 0)) return 'Bajo Stock';
    return 'En Stock';
};

const tabClass = (tab) => ({
    'border-blue-500 text-blue-600': activeTab.value === tab,
    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab.value !== tab,
});
</script>

<style scoped>
.container {
    max-width: 1200px;
}
</style>

