<template>
  <div class="notification-bell-container">
    <!-- Botón de la campana -->
    <button
      @click="toggleDropdown"
      class="notification-bell-btn"
      type="button"
    >
      🔔
      <!-- Badge del contador -->
      <span
        v-if="unreadCount > 0"
        class="notification-badge"
      >
        {{ unreadCount }}
      </span>
    </button>

    <!-- Dropdown de notificaciones -->
    <div
      v-if="showDropdown"
      class="notifications-dropdown"
    >
      <!-- Header del dropdown -->
      <div class="dropdown-header">
        <h4>Notificaciones</h4>
        <button @click="closeDropdown" class="close-btn">×</button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading">
        Cargando notificaciones...
      </div>

      <!-- Lista de notificaciones -->
      <div v-else class="notifications-list">
        <div v-if="notifications.length === 0" class="no-notifications">
          No hay notificaciones
        </div>

        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="notification-item"
          :class="{ 'unread': !notification.read && notification.read !== undefined }"
          @click="markAsRead(notification.id)"
        >
          <div class="notification-title">{{ notification.title }}</div>
          <div class="notification-message">{{ notification.message }}</div>
          <div class="notification-time">{{ formatTime(notification.created_at) }}</div>
        </div>
      </div>

      <!-- Footer del dropdown -->
      <div v-if="notifications.length > 0" class="dropdown-footer">
        <button @click="markAllAsRead" class="mark-all-read-btn">
          Marcar todas como leídas
        </button>
      </div>
    </div>

    <!-- Overlay para cerrar el dropdown -->
    <div
      v-if="showDropdown"
      class="dropdown-overlay"
      @click="closeDropdown"
    ></div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'NotificationBell',

  data() {
    return {
      notifications: [],
      unreadCount: 0,
      showDropdown: false,
      loading: false
    }
  },

  mounted() {
    console.log('NotificationBell component mounted');
    this.loadUnreadCount();

    // Opcional: Actualizar contador cada 30 segundos
    this.unreadCountInterval = setInterval(() => {
      this.loadUnreadCount();
    }, 30000);
  },

  beforeUnmount() {
    if (this.unreadCountInterval) {
      clearInterval(this.unreadCountInterval);
    }
  },

  methods: {
    async toggleDropdown() {
      console.log('Button clicked! Current state:', this.showDropdown);

      if (!this.showDropdown) {
        // Abrir dropdown
        this.showDropdown = true;
        console.log('Opening dropdown');

        // Cargar notificaciones si no están cargadas
        if (this.notifications.length === 0) {
          await this.loadNotifications();
        }
      } else {
        // Cerrar dropdown
        this.closeDropdown();
      }

      console.log('New showDropdown state:', this.showDropdown);
    },

    closeDropdown() {
      console.log('Closing dropdown');
      this.showDropdown = false;
    },

    async loadNotifications() {
      this.loading = true;

      try {
        console.log('=== LOADING NOTIFICATIONS ===');
        console.log('showDropdown before API call:', this.showDropdown);

        const response = await axios.get('/notifications');

        console.log('API Response status:', response.status);
        console.log('API Response data:', response.data);

        // Asignar datos SIN modificar showDropdown
        this.notifications = response.data.notifications || [];
        this.unreadCount = response.data.unread_count || 0;

        console.log('=== AFTER ASSIGNMENT ===');
        console.log('Notifications loaded:', this.notifications.length);
        console.log('Unread count:', this.unreadCount);
        console.log('showDropdown after assignment:', this.showDropdown); // NO debe cambiar

        // Debug de cada notificación
        this.notifications.forEach((notification, index) => {
          console.log(`Notification ${index}:`, notification);
        });

      } catch (error) {
        console.error('Error loading notifications:', error);
      } finally {
        this.loading = false;
      }
    },

    async loadUnreadCount() {
      try {
        console.log('Loading unread count...');
        const response = await axios.get('/notifications/unread-count');

        console.log('Unread count response:', response.data);
        this.unreadCount = response.data.unread_count || 0;
        console.log('Unread count set to:', this.unreadCount);

      } catch (error) {
        console.error('Error loading unread count:', error);
      }
    },

    async markAsRead(notificationId) {
      try {
        await axios.post(`/notifications/${notificationId}/read`);

        // Actualizar la notificación localmente
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
          notification.read = true;
          notification.read_at = new Date().toISOString();
        }

        // Actualizar contador
        await this.loadUnreadCount();

      } catch (error) {
        console.error('Error marking notification as read:', error);
      }
    },

    async markAllAsRead() {
      try {
        // Intentar diferentes endpoints comunes
        let response;

        try {
          response = await axios.post('/notifications/mark-all-read');
        } catch (error) {
          if (error.response?.status === 405 || error.response?.status === 404) {
            // Intentar endpoints alternativos
            try {
              response = await axios.post('/notifications/read-all');
            } catch (error2) {
              response = await axios.put('/notifications/mark-read');
            }
          } else {
            throw error;
          }
        }

        // Actualizar todas las notificaciones localmente
        this.notifications.forEach(notification => {
          notification.read = true;
          notification.read_at = new Date().toISOString();
        });

        // Actualizar contador
        this.unreadCount = 0;

        console.log('All notifications marked as read');

      } catch (error) {
        console.error('Error marking all notifications as read:', error);
        alert('Error al marcar las notificaciones como leídas');
      }
    },

    formatTime(timestamp) {
      if (!timestamp) return '';

      const date = new Date(timestamp);
      const now = new Date();
      const diff = now - date;

      // Menos de 1 minuto
      if (diff < 60000) {
        return 'Hace un momento';
      }

      // Menos de 1 hora
      if (diff < 3600000) {
        const minutes = Math.floor(diff / 60000);
        return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
      }

      // Menos de 24 horas
      if (diff < 86400000) {
        const hours = Math.floor(diff / 3600000);
        return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
      }

      // Más de 24 horas
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
}
</script>

<style scoped>
.notification-bell-container {
  position: relative;
  display: inline-block;
}

.notification-bell-btn {
  position: relative;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.notification-bell-btn:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #ff4444;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 12px;
  font-weight: bold;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.notifications-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  width: 360px;
  max-height: 400px;
  z-index: 1001;
  overflow: hidden;
  margin-top: 5px;
}

.dropdown-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  background: transparent;
}

.dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  background: #f8f9fa;
}

.dropdown-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}

.close-btn:hover {
  background-color: #e9ecef;
}

.loading {
  padding: 20px;
  text-align: center;
  color: #666;
}

.notifications-list {
  max-height: 300px;
  overflow-y: auto;
}

.no-notifications {
  padding: 40px 20px;
  text-align: center;
  color: #666;
  font-style: italic;
}

.notification-item {
  padding: 15px 20px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item:hover {
  background-color: #f8f9fa;
}

.notification-item.unread {
  background-color: #e3f2fd;
  border-left: 4px solid #2196f3;
}

.notification-item.unread::before {
  content: '';
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  background: #2196f3;
  border-radius: 50%;
}

.notification-title {
  font-weight: 600;
  font-size: 14px;
  color: #333;
  margin-bottom: 4px;
  line-height: 1.3;
}

.notification-message {
  font-size: 13px;
  color: #666;
  margin-bottom: 6px;
  line-height: 1.4;
}

.notification-time {
  font-size: 11px;
  color: #999;
}

.dropdown-footer {
  padding: 12px 20px;
  border-top: 1px solid #eee;
  background: #f8f9fa;
}

.mark-all-read-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
  width: 100%;
  transition: background-color 0.2s;
}

.mark-all-read-btn:hover {
  background: #0056b3;
}

/* Scrollbar personalizada */
.notifications-list::-webkit-scrollbar {
  width: 6px;
}

.notifications-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.notifications-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.notifications-list::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
