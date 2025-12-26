<template>
    <div>
        <select :class="selectClasses" v-model="perPage" class="w-20 mt-3 !box sm:mt-0">
            <option v-for="option in options" :value="option">{{ option }}</option>
        </select>
    </div>
</template>

<script>
import FormSelect from "../base-components/Form/FormSelect.vue";

export default {
    name: "PerPageSelect",
    data() {
        return {
            perPage: 10,
            options: [10, 25, 35, 50]
        }
    },
    components: {
        FormSelect
    },
    computed: {
        selectClasses() {
            return [
                "disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50",
                "[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50",
                "transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8",
                "focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40",
                "dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50",
            ]
        }
    },
    watch: {
        perPage() {
            this.$inertia.reload({
                data: {
                    per_page: this.perPage
                }
            })
        }
    },
    mounted() {
        const url = window.location.search.substring(1);
        const params = new URLSearchParams(url);

        if (params.has('per_page')) {
            this.perPage = params.get('per_page')
            console.log(this.perPage)
        }
    }
}
</script>
