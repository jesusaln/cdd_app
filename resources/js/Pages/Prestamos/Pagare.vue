<!-- /resources/js/Pages/Prestamos/Pagare.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

defineOptions({ layout: AppLayout })

/* =========================
    Props
========================= */
const props = defineProps({
  prestamo: { type: Object, required: true },
  cliente: { type: Object, required: true },
  empresa: { type: Object, required: true },
  fecha_actual: { type: String, required: true }, // ISO o legible es-MX
  monto_letras: { type: String, required: true },
  tasa_mensual: { type: Number, required: true },
  pago_mensual_letras: { type: String, required: true }
})

/* =========================
    Branding (Información empresarial desde props)
 ========================= */
const EMPRESA = computed(() => ({
  nombre: props.empresa?.nombre || 'EMPRESA NO ESPECIFICADA',
  nombreCorto: props.empresa?.nombre_comercial || 'EMP',
  rfc: props.empresa?.rfc || 'RFC NO ESPECIFICADO',
  domicilio: props.empresa?.direccion || 'Domicilio no especificado',
  lugarPago: props.empresa?.direccion || 'Lugar de pago no especificado',
  telefono: props.empresa?.telefono || 'Teléfono no especificado',
  email: props.empresa?.email || 'Email no especificado'
}))

/* =========================
   Información adicional del documento
========================= */
const DOCUMENTO_INFO = {
  titulo: 'PAGARÉ',
  subtitulo: 'Título Ejecutivo de Crédito',
  descripcion: 'Documento legal que constituye obligación financiera exigible por la vía ejecutiva mercantil.',
  version: '3.0',
  fechaActualizacion: '2024'
}

/* =========================
   Notificaciones
========================= */
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

/* =========================
   Utilidades de formato
========================= */
const formatearMoneda = (num) => {
  const value = parseFloat(num)
  const safe = Number.isFinite(value) ? value : 0
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(safe)
}

const formatearFecha = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const time = new Date(date).getTime()
    if (Number.isNaN(time)) return 'Fecha inválida'
    return new Date(time).toLocaleDateString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch {
    return 'Fecha inválida'
  }
}

const formatearFechaCompleta = (date) => {
  if (!date) return 'Fecha no disponible'
  try {
    const time = new Date(date).getTime()
    if (Number.isNaN(time)) return 'Fecha inválida'
    return new Date(time).toLocaleString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return 'Fecha inválida'
  }
}

/* =========================
   Funciones auxiliares
========================= */
const obtenerInicialesCliente = () => {
  if (!props.cliente?.nombre_razon_social) return 'CL'
  const palabras = props.cliente.nombre_razon_social.trim().split(' ')
  if (palabras.length >= 2) {
    return (palabras[0][0] + palabras[1][0]).toUpperCase()
  }
  return palabras[0].substring(0, 2).toUpperCase()
}

const formatearNumeroContrato = () => {
  return `CDD-${new Date().getFullYear()}-${String(props.prestamo?.id ?? '').padStart(4, '0')}`
}

/* =========================
   Computados
========================= */
const fechaPrimerPago = computed(() => {
  if (!props.prestamo?.fecha_primer_pago) return 'Fecha no disponible'
  return formatearFecha(props.prestamo.fecha_primer_pago)
})

const fechaVencimiento = computed(() => {
  // Vencimiento por número de pagos desde fecha_inicio
  if (!props.prestamo?.fecha_inicio || !props.prestamo?.numero_pagos) return 'Fecha no disponible'
  const fecha = new Date(props.prestamo.fecha_inicio)
  fecha.setMonth(fecha.getMonth() + Number(props.prestamo.numero_pagos || 0))
  return formatearFecha(fecha)
})

const resumenFinanciero = computed(() => {
  return {
    montoTotal: formatearMoneda(props.prestamo?.monto_prestado || 0),
    pagoMensual: formatearMoneda(props.prestamo?.pago_periodico || 0),
    numeroPagos: props.prestamo?.numero_pagos || 0,
    tasaInteres: `${(props.tasa_mensual || 0).toFixed(2)}%`,
    fechaInicio: formatearFecha(props.prestamo?.fecha_inicio),
    primerPago: fechaPrimerPago.value
  }
})

/* =========================
   Validación previa
========================= */
const validarDatos = () => {
  const faltantes = []
  if (!props.cliente?.nombre_razon_social) faltantes.push('Nombre/Razón social del cliente')
  if (!props.prestamo?.monto_prestado) faltantes.push('Monto del préstamo')
  if (!props.prestamo?.pago_periodico) faltantes.push('Pago mensual')
  if (!props.prestamo?.numero_pagos) faltantes.push('Número de pagos')
  if (!props.prestamo?.fecha_inicio) faltantes.push('Fecha de inicio')
  if (!props.prestamo?.fecha_primer_pago) faltantes.push('Fecha del primer pago')
  if (faltantes.length) {
    notyf.open({ type: 'warning', message: `Faltan datos: ${faltantes.join(', ')}` })
  }
}

/* =========================
   Opciones de tamaño de papel
========================= */
const tamanosPapel = {
  carta: { nombre: 'Carta', dimensiones: '216mm 279mm', margin: '15mm 18mm 15mm 18mm' },
  oficio: { nombre: 'Oficio', dimensiones: '216mm 330mm', margin: '15mm 18mm 15mm 18mm' },
  a4: { nombre: 'A4', dimensiones: '210mm 297mm', margin: '15mm 18mm 15mm 18mm' }
}

const tamanoSeleccionado = ref('carta')

/* =========================
   Generación de PDF (print)
========================= */
const generarPDF = (tamano = 'carta') => {
  try {
    validarDatos()

    const contenidoPDF = generarContenidoPagare(tamano)
    const win = window.open('', '_blank', 'width=900,height=1100')
    if (!win) {
      notyf.error('Habilita las ventanas emergentes para generar el PDF.')
      return
    }
    win.document.write(contenidoPDF)
    win.document.close()
    setTimeout(() => { win.print() }, 600)
  } catch (error) {
    console.error('Error generando PDF:', error)
    notyf.error('Error al generar el pagaré PDF')
  }
}

const generarContenidoPagare = (tamano = 'carta') => {
  // Datos seguros y formateados
  const folio = `PAG-${String(props.prestamo?.id ?? '').padStart(6, '0')}`
  const clienteNombre = props.cliente?.nombre_razon_social || 'DEUDOR NO ESPECIFICADO'
  const clienteDomicilio = props.cliente?.direccion_completa || 'Domicilio del deudor no especificado'
  const clienteRfc = props.cliente?.rfc || 'RFC no especificado'
  const monto = formatearMoneda(props.prestamo?.monto_prestado)
  const pagoMensual = formatearMoneda(props.prestamo?.pago_periodico)
  const fechaActual = formatearFecha(props.fecha_actual)
  const primerPago = fechaPrimerPago.value
  const vencimiento = fechaVencimiento.value
  const numeroPagos = props.prestamo?.numero_pagos ?? 'N/D'
  const tasa = (props.tasa_mensual ?? 0).toFixed(2)

  // Información empresarial real
  const empresaNombre = props.empresa?.nombre || 'ACREEDOR NO ESPECIFICADO'
  const empresaDomicilio = props.empresa?.direccion || 'Domicilio del acreedor no especificado'
  const empresaRfc = props.empresa?.rfc || 'RFC no especificado'

  // Información adicional para el documento
  const fechaLimite = new Date()
  fechaLimite.setDate(fechaLimite.getDate() + 30) // 30 días para presentación
  const referenciaContrato = `Contrato de Préstamo No. ${props.prestamo?.id ?? 'N/A'}`

  return `
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>${folio} - PAGARÉ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    @page { size: ${tamanosPapel[tamano]?.dimensiones || '216mm 279mm'}; margin: ${tamanosPapel[tamano]?.margin || '20mm 15mm 20mm 15mm'}; }
    :root {
      --color-primary: #000000;
      --color-secondary: #333333;
      --color-border: #000000;
      --font-primary: 'Times New Roman', Times, serif;
    }
    * { box-sizing: border-box; }
    html, body {
      font-family: var(--font-primary);
      color: var(--color-primary);
      line-height: 1.4;
      font-size: 12px;
      margin: 0;
      padding: 0;
      background: #fff;
    }

    /* Encabezado fijo */
    header {
      position: fixed;
      top: 0;
      left: 0; right: 0;
      height: 50px;
      border-bottom: 1px solid var(--color-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 5mm;
    }
    header .brand {
      font-weight: bold;
      font-size: 14px;
      color: var(--color-primary);
    }
    header .folio {
      font-size: 12px;
      color: var(--color-secondary);
      font-weight: bold;
    }

    /* Pie fijo */
    footer {
      position: fixed;
      bottom: 0;
      left: 0; right: 0;
      height: 40px;
      border-top: 1px solid var(--color-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 5mm;
      color: var(--color-secondary);
      font-size: 9px;
    }

    main { padding: 60px 0 50px; }

    /* Título principal */
    .titulo {
      text-align: center;
      margin: 10mm 0 5mm;
      font-weight: bold;
      font-size: 18px;
      color: var(--color-primary);
      text-transform: uppercase;
      letter-spacing: 2px;
    }
    .subtitulo {
      text-align: center;
      color: var(--color-secondary);
      margin-bottom: 8mm;
      font-size: 11px;
    }

    /* Información del pagaré */
    .pagare-info {
      margin-bottom: 12mm;
      line-height: 1.6;
    }
    .pagare-info p {
      margin: 0 0 6px;
      text-align: justify;
    }

    /* Datos importantes */
    .datos-importantes {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10mm;
      margin: 12mm 0;
    }
    .datos-columna {
      line-height: 1.5;
    }
    .datos-label {
      font-size: 10px;
      color: var(--color-secondary);
      margin-bottom: 2px;
    }
    .datos-value {
      font-size: 12px;
      font-weight: bold;
      color: var(--color-primary);
    }

    /* Cláusulas */
    .clausulas {
      margin: 12mm 0;
      line-height: 1.5;
    }
    .clausulas ol {
      margin: 0;
      padding-left: 16px;
    }
    .clausulas li {
      margin: 6px 0;
      font-size: 11px;
    }

    /* Firmas */
    .firmas {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20mm;
      margin-top: 20mm;
    }
    .firma {
      text-align: center;
    }
    .firma-linea {
      border-bottom: 1px solid var(--color-border);
      width: 70%;
      margin: 20px auto 6px;
      height: 30px;
    }
    .firma-nombre {
      font-size: 11px;
      font-weight: bold;
      color: var(--color-primary);
      margin-bottom: 2px;
    }
    .firma-rol {
      font-size: 9px;
      color: var(--color-secondary);
    }
    .firma-datos {
      font-size: 8px;
      color: var(--color-secondary);
      margin-top: 2px;
    }

    /* Información adicional */
    .info-adicional {
      margin-top: 15mm;
      padding: 8px;
      background: #f5f5f5;
      border-radius: 4px;
      font-size: 9px;
      line-height: 1.4;
      text-align: center;
    }

    @media print {
      .no-print { display: none !important; }
      a { color: inherit; text-decoration: none; }
    }
  </style>
</head>
<body>
  <header>
    <div class="brand">${empresaNombre}</div>
    <div class="folio">${folio}</div>
  </header>

  <footer>
    <div>${empresaDomicilio}</div>
    <div>Generado: ${formatearFechaCompleta(new Date())} | Ref: ${referenciaContrato}</div>
  </footer>

  <main>
    <!-- Título principal -->
    <div class="titulo">PAGARÉ</div>
    <div class="subtitulo">Hermosillo, Sonora, México, ${fechaActual}</div>

    <!-- Promesa de pago principal -->
    <div class="pagare-info">
      <p>
        Por el presente PAGARÉ, yo <strong>${clienteNombre}</strong>, con domicilio en <strong>${clienteDomicilio}</strong>,
        me obligo incondicionalmente a pagar a la orden de <strong>${empresaNombre}</strong>, en <strong>${empresaDomicilio}</strong>,
        la cantidad de <strong>${monto}</strong> (${props.monto_letras}), más intereses ordinarios a razón de <strong>${tasa}% mensual</strong>,
        pagaderos mensualmente junto con cada exhibición de capital.
      </p>
    </div>

    <!-- Datos importantes -->
    <div class="datos-importantes">
      <div class="datos-columna">
        <div class="datos-item">
          <div class="datos-label">DEUDOR:</div>
          <div class="datos-value">${clienteNombre}</div>
          <div class="datos-label">Domicilio:</div>
          <div class="datos-value">${clienteDomicilio}</div>
          <div class="datos-label">RFC:</div>
          <div class="datos-value">${clienteRfc}</div>
        </div>
      </div>
      <div class="datos-columna">
        <div class="datos-item">
          <div class="datos-label">BENEFICIARIO:</div>
          <div class="datos-value">${empresaNombre}</div>
          <div class="datos-label">Domicilio:</div>
          <div class="datos-value">${empresaDomicilio}</div>
          <div class="datos-label">RFC:</div>
          <div class="datos-value">${empresaRfc}</div>
        </div>
      </div>
    </div>

    <!-- Información de pagos -->
    <div class="datos-importantes">
      <div class="datos-columna">
        <div class="datos-item">
          <div class="datos-label">MONTO DEL PRÉSTAMO:</div>
          <div class="datos-value">${monto}</div>
          <div class="datos-label">(${props.monto_letras})</div>
        </div>
        <div class="datos-item">
          <div class="datos-label">TASA DE INTERÉS:</div>
          <div class="datos-value">${tasa}% mensual</div>
        </div>
      </div>
      <div class="datos-columna">
        <div class="datos-item">
          <div class="datos-label">PAGO MENSUAL:</div>
          <div class="datos-value">${pagoMensual}</div>
          <div class="datos-label">(${props.pago_mensual_letras})</div>
        </div>
        <div class="datos-item">
          <div class="datos-label">NÚMERO DE PAGOS:</div>
          <div class="datos-value">${numeroPagos} pagos mensuales</div>
        </div>
      </div>
    </div>

    <!-- Fechas de pago -->
    <div class="datos-importantes">
      <div class="datos-columna">
        <div class="datos-item">
          <div class="datos-label">PRIMER PAGO:</div>
          <div class="datos-value">${primerPago}</div>
        </div>
      </div>
      <div class="datos-columna">
        <div class="datos-item">
          <div class="datos-label">VENCIMIENTO FINAL:</div>
          <div class="datos-value">${vencimiento}</div>
        </div>
      </div>
    </div>

    <!-- Cláusulas -->
    <div class="clausulas">
      <ol>
        <li><strong>Intereses moratorios:</strong> En caso de incumplimiento en el pago de cualquier mensualidad, se causarán intereses moratorios al doble de la tasa ordinaria pactada, sobre los saldos vencidos y no pagados, hasta su total liquidación.</li>
        <li><strong>Vencimiento anticipado:</strong> El incumplimiento de dos (2) pagos mensuales consecutivos o tres (3) pagos no consecutivos faculta al acreedor para dar por vencido anticipadamente el saldo total adeudado, exigiendo el pago inmediato del mismo.</li>
        <li><strong>Gastos y costas:</strong> Todos los gastos, costas, honorarios y demás accesorios que se originen por el incumplimiento de las obligaciones aquí contraídas, incluyendo gastos de cobranza judicial y extrajudicial, correrán a cargo exclusivo del deudor.</li>
        <li><strong>Jurisdicción:</strong> Para la interpretación, ejecución y cumplimiento del presente título, las partes se someten expresamente a la jurisdicción de los tribunales competentes de Hermosillo, Sonora, renunciando al fuero que por razón de su domicilio presente o futuro pudiera corresponderles.</li>
        <li><strong>Cesión:</strong> El presente pagaré es negociable y transmisible por endoso, confiriendo al tenedor legítimo todos los derechos aquí establecidos sin necesidad de notificación al deudor.</li>
      </ol>
    </div>

    <!-- Información adicional -->
    <div class="info-adicional">
      Este documento constituye un título ejecutivo conforme al artículo 170 de la Ley General de Títulos y Operaciones de Crédito.
      Requiere firma autógrafa del deudor para su validez legal.
    </div>

    <!-- Firmas -->
    <div class="firmas">
      <div class="firma">
        <div class="firma-linea"></div>
        <div class="firma-nombre">${clienteNombre}</div>
        <div class="firma-rol">SUSCRIPTOR (DEUDOR)</div>
        <div class="firma-datos">Firma autógrafa</div>
      </div>
      <div class="firma">
        <div class="firma-linea"></div>
        <div class="firma-nombre">${empresaNombre}</div>
        <div class="firma-rol">BENEFICIARIO (ACREEDOR)</div>
        <div class="firma-datos">Representante legal</div>
      </div>
    </div>
  </main>
</body>
</html>
`
}
</script>

<template>
  <Head title="Pagaré del Préstamo" />

  <div class="pagare-page min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-6 py-8">
      <!-- Header mejorado -->
      <div class="mb-8">
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-6 text-white">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold tracking-tight mb-2">Pagaré Digital</h1>
              <p class="text-gray-300 text-lg">Documento legal de obligación financiera</p>
              <div class="flex items-center gap-4 mt-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-400/30">
                  ✅ Documento Válido
                </span>
                <span class="text-sm text-gray-300">
                  Referencia: {{ formatearNumeroContrato() }}
                </span>
              </div>
            </div>
            <div class="hidden md:flex items-center gap-3">
              <div class="text-center">
                <div class="text-2xl font-bold">{{ prestamo.numero_pagos }}</div>
                <div class="text-xs text-gray-300 uppercase tracking-wide">Pagos</div>
              </div>
              <div class="w-px h-12 bg-gray-600"></div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-400">{{ formatearMoneda(prestamo.monto_prestado) }}</div>
                <div class="text-xs text-gray-300 uppercase tracking-wide">Monto Total</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Selector de tamaño de papel y acciones -->
        <div class="flex flex-wrap items-center justify-between gap-4 mt-4">
          <div class="flex items-center gap-3">
            <!-- Selector de tamaño de papel -->
            <div class="flex items-center gap-2">
              <label class="text-sm font-medium text-gray-700">Tamaño de papel:</label>
              <select
                v-model="tamanoSeleccionado"
                @change="generarPDF(tamanoSeleccionado)"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              >
                <option value="carta">Carta (8.5" × 11")</option>
                <option value="oficio">Oficio (8.5" × 13")</option>
                <option value="a4">A4 (210mm × 297mm)</option>
              </select>
            </div>

            <div class="w-px h-8 bg-gray-300"></div>

            <button
              @click="generarPDF(tamanoSeleccionado)"
              class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              Generar PDF
            </button>
            <button
              @click="validarDatos"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Validar Datos
            </button>
          </div>

          <div class="flex items-center gap-2">
            <Link
              :href="`/prestamos/${prestamo.id}`"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              Ver Préstamo
            </Link>
            <Link
              href="/prestamos"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              Volver a Préstamos
            </Link>
          </div>
        </div>
      </div>

      <!-- Vista previa mejorada -->
      <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 mb-2">Vista Previa del Documento</h2>
              <p class="text-gray-600">
                Versión preliminar del pagaré oficial • El PDF final incluye formato A4 profesional
              </p>
            </div>
            <div class="hidden md:flex items-center gap-2">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Documento Legal
              </span>
              <span class="text-sm text-gray-500">Versión {{ DOCUMENTO_INFO.version }}</span>
            </div>
          </div>
        </div>

        <div class="p-8">
          <div class="max-w-[900px] mx-auto">
            <!-- Encabezado del documento -->
            <div class="text-center mb-8">
              <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ DOCUMENTO_INFO.titulo }}</h1>
              <p class="text-gray-600">{{ DOCUMENTO_INFO.subtitulo }}</p>
              <div class="flex items-center justify-center gap-2 mt-3">
                <span class="text-sm text-gray-500">{{ empresa.direccion }}</span>
                <span class="text-gray-300">•</span>
                <span class="text-sm text-gray-500">{{ formatearFecha(fecha_actual) }}</span>
              </div>
            </div>

            <!-- Información del contrato -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <div class="flex items-center mb-3">
                  <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">Referencia</div>
                    <div class="text-xs text-gray-500">Número de contrato</div>
                  </div>
                </div>
                <div class="text-lg font-bold text-gray-900">{{ formatearNumeroContrato() }}</div>
              </div>

              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <div class="flex items-center mb-3">
                  <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 9l6-6m0 0v6m0-6h-6"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">Fecha de Inicio</div>
                    <div class="text-xs text-gray-500">Primer pago</div>
                  </div>
                </div>
                <div class="text-lg font-bold text-gray-900">{{ fechaPrimerPago }}</div>
              </div>

              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <div class="flex items-center mb-3">
                  <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">Lugar de Pago</div>
                    <div class="text-xs text-gray-500">Jurisdicción</div>
                  </div>
                </div>
                <div class="text-lg font-bold text-gray-900">{{ empresa.direccion }}</div>
              </div>
            </div>

            <!-- Resumen financiero mejorado -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
              <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 text-center">
                <div class="text-green-600 mb-2">
                  <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
                <div class="text-xs text-green-700 uppercase tracking-wide font-semibold mb-1">Monto Total</div>
                <div class="text-2xl font-extrabold text-green-800">{{ formatearMoneda(prestamo.monto_prestado) }}</div>
                <div class="text-xs text-green-600">{{ monto_letras }}</div>
              </div>

              <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200 text-center">
                <div class="text-blue-600 mb-2">
                  <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
                <div class="text-xs text-blue-700 uppercase tracking-wide font-semibold mb-1">Tasa Mensual</div>
                <div class="text-2xl font-extrabold text-blue-800">{{ tasa_mensual.toFixed(2) }}%</div>
                <div class="text-xs text-blue-600">Interés ordinario</div>
              </div>

              <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200 text-center">
                <div class="text-purple-600 mb-2">
                  <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="text-xs text-purple-700 uppercase tracking-wide font-semibold mb-1">Pago Mensual</div>
                <div class="text-2xl font-extrabold text-purple-800">{{ formatearMoneda(prestamo.pago_periodico) }}</div>
                <div class="text-xs text-purple-600">{{ pago_mensual_letras }}</div>
              </div>

              <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200 text-center">
                <div class="text-orange-600 mb-2">
                  <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                  </svg>
                </div>
                <div class="text-xs text-orange-700 uppercase tracking-wide font-semibold mb-1">Total de Pagos</div>
                <div class="text-2xl font-extrabold text-orange-800">{{ prestamo.numero_pagos }}</div>
                <div class="text-xs text-orange-600">Mensualidades</div>
              </div>
            </div>

            <!-- Información de las partes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  Información del Deudor
                </h3>
                <div class="space-y-3">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Nombre completo</div>
                    <div class="text-lg font-bold text-gray-900">{{ cliente.nombre_razon_social }}</div>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-700">Domicilio</div>
                    <div class="text-sm font-medium text-gray-900">{{ cliente.direccion_completa || 'Domicilio no especificado' }}</div>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-700">Fecha de Vencimiento</div>
                    <div class="text-lg font-bold text-gray-900">{{ fechaVencimiento }}</div>
                  </div>
                </div>
              </div>

              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                  Información del Acreedor
                </h3>
                <div class="space-y-3">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Beneficiario</div>
                    <div class="text-lg font-bold text-gray-900">{{ empresa.nombre }}</div>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-700">Lugar de Pago</div>
                    <div class="text-lg font-bold text-gray-900">{{ empresa.direccion }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Texto legal mejorado -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Compromiso de Pago
              </h3>
              <p class="text-gray-800 leading-relaxed mb-4">
                Yo, <strong>{{ cliente.nombre_razon_social }}</strong>, con domicilio en <strong>{{ cliente.direccion_completa || 'Domicilio no especificado' }}</strong>,
                me obligo incondicionalmente a pagar a la orden de <strong>{{ empresa.nombre }}</strong> la cantidad
                de <strong>{{ formatearMoneda(prestamo.monto_prestado) }}</strong> (${{ monto_letras }}), más intereses ordinarios a razón de <strong>{{ tasa_mensual.toFixed(2) }}%</strong> mensual,
                pagaderos mensualmente junto con cada exhibición de capital.
              </p>
              <p class="text-gray-800 leading-relaxed">
                Los pagos se realizarán mensualmente a partir del <strong>{{ fechaPrimerPago }}</strong>,
                hasta completar un total de <strong>{{ prestamo.numero_pagos }}</strong> pagos,
                conforme al plan de pagos establecido.
              </p>
            </div>

            <!-- Firmas visuales mejoradas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
              <div class="text-center p-6 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                <div class="w-3/4 mx-auto h-16 border-b-2 border-gray-700 mb-4"></div>
                <div class="text-lg font-semibold text-gray-900">{{ cliente.nombre_razon_social }}</div>
                <div class="text-sm text-gray-600">Deudor(a) — Firma autógrafa</div>
              </div>
              <div class="text-center p-6 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                <div class="w-3/4 mx-auto h-16 border-b-2 border-gray-700 mb-4"></div>
                <div class="text-lg font-semibold text-gray-900">{{ empresa.nombre_comercial }}  {{ empresa.nombre }}</div>
                <div class="text-sm text-gray-600">Acreedor — Representante legal</div>
              </div>
            </div>

            <!-- Información adicional -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
              <div class="flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-blue-900">Información Importante</h3>
              </div>
              <p class="text-blue-800 mb-4">
                Este documento constituye un título ejecutivo que faculta al tenedor legítimo para exigir el pago
                por la vía ejecutiva correspondiente. El incumplimiento generará intereses moratorios y podrá
                resultar en el vencimiento anticipado de la obligación.
              </p>
              <div class="flex items-center justify-center gap-4 text-sm text-blue-700">
                <span>✓ Documento Legal Válido</span>
                <span>✓ Protegido por Legislación Mexicana</span>
                <span>✓ Confidencial y Privado</span>
              </div>
            </div>

            <!-- Footer de la vista previa -->
            <div class="text-center mt-8 pt-6 border-t border-gray-200">
              <p class="text-sm text-gray-500">
                Esta es una vista previa • El documento PDF oficial incluye formato A4 con encabezado y pie de página profesionales
              </p>
              <p class="text-xs text-gray-400 mt-2">
                Generado el {{ formatearFechaCompleta(new Date()) }} • Versión {{ DOCUMENTO_INFO.version }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navegación inferior mejorada -->
      <div class="flex justify-center mt-8">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <div class="flex flex-col items-center gap-4">
            <!-- Selector de tamaño para el botón inferior -->
            <div class="flex items-center gap-3">
              <label class="text-sm font-medium text-gray-700">Tamaño:</label>
              <select
                v-model="tamanoSeleccionado"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              >
                <option value="carta">📄 Carta (8.5" × 11")</option>
                <option value="oficio">📃 Oficio (8.5" × 13")</option>
                <option value="a4">📋 A4 (210mm × 297mm)</option>
              </select>
            </div>

            <button
              @click="generarPDF(tamanoSeleccionado)"
              class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-lg font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              Generar Documento PDF Oficial
            </button>
            <div class="text-center text-gray-600">
              <div class="font-medium">Documento profesional listo para impresión</div>
              <div class="text-sm text-gray-500">Tamaño seleccionado: {{ tamanosPapel[tamanoSeleccionado]?.nombre }} • Diseño ejecutivo</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pagare-page { min-height: 100vh; background-color: #f9fafb; }
</style>
