<template>
    <div class="space-y-3">
        <!-- Upload Section -->
        <div>
            <div v-if="label || showLabel" class="flex items-center gap-2 mb-2">
                <Image class="w-5 h-5 text-green-500" />
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                    {{ label || 'Image' }} {{ isOptional ? '(Optional)' : '' }}
                </h3>
            </div>

            <!-- Drag & Drop Area (only shown when no image selected and no preview URL) -->
            <div v-if="!selectedFile && !previewUrl" class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer
                bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
                :class="{ 'border-purple-500 bg-purple-50': isDragging }" @drop.prevent="handleDrop"
                @dragover.prevent="isDragging = true" @dragleave="isDragging = false">
                <input :id="`image-upload-${uniqueId}`" type="file" :accept="accept || 'image/png,image/jpeg,image/jpg'"
                    class="hidden" @change="handleFileChange" />
                <label :for="`image-upload-${uniqueId}`"
                    class="flex flex-col items-center justify-center text-center cursor-pointer">
                    <UploadCloud class="w-10 h-10 mb-3 text-gray-400" />
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-semibold text-purple-600 dark:text-purple-400">Click to upload</span> or drag
                        and drop
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ acceptHint || 'JPEG, JPG, PNG' }}</p>
                </label>
            </div>

            <!-- Selected file preview -->
            <div v-if="selectedFile"
                class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
                <div class="flex items-center gap-2">
                    <Image class="w-4 h-4 text-gray-400" />
                    <span>Selected: <span class="font-medium">{{ selectedFile.name }}</span></span>
                    <img v-if="previewUrl" :src="previewUrl" class="w-8 h-8 rounded object-cover ml-2 border" />
                </div>
                <button @click="removeImage" class="text-red-500 hover:text-red-600 transition">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Existing image preview -->
            <div v-else-if="previewUrl"
                class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
                <div class="flex items-center gap-2">
                    <Image class="w-4 h-4 text-gray-400" />
                    <span>{{ existingLabel || 'Current image:' }}</span>
                    <img :src="previewUrl" class="w-8 h-8 rounded object-cover border" />
                </div>
                <button @click="removeImage" class="text-red-500 hover:text-red-600 transition">
                    <X class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Cropper Modal -->
        <BaseModal v-model="showCropModal" :title="modalTitle || 'Crop Image'" max-width="520px">
            <div class="p-6 space-y-6">
                <!-- Instructions -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ modalInstructions || defaultInstructions }}
                    </p>
                </div>

                <!-- Crop Area Container -->
                <div
                    class="relative mx-auto w-full max-w-[320px] aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700">
                    <canvas ref="cropCanvas" @mousedown="startPan" @mousemove="handlePan" @mouseup="endPan"
                        @mouseleave="endPan" @wheel="handleZoom" @touchstart="startTouchPan" @touchmove="handleTouchPan"
                        @touchend="endTouchPan" @touchcancel="endTouchPan"
                        class="absolute inset-0 w-full h-full touch-none cursor-grab active:cursor-grabbing"
                        :class="{ 'cursor-grabbing': isPanning }" />

                    <!-- Circular Overlay -->
                    <div class="absolute inset-0 pointer-events-none" v-if="cropShape !== 'rectangle'">
                        <div class="absolute inset-0 bg-black/30"></div>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-4/5 h-4/5">
                            <div class="absolute inset-0 border-2 border-white/80 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Zoom Controls -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Zoom Level</label>
                        <span
                            class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                            {{ Math.round(scale * 100) }}%
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <button @click="zoomOut" :disabled="scale <= (minScale || 0.5)"
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed">
                            <Minus class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        </button>
                        <span class="text-xs text-gray-500">{{ ((minScale || 0.5) * 100).toFixed(0) }}%</span>
                        <input type="range" :min="minScale || 0.5" :max="maxScale || 3" :step="0.1"
                            v-model.number="scale" @input="drawCanvas"
                            class="flex-1 h-2 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-lg appearance-none cursor-pointer" />
                        <span class="text-xs text-gray-500">{{ ((maxScale || 3) * 100).toFixed(0) }}%</span>
                        <button @click="zoomIn" :disabled="scale >= (maxScale || 3)"
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed">
                            <Plus class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 justify-center">
                    <button @click="resetPosition"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600">
                        <RefreshCcw class="w-4 h-4" />
                        Reset View
                    </button>

                    <button @click="selectNewImage"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100">
                        <Image class="w-4 h-4" />
                        Change Image
                    </button>
                </div>
            </div>

            <template #actions>
                <div class="flex justify-end gap-3 px-6 pb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <button @click="cancelCrop"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button @click="confirmCrop"
                        class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700">
                        <Check class="w-4 h-4" />
                        Apply Crop
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onUnmounted } from 'vue'
import { Image, UploadCloud, X, RefreshCcw, Plus, Minus, Check } from 'lucide-vue-next'
import { useToast } from 'vue-toastification'
import BaseModal from './BaseModal.vue'

// Props
const props = defineProps<{
    modelValue?: File | null
    modelPreviewUrl?: string | null
    modelRemoveFlag?: boolean
    label?: string
    showLabel?: boolean
    isOptional?: boolean
    existingLabel?: string
    accept?: string
    acceptHint?: string
    modalTitle?: string
    modalInstructions?: string
    cropShape?: 'circle' | 'rectangle'
    minScale?: number
    maxScale?: number
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', file: File | null): void
    (e: 'update:modelPreviewUrl', url: string | null): void
    (e: 'update:modelRemoveFlag', flag: boolean): void
}>()

// Toast for error messages
const toast = useToast()

// Local state
const uniqueId = Math.random().toString(36).substring(2, 9)
const isDragging = ref(false)
const showCropModal = ref(false)
const cropCanvas = ref<HTMLCanvasElement | null>(null)
const selectedImage = ref<HTMLImageElement | null>(null)
const imageSrc = ref<string | null>(null)
const scale = ref(1)
const offsetX = ref(0)
const offsetY = ref(0)
const isPanning = ref(false)
const lastPanPoint = ref({ x: 0, y: 0 })
const lastTouchDistance = ref(0)
let pendingFile: File | null = null
const objectUrl = ref<string | null>(null)
const defaultInstructions = 'Adjust your image to fit perfectly. Drag to reposition and use the zoom controls below.'

// Computed properties to sync with props
const selectedFile = ref<File | null>(props.modelValue || null)
const previewUrl = ref<string | null>(props.modelPreviewUrl || null)

// Sync with props when they change
watch(() => props.modelValue, (newVal) => {
    selectedFile.value = newVal || null
})

watch(() => props.modelPreviewUrl, (newVal) => {
    previewUrl.value = newVal || null
})

// Allowed file types
const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg']

// Validate file
function validateFile(file: File): boolean {
    if (!file) return false
    if (!allowedTypes.includes(file.type)) {
        toast.error('Unsupported image format. Use PNG or JPEG/JPG.')
        return false
    }
    return true
}

// Open cropper with file
async function openCropper(file: File) {
    pendingFile = file
    revokeObjectUrl()
    imageSrc.value = URL.createObjectURL(file)
    scale.value = 1
    offsetX.value = 0
    offsetY.value = 0
    showCropModal.value = true

    await nextTick()
    if (!imageSrc.value) return

    const img = document.createElement('img')
    img.src = imageSrc.value
    await new Promise<void>((resolve, reject) => {
        img.onload = () => resolve()
        img.onerror = reject
    })
    selectedImage.value = img
    drawCanvas()
}

// Draw canvas
function drawCanvas() {
    if (!cropCanvas.value || !selectedImage.value) return

    const canvas = cropCanvas.value
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    const rect = canvas.getBoundingClientRect()
    let width = Math.floor(rect.width)
    let height = Math.floor(rect.height)

    canvas.width = width
    canvas.height = height

    ctx.clearRect(0, 0, width, height)
    ctx.fillStyle = '#ffffff'
    ctx.fillRect(0, 0, width, height)

    const img = selectedImage.value
    const imgAspect = img.naturalWidth / img.naturalHeight
    const canvasAspect = width / height

    let drawWidth: number, drawHeight: number
    if (imgAspect > canvasAspect) {
        drawWidth = width
        drawHeight = width / imgAspect
    } else {
        drawHeight = height
        drawWidth = height * imgAspect
    }

    drawWidth *= scale.value
    drawHeight *= scale.value

    const x = (width - drawWidth) / 2 + offsetX.value
    const y = (height - drawHeight) / 2 + offsetY.value

    ctx.save()
    if (props.cropShape !== 'rectangle') {
        const centerX = width / 2
        const centerY = height / 2
        const radius = Math.min(width, height) / 2
        ctx.beginPath()
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2)
        ctx.clip()
    }

    ctx.drawImage(img, x, y, drawWidth, drawHeight)
    ctx.restore()
}

// Zoom controls
function zoomIn() {
    scale.value = Math.min(props.maxScale || 3, scale.value + 0.1)
    drawCanvas()
}

function zoomOut() {
    scale.value = Math.max(props.minScale || 0.5, scale.value - 0.1)
    drawCanvas()
}

function handleZoom(event: WheelEvent) {
    event.preventDefault()
    const delta = event.deltaY > 0 ? -0.05 : 0.05
    scale.value = Math.max(props.minScale || 0.5, Math.min(props.maxScale || 3, scale.value + delta))
    drawCanvas()
}

// Pan controls
function startPan(event: MouseEvent) {
    isPanning.value = true
    lastPanPoint.value = { x: event.clientX, y: event.clientY }
}

function handlePan(event: MouseEvent) {
    if (!isPanning.value) return
    const deltaX = event.clientX - lastPanPoint.value.x
    const deltaY = event.clientY - lastPanPoint.value.y
    offsetX.value += deltaX
    offsetY.value += deltaY
    lastPanPoint.value = { x: event.clientX, y: event.clientY }
    drawCanvas()
}

function endPan() {
    isPanning.value = false
}

// Touch pan/zoom
function getTouchDistance(touches: TouchList): number {
    if (touches.length < 2) return 0
    const t1 = touches[0], t2 = touches[1]
    const dx = t1.clientX - t2.clientX
    const dy = t1.clientY - t2.clientY
    return Math.sqrt(dx * dx + dy * dy)
}

function startTouchPan(event: TouchEvent) {
    event.preventDefault()
    if (event.touches.length === 1) {
        isPanning.value = true
        const t = event.touches[0]
        lastPanPoint.value = { x: t.clientX, y: t.clientY }
    } else if (event.touches.length === 2) {
        isPanning.value = false
        lastTouchDistance.value = getTouchDistance(event.touches)
    }
}

function handleTouchPan(event: TouchEvent) {
    event.preventDefault()
    if (event.touches.length === 1 && isPanning.value) {
        const t = event.touches[0]
        const deltaX = t.clientX - lastPanPoint.value.x
        const deltaY = t.clientY - lastPanPoint.value.y
        offsetX.value += deltaX
        offsetY.value += deltaY
        lastPanPoint.value = { x: t.clientX, y: t.clientY }
        drawCanvas()
    } else if (event.touches.length === 2) {
        const dist = getTouchDistance(event.touches)
        if (lastTouchDistance.value > 0) {
            const delta = (dist - lastTouchDistance.value) * 0.01
            scale.value = Math.max(props.minScale || 0.5, Math.min(props.maxScale || 3, scale.value + delta))
            drawCanvas()
        }
        lastTouchDistance.value = dist
    }
}

function endTouchPan() {
    isPanning.value = false
    lastTouchDistance.value = 0
}

function resetPosition() {
    scale.value = 1
    offsetX.value = 0
    offsetY.value = 0
    drawCanvas()
}

function selectNewImage() {
    const input = document.getElementById(`image-upload-${uniqueId}`) as HTMLInputElement | null
    if (input) input.click()
}

// Handle file change
function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    if (!file) return

    if (!validateFile(file)) return
    openCropper(file)
    input.value = ''
}

// Handle file drop
function handleDrop(event: DragEvent) {
    event.preventDefault()
    isDragging.value = false
    const file = event.dataTransfer?.files?.[0]
    if (!file) return

    if (!validateFile(file)) return
    openCropper(file)
}

// Confirm crop
async function confirmCrop() {
    if (!cropCanvas.value || !selectedImage.value || !pendingFile) return

    try {
        const blob = await new Promise<Blob | null>((resolve) => {
            cropCanvas.value!.toBlob(function (b) {
                resolve(b)
            }, 'image/png', 0.95)
        })

        if (!blob) throw new Error('Failed to generate image')

        const croppedFile = new File([blob], pendingFile.name, { type: 'image/png' })

        // Update local state
        selectedFile.value = croppedFile
        revokeObjectUrl()
        objectUrl.value = URL.createObjectURL(croppedFile)
        previewUrl.value = objectUrl.value

        // Emit updates
        emit('update:modelValue', croppedFile)
        emit('update:modelPreviewUrl', objectUrl.value)
        emit('update:modelRemoveFlag', false)

        // Close modal
        showCropModal.value = false

        // Cleanup
        if (imageSrc.value?.startsWith('blob:')) {
            URL.revokeObjectURL(imageSrc.value)
        }
        imageSrc.value = null
        selectedImage.value = null
        pendingFile = null
    } catch (err) {
        console.error('Crop error:', err)
        toast.error('Failed to process image')
    }
}

// Cancel crop
function cancelCrop() {
    showCropModal.value = false
    if (imageSrc.value?.startsWith('blob:')) {
        URL.revokeObjectURL(imageSrc.value)
    }
    imageSrc.value = null
    selectedImage.value = null
    pendingFile = null
}

// Remove image
function removeImage() {
    emit('update:modelRemoveFlag', true)
    emit('update:modelValue', null)
    emit('update:modelPreviewUrl', null)
    revokeObjectUrl()
}

// Revoke object URL
function revokeObjectUrl() {
    if (objectUrl.value?.startsWith('blob:')) {
        URL.revokeObjectURL(objectUrl.value)
    }
    objectUrl.value = null
}

// Watch for modal open
watch(showCropModal, (val) => {
    if (val) {
        nextTick(() => drawCanvas())
    }
})

// Cleanup on unmount
onUnmounted(() => {
    revokeObjectUrl()
    if (imageSrc.value?.startsWith('blob:')) {
        URL.revokeObjectURL(imageSrc.value)
    }
})
</script>

<style scoped>
input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

input[type="range"]::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

input[type="range"]::-webkit-slider-runnable-track {
    width: 100%;
    height: 4px;
    cursor: pointer;
    background: linear-gradient(to right, #3b82f6, #60a5fa, #93c5fd);
    border-radius: 2px;
}

input[type="range"]::-moz-range-track {
    width: 100%;
    height: 4px;
    cursor: pointer;
    background: linear-gradient(to right, #3b82f6, #60a5fa, #93c5fd);
    border-radius: 2px;
}
</style>
