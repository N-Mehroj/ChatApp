<template>
    <h2 class="mt-10 text-lg font-medium intro-y">Меню</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div
            class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap"
        >
            <Button variant="primary" class="mr-2 shadow-md">
                <Link href="/menu/create">
                    Добавить пункт меню
                </Link>
            </Button>
        </div>
        <template v-if="menuList && menuList.length">
            <!-- BEGIN: Data List -->
            <div class="col-span-12 overflow-x-auto intro-y">
                <Table class="border-spacing-y-[10px] border-separate -mt-2">
                    <Table.Thead>
                        <Table.Tr>
                            <Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Заголовок</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Ссылка</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Статус</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Сортировка</Table.Th>
                            <Table.Th class="text-center border-b-0 whitespace-nowrap">Действия</Table.Th>
                        </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                        <Table.Tr
                            v-for="menu in menuList"
                            :key="menu.id"
                            class="intro-x"
                        >
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <Link :href="`/menu/${menu.id}/edit`" class="font-medium whitespace-nowrap">
                                    {{ menu.id }}
                                </Link>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <Link :href="`/menu/${menu.id}/edit`" class="font-medium whitespace-nowrap">
                                    {{ menu.title }}
                                </Link>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ menu.link }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <FormSwitch>
                                    <FormSwitch.Input type="checkbox"
                                                      @input="toggleMenuStatus(menu.id)"
                                                      :checked="menu.status"
                                    />
                                </FormSwitch>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ menu.sort }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
                            >
                                <div class="flex items-center justify-center text-primary">
                                    <Link class="flex items-center mr-3" :href="`/menu/${menu.id}/edit`">
                                        <Lucide icon="CheckSquare" class=" w-4 h-4 mr-1" />
                                        Редактировать
                                    </Link>
                                </div>
                            </Table.Td>
                        </Table.Tr>
                    </Table.Tbody>
                </Table>
            </div>
            <!-- END: Data List -->
            <div
                class="flex flex-wrap items-center col-span-12 intro-y sm:flex-row sm:flex-nowrap"
            >
                <ThePagination :data="menu" />
            </div>
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
        menu: Object
    },
    data() {
        return {
            isDeleteModalOpen: false,
            deleteRecordUrl: '',
        }
    },
    computed: {
        menuList() {
            return this.menu.data
        }
    },
    methods: {
        toggleMenuStatus(id) {
            this.$inertia.put(`/menu/${id}/toggle-status`)
        }
    }
}
</script>

<script setup lang="ts">
import Table from "../../base-components/Table";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import FormSwitch from "../../base-components/Form/FormSwitch";
</script>
