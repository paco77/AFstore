<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import debounce from 'lodash/debounce';
import axios from 'axios';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import Toast from '@/Components/Toast.vue';
import { 
    MagnifyingGlassIcon, 
    TrashIcon, 
    ShoppingCartIcon, 
    BanknotesIcon,
    UserIcon,
    DocumentTextIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline';

const searchProductTerm = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

const form = useForm({
    productos: [],
    tipo_venta: 'NOTA',
    metodo_pago: 'EFECTIVO',
    cliente_nombre: 'PUBLICO EN GENERAL',
});

const showingPaymentModal = ref(false);
const showingSearchModal = ref(false);
const pagoRecibido = ref(0);
const toasts = ref([]);

const showToast = (message, type = 'success') => {
    const id = Date.now();
    toasts.value.push({ id, message, type });
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, 4000);
};

const mobileToast = ref(false);
const mobileToastMessage = ref('');
let mobileToastTimeout = null;

const showMobileToast = (message) => {
    mobileToastMessage.value = message;
    mobileToast.value = true;
    if (mobileToastTimeout) clearTimeout(mobileToastTimeout);
    mobileToastTimeout = setTimeout(() => {
        mobileToast.value = false;
    }, 2000);
};

const cambio = computed(() => {
    const total = parseFloat(totalVenta.value);
    const recibido = parseFloat(pagoRecibido.value) || 0;
    return Math.max(0, recibido - total).toFixed(2);
});

onMounted(() => {
    fetchProducts('');
});

const fetchProducts = async (value) => {
    isSearching.value = true;
    try {
        const response = await axios.get(route('ventas.search'), {
            params: { search: value }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        isSearching.value = false;
    }
};

// Search products logic
watch(searchProductTerm, debounce(function (value) {
    fetchProducts(value);
}, 300));

const addProduct = (product) => {
    const exists = form.productos.find(p => p.id === product.id);
    if (exists) {
        exists.cantidad += 1;
    } else {
        form.productos.push({
            id: product.id,
            imagen: product.imagen,
            nombre: product.nombre,
            cantidad: 1,
            precio_unitario: product.precio_venta,
            descuento_porcentaje: 0,
            stock: product.stock
        });
    }
    showToast(`Agregado: ${product.nombre}`);
    if (showingSearchModal.value) {
        showMobileToast(`Agregado: ${product.nombre}`);
    }
};

const removeProduct = (index) => {
    form.productos.splice(index, 1);
};

const calculateSubtotal = (item) => {
    const subtotal = item.cantidad * item.precio_unitario;
    const descuento = subtotal * (item.descuento_porcentaje / 100);
    return (subtotal - descuento).toFixed(2);
};

const totalVenta = computed(() => {
    return form.productos.reduce((acc, item) => {
        return acc + parseFloat(calculateSubtotal(item));
    }, 0).toFixed(2);
});

const submitVenta = () => {
    if (form.productos.length === 0) {
        showToast('Agregue al menos un producto.', 'error');
        return;
    }
    
    if (form.metodo_pago === 'EFECTIVO') {
        pagoRecibido.value = totalVenta.value;
        showingPaymentModal.value = true;
    } else {
        confirmarFinalizarVenta();
    }
};

const confirmarFinalizarVenta = () => {
    if (form.metodo_pago === 'EFECTIVO' && parseFloat(pagoRecibido.value) < parseFloat(totalVenta.value)) {
        alert('El monto recibido es menor al total de la venta.');
        return;
    }

    form.transform((data) => ({
        ...data,
        pago_recibido: form.metodo_pago === 'EFECTIVO' ? pagoRecibido.value : 0,
        cambio_entregado: form.metodo_pago === 'EFECTIVO' ? cambio.value : 0
    })).post(route('ventas.store'), {
        onSuccess: () => {
            showToast('¡Venta realizada con éxito!');
            form.reset();
            showingPaymentModal.value = false;
        },
        onError: () => {
            showToast('Error al procesar la venta. Intente de nuevo.', 'error');
        }
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
};

const page = usePage();

watch(() => page.props.flash?.ticket, (ticket) => {
    if (ticket && ticket.id) {
        window.open(route('ventas.ticket', { 
            venta: ticket.id, 
            pago: ticket.pago, 
            cambio: ticket.cambio 
        }), '_blank', 'width=400,height=600');
    }
}, { immediate: true });
</script>

<template>
    <Head title="Punto de Venta" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <ShoppingCartIcon class="w-6 h-6 text-indigo-600" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Punto de Venta Online</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 min-h-[calc(100vh-140px)] flex flex-col">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1">
                    <!-- Left Column: Cart & Checkout -->
                    <div class="lg:col-span-7 xl:col-span-8 flex flex-col order-2 lg:order-1">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-100 flex-1 flex flex-col">
                            <!-- Top Bar: Options -->
                            <div class="p-6 bg-gray-50 border-b border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                                    <!-- Tipo Venta -->
                                    <div>
                                        <InputLabel for="tipo_venta" value="Tipo Venta:*" />
                                        <select id="tipo_venta" v-model="form.tipo_venta" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                            <option value="FACTURA">FACTURA</option>
                                            <option value="NOTA">NOTA</option>
                                        </select>
                                    </div>

                                    <!-- Cliente -->
                                    <div>
                                        <InputLabel for="cliente" value="Cliente:*" />
                                        <div class="mt-1 relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <UserIcon class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input type="text" id="cliente" v-model="form.cliente_nombre" class="block w-full pl-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
                                        </div>
                                    </div>

                                    <!-- Metodo Pago -->
                                    <div>
                                        <InputLabel for="metodo_pago" value="Método de Pago:*" />
                                        <select id="metodo_pago" v-model="form.metodo_pago" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                            <option value="EFECTIVO">EFECTIVO</option>
                                            <option value="TARJETA">TARJETA</option>
                                            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-6 lg:hidden">
                                    <PrimaryButton @click="showingSearchModal = true" type="button" class="w-full justify-center flex items-center gap-2 py-3 text-lg">
                                        <MagnifyingGlassIcon class="w-6 h-6" /> AGREGAR PRODUCTOS
                                    </PrimaryButton>
                                </div>
                            </div>

                            <!-- Sales Table -->
                            <div class="overflow-x-auto overflow-y-auto relative flex-1 min-h-[300px]">
                        <table class="min-w-full divide-y divide-gray-200 border-separate border-spacing-0">
                            <thead class="bg-gray-100 sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-16 text-center bg-gray-100 border-b">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-16 text-center bg-gray-100 border-b">Img</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100 border-b">Artículo</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-24 bg-gray-100 border-b">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-32 bg-gray-100 border-b">Precio</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-32 bg-gray-100 border-b">Descuento %</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-32 bg-gray-100 border-b text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-20 bg-gray-100 border-b">Cancelar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(item, index) in form.productos" :key="index" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center font-bold">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap text-center">
                                        <div class="w-12 h-12 mx-auto rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                            <img v-if="item.imagen" :src="'/storage/' + item.imagen" :alt="item.nombre" class="w-full h-full object-cover" />
                                            <svg v-else class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ item.nombre }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input type="number" v-model="item.cantidad" min="1" :max="item.stock" class="w-20 border-gray-200 rounded focus:ring-indigo-500 focus:border-indigo-500 text-center text-sm" />
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-1">
                                            <span class="text-gray-400">$</span>
                                            <input type="number" v-model="item.precio_unitario" step="0.01" class="w-24 border-gray-200 rounded focus:ring-indigo-500 focus:border-indigo-500 text-right text-sm" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <select v-model="item.descuento_porcentaje" class="w-full border-gray-200 rounded focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            <option :value="0">N/A</option>
                                            <option v-for="d in [5, 10, 15, 20, 25, 50]" :key="d" :value="d">{{ d }}%</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                        {{ formatCurrency(calculateSubtotal(item)) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <button @click="removeProduct(index)" class="text-red-400 hover:text-red-600 p-1 rounded-full hover:bg-red-50 transition">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="form.productos.length === 0">
                                    <td colspan="8" class="px-6 py-20 text-center">
                                        <ShoppingCartIcon class="w-16 h-16 mx-auto text-gray-200 mb-4" />
                                        <p class="text-gray-400 text-lg">Busca y agrega productos para comenzar la venta</p>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-if="form.productos.length > 0">
                                <tr class="bg-gray-50">
                                    <td colspan="6" class="px-4 py-4 text-right font-bold text-gray-700 uppercase tracking-wider">Total:</td>
                                    <td class="px-4 py-4 text-right font-black text-2xl text-indigo-700 whitespace-nowrap">
                                        {{ formatCurrency(totalVenta) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                            <!-- Footer Action -->
                            <div class="p-6 bg-white border-t border-gray-200 mt-auto">
                                <div class="flex justify-between items-center">
                                    <div class="flex flex-col text-sm text-gray-500">
                                        <span>Productos: <span class="font-bold text-gray-900">{{ form.productos.length }}</span></span>
                                    </div>
                                    <button 
                                        @click="submitVenta" 
                                        :disabled="form.processing || form.productos.length === 0"
                                        class="flex items-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow transition duration-300 disabled:opacity-50"
                                    >
                                        <ShoppingCartIcon v-if="!form.processing" class="w-6 h-6" />
                                        <ArrowPathIcon v-else class="w-6 h-6 animate-spin" />
                                        COBRAR {{ formatCurrency(totalVenta) }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Product Grid -->
                    <div class="hidden lg:flex lg:col-span-5 xl:col-span-4 flex-col gap-4 order-1 lg:order-2">
                        <!-- Search Bar -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-4 shrink-0">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon class="h-6 w-6 text-gray-400" />
                                </div>
                                <input 
                                    type="text" 
                                    v-model="searchProductTerm"
                                    class="block w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition text-lg font-medium"
                                    placeholder="Buscar por nombre, clave o tipo..."
                                    autocomplete="off"
                                />
                                <div v-if="isSearching" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <ArrowPathIcon class="h-5 w-5 text-indigo-500 animate-spin" />
                                </div>
                            </div>
                        </div>

                        <!-- Product Grid (Scrollable) -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 flex-1 overflow-y-auto p-4 custom-scrollbar max-h-[calc(100vh-280px)]">
                            <div v-if="isSearching && searchResults.length === 0" class="flex flex-col items-center justify-center h-48 space-y-4">
                                <ArrowPathIcon class="w-10 h-10 animate-spin text-indigo-500" />
                                <span class="text-gray-500 font-medium">Buscando productos...</span>
                            </div>
                            
                            <div v-else-if="searchResults.length === 0" class="flex flex-col items-center justify-center h-48 text-gray-500">
                                <ShoppingCartIcon class="w-16 h-16 text-gray-200 mb-4" />
                                <span class="text-lg font-medium text-gray-400">Sin resultados</span>
                            </div>

                            <div v-else class="grid grid-cols-2 gap-4">
                                <div v-for="product in searchResults" :key="product.id" 
                                     @click="addProduct(product)"
                                     class="relative flex flex-col bg-white border border-gray-100 rounded-2xl p-4 cursor-pointer hover:border-indigo-400 hover:shadow-lg transition-all duration-200 group overflow-hidden h-[240px]"
                                     :class="{'opacity-50 grayscale': product.stock <= 0}"
                                >
                                    <!-- Add Overlay -->
                                    <div class="absolute inset-0 bg-indigo-600/10 opacity-0 group-hover:opacity-100 transition-opacity z-10 flex items-center justify-center">
                                        <div class="bg-white p-2 rounded-full shadow-lg transform scale-50 group-hover:scale-100 transition-transform">
                                            <ShoppingCartIcon class="w-6 h-6 text-indigo-600" />
                                        </div>
                                    </div>

                                    <!-- Image Area -->
                                    <div class="w-full h-24 mb-3 flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden shrink-0">
                                        <img v-if="product.imagen" :src="'/storage/' + product.imagen" :alt="product.nombre" class="h-full w-full object-cover mix-blend-multiply group-hover:scale-110 transition-transform duration-300" />
                                        <div v-else class="text-gray-300">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    </div>

                                    <!-- Product Info -->
                                    <div class="flex flex-col flex-1">
                                        <h3 class="text-sm font-bold text-gray-900 leading-tight line-clamp-2 mb-1 group-hover:text-indigo-700 transition-colors">{{ product.nombre }}</h3>
                                        <p class="text-xs text-gray-400 font-mono mb-2">{{ product.clave }} <span v-if="product.tipo" class="text-xs bg-gray-100 text-gray-500 px-1 py-0.5 rounded ml-1">{{ product.tipo }}</span></p>
                                        
                                        <div class="mt-auto flex items-center justify-between">
                                            <span class="text-lg font-black text-indigo-600">{{ formatCurrency(product.precio_venta) }}</span>
                                            <span class="text-xs font-bold px-2 py-1 rounded-full" 
                                                  :class="product.stock > 10 ? 'bg-green-100 text-green-700' : (product.stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')">
                                                Stock: {{ product.stock }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="product.stock <= 0" class="absolute inset-0 bg-red-500/10 flex items-center justify-center backdrop-blur-[1px] z-20">
                                        <span class="bg-red-600 text-white font-black text-sm px-3 py-1 rounded-full shadow-lg transform rotate-[-15deg]">AGOTADO</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toasts -->
        <div class="fixed bottom-4 right-4 z-[100] space-y-2">
            <Toast v-for="toast in toasts" :key="toast.id" :message="toast.message" :type="toast.type" />
        </div>

        <!-- Payment Confirmation Modal -->
        <Modal :show="showingPaymentModal" @close="showingPaymentModal = false" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-center mb-6">
                    <div class="bg-indigo-100 p-4 rounded-full">
                        <BanknotesIcon class="w-12 h-12 text-indigo-600" />
                    </div>
                </div>
                
                <h3 class="text-2xl font-black text-gray-900 text-center uppercase tracking-wider mb-2">
                    Confirmar Pago
                </h3>
                <p class="text-gray-500 text-center mb-8">Ingresa el monto recibido para completar la venta</p>

                <div class="space-y-6">
                    <!-- Total to Pay -->
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex justify-between items-center">
                        <span class="text-gray-600 font-bold">TOTAL A PAGAR:</span>
                        <span class="text-3xl font-black text-indigo-700">{{ formatCurrency(totalVenta) }}</span>
                    </div>

                    <!-- Amount Received -->
                    <div>
                        <InputLabel for="pago_recibido" value="DINERO RECIBIDO:" class="text-xs font-black text-gray-400 mb-2" />
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-bold text-gray-400">$</span>
                            <input 
                                id="pago_recibido"
                                type="number" 
                                v-model="pagoRecibido" 
                                step="0.01"
                                autofocus
                                @keyup.enter="confirmarFinalizarVenta"
                                class="w-full pl-10 pr-4 py-4 text-3xl font-black text-gray-800 bg-white border-2 border-gray-100 focus:border-indigo-500 focus:ring-0 rounded-2xl transition-all shadow-inner"
                            />
                        </div>
                    </div>

                    <!-- Change -->
                    <div class="bg-green-50 p-4 rounded-2xl border border-green-100 flex justify-between items-center">
                        <span class="text-green-700 font-bold uppercase tracking-widest">Su Cambio:</span>
                        <span class="text-3xl font-black text-green-600">{{ formatCurrency(cambio) }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3 pt-4">
                        <PrimaryButton 
                            @click="confirmarFinalizarVenta" 
                            :disabled="form.processing || parseFloat(pagoRecibido) < parseFloat(totalVenta)"
                            class="w-full justify-center py-4 rounded-2xl text-xl font-black shadow-xl disabled:opacity-50"
                        >
                            <span v-if="!form.processing">CONFIRMAR VENTA</span>
                            <ArrowPathIcon v-else class="w-6 h-6 animate-spin mx-auto" />
                        </PrimaryButton>
                        <SecondaryButton @click="showingPaymentModal = false" class="w-full justify-center py-3 border-none text-gray-400 hover:text-gray-600">
                            Cancelar y Revisar
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Mobile Search Modal -->
        <Modal :show="showingSearchModal" @close="showingSearchModal = false" maxWidth="lg">
            <div class="flex flex-col h-[85vh] bg-gray-50 relative">
                <!-- Mobile Alert -->
                <div class="absolute top-16 left-0 right-0 z-50 flex justify-center pointer-events-none">
                    <transition
                        enter-active-class="ease-out duration-300"
                        enter-from-class="opacity-0 -translate-y-4"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-4"
                    >
                        <div v-if="mobileToast" class="bg-indigo-600 text-white px-4 py-2 rounded-full shadow-xl font-bold text-sm mx-4 flex items-center gap-2 transform transition-all">
                            <CheckCircleIcon class="w-5 h-5" />
                            <span class="truncate max-w-[250px]">{{ mobileToastMessage }}</span>
                        </div>
                    </transition>
                </div>

                <div class="p-4 bg-white border-b border-gray-200 flex justify-between items-center shrink-0 shadow-sm z-10">
                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                        <MagnifyingGlassIcon class="w-6 h-6 text-indigo-600" />
                        BUSCAR PRODUCTOS
                    </h3>
                    <button @click="showingSearchModal = false" class="text-gray-400 hover:text-red-500 transition-colors p-1 bg-gray-100 rounded-full">
                        <XCircleIcon class="w-8 h-8" />
                    </button>
                </div>
                
                <div class="p-4 shrink-0 bg-white border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-6 w-6 text-gray-400" />
                        </div>
                        <input 
                            type="text" 
                            v-model="searchProductTerm"
                            class="block w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition text-lg font-medium shadow-inner"
                            placeholder="Buscar por nombre, clave o tipo..."
                            autocomplete="off"
                        />
                        <div v-if="isSearching" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <ArrowPathIcon class="h-5 w-5 text-indigo-500 animate-spin" />
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <div v-if="isSearching && searchResults.length === 0" class="flex flex-col items-center justify-center h-full space-y-4">
                        <ArrowPathIcon class="w-10 h-10 animate-spin text-indigo-500" />
                        <span class="text-gray-500 font-medium">Buscando productos...</span>
                    </div>
                    
                    <div v-else-if="searchResults.length === 0" class="flex flex-col items-center justify-center h-full text-gray-500">
                        <ShoppingCartIcon class="w-16 h-16 text-gray-200 mb-4" />
                        <span class="text-lg font-medium text-gray-400">Sin resultados</span>
                    </div>

                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div v-for="product in searchResults" :key="product.id" 
                             @click="addProduct(product)"
                             class="relative flex flex-col bg-white border border-gray-200 rounded-2xl p-3 cursor-pointer hover:border-indigo-400 hover:shadow-lg transition-all duration-200 group overflow-hidden h-[220px]"
                             :class="{'opacity-50 grayscale': product.stock <= 0}"
                        >
                            <!-- Image Area -->
                            <div class="w-full h-20 mb-2 flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden shrink-0">
                                <img v-if="product.imagen" :src="'/storage/' + product.imagen" :alt="product.nombre" class="h-full w-full object-cover mix-blend-multiply group-hover:scale-110 transition-transform duration-300" />
                                <div v-else class="text-gray-300">
                                    <ShoppingCartIcon class="w-8 h-8" />
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="flex flex-col flex-1">
                                <h3 class="text-xs font-bold text-gray-900 leading-tight line-clamp-2 mb-1 group-hover:text-indigo-700 transition-colors">{{ product.nombre }}</h3>
                                <p class="text-[10px] text-gray-400 font-mono mb-1">{{ product.clave }}</p>
                                
                                <div class="mt-auto flex items-center justify-between">
                                    <span class="text-sm font-black text-indigo-600">{{ formatCurrency(product.precio_venta) }}</span>
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full" 
                                          :class="product.stock > 10 ? 'bg-green-100 text-green-700' : (product.stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')">
                                        Stock: {{ product.stock }}
                                    </span>
                                </div>
                            </div>
                            
                            <div v-if="product.stock <= 0" class="absolute inset-0 bg-red-500/10 flex items-center justify-center backdrop-blur-[1px] z-20">
                                <span class="bg-red-600 text-white font-black text-xs px-2 py-1 rounded-full shadow-lg transform rotate-[-15deg]">AGOTADO</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
    appearance: none;
}
</style>
```
