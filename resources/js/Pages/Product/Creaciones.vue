<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    products: Array,
});

const form = useForm({});

const submitDelete = (id) => {
    console.log(id);
    form.delete(route('creaciones.destroy', id));
};

</script>


<template>
    <AppLayout title="Creaciones">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Creaciones
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-end p-4">
                    <Link :href="route('creaciones.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Registrar Creación
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th scope="col" class="hidden md:table-cell px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Descripción
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Numero Imagenes
                                    </th>
                                    <th scope="col" class="hidden md:table-cell px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Landing Page
                                    </th>                                                   
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" v-if="products.length > 0">
                                <tr v-for="product in products" :key="product.id" class="odd:bg-white even:bg-gray-50">
                                    <td class="text-center font-semibold px-6 py-4 whitespace-nowrap text-sm">
                                        {{ product.name }}
                                    </td>
                                    <td class="max-w-96  px-6 py-4  text-sm text-gray-500 truncate ...">
                                        {{ product.description }}
                                    </td>
                                    <td class="text-center px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ product.images.length }}
                                    </td>
                                    <td class="text-center hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ Boolean(product.landing) }}
                                    </td>
                                    <td class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium flex justify-center gap-4">
                                        <Link :href="route('creaciones.update', product.id)" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-600 hover:text-indigo-900 font-bold py-2 px-4 rounded">Editar</Link>
                                        
                                        <button type="submit" @click="submitDelete(product.id)" class="bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-900 font-bold py-2 px-4 rounded">Eliminar</button>
                                       
                                    </td>
                                </tr>
                            </tbody>                                            
                            <tbody class="bg-white divide-y divide-gray-200" v-else>
                                <tr>
                                    <td class="px-6 py-4 w-full text-base text-gray-500 text-center" colspan="8">No has registrado creaciones</td>
                                </tr>
                            </tbody>
                        </table>   
                    
                </div>
            </div>
        </div>
    </AppLayout>
</template>
