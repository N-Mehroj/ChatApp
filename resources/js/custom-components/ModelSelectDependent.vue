<template>
    <FormLabel v-if="label">{{ label }}</FormLabel>
    <div class="ts-wrapper tom-select w-full single plugin-dropdown_input has-options bg-white"
         style="background-image: none"
    >
        <div class="ts-control">
            <input class="items-placeholder"
                   style="cursor: text; color: inherit"
                   v-model="query"
                   :disabled="disabled"
                   :placeholder="placeholder"
                   @keyup="search"
                   @click="search"
            >
        </div>
        <div v-if="items.length"
             class="ts-dropdown single plugin-dropdown_input"
             style="visibility: visible;"
        >
            <div class="ts-dropdown-content">
                <div v-for="item in items"
                     :key="item.id"
                     class="option"
                     style="cursor: pointer; opacity: 1"
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
    name: "ModelSelectDependent",
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
        disabled: {
            type: Boolean,
            required: false,
            default: false
        },
        nameValue: {
            type: String,
            required: false,
            default: 'name_ru'
        },
        searchBy: {
            type: String,
            required: false
        },
    },
    data() {
        return {
            items: [],
            query: '',
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
            axios.get(`/find-by/${this.findTarget}`, {
                params: {
                    id: id
                }
            })
                .then(response => {
                    this.select(response.data.data)

                })
        },
        search() {
            axios.get(`/search-by/${this.searchTarget}`, {
                params: {
                    search_by: this.searchBy,
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
