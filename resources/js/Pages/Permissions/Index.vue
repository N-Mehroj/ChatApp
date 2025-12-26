<template>
	<h2 class="mt-10 text-lg font-medium intro-y">Все разрешения</h2>
	<div class="grid grid-cols-12 gap-6 mt-5">
		<div
			class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap"
		>
			<Button variant="primary" class="mr-2 shadow-md">
				<Link href="/permissions/create">
					Добавить разрешение
				</Link>
			</Button>
			<Menu>
				<Menu.Button :as="Button" class="px-2 !box">
                  <span class="flex items-center justify-center w-5 h-5">
                    <Lucide icon="Plus" class="w-4 h-4" />
                  </span>
				</Menu.Button>
				<Menu.Items class="w-40">
					<Menu.Item>
						<Lucide icon="Printer" class="w-4 h-4 mr-2" /> Print
					</Menu.Item>
					<Menu.Item>
						<Lucide icon="FileText" class="w-4 h-4 mr-2" /> Export to Excel
					</Menu.Item>
					<Menu.Item>
						<Lucide icon="FileText" class="w-4 h-4 mr-2" /> Export to PDF
					</Menu.Item>
				</Menu.Items>
			</Menu>
			<div v-if="permissions.total" class="hidden mx-auto md:block text-slate-500">
				Показано с {{ permissions.from }} по {{ permissions.to }} из {{ permissions.total }} записей
			</div>
			<div v-else class="hidden mx-auto md:block text-slate-500">
				Разрешения не найдены
			</div>
			<Search />
		</div>
		<!-- BEGIN: Data List -->
		<div class="col-span-12 overflow-x-auto intro-y">
			<Table class="border-spacing-y-[10px] border-separate -mt-2">
				<Table.Thead>
					<Table.Tr>
						<Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
						<Table.Th class="border-b-0 whitespace-nowrap">Название</Table.Th>
						<Table.Th class="text-center border-b-0 whitespace-nowrap">Действия</Table.Th>
					</Table.Tr>
				</Table.Thead>
				<Table.Tbody>
					<Table.Tr
						v-for="permission in permissionsList"
						:key="permission.id"
						class="intro-x"
					>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
						>
							<a href="" class="font-medium whitespace-nowrap">
								{{ permission.id }}
							</a>
						</Table.Td>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
						>
							<a href="" class="font-medium whitespace-nowrap">
								{{ permission.name }}
							</a>
						</Table.Td>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
						>
							<div class="flex items-center justify-center">
								<Link class="flex items-center mr-3" :href="`/permissions/${permission.id}/edit`">
									<Lucide icon="CheckSquare" class="w-4 h-4 mr-1 text-primary" />

								</Link>
								<a
									class="flex items-center text-danger"
									href="#"
									@click.prevent="deleteUser(permission.id)"
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
			<ThePagination :data="permissions" />
		</div>
		<!-- END: Pagination -->
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

export default {
	layout: Layout,
	components: {
		Link,
		DeleteModal,
	},
	props: {
		permissions: Object
	},
	data() {
		return {
			isDeleteModalOpen: false,
			deleteRecordUrl: ''
		}
	},
	computed: {
		permissionsList() {
			return this.permissions.data
		}
	},
	methods: {
		deleteUser(id) {
			this.deleteRecordUrl = `/permissions/${id}`
			this.isDeleteModalOpen = true
		}
	}
}
</script>

<script setup lang="ts">
import Table from "../../base-components/Table";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import { Menu } from "../../base-components/Headless";
import { FormInput, FormSelect } from "../../base-components/Form";
</script>
