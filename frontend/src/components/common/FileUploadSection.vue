<template>
    <div>
        <!-- Drag & Drop Area (shown when no file is selected and no existing file) -->
        <div v-if="!selectedFile && !existingFilename"
            class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
            @drop.prevent="handleDrop" @dragover.prevent="allowDrop">
            <input :id="`file-upload-${label.toLowerCase()}`" type="file" :accept="accept" class="hidden"
                @change="handleFileChange" />
            <label :for="`file-upload-${label.toLowerCase()}`"
                class="flex flex-col items-center justify-center text-center cursor-pointer">
                <UploadCloud class="w-10 h-10 mb-3 text-gray-400" />
                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-semibold text-purple-600 dark:text-purple-400">Click to upload</span> or drag and
                    drop
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ accept.replaceAll(',', ', ') }} (max. 10MB)
                </p>
            </label>
        </div>

        <!-- New file selected preview -->
        <div v-if="selectedFile"
            class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
            <div class="flex items-center gap-2">
                <FileText class="w-4 h-4 text-gray-400" />
                <span>Selected: <span class="font-medium">{{ selectedFile.name }}</span></span>
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <button @click="removeSelected" class="text-red-500 hover:text-red-600 transition">
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Existing file preview (when editing) -->
        <div v-else-if="existingFilename && !selectedFile"
            class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
            <div class="flex items-center gap-2">
                <FileText class="w-4 h-4 text-gray-400" />
                <span>Current file: <span class="font-medium">{{ existingFilename }}</span></span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="emit('preview')" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                    Preview
                </button>
                <button @click="removeExisting" class="text-red-500 hover:text-red-600 transition">
                    <X class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { UploadCloud, FileText, X } from 'lucide-vue-next'

const props = defineProps<{
    selectedFile?: File | null
    previewUrl?: string | null
    removeFlag?: boolean
    existingFilename?: string
    label: string
    accept: string
}>()

const emit = defineEmits<{
    (e: 'update:selectedFile', file: File | null): void
    (e: 'update:previewUrl', url: string | null): void
    (e: 'update:removeFlag', flag: boolean): void
    (e: 'remove-existing'): void
    (e: 'preview'): void
}>()

function allowDrop(event: DragEvent) {
    event.preventDefault()
}

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    if (!file) return
    emit('update:selectedFile', file)
    emit('update:previewUrl', URL.createObjectURL(file))
    emit('update:removeFlag', false)
}

function handleDrop(event: DragEvent) {
    event.preventDefault()
    const file = event.dataTransfer?.files?.[0]
    if (!file) return
    emit('update:selectedFile', file)
    emit('update:previewUrl', URL.createObjectURL(file))
    emit('update:removeFlag', false)
}

function removeSelected() {
    emit('update:selectedFile', null)
    if (props.previewUrl) URL.revokeObjectURL(props.previewUrl)
    emit('update:previewUrl', null)
    emit('update:removeFlag', false)
}

function removeExisting() {
    emit('update:removeFlag', true)
    emit('update:selectedFile', null)
    emit('update:previewUrl', null)
    emit('remove-existing')
}
</script>