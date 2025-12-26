<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ pageTitle }}
        </h2>
    </div>

    <div class="grid grid-cols-12 gap-5 mt-5 intro-y">
        <div class="col-span-12 intro-y lg:col-span-8">
            <Tab.Group class="mt-5 overflow-hidden intro-y box">
                <div class="p-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
                    <div>
                        <FormLabel>Robots.txt</FormLabel>
                        <FormTextarea v-model="form.robots_txt" style="height: 150px" />
                    </div>
                </div>
            </Tab.Group>
            <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
                <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
                    <Menu>
                        <Menu.Button @click="updateSetting"
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

<script lang="ts">
import { Link } from '@inertiajs/inertia-vue3'
import Layout from '../../Shared/Layout.vue'

export default {
    layout: Layout,
    components: {
        Link
    },
    props: {
        settings: {
            type: [Object],
            required: false
        },
        errors: Object,
    },
    computed: {
        pageTitle() {
            return this.settings ? 'Редактирование настроек' : 'Добавить настройки'
        }
    },
    data() {
        return {
            form: {
                robots_txt: '',
            },
        }
    },

    beforeMount() {
        this.updateForm()
    },
    methods: {
        updateSetting() {
            console.log(this.form)
            this.$inertia.post('/settings', this.form)
        },
        updateForm() {
            let fields = {
                robots_txt: ''
            }

            if (Object.values(this.settings).length) {
                fields = this.settings
            }

            this.form = fields
        },
    }
}
</script>

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

