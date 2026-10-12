<template>
    <BaseModal v-model="isOpen" title="Graft Size Details" size="lg">
        <template v-if="graft">
            <div class="p-4 sm:p-6 space-y-5 sm:space-y-6">
                <!-- Mobile Card Layout (shown on small screens) -->
                <div class="md:hidden space-y-4">
                    <!-- Compact Header Card -->
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="w-16 h-16 rounded-xl bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 flex items-center justify-center border border-green-200 dark:border-green-700 shadow-sm flex-shrink-0">
                                <PencilRuler class="w-8 h-8 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight truncate">
                                    {{ graft.size || 'Unnamed Size' }}
                                </h2>
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1.5',
                                    getStatusClasses(graft.graft_status)
                                ]">
                                    {{ getStatusLabel(graft.graft_status) }}
                                </span>
                            </div>
                        </div>
                        <!-- Quick Info Grid -->
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <Factory class="w-4 h-4 text-indigo-500 dark:text-indigo-400 flex-shrink-0" />
                                <span class="text-gray-500 dark:text-gray-400">Manufacturer:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 truncate">
                                    {{ graft.manufacturer?.manufacturer_name || 'N/A' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <Package class="w-4 h-4 text-purple-500 dark:text-purple-400 flex-shrink-0" />
                                <span class="text-gray-500 dark:text-gray-400">Brand:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 truncate">
                                    {{ graft.brand?.brand_name || 'N/A' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <Tag class="w-4 h-4 text-teal-500 dark:text-teal-400 flex-shrink-0" />
                                <span class="text-gray-500 dark:text-gray-400">Item No:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                    {{ graft.item_no || 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid - 3 Column Cards -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-gray-100 dark:bg-gray-700/70 rounded-xl py-4 px-2 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ graft.stock || 0 }}
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide">
                                Stock</div>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl py-4 px-2 text-center">
                            <div class="text-xl font-bold text-emerald-700 dark:text-emerald-300 tabular-nums">
                                {{ formatCurrency(graft.price) }}
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide">
                                Price</div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl py-4 px-2 text-center">
                            <div class="text-xl font-bold text-blue-700 dark:text-blue-300">
                                {{ graft.area ? formatNumber(graft.area) : '—' }}
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide">
                                Area</div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div v-if="isLowStock(graft.stock)"
                        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex items-center gap-3 text-amber-800 dark:text-amber-300">
                        <AlertCircle class="w-5 h-5 flex-shrink-0" />
                        <span class="text-sm">Low stock: {{ graft.stock }} units left</span>
                    </div>

                    <!-- Notes Section -->
                    <div v-if="graft.notes || graft.description">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2.5">Notes</h3>
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                                {{ graft.notes || graft.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl text-center border border-gray-200 dark:border-gray-700">
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">
                                Created</div>
                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ formatDate(graft.created_at) }}
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl text-center border border-gray-200 dark:border-gray-700">
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">
                                Updated</div>
                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ formatDate(graft.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Layout (shown on medium+ screens) -->
                <div class="hidden md:block space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-20 h-20 rounded-xl bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 flex items-center justify-center border border-green-200 dark:border-green-700 shadow-sm">
                                    <PencilRuler class="w-10 h-10 text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                    {{ graft.size || 'Unnamed Size' }}
                                </h2>
                                <div class="mt-2">
                                    <span :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                        getStatusClasses(graft.graft_status)
                                    ]">
                                        <span class="w-2 h-2 rounded-full bg-current mr-2"></span>
                                        {{ getStatusLabel(graft.graft_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <Factory class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mr-2" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ graft.manufacturer?.manufacturer_name || 'No Records Found' }}
                                </span>
                            </div>
                            <div
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <Package class="w-5 h-5 text-purple-500 dark:text-purple-400 mr-2" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ graft.brand?.brand_name || 'No Records Found' }}
                                </span>
                            </div>
                            <div
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <Tag class="w-5 h-5 text-teal-500 dark:text-teal-400 mr-2" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ graft.item_no || 'No Records Found' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-100 dark:bg-gray-700/70 rounded-xl p-5 text-center shadow-sm">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ graft.stock || 0 }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">Current Stock</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5 text-center">
                            <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                                {{ formatCurrency(graft.price) }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Unit Price</div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 text-center">
                            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ graft.area ? formatNumber(graft.area) + ' cm²' : '—' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Area</div>
                        </div>
                    </div>

                    <div v-if="isLowStock(graft.stock)"
                        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 flex items-center gap-3 text-yellow-800 dark:text-yellow-300">
                        <AlertCircle class="w-6 h-6 flex-shrink-0" />
                        <span>Low stock alert: only {{ graft.stock }} units remaining</span>
                    </div>

                    <div v-if="graft.notes || graft.description">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Notes</h3>
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                                {{ graft.notes || graft.description }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Created At</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                {{ formatDate(graft.created_at) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Last Updated</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                {{ formatDate(graft.updated_at) }}
                            </div>
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
import { PencilRuler, Factory, Package, Tag, AlertCircle } from 'lucide-vue-next'
import { formatCurrency, formatNumber } from '@/utils/currency'
import { useProductStatus } from '@/composables/products/useProductStatus'

export interface GraftRequest {
    graft_size_id: string
    brand_id: string
    item_no: string
    size: string
    area?: number | null
    price?: number | null
    stock: number
    graft_status: number
    created_at: string
    updated_at: string
    notes?: string
    description?: string
    brand?: {
        brand_id: string
        brand_name: string
    }
    manufacturer?: {
        manufacturer_id: string
        manufacturer_name: string
    }
}

interface Props {
    modelValue: boolean
    graft: GraftRequest | null
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
