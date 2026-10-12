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
    </div>

    <!-- Cropper Modal - Using teleport to render outside parent modal hierarchy -->
    <Teleport to="body">
        <transition name="fade">
            <div v-if="showCropModal"
                class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center overflow-y-auto overflow-x-hidden">
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="cancelCrop"></div>
                <div class="relative rounded-t-2xl sm:rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 sm:mx-4 my-0 sm:my-8 overflow-hidden w-full max-w-4xl max-h-[90vh] flex flex-col"
                    style="contain: layout style;">
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between p-4 border-b flex-shrink-0 bg-white dark:bg-gray-800 sticky top-0 z-10">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white pr-4 line-clamp-1">
                            {{ modalTitle || 'Crop Image' }}
                        </h2>
                        <button @click="cancelCrop"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-2xl leading-none flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            &times;
                        </button>
                    </div>
                    <!-- Content -->
                    <div class="p-4 sm:p-6 flex-1 overflow-y-auto overflow-x-hidden">
                        <div class="space-y-6">
                            <!-- Instructions -->
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ modalInstructions || defaultInstructions }}
                                </p>
                            </div>

                            <!-- Crop Area Container -->
                            <div
                                class="relative mx-auto w-full max-w-[320px] aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700">
                                <canvas ref="cropCanvas" @mousedown="startPan" @mousemove="handlePan" @mouseup="endPan"
                                    @mouseleave="endPan" @wheel="handleZoom" @touchstart="startTouchPan"
                                    @touchmove="handleTouchPan" @touchend="endTouchPan" @touchcancel="endTouchPan"
                                    class="absolute inset-0 w-full h-full touch-none cursor-grab active:cursor-grabbing select-none transition-transform duration-150"
                                    :class="{ 'cursor-grabbing': isPanning }"></canvas>

                                <!-- Rounded Square Crop Overlay with Circular Guide -->
                                <div class="absolute inset-0 pointer-events-none" v-if="cropShape !== 'rectangle'">
                                    <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px]"></div>
                                    <div
                                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-4/5 h-4/5">
                                        <div
                                            class="absolute inset-0 border-2 border-white/80 rounded-2xl shadow-[0_0_0_1px_rgba(255,255,255,0.3),0_0_20px_rgba(0,0,0,0.2)]">
                                        </div>
                                        <div
                                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[85%] h-[85%] border border-dashed border-white/40 rounded-full">
                                        </div>
                                        <div
                                            class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-white rounded-tl-lg">
                                        </div>
                                        <div
                                            class="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-white rounded-tr-lg">
                                        </div>
                                        <div
                                            class="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-white rounded-bl-lg">
                                        </div>
                                        <div
                                            class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-white rounded-br-lg">
                                        </div>
                                    </div>
                                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2">
                                        <div
                                            class="flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-sm rounded-full">
                                            <FoldVertical class="w-3 h-3 text-white/80"></FoldVertical>
                                            <span class="text-xs text-white/80 font-medium">Drag to move • Scroll to
                                                zoom</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Controls Section -->
                            <div class="space-y-6">
                                <!-- Zoom Controls -->
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Zoom
                                            Level</label>
                                        <span
                                            class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                                            {{ Math.round(scale * 100) }}%
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <button @click="zoomOut" :disabled="scale <= (minScale || 0.5)"
                                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow">
                                            <Minus class="w-5 h-5 text-gray-600 dark:text-gray-400"></Minus>
                                        </button>
                                        <span class="text-xs text-gray-500">{{ ((minScale || 0.5) * 100).toFixed(0)
                                            }}%</span>
                                        <div class="flex-1 relative">
                                            <input type="range" :min="minScale || 0.5" :max="maxScale || 3" :step="0.1"
                                                v-model.number="scale" @input="drawCanvas"
                                                class="w-full h-2 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-lg appearance-none cursor-pointer slider-thumb">
                                        </div>
                                        <span class="text-xs text-gray-500">{{ ((maxScale || 3) * 100).toFixed(0)
                                            }}%</span>

                                        <button @click="zoomIn" :disabled="scale >= (maxScale || 3)"
                                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow">
                                            <Plus class="w-5 h-5 text-gray-600 dark:text-gray-400"></Plus>
                                        </button>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3 justify-center">
                                    <button @click="resetPosition"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow">
                                        <RefreshCcw class="w-4 h-4"></RefreshCcw>
                                        Reset View
                                    </button>

                                    <button @click="selectNewImage"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200 shadow-sm hover:shadow">
                                        <Image class="w-4 h-4"></Image>
                                        Change Image
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Actions Footer -->
                    <div
                        class="border-t border-gray-100 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800 p-3">
                        <div class="flex justify-end gap-3">
                            <button @click="cancelCrop"
                                class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow">
                                Cancel
                            </button>
                            <button @click="confirmCrop"
                                class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.02]">
                                <Check class="w-4 h-4"></Check>
                                Apply Crop
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onUnmounted } from 'vue'
import { Image, UploadCloud, X, RefreshCcw, Plus, Minus, Check, FoldVertical } from 'lucide-vue-next'
import { useAppToast } from '@/composables/ui/useAppToast'

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
    (e: 'cropper-opening'): void
    (e: 'cropper-closing'): void
}>()

// Toast for error messages
const toast = useAppToast()

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
    // Use the smaller dimension to ensure square canvas
    const size = Math.floor(Math.min(rect.width, rect.height))

    canvas.width = size
    canvas.height = size

    ctx.clearRect(0, 0, size, size)
    ctx.fillStyle = '#ffffff'
    ctx.fillRect(0, 0, size, size)

    const img = selectedImage.value
    const imgAspect = img.naturalWidth / img.naturalHeight
    const canvasAspect = 1 // Always square

    let drawWidth: number, drawHeight: number
    if (imgAspect > canvasAspect) {
        drawWidth = size
        drawHeight = size / imgAspect
    } else {
        drawHeight = size
        drawWidth = size * imgAspect
    }

    drawWidth *= scale.value
    drawHeight *= scale.value

    const x = (size - drawWidth) / 2 + offsetX.value
    const y = (size - drawHeight) / 2 + offsetY.value

    ctx.save()
    if (props.cropShape !== 'rectangle') {
        const r = Math.min(size, size) * 0.05
        ctx.beginPath()
        ctx.moveTo(r, 0)
        ctx.lineTo(size - r, 0)
        ctx.quadraticCurveTo(size, 0, size, r)
        ctx.lineTo(size, size - r)
        ctx.quadraticCurveTo(size, size, size - r, size)
        ctx.lineTo(r, size)
        ctx.quadraticCurveTo(0, size, 0, size - r)
        ctx.lineTo(0, r)
        ctx.quadraticCurveTo(0, 0, r, 0)
        ctx.closePath()
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

// Watch for modal open/close to emit events for parent modal visibility
watch(showCropModal, (val) => {
    if (val) {
        // Cropper is opening - emit event to parent modal
        emit('cropper-opening')
        nextTick(() => drawCanvas())
    } else {
        // Cropper is closing - emit event to restore parent modal
        emit('cropper-closing')
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
