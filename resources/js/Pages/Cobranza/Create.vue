<!-- /resources/js/Pages/Cobranza/Create.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
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

// Props
const props = defineProps({
  rentas: { type: Array, default: () => [] }
})

// Formulario
const form = useForm({
  renta_id: '',
  fecha_cobro: new Date().toISOString().split('T')[0],
  monto_cobrado: '',
  concepto: 'mensualidad',
  notas: ''
})

// Estado
const saving = ref(false)

// Computed
const rentasActivas = computed(() => {
  return props.rentas.filter(renta => renta.estado === 'activo')
})

const rentaSeleccionada = computed(() => {
  return props.rentas.find(r => r.id == form.renta_id)
})

// Métodos
const submit = () => {
  saving.value = true

  form.post(route('cobranza.store'), {
    onSuccess: () => {
      notyf.success('Cobranza creada exitosamente')
      router.visit(route('cobranza.index'))
    },
    onError: (errors) => {
      notyf.error('Error al crear la cobranza')
      saving.value = false
    },
    onFinish: () => {
      saving.value = false
    }
  })
}

const actualizarMonto = () => {
  if (rentaSeleccionada.value && form.concepto === 'mensualidad') {
    form.monto_cobrado = rentaSeleccionada.value.monto_mensual || ''
  }
}

const cancelar = () => {
  router.visit(route('cobranza.index'))
}
</script>

<template>
  <Head title="Crear Cobranza" />

  <div class="cobranza-create min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex items-center gap-3">
          <button
            @click="cancelar"
            class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <div>
            <h1 class="text-2xl font-bold text-slate-900">Crear Nueva Cobranza</h1>
            <p class="text-slate-600 mt-1">Registra un nuevo cobro para una renta activa</p>
          </div>
        </div>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Renta -->
          <div class="md:col-span-2">
            <label for="renta_id" class="block text-sm font-medium text-gray-700 mb-2">
              Renta *
            </label>
            <select
              v-model="form.renta_id"
              @change="actualizarMonto"
              id="renta_id"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
              <option value="">Selecciona una renta</option>
              <option
                v-for="renta in rentasActivas"
                :key="renta.id"
                :value="renta.id"
              >
                {{ renta.numero_contrato }} - {{ renta.cliente?.nombre_razon_social }} (${{ formatNumber(renta.monto_mensual) }}/mes)
              </option>
            </select>
            <p v-if="form.errors.renta_id" class="mt-1 text-sm text-red-600">
              {{ form.errors.renta_id }}
            </p>
          </div>

          <!-- Fecha de Cobro -->
          <div>
            <label for="fecha_cobro" class="block text-sm font-medium text-gray-700 mb-2">
              Fecha de Cobro *
            </label>
            <input
              v-model="form.fecha_cobro"
              id="fecha_cobro"
              type="date"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            />
            <p v-if="form.errors.fecha_cobro" class="mt-1 text-sm text-red-600">
              {{ form.errors.fecha_cobro }}
            </p>
          </div>

          <!-- Concepto -->
          <div>
            <label for="concepto" class="block text-sm font-medium text-gray-700 mb-2">
              Concepto *
            </label>
            <select
              v-model="form.concepto"
              @change="actualizarMonto"
              id="concepto"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
              <option value="mensualidad">Mensualidad</option>
              <option value="deposito_garantia">Depósito de Garantía</option>
              <option value="mora">Mora</option>
              <option value="reparacion">Reparación</option>
              <option value="otro">Otro</option>
            </select>
            <p v-if="form.errors.concepto" class="mt-1 text-sm text-red-600">
              {{ form.errors.concepto }}
            </p>
          </div>

          <!-- Monto -->
          <div>
            <label for="monto_cobrado" class="block text-sm font-medium text-gray-700 mb-2">
              Monto a Cobrar *
            </label>
            <div class="relative">
              <span class="absolute left-3 top-3 text-gray-500">$</span>
              <input
                v-model="form.monto_cobrado"
                id="monto_cobrado"
                type="number"
                step="0.01"
                min="0"
                class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
                required
              />
            </div>
            <p v-if="form.errors.monto_cobrado" class="mt-1 text-sm text-red-600">
              {{ form.errors.monto_cobrado }}
            </p>
          </div>

          <!-- Información de la renta seleccionada -->
          <div v-if="rentaSeleccionada" class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-900 mb-2">Información de la Renta</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <span class="text-blue-700 font-medium">Cliente:</span>
                <span class="text-blue-900 ml-2">{{ rentaSeleccionada.cliente?.nombre_razon_social }}</span>
              </div>
              <div>
                <span class="text-blue-700 font-medium">Monto Mensual:</span>
                <span class="text-blue-900 ml-2">${{ formatNumber(rentaSeleccionada.monto_mensual) }}</span>
              </div>
              <div>
                <span class="text-blue-700 font-medium">Estado:</span>
                <span class="text-blue-900 ml-2 capitalize">{{ rentaSeleccionada.estado }}</span>
              </div>
            </div>
          </div>

          <!-- Notas -->
          <div class="md:col-span-2">
            <label for="notas" class="block text-sm font-medium text-gray-700 mb-2">
              Notas
            </label>
            <textarea
              v-model="form.notas"
              id="notas"
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
              placeholder="Observaciones adicionales sobre este cobro..."
            ></textarea>
            <p v-if="form.errors.notas" class="mt-1 text-sm text-red-600">
              {{ form.errors.notas }}
            </p>
          </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="cancelar"
            class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-medium"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
          >
            {{ saving ? 'Creando...' : 'Crear Cobranza' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
const formatNumber = (num) => {
  if (!num) return '0'
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}
</script>

<style scoped>
.cobranza-create {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
