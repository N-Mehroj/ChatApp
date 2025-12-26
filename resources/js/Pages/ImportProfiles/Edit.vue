<template>
    <div class="flex items-center mt-8">
        <h2 class="mr-auto text-lg font-medium intro-y">Профиль импорта</h2>
    </div>
    <!-- BEGIN: Wizard Layout -->
    <div class="py-10 mt-5 intro-y box sm:py-20">

        <!-- HEADING -->
        <div class="relative before:hidden before:lg:block before:absolute before:w-[60%] before:h-[3px] before:top-0 before:bottom-0 before:mt-4 before:bg-slate-100 before:dark:bg-darkmode-400 flex flex-col lg:flex-row justify-center px-5 sm:px-20">
            <div class="z-10 flex items-center flex-1 intro-x lg:text-center lg:block">
                <Button variant="primary"
                        :class="getButtonClasses(1)"
                        @click="activeStep = 1"
                >
                    1
                </Button>
                <div :class="getHeadingClasses(1)">
                    Общее
                </div>
            </div>
            <div class="z-10 flex items-center flex-1 intro-x lg:text-center lg:block">
                <Button variant="primary"
                        :class="getButtonClasses(2)"
                        @click="activeStep = 2"
                >
                    2
                </Button>
                <div :class="getHeadingClasses(2)">
                    Заголовки в файле
                </div>
            </div>
            <div class="z-10 flex items-center flex-1 intro-x lg:text-center lg:block">
                <Button variant="primary"
                        :class="getButtonClasses(3)"
                        @click="activeStep = 3"
                >
                    3
                </Button>
                <div :class="getHeadingClasses(3)">
                    Импорт
                </div>
            </div>
        </div>
        <!-- HEADING -->

        <!-- CONTENTS -->
        <div v-if="activeStep === 1"
             class="px-5 pt-10 mt-10 border-t sm:px-20 border-slate-200/60 dark:border-darkmode-400"
        >
            <div class="grid grid-cols-12 gap-4 mt-5 gap-y-5">
                <div class="col-span-12 intro-y sm:col-span-6">
                    <TheInput class="mt-0"
                              label="Название"
                              v-model="form.name"
                    />
                </div>
                <div class="col-span-12 intro-y sm:col-span-6">
                    <ItemSelect label="Тип"
                                :items="types"
                                id-value="value"
                                name-value="label_ru"
                                v-model="form.type"
                    />
                </div>
                <div class="col-span-12 intro-y sm:col-span-6">
                    <ItemSelect label="Модель"
                                :items="models"
                                id-value="value"
                                name-value="label"
                                v-model="form.config.model"
                    />
                </div>
                <div class="col-span-12 intro-y sm:col-span-6">
                    <div>
                        <FormLabel>Поля для идентификации элемента</FormLabel>
                        <TomSelect
                            multiple
                            class="w-full"
                            v-model="form.config.selected_primary_fields"
                        >
                            <option v-for="(field, index) in primary_fields"
                                    :value="field"
                                    :key="index"
                            >
                                {{ field }}
                            </option>
                        </TomSelect>
                    </div>
                </div>
                <div class="col-span-12 intro-y sm:col-span-6">
                    <ItemSelect label="Действие"
                                :items="actions"
                                id-value="value"
                                name-value="label_ru"
                                v-model="form.action"
                    />
                </div>
                <div class="col-span-12 intro-y sm:col-span-6">
                    <div v-if="form.id" class="flex flex-col mt-7">
                        <div class="relative cursor-pointer w-48">
                            <Button variant="primary" type="button" class="w-full">
                                Загрузить файл
                            </Button>
                            <FormInput @input="uploadFile"
                                       type="file"
                                       class="absolute top-0 left-0 w-full h-full opacity-0"
                            />
                        </div>
                        <p v-if="form.config.file" class="mt-3">{{ form.config.file }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-10">
                <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
            </div>
        </div>

        <div v-if="activeStep === 2"
             class="px-5 pt-10 mt-10 border-t sm:px-20 border-slate-200/60 dark:border-darkmode-400"
        >
            <div class="relative p-5 mt-5 border rounded-md bg-warning/20 dark:bg-darkmode-600 border-warning dark:border-0"
                 v-if="Object.entries(headings).length"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="stroke-1.5 absolute top-0 right-0 w-12 h-12 mt-5 mr-3 text-warning/80"
                >
                    <line x1="9" y1="18" x2="15" y2="18"></line>
                    <line x1="10" y1="22" x2="14" y2="22"></line>
                    <path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"></path>
                </svg>
                <h2 class="text-lg font-medium">Заголовки в файле</h2>
                <div v-for="(value, key) in headings" class="mt-2 font-medium">
                    {{ key }} - {{ value }}
                </div>
            </div>
        </div>

        <div v-if="activeStep === 3"
             class="px-5 pt-10 mt-10 border-t sm:px-20 border-slate-200/60 dark:border-darkmode-400"
        >
            <Button variant="primary" @click="updateOrCreate">Сохранить</Button>
            <Button v-if="isImportAvailable"
                    class="ml-3"
                    variant="outline-success"
                    @click="saveAndImport"
            >
                Импортировать
            </Button>
            <Button v-if="form.config.errors_file"
                    class="ml-3"
                    variant="secondary"
                    :href="downloadErrorsFile()"
                    download
                    as="a"
            >
                Скачать файл с ошибками
            </Button>
        </div>
        <!-- CONTENTS -->

        <div class="mt-10 mx-10">
            <TheErrorMessages :errors="errors" />
        </div>

        <div class="px-5 pt-10 mt-5 sm:px-20">
            <div class="grid grid-cols-12 gap-4 mt-10 gap-y-5">
                <div class="flex items-center justify-center col-span-12 mt-5 intro-y sm:justify-end">
                    <Button variant="secondary" class="w-24" @click="prevStep">
                        Назад
                    </Button>
                    <Button variant="primary" class="w-24 ml-2" @click="nextStep">
                        Далее
                    </Button>
                </div>
            </div>
        </div>

    </div>
    <!-- END: Wizard Layout -->
</template>

<script>
import Layout from "../../Shared/Layout.vue";
import Button from "../../base-components/Button";
import TomSelect from "../../base-components/TomSelect/TomSelect.vue";
import {FormInput, FormLabel, FormSelect} from "../../base-components/Form";
import axios from "axios";

export default {
    layout: Layout,
    components: {
        Button,
        TomSelect,
        FormInput,
        FormLabel,
        FormSelect,
    },
    props: {
        profile: {
            type: Object,
            required: false
        },
        types: {
            type: Object,
            required: true
        },
        actions: {
            type: Object,
            required: true
        },
        errors: Object
    },
    data() {
        return {
            activeStep: 1,
            firstStep: 1,
            lastStep: 3,
            form: {
                name: '',
                type: '',
                config: {
                    file: '',
                    errors_file: '',
                    model: '',
                    selected_primary_fields: [],
                },
                action: 1 // UPDATE_OR_CREATE
            },
            models: [],
            primary_fields: [],
            headings: {}
        }
    },
    beforeMount() {
        if (this.profile) {
            this.form = this.profile
        }
    },
    computed: {
        pageTitle() {
            return this.profile ? 'Редактирование профиля' : 'Добавление профиля'
        },
        isImportAvailable() {
            return this.profile && this.form.config.file
        }
    },
    watch: {
        'form.type'(newVal) {
            if (! newVal) {
                this.models = []
                return
            }

            this.getModels(this.types[newVal].slug)
        },
        'form.config.model'(newVal) {
            this.getConfigDetails(newVal)
        }
    },
    methods: {
        getButtonClasses(step) {
            return this.activeStep === step
                ? 'w-10 h-10 rounded-full'
                : 'w-10 h-10 rounded-full text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400 border-none'
        },
        getHeadingClasses(step) {
            return this.activeStep === step
                ? 'ml-3 text-base font-medium lg:w-32 lg:mt-3 lg:mx-auto'
                : 'ml-3 text-base lg:w-32 lg:mt-3 lg:mx-auto text-slate-600 dark:text-slate-400'
        },
        nextStep() {
            if (this.activeStep + 1 < this.lastStep) {
                this.activeStep += 1
            } else {
                this.activeStep = this.lastStep
            }
        },
        prevStep() {
            if (this.activeStep - 1 > this.firstStep) {
                this.activeStep -= 1
            } else {
                this.activeStep = this.firstStep
            }
        },
        updateOrCreate() {
            if (this.profile) {
                this.update()
            } else {
                this.create()
            }
        },
        saveAndImport() {
            this.import()
        },
        update() {
            this.$inertia.put(`/import-profiles/${this.profile.id}`, this.form, {
                onSuccess: params => {
                    this.form = params.props.profile
                }
            })
        },
        create() {
            this.$inertia.post('/import-profiles', this.form, {
                onSuccess: params => {
                    this.form = params.props.profile
                }
            })
        },
        import() {
            this.$inertia.put(`/import-profiles/${this.profile.id}/import`, {}, {
                onSuccess: params => {
                    this.form = params.props.profile
                }
            })
        },
        uploadFile(event) {
            const file = event.target.files[0]

            this.$inertia.post(`/import-profiles/${this.profile.id}/upload`, {
                file: file
            }, {
                onSuccess: params => {
                    this.form = params.props.profile
                }
            })
        },
        downloadErrorsFile() {
            const file = this.form.config.errors_file

            return `/uploads/${file}`
        },
        getModels(type) {
            axios.get('/import-profiles/models', {
                params: {
                    type: type
                }
            })
                .then(response => {
                    this.models = response.data.data
                })
        },
        getConfigDetails(model) {
            axios.get('/import-profiles/details', {
                params: {
                    model: model
                }
            })
                .then(response => {
                    this.primary_fields = response.data.data.primary_fields
                    this.headings = response.data.data.headings
                })
        }
    }
}
</script>
