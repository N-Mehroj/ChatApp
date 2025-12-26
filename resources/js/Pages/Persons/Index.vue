<template>
    <h2 class="mt-10 text-lg font-medium intro-y">Все люди</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div
            class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap"
        >
            <Button v-on:click="isFilterHidden = !isFilterHidden" variant="secondary" class="px-4 mr-2 shadow-md">
                <Lucide icon="Filter" class="w-5 h-5 mr-2"/> Фильтр
            </Button>
            <Button variant="primary" class="mr-2 shadow-md">
                <Link href="/persons/create">
                    Добавить человека
                </Link>
            </Button>
            <Menu>
                <Menu.Button :as="Button" class="px-2 !box">
                  <span class="flex items-center justify-center w-5 h-5">
                    <Lucide icon="Plus" class="w-4 h-4" />
                  </span>
                </Menu.Button>
                <Menu.Items class="w-40">
                    <Menu.Item>
                        <Lucide icon="Printer" class="w-4 h-4 mr-2" /> Print
                    </Menu.Item>
                    <Menu.Item>
                        <Lucide icon="FileText" class="w-4 h-4 mr-2" /> Export to Excel
                    </Menu.Item>
                    <Menu.Item>
                        <Lucide icon="FileText" class="w-4 h-4 mr-2" /> Export to PDF
                    </Menu.Item>
                </Menu.Items>
            </Menu>
            <div v-if="persons.total" class="hidden mx-auto md:block text-slate-500">
                Показано с {{ persons.from }} по {{ persons.to }} из {{ persons.total }} записей
            </div>
            <div v-else class="hidden mx-auto md:block text-slate-500">
                Врачи не найдены
            </div>
            <Search />
        </div>
        <ComplexFilter :is-filter-hidden="isFilterHidden" :filterFields="filterFields" @search="doFilter"/>

        <template v-if="personsList.length">
            <!-- BEGIN: Data List -->
            <div class="col-span-12 overflow-x-auto intro-y">
                <Table class="border-spacing-y-[10px] border-separate -mt-2">
                    <Table.Thead>
                        <Table.Tr>
                            <Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Имя Фамилия</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Организация</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Символьный код</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Статус</Table.Th>
                        </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                        <Table.Tr
                            v-for="person in personsList"
                            :key="person.id"
                            class="intro-x"
                        >
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <a href="" class="font-medium whitespace-nowrap">
                                    {{ person.id }}
                                </a>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <a href="" class="font-medium whitespace-nowrap">
                                    {{ person.name_ru }}
                                </a>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <div v-for="organization in person.organizations">
                                    {{ organization.name_ru }}
                                </div>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ person.slug }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ person.person_status.label_ru }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
                            >
                                <div class="flex items-center justify-center">
                                    <Link class="flex items-center mr-3 text-primary" :href="`/persons/${person.id}/edit`">
                                        <Lucide icon="CheckSquare" class="w-4 h-4 mr-1 text-primary" />
                                    </Link>
                                    <a
                                        class="flex items-center text-danger"
                                        href="#"
                                        @click.prevent="deleteRecord(person.id)"
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
                <ThePagination :data="persons" />
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
import Search from "../../custom-components/Search.vue";
import ComplexFilter from "../../custom-components/ComplexFilter.vue";


export default {
    layout: Layout,
    components: {
        Link,
        DeleteModal,
        Search,
        ComplexFilter
    },
    props: {
        persons: Object,
        statuses: Object,
    },
    data() {
        return {
            isDeleteModalOpen: false,
            isFilterHidden: true,
            deleteRecordUrl: '',
            filterFields: [
                {name: 'Клиника', field: 'organization', type:'modelMultipleSelect', search_target: 'organizations'  },
                {name: 'Статус', field: 'status', type: 'itemMultipleSelect', options: this.statuses, nameValue: 'label_ru', idValue: 'value'},
            ],
        }
    },
    computed: {
        personsList() {
            return this.persons.data
        }
    },
    methods: {
        deleteRecord(id) {
            this.deleteRecordUrl = `persons/${id}`
            this.isDeleteModalOpen = true
        },
        doFilter(filter) {
            this.$inertia.reload({
                data: {
                    filter,
                }
            })
        },
    }
}
</script>

<script setup lang="ts">
import Table from "../../base-components/Table";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import { Menu } from "../../base-components/Headless";
import { FormInput, FormSelect } from "../../base-components/Form";
</script>
