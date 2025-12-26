<template>
	<h2 class="mt-10 text-lg font-medium intro-y">Все пользователи</h2>
	<div class="grid grid-cols-12 gap-6 mt-5">
		<div
			class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap"
		>
            <Button v-on:click="isFilterHidden = !isFilterHidden" variant="secondary" class="px-4 mr-2 shadow-md">
                <Lucide icon="Filter" class="w-5 h-5 mr-2"/> Фильтр
            </Button>
			<Button variant="primary" class="mr-2 shadow-md">
				<Link href="/users/create">
					Добавить пользователя
				</Link>
			</Button>
			<div v-if="users.total" class="hidden mx-auto md:block text-slate-500">
				Показано с {{ users.from }} по {{ users.to }} из {{ users.total }} записей
			</div>
			<div v-else class="hidden mx-auto md:block text-slate-500">
				Пользователи не найдены
			</div>
            <Search />
		</div>
        <div class="flex flex-wrap items-center col-span-12 mt-2 intro-y sm:flex-nowrap">
            <ComplexFilter :is-filter-hidden="isFilterHidden" :filterFields="filterFields" @search="doFilter"/>
        </div>
		<!-- BEGIN: Data List -->
		<div class="col-span-12 overflow-x-auto intro-y">
			<Table class="border-spacing-y-[10px] border-separate -mt-2">
				<Table.Thead>
					<Table.Tr>
						<Table.Th class="border-b-0 whitespace-nowrap">ID</Table.Th>
						<Table.Th class="border-b-0 whitespace-nowrap">Имя</Table.Th>
						<Table.Th class="border-b-0 whitespace-nowrap">Почта</Table.Th>
						<Table.Th class="border-b-0 whitespace-nowrap">Роли</Table.Th>
						<Table.Th class="text-center border-b-0 whitespace-nowrap">Действия</Table.Th>
					</Table.Tr>
				</Table.Thead>
				<Table.Tbody>
					<Table.Tr
						v-for="user in usersList"
						:key="user.id"
						class="intro-x"
					>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
						>
							<a href="" class="font-medium whitespace-nowrap">
								{{ user.id }}
							</a>
						</Table.Td>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
						>
							<a href="" class="font-medium whitespace-nowrap">
								{{ user.name }}
							</a>
						</Table.Td>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
						>
							{{ user.email }}
						</Table.Td>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]"
						>
							{{ rolesAsString(user.roles) }}
						</Table.Td>
						<Table.Td
							class="first:rounded-l-md last:rounded-r-md w-56 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] py-0 relative before:block before:w-px before:h-8 before:bg-slate-200 before:absolute before:left-0 before:inset-y-0 before:my-auto before:dark:bg-darkmode-400"
						>
							<div class="flex items-center justify-center">
								<Link class="flex items-center mr-3" :href="`/users/${user.id}/edit`">
									<Lucide icon="CheckSquare" class="w-4 h-4 mr-1 text-primary" />

								</Link>
								<a
									class="flex items-center text-danger"
									href="#"
									@click.prevent="deleteUser(user.id)"
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
			<ThePagination :data="users" />
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
import ComplexFilter from "../../custom-components/ComplexFilter.vue";

export default {
	layout: Layout,
	components: {
		ComplexFilter,
		Link,
		DeleteModal
	},
	props: {
		users: Object
	},
	url: String,
	data() {
		return {
            isFilterHidden: true,
			isDeleteModalOpen: false,
			deleteRecordUrl: '',
            filterFields: [
                {name: 'Почта', field: 'email', type: 'text'},
                {name: 'Роль', field: 'role', type: 'itemMultipleSelect', options: this.roles, idValue: 'id', nameValue: 'label_ru'},
            ],
			query: '',
		}
	},
	computed: {
		usersList() {
			return this.users.data
		}
	},
	methods: {
		rolesAsString(roles) {
			return roles.map(role => role.label_ru).join(', ')
		},
		deleteUser(id) {
			this.deleteRecordUrl = `/users/${id}`
			this.isDeleteModalOpen = true
		},
        doFilter(filter) {
            this.$inertia.reload({
                data: {
                    filter,
                }
            })
        },
		search(query) {
			this.$inertia.reload({
				data: {
					q: query
				}
			})
		},

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
