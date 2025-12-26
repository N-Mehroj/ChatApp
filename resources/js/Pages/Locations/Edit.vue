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
				<Tab.List
					class="flex-col border-transparent dark:border-transparent sm:flex-row bg-slate-200 dark:bg-darkmode-800"
				>
					<Tab v-slot="{ selected }">
						<Tab.Button
							class="flex items-center justify-center w-full px-0 py-0 text-slate-500"
							:class="[
                                {
                                    'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300': !selected,
                                },
                                {
                                    'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white': selected,
                                },
                            ]"
							as="button"
						>
							<div class="flex items-center justify-center w-full py-4">
								<Lucide icon="FileText" class="w-4 h-4 mr-2"/>
								Основная информация
							</div>
						</Tab.Button>
					</Tab>
					<Tab v-slot="{ selected }">
						<Tab.Button
							class="flex items-center justify-center w-full px-0 py-0 text-slate-500"
							:class="[
                                {
                                    'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300': !selected,
                                },
                                {
                                    'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white': selected,
                                },
                            ]"
							as="button"
						>
							<div class="flex items-center justify-center w-full py-4">
								<Lucide icon="FileText" class="w-4 h-4 mr-2"/>
								SEO
							</div>
						</Tab.Button>
					</Tab>
				</Tab.List>
				<Tab.Panels>
					<Tab.Panel class="p-5">
						<TheInput class="mt-0"
								  label="Название RU"
								  v-model="form.name_ru"
						/>
						<Slug v-model="form.slug"></Slug>
						<TheInput label="Название UZ"
								  v-model="form.name_uz"
						/>
						<TheInput label="Широта"
								  v-model="form.latitude"
						/>
						<TheInput label="Долгота"
								  v-model="form.longitude"
						/>
					</Tab.Panel>
					<Tab.Panel class="p-5">
						<TheInput class="mt-0"
								  label="SEO Name 1 RU"
								  v-model="form.seo_name_1_ru"
						/>
						<TheInput label="SEO Name 1 UZ"
								  v-model="form.seo_name_1_uz"
						/>
						<TheInput label="SEO Name 2 RU"
								  v-model="form.seo_name_2_ru"
						/>
						<TheInput label="SEO Name 2 UZ"
								  v-model="form.seo_name_2_uz"
						/>
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
				<div class="mt-3">
					<item-select v-model="form.type" :label="'Тип Локации'" :items="locationTypes" :name-value="'label_ru'" :id-value="'value'"/>
				</div>
				<div class="mt-3">
					<item-select v-model="form.parent_id" :label="'Родитель'" :items="locations"/>
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
import ItemSelect from "../../custom-components/ItemSelect.vue";
import { Link } from "@inertiajs/inertia-vue3";

export default {
	layout: Layout,
	components: {
		Link,
		ItemSelect
	},
	props: {
		location: {
			type: Object,
			required: false
		},
		locations: Array,
		locationTypes: Object,
		errors: Object
	},
	data() {
		return {
			form: {
				slug: '',
				name_ru: '',
				name_uz: '',
				latitude: '',
				longitude: '',
				type: '',
				parent_id: '',
				seo_name_1_ru: '',
				seo_name_1_uz: '',
				seo_name_2_ru: '',
				seo_name_2_uz: '',
			}
		}
	},
	beforeMount() {
		if (this.location) {
			this.form = this.location

			if (this.form.parent_id === null) {
				this.form.parent_id = ''
			}
		}
	},
	computed: {
		pageTitle() {
			return this.location ? 'Редактирование локации' : 'Добавление локации'
		}
	},
	methods: {
		updateOrCreate() {
			if (this.location) {
				this.update()
			} else {
				this.create()
			}
		},
		update() {
			this.$inertia.put(`/handbook/locations/${this.location.id}`, this.form)
		},
		create() {
			this.$inertia.post('/handbook/locations', this.form)
		}
	}
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel } from "../../base-components/Form";
import Lucide from "../../base-components/Lucide";
import TomSelect from "../../base-components/TomSelect";
import { Tab } from "../../base-components/Headless";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";
</script>
