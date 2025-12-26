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
                        <div class="col-span-12 intro-y sm:col-span-6">
                            <FormLabel htmlFor="input-wizard-6">Тип</FormLabel>
                            <FormSelect v-model="form.textable_type" id="input-wizard-6">
                                <option value="">Не выбрано</option>
                                <option v-for="type in types" :value="type.slug">{{ type.label_ru }}</option>
                            </FormSelect>
                        </div>

                        <div class="col-span-12 intro-y sm:col-span-6">
                            <FormLabel htmlFor="input-wizard-6">ID Типа</FormLabel>
                            <FormSelect v-model="form.textable_id" id="input-wizard-6">
                               <option value="">Не выбрано</option>
                               <option :value="1">Вариант 1</option>
                               <option :value="2">Вариант 2</option>
                               <option :value="3">Вариант 3</option>
                            </FormSelect>
                        </div>

                        <div class="mt-3">
                            <FormLabel>Контент RU</FormLabel>
                            <ClassicEditor v-model="form.content_ru" />
                        </div>
                        <div class="mt-3">
                            <FormLabel>Контент UZ</FormLabel>
                            <ClassicEditor v-model="form.content_uz" />
                        </div>
                    </Tab.Panel>
                </Tab.Panels>
            </Tab.Group>
            <TheErrorMessages :errors="errors" />
            <div class="flex w-full mt-4 sm:w-auto">
                <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
            </div>
        </div>

<!--        <div class="col-span-12 lg:col-span-4">-->
<!--            <div class="p-5 intro-y box">-->
<!--                <div>-->
<!--                    <FormLabel htmlFor="post-form-3">Роли</FormLabel>-->
<!--                    <TomSelect-->
<!--                        id="post-form-3"-->
<!--                        v-model="form.roles"-->
<!--                        class="w-full"-->
<!--                        multiple-->
<!--                    >-->
<!--                        <option v-for="role in roles" :value="role.id" :key="role.id">{{ role.label_ru }}</option>-->
<!--                    </TomSelect>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

    </div>
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";
import ClassicEditor from "../../base-components/Ckeditor/ClassicEditor.vue";
import axios from 'axios'

export default {
    layout: Layout,
    components: {
        Link,
        ClassicEditor
    },
    props: {
        seoText: {
            type: Object,
            required: false
        },
        errors: Object
    },
    data() {
        return {
            form: {
                textable_type: '',
                textable_id: '',
                content_ru: '',
                content_uz: ''
            },
            types: [],
            ids: [],
        }
    },
    beforeMount() {
        if (this.seoText) {
            this.form = this.seoText
        }
    },
    mounted () {
        this.getSeoTypes()
    },
    computed: {
        pageTitle() {
            return this.seoText ? 'Редактирование SEO текста' : 'Добавление SEO текста'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.seoText) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/seo-texts/${this.seoText.id}`, this.form)
        },
        create() {
            this.$inertia.post('/seo-texts', this.form)
        },
        async getSeoTypes() {
            await axios.get('/seo-texts/get-types')
                .then(r => {
                    this.types = r.data.types
                })
                .catch(e => {
                    this.$buefy.toast.open({
                        message: `Error: ${e.message}`,
                        type: 'is-danger',
                        queue: false
                    })
                })
        },
    }
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel, FormSelect } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import TomSelect from "../../base-components/TomSelect";
import { Tab } from "../../base-components/Headless";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";

</script>
