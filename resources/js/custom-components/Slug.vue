<template>
    <Dialog
        :open="isModalActive"
        @close="cancelEdit"
    >
        <Dialog.Panel>
            <div class="p-5 text-center">
                <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
                <div class="mt-5 text-base">
                    <p>ВНИМАНИЕ: Поле slug не рекомендуется редактировать, не согласовав с руководством.</p>
                    Вы точно хотите его изменить?
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <Button
                    variant="outline-secondary"
                    type="button"
                    @click="cancelEdit"
                    class="w-24 mr-1"
                >
                    Нет
                </Button>
                <Button
                    variant="danger"
                    type="button"
                    class="w-24"
                    @click="confirmEdit"
                >
                    Да
                </Button>
            </div>
        </Dialog.Panel>
    </Dialog>
    <FormLabel :style="'margin-top: 10px'"> Символьный код </FormLabel>
    <div class="items-center sm:flex">
        <TheInput :disabled="editable === false"
                   v-model="localValue"
                  class="mr-4"
         />
        <Button
            variant='secondary'
            type="button"
            class="w-full sm:w-auto h-10"
            @click="makeEditable"
        >
            Редактировать
        </Button>
    </div>
    <Alert v-if="hasErrors" variant="danger" class="mb-2 mt-3">
        <div class="flex items-center">
            <div v-html="errorMessage" class="font-medium"></div>
        </div>
    </Alert>
</template>

<script lang="ts">
import Alert from "../base-components/Alert/Alert.vue";
import TheInput from "./TheInput.vue";

export default {
    name: 'Slug',
    components: {
        TheInput,
        Alert
    },
    props: {
        value: {
            type: String,
            default: null
        },
        modelValue: undefined,
    },
    data: () => ({
        editable: false,
        isModalActive: false,
        hasErrors: false,
        errorMessage: 'Символьный код введен неправильно',
    }),
    computed: {
        localValue: {
            get() {
                return this.modelValue
            },
            set(value) {
                const regex = /^[a-z0-9]+(?:(?:-)+[a-z0-9]+)*$/gm
                if (regex.exec(value)) {
                    this.hasErrors = false
                    console.log(value)
                    this.$emit('update:modelValue', value)
                }
                else {
                    this.hasErrors = true
                    return null
                }
            }
        }
    },
    methods: {
        makeEditable() {
            this.isModalActive = true
        },
        confirmEdit() {
            this.isModalActive = false
            this.editable = true
        },
        cancelEdit() {
            this.isModalActive = false
        },
    }
}
</script>

<script setup lang="ts">
import Button from "../base-components/Button";
import { FormLabel } from "../base-components/Form";
import Lucide from "../base-components/Lucide";
import { Dialog } from "../base-components/Headless";
</script>
