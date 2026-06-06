<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { PencilSquareIcon, TrashIcon, PlusIcon, CubeIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    products: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

watch([search, perPage], debounce(function ([searchValue, perPageValue]) {
    router.get(route('product-almacen.index'), { ...props.filters, search: searchValue, perPage: perPageValue }, { preserveState: true, replace: true });
}, 300));

const sortBy = (field) => {
    let direction = 'asc';
    if (props.filters.sort_field === field && props.filters.sort_direction === 'asc') {
        direction = 'desc';
    }
    router.get(route('product-almacen.index'), { ...props.filters, sort_field: field, sort_direction: direction }, { preserveState: true, replace: true });
};

const showingModal = ref(false);
const editingProduct = ref(null);
const imagePreview = ref(null);
const fileInput = ref(null);

const form = useForm({
    nombre: '',
    clave: '',
    tipo: '',
    imagen: null,
    precio_venta: '',
    precio_mayoreo: ''
});

const showingImageModal = ref(false);
const selectedImageUrl = ref('');
const selectedImageTitle = ref('');

const viewImage = (url, title) => {
    selectedImageUrl.value = url;
    selectedImageTitle.value = title;
    showingImageModal.value = true;
};

const closeImageModal = () => {
    showingImageModal.value = false;
    setTimeout(() => {
        selectedImageUrl.value = '';
        selectedImageTitle.value = '';
    }, 300);
};

const compressImage = (file, maxWidth = 800, maxHeight = 800, quality = 0.8) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height = Math.round(height * (maxWidth / width));
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = Math.round(width * (maxHeight / height));
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const mimeType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                
                canvas.toBlob((blob) => {
                    const compressedFile = new File([blob], file.name, {
                        type: mimeType,
                        lastModified: Date.now(),
                    });
                    resolve({ file: compressedFile, preview: event.target.result });
                }, mimeType, quality);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const handleImageChange = async (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            const { file: compressedFile, preview } = await compressImage(file);
            form.imagen = compressedFile;
            imagePreview.value = preview;
        } else {
            form.imagen = file;
            const reader = new FileReader();
            reader.onload = (ev) => {
                imagePreview.value = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
};

const openCreateModal = () => {
    editingProduct.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
    showingModal.value = true;
};

const openEditModal = (product) => {
    editingProduct.value = product;
    form.nombre = product.nombre;
    form.clave = product.clave;
    form.tipo = product.tipo || '';
    form.precio_venta = product.precio_venta;
    form.precio_mayoreo = product.precio_mayoreo;
    form.imagen = null;
    form.clearErrors();
    imagePreview.value = product.imagen ? '/storage/' + product.imagen : null;
    if (fileInput.value) fileInput.value.value = '';
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    setTimeout(() => {
        form.reset();
        imagePreview.value = null;
    }, 300);
};

const saveProduct = () => {
    // Definimos el transform para este request en particular
    form.transform((data) => {
        if (editingProduct.value) {
            return {
                ...data,
                _method: 'PUT',
            };
        }
        return data;
    });

    if (editingProduct.value) {
        // Usamos post con _method spoofing porque Inertia no soporta PUT con multipart/form-data nativamente.
        form.post(route('product-almacen.update', editingProduct.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('product-almacen.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProduct = (product) => {
    if (confirm('¿Estás seguro de que quieres eliminar ' + product.nombre + '?')) {
        router.delete(route('product-almacen.destroy', product.id));
    }
};
</script>

<template>
    <Head title="Almacén de Productos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <CubeIcon class="w-6 h-6 text-indigo-600" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Almacén de Productos</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-[1920px] mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200 flex flex-col lg:flex-row justify-between items-center gap-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-1/2">
                            <div class="relative flex-1 w-full">
                                <input v-model="search" type="text" class="w-full pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" placeholder="Buscar por nombre, clave o tipo..." />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
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
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                            <a :href="route('product-almacen.export')" target="_blank" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 gap-2">
                                <DocumentArrowDownIcon class="w-5 h-5" /> Exportar a Excel
                            </a>
                            <PrimaryButton @click="openCreateModal" class="w-full sm:w-auto flex justify-center items-center gap-2">
                                <PlusIcon class="w-5 h-5" /> Nuevo Producto
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Datatable -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                        <table class="min-w-full divide-y divide-gray-200 relative">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Imagen</th>
                                    <th scope="col" @click="sortBy('clave')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition whitespace-nowrap">
                                        Clave <span v-if="filters.sort_field === 'clave'">{{ filters.sort_direction === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" @click="sortBy('tipo')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition whitespace-nowrap">
                                        Tipo <span v-if="filters.sort_field === 'tipo'">{{ filters.sort_direction === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P. Venta</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P. Mayoreo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex-shrink-0 h-10 w-10 mx-auto">
                                            <img v-if="product.imagen" @click="viewImage('/storage/' + product.imagen, product.nombre)" class="h-10 w-10 rounded-full object-cover border border-gray-200 shadow-sm cursor-pointer hover:opacity-75 transition" :src="'/storage/' + product.imagen" alt="" />
                                            <div v-else class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                                                <CubeIcon class="h-6 w-6" />
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ product.clave }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">{{ product.nombre }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ product.tipo || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-green-600 font-medium">${{ Number(product.precio_venta).toFixed(2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-blue-600 font-medium">${{ Number(product.precio_mayoreo).toFixed(2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        <button @click="openEditModal(product)" class="text-indigo-600 hover:text-indigo-900 focus:outline-none bg-indigo-50 p-2 rounded-full hover:bg-indigo-100 transition" title="Editar">
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="deleteProduct(product)" class="text-red-600 hover:text-red-900 focus:outline-none bg-red-50 p-2 rounded-full hover:bg-red-100 transition" title="Eliminar">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No se encontraron productos en el almacén.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200 flex justify-end" v-if="products.links.length > 3">
                        <div class="flex space-x-1">
                            <template v-for="(link, k) in products.links" :key="k">
                                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                                <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-indigo-50 hover:text-indigo-700 transition focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-600 text-white hover:bg-indigo-700': link.active }" :href="link.url" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showingModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">
                    {{ editingProduct ? 'Editar Producto' : 'Crear Producto' }}
                </h2>

                <form @submit.prevent="saveProduct" class="mt-4 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div>
                                <InputLabel for="clave" value="Clave de Producto" />
                                <TextInput id="clave" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white transition" v-model="form.clave" required autofocus />
                                <InputError class="mt-2" :message="form.errors.clave" />
                            </div>

                            <div>
                                <InputLabel for="nombre" value="Nombre" />
                                <TextInput id="nombre" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white transition" v-model="form.nombre" required />
                                <InputError class="mt-2" :message="form.errors.nombre" />
                            </div>

                            <div>
                                <InputLabel for="tipo" value="Tipo de Producto" />
                                <select id="tipo" v-model="form.tipo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-gray-50 focus:bg-white transition" required>
                                    <option value="" disabled>Selecciona el tipo...</option>
                                    <option value="Vitaminas">Vitaminas</option>
                                    <option value="Termogénico">Termogénico</option>
                                    <option value="Pre-entreno">Pre-entreno</option>
                                    <option value="Creatina">Creatina</option>
                                    <option value="Glutamina">Glutamina</option>
                                    <option value="Aminoácidos">Aminoácidos</option>
                                    <option value="Carbohidratos">Carbohidratos</option>
                                    <option value="Proteína">Proteína</option>
                                    <option value="Ropa">Ropa</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.tipo" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="precio_venta" value="Precio Venta ($)" />
                                    <TextInput id="precio_venta" type="number" step="0.01" class="mt-1 block w-full bg-gray-50 focus:bg-white transition" v-model="form.precio_venta" required />
                                    <InputError class="mt-2" :message="form.errors.precio_venta" />
                                </div>
                                <div>
                                    <InputLabel for="precio_mayoreo" value="Precio Mayoreo ($)" />
                                    <TextInput id="precio_mayoreo" type="number" step="0.01" class="mt-1 block w-full bg-gray-50 focus:bg-white transition" v-model="form.precio_mayoreo" required />
                                    <InputError class="mt-2" :message="form.errors.precio_mayoreo" />
                                </div>
                            </div>
                        </div>

                        <!-- Right Column (Image) -->
                        <div class="flex flex-col items-center justify-start pt-6">
                            <div class="w-full flex justify-center items-center">
                                <div class="relative h-48 w-48 rounded-lg border-2 border-dashed border-gray-300 overflow-hidden group hover:border-indigo-500 transition-colors bg-gray-50 flex items-center justify-center">
                                    <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" />
                                    <div v-else class="text-gray-400 flex flex-col items-center">
                                        <svg class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm">Subir Imagen</span>
                                    </div>
                                    <input 
                                        type="file" 
                                        ref="fileInput"
                                        @change="handleImageChange" 
                                        accept="image/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                </div>
                            </div>
                            <InputError class="mt-2 text-center" :message="form.errors.imagen" />
                            <p class="text-xs text-gray-500 mt-4 text-center">Formatos soportados: JPG, PNG, WEBP (Max: 2MB)</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeModal" class="mr-3">Cancelar</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editingProduct ? 'Actualizar Producto' : 'Guardar Producto' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Image View Modal -->
        <Modal :show="showingImageModal" @close="closeImageModal" maxWidth="md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 pb-2 border-b">
                    <h2 class="text-lg font-medium text-gray-900">{{ selectedImageTitle }}</h2>
                    <button @click="closeImageModal" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex justify-center p-2">
                    <img :src="selectedImageUrl" class="max-w-full max-h-[70vh] rounded-lg shadow-md object-contain" :alt="selectedImageTitle" />
                </div>
                <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100">
                    <PrimaryButton @click="closeImageModal">Cerrar</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
