<template>
    <BaseModal v-model="isOpen" :title="isEdit ? 'Edit Brand' : 'Add New Brand'">
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Brand Information -->
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <Package class="w-5 h-5 text-green-500" />
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Brand Information</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                            <input v-model="localFormData.brandName" type="text" required placeholder="Brand Name"
                                class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
									focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status<span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                            <select v-model="localFormData.brandStatus" required
                                class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option :value="0">Active</option>
                                <option :value="1">Inactive</option>
                                <option :value="2">Archived</option>
                            </select>
                        </div>
                    </div>

                    <!-- Manufacturer -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Manufacturer<span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                            <select v-model="localFormData.manufacturerId" required
                                class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option v-for="opt in manufacturerOptions" :key="opt.value ?? 'none'"
                                    :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">MUE (MedicallyUnlikely
                            Edits) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                            <input v-model.number="localFormData.mue" type="number" min="1" required
                                placeholder="Enter MUE value" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                          focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum units allowed per day per
                            patient
                        </p>
                    </div>

                    <!-- Logo Upload Section -->
                    <div class="sm:col-span-2">
                        <ImageUploadCrop v-model="localLogoFile" v-model:model-preview-url="localFormData.logoUrl"
                            v-model:model-remove-flag="localRemoveLogoFlag" label="Brand Logo" :show-label="true"
                            :is-optional="true" existing-label="Current logo:" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Description
                            (Optional)</label>
                        <div class="relative">
                            <Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                            <textarea v-model="localFormData.description" placeholder="Description" rows="3"
                                class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
										focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-vertical"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graft Sizes -->
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <PencilRuler class="w-5 h-5 text-green-500" />
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Graft Sizes (Optional)</h3>
                </div>
                <div class="space-y-3">
                    <div v-for="(graftSize, index) in localFormData.graftSizes" :key="graftSize.id || index"
                        :class="index > 0 ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : ''">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Size {{ index + 1
                            }}</span>
                            <button v-if="index > 0" type="button" @click="removeGraftSize(index)"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Item
                                    No</label>
                                <div class="relative">
                                    <Barcode class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                    <input v-model="graftSize.item_no" type="text" placeholder="ABC-0000"
                                        class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Size</label>
                                <div class="relative">
                                    <RulerDimensionLine class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                    <input v-model="graftSize.size" type="text" placeholder="2cm x 2cm" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Area
                                    (cm²)</label>
                                <div class="relative">
                                    <Diameter class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                    <input v-model.number="graftSize.area" type="number" placeholder="0" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Price</label>
                                <div class="relative">
                                    <DollarSign class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                    <input v-model.number="graftSize.price" type="number" step="0.01" placeholder="0.00"
                                        class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Stock</label>
                                <div class="relative">
                                    <Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                    <input v-model.number="graftSize.stock" type="number" min="0" placeholder="0" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="addGraftSize"
                        class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors w-full sm:w-auto">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Size
                    </button>
                </div>
            </div>
        </form>
        <template #actions>
            <div class="flex justify-end w-full p-5 gap-3">
                <button type="button" @click="handleCancel" class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl 
				text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 
				transition-all duration-200">
                    Cancel
                </button>

                <button type="submit" @click="handleSubmit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white 
				rounded-xl hover:from-blue-700 hover:to-indigo-700 
				transition-all duration-200 shadow-md hover:shadow-lg">
                    {{ isEdit ? 'Update Brand' : 'Create Brand' }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import ImageUploadCrop from '@/components/common/ImageUploadCrop.vue'
import { Package, CircleCheck, Factory, Hash, Globe, PencilRuler, Plus, Trash2, Barcode, RulerDimensionLine, Diameter, DollarSign } from 'lucide-vue-next'

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

export interface SimpleManufacturer {
    id: number
    manufacturerName: string
}

export interface BrandFormData {
    brandName: string
    brandStatus: number
    manufacturerId: number | null
    mue: number | null
    logoUrl: string
    description: string
    graftSizes: GraftSize[]
}

interface Props {
    modelValue: boolean
    isEdit: boolean
    formData: BrandFormData
    manufacturers: SimpleManufacturer[]
}

interface Emits {
    'update:modelValue': [value: boolean]
    'submit': [data: { formData: BrandFormData; logoFile: File | null; removeLogoFlag: boolean }]
    'cancel': []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Local reactive state
const localFormData = ref<BrandFormData>({
    brandName: '',
    brandStatus: 0,
    manufacturerId: null,
    mue: null,
    logoUrl: '',
    description: '',
    graftSizes: [{ size: '', area: null, price: null, stock: 0, item_no: '' }]
})

const localLogoFile = ref<File | null>(null)
const localRemoveLogoFlag = ref(false)

// Sync with props
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        // Reset local state when modal opens
        localFormData.value = { ...props.formData }
        localLogoFile.value = null
        localRemoveLogoFlag.value = false
    }
}, { immediate: true })

watch(() => props.formData, (newFormData) => {
    localFormData.value = { ...newFormData }
}, { deep: true })

// Computed
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const manufacturerOptions = computed(() => {
    const opts = props.manufacturers.map(m => ({ value: m.id, label: m.manufacturerName }))
    if (localFormData.value.manufacturerId === null) {
        return [{ value: null as any, label: 'Select a Manufacturer' }, ...opts]
    } else {
        return opts
    }
})

// Methods
function addGraftSize() {
    localFormData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0 })
}

function removeGraftSize(index: number) {
    localFormData.value.graftSizes.splice(index, 1)
    if (localFormData.value.graftSizes.length === 0) {
        localFormData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, item_no: '' })
    }
}

function handleSubmit() {
    emit('submit', {
        formData: { ...localFormData.value },
        logoFile: localLogoFile.value,
        removeLogoFlag: localRemoveLogoFlag.value
    })
}

function handleCancel() {
    emit('cancel')
    localFormData.value = {
        brandName: '',
        brandStatus: 0,
        manufacturerId: null,
        mue: null,
        logoUrl: '',
        description: '',
        graftSizes: [{ size: '', area: null, price: null, stock: 0, item_no: '' }]
    }
    localLogoFile.value = null
    localRemoveLogoFlag.value = false
}
</script>
