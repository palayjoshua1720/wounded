<template>
    <div class="space-y-3">
        <!-- UPLOAD AREA -->
        <div v-if="!selectedFile && !existingFilename" class="relative group" @drop.prevent="handleDrop"
            @dragover.prevent="isDragging = true" @dragleave="isDragging = false">
            <input :id="`file-upload-${label.toLowerCase()}`" type="file" :accept="accept" class="hidden"
                @change="handleFileChange" />

            <label :for="`file-upload-${label.toLowerCase()}`" class="flex flex-col items-center justify-center h-44 rounded-xl border-2 border-dashed transition cursor-pointer
               bg-white dark:bg-gray-800
               hover:border-purple-400 hover:bg-purple-50 dark:hover:bg-gray-700
               group"
                :class="isDragging ? 'border-purple-500 bg-purple-50 dark:bg-gray-700' : 'border-gray-300 dark:border-gray-600'">
                <UploadCloud class="w-10 h-10 mb-3 text-gray-400 group-hover:text-purple-500 transition" />

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold text-purple-600 dark:text-purple-400">Click to upload</span>
                    or drag & drop
                </p>

                <p class="text-xs text-gray-400 mt-1">
                    {{ accept.replaceAll(',', ', ') }} • max 10MB
                </p>
            </label>
        </div>

        <!-- SELECTED FILE -->
        <div v-if="selectedFile"
            class="flex items-center justify-between p-3 rounded-xl border bg-green-50 dark:bg-gray-800 border-green-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-green-100 dark:bg-gray-700">
                    <FileText class="w-4 h-4 text-green-600" />
                </div>

                <div class="text-sm">
                    <p class="text-gray-700 dark:text-gray-200">
                        <span class="font-medium">{{ selectedFile.name }}</span>
                    </p>
                    <p class="text-xs text-green-600">Ready to upload</p>
                </div>
            </div>

            <button @click="removeSelected"
                class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-gray-700 text-red-500 transition">
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- EXISTING FILE -->
        <div v-else-if="existingFilename"
            class="flex items-center justify-between p-3 rounded-xl border bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700">
                    <FileText class="w-4 h-4 text-gray-500" />
                </div>

                <div class="text-sm">
                    <p class="text-gray-700 dark:text-gray-200">
                        <span class="font-medium">{{ displayFilename }}</span>
                    </p>
                    <p class="text-xs text-gray-400">Current file</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button @click="emit('preview')"
                    class="px-2 py-1 text-xs rounded-md text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700">
                    Preview
                </button>

                <button @click="removeExisting"
                    class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-gray-700 text-red-500 transition">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { UploadCloud, FileText, X } from 'lucide-vue-next'

const isDragging = ref(false)
const props = defineProps<{
    selectedFile?: File | null
    previewUrl?: string | null
    removeFlag?: boolean
    existingFilename?: string
    existingFileExtension?: string
    label: string
    accept: string
}>()

// Compute display filename - replace .enc with actual extension
const displayFilename = computed(() => {
    if (!props.existingFilename) return ''

    const ext =
        props.existingFileExtension ||
        props.existingFilename.split('.').pop()

    // Extract YYYYMMDD from filename
    const dateMatch = props.existingFilename.match(/(\d{8})/)
    let formattedDate = ''

    if (dateMatch?.[1]) {
        const raw = dateMatch[1]

        const year = raw.slice(0, 4)
        const month = raw.slice(4, 6)
        const day = raw.slice(6, 8)

        const date = new Date(`${year}-${month}-${day}`)

        formattedDate = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        })
    }

    // Clean filename (only remove system noise, keep meaning intact)
    let name = props.existingFilename
        .replace(/\.[^/.]+$/, '')          // remove extension
        .replace(/^new_/, '')              // remove "new_" prefix
        .replace(/_\d{8}_\d{6}/, '')       // remove timestamp block
        .replace(/_[a-f0-9]{8,}$/i, '')    // remove hash tail
        .replace(/_/g, ' ')                // underscores → spaces
        .trim()

    // Light cleanup (no aggressive transformations)
    name = name.replace(/\s+/g, ' ')

    return formattedDate
        ? `${name} • ${formattedDate}`.toUpperCase()
        : name.toUpperCase()
})

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