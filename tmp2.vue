const submitAjuste = async () => {
  ajusteError.value = ''
  const a = ajuste.value
  if (!a.empleadoId) {
    ajusteError.value = 'Seleccione un empleado'
    return
  }
  if (!Number.isInteger(a.dias) || a.dias < -365 || a.dias > 365) {
    ajusteError.value = 'Ingrese un número de días entre -365 y 365'
    return
  }
  ajusteLoading.value = true
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const res = await fetch(route('registro-vacaciones.ajustar', a.empleadoId), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ dias: a.dias, anio: a.anio, motivo: a.motivo })
    })
    if (!res.ok) {
      const data = await res.json().catch(() => ({}))
      throw new Error(data?.message || 'No se pudo aplicar el ajuste')
    }
    closeAjuste()
    notyf.success('Ajuste aplicado correctamente')
    // Refrescar listado para reflejar cambios, si aplica
    router.reload({ only: ['vacaciones', 'stats'] })
  } catch (e) {
    ajusteError.value = e.message
    notyf.error(ajusteError.value)
  } finally {
    ajusteLoading.value = false
  }
}

const applyFilters = () => {
  router.get(route('vacaciones.index'), {
    empleado: filters.value.empleado,
    estado: filters.value.estado,
    fecha_desde: filters.value.fecha_desde,
    fecha_hasta: filters.value.fecha_hasta,
    page: 1
  }, { preserveState: true, preserveScroll: true })
}

const aprobarVacacion = (vacacion) => {
  if (confirm('¿Estás seguro de que deseas aprobar esta solicitud de vacaciones?')) {
    router.post(route('vacaciones.aprobar', vacacion.id), {
      observaciones: ''
    }, {
      onSuccess: () => {
        notyf.success('Vacaciones aprobadas exitosamente')
      },
      onError: () => {
        notyf.error('Error al aprobar las vacaciones')
      }
    })
  }
}

const rechazarVacacion = (vacacion) => {
  const observaciones = prompt('Ingresa el motivo del rechazo (opcional):')
  if (confirm('¿Estás seguro de que deseas rechazar esta solicitud de vacaciones?')) {
    router.post(route('vacaciones.rechazar', vacacion.id), {
      observaciones: observaciones || ''
    }, {
      onSuccess: () => {
        notyf.success('Vacaciones rechazadas')
      },
      onError: () => {
        notyf.error('Error al rechazar las vacaciones')
      }
    })
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  try {
    return new Date(date).toLocaleDateString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch {
    return 'Fecha inválida'
  }
}

const getEstadoClasses = (estado) => {
  const classes = {
    'pendiente': 'bg-yellow-100 text-yellow-700',
    'aprobada': 'bg-green-100 text-green-700',
    'rechazada': 'bg-red-100 text-red-700',
  }
  return classes[estado] || 'bg-gray-100 text-gray-700'
}

const getEstadoLabel = (estado) => {
  const labels = {
    'pendiente': 'Pendiente',
    'aprobada': 'Aprobada',
    'rechazada': 'Rechazada',
  }
  return labels[estado] || 'Desconocido'
}

const getPageNumbers = () => {
  const currentPage = props.vacaciones.current_page
  const lastPage = props.vacaciones.last_page
  const pages = []

  for (let i = Math.max(1, currentPage - 2); i <= Math.min(lastPage, currentPage + 2); i++) {
    pages.push(i)
  }

  return pages
}

const changePage = (page) => {
  router.get(route('vacaciones.index'), {
    ...filters.value,
    page: page
  }, { preserveState: true, preserveScroll: true })
}

const crearParaEmpleado = () => {
  if (empleadoSeleccionado.value) {
    router.visit(route('vacaciones.create-para-empleado', empleadoSeleccionado.value))
    showCrearParaEmpleado.value = false
    empleadoSeleccionado.value = ''
  }
}
</script>

<style scoped>
.vacaciones-index {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
