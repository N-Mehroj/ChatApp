<script setup lang="ts">
import _ from "lodash";
import { ref } from "vue";
import fakerData from "../../utils/faker";
import Button from "../../base-components/Button";
import { FormInput, FormLabel, FormSwitch, FormTextarea } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import Tippy from "../../base-components/Tippy";
import Litepicker from "../../base-components/Litepicker";
import TomSelect from "../../base-components/TomSelect";
import { ClassicEditor } from "../../base-components/Ckeditor";
import { Menu, Tab } from "../../base-components/Headless";
</script>

<script lang="ts">

import { Link } from '@inertiajs/inertia-vue3'
import Layout from '../../Shared/Layout.vue'
import axios from 'axios'

export default {
    layout: Layout,
    components: {
        Link
    },
    props: {
        newsItem: {
            type: [Object],
            required: false
        },
        errors: Object,
    },
    computed: {
        pageTitle() {
            return this.newsItem ? 'Редактирование новостей' : 'Добавить новости'
        }
    },
    data() {
        return {
            form: {
                title_ru: '',
                title_uz: '',
                body_ru: '',
                body_uz: '',
                slug: '',
                image: '',
            },
            folder: '',
        }
    },

    beforeMount() {
        this.updateForm()
    },
    methods: {
        updateOrCreate() {
            if (this.newsItem) {
                this.updateNews()
            } else {
                this.createNews()
            }
        },
        updateNews() {
            this.$inertia.put(`/news/${this.newsItem.id}`, this.form)
        },
        createNews() {
            this.$inertia.post('/news', this.form)
        },
        updateForm() {
            let fields = {
                title_ru: '',
                title_uz: '',
                body_ru: '',
                body_uz: '',
                slug: '',
                image: '',
                redirectTo: 'edit'
            }

            if (this.newsItem) {
                fields = this.newsItem
            }
            this.form = this.$inertia.form(fields)
        },

    }
}
</script>

<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ pageTitle }}
        </h2>
    </div>

    <div class="grid grid-cols-12 gap-5 mt-5 intro-y">
        <div class="col-span-12 intro-y lg:col-span-8">
            <TheInput
                type="text"
                label="Название ру"
                v-model="form.title_ru"
            />
            <TheInput
                type="text"
                label="Название уз"
                v-model="form.title_uz"
            />
            <Slug v-model="form.slug"></Slug>
            <Tab.Group class="mt-5 overflow-hidden intro-y box">
                <div class="p-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
                    <div class="mt-5">
                        <FormLabel> Текст ру </FormLabel>
                        <ClassicEditor
                            type="text"
                            class="px-4 py-3 pr-10 intro-y !box"
                            v-model="form.body_ru"
                        />
                    </div>
                    <div class="mt-5">
                        <FormLabel> Текст уз </FormLabel>
                        <ClassicEditor
                            type="text"
                            class="px-4 py-3 pr-10 intro-y !box"
                            v-model="form.body_uz"
                        />
                    </div>
                </div>
            </Tab.Group>
            <div class="p-5 mt-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
                <div class="flex items-center pb-5 font-medium border-b border-slate-200/60 dark:border-darkmode-400">
                    <Lucide icon="ChevronDown" class="w-4 h-4 mr-2" />
                    Фото
                </div>
                <div class="mt-5">
                    <div class="mt-3">
                        <SingleFileUpload v-model="form.image" :folder="'news'"/>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
                <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
                    <Menu>
                        <Menu.Button @click="updateOrCreate('create')"
                                     :as="Button"
                                     variant="primary"
                                     class="flex items-center shadow-md"
                        >
                            Сохранить
                        </Menu.Button>
                    </Menu>
                </div>
            </div>
        </div>
    </div>
</template>
