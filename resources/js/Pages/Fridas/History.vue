<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    ventas: Object,
    metrics: Object,
    filters: Object,
    categorias: Array,
});

const search = ref(props.filters.search || '');
const tipo_producto = ref(props.filters.tipo_producto || '');
const metodo_pago = ref(props.filters.metodo_pago || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');

// Selected sale for detail modal
const selectedVenta = ref(null);
const showModal = ref(false);

const applyFilters = () => {
    router.get(route('fridas.history'), {
        search: search.value,
        tipo_producto: tipo_producto.value,
        metodo_pago: metodo_pago.value,
        date_from: date_from.value,
        date_to: date_to.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    tipo_producto.value = '';
    metodo_pago.value = '';
    date_from.value = '';
    date_to.value = '';
    applyFilters();
};

const openDetails = (venta) => {
    selectedVenta.value = venta;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedVenta.value = null;
};

const deleteVenta = (id) => {
    if (confirm('¿Estás seguro de eliminar este registro de venta Fridas? Esta acción no se puede deshacer.')) {
        router.delete(route('fridas.destroy', id), {
            preserveScroll: true,
        });
    }
};

const formatCurrency = (val) => {
    return Number(val || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleString('es-MX', {
        dateStyle: 'short',
        timeStyle: 'short'
    });
};

const getBadgeClass = (tipo) => {
    switch (tipo) {
        case 'Ropa':
            return 'bg-purple-100 text-purple-800 border-purple-200';
        case 'Accesorios':
            return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'Ropa interior':
            return 'bg-pink-100 text-pink-800 border-pink-200';
        case 'Sticker':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};
</script>

<template>
    <Head title="Historial - Fridas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 flex items-center gap-2">
                        <span class="inline-block w-3 h-8 bg-pink-500 rounded-full"></span>
                        Historial de Ventas — Fridas
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Registros y métricas independientes del módulo Fridas</p>
                </div>
                <div>
                    <Link
                        :href="route('fridas.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-sm font-semibold shadow transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Venta Fridas
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Alert notification -->
                <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <!-- Metrics Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-pink-100 text-pink-600 rounded-xl">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Ventas Registradas</p>
                            <h4 class="text-2xl font-extrabold text-gray-900 mt-1">{{ metrics.total_count }}</h4>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Ingreso Total</p>
                            <h4 class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatCurrency(metrics.total_revenue) }}</h4>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-2">Ventas por Categoría</p>
                        <div class="flex flex-wrap gap-1.5">
                            <div v-for="b in metrics.breakdown" :key="b.tipo_producto" class="text-xs px-2 py-1 bg-gray-50 border border-gray-200 rounded-lg flex items-center gap-1.5">
                                <span :class="['w-2 h-2 rounded-full', getBadgeClass(b.tipo_producto).includes('purple') ? 'bg-purple-500' : getBadgeClass(b.tipo_producto).includes('amber') ? 'bg-amber-500' : getBadgeClass(b.tipo_producto).includes('pink') ? 'bg-pink-500' : 'bg-blue-500']"></span>
                                <span class="font-medium text-gray-700">{{ b.tipo_producto }}:</span>
                                <span class="font-bold text-gray-900">{{ formatCurrency(b.total_monto) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Buscar</label>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Cliente o descripción..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Tipo Producto</label>
                            <select
                                v-model="tipo_producto"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todas las categorías</option>
                                <option v-for="cat in categorias" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Método Pago</label>
                            <select
                                v-model="metodo_pago"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos los métodos</option>
                                <option value="EFECTIVO">EFECTIVO</option>
                                <option value="TARJETA">TARJETA</option>
                                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Desde</label>
                            <input
                                type="date"
                                v-model="date_from"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                @change="applyFilters"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Hasta</label>
                            <input
                                type="date"
                                v-model="date_to"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                @change="applyFilters"
                            />
                        </div>
                    </div>

                    <div class="mt-3 flex justify-end gap-2">
                        <button
                            @click="resetFilters"
                            type="button"
                            class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                        >
                            Limpiar Filtros
                        </button>
                        <button
                            @click="applyFilters"
                            type="button"
                            class="px-4 py-1.5 text-xs font-semibold text-white bg-pink-600 hover:bg-pink-700 rounded-lg shadow transition"
                        >
                            Filtrar
                        </button>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4"># Folio</th>
                                    <th class="py-3 px-4">Fecha / Hora</th>
                                    <th class="py-3 px-4">Cliente</th>
                                    <th class="py-3 px-4">Registrado Por</th>
                                    <th class="py-3 px-4">Productos / Ítems</th>
                                    <th class="py-3 px-4 text-right">Total</th>
                                    <th class="py-3 px-4 text-center">Método Pago</th>
                                    <th class="py-3 px-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-if="ventas.data.length === 0">
                                    <td colspan="8" class="text-center py-8 text-gray-400">
                                        No se encontraron registros de ventas Fridas con los criterios ingresados.
                                    </td>
                                </tr>
                                <tr v-for="v in ventas.data" :key="v.id" class="hover:bg-pink-50/20 transition">
                                    <td class="py-3.5 px-4 font-bold text-gray-900">#FRIDA-{{ v.id }}</td>
                                    <td class="py-3.5 px-4 text-gray-600 text-xs">{{ formatDate(v.created_at) }}</td>
                                    <td class="py-3.5 px-4 text-gray-800 font-medium">
                                        {{ v.cliente_nombre || 'General' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 text-xs">
                                        {{ v.user?.name || '—' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="d in v.detalles"
                                                :key="d.id"
                                                :class="['inline-flex items-center px-2 py-0.5 rounded text-xs border font-medium', getBadgeClass(d.tipo_producto)]"
                                            >
                                                {{ d.tipo_producto }} ({{ d.cantidad }})
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-extrabold text-gray-900">
                                        {{ formatCurrency(v.total) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            {{ v.metodo_pago }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                @click="openDetails(v)"
                                                type="button"
                                                class="px-2.5 py-1 text-xs font-semibold text-pink-600 hover:text-pink-800 hover:bg-pink-50 rounded-md border border-pink-200 transition"
                                            >
                                                Ver Detalles
                                            </button>

                                            <button
                                                v-if="$page.props.auth.user.rol === 'Admin'"
                                                @click="deleteVenta(v.id)"
                                                type="button"
                                                class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition"
                                                title="Eliminar registro"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="ventas.links && ventas.links.length > 3" class="p-4 border-t border-gray-100 flex justify-end">
                        <div class="flex space-x-1">
                            <template v-for="(link, k) in ventas.links" :key="k">
                                <div v-if="link.url === null" class="px-3 py-1 text-sm text-gray-400 border rounded-md" v-html="link.label" />
                                <Link v-else class="px-3 py-1 text-sm border rounded-md transition hover:bg-pink-50 hover:text-pink-600" :class="{ 'bg-pink-600 text-white border-pink-600 hover:bg-pink-700 hover:text-white': link.active }" :href="link.url" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="showModal && selectedVenta" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-gray-100 space-y-5 animate-fade-in">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Detalle de Venta Fridas #FRIDA-{{ selectedVenta.id }}</h3>
                        <p class="text-xs text-gray-500">{{ formatDate(selectedVenta.created_at) }}</p>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl text-sm">
                    <div>
                        <span class="text-xs text-gray-500 font-medium">Cliente:</span>
                        <p class="font-semibold text-gray-800">{{ selectedVenta.cliente_nombre || 'General' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 font-medium">Atendió:</span>
                        <p class="font-semibold text-gray-800">{{ selectedVenta.user?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 font-medium">Método de Pago:</span>
                        <p class="font-semibold text-gray-800">{{ selectedVenta.metodo_pago }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 font-medium">Total:</span>
                        <p class="font-bold text-pink-600 text-base">{{ formatCurrency(selectedVenta.total) }}</p>
                    </div>
                    <div v-if="selectedVenta.notas" class="col-span-2">
                        <span class="text-xs text-gray-500 font-medium">Notas:</span>
                        <p class="text-gray-700 italic">{{ selectedVenta.notas }}</p>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-gray-800 mb-2">Desglose de Ítems</h4>
                    <div class="overflow-x-auto border border-gray-100 rounded-lg">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                <tr>
                                    <th class="py-2.5 px-3">Tipo</th>
                                    <th class="py-2.5 px-3">Descripción</th>
                                    <th class="py-2.5 px-3 text-center">Cant.</th>
                                    <th class="py-2.5 px-3 text-right">P. Unitario</th>
                                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="d in selectedVenta.detalles" :key="d.id">
                                    <td class="py-2.5 px-3">
                                        <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border', getBadgeClass(d.tipo_producto)]">
                                            {{ d.tipo_producto }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-700">{{ d.descripcion || '—' }}</td>
                                    <td class="py-2.5 px-3 text-center font-semibold">{{ d.cantidad }}</td>
                                    <td class="py-2.5 px-3 text-right text-gray-600">{{ formatCurrency(d.precio_unitario) }}</td>
                                    <td class="py-2.5 px-3 text-right font-bold text-gray-900">{{ formatCurrency(d.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        @click="closeModal"
                        type="button"
                        class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold text-sm rounded-lg transition"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
