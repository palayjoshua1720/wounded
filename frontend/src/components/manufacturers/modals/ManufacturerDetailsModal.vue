<template>
    <BaseModal v-model="isOpen" title="Manufacturer Details">
        <template v-if="manufacturer">
            <div class="space-y-4 sm:space-y-6">
                <!-- Header -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Logo -->
                    <div
                        class="w-12 h-12 sm:w-16 sm:h-16 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center shrink-0">
                        <img v-if="manufacturer.logoUrl" :src="manufacturer.logoUrl"
                            :alt="`${manufacturer.manufacturerName} logo`" class="w-full h-full object-cover"
                            @error="handleLogoError" />
                        <Factory class="w-6 h-6 sm:w-8 sm:h-8 text-green-500"
                            :class="{ 'hidden': manufacturer.logoUrl }" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white truncate">
                            {{ manufacturer.manufacturerName }}
                        </p>
                        <span :class="[
                            'inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium',
                            getStatusClasses(manufacturer.manufacturerStatus)
                        ]">
                            <span class="w-2 h-2 rounded-full bg-current mr-1.5 sm:mr-2"></span>
                            {{ getStatusLabel(manufacturer.manufacturerStatus) }}
                        </span>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-3 sm:space-y-4">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white">Contact Information</h3>
                    <div
                        class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Left Column -->
                            <div class="space-y-3 sm:space-y-4">
                                <div class="flex items-start sm:items-center space-x-2 sm:space-x-3">
                                    <UserRound class="w-4 h-4 sm:w-5 sm:h-5 text-orange-500 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Contact Person
                                        </p>
                                        <p class="text-sm sm:text-base text-gray-900 dark:text-white truncate">{{
                                            manufacturer.contactPerson }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start sm:items-center space-x-2 sm:space-x-3">
                                    <Phone class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Contact Number
                                        </p>
                                        <p class="text-sm sm:text-base text-gray-900 dark:text-white break-all">{{
                                            manufacturer.contactNumber }}</p>
                                    </div>
                                </div>

                                <div v-if="manufacturer.address"
                                    class="flex items-start sm:items-center space-x-2 sm:space-x-3">
                                    <MapPin class="w-4 h-4 sm:w-5 sm:h-5 text-red-500 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Address</p>
                                        <p class="text-sm sm:text-base text-gray-900 dark:text-white break-words">{{
                                            manufacturer.address }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-3 sm:space-y-4">
                                <div class="flex items-start sm:items-center space-x-2 sm:space-x-3">
                                    <Mail class="w-4 h-4 sm:w-5 sm:h-5 text-purple-500 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Primary Email</p>
                                        <p
                                            class="text-sm sm:text-base text-gray-900 dark:text-white break-all text-xs sm:text-sm">
                                            {{ manufacturer.primaryEmail }}</p>
                                    </div>
                                </div>

                                <div v-if="manufacturer.secondaryEmail"
                                    class="flex items-start sm:items-center space-x-2 sm:space-x-3">
                                    <Mail class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-500 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Secondary Email
                                        </p>
                                        <p
                                            class="text-sm sm:text-base text-gray-900 dark:text-white break-all text-xs sm:text-sm">
                                            {{ manufacturer.secondaryEmail }}</p>
                                    </div>
                                </div>

                                <div v-if="manufacturer.website"
                                    class="flex items-start sm:items-center space-x-2 sm:space-x-3">
                                    <Globe class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">Website</p>
                                        <a :href="manufacturer.website" target="_blank"
                                            class="text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300 text-xs sm:text-sm break-all">
                                            {{ manufacturer.website }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specialized Emails Section (only show if at least one email exists) -->
                <div v-if="manufacturer.orderEmail || manufacturer.eligibilityEmail" class="space-y-3 sm:space-y-4">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white">Other Emails</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <!-- Order Email Card -->
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-4">
                            <div class="flex items-start space-x-2 sm:space-x-3">
                                <Mail class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500 mt-0.5 shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                        Order Email
                                    </h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 hidden sm:block">For order
                                        processing &
                                        notifications</p>
                                    <div v-if="manufacturer.orderEmail">
                                        <p
                                            class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white break-all">
                                            {{
                                                manufacturer.orderEmail }}</p>
                                    </div>
                                    <div v-else>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 italic">No Records
                                            Found</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Eligibility Email Card -->
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-4">
                            <div class="flex items-start space-x-2 sm:space-x-3">
                                <Mail class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-500 mt-0.5 shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                        Eligibility
                                        Email</h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 hidden sm:block">For
                                        eligibility &
                                        compliance
                                    </p>
                                    <div v-if="manufacturer.eligibilityEmail">
                                        <p
                                            class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white break-all">
                                            {{
                                                manufacturer.eligibilityEmail }}</p>
                                    </div>
                                    <div v-else>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 italic">No Records
                                            Found</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files Section (only show if at least one file exists) -->
                <div v-if="hasFiles" class="space-y-3 sm:space-y-4">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white">Files & Documents</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <!-- IVR Form Card -->
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-4">
                            <div class="flex items-center justify-between mb-2 sm:mb-3">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div
                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                        <FileText :class="getFileTypeIcon(manufacturer.ivrFileExtension || 'pdf')"
                                            class="w-4 h-4 sm:w-5 sm:h-5" />
                                    </div>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">IVR
                                            Form</h4>
                                        <div v-if="manufacturer.ivrFilename"
                                            class="flex items-center gap-1 sm:gap-2 mt-0.5 sm:mt-1">
                                            <span :class="[
                                                'inline-flex px-1.5 sm:px-2 py-0.5 text-xs rounded-full font-medium',
                                                (manufacturer.ivrFileExtension || 'pdf') === 'pdf'
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                                            ]">
                                                {{ (manufacturer.ivrFileExtension || 'pdf').toUpperCase() }}
                                            </span>
                                        </div>
                                        <div v-else class="mt-0.5 sm:mt-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400 italic">N/A</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button v-if="manufacturer.ivrFilename" @click="handleDownload('ivr')"
                                class="w-full mt-2 px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                                Download
                            </button>
                            <div v-else
                                class="w-full mt-2 px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                No File
                            </div>
                        </div>

                        <!-- Order Form Card -->
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-4">
                            <div class="flex items-center justify-between mb-2 sm:mb-3">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div
                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                        <FileText :class="getFileTypeIcon(manufacturer.orderFileExtension || 'pdf')"
                                            class="w-4 h-4 sm:w-5 sm:h-5" />
                                    </div>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">Order
                                            Form</h4>
                                        <div v-if="manufacturer.orderFilename"
                                            class="flex items-center gap-1 sm:gap-2 mt-0.5 sm:mt-1">
                                            <span :class="[
                                                'inline-flex px-1.5 sm:px-2 py-0.5 text-xs rounded-full font-medium',
                                                (manufacturer.orderFileExtension || 'pdf') === 'pdf'
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                                            ]">
                                                {{ (manufacturer.orderFileExtension || 'pdf').toUpperCase() }}
                                            </span>
                                        </div>
                                        <div v-else class="mt-0.5 sm:mt-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400 italic">N/A</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button v-if="manufacturer.orderFilename" @click="handleDownload('order')"
                                class="w-full mt-2 px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                                Download
                            </button>
                            <div v-else
                                class="w-full mt-2 px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                No File
                            </div>
                        </div>

                        <!-- Onboarding File Card -->
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-4">
                            <div class="flex items-center justify-between mb-2 sm:mb-3">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div
                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                        <FileText
                                            :class="getFileTypeIcon(manufacturer.onboardingFileExtension || 'pdf')"
                                            class="w-4 h-4 sm:w-5 sm:h-5" />
                                    </div>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">
                                            Onboarding</h4>
                                        <div v-if="manufacturer.onboardingFilename"
                                            class="flex items-center gap-1 sm:gap-2 mt-0.5 sm:mt-1">
                                            <span :class="[
                                                'inline-flex px-1.5 sm:px-2 py-0.5 text-xs rounded-full font-medium',
                                                (manufacturer.onboardingFileExtension || 'pdf') === 'pdf'
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                                            ]">
                                                {{ (manufacturer.onboardingFileExtension || 'pdf').toUpperCase() }}
                                            </span>
                                        </div>
                                        <div v-else class="mt-0.5 sm:mt-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400 italic">N/A</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button v-if="manufacturer.onboardingFilename" @click="handleDownload('onboarding')"
                                class="w-full mt-2 px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                                Download
                            </button>
                            <div v-else
                                class="w-full mt-2 px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                No File
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Brands Information -->
                <div v-if="manufacturer.brands && manufacturer.brands.length > 0"
                    class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-5">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white mb-3 sm:mb-4">Associated
                        Brands</h3>

                    <div class="space-y-2 sm:space-y-3 max-h-40 sm:max-h-60 overflow-y-auto pr-1 sm:pr-2 brands-scroll">
                        <div v-for="brand in manufacturer.brands" :key="brand.id"
                            class="p-2 sm:p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex justify-between items-center">
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate">{{
                                        brand.brandName }}</h4>
                                </div>
                                <span class="px-1.5 sm:px-2 py-0.5 text-xs font-medium rounded-full shrink-0 ml-2"
                                    :class="[
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

                    <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            Total: {{ manufacturer.brands.length }} brand{{ manufacturer.brands.length !== 1 ? 's' : ''
                            }}
                        </p>
                    </div>
                </div>

                <div v-else
                    class="text-center py-6 sm:py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No brands associated</p>
                </div>
            </div>
        </template>

        <template #actions>
            <div class="p-3 sm:p-4 flex items-center justify-center sm:justify-end">
                <button type="button" @click="handleClose"
                    class="px-4 py-2 w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Close
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { Factory, Mail, Phone, UserRound, MapPin, Globe, FileText } from 'lucide-vue-next'
import { useProductStatus } from '@/composables/products/useProductStatus'

// Use the shared product status composable for consistent styling
const { getStatusClasses, getStatusLabel } = useProductStatus()

// Types
export interface Brand {
    id: number
    brandName: string
    brandStatus: number
}

export interface ManufacturerDetails {
    id: number
    manufacturerName: string
    primaryEmail: string
    orderEmail: string
    eligibilityEmail: string
    secondaryEmail?: string
    website?: string
    address?: string
    contactPerson: string
    contactNumber: string
    ivrFilename?: string
    ivrFileExtension?: string
    orderFilename?: string
    orderFileExtension?: string
    onboardingFilename?: string
    onboardingFileExtension?: string
    manufacturerStatus: number
    logoUrl?: string | null
    brands?: Brand[]
}

interface Props {
    modelValue: boolean
    manufacturer: ManufacturerDetails | null
}

interface Emits {
    'update:modelValue': [value: boolean]
    'close': []
    'download': [type: 'ivr' | 'order' | 'onboarding']
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Computed
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const hasFiles = computed(() => {
    const m = props.manufacturer
    if (!m) return false
    return m.ivrFilename || m.orderFilename || m.onboardingFilename
})

// Methods
function handleLogoError(event: Event) {
    const target = event.target as HTMLImageElement
    target.style.display = 'none'
    target.nextElementSibling?.classList.remove('hidden')
}

function handleClose() {
    emit('update:modelValue', false)
    emit('close')
}

function handleDownload(type: 'ivr' | 'order' | 'onboarding') {
    emit('download', type)
}

function getFileExtension(filename: string | undefined): string {
    if (!filename) return ''
    return filename.split('.').pop()?.toLowerCase() || ''
}

function getFileTypeDisplay(extension: string): string {
    const typeMap: Record<string, string> = {
        'pdf': 'PDF Document',
        'doc': 'Word Document',
        'docx': 'Word Document'
    }
    return typeMap[extension] || 'Document'
}

function getFileTypeIcon(extension: string): string {
    if (!extension) return 'text-gray-400'

    const iconMap: Record<string, string> = {
        'pdf': 'text-red-500',
        'doc': 'text-blue-500',
        'docx': 'text-blue-500'
    }
    return iconMap[extension] || 'text-gray-500'
}
</script>

<style scoped>
.brands-scroll {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.brands-scroll::-webkit-scrollbar {
    width: 0px;
    transition: width 0.2s ease;
}

.brands-scroll:hover {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.6) transparent;
}

.brands-scroll:hover::-webkit-scrollbar {
    width: 6px;
}

.brands-scroll:hover::-webkit-scrollbar-track {
    background: transparent;
}

.brands-scroll:hover::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.6);
    border-radius: 999px;
    border: 2px solid transparent;
    background-clip: content-box;
}

.brands-scroll:hover::-webkit-scrollbar-thumb:hover {
    background-color: rgba(107, 114, 128, 0.8);
}
</style>
