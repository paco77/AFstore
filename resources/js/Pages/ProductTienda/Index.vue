<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { TrashIcon, PlusIcon, ClipboardDocumentListIcon, MagnifyingGlassIcon, CheckIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    inventory: Object,
    tiendas: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);
const toasts = ref([]);

const showToast = (message) => {
    const id = Date.now();
    toasts.value.push({ id, message });
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, 3000);
};

watch([search, perPage], debounce(function ([searchValue, perPageValue]) {
    router.get(route('product-tienda.index'), { search: searchValue, perPage: perPageValue }, { preserveState: true, replace: true });
}, 300));

const showingModal = ref(false);
const form = useForm({
    tienda_id: '',
    products: []
});

const searchProductTerm = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

const updatingId = ref(null);

watch(searchProductTerm, debounce(async function (value) {
    if (!value || value.length < 2) {
        searchResults.value = [];
        return;
    }
    isSearching.value = true;
    try {
        const response = await axios.get(route('product-tienda.index'), {
            params: { search_products: value }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        isSearching.value = false;
    }
}, 300));

const openAssignModal = () => {
    form.reset();
    form.clearErrors();
    searchProductTerm.value = '';
    searchResults.value = [];
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    setTimeout(() => {
        form.reset();
        searchProductTerm.value = '';
        searchResults.value = [];
    }, 300);
};

const addProductToList = (product) => {
    // Check if already in list
    const exists = form.products.find(p => p.id === product.id);
    if (exists) {
        exists.amount += 1;
    } else {
        form.products.push({
            ...product,
            amount: 1
        });
    }
    showToast(`Producto ${product.nombre} asignado a la lista`);
};

const removeProductFromList = (index) => {
    form.products.splice(index, 1);
};

const saveAssignment = () => {
    form.post(route('product-tienda.store'), {
        onSuccess: () => closeModal(),
    });
};

const updateItem = async (item) => {
    updatingId.value = item.id;
    try {
        await axios.patch(route('product-tienda.update', item.id), {
            amount: item.amount,
            precio: item.precio
        });
        showToast('Inventario actualizado correctamente');
    } catch (error) {
        console.error('Error updating inventory:', error);
        alert('Error al actualizar el inventario');
    } finally {
        updatingId.value = null;
    }
};

const deleteRecord = (record) => {
    if (confirm('¿Eliminar este registro de inventario de ' + record.tienda.nombre + '?')) {
        router.delete(route('product-tienda.destroy', record.id));
    }
};
</script>

<template>
    <Head title="Asignación a Tiendas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <ClipboardDocumentListIcon class="w-6 h-6 text-indigo-600" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $page.props.auth.user.email === 'gabo@mail.com' ? 'Catálogo de Precios' : 'Inventario por Tienda' }}
                </h2>
            </div>
        </template>

        <!-- Toasts -->
        <Toast v-for="toast in toasts" :key="toast.id" :message="toast.message" />

        <div class="py-12">
            <div class="max-w-[1920px] mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200 flex flex-col lg:flex-row justify-between items-center gap-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-1/2">
                            <div class="relative flex-1 w-full">
                                <input v-model="search" type="text" class="w-full pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" placeholder="Buscar por nombre, clave o tipo..." />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center justify-between sm:justify-start gap-2 w-full sm:w-auto">
                                <span class="text-sm text-gray-500">Registros por página:</span>
                                <select v-model="perPage" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <PrimaryButton v-if="$page.props.auth.user.email !== 'gabo@mail.com'" @click="openAssignModal" class="w-full sm:w-auto flex justify-center items-center gap-2">
                            <PlusIcon class="w-5 h-5" /> Asignar Productos
                        </PrimaryButton>
                    </div>

                    <!-- Datatable -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                        <table class="min-w-full divide-y divide-gray-200 relative">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr v-if="$page.props.auth.user.email === 'gabo@mail.com'">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sucursal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto (Clave)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Tienda</th>
                                </tr>
                                <tr v-else>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tienda</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto (Clave)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40 text-center">Precio Almacén</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Precio Tienda</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Asignación</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in inventory.data" :key="item.id" class="hover:bg-gray-50 transition-colors">
                                    <template v-if="$page.props.auth.user.email === 'gabo@mail.com'">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ item.tienda?.nombre }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ item.product_almacen?.tipo || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 mr-3">
                                                    <img v-if="item.product_almacen?.imagen" class="h-10 w-10 rounded-full object-cover border border-gray-200" :src="'/storage/' + item.product_almacen.imagen" alt="" />
                                                    <div v-else class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                                        <ClipboardDocumentListIcon class="w-5 h-5" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm text-gray-900 font-medium">{{ item.product_almacen?.nombre }}</div>
                                                    <div class="text-xs text-gray-500 font-mono">{{ item.product_almacen?.clave }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-lg font-black text-indigo-700">
                                                ${{ (item.precio * 1.07).toFixed(2) }}
                                            </div>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ item.tienda?.nombre }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ item.product_almacen?.tipo || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 mr-3">
                                                    <img v-if="item.product_almacen?.imagen" class="h-10 w-10 rounded-full object-cover border border-gray-200" :src="'/storage/' + item.product_almacen.imagen" alt="" />
                                                    <div v-else class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                                        <ClipboardDocumentListIcon class="w-5 h-5" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm text-gray-900 font-medium">{{ item.product_almacen?.nombre }}</div>
                                                    <div class="text-xs text-gray-500 font-mono">{{ item.product_almacen?.clave }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input 
                                                type="number" 
                                                v-model="item.amount" 
                                                class="w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm text-center font-bold"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="text-sm font-bold text-gray-400">
                                                ${{ item.product_almacen?.precio_venta }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-1">
                                                <span class="text-gray-400">$</span>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    v-model="item.precio" 
                                                    class="w-32 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-bold text-indigo-600"
                                                />
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ new Date(item.created_at).toLocaleDateString() }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button 
                                                @click="updateItem(item)" 
                                                :disabled="updatingId === item.id"
                                                class="text-indigo-600 hover:text-indigo-900 focus:outline-none bg-indigo-50 p-2 rounded-full hover:bg-indigo-100 transition disabled:opacity-50"
                                                title="Guardar Cambios"
                                            >
                                                <CheckIcon v-if="updatingId !== item.id" class="w-5 h-5" />
                                                <ArrowPathIcon v-else class="w-5 h-5 animate-spin" />
                                            </button>
                                            <button 
                                                @click="deleteRecord(item)" 
                                                class="text-red-600 hover:text-red-900 focus:outline-none bg-red-50 p-2 rounded-full hover:bg-red-100 transition" 
                                                title="Eliminar Asignación"
                                            >
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </td>
                                    </template>
                                </tr>
                                <tr v-if="inventory.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No hay productos asignados a tiendas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200 flex justify-end" v-if="inventory.links.length > 3">
                        <div class="flex space-x-1">
                            <template v-for="(link, k) in inventory.links" :key="k">
                                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                                <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-indigo-50 hover:text-indigo-700 transition focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-600 text-white hover:bg-indigo-700': link.active }" :href="link.url" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <Modal :show="showingModal" @close="closeModal" maxWidth="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b uppercase tracking-wider">
                    Asignar Productos a Tienda
                </h2>

                <form @submit.prevent="saveAssignment" class="mt-4 space-y-6">
                    
                    <!-- Store Selection -->
                    <div>
                        <InputLabel for="tienda_id" value="Seleccionar Tienda" />
                        <select id="tienda_id" v-model="form.tienda_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-gray-50 focus:bg-white transition font-bold">
                            <option value="" disabled>Elige una tienda...</option>
                            <option v-for="tienda in tiendas" :key="tienda.id" :value="tienda.id">
                                {{ tienda.nombre }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.tienda_id" />
                    </div>

                    <!-- Product Search -->
                    <div class="relative">
                        <InputLabel value="Buscar Producto (Nombre, Clave o Tipo)" />
                        <div class="relative mt-1">
                            <input 
                                v-model="searchProductTerm" 
                                type="text" 
                                class="w-full pl-10 pr-4 py-2 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm bg-gray-50 focus:bg-white transition" 
                                placeholder="Escribe para buscar en el almacén..." 
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            
                            <!-- Search Results Dropdown -->
                            <div v-if="searchProductTerm.length > 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-2xl max-h-60 overflow-auto">
                                <div v-if="isSearching" class="p-4 text-center">
                                    <ArrowPathIcon class="w-5 h-5 animate-spin mx-auto text-indigo-500" />
                                </div>
                                <div v-else-if="searchResults.length === 0" class="p-3 text-sm text-gray-500 text-center">No se encontraron productos.</div>
                                <ul v-else class="divide-y divide-gray-100">
                                    <li v-for="product in searchResults" :key="product.id" 
                                        @click="addProductToList(product)"
                                        class="p-3 hover:bg-indigo-50 cursor-pointer flex justify-between items-center transition-colors">
                                        <div>
                                            <div class="font-bold text-gray-900">{{ product.nombre }}</div>
                                            <div class="text-xs text-gray-500 font-mono">
                                                Clave: {{ product.clave }} | 
                                                Tipo: {{ product.tipo || 'N/A' }}
                                            </div>
                                        </div>
                                        <PlusIcon class="w-6 h-6 text-indigo-600 bg-indigo-50 p-1 rounded-full" />
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Products List with Scroll -->
                    <div v-if="form.products.length > 0" class="mt-6">
                        <InputLabel value="Productos a Asignar" class="mb-2" />
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                            <div class="max-h-64 overflow-y-auto">
                                <ul class="divide-y divide-gray-200">
                                    <li v-for="(item, index) in form.products" :key="index" class="p-4 flex items-center justify-between bg-white">
                                        <div class="flex items-center space-x-3 w-1/2">
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ item.nombre }}</p>
                                                <p class="text-xs text-indigo-600 font-mono">{{ item.clave }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-4">
                                            <div class="flex flex-col items-center">
                                                <label class="text-[10px] text-gray-400 uppercase font-black mb-1">Cantidad</label>
                                                <input type="number" v-model="item.amount" min="1" class="w-20 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold" />
                                            </div>
                                            <button @click.prevent="removeProductFromList(index)" class="mt-5 text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-full transition shadow-sm">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.products" />
                    </div>

                    <div class="flex flex-col items-center justify-center py-10 text-gray-300 border-2 border-dashed border-gray-100 rounded-lg" v-else>
                        <ClipboardDocumentListIcon class="w-16 h-16 mb-2" />
                        <p class="text-sm font-medium">Aún no has agregado productos a la lista de asignación</p>
                    </div>

                    <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-100 gap-3">
                        <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25' : form.processing || form.products.length === 0 }" :disabled="form.processing || form.products.length === 0" class="px-8 !bg-indigo-600">
                            Confirmar Asignación
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
