<template>
    <BaseModal v-model="isOpen" title="Product Details" size="lg">
        <template v-if="product">
            <div class="p-6 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="flex-shrink-0">
                            <div
                                class="w-20 h-20 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-800/30 flex items-center justify-center border border-blue-200 dark:border-blue-700 shadow-sm">
                                <Package class="w-10 h-10 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ product.product_name }}
                            </h2>
                            <div class="mt-2">
                                <span :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    getStatusClasses(product.status)
                                ]">
                                    <span class="w-2 h-2 rounded-full bg-current mr-2"></span>
                                    {{ getStatusLabel(product.status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{
                                    product.product_type === 0
                                        ? 'Wound Supplies'
                                        : product.product_type === 1
                                            ? 'Devices'
                                            : 'Unknown Type'
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gray-100 dark:bg-gray-700/70 rounded-xl p-5 text-center shadow-sm">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ product.stock || 0 }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">Current Stock</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5 text-center">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                            {{ formatCurrency(product.price) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Unit Price</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 text-center">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Availability</div>
                    </div>
                </div>

                <div v-if="isLowStock(product.stock)"
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 flex items-center gap-3 text-yellow-800 dark:text-yellow-300">
                    <AlertCircle class="w-6 h-6 flex-shrink-0" />
                    <span>Low stock warning: only {{ product.stock }} units left</span>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                        <p v-if="product.description"
                            class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                            {{ product.description }}
                        </p>
                        <p v-else class="text-gray-500 dark:text-gray-400 italic">
                            No description provided.
                        </p>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Created At</div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                            {{ formatDate(product.created_at) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Last Updated</div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                            {{ formatDate(product.updated_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template #actions>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <button @click="handleClose"
                    class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                    Close
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { Package, AlertCircle } from 'lucide-vue-next'
import { formatCurrency } from '@/utils/currency'
import { useProductStatus } from '@/composables/products/useProductStatus'

export interface OtherProduct {
    other_product_id: string
    product_type: number
    product_name: string
    price: number
    stock: number
    status: number
    description?: string
    created_at: string
    updated_at: string
}

interface Props {
    modelValue: boolean
    product: OtherProduct | null
}

interface Emits {
    'update:modelValue': [value: boolean]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { getStatusClasses, getStatusIcon, getStatusLabel } = useProductStatus()

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const isLowStock = (stock: number) => stock < 10

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr)
    const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
    return `${formattedDate} [${formattedTime}]`
}

function handleClose() {
    emit('update:modelValue', false)
}
</script>
