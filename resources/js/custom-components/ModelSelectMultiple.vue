<template>
    <FormLabel>{{ label }}</FormLabel>
    <div class="tom-select w-full ts-wrapper multi plugin-remove_button plugin-dropdown_input has-items focus input-active dropdown-active"
         style="background-image: none"
         @mouseleave="isFocused = false"
    >
        <div class="ts-control"
             @click="isFocused = !isFocused"
        >
            <div class="item"
                 v-for="selectedItem in selectedItems"
            >
                {{ selectedItem[nameValue] }}
                <button class="remove"
                        @click="remove(selectedItem)"
                >
                    ×
                </button>
            </div>
        </div>
        <div v-if="items.length || isFocused"
             class="ts-dropdown single plugin-dropdown_input"
             style="visibility: visible;"
        >
            <div class="dropdown-input-wrap" v-if="isFocused">
                <input class="dropdown-input"
                       :disabled = "selectedItemsIds[0] === -1"
                       placeholder="Введите текст..."
                       v-model="query"
                       @keyup="search"
                >
            </div>
            <div class="ts-dropdown-content" v-if="selectedItems.length === 0">
                <div
                    v-if="isFocused"
                    :key="null"
                    class="option"
                    @click="select()"
                    style="cursor: pointer; opacity: 1"
                    @mouseenter="$event.target.classList.add('active')"
                    @mouseleave="$event.target.classList.remove('active')"
                >
                    {{ emptyOption.name_ru }}
                </div>
            </div>
            <div class="ts-dropdown-content" v-if="items.length">
                <div v-for="item in items"
                     v-if="isFocused"
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
    name: "ModelSelectMultiple",
    components: {
        FormLabel
    },
    props: {
        label: {
            type: String,
            required: true
        },
        modelValue: {
            type: Array,
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
        nameValue: {
            type: String,
            required: false,
            default: 'name_ru'
        },
        emptyOption: {
            type: [Array, Object],
            required: false,
            default: {
                name_ru: 'Пустое значение',
                id: '-1'
            }
        },
        searchDefault: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            items: [],
            query: '',
            isFocused: false,
            selectedItems: [],
            selectedItemsIds: [],
            searchByDefault: this.searchDefault
        }
    },
    computed: {
        filteredItems() {
            return this.items.filter(item => ! this.selectedItemsIds.includes(item.id))
        }
    },
    mounted() {
        if (this.searchByDefault) {
            this.selectedItems.push(this.emptyOption)
            this.selectedItemsIds.push(this.emptyOption.id)
            this.$emit('update:modelValue', [-1])
        }

        else if (this.modelValue != null) {
            if (this.modelValue.length) {
                const ids = [];
                if (typeof this.modelValue == 'string')
                {
                    ids.push(this.modelValue)
                }
                else {
                    this.modelValue.forEach(item => {
                        if (Number.isInteger(item)) {
                            ids.push(item)
                        } else {
                            ids.push(item.id)
                        }
                    })
                }
                this.find(ids)
            }
        }
    },
    methods: {
        find(ids) {
            axios.get(`/find/${this.searchTarget}`, {
                params: {
                    id: ids
                }
            })
                .then(response => {
                    response.data.data.forEach(item => {
                        this.select(item)
                    })
                })
        },
        search() {
            if (this.query.length) {
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
                        this.selectedItems = []

                        this.emitSelected([])
                    })
            }
        },
        select(item=null) {
            if (item == null) {
                this.selectedItems.push(this.emptyOption)
                this.selectedItemsIds.push(this.emptyOption.id)
            }
            else {
                this.items = []
                if (this.selectedItems && !this.selectedItems.find(e => e.id === item.id)) {
                    this.selectedItems.push(item)
                    this.selectedItemsIds.push(item.id)
                }
            }

            this.emitSelected(this.selectedItemsIds)
        },
        remove(item) {
            this.selectedItems = this.selectedItems.filter(selectedItem => selectedItem.id !== item.id)
            if (item.name_ru === this.emptyOption['name_ru']) {
                this.selectedItemsIds = this.selectedItemsIds.filter(id => id !== -1)
                this.searchByDefault = false
            }
            else {
                this.selectedItemsIds = this.selectedItemsIds.filter(id => id !== item.id)
            }

            this.emitSelected(this.selectedItemsIds)
        },
        emitSelected(ids) {
            this.$emit('update:modelValue', ids)
        }
    }
}
</script>
