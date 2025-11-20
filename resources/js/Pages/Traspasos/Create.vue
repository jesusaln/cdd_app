<!-- /resources/js/Pages/Traspasos/Create.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
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
const props = defineProps({
  productos: { type: Array, default: () => [] },
  almacenes: { type: Array, default: () => [] },
  inventarios: { type: Array, default: () => [] }
})

// Form data
const form = ref({
  producto_id: '',
  almacen_origen_id: '',
  almacen_destino_id: '',
  cantidad: 1,
  series: []
})

// Estados
const loading = ref(false)
const showNoStockModal = ref(false)
const suggestedAlmacen = ref(null)
const seriesDisponibles = ref([])
const seriesSeleccionadas = ref([])

// Computed
const productoSeleccionado = computed(() => props.productos.find(p => p.id == form.value.producto_id))
const requiereSerie = computed(() => !!productoSeleccionado.value?.requiere_serie)

const productosFiltrados = computed(() => {
  if (!form.value.almacen_origen_id) return props.productos
  return props.productos.filter(producto => {
    // Incluir el producto seleccionado aunque no tenga stock
    if (producto.id == form.value.producto_id) return true
    // Filtrar productos que tienen stock en el almacén origen
    return props.inventarios.some(inv =>
      inv.producto_id == producto.id &&
      inv.almacen_id == form.value.almacen_origen_id &&
      inv.cantidad > 0
    )
  })
})

const almacenesOrigenDisponibles = computed(() => {
  if (!form.value.producto_id) return props.almacenes
  return props.almacenes.filter(almacen => {
    return props.inventarios.some(inv =>
      inv.producto_id == form.value.producto_id &&
      inv.almacen_id == almacen.id &&
      inv.cantidad > 0
    )
  })
})

const almacenesDestino = computed(() => {
  return props.almacenes.filter(almacen => almacen.id != form.value.almacen_origen_id)
})

const stockOrigen = computed(() => {
  if (!form.value.producto_id || !form.value.almacen_origen_id) return 0
  const inventario = props.inventarios.find(inv =>
    inv.producto_id == form.value.producto_id &&
    inv.almacen_id == form.value.almacen_origen_id
  )
  return inventario ? inventario.cantidad : 0
})

const stockDestino = computed(() => {
  if (!form.value.producto_id || !form.value.almacen_destino_id) return 0
  const inventario = props.inventarios.find(inv =>
    inv.producto_id == form.value.producto_id &&
    inv.almacen_id == form.value.almacen_destino_id
  )
  return inventario ? inventario.cantidad : 0
})

const stockDestinoFinal = computed(() => {
  return stockDestino.value + form.value.cantidad
})

watch(() => seriesSeleccionadas.value.length, (val) => {
  if (requiereSerie.value) {
    form.value.cantidad = val
  }
})

// Watchers
watch(() => form.value.almacen_origen_id, () => {
  if (form.value.almacen_origen_id && form.value.producto_id) {
    const stock = stockOrigen.value
    if (stock === 0) {
      // Buscar almacén con más stock
      const almacenesConStock = props.inventarios
        .filter(inv => inv.producto_id == form.value.producto_id && inv.cantidad > 0)
        .sort((a, b) => b.cantidad - a.cantidad)

      if (almacenesConStock.length > 0) {
        suggestedAlmacen.value = props.almacenes.find(a => a.id == almacenesConStock[0].almacen_id)
      }

      showNoStockModal.value = true
      form.value.almacen_origen_id = ''
      return
    }
  }

  // Reset almacen destino si es el mismo que origen
  if (form.value.almacen_destino_id === form.value.almacen_origen_id) {
    form.value.almacen_destino_id = ''
  }
})

watch(() => form.value.almacen_destino_id, () => {
  // Reset si es el mismo que origen
  if (form.value.almacen_destino_id === form.value.almacen_origen_id) {
    form.value.almacen_destino_id = ''
  }
})

watch(
  () => [form.value.producto_id, form.value.almacen_origen_id],
  () => {
    seriesSeleccionadas.value = []
    form.value.series = []
    if (requiereSerie.value) {
      cargarSeriesDisponibles()
    } else {
      seriesDisponibles.value = []
    }
  }
)

const submit = () => {
  if (form.value.cantidad > stockOrigen.value) {
    notyf.error('La cantidad excede el stock disponible en el almacen origen')
    return
  }

  if (requiereSerie.value) {
    if (!seriesSeleccionadas.value.length) {
      notyf.error('Selecciona las series a traspasar')
      return
    }
    form.value.series = [...seriesSeleccionadas.value]
    form.value.cantidad = seriesSeleccionadas.value.length
  }

  loading.value = true

  router.post(route('traspasos.store'), form.value, {
    onSuccess: () => {
      notyf.success('Traspaso realizado correctamente')
      router.visit(route('traspasos.index'))
    },
    onError: (errors) => {
      console.error('Errores de validación:', errors)
      notyf.error('Error al realizar el traspaso')
    },
    onFinish: () => {
      loading.value = false
    }
  })
}

const cancel = () => {
  router.visit(route('traspasos.index'))
}

const selectSuggestedAlmacen = () => {
  if (suggestedAlmacen.value) {
    form.value.almacen_origen_id = suggestedAlmacen.value.id
  }
  showNoStockModal.value = false
  suggestedAlmacen.value = null
}

const closeNoStockModal = () => {
  showNoStockModal.value = false
  suggestedAlmacen.value = null
}

const cargarSeriesDisponibles = async () => {
  if (!form.value.producto_id || !form.value.almacen_origen_id || !requiereSerie.value) {
    seriesDisponibles.value = []
    return
  }

  try {
    const url = route('productos.series', form.value.producto_id) + `?almacen_id=${form.value.almacen_origen_id}`
    const res = await fetch(url, { headers: { Accept: 'application/json' } })
    if (!res.ok) throw new Error(`Error ${res.status}`)
    const data = await res.json()
    seriesDisponibles.value = data?.series?.en_stock || []
  } catch (error) {
    console.error('Error cargando series disponibles', error)
    notyf.error('No se pudieron cargar las series del almacén origen')
    seriesDisponibles.value = []
  }
}
</script>

<template>
  <Head title="Nuevo Traspaso" />

  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Nuevo Traspaso</h1>
            <p class="text-gray-600 mt-1">Transfiere productos entre almacenes</p>
          </div>
          <button
            @click="cancel"
            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Cancelar
          </button>
        </div>
      </div>

      <!-- Formulario -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Producto -->
          <div>
            <label for="producto_id" class="block text-sm font-medium text-gray-700 mb-2">
              Producto *
            </label>
            <select
              id="producto_id"
              v-model="form.producto_id"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
            >
              <option value="">Seleccionar producto</option>
              <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                {{ producto.nombre }}
              </option>
            </select>
          </div>

          <!-- Almacén Origen -->
          <div>
            <label for="almacen_origen_id" class="block text-sm font-medium text-gray-700 mb-2">
              Almacén Origen *
            </label>
            <select
              id="almacen_origen_id"
              v-model="form.almacen_origen_id"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
            >
              <option value="">Seleccionar almacén origen</option>
              <option v-for="almacen in almacenesOrigenDisponibles" :key="almacen.id" :value="almacen.id">
                {{ almacen.nombre }}
              </option>
            </select>
            <p v-if="form.almacen_origen_id && form.producto_id" class="mt-1 text-sm text-gray-600">
              Stock actual en origen: <strong>{{ stockOrigen }}</strong>
            </p>
          </div>

          <!-- Almacén Destino -->
          <div>
            <label for="almacen_destino_id" class="block text-sm font-medium text-gray-700 mb-2">
              Almacén Destino *
            </label>
            <select
              id="almacen_destino_id"
              v-model="form.almacen_destino_id"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
            >
              <option value="">Seleccionar almacén destino</option>
              <option v-for="almacen in almacenesDestino" :key="almacen.id" :value="almacen.id">
                {{ almacen.nombre }}
              </option>
            </select>
            <div v-if="form.almacen_destino_id && form.producto_id" class="mt-1 text-sm text-gray-600">
              <p>Stock actual en destino: <strong>{{ stockDestino }}</strong></p>
              <p>Stock después del traspaso: <strong>{{ stockDestinoFinal }}</strong></p>
            </div>
          </div>

          <!-- Cantidad -->
          <div>
            <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-2">
              Cantidad *
            </label>
            <input
              id="cantidad"
              v-model.number="form.cantidad"
              type="number"
              min="1"
              :max="stockOrigen"
              :disabled="requiereSerie"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
              placeholder="Cantidad a transferir"
            />
            <p v-if="form.producto_id && form.almacen_origen_id" class="mt-1 text-sm text-gray-500">
              Stock disponible en origen: <strong>{{ stockOrigen }}</strong>
            </p>
            <p v-if="requiereSerie" class="mt-1 text-sm text-blue-600">
              La cantidad se define por las series seleccionadas ({{ seriesSeleccionadas.length }}).
            </p>
          </div>

          <div v-if="requiereSerie" class="border border-blue-200 bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
              <div>
                <p class="font-medium text-blue-800">Series disponibles en el almacén origen</p>
                <p class="text-sm text-blue-700">Selecciona las series que deseas traspasar</p>
              </div>
              <button type="button" @click="cargarSeriesDisponibles" class="text-sm text-blue-700 hover:underline">
                Recargar
              </button>
            </div>
            <div v-if="!seriesDisponibles.length" class="text-sm text-blue-700">
              No hay series en stock en este almacén.
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto">
              <label
                v-for="serie in seriesDisponibles"
                :key="serie.id"
                class="flex items-center gap-3 p-2 bg-white border border-blue-200 rounded-md hover:bg-blue-100"
              >
                <input
                  type="checkbox"
                  :value="serie.id"
                  v-model="seriesSeleccionadas"
                  class="text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <div class="text-sm text-gray-800">
                  <span class="font-semibold">Serie:</span> {{ serie.numero_serie }}
                </div>
              </label>
            </div>
            <p class="mt-3 text-sm text-blue-800 font-medium">
              Seleccionadas: {{ seriesSeleccionadas.length }}
            </p>
          </div>

          <!-- Información -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                  Información importante
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                  <ul class="list-disc pl-5 space-y-1">
                    <li>Verifica que el producto tenga stock suficiente en el almacén origen</li>
                    <li>El almacén destino debe ser diferente al origen</li>
                    <li>Esta acción no se puede deshacer</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="cancel"
              :disabled="loading"
              class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:ring-4 focus:ring-gray-300 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loading || !form.producto_id || !form.almacen_origen_id || !form.almacen_destino_id || form.cantidad < 1"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
              </svg>
              {{ loading ? 'Procesando...' : 'Realizar Traspaso' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de No Stock -->
    <div v-if="showNoStockModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <h3 class="ml-3 text-lg font-medium text-gray-900">Sin Stock Disponible</h3>
        </div>
        <div class="mb-4">
          <p class="text-sm text-gray-600">
            El almacén seleccionado no tiene stock del producto elegido.
          </p>
          <p v-if="suggestedAlmacen" class="text-sm text-gray-600 mt-2">
            Sugerencia: El almacén <strong>{{ suggestedAlmacen.nombre }}</strong> tiene el mayor stock disponible.
          </p>
        </div>
        <div class="flex justify-end gap-3">
          <button @click="closeNoStockModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
            Elegir Otro
          </button>
          <button v-if="suggestedAlmacen" @click="selectSuggestedAlmacen" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Usar Sugerido
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>

