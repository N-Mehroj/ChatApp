<template>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="mr-auto text-lg font-medium">{{ pageTitle }}</h2>
        <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-5 mt-5 intro-y">

        <div class="col-span-12 intro-y lg:col-span-8">
            <div class="p-5 intro-y box">
                <TheInput style="margin-top: 0" label="Название" v-model="form.title" />
                <Slug v-model="form.slug" />
                <TheInput label="Ссылка" v-model="form.link" placeholder="/users" />
                <div class="flex items-center mt-3">
                    <span><Lucide icon="Info" class="w-4 h-4 mr-2" /></span>
                    <span>Оставьте пустым если хотите создать родителя.</span>
                </div>
                <TheInput label="Сортировка" v-model="form.sort" />
                <TheInput label="Иконка" v-model="form.icon" />
                <div class="flex items-center mt-3">
                    <span><Lucide icon="Info" class="w-4 h-4 mr-2" /></span>
                    <span>
                        Иконки можно посмотреть на <a href="https://lucide.dev/" class="ml-1 underline" target="_blank">этом сайте</a>.
                        Запишите название иконки в таком регистре: AlarmCheck
                    </span>
                </div>
                <ItemSelect :items="permissions"
                            label="Permission"
                            name-value="name"
                            id-value="name"
                            v-model="form.permission"
                            class="mt-3"
                />
            </div>
            <TheErrorMessages :errors="errors" />
            <div class="flex w-full mt-4 sm:w-auto">
                <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4">
            <div class="p-5 intro-y box">
                <ModelSelect label="Родитель"
                             v-model="form.parent_id"
                             search-target="menu"
                             name-value="title"
                />
                <div>
                    <FormSwitch class="flex flex-col items-start mt-3">
                        <FormSwitch.Label htmlFor="post-form-5" class="mb-2 ml-0">
                            Активность
                        </FormSwitch.Label>
                        <FormSwitch.Input id="post-form-5" type="checkbox" v-model="form.status" true-value=1 false-value=0 />
                    </FormSwitch>
                </div>
            </div>
        </div>

    </div>
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";
import ItemSelect from "../../custom-components/ItemSelect.vue";

export default {
    layout: Layout,
    components: {
        Link,
        ItemSelect
    },
    props: {
        menu: {
            type: Object,
            required: false
        },
        permissions: Array,
        errors: Object
    },
    data() {
        return {
            form: {
                slug: '',
                title: '',
                link: '',
                icon: '',
                parent_id: '',
                status: '0',
                sort: 500,
                permission: ''
            }
        }
    },
    beforeMount() {
        if (this.menu) {
            this.form = this.menu
        }
    },
    computed: {
        pageTitle() {
            return this.menu ? 'Редактирование пункта меню' : 'Добавление пункта меню'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.menu) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/menu/${this.menu.id}`, this.form)
        },
        create() {
            this.$inertia.post('/menu', this.form)
        }
    }
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import TomSelect from "../../base-components/TomSelect";
import {TabGroup, TabPanel, TabPanels} from "@headlessui/vue";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";
import TheInput from "../../custom-components/TheInput.vue";
import Slug from "../../custom-components/Slug.vue";
import FormSwitch from "../../base-components/Form/FormSwitch/";
</script>
