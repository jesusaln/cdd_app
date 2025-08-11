<template>
    <AppLayout title="Respaldo de Base de Datos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Respaldo de Base de Datos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Alertas mejoradas -->
                <Transition name="fade" appear>
                    <div v-if="$page.props.flash?.success"
                         class="bg-green-50 border-l-4 border-green-400 p-4 rounded-md shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ $page.props.flash.success }}</p>
                            </div>
                        </div>
                    </div>
                </Transition>

                <Transition name="fade" appear>
                    <div v-if="$page.props.flash?.error"
                         class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">{{ $page.props.flash.error }}</p>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Estadísticas del sistema -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-bold">{{ backups.length }}</span>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total de Respaldos</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ backups.length }} archivos</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Estado del Sistema</dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ mysqldump_available ? 'mysqldump' : 'PHP nativo' }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs">{{ totalSizeFormatted }}</span>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Espacio Total</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ totalSizeFormatted }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Creación de Respaldo -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Crear Nuevo Respaldo
                        </h3>

                        <form @submit.prevent="createBackup" class="space-y-4">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="lg:col-span-2">
                                    <label for="backupName" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre del Respaldo
                                    </label>
                                    <input
                                        id="backupName"
                                        v-model="backupForm.name"
                                        type="text"
                                        :placeholder="defaultBackupName"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-200" />
                                    <p class="mt-1 text-xs text-gray-500">Si se deja vacío, se usará: {{ defaultBackupName }}</p>
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input
                                            v-model="backupForm.compress"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                        <span class="ml-2 text-sm text-gray-600">Comprimir (ZIP)</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input
                                            v-model="backupForm.include_structure_only"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                        <span class="ml-2 text-sm text-gray-600">Solo estructura</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center text-sm">
                                    <div class="flex items-center">
                                        <div :class="mysqldump_available ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                             class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            <span v-if="mysqldump_available" class="mr-1">✓</span>
                                            <span v-else class="mr-1">⚠</span>
                                            {{ mysqldump_available ? 'mysqldump disponible' : 'Usando método PHP' }}
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="creating"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <LoadingSpinner v-if="creating" class="mr-2" />
                                    {{ creating ? 'Creando...' : 'Crear Respaldo' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Panel de Gestión -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                Gestión de Respaldos
                            </h3>

                            <div class="flex space-x-2">
                                <button
                                    @click="showCleanDialog = true"
                                    class="inline-flex items-center px-3 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Limpiar
                                </button>

                                <button
                                    @click="refreshBackups"
                                    :disabled="refreshing"
                                    class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-50 transition ease-in-out duration-150">
                                    <svg :class="{ 'animate-spin': refreshing }" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    {{ refreshing ? 'Actualizando...' : 'Actualizar' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Respaldos -->
                    <div v-if="backups.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay respaldos</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando tu primer respaldo de la base de datos.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <TransitionGroup name="list" tag="div">
                            <div v-for="backup in sortedBackups" :key="backup.path"
                                 class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ backup.name }}
                                                </p>
                                                <div class="flex items-center mt-1 space-x-4 text-xs text-gray-500">
                                                    <span class="flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        {{ formatDate(backup.created_at) }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                        {{ formatFileSize(backup.size) }}
                                                    </span>
                                                    <span v-if="backup.compressed" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        ZIP
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 ml-4">
                                        <button
                                            @click="downloadBackup(backup)"
                                            class="inline-flex items-center p-2 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                        </button>

                                        <button
                                            @click="confirmRestore(backup)"
                                            class="inline-flex items-center p-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </button>

                                        <button
                                            @click="confirmDelete(backup)"
                                            class="inline-flex items-center p-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </TransitionGroup>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Restauración -->
        <Modal v-if="restoreDialog.show" @close="restoreDialog.show = false">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Limpiar Respaldos Antiguos
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 mb-4">
                            Eliminar respaldos más antiguos que:
                        </p>
                        <select
                            v-model="cleanForm.daysOld"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="7">7 días</option>
                            <option value="15">15 días</option>
                            <option value="30">30 días</option>
                            <option value="60">60 días</option>
                            <option value="90">90 días</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-2">
                            Se eliminarán {{ oldBackupsCount }} respaldos aproximadamente.
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button
                    @click="executeClean"
                    :disabled="cleaning"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                    <LoadingSpinner v-if="cleaning" class="mr-2" />
                    {{ cleaning ? 'Limpiando...' : 'Limpiar' }}
                </button>
                <button
                    @click="showCleanDialog = false"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Componentes auxiliares
const LoadingSpinner = {
    template: `
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    `
}

const Modal = {
    emits: ['close'],
    template: `
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="$emit('close')">
            <div class="relative top-20 mx-auto p-5 border max-w-md shadow-lg rounded-md bg-white" @click.stop>
                <slot />
            </div>
        </div>
    `
}

const Transition = {
    name: 'fade',
    template: '<transition name="fade" appear><slot /></transition>'
}

const TransitionGroup = {
    name: 'list',
    tag: 'div',
    template: '<transition-group name="list" tag="div"><slot /></transition-group>'
}

// Props
const props = defineProps({
    backups: {
        type: Array,
        default: () => []
    },
    mysqldump_available: {
        type: Boolean,
        default: false
    }
})

// Estado reactivo
const creating = ref(false)
const restoring = ref(false)
const deleting = ref(false)
const cleaning = ref(false)
const refreshing = ref(false)
const showCleanDialog = ref(false)

// Formularios
const backupForm = reactive({
    name: '',
    compress: true,
    include_structure_only: false
})

const cleanForm = reactive({
    daysOld: 30
})

// Diálogos
const restoreDialog = reactive({
    show: false,
    backup: null
})

const deleteDialog = reactive({
    show: false,
    backup: null
})

// Computed properties
const defaultBackupName = computed(() => {
    const now = new Date()
    const timestamp = now.toISOString().slice(0, 19).replace(/[:-]/g, '').replace('T', '_')
    return `backup_${timestamp}`
})

const sortedBackups = computed(() => {
    return [...props.backups].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const totalSizeFormatted = computed(() => {
    const totalSize = props.backups.reduce((sum, backup) => sum + (backup.size || 0), 0)
    return formatFileSize(totalSize)
})

const oldBackupsCount = computed(() => {
    if (!cleanForm.daysOld) return 0
    const cutoffDate = new Date()
    cutoffDate.setDate(cutoffDate.getDate() - parseInt(cleanForm.daysOld))

    return props.backups.filter(backup =>
        new Date(backup.created_at) < cutoffDate
    ).length
})

// Métodos principales
const createBackup = async () => {
    if (creating.value) return

    creating.value = true

    try {
        await router.post(route('backup.create'), {
            name: backupForm.name || defaultBackupName.value,
            compress: backupForm.compress,
            include_structure_only: backupForm.include_structure_only
        }, {
            onSuccess: () => {
                // Resetear formulario
                backupForm.name = ''
                backupForm.compress = true
                backupForm.include_structure_only = false

                // Mostrar notificación de éxito
                showNotification('Respaldo creado exitosamente', 'success')
            },
            onError: (errors) => {
                console.error('Error creating backup:', errors)
                showNotification('Error al crear el respaldo', 'error')
            },
            onFinish: () => {
                creating.value = false
            }
        })
    } catch (error) {
        console.error('Unexpected error:', error)
        creating.value = false
    }
}

const confirmRestore = (backup) => {
    restoreDialog.backup = backup
    restoreDialog.show = true
}

const executeRestore = async () => {
    if (restoring.value || !restoreDialog.backup) return

    restoring.value = true

    try {
        await router.post(route('backup.restore', restoreDialog.backup.path), {}, {
            onSuccess: () => {
                restoreDialog.show = false
                restoreDialog.backup = null
                showNotification('Base de datos restaurada exitosamente', 'success')
            },
            onError: (errors) => {
                console.error('Error restoring backup:', errors)
                showNotification('Error al restaurar la base de datos', 'error')
            },
            onFinish: () => {
                restoring.value = false
            }
        })
    } catch (error) {
        console.error('Unexpected error:', error)
        restoring.value = false
    }
}

const confirmDelete = (backup) => {
    deleteDialog.backup = backup
    deleteDialog.show = true
}

const executeDelete = async () => {
    if (deleting.value || !deleteDialog.backup) return

    deleting.value = true

    try {
        await router.delete(route('backup.delete', deleteDialog.backup.path), {
            onSuccess: () => {
                deleteDialog.show = false
                deleteDialog.backup = null
                showNotification('Respaldo eliminado exitosamente', 'success')
            },
            onError: (errors) => {
                console.error('Error deleting backup:', errors)
                showNotification('Error al eliminar el respaldo', 'error')
            },
            onFinish: () => {
                deleting.value = false
            }
        })
    } catch (error) {
        console.error('Unexpected error:', error)
        deleting.value = false
    }
}

const executeClean = async () => {
    if (cleaning.value) return

    cleaning.value = true

    try {
        await router.post(route('backup.clean'), {
            days_old: cleanForm.daysOld
        }, {
            onSuccess: () => {
                showCleanDialog.value = false
                showNotification('Respaldos antiguos eliminados exitosamente', 'success')
            },
            onError: (errors) => {
                console.error('Error cleaning backups:', errors)
                showNotification('Error al limpiar los respaldos', 'error')
            },
            onFinish: () => {
                cleaning.value = false
            }
        })
    } catch (error) {
        console.error('Unexpected error:', error)
        cleaning.value = false
    }
}

const refreshBackups = async () => {
    if (refreshing.value) return

    refreshing.value = true

    try {
        await router.reload({
            only: ['backups'],
            onFinish: () => {
                refreshing.value = false
                showNotification('Lista de respaldos actualizada', 'success')
            }
        })
    } catch (error) {
        console.error('Error refreshing backups:', error)
        refreshing.value = false
    }
}

const downloadBackup = (backup) => {
    // Crear enlace temporal para descarga
    const link = document.createElement('a')
    link.href = route('backup.download', backup.path)
    link.download = backup.name
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    showNotification('Descarga iniciada', 'success')
}

// Métodos utilitarios
const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}

const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 B'

    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const showNotification = (message, type = 'info') => {
    // Aquí puedes integrar con tu sistema de notificaciones preferido
    // Por ejemplo, usando toast, sweetalert2, etc.
    console.log(`${type.toUpperCase()}: ${message}`)
}

// Lifecycle hooks
onMounted(() => {
    // Inicialización si es necesaria
    console.log('Database Backup Manager initialized')
})

// Manejo de errores global
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason)
    showNotification('Ha ocurrido un error inesperado', 'error')
})
</script>

<style scoped>
/* Animaciones */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.list-enter-active, .list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.list-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

.list-move {
    transition: transform 0.3s ease;
}

/* Animación de spin personalizada */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Hover effects */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

/* Focus styles mejorados */
.focus\:ring-2:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

/* Custom scrollbar para modal */
.modal-content::-webkit-scrollbar {
    width: 4px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Estados de loading */
.loading-overlay {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(2px);
}

/* Mejoras responsive */
@media (max-width: 640px) {
    .grid-cols-1 {
        gap: 1rem;
    }

    .space-x-2 > * + * {
        margin-left: 0.25rem;
    }
}
</style>
