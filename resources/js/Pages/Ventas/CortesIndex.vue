<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PlusIcon, EyeIcon } from '@heroicons/vue/24/outline';

import { ref, watch } from 'vue';

const props = defineProps({
    cortes: Object,
    filters: Object
});

const perPage = ref(props.filters?.perPage || 10);

watch(perPage, function (value) {
    router.get(route('cortes.index'), { perPage: value }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Cortes de Caja" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Historial de Cortes de Caja</h2>
                <Link :href="route('cortes.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition ease-in-out duration-150">
                    <PlusIcon class="w-5 h-5 mr-2" /> Realizar Corte
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-[1664px] mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 flex justify-end">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Registros por página:</span>
                            <select v-model="perPage" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                        <table class="min-w-full divide-y divide-gray-200 relative">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Cierre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tienda</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Global</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="corte in cortes.data" :key="corte.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ corte.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(corte.created_at).toLocaleString() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ corte.user ? corte.user.name : 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ corte.tienda ? corte.tienda.nombre : 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">${{ corte.total_global }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('cortes.show', corte.id)" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                            <EyeIcon class="w-5 h-5 mr-1" /> Ver Detalle
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="cortes.data.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">No se han realizado cortes de caja.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200 flex justify-end" v-if="cortes.links.length > 3">
                        <div class="flex space-x-1">
                            <template v-for="(link, k) in cortes.links" :key="k">
                                <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400 border rounded" v-html="link.label" />
                                <Link v-else class="px-4 py-2 text-sm border rounded hover:bg-indigo-50" :class="{ 'bg-indigo-600 text-white': link.active }" :href="link.url" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
