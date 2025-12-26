<template>
	<div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
		<h2 class="mr-auto text-lg font-medium">{{ pageTitle }}</h2>
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
						<TheInput label="Позиция"
								  v-model="form.position"
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
			<div class="p-5 intro-y box"  v-if="form.id">
				<div class="mt-5">
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
		bannerPosition: {
			type: Object,
			required: false
		},
		roles: Array,
		errors: Object
	},
	data() {
		return {
			form: {
				name: '',
				slug: '',
				position: '',
			}
		}
	},
	beforeMount() {
		if (this.bannerPosition) {
			this.form = this.bannerPosition
		}
	},
	computed: {
		pageTitle() {
			return this.bannerPosition ? 'Редактирование баннерной зоны' : 'Добавление баннерной зоны'
		}
	},
	methods: {
		updateOrCreate() {
			if (this.bannerPosition) {
				this.update()
			} else {
				this.create()
			}
		},
		update() {
			this.$inertia.put(`/advertising/banner-positions/${this.bannerPosition.id}`, this.form)
		},
		create() {
			this.$inertia.post('/advertising/banner-positions', this.form)
		}
	}
}
</script>

<script setup lang="ts">
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import { Tab } from "../../base-components/Headless";
import TheErrorMessages from "../../custom-components/TheErrorMessages.vue";
</script>
