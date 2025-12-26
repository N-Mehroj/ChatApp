<template>
    <h2 class="mt-10 text-lg font-medium intro-y">Все SEO метаданные</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div
            class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap"
        >
            <Button variant="primary" class="mr-2 shadow-md">
                <Link href="/seo-metas/create">
                    Добавить SEO Метаданные
                </Link>
            </Button>
            <div v-if="seoMetas.total" class="hidden mx-auto md:block text-slate-500">
                Показано с {{ seoMetas.from }} по {{ seoMetas.to }} из {{ seoMetas.total }} записей
            </div>
            <div v-else class="hidden mx-auto md:block text-slate-500">
                SEO метаданные не найдены
            </div>
            <Search />
        </div>
        <template v-if="seoMetasList.length">
            <!-- BEGIN: Data List -->
			<div class="col-span-12 overflow-x-auto intro-y">
                <Table class="border-spacing-y-[10px] border-separate -mt-2">
                    <Table.Thead>
                        <Table.Tr>
                            <Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Тип</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">ID типа</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Название страницы</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Мета название страницы</Table.Th>
                            <Table.Th class="text-center border-b-0 whitespace-nowrap">Действия</Table.Th>
                        </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                        <Table.Tr
                            v-for="seoMeta in seoMetasList"
                            :key="seoMeta.id"
                            class="intro-x"
                        >
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <a href="" class="font-medium whitespace-nowrap">
                                    {{ seoMeta.id }}
                                </a>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ seoMeta.metable_type }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ seoMeta.metable_id }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ seoMeta.page_title_ru }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ seoMeta.meta_title_ru }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
                            >
                                <div class="flex items-center justify-center">
                                    <Link class="flex items-center mr-3" :href="`/seo-metas/${seoMeta.id}/edit`">
                                        <Lucide icon="CheckSquare" class="w-4 h-4 mr-1 text-primary" />

                                    </Link>
                                    <a
                                        class="flex items-center text-danger"
                                        href="#"
                                        @click.prevent="deleteRecord(seoMeta.id)"
                                    >
                                        <Lucide icon="Trash2" class="w-4 h-4 mr-1" />
                                    </a>
                                </div>
                            </Table.Td>
                        </Table.Tr>
                    </Table.Tbody>
                </Table>
            </div>
            <!-- END: Data List -->
            <!-- BEGIN: Pagination -->
            <div
                class="flex flex-wrap items-center col-span-12 intro-y sm:flex-row sm:flex-nowrap"
            >
                <ThePagination :data="seoMetas" />
                <PerPageSelect />
            </div>
            <!-- END: Pagination -->
        </template>
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
        DeleteModal,
    },
    props: {
        seoMetas: Object
    },
    data() {
        return {
            isDeleteModalOpen: false,
            deleteRecordUrl: ''
        }
    },
    computed: {
        seoMetasList() {
            return this.seoMetas.data
        }
    },
    methods: {
        deleteRecord(id) {
            this.deleteRecordUrl = `/seo-metas/${id}`
            this.isDeleteModalOpen = true
        }
    }
}
</script>

<script setup lang="ts">
import Table from "../../base-components/Table";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import { FormInput } from "../../base-components/Form";
</script>
