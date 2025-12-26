<template>
	<div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
		<h2 class="mr-auto text-lg font-medium">Добавить организацию</h2>
	</div>
	<div class="grid grid-cols-12 gap-5 mt-5 intro-y">
		<!-- BEGIN: Post Content -->
		<div class="col-span-12 intro-y lg:col-span-8">

			<Tab.Group class="mt-5 overflow-hidden intro-y box">
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
								<Lucide icon="Contact" class="w-4 h-4 mr-2" /> Контакты
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
								<Lucide icon="MessageCircle" class="w-4 h-4 mr-2" /> Социальные сети
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
								<Lucide icon="Phone" class="w-4 h-4 mr-2" /> Телефоны
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
								<Lucide icon="FileEdit" class="w-4 h-4 mr-2" /> Адреса
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
								<Lucide icon="ListOrdered" class="w-4 h-4 mr-2" /> График
							</Tippy>
						</Tab.Button>
					</Tab>
				</Tab.List>

				<Tab.Panels>
					<Tab.Panel class="p-5">
						<div class="mt-5">
							<ModelSelect label="Владелец"
										 v-model="form.user_id"
										 search-target="users"
										 name-value=first_name
							/>
						</div>
						<div class="mt-5 mb-5">
							<FormLabel> Название </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.name"
							/>
						</div>


						<div class="p-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
							<div class="flex items-center pb-5 font-medium border-b border-slate-200/60 dark:border-darkmode-400">
								<Lucide icon="ChevronDown" class="w-4 h-4 mr-2" /> Описание
							</div>
							<div class="mt-5">
								<ClassicEditor v-model="form.description" />
							</div>
						</div>
						<div class="p-5 mt-5 border rounded-md border-slate-200/60 dark:border-darkmode-400">
							<div class="flex items-center pb-5 font-medium border-b border-slate-200/60 dark:border-darkmode-400">
								<Lucide class="w-4 h-4 mr-2" />
								Лого
							</div>
							<div class="mt-5">
								<div class="mt-3">
									<SingleFileUpload v-model="form.logo" :folder="'organizations/logo'"/>
								</div>
							</div>
						</div>
					</Tab.Panel>
					<Tab.Panel class="p-5">
						<div class="mt-5">
							<FormLabel :style="'margin-top:10px'"> Вебсайт </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.contact.website"
							/>
							<FormLabel :style="'margin-top:10px'"> Телефон </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.contact.phone"
							/>
							<FormLabel :style="'margin-top:10px'"> Email </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.contact.email"
							/>
							<FormLabel :style="'margin-top:10px'"> Telegram chat id </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.contact.telegram_chat_id"
							/>
						</div>
					</Tab.Panel>
					<Tab.Panel class="p-5">
						<div class="mt-5">
							<FormLabel :style="'margin-top:10px'"> Telegram </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.socials.telegram"
							/>
							<FormLabel :style="'margin-top:10px'"> Youtube </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.socials.youtube"
							/>
							<FormLabel :style="'margin-top:10px'"> Facebook </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.socials.facebook"
							/>
							<FormLabel :style="'margin-top:10px'"> Instagram </FormLabel>
							<FormInput
								type="text"
								class="px-4 py-3 pr-10 intro-y"
								v-model="form.socials.istagram"
							/>
						</div>
					</Tab.Panel>
					<Tab.Panel class="p-5">
						<div class="mt-5">
							<Menu>
								<Menu.Button @click.prevent="addPhoneRow"
											 :as="Button"
											 variant="primary"
											 class="flex items-center shadow-md"
								>
									Добавить
								</Menu.Button>
							</Menu>
							<div v-for="(phone, index) in form.phones" >
								<FormLabel :style="'margin-top:20px'"> Телефон №{{index + 1}}  </FormLabel>
								<FormInput
									type="text"
									class="px-4 py-3 pr-10 intro-y"
									v-model="phone.phone"
								/>
							</div>
						</div>
					</Tab.Panel>
					<Tab.Panel class="p-5">
						<div class="mt-5">
							<Menu>
								<Menu.Button @click.prevent="addAddressRow"
											 :as="Button"
											 variant="primary"
											 class="flex items-center shadow-md"
								>
									Добавить
								</Menu.Button>
							</Menu>
							<div v-for="(address, index) in form.addresses" >
								<FormLabel :style="'margin-top:20px'"> Адрес №{{index + 1}}  </FormLabel>
								<FormInput
									type="text"
									class="px-4 py-3 pr-10 intro-y"
									placeholder="Адрес RU"
									v-model="address.address_ru"
								/>
								<FormInput :style="'margin-top:10px'"
										   type="text"
										   placeholder="Адрес UZ"
										   class="px-4 py-3 pr-10 intro-y"
										   v-model="address.address_uz"
								/>
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
					<FormLabel>Статус</FormLabel>
					<TomSelect
						v-model="form.status"
						class="w-full"
					>
						<option value="null">Не выбрано</option>
						<option v-for="status in statuses"
								:value="status.value"
								:key="status.value">
							{{ status.label_ru }}
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


		<!-- END: Post Content -->
		<!-- BEGIN: Post Info -->
		<!-- END: Post Info -->
	</div>
</template>
<script lang="ts">
import { Link } from '@inertiajs/inertia-vue3'
import Layout from '../../Shared/Layout.vue'
import axios from 'axios'

export default {
	layout: Layout,
	components: {
		Link,
	},
	props: {
		organization: {
			type: [Object],
			required: false
		},
		errors: Object,
		statuses: Object,
	},
	computed: {
		pageTitle() {
			return this.organization ? 'Редактирование организации' : 'Добавить организацию'
		}
	},
	data() {
		return {
			form: {
				user_id: '',
				name: '',
				slug: '',
				logo: '',
				description: '',
				status: '',
				contact: {
					website: '',
					phone: '',
					email: '',
					telegram_chat_id: ''
				},
				socials: {
					istagram: '',
					facebook: '',
					youtube: '',
					telegram: '',
				},
				phones: [],
				addresses: [],
				schedule: []
			}
		}
	},
	beforeMount() {
		if (this.organization) {
			this.form = this.organization

			if (this.form.parent_id === null) {
				this.form.parent_id = ''
			}
		}
	},
	methods: {
		updateOrCreate() {
			if (this.organization) {
				this.updateOrganization()
			} else {
				this.createOrganization()
			}
		},
		updateOrganization() {
			this.$inertia.put(`/organizations/${this.organization.id}`, this.form)
		},
		createOrganization() {
			this.$inertia.post('/organizations', this.form)
		},
		updateForm() {
			let fields = {
				user_id: '',
				name: '',
				slug: '',
				logo: '',
				description: '',
				status: '',
				redirectTo: 'edit'
			}

			if (this.organization) {
				fields = this.organization
			}
			this.form = this.$inertia.form(fields)
		},

		addPhoneRow(){
			this.form.phones.push({
				phone: '',
			});
		},
		addAddressRow(){
			this.form.addresses.push({
				address_ru: '',
				address_uz: '',
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
