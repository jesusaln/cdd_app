<template>
  <Head title="Registrar Actividad" />
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Registrar Actividad</h1>
            <p class="mt-2 text-sm text-gray-600">Captura detallada para tu bitácora. Los campos marcados con * son obligatorios.</p>
          </div>
          <Link :href="route('bitacora.index')" class="inline-flex items-center px-4 py-2 bg-white border rounded-lg text-sm font-medium hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver
          </Link>
        </div>
      </div>
      <!-- Errores globales -->
      <div v-if="hasAnyError" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
        <div class="font-semibold mb-1">Revisa los campos marcados:</div>
        <ul class="list-disc ml-5 text-sm space-y-0.5">
          <li v-for="(msg, key) in form.errors" :key="key">{{ msg }}</li>
        </ul>
      </div>
      <!-- Formulario -->
      <form @submit.prevent="submit('index')" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Título -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Título <span class="text-red-500">*</span></label>
            <input
              v-model.trim="form.titulo"
              type="text"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="inputError('titulo')"
              placeholder="Ej. Instalación minisplit sucursal Centro"
              maxlength="150"
            />
            <p v-if="form.errors.titulo" class="mt-1 text-sm text-red-600">{{ form.errors.titulo }}</p>
          </div>
          <!-- Empleado -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Empleado <span class="text-red-500">*</span></label>
            <select
              v-model="form.user_id"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="inputError('user_id')"
            >
              <option value="" disabled>Selecciona un empleado</option>
              <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
            <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
          </div>
          <!-- Cliente -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Cliente</label>
            <select
              v-model="form.cliente_id"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="inputError('cliente_id')"
            >
              <option :value="null">— Sin cliente —</option>
              <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre_razon_social }}</option>
            </select>
            <p v-if="form.errors.cliente_id" class="mt-1 text-sm text-red-600">{{ form.errors.cliente_id }}</p>
          </div>
          <!-- Tipo -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Tipo <span class="text-red-500">*</span></label>
            <select
              v-model="form.tipo"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent capitalize"
              :class="inputError('tipo')"
            >
              <option value="" disabled>Selecciona un tipo</option>
              <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
            </select>
            <p v-if="form.errors.tipo" class="mt-1 text-sm text-red-600">{{ form.errors.tipo }}</p>
          </div>
          <!-- Estado -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Estado <span class="text-red-500">*</span></label>
            <select
              v-model="form.estado"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent capitalize"
              :class="inputError('estado')"
            >
              <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
            </select>
            <p v-if="form.errors.estado" class="mt-1 text-sm text-red-600">{{ form.errors.estado }}</p>
          </div>
          <!-- Inicio -->
          <div>
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700">Inicio <span class="text-red-500">*</span></label>
              <div class="flex gap-2">
                <button type="button" @click="setNow('inicio_at')" class="text-xs px-2 py-1 border rounded hover:bg-gray-50">Ahora</button>
              </div>
            </div>
            <input
              v-model="form.inicio_at"
              type="datetime-local"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="inputError('inicio_at')"
            />
            <p v-if="form.errors.inicio_at" class="mt-1 text-sm text-red-600">{{ form.errors.inicio_at }}</p>
          </div>
          <!-- Fin -->
          <div>
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700">Fin</label>
              <div class="flex gap-2">
                <button type="button" @click="setNow('fin_at')" class="text-xs px-2 py-1 border rounded hover:bg-gray-50">Ahora</button>
                <button type="button" @click="sumarMinutos(30)" class="text-xs px-2 py-1 border rounded hover:bg-gray-50">+30m</button>
                <button type="button" @click="sumarMinutos(60)" class="text-xs px-2 py-1 border rounded hover:bg-gray-50">+1h</button>
                <button type="button" @click="sumarMinutos(120)" class="text-xs px-2 py-1 border rounded hover:bg-gray-50">+2h</button>
              </div>
            </div>
            <input
              v-model="form.fin_at"
              type="datetime-local"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="inputError('fin_at')"
            />
            <p v-if="form.errors.fin_at" class="mt-1 text-sm text-red-600">{{ form.errors.fin_at }}</p>
            <p v-if="duracionTexto" class="mt-1 text-xs text-gray-500">Duración: <span class="font-medium text-gray-700">{{ duracionTexto }}</span></p>
          </div>
          <!-- Ubicación -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Ubicación</label>
            <input
              v-model.trim="form.ubicacion"
              type="text"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="inputError('ubicacion')"
              placeholder="Ej. Sucursal Centro, Blvd. X, #123"
              maxlength="255"
            />
            <p v-if="form.errors.ubicacion" class="mt-1 text-sm text-red-600">{{ form.errors.ubicacion }}</p>
          </div>
          <!-- Descripción -->
          <div class="md:col-span-2">
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700">Descripción</label>
              <span class="text-xs text-gray-400">{{ (form.descripcion || '').length }} / 5000</span>
            </div>
            <textarea
              v-model.trim="form.descripcion"
              rows="5"
              class="mt-1 w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="inputError('descripcion')"
              placeholder="Detalle las actividades realizadas, materiales usados, pendientes, etc."
              maxlength="5000"
            ></textarea>
            <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
          </div>
        </div>
        <!-- Actions -->
        <div class="mt-8 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
          <div class="text-xs text-gray-500">Se guardará el usuario que registra y la hora exacta del envío.</div>
          <div class="flex gap-3">
            <button type="button" @click="submit('stay')" :disabled="form.processing" class="px-5 py-2.5 rounded-lg border bg-white hover:bg-gray-50 disabled:opacity-50">Guardar y capturar otra</button>
            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
              <span v-if="!form.processing">Guardar</span>
              <span v-else class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Guardando…
              </span>
            </button>
          </div>
        </div>
      </form>
      <!-- Sugerencias de captura -->
      <div class="mt-6 text-xs text-gray-500">
        <p>Tip: si la actividad aún está en curso, deja el <span class="font-medium">Fin</span> vacío y marca el estado como <span class="font-medium">en_proceso</span>.</p>
      </div>
    </div>
  </div>
</template>
<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
const props = defineProps({
  usuarios: { type: Array, default: () => [] },
  clientes: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => ['pendiente', 'en_proceso', 'completado', 'cancelado'] },
})
const page = usePage()
const currentUserId = page?.props?.auth?.user?.id ?? ''
const form = useForm({
  titulo: '',
  user_id: currentUserId || '',
  cliente_id: null,
  tipo: props.tipos[0] || '',
  estado: props.estados.includes('en_proceso') ? 'en_proceso' : (props.estados[0] || ''),
  inicio_at: '',
  fin_at: '',
  ubicacion: '',
  descripcion: '',
})
const hasAnyError = computed(() => Object.keys(form.errors || {}).length > 0)
function inputError(field) {
  return form.errors?.[field] ? 'border-red-300 focus:ring-red-500' : 'border-gray-300'
}
function setNow(field) {
  const now = new Date()
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset()) // para datetime-local
  const val = now.toISOString().slice(0,16)
  form[field] = val
}
function sumarMinutos(mins) {
  if (!form.inicio_at) return
  const base = new Date(form.inicio_at)
  const local = new Date(base.getTime() - base.getTimezoneOffset() * 60000)
  local.setMinutes(local.getMinutes() + mins)
  const val = new Date(local.getTime() + local.getTimezoneOffset() * 60000) // revert hack
  val.setMinutes(val.getMinutes() - val.getTimezoneOffset())
  form.fin_at = val.toISOString().slice(0,16)
}
const duracionTexto = computed(() => {
  if (!form.inicio_at || !form.fin_at) return ''
  const i = new Date(form.inicio_at)
  const f = new Date(form.fin_at)
  const ms = f - i
  if (isNaN(ms) || ms <= 0) return ''
  const m = Math.round(ms / 60000)
  const h = Math.floor(m / 60)
  const mm = m % 60
  return h ? `${h}h ${mm}m` : `${mm}m`
})
function normalizePayload() {
  // Enviamos como strings datetime-local; Laravel Carbon los parsea sin problema.
  // Si se requiere formato Y-m-d H:i:s, convertir aquí.
  return { ...form.data() }
}
function submit(mode = 'index') {
  const payload = normalizePayload()
  form.post(route('bitacora.store'), {
    preserveScroll: true,
    data: payload,
    onSuccess: () => {
      if (mode === 'stay') {
        const keepUser = form.user_id
        const keepTipo = form.tipo
        const keepEstado = form.estado
        form.reset('titulo', 'cliente_id', 'inicio_at', 'fin_at', 'ubicacion', 'descripcion')
        form.user_id = keepUser
        form.tipo = keepTipo
        form.estado = keepEstado
        // Opcional: mantener foco en título
        const el = document.querySelector('#campo-titulo')
        el?.focus?.()
      } else {
        router.visit(route('bitacora.index'))
      }
    },
  })
}
</script>
<style scoped>
/* Mejores focos y estados */
input:focus, select:focus, textarea:focus { outline: none; }
</style>
