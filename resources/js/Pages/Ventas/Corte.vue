<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { BanknotesIcon, CreditCardIcon, ArrowsRightLeftIcon, CalculatorIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    totales: Object,
    total_global: Number,
    ventas_count: Number,
    fecha_inicio: String,
    fecha_fin: String
});

const form = useForm({
    observaciones: ''
});

const submitCorte = () => {
    if (confirm('¿Estás seguro de realizar el corte de caja? Esto vinculará todas las ventas pendientes.')) {
        form.post(route('cortes.store'));
    }
};
</script>

<template>
    <Head title="Realizar Corte de Caja" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Realizar Corte de Caja</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-8 border-b pb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Resumen de Ventas Pendientes</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ fecha_inicio ? 'Desde: ' + new Date(fecha_inicio).toLocaleString() : 'Sin ventas' }} 
                                    <span v-if="fecha_inicio"> - Hasta: {{ new Date(fecha_fin).toLocaleString() }}</span>
                                </p>
                            </div>
                            <div class="bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100">
                                <span class="text-indigo-700 font-bold text-lg">{{ ventas_count }} Ventas</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Efectivo -->
                            <div class="bg-green-50 p-6 rounded-2xl border border-green-100 flex items-center">
                                <div class="bg-green-100 p-3 rounded-full mr-4">
                                    <BanknotesIcon class="w-8 h-8 text-green-700" />
                                </div>
                                <div>
                                    <p class="text-sm text-green-700 font-medium uppercase tracking-wider">Efectivo</p>
                                    <p class="text-2xl font-black text-green-900">${{ totales.efectivo }}</p>
                                </div>
                            </div>

                            <!-- Tarjeta -->
                            <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 flex items-center">
                                <div class="bg-blue-100 p-3 rounded-full mr-4">
                                    <CreditCardIcon class="w-8 h-8 text-blue-700" />
                                </div>
                                <div>
                                    <p class="text-sm text-blue-700 font-medium uppercase tracking-wider">Tarjeta</p>
                                    <p class="text-2xl font-black text-blue-900">${{ totales.tarjeta }}</p>
                                </div>
                            </div>

                            <!-- Transferencia -->
                            <div class="bg-purple-50 p-6 rounded-2xl border border-purple-100 flex items-center">
                                <div class="bg-purple-100 p-3 rounded-full mr-4">
                                    <ArrowsRightLeftIcon class="w-8 h-8 text-purple-700" />
                                </div>
                                <div>
                                    <p class="text-sm text-purple-700 font-medium uppercase tracking-wider">Transf.</p>
                                    <p class="text-2xl font-black text-purple-900">${{ totales.transferencia }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-900 p-8 rounded-2xl text-white flex justify-between items-center mb-8 shadow-xl relative overflow-hidden">
                            <div class="absolute right-0 top-0 opacity-10">
                                <CalculatorIcon class="w-32 h-32" />
                            </div>
                            <div>
                                <h4 class="text-gray-400 uppercase text-xs font-bold tracking-widest">Total Global a Entregar</h4>
                                <div class="text-5xl font-black mt-1">${{ total_global }}</div>
                            </div>
                            <div class="text-right">
                                <span class="bg-white/20 px-3 py-1 rounded text-xs font-bold uppercase">Corte de Caja</span>
                            </div>
                        </div>

                        <form @submit.prevent="submitCorte">
                            <div class="mb-6">
                                <InputLabel for="observaciones" value="Observaciones / Comentarios" />
                                <textarea id="observaciones" v-model="form.observaciones" rows="3" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1" placeholder="Ej: Faltante de $10, sin novedad, etc."></textarea>
                            </div>

                            <div class="flex items-center justify-end">
                                <PrimaryButton :disabled="form.processing || ventas_count === 0" class="w-full sm:w-auto h-12 text-base px-10">
                                    Confirmar y Cerrar Turno
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
