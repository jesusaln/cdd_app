<template>
    <Head title="Gestión de Carros" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                            Gestión de Carros
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Administra tu inventario de vehículos de manera eficiente
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button
                            @click="toggleView"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                        >
                            <svg v-if="vistaTabla" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            {{ vistaTabla ? 'Vista Tarjetas' : 'Vista Tabla' }}
                        </button>

                        <Link
                            :href="route('carros.create')"
                            class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar Carro
                        </Link>
                    </div>
                </div>

                <!-- Search and Filter Bar -->
                <div class="mt-6 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                v-model="busqueda"
                                type="text"
                                placeholder="Buscar por marca, modelo o color..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            >
                        </div>
                    </div>
                    <select
                        v-model="filtroAnio"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    >
                        <option value="">Todos los años</option>
                        <option v-for="anio in aniosUnicos" :key="anio" :value="anio">{{ anio }}</option>
                    </select>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Carros</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ carrosFiltrados.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Año Promedio</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ anioPromedio }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Marcas Únicas</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ marcasUnicas.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Km Promedio</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ kilometrajePromedio }}k</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div v-if="carrosFiltrados.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Table View -->
                <div v-if="vistaTabla" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vehículo</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Detalles</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kilometraje</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="carro in carrosFiltrados" :key="carro.id" class="hover:bg-gray-50 transition-colors duration-200">
                                <!-- Columna de vehículo con foto -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-16 w-16">
                                            <img v-if="carro.foto"
                                                :src="carro.foto"
                                                :alt="`${carro.marca} ${carro.modelo}`"
                                                class="h-16 w-16 rounded-xl object-cover shadow-sm border border-gray-200">
                                            <div v-else class="h-16 w-16 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border border-gray-200">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 011-1h1m2 2V5a2 2 0 011-1h1m2 2V5a2 2 0 011-1h1"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ carro.marca }}</div>
                                            <div class="text-sm text-gray-600">{{ carro.modelo }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Detalles -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ carro.anio }}</div>
                                    <div class="text-sm text-gray-600 capitalize">{{ carro.color }}</div>
                                </td>

                                <!-- Kilometraje -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ formatearKilometraje(carro.kilometraje) }} km
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ obtenerEstadoKilometraje(carro.kilometraje) }}
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        obtenerClaseEstado(carro.anio)
                                    ]">
                                        {{ obtenerEstadoVehiculo(carro.anio) }}
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button @click="abrirModal(carro)"
                                            class="inline-flex items-center p-2 text-sm text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                            title="Ver detalles">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        <Link :href="route('carros.edit', carro.id)"
                                            class="inline-flex items-center p-2 text-sm text-amber-600 hover:text-amber-900 hover:bg-amber-50 rounded-lg transition-all duration-200"
                                            title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </Link>

                                        <button @click="abrirModalEliminar(carro.id)"
                                            class="inline-flex items-center p-2 text-sm text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200"
                                            title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Card View -->
                <div v-else class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div v-for="carro in carrosFiltrados" :key="carro.id"
                            class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">

                            <!-- Imagen del carro -->
                            <div class="aspect-w-16 aspect-h-9 bg-gradient-to-br from-gray-100 to-gray-200">
                                <img v-if="carro.foto"
                                    :src="carro.foto"
                                    :alt="`${carro.marca} ${carro.modelo}`"
                                    class="w-full h-48 object-cover">
                                <div v-else class="w-full h-48 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 011-1h1m2 2V5a2 2 0 011-1h1m2 2V5a2 2 0 011-1h1"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Contenido de la tarjeta -->
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ carro.marca }}</h3>
                                        <p class="text-sm text-gray-600">{{ carro.modelo }}</p>
                                    </div>
                                    <span :class="[
                                        'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                        obtenerClaseEstado(carro.anio)
                                    ]">
                                        {{ obtenerEstadoVehiculo(carro.anio) }}
                                    </span>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Año:</span>
                                        <span class="font-medium text-gray-900">{{ carro.anio }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Color:</span>
                                        <span class="font-medium text-gray-900 capitalize">{{ carro.color }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Kilometraje:</span>
                                        <span class="font-medium text-gray-900">{{ formatearKilometraje(carro.kilometraje) }} km</span>
                                    </div>
                                </div>

                                <!-- Acciones de la tarjeta -->
                                <div class="flex space-x-2">
                                    <button @click="abrirModal(carro)"
                                        class="flex-1 bg-blue-50 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors duration-200">
                                        Ver
                                    </button>
                                    <Link :href="route('carros.edit', carro.id)"
                                        class="flex-1 bg-amber-50 text-amber-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-amber-100 transition-colors duration-200 text-center">
                                        Editar
                                    </Link>
                                    <button @click="abrirModalEliminar(carro.id)"
                                        class="px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
                    <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 011-1h1m2 2V5a2 2 0 011-1h1m2 2V5a2 2 0 011-1h1"/>
                    </svg>
                    <h3 class="mt-6 text-lg font-medium text-gray-900">No hay carros registrados</h3>
                    <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
                        {{ busqueda || filtroAnio ? 'No se encontraron carros con los filtros aplicados' : 'Comienza agregando tu primer vehículo al inventario' }}
                    </p>
                    <div class="mt-6">
                        <Link v-if="!busqueda && !filtroAnio"
                            :href="route('carros.create')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar Primer Carro
                        </Link>
                        <button v-else
                            @click="limpiarFiltros"
                            class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-all duration-200">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Detalles -->
        <ModalCarro :mostrar-modal="mostrarModalDetalles" :carro="carroSeleccionado" @cerrar="cerrarModalDetalles" />

        <!-- Modal de Confirmación de Eliminación Mejorado -->
        <Transition name="modal" appear>
            <div v-if="mostrarModalEliminar" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex justify-center items-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-300">
                    <div class="p-6">
                        <!-- Icono de advertencia -->
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>

                        <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Confirmar Eliminación</h3>
                        <p class="text-gray-600 text-center mb-6">
                            ¿Estás seguro de que deseas eliminar este carro? Esta acción no se puede deshacer.
                        </p>

                        <div class="flex space-x-3">
                            <button @click="cerrarModalEliminar"
                                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors duration-200">
                                Cancelar
                            </button>
                            <button @click="eliminarCarro"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors duration-200">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import ModalCarro from '@/Components/Modal/ModalCarro.vue';

// Define el layout del dashboard
defineOptions({ layout: AppLayout });

// Recibe los carros como prop
const props = defineProps({ carros: Array });

// Configuración personalizada de Notyf
const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    ripple: true,
    types: [
        {
            type: 'success',
            background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
            icon: { className: 'fas fa-check-circle', tagName: 'i', color: '#fff' }
        },
        {
            type: 'error',
            background: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
            icon: { className: 'fas fa-times-circle', tagName: 'i', color: '#fff' }
        },
    ],
});

// Estado reactivo para búsqueda y filtros
const busqueda = ref('');
const filtroAnio = ref('');
const vistaTabla = ref(true);

// Estado reactivo para modales
const mostrarModalDetalles = ref(false);
const carroSeleccionado = ref(null);
const mostrarModalEliminar = ref(false);
const idCarroAEliminar = ref(null);

// Computed properties para estadísticas y filtros
const carrosFiltrados = computed(() => {
    let resultado = props.carros;

    if (busqueda.value) {
        const termino = busqueda.value.toLowerCase();
        resultado = resultado.filter(carro =>
            carro.marca.toLowerCase().includes(termino) ||
            carro.modelo.toLowerCase().includes(termino) ||
            carro.color.toLowerCase().includes(termino)
        );
    }

    if (filtroAnio.value) {
        resultado = resultado.filter(carro => carro.anio.toString() === filtroAnio.value);
    }

    return resultado;
});

const aniosUnicos = computed(() => {
    const anios = [...new Set(props.carros.map(carro => carro.anio))];
    return anios.sort((a, b) => b - a);
});

const marcasUnicas = computed(() => {
    return [...new Set(props.carros.map(carro => carro.marca))];
});

const anioPromedio = computed(() => {
    if (props.carros.length === 0) return 0;
    const suma = props.carros.reduce((acc, carro) => acc + parseInt(carro.anio), 0);
    return Math.round(suma / props.carros.length);
});

const kilometrajePromedio = computed(() => {
    if (props.carros.length === 0) return 0;
    const suma = props.carros.reduce((acc, carro) => acc + parseInt(carro.kilometraje || 0), 0);
    return Math.round(suma / props.carros.length / 1000);
});

// Funciones de utilidad
const formatearKilometraje = (km) => {
    return new Intl.NumberFormat('es-ES').format(km);
};

const obtenerEstadoKilometraje = (km) => {
    if (km < 50000) return 'Bajo kilometraje';
    if (km < 100000) return 'Kilometraje medio';
    return 'Alto kilometraje';
};

const obtenerEstadoVehiculo = (anio) => {
    const anioActual = new Date().getFullYear();
    const antiguedad = anioActual - parseInt(anio);

    if (antiguedad <= 3) return 'Nuevo';
    if (antiguedad <= 7) return 'Semi-nuevo';
    if (antiguedad <= 15) return 'Usado';
    return 'Clásico';
};

const obtenerClaseEstado = (anio) => {
    const anioActual = new Date().getFullYear();
    const antiguedad = anioActual - parseInt(anio);

    if (antiguedad <= 3) return 'bg-green-100 text-green-800';
    if (antiguedad <= 7) return 'bg-blue-100 text-blue-800';
    if (antiguedad <= 15) return 'bg-yellow-100 text-yellow-800';
    return 'bg-purple-100 text-purple-800';
};

// Funciones de control
const toggleView = () => {
    vistaTabla.value = !vistaTabla.value;
};

const limpiarFiltros = () => {
    busqueda.value = '';
    filtroAnio.value = '';
};

// Funciones de modal de detalles
const abrirModal = (carro) => {
    carroSeleccionado.value = carro;
    mostrarModalDetalles.value = true;
};

const cerrarModalDetalles = () => {
    mostrarModalDetalles.value = false;
    carroSeleccionado.value = null;
};

// Funciones de modal de eliminación
const abrirModalEliminar = (id) => {
    idCarroAEliminar.value = id;
    mostrarModalEliminar.value = true;
};

const cerrarModalEliminar = () => {
    mostrarModalEliminar.value = false;
    idCarroAEliminar.value = null;
};

// Función para eliminar el carro
const eliminarCarro = async () => {
    try {
        await router.delete(route('carros.destroy', idCarroAEliminar.value), {
            onStart: () => {
                // Opcional: mostrar loading
            },
            onSuccess: () => {
                notyf.success('¡Carro eliminado exitosamente!');
                cerrarModalEliminar();
            },
            onError: (errors) => {
                console.error('Error al eliminar el carro:', errors);
                notyf.error('Error al eliminar el carro. Inténtalo de nuevo.');
                cerrarModalEliminar();
            },
        });
    } catch (error) {
        console.error('Error inesperado:', error);
        notyf.error('Ocurrió un error inesperado. Inténtalo más tarde.');
        cerrarModalEliminar();
    }
};
</script>

<style scoped>
/* Transiciones para modales */
.modal-enter-active, .modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

/* Animaciones adicionales */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}

/* Scrollbar personalizado para la tabla */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Hover effects mejorados */
.hover-scale:hover {
    transform: scale(1.02);
}

/* Estados de carga */
.loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>
