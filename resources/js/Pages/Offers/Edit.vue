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
				<!--                <Tab.List-->
				<!--                    class="flex-col border-transparent dark:border-transparent sm:flex-row bg-slate-200 dark:bg-darkmode-800"-->
				<!--                >-->
				<!--                    <Tab v-slot="{ selected }">-->
				<!--                        <Tab.Button-->
				<!--                            class="flex items-center justify-center w-full px-0 py-0 text-slate-500"-->
				<!--                            :class="[-->
				<!--                                {-->
				<!--                                    'hover:border-transparent hover:bg-transparent hover:text-slate-600 hover:dark:bg-transparent hover:dark:text-slate-300': !selected,-->
				<!--                                },-->
				<!--                                {-->
				<!--                                    'text-primary border-transparent dark:bg-darkmode-600 dark:border-x-transparent dark:border-t-transparent dark:text-white': selected,-->
				<!--                                },-->
				<!--                            ]"-->
				<!--                            as="button"-->
				<!--                        >-->
				<!--                            <div class="flex items-center justify-center w-full py-4">-->
				<!--                                <Lucide icon="FileText" class="w-4 h-4 mr-2" />-->
				<!--                                Основная информация-->
				<!--                            </div>-->
				<!--                        </Tab.Button>-->
				<!--                    </Tab>-->
				<!--                </Tab.List>-->
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

						<div class="mt-5">
							<SingleFileUpload v-model="form.images" :folder="'offers'"/>
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
				<div class="mb-3">
					<ModelSelect label="Продукт (SPU)"
								 v-model="form.product_id"
								 search-target="products"
					/>
				</div>
				<div class="mb-3">
					<ModelSelect label="Категория"
								 v-model="form.category_id"
								 search-target="categories"
					/>
				</div>
				<div class="mb-3">
					<ModelSelect label="Организация"
								 v-model="form.organization_id"
								 search-target="organizations"
					/>
				</div>
				<div>
					<ModelSelectMultiple label="Теги"
										 v-model="form.tags"
										 search-target="tags"
					/>
				</div>
				<div>
					<TheInput label="Цена"
							  type="number"
							  v-model="form.base_price"
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
			</div>
		</div>

	</div>
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";
import axios from "axios";
import MultipleFileUpload from "../../custom-components/MultipleFileUpload.vue";

export default {
	layout: Layout,
	components: {
		MultipleFileUpload,
		Link,
	},
	props: {
		offer: {
			type: Object,
			required: false
		},
		products: Array,
		errors: Object
	},
	data() {
		return {
			form: {
				slug: '',
				name_ru: '',
				name_uz: '',
				product_id: '',
				organization_id: '',
				base_price: '',
				tags: [],
				images: ''
			}
		}
	},
	beforeMount() {
		if (this.offer) {
			this.form = this.offer

			if (! this.form.product_id) {
				this.form.product_id = ''
			}
		}
	},
	computed: {
		pageTitle() {
			return this.offer ? 'Редактирование оффера' : 'Добавление оффера'
		}
	},
	methods: {
		updateOrCreate() {
			if (this.offer) {
				this.update()
			} else {
				this.create()
			}
		},
		update() {
			this.$inertia.put(`/offers/${this.offer.id}`, this.form)
		},
		create() {
			this.$inertia.post('/offers', this.form)
		},
	}
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import { FormLabel } from "../../base-components/Form";
import TomSelect from "../../base-components/TomSelect";
import { Tab } from "../../base-components/Headless";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";
import ModelSelectMultiple from "../../custom-components/ModelSelectMultiple.vue";
import Dropzone from "../../base-components/Dropzone/Dropzone.vue";
import Lucide from "../../base-components/Lucide/Lucide.vue";
import FormInput from "../../base-components/Form/FormInput.vue";
</script>
