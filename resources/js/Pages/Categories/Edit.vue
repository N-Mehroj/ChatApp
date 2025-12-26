<template>
	<div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
		<h2 class="mr-auto text-lg font-medium">{{ pageTitle }}</h2>
	</div>
	<div class="grid grid-cols-12 gap-5 mt-5 intro-y">

		<div class="col-span-12 intro-y lg:col-span-8">
			<Tab.Group class="overflow-hidden intro-y box">
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
				<ModelSelect label="Родитель"
							 v-model="form.parent_id"
							 search-target="categories"
				/>
				<div>
					<FormSwitch class="flex flex-col items-start mt-3">
						<FormSwitch.Label htmlFor="post-form-5" class="mb-2 ml-0">
							Активность
						</FormSwitch.Label>
						<FormSwitch.Input id="post-form-5" type="checkbox" v-model="form.status" true-value=1 false-value=0 />
					</FormSwitch>
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

export default {
	layout: Layout,
	components: {
		Link
	},
	props: {
		category: {
			type: Object,
			required: false
		},
		errors: Object
	},
	data() {
		return {
			form: {
				slug: '',
				name_ru: '',
				name_uz: '',
				parent_id: '',
				status: '0',
			}
		}
	},
	beforeMount() {
		if (this.category) {
			this.form = this.category
		}
	},
	computed: {
		pageTitle() {
			return this.category ? 'Редактирование категории' : 'Добавление категории'
		}
	},
	methods: {
		updateOrCreate() {
			if (this.category) {
				this.update()
			} else {
				this.create()
			}
		},
		update() {
			this.$inertia.put(`/categories/${this.category.id}`, this.form)
		},
		create() {
			this.$inertia.post('/categories', this.form)
		}
	}

}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel } from "../../base-components/Form";
import FormSwitch from "../../base-components/Form/FormSwitch";
import TomSelect from "../../base-components/TomSelect";
import { Tab } from "../../base-components/Headless";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";
</script>
