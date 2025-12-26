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
                <Tab.List
                    class="flex-col border-transparent dark:border-transparent sm:flex-row bg-slate-200 dark:bg-darkmode-800"
                >
                    <Tab v-slot="{ selected }">
                        <Tab.Button
                            class="flex items-center justify-center w-full px-0 py-0 text-slate-500"
                            :class="[
                                {
                                    'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300': !selected,
                                },
                                {
                                    'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white': selected,
                                },
                            ]"
                            as="button"
                        >
                            <div class="flex items-center justify-center w-full py-4">
                                <Lucide icon="Info" class="w-4 h-4 mr-2" />
                                Основная информация
                            </div>
                        </Tab.Button>
                    </Tab>
                    <Tab v-slot="{ selected }">
                        <Tab.Button
                            class="flex items-center justify-center w-full px-0 py-0 text-slate-500"
                            :class="[
                                {
                                    'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300': !selected,
                                },
                                {
                                    'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white': selected,
                                },
                            ]"
                            as="button"
                        >
                            <div class="flex items-center justify-center w-full py-4">
                                <Lucide icon="Info" class="w-4 h-4 mr-2" />
                                Дополнительно
                            </div>
                        </Tab.Button>
                    </Tab>
                </Tab.List>
                <Tab.Panels>
                    <Tab.Panel class="p-5">
                        <TheInput class="mt-0"
                                  label="Название RU"
                                  v-model="form.name_ru"
                        />
                        <TheInput label="Название UZ"
                                  v-model="form.name_uz"
                        />
                        <Slug v-model="form.slug"></Slug>
                        <TheInput label="META TITLE RU"
                                  v-model="form.meta_title_ru"
                        />
                        <TheInput label="META TITLE UZ"
                                  v-model="form.meta_title_uz"
                        />
                        <TheInput label="META DESCRIPTION RU"
                                  v-model="form.meta_description_ru"
                        />
                        <TheInput label="META DESCRIPTION UZ"
                                  v-model="form.meta_description_uz"
                        />
                    </Tab.Panel>
                    <Tab.Panel class="p-5">
                        <div class="mt-0">
                            <FormLabel>Контент RU</FormLabel>
                            <ckeditor v-model="form.content_ru" :config="editorConfigRu"></ckeditor>
                        </div>
                        <div class="mt-3">
                            <FormLabel>Контент UZ</FormLabel>
                            <ckeditor v-model="form.content_uz" :config="editorConfigUz"></ckeditor>
                        </div>
                    </Tab.Panel>
                </Tab.Panels>
            </Tab.Group>
            <TheErrorMessages :errors="errors" />
            <div class="flex w-full mt-4 sm:w-auto">
                <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-4">
            <div class="p-5 intro-y box">
                <div v-if="form.id">
                    <div>
                        <b>ID</b>
                        <p class="pt-2">{{form.id}}</p>
                    </div>
                    <div class="mt-5">
                        <b>Дата создания</b>
                        <p class="pt-2">{{form.created_at}}</p>
                    </div>
                    <div class="mt-5">
                        <b>Дата изменения</b>
                        <p class="pt-2">{{form.updated_at}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";
import FormInput from "../../base-components/Form/FormInput.vue";

export default {
    layout: Layout,
    components: {
        FormInput,
        Link
    },
    props: {
        staticPage: {
            type: Object,
            required: false
        },
        errors: Object
    },
    data() {
        return {
            form: {
                name_ru: '',
                name_uz: '',
                slug: '',
                meta_title_ru: '',
                meta_title_uz: '',
                meta_description_ru: '',
                meta_description_uz: '',
                content_ru: '',
                content_uz: ''
            },
            editorConfigRu: {
                allowedContent: true
            },
            editorConfigUz: {
                allowedContent: true
            }
        }
    },
    beforeMount() {
        if (this.staticPage) {
            this.form = this.staticPage
            this.form.content_ru = this.form.content_ru || ''
            this.form.content_uz = this.form.content_uz || ''
        }
    },
    computed: {
        pageTitle() {
            return this.staticPage ? 'Редактирование страницы' : 'Добавление страницы'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.staticPage) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/static-pages/${this.staticPage.id}`, this.form)
        },
        create() {
            this.$inertia.post('/static-pages', this.form)
        },
    }
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import { Tab } from "../../base-components/Headless";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";
import TheInput from "../../custom-components/TheInput.vue";
import { component as ckeditor } from '@mayasabha/ckeditor4-vue3'
</script>
