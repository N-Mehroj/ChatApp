<template>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="mr-auto text-lg font-medium">{{ pageTitle }}</h2>
    </div>
    <div class="grid grid-cols-12 gap-5 mt-5 intro-y">
        <!-- BEGIN: Post Content -->
        <div class="col-span-12 intro-y lg:col-span-8">

            <Tab.Group class="mt-5 overflow-inherit intro-y box">
                <Tab.List
                    class="flex-col border-transparent dark:border-transparent sm:flex-row bg-slate-200 dark:bg-darkmode-800"
                >
                    <Tab :fullWidth="true" v-slot="{ selected }">
                        <Tab.Button
                            :class="[
                                'flex items-center justify-center w-full px-0 py-0 text-slate-500',
                                {
                                  'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300':
                                    !selected,
                                },
                                {
                                  'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white':
                                    selected,
                                },
                             ]"
                            as="button"
                        >
                            <Tippy
                                content="Fill in the article content"
                                class="flex items-center justify-center w-full py-4"
                                aria-controls="content"
                                aria-selected="true"
                            >
                                <Lucide icon="FileText" class="w-4 h-4 mr-2" />
                                Основные
                            </Tippy>
                        </Tab.Button>
                    </Tab>
                    <Tab :fullWidth="true" v-slot="{ selected }">
                        <Tab.Button
                            :class="[
                                'flex items-center justify-center w-full px-0 py-0 text-slate-500',
                                {
                                  'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300':
                                    !selected,
                                },
                                {
                                  'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white':
                                    selected,
                                },
                            ]"
                            as="button"
                        >
                            <Tippy
                                content="Adjust the meta title"
                                class="flex items-center justify-center w-full py-4"
                                aria-selected="false"
                            >
                                <Lucide icon="Briefcase" class="w-4 h-4 mr-2" />
                                Места работы
                            </Tippy>
                        </Tab.Button>
                    </Tab>
                    <Tab :fullWidth="true" v-slot="{ selected }">
                        <Tab.Button
                            :class="[
                                'flex items-center justify-center w-full px-0 py-0 text-slate-500',
                                {
                                  'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300':
                                    !selected,
                                },
                                {
                                  'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white':
                                    selected,
                                },
                            ]"
                            as="button"
                        >
                            <Tippy
                                content="Adjust the meta title"
                                class="flex items-center justify-center w-full py-4"
                                aria-selected="false"
                            >
                                <Lucide icon="FileEdit" class="w-4 h-4 mr-2" />
                                Дополнительно
                            </Tippy>
                        </Tab.Button>
                    </Tab>
                </Tab.List>

                <Tab.Panels>
                    <Tab.Panel class="p-5">
                        <div class="mt-5 mb-5">
                            <TheInput
                                label="Имя RU"
                                type="text"
                                v-model="form.name_ru"
                            />
                        </div>
                        <div class="mt-5 mb-5">
                            <TheInput
                                label="Имя UZ"
                                type="text"
                                v-model="form.name_uz"
                            />
                        </div>
                        <div class="mb-5">
                            <Slug v-model="form.slug"></Slug>
                        </div>
                    </Tab.Panel>
                    <Tab.Panel class="p-5">
                        <div class="mt-5">

                            <div v-for="(work_place, index) in form.work_places" class="box p-4 mt-4">
                                <FormLabel :style="'margin-top:20px'"><strong> Место работы №{{index + 1}} </strong> </FormLabel>
                                <div class="mt-5">
                                    <ModelSelect label="Организация"
                                                 v-model="work_place.organization_id"
                                                 search-target="organizations"
                                    />
                                </div>
                                <div class="mt-5" :style="'margin-bottom: 20px'">
                                    <ItemSelect label="Активность"
                                                :items="is_active_statuses"
                                                id-value="value"
                                                name-value="label_ru"
                                                v-model="work_place.status"
                                    />
                                </div>
                                <TheInput
                                    label="Комментарий"
                                    type="text"
                                    v-model="work_place.comment"
                                />
                                <FormLabel :style="'margin-top:20px'"> График </FormLabel>
                                <WorkDays v-model="work_place.work_time" :schedule="form.work_time"></WorkDays>
                            </div>
                            <Menu>
                                <Menu.Button @click.prevent="addWorkPlaceRow"
                                             :as="Button"
                                             variant="primary"
                                             class="flex items-center shadow-md mt-4"
                                >
                                    Добавить место работы
                                </Menu.Button>
                            </Menu>
                        </div>
                    </Tab.Panel>

                    <Tab.Panel class="p-5">
                        <div class="p-5 border rounded-md border-slate-200/60 dark:border-darkmode-400 mt-5">
                            <div class="flex items-center pb-5 font-medium border-b border-slate-200/60 dark:border-darkmode-400">
                                <Lucide icon="ChevronDown" class="w-4 h-4 mr-2" /> Описание RU
                            </div>
                            <div class="mt-5">
                                <ClassicEditor v-model="form.description_ru" />
                            </div>
                        </div>
                        <div class="p-5 border rounded-md border-slate-200/60 dark:border-darkmode-400 mt-5">
                            <div class="flex items-center pb-5 font-medium border-b border-slate-200/60 dark:border-darkmode-400">
                                <Lucide icon="ChevronDown" class="w-4 h-4 mr-2" /> Описание UZ
                            </div>
                            <div class="mt-5">
                                <ClassicEditor v-model="form.description_uz" />
                            </div>
                        </div>
                        <div class="p-5 mt-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
                            <div class="flex items-center pb-5 font-medium border-b border-slate-200/60 dark:border-darkmode-400">
                                <Lucide class="w-4 h-4 mr-2" />
                                Лого
                            </div>
                            <div class="mt-5">
                                <div class="mt-3">
                                    <SingleFileUpload v-model="form.photo" :folder="'images'"/>
                                </div>
                            </div>
                        </div>

                    </Tab.Panel>
                    <Tab.Panel class="p-5">
                        <WorkSchedule class="mt-0" v-model="form.schedule" />
                    </Tab.Panel>
                </Tab.Panels>
            </Tab.Group>
        </div>
        <div class="col-span-12 intro-y lg:col-span-4" >
            <div class="p-5 mt-5 intro-y box">
                <div class="mt-5" :style="'margin-bottom: 20px'">
                    <ItemSelect label="Статус"
                                :items="statuses"
                                id-value="value"
                                name-value="label_ru"
                                v-model="form.status"
                    />
                </div>
                <div class="mt-5" :style="'margin-bottom: 20px'">
                    <ItemSelect label="Пол"
                                :items="genders"
                                id-value="value"
                                name-value="label_ru"
                                v-model="form.gender"
                    />
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
    </div>
</template>

<script lang="ts">
import { Link } from '@inertiajs/inertia-vue3'
import Layout from '../../Shared/Layout.vue'
import axios from 'axios'
import ModelSelectMultiple from "../../custom-components/ModelSelectMultiple.vue";
import TheInput from "../../custom-components/TheInput.vue";
import Datepicker from "../../custom-components/Datepicker.vue";
import WorkDays from '../../custom-components/WorkDays.vue'


export default {
    layout: Layout,
    components: {
        TheInput,
        ModelSelectMultiple,
        Link,
        Datepicker,
        WorkDays
    },
    props: {
        person: {
            type: [Object],
            required: false
        },
        errors: Object,
        statuses: Object,
        person_work_places: Array,
        genders: Object,
    },
    computed: {
        pageTitle() {
            return this.doctor ? 'Редактирование человека' : 'Добавить человека'
        }
    },
    data() {
        return {
            form: {
                name_ru: '',
                name_uz: '',
                slug: '',
                photo: '',
                description_ru: '',
                description_uz: '',
                status: '',
                work_places: [],
                status_comment: '',
                gender: '',
            },
            daysOfWeek: [
                {id: 1, label: 'Понедельник', start: '', end: ''},
                {id: 2, label: 'Вторник', start: '', end: ''},
                {id: 3, label: 'Среда', start: '', end: ''},
                {id: 4, label: 'Четверг', start: '', end: ''},
                {id: 5, label: 'Пятница', start: '', end: ''},
                {id: 6, label: 'Суббота', start: '', end: ''},
                {id: 7, label: 'Воскресенье', start: '', end: ''},
            ],
            is_active_statuses: [
                {label_ru: 'Да', value: '1'},
                {label_ru: 'Нет', value: '0'},
            ],
            autoApplyOption: false,
        }
    },
    beforeMount() {
        this.updateForm()
        if (this.person) {
            this.form = this.person
            if (this.person.description_ru == null) {
                this.form.description_ru = ""
            }
            if (this.person.description_uz == null) {
                this.form.description_uz = ""
            }
            this.form.work_places = this.person_work_places
        }

    },
    mounted() {
        this.autoApplyOption = true
    },
    methods: {
        checkBeforeUpdateOrCreate() {
            if (this.form.status == "") {
                this.form.status = 1
            }
        },
        updateOrCreate() {
            this.checkBeforeUpdateOrCreate()

            if (this.person) {
                this.updatePerson()
            } else {
                this.createPerson()
            }
        },
        updatePerson() {
            this.$inertia.put(`/persons/${this.person.id}`, this.form)
        },
        createPerson() {
            this.$inertia.post('/persons', this.form)
        },
        updateForm() {
            let fields = {
                name_ru: '',
                name_uz: '',
                slug: '',
                photo: '',
                description_ru: '',
                description_uz: '',
                status: '',
                work_places: [],
                status_comment: '',
                gender: '',
            }

            if (this.person) {
                fields = this.person
                fields.work_places = [];
            }
            this.form = this.$inertia.form(fields)
        },

        addWorkPlaceRow(){

            this.form.work_places.push({
                organization_id: '',
                comment: '',
                work_time: [],
                status: '',
            });
        },
    }
}
</script>

<script setup lang="ts">
import _ from "lodash";
import { ref } from "vue";
import fakerData from "../../utils/faker";
import Button from "../../base-components/Button";
import { FormInput, FormLabel, FormSwitch } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import Tippy from "../../base-components/Tippy";
import Litepicker from "../../base-components/Litepicker";
import TomSelect from "../../base-components/TomSelect";
import { ClassicEditor } from "../../base-components/Ckeditor";
import { Menu, Tab } from "../../base-components/Headless";
import Dropzone from "../../base-components/Dropzone";

</script>
