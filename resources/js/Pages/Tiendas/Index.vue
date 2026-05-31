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
import { PencilSquareIcon, TrashIcon, PlusIcon, BuildingStorefrontIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    tiendas: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

watch([search, perPage], debounce(function ([searchValue, perPageValue]) {
    router.get(route('tiendas.index'), { search: searchValue, perPage: perPageValue }, { preserveState: true, replace: true });
}, 300));

const showingModal = ref(false);
const editingTienda = ref(null);
const form = useForm({
    nombre: '',
    userName: '',
    direccion: ''
});

const openCreateModal = () => {
    editingTienda.value = null;
    form.reset();
    form.clearErrors();
    showingModal.value = true;
};

const openEditModal = (tienda) => {
    editingTienda.value = tienda;
    form.nombre = tienda.nombre;
    form.userName = tienda.userName;
    form.direccion = tienda.direccion || '';
    form.clearErrors();
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    setTimeout(() => form.reset(), 300);
};

const saveTienda = () => {
    if (editingTienda.value) {
        form.put(route('tiendas.update', editingTienda.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('tiendas.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTienda = (tienda) => {
    if (confirm('¿Estás seguro de que quieres eliminar la tienda ' + tienda.nombre + '?')) {
        router.delete(route('tiendas.destroy', tienda.id));
    }
};
</script>

<template>
    <Head title="Tiendas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <BuildingStorefrontIcon class="w-6 h-6 text-indigo-600" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Tiendas</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-[1664px] mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200 flex flex-col lg:flex-row justify-between items-center gap-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-1/2">
                            <div class="relative flex-1 w-full">
                                <input v-model="search" type="text" class="w-full pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" placeholder="Buscar tiendas..." />
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
                        <PrimaryButton @click="openCreateModal" class="w-full sm:w-auto flex justify-center items-center gap-2">
                            <PlusIcon class="w-5 h-5" /> Nueva Tienda
                        </PrimaryButton>
                    </div>

                    <!-- Datatable -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                        <table class="min-w-full divide-y divide-gray-200 relative">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UserName</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="tienda in tiendas.data" :key="tienda.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ tienda.nombre }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ tienda.userName }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 align-middle truncate max-w-xs">{{ tienda.direccion || 'Sin dirección' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        <button @click="openEditModal(tienda)" class="text-indigo-600 hover:text-indigo-900 focus:outline-none bg-indigo-50 p-2 rounded-full hover:bg-indigo-100 transition" title="Editar">
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="deleteTienda(tienda)" class="text-red-600 hover:text-red-900 focus:outline-none bg-red-50 p-2 rounded-full hover:bg-red-100 transition" title="Eliminar">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="tiendas.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        No se encontraron tiendas en la base de datos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200 flex justify-end" v-if="tiendas.links.length > 3">
                        <div class="flex space-x-1">
                            <template v-for="(link, k) in tiendas.links" :key="k">
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
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingTienda ? 'Editar Tienda' : 'Crear Tienda' }}
                </h2>

                <form @submit.prevent="saveTienda" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="nombre" value="Nombre de Tienda" />
                        <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required autofocus />
                        <InputError class="mt-2" :message="form.errors.nombre" />
                    </div>

                    <div>
                        <InputLabel for="userName" value="Enlace / UserName" />
                        <TextInput id="userName" type="text" class="mt-1 block w-full" v-model="form.userName" required />
                        <InputError class="mt-2" :message="form.errors.userName" />
                    </div>

                    <div>
                        <InputLabel for="direccion" value="Dirección" />
                        <TextInput id="direccion" type="text" class="mt-1 block w-full" v-model="form.direccion" />
                        <InputError class="mt-2" :message="form.errors.direccion" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <SecondaryButton @click="closeModal" class="mr-3">Cancelar</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editingTienda ? 'Actualizar' : 'Guardar' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
