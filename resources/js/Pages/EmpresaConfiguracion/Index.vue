<!-- /resources/js/Pages/EmpresaConfiguracion/Index.vue -->
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
  configuracion: { type: Object, required: true },
})

// Estado de la interfaz
const activeTab = ref('general')
const logoPreview = ref(null)
const faviconPreview = ref(null)
const logoReportesPreview = ref(null)

// Formulario principal
const form = useForm({
  nombre_empresa: props.configuracion.nombre_empresa,
  rfc: props.configuracion.rfc,
  razon_social: props.configuracion.razon_social,
  calle: props.configuracion.calle,
  numero_exterior: props.configuracion.numero_exterior,
  numero_interior: props.configuracion.numero_interior,
  telefono: props.configuracion.telefono,
  email: props.configuracion.email,
  sitio_web: props.configuracion.sitio_web,
  codigo_postal: props.configuracion.codigo_postal,
  colonia: props.configuracion.colonia,
  ciudad: props.configuracion.ciudad,
  estado: props.configuracion.estado,
  pais: props.configuracion.pais,
  descripcion_empresa: props.configuracion.descripcion_empresa,
  color_principal: props.configuracion.color_principal,
  color_secundario: props.configuracion.color_secundario,
  pie_pagina_facturas: props.configuracion.pie_pagina_facturas,
  pie_pagina_cotizaciones: props.configuracion.pie_pagina_cotizaciones,
  terminos_condiciones: props.configuracion.terminos_condiciones,
  politica_privacidad: props.configuracion.politica_privacidad,
  iva_porcentaje: props.configuracion.iva_porcentaje,
  moneda: props.configuracion.moneda,
  formato_numeros: props.configuracion.formato_numeros,
  mantenimiento: props.configuracion.mantenimiento,
  mensaje_mantenimiento: props.configuracion.mensaje_mantenimiento,
  registro_usuarios: props.configuracion.registro_usuarios,
  notificaciones_email: props.configuracion.notificaciones_email,
  formato_fecha: props.configuracion.formato_fecha,
  formato_hora: props.configuracion.formato_hora,
  backup_automatico: props.configuracion.backup_automatico,
  frecuencia_backup: props.configuracion.frecuencia_backup,
  retencion_backups: props.configuracion.retencion_backups,
  intentos_login: props.configuracion.intentos_login,
  tiempo_bloqueo: props.configuracion.tiempo_bloqueo,
  requerir_2fa: props.configuracion.requerir_2fa,
})

// SEPOMEX: colonias obtenidas por CP y colonia seleccionada
const colonias = ref([])

const validarCodigoPostalEmpresa = async () => {
  try {
    form.codigo_postal = (form.codigo_postal || '').toString().replace(/\D/g, '')
    colonias.value = []
    form.colonia = ''

    if (form.codigo_postal && form.codigo_postal.length === 5) {
      const resp = await fetch(`/api/cp/${form.codigo_postal}`)
      if (resp.ok) {
        const data = await resp.json()
        // Mapear a campos del formulario
        if (data.estado) form.estado = data.estado
        if (data.municipio) form.ciudad = data.municipio
        if (data.ciudad && !form.ciudad) form.ciudad = data.ciudad
        if (data.pais) form.pais = data.pais
        if (Array.isArray(data.colonias)) {
          colonias.value = data.colonias
          if (colonias.value.length === 1) form.colonia = colonias.value[0]
        }
      } else if (resp.status === 404) {
        // Código postal no encontrado
        notyf.error(`El código postal ${form.codigo_postal} no se encuentra en la base de datos. Puedes continuar ingresando los datos manualmente.`)
        // Limpiar campos que se habrían autocompletado
        form.estado = ''
        form.ciudad = ''
        form.pais = ''
        form.colonia = ''
        colonias.value = []
      } else {
        // Otro tipo de error
        const errorData = await resp.json().catch(() => ({}))
        notyf.error(errorData.error || 'Error al consultar el código postal')
      }
    }
  } catch (e) {
    // Error de red u otro tipo de error
    console.warn('Error consultando CP', e)
    notyf.error('Error de conexión al consultar el código postal. Puedes continuar ingresando los datos manualmente.')
  }
}

// Formularios para archivos
const logoForm = useForm({
  logo: null,
})

const faviconForm = useForm({
  favicon: null,
})

const logoReportesForm = useForm({
  logo_reportes: null,
})

// Pestañas disponibles
const tabs = [
  { id: 'general', nombre: 'Información General', icono: 'building' },
  { id: 'visual', nombre: 'Apariencia', icono: 'palette' },
  { id: 'documentos', nombre: 'Documentos', icono: 'document-text' },
  { id: 'sistema', nombre: 'Sistema', icono: 'cog' },
  { id: 'seguridad', nombre: 'Seguridad', icono: 'shield-check' },
]

// Iconos para las pestañas
const tabIcons = {
  building: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  palette: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z',
  'document-text': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  cog: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
  'shield-check': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
}

// Métodos para manejar archivos
const handleLogoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    logoForm.logo = file
    logoPreview.value = URL.createObjectURL(file)
  }
}

const handleFaviconChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    faviconForm.favicon = file
    faviconPreview.value = URL.createObjectURL(file)
  }
}

const handleLogoReportesChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    logoReportesForm.logo_reportes = file
    logoReportesPreview.value = URL.createObjectURL(file)
  }
}

// Métodos para subir archivos
const subirLogo = () => {
  if (logoForm.logo) {
    logoForm.post(route('empresa-configuracion.subir-logo'), {
      onSuccess: () => {
        notyf.success('Logo subido correctamente')
        logoForm.logo = null
        logoPreview.value = null
        router.reload()
      },
      onError: () => {
        notyf.error('Error al subir el logo')
      }
    })
  }
}

const subirFavicon = () => {
  if (faviconForm.favicon) {
    faviconForm.post(route('empresa-configuracion.subir-favicon'), {
      onSuccess: () => {
        notyf.success('Favicon subido correctamente')
        faviconForm.favicon = null
        faviconPreview.value = null
        router.reload()
      },
      onError: () => {
        notyf.error('Error al subir el favicon')
      }
    })
  }
}

const subirLogoReportes = () => {
  if (logoReportesForm.logo_reportes) {
    logoReportesForm.post(route('empresa-configuracion.subir-logo-reportes'), {
      onSuccess: () => {
        notyf.success('Logo para reportes subido correctamente')
        logoReportesForm.logo_reportes = null
        logoReportesPreview.value = null
        router.reload()
      },
      onError: () => {
        notyf.error('Error al subir el logo para reportes')
      }
    })
  }
}

// Método para eliminar archivos
const eliminarLogo = () => {
  if (confirm('¿Estás seguro de que deseas eliminar el logo?')) {
    router.delete(route('empresa-configuracion.eliminar-logo'), {
      onSuccess: () => {
        notyf.success('Logo eliminado correctamente')
        router.reload()
      },
      onError: () => {
        notyf.error('Error al eliminar el logo')
      }
    })
  }
}

const eliminarFavicon = () => {
  if (confirm('¿Estás seguro de que deseas eliminar el favicon?')) {
    router.delete(route('empresa-configuracion.eliminar-favicon'), {
      onSuccess: () => {
        notyf.success('Favicon eliminado correctamente')
        router.reload()
      },
      onError: () => {
        notyf.error('Error al eliminar el favicon')
      }
    })
  }
}

const eliminarLogoReportes = () => {
  if (confirm('¿Estás seguro de que deseas eliminar el logo para reportes?')) {
    router.delete(route('empresa-configuracion.eliminar-logo-reportes'), {
      onSuccess: () => {
        notyf.success('Logo para reportes eliminado correctamente')
        router.reload()
      },
      onError: () => {
        notyf.error('Error al eliminar el logo para reportes')
      }
    })
  }
}

// Método para guardar configuración
const guardarConfiguracion = () => {
  form.put(route('empresa-configuracion.update'), {
    onSuccess: () => {
      notyf.success('Configuración guardada correctamente')
    },
    onError: () => {
      notyf.error('Error al guardar la configuración')
    }
  })
}

// Método para probar colores
const previewColores = () => {
  router.post(route('empresa-configuracion.preview-colores'), {
    color_principal: form.color_principal,
    color_secundario: form.color_secundario,
  }, {
    onSuccess: (response) => {
      notyf.success('Vista previa de colores aplicada')
    },
    onError: () => {
      notyf.error('Error al aplicar vista previa de colores')
    }
  })
}

// Computed para obtener la pestaña activa
const tabActiva = computed(() => {
  return tabs.find(tab => tab.id === activeTab.value) || tabs[0]
})
</script>

<template>
  <Head title="Configuración de Empresa" />

  <div class="empresa-configuracion min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-slate-900">Configuración de Empresa</h1>
            <p class="text-slate-600 mt-1">Personaliza la información y apariencia de tu sistema</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar con pestañas -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <nav class="space-y-2">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg transition-all duration-200',
                  activeTab === tab.id
                    ? 'bg-blue-50 text-blue-700 border border-blue-200'
                    : 'text-gray-700 hover:bg-gray-50'
                ]
              ">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path :d="getTabIconPath(tab.icono)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                <span class="font-medium">{{ tab.nombre }}</span>
              </button>
            </nav>
          </div>
        </div>

        <!-- Contenido principal -->
        <div class="lg:col-span-3">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <!-- Información General -->
            <div v-if="activeTab === 'general'" class="space-y-8">
              <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Información General</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Nombre de la empresa -->
                  <div class="md:col-span-2">
                    <label for="nombre_empresa" class="block text-sm font-medium text-gray-700 mb-2">
                      Nombre de la Empresa *
                    </label>
                    <input
                      v-model="form.nombre_empresa"
                      id="nombre_empresa"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Nombre de tu empresa"
                    />
                    <p v-if="form.errors.nombre_empresa" class="mt-1 text-sm text-red-600">
                      {{ form.errors.nombre_empresa }}
                    </p>
                  </div>

                  <!-- RFC -->
                  <div>
                    <label for="rfc" class="block text-sm font-medium text-gray-700 mb-2">
                      RFC
                    </label>
                    <input
                      v-model="form.rfc"
                      id="rfc"
                      type="text"
                      maxlength="13"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="XAXX010101000"
                    />
                    <p v-if="form.errors.rfc" class="mt-1 text-sm text-red-600">
                      {{ form.errors.rfc }}
                    </p>
                  </div>

                  <!-- Razón Social -->
                  <div>
                    <label for="razon_social" class="block text-sm font-medium text-gray-700 mb-2">
                      Razón Social
                    </label>
                    <input
                      v-model="form.razon_social"
                      id="razon_social"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Razón social completa"
                    />
                    <p v-if="form.errors.razon_social" class="mt-1 text-sm text-red-600">
                      {{ form.errors.razon_social }}
                    </p>
                  </div>

                  <!-- Dirección organizada -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Dirección
                    </label>

                    <!-- Calle -->
                    <div class="mb-4">
                      <label for="calle" class="block text-sm font-medium text-gray-700 mb-2">
                        Calle *
                      </label>
                      <input
                        v-model="form.calle"
                        id="calle"
                        type="text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Nombre de la calle"
                      />
                      <p v-if="form.errors.calle" class="mt-1 text-sm text-red-600">
                        {{ form.errors.calle }}
                      </p>
                    </div>

                    <!-- Números Exterior e Interior -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                      <div>
                        <label for="numero_exterior" class="block text-sm font-medium text-gray-700 mb-2">
                          Número Exterior *
                        </label>
                        <input
                          v-model="form.numero_exterior"
                          id="numero_exterior"
                          type="text"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="123"
                        />
                        <p v-if="form.errors.numero_exterior" class="mt-1 text-sm text-red-600">
                          {{ form.errors.numero_exterior }}
                        </p>
                      </div>

                      <div>
                        <label for="numero_interior" class="block text-sm font-medium text-gray-700 mb-2">
                          Número Interior
                        </label>
                        <input
                          v-model="form.numero_interior"
                          id="numero_interior"
                          type="text"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="A, Depto 1B, etc."
                        />
                        <p v-if="form.errors.numero_interior" class="mt-1 text-sm text-red-600">
                          {{ form.errors.numero_interior }}
                        </p>
                      </div>
                    </div>

                    <!-- Código Postal, Ciudad, Estado, País -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="codigo_postal" class="block text-sm font-medium text-gray-700 mb-2">
                          Código Postal
                        </label>
                        <input
                          v-model="form.codigo_postal"
                          id="codigo_postal"
                          type="text"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="00000"
                          @blur="validarCodigoPostalEmpresa"
                        />
                        <p v-if="form.errors.codigo_postal" class="mt-1 text-sm text-red-600">
                          {{ form.errors.codigo_postal }}
                        </p>

                        <!-- Colonia: select si hay catálogo, si no input libre -->
                        <div v-if="colonias.length" class="mt-3">
                          <label class="block text-sm font-medium text-gray-700 mb-2">Colonia</label>
                          <select
                            v-model="form.colonia"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          >
                            <option value="">Selecciona una colonia</option>
                            <option v-for="col in colonias" :key="col" :value="col">{{ col }}</option>
                          </select>
                          <p v-if="form.errors.colonia" class="mt-1 text-sm text-red-600">{{ form.errors.colonia }}</p>
                        </div>
                        <div v-else class="mt-3">
                          <label for="colonia" class="block text-sm font-medium text-gray-700 mb-2">Colonia</label>
                          <input
                            v-model="form.colonia"
                            id="colonia"
                            type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Nombre de la colonia"
                          />
                          <p v-if="form.errors.colonia" class="mt-1 text-sm text-red-600">{{ form.errors.colonia }}</p>
                        </div>
                      </div>

                      <div>
                        <label for="ciudad" class="block text-sm font-medium text-gray-700 mb-2">
                          Ciudad
                        </label>
                        <input
                          v-model="form.ciudad"
                          id="ciudad"
                          type="text"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Ciudad de México"
                        />
                        <p v-if="form.errors.ciudad" class="mt-1 text-sm text-red-600">
                          {{ form.errors.ciudad }}
                        </p>
                      </div>

                      <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                          Estado
                        </label>
                        <input
                          v-model="form.estado"
                          id="estado"
                          type="text"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="CDMX"
                        />
                        <p v-if="form.errors.estado" class="mt-1 text-sm text-red-600">
                          {{ form.errors.estado }}
                        </p>
                      </div>

                      <div>
                        <label for="pais" class="block text-sm font-medium text-gray-700 mb-2">
                          País
                        </label>
                        <input
                          v-model="form.pais"
                          id="pais"
                          type="text"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="México"
                        />
                        <p v-if="form.errors.pais" class="mt-1 text-sm text-red-600">
                          {{ form.errors.pais }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Teléfono -->
                  <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                      Teléfono
                    </label>
                    <input
                      v-model="form.telefono"
                      id="telefono"
                      type="tel"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="55-5555-5555"
                    />
                    <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">
                      {{ form.errors.telefono }}
                    </p>
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                      Email
                    </label>
                    <input
                      v-model="form.email"
                      id="email"
                      type="email"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="contacto@empresa.com"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                      {{ form.errors.email }}
                    </p>
                  </div>

                  <!-- Sitio Web -->
                  <div>
                    <label for="sitio_web" class="block text-sm font-medium text-gray-700 mb-2">
                      Sitio Web
                    </label>
                    <input
                      v-model="form.sitio_web"
                      id="sitio_web"
                      type="url"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="https://empresa.com"
                    />
                    <p v-if="form.errors.sitio_web" class="mt-1 text-sm text-red-600">
                      {{ form.errors.sitio_web }}
                    </p>
                  </div>


                  <!-- Descripción de la empresa -->
                  <div class="md:col-span-2">
                    <label for="descripcion_empresa" class="block text-sm font-medium text-gray-700 mb-2">
                      Descripción de la Empresa
                    </label>
                    <textarea
                      v-model="form.descripcion_empresa"
                      id="descripcion_empresa"
                      rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                      placeholder="Breve descripción de la empresa..."
                    ></textarea>
                    <p v-if="form.errors.descripcion_empresa" class="mt-1 text-sm text-red-600">
                      {{ form.errors.descripcion_empresa }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Apariencia -->
            <div v-if="activeTab === 'visual'" class="space-y-8">
              <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Apariencia del Sistema</h2>

                <!-- Logo actual -->
                <div class="mb-8">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Logo de la Empresa</h3>
                  <div class="flex items-start gap-6">
                    <div v-if="configuracion.logo_url" class="flex-shrink-0">
                      <img :src="configuracion.logo_url" alt="Logo actual" class="w-32 h-32 object-contain border border-gray-200 rounded-lg p-2" />
                    </div>
                    <div class="flex-1">
                      <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div class="text-center">
                          <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                          <div class="mt-4">
                            <label for="logo-input" class="cursor-pointer">
                              <span class="mt-2 block text-sm font-medium text-gray-900">Subir nuevo logo</span>
                              <input id="logo-input" type="file" accept="image/*" @change="handleLogoChange" class="sr-only" />
                            </label>
                          </div>
                          <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF hasta 2MB</p>
                        </div>
                      </div>

                      <!-- Vista previa del logo seleccionado -->
                      <div v-if="logoPreview" class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Vista previa:</p>
                        <img :src="logoPreview" alt="Vista previa del logo" class="w-32 h-32 object-contain border border-gray-200 rounded-lg p-2" />
                        <div class="mt-2 flex gap-2">
                          <button @click="subirLogo" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                            Subir Logo
                          </button>
                          <button @click="logoPreview = null; logoForm.logo = null" class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400">
                            Cancelar
                          </button>
                        </div>
                      </div>

                      <!-- Botón para eliminar logo actual -->
                      <div v-if="configuracion.logo_url" class="mt-4">
                        <button @click="eliminarLogo" class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                          Eliminar Logo Actual
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Favicon actual -->
                <div class="mb-8">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Favicon</h3>
                  <div class="flex items-start gap-6">
                    <div v-if="configuracion.favicon_url" class="flex-shrink-0">
                      <img :src="configuracion.favicon_url" alt="Favicon actual" class="w-8 h-8 border border-gray-200 rounded-lg p-1" />
                    </div>
                    <div class="flex-1">
                      <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div class="text-center">
                          <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                          <div class="mt-4">
                            <label for="favicon-input" class="cursor-pointer">
                              <span class="mt-2 block text-sm font-medium text-gray-900">Subir nuevo favicon</span>
                              <input id="favicon-input" type="file" accept="image/*" @change="handleFaviconChange" class="sr-only" />
                            </label>
                          </div>
                          <p class="mt-1 text-xs text-gray-500">PNG, JPG, ICO hasta 1MB</p>
                        </div>
                      </div>

                      <!-- Vista previa del favicon seleccionado -->
                      <div v-if="faviconPreview" class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Vista previa:</p>
                        <img :src="faviconPreview" alt="Vista previa del favicon" class="w-8 h-8 border border-gray-200 rounded-lg p-1" />
                        <div class="mt-2 flex gap-2">
                          <button @click="subirFavicon" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                            Subir Favicon
                          </button>
                          <button @click="faviconPreview = null; faviconForm.favicon = null" class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400">
                            Cancelar
                          </button>
                        </div>
                      </div>

                      <!-- Botón para eliminar favicon actual -->
                      <div v-if="configuracion.favicon_url" class="mt-4">
                        <button @click="eliminarFavicon" class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                          Eliminar Favicon Actual
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Logo para reportes -->
                <div class="mb-8">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Logo para Reportes</h3>
                  <div class="flex items-start gap-6">
                    <div v-if="configuracion.logo_reportes_url" class="flex-shrink-0">
                      <img :src="configuracion.logo_reportes_url" alt="Logo para reportes" class="w-32 h-32 object-contain border border-gray-200 rounded-lg p-2" />
                    </div>
                    <div class="flex-1">
                      <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div class="text-center">
                          <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                          <div class="mt-4">
                            <label for="logo-reportes-input" class="cursor-pointer">
                              <span class="mt-2 block text-sm font-medium text-gray-900">Subir logo para reportes</span>
                              <input id="logo-reportes-input" type="file" accept="image/*" @change="handleLogoReportesChange" class="sr-only" />
                            </label>
                          </div>
                          <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF hasta 2MB</p>
                        </div>
                      </div>

                      <!-- Vista previa del logo para reportes seleccionado -->
                      <div v-if="logoReportesPreview" class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Vista previa:</p>
                        <img :src="logoReportesPreview" alt="Vista previa del logo para reportes" class="w-32 h-32 object-contain border border-gray-200 rounded-lg p-2" />
                        <div class="mt-2 flex gap-2">
                          <button @click="subirLogoReportes" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                            Subir Logo
                          </button>
                          <button @click="logoReportesPreview = null; logoReportesForm.logo_reportes = null" class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400">
                            Cancelar
                          </button>
                        </div>
                      </div>

                      <!-- Botón para eliminar logo para reportes actual -->
                      <div v-if="configuracion.logo_reportes_url" class="mt-4">
                        <button @click="eliminarLogoReportes" class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                          Eliminar Logo para Reportes
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Colores del sistema -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Color Principal -->
                  <div>
                    <label for="color_principal" class="block text-sm font-medium text-gray-700 mb-2">
                      Color Principal
                    </label>
                    <div class="flex items-center gap-3">
                      <input
                        v-model="form.color_principal"
                        id="color_principal"
                        type="color"
                        class="w-12 h-10 border border-gray-300 rounded-lg cursor-pointer"
                      />
                      <input
                        v-model="form.color_principal"
                        type="text"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="#3B82F6"
                      />
                    </div>
                    <p v-if="form.errors.color_principal" class="mt-1 text-sm text-red-600">
                      {{ form.errors.color_principal }}
                    </p>
                  </div>

                  <!-- Color Secundario -->
                  <div>
                    <label for="color_secundario" class="block text-sm font-medium text-gray-700 mb-2">
                      Color Secundario
                    </label>
                    <div class="flex items-center gap-3">
                      <input
                        v-model="form.color_secundario"
                        id="color_secundario"
                        type="color"
                        class="w-12 h-10 border border-gray-300 rounded-lg cursor-pointer"
                      />
                      <input
                        v-model="form.color_secundario"
                        type="text"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="#1E40AF"
                      />
                    </div>
                    <p v-if="form.errors.color_secundario" class="mt-1 text-sm text-red-600">
                      {{ form.errors.color_secundario }}
                    </p>
                  </div>
                </div>

                <!-- Vista previa de colores -->
                <div class="mt-6 p-4 border border-gray-200 rounded-lg">
                  <h4 class="text-sm font-medium text-gray-700 mb-3">Vista Previa de Colores</h4>
                  <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                      <div
                        :style="{ backgroundColor: form.color_principal }"
                        class="w-8 h-8 rounded-lg border border-gray-300"
                      ></div>
                      <span class="text-sm text-gray-600">{{ form.color_principal }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <div
                        :style="{ backgroundColor: form.color_secundario }"
                        class="w-8 h-8 rounded-lg border border-gray-300"
                      ></div>
                      <span class="text-sm text-gray-600">{{ form.color_secundario }}</span>
                    </div>
                    <button @click="previewColores" class="px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                      Aplicar Vista Previa
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Documentos -->
            <div v-if="activeTab === 'documentos'" class="space-y-8">
              <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuración de Documentos</h2>

                <div class="space-y-6">
                  <!-- IVA -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="iva_porcentaje" class="block text-sm font-medium text-gray-700 mb-2">
                        Porcentaje de IVA (%)
                      </label>
                      <input
                        v-model="form.iva_porcentaje"
                        id="iva_porcentaje"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="16.00"
                      />
                      <p v-if="form.errors.iva_porcentaje" class="mt-1 text-sm text-red-600">
                        {{ form.errors.iva_porcentaje }}
                      </p>
                    </div>

                    <div>
                      <label for="moneda" class="block text-sm font-medium text-gray-700 mb-2">
                        Moneda
                      </label>
                      <select
                        v-model="form.moneda"
                        id="moneda"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      >
                        <option value="MXN">Peso Mexicano (MXN)</option>
                        <option value="USD">Dólar Estadounidense (USD)</option>
                        <option value="EUR">Euro (EUR)</option>
                      </select>
                      <p v-if="form.errors.moneda" class="mt-1 text-sm text-red-600">
                        {{ form.errors.moneda }}
                      </p>
                    </div>
                  </div>

                  <!-- Pie de página para facturas -->
                  <div>
                    <label for="pie_pagina_facturas" class="block text-sm font-medium text-gray-700 mb-2">
                      Pie de Página para Facturas
                    </label>
                    <textarea
                      v-model="form.pie_pagina_facturas"
                      id="pie_pagina_facturas"
                      rows="3"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                      placeholder="Texto que aparecerá al pie de las facturas..."
                    ></textarea>
                    <p v-if="form.errors.pie_pagina_facturas" class="mt-1 text-sm text-red-600">
                      {{ form.errors.pie_pagina_facturas }}
                    </p>
                  </div>

                  <!-- Pie de página para cotizaciones -->
                  <div>
                    <label for="pie_pagina_cotizaciones" class="block text-sm font-medium text-gray-700 mb-2">
                      Pie de Página para Cotizaciones
                    </label>
                    <textarea
                      v-model="form.pie_pagina_cotizaciones"
                      id="pie_pagina_cotizaciones"
                      rows="3"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                      placeholder="Texto que aparecerá al pie de las cotizaciones..."
                    ></textarea>
                    <p v-if="form.errors.pie_pagina_cotizaciones" class="mt-1 text-sm text-red-600">
                      {{ form.errors.pie_pagina_cotizaciones }}
                    </p>
                  </div>

                  <!-- Términos y condiciones -->
                  <div>
                    <label for="terminos_condiciones" class="block text-sm font-medium text-gray-700 mb-2">
                      Términos y Condiciones
                    </label>
                    <textarea
                      v-model="form.terminos_condiciones"
                      id="terminos_condiciones"
                      rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                      placeholder="Términos y condiciones generales..."
                    ></textarea>
                    <p v-if="form.errors.terminos_condiciones" class="mt-1 text-sm text-red-600">
                      {{ form.errors.terminos_condiciones }}
                    </p>
                  </div>

                  <!-- Política de privacidad -->
                  <div>
                    <label for="politica_privacidad" class="block text-sm font-medium text-gray-700 mb-2">
                      Política de Privacidad
                    </label>
                    <textarea
                      v-model="form.politica_privacidad"
                      id="politica_privacidad"
                      rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                      placeholder="Política de privacidad..."
                    ></textarea>
                    <p v-if="form.errors.politica_privacidad" class="mt-1 text-sm text-red-600">
                      {{ form.errors.politica_privacidad }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sistema -->
            <div v-if="activeTab === 'sistema'" class="space-y-8">
              <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuración del Sistema</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Formato de fecha -->
                  <div>
                    <label for="formato_fecha" class="block text-sm font-medium text-gray-700 mb-2">
                      Formato de Fecha
                    </label>
                    <select
                      v-model="form.formato_fecha"
                      id="formato_fecha"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="d/m/Y">DD/MM/YYYY (31/12/2023)</option>
                      <option value="m/d/Y">MM/DD/YYYY (12/31/2023)</option>
                      <option value="Y-m-d">YYYY-MM-DD (2023-12-31)</option>
                      <option value="d-m-Y">DD-MM-YYYY (31-12-2023)</option>
                    </select>
                    <p v-if="form.errors.formato_fecha" class="mt-1 text-sm text-red-600">
                      {{ form.errors.formato_fecha }}
                    </p>
                  </div>

                  <!-- Formato de hora -->
                  <div>
                    <label for="formato_hora" class="block text-sm font-medium text-gray-700 mb-2">
                      Formato de Hora
                    </label>
                    <select
                      v-model="form.formato_hora"
                      id="formato_hora"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="H:i:s">24 horas (14:30:45)</option>
                      <option value="h:i:s A">12 horas (02:30:45 PM)</option>
                      <option value="H:i">24 horas corto (14:30)</option>
                      <option value="h:i A">12 horas corto (02:30 PM)</option>
                    </select>
                    <p v-if="form.errors.formato_hora" class="mt-1 text-sm text-red-600">
                      {{ form.errors.formato_hora }}
                    </p>
                  </div>

                  <!-- Formato de números -->
                  <div>
                    <label for="formato_numeros" class="block text-sm font-medium text-gray-700 mb-2">
                      Formato de Números
                    </label>
                    <select
                      v-model="form.formato_numeros"
                      id="formato_numeros"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="es-ES">Español (1.234,56)</option>
                      <option value="en-US">Inglés (1,234.56)</option>
                      <option value="de-DE">Alemán (1.234,56)</option>
                    </select>
                    <p v-if="form.errors.formato_numeros" class="mt-1 text-sm text-red-600">
                      {{ form.errors.formato_numeros }}
                    </p>
                  </div>
                </div>

                <!-- Configuración de mantenimiento -->
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Modo de Mantenimiento</h3>

                  <div class="space-y-4">
                    <div class="flex items-center">
                      <input
                        v-model="form.mantenimiento"
                        id="mantenimiento"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <label for="mantenimiento" class="ml-2 block text-sm text-gray-900">
                        Activar modo de mantenimiento
                      </label>
                    </div>

                    <div v-if="form.mantenimiento">
                      <label for="mensaje_mantenimiento" class="block text-sm font-medium text-gray-700 mb-2">
                        Mensaje de Mantenimiento
                      </label>
                      <textarea
                        v-model="form.mensaje_mantenimiento"
                        id="mensaje_mantenimiento"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical"
                        placeholder="Mensaje que verán los usuarios cuando el sistema esté en mantenimiento..."
                      ></textarea>
                      <p v-if="form.errors.mensaje_mantenimiento" class="mt-1 text-sm text-red-600">
                        {{ form.errors.mensaje_mantenimiento }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Configuración de usuarios -->
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Usuarios</h3>

                  <div class="space-y-4">
                    <div class="flex items-center">
                      <input
                        v-model="form.registro_usuarios"
                        id="registro_usuarios"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <label for="registro_usuarios" class="ml-2 block text-sm text-gray-900">
                        Permitir registro de nuevos usuarios
                      </label>
                    </div>

                    <div class="flex items-center">
                      <input
                        v-model="form.notificaciones_email"
                        id="notificaciones_email"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <label for="notificaciones_email" class="ml-2 block text-sm text-gray-900">
                        Activar notificaciones por email
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Configuración de backups -->
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Backups</h3>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center">
                      <input
                        v-model="form.backup_automatico"
                        id="backup_automatico"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <label for="backup_automatico" class="ml-2 block text-sm text-gray-900">
                        Realizar backups automáticos
                      </label>
                    </div>

                    <div>
                      <label for="frecuencia_backup" class="block text-sm font-medium text-gray-700 mb-2">
                        Frecuencia de Backups (días)
                      </label>
                      <input
                        v-model="form.frecuencia_backup"
                        id="frecuencia_backup"
                        type="number"
                        min="1"
                        max="365"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                      <p v-if="form.errors.frecuencia_backup" class="mt-1 text-sm text-red-600">
                        {{ form.errors.frecuencia_backup }}
                      </p>
                    </div>

                    <div class="md:col-span-2">
                      <label for="retencion_backups" class="block text-sm font-medium text-gray-700 mb-2">
                        Retención de Backups (días)
                      </label>
                      <input
                        v-model="form.retencion_backups"
                        id="retencion_backups"
                        type="number"
                        min="1"
                        max="365"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                      <p v-if="form.errors.retencion_backups" class="mt-1 text-sm text-red-600">
                        {{ form.errors.retencion_backups }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Seguridad -->
            <div v-if="activeTab === 'seguridad'" class="space-y-8">
              <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuración de Seguridad</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Intentos de login -->
                  <div>
                    <label for="intentos_login" class="block text-sm font-medium text-gray-700 mb-2">
                      Intentos de Login Permitidos
                    </label>
                    <input
                      v-model="form.intentos_login"
                      id="intentos_login"
                      type="number"
                      min="1"
                      max="20"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p v-if="form.errors.intentos_login" class="mt-1 text-sm text-red-600">
                      {{ form.errors.intentos_login }}
                    </p>
                  </div>

                  <!-- Tiempo de bloqueo -->
                  <div>
                    <label for="tiempo_bloqueo" class="block text-sm font-medium text-gray-700 mb-2">
                      Tiempo de Bloqueo (minutos)
                    </label>
                    <input
                      v-model="form.tiempo_bloqueo"
                      id="tiempo_bloqueo"
                      type="number"
                      min="1"
                      max="1440"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p v-if="form.errors.tiempo_bloqueo" class="mt-1 text-sm text-red-600">
                      {{ form.errors.tiempo_bloqueo }}
                    </p>
                  </div>
                </div>

                <!-- Autenticación de dos factores -->
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                  <div class="flex items-center">
                    <input
                      v-model="form.requerir_2fa"
                      id="requerir_2fa"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label for="requerir_2fa" class="ml-2 block text-sm text-gray-900">
                      Requerir autenticación de dos factores (2FA) para todos los usuarios
                    </label>
                  </div>
                  <p class="mt-2 text-sm text-gray-600">
                    Si se activa, todos los usuarios deberán configurar la autenticación de dos factores para acceder al sistema.
                  </p>
                </div>
              </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
              <button
                type="button"
                @click="guardarConfiguracion"
                :disabled="form.processing"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
              >
                {{ form.processing ? 'Guardando...' : 'Guardar Configuración' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Método auxiliar para obtener el path del ícono
const getTabIconPath = (icono) => {
  const paths = {
    building: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    palette: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z',
    'document-text': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    cog: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    'shield-check': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
  }

  return paths[icono] || paths.cog
}
</script>

<style scoped>
.empresa-configuracion {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>


