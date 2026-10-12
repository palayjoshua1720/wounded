<template>
    <div
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div>
                <div class="flex items-center gap-3">
                    <!-- Logo or Icon -->
                    <div v-if="brand.logoUrl" class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100">
                        <img :src="brand.logoUrl" :alt="`${brand.brandName} logo`" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="p-2 bg-green-100 rounded-lg">
                        <Package class="w-5 h-5 text-green-600" />
                    </div>

                    <div class="flex flex-col">
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            {{ brand.brandName }}
                        </h3>
                        <span :class="[
                            'inline-flex px-2 py-1 text-xs rounded-full w-fit',
                            brand.brandStatus === 0
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                : brand.brandStatus === 1
                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                        ]">
                            {{ brand.brandStatus === 0 ? 'Active' : brand.brandStatus === 1 ? 'Inactive' :
                                'Archived' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions using ActionGroup component -->
            <ActionGroup :actions="brandActions" />

        </div>

        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <Factory class="w-4 h-4" />
                <span>{{ brand.manufacturerName || 'No Manufacturer' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <TriangleAlert class="w-4 h-4" />
                <span>{{ brand.mue || 'N/A' }} MUE</span>
            </div>


            <hr />
            <div class="flex items-center gap-2 flex-wrap"
                v-if="getActiveSizes(brand).length > 0 || getInactiveCount(brand) > 0">
                <strong>Available Sizes:</strong>
                <span v-for="sizeObj in getActiveSizes(brand).slice(0, 4)" :key="sizeObj.id || sizeObj.size"
                    class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 ml-1 mt-1">
                    {{ sizeObj.size }}
                </span>
                <span v-if="getActiveSizes(brand).length > 4"
                    class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 ml-1 mt-1">
                    +{{ getActiveSizes(brand).length - 4 }} more
                </span>
                <span v-if="getInactiveCount(brand) > 0" class="text-xs text-gray-500 ml-1">
                    (+{{ getInactiveCount(brand) }} inactive)
                </span>
            </div>
            <p v-else class="text-xs text-gray-500">No sizes available</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Package, Eye, SquarePen, Trash2, Archive, ArchiveRestore, CircleCheck, CircleX, Factory, TriangleAlert } from 'lucide-vue-next'
import ActionGroup from '@/components/ui/ActionGroup.vue'
import type { ActionConfig } from '@/components/ui/ActionGroup.vue'

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
    brand: Brand
    isStaff: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'view': [brand: Brand]
    'edit': [brand: Brand]
    'toggle': [id: number, status: number]
    'archive': [id: number]
    'delete': [brand: Brand]
}>()

// Computed actions array for ActionGroup component
const brandActions = computed<ActionConfig[]>(() => [
    {
        id: 'view',
        icon: Eye,
        title: 'View',
        hoverColor: 'green',
        onClick: () => emit('view', props.brand)
    },
    {
        id: 'edit',
        icon: SquarePen,
        title: 'Edit',
        hoverColor: 'blue',
        onClick: () => emit('edit', props.brand)
    },
    {
        id: 'toggle',
        icon: props.brand.brandStatus === 0 ? CircleX : CircleCheck,
        title: props.brand.brandStatus === 0 ? 'Deactivate' : 'Activate',
        hoverColor: props.brand.brandStatus === 0 ? 'red' : 'green',
        onClick: () => emit('toggle', props.brand.id, props.brand.brandStatus)
    },
    {
        id: 'archive',
        icon: props.brand.brandStatus === 2 ? ArchiveRestore : Archive,
        title: props.brand.brandStatus === 2 ? 'Unarchive' : 'Archive',
        hoverColor: props.brand.brandStatus === 2 ? 'green' : 'orange',
        onClick: () => emit('archive', props.brand.id)
    },
    {
        id: 'delete',
        icon: Trash2,
        title: 'Delete',
        hoverColor: 'red',
        visible: props.brand.brandStatus === 2 && !props.isStaff,
        onClick: () => emit('delete', props.brand)
    }
])

// Helper functions
function getActiveSizes(brand: Brand) {
    return brand.graftSizes.filter(s => s.graftStatus === 0)
}

function getInactiveCount(brand: Brand) {
    return brand.graftSizes.filter(s => s.graftStatus !== 0).length
}
</script>
