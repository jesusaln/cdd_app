<template>
  <div class="modal-overlay" v-if="isVisible" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Crear Nuevo Cliente</h2>
        <button @click="closeModal" class="close-btn">&times;</button>
      </div>

      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input
              type="text"
              id="nombre"
              v-model="cliente.nombre"
              required
              class="form-input"
            >
          </div>

          <div class="form-group">
            <label for="email">Email:</label>
            <input
              type="email"
              id="email"
              v-model="cliente.email"
              required
              class="form-input"
            >
          </div>

          <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input
              type="tel"
              id="telefono"
              v-model="cliente.telefono"
              class="form-input"
            >
          </div>

          <div class="form-group">
            <label for="empresa">Empresa:</label>
            <input
              type="text"
              id="empresa"
              v-model="cliente.empresa"
              class="form-input"
            >
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button @click="closeModal" class="btn btn-secondary">Cancelar</button>
        <button @click="submitForm" class="btn btn-primary">Crear Cliente</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CrearClienteModal',

  props: {
    isVisible: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      cliente: {
        nombre: '',
        email: '',
        telefono: '',
        empresa: ''
      }
    }
  },

  methods: {
    closeModal() {
      this.resetForm()
      this.$emit('close')
    },

    submitForm() {
      // Validación básica
      if (!this.cliente.nombre || !this.cliente.email) {
        alert('Por favor, completa los campos requeridos')
        return
      }

      // Emitir evento con los datos del cliente
      this.$emit('cliente-creado', { ...this.cliente })
      this.closeModal()
    },

    resetForm() {
      this.cliente = {
        nombre: '',
        email: '',
        telefono: '',
        empresa: ''
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #374151;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: 500;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.875rem;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background-color 0.2s;
}

.btn-secondary {
  background-color: #f3f4f6;
  color: #374151;
}

.btn-secondary:hover {
  background-color: #e5e7eb;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}
</style>
