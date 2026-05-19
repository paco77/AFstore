<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import TextInput from '@/Components/TextInput.vue';
import { CalendarIcon, MagnifyingGlassIcon, ArrowDownTrayIcon, EyeIcon, TrashIcon, XMarkIcon, CalculatorIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const props = defineProps({
    ventas: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');
const perPage = ref(props.filters.perPage || 20);

const selectedVenta = ref(null);
const showingDetailModal = ref(false);

watch([search, date_from, date_to, perPage], debounce(function () {
    router.get(route('ventas.history'), { 
        search: search.value,
        date_from: date_from.value,
        date_to: date_to.value,
        perPage: perPage.value
    }, { preserveState: true, replace: true });
}, 300));

const exportSales = () => {
    window.location.href = route('ventas.export', {
        date_from: date_from.value,
        date_to: date_to.value
    });
};

const showDetails = (venta) => {
    axios.get(route('ventas.show', venta.id)).then(response => {
        selectedVenta.value = response.data;
        showingDetailModal.value = true;
    });
};

const deleteVenta = (venta) => {
    if (confirm('¿Estás seguro de eliminar esta venta? El stock se restaurará automáticamente.')) {
        router.delete(route('ventas.destroy', venta.id));
    }
};
</script>

<template>
    <Head title="Historial de Ventas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Historial de Ventas</h2>
                <Link :href="route('cortes.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition ease-in-out duration-150 shadow-lg shadow-indigo-200">
                    <CalculatorIcon class="w-5 h-5 mr-2" /> Realizar Corte
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-[1664px] mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Filters -->
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Cliente</label>
                                <div class="relative">
                                    <TextInput v-model="search" type="text" class="w-full pl-10" placeholder="Nombre del cliente..." />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                                <div class="relative">
                                    <TextInput v-model="date_from" type="date" class="w-full pl-10" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <CalendarIcon class="h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                                <div class="relative">
                                    <TextInput v-model="date_to" type="date" class="w-full pl-10" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <CalendarIcon class="h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <button @click="exportSales" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <ArrowDownTrayIcon class="w-5 h-5 mr-2" /> Exportar CSV
                                </button>
                                <div class="flex items-center gap-2 mt-2 w-full justify-end">
                                    <span class="text-sm text-gray-500 whitespace-nowrap">Por página:</span>
                                    <select v-model="perPage" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm p-1 ml-auto">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                        <table class="min-w-full divide-y divide-gray-200 relative">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tienda</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="venta in ventas.data" :key="venta.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">#{{ venta.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(venta.created_at).toLocaleString() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ venta.cliente_nombre || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ venta.tienda ? venta.tienda.nombre : 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">${{ venta.total }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ venta.metodo_pago }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        <button @click="showDetails(venta)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-full hover:bg-indigo-100 transition" title="Ver Detalle">
                                            <EyeIcon class="w-5 h-5" />
                                        </button>
                                        <button v-if="$page.props.auth.user.rol === 'Admin'" @click="deleteVenta(venta)" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-full hover:bg-red-100 transition" title="Eliminar">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="ventas.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">No se encontraron ventas con los filtros seleccionados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200 flex justify-end" v-if="ventas.links.length > 3">
                        <div class="flex space-x-1">
                            <template v-for="(link, k) in ventas.links" :key="k">
                                <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400 border rounded" v-html="link.label" />
                                <Link v-else class="px-4 py-2 text-sm border rounded hover:bg-indigo-50" :class="{ 'bg-indigo-600 text-white': link.active }" :href="link.url" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <Modal :show="showingDetailModal" @close="showingDetailModal = false">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Detalle de Venta #{{ selectedVenta?.id }}</h3>
                    <button @click="showingDetailModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div v-if="selectedVenta" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Cliente</p>
                            <p class="text-sm font-semibold text-gray-900">{{ selectedVenta.cliente_nombre || 'General' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Fecha</p>
                            <p class="text-sm font-semibold text-gray-900">{{ new Date(selectedVenta.created_at).toLocaleString() }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Metodo de Pago</p>
                            <p class="text-sm font-semibold text-gray-900">{{ selectedVenta.metodo_pago }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Vendedor</p>
                            <p class="text-sm font-semibold text-gray-900">{{ selectedVenta.user?.name }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto border rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Producto</th>
                                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Cant.</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Precio</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Desc.</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="detalle in selectedVenta.detalles" :key="detalle.id">
                                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                        {{ detalle.product_almacen?.nombre }}
                                        <p class="text-xs text-gray-400">{{ detalle.product_almacen?.clave }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ detalle.cantidad }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ detalle.precio_unitario }}</td>
                                    <td class="px-4 py-3 text-sm text-red-600 text-right">{{ detalle.descuento_porcentaje }}%</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-bold text-right">${{ detalle.subtotal }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 font-black">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right text-gray-900 text-lg uppercase tracking-widest">Total:</td>
                                    <td class="px-4 py-3 text-right text-indigo-700 text-xl font-black">${{ selectedVenta.total }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
