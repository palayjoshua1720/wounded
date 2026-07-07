<template>
    <BaseModal v-model="isOpen" :title="isEdit ? 'Edit Graft Size' : 'New Graft Size(s)'" size="2xl">
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Brand <span class="text-red-500 ml-1">*</span>
                </label>
                <select v-model="localFormData.brand_id" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                    <option disabled value="">Select a Brand</option>
                    <option v-for="brand in brands" :key="brand.brand_id" :value="brand.brand_id">
                        {{ brand.manufacturer?.manufacturer_name || 'Unknown Manufacturer' }} -
                        {{ brand.brand_name }}
                    </option>
                </select>
            </div>

            <div>
                <div class="flex items-center gap-2 mb-4">
                    <PencilRuler class="w-5 h-5 text-green-500" />
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                        Graft Sizes <span class="text-red-500">*</span>
                    </h3>
                </div>

                <div class="space-y-6">
                    <div v-for="(graftSize, index) in localFormData.graftSizes" :key="graftSize.id || index"
                        class="relative p-5 sm:p-6 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Size Entry {{ index + 1 }}
                            </span>
                            <button v-if="index > 0" type="button" @click="removeGraftSize(index)"
                                class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <Trash2 class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-5">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                    Item No <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Tag class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input v-model="graftSize.item_no" type="text" required placeholder="GS-001"
                                        class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                    Size <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <RulerDimensionLine
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input v-model="graftSize.size" type="text" required placeholder="2cm × 2cm"
                                        class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                    Area (cm²) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Diameter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input v-model.number="graftSize.area" type="number" required min="0" step="0.01"
                                        placeholder="4.00"
                                        class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                    Price <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <DollarSign
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input :value="focusedPriceField === `graft-${index}`
                                        ? graftSize.price ?? ''
                                        : formatPriceForDisplay(graftSize.price)
                                        " @focus="focusedPriceField = `graft-${index}`"
                                        @blur="focusedPriceField = null"
                                        @input="graftSize.price = parsePriceInput(($event.target as HTMLInputElement).value)"
                                        type="text" inputmode="decimal" required min="0" placeholder="0.00"
                                        class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                    Stock <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Package class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input v-model.number="graftSize.stock" type="number" required min="0"
                                        placeholder="0"
                                        class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button v-if="!isEdit" type="button" @click="addGraftSize"
                    class="mt-4 flex items-center justify-center w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors font-medium">
                    <Plus class="w-5 h-5 mr-2" />
                    Add Another Size
                </button>
            </div>
        </form>
        <template #actions>
            <div class="flex justify-end w-full p-5 gap-3">
                <button v-if="!isEdit" type="button" @click="handleBack"
                    class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" />
                    Back
                </button>

                <button type="button" @click="handleSubmit"
                    class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md">
                    {{ isEdit ? 'Update Graft Size' : 'Create Graft Size(s)' }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { PencilRuler, Trash2, Tag, RulerDimensionLine, Diameter, DollarSign, Package, Plus, ArrowLeft } from 'lucide-vue-next'

export interface GraftSize {
    id?: string
    item_no: string
    size: string
    area: number | null
    price: number | null
    stock: number
}

export interface Brand {
    brand_id: string
    brand_name: string
    manufacturer?: { manufacturer_id: string; manufacturer_name: string }
}

export interface GraftFormData {
    brand_id: string
    graftSizes: GraftSize[]
}

interface Props {
    modelValue: boolean
    isEdit: boolean
    formData: GraftFormData
    brands: Brand[]
}

interface Emits {
    'update:modelValue': [value: boolean]
    'submit': [data: GraftFormData]
    'back': []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const localFormData = ref<GraftFormData>({
    brand_id: '',
    graftSizes: [{
        id: undefined,
        item_no: '',
        size: '',
        area: null,
        price: null,
        stock: 0
    }]
})

const focusedPriceField = ref<string | null>(null)

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        localFormData.value = {
            brand_id: props.formData.brand_id,
            graftSizes: props.formData.graftSizes.map(gs => ({ ...gs }))
        }
    }
}, { immediate: true })

watch(() => props.formData, (newFormData) => {
    localFormData.value = {
        brand_id: newFormData.brand_id,
        graftSizes: newFormData.graftSizes.map(gs => ({ ...gs }))
    }
}, { deep: true })

function addGraftSize() {
    localFormData.value.graftSizes.push({
        id: undefined,
        item_no: '',
        size: '',
        area: null,
        price: null,
        stock: 0
    })
}

function removeGraftSize(index: number) {
    localFormData.value.graftSizes.splice(index, 1)
    if (localFormData.value.graftSizes.length === 0) {
        addGraftSize()
    }
}

function parsePriceInput(value: string): number {
    if (!value || value.trim() === '') return 0
    const cleaned = value.replace(/,/g, '').trim()
    const parsed = parseFloat(cleaned)
    return isNaN(parsed) ? 0 : parsed
}

function formatPriceForDisplay(value: number | null | undefined): string {
    if (value === null || value === undefined || isNaN(value)) return ''
    return (Math.round(value * 100) / 100).toFixed(2)
}

function handleSubmit() {
    emit('submit', { ...localFormData.value })
}

function handleBack() {
    emit('back')
}
</script>
