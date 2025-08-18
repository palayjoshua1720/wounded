<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice & Payment Tracking</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage invoices and track payment status</p>
      </div>
      <div class="flex gap-2">
        <button
          class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          @click="showUploadModal = true"
        >
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Upload Invoice
        </button>
        <button
          class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          @click="showManualModal = true"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Invoice Manually
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Amount</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ totalAmount.toLocaleString() }}</p>
          </div>
          <BanknotesIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Paid</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ paidAmount.toLocaleString() }}</p>
          </div>
          <CheckCircleIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ pendingAmount.toLocaleString() }}</p>
          </div>
          <ClockIcon class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Overdue</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ overdueAmount.toLocaleString() }}</p>
          </div>
          <ExclamationTriangleIcon class="w-8 h-8 text-red-600 dark:text-red-400" />
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="flex-1">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
          <input
            type="text"
            placeholder="Search invoices..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            v-model="searchTerm"
          />
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <select
          v-model="statusFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
          <option value="overdue">Overdue</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <select
          v-model="clinicFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="all">All Clinics</option>
          <option v-for="clinic in uniqueClinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
        </select>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Invoice Number
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Clinic
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Due Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ invoice.invoiceNumber }}</div>
                <div v-if="invoice.orderId" class="text-sm text-gray-500 dark:text-gray-400">Order: {{ invoice.orderId }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ invoice.clinicName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">${{ invoice.amount.toLocaleString() }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(invoice.status)}`">
                  <component :is="getStatusIcon(invoice.status)" class="w-4 h-4" />
                  <span class="ml-1 capitalize">{{ invoice.status }}</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(invoice.dueDate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="selectedInvoice = invoice"
                  class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  View
                </button>
                <div class="inline-flex space-x-1">
                  <button
                    v-if="invoice.status === 'pending'"
                    @click="handleStatusUpdate(invoice.id, 'paid')"
                    class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
                  >
                    Mark Paid
                  </button>
                  <button
                    v-if="invoice.status === 'overdue'"
                    @click="handleStatusUpdate(invoice.id, 'paid')"
                    class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
                  >
                    Mark Paid
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Upload Modal -->
  <BaseModal v-model="showUploadModal" title="Upload Invoice & Payment Files">
    <div class="space-y-6">
      <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
        <DocumentTextIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Invoice PDF</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          Upload PDF invoices for OCR extraction and processing
        </p>
        <input
          type="file"
          accept=".pdf"
          class="hidden"
          id="invoice-upload"
        />
        <label
          for="invoice-upload"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer"
        >
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Choose PDF File
        </label>
      </div>
      <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <h4 class="font-medium text-blue-900 dark:text-blue-400 mb-2">OCR will extract:</h4>
        <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1">
          <li>• Invoice number and date</li>
          <li>• Clinic information</li>
          <li>• Amount and line items</li>
          <li>• Serial numbers (if present)</li>
          <li>• Due date and payment terms</li>
        </ul>
      </div>
      <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
        <CalendarIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Payment Status</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          Upload Google Sheet or CSV file to sync payment status
        </p>
        <input
          type="file"
          accept=".csv,.xlsx,.xls"
          class="hidden"
          id="payment-upload"
        />
        <label
          for="payment-upload"
          class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 cursor-pointer"
        >
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Choose Payment File
        </label>
      </div>
    </div>
    <template #actions>
      <button
        @click="showUploadModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Cancel
      </button>
      <button
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        Process Files
      </button>
    </template>
  </BaseModal>

  <!-- Invoice Details Modal -->
  <BaseModal v-model="showInvoiceModal" title="Invoice Details">
    <div v-if="selectedInvoice" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Number</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.invoiceNumber }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(selectedInvoice.status)}`">
            <component :is="getStatusIcon(selectedInvoice.status)" class="w-4 h-4" />
            <span class="ml-1 capitalize">{{ selectedInvoice.status }}</span>
          </span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.clinicName }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">${{ selectedInvoice.amount.toLocaleString() }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedInvoice.dueDate) }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Paid Date</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">
            {{ selectedInvoice.paidDate ? formatDate(selectedInvoice.paidDate) : 'Not paid' }}
          </p>
        </div>
      </div>

      <div v-if="selectedInvoice.orderId">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Related Order</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.orderId }}</p>
      </div>

      <div v-if="selectedInvoice.serials && selectedInvoice.serials.length > 0">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Numbers</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.serials.join(', ') }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDateTime(selectedInvoice.createdAt) }}</p>
      </div>
    </div>
    <template #actions>
      <button
        @click="showInvoiceModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Close
      </button>
    </template>
  </BaseModal>

  <!-- Add Invoice Manually Modal -->
  <BaseModal v-model="showManualModal" title="Add Invoice Manually">
    <form @submit.prevent="handleManualInvoiceSubmit" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Number <span class="text-red-500">*</span></label>
          <input v-model="manualInvoice.invoiceNumber" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic <span class="text-red-500">*</span></label>
          <select v-model="manualInvoice.clinicId" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Select Clinic</option>
            <option v-for="clinic in uniqueClinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date <span class="text-red-500">*</span></label>
          <input v-model="manualInvoice.date" type="date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount <span class="text-red-500">*</span></label>
          <input v-model.number="manualInvoice.amount" type="number" min="0" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
          <select v-model="manualInvoice.status" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
          <input v-model="manualInvoice.dueDate" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order ID</label>
          <input v-model="manualInvoice.orderId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Enter order ID (optional)" />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Graft Serial Numbers</label>
        <div v-for="(serial, idx) in manualInvoice.serialNumbers" :key="idx" class="flex items-center gap-2 mb-2">
          <input v-model="manualInvoice.serialNumbers[idx]" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Enter serial number" />
          <button type="button" @click="removeSerial(idx)" class="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 rounded-lg p-2"><MinusIcon class="w-4 h-4" /></button>
        </div>
        <button type="button" @click="addSerial" class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40 text-xs">Add Serial</button>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" @click="showManualModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Add Invoice</button>
      </div>
    </form>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { ArrowUpTrayIcon, MagnifyingGlassIcon, FunnelIcon, BanknotesIcon, CheckCircleIcon, ExclamationTriangleIcon, ClockIcon, DocumentTextIcon, CalendarIcon, PlusIcon, MinusIcon } from '@heroicons/vue/24/outline'

interface Invoice {
  id: string
  clinicId: string
  clinicName: string
  invoiceNumber: string
  amount: number
  status: 'pending' | 'paid' | 'overdue' | 'cancelled'
  dueDate: string
  paidDate?: string
  orderId?: string
  serials: string[]
  createdAt: string
}

const mockInvoices: Invoice[] = [
  {
    id: '1',
    clinicId: '1',
    clinicName: "St. Mary's Hospital",
    invoiceNumber: 'INV-2025-001',
    amount: 2500.00,
    status: 'paid',
    dueDate: '2025-02-15',
    paidDate: '2025-01-25',
    orderId: 'ORD-001',
    serials: ['GM001', 'GM002'],
    createdAt: '2025-01-15T10:00:00Z'
  },
  {
    id: '2',
    clinicId: '2',
    clinicName: 'General Health Center',
    invoiceNumber: 'INV-2025-002',
    amount: 1800.00,
    status: 'pending',
    dueDate: '2025-02-20',
    orderId: 'ORD-002',
    serials: ['WC003'],
    createdAt: '2025-01-20T14:30:00Z'
  },
  {
    id: '3',
    clinicId: '1',
    clinicName: "St. Mary's Hospital",
    invoiceNumber: 'INV-2025-003',
    amount: 3200.00,
    status: 'overdue',
    dueDate: '2025-01-10',
    orderId: 'ORD-003',
    serials: ['SG004', 'SG005'],
    createdAt: '2025-01-01T09:00:00Z'
  },
  {
    id: '4',
    clinicId: '3',
    clinicName: 'City Medical Center',
    invoiceNumber: 'INV-2025-004',
    amount: 4500.00,
    status: 'pending',
    dueDate: '2025-03-01',
    orderId: 'ORD-004',
    serials: ['AW006', 'AW007'],
    createdAt: '2025-01-25T16:45:00Z'
  },
  {
    id: '5',
    clinicId: '2',
    clinicName: 'General Health Center',
    invoiceNumber: 'INV-2025-005',
    amount: 1200.00,
    status: 'paid',
    dueDate: '2025-01-20',
    paidDate: '2025-01-18',
    orderId: 'ORD-005',
    serials: ['TR008'],
    createdAt: '2025-01-10T11:20:00Z'
  }
]

const invoices = ref<Invoice[]>([...mockInvoices])
const searchTerm = ref('')
const statusFilter = ref('all')
const clinicFilter = ref('all')
const showUploadModal = ref(false)
const showInvoiceModal = ref(false)
const selectedInvoice = ref<Invoice | null>(null)
const showManualModal = ref(false)
const manualInvoice = ref({
  invoiceNumber: '',
  clinicId: '',
  date: '',
  amount: 0,
  status: 'pending' as 'pending' | 'paid' | 'overdue' | 'cancelled',
  dueDate: '',
  serialNumbers: [''],
  orderId: ''
})

const uniqueClinics = computed(() => {
  const seen = new Map()
  for (const invoice of invoices.value) {
    if (!seen.has(invoice.clinicId)) {
      seen.set(invoice.clinicId, invoice.clinicName)
    }
  }
  return Array.from(seen, ([id, name]) => ({ id, name }))
})

const filteredInvoices = computed(() => {
  return invoices.value.filter(invoice => {
    const matchesSearch = invoice.invoiceNumber.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      invoice.clinicName.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus = statusFilter.value === 'all' || invoice.status === statusFilter.value
    const matchesClinic = clinicFilter.value === 'all' || invoice.clinicId === clinicFilter.value
    return matchesSearch && matchesStatus && matchesClinic
  })
})

const totalAmount = computed(() => invoices.value.reduce((sum, invoice) => sum + invoice.amount, 0))
const paidAmount = computed(() => invoices.value.filter(inv => inv.status === 'paid').reduce((sum, invoice) => sum + invoice.amount, 0))
const pendingAmount = computed(() => invoices.value.filter(inv => inv.status === 'pending').reduce((sum, invoice) => sum + invoice.amount, 0))
const overdueAmount = computed(() => invoices.value.filter(inv => inv.status === 'overdue').reduce((sum, invoice) => sum + invoice.amount, 0))

function getStatusColor(status: string) {
  switch (status) {
    case 'paid': return 'bg-green-100 text-green-800'
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'overdue': return 'bg-red-100 text-red-800'
    case 'cancelled': return 'bg-gray-100 text-gray-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

function getStatusIcon(status: string) {
  switch (status) {
    case 'paid': return CheckCircleIcon
    case 'pending': return ClockIcon
    case 'overdue': return ExclamationTriangleIcon
    default: return null
  }
}

function handleStatusUpdate(invoiceId: string, newStatus: Invoice['status']) {
  invoices.value = invoices.value.map(invoice => 
    invoice.id === invoiceId 
      ? { 
          ...invoice, 
          status: newStatus,
          paidDate: newStatus === 'paid' ? new Date().toISOString().split('T')[0] : undefined
        }
      : invoice
  )
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString()
}

function formatDateTime(dateTimeString: string) {
  return new Date(dateTimeString).toLocaleString()
}

function addSerial() {
  manualInvoice.value.serialNumbers.push('')
}
function removeSerial(idx: number) {
  if (manualInvoice.value.serialNumbers.length > 1) manualInvoice.value.serialNumbers.splice(idx, 1)
}
function handleManualInvoiceSubmit() {
  // Find clinic name
  const clinic = uniqueClinics.value.find((c: { id: string }) => c.id === manualInvoice.value.clinicId)
  invoices.value.unshift({
    id: `INV-${Math.floor(Math.random() * 100000)}`,
    invoiceNumber: manualInvoice.value.invoiceNumber,
    clinicId: manualInvoice.value.clinicId,
    clinicName: clinic ? clinic.name : '',
    createdAt: manualInvoice.value.date || new Date().toISOString(),
    amount: manualInvoice.value.amount,
    status: manualInvoice.value.status,
    dueDate: manualInvoice.value.dueDate,
    serials: manualInvoice.value.serialNumbers.filter(s => s.trim()),
    orderId: manualInvoice.value.orderId,
    paidDate: undefined
  })
  showManualModal.value = false
  manualInvoice.value = { invoiceNumber: '', clinicId: '', date: '', amount: 0, status: 'pending', dueDate: '', serialNumbers: [''], orderId: '' }
}

// Watch for selectedInvoice changes to show modal
import { watch } from 'vue'
watch(selectedInvoice, (newInvoice) => {
  showInvoiceModal.value = !!newInvoice
})
</script> 