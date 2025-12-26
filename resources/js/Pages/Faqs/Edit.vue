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
                        <faq-questions v-model="form.questions" />
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
                <div class="col-span-12 intro-y sm:col-span-6">
                    <FormLabel>Тип</FormLabel>
                    <FormSelect v-model="form.type">
                        <option value="">Не выбрано</option>
                        <option v-for="type in types" :value="type.slug">
                            {{ type.label_ru }}
                        </option>
                    </FormSelect>
                </div>
                <div class="mt-3" v-if="form.type && types[form.type]">
                    <FormLabel>Константы</FormLabel>
                    <p v-html="types[form.type].constants"></p>
                    
                </div>
                <div class="mt-5" v-if="form.id">
                    <div class="mt-5">
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
import FaqQuestions from "../../custom-components/FaqQuestions.vue";
import ClassicEditor from "../../base-components/Ckeditor/ClassicEditor.vue";

export default {
    layout: Layout,
    components: {
        Link,
        FormInput,
        FaqQuestions,
        ClassicEditor
    },
    props: {
        faq: {
            type: Object,
            required: false
        },
        types: Object,
        errors: Object
    },
    data() {
        return {
            form: {
                type: '',
                questions: []
            }
        }
    },
    beforeMount() {
        if (this.faq) {
            this.form = this.faq
        }
    },
    computed: {
        pageTitle() {
            return this.faq ? 'Редактирование FAQ' : 'Добавление FAQ'
        }
    },
    methods: {
        updateOrCreate() {
            if (this.faq) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/faqs/${this.faq.id}`, this.form)
        },
        create() {
            this.$inertia.post('/faqs', this.form)
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
import TheInput from "../../custom-components/TheInput.vue";
import FormTextarea from "../../base-components/Form/FormTextarea.vue";

</script>
