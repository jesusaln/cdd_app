<template>
    <div class="cotizaciones-index">
      <h1>Listado de Cotizaciones</h1>

      <!-- Tabla de cotizaciones -->
      <table class="cotizaciones-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Productos</th>
            <th>Total</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cotizacion in cotizaciones" :key="cotizacion.id">
            <td>{{ cotizacion.id }}</td>
            <td>{{ cotizacion.cliente.nombre }}</td>
            <td>
              <ul>
                <li v-for="producto in cotizacion.productos" :key="producto.id">
                  {{ producto.nombre }} - ${{ producto.pivot.precio }} (Cantidad: {{ producto.pivot.cantidad }})
                </li>
              </ul>
            </td>
            <td>${{ cotizacion.total }}</td>
            <td>
              <button @click="editarCotizacion(cotizacion.id)">Editar</button>
              <button @click="eliminarCotizacion(cotizacion.id)">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Botón para crear nueva cotización -->
      <div class="actions">
        <inertia-link href="/cotizaciones/create" class="btn-create">Crear Nueva Cotización</inertia-link>
      </div>
    </div>
  </template>

  <script>
  import { Inertia } from "@inertiajs/inertia";

  export default {
    props: {
      cotizaciones: Array // Las cotizaciones se pasan desde el backend
    },
    methods: {
      // Método para redirigir a la página de edición
      editarCotizacion(id) {
        Inertia.get(`/cotizaciones/${id}/edit`);
      },
      // Método para eliminar una cotización
      eliminarCotizacion(id) {
        if (confirm("¿Estás seguro de que deseas eliminar esta cotización?")) {
          Inertia.delete(`/cotizaciones/${id}`);
        }
      }
    }
  };
  </script>

  <style scoped>
  .cotizaciones-index {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }

  .cotizaciones-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
  }

  .cotizaciones-table th,
  .cotizaciones-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }

  .cotizaciones-table th {
    background-color: #f4f4f4;
  }

  .actions {
    text-align: right;
  }

  .btn-create {
    display: inline-block;
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 5px;
  }

  .btn-create:hover {
    background-color: #218838;
  }
  </style>
