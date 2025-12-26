<template>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="mr-auto text-lg font-medium">{{ pageTitle }}</h2>
        <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button type="button" class="flex items-center ml-auto mr-2 !box sm:ml-0">
                <Link href="/permissions">Отмена</Link>
            </Button>
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
                                  label="Название"
                                  v-model="form.name"
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
        permission: {
            type: Object,
            required: false
        },
        permissions: Array,
        errors: Object
    },
    data() {
        return {
            form: {
                name: ''
            }
        }
    },
    beforeMount() {
        if (this.permission) {
            this.form = this.permission
        }
    },
    computed: {
        pageTitle() {
            return this.permission ? 'Редактирование разрешения' : 'Добавление разрешения'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.permission) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/permissions/${this.permission.id}`, this.form)
        },
        create() {
            this.$inertia.post('/permissions', this.form)
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
