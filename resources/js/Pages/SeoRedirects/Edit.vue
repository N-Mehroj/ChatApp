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
                        <TheInput class="mt-0"
                                  label="Редирект с"
                                  v-model="form.from_url"
                        />
                        <TheInput label="Редирект на"
                                  v-model="form.to_url"
                        />
                        <FormLabel class="mt-3">Статус код</FormLabel>
                        <FormSelect v-model="form.status_code">
                            <option value="301">301: Перемещено навсегда</option>
                            <option value="302">302: Перемещено временно</option>
                            <option value="303">303: Смотреть другое</option>
                            <option value="410">410: Удалено</option>
                        </FormSelect>
                        <TheInput label="Комментарий"
                                  v-model="form.comment"
                        />
                        <FormSwitch class="flex flex-col items-start mt-3">
                            <FormSwitch.Label htmlFor="post-form-5" class="mb-2 ml-0">
                                Активность
                            </FormSwitch.Label>
                            <FormSwitch.Input id="post-form-5" type="checkbox" v-model="form.is_active" />
                        </FormSwitch>
                        <FormSwitch class="flex flex-col items-start mt-3">
                            <FormSwitch.Label htmlFor="post-form-5" class="mb-2 ml-0">
                                Все вхождения
                            </FormSwitch.Label>
                            <FormSwitch.Input id="post-form-5" type="checkbox" v-model="form.is_with_entries" />
                        </FormSwitch>
                        <FormSwitch class="flex flex-col items-start mt-3">
                            <FormSwitch.Label htmlFor="post-form-5" class="mb-2 ml-0">
                                Использовать регулярные выражения
                            </FormSwitch.Label>
                            <FormSwitch.Input id="post-form-5" type="checkbox" v-model="form.is_regex" />
                        </FormSwitch>
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
        seoRedirect: {
            type: Object,
            required: false
        },
        errors: Object
    },
    data() {
        return {
            form: {
                from_url: '',
                to_url: '',
                status_code: 301,
                is_active: false,
                comment: '',
                is_with_entries: false,
                is_regex: '',
            },
        }
    },
    beforeMount() {
        if (this.seoRedirect) {
            this.form = this.seoRedirect
        }
    },
    computed: {
        pageTitle() {
            return this.seoRedirect ? 'Редактирование редиректа' : 'Добавление редиректа'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.seoRedirect) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/seo-redirects/${this.seoRedirect.id}`, this.form)
        },
        create() {
            this.$inertia.post('/seo-redirects', this.form)
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
import FormSelect from "../../base-components/Form/FormSelect.vue";
import FormSwitch from "../../base-components/Form/FormSwitch";
</script>
