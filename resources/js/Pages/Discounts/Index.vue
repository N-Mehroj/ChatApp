<template>
    <h2 class="mt-10 text-lg font-medium intro-y">Все скидки</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div
            class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap"
        >
            <Button variant="primary" class="mr-2 shadow-md">
                <Link href="discounts/create">
                    Добавить скидки
                </Link>
            </Button>
            <div v-if="discounts.total" class="hidden mx-auto md:block text-slate-500">
                Showing {{ discounts.from }} to {{ discounts.to }} of {{ discounts.total }} entries
            </div>
            <div v-else class="hidden mx-auto md:block text-slate-500">
                Скидки не найдены
            </div>
            <div class="w-full mt-3 sm:w-auto sm:mt-0 sm:ml-auto md:ml-0">
                <div class="relative w-56 text-slate-500">
                    <FormInput
                        type="text"
                        class="w-56 pr-10 !box"
                        placeholder="Search..."
                    />
                    <Lucide
                        icon="Search"
                        class="absolute inset-y-0 right-0 w-4 h-4 my-auto mr-3"
                    />
                </div>
            </div>
        </div>
        <template v-if="discountsList.length">
            <!-- BEGIN: Data List -->
			<div class="col-span-12 overflow-x-auto intro-y">
                <Table class="border-spacing-y-[10px] border-separate -mt-2">
                    <Table.Thead>
                        <Table.Tr>
                            <Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Название</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Описание</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Процент скидки</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Дата с</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Дата до</Table.Th>
                        </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                        <Table.Tr
                            v-for="discount in discountsList"
                            :key="discount.id"
                            class="intro-x"
                        >
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <a href="" class="font-medium whitespace-nowrap">
                                    {{ discount.id }}
                                </a>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ discount.title_ru }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ discount.description_ru }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ discount.percentage }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ discount.start_date }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ discount.end_date }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
                            >
                                <div class="flex items-center justify-center">
                                    <Link class="flex items-center mr-3" :href="`/sale/discounts/${discount.id}/edit`">
                                        <Lucide icon="CheckSquare" class="w-4 h-4 mr-1 text-primary" />

                                    </Link>
                                    <a
                                        class="flex items-center text-danger"
                                        href="#"
                                        @click.prevent="deleteRecord(discount.id)"
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
                <ThePagination :data="discounts" />
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
        discounts: Object
    },
    data() {
        return {
            isDeleteModalOpen: false,
            deleteRecordUrl: ''
        }
    },
    computed: {
        discountsList() {
            return this.discounts.data
        }
    },
    methods: {
        deleteRecord(id) {
            this.deleteRecordUrl = `/sale/discounts/${id}`
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
