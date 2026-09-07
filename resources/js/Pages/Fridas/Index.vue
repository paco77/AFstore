<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    categorias: {
        type: Array,
        default: () => ['Ropa', 'Accesorios', 'Ropa interior', 'Sticker']
    }
});

// Item form state
const itemForm = ref({
    tipo_producto: 'Ropa',
    descripcion: '',
    cantidad: 1,
    precio_unitario: '',
});

// Cart items array
const items = ref([]);

// Sale form parameters
const cliente_nombre = ref('');
const metodo_pago = ref('EFECTIVO');
const notas = ref('');
const pago_recibido = ref('');
const isSubmitting = ref(false);

// Auto-calculated item subtotal
const calculatedSubtotal = computed(() => {
    const qty = parseFloat(itemForm.value.cantidad) || 0;
    const price = parseFloat(itemForm.value.precio_unitario) || 0;
    return (qty * price).toFixed(2);
});

// Auto-calculated total for cart
const cartTotal = computed(() => {
    return items.value.reduce((acc, item) => acc + (item.cantidad * item.precio_unitario), 0);
});

const cartTotalFormatted = computed(() => {
    return cartTotal.value.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
});

// Auto-calculated change
const cambioCalculado = computed(() => {
    const pago = parseFloat(pago_recibido.value) || 0;
    const diff = pago - cartTotal.value;
    return diff > 0 ? diff.toFixed(2) : '0.00';
});

// Add item to cart
const addItem = () => {
    if (!itemForm.value.tipo_producto) {
        alert('Por favor selecciona un tipo de producto.');
        return;
    }
    if (!itemForm.value.cantidad || itemForm.value.cantidad <= 0) {
        alert('La cantidad debe ser mayor a 0.');
        return;
    }
    if (itemForm.value.precio_unitario === '' || parseFloat(itemForm.value.precio_unitario) < 0) {
        alert('Ingresa un precio unitario válido.');
        return;
    }

    const price = parseFloat(itemForm.value.precio_unitario);
    const qty = parseInt(itemForm.value.cantidad);
    const subtotal = qty * price;

    items.value.push({
        id: Date.now() + Math.random(),
        tipo_producto: itemForm.value.tipo_producto,
        descripcion: itemForm.value.descripcion.trim(),
        cantidad: qty,
        precio_unitario: price,
        subtotal: subtotal
    });

    // Reset item form inputs except category
    itemForm.value.descripcion = '';
    itemForm.value.cantidad = 1;
    itemForm.value.precio_unitario = '';
};

// Remove item from cart
const removeItem = (index) => {
    items.value.splice(index, 1);
};

// Clear entire cart
const clearCart = () => {
    if (confirm('¿Estás seguro de vaciar el carrito actual?')) {
        items.value = [];
        pago_recibido.value = '';
    }
};

// Submit sale
const registrarVenta = () => {
    if (items.value.length === 0) {
        alert('Debes agregar al menos un producto al carrito.');
        return;
    }

    isSubmitting.value = true;

    router.post(route('fridas.store'), {
        items: items.value.map(item => ({
            tipo_producto: item.tipo_producto,
            descripcion: item.descripcion,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario,
        })),
        cliente_nombre: cliente_nombre.value,
        metodo_pago: metodo_pago.value,
        notas: notas.value,
        pago_recibido: pago_recibido.value ? parseFloat(pago_recibido.value) : 0,
        cambio_entregado: parseFloat(cambioCalculado.value),
    }, {
        onSuccess: () => {
            items.value = [];
            cliente_nombre.value = '';
            notas.value = '';
            pago_recibido.value = '';
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
};

// Badge CSS logic by category
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
    <Head title="Módulo Fridas - Ventas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 flex items-center gap-2">
                        <span class="inline-block w-3 h-8 bg-pink-500 rounded-full"></span>
                        Módulo Fridas — Ventas
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Registro de ventas independientes por tipo de producto (Ropa, Accesorios, Ropa interior, Stickers)</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('fridas.history')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Historial Fridas
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Alert notification on success -->
                <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-medium">{{ $page.props.flash.success }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Panel: Product Input Form & Cart -->
                    <div class="lg:col-span-8 space-y-6">
                        
                        <!-- Form Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-6 transition hover:shadow-md">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Agregar Producto a la Venta
                            </h3>

                            <form @submit.prevent="addItem" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                    
                                    <!-- Tipo de Producto (Select) -->
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            Tipo de Producto <span class="text-pink-500">*</span>
                                        </label>
                                        <select
                                            v-model="itemForm.tipo_producto"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm font-medium"
                                        >
                                            <option v-for="cat in categorias" :key="cat" :value="cat">
                                                {{ cat }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Descripción (Optional) -->
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            Descripción / Detalle
                                        </label>
                                        <input
                                            type="text"
                                            v-model="itemForm.descripcion"
                                            placeholder="Ej: Playera M, Color Rosa..."
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                        />
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            Cantidad <span class="text-pink-500">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            v-model="itemForm.cantidad"
                                            min="1"
                                            step="1"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm font-medium"
                                        />
                                    </div>

                                    <!-- Precio Unitario -->
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            Precio Unitario ($) <span class="text-pink-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                                            <input
                                                type="number"
                                                v-model="itemForm.precio_unitario"
                                                min="0"
                                                step="0.01"
                                                placeholder="0.00"
                                                class="w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm font-medium"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Subtotal Automatic Preview & Add Button -->
                                <div class="flex flex-col sm:flex-row items-center justify-between pt-3 border-t border-gray-100 gap-3">
                                    <div class="text-sm text-gray-600 flex items-center gap-2">
                                        <span>Subtotal calculado:</span>
                                        <span class="text-lg font-bold text-gray-900 bg-pink-50 text-pink-700 px-3 py-0.5 rounded-full border border-pink-200">
                                            ${{ calculatedSubtotal }}
                                        </span>
                                    </div>

                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-pink-600 hover:bg-pink-700 active:bg-pink-800 text-white text-sm font-semibold rounded-lg shadow transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Agregar a la Venta
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Cart Items List -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                                    </svg>
                                    Lista de Productos en la Venta Actual
                                    <span class="text-xs font-semibold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full ml-1">
                                        {{ items.length }} {{ items.length === 1 ? 'producto' : 'productos' }}
                                    </span>
                                </h3>

                                <button
                                    v-if="items.length > 0"
                                    @click="clearCart"
                                    type="button"
                                    class="text-xs font-medium text-red-600 hover:text-red-800 transition"
                                >
                                    Vaciar Carrito
                                </button>
                            </div>

                            <div v-if="items.length === 0" class="p-8 text-center text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <p class="text-sm font-medium">No hay productos agregados en esta venta.</p>
                                <p class="text-xs text-gray-400 mt-1">Selecciona el tipo de producto arriba y presiona "Agregar a la Venta".</p>
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <th class="py-3 px-4">#</th>
                                            <th class="py-3 px-4">Tipo</th>
                                            <th class="py-3 px-4">Descripción</th>
                                            <th class="py-3 px-4 text-center">Cant.</th>
                                            <th class="py-3 px-4 text-right">P. Unitario</th>
                                            <th class="py-3 px-4 text-right">Subtotal</th>
                                            <th class="py-3 px-4 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-sm">
                                        <tr v-for="(item, idx) in items" :key="item.id" class="hover:bg-pink-50/30 transition">
                                            <td class="py-3 px-4 text-xs font-medium text-gray-400">{{ idx + 1 }}</td>
                                            <td class="py-3 px-4">
                                                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border', getBadgeClass(item.tipo_producto)]">
                                                    {{ item.tipo_producto }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-gray-700">
                                                {{ item.descripcion || '—' }}
                                            </td>
                                            <td class="py-3 px-4 text-center font-semibold text-gray-900">
                                                {{ item.cantidad }}
                                            </td>
                                            <td class="py-3 px-4 text-right text-gray-600">
                                                ${{ item.precio_unitario.toFixed(2) }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-bold text-gray-900">
                                                ${{ item.subtotal.toFixed(2) }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <button
                                                    @click="removeItem(idx)"
                                                    type="button"
                                                    class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition"
                                                    title="Eliminar"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Right Panel: Summary & Complete Sale -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-6 sticky top-6">
                            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4 flex items-center justify-between">
                                <span>Resumen de Venta</span>
                                <span class="text-xs font-normal text-pink-600 bg-pink-50 px-2 py-1 rounded">Fridas</span>
                            </h3>

                            <!-- Total Display -->
                            <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-xl p-4 mb-5 border border-pink-100 text-center">
                                <span class="text-xs font-semibold uppercase text-pink-700 tracking-wider">Total a Cobrar</span>
                                <div class="text-3xl font-extrabold text-gray-900 mt-1">
                                    {{ cartTotalFormatted }}
                                </div>
                            </div>

                            <!-- Customer & Payment Form -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                        Nombre del Cliente (Opcional)
                                    </label>
                                    <input
                                        type="text"
                                        v-model="cliente_nombre"
                                        placeholder="Ej: María López"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                        Método de Pago <span class="text-pink-500">*</span>
                                    </label>
                                    <select
                                        v-model="metodo_pago"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm font-medium"
                                    >
                                        <option value="EFECTIVO">EFECTIVO</option>
                                        <option value="TARJETA">TARJETA (Débito/Crédito)</option>
                                        <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                                        <option value="OTRO">OTRO</option>
                                    </select>
                                </div>

                                <div v-if="metodo_pago === 'EFECTIVO'" class="grid grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            Pago Recibido ($)
                                        </label>
                                        <input
                                            type="number"
                                            v-model="pago_recibido"
                                            min="0"
                                            step="0.5"
                                            placeholder="0.00"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm font-medium"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            Cambio ($)
                                        </label>
                                        <div class="w-full py-2 px-3 bg-gray-100 rounded-lg text-sm font-bold text-emerald-600 border border-gray-200 text-right">
                                            ${{ cambioCalculado }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                        Notas u Observaciones
                                    </label>
                                    <textarea
                                        v-model="notas"
                                        rows="2"
                                        placeholder="Detalles adicionales..."
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                                    ></textarea>
                                </div>

                                <button
                                    @click="registrarVenta"
                                    type="button"
                                    :disabled="items.length === 0 || isSubmitting"
                                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-base font-bold rounded-lg shadow-md transition flex items-center justify-center gap-2 mt-4"
                                >
                                    <svg v-if="!isSubmitting" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span v-if="isSubmitting">Procesando...</span>
                                    <span v-else>Completar Venta Fridas</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
