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
								  label="Название"
								  v-model="form.name"
						/>
						<Slug v-model="form.slug"></Slug>
						<TheInput label="Контент RU"
								  v-model="form.content_ru"
						/>
						<TheInput label="Контент UZ"
								  v-model="form.content_uz"
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
				<div>
					<FormLabel htmlFor="post-form-3">Позиции</FormLabel>
					<TomSelect
						id="post-form-3"
						v-model="form.positions"
						class="w-full"
						multiple
					>
						<option v-for="position in bannerPositions" :value="position.id" :key="position.id">
							{{ position.name }} ({{ position.position }})
						</option>
					</TomSelect>
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
		banner: {
			type: Object,
			required: false
		},
		bannerPositions: Array,
		errors: Object
	},
	data() {
		return {
			form: {
				name: '',
				slug: '',
				content_ru: '',
				content_uz: '',
				positions: []
			}
		}
	},
	beforeMount() {
		if (this.banner) {
			this.form = this.banner

			this.form.positions = this.form.positions.map(position => position.id)
		}
	},
	computed: {
		pageTitle() {
			return this.banner ? 'Редактирование баннера' : 'Добавление баннера'
		}
	},
	methods: {
		updateOrCreate() {
			if (this.banner) {
				this.update()
			} else {
				this.create()
			}
		},
		update() {
			this.$inertia.put(`/advertising/banners/${this.banner.id}`, this.form)
		},
		create() {
			this.$inertia.post('/advertising/banners', this.form)
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
