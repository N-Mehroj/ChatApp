<template>
    <div class="mt-3">
        <FormLabel>{{ label }}</FormLabel>
        <div class="relative">
            <div class="absolute flex items-center justify-center w-10 h-full border rounded-l bg-slate-100 text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400">
                <Lucide icon="Calendar" class="w-4 h-4" />
            </div>
            <Litepicker
                v-model="value"
                @keyup.delete="deleteDay"
                :options="{
                    format: 'YYYY.MM.DD',
                    singleMode: false,
                    autoApply: this.autoApply,
                    numberOfColumns: 2,
                    numberOfMonths: 2,
                    dropdowns: {
                        minYear: 1950,
                        maxYear: 2050,
                        months: true,
                        years: true
                }}"
                class="pl-12"
            />
        </div>
    </div>
</template>

<script>
import Litepicker from "../base-components/Litepicker";
import Lucide from "../base-components/Lucide/Lucide.vue";
import FormLabel from "../base-components/Form/FormLabel.vue";

export default {
    name: "Datepicker",
    components: {
        Litepicker,
        Lucide,
        FormLabel
    },
    props: {
        modelValue: {
            type: [String, Number]
        },
        label: String,
    },
    data() {
        return {
            value: '',
            autoApply: false,
        }
    },
    mounted() {
        if (! this.modelValue) {
            this.value = ''
        } else {
            const date = new Date(this.modelValue)
            this.value = date.toLocaleDateString()
        }

        this.autoApply = true
    },
    watch: {
        value(newValue) {
            this.$emit('update:modelValue', newValue)
        }
    },
    methods: {
        deleteDay() {
            this.value = ''
        }
    }
}
</script>

<style scoped>

</style>
