<template>
    <Alert v-if="hasErrors" variant="danger" class="mb-2 mt-3">
        <div class="flex items-center">
            <div v-html="errorMessage" class="font-medium"></div>
        </div>
    </Alert>
</template>

<script>
import Alert from "../base-components/Alert/Alert.vue";

export default {
    name: "TheErrorMessages",
    components: {
        Alert
    },
    props: {
        errors: Object
    },
    computed: {
        hasErrors() {
            return Object.keys(this.errors).length
        },
        errorMessage() {
            return Object.values(this.errors).join('<br/>')
        }
    }
}
</script>
