<template>
    <BaseModal v-model="isOpen" :title="isCreate ? 'Add New Manufacturer' : 'Edit Manufacturer'"
        :class="{ 'pointer-events-none': isCropperOpen, 'opacity-0': isCropperOpen }">
        <form @submit.prevent="handleSubmitForm" class="flex flex-col h-full">
            <div class="flex-1 overflow-y-auto space-y-6 px-6">
                <!-- Manufacturer Information -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <Building2 class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Manufacturer Information</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.manufacturerName" type="text" required
                                    placeholder="Manufacturer Name"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">
                                Status<span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <select v-model="localFormData.manufacturerStatus" required
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option :value="0">Active</option>
                                    <option :value="1">Inactive</option>
                                    <option :value="2">Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Website
                                (Optional)</label>
                            <div class="relative">
                                <Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.website" type="text" placeholder="https://"
                                    @blur="fixWebsite"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Address
                                (Optional)</label>
                            <div class="relative">
                                <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.address" type="text" placeholder="Manufacturer Address"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo Upload -->
                <div>
                    <ImageUploadCrop v-model="localLogoFile" v-model:model-preview-url="localFormData.logoUrl"
                        v-model:model-remove-flag="localRemoveLogoFlag" label="Manufacturer Logo" :show-label="true"
                        :is-optional="true" existing-label="Current logo:" @cropper-opening="handleCropperOpening"
                        @cropper-closing="handleCropperClosing" />
                </div>

                <!-- Contact Information -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <Contact class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Contact Information</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Person
                                <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <UserRound class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.contactPerson" type="text" required
                                    placeholder="Contact Person"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Number
                                <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Phone class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.contactNumber" type="text" required
                                    placeholder="Phone/Tel" @input="handleContactInput"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <!-- Primary Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Primary Email
                                <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.primaryEmail" type="email" required
                                    placeholder="example@manufacturer.com"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <!-- Secondary Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Secondary Email
                                (Optional)</label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.secondaryEmail" type="email"
                                    placeholder="example@manufacturer.com"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <!-- Order Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Email for Orders
                                <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.orderEmail" type="email" required
                                    placeholder="example@manufacturer.com"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>

                        <!-- Eligibility Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Email for
                                Eligibility <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input v-model="localFormData.eligibilityEmail" type="email" required
                                    placeholder="example@manufacturer.com"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Upload Sections -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <FilePenLine class="w-3.5 h-3.5 text-green-600" />
                        <span
                            class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Document
                            Uploads</span>
                    </div>
                    <div class="space-y-4">

                        <!-- IVR -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                IVR Form <span class="text-red-500">*</span>
                            </label>
                            <FileUploadSection v-model:selectedFile="localIvrFile" v-model:previewUrl="ivrPreviewUrl"
                                v-model:removeFlag="localRemoveIvrFlag" :existingFilename="localFormData.ivrFilename"
                                :existingFileExtension="localFormData.ivrFileExtension" label="IVR Form"
                                accept=".pdf,.doc,.docx" @remove-existing="handleRemoveIvr"
                                @preview="handlePreviewIvr" />
                        </div>

                        <!-- Order Form -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                Order Form <span class="text-red-500">*</span>
                            </label>
                            <FileUploadSection v-model:selectedFile="localOrderFile"
                                v-model:previewUrl="orderPreviewUrl" v-model:removeFlag="localRemoveOrderFlag"
                                :existingFilename="localFormData.orderFilename"
                                :existingFileExtension="localFormData.orderFileExtension" label="Order Form"
                                accept=".pdf,.doc,.docx" @remove-existing="handleRemoveOrder"
                                @preview="handlePreviewOrder" />
                        </div>

                        <!-- Onboarding File -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                Onboarding File <span class="text-red-500">*</span>
                            </label>
                            <FileUploadSection v-model:selectedFile="localOnboardingFile"
                                v-model:previewUrl="onboardingPreviewUrl" v-model:removeFlag="localRemoveOnboardingFlag"
                                :existingFilename="localFormData.onboardingFilename"
                                :existingFileExtension="localFormData.onboardingFileExtension" label="Onboarding File"
                                accept=".pdf,.doc,.docx" @remove-existing="handleRemoveOnboarding"
                                @preview="handlePreviewOnboarding" />
                        </div>

                    </div>
                </div>
            </div>
        </form>
        <template #actions>
            <!-- Actions -->
            <div class="p-3 sm:p-4 flex flex-col xs:flex-row items-center justify-center xs:justify-end gap-2">
                <button type="button" @click="handleCancel"
                    class="px-4 py-2 w-full xs:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="button" @click="handleSubmitForm"
                    class="px-4 py-2 w-full xs:w-auto text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ isCreate ? 'Create' : 'Update' }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import ImageUploadCrop from '@/components/common/ImageUploadCrop.vue'
import FileUploadSection from '@/components/common/FileUploadSection.vue'
import {
    Factory, Globe, Building2, Mail, Contact, Phone, UserRound,
    FilePenLine, MapPin, CircleCheck
} from 'lucide-vue-next'

// Types
export interface ManufacturerFormData {
    manufacturerName: string
    primaryEmail: string
    orderEmail: string
    eligibilityEmail: string
    secondaryEmail: string
    website: string
    address: string
    contactPerson: string
    contactNumber: string
    manufacturerStatus: number
    logoUrl: string
    ivrFilename: string
    ivrFileExtension: string
    orderFilename: string
    orderFileExtension: string
    onboardingFilename: string
    onboardingFileExtension: string
}

interface Props {
    modelValue: boolean
    isCreate: boolean
    formData: ManufacturerFormData
    selectedManufacturerId: number | null
}

interface Emits {
    'update:modelValue': [value: boolean]
    'submit': [data: {
        formData: ManufacturerFormData
        isCreate: boolean
        manufacturerId: number | null
        logoFile: File | null
        removeLogoFlag: boolean
        ivrFile: File | null
        removeIvrFlag: boolean
        orderFile: File | null
        removeOrderFlag: boolean
        onboardingFile: File | null
        removeOnboardingFlag: boolean
    }]
    'cancel': []
    'preview-file': [type: 'ivr' | 'order' | 'onboarding']
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Local reactive state
const localFormData = ref<ManufacturerFormData>({
    manufacturerName: '',
    primaryEmail: '',
    orderEmail: '',
    eligibilityEmail: '',
    secondaryEmail: '',
    website: '',
    address: '',
    contactPerson: '',
    contactNumber: '',
    manufacturerStatus: 0,
    logoUrl: '',
    ivrFilename: '',
    ivrFileExtension: '',
    orderFilename: '',
    orderFileExtension: '',
    onboardingFilename: '',
    onboardingFileExtension: '',
})

const localLogoFile = ref<File | null>(null)
const localRemoveLogoFlag = ref(false)
const isCropperOpen = ref(false)

function handleCropperOpening() {
    isCropperOpen.value = true
}

function handleCropperClosing() {
    isCropperOpen.value = false
}

const localIvrFile = ref<File | null>(null)
const ivrPreviewUrl = ref<string | null>(null)
const localRemoveIvrFlag = ref(false)

const localOrderFile = ref<File | null>(null)
const orderPreviewUrl = ref<string | null>(null)
const localRemoveOrderFlag = ref(false)

const localOnboardingFile = ref<File | null>(null)
const onboardingPreviewUrl = ref<string | null>(null)
const localRemoveOnboardingFlag = ref(false)

// Computed
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

// Sync with props
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        // Reset local state when modal opens
        localFormData.value = { ...props.formData }
        localLogoFile.value = null
        localRemoveLogoFlag.value = false
        localIvrFile.value = null
        ivrPreviewUrl.value = null
        localRemoveIvrFlag.value = false
        localOrderFile.value = null
        orderPreviewUrl.value = null
        localRemoveOrderFlag.value = false
        localOnboardingFile.value = null
        onboardingPreviewUrl.value = null
        localRemoveOnboardingFlag.value = false
    }
}, { immediate: true })

watch(() => props.formData, (newFormData) => {
    localFormData.value = { ...newFormData }
}, { deep: true })

// Methods
function fixWebsite() {
    const url = localFormData.value.website
    if (url && !/^https?:\/\//i.test(url)) {
        localFormData.value.website = 'https://' + url
    }
}

function handleContactInput(event: Event) {
    const target = event.target as HTMLInputElement
    localFormData.value.contactNumber = formatContactNumber(target.value)
}

function formatContactNumber(value: string) {
    // Allow only numbers, hyphens, parentheses, plus sign, and spaces
    return value.replace(/[^0-9\-\(\)\+\s]/g, '')
}

function handleRemoveIvr() {
    localRemoveIvrFlag.value = true
    localIvrFile.value = null
    localFormData.value.ivrFilename = ''
}

function handleRemoveOrder() {
    localRemoveOrderFlag.value = true
    localOrderFile.value = null
    localFormData.value.orderFilename = ''
}

function handleRemoveOnboarding() {
    localRemoveOnboardingFlag.value = true
    localOnboardingFile.value = null
    localFormData.value.onboardingFilename = ''
}

function handlePreviewIvr() {
    emit('preview-file', 'ivr')
}

function handlePreviewOrder() {
    emit('preview-file', 'order')
}

function handlePreviewOnboarding() {
    emit('preview-file', 'onboarding')
}

function handleSubmitForm() {
    emit('submit', {
        formData: { ...localFormData.value },
        isCreate: props.isCreate,
        manufacturerId: props.selectedManufacturerId,
        logoFile: localLogoFile.value,
        removeLogoFlag: localRemoveLogoFlag.value,
        ivrFile: localIvrFile.value,
        removeIvrFlag: localRemoveIvrFlag.value,
        orderFile: localOrderFile.value,
        removeOrderFlag: localRemoveOrderFlag.value,
        onboardingFile: localOnboardingFile.value,
        removeOnboardingFlag: localRemoveOnboardingFlag.value,
    })
}

function handleCancel() {
    emit('cancel')
    resetForm()
}

function resetForm() {
    localFormData.value = {
        manufacturerName: '',
        primaryEmail: '',
        orderEmail: '',
        eligibilityEmail: '',
        secondaryEmail: '',
        website: '',
        address: '',
        contactPerson: '',
        contactNumber: '',
        manufacturerStatus: 0,
        logoUrl: '',
        ivrFilename: '',
        ivrFileExtension: '',
        orderFilename: '',
        orderFileExtension: '',
        onboardingFilename: '',
        onboardingFileExtension: '',
    }
    localLogoFile.value = null
    localRemoveLogoFlag.value = false
    localIvrFile.value = null
    ivrPreviewUrl.value = null
    localRemoveIvrFlag.value = false
    localOrderFile.value = null
    orderPreviewUrl.value = null
    localRemoveOrderFlag.value = false
    localOnboardingFile.value = null
    onboardingPreviewUrl.value = null
    localRemoveOnboardingFlag.value = false
}
</script>
