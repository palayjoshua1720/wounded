<template>
    <BaseModal v-model="isOpen" :title="isEdit ? 'Edit Other Product' : 'New Other Product'" size="xl">
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Product Type Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Product Type <span class="text-red-500">*</span>
                </label>
                <select v-model="localFormData.product_type" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option :value="null" disabled>Select a Product Type</option>
                    <option :value="0">Wound Supplies</option>
                    <option :value="1">Devices</option>
                </select>
            </div>

            <!-- Product Information -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <PencilLine class="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                        <input v-model="localFormData.product_name" type="text" required
                            placeholder="e.g., Sterile Gloves, Wound Dressing Kit"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <DollarSign class="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                        <input :value="focusedPriceField === 'other-product'
                            ? localFormData.price ?? ''
                            : formatPriceForDisplay(localFormData.price)
                            " @focus="focusedPriceField = 'other-product'" @blur="focusedPriceField = null"
                            @input="localFormData.price = parsePriceInput(($event.target as HTMLInputElement).value)"
                            type="text" inputmode="decimal" required min="0" placeholder="0.00"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Stock <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <Package class="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                        <input v-model.number="localFormData.stock" type="number" required min="0" placeholder="0"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description (optional)
                </label>
                <textarea v-model="localFormData.description" rows="4"
                    placeholder="Additional details about the product..."
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"></textarea>
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
                    class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    {{ isEdit ? 'Update Product' : 'Create Product' }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { DollarSign, Package, PencilLine, ArrowLeft } from 'lucide-vue-next'

export interface OtherProductFormData {
    product_id?: string
    product_type: number | null
    product_name: string
    price: number
    stock: number
    description: string
}

interface Props {
    modelValue: boolean
    isEdit: boolean
    formData: OtherProductFormData
}

interface Emits {
    'update:modelValue': [value: boolean]
    'submit': [data: OtherProductFormData]
    'back': []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const localFormData = ref<OtherProductFormData>({
    product_id: undefined,
    product_type: null,
    product_name: '',
    price: 0,
    stock: 0,
    description: ''
})

const focusedPriceField = ref<string | null>(null)

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        localFormData.value = { ...props.formData }
    }
}, { immediate: true })

watch(() => props.formData, (newFormData) => {
    localFormData.value = { ...newFormData }
}, { deep: true })

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
