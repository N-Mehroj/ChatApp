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
						<div class="p-5 mt-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
							<div class="flex items-center pb-5 font-medium border-b border-slate-200/60 dark:border-darkmode-400">
								<Lucide icon="ChevronDown" class="w-4 h-4 mr-2" />
								Лого
							</div>
							<div class="mt-5">
								<div class="mt-3">
									<SingleFileUpload v-model="form.logo" :folder="'brands'"/>
								</div>
							</div>
						</div>
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
import axios from 'axios'

export default {
	layout: Layout,
	components: {
		Link
	},
	props: {
		brand: {
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
				logo: '',
				status: '0',
			}
		}
	},
	beforeMount() {
		if (this.brand) {
			this.form = this.brand
		}
	},
	computed: {
		pageTitle() {
			return this.brand ? 'Редактирование бренда' : 'Добавление бренда'
		}
	},
	methods: {
		updateOrCreate() {
			if (this.brand) {
				this.update()
			} else {
				this.create()
			}
		},
		update() {
			this.$inertia.put(`/brands/${this.brand.id}`, this.form)
		},
		create() {
			this.$inertia.post('/brands', this.form)
		}
	}

}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel, FormInput } from "../../base-components/Form";
import FormSwitch from "../../base-components/Form/FormSwitch";
import TomSelect from "../../base-components/TomSelect";
import { Tab } from "../../base-components/Headless";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";
</script>
