<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" class="text-gray-300 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-white/5 border-white/10 text-white placeholder-gray-500 focus:border-red-500 focus:ring-red-500 rounded-xl transition-all duration-300"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="admin@example.com"
                />

                <InputError class="mt-2 text-red-400" :message="form.errors.email" />
            </div>

            <div class="mt-6">
                <InputLabel for="password" value="Contraseña" class="text-gray-300 font-medium" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full bg-white/5 border-white/10 text-white placeholder-gray-500 focus:border-red-500 focus:ring-red-500 rounded-xl transition-all duration-300"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2 text-red-400" :message="form.errors.password" />
            </div>

            <div class="mt-6 block">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox 
                        name="remember" 
                        v-model:checked="form.remember" 
                        class="bg-white/5 border-white/10 text-red-600 focus:ring-red-500 rounded"
                    />
                    <span class="ms-2 text-sm text-gray-400 group-hover:text-gray-300 transition-colors"
                        >Recordarme</span
                    >
                </label>
            </div>

            <div class="mt-8 flex flex-col gap-4">
                <PrimaryButton
                    class="w-full justify-center bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold py-3 rounded-xl shadow-[0_0_20px_rgba(220,38,38,0.3)] transition-all duration-300 border-none uppercase tracking-widest text-xs"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Entrar al Sistema
                </PrimaryButton>

                <div class="flex items-center justify-center">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-gray-500 hover:text-gray-300 transition-colors duration-300"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
