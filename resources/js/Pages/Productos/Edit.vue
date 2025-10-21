```vue
<template>
    <Head title="Editar Producto" />
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Editar Producto</h1>
                    <div class="flex space-x-3">
                        <button @click="$inertia.visit(route('productos.index'))"
                                class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 disabled:opacity-25 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </button>
                    </div>
                </div>

                <!-- Formulario de edición de producto -->
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Información Básica -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Información Básica
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre *
                                </label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    id="nombre"
                                    placeholder="Nombre del producto"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                    required
                                />
                                <div v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.nombre }}
                                </div>
                            </div>

                            <!-- Código -->
                            <div>
                                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Código
                                </label>
                                <input
                                    v-model="form.codigo"
                                    type="text"
                                    id="codigo"
                                    placeholder="Se asignará automáticamente"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                    disabled
                                />
                                <div v-if="form.errors.codigo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.codigo }}
                                </div>
                            </div>

                            <!-- Código de Barras -->
                            <div>
                                <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">
                                    Código de Barras *
                                </label>
                                <input
                                    v-model="form.codigo_barras"
                                    type="text"
                                    id="codigo_barras"
                                    placeholder="Código de barras"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                    required
                                />
                                <div v-if="form.errors.codigo_barras" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.codigo_barras }}
                                </div>
                            </div>

                            <!-- Requiere serie por unidad -->
                            <div class="md:col-span-1">
                                <div class="flex items-center space-x-3 mt-6">
                                    <input id="requiere_serie" type="checkbox" v-model="form.requiere_serie" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                                    <label for="requiere_serie" class="text-sm text-gray-700">Requiere capturar número de serie por unidad (en Compras)</label>
                                </div>
                            </div>

                            <!-- Tipo de Producto -->
                            <div>
                                <label for="tipo_producto" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Producto *
                                </label>
                                <select
                                    v-model="form.tipo_producto"
                                    id="tipo_producto"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                    required
                                >
                                    <option value="">Selecciona un tipo</option>
                                    <option value="fisico">Físico</option>
                                    <option value="digital">Digital</option>
                                    <option value="servicio">Servicio</option>
                                </select>
                                <div v-if="form.errors.tipo_producto" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.tipo_producto }}
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado *
                                </label>
                                <select
                                    v-model="form.estado"
                                    id="estado"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                    required
                                >
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="descontinuado">Descontinuado</option>
                                    <option value="agotado">Agotado</option>
                                </select>
                                <div v-if="form.errors.estado" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.estado }}
                                </div>
                            </div>
                        </div>

                        <!-- Descripción (ancho completo) -->
                        <div class="mt-6">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción
                            </label>
                            <textarea
                                v-model="form.descripcion"
                                id="descripcion"
                                rows="4"
                                placeholder="Descripción detallada del producto"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            ></textarea>
                            <div v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">
                                {{ form.errors.descripcion }}
                            </div>
                        </div>
                    </div>

                    <!-- Categorización -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Categorización
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Categoría -->
                            <div>
                                <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoría *
                                </label>
                                 <select
                                     v-model="form.categoria_id"
                                     id="categoria_id"
                                     class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                     required
                                 >
                                    <option value="">Selecciona una categoría</option>
                                    <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                        {{ categoria.nombre || categoria.descripcion || `Categoría ${categoria.id}` }}
                                    </option>
                                 </select>
                                 <button type="button" class="mt-2 text-sm text-blue-600 hover:underline" @click="showCategoriaModal = true">+ Nueva categoría</button>
                                <div v-if="form.errors.categoria_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.categoria_id }}
                                </div>
                                <!-- Debug: mostrar información de categorías -->
                                <div v-if="categorias && categorias.length === 0" class="mt-1 text-xs text-orange-600">
                                    ⚠️ No hay categorías disponibles
                                </div>
                                <div v-else-if="!categorias" class="mt-1 text-xs text-red-600">
                                    ❌ Error: No se recibieron datos de categorías
                                </div>
                                <div v-else class="mt-1 text-xs text-green-600">
                                    ✅ {{ categorias.length }} categoría(s) disponible(s)
                                </div>
                            </div>

                            <!-- Marca -->
                            <div>
                                <label for="marca_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Marca *
                                </label>
                                 <select
                                     v-model="form.marca_id"
                                     id="marca_id"
                                     class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                     required
                                 >
                                    <option value="">Selecciona una marca</option>
                                    <option v-for="marca in marcas" :key="marca.id" :value="marca.id">
                                        {{ marca.nombre || marca.descripcion || `Marca ${marca.id}` }}
                                    </option>
                                 </select>
                                 <button type="button" class="mt-2 text-sm text-blue-600 hover:underline" @click="showMarcaModal = true">+ Nueva marca</button>
                                <div v-if="form.errors.marca_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.marca_id }}
                                </div>
                                <!-- Debug: mostrar información de marcas -->
                                <div v-if="marcas && marcas.length === 0" class="mt-1 text-xs text-orange-600">
                                    ⚠️ No hay marcas disponibles
                                </div>
                                <div v-else-if="!marcas" class="mt-1 text-xs text-red-600">
                                    ❌ Error: No se recibieron datos de marcas
                                </div>
                                <div v-else class="mt-1 text-xs text-green-600">
                                    ✅ {{ marcas.length }} marca(s) disponible(s)
                                </div>
                            </div>

                            <!-- Proveedor -->
                            <div>
                                <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Proveedor
                                </label>
                                <select
                                    v-model="form.proveedor_id"
                                    id="proveedor_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                >
                                    <option value="">Selecciona un proveedor</option>
                                    <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                        {{ proveedor.nombre_razon_social || `Proveedor ${proveedor.id}` }}
                                    </option>
                                </select>
                                <div v-if="form.errors.proveedor_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.proveedor_id }}
                                </div>
                                <!-- Debug: mostrar información de proveedores -->
                                <div v-if="proveedores && proveedores.length === 0" class="mt-1 text-xs text-orange-600">
                                    ⚠️ No hay proveedores disponibles
                                </div>
                                <div v-else-if="!proveedores" class="mt-1 text-xs text-red-600">
                                    ❌ Error: No se recibieron datos de proveedores
                                </div>
                                <div v-else class="mt-1 text-xs text-green-600">
                                    ✅ {{ proveedores.length }} proveedor(es) disponible(s)
                                </div>
                                <div v-if="proveedores && proveedores.length > 0 && proveedores.some(p => !p.nombre_razon_social)" class="mt-1 text-xs text-orange-600">
                                    ⚠️ Algunos proveedores no tienen nombre definido
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventario y Precios -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Inventario y Precios
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Almacén -->
                            <div>
                                <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Almacén
                                </label>
                                 <select
                                     v-model="form.almacen_id"
                                     id="almacen_id"
                                     class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                 >
                                    <option value="">Selecciona un almacén</option>
                                    <option v-for="almacen in almacenes" :key="almacen.id" :value="almacen.id">
                                        {{ almacen.nombre || almacen.descripcion || `Almacén ${almacen.id}` }}
                                    </option>
                                 </select>
                                 <button type="button" class="mt-2 text-sm text-blue-600 hover:underline" @click="showAlmacenModal = true">+ Nuevo almacén</button>
                                <div v-if="form.errors.almacen_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.almacen_id }}
                                </div>
                                <!-- Debug: mostrar información de almacenes -->
                                <div v-if="almacenes && almacenes.length === 0" class="mt-1 text-xs text-orange-600">
                                    ⚠️ No hay almacenes disponibles
                                </div>
                                <div v-else-if="!almacenes" class="mt-1 text-xs text-red-600">
                                    ❌ Error: No se recibieron datos de almacenes
                                </div>
                                <div v-else class="mt-1 text-xs text-green-600">
                                    ✅ {{ almacenes.length }} almacén(es) disponible(s)
                                </div>
                            </div>

                            <!-- Stock Mínimo -->
                            <div>
                                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Mínimo
                                </label>
                                <input
                                    v-model="form.stock_minimo"
                                    type="number"
                                    id="stock_minimo"
                                    min="0"
                                    placeholder="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                />
                                <div v-if="form.errors.stock_minimo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.stock_minimo }}
                                </div>
                            </div>

                            <!-- Unidad de Medida -->
                            <div>
                                <label for="unidad_medida" class="block text-sm font-medium text-gray-700 mb-2">
                                    Unidad de Medida <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.unidad_medida"
                                    id="unidad_medida"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                    required
                                >
                                    <option value="">Selecciona una unidad</option>
                                    <option v-for="unidad in unidadesMedida" :key="unidad.id" :value="unidad.nombre">
                                        {{ unidad.nombre }}
                                    </option>
                                </select>
                                <button type="button" class="mt-2 text-sm text-blue-600 hover:underline" @click="showUnidadMedidaModal = true">+ Gestionar unidades</button>
                                <div v-if="form.errors.unidad_medida" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.unidad_medida }}
                                </div>
                            </div>

                            <!-- Precio de Compra -->
                            <div>
                                <label for="precio_compra" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de Compra *
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                                    <input
                                        v-model="form.precio_compra"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="precio_compra"
                                        placeholder="0.00"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                        required
                                    />
                                </div>
                                <div v-if="form.errors.precio_compra" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.precio_compra }}
                                </div>
                            </div>

                            <!-- Precio de Venta -->
                            <div>
                                <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de Venta *
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                                    <input
                                        v-model="form.precio_venta"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="precio_venta"
                                        placeholder="0.00"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                        required
                                    />
                                </div>
                                <div v-if="form.errors.precio_venta" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.precio_venta }}
                                </div>
                            </div>

                            <!-- Margen de Ganancia (calculado automáticamente) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Margen de Ganancia
                                </label>
                                <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-600">
                                    {{ calcularMargen() }}%
                                </div>
                            </div>

                            <!-- Descuento Máximo -->
                            <div>
                                <label for="descuento_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descuento Máximo (%)
                                </label>
                                <input
                                    v-model="form.descuento_maximo"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    id="descuento_maximo"
                                    placeholder="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                />
                                <div v-if="form.errors.descuento_maximo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.descuento_maximo }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Información Adicional
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imagen -->
                            <div>
                                <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">
                                    Imagen del Producto
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition duration-200">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="imagen" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Subir archivo</span>
                                                <input
                                                    type="file"
                                                    @change="handleImageUpload"
                                                    id="imagen"
                                                    accept="image/*"
                                                    class="sr-only"
                                                />
                                            </label>
                                            <p class="pl-1">o arrastra y suelta</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 10MB</p>
                                    </div>
                                </div>
                                <div v-if="form.errors.imagen" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.imagen }}
                                </div>
                            </div>

                            <!-- Vista previa de imagen actual -->
                            <div v-if="props.producto?.imagen" class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Imagen Actual
                                </label>
                                <div class="relative">
                                    <img
                                        :src="props.producto.imagen"
                                        :alt="props.producto.nombre"
                                        class="w-full h-48 object-cover rounded-lg border border-gray-300"
                                        @error="handleImageError"
                                    />
                                    <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-all duration-200 rounded-lg"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos adicionales -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Peso -->
                            <div>
                                <label for="peso" class="block text-sm font-medium text-gray-700 mb-2">
                                    Peso (kg)
                                </label>
                                <input
                                    v-model="form.peso"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="peso"
                                    placeholder="0.00"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                />
                                <div v-if="form.errors.peso" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.peso }}
                                </div>
                            </div>

                            <!-- Dimensiones -->
                            <div>
                                <label for="dimensiones" class="block text-sm font-medium text-gray-700 mb-2">
                                    Dimensiones (LxAxA cm)
                                </label>
                                <input
                                    v-model="form.dimensiones"
                                    type="text"
                                    id="dimensiones"
                                    placeholder="ej: 10x5x3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                />
                                <div v-if="form.errors.dimensiones" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.dimensiones }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button
                            type="button"
                            @click="$inertia.visit(route('productos.index'))"
                            class="inline-flex items-center px-6 py-3 bg-gray-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 disabled:opacity-25 transition duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 disabled:opacity-25 transition duration-200"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <!-- Modales rápidos -->
        <div v-if="showCategoriaModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Nueva categoría</h3>
            <input v-model="quickCategoria.nombre" type="text" placeholder="Nombre" class="w-full px-3 py-2 border border-gray-300 rounded mb-2" />
            <textarea v-model="quickCategoria.descripcion" placeholder="Descripción (opcional)" class="w-full px-3 py-2 border border-gray-300 rounded mb-4"></textarea>
            <div class="flex justify-end space-x-2">
              <button class="px-3 py-2 bg-gray-200 rounded" @click="closeCategoriaModal">Cancelar</button>
              <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="crearCategoriaRapida" :disabled="savingQuick">{{ savingQuick ? 'Guardando...' : 'Guardar' }}</button>
            </div>
          </div>
        </div>

        <div v-if="showMarcaModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Nueva marca</h3>
            <input v-model="quickMarca.nombre" type="text" placeholder="Nombre" class="w-full px-3 py-2 border border-gray-300 rounded mb-2" />
            <textarea v-model="quickMarca.descripcion" placeholder="Descripción (opcional)" class="w-full px-3 py-2 border border-gray-300 rounded mb-4"></textarea>
            <div class="flex justify-end space-x-2">
              <button class="px-3 py-2 bg-gray-200 rounded" @click="closeMarcaModal">Cancelar</button>
              <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="crearMarcaRapida" :disabled="savingQuick">{{ savingQuick ? 'Guardando...' : 'Guardar' }}</button>
            </div>
          </div>
        </div>

        <div v-if="showAlmacenModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Nuevo almacén</h3>
            <input v-model="quickAlmacen.nombre" type="text" placeholder="Nombre" class="w-full px-3 py-2 border border-gray-300 rounded mb-2" />
            <input v-model="quickAlmacen.ubicacion" type="text" placeholder="Ubicación/Dirección" class="w-full px-3 py-2 border border-gray-300 rounded mb-2" />
            <textarea v-model="quickAlmacen.descripcion" placeholder="Descripción (opcional)" class="w-full px-3 py-2 border border-gray-300 rounded mb-4"></textarea>
            <div class="flex justify-end space-x-2">
              <button class="px-3 py-2 bg-gray-200 rounded" @click="closeAlmacenModal">Cancelar</button>
              <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="crearAlmacenRapido" :disabled="savingQuick">{{ savingQuick ? 'Guardando...' : 'Guardar' }}</button>
            </div>
          </div>
        </div>

        <!-- Modal de gestión de unidades de medida -->
        <UnidadMedidaModal
            :show="showUnidadMedidaModal"
            :unidades="unidadesMedida"
            @close="showUnidadMedidaModal = false"
            @unidad-created="handleUnidadCreated"
            @unidad-updated="handleUnidadUpdated"
            @unidad-deleted="handleUnidadDeleted"
        />

 </template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue';
import UnidadMedidaModal from '@/Components/Modals/UnidadMedidaModal.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe el producto, categorías, marcas y proveedores como props
const props = defineProps({
    producto: Object,
    categorias: { type: Array, default: () => [] },
    marcas: { type: Array, default: () => [] },
    proveedores: { type: Array, default: () => [] },
    almacenes: { type: Array, default: () => [] },
    unidadesMedida: { type: Array, default: () => [] },
});

// Crear referencias reactivas para poder actualizar las listas
const categorias = ref([...props.categorias]);
const marcas = ref([...props.marcas]);
const almacenes = ref([...props.almacenes]);

// Lista de unidades de medida
const unidadesMedida = ref([...props.unidadesMedida]);

// Modal de unidades de medida
const showUnidadMedidaModal = ref(false);

// Inicializa el formulario con los datos del producto
const form = useForm({
    id: props.producto?.id || '',
    nombre: props.producto?.nombre || '',
    categoria_id: props.producto?.categoria_id || '',
    marca_id: props.producto?.marca_id || '',
    proveedor_id: props.producto?.proveedor_id || '',
    almacen_id: props.producto?.almacen_id || '',
    descripcion: props.producto?.descripcion || '',
    codigo: props.producto?.codigo || '',
    codigo_barras: props.producto?.codigo_barras || '',
    requiere_serie: props.producto?.requiere_serie ?? false,
    stock_minimo: props.producto?.stock_minimo || '',
    precio_compra: props.producto?.precio_compra || '',
    precio_venta: props.producto?.precio_venta || '',
    tipo_producto: props.producto?.tipo_producto || '',
    unidad_medida: props.producto?.unidad_medida || 'Pieza',
    descuento_maximo: props.producto?.descuento_maximo || '',
    peso: props.producto?.peso || '',
    dimensiones: props.producto?.dimensiones || '',
    estado: props.producto?.estado || '',
});

watch(
    () => form.unidad_medida,
    (v) => {
        if (v && typeof form.clearErrors === 'function') {
            form.clearErrors('unidad_medida');
        } else if (v) {
            form.errors.unidad_medida = null;
        }
    }
);

// Función para calcular el margen de ganancia
const calcularMargen = () => {
    const compra = parseFloat(form.precio_compra) || 0;
    const venta = parseFloat(form.precio_venta) || 0;

    if (compra === 0) return 0;

    const margen = ((venta - compra) / compra) * 100;
    return margen.toFixed(2);
};

// Función para enviar el formulario de actualización de producto
const submit = () => {
    if (!form.unidad_medida || String(form.unidad_medida).trim() === '') {
        if (typeof form.setError === 'function') {
            form.setError('unidad_medida', 'Selecciona una unidad de medida.');
        } else {
            form.errors.unidad_medida = 'Selecciona una unidad de medida.';
        }
        return;
    }
    form.put(route('productos.update', props.producto.id), {
        onSuccess: () => {
            // Producto actualizado correctamente
        },
        onError: (errors) => {
            console.error('Error al actualizar el producto:', errors);
        },
        onFinish: () => {
            console.log('Actualización de producto completada');
        },
    });
};

// Manejar carga de imágenes
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar tamaño del archivo (10MB máximo)
        if (file.size > 10 * 1024 * 1024) {
            alert('El archivo es demasiado grande. Máximo 10MB.');
            return;
        }

        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Tipo de archivo no válido. Solo se permiten JPG, PNG y GIF.');
            return;
        }

        form.imagen = file;
    }
};

// Función para manejar errores de carga de imagen
const handleImageError = (event) => {
    console.warn('Error al cargar imagen del producto:', event.target.src);
    // Establecer una imagen de placeholder local
    event.target.src = '/images/placeholder-product.svg';
    event.target.alt = 'Imagen no disponible';
};

// Modales rápidos
const showCategoriaModal = ref(false)
const showMarcaModal = ref(false)
const showAlmacenModal = ref(false)
const savingQuick = ref(false)
const quickCategoria = ref({ nombre: '', descripcion: '' })
const quickMarca = ref({ nombre: '', descripcion: '' })
const quickAlmacen = ref({ nombre: '', ubicacion: '', descripcion: '' })
const csrfToken = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content')
const closeCategoriaModal = () => { showCategoriaModal.value = false; quickCategoria.value = { nombre: '', descripcion: '' } }
const closeMarcaModal = () => { showMarcaModal.value = false; quickMarca.value = { nombre: '', descripcion: '' } }
const closeAlmacenModal = () => { showAlmacenModal.value = false; quickAlmacen.value = { nombre: '', ubicacion: '', descripcion: '' } }
const crearCategoriaRapida = async () => {
  if (!quickCategoria.value.nombre?.trim()) return;
  savingQuick.value = true;

  try {
    const apiUrl = `${window.location.origin}/api/categorias`;
    console.log('Creando categoría en:', apiUrl);

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        nombre: quickCategoria.value.nombre.trim(),
        descripcion: quickCategoria.value.descripcion?.trim() || null
      })
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Error response:', response.status, errorText);
      throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
    }

    const nuevaCategoria = await response.json();
    console.log('Categoría creada exitosamente:', nuevaCategoria);

    // Agregar la nueva categoría a la lista local
    categorias.push(nuevaCategoria);

    // Seleccionar automáticamente la nueva categoría
    form.categoria_id = nuevaCategoria.id;

    // Cerrar modal y limpiar formulario
    closeCategoriaModal();

    // Mostrar mensaje de éxito
    alert('Categoría creada exitosamente');

  } catch (error) {
    console.error('Error completo creando categoría:', error);

    let errorMessage = 'Error al crear la categoría. ';
    if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
      errorMessage += 'Verifique su conexión a internet.';
    } else if (error.message.includes('403')) {
      errorMessage += 'No tiene permisos para crear categorías.';
    } else if (error.message.includes('422')) {
      errorMessage += 'Los datos ingresados no son válidos.';
    } else {
      errorMessage += 'Por favor, inténtelo de nuevo.';
    }

    alert(errorMessage);
  } finally {
    savingQuick.value = false;
  }
}

// Funciones para manejar eventos del modal de unidades
const handleUnidadCreated = (unidad) => {
    // Agregar la nueva unidad a la lista
    unidadesMedida.value.push(unidad);
    console.log('Nueva unidad creada:', unidad);
};

const handleUnidadUpdated = (unidad) => {
    // Actualizar la unidad en la lista
    const index = unidadesMedida.value.findIndex(u => u.id === unidad.id);
    if (index !== -1) {
        unidadesMedida.value[index] = unidad;
    }
    console.log('Unidad actualizada:', unidad);
};

const handleUnidadDeleted = (unidad) => {
    // Remover la unidad de la lista
    unidadesMedida.value = unidadesMedida.value.filter(u => u.id !== unidad.id);
    console.log('Unidad eliminada:', unidad);
};
const crearMarcaRapida = async () => {
  if (!quickMarca.value.nombre?.trim()) return;
  savingQuick.value = true;

  try {
    const apiUrl = `${window.location.origin}/api/marcas`;
    console.log('Creando marca en:', apiUrl);

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        nombre: quickMarca.value.nombre.trim(),
        descripcion: quickMarca.value.descripcion?.trim() || null
      })
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Error response:', response.status, errorText);
      throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
    }

    const nuevaMarca = await response.json();
    console.log('Marca creada exitosamente:', nuevaMarca);

    // Agregar la nueva marca a la lista local
    marcas.push(nuevaMarca);

    // Seleccionar automáticamente la nueva marca
    form.marca_id = nuevaMarca.id;

    // Cerrar modal y limpiar formulario
    closeMarcaModal();

    // Mostrar mensaje de éxito
    alert('Marca creada exitosamente');

  } catch (error) {
    console.error('Error completo creando marca:', error);

    let errorMessage = 'Error al crear la marca. ';
    if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
      errorMessage += 'Verifique su conexión a internet.';
    } else if (error.message.includes('403')) {
      errorMessage += 'No tiene permisos para crear marcas.';
    } else if (error.message.includes('422')) {
      errorMessage += 'Los datos ingresados no son válidos.';
    } else {
      errorMessage += 'Por favor, inténtelo de nuevo.';
    }

    alert(errorMessage);
  } finally {
    savingQuick.value = false;
  }
}
const crearAlmacenRapido = async () => {
  if (!quickAlmacen.value.nombre?.trim()) return;
  savingQuick.value = true;

  try {
    const apiUrl = `${window.location.origin}/api/almacenes`;
    console.log('Creando almacén en:', apiUrl);

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        nombre: quickAlmacen.value.nombre.trim(),
        descripcion: quickAlmacen.value.descripcion?.trim() || null,
        ubicacion: quickAlmacen.value.ubicacion?.trim() || ''
      })
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Error response:', response.status, errorText);
      throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
    }

    const nuevoAlmacen = await response.json();
    console.log('Almacén creado exitosamente:', nuevoAlmacen);

    // Agregar el nuevo almacén a la lista local
    almacenes.push(nuevoAlmacen);

    // Seleccionar automáticamente el nuevo almacén
    form.almacen_id = nuevoAlmacen.id;

    // Cerrar modal y limpiar formulario
    closeAlmacenModal();

    // Mostrar mensaje de éxito
    alert('Almacén creado exitosamente');

  } catch (error) {
    console.error('Error completo creando almacén:', error);

    let errorMessage = 'Error al crear el almacén. ';
    if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
      errorMessage += 'Verifique su conexión a internet.';
    } else if (error.message.includes('403')) {
      errorMessage += 'No tiene permisos para crear almacenes.';
    } else if (error.message.includes('422')) {
      errorMessage += 'Los datos ingresados no son válidos.';
    } else {
      errorMessage += 'Por favor, inténtelo de nuevo.';
    }

    alert(errorMessage);
  } finally {
    savingQuick.value = false;
  }
}
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Animación para el spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
```
