<template>
    <div class="mt-3" v-if="label">
        <FormLabel>{{ label }}</FormLabel>
        <FormInput v-model="value"
            :type="type"
            :placeholder="placeholder"
        />
    </div>
    <FormInput v-else v-model="value"
               :type="type"
               :placeholder="placeholder"
    />
</template>

<script>
import FormLabel from "../base-components/Form/FormLabel.vue";
import FormInput from "../base-components/Form/FormInput.vue";

export default {
    name: "TheInput",
    components: {
        FormLabel,
        FormInput
    },
    emits: ['input'],
    props: {
        modelValue: String,
        label: String,
        placeholder: {
            type: String,
            default(props) {
                return props.label
            }
        },
        type: {
            type: String,
            default: 'text'
        }
    },
    computed: {
        value: {
            get() {
                return this.modelValue
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        }
    }
}
</script>

<style scoped>

</style>
