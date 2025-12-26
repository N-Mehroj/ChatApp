<template>
    <div id="success-notification-toastify" class="toastify on toastify-right toastify-top">
        <Notification id="success-notification" class="flex items-center">
            <Lucide icon="CheckCircle" class="text-success"/>
            <div class="ml-4 mr-4">
                <div class="font-medium">{{ message }}</div>
            </div>
        </Notification>
        <button class="toast-close" @click="hide">✖</button>
    </div>
</template>

<script lang="ts">
export default {
    name: "SuccessMessage",
    props: {
        message: {
            type: String,
            default: 'Сохранено успешно!'
        },
        primary: {
            type: Boolean,
            default: false
        },
        shouldHide: {
            type: Boolean,
            default: true
        },
        hideAfter: {
            type: Number,
            default: 2500
        }
    },
    mounted() {
        if (this.primary) {
            document.querySelector('.layout-success-message').remove()
        }

        this.$inertia.on('success', (event) => {
            const page = event.detail.page.component
            const method = this.$inertia.activeVisit.method

            if ((page.endsWith('Edit') || page.endsWith('Edit_mod')) && (method === 'post' || method === 'put')) {
                this.show()
            }
        })

        document.body.appendChild(document.getElementById('success-notification-toastify'))
    },
    methods: {
        show() {
            const successNotificationToastify = document.getElementById('success-notification-toastify')
            const successNotification = document.getElementById('success-notification')

            successNotificationToastify.style.transform = 'translate(0px, 0px)'
            successNotificationToastify.style.top = '15px'

            successNotification.classList.remove('hidden')

            if (this.shouldHide) {
                setTimeout(this.hide, this.hideAfter)
            }
        },
        hide() {
            const successNotificationToastify = document.getElementById('success-notification-toastify')
            const successNotification = document.getElementById('success-notification')

            successNotificationToastify.style.transform = 'translateY(50px)'
            successNotificationToastify.style.top = '-150px'

            successNotification.classList.add('hidden')
        }
    }
}
</script>

<script setup lang="ts">
import Lucide from "../base-components/Lucide";
import Notification from "../base-components/Notification";
</script>
