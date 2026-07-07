<template>
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="flex-1">
                    <div class="relative">
                        <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                        <input v-model="searchTerm" type="text" placeholder="Search by name or description..."
                            class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative">
                        <select v-model="itemsPerPage"
                            class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Products Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Product Name
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Type
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Price
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Stock
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <TableLoader v-if="tableLoader" :colspan="7" />
                        <tr v-else v-for="product in paginatedOtherProducts" :key="product.other_product_id"
                            class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        <Package class="w-5 h-5" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ product.product_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{
                                    product.product_type === 0
                                        ? 'Wound Supplies'
                                        : product.product_type === 1
                                            ? 'Devices'
                                            : 'Unknown'
                                }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(product.price) }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ product.stock || 0 }} in stock
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span :class="[
                                    'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium',
                                    getStatusClasses(product.status)
                                ]">
                                    <component :is="getStatusIcon(product.status)" class="w-3 h-3 mr-1.5" />
                                    {{ getStatusLabel(product.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(product.created_at) }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <ActionGroup :actions="getProductActions(product)" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-if="products.length === 0 && !tableLoader" class="text-center py-12">
                <div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <Package class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No other products found</h3>
                <p class="text-gray-500 dark:text-gray-400">
                    Try adjusting your search or add a new product.
                </p>
            </div>

            <!-- Pagination -->
            <Pagination v-if="products.length > 0 && !tableLoader" :pagination="pagination"
                @update:page="$emit('page-change', $event)" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { Search, Eye, SquarePen, Trash2, Package, Archive, ArchiveRestore, CheckCircle2, XCircle, ChevronDown } from 'lucide-vue-next'
import { useProductStatus } from '@/composables/products/useProductStatus'
import { formatCurrency } from '@/utils/currency'
import ActionGroup, { type ActionConfig } from '@/components/ui/ActionGroup.vue'

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

const props = defineProps<{
    products: OtherProduct[]
    tableLoader: boolean
    searchTerm: string
    itemsPerPage: number
    currentPage: number
    totalResults: number
    isStaff: boolean
    pagination: {
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
}>()

const { getStatusClasses, getStatusIcon, getStatusLabel } = useProductStatus()

const filteredProducts = computed(() => {
    return props.products.filter(product => {
        const matchesSearch = product.product_name.toLowerCase().includes(props.searchTerm.toLowerCase()) ||
            (product.description?.toLowerCase().includes(props.searchTerm.toLowerCase()) ?? false)
        return matchesSearch
    })
})

const paginatedOtherProducts = computed(() => {
    return filteredProducts.value
})

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr)
    const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
    return `${formattedDate} [${formattedTime}]`
}

// Emit events
const emit = defineEmits<{
    (e: 'view', product: OtherProduct): void
    (e: 'edit', product: OtherProduct): void
    (e: 'toggle-status', product: OtherProduct): void
    (e: 'archive', product: OtherProduct): void
    (e: 'delete', product: OtherProduct): void
    (e: 'page-change', page: number): void
}>()

// Generate actions for a product row
function getProductActions(product: OtherProduct): ActionConfig[] {
    const actions: ActionConfig[] = []

    // View action
    actions.push({
        id: 'view',
        icon: Eye,
        title: 'View',
        hoverColor: 'blue',
        onClick: () => emit('view', product)
    })

    // Edit action
    actions.push({
        id: 'edit',
        icon: SquarePen,
        title: 'Edit',
        hoverColor: 'indigo',
        onClick: () => emit('edit', product)
    })

    // Toggle status action (only show if not archived)
    if (product.status !== 2) {
        const isActive = product.status === 0
        actions.push({
            id: 'toggle-status',
            icon: isActive ? XCircle : CheckCircle2,
            title: isActive ? 'Deactivate' : 'Activate',
            hoverColor: isActive ? 'red' : 'green',
            onClick: () => emit('toggle-status', product)
        })
    }

    // Archive/Unarchive action
    const isArchived = product.status === 2
    actions.push({
        id: 'archive',
        icon: isArchived ? ArchiveRestore : Archive,
        title: isArchived ? 'Unarchive Product' : 'Archive Product',
        hoverColor: isArchived ? 'green' : 'orange',
        onClick: () => emit('archive', product)
    })

    // Delete action (only show if archived and user is not staff)
    if (product.status === 2 && !props.isStaff) {
        actions.push({
            id: 'delete',
            icon: Trash2,
            title: 'Delete Archived Product',
            hoverColor: 'red',
            onClick: () => emit('delete', product)
        })
    }

    return actions
}
</script>
