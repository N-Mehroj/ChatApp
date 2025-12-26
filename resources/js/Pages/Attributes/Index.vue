<template>
	<h2 class="mt-10 text-lg font-medium intro-y">Все аттрибуты</h2>
	<div class="grid grid-cols-12 gap-6 mt-5">
		<div
			class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap"
		>
			<Button variant="primary" class="mr-2 shadow-md">
				<Link href="/attributes/create">
					Добавить аттрибут
				</Link>
			</Button>
			<div v-if="attributes.total" class="hidden mx-auto md:block text-slate-500">
				Показано с {{ attributes.from }} по {{ attributes.to }} из {{ attributes.total }} записей
			</div>
			<div v-else class="hidden mx-auto md:block text-slate-500">
				Аттрибуты не найдены
			</div>
			<Search />
		</div>
		<template v-if="attributesList.length">
			<!-- BEGIN: Data List -->
			<div class="col-span-12 overflow-x-auto intro-y">
				<Table class="border-spacing-y-[10px] border-separate -mt-2">
					<Table.Thead>
						<Table.Tr>
							<Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
							<Table.Th class="border-b-0 whitespace-nowrap">Название RU</Table.Th>
							<Table.Th class="border-b-0 whitespace-nowrap">Символьный код</Table.Th>
						</Table.Tr>
					</Table.Thead>
					<Table.Tbody>
						<Table.Tr
							v-for="attribute in attributesList"
							:key="attribute.id"
							class="intro-x"
						>
							<Table.Td
								class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
							>
								<Link :href="`/attributes/${attribute.id}/edit`" class="font-medium whitespace-nowrap">
									{{ attribute.id }}
								</Link>
							</Table.Td>
							<Table.Td
								class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
							>
								<Link :href="`/attributes/${attribute.id}/edit`" class="font-medium whitespace-nowrap">
									{{ attribute.name_ru }}
								</Link>
							</Table.Td>
							<Table.Td
								class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
							>
								{{ attribute.slug }}
							</Table.Td>

							<Table.Td
								class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
							>
								<div class="flex items-center justify-center">
									<Link class="flex items-center mr-3 text-primary" :href="`/attributes/${attribute.id}/edit`">
										<Lucide icon="CheckSquare" class="w-4 h-4 mr-1 text-primary" />
									</Link>
									<a
										class="flex items-center text-danger"
										href="#"
										@click.prevent="deleteRecord(attribute.id)"
									>
										<Lucide icon="Trash2" class="w-4 h-4 mr-1" />
									</a>
								</div>
							</Table.Td>
						</Table.Tr>
					</Table.Tbody>
				</Table>
			</div>
			<!-- END: Data List -->
			<!-- BEGIN: Pagination -->
			<div
				class="flex flex-wrap items-center col-span-12 intro-y sm:flex-row sm:flex-nowrap"
			>
				<ThePagination :data="attributes" />
			</div>
			<!-- END: Pagination -->
		</template>
	</div>
	<DeleteModal :is-open="isDeleteModalOpen"
				 :delete-url="deleteRecordUrl"
				 @cancel="isDeleteModalOpen = false"
	/>
</template>

<script lang="ts">
import Layout from "../../Shared/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";
import DeleteModal from "../../custom-components/DeleteModal.vue";
import Search from "../../custom-components/Search.vue";

export default {
	layout: Layout,
	components: {
		Link,
		DeleteModal,
		Search
	},
	props: {
		attributes: Object
	},
	data() {
		return {
			isDeleteModalOpen: false,
			deleteRecordUrl: ''
		}
	},
	computed: {
		attributesList() {
			return this.attributes.data
		}
	},
	methods: {
		deleteRecord(id) {
			this.deleteRecordUrl = `/attributes/${id}`
			this.isDeleteModalOpen = true
		}
	}
}
</script>

<script setup lang="ts">
import Table from "../../base-components/Table";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import { FormInput } from "../../base-components/Form";
</script>
