<template>
    <div class="w-full mt-3 sm:w-auto sm:mt-0 sm:ml-auto md:ml-0">
        <div class="relative w-56 text-slate-500 flex w-full mt-4 sm:w-auto sm:mt-0">
            <FormInput
                type="text"
                class="w-56 pr-10 !box"
                :placeholder="placeholder"
                v-model="filter.q"
            />

            <Button
                variant='primary'
                @click.prevent="doFilter(filter)"
                class="w-full sm:w-10 ml-2">
                <Lucide
                    icon="Search"
                    class="w-4 h-4"
                />
            </Button>

            <Button
                variant='secondary'
                @click.prevent="reset()"
                class="w-full sm:w-10 ml-2">
                <Lucide
                    icon="X"
                    class="w-4 h-4"
                />
            </Button>
        </div>
    </div>
</template>

<script lang="ts">
import Button from "../base-components/Button/Button.vue";
import FormInput from "../base-components/Form/FormInput.vue";
import Lucide from "../base-components/Lucide/Lucide.vue";

export default {
    name: 'Search',
    props: {
        placeholder: {
            type: String,
            default: 'Поиск'
        }
    },
    components: {
        Button,
        FormInput,
        Lucide
    },
    data() {
        return {
            filter: {}
        }
    },
    methods: {
        doFilter(query) {
            this.$inertia.reload({
                data: {
                    filter: query
                }
            })
        },
        reset() {
            let url = window.location.href
            window.location.href = url.slice(0, url.indexOf('?'))
        },
    }
}
</script>
