<template>
  <BaseModal v-model="showModal" :title="title" max-width="520px">
    <div class="p-6 space-y-6">
      <!-- Instructions -->
      <div class="text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Adjust your image to fit perfectly. Drag to reposition and use the zoom controls below.
        </p>
      </div>

      <!-- Crop Area Container -->
      <div class="relative mx-auto w-full max-w-[320px] aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700">
        <!-- Canvas Container with subtle grid background -->
        <div class="absolute inset-0 opacity-20">
          <div class="w-full h-full bg-grid-pattern bg-[length:20px_20px]"></div>
        </div>
        
        <canvas
          ref="cropperCanvas"
          @mousedown="startPan"
          @mousemove="handlePan"
          @mouseup="endPan"
          @mouseleave="endPan"
          @wheel="handleZoom"
          @touchstart="startTouchPan"
          @touchmove="handleTouchPan"
          @touchend="endTouchPan"
          @touchcancel="endTouchPan"
          class="absolute inset-0 w-full h-full touch-none cursor-grab active:cursor-grabbing select-none transition-transform duration-150"
          :class="{ 'cursor-grabbing': isPanning }"
        ></canvas>

        <!-- Circular Crop Overlay with modern design -->
        <div class="absolute inset-0 pointer-events-none">
          <!-- Outer shadow/mask -->
          <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px]"></div>
          
          <!-- Circular crop window -->
          <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-4/5 h-4/5">
            <!-- Border with glow effect -->
            <div class="absolute inset-0 border-2 border-white/80 rounded-full shadow-[0_0_0_1px_rgba(255,255,255,0.3),0_0_20px_rgba(0,0,0,0.2)]"></div>
            
            <!-- Corner indicators -->
            <div class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-white rounded-tl"></div>
            <div class="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-white rounded-tr"></div>
            <div class="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-white rounded-bl"></div>
            <div class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-white rounded-br"></div>
          </div>

          <!-- Help text -->
          <div class="absolute bottom-4 left-1/2 -translate-x-1/2">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-sm rounded-full">
              <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
              </svg>
              <span class="text-xs text-white/80 font-medium">Drag to move • Scroll to zoom</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Controls Section -->
      <div class="space-y-6">
        <!-- Zoom Controls -->
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
          <div class="flex items-center justify-between mb-3">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Zoom Level</label>
            <span class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
              {{ Math.round(scale * 100) }}%
            </span>
          </div>
          
          <div class="flex items-center gap-4">
            <button 
              @click="zoomOut"
              :disabled="scale <= 0.5"
              class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow"
            >
              <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
              </svg>
            </button>
            
            <div class="flex-1 relative">
              <input
                type="range"
                min="0.5"
                max="3"
                step="0.1"
                v-model="scale"
                @input="drawCanvas"
                class="w-full h-2 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-lg appearance-none cursor-pointer slider-thumb"
              />
              <div class="absolute inset-0 flex justify-between items-center pointer-events-none px-1">
                <span class="text-xs text-gray-500">50%</span>
                <span class="text-xs text-gray-500">300%</span>
              </div>
            </div>
            
            <button 
              @click="zoomIn"
              :disabled="scale >= 3"
              class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow"
            >
              <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 justify-center">
          <button
            @click="resetPosition"
            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Reset View
          </button>
          
          <button
            @click="selectNewImage"
            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200 shadow-sm hover:shadow"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Change Image
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Footer -->
    <template #actions>
      <div class="flex justify-end gap-3 px-6 pb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
        <button
          @click="cancelCrop"
          class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow"
        >
          Cancel
        </button>
        <button
          @click="confirmCrop"
          class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.02]"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Apply Crop
        </button>
      </div>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch, onUnmounted } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'

// Props
const props = defineProps<{
  modelValue: boolean
  title?: string
  imageFile: File | null
}>()

// Emits
const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'crop-complete', file: File): void
  (e: 'cancel'): void
}>()

// Reactive refs
const showModal = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const title = computed(() => props.title || 'Crop Image')

const cropperCanvas = ref<HTMLCanvasElement | null>(null)
const selectedImage = ref<HTMLImageElement | null>(null)
const imageSrc = ref<string | null>(null)
const scale = ref(1)
const offsetX = ref(0)
const offsetY = ref(0)
const isPanning = ref(false)
const lastPanPoint = ref({ x: 0, y: 0 })
const lastTouchDistance = ref(0)

// Watch for image file changes
watch(() => props.imageFile, (newFile) => {
  if (newFile) {
    openCropper(newFile)
  }
}, { immediate: true })

// Open cropper with a file
function openCropper(file: File) {
  revokeObjectUrl()
  imageSrc.value = URL.createObjectURL(file)
  scale.value = 1
  offsetX.value = 0
  offsetY.value = 0
  
  // When modal opens, load image into an HTMLImageElement and draw
  setTimeout(async () => {
    if (!imageSrc.value) return
    const img = document.createElement('img') as HTMLImageElement
    img.src = imageSrc.value!
    await new Promise((res, rej) => { 
      img.onload = res
      img.onerror = rej
    })
    selectedImage.value = img
    drawCanvas()
  }, 50)
}

// Draw the canvas
function drawCanvas() {
  if (!cropperCanvas.value || !selectedImage.value) return
  const canvasEl = cropperCanvas.value
  const ctx = canvasEl.getContext('2d')
  if (!ctx) return

  const rect = canvasEl.getBoundingClientRect()
  const size = Math.floor(Math.min(rect.width, rect.height))
  canvasEl.width = size
  canvasEl.height = size

  ctx.clearRect(0, 0, size, size)
  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, size, size)

  const img = selectedImage.value
  const imgAspect = img.naturalWidth / img.naturalHeight
  const canvasAspect = 1

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

  // Square clip (full-canvas square) and border
  ctx.save()
  ctx.beginPath()
  ctx.rect(0, 0, size, size)
  ctx.clip()
  ctx.drawImage(img, x, y, drawWidth, drawHeight)
  ctx.restore()
}

// Zoom controls
function zoomIn() {
  scale.value = Math.min(3, scale.value + 0.1)
  drawCanvas()
}

function zoomOut() {
  scale.value = Math.max(0.5, scale.value - 0.1)
  drawCanvas()
}

function handleZoom(event: WheelEvent) {
  event.preventDefault()
  const delta = event.deltaY > 0 ? -0.05 : 0.05
  scale.value = Math.max(0.5, Math.min(3, scale.value + delta))
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
  return Math.sqrt(dx*dx + dy*dy)
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
    const dx = t.clientX - lastPanPoint.value.x
    const dy = t.clientY - lastPanPoint.value.y
    offsetX.value += dx
    offsetY.value += dy
    lastPanPoint.value = { x: t.clientX, y: t.clientY }
    drawCanvas()
  } else if (event.touches.length === 2) {
    const dist = getTouchDistance(event.touches)
    if (lastTouchDistance.value > 0) {
      const delta = (dist - lastTouchDistance.value) * 0.01
      scale.value = Math.max(0.5, Math.min(3, scale.value + delta))
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
  // Clear current, allow user to pick new file
  selectedImage.value = null
  imageSrc.value = null
  
  // Emit event to allow parent to trigger file selection
  emit('cancel')
}

async function confirmCrop() {
  if (!cropperCanvas.value || !selectedImage.value || !props.imageFile) return
  const canvasEl = cropperCanvas.value
  canvasEl.toBlob((blob) => {
    if (!blob) return
    const newFile = new File([blob], props.imageFile!.name, { type: blob.type })
    emit('crop-complete', newFile)
    closeModal()
  }, 'image/png')
}

function cancelCrop() {
  emit('cancel')
  closeModal()
}

function closeModal() {
  showModal.value = false
  revokeObjectUrl()
  imageSrc.value = null
  selectedImage.value = null
}

function revokeObjectUrl() {
  if (imageSrc.value && imageSrc.value.startsWith('blob:')) {
    URL.revokeObjectURL(imageSrc.value)
  }
}

// Cleanup object URLs on unmount
onUnmounted(() => {
  revokeObjectUrl()
})
</script>

<style scoped>
/* Custom grid pattern for the background */
.bg-grid-pattern {
  background-image: 
    linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px);
}

/* Enhanced range slider styling */
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
  border: none;
}

input[type="range"].slider-thumb::-moz-range-thumb:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
}

/* Smooth transitions for all interactive elements */
button {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

canvas {
  transition: transform 0.15s ease;
}
</style>