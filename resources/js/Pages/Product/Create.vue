<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { useForm } from '@inertiajs/vue3';


const form = useForm({
    name: '',
    description: '',
    landing: false,
    images: []
    
});

const selectedImages = ref([]);

const handleImageUpload = (event) => {
    const files = event.target.files;
    selectedImages.value = [];
    form.images = [];
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = (e) => {
            selectedImages.value.push({ url: e.target.result, isMain: false });
            form.images.push({ file, isMain: false });
        };
        reader.readAsDataURL(file);
    }
};

const handleMainImageChange = (index) => {
    form.images.forEach((image, i) => {
        image.isMain = i === index;
    });
    selectedImages.value.forEach((image, i) => {
        image.isMain = i === index;
    });
};

const submit = () => {
    form.transform(data => ({
        ...data,
    })).post(route('creaciones.store'));
};
</script>


<template>
    <AppLayout title="Creaciones">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrar Creación
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="min-w-full overflow-auto border border-gray-200 rounded-lg p-6"> 
                        <div>
                            <form @submit.prevent="submit">
                                <div>
                                    <InputLabel for="name" value="Nombre" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="description" value="Descripción" />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <div class="block mt-4">
                                    <label class="flex items-center">
                                        <Checkbox v-model:checked="form.landing" name="landing" />
                                        <span class="ms-2 text-sm text-gray-600">Añadir a landing?</span>
                                    </label>
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="images" value="Imágenes" />
                                    <input
                                        id="images"
                                        type="file"
                                        multiple
                                        @change="handleImageUpload"
                                        class="mt-1 block w-full"
                                    />
                                </div>

                                <!-- Previsualización de imágenes -->
                                <div> 
                                    <div class="mt-4 flex flex-wrap">
                                        <div v-for="(image, index) in selectedImages" :key="index" class="m-2">
                                            <img :src="image.url" alt="Previsualización" class="w-32 h-32 object-cover" />
                                            <label class="block text-center">
                                                <input
                                                    type="checkbox"
                                                    :checked="image.isMain"
                                                    @change="handleMainImageChange(index)"
                                                />
                                                Principal
                                            </label>
                                        </div>                                    
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.images" />
                                </div>

                                <div class="flex items-center justify-center mt-4">

                                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Registrar
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
