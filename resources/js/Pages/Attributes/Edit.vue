<template>
	<div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
		<h2 class="mr-auto text-lg font-medium">Добавить аттрибут</h2>
	</div>
	<div class="grid grid-cols-12 gap-5 mt-5 intro-y">
		<!-- BEGIN: Post Content -->
		<div class="col-span-12 intro-y lg:col-span-8 " >
			<Tab.Group class="mt-5 overflow-initial intro-y box">
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
								<Lucide icon="Contact" class="w-4 h-4 mr-2" /> Варианты
							</Tippy>
						</Tab.Button>
					</Tab>
				</Tab.List>

				<Tab.Panels>
					<Tab.Panel class="p-5">
						<TheInput class="mt-0"
								  label="Название RU"
								  v-model="form.name_ru"
						/>
						<TheInput
							label="Название UZ"
							v-model="form.name_uz"
						/>
						<Slug v-model="form.slug"></Slug>
						<div v-if="! form.id" class="mt-5" :style="'margin-bottom: 10px'">
							<ItemSelect :label="'Тип'"
										:items="types"
										:id-value="'value'"
										:name-value="'label_ru'"
										v-model="form.type"
							/>
						</div>
						<p v-else class="mt-3">Тип: {{ types[form.type].label_ru }}</p>
					</Tab.Panel>
					<Tab.Panel class="p-5">
						<AttributeVariants v-model="form.variants"></AttributeVariants>
					</Tab.Panel>
				</Tab.Panels>
			</Tab.Group>
		</div>
		<div class="col-span-12 intro-y lg:col-span-4" >
			<div class="p-5 mt-5 intro-y box " v-if="form.id">
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
	<!-- END: Post Content -->
	<!-- BEGIN: Post Info -->
	<!-- END: Post Info -->

</template>
<script lang="ts">
import { Link } from '@inertiajs/inertia-vue3'
import Layout from '../../Shared/Layout.vue'
import AttributeVariants from '../../custom-components/AttributeVariants.vue'
import axios from 'axios'

export default {
	layout: Layout,
	components: {
		Link,
		AttributeVariants
	},
	props: {
		attribute: {
			type: [Object],
			required: false
		},
		errors: Object,
		types: Object,
	},
	computed: {
		pageTitle() {
			return this.attribute ? 'Редактирование аттрибута' : 'Добавить аттрибут'
		}
	},
	data() {
		return {
			form: {
				name_ru: '',
				name_uz: '',
				slug: '',
				type: '',
				variants: [],
			}
		}
	},
	beforeMount() {
		if (this.attribute) {
			this.form = this.attribute
		}
	},
	methods: {
		updateOrCreate() {
			if (this.attribute) {
				this.updateAttribute()
			} else {
				this.createAttribute()
			}
		},
		updateAttribute() {
			this.$inertia.put(`/attributes/${this.attribute.id}`, this.form)
		},
		createAttribute() {
			this.$inertia.post('/attributes', this.form)
		},
		updateForm() {
			let fields = {
				name_ru: '',
				name_uz: '',
				slug: '',
				type: '',
				variants: [],
			}

			if (this.attribute) {
				fields = this.attribute
			}
			this.form = this.$inertia.form(fields)
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
