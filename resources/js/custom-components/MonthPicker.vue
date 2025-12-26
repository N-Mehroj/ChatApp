<template>
    <div class="w-full ts-wrapper multi plugin-remove_button plugin-dropdown_input has-items focus input-active dropdown-active border-b"
         style="background-image: none"
    >
        <FormLabel>Дата создания</FormLabel>
        <input class="col-span-2 transition  w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90
                        focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40
                        dark:placeholder:text-slate-500/80"
                  type="text"
                  v-model="date"
                  @click="isOpen = !isOpen"
        />
        <Dialog class="w-full p-0 m-2"
            :open="isOpen"
            @close="closeModal"
        >

        <Dialog.Panel class="w-full" @mouseleave="isOpen = false">
            <div class="pb-5 text-left w-full">
                <div class="grid grid-cols-11 w-full" >
                    <Button variant="outline-secondary" class="col-span-1 mt-auto border-white" @click="prev" :aria-disabled="disableLeft === true">
                        <strong><Lucide icon="ChevronLeft" class="w-5 h-5 text-slate-700"/></strong>
                    </Button>
                    <ItemSelect class="col-span-5 ml-5"
                        :items="months"
                                 :id-value="'value'"
                                 :name-value="'label_ru'"
                                 v-model="month"
                                 />
                    <ItemSelect class="col-span-4 ml-0.5 mr-5"
                        :items="getYearList"
                                 :id-value="'value'"
                                 :name-value="'value'"
                                 v-model="year"
                    />
                    <Button variant="outline-secondary" class="col-span-1 mt-auto border-none" @click="next" :aria-disabled="disableRight === true">
                        <strong><Lucide icon="ChevronRight" class="w-5 h-5 text-slate-700"/></strong>
                    </Button>
                </div>

            </div>
            <div class="px-5 pb-8 text-right">
                <Button
                    variant="outline-secondary"
                    type="button"
                    class="w-20 mr-1"
                    @click="deleteDate"
                >
                    Cancel
                </Button>
                <Button

                    variant="primary"
                    type="button"
                    class="w-20"
                    @click="applyDate"
                >
                    Apply
                </Button>
            </div>
        </Dialog.Panel>
        </Dialog>
    </div>
</template>

<script lang="ts">
import Lucide from "../base-components/Lucide/Lucide.vue";
import FormLabel from "../base-components/Form/FormLabel.vue";
import TheInput from "../custom-components/TheInput.vue";
import {integer} from "@vuelidate/validators";
export default {
    name: 'MonthPicker',
    components: {
        Lucide,
        FormLabel,
        TheInput
    },
    props: {
        modelValue: {
            type: [String, Number]
        },
    },
    data() {
        return {
            isOpen: false,
            month: '',
            year: '',
            dateRange: '',
            disableRight: false,
            disableLeft: false,
            months: [
                {label_ru: 'Январь', value: '1'},
                {label_ru: 'Февраль', value: '2'},
                {label_ru: 'Март', value: '3'},
                {label_ru: 'Апрель', value: '4'},
                {label_ru: 'Май', value: '5'},
                {label_ru: 'Июнь', value: '6'},
                {label_ru: 'Июль', value: '7'},
                {label_ru: 'Август', value: '8'},
                {label_ru: 'Сентябрь', value: '9'},
                {label_ru: 'Октябрь', value: '10'},
                {label_ru: 'Ноябрь', value: '11'},
                {label_ru: 'Декабрь', value: '12'},
            ],
            years: [
            ],
        }
    },
    computed: {
        getYearList() {
            let current = new Date().getFullYear()

            for (let year = current-5; year <= current; year++) {
                this.years.push({
                    value: year
                })
            }
            return this.years
        },
        date() {
            if (this.dateRange) {
                return this.dateRange
            }
            else {
                return null
            }
        }
    },
    mounted() {
        if (this.modelValue) {
            this.dateRange = this.modelValue
        }
    },
    methods: {
        applyDate() {
            if (this.month && this.year) {
                if (this.year == new Date().getFullYear() && Number(this.month) > new Date().getMonth()+1) {
                    this.month = new Date().getMonth()+1
                    this.month = this.month.toString()
                }
                let month = ''
                if (Number(this.month) < 10) {
                    month = '0' + this.month
                }
                else {
                    month = this.month
                }
                let startDate = this.year + '.' + month + '.' + '01'
                let endDate = this.year + '.' + month + '.' + this.getDaysInMonth(Number(this.month), this.year)
                this.dateRange = startDate + ' - ' + endDate
                this.emitSelected(this.dateRange)
            }
        },
        deleteDate() {
            this.dateRange = null
            this.emitSelected(this.dateRange)
        },
        getDaysInMonth(month, year) {
            return new Date(year, month, 0).getDate();
        },
        emitSelected(date) {
            this.$emit('update:modelValue', date)
        },
        next() {
            if (Number(this.month) < 12) {
                if (! (Number(this.month) == new Date().getMonth()+1 && this.year == new Date().getFullYear())) {
                    this.month = Number(this.month) + 1
                    this.month = this.month.toString()
                }
            }
            else {
                this.disableRight = true
            }
        },
        prev() {
            if (Number(this.month) > 1) {
                this.month = Number(this.month) - 1
                this.month = this.month.toString()
            }
            else {
                this.disableLeft = true
            }
        }
    }
}
</script>
<script setup lang="ts">
import { FormInput, FormSelect } from "../base-components/Form";
import Button from "../base-components/Button";
</script>
