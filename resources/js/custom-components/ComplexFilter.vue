<template>
    <div v-if="isFilterHidden === false" class="intro-y box col-span-12">
        <div class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400">
            <h2 class="font-medium text-base mr-auto">
                Фильтр
            </h2>
        </div>
        <div class="px-5 pt-1 pb-4" >
            <div class="grid grid-cols-12 gap-3" >
                <template v-for="(filterField, index) in filterFields">
                    <div v-if="filterField.type === 'date'" class="col-span-3">
                        <DatepickerRange v-model="_filters[index].value" :label="_filters[index].name"/>
                    </div>
                    <div v-if="filterField.type === 'number'" class="col-span-3">
                        <TheInput class="col-span-2"
                                  :label=_filters[index].name
                                  type="number"
                                  v-model="_filters[index].value"
                        />
                    </div>
                    <div v-if="filterField.type === 'text'" class="col-span-3">
                        <TheInput class="col-span-2"
                                  :label=_filters[index].name
                                  type="text"
                                  v-model="_filters[index].value"
                        />
                    </div>
                    <div v-if="filterField.type === 'itemMultipleSelect'" class="mt-3 col-span-3">
                        <ItemMultipleSelect :label="_filters[index].name"
                                            :items="_filters[index].options"
                                            :id-value="_filters[index].idValue"
                                            :name-value="_filters[index].nameValue"
                                            v-model="_filters[index].value"
                        />
                    </div>
                    <div v-if="filterField.type === 'modelSingleSelect'" class="mt-3 col-span-3">
                        <ModelSelect
                            :label="_filters[index].name"
                            v-model="_filters[index].value"
                            :search-target="_filters[index].search_target"
                        />
                    </div>
                    <div v-if="filterField.type === 'modelMultipleSelect'" class="mt-3 col-span-3">
                        <ModelSelectMultiple
                            :empty-option="_filters[index].search_without"
                            :name-value="_filters[index].nameValue"
                            :label="_filters[index].name"
                            v-model="_filters[index].value"
                            :search-target="_filters[index].search_target"
                            :search-default="_filters[index].search_default"
                        />
                    </div>
                    <div v-if="filterField.type === 'modelMultipleSelectDependent'" class="mt-3 col-span-3">
                        <ModelSelectMultipleDependant
                            :label="_filters[index].name"
                            v-model="_filters[index].value"
                            :search-target="_filters[index].search_target"
                            :search-by="_filters.filter(element => element.field === 'clinic_id')[0].value"
                        />
                    </div>
                    <div v-if="filterField.type === 'monthPicker'" class="mt-3 col-span-3">
                        <MonthPicker v-model="_filters[index].value"/>
                    </div>
                </template>
            </div>
        </div>
        <div class="flex items-center px-5 py-5 sm:py-3 border-t border-slate-200/60 dark:border-darkmode-400">
            <Button
                variant='primary'
                @click.prevent="doFilter(filter)"
                class="">
                Фильтровать
            </Button>
            <Button
                class="ml-2"
                variant="secondary"
                type="button"
                @click="reset"
            >
                Сбросить
            </Button>
        </div>
    </div>
</template>

<script lang="ts">

import ModelSelectMultipleDependant from '../custom-components/ModelSelectMultipleDependent.vue'
import MonthPicker from "../custom-components/MonthPicker.vue";


export default {
    components: {
        ModelSelectMultipleDependant,
        MonthPicker
    },
    props: {
        filterFields: {
            type: [Array, Object],
            default: false
        },
        isFilterHidden: {
            type: Boolean,
            default: false
        }
    },
    name: "ComplexFilter",
    data () {
        return {
            _filters: this.filterFields.map(el => (
                {
                    name: el.name,
                    field:el.field,
                    type: el.type,
                    options: el.options,
                    search_target: el.search_target,
                    value: '',
                    nameValue: el.nameValue,
                    idValue: el.idValue,
                    search_without: el.search_without,
                    filter_by: el.filter_by,
                    search_default: el.search_default
                }
            ))
        }
    },
    methods: {
        reset() {
            let url = window.location.href
            if (url.includes('?')) {
                window.location.href = url.slice(0, url.indexOf('?'))
            }
        },
        doFilter() {
            const filterObj = {}
            this._filters.forEach(el => {
                if (el.value) {
                    filterObj[el.field] = `${el.value}`
                }
            })
            this.$emit('search', filterObj)
        },
    }
}
</script>

<script setup lang="ts">

import Button from "../base-components/Button";
import { FormInput, FormLabel, FormSwitch } from "../base-components/Form";

</script>
