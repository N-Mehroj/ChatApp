<template>
    <Table class="border-spacing-y-[10px] border-separate mt-5">
        <Table.Thead>
            <Table.Tr>
                <Table.Th class="border-b-0 whitespace-nowrap">День недели</Table.Th>
                <Table.Th class="border-b-0 whitespace-nowrap">Начало</Table.Th>
                <Table.Th class="border-b-0 whitespace-nowrap">Конец</Table.Th>
                <Table.Th class="border-b-0 whitespace-nowrap">Выходной</Table.Th>
            </Table.Tr>
        </Table.Thead>
        <Table.Tbody>
            <Table.Tr v-for="(schedule, index) in value"
                       :key="schedule.day_number"
                       class="intro-y dark:border-darkmode-300 border-b-0 dark:bg-darkmode-600"
            >
                <Table.Td class="first:rounded-l-md last:rounded-r-md w-40 bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                    <FormLabel class="mb-0">{{ days[index].label }}</FormLabel>
                    <FormInput type="text"
                               hidden
                               readonly="true"
                               class="px-4 py-3 pr-10 intro-y"
                               v-model="schedule.day_number"
                    />
                </Table.Td>
                <Table.Td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                    <FormInput type="time"
                               class="px-4 py-3 pr-10 intro-y"
                               v-model="schedule.start_at"
                    />
                </Table.Td>
                <Table.Td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                    <FormInput type="time"
                               class="px-4 py-3 pr-10 intro-y"
                               v-model="schedule.finish_at"
                    />
                </Table.Td>
                <Table.Td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                    <FormSwitch class="flex flex-col items-start">
                        <FormSwitch.Input id="post-form-5"
                                          type="checkbox"
                                          :key="index"
                                          v-model="schedule.is_weekend"
                                          :true-value="1"
                                          :false-value="0"
                        />
                    </FormSwitch>
                </Table.Td>
            </Table.Tr>
        </Table.Tbody>
    </Table>
</template>

<script lang="ts">
export default {
    name: "WorkSchedule",
    props: {
        modelValue: {
            type: [Array, Object],
            default: false
        },
    },
    data() {
        return {
            defaultSchedule: [
                {
                    day_number: 1,
                    start_at: '09:00',
                    finish_at: '18:00',
                    is_weekend: false,
                },
                {
                    day_number: 2,
                    start_at: '09:00',
                    finish_at: '18:00',
                    is_weekend: false,
                },
                {
                    day_number: 3,
                    start_at: '09:00',
                    finish_at: '18:00',
                    is_weekend: false,
                },
                {
                    day_number: 4,
                    start_at: '09:00',
                    finish_at: '18:00',
                    is_weekend: false,
                },
                {
                    day_number: 5,
                    start_at: '09:00',
                    finish_at: '18:00',
                    is_weekend: false,
                },
                {
                    day_number: 6,
                    start_at: '09:00',
                    finish_at: '18:00',
                    is_weekend: false,
                },
                {
                    day_number: 7,
                    start_at: '09:00',
                    finish_at: '18:00',
                    is_weekend: false,
                },
            ],
            days: [
                {
                    label: 'Пн',
                },
                {
                    label: 'Вт',
                },
                {
                    label: 'Ср',
                },
                {
                    label: 'Чт',
                },
                {
                    label: 'Пт',
                },
                {
                    label: 'Сб',
                },
                {
                    label: 'Вс',
                }
            ],
            value: []
        }
    },
    beforeMount() {
        if (this.modelValue && this.modelValue.length) {
            this.value = this.modelValue
        } else {
            this.value = this.defaultSchedule
        }
    },
    watch: {
        value: {
            deep: true,
            handler(newVal) {
                this.$emit('update:modelValue', newVal)
            }
        }
    }
}
</script>

<script setup lang="ts">
import { FormInput, FormLabel, FormSwitch } from "../base-components/Form";
import Table from "../base-components/Table";
</script>
