<template>
    <h2 class="mt-10 text-lg font-medium intro-y">Статистика по товарам</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <Filter :fields="fields" v-model="filter" @search="doFilter"></Filter>
        <div class="col-span-12 overflow-auto intro-y lg:overflow-visible">
            <Table class="border-spacing-y-[10px] border-separate -mt-2">
                <Table.Thead>
                    <Table.Tr>
                        <Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
                        <Table.Th class="border-b-0 whitespace-nowrap">Название RU</Table.Th>
                        <Table.Th class="border-b-0 whitespace-nowrap">Просмотров товара</Table.Th>
                        <Table.Th class="border-b-0 whitespace-nowrap">Показов номера</Table.Th>
                        <Table.Th class="border-b-0 whitespace-nowrap">Переходов в Telegram</Table.Th>
                    </Table.Tr>
                </Table.Thead>
                <Table.Tbody>
                    <Table.Tr
                        v-for="category in categoriesList"
                        class="intro-x"
                    >
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            {{ category.id }}
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            {{ category.name_ru }}
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            {{  category.products_view }}
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            {{ category.products_show_phone }}
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            {{ category.products_telegram_click }}
                        </Table.Td>
                    </Table.Tr>
                </Table.Tbody>
            </Table>
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
import Search from "../../custom-components/Search.vue";
import Filter from "../../custom-components/Filter.vue";

export default {
    layout: Layout,
    components: {
        Search,
        Link,
        DeleteModal,
        Filter
    },
    props: {
        categories: Object
    },
    data() {
        return {
            isDeleteModalOpen: false,
            deleteRecordUrl: '',
            fields : [
                {'name': 'Дата с (категории)', 'field': 'create_after', 'value': ''},
                {'name': 'Дата до (категории)', 'field': 'create_before', 'value': ''},
                {'name': 'Статистика с', 'field': 'statistics_after', 'value': ''},
                {'name': 'Статистика до', 'field': 'statistics_before', 'value': ''},
            ],
        }
    },
    computed: {
        categoriesList() {
            return JSON.parse(JSON.stringify(this.categories))
        }
    },
    methods: {
        doFilter(filter) {
            let requestFilter = {}
            let statisticFilter = {}
            filter.forEach((item) => {
                if (item.value && item.field) {
                    if (item.field == 'statistics_after' ||  item.field == 'statistics_before') {
                        statisticFilter[item.field] = item.value
                    }
                    else {
                        requestFilter[item.field] = item.value
                    }
                }
            })

            this.$inertia.reload({
                data: {
                    filter: requestFilter,
                    statisticFilter: statisticFilter
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
import { FormInput } from "../../base-components/Form";
</script>
