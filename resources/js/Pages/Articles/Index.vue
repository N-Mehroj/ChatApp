<script lang="ts">
import Layout from '../../Shared/Layout.vue'
import {Link} from "@inertiajs/inertia-vue3";
import DeleteModal from "../../custom-components/DeleteModal.vue";


export default {
    layout: Layout,
    components: {
        Link,
    },
    props: {
        articles: Object
    },
    data() {
        return {
            isDeleteModalOpen: false,
            deleteRecordUrl: ''
        }
    },
    computed: {
        articleList() {
            return this.articles.data
        },
        links() {
            return this.articles.links
        },
    },
    methods: {
        deleteRecord(id) {
            this.deleteRecordUrl = `/articles/${id}`
            this.isDeleteModalOpen = true
        },
    }
}

</script>
<script setup lang="ts">
import _ from "lodash";
import { ref } from "vue";
import fakerData from "../../utils/faker";
import Button from "../../base-components/Button";
import Pagination from "../../base-components/Pagination";
import { FormInput, FormSelect } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import Tippy from "../../base-components/Tippy";
import { Dialog, Menu } from "../../base-components/Headless";
import Table from "../../base-components/Table";

const deleteConfirmationModal = ref(false);
const deleteButtonRef = ref(null);

const setDeleteConfirmationModal = (value: boolean) => {
    deleteConfirmationModal.value = value;
};
</script>

<template>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="mr-auto text-lg font-medium"> Список статей </h2>
        <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Link href="/articles/create" class="btn btn-primary shadow-md mr-2">
                <Button variant="primary" class="mr-2 shadow-md"> Добавить статью </Button>
            </Link>

        </div>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5 intro-y">
        <div
            v-for="article in articleList"
            class="col-span-12 intro-y md:col-span-6 xl:col-span-4 box"
        >
            <div
                class="flex items-center px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400"
            >
                <Menu class="ml-3">
                    <Menu.Button tag="a" href="#" class="w-5 h-5 text-slate-500">
                        <Lucide icon="MoreVertical" class="w-5 h-5" />
                    </Menu.Button>
                    <Menu.Items class="w-40">
                        <Menu.Item>
                            <Link class="flex items-center mr-3" :href="`/articles/${article.id}/edit`">
                                <Lucide icon="Edit2" class="w-4 h-4 mr-2" />
                            </Link>

                        </Menu.Item>
                        <Menu.Item>
                            <a
                                class="flex items-center text-danger"
                                href="#"
                                @click.prevent="deleteRecord(article.id)"
                            >
                                <Lucide icon="Trash2" class="w-4 h-4 mr-1" />
                            </a>
                        </Menu.Item>
                    </Menu.Items>
                </Menu>
            </div>
            <div class="p-5">
                <div class="h-40 2xl:h-56 image-fit">
                    <img
                        class="rounded-md"
                        :src="'/uploads/' + article.image"
                    />
                </div>
                <a href="" class="block mt-5 text-base font-medium">
                    {{ article.title_ru }}
                </a>
                <div class="mt-2 text-slate-600 dark:text-slate-500">
                    {{ article.body_ru }}
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center col-span-12 intro-y sm:flex-row sm:flex-nowrap">
            <div class="flex flex-wrap items-center col-span-12 intro-y sm:flex-row sm:flex-nowrap">
                <ThePagination :data="articles" />
            </div>
        </div>
        <DeleteModal :is-open="isDeleteModalOpen"
                     :delete-url="deleteRecordUrl"
                     @cancel="isDeleteModalOpen = false"
        />
    </div>
</template>



