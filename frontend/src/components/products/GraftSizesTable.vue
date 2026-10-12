<template>
    <div class="space-y-6">
        <!-- Filters Card for grafts -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="flex-1">
                    <div class="relative">
                        <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                        <input v-model="searchTerm" type="text" placeholder="Search by brand or size..."
                            class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative">
                        <Filter class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
                        <select v-model="statusFilter"
                            class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="archived">Archived</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                    <div class="relative">
                        <select v-model="itemsPerPage"
                            class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
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

        <!-- Graft Sizes Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Manufacturer - Brand
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Size
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
                        <TableLoader v-if="tableLoader" :colspan="6" />
                        <tr v-else v-for="graft in paginatedGrafts" :key="graft.graft_size_id"
                            class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 font-medium text-sm">
                                        <PencilRuler class="w-5 h-5" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ graft.manufacturer?.manufacturer_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ graft.brand?.brand_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ graft.size }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ graft.area ? graft.area + 'cm²' : 'No data found' }}
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ graft.stock || 0 }} in stock
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span :class="[
                                    'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200',
                                    getStatusClasses(graft.graft_status)
                                ]">
                                    <component :is="getStatusIcon(graft.graft_status)" class="w-3 h-3 mr-1.5" />
                                    {{ getStatusLabel(graft.graft_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(graft.created_at) }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <ActionGroup :actions="getGraftActions(graft)" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredGraftRequest.length === 0 && !tableLoader" class="text-center py-12">
                <div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <PencilRuler class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No graft sizes found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Try adjusting your search or filter to find what you're looking for.
                </p>
            </div>

            <!-- Pagination -->
            <Pagination v-if="filteredGraftRequest.length > 0 && !tableLoader" :pagination="pagination"
                @update:page="$emit('page-change', $event)" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { Search, Eye, SquarePen, Trash2, PencilRuler, Archive, ArchiveRestore, CheckCircle2, XCircle, Filter, ChevronDown } from 'lucide-vue-next'
import { useProductStatus } from '@/composables/products/useProductStatus'
import ActionGroup, { type ActionConfig } from '@/components/ui/ActionGroup.vue'

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

export interface Brand {
    brand_id: string
    brand_name: string
    manufacturer?: { manufacturer_id: string; manufacturer_name: string }
}

const props = defineProps<{
    grafts: GraftRequest[]
    tableLoader: boolean
    searchTerm: string
    statusFilter: string
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

const filteredGraftRequest = computed(() => {
    return props.grafts.filter(graft => {
        const brandName = graft.brand?.brand_name || ''
        const manufacturerName = graft.manufacturer?.manufacturer_name || ''
        const matchesSearch = brandName.toLowerCase().includes(props.searchTerm.toLowerCase()) ||
            manufacturerName.toLowerCase().includes(props.searchTerm.toLowerCase()) ||
            graft.size.toLowerCase().includes(props.searchTerm.toLowerCase()) ||
            (graft.area?.toString().toLowerCase().includes(props.searchTerm.toLowerCase()) ?? false) ||
            (graft.price?.toString().toLowerCase().includes(props.searchTerm.toLowerCase()) ?? false) ||
            graft.stock.toString().toLowerCase().includes(props.searchTerm.toLowerCase())
        const matchesStatus = props.statusFilter === 'all' ||
            (props.statusFilter === 'active' && graft.graft_status === 0) ||
            (props.statusFilter === 'inactive' && graft.graft_status === 1) ||
            (props.statusFilter === 'archived' && graft.graft_status === 2)
        return matchesSearch && matchesStatus
    })
})

const paginatedGrafts = computed(() => {
    return filteredGraftRequest.value
})

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr)
    const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
    return `${formattedDate} [${formattedTime}]`
}

// Generate actions for a graft row
const emit = defineEmits<{
    (e: 'view', graft: GraftRequest): void
    (e: 'edit', graft: GraftRequest): void
    (e: 'toggle-status', graft: GraftRequest): void
    (e: 'archive', graft: GraftRequest): void
    (e: 'delete', graft: GraftRequest): void
    (e: 'page-change', page: number): void
}>()

function getGraftActions(graft: GraftRequest): ActionConfig[] {
    const actions: ActionConfig[] = []

    // View action
    actions.push({
        id: 'view',
        icon: Eye,
        title: 'View Details',
        hoverColor: 'blue',
        onClick: () => emit('view', graft)
    })

    // Edit action
    actions.push({
        id: 'edit',
        icon: SquarePen,
        title: 'Edit Graft',
        hoverColor: 'indigo',
        onClick: () => emit('edit', graft)
    })

    // Toggle status action (only show if not archived)
    if (graft.graft_status !== 2) {
        const isActive = graft.graft_status === 0
        actions.push({
            id: 'toggle-status',
            icon: isActive ? XCircle : CheckCircle2,
            title: isActive ? 'Deactivate' : 'Activate',
            hoverColor: isActive ? 'red' : 'green',
            onClick: () => emit('toggle-status', graft)
        })
    }

    // Archive/Unarchive action
    const isArchived = graft.graft_status === 2
    actions.push({
        id: 'archive',
        icon: isArchived ? ArchiveRestore : Archive,
        title: isArchived ? 'Unarchive Graft' : 'Archive Graft',
        hoverColor: isArchived ? 'green' : 'orange',
        onClick: () => emit('archive', graft)
    })

    // Delete action (only show if archived and user is not staff)
    if (graft.graft_status === 2 && !props.isStaff) {
        actions.push({
            id: 'delete',
            icon: Trash2,
            title: 'Delete Archived Graft',
            hoverColor: 'red',
            onClick: () => emit('delete', graft)
        })
    }

    return actions
}
</script>
