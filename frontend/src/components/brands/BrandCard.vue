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
                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit transition-all duration-200',
                            getStatusClasses(brand.brandStatus)
                        ]">
                            <component :is="getStatusIcon(brand.brandStatus)" class="w-2.5 h-2.5 mr-1" />
                            {{ getStatusLabel(brand.brandStatus) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions using ActionGroup component - visible only on sm+ screens -->
            <div class="hidden sm:block shrink-0">
                <ActionGroup :actions="brandActions" />
            </div>

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

        <!-- Mobile Card Actions - visible only on small screens -->
        <div
            class="sm:hidden pt-3 mt-3 border-t border-gray-200 dark:border-gray-700 -mx-6 -mb-6 px-6 pb-6 rounded-b-lg bg-gray-50 dark:bg-gray-700/30">
            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                <ActionGroup :actions="brandActions" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Package, Eye, SquarePen, Trash2, Archive, ArchiveRestore, CircleCheck, CircleX, Factory, TriangleAlert } from 'lucide-vue-next'
import ActionGroup from '@/components/ui/ActionGroup.vue'
import type { ActionConfig } from '@/components/ui/ActionGroup.vue'
import { useProductStatus } from '@/composables/products/useProductStatus'

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
    'unarchive': [id: number]
    'delete': [brand: Brand]
}>()

// Use the shared product status composable for consistent styling
const { getStatusClasses, getStatusIcon, getStatusLabel } = useProductStatus()

// Computed actions array for ActionGroup component
const brandActions = computed<ActionConfig[]>(() => {
    const isArchived = props.brand.brandStatus === 2

    const actions: ActionConfig[] = [
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
        }
    ]

    // For archived records: show Unarchive, View, Edit, Delete
    if (isArchived) {
        actions.push({
            id: 'unarchive',
            icon: ArchiveRestore,
            title: 'Unarchive',
            hoverColor: 'green',
            onClick: () => emit('unarchive', props.brand.id)
        })
        actions.push({
            id: 'delete',
            icon: Trash2,
            title: 'Delete',
            hoverColor: 'red',
            visible: !props.isStaff,
            onClick: () => emit('delete', props.brand)
        })
    } else {
        // For non-archived records: show Toggle and Archive
        actions.push({
            id: 'toggle',
            icon: props.brand.brandStatus === 0 ? CircleX : CircleCheck,
            title: props.brand.brandStatus === 0 ? 'Deactivate' : 'Activate',
            hoverColor: props.brand.brandStatus === 0 ? 'red' : 'green',
            onClick: () => emit('toggle', props.brand.id, props.brand.brandStatus)
        })
        actions.push({
            id: 'archive',
            icon: Archive,
            title: 'Archive',
            hoverColor: 'orange',
            onClick: () => emit('archive', props.brand.id)
        })
    }

    return actions
})

// Helper functions
function getActiveSizes(brand: Brand) {
    return brand.graftSizes.filter(s => s.graftStatus === 0)
}

function getInactiveCount(brand: Brand) {
    return brand.graftSizes.filter(s => s.graftStatus !== 0).length
}
</script>
