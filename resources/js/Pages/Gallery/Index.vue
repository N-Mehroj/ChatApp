<template>
    <div class="grid grid-cols-12 gap-6 mt-8">
        <div class="col-span-12 lg:col-span-3 2xl:col-span-2">
            <h2 class="mt-2 mr-auto text-lg font-medium intro-y">Галерея</h2>
            <!-- BEGIN: File Manager Menu -->
            <div v-if="galleryList.length" class="p-5 mt-6 intro-y box">
                <div class="mt-1">
                    <a href="" class="flex items-center px-3 py-2 font-medium text-white rounded-md bg-primary">
                        <Lucide icon="Image" class="w-4 h-4 mr-2"/>
                        Изображения
                    </a>
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <Lucide icon="Video" class="w-4 h-4 mr-2"/>-->
<!--                        Videos-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <Lucide icon="File" class="w-4 h-4 mr-2"/>-->
<!--                        Documents-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <Lucide icon="Users" class="w-4 h-4 mr-2"/>-->
<!--                        Shared-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <Lucide icon="Trash" class="w-4 h-4 mr-2"/>-->
<!--                        Trash-->
<!--                    </a>-->
                </div>
<!--                <div class="pt-4 mt-4 border-t border-slate-200 dark:border-darkmode-400">-->
<!--                    <a href="" class="flex items-center px-3 py-2 rounded-md">-->
<!--                        <div class="w-2 h-2 mr-3 rounded-full bg-pending"></div>-->
<!--                        Custom Work-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <div class="w-2 h-2 mr-3 rounded-full bg-success"></div>-->
<!--                        Important Meetings-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <div class="w-2 h-2 mr-3 rounded-full bg-warning"></div>-->
<!--                        Work-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <div class="w-2 h-2 mr-3 rounded-full bg-pending"></div>-->
<!--                        Design-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <div class="w-2 h-2 mr-3 rounded-full bg-danger"></div>-->
<!--                        Next Week-->
<!--                    </a>-->
<!--                    <a href="" class="flex items-center px-3 py-2 mt-2 rounded-md">-->
<!--                        <Lucide icon="Plus" class="w-4 h-4 mr-2"/>-->
<!--                        Add New Label-->
<!--                    </a>-->
<!--                </div>-->
            </div>
            <!-- END: File Manager Menu -->
        </div>
        <div class="col-span-12 lg:col-span-9 2xl:col-span-10">
            <!-- BEGIN: File Manager Filter -->
            <div class="flex flex-col-reverse items-center intro-y sm:flex-row">
                <div class="relative w-full mt-3 mr-auto sm:w-auto sm:mt-0">
                    <Lucide
                        icon="Search"
                        class="absolute inset-y-0 left-0 z-10 w-4 h-4 my-auto ml-3 text-slate-500"
                    />
                    <FormInput
                        type="text"
                        class="w-full px-10 sm:w-64 !box"
                        placeholder="Search files"
                    />
                    <Menu class="absolute inset-y-0 right-0 flex items-center mr-3">
                        <Menu.Button as="a" role="button" class="block w-4 h-4" href="#">
                            <Lucide
                                icon="ChevronDown"
                                class="w-4 h-4 cursor-pointer text-slate-500"
                            />
                        </Menu.Button>
                        <Menu.Items
                            placement="bottom-start"
                            class="pt-2 w-[478px] -ml-[228px] -mt-0.5"
                        >
                            <div class="grid grid-cols-12 gap-4 p-3 gap-y-3">
                                <div class="col-span-6">
                                    <FormLabel htmlFor="input-filter-1" class="text-xs">
                                        File Name
                                    </FormLabel>
                                    <FormInput
                                        id="input-filter-1"
                                        type="text"
                                        class="flex-1"
                                        placeholder="Type the file name"
                                    />
                                </div>
                                <div class="col-span-6">
                                    <FormLabel htmlFor="input-filter-2" class="text-xs">
                                        Shared With
                                    </FormLabel>
                                    <FormInput
                                        id="input-filter-2"
                                        type="text"
                                        class="flex-1"
                                        placeholder="example@gmail.com"
                                    />
                                </div>
                                <div class="col-span-6">
                                    <FormLabel htmlFor="input-filter-3" class="text-xs">
                                        Created At
                                    </FormLabel>
                                    <FormInput
                                        id="input-filter-3"
                                        type="text"
                                        class="flex-1"
                                        placeholder="Important Meeting"
                                    />
                                </div>
                                <div class="col-span-6">
                                    <FormLabel htmlFor="input-filter-4" class="text-xs">
                                        Size
                                    </FormLabel>
                                    <FormSelect id="input-filter-4" class="flex-1">
                                        <option>10</option>
                                        <option>25</option>
                                        <option>35</option>
                                        <option>50</option>
                                    </FormSelect>
                                </div>
                                <div class="flex items-center col-span-12 mt-3">
                                    <Button variant="secondary" class="w-32 ml-auto">
                                        Create Filter
                                    </Button>
                                    <Button variant="primary" class="w-32 ml-2"> Search</Button>
                                </div>
                            </div>
                        </Menu.Items>
                    </Menu>
                </div>
                <div class="flex w-full sm:w-auto">
                    <Button variant="primary" class="mr-2 shadow-md">
                        <Link href="/gallery/create">Загрузить новые файлы</Link>
                    </Button>
                </div>
            </div>
            <!-- END: File Manager Filter -->
            <template v-if="galleryList.length">
                <!-- BEGIN: Directory & Files -->
                <div class="grid grid-cols-12 gap-3 mt-5 intro-y sm:gap-6">
                    <div v-for="galleryItem in galleryList"
                        :key="galleryItem.id"
                        class="col-span-6 intro-y sm:col-span-4 md:col-span-3 2xl:col-span-2"
                    >
                        <div class="relative px-3 pt-8 pb-5 rounded-md file box sm:px-5 zoom-in">
<!--                            <div class="absolute top-0 left-0 mt-3 ml-3">-->
<!--                                <FormCheck.Input class="border" type="checkbox"/>-->
<!--                            </div>-->
                            <FileIcon class="w-3/5 mx-auto"
                                variant="image"
                                :src="getImagePath(galleryItem.path)"
                            />
                            <a href="" class="block mt-4 font-medium text-center truncate">
                                {{ galleryItem.name }}
                            </a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">
                                42 MB
                            </div>
                            <Menu class="absolute top-0 right-0 mt-3 ml-auto mr-2">
                                <Menu.Button as="a" class="block w-5 h-5" href="#">
                                    <Lucide icon="MoreVertical" class="w-5 h-5 text-slate-500"/>
                                </Menu.Button>
                                <Menu.Items class="w-40">
                                    <Menu.Item>
                                        <Lucide icon="Users" class="w-4 h-4 mr-2"/>
                                        Share File
                                    </Menu.Item>
                                    <Menu.Item @click="deleteRecord(galleryItem.id)">
                                        <Lucide icon="Trash" class="w-4 h-4 mr-2"/>
                                    </Menu.Item>
                                </Menu.Items>
                            </Menu>
                        </div>
                    </div>
                </div>
                <!-- END: Directory & Files -->
                <!-- BEGIN: Pagination -->
                <div class="flex flex-wrap items-center mt-6 intro-y sm:flex-row sm:flex-nowrap">
                    <ThePagination :data="galleryData" />
                </div>
                <!-- END: Pagination -->
            </template>
        </div>
    </div>
    <DeleteModal :is-open="isDeleteModalOpen"
                 :delete-url="deleteRecordUrl"
                 @cancel="isDeleteModalOpen = false"
    />
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";
import DeleteModal from "../../custom-components/DeleteModal.vue";

export default {
    layout: Layout,
    components: {
        Link,
        DeleteModal
    },
    props: {
        galleryData: Object
    },
    data() {
        return {
            deleteRecordUrl: '',
            isDeleteModalOpen: false
        }
    },
    computed: {
        galleryList() {
            return this.galleryData.data
        }
    },
    methods: {
        getImagePath(path) {
            return path.startsWith('http')
                ? path
                : `/uploads/${path}`
        },
        deleteRecord(id) {
            this.deleteRecordUrl = `/gallery/${id}`
            this.isDeleteModalOpen = true
        }
    }
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import {FormInput, FormLabel, FormSelect,} from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import {Menu} from "../../base-components/Headless";
import FileIcon from "../../base-components/FileIcon";
</script>
