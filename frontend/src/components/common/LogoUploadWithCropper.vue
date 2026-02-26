<template>
    <div class="space-y-4">
        <!-- Upload / Drag-drop area (shown when no file & no existing logo) -->
        <div v-if="!modelValue && !existingUrl"
            class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
            @drop.prevent="handleLogoDrop" @dragover.prevent="allowLogoDrop">
            <input id="logo-upload-internal" type="file" accept="image/png,image/jpeg,image/jpg" class="hidden"
                @change="handleLogoChange" />
            <label for="logo-upload-internal"
                class="flex flex-col items-center justify-center text-center cursor-pointer w-full h-full">
                <UploadCloud class="w-10 h-10 mb-3 text-gray-400" />
                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-semibold text-purple-600 dark:text-purple-400">Click to upload</span> or drag and
                    drop
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">JPEG, JPG, PNG (max. 2MB)</p>
            </label>
        </div>

        <!-- Preview when a new file is selected (after crop) -->
        <div v-else-if="modelValue"
            class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
            <div class="flex items-center gap-2">
                <Image class="w-4 h-4 text-gray-400" />
                <span>Selected: <span class="font-medium">{{ modelValue.name }}</span></span>
                <img v-if="localPreviewUrl" :src="localPreviewUrl" class="w-8 h-8 rounded object-cover ml-2 border"
                    alt="Cropped logo preview" />
            </div>
            <button @click="removeLogo" class="text-red-500 hover:text-red-600 transition">
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Preview when editing (existing logo from server) -->
        <div v-else-if="existingUrl"
            class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
            <div class="flex items-center gap-2">
                <Image class="w-4 h-4 text-gray-400" />
                <span>Current logo:</span>
                <img :src="existingUrl" class="w-8 h-8 rounded object-cover border" alt="Current logo" />
            </div>
            <button @click="removeLogo" class="text-red-500 hover:text-red-600 transition">
                <X class="w-5 h-5" />
            </button>
        </div>
    </div>

    <!-- Cropper Modal -->
    <BaseModal v-model="showLogoCropModal" title="Crop Logo" max-width="520px">
        <div class="p-6 space-y-6">
            <!-- Instructions -->
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Adjust your logo to fit perfectly. Drag to reposition and use the zoom controls below.
                </p>
            </div>

            <!-- Crop Area -->
            <div
                class="relative mx-auto w-full max-w-[320px] aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="absolute inset-0 opacity-20">
                    <div class="w-full h-full bg-grid-pattern bg-[length:20px_20px]"></div>
                </div>

                <canvas ref="logoCanvas" @mousedown="logoStartPan" @mousemove="logoHandlePan" @mouseup="logoEndPan"
                    @mouseleave="logoEndPan" @wheel="logoHandleZoom" @touchstart="logoStartTouchPan"
                    @touchmove="logoHandleTouchPan" @touchend="logoEndTouchPan" @touchcancel="logoEndTouchPan"
                    class="absolute inset-0 w-full h-full touch-none cursor-grab active:cursor-grabbing select-none transition-transform duration-150"
                    :class="{ 'cursor-grabbing': logoIsPanning }"></canvas>

                <!-- Circular Crop Overlay -->
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px]"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-4/5 h-4/5">
                        <div
                            class="absolute inset-0 border-2 border-white/80 rounded-full shadow-[0_0_0_1px_rgba(255,255,255,0.3),0_0_20px_rgba(0,0,0,0.2)]">
                        </div>
                        <div class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-white rounded-tl">
                        </div>
                        <div class="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-white rounded-tr">
                        </div>
                        <div class="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-white rounded-bl">
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-white rounded-br">
                        </div>
                    </div>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-sm rounded-full">
                            <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                            <span class="text-xs text-white/80 font-medium">Drag to move • Scroll to zoom</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="space-y-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Zoom Level</label>
                        <span
                            class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                            {{ Math.round(logoScale * 100) }}%
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <button @click="logoZoomOut" :disabled="logoScale <= 0.5"
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow">
                            <Minus class="w-5 h-5 text-gray-500" />
                        </button>

                        <div class="flex-1 relative">
                            <input type="range" min="0.5" max="3" step="0.1" v-model="logoScale" @input="drawLogoCanvas"
                                class="w-full h-2 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-lg appearance-none cursor-pointer slider-thumb" />
                            <div class="absolute inset-0 flex justify-between items-center pointer-events-none px-1">
                                <span class="text-xs text-gray-500">50%</span>
                                <span class="text-xs text-gray-500">300%</span>
                            </div>
                        </div>

                        <button @click="logoZoomIn" :disabled="logoScale >= 3"
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow">
                            <Plus class="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 justify-center">
                    <button @click="logoResetPosition"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow">
                        <RefreshCw class="w-5 h-5 text-gray-500" />
                        Reset View
                    </button>

                    <button @click="logoSelectNewImage"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200 shadow-sm hover:shadow">
                        <Image class="w-5 h-5 text-blue-500" />
                        Change Image
                    </button>
                </div>
            </div>
        </div>

        <template #actions>
            <div class="flex justify-end gap-3 px-6 pb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                <button @click="logoCancelCrop"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow">
                    Cancel
                </button>
                <button @click="logoConfirmCrop"
                    class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.02]">
                    Apply Crop
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, watch, onUnmounted, nextTick } from 'vue'
import {
    UploadCloud,
    Image as LucideImage,
    X,
    RefreshCw,
    Minus,
    Plus
} from 'lucide-vue-next'
import BaseModal from '@/components/common/BaseModal.vue'

// ────────────────────────────────────────────────
// Props & Emits
// ────────────────────────────────────────────────
const props = defineProps<{
    modelValue: File | null
    existingUrl?: string | null
    isUploading?: boolean
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', file: File | null): void
    (e: 'remove'): void
    (e: 'start-upload'): void
    (e: 'end-upload'): void
}>()

// ────────────────────────────────────────────────
// State
// ────────────────────────────────────────────────
const showLogoCropModal = ref(false)
const logoCanvas = ref<HTMLCanvasElement | null>(null)
const logoSelectedImage = ref<HTMLImageElement | null>(null)
const logoScale = ref(1)
const logoOffsetX = ref(0)
const logoOffsetY = ref(0)
const logoIsPanning = ref(false)
const logoLastPanPoint = ref({ x: 0, y: 0 })
const logoLastTouchDistance = ref(0)
let logoPendingFile = ref<File | null>(null)

const localPreviewUrl = ref<string | null>(null)

// ────────────────────────────────────────────────
// Watch modelValue → generate local preview
// ────────────────────────────────────────────────
watch(
    () => props.modelValue,
    (newFile) => {
        if (newFile) {
            if (localPreviewUrl.value) {
                URL.revokeObjectURL(localPreviewUrl.value)
            }
            localPreviewUrl.value = URL.createObjectURL(newFile)
        } else {
            if (localPreviewUrl.value) {
                URL.revokeObjectURL(localPreviewUrl.value)
                localPreviewUrl.value = null
            }
        }
    },
    { immediate: true }
)

// ────────────────────────────────────────────────
// File handling
// ────────────────────────────────────────────────
function allowLogoDrop(e: DragEvent) {
    e.preventDefault()
}

async function handleLogoDrop(e: DragEvent) {
    e.preventDefault()
    const file = e.dataTransfer?.files?.[0]
    if (!file) return
    await processSelectedFile(file)
}

async function handleLogoChange(e: Event) {
    const input = e.target as HTMLInputElement
    const file = input.files?.[0]
    if (!file) return
    await processSelectedFile(file)
    input.value = '' // reset input
}

async function processSelectedFile(file: File) {
    if (!file.type.startsWith('image/') || !['png', 'jpeg', 'jpg'].some(ext => file.type.endsWith(ext))) {
        // You can add toast here if you want
        console.warn('Only PNG and JPEG allowed')
        return
    }

    logoPendingFile.value = file
    const url = URL.createObjectURL(file)

    const img = new Image()
    img.src = url
    await new Promise((resolve, reject) => {
        img.onload = resolve
        img.onerror = reject
    })

    logoSelectedImage.value = img
    logoScale.value = 1
    logoOffsetX.value = 0
    logoOffsetY.value = 0

    showLogoCropModal.value = true

    await nextTick()
    drawLogoCanvas()
}

// ────────────────────────────────────────────────
// Canvas drawing
// ────────────────────────────────────────────────
function drawLogoCanvas() {
    if (!logoCanvas.value || !logoSelectedImage.value) return

    const canvas = logoCanvas.value
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    const rect = canvas.getBoundingClientRect()
    const size = Math.floor(Math.min(rect.width, rect.height))

    canvas.width = size
    canvas.height = size

    ctx.clearRect(0, 0, size, size)
    ctx.fillStyle = '#ffffff'
    ctx.fillRect(0, 0, size, size)

    const img = logoSelectedImage.value
    const imgAspect = img.naturalWidth / img.naturalHeight

    let drawWidth = size
    let drawHeight = size / imgAspect

    if (imgAspect < 1) {
        drawHeight = size
        drawWidth = size * imgAspect
    }

    drawWidth *= logoScale.value
    drawHeight *= logoScale.value

    const x = (size - drawWidth) / 2 + logoOffsetX.value
    const y = (size - drawHeight) / 2 + logoOffsetY.value

    ctx.save()
    ctx.beginPath()
    ctx.rect(0, 0, size, size)
    ctx.clip()
    ctx.drawImage(img, x, y, drawWidth, drawHeight)
    ctx.restore()
}

// ────────────────────────────────────────────────
// Zoom & Pan controls
// ────────────────────────────────────────────────
function logoZoomIn() {
    logoScale.value = Math.min(3, logoScale.value + 0.1)
    drawLogoCanvas()
}

function logoZoomOut() {
    logoScale.value = Math.max(0.5, logoScale.value - 0.1)
    drawLogoCanvas()
}

function logoHandleZoom(e: WheelEvent) {
    e.preventDefault()
    const delta = e.deltaY > 0 ? -0.05 : 0.05
    logoScale.value = Math.max(0.5, Math.min(3, logoScale.value + delta))
    drawLogoCanvas()
}

function logoStartPan(e: MouseEvent) {
    logoIsPanning.value = true
    logoLastPanPoint.value = { x: e.clientX, y: e.clientY }
}

function logoHandlePan(e: MouseEvent) {
    if (!logoIsPanning.value) return
    const dx = e.clientX - logoLastPanPoint.value.x
    const dy = e.clientY - logoLastPanPoint.value.y
    logoOffsetX.value += dx
    logoOffsetY.value += dy
    logoLastPanPoint.value = { x: e.clientX, y: e.clientY }
    drawLogoCanvas()
}

function logoEndPan() {
    logoIsPanning.value = false
}

function getTouchDistance(touches: TouchList) {
    if (touches.length < 2) return 0
    const dx = touches[0].clientX - touches[1].clientX
    const dy = touches[0].clientY - touches[1].clientY
    return Math.sqrt(dx * dx + dy * dy)
}

function logoStartTouchPan(e: TouchEvent) {
    e.preventDefault()
    if (e.touches.length === 1) {
        logoIsPanning.value = true
        logoLastPanPoint.value = { x: e.touches[0].clientX, y: e.touches[0].clientY }
    } else if (e.touches.length === 2) {
        logoLastTouchDistance.value = getTouchDistance(e.touches)
    }
}

function logoHandleTouchPan(e: TouchEvent) {
    e.preventDefault()
    if (e.touches.length === 1 && logoIsPanning.value) {
        const t = e.touches[0]
        const dx = t.clientX - logoLastPanPoint.value.x
        const dy = t.clientY - logoLastPanPoint.value.y
        logoOffsetX.value += dx
        logoOffsetY.value += dy
        logoLastPanPoint.value = { x: t.clientX, y: t.clientY }
        drawLogoCanvas()
    } else if (e.touches.length === 2) {
        const dist = getTouchDistance(e.touches)
        if (logoLastTouchDistance.value > 0) {
            const delta = (dist - logoLastTouchDistance.value) * 0.01
            logoScale.value = Math.max(0.5, Math.min(3, logoScale.value + delta))
            drawLogoCanvas()
        }
        logoLastTouchDistance.value = dist
    }
}

function logoEndTouchPan() {
    logoIsPanning.value = false
    logoLastTouchDistance.value = 0
}

function logoResetPosition() {
    logoScale.value = 1
    logoOffsetX.value = 0
    logoOffsetY.value = 0
    drawLogoCanvas()
}

function logoSelectNewImage() {
    logoSelectedImage.value = null
    if (logoPendingFile.value) {
        URL.revokeObjectURL(URL.createObjectURL(logoPendingFile.value))
    }
    logoPendingFile.value = null

    const input = document.getElementById('logo-upload-internal') as HTMLInputElement
    if (input) input.click()
}

// ────────────────────────────────────────────────
// Confirm / Cancel
// ────────────────────────────────────────────────
async function logoConfirmCrop() {
    if (!logoCanvas.value || !logoPendingFile.value) return

    emit('start-upload')

    const blob = await new Promise<Blob | null>((resolve) => {
        logoCanvas.value!.toBlob((b) => resolve(b), 'image/png', 0.92)
    })

    if (!blob) return

    const croppedFile = new File([blob], logoPendingFile.value.name, { type: 'image/png' })

    emit('update:modelValue', croppedFile)

    showLogoCropModal.value = false

    // Cleanup
    if (logoSelectedImage.value?.src.startsWith('blob:')) {
        URL.revokeObjectURL(logoSelectedImage.value.src)
    }
    logoSelectedImage.value = null
    logoPendingFile.value = null

    emit('end-upload')
}

function logoCancelCrop() {
    showLogoCropModal.value = false

    if (logoSelectedImage.value?.src.startsWith('blob:')) {
        URL.revokeObjectURL(logoSelectedImage.value.src)
    }
    logoSelectedImage.value = null
    logoPendingFile.value = null
}

function removeLogo() {
    emit('update:modelValue', null)
    emit('remove')
}

// ────────────────────────────────────────────────
// Cleanup
// ────────────────────────────────────────────────
onUnmounted(() => {
    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value)
    }
    if (logoSelectedImage.value?.src.startsWith('blob:')) {
        URL.revokeObjectURL(logoSelectedImage.value.src)
    }
})
</script>

<style scoped>
.bg-grid-pattern {
    background-image:
        linear-gradient(rgba(0, 0, 0, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.1) 1px, transparent 1px);
}

input[type="range"].slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    background: linear-gradient(to right, #3b82f6 0%, #3b82f6 calc((var(--value, 50) - 50) * 100 / 250), #e5e7eb calc((var(--value, 50) - 50) * 100 / 250), #e5e7eb 100%);
    border-radius: 10px;
    height: 6px;
}

input[type="range"].slider-thumb::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: #3b82f6;
    border: 2px solid #ffffff;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
    transition: all 0.2s ease;
}

input[type="range"].slider-thumb::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
}

input[type="range"].slider-thumb::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background: #3b82f6;
    border: 2px solid #ffffff;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
    transition: all 0.2s ease;
}
</style>