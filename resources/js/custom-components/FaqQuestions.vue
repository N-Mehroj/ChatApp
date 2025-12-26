<template>
    <div>
        <Disclosure.Group variant="boxed">
            <div class="mb-5" v-for="(item, index) in newValue" v-if="newValue.length > 0">
                <template v-if="!item.softDeleted">
                    <Disclosure :index="index">
                        <Disclosure.Button>{{ item.question_ru || 'Новый вопрос' }}</Disclosure.Button>
                        <Disclosure.Panel class="leading-relaxed text-slate-600 dark:text-slate-500">
                            <TheInput label="Вопрос RU" v-model="item.question_ru" class="mt-0"/>
                            <TheInput label="Вопрос UZ" v-model="item.question_uz"/>

                            <div class="mt-3">
                                <FormLabel>Ответ RU</FormLabel>
                                <ClassicEditor v-model="item.answer_ru"/>
                            </div>
                            <div class="mt-3">
                                <FormLabel>Ответ UZ</FormLabel>
                                <ClassicEditor v-model="item.answer_uz"/>
                            </div>

                            <div class="mt-3">
                                <FormLabel>Альтернативный ответ RU</FormLabel>
                                <ClassicEditor v-model="item.alt_answer_1_ru"/>
                            </div>
                            <div class="mt-3">
                                <FormLabel>Альтернативный ответ UZ</FormLabel>
                                <ClassicEditor v-model="item.alt_answer_1_uz"/>
                            </div>
                            <TheInput label="Условие для альтернативного ответа (Должен предоставить разработчик)"
                                      v-model="item.alt_answer_1_condition"
                            />

                            <Button variant='danger'
                                    type="button"
                                    class="w-full sm:w-auto h-10 mt-5"
                                    style="width: 100%"
                                    @click="removeRow(index)"
                            >
                                Удалить
                            </Button>
                        </Disclosure.Panel>
                    </Disclosure>
                </template>
            </div>
        </Disclosure.Group>
        <Button class="border-dashed" style="width: 100%" variant="outline-primary" @click="addRow">
            Добавить
        </Button>
    </div>
</template>

<script lang="ts">
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
        modelValue: Array,
    },
    data() {
        return {
            newValue: this.modelValue,
        }
    },
    mounted() {
        if (!Object.entries(this.modelValue).length) {
            this.addRow()
        }
    },
    watch: {
        value(newVal) {
            this.newValue = newVal;
        }
    },
    methods: {
        async addRow() {
            this.newValue.push({
                question_ru: '',
                question_uz: '',
                answer_ru: '',
                answer_uz: '',
                alt_answer_1_ru: '',
                alt_answer_1_uz: '',
                alt_answer_1_condition: '',
                newVariant: true
            });

            this.$emit('update:modelValue', this.newValue)
        },
        removeRow(index) {
            if (this.newValue[index].newVariant) {
                this.newValue.splice(index, 1);
            } else if (this.newValue[index].id) {
                this.newValue[index].softDeleted = true
            }
        },
    }
}
</script>

<script setup lang="ts">
import {Disclosure} from "../base-components/Headless";
import TheInput from "./TheInput.vue";
import ClassicEditor from "../base-components/Ckeditor/ClassicEditor.vue";
import FormLabel from "../base-components/Form/FormLabel.vue";
</script>
