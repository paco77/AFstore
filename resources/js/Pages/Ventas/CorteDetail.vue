<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeftIcon, PrinterIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    corte: Object
});
</script>

<template>
    <Head title="Detalle de Corte" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <Link :href="route('cortes.index')" class="mr-4 text-gray-500 hover:text-gray-700">
                        <ChevronLeftIcon class="w-6 h-6" />
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle de Corte #{{ corte.id }}</h2>
                </div>
                <button @click="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    <PrinterIcon class="w-5 h-5 mr-2" /> Imprimir
                </button>
            </div>
        </template>

        <div class="py-12 printable">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- General Info -->
                    <div class="bg-white p-6 rounded-lg shadow sm:rounded-lg border-t-4 border-indigo-500">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Información General</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-400 capitalize">Usuario</p>
                                <p class="text-sm font-semibold text-gray-900">{{ corte.user.name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 capitalize">Tienda</p>
                                <p class="text-sm font-semibold text-gray-900">{{ corte.tienda.nombre }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 capitalize">Fecha Cierre</p>
                                <p class="text-sm font-semibold text-gray-900">{{ new Date(corte.created_at).toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Totals Breakdown -->
                    <div class="bg-white p-6 rounded-lg shadow sm:rounded-lg border-t-4 border-green-500">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Desglose de Totales</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Efectivo:</span>
                                <span class="text-sm font-bold text-gray-900">${{ corte.total_efectivo }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tarjeta:</span>
                                <span class="text-sm font-bold text-gray-900">${{ corte.total_tarjeta }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Transferencia:</span>
                                <span class="text-sm font-bold text-gray-900">${{ corte.total_transferencia }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t font-black text-lg">
                                <span>TOTAL:</span>
                                <span>${{ corte.total_global }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="bg-white p-6 rounded-lg shadow sm:rounded-lg border-t-4 border-yellow-500">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Observaciones</h3>
                        <p class="text-sm text-gray-700 italic">
                            {{ corte.observaciones || 'Sin observaciones registradas.' }}
                        </p>
                    </div>
                </div>

                <!-- Sales Linked to this Corte -->
                <div class="bg-white overflow-hidden shadow sm:rounded-lg">
                    <div class="p-6 bg-gray-50 border-b border-gray-200 font-bold text-gray-700 uppercase text-xs tracking-widest">
                        Ventas Incluidas en este Corte
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Venta ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Fecha/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="venta in corte.ventas" :key="venta.id">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">#{{ venta.id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ venta.cliente_nombre || 'General' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(venta.created_at).toLocaleString() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ venta.metodo_pago }}</td>
                                <td class="px-6 py-4 text-sm font-black text-gray-900 text-right">${{ venta.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    .printable {
        padding: 0 !important;
    }
    nav, header, button, .no-print {
        display: none !important;
    }
}
</style>
