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

const compressImage = (file, maxWidth = 800, maxHeight = 800, quality = 0.8) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height = Math.round(height * (maxWidth / width));
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = Math.round(width * (maxHeight / height));
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const mimeType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                
                canvas.toBlob((blob) => {
                    const compressedFile = new File([blob], file.name, {
                        type: mimeType,
                        lastModified: Date.now(),
                    });
                    resolve(compressedFile);
                }, mimeType, quality);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const handleLogoChange = async (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            form.logo = await compressImage(file, 500, 500); // 500x500 is enough for a logo
        } else {
            form.logo = file;
        }
    }
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
