<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { PencilSquareIcon, TrashIcon, PlusIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    compras: Object,
    tiendas: Array,
    filters: Object,
    totalGastado: Number,
});

const search = ref(props.filters.search || '');
const fechaInicio = ref(props.filters.fecha_inicio || '');
const fechaFin = ref(props.filters.fecha_fin || '');

watch([search, fechaInicio, fechaFin], debounce(function ([searchValue, inicioValue, finValue]) {
    router.get(route('compras.index'), {
        search: searchValue,
        fecha_inicio: inicioValue,
        fecha_fin: finValue
    }, { preserveState: true, replace: true });
}, 300));

const showingModal = ref(false);
const editingCompra = ref(null);

const form = useForm({
    concepto: '',
    tipo_gasto: '',
    monto: '',
    fecha_compra: new Date().toISOString().split('T')[0],
    tienda_id: '',
});

const openCreateModal = () => {
    editingCompra.value = null;
    form.reset();
    form.clearErrors();
    form.fecha_compra = new Date().toISOString().split('T')[0];
    showingModal.value = true;
};

const openEditModal = (compra) => {
    editingCompra.value = compra;
    form.concepto = compra.concepto;
    form.tipo_gasto = compra.tipo_gasto;
    form.monto = compra.monto;
    form.fecha_compra = compra.fecha_compra;
    form.tienda_id = compra.tienda_id || '';
    form.clearErrors();
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    setTimeout(() => form.reset(), 300);
};

const saveCompra = () => {
    if (editingCompra.value) {
        form.put(route('compras.update', editingCompra.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('compras.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCompra = (compra) => {
    if (confirm('¿Estás seguro de que deseas eliminar este registro de gasto?')) {
        router.delete(route('compras.destroy', compra.id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return;
    const date = new Date(dateString + 'T12:00:00'); // Prevent timezone shift
    return new Intl.DateTimeFormat('es-MX', { year: 'numeric', month: 'short', day: 'numeric' }).format(date);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
};

const tiposDeGasto = [
    'Mercancía',
    'Insumos',
    'Servicios (Luz, Agua, Internet)',
    'Renta',
    'Papelería y Oficina',
    'Publicidad',
    'Mantenimiento',
    'Nómina / Pago a Empleados',
    'Viáticos',
    'Otros'
];
</script>

<template>
    <Head title="Compras y Gastos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Compras y Gastos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                    
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <!-- Filters -->
                            <div class="flex flex-col sm:flex-row w-full md:w-auto gap-4 items-center">
                                <div class="relative w-full sm:w-64">
                                    <TextInput
                                        v-model="search"
                                        type="text"
                                        class="mt-1 block w-full pl-10"
                                        placeholder="Buscar por concepto o tipo..."
                                    />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 mt-1">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                                    <span class="text-sm text-gray-500 hidden sm:inline">Del:</span>
                                    <TextInput v-model="fechaInicio" type="date" class="block w-full sm:w-auto py-1" />
                                    <span class="text-sm text-gray-500 hidden sm:inline">al:</span>
                                    <TextInput v-model="fechaFin" type="date" class="block w-full sm:w-auto py-1" />
                                </div>
                            </div>

                            <!-- Total & Action -->
                            <div class="flex items-center justify-between w-full md:w-auto gap-6 shrink-0 mt-4 md:mt-0">
                                <div class="bg-indigo-50 border border-indigo-100 rounded-lg px-4 py-2 flex flex-col items-center">
                                    <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Total Gastado</span>
                                    <span class="text-xl font-bold text-indigo-900">{{ formatCurrency(totalGastado) }}</span>
                                </div>
                                <PrimaryButton @click="openCreateModal" class="flex items-center gap-2">
                                    <PlusIcon class="w-5 h-5" /> Registrar Gasto
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Datatable -->
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sucursal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado por</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="compra in compras.data" :key="compra.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ formatDate(compra.fecha_compra) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-normal text-sm text-gray-900">
                                        {{ compra.concepto }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ compra.tipo_gasto }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ compra.tienda ? compra.tienda.nombre : 'General' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                                        {{ formatCurrency(compra.monto) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ compra.user ? compra.user.name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditModal(compra)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-md transition-colors" title="Editar">
                                                <PencilSquareIcon class="w-5 h-5" />
                                            </button>
                                            <button @click="deleteCompra(compra)" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-md transition-colors" title="Eliminar">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="compras.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <CurrencyDollarIcon class="w-12 h-12 text-gray-300 mb-2" />
                                            <p>No se encontraron registros de gastos.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200" v-if="compras.links.length > 3">
                        <div class="flex flex-wrap -mb-1">
                            <template v-for="(link, k) in compras.links" :key="k">
                                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                                <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-indigo-50 hover:text-indigo-700 transition focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-600 text-white hover:bg-indigo-700': link.active }" :href="link.url" v-html="link.label" />
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showingModal" @close="closeModal" maxWidth="xl">
            <form @submit.prevent="saveCompra" class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6 font-bold flex items-center gap-2">
                    <CurrencyDollarIcon class="w-6 h-6 text-indigo-600" />
                    {{ editingCompra ? 'Editar Gasto' : 'Registrar Nuevo Gasto' }}
                </h2>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="fecha_compra" value="Fecha" />
                        <TextInput
                            id="fecha_compra"
                            ref="fechaInput"
                            v-model="form.fecha_compra"
                            type="date"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.fecha_compra" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="tipo_gasto" value="Tipo de Gasto" />
                        <select
                            id="tipo_gasto"
                            v-model="form.tipo_gasto"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                            required
                        >
                            <option value="">Selecciona una categoría</option>
                            <option v-for="tipo in tiposDeGasto" :key="tipo" :value="tipo">
                                {{ tipo }}
                            </option>
                        </select>
                        <InputError :message="form.errors.tipo_gasto" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="concepto" value="Concepto / Descripción breve" />
                        <TextInput
                            id="concepto"
                            v-model="form.concepto"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            placeholder="Ej. Pago recibo de luz"
                        />
                        <InputError :message="form.errors.concepto" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="monto" value="Monto Total ($)" />
                        <TextInput
                            id="monto"
                            v-model="form.monto"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="mt-1 block w-full text-red-600 font-bold"
                            required
                        />
                        <InputError :message="form.errors.monto" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="tienda_id" value="Sucursal / Tienda (Opcional)" />
                        <select
                            id="tienda_id"
                            v-model="form.tienda_id"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                        >
                            <option value="">Gasto General (Sin Sucursal)</option>
                            <option v-for="tienda in tiendas" :key="tienda.id" :value="tienda.id">
                                {{ tienda.nombre }}
                            </option>
                        </select>
                        <InputError :message="form.errors.tienda_id" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Cancelar
                    </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ editingCompra ? 'Actualizar' : 'Guardar' }} Gasto
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
