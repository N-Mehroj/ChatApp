<template>
    <Dialog
        :open="isOpen"
        @close="closeModal"
    >
        <Dialog.Panel>
            <div class="p-5 text-center">
                <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
                <div class="mt-5 text-3xl">Вы уверены?</div>
            </div>
            <div class="px-5 pb-8 text-center">
                <Button
                    variant="outline-secondary"
                    type="button"
                    @click="closeModal"
                    class="w-24 mr-1"
                >
                    Отмена
                </Button>
                <Button
                    variant="danger"
                    type="button"
                    class="w-24"
                    @click="deleteRecord"
                >
                    Удалить
                </Button>
            </div>
        </Dialog.Panel>
    </Dialog>
</template>

<script lang="ts">
export default {
    name: "DeleteModal",
    emits: ['cancel'],
    props: {
        deleteUrl: String,
        isOpen: Boolean
    },
    methods: {
        deleteRecord() {
            this.$inertia.delete(this.deleteUrl)

            this.$emit('cancel')
        },
        closeModal() {
            this.$emit('cancel')
        }
    }
}
</script>

<script setup lang="ts">
import Button from "../base-components/Button";
import Lucide from "../base-components/Lucide";
import { Dialog } from "../base-components/Headless";
</script>
