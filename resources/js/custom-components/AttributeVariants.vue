<template>
    <div class=" sm:mr-4" >
        <div class="items-center sm:flex mt-5" v-for="(item, index) in newValue" v-if="newValue.length > 0">
            <template v-if="!item.softDeleted">
                <Label v-if="item.id">ID: {{item.id}}</Label>
                <TheInput placeholder="Значение RU"
                          v-model="item.value_ru"
                          class="mr-4 ml-4 sm:w-[300px]"
                />
                <TheInput placeholder="Значение UZ"
                          v-model="item.value_uz"
                          class="mr-4 ml-4 sm:w-[300px]"
                />
                <Button
                    variant='danger'
                    type="button"
                    class="w-full sm:w-auto h-10 mr-2"
                    @click="removeRow(index)"
                >
                    Удалить
                </Button>
            </template>
        </div>
        <Button class="ml-5 mt-5" @click="addRow" variant="primary" type="button">Добавить</Button>
    </div>
</template>

<script lang="ts">
import axios from "axios";
import Button from "../base-components/Button/Button.vue";

export default {
    name: 'AttributeVariants',
    components: {
        Button
    },
    props: {
        value: {
            type: [Array, Object]
        },
        modelValue: undefined,
    },
    data() {
        return {
            newValue: this.modelValue,
            nextId: null,
        }
    },
    watch: {
        value(newVal) {
            this.newValue = newVal;
        }
    },
    methods: {
        async addRow() {
            if (! this.nextId) {
                const res = await axios.get('/attribute-variants/last')
                this.nextId = res.data;
            }

            this.newValue.push({
                id: this.nextId += 1,
                value_ru: null,
                value_uz: null,
                newVariant: true
            });

            this.$emit('update:modelValue', this.newValue)
        },
        removeRow(index) {
            if (this.newValue[index].newVariant) {
                this.nextId -= 1;
                this.newValue.splice(index, 1);
            } else if (this.newValue[index].id) {
                this.newValue[index].value_ru = ''
                this.newValue[index].softDeleted = true
            }
        },
    }
}
</script>
