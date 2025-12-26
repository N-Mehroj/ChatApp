<template>
    <div class="p-5 mt-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
        <div class="flex flex-wrap px-4" v-if="loading || imageSource">
            <div v-if="!isLoaded"
                 class="relative w-4 h-4 mb-5 mr-5 ml-12 mt-10"
            >
                <LoadingIcon v-if="!isLoaded"
                             icon="oval"
                             color="black"
                             class="w-2 h-2 ml-2"
                />
            </div>
            <div class="relative w-32 h-32 mb-5 mr-5 cursor-pointer image-fit zoom-in">
                <img v-if="imageSource && isLoaded"
                     class="rounded-md"
                     :src="'/uploads/' + imageSource"
                />
                <span class="cursor-pointer absolute top-0 right-0 flex items-center justify-center w-5 h-5 -mt-2 -mr-2 text-white rounded-full bg-danger"
                      @click="deleteImage"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stroke-1.5 w-4 h-4">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </span>
            </div>
        </div>
        <div class="pt-4 border-2 border-dashed rounded-md dark:border-darkmode-400"
             style="height: 200px"
        >
            <div class="relative flex items-center px-4 pb-4 cursor-pointer">
                <div :style="styles" class="flex">
                    <Lucide icon="Image" class="w-4 h-4 mr-2"/>
                    <span class="mr-1 text-primary">Нажмите или перетащите файл</span>
                </div>
                <FormInput type="file"
                           @input="uploadImage"
                           class="absolute top-0 left-0 w-full h-full opacity-0"
                           accept="image/png, image/jpeg, image/jpg"
                           style="position: absolute; height: 200px"
                />
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import axios from 'axios'

export default {
    name: "SingleFileUpload",
    data() {
        return {
            imageSource: '',
            isLoaded: true,
            loading: false,
        }
    },
    props: {
        folder: String,
        modelValue: String
    },
    mounted() {
        this.imageSource = this.modelValue
    },
    computed: {
        styles() {
            return {
                position: 'absolute',
                left: '50%',
                translate: '-50%',
                top: '75px'
            }
        }
    },
    methods: {
        async uploadImage(event) {
            this.loading = true
            this.isLoaded = false

            const formData = new FormData();
            formData.append('image', event.target.files[0]);
            formData.append('folder', this.folder);

            await axios
                .post('/upload-image', formData, {
                    headers: {"Content-Type": "multipart/form-data"},
                })
                .then(res => {
                    this.isLoaded = true
                    this.imageSource = res.data
                    this.$emit('update:modelValue', this.imageSource)
                })
        },
        deleteImage() {
            this.$emit('delete', this.imageSource, true)
        }
    }
}
</script>

<script setup lang="ts">
import Lucide from "../base-components/Lucide";
import {FormInput} from "../base-components/Form";
import {DropzoneElement} from "../base-components/Dropzone";
import LoadingIcon from "../base-components/LoadingIcon";
</script>
