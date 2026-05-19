<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center justify-center bg-zinc-950 px-4 py-6 sm:px-0"
    >
        <!-- Background Image with Overlay -->
        <div class="fixed inset-0 z-0 overflow-hidden">
            <img 
                src="/storage/images/logos2.png" 
                alt="Background" 
                class="h-full w-full object-cover blur-sm opacity-50 scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/40 to-black/80"></div>
        </div>

        <!-- Content Container -->
        <div class="relative z-10 flex w-full flex-col items-center">
            <div class="mb-8">
                <Link href="/">
                    <img src="/storage/images/logos2.png" alt="Logo" class="h-32 w-auto drop-shadow-[0_0_15px_rgba(255,255,255,0.3)] transition-transform hover:scale-110 duration-500" />
                </Link>
            </div>

            <div
                class="mt-6 w-full overflow-hidden border border-white/10 bg-black/40 px-8 py-10 shadow-2xl backdrop-blur-xl sm:max-w-md sm:rounded-3xl"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
