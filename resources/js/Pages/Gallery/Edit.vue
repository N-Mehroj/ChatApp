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
                    <TheInput type="text"
                              label="Название"
                              class="mt-0"
                              v-model="form.name"
                    />
                    <TheInput label="Альтернативный текст"
                              v-model="form.name"
                    />
                    <div class="mt-5">
                        <SingleFileUpload v-model="form.path" :folder="'gallery'"/>
                    </div>
                </div>
            </Tab.Group>
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
		<div class="col-span-12 lg:col-span-4 mt-5">
			<div class="p-5 intro-y box" v-if="form.id">
				<div class="mt-5" >
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
import {Link} from '@inertiajs/inertia-vue3'
import Layout from '../../Shared/Layout.vue'
import axios from 'axios'

export default {
    layout: Layout,
    components: {
        Link
    },
    props: {
        gallery: {
            type: [Object],
            required: false
        },
        errors: Object,
    },
    computed: {
        pageTitle() {
            return this.gallery ? 'Редактирование файла' : 'Добавить файл'
        }
    },
    data() {
        return {
            form: {
                name: '',
                path: '',
                alt_text: '',
            },
        }
    },

    beforeMount() {
        this.updateForm()
    },
    methods: {
        updateOrCreate() {
            if (this.gallery) {
                this.update()
            } else {
                this.create()
            }
        },
        update() {
            this.$inertia.put(`/gallery/${this.gallery.id}`, this.form)
        },
        create() {
            console.log(this.form)
            this.$inertia.post('/gallery', this.form)
        },
        updateForm() {
            let fields = {
                name: '',
                path: '',
                alt_text: '',
                redirectTo: 'edit'
            }

            if (this.gallery) {
                fields = this.gallery
            }

            this.form = this.$inertia.form(fields)
        }
    }
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import {FormInput} from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import {Menu, Tab} from "../../base-components/Headless";
import TheInput from "../../custom-components/TheInput.vue";
</script>
