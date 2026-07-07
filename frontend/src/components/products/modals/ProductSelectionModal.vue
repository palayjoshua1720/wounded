<template>
    <BaseModal v-model="isOpen" title="Add New Product">
        <div class="space-y-4">
            <p class="text-gray-600 dark:text-gray-400">Choose the type of product you want to add:</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <button @click="handleSelectGraft"
                    class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:border-green-500 hover:bg-green-50 dark:hover:bg-gray-700 transition-all duration-200 group">
                    <PencilRuler
                        class="w-12 h-12 text-green-600 dark:text-green-400 mb-4 group-hover:scale-110 transition-transform" />
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Graft Size</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center max-w-xs">
                        Add graft products with multiple sizes, area (cm²), individual pricing, and stock per size
                    </span>
                </button>

                <button @click="handleSelectOther"
                    class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 group">
                    <Package
                        class="w-12 h-12 text-blue-600 dark:text-blue-400 mb-4 group-hover:scale-110 transition-transform" />
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Other Product</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center max-w-xs">
                        Add consumables, equipment, kits, or any non-graft item with simple name, price, and stock
                    </span>
                </button>
            </div>
        </div>
        <template #actions>
            <div class="flex justify-end w-full p-5">
                <button @click="handleCancel"
                    class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                    Cancel
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { PencilRuler, Package } from 'lucide-vue-next'

interface Props {
    modelValue: boolean
}

interface Emits {
    'update:modelValue': [value: boolean]
    'select-graft': []
    'select-other': []
    'cancel': []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

function handleSelectGraft() {
    emit('select-graft')
}

function handleSelectOther() {
    emit('select-other')
}

function handleCancel() {
    emit('cancel')
}
</script>
