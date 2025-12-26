<template>
    <div class="flex flex-col sm:flex-row flex-wrap col-span-12">
        <form
            id="tabulator-html-filter-form"
            class="w-full xl:flex sm:mr-auto flex-wrap"
            @submit="
          (e) => {
            e.preventDefault();
            doFilter();
          }
        "
        >
            <div class="block w-full">
                <div class="w-full p-2 bg-white rounded">
                    <div class="flex sm:flex-row mt-2" v-for="(filter, index) in filters" :key="filter.id">
                        <div class="items-center sm:flex sm:mr-4">
                            <label class="flex-none w-12 mr-2 xl:w-auto xl:flex-initial">
                                Поле
                            </label>
                            <FormSelect
                                id="tabulator-html-filter-field"
                                v-model="filter.field"
                                class="w-full mt-2  sm:mt-0 sm:w-auto"
                            >
                                <option v-for="field in fields" :key="field.name" :value="field.field">{{ field.name }}</option>
                            </FormSelect>

                        </div>
                        <div class="items-center mt-2 sm:flex sm:mr-4 xl:mt-0">
                            <label class="flex-none w-12 mr-2 xl:w-auto xl:flex-initial">
                                Значение
                            </label>
                            <div v-if="filter.field === 'create_before' || filter.field === 'statistics_before' || filter.field === 'create_after'|| filter.field === 'statistics_after' ">
                                <Litepicker
                                    v-model="filter.value"
                                    :options="{
                                        autoApply: true,
                                        format: 'YYYY-MM-DD',
                                        dropdowns: {
                                            minYear: 2020,
                                            maxYear: null,
                                            months: true,
                                            years: true,

                                        },
                                    }"
                                    class="block w-56"
                                />
                            </div>
<!--                            <div v-else-if="filter.field === 'create_after'|| filter.field === 'statistics_after' ">-->
<!--                                <Litepicker-->
<!--                                    v-model="filter.value"-->
<!--                                    :options="{-->
<!--                                        autoApply: true,-->
<!--                                        format: 'YYYY-MM-DD',-->
<!--                                        dropdowns: {-->
<!--                                            minYear: 2020,-->
<!--                                            maxYear: null,-->
<!--                                            months: true,-->
<!--                                            years: true,-->
<!--                                        },-->
<!--                                    }"-->
<!--                                    class="block w-56"-->
<!--                                />-->
<!--                            </div>-->
                            <div v-else-if="filter.field === 'organization_id'">
                                <ModelSelect
                                    v-model="filter.value"
                                    :name-value = "'name'"
                                    search-target="organizations"
                                />
                            </div>
                            <div v-else-if="filter.field === 'user_id'">
                                <ModelSelect
                                    v-model="filter.value"
                                    :name-value = "'first_name'"
                                    search-target="users"
                                />
                            </div>
                            <div v-else>
                                <FormInput
                                    id="tabulator-html-filter-value"
                                    v-model="filter.value"
                                    type="text"
                                    class="w-full mt-2  sm:mt-0 sm:w-auto"
                                />
                            </div>
                        </div>
                        <Button
                            variant='primary'
                            type="button"
                            class="w-full sm:w-10 mr-2"
                            @click="addFilter"
                        >
                            <Lucide icon="Plus" class="w-4 h-4" />
                        </Button>
                        <Button
                            variant='primary'
                            type="button"
                            class="w-full sm:w-10"
                            @click="removeFilter(filter)"
                        >
                            <Lucide icon="Minus" class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
                <div class="mt-2 xl:mt-2 sm:flex-row">
                    <Button
                        id="tabulator-html-filter-go"
                        variant="primary"
                        type="button"
                        class="w-full sm:w-20 sm:mr-2"
                        @click="doFilter"
                    >
                        Найти
                    </Button>
                    <Button
                        variant="primary"
                        type="button"
                        class="w-full sm:w-20"
                        @click="reset"
                    >
                        Сбросить
                    </Button>
                </div>
            </div>
        </form>
    </div>
</template>
<script  lang="ts">


export default{
    name: 'Filter',
    props: {
        fields: {
            type: [Array, Object],
            default: false
        },
    },
    data () {
        return {
            filters: [{
                id: 0,
                field: null,
                value: null
            }],
            emitValues: {
                filterFields: [],
                filterVals: [],
            },
            count: 0
        }
    },
    methods:{
        doFilter() {
            this.$emit('search', this.filters)
        },
        addFilter() {
            this.count+=1
            this.filters.push({
                id: this.count,
                field: null,
                value: null
            })
        },
        removeFilter(filter) {
            this.filters.splice(this.filters.indexOf(filter), 1);
        },
        reset() {
            let url = window.location.href
            window.location.href = url.slice(0, url.indexOf('?'))
        },
    }
}
</script>

<script setup lang="ts">
import Lucide from "../base-components/Lucide";
import { Menu } from "../base-components/Headless";
import Button from "../base-components/Button";
import { FormInput, FormSelect } from "../base-components/Form";
import * as xlsx from "xlsx";
import { onMounted, ref, reactive } from "vue";
import { createIcons, icons } from "lucide";
import { TabulatorFull as Tabulator } from "tabulator-tables";
import { stringToHTML } from "../utils/helper";
import Litepicker from "../base-components/Litepicker";
</script>
