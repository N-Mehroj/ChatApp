<template>
    <h2 class="mt-10 text-lg font-medium intro-y">Пакеты</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- BEGIN: Data List -->
        <div class="col-span-12 overflow-auto intro-y lg:overflow-visible">
            <Table class="border-spacing-y-[10px] border-separate -mt-2">
                <Table.Thead>
                    <Table.Tr>
                        <Table.Th class="border-b-0 whitespace-nowrap">Название</Table.Th>
                        <Table.Th class="border-b-0 whitespace-nowrap">Версия</Table.Th>
                        <Table.Th class="text-center border-b-0 whitespace-nowrap">Статус</Table.Th>
                        <Table.Th class="text-center border-b-0 whitespace-nowrap">Вкл./Выкл.</Table.Th>
                        <Table.Th class="text-center border-b-0 whitespace-nowrap">Действия</Table.Th>
                    </Table.Tr>
                </Table.Thead>
                <Table.Tbody>
                    <Table.Tr
                        v-for="platformPackage in packages"
                        :key="platformPackage.slug"
                        class="intro-x"
                    >
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md  bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            {{ platformPackage.name }}
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            {{ platformPackage.version }}
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            <div class="flex items-center justify-center"
                                 :class="{ 'text-success': platformPackage.is_installed, 'text-danger': !platformPackage.is_installed }"
                            >
                                <Lucide :icon="platformPackage.is_on ? 'PackageCheck' : 'PackageMinus'"
                                        class="w-4 h-4 mr-2"
                                />
                                {{ platformPackage.is_installed ? "Уставновлен" : "Не установлен" }}
                            </div>
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                        >
                            <div class="flex items-center justify-center"
                                 :class="{ 'text-success': platformPackage.is_on, 'text-danger': !platformPackage.is_on }"
                            >
                                <FormSwitch class="mt-2">
                                    <FormSwitch.Input type="checkbox"
                                                      @input="handlePackageVisibility(platformPackage.slug)"
                                                      :checked="platformPackage.is_on"
                                                      :disabled="!platformPackage.is_installed || platformPackage.slug === 'core'"
                                    />
                                </FormSwitch>
                            </div>
                        </Table.Td>
                        <Table.Td
                            class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
                        >
                            <div class="flex items-center justify-center">
                                <Link v-if="platformPackage.is_installed && platformPackage.has_updates"
                                      class="flex items-center mr-3"
                                      :href="`/platform-packages/${platformPackage.slug}/update`"
                                      method="put"
                                      :data="{ 'version': platformPackage.update_version }"
                                >
                                    <Lucide icon="RefreshCw" class="w-4 h-4 mr-1" />
                                    Обновить
                                </Link>
                                <Link v-if="!platformPackage.is_installed"
                                      class="flex items-center mr-3 text-success"
                                      :href="`/platform-packages/${platformPackage.slug}/install`"
                                      method="put"
                                >
                                    <Lucide icon="CheckSquare" class="w-4 h-4 mr-1" />
                                    Установить
                                </Link>
                                <button
                                    v-if="platformPackage.is_installed && platformPackage.slug !== 'core'"
                                    class="flex items-center text-danger"
                                    @click="deleteRecord(platformPackage.slug)"
                                >
                                    <Lucide icon="Trash2" class="w-4 h-4 mr-1" />
                                    Удалить
                                </button>
                            </div>
                        </Table.Td>
                    </Table.Tr>
                </Table.Tbody>
            </Table>
        </div>
        <!-- END: Data List -->
    </div>
    <DeleteModal :is-open="isDeleteModalOpen"
                 :delete-url="deleteRecordUrl"
                 @cancel="isDeleteModalOpen = false"
    />

    <div v-if="havePlatformPackages">
        <h2 class="mt-10 text-lg font-medium intro-y">Не скачанные пакеты</h2>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <!-- BEGIN: Data List -->
            <div class="col-span-12 overflow-auto intro-y lg:overflow-visible">
                <Table class="border-spacing-y-[10px] border-separate -mt-2 opacity-75">
                    <Table.Thead>
                        <Table.Tr>
                            <Table.Th class="border-b-0 whitespace-nowrap">Название</Table.Th>
                            <Table.Th class="border-b-0 whitespace-nowrap">Версия</Table.Th>
                            <Table.Th class="text-center border-b-0 whitespace-nowrap">Статус</Table.Th>
                            <Table.Th class="text-center border-b-0 whitespace-nowrap">Действия</Table.Th>
                        </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                        <Table.Tr
                            v-for="platformPackage in platformPackages"
                            :key="platformPackage.slug"
                            class="intro-x"
                        >
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md  bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ platformPackage.name }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                {{ platformPackage.version }}
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
                            >
                                <div class="flex items-center justify-center text-danger">
                                    <Lucide icon="PackageMinus" class="w-4 h-4 mr-2"/>
                                    Не скачано
                                </div>
                            </Table.Td>
                            <Table.Td
                                class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
                            >
                                <div class="flex items-center justify-center opacity-100">
                                    <Link class="flex items-center mr-3 text-success"
                                          :href="`/platform-packages/${platformPackage.slug}/download`"
                                          method="get"
                                    >
                                        <Lucide icon="Download" class="w-4 h-4 mr-1" />
                                        Скачать
                                    </Link>
                                </div>
                            </Table.Td>
                        </Table.Tr>
                    </Table.Tbody>
                </Table>
            </div>
            <!-- END: Data List -->
        </div>
    </div>
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import DeleteModal from "../../custom-components/DeleteModal.vue";

export default {
    layout: Layout,
    components: {
        DeleteModal
    },
    props: {
        packages: Array,
        platformPackages: Array
    },
    data() {
        return {
            isDeleteModalOpen: false,
            deleteRecordUrl: ''
        }
    },
    computed: {
        havePlatformPackages() {
            return Object.keys(this.platformPackages).length
        }
    },
    methods: {
        deleteRecord(slug) {
            this.deleteRecordUrl = `/platform-packages/${slug}`
            this.isDeleteModalOpen = true
        },
        handlePackageVisibility(slug) {
            this.$inertia.put(`/platform-packages/${slug}/handle-visibility`)
        }
    }
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import { Link } from "@inertiajs/inertia-vue3";
import FormSwitch from "../../base-components/Form/FormSwitch";
</script>
