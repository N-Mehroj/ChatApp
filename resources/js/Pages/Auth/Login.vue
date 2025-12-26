<script setup lang="ts">
import DarkModeSwitcher from "../../components/DarkModeSwitcher";
import MainColorSwitcher from "../../components/MainColorSwitcher";
import logoUrl from "../../../images/logo.svg";
import illustrationUrl from "../../../images/illustration.svg";
import { FormInput, FormCheck } from "../../base-components/Form";
import Button from "../../base-components/Button";

import { reactive } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    errors: Object
})

const form = reactive({
    email: null,
    password: null,
    remember_me: false,
})

function submit() {
    Inertia.post('/login', form)
}
</script>

<template>
    <div
        :class="[
      '-m-3 sm:-mx-8 p-3 sm:px-8 relative h-screen lg:overflow-hidden bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
      'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-primary/20 before:rounded-[100%] before:dark:bg-darkmode-400',
      'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform after:rotate-[-4.5deg] after:bg-primary after:rounded-[100%] after:dark:bg-darkmode-700',
    ]"
    >
        <DarkModeSwitcher/>
        <MainColorSwitcher/>
        <div class="container relative z-10 sm:px-10">
            <div class="block grid-cols-2 gap-4 xl:grid">
                <!-- BEGIN: Login Info -->
                <div class="flex-col hidden min-h-screen xl:flex">
                    <a href="" class="flex items-center pt-5 -intro-x">
                        <img
                            alt="Midone Tailwind HTML Admin Template"
                            class="w-6"
                            :src="logoUrl"
                        />
                        <span class="ml-3 text-lg text-white"> Платформа </span>
                    </a>
                    <div class="my-auto">
                        <img
                            alt="Midone Tailwind HTML Admin Template"
                            class="w-1/2 -mt-16 -intro-x"
                            :src="illustrationUrl"
                        />
                    </div>
                </div>
                <!-- END: Login Info -->
                <!-- BEGIN: Login Form -->
                <div class="flex h-screen py-5 my-10 xl:h-auto xl:py-0 xl:my-0">
                    <div
                        class="w-full px-5 py-8 mx-auto my-auto bg-white rounded-md shadow-md xl:ml-20 dark:bg-darkmode-600 xl:bg-transparent sm:px-8 xl:p-0 xl:shadow-none sm:w-3/4 lg:w-2/4 xl:w-auto"
                    >
                        <h2
                            class="text-2xl font-bold text-center intro-x xl:text-3xl xl:text-left"
                        >
                            Вход
                        </h2>
                        <div class="mt-2 text-center intro-x text-slate-400 xl:hidden">
                            A few more clicks to sign in to your account. Manage all your
                            e-commerce accounts in one place
                        </div>
                        <div class="mt-8 intro-x">
                            <FormInput
                                type="text"
                                class="block px-4 py-3 intro-x login__input min-w-full xl:min-w-[350px]"
                                placeholder="Email"
                                v-model="form.email"
                            />
                            <div v-if="errors?.email" class="mt-2 text-danger">
                                {{ errors?.email }}
                            </div>
                            <FormInput
                                type="password"
                                class="block px-4 py-3 mt-4 intro-x login__input min-w-full xl:min-w-[350px]"
                                placeholder="Пароль"
                                v-model="form.password"
                            />
                            <div v-if="errors?.password" class="mt-2 text-danger">
                                {{ errors?.password }}
                            </div>
                        </div>
                        <div
                            class="flex mt-4 text-xs intro-x text-slate-600 dark:text-slate-500 sm:text-sm"
                        >
                            <div class="flex items-center mr-auto">
                                <FormCheck.Input
                                    id="remember-me"
                                    type="checkbox"
                                    class="mr-2 border"
                                    v-model="form.remember_me"
                                />
                                <label class="cursor-pointer select-none" for="remember-me">
                                    Запомнить меня
                                </label>
                            </div>
                        </div>
                        <div class="mt-5 text-center intro-x xl:mt-8 xl:text-left">
                            <Button
                                variant="primary"
                                class="w-full px-4 py-3 align-top xl:w-32 xl:mr-3"
                                @click="submit"
                            >
                                Войти
                            </Button>
                        </div>
                    </div>
                </div>
                <!-- END: Login Form -->
            </div>
        </div>
    </div>
</template>
