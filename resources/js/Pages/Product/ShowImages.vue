<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    images: Array,
});

const form = useForm({});

const submitOptimize = () =>  form.post(route('creaciones.images.optimize'));

</script>


<template>
    <AppLayout title="Images">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Images
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-end p-4">
                    <form @submit.prevent="submitOptimize">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Optimizar Imagenes
                        </button>
                    </form>
                </div>
                <div class="flex justify-center gap-4 mb-10">
                    <span>menor a 100: <span class="text-blue-500">{{ images.filter(images => images.size <= 100000).length }}</span></span>
                    <span>100 - 200: <span class="text-blue-500">{{ images.filter(images => images.size > 100000 && images.size <= 200000).length }}</span></span>
                    <span>200 - 300: <span class="text-blue-500">{{ images.filter(images => images.size > 200000 && images.size <= 300000).length }}</span></span>
                    <span>300 - 400: <span class="text-blue-500">{{ images.filter(images => images.size > 300000 && images.size <= 400000).length }}</span></span>
                    <span>mayor 400: <span class="text-blue-500">{{ images.filter(images => images.size > 400000).length }}</span></span>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div v-for="image in images" :key="image.id" class="flex justify-center gap-4">
                        <img :src="image.src" alt="image" class="w-1/4 h-1/4">
                        <span class="text-center font-semibold px-6 py-4 whitespace-nowrap text-sm">{{ image.size }}</span>
                        <def>{{ image }}</def>
                        <!-- <table class="min-w-full divide-y divide-gray-200 table-fixed">
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
                        </table>    -->
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
