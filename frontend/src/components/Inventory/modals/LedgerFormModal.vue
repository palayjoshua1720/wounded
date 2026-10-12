<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
    <div class="fixed inset-0 bg-black/40 transition-opacity" @click="$emit('close')"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
      <div class="flex-shrink-0 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEditing ? 'Edit Ledger Entry' : 'Add Ledger Entry' }}
        </h3>
        <button @click="$emit('close')" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <X class="w-5 h-5" />
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- Basic Information -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-4">
          <div class="flex items-center gap-2 mb-1">
            <Hash class="w-4 h-4 text-blue-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Basic Information</h4>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Serial Number <span class="text-red-500">*</span></label>
              <input v-model="localForm.serial_number" type="text" placeholder="e.g. SN-12345"
                class="w-full px-4 py-2.5 border-0 rounded-xl text-sm focus:ring-2 transition-all duration-200"
                :class="formErrors.serial_number
                  ? 'bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white ring-2 ring-red-300 focus:ring-red-500'
                  : 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500'" />
              <p v-if="formErrors.serial_number" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                <AlertCircle class="w-3 h-3" />
                {{ formErrors.serial_number }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Product Type <span class="text-red-500">*</span></label>
              <select v-model="localForm.product_type" @change="onProductTypeChange"
                class="w-full px-4 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500 transition-all duration-200 appearance-none">
                <option value="graft">Graft</option>
                <option value="other_product">Other Product</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Product Selection -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-4">
          <div class="flex items-center gap-2 mb-1">
            <Package class="w-4 h-4 text-blue-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Product Selection</h4>
          </div>
          <template v-if="localForm.product_type === 'graft'">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Clinic <span class="text-red-500">*</span></label>
                <select v-model="localForm.clinic_id"
                  class="w-full px-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500 transition-all duration-200 appearance-none">
                  <option value="">Select clinic</option>
                  <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="String(clinic.clinic_id)">{{ clinic.name }}</option>
                </select>
                <p v-if="formErrors.clinic_id" class="mt-1 text-xs text-red-500">{{ formErrors.clinic_id }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Brand <span class="text-red-500">*</span></label>
                <select v-model="localForm.brand_id" @change="localForm.product_id = ''"
                  class="w-full px-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500 transition-all duration-200 appearance-none">
                  <option value="">Select brand</option>
                  <option v-for="brand in brands" :key="brand.brand_id" :value="String(brand.brand_id)">{{ brand.brandName }}</option>
                </select>
                <p v-if="formErrors.brand_id" class="mt-1 text-xs text-red-500">{{ formErrors.brand_id }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Graft Size <span class="text-red-500">*</span></label>
                <select v-model="localForm.product_id" :disabled="!localForm.brand_id"
                  class="w-full px-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500 transition-all duration-200 appearance-none disabled:opacity-50 disabled:cursor-not-allowed">
                  <option value="">{{ localForm.brand_id ? 'Select size' : 'Select brand first' }}</option>
                  <option v-for="prod in filteredGraftProducts" :key="prod.id" :value="prod.id">{{ prod.name }} {{ prod.area ? `(${prod.area} cm²)` : '' }}</option>
                </select>
                <p v-if="formErrors.product_id" class="mt-1 text-xs text-red-500">{{ formErrors.product_id }}</p>
              </div>
            </div>
          </template>
          <template v-else>
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Product <span class="text-red-500">*</span></label>
              <select v-model="localForm.product_id"
                class="w-full px-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500 transition-all duration-200 appearance-none">
                <option value="">Select a product</option>
                <option v-for="prod in otherProducts" :key="prod.id" :value="prod.id">{{ prod.name }} {{ prod.price ? `($${prod.price})` : '' }}</option>
              </select>
              <p v-if="formErrors.product_id" class="mt-1 text-xs text-red-500">{{ formErrors.product_id }}</p>
            </div>
          </template>
        </div>

        <!-- Order & Status -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-4">
          <div class="flex items-center gap-2 mb-1">
            <ClipboardList class="w-4 h-4 text-blue-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Order & Status</h4>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Order ID</label>
            <div class="relative">
              <input v-model="orderSearchQuery" @input="$emit('searchOrders', orderSearchQuery)" @focus="onOrderInputFocus()" @blur="hideOrderDropdown()" type="text" placeholder="Type order number to search..."
                class="w-full px-4 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500 transition-all duration-200 pr-10" />
              <div class="absolute right-3 top-3">
                <Loader2 v-if="isSearchingOrders" class="w-4 h-4 text-gray-400 animate-spin" />
                <button v-else-if="orderSearchQuery || localForm.order_id" @click="clearOrder" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"><X class="w-4 h-4" /></button>
                <Search v-else class="w-4 h-4 text-gray-400" />
              </div>
              <div v-if="showOrderDropdown && orderSuggestions.length > 0" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                <button v-for="order in orderSuggestions" :key="order.id" @click="selectOrder(order)"
                  class="w-full px-4 py-2.5 text-left text-sm text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors first:rounded-t-xl last:rounded-b-xl">
                  <div class="font-medium">{{ order.display }}</div>
                  <div v-if="order.order_code && order.order_code !== order.display" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Code: {{ order.order_code }}</div>
                </button>
              </div>
              <div v-if="showOrderDropdown && !isSearchingOrders && orderSuggestions.length === 0 && orderSearchQuery.trim()" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg px-4 py-3 text-sm text-gray-500 dark:text-gray-400">No orders found</div>
            </div>
            <p v-if="localForm.order_id" class="mt-1.5 text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3" /> Linked to: {{ orderSearchQuery }}
            </p>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Status <span class="text-red-500">*</span></label>
            <div class="flex flex-wrap gap-2">
              <button v-for="(label, value) in statusOptions" :key="value" type="button" @click="localForm.status = Number(value)"
                :class="['relative px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 ease-out select-none border-2 flex items-center gap-1.5',
                  localForm.status === Number(value) ? getStatusPillActiveClass(Number(value)) : getStatusPillInactiveClass(Number(value))]">
                <span class="w-1.5 h-1.5 rounded-full transition-transform duration-200"
                  :class="localForm.status === Number(value) ? 'scale-110' : 'scale-90 opacity-60'"
                  :style="{ backgroundColor: localForm.status === Number(value) ? 'currentColor' : getStatusDotColor(Number(value)) }" />
                {{ label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Usage & Billing -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-4">
          <div class="flex items-center gap-2 mb-1">
            <Receipt class="w-4 h-4 text-blue-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Usage & Billing</h4>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Used</label>
              <div class="inline-flex p-1 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-xl">
                <button type="button" @click="localForm.is_used = true"
                  :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5', localForm.is_used ? 'bg-green-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500']">
                  <CheckCircle2 class="w-3.5 h-3.5" /> Yes
                </button>
                <button type="button" @click="localForm.is_used = false; localForm.graft_usage_id = ''"
                  :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5', !localForm.is_used ? 'bg-gray-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500']">
                  <X class="w-3.5 h-3.5" /> No
                </button>
              </div>
              <transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                <div v-if="localForm.is_used" class="mt-3 bg-white dark:bg-gray-700 rounded-xl border border-green-200 dark:border-green-800 shadow-sm overflow-hidden">
                  <div class="flex items-start gap-3 p-3">
                    <div class="mt-0.5 flex-shrink-0 w-7 h-7 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                      <Hash class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <label class="block text-xs font-semibold text-green-700 dark:text-green-400 mb-1.5">Graft Usage ID</label>
                      <input v-model="localForm.graft_usage_id" type="text" placeholder="e.g. GRL-2026-0001"
                        class="w-full px-3 py-2 border-0 rounded-lg text-sm focus:ring-2 transition-all duration-200"
                        :class="formErrors.graft_usage_id
                          ? 'bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white ring-2 ring-red-300 focus:ring-red-500'
                          : 'bg-gray-50 dark:bg-gray-600 text-gray-900 dark:text-white focus:ring-green-500'" />
                      <p v-if="formErrors.graft_usage_id" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <AlertCircle class="w-3 h-3" />
                        {{ formErrors.graft_usage_id }}
                      </p>
                    </div>
                  </div>
                </div>
              </transition>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Invoice</label>
              <div class="inline-flex p-1 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-xl">
                <button type="button" @click="localForm.invoice_status = 'paid'"
                  :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5', localForm.invoice_status === 'paid' ? 'bg-green-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500']">
                  <CheckCircle2 class="w-3.5 h-3.5" /> Paid
                </button>
                <button type="button" @click="localForm.invoice_status = 'unpaid'; localForm.invoice_id = ''"
                  :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5', localForm.invoice_status === 'unpaid' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500']">
                  <AlertCircle class="w-3.5 h-3.5" /> Unpaid
                </button>
              </div>
              <transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                <div v-if="localForm.invoice_status === 'paid'" class="mt-3 bg-white dark:bg-gray-700 rounded-xl border border-green-200 dark:border-green-800 shadow-sm overflow-hidden">
                  <div class="flex items-start gap-3 p-3">
                    <div class="mt-0.5 flex-shrink-0 w-7 h-7 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                      <FileText class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <label class="block text-xs font-semibold text-green-700 dark:text-green-400 mb-1.5">Select Invoice</label>
                      <select v-model="localForm.invoice_id"
                        class="w-full px-3 py-2 border-0 bg-gray-50 dark:bg-gray-600 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500 transition-all duration-200 appearance-none">
                        <option value="">Select invoice</option>
                        <option v-for="inv in invoices" :key="inv.id" :value="String(inv.id)">{{ inv.invoice_number }} ({{ inv.status }})</option>
                      </select>
                    </div>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
          <div class="flex items-center gap-2 mb-3">
            <FileText class="w-4 h-4 text-blue-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Notes</h4>
            <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">Optional</span>
          </div>
          <textarea v-model="localForm.notes" rows="3" placeholder="Add any additional notes here..."
            class="w-full px-4 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-500 transition-all duration-200 resize-none"></textarea>
        </div>
      </div>
      <div class="flex-shrink-0 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
        <button @click="$emit('close')" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">Cancel</button>
        <button @click="$emit('save')" :disabled="isSaving" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30 active:scale-95">
          <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin" />
          <Save v-else class="w-4 h-4" />
          {{ isEditing ? 'Update Entry' : 'Add Entry' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  X, Hash, Package, AlertCircle, Loader2, Search, CheckCircle2, ClipboardList,
  Receipt, FileText, Save
} from 'lucide-vue-next'

interface ProductOption {
  id: string
  type: string
  name: string
  brand_id?: string
  brand_name?: string
  area?: number
  price?: number
}

interface FormData {
  ledger_id: number | null
  serial_number: string
  product_type: string
  product_id: string
  brand_id: string
  clinic_id: string
  order_id: string | number
  status: number
  is_used: boolean
  graft_usage_id: string
  invoice_status: string
  invoice_id: string
  notes: string
}

const props = defineProps<{
  modelValue: boolean
  isEditing: boolean
  formData: FormData
  formErrors: Record<string, string>
  clinics: any[]
  brands: any[]
  otherProducts: ProductOption[]
  graftSizeProducts: ProductOption[]
  invoices: any[]
  statusOptions: Record<number, string>
  orderSearchQuery: string
  orderSuggestions: any[]
  showOrderDropdown: boolean
  isSearchingOrders: boolean
  isSaving: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'close'): void
  (e: 'save'): void
  (e: 'update:formData', data: FormData): void
  (e: 'update:orderSearchQuery', value: string): void
  (e: 'searchOrders', query: string): void
  (e: 'selectOrder', order: any): void
  (e: 'clearOrder'): void
}>()

const localForm = ref<FormData>({ ...props.formData })

watch(() => props.formData, (newVal) => {
  localForm.value = { ...newVal }
}, { deep: true })

watch(localForm, (newVal) => {
  emit('update:formData', { ...newVal })
}, { deep: true })

const orderSearchQuery = ref(props.orderSearchQuery)

watch(() => props.orderSearchQuery, (newVal) => {
  orderSearchQuery.value = newVal
})

watch(orderSearchQuery, (newVal) => {
  emit('update:orderSearchQuery', newVal)
})

const filteredGraftProducts = computed(() => {
  if (!localForm.value.brand_id) return []
  return props.graftSizeProducts.filter((prod) => String(prod.brand_id) === String(localForm.value.brand_id))
})

function onProductTypeChange() {
  localForm.value.product_id = ''
  localForm.value.brand_id = ''
  localForm.value.clinic_id = ''
}

function selectOrder(order: any) {
  localForm.value.order_id = order.id
  orderSearchQuery.value = order.display
  emit('selectOrder', order)
}

function clearOrder() {
  localForm.value.order_id = ''
  orderSearchQuery.value = ''
  emit('clearOrder')
}

function hideOrderDropdown() {
  setTimeout(() => {
    emit('update:showOrderDropdown', false)
  }, 200)
}

function onOrderInputFocus() {
  if (props.orderSuggestions.length > 0) {
    emit('update:showOrderDropdown', true)
  }
}

function getStatusPillActiveClass(status: number): string {
  const c: Record<number, string> = {
    0: 'bg-gray-500 border-gray-500 text-white shadow-md shadow-gray-200/50 dark:shadow-gray-900/30 scale-[1.02]',
    1: 'bg-blue-500 border-blue-500 text-white shadow-md shadow-blue-200/50 dark:shadow-blue-900/30 scale-[1.02]',
    2: 'bg-green-500 border-green-500 text-white shadow-md shadow-green-200/50 dark:shadow-green-900/30 scale-[1.02]',
    3: 'bg-amber-500 border-amber-500 text-white shadow-md shadow-amber-200/50 dark:shadow-amber-900/30 scale-[1.02]',
    4: 'bg-purple-500 border-purple-500 text-white shadow-md shadow-purple-200/50 dark:shadow-purple-900/30 scale-[1.02]',
    5: 'bg-gray-500 border-gray-500 text-white shadow-md shadow-gray-200/50 dark:shadow-gray-900/30 scale-[1.02]',
    6: 'bg-red-500 border-red-500 text-white shadow-md shadow-red-200/50 dark:shadow-red-900/30 scale-[1.02]',
  }
  return c[status] || c[1]
}

function getStatusPillInactiveClass(status: number): string {
  const c: Record<number, string> = {
    0: 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:border-gray-300 dark:hover:border-gray-500',
    1: 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700',
    2: 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-700',
    3: 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/20 hover:border-amber-300 dark:hover:border-amber-700',
    4: 'bg-purple-50 dark:bg-purple-900/10 border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-700',
    5: 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:border-gray-300 dark:hover:border-gray-500',
    6: 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-700',
  }
  return c[status] || c[1]
}

function getStatusDotColor(status: number): string {
  const c: Record<number, string> = {
    0: '#9ca3af', 1: '#3b82f6', 2: '#22c55e', 3: '#f59e0b', 4: '#a855f7', 5: '#9ca3af', 6: '#ef4444',
  }
  return c[status] || c[1]
}
</script>
