<template>
  <div v-if="modelValue && entry" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
    <div class="fixed inset-0 bg-black/40 transition-opacity" @click="$emit('update:modelValue', false)"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
      <!-- Header -->
      <div class="flex-shrink-0 px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-start justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
            :class="entry.product_type === 'graft' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-purple-100 dark:bg-purple-900/30'">
            <Package class="w-5 h-5" :class="entry.product_type === 'graft' ? 'text-blue-600 dark:text-blue-400' : 'text-purple-600 dark:text-purple-400'" />
          </div>
          <div class="min-w-0">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white font-mono truncate">{{ entry.serial_number }}</h3>
            <div class="mt-1 flex items-center gap-1.5 flex-wrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium" :class="getStatusBadgeClass(entry.status)">{{ entry.status_label }}</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium"
                :class="entry.product_type === 'graft' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300'">
                {{ entry.product_type === 'graft' ? 'Graft' : 'Other Product' }}
              </span>
            </div>
          </div>
        </div>
        <button @click="$emit('update:modelValue', false)" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex-shrink-0">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto p-5 space-y-5">
        <!-- Product Section -->
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Tag class="w-4 h-4 text-blue-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Product</h4>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-3">
            <div>
              <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Product Name</p>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.product_name }}</p>
            </div>
            <template v-if="entry.product_type === 'graft'">
              <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-200/60 dark:border-gray-600/40">
                <div>
                  <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Brand</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.brand_name || 'No Records Found' }}</p>
                </div>
                <div>
                  <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Size</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.size_name || 'No Records Found' }}</p>
                </div>
                <div class="col-span-2">
                  <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Clinic</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.clinic_name }}</p>
                </div>
              </div>
            </template>
            <div class="pt-3 border-t border-gray-200/60 dark:border-gray-600/40">
              <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Order ID</p>
              <div v-if="entry.order_number || entry.order_code" class="flex items-center gap-2">
                <p class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ entry.order_number || entry.order_code }}</p>
                <button v-if="entry.order_code && entry.order_number" @click="copyToClipboard(entry.order_code, 'order')"
                  class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Copy order code">
                  <Copy class="w-3.5 h-3.5" />
                </button>
              </div>
              <p v-else class="text-sm text-gray-400 dark:text-gray-500">No Records Found</p>
            </div>
          </div>
        </div>

        <!-- Billing & Usage -->
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Receipt class="w-4 h-4 text-green-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Billing & Usage</h4>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-1">Invoice Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="entry.invoice_status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'">
                  {{ entry.invoice_status === 'paid' ? 'Paid' : 'Unpaid' }}
                </span>
              </div>
              <div>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-1">Usage Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="entry.is_used ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300'">
                  {{ entry.is_used ? 'Used' : 'Not Used' }}
                </span>
              </div>
            </div>
            <div v-if="(entry.invoice_status === 'paid' && entry.invoice_number) || (entry.is_used && entry.graft_usage_id)" class="space-y-2 pt-3 border-t border-gray-200/60 dark:border-gray-600/40">
              <div v-if="entry.invoice_status === 'paid' && entry.invoice_number" class="flex items-center justify-between bg-white dark:bg-gray-600 rounded-lg px-3 py-2">
                <div class="flex items-center gap-2">
                  <FileText class="w-3.5 h-3.5 text-green-500" />
                  <div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500">Invoice #</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ entry.invoice_number }}</p>
                  </div>
                </div>
                <button @click="copyToClipboard(entry.invoice_number, 'invoice')"
                  class="p-1 rounded text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Copy">
                  <Check v-if="copiedField === 'invoice'" class="w-3.5 h-3.5 text-green-500" />
                  <Copy v-else class="w-3.5 h-3.5" />
                </button>
              </div>
              <div v-if="entry.is_used && entry.graft_usage_id" class="flex items-center justify-between bg-white dark:bg-gray-600 rounded-lg px-3 py-2">
                <div class="flex items-center gap-2">
                  <Hash class="w-3.5 h-3.5 text-green-500" />
                  <div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500">Graft Usage ID</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ entry.graft_usage_id }}</p>
                  </div>
                </div>
                <button @click="copyToClipboard(entry.graft_usage_id, 'graft')"
                  class="p-1 rounded text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Copy">
                  <Check v-if="copiedField === 'graft'" class="w-3.5 h-3.5 text-green-500" />
                  <Copy v-else class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="entry.notes">
          <div class="flex items-center gap-2 mb-2">
            <FileText class="w-4 h-4 text-amber-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Notes</h4>
          </div>
          <div class="bg-amber-50/50 dark:bg-amber-900/10 rounded-xl px-4 py-3 border border-amber-200/40 dark:border-amber-800/30">
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ entry.notes }}</p>
          </div>
        </div>

        <!-- Timestamps -->
        <div class="flex flex-col gap-1.5 text-xs text-gray-400 dark:text-gray-500 pt-1">
          <span class="flex items-center gap-1.5"><Clock class="w-3.5 h-3.5" /> Created {{ formatDateTime(entry.created_at) }}</span>
          <span class="flex items-center gap-1.5"><Clock class="w-3.5 h-3.5" /> Updated {{ formatDateTime(entry.updated_at) }}</span>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex-shrink-0 px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2">
        <button @click="$emit('update:modelValue', false)" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">Close</button>
        <button @click="$emit('edit', entry)"
          class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30 active:scale-95 text-sm font-medium">
          <Pencil class="w-4 h-4" />
          Edit
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import {
  X, Package, Tag, Receipt, FileText, Hash, Copy, Check, Clock, Pencil
} from 'lucide-vue-next'

interface LedgerEntry {
  ledger_id: number
  serial_number: string
  product_type: string
  product_id: number
  product_name: string
  brand_id: string | null
  brand_name: string
  size_name: string | null
  clinic_id: string
  clinic_name: string
  order_id: string | null
  order_number: string | null
  order_code: string | null
  status: number
  status_label: string
  status_color: string
  is_used: boolean
  graft_usage_id: string | null
  invoice_status: string
  invoice_id: string | null
  invoice_number: string | null
  notes: string | null
  created_at: string
  updated_at: string
}

const props = defineProps<{
  modelValue: boolean
  entry: LedgerEntry | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'edit', entry: LedgerEntry): void
}>()

const copiedField = ref<string | null>(null)

function getStatusBadgeClass(status: number): string {
  const c: Record<number, string> = {
    0: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    1: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    2: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    3: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    4: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    5: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    6: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
  }
  return c[status] || c[1]
}

function formatDateTime(dateStr: string): string {
  if (!dateStr) return 'No Records Found'
  const date = new Date(dateStr)
  return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function copyToClipboard(text: string, field: string) {
  try {
    await navigator.clipboard.writeText(text)
    copiedField.value = field
    setTimeout(() => { copiedField.value = null }, 2000)
  } catch (err) { console.error('Failed to copy:', err) }
}
</script>
