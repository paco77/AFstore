<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: 'success'
    },
    duration: {
        type: Number,
        default: 3000
    }
});

const visible = ref(true);

onMounted(() => {
    setTimeout(() => {
        visible.value = false;
    }, props.duration);
});
</script>

<template>
    <transition name="fade">
        <div v-if="visible" 
            class="fixed bottom-5 right-5 z-[100] px-6 py-3 rounded-lg shadow-2xl flex items-center gap-3 border transition-all duration-500"
            :class="{
                'bg-green-600 text-white border-green-700': type === 'success',
                'bg-red-600 text-white border-red-700': type === 'error',
                'bg-blue-600 text-white border-blue-700': type === 'info'
            }"
        >
            <span class="font-medium text-sm">{{ message }}</span>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
