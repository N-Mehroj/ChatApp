<script setup lang="ts">
import { ref, provide } from "vue";
import Button from "../../base-components/Button";
import { TinySliderElement } from "../../base-components/TinySlider";
import Lucide from "../../base-components/Lucide";
import Tippy from "../../base-components/Tippy";
import ReportDonutChart from "../../components/ReportDonutChart";
import ReportBarChart from "../../components/ReportBarChart";
import { Menu, Tab } from "../../base-components/Headless";
import Notification from "../../base-components/Notification";
import { NotificationElement } from "../../base-components/Notification";
import { Inertia } from "@inertiajs/inertia";
import SuccessMessage from "../../custom-components/SuccessMessage.vue";

const importantNotesRef = ref<TinySliderElement>();

provide("bind[importantNotesRef]", (el: TinySliderElement) => {
    importantNotesRef.value = el;
});

const successNotification = ref<NotificationElement>();

const successNotificationToggle = () => {
    successNotification.value?.showToast();
};

provide("bind[successNotification]", (el: NotificationElement) => {
    successNotification.value = el;
});

const clearCache = () => {
    Inertia.get('clear-cache', {}, {
        onSuccess: () => {
            successNotificationToggle()
        }
    })
}
</script>

<template>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-2 lg:col-span-8 xl:col-span-6">
                    <div class="items-center block h-10 intro-y sm:flex">
                        <h2 class="mr-5 text-lg font-medium truncate">Общая информация</h2>
                    </div>
                    <div
                        class="relative mt-12 intro-y sm:mt-5 before:content-[''] before:w-[90%] before:shadow-[0px_3px_20px_#0000000b] before:bg-slate-50 before:h-full before:mt-3 before:absolute before:rounded-md before:mx-auto before:inset-x-0 before:dark:bg-darkmode-400/70"
                    >
                        <div class="box sm:flex">
                            <div class="flex flex-col justify-center flex-1 px-8 py-12">
                                <Lucide icon="ShoppingBag" class="w-10 h-10 text-warning" />
                                <div class="relative text-3xl font-medium mt-12 pl-4 ml-0.5">
                  <span
                      class="absolute text-2xl font-medium top-0 left-0 -ml-0.5"
                  >
                    $
                  </span>
                                    54.143
                                </div>
                                <Tippy
                                    as="div"
                                    class="cursor-pointer py-[3px] flex rounded-full text-white text-xs pl-2 pr-1 mt-3 mr-auto items-center font-medium bg-success"
                                    content="47% Higher than last month"
                                >
                                    47% <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                                </Tippy>
                                <div class="mt-4 text-slate-500">
                                    Доход за месяц
                                </div>
                            </div>
                            <div
                                class="flex flex-col justify-center flex-1 px-8 py-12 border-t border-dashed sm:border-t-0 sm:border-l border-slate-200 dark:border-darkmode-300"
                            >
                                <div class="text-xs text-slate-500">Транзакции</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">4.501</div>
                                    <Tippy
                                        as="div"
                                        class="flex ml-2 text-xs font-medium cursor-pointer text-danger"
                                        content="2% Lower than last month"
                                    >
                                        2%
                                        <Lucide icon="ChevronDown" class="w-4 h-4 ml-0.5" />
                                    </Tippy>
                                </div>
                                <div class="mt-5 text-xs text-slate-500">Транзакции</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">2</div>
                                    <Tippy
                                        as="div"
                                        class="flex ml-2 text-xs font-medium cursor-pointer text-danger"
                                        content="0.1% Lower than last month"
                                    >
                                        0.1%
                                        <Lucide icon="ChevronDown" class="w-4 h-4 ml-0.5" />
                                    </Tippy>
                                </div>
                                <div class="mt-5 text-xs text-slate-500">
                                    Транзакции
                                </div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">$72.000</div>
                                    <Tippy
                                        as="div"
                                        class="flex ml-2 text-xs font-medium cursor-pointer text-success"
                                        content="49% Higher than last month"
                                    >
                                        49%
                                        <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                                    </Tippy>
                                </div>
                                <div class="mt-5 text-xs text-slate-500">
                                    Транзакции
                                </div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">$54.000</div>
                                    <Tippy
                                        as="div"
                                        class="flex ml-2 text-xs font-medium cursor-pointer text-success"
                                        content="52% Higher than last month"
                                    >
                                        52%
                                        <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                                    </Tippy>
                                </div>
                                <div class="mt-5 text-xs text-slate-500">Транзакции</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">2.500</div>
                                    <Tippy
                                        as="div"
                                        class="flex ml-2 text-xs font-medium cursor-pointer text-success"
                                        content="52% Higher than last month"
                                    >
                                        52%
                                        <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                                    </Tippy>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: General Report -->
                <!-- BEGIN: Visitors -->
                <div class="col-span-12 mt-2 sm:col-span-6 lg:col-span-4 xl:col-span-3">
                    <div class="flex items-center h-10 intro-y">
                        <h2 class="mr-5 text-lg font-medium truncate">Посетители</h2>
                    </div>
                    <div
                        class="mt-5 intro-y relative before:content-[''] before:w-[90%] before:shadow-[0px_3px_20px_#0000000b] before:bg-slate-50 before:h-full before:mt-3 before:absolute before:rounded-md before:mx-auto before:inset-x-0 before:dark:bg-darkmode-400/70"
                    >
                        <div class="p-5 box">
                            <div class="flex items-center">
                                Активные пользователи
                                <Menu class="ml-auto">
                                    <Menu.Button as="a" class="block w-5 h-5 -mr-2" href="#">
                                        <Lucide
                                            icon="MoreVertical"
                                            class="w-5 h-5 text-slate-500"
                                        />
                                    </Menu.Button>
                                    <Menu.Items class="w-40">
                                        <Menu.Item>
                                            <Lucide icon="FileText" class="w-4 h-4 mr-2" />
                                            Export
                                        </Menu.Item>
                                        <Menu.Item>
                                            <Lucide icon="Settings" class="w-4 h-4 mr-2" />
                                            Settings
                                        </Menu.Item>
                                    </Menu.Items>
                                </Menu>
                            </div>
                            <div class="mt-2 text-2xl font-medium">214</div>
                            <div class="flex pb-2 mt-4 border-b border-slate-200">
                                <div class="text-xs text-slate-500">Просмотров в секунду</div>
                                <Tippy
                                    as="div"
                                    class="flex ml-auto text-xs font-medium cursor-pointer text-success"
                                    content="49% Lower than last month"
                                >
                                    49% <Lucide icon="ChevronUp" class="w-4 h-4 ml-0.5" />
                                </Tippy>
                            </div>
                            <div class="mt-2 border-b broder-slate-200">
                                <div class="-mb-1.5 -ml-2.5">
                                    <ReportBarChart :height="79" />
                                </div>
                            </div>
                            <div
                                class="flex pb-2 mt-4 mb-2 text-xs border-b text-slate-500 border-slate-200"
                            >
                                <div>Топ страниц</div>
                                <div class="ml-auto">Активных пользователей</div>
                            </div>
                            <div class="flex">
                                <div>/letz-lara…review/2653</div>
                                <div class="ml-auto">472</div>
                            </div>
                            <div class="flex mt-1.5">
                                <div>/midone…review/1674</div>
                                <div class="ml-auto">294</div>
                            </div>
                            <div class="flex mt-1.5">
                                <div>/profile…review/46789</div>
                                <div class="ml-auto">83</div>
                            </div>
                            <div class="flex mt-1.5">
                                <div>/profile…review/24357</div>
                                <div class="ml-auto">21</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: Visitors -->
                <!-- BEGIN: Users By Age -->
                <div
                    class="col-span-12 mt-2 sm:col-span-6 lg:col-span-4 xl:col-span-3 lg:mt-6 xl:mt-2"
                >
                    <div class="flex items-center h-10 intro-y">
                        <h2 class="mr-5 text-lg font-medium truncate">Возраст пользователей</h2>
                    </div>
                    <div
                        class="mt-5 intro-y relative before:content-[''] before:w-[90%] before:shadow-[0px_3px_20px_#0000000b] before:bg-slate-50 before:h-full before:mt-3 before:absolute before:rounded-md before:mx-auto before:inset-x-0 before:dark:bg-darkmode-400/70"
                    >
                        <Tab.Group class="p-5 box">
                            <Tab.List
                                variant="pills"
                                class="w-4/5 mx-auto rounded-md bg-slate-100 dark:bg-black/20"
                            >
                                <Tab>
                                    <Tab.Button as="button" class="w-full py-1.5 px-2">
                                        Активные
                                    </Tab.Button>
                                </Tab>
                                <Tab>
                                    <Tab.Button as="button" class="w-full py-1.5 px-2">
                                        Не активные
                                    </Tab.Button>
                                </Tab>
                            </Tab.List>
                            <Tab.Panels class="mt-6">
                                <Tab.Panel>
                                    <div class="relative">
                                        <ReportDonutChart :height="208" class="mt-3" />
                                        <div
                                            class="absolute top-0 left-0 flex flex-col items-center justify-center w-full h-full"
                                        >
                                            <div class="text-2xl font-medium">2.501</div>
                                            <div class="text-slate-500 mt-0.5 text-center">Активных <br> пользователей</div>
                                        </div>
                                    </div>
                                    <div class="mx-auto mt-5 w-52 sm:w-auto">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 mr-3 rounded-full bg-primary"></div>
                                            <span class="truncate">17 - 30 лет</span>
                                            <span class="ml-auto font-medium">62%</span>
                                        </div>
                                        <div class="flex items-center mt-4">
                                            <div class="w-2 h-2 mr-3 rounded-full bg-pending"></div>
                                            <span class="truncate">31 - 50 лет</span>
                                            <span class="ml-auto font-medium">33%</span>
                                        </div>
                                        <div class="flex items-center mt-4">
                                            <div class="w-2 h-2 mr-3 rounded-full bg-warning"></div>
                                            <span class="truncate">&gt;= 50 лет</span>
                                            <span class="ml-auto font-medium">10%</span>
                                        </div>
                                    </div>
                                </Tab.Panel>
                            </Tab.Panels>
                        </Tab.Group>
                    </div>
                </div>
                <!-- END: Users By Age -->
            </div>
        </div>
        <div class="col-span-2">
            <h2 class="mr-5 text-lg font-medium truncate">Очистка кэша</h2>
            <Button @click="clearCache" variant="primary" class="mt-3">
                Очистить кэш
            </Button>
        </div>
        <div class="col-span-3">
            <div class="text-center">
                <!-- BEGIN: Notification Content -->
                <Notification refKey="successNotification" class="flex">
                    <Lucide icon="CheckCircle" class="text-success" />
                    <div class="ml-4 mr-4">
                        <div class="font-medium">Кэш успешно очищен!</div>
                    </div>
                </Notification>
                <success-message :primary="true" style="display: none"></success-message>
                <!-- END: Notification Content -->
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Layout from '../../Shared/Layout.vue'

export default {
    layout: Layout
}
</script>
