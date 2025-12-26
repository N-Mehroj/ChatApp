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
        <div v-if="isFocused"
             class="ts-dropdown single plugin-dropdown_input"
             style="visibility: visible;"
             @click="search"
        >
            <div class="dropdown-input-wrap">
                <input class="dropdown-input"
                       placeholder="Введите текст..."
                       v-model="query"
                       @keyup="search"
                >
            </div>
            <div class="ts-dropdown-content">
                <div v-if="isFocused"
                     v-for="item in items"
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
    name: "ModelSelectMultipleDependent",
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
            required: true,
        },
        placeholder: {
            type: String,
            default: 'Введите текст...'
        },
        searchTarget: {
            type: String,
            required: true
        },
        searchBy: {
            type: [Array, Number],
            required: false
        },
        nameValue: {
            type: String,
            required: false,
            default: 'name_ru'
        }
    },
    data() {
        return {
            items: [],
            query: '',
            isFocused: false,
            selectedItems: [],
            selectedItemsIds: [],
        }
    },
    computed: {
        filteredItems() {
            return this.items.filter(item => ! this.selectedItemsIds.includes(item.id))
        }
    },
    mounted() {
        if (this.modelValue != null) {
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
            axios.get(`/find-by/${this.searchTarget}`, {
                params: {
                    search_by: this.searchBy,
                    ids: ids
                }
            })
                .then(response => {
                    response.data.data.forEach(item => {
                        this.select(item)
                    })
                })
        },
        search() {
            let searchBy = []
            if (this.searchBy) {
                this.searchBy.forEach(item => searchBy.push(item))
            }
            axios.get(`/search-by/${this.searchTarget}`, {
                params: {
                    search_by: searchBy,
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
        },
        select(item) {
            this.items = []
            if (this.selectedItems && !this.selectedItems.find(e => e.id === item.id)){
                this.selectedItems.push(item)
                this.selectedItemsIds.push(item.id)
            }

            this.emitSelected(this.selectedItemsIds)
        },
        remove(item) {
            this.selectedItems = this.selectedItems.filter(selectedItem => selectedItem.id !== item.id)
            this.selectedItemsIds = this.selectedItemsIds.filter(id => id !== item.id)

            this.emitSelected(this.selectedItemsIds)
        },
        emitSelected(ids) {
            this.$emit('update:modelValue', ids)
        }
    }
}
</script>
