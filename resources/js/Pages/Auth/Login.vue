<template>
    <!-- Configuración del título de la página -->
    <Head title="Iniciar sesión" />

    <!-- Componente principal de autenticación -->
    <AuthenticationCard>
        <!-- Slot para el logo personalizado -->
        <template #logo>
            <!-- Imagen del logo ubicada en /public/images/logo.png -->
            <img src="/images/logo.png" alt="Logo de la aplicación" class="w-150 h-30">
        </template>

        <!-- Mensaje de estado (por ejemplo, "Sesión iniciada correctamente") -->
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <!-- Formulario de inicio de sesión -->
        <form @submit.prevent="submit">
            <!-- Campo de correo electrónico -->
            <div>
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                    ref="emailInput"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Campo de contraseña -->
            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Opción para recordar sesión -->
            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Recordarme</span>
                </label>
            </div>

            <!-- Botones de "Olvidé mi contraseña" e "Iniciar sesión" -->
            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Iniciar sesión
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Definir las propiedades que recibe el componente
defineProps({
    canResetPassword: Boolean, // Indica si se permite restablecer la contraseña
    status: String, // Estado de la autenticación (por ejemplo, mensaje de éxito)
});

// Referencia al campo de correo electrónico para enfocarlo automáticamente
const emailInput = ref(null);

// Objeto de formulario para manejar los datos de inicio de sesión
const form = useForm({
    email: '', // Correo electrónico
    password: '', // Contraseña
    remember: false, // Recordar sesión
});

// Enfocar automáticamente el campo de correo electrónico cuando se carga la página
onMounted(() => {
    nextTick(() => {
        if (!document.activeElement || document.activeElement === document.body) {
            emailInput.value?.focus();
        }
    });
});

// Función para enviar el formulario de inicio de sesión
const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '', // Convertir "remember" a un valor adecuado
    })).post(route('login'), {
        onFinish: () => form.reset('password'), // Limpiar el campo de contraseña después de enviar
    });
};
</script>
