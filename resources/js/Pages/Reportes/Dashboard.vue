<template>
    <Head title="Centro de Reportes" />

    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">Centro de Reportes</h1>
                <p class="text-sm text-gray-600 mt-1">Accede a todos los reportes del sistema organizados por categorías</p>

            </div>

            <!-- Contenido -->
            <div class="p-6">
                <!-- Todos los reportes en una cuadrícula -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- Ventas -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('ventas')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Ventas Generales</h3>
                                <p class="text-sm text-gray-600">Análisis completo de todas las ventas realizadas</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=ventas"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Ventas Pendientes -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('ventas')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Ventas Pendientes</h3>
                                <p class="text-sm text-gray-600">Ventas que requieren atención</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=ventas"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Productos Más Vendidos -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('ventas')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Productos Más Vendidos</h3>
                                <p class="text-sm text-gray-600">Ranking de productos por volumen de ventas</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=productos"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Corte de Pagos -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-green-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('pagos')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Corte de Pagos</h3>
                                <p class="text-sm text-gray-600">Resumen diario/semanal de ingresos y pagos</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=corte"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Cobranzas -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-green-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('pagos')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Cobranzas</h3>
                                <p class="text-sm text-gray-600">Estado de pagos pendientes y realizados</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=cobranzas"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Clientes Activos -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-purple-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('clientes')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Clientes Activos</h3>
                                <p class="text-sm text-gray-600">Clientes con actividad reciente</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=clientes"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Clientes Deudores -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-purple-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('clientes')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Clientes Deudores</h3>
                                <p class="text-sm text-gray-600">Clientes con pagos pendientes</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=clientes"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Productos en Stock -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-orange-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('inventario')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Productos en Stock</h3>
                                <p class="text-sm text-gray-600">Estado actual del inventario</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=inventario"
                            class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Productos Bajos -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-orange-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('inventario')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Productos Bajos</h3>
                                <p class="text-sm text-gray-600">Productos por debajo del stock mínimo</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=inventario"
                            class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Movimientos Inventario -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-orange-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('inventario')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Movimientos Inventario</h3>
                                <p class="text-sm text-gray-600">Historial de entradas y salidas de productos</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=movimientos"
                            class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Servicios Más Vendidos -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-indigo-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('servicios')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Servicios Más Vendidos</h3>
                                <p class="text-sm text-gray-600">Servicios más demandados</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=servicios"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Citas Programadas -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-indigo-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('servicios')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Citas Programadas</h3>
                                <p class="text-sm text-gray-600">Calendario de citas agendadas</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=citas"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Mantenimientos -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-indigo-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('servicios')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Mantenimientos</h3>
                                <p class="text-sm text-gray-600">Historial de trabajos de mantenimiento</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=mantenimientos"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Rentas Activas -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-teal-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('rentas')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Rentas Activas</h3>
                                <p class="text-sm text-gray-600">Equipos actualmente rentados</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=rentas"
                            class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-md hover:bg-teal-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Ganancias Generales -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-yellow-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('finanzas')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Ganancias Generales</h3>
                                <p class="text-sm text-gray-600">Análisis financiero completo</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=ganancias"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Compras -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-yellow-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('finanzas')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Compras</h3>
                                <p class="text-sm text-gray-600">Historial de compras realizadas</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=compras"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Proveedores -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-yellow-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('finanzas')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Proveedores</h3>
                                <p class="text-sm text-gray-600">Análisis de proveedores y compras</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=proveedores"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Técnicos -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-red-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('personal')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Técnicos</h3>
                                <p class="text-sm text-gray-600">Rendimiento y comisiones por técnico</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=personal"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Empleados -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-red-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('personal')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Empleados</h3>
                                <p class="text-sm text-gray-600">Información general del personal</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=personal"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Bitácora de Actividades -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-600 flex items-center justify-center">
                                <component :is="getCategoriaIcon('auditoria')" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Bitácora de Actividades</h3>
                                <p class="text-sm text-gray-600">Registro de todas las operaciones</p>
                            </div>
                        </div>
                        <Link
                            href="/reportes?tab=auditoria"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors w-full justify-center"
                        >
                            Ver Reporte
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

// Función para obtener el color de la categoría
const getCategoriaColor = (categoria) => {
    const colores = {
        ventas: 'bg-blue-600',
        pagos: 'bg-green-600',
        clientes: 'bg-purple-600',
        inventario: 'bg-orange-600',
        servicios: 'bg-indigo-600',
        rentas: 'bg-teal-600',
        finanzas: 'bg-yellow-600',
        personal: 'bg-red-600',
        auditoria: 'bg-gray-600',
    };
    return colores[categoria] || 'bg-gray-600';
};

// Función para obtener el ícono de la categoría
const getCategoriaIcon = (categoria) => {
    const iconos = {
        ventas: 'ShoppingCartIcon',
        pagos: 'CashIcon',
        clientes: 'UsersIcon',
        inventario: 'ArchiveIcon',
        servicios: 'WrenchIcon',
        rentas: 'HandIcon',
        finanzas: 'ChartBarIcon',
        personal: 'UserGroupIcon',
        auditoria: 'DocumentIcon',
    };
    return iconos[categoria] || 'DocumentIcon';
};

// Componentes de íconos (usando Heroicons)
const ShoppingCartIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13v8a2 2 0 002 2h10a2 2 0 002-2v-3"/></svg>`
};

const CashIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7l4 0m0 4l4 0"/></svg>`
};

const UsersIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>`
};

const ArchiveIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>`
};

const WrenchIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`
};

const HandIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6v2.5m0-2.5c1.5 0 3-1.5 3-3.5s-1.5-3.5-3-3.5-3 1.5-3 3.5 1.5 3.5 3 3.5z"/></svg>`
};

const ChartBarIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`
};

const UserGroupIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`
};

const DocumentIcon = {
    template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
