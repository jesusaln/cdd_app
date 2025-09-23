<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

defineOptions({ layout: AppLayout })

// Notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false },
    { type: 'warning', background: '#f59e0b', icon: false }
  ]
})

const page = usePage()
onMounted(() => {
  const flash = page.props.flash
  if (flash?.success) notyf.success(flash.success)
  if (flash?.error) notyf.error(flash.error)
})

// Props
const props = defineProps({
  herramientas: { type: Array, default: () => [] },
  tecnicos: { type: Array, default: () => [] }
})

// Estado del formulario
const form = ref({
  tecnico_id: '',
  herramientas_ids: [],
  proyecto_trabajo: '',
  fecha_devolucion_programada: '',
  observaciones_asignacion: '',
  requiere_autorizacion: false
})

// Estado UI
const searchHerramientas = ref('')
const categoriaFilter = ref('')
const showPreview = ref(false)

// Computed
const herramientasFiltradas = computed(() => {
  let filtered = props.herramientas

  if (searchHerramientas.value) {
    const search = searchHerramientas.value.toLowerCase()
    filtered = filtered.filter(h =>
      h.nombre.toLowerCase().includes(search) ||
      h.numero_serie.toLowerCase().includes(search)
    )
  }

  if (categoriaFilter.value) {
    filtered = filtered.filter(h => h.categoria_id == categoriaFilter.value)
  }

  return filtered
})

const herramientasSeleccionadas = computed(() => {
  return props.herramientas.filter(h => form.value.herramientas_ids.includes(h.id))
})

const valorTotalSeleccionadas = computed(() => {
  return herramientasSeleccionadas.value.reduce((total, h) => total + (h.costo_reemplazo || 0), 0)
})

const tecnicoSeleccionado = computed(() => {
  return props.tecnicos.find(t => t.id == form.value.tecnico_id)
})

const categorias = computed(() => {
  const cats = new Map()
  props.herramientas.forEach(h => {
    if (h.categoria_herramienta) {
      cats.set(h.categoria_herramienta.id, h.categoria_herramienta.nombre)
    }
  })
  return Array.from(cats.entries()).map(([id, nombre]) => ({ id, nombre }))
})

const isFormValid = computed(() => {
  return form.value.tecnico_id && form.value.herramientas_ids.length > 0
})

// Métodos
const toggleHerramienta = (herramientaId) => {
  const index = form.value.herramientas_ids.indexOf(herramientaId)
  if (index > -1) {
    form.value.herramientas_ids.splice(index, 1)
  } else {
    form.value.herramientas_ids.push(herramientaId)
  }
}

const seleccionarTodas = () => {
  form.value.herramientas_ids = herramientasFiltradas.value.map(h => h.id)
}

const deseleccionarTodas = () => {
  form.value.herramientas_ids = []
}

const submitForm = () => {
  if (!isFormValid.value) {
    notyf.error('Por favor complete todos los campos requeridos')
    return
  }

  router.post(route('herramientas.asignaciones-masivas.store'), form.value, {
    onSuccess: () => {
      notyf.success('Asignación masiva creada correctamente')
    },
    onError: (errors) => {
      console.error('Errores:', errors)
      const firstError = Object.values(errors)[0]
      notyf.error(Array.isArray(firstError) ? firstError[0] : firstError)
    }
  })
}

const cancel = () => {
  router.visit(route('herramientas.asignaciones-masivas.index'))
}

const formatCurrency = (num) => new Intl.NumberFormat('es-MX', {
  style: 'currency',
  currency: 'MXN'
}).format(num)

const formatNumber = (num) => new Intl.NumberFormat('es-ES').format(num)
</script>

<template>
  <Head title="Nueva Asignación Masiva de Herramientas" />

  <div class="asignacion-masiva-create min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <Link
              :href="route('herramientas.asignaciones-masivas.index')"
              class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>Volver</span>
            </Link>
            <div class="h-6 w-px bg-gray-300"></div>
            <h1 class="text-2xl font-bold text-slate-900">Nueva Asignación Masiva</h1>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario Principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información Básica -->
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información de la Asignación</h2>

            <div class="space-y-4">
              <!-- Técnico -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Técnico Receptor *
                </label>
                <select
                  v-model="form.tecnico_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="">Seleccionar técnico...</option>
                  <option
                    v-for="tecnico in tecnicos"
                    :key="tecnico.id"
                    :value="tecnico.id"
                  >
                    {{ tecnico.nombre }} {{ tecnico.apellido }}
                  </option>
                </select>
              </div>

              <!-- Proyecto -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Proyecto o Trabajo
                </label>
                <input
                  v-model="form.proyecto_trabajo"
                  type="text"
                  placeholder="Nombre del proyecto o trabajo..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <!-- Fecha de devolución programada -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fecha de Devolución Programada
                </label>
                <input
                  v-model="form.fecha_devolucion_programada"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <!-- Observaciones -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Observaciones de Asignación
                </label>
                <textarea
                  v-model="form.observaciones_asignacion"
                  rows="3"
                  placeholder="Observaciones sobre la asignación..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                ></textarea>
              </div>

              <!-- Requiere autorización -->
              <div class="flex items-center">
                <input
                  v-model="form.requiere_autorizacion"
                  type="checkbox"
                  id="requiere_autorizacion"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="requiere_autorizacion" class="ml-2 block text-sm text-gray-700">
                  Requiere autorización antes de activar
                </label>
              </div>
            </div>
          </div>

          <!-- Selección de Herramientas -->
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Seleccionar Herramientas</h2>
              <div class="flex gap-2">
                <button
                  @click="seleccionarTodas"
                  class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-md hover:bg-blue-200 transition-colors"
                >
                  Seleccionar Todas
                </button>
                <button
                  @click="deseleccionarTodas"
                  class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-md hover:bg-gray-200 transition-colors"
                >
                  Deseleccionar Todas
                </button>
              </div>
            </div>

            <!-- Filtros de herramientas -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
              <div class="flex-1">
                <input
                  v-model="searchHerramientas"
                  type="text"
                  placeholder="Buscar herramientas..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <select
                v-model="categoriaFilter"
                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todas las categorías</option>
                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                  {{ categoria.nombre }}
                </option>
              </select>
            </div>

            <!-- Lista de herramientas -->
            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
              <div class="space-y-1 p-2">
                <div
                  v-for="herramienta in herramientasFiltradas"
                  :key="herramienta.id"
                  class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                  @click="toggleHerramienta(herramienta.id)"
                >
                  <input
                    type="checkbox"
                    :checked="form.herramientas_ids.includes(herramienta.id)"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    @click.stop="toggleHerramienta(herramienta.id)"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900">{{ herramienta.nombre }}</div>
                    <div class="text-xs text-gray-500">{{ herramienta.numero_serie }}</div>
                  </div>
                  <div class="text-right">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                      {{ herramienta.categoria_herramienta?.nombre || 'Sin categoría' }}
                    </span>
                    <div v-if="herramienta.costo_reemplazo" class="text-xs text-gray-500 mt-1">
                      {{ formatCurrency(herramienta.costo_reemplazo) }}
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="herramientasFiltradas.length === 0" class="p-8 text-center">
                <div class="text-gray-500">No hay herramientas disponibles</div>
              </div>
            </div>

            <div v-if="form.herramientas_ids.length > 0" class="mt-4 p-3 bg-blue-50 rounded-lg">
              <div class="text-sm text-blue-800">
                <strong>{{ form.herramientas_ids.length }}</strong> herramientas seleccionadas
                <span v-if="valorTotalSeleccionadas > 0" class="ml-2">
                  (Valor total: {{ formatCurrency(valorTotalSeleccionadas) }})
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel Lateral - Resumen -->
        <div class="space-y-6">
          <!-- Resumen de la Asignación -->
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen</h3>

            <div class="space-y-4">
              <div v-if="tecnicoSeleccionado" class="p-3 bg-blue-50 rounded-lg">
                <div class="text-sm font-medium text-blue-900">{{ tecnicoSeleccionado.nombre }} {{ tecnicoSeleccionado.apellido }}</div>
                <div class="text-xs text-blue-700">Técnico receptor</div>
              </div>

              <div v-if="form.proyecto_trabajo" class="p-3 bg-purple-50 rounded-lg">
                <div class="text-sm font-medium text-purple-900">{{ form.proyecto_trabajo }}</div>
                <div class="text-xs text-purple-700">Proyecto</div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">{{ form.herramientas_ids.length }}</div>
                  <div class="text-xs text-gray-600">Herramientas</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                  <div class="text-lg font-bold text-green-600">{{ formatCurrency(valorTotalSeleccionadas) }}</div>
                  <div class="text-xs text-gray-600">Valor Total</div>
                </div>
              </div>

              <div v-if="form.fecha_devolucion_programada" class="p-3 bg-yellow-50 rounded-lg">
                <div class="text-sm font-medium text-yellow-900">
                  {{ new Date(form.fecha_devolucion_programada).toLocaleDateString('es-MX') }}
                </div>
                <div class="text-xs text-yellow-700">Devolución programada</div>
              </div>

              <div v-if="form.requiere_autorizacion" class="p-3 bg-orange-50 rounded-lg border border-orange-200">
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  <span class="text-sm font-medium text-orange-800">Requiere Autorización</span>
                </div>
                <div class="text-xs text-orange-700 mt-1">La asignación quedará pendiente hasta ser autorizada</div>
              </div>
            </div>
          </div>

          <!-- Herramientas Seleccionadas Preview -->
          <div v-if="form.herramientas_ids.length > 0" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Herramientas Seleccionadas</h3>

            <div class="max-h-64 overflow-y-auto space-y-2">
              <div
                v-for="herramienta in herramientasSeleccionadas.slice(0, 10)"
                :key="herramienta.id"
                class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg"
              >
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900 truncate">{{ herramienta.nombre }}</div>
                  <div class="text-xs text-gray-500 truncate">{{ herramienta.numero_serie }}</div>
                </div>
                <button
                  @click="toggleHerramienta(herramienta.id)"
                  class="w-6 h-6 bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition-colors"
                  title="Remover"
                >
                  <svg class="w-3 h-3 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="herramientasSeleccionadas.length > 10" class="text-xs text-gray-500 mt-2 text-center">
              +{{ herramientasSeleccionadas.length - 10 }} más...
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex justify-end gap-3">
              <button
                type="button"
                @click="cancel"
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
              >
                Cancelar
              </button>
              <button
                @click="submitForm"
                :disabled="!isFormValid"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
              >
                {{ form.requiere_autorizacion ? 'Crear y Enviar para Autorización' : 'Crear y Activar Asignación' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Panel de Ayuda -->
        <div class="space-y-6">
          <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">¿Cómo funciona?</h3>
            <div class="space-y-3 text-sm text-blue-800">
              <div class="flex items-start gap-2">
                <div class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <span class="text-xs font-bold text-blue-900">1</span>
                </div>
                <div>
                  <div class="font-medium">Selecciona el técnico</div>
                  <div class="text-blue-700">Elige quién recibirá las herramientas</div>
                </div>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <span class="text-xs font-bold text-blue-900">2</span>
                </div>
                <div>
                  <div class="font-medium">Selecciona herramientas</div>
                  <div class="text-blue-700">Marca las herramientas que quieres asignar</div>
                </div>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <span class="text-xs font-bold text-blue-900">3</span>
                </div>
                <div>
                  <div class="font-medium">Configura la asignación</div>
                  <div class="text-blue-700">Agrega proyecto, fechas y observaciones</div>
                </div>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <span class="text-xs font-bold text-blue-900">4</span>
                </div>
                <div>
                  <div class="font-medium">Crear asignación</div>
                  <div class="text-blue-700">Se activará automáticamente o quedará pendiente de autorización</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-yellow-900 mb-3">Importante</h3>
            <div class="space-y-2 text-sm text-yellow-800">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Solo se pueden asignar herramientas disponibles</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Se generará un código único para la asignación</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Se registrará en el historial de cada herramienta</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.asignacion-masiva-create {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
