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
                <Tab.Panels>
                    <Tab.Panel class="p-5">
                        <div class="mt-3">
                            <TheInput
                                type="text"
                                label="Название страницы RU"
                                v-model="form.page_title_ru" />
                        </div>
                        <div class="mt-3">
                            <FormInput
                                type="text"
                                label="Название страницы UZ"
                                v-model="form.page_title_uz" />
                        </div>
                        <div class="col-span-12 intro-y sm:col-span-6 mt-3">
                            <FormLabel htmlFor="input-wizard-6">Тип</FormLabel>
                            <FormSelect v-model="form.metable_type" id="input-wizard-6">
                                <option value="">Не выбрано</option>
                                <option v-for="type in types" :value="type.slug">{{ type.label_ru }}</option>
                            </FormSelect>
                        </div>

                        <div class="col-span-12 intro-y sm:col-span-6 mt-3">
                            <FormLabel htmlFor="input-wizard-6">ID Типа</FormLabel>
                            <FormSelect v-model="form.metable_id" id="input-wizard-6">
                                <option value="">Не выбрано</option>
                                <option :value="1">Вариант 1</option>
                                <option :value="2">Вариант 2</option>
                                <option :value="3">Вариант 3</option>
                            </FormSelect>
                        </div>
                        <div class="mt-3">
                            <TheInput
                                type="text"
                                label="Мета название страницы RU"
                                v-model="form.meta_title_ru" />
                        </div>
                        <div class="mt-3">
                            <TheInput
                                type="text"
                                label="Мета название страницы UZ"
                                v-model="form.meta_title_uz" />
                        </div>
                        <div class="mt-3">
                            <FormLabel>Мета контент RU</FormLabel>
                            <ClassicEditor v-model="form.meta_description_ru" />
                        </div>
                        <div class="mt-3">
                            <FormLabel>Мета контент UZ</FormLabel>
                            <ClassicEditor v-model="form.meta_description_uz" />
                        </div>
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
import ClassicEditor from "../../base-components/Ckeditor/ClassicEditor.vue";
import axios from 'axios'
import FormInput from "../../base-components/Form/FormInput.vue";

export default {
    layout: Layout,
    components: {
        FormInput,
        Link,
        ClassicEditor
    },
    props: {
        seoMeta: {
            type: Object,
            required: false
        },
        errors: Object
    },
    data() {
        return {
            form: {
                page_title_ru: '',
                page_title_uz: '',
                metable_type: '',
                metable_id: '',
                meta_title_ru: '',
                meta_title_uz: '',
                meta_description_ru: '',
                meta_description_uz: '',
            },
            types: [],
            ids: [],
        }
    },
    beforeMount() {
        if (this.seoMeta) {
            this.form = this.seoMeta
        }
    },
    mounted () {
        this.getSeoTypes()
        this.getSeoIds()
    },
    computed: {
        pageTitle() {
            return this.seoMeta ? 'Редактирование SEO метаданных' : 'Добавление SEO метаданных'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.seoMeta) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/seo-metas/${this.seoMeta.id}`, this.form)
        },
        create() {
            this.$inertia.post('/seo-metas', this.form)
        },
        async getSeoTypes() {
            await axios.get('/seo-metas/get-types')
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
