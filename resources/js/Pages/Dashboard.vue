<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    logo: null,
});

const uploadLogo = () => {
    form.post(route('upload.logo'), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
        },
    });
};

const handleLogoChange = (e) => {
    form.logo = e.target.files[0];
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold text-gray-800">¡Bienvenido al panel de administración!</h3>
                        <p class="text-gray-500 mt-1">Desde aquí puedes configurar aspectos generales del sistema.</p>
                    </div>
                </div>

                <!-- Logo Upload Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Configuración de Marca</h3>
                        
                        <form @submit.prevent="uploadLogo" class="max-w-xl">
                            <div>
                                <InputLabel for="logo" value="Logo de la Aplicación" />
                                <div class="mt-2 flex items-center gap-4">
                                    <div class="h-16 w-16 bg-gray-50 border rounded-lg flex items-center justify-center p-2 shrink-0">
                                        <img src="/logo.png" @error="$event.target.style.display='none'" class="max-h-full max-w-full object-contain" />
                                    </div>
                                    
                                    <div class="flex-1">
                                        <input 
                                            type="file" 
                                            id="logo" 
                                            accept="image/*"
                                            @change="handleLogoChange"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition focus:outline-none"
                                        />
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.logo" />
                                <p class="mt-2 text-xs text-gray-500">Se recomienda una imagen cuadrada en formato PNG con fondo transparente.</p>
                            </div>

                            <div class="mt-6">
                                <PrimaryButton :class="{ 'opacity-25': form.processing || !form.logo }" :disabled="form.processing || !form.logo">
                                    Actualizar Logo
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
