<template>
	<div class="w-full sm:w-auto sm:mr-auto">
		<nav>
			<ul class="flex w-full mr-0 sm:w-auto sm:mr-auto">
				<li class="flex-1 sm:flex-initial">
					<Link :href="prevPageUrl"
						  :class="linkGeneralStyles"
						  class="flex items-center justify-center"
						  style="height: 100%"
					>
						<Lucide icon="ChevronLeft" class="w-4 h-4" />
					</Link>
				</li>

				<li v-for="link in links" class="flex-1 sm:flex-initial">
					<Link v-if="link.label !== '...'"
						  :href="link.url"
						  :class="[{ '!box font-medium dark:bg-darkmode-400': link.active }, ...linkGeneralStyles]"
					>
						{{ link.label }}
					</Link>
					<button v-else
							:class="linkGeneralStyles"
					>
						{{ link.label }}
					</button>
				</li>

				<li class="flex-1 sm:flex-initial">
					<Link :href="nextPageUrl"
						  :class="linkGeneralStyles"
						  class="flex items-center justify-center"
						  style="height: 100%"
					>
						<Lucide icon="ChevronRight" class="w-4 h-4" />
					</Link>
				</li>
			</ul>
		</nav>
	</div>
	<div>
		<select :class="selectClasses" v-model="perPage" class="w-20 mt-3 !box sm:mt-0">
			<option v-for="option in options" :value="option">{{ option }}</option>
		</select>
	</div>
</template>

<script>
import Lucide from "../base-components/Lucide";
import { Link } from "@inertiajs/inertia-vue3";
import FormSelect from "../base-components/Form/FormSelect.vue";

export default {
	name: "ThePagination",
	components: {
		Lucide,
		Link,
		FormSelect
	},
	data() {
		return {
			perPage: 25,
			options: [10, 25, 35, 50]
		}
	},
	props: {
		data: Object
	},
	computed: {
		prevPageUrl() {
			return this.data.prev_page_url + `&per_page=${this.perPage}`
		},
		nextPageUrl() {
			return this.data.next_page_url + `&per_page=${this.perPage}`
		},
		links() {
			const links = [...this.data.links]
			links.splice(0, 1)
			links.splice(-1, 1)
			links.forEach(link => {
				link.url += `&per_page=${this.perPage}`
			})
			return links
		},
		linkGeneralStyles() {
			return [
				"transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer", // Default
				"focus:ring-4 focus:ring-primary focus:ring-opacity-20", // On focus
				"focus-visible:outline-none", // On focus visible
				"dark:focus:ring-slate-700 dark:focus:ring-opacity-50", // Dark mode
				"[&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90", // On hover and not disabled
				"[&:not(button)]:text-center", // Not a button element
				"disabled:opacity-70 disabled:cursor-not-allowed", // Disabled
				"min-w-0 sm:min-w-[40px] shadow-none font-normal flex items-center justify-center border-transparent text-slate-800 sm:mr-2 dark:text-slate-300 px-1 sm:px-3"
			];
		},
		selectClasses() {
			return [
				"disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50",
				"[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50",
				"transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8",
				"focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40",
				"dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50",
			]
		}
	},
	watch: {
		perPage() {
			this.$inertia.reload({
				data: {
					per_page: this.perPage
				}
			})
		}
	},
	mounted() {
		const url = window.location.search.substring(1);
		const params = new URLSearchParams(url);

		if (params.has('per_page')) {
			this.perPage = params.get('per_page')
			console.log(this.perPage)
		}
	}
}
</script>
