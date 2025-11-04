<script setup>
// Importar componentes necesarios
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import { computed, ref } from 'vue';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

const props = defineProps({
    confirmsTwoFactorAuthentication: Boolean, // Indica si se requiere confirmación para autenticación de dos factores
    sessions: Array, // Lista de sesiones activas del usuario
    almacenes: Array, // Lista de almacenes disponibles
    user: Object, // Información del usuario
});

// Notificaciones
const notyf = new Notyf({
  duration: 4000,
  position: { x: 'right', y: 'top' },
  types: [
    { type: 'success', background: '#10b981', icon: false },
    { type: 'error', background: '#ef4444', icon: false }
  ]
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Estado para almacén de venta
const almacenes = ref(props.almacenes || []);
const userData = computed(() => props.user || user.value);
const selectedAlmacen = ref(userData.value?.almacen_venta_id || '');

// Estadísticas del perfil
const profileStats = computed(() => ({
  totalSessions: props.sessions?.length || 0,
  twoFactorEnabled: user.value?.two_factor_secret ? true : false,
  emailVerified: user.value?.email_verified_at ? true : false,
  lastLogin: user.value?.last_login_at || null
}));

// Configuración del header
const headerConfig = {
  module: 'profile',
  title: 'Mi Perfil',
  subtitle: `Bienvenido, ${user.value?.name || 'Usuario'}`,
  showCreateButton: false,
  showStats: true
};

// Función para actualizar almacén de venta
const updateAlmacenVenta = async () => {
    try {
        const response = await fetch('/user/update-almacen-venta', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                almacen_venta_id: selectedAlmacen.value || null
            })
        });

        if (response.ok) {
            const data = await response.json();
            // Actualizar el usuario localmente
            if (userData.value) {
                userData.value.almacen_venta_id = selectedAlmacen.value;
                userData.value.almacen_venta = data.almacen_venta;
            }
            notyf.success('Almacén de venta actualizado correctamente');
        } else {
            throw new Error('Error al actualizar');
        }
    } catch (error) {
        console.error('Error:', error);
        notyf.error('Error al actualizar el almacén de venta');
        // Revertir el cambio
        selectedAlmacen.value = userData.value?.almacen_venta_id || '';
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Mi Perfil" />

        <div class="profile-page min-h-screen bg-gray-50">
        <!-- Header personalizado -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-6 transition-all duration-300 hover:shadow-lg">
            <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
                <!-- Información del usuario -->
                <div class="flex flex-col gap-6 w-full lg:w-auto">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <img
                                :src="user.profile_photo_url"
                                :alt="user.name"
                                class="w-20 h-20 rounded-full border-4 border-blue-100 object-cover shadow-lg"
                            />
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-slate-900">{{ user.name }}</h1>
                            <p class="text-lg text-slate-600">{{ user.email }}</p>
                            <div class="flex items-center gap-4 mt-2">
                                <span v-if="profileStats.emailVerified" class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Email verificado
                                </span>
                                <span v-if="profileStats.twoFactorEnabled" class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    2FA activado
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas del perfil -->
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div class="group flex items-center gap-2 px-4 py-3 bg-blue-50 rounded-xl border border-blue-200 flex-shrink-0 hover:bg-blue-100 transition-all duration-200 cursor-default">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="font-medium text-blue-700">Sesiones activas:</span>
                            <span class="font-bold text-blue-900 text-lg">{{ profileStats.totalSessions }}</span>
                        </div>

                        <div class="group flex items-center gap-2 px-4 py-3 bg-green-50 rounded-xl border border-green-200 flex-shrink-0 hover:bg-green-100 transition-all duration-200 cursor-default">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span class="font-medium text-green-700">Estado de seguridad:</span>
                            <span class="font-bold text-green-900 text-lg">{{ profileStats.twoFactorEnabled ? 'Protegido' : 'Básico' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="max-w-8xl mx-auto px-6 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Información del Perfil -->
                <div v-if="$page.props.jetstream.canUpdateProfileInformation" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Información del Perfil</h3>
                    </div>
                    <UpdateProfileInformationForm :user="user" />
                </div>

                <!-- Seguridad -->
                <div v-if="$page.props.jetstream.canUpdatePassword" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Contraseña</h3>
                    </div>
                    <UpdatePasswordForm />
                </div>

                <!-- Autenticación de Dos Factores -->
                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Autenticación de Dos Factores</h3>
                    </div>
                    <TwoFactorAuthenticationForm :requires-confirmation="props.confirmsTwoFactorAuthentication" />
                </div>

                <!-- Almacén de Venta Predeterminado -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Almacén de Venta Predeterminado</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="almacen_venta" class="block text-sm font-medium text-gray-700 mb-2">
                                Selecciona tu almacén predeterminado para ventas
                            </label>
                            <select
                                v-model="selectedAlmacen"
                                @change="updateAlmacenVenta"
                                id="almacen_venta"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                            >
                                <option value="">Sin almacén predeterminado</option>
                                <option v-for="almacen in almacenes" :key="almacen.id" :value="almacen.id">
                                    {{ almacen.nombre }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Este almacén se seleccionará automáticamente al crear nuevas ventas.
                            </p>
                        </div>
                        <div v-if="userData.almacen_venta" class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm text-indigo-700">
                                    Almacén actual: <strong>{{ userData.almacen_venta.nombre }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sesiones del Navegador -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Sesiones del Navegador</h3>
                    </div>
                    <LogoutOtherBrowserSessionsForm :sessions="props.sessions" />
                </div>
            </div>

            <!-- Eliminar Cuenta -->
            <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                <div class="mt-8 bg-white rounded-xl shadow-sm border border-red-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-red-900">Eliminar Cuenta</h3>
                    </div>
                    <DeleteUserForm />
                </div>
            </template>
        </div>
    </div>
    </AppLayout>
</template>

<style scoped>
.profile-page {
  min-height: 100vh;
  background-color: #f9fafb;
}

.profile-page > * {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hover effects para las tarjetas */
.profile-page .bg-white {
  transition: all 0.3s ease;
}

.profile-page .bg-white:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Responsive */
@media (max-width: 640px) {
  .profile-page .max-w-8xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .profile-page h1 {
    font-size: 1.875rem;
  }

  .profile-page h3 {
    font-size: 1.125rem;
  }
}

/* Animaciones para los iconos */
.profile-page svg {
  transition: transform 0.2s ease;
}

.profile-page .group:hover svg {
  transform: scale(1.1);
}

/* Estilos para los badges de estado */
.profile-page .rounded-full {
  transition: all 0.2s ease;
}

.profile-page .rounded-full:hover {
  transform: scale(1.05);
}
</style>
