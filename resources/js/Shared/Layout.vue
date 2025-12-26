<script setup lang="ts">
import { useRoute } from "vue-router";
import Devider from "./SideMenu/Devider.vue";
import Menu from "./SideMenu/Menu.vue";
import TopBar from "../components/TopBar";
import DarkModeSwitcher from "../components/DarkModeSwitcher";
import MainColorSwitcher from "../components/MainColorSwitcher";
import MobileMenu from "../components/MobileMenu";
import { enter, FormattedMenu, leave, nestedMenu } from "./SideMenu/side-menu";
import { computed, reactive } from "vue";
import {usePage} from "@inertiajs/inertia-vue3";

const route = useRoute();
let formattedMenu = reactive<Array<FormattedMenu | "devider">>([]);
const setFormattedMenu = (
    computedFormattedMenu: Array<FormattedMenu | "devider">
) => {
    Object.assign(formattedMenu, computedFormattedMenu);
};

const sideMenu = computed(() => usePage().props.value.sideMenu)
</script>

<template>
    <div
        class="pt-2 pb-7 before:content-[''] before:absolute before:inset-0 before:bg-fixed before:bg-no-repeat before:bg-skew-pattern dark:before:bg-skew-pattern-dark"
    >
        <DarkModeSwitcher/>
        <MainColorSwitcher/>
        <MobileMenu/>
        <TopBar/>
        <div
            :class="[
        'relative',
        'before:content-[\'\'] before:w-[95%] before:z-[-1] before:rounded-[1.3rem] before:bg-white/10 before:h-full before:-mt-4 before:absolute before:mx-auto before:inset-x-0 before:dark:bg-darkmode-400/50',

        // Animation
        'before:translate-y-[35px] before:opacity-0 before:animate-[0.4s_ease-in-out_0.1s_intro-wrapper] before:animate-fill-mode-forwards',
      ]"
        >
            <div
                :class="[
          'translate-y-0 bg-primary flex rounded-[1.3rem] -mt-[7px] md:mt-0 dark:bg-darkmode-400',
          'before:block before:absolute before:inset-0 before:bg-black/[0.15] before:rounded-[1.3rem] before:z-[-1]',

          // Animation
          'animate-[0.4s_ease-in-out_0.2s_intro-wrapper] animate-fill-mode-forwards translate-y-[35px]',
        ]"
            >
                <!-- BEGIN: Side Menu -->
                <nav
                    class="hidden md:block w-[105px] xl:w-[250px] px-5 pt-8 pb-16 overflow-x-hidden"
                >
                    <ul>
                        <!-- BEGIN: First Child -->
                        <template v-for="(menu, menuKey) in sideMenu">
                            <Devider
                                v-if="menu == 'devider'"
                                type="li"
                                :class="[
                  'my-6',

                  // Animation
                  `opacity-0 animate-[0.4s_ease-in-out_0.1s_intro-devider] animate-fill-mode-forwards animate-delay-${
                    (menuKey + 1) * 10
                  }`,
                ]"
                                :key="'devider-' + menuKey"
                            ></Devider>
                            <li v-else :key="menuKey">
                                <Menu
                                    :class="{
                    // Animation
                    [`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                      (menuKey + 1) * 10
                    }`]: !menu.active,
                  }"
                                    :menu="menu"
                                    :formattedMenuState="[formattedMenu, setFormattedMenu]"
                                    level="first"
                                ></Menu>
                                <!-- BEGIN: Second Child -->
                                <Transition @enter="enter" @leave="leave">
                                    <ul
                                        v-if="menu.subMenu && menu.activeDropdown"
                                        :class="[
                      'bg-white/[0.04] rounded-lg relative dark:bg-transparent',
                      'before:content-[\'\'] before:block before:inset-0 before:bg-primary/60 before:rounded-lg before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                      {
                        block: menu.activeDropdown,
                      },
                      { hidden: !menu.activeDropdown },
                    ]"
                                    >
                                        <li
                                            v-for="(subMenu, subMenuKey) in menu.subMenu"
                                            :key="subMenuKey"
                                        >
                                            <Menu
                                                :class="`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                          (subMenuKey + 1) * 10
                        }`"
                                                :menu="subMenu"
                                                :formattedMenuState="[formattedMenu, setFormattedMenu]"
                                                level="second"
                                            ></Menu>
                                            <!-- BEGIN: Third Child -->
                                            <Transition
                                                @enter="enter"
                                                @leave="leave"
                                                v-if="subMenu.subMenu"
                                            >
                                                <ul
                                                    v-if="subMenu.subMenu && subMenu.activeDropdown"
                                                    :class="[
                            'bg-white/[0.04] rounded-lg relative dark:bg-transparent',
                            'before:content-[\'\'] before:block before:inset-0 before:bg-primary/60 before:rounded-lg before:absolute before:z-[-1] before:dark:bg-darkmode-900/30',
                            {
                              block: subMenu.activeDropdown,
                            },
                            { hidden: !subMenu.activeDropdown },
                          ]"
                                                >
                                                    <li
                                                        v-for="(
                              lastSubMenu, lastSubMenuKey
                            ) in subMenu.subMenu"
                                                        :key="lastSubMenuKey"
                                                    >
                                                        <Menu
                                                            :class="`opacity-0 translate-x-[50px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards animate-delay-${
                                (lastSubMenuKey + 1) * 10
                              }`"
                                                            :menu="lastSubMenu"
                                                            :formattedMenuState="[
                                formattedMenu,
                                setFormattedMenu,
                              ]"
                                                            level="third"
                                                        ></Menu>
                                                    </li>
                                                </ul>
                                            </Transition>
                                            <!-- END: Third Child -->
                                        </li>
                                    </ul>
                                </Transition>
                                <!-- END: Second Child -->
                            </li>
                        </template>
                        <!-- END: First Child -->
                    </ul>
                </nav>
                <!-- END: Side Menu -->
                <!-- BEGIN: Content -->
                <div
                    class="px-4 md:px-[22px] max-w-full md:max-w-auto rounded-[1.3rem] flex-1 min-w-0 min-h-screen pb-10 shadow-sm bg-slate-100 dark:bg-darkmode-700 before:content-[''] before:w-full before:h-px before:block"
                >
                    <success-message class="layout-success-message"></success-message>
                    <slot></slot>
                </div>
                <!-- END: Content -->
            </div>
        </div>
    </div>
</template>
