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
import { PencilSquareIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Object,
    filters: Object,
    tiendas: Array
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

watch([search, perPage], debounce(function ([searchValue, perPageValue]) {
    router.get(route('users.index'), { search: searchValue, perPage: perPageValue }, { preserveState: true, replace: true });
}, 300));

const showingModal = ref(false);
const editingUser = ref(null);
const userForm = useForm({
    name: '',
    email: '',
    userName: '',
    password: '',
    password_confirmation: '',
    rol: 'Cajero',
    tienda_id: ''
});

const openCreateModal = () => {
    editingUser.value = null;
    userForm.reset();
    userForm.clearErrors();
    showingModal.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.userName = user.userName;
    userForm.rol = user.rol;
    userForm.tienda_id = user.tienda_id || '';
    userForm.password = '';
    userForm.password_confirmation = '';
    userForm.clearErrors();
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    setTimeout(() => userForm.reset(), 300);
};

const saveUser = () => {
    if (editingUser.value) {
        userForm.put(route('users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        userForm.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (user) => {
    if (confirm('¿Estás seguro de que quieres eliminar a ' + user.name + '?')) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Usuarios</h2>
        </template>

        <div class="py-12">
            <div class="max-w-[1664px] mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex items-center gap-4 w-1/2">
                            <div class="relative flex-1">
                                <input v-model="search" type="text" class="w-full pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" placeholder="Buscar usuarios..." />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                <span class="text-sm text-gray-500">Registros por página:</span>
                                <select v-model="perPage" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <PrimaryButton @click="openCreateModal" class="flex items-center gap-2">
                            <PlusIcon class="w-5 h-5" /> Nuevo Usuario
                        </PrimaryButton>
                    </div>

                    <!-- Datatable -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                        <table class="min-w-full divide-y divide-gray-200 relative">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UserName</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tienda</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ user.userName }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ user.tienda ? user.tienda.nombre : 'Sin Tienda' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="user.rol === 'Admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'">
                                          {{ user.rol }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900 focus:outline-none bg-indigo-50 p-2 rounded-full hover:bg-indigo-100 transition" title="Editar">
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900 focus:outline-none bg-red-50 p-2 rounded-full hover:bg-red-100 transition" title="Eliminar">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No se encontraron usuarios en la base de datos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200 flex justify-end" v-if="users.links.length > 3">
                        <div class="flex space-x-1">
                            <template v-for="(link, k) in users.links" :key="k">
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
                    {{ editingUser ? 'Editar Usuario' : 'Crear Usuario' }}
                </h2>

                <form @submit.prevent="saveUser" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Nombre" />
                        <TextInput id="name" type="text" class="mt-1 block w-full" v-model="userForm.name" required autofocus />
                        <InputError class="mt-2" :message="userForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="userName" value="UserName" />
                        <TextInput id="userName" type="text" class="mt-1 block w-full" v-model="userForm.userName" required />
                        <InputError class="mt-2" :message="userForm.errors.userName" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Correo Electrónico" />
                        <TextInput id="email" type="email" class="mt-1 block w-full" v-model="userForm.email" required />
                        <InputError class="mt-2" :message="userForm.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="rol" value="Rol" />
                        <select id="rol" v-model="userForm.rol" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            <option value="Cajero">Cajero</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <InputError class="mt-2" :message="userForm.errors.rol" />
                    </div>

                    <div>
                        <InputLabel for="tienda_id" value="Tienda" />
                        <select id="tienda_id" v-model="userForm.tienda_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            <option value="">Ninguna / Por defecto</option>
                            <option v-for="tienda in tiendas" :key="tienda.id" :value="tienda.id">
                                {{ tienda.nombre }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="userForm.errors.tienda_id" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Contraseña" />
                        <TextInput id="password" type="password" class="mt-1 block w-full" v-model="userForm.password" :required="!editingUser" />
                        <InputError class="mt-2" :message="userForm.errors.password" />
                        <p v-if="editingUser" class="text-xs text-gray-500 mt-1">Déjalo en blanco para mantener la actual.</p>
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                        <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="userForm.password_confirmation" :required="!editingUser" />
                        <InputError class="mt-2" :message="userForm.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <SecondaryButton @click="closeModal" class="mr-3">Cancelar</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': userForm.processing }" :disabled="userForm.processing">
                            {{ editingUser ? 'Actualizar' : 'Guardar' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
