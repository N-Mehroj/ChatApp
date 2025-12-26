<template>
    <div>
        <FormLabel>{{ label }}</FormLabel>
        <TomSelect
            v-model="dataItem"
            class="w-full"
        >
            <option value="">Не выбрано</option>
            <option v-for="item in items"
                    :value="item[idValue]"
                    :key="item[idValue]">
                {{ item[nameValue] }}
            </option>
        </TomSelect>
    </div>
</template>

<script lang="ts">
import { FormLabel } from "../base-components/Form";
import TomSelect from "../base-components/TomSelect";

export default {
    name: "ItemSelect",
    components: {
        FormLabel, TomSelect
    },
    props: {
        value: {
            type: String
        },
        label: {
            type: String,
            required: true
        },
        items: {
            type: [Object, Array],
            required: true
        },
        nameValue: {
            type: String,
            required: false,
            default: 'name_ru'
        },
        idValue: {
            type: String,
            required: false,
            default: 'id'
        },
        modelValue: undefined
    },
    data() {
        return {
            dataItem: '',
        }
    },
    mounted() {
        if (this.modelValue) {
            this.dataItem = this.modelValue?.toString()
        } else {
            this.dataItem = ''
        }
    },
    watch: {
        dataItem: function(val) {
            this.$emit('update:modelValue', val);
        },
        modelValue(newValue) {
            this.dataItem= newValue;
        }
    },
}
</script>
