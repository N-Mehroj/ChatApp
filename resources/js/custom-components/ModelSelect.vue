<template>
    <FormLabel v-if="label">{{ label }}</FormLabel>
    <div class="ts-wrapper tom-select w-full single plugin-dropdown_input has-options bg-white"
         style="background-image: none"
         @mouseleave ="isFocused = false"
    >
        <div class="ts-control" >
            <input class="items-placeholder"
                   style="cursor: text; color: inherit"
                   v-model="query"
                   :disabled="disabled"
                   :placeholder="placeholder"
                   @keyup="search"
            >
        </div>
        <div v-if="items.length && isFocused"
             class="ts-dropdown single plugin-dropdown_input"
             style="visibility: visible;"
        >
            <div class="ts-dropdown-content" >
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="option"
                    style="cursor: pointer; opacity: 1;"
                    @click="select(item)"
                    @mouseenter="$event.target.classList.add('active')"
                    @mouseleave="$event.target.classList.remove('active')"
                >
                    {{ item[nameValue] }} (ID: {{ item.id }})
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FormLabel from "../base-components/Form/FormLabel.vue";

export default {
    name: "ModelSelect",
    components: {
        FormLabel
    },
    props: {
        label: {
            type: String,
            required: true
        },
        modelValue: {
            type: [Array, String, Number],
            required: true
        },
        placeholder: {
            type: String,
            default: 'Введите текст...'
        },
        searchTarget: {
            type: String,
            required: true
        },
        findTarget: {
            type: String,
            required: false,
            default(props) {
                return props.searchTarget
            }
        },
        nameValue: {
            type: String,
            required: false,
            default: 'name_ru'
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        },

    },
    data() {
        return {
            items: [],
            query: '',
            isFocused: false,
            selected: {
                id: null,
            }
        }
    },
    mounted() {
        if (this.modelValue) {
            if (Number.isInteger(this.modelValue)) {
                this.find(this.modelValue)
            } else {
                this.select(this.modelValue)
            }
        }
    },
    methods: {
        find(id) {
            axios.get(`/find/${this.findTarget}`, {
                params: {
                    id: id
                }
            })
                .then(response => {
                    this.select(response.data.data)
                })
        },
        search() {
            this.isFocused=true

            axios.get(`/search/${this.searchTarget}`, {
                params: {
                    q: this.query
                }
            })
                .then(response => {
                    this.items = response.data.data
                })
                .catch(error => {
                    this.items = []
                    this.selected = {
                        id: null,
                        nameValue: this.nameValue
                    }

                    this.emitSelected(null)
                })
        },
        select(value) {
            this.items = []
            this.selected = value
            this.query = value[this.nameValue]
            this.emitSelected(value.id)
        },
        emitSelected(value) {
            this.$emit('update:modelValue', value)
        }
    }
}
</script>
