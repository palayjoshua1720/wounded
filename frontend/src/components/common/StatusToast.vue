<template>
  <div
    class="status-toast"
    :class="`status-toast--${variant}`"
    :style="{ '--status-color': statusColor }"
    role="status"
  >
    <span class="status-toast__circle status-toast__circle--large" aria-hidden="true"></span>
    <span class="status-toast__circle status-toast__circle--medium" aria-hidden="true"></span>
    <span class="status-toast__circle status-toast__circle--small" aria-hidden="true"></span>

    <div class="status-toast__icon-badge" aria-hidden="true">
      <Check v-if="variant === 'success'" class="status-toast__icon" :size="21" :stroke-width="2.5" />
      <Info v-else-if="variant === 'info'" class="status-toast__icon" :size="21" :stroke-width="2.5" />
      <AlertTriangle v-else-if="variant === 'warning'" class="status-toast__icon" :size="21" :stroke-width="2.5" />
      <AlertCircle v-else class="status-toast__icon" :size="21" :stroke-width="2.5" />
    </div>

    <div class="status-toast__content">
      <div class="status-toast__title">{{ title }}</div>
      <div class="status-toast__message">{{ message }}</div>
    </div>

    <button
      type="button"
      class="status-toast__dismiss"
      aria-label="Dismiss notification"
      @click="$emit('close-toast')"
    >
      &times;
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { AlertCircle, AlertTriangle, Check, Info } from 'lucide-vue-next'

type StatusToastVariant = 'success' | 'error' | 'warning' | 'info'

const props = defineProps<{
  variant: StatusToastVariant
  title: string
  message: string
}>()

defineEmits<{
  'close-toast': []
}>()

const statusColors: Record<StatusToastVariant, string> = {
  success: '#2f855a',
  error: '#b4535b',
  warning: '#b7791f',
  info: '#2563eb',
}

const statusColor = computed(() => statusColors[props.variant])
</script>

<style scoped>
:global(.Vue-Toastification__toast.status-toast-host) {
  width: min(480px, calc(100vw - 32px));
  min-width: 0;
  max-width: none;
  min-height: 0;
  padding: 0;
  border-radius: 16px;
  background: transparent;
  box-shadow: none;
  color: inherit;
  overflow: visible;
}

:global(.Vue-Toastification__toast-component-body.status-toast-host-body) {
  width: 100%;
  min-width: 0;
  padding: 0;
}

.status-toast {
  --status-body-color: color-mix(in srgb, var(--status-color) 82%, #374151 18%);
  position: relative;
  display: grid;
  grid-template-columns: 40px minmax(0, 1fr) auto;
  align-items: start;
  gap: 14px;
  width: 100%;
  box-sizing: border-box;
  padding: 20px;
  border-radius: 16px;
  background: color-mix(in srgb, var(--status-color) 10%, white);
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14), 0 2px 6px rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.status-toast__circle {
  position: absolute;
  border: 1px solid color-mix(in srgb, var(--status-color) 22%, transparent);
  border-radius: 50%;
  pointer-events: none;
  z-index: 0;
}

.status-toast__circle--large {
  top: -74px;
  right: 24px;
  width: 150px;
  height: 150px;
}

.status-toast__circle--medium {
  right: 164px;
  bottom: -34px;
  width: 78px;
  height: 78px;
}

.status-toast__circle--small {
  top: 12px;
  right: 202px;
  width: 34px;
  height: 34px;
}

.status-toast > :not(.status-toast__circle) {
  position: relative;
  z-index: 1;
}

.status-toast__icon-badge {
  display: flex;
  width: 40px;
  height: 40px;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 9999px;
  background: #fff;
  color: var(--status-color);
  box-shadow: 0 7px 12px color-mix(in srgb, var(--status-color) 18%, transparent), 0 2px 4px rgba(15, 23, 42, 0.1);
}

.status-toast__icon-badge::after {
  position: absolute;
  bottom: -3px;
  width: 22px;
  height: 6px;
  border-radius: 50%;
  background: color-mix(in srgb, var(--status-color) 24%, transparent);
  content: '';
  filter: blur(4px);
}

.status-toast__icon {
  position: relative;
  z-index: 1;
}

.status-toast__content {
  min-width: 0;
  padding-top: 1px;
}

.status-toast__title {
  color: #1f2937;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.35;
}

.status-toast__message {
  margin-top: 3px;
  color: var(--status-body-color);
  font-size: 0.9rem;
  font-weight: 500;
  line-height: 1.45;
  overflow-wrap: anywhere;
  white-space: pre-wrap;
}

.status-toast__dismiss {
  align-self: start;
  margin: -3px -4px 0 0;
  padding: 0 2px;
  border: 0;
  background: transparent;
  color: color-mix(in srgb, var(--status-color) 58%, #6b7280);
  cursor: pointer;
  font-size: 1.5rem;
  font-weight: 300;
  line-height: 1;
  opacity: 0.62;
  transition: opacity 150ms ease;
}

.status-toast__dismiss:hover,
.status-toast__dismiss:focus-visible {
  opacity: 1;
}

.status-toast__dismiss:focus-visible {
  outline: 2px solid var(--status-color);
  outline-offset: 2px;
}

:global(.dark) .status-toast {
  --status-body-color: color-mix(in srgb, var(--status-color) 78%, #e5e7eb 22%);
  background: color-mix(in srgb, var(--status-color) 20%, #1f2937);
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28), 0 2px 6px rgba(0, 0, 0, 0.2);
}

:global(.dark) .status-toast__title {
  color: #f9fafb;
}
</style>
