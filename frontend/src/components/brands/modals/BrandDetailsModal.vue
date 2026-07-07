<template>
    <BaseModal v-model="isOpen" title="Brand Details" max-width="800px">
        <template v-if="brand">
            <div class="p-6 space-y-8">
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <div v-if="brand.logoUrl"
                                class="w-20 h-20 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                <img :src="brand.logoUrl" :alt="`${brand.brandName} logo`"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div v-else
                                class="w-20 h-20 rounded-xl bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 flex items-center justify-center border border-green-200 dark:border-green-700">
                                <PackageOpen class="w-10 h-10 text-green-600 dark:text-green-400" />
                            </div>
                        </div>

                        <!-- Title & Status -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ brand.brandName }}
                            </h2>
                            <div class="mt-2">
                                <span :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    brand.brandStatus === 0 ? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400' :
                                        brand.brandStatus === 1 ? 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400' :
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400'
                                ]">
                                    <span class="w-2 h-2 rounded-full bg-current mr-2"></span>
                                    {{ brand.brandStatus === 0 ? 'Active' : brand.brandStatus === 1 ?
                                        'Inactive' : 'Archived' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info Badges -->
                    <div class="flex flex-wrap gap-3">
                        <div
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <Factory class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mr-2" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ brand.manufacturerName || 'No Manufacturer' }}
                            </span>
                        </div>
                        <div
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <Ruler class="w-5 h-5 text-purple-500 dark:text-purple-400 mr-2" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ getActiveSizes(brand).length }} Sizes
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Key Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-100 dark:bg-gray-700/70 rounded-xl p-5 text-center shadow-sm">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ brand.graftSizes.length
                        }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">Total Sizes</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 text-center">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{
                            getActiveSizes(brand).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Active Sizes</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-5 text-center">
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ brand.mue || 0
                        }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">MUE Limit</div>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-5 text-center">
                        <div class="text-2xl font-bold text-orange-700 dark:text-orange-300">
                            {{ brand.productType || 'Graft' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Product Type</div>
                    </div>
                </div>
                <!-- Description -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                        <p v-if="brand.description"
                            class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                            {{ brand.description }}
                        </p>
                        <p v-else class="text-gray-500 dark:text-gray-400 italic">
                            No description provided.
                        </p>
                    </div>
                </div>

                <!-- Graft Sizes -->
                <div>
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                        Graft Sizes
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ brand.graftSizes.length }} total
                            <span v-if="getActiveSizes(brand).length !== brand.graftSizes.length"
                                class="ml-2 text-gray-400">
                                ({{ getActiveSizes(brand).length }} active)
                            </span>
                        </span>
                    </h3>

                    <div v-if="brand.graftSizes.length === 0"
                        class="text-center py-12 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700">
                        <Package class="w-14 h-14 mx-auto text-gray-400 mb-4 opacity-80" />
                        <h4 class="text-base font-medium text-gray-700 dark:text-gray-300 mb-1">No graft sizes
                            configured</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Add sizes when creating or editing this
                            brand.</p>
                    </div>

                    <div v-else
                        class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                        <!-- Table Header (sticky) -->
                        <div
                            class="bg-gray-100 dark:bg-gray-700/90 px-6 py-3.5 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
                            <div
                                class="grid grid-cols-12 gap-4 text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                <div class="col-span-2">Item No</div>
                                <div class="col-span-4">Size & Area</div>
                                <div class="col-span-2 text-center">Price</div>
                                <div class="col-span-2 text-center">Stock</div>
                                <div class="col-span-2 text-center">Status</div>
                            </div>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/60">
                            <div v-for="(size, index) in brand.graftSizes" :key="size.id || size.size" :class="[
                                'px-6 py-5 transition-colors duration-150',
                                index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50/70 dark:bg-gray-800/30'
                            ]">
                                <div class="grid grid-cols-12 gap-4 items-center text-sm">
                                    <!-- Item Number -->
                                    <div class="col-span-2 font-medium text-gray-800 dark:text-gray-100">
                                        {{ size.item_no || '—' }}
                                    </div>

                                    <!-- Size & Area (merged - styled like reference) -->
                                    <div class="col-span-4">
                                        <div class="space-y-1">
                                            <div
                                                class="text-base font-medium text-gray-900 dark:text-gray-100 leading-tight">
                                                {{ size.size }}
                                            </div>
                                            <div v-if="size.area" class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ formatNumber(size.area) }} cm²
                                            </div>
                                            <div v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                                                — cm²
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-span-2 text-center tabular-nums font-medium">
                                        <span v-if="size.price" class="text-emerald-700 dark:text-emerald-400">
                                            {{ formatCurrency(size.price) }}
                                        </span>
                                    </div>

                                    <!-- Stock -->
                                    <div class="col-span-2 text-center font-medium tabular-nums">
                                        {{ size.stock || 0 }}
                                        <span v-if="size.stock === 0"
                                            class="text-red-500 dark:text-red-400 text-xs ml-1.5">(out)</span>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-span-2 text-center">
                                        <span :class="[
                                            'inline-flex items-center px-3 py-1 text-xs font-medium rounded-full',
                                            size.graftStatus === 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' :
                                                size.graftStatus === 1 ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' :
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'
                                        ]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></span>
                                            {{ size.graftStatus === 0 ? 'Active' : size.graftStatus === 1 ?
                                                'Inactive' : 'Archived' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #actions>
            <div class="px-6 py-4 flex justify-end">
                <button type="button" @click="handleClose"
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
import { Package, Factory, Ruler, PackageOpen } from 'lucide-vue-next'
import { formatCurrency, formatNumber } from '@/utils/currency'

// Types
export interface GraftSize {
    id?: number
    item_no?: string | null
    size: string
    area: number | null
    price: number | null
    graftStatus?: number
    stock?: number
}

export interface Brand {
    id: number
    brandName: string
    brandStatus: number
    manufacturerId?: number
    manufacturerName?: string
    mue?: number | null
    logoUrl?: string | null
    description?: string
    productType?: string
    graftSizes: GraftSize[]
    createdAt: string
    updatedAt: string
}

interface Props {
    modelValue: boolean
    brand: Brand | null
}

interface Emits {
    'update:modelValue': [value: boolean]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Computed
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

// Methods
function getActiveSizes(brand: Brand) {
    return brand.graftSizes.filter(s => s.graftStatus === 0)
}

function handleClose() {
    emit('update:modelValue', false)
}
</script>
