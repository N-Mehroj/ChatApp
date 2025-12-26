<template>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="mr-auto text-lg font-medium">{{ pageTitle }}</h2>
        <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-5 mt-5 intro-y">

        <div class="col-span-12 intro-y lg:col-span-8">
            <Tab.Group class="overflow-hidden intro-y box">
<!--                <Tab.List-->
<!--                    class="flex-col border-transparent dark:border-transparent sm:flex-row bg-slate-200 dark:bg-darkmode-800"-->
<!--                >-->
<!--                    <Tab v-slot="{ selected }">-->
<!--                        <Tab.Button-->
<!--                            class="flex items-center justify-center w-full px-0 py-0 text-slate-500"-->
<!--                            :class="[-->
<!--                                {-->
<!--                                    'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300': !selected,-->
<!--                                },-->
<!--                                {-->
<!--                                    'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white': selected,-->
<!--                                },-->
<!--                            ]"-->
<!--                            as="button"-->
<!--                        >-->
<!--                            <div class="flex items-center justify-center w-full py-4">-->
<!--                                <Lucide icon="FileText" class="w-4 h-4 mr-2" />-->
<!--                                Основная информация-->
<!--                            </div>-->
<!--                        </Tab.Button>-->
<!--                    </Tab>-->
<!--                </Tab.List>-->
                <Tab.Panels>
                    <Tab.Panel class="p-5">
                        <TheInput class="mt-0"
                                  label="Название RU"
                                  v-model="form.label_ru"
                        />
                        <TheInput label="Символьный код"
                                  v-model="form.name"
                        />
                        <TheInput label="Название UZ"
                                  v-model="form.label_uz"
                        />
                    </Tab.Panel>
                </Tab.Panels>
            </Tab.Group>
            <TheErrorMessages :errors="errors" />
            <div class="flex w-full mt-4 sm:w-auto">
                <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
            </div>
        </div>

    </div>
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";

export default {
    layout: Layout,
    components: {
        Link
    },
    props: {
        role: {
            type: Object,
            required: false
        },
        roles: Array,
        errors: Object
    },
    data() {
        return {
            form: {
                name: '',
                label_ru: '',
                label_uz: '',
            }
        }
    },
    beforeMount() {
        if (this.role) {
            this.form = this.role
        }
    },
    computed: {
        pageTitle() {
            return this.role ? 'Редактирование роли' : 'Добавление роли'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.role) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/roles/${this.role.id}`, this.form)
        },
        create() {
            this.$inertia.post('/roles', this.form)
        }
    }

}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import TomSelect from "../../base-components/TomSelect";
import { Tab } from "../../base-components/Headless";
</script>
