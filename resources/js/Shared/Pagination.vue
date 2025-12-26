<template>
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
        <nav class="w-full sm:w-auto sm:mr-auto">
            <ul class="pagination">
                <li class="page-item" v-for="link in links" :class="{ 'active': link.active }">
                    <Link class="page-link" :href="link.url">
                        <i class="w-4 h-4" data-lucide="chevron-left" v-if="link.label === '&laquo; Previous'"></i>
                        <span v-if="Number.isInteger(+link.label) || link.label === '...'">
                            {{ link.label }}
                        </span>
                        <i class="w-4 h-4" data-lucide="chevron-right" v-if="link.label === 'Next &raquo;'"></i>
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'

lucide.createIcons({
    nameAttr: 'data-lucide'
});

export default {
    components: {
        Link
    },
    props: {
        links: Array
    }
}
</script>
