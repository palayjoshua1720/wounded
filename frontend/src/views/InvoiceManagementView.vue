<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice & Payment Tracking</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage invoices, OCR extraction, and payment synchronization</p>
      </div>
      <div class="flex gap-2">
        <button
          class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          @click="showUploadModal = true" :disabled="loading">
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Upload Invoice PDF
        </button>
        <button
          class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          @click="showSheetSyncModal = true" :disabled="loading">
          <LinkIcon class="w-4 h-4 mr-2" />
          Sync Google Sheet
        </button>
        <button
          class="flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
          @click="showManualModal = true" :disabled="loading">
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Invoice
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && invoices.length === 0" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Stats Cards -->
    <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Invoices</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_invoices }}</p>
          </div>
          <DocumentTextIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Awaiting Review</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.pending_review }}</p>
          </div>
          <EyeIcon class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Payment</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.pending_payment }}</p>
          </div>
          <ClockIcon class="w-8 h-8 text-orange-600 dark:text-orange-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sync Required</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.sync_required }}</p>
          </div>
          <ArrowPathIcon class="w-8 h-8 text-red-600 dark:text-red-400" />
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div v-if="!loading"
      class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="flex-1">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
          <input type="text" placeholder="Search invoices, serial numbers, clinics..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            v-model="filters.search" @input="debouncedFetchInvoices" />
        </div>
      </div>
      <div class="flex items-center space-x-2 flex-wrap gap-2">
        <FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <select v-model="filters.status" @change="fetchInvoices"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="all">All Status</option>
          <option value="pending_review">Pending Review</option>
          <option value="pending">Pending Payment</option>
          <option value="paid">Paid</option>
          <option value="overdue">Overdue</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <select v-model="filters.clinic_id" @change="fetchInvoices"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="all">All Clinics</option>
          <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="clinic.clinic_id">
            {{ clinicDisplayName(clinic) }}
          </option>
        </select>
        <input type="date" v-model="filters.date_from" @change="fetchInvoices"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          placeholder="From Date" />
        <input type="date" v-model="filters.date_to" @change="fetchInvoices"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          placeholder="To Date" />
      </div>
    </div>

    <!-- Invoices Table -->
    <div v-if="!loading"
      class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Invoice Details
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Clinic & Order
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Amount
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Status
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Dates
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <div v-if="invoice.needs_review" class="flex-shrink-0">
                    <span
                      class="inline-flex items-center justify-center w-6 h-6 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                      <EyeIcon class="w-3 h-3" />
                    </span>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ invoice.invoice_number }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ invoice.serials?.length || 0 }} serials
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 dark:text-white">{{ invoice.clinic.clinic_name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ invoice.clinic.clinic_code }}</div>
                <div v-if="invoice.order_id" class="text-sm text-blue-600 dark:text-blue-400">Order: {{ invoice.order_id
                  }}</div>
                <div v-else class="text-sm text-gray-500 dark:text-gray-400">No order linked</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900 dark:text-white">${{ invoice.amount.toLocaleString() }}
                </div>
                <div v-if="invoice.partial_payment" class="text-sm text-orange-600 dark:text-orange-400">
                  Partial: ${{ invoice.paid_amount?.toLocaleString() }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-col space-y-1">
                  <span
                    :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(invoice.status)} w-fit`">
                    <component :is="getStatusIcon(invoice.status)" class="w-4 h-4" />
                    <span class="ml-1 capitalize">{{ invoice.status.replace('_', ' ') }}</span>
                  </span>
                  <span v-if="invoice.sync_status === 'out_of_sync'"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                    <ExclamationCircleIcon class="w-3 h-3 mr-1" />
                    Sync Required
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(invoice.invoice_date) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Due: {{ formatDate(invoice.due_date) }}</div>
                <div v-if="invoice.paid_date" class="text-sm text-green-600 dark:text-green-400">
                  Paid: {{ formatDate(invoice.paid_date) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button @click="viewInvoiceDetails(invoice)"
                  class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                  View
                </button>
                <button v-if="invoice.needs_review" @click="reviewExtractedData(invoice)"
                  class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                  Review
                </button>
                <button v-if="!invoice.order_id && invoice.status !== 'cancelled'" @click="linkToOrder(invoice)"
                  class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                  Link Order
                </button>
                <button v-if="invoice.status === 'pending' || invoice.status === 'overdue'" @click="markAsPaid(invoice)"
                  class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                  Mark Paid
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700 dark:text-gray-400">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
          </div>
          <div class="flex space-x-2">
            <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
              class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
              Previous
            </button>
            <button @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && invoices.length === 0" class="text-center py-12">
      <DocumentTextIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No invoices found</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by uploading your first invoice PDF.</p>
      <button @click="showUploadModal = true"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
        Upload Invoice PDF
      </button>
    </div>
  </div>

  <!-- Upload Invoice PDF Modal -->
  <BaseModal v-model="showUploadModal" title="Upload Invoice PDF for OCR Processing" size="lg">
    <div class="space-y-6">
      <!-- File Upload -->
      <div
        class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-400 transition-colors"
        @drop="handleDrop" @dragover.prevent @dragenter.prevent>
        <DocumentTextIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Invoice PDF</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          Drag and drop your PDF file here, or click to browse
        </p>
        <input type="file" accept=".pdf" class="hidden" id="invoice-pdf-upload" @change="handlePdfUpload"
          ref="pdfInput" />
        <label for="invoice-pdf-upload"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer">
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Choose PDF Files
        </label>
      </div>

      <!-- Uploaded Files -->
      <div v-if="uploadedFiles.length > 0" class="space-y-2">
        <h4 class="font-medium text-gray-900 dark:text-white">Selected Files:</h4>
        <div v-for="(file, index) in uploadedFiles" :key="index"
          class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
          <div class="flex items-center space-x-3">
            <DocumentTextIcon class="w-5 h-5 text-gray-400" />
            <span class="text-sm text-gray-900 dark:text-white">{{ file.name }}</span>
          </div>
          <button @click="removeUploadedFile(index)" class="text-red-600 hover:text-red-800">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Processing Options -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Invoice Grouping</label>
          <select v-model="uploadOptions.grouping"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="auto">Auto-group multi-page invoices</option>
            <option value="manual">Process each page separately</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">OCR Confidence</label>
          <select v-model="uploadOptions.confidence"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="high">High (Slower, more accurate)</option>
            <option value="standard">Standard (Balanced)</option>
            <option value="fast">Fast (Quick extraction)</option>
          </select>
        </div>
      </div>

      <!-- OCR Extraction Preview -->
      <div v-if="extractionPreview" class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">OCR Extraction Preview</h4>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Invoice Number:</span>
            <span class="font-medium">{{ extractionPreview.invoice_number }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Amount:</span>
            <span class="font-medium">${{ extractionPreview.amount?.toLocaleString() }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Invoice Date:</span>
            <span class="font-medium">{{ formatDate(extractionPreview.invoice_date) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Serials Found:</span>
            <span class="font-medium">{{ extractionPreview.serials?.length || 0 }}</span>
          </div>
        </div>
      </div>
    </div>
    <template #actions>
      <button @click="showUploadModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
        :disabled="uploading">
        Cancel
      </button>
      <button @click="processUploadedInvoices" :disabled="!hasFilesForProcessing || uploading"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
        <ArrowPathIcon v-if="uploading" class="w-4 h-4 mr-2 inline animate-spin" />
        <span v-else>Process {{ uploadedFiles.length }} File(s)</span>
      </button>
    </template>
  </BaseModal>

  <!-- Add Invoice Manually Modal -->
  <BaseModal v-model="showManualModal" title="Add Invoice Manually" size="lg">
    <form @submit.prevent="handleManualInvoiceSubmit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Invoice Number <span class="text-red-500">*</span>
          </label>
          <input v-model="manualInvoice.invoice_number" required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="e.g., INV-2024-001" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Clinic <span class="text-red-500">*</span>
          </label>
          <select v-model="manualInvoice.clinic_id" required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Select Clinic</option>
            <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="clinic.clinic_id">
              {{ clinicDisplayName(clinic) }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Invoice Date <span class="text-red-500">*</span>
          </label>
          <input v-model="manualInvoice.invoice_date" type="date" required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Due Date <span class="text-red-500">*</span>
          </label>
          <input v-model="manualInvoice.due_date" type="date" required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Amount <span class="text-red-500">*</span>
          </label>
          <input v-model.number="manualInvoice.amount" type="number" step="0.01" min="0" required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Status <span class="text-red-500">*</span>
          </label>
          <select v-model="manualInvoice.status" required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="pending_review">Pending Review</option>
            <option value="pending">Pending Payment</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Order ID
          </label>
          <input v-model="manualInvoice.order_id"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Enter order ID (optional)" />
        </div>
      </div>

      <!-- Serial Numbers -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Graft Serial Numbers
        </label>
        <div class="space-y-2">
          <div v-for="(serial, index) in manualInvoice.serials" :key="index" class="flex items-center space-x-2">
            <input v-model="manualInvoice.serials[index]"
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Enter serial number" />
            <button type="button" @click="removeSerial(index)"
              class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg">
              <MinusIcon class="w-4 h-4" />
            </button>
          </div>
          <button type="button" @click="addSerial"
            class="flex items-center px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40">
            <PlusIcon class="w-4 h-4 mr-1" />
            Add Serial
          </button>
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Notes
        </label>
        <textarea v-model="manualInvoice.notes" rows="3"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          placeholder="Additional notes (optional)"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <button type="button" @click="showManualModal = false"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button type="submit" :disabled="submitting"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
          <PlusIcon v-if="submitting" class="w-4 h-4 mr-2 inline animate-spin" />
          <span v-else>Add Invoice</span>
        </button>
      </div>
    </form>
  </BaseModal>

  <!-- Invoice Details Modal -->
  <BaseModal v-model="showInvoiceModal" title="Invoice Details" size="xl">
    <div v-if="selectedInvoice" class="space-y-6">
      <!-- Basic Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Number</label>
          <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ selectedInvoice.invoice_number }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <span
            :class="`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-1 ${getStatusColor(selectedInvoice.status)}`">
            <component :is="getStatusIcon(selectedInvoice.status)" class="w-4 h-4 mr-1" />
            <span class="capitalize">{{ selectedInvoice.status.replace('_', ' ') }}</span>
          </span>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.clinic.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
          <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">${{
            selectedInvoice.amount.toLocaleString()
          }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Date</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedInvoice.invoice_date) }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedInvoice.due_date) }}</p>
        </div>
        <div v-if="selectedInvoice.paid_date">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Paid Date</label>
          <p class="mt-1 text-sm text-green-600 dark:text-green-400">{{ formatDate(selectedInvoice.paid_date) }}</p>
        </div>
        <div v-if="selectedInvoice.order_id">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order ID</label>
          <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">{{ selectedInvoice.order_id }}</p>
        </div>
      </div>

      <!-- Serial Numbers -->
      <div v-if="selectedInvoice.serials && selectedInvoice.serials.length > 0">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Graft Serial Numbers</label>
        <div class="flex flex-wrap gap-2">
          <span v-for="(serial, index) in selectedInvoice.serials" :key="index"
            class="inline-flex items-center px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
            {{ serial }}
          </span>
        </div>
      </div>

      <!-- Payment Information -->
      <div v-if="selectedInvoice.status === 'paid'" class="border-t pt-4">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Payment Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Paid Amount</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">${{ selectedInvoice.paid_amount?.toLocaleString() ||
              selectedInvoice.amount.toLocaleString() }}</p>
          </div>
          <div v-if="selectedInvoice.payment_method">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.payment_method }}</p>
          </div>
          <div v-if="selectedInvoice.payment_reference">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Reference</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.payment_reference }}</p>
          </div>
          <div v-if="selectedInvoice.partial_payment">
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400">
              Partial Payment
            </span>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="selectedInvoice.notes">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
        <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ selectedInvoice.notes }}</p>
      </div>
    </div>
    <template #actions>
      <button @click="showInvoiceModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
        Close
      </button>
    </template>
  </BaseModal>

  <!-- Mark as Paid Modal -->
  <BaseModal v-model="showMarkPaidModal" title="Mark Invoice as Paid">
    <div v-if="invoiceToMarkPaid" class="space-y-4">
      <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <p class="text-sm text-blue-800 dark:text-blue-300">
          Marking <strong>{{ invoiceToMarkPaid.invoice_number }}</strong> as paid.
          Amount: <strong>${{ invoiceToMarkPaid.amount.toLocaleString() }}</strong>
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Paid Amount
          </label>
          <input v-model.number="paymentData.paid_amount" type="number" step="0.01" :max="invoiceToMarkPaid.amount"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Enter paid amount" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Payment Method
          </label>
          <select v-model="paymentData.payment_method"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Select Method</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="credit_card">Credit Card</option>
            <option value="check">Check</option>
            <option value="cash">Cash</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Payment Reference
          </label>
          <input v-model="paymentData.payment_reference" type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Transaction ID, check number, etc." />
        </div>
      </div>

      <div v-if="paymentData.paid_amount && paymentData.paid_amount < invoiceToMarkPaid.amount"
        class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg">
        <div class="flex items-center">
          <ExclamationTriangleIcon class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" />
          <span class="text-sm text-yellow-800 dark:text-yellow-300">
            This will be marked as a partial payment. Remaining:
            <strong>${{ (invoiceToMarkPaid.amount - paymentData.paid_amount).toLocaleString() }}</strong>
          </span>
        </div>
      </div>
    </div>
    <template #actions>
      <button @click="showMarkPaidModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
        Cancel
      </button>
      <button @click="confirmMarkAsPaid" :disabled="!paymentData.paid_amount"
        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
        Mark as Paid
      </button>
    </template>
  </BaseModal>

  <!-- Toast Notifications -->
  <div class="fixed top-4 right-4 space-y-2 z-50">
    <div v-for="toast in toasts" :key="toast.id" :class="`p-4 rounded-lg shadow-lg border-l-4 ${toast.type === 'success' ? 'bg-green-50 border-green-500 text-green-800 dark:bg-green-900/20 dark:text-green-300' :
      toast.type === 'error' ? 'bg-red-50 border-red-500 text-red-800 dark:bg-red-900/20 dark:text-red-300' :
        'bg-blue-50 border-blue-500 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'
      }`">
      <div class="flex items-center">
        <CheckCircleIcon v-if="toast.type === 'success'" class="w-5 h-5 mr-2" />
        <ExclamationTriangleIcon v-else class="w-5 h-5 mr-2" />
        <span class="font-medium">{{ toast.message }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import api from '@/services/api'
import {
  ArrowUpTrayIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  DocumentTextIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  PlusIcon,
  MinusIcon,
  LinkIcon,
  ArrowPathIcon,
  EyeIcon,
  TableCellsIcon,
  ExclamationCircleIcon,
  XMarkIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

// Updated Types for woundmed_clinics
interface Clinic {
  clinic_id: string
  clinic_code: string
  clinic_name: string
  email?: string
  contact_person?: string
  phone?: string
  address?: string
  clinic_status?: number
}

interface Invoice {
  id: string
  clinic_id: string
  clinic: Clinic
  invoice_number: string
  amount: number
  status: 'pending_review' | 'pending' | 'paid' | 'overdue' | 'cancelled'
  invoice_date: string
  due_date: string
  paid_date?: string
  order_id?: string
  serials: string[]
  notes?: string
  needs_review: boolean
  sync_status: 'synced' | 'out_of_sync'
  partial_payment: boolean
  paid_amount?: number
  payment_method?: string
  payment_reference?: string
  pdf_path?: string
  created_at: string
  updated_at: string
}

interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

interface Stats {
  total_invoices: number
  pending_review: number
  pending_payment: number
  sync_required: number
  total_amount: number
  paid_amount: number
  pending_amount: number
  overdue_amount: number
}

interface Toast {
  id: number
  type: 'success' | 'error' | 'info'
  message: string
}

// Reactive state
const invoices = ref<Invoice[]>([])
const clinics = ref<Clinic[]>([])
const loading = ref(false)
const uploading = ref(false)
const submitting = ref(false)
const pagination = ref<Pagination | null>(null)
const stats = ref<Stats>({
  total_invoices: 0,
  pending_review: 0,
  pending_payment: 0,
  sync_required: 0,
  total_amount: 0,
  paid_amount: 0,
  pending_amount: 0,
  overdue_amount: 0
})

// Filters
const filters = ref({
  search: '',
  status: 'all',
  clinic_id: 'all',
  date_from: '',
  date_to: '',
  page: 1
})

// Modal states
const showUploadModal = ref(false)
const showSheetSyncModal = ref(false)
const showManualModal = ref(false)
const showInvoiceModal = ref(false)
const showMarkPaidModal = ref(false)
const showReviewModal = ref(false)

// Selected data
const selectedInvoice = ref<Invoice | null>(null)
const invoiceUnderReview = ref<Invoice | null>(null)
const invoiceToMarkPaid = ref<Invoice | null>(null)

// Upload state
const uploadedFiles = ref<File[]>([])
const pdfInput = ref<HTMLInputElement | null>(null)
const uploadOptions = ref({
  grouping: 'auto',
  confidence: 'standard'
})
const extractionPreview = ref<any>(null)

// Manual invoice form
const manualInvoice = ref({
  invoice_number: '',
  clinic_id: '',
  invoice_date: '',
  due_date: '',
  amount: 0,
  status: 'pending_review' as Invoice['status'],
  order_id: '',
  serials: [''],
  notes: ''
})

// Payment data
const paymentData = ref({
  paid_amount: 0,
  payment_method: '',
  payment_reference: ''
})

// Notifications
const toasts = ref<Toast[]>([])

// Computed properties
const hasFilesForProcessing = computed(() => uploadedFiles.value.length > 0)

// Format clinic display name for dropdowns
const clinicDisplayName = (clinic: Clinic) => {
  return `${clinic.clinic_name} (${clinic.clinic_code})`;
}

// Methods
function showToast(message: string, type: Toast['type'] = 'info') {
  const id = Date.now()
  toasts.value.push({ id, type, message })
  setTimeout(() => {
    toasts.value = toasts.value.filter(toast => toast.id !== id)
  }, 5000)
}

async function fetchInvoices() {
  loading.value = true
  try {
    const params = new URLSearchParams()

    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.status !== 'all') params.append('status', filters.value.status)
    if (filters.value.clinic_id !== 'all') params.append('clinic_id', filters.value.clinic_id)
    if (filters.value.date_from) params.append('date_from', filters.value.date_from)
    if (filters.value.date_to) params.append('date_to', filters.value.date_to)
    params.append('page', filters.value.page.toString())

    const response = await api.get(`/invoices`, { params })
    const data = response.data
    invoices.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total,
      from: data.from,
      to: data.to
    }
  } catch (error) {
    console.error('Error fetching invoices:', error)
    showToast('Failed to load invoices', 'error')
  } finally {
    loading.value = false
  }
}

async function fetchStats() {
  try {
    const response = await api.get('/invoices/stats')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

async function fetchClinics() {
  try {
    const response = await api.get('/invoices/clinics')
    clinics.value = response.data

    // If we have clinics, set the first one as default for manual invoice
    if (clinics.value.length > 0 && !manualInvoice.value.clinic_id) {
      manualInvoice.value.clinic_id = clinics.value[0].clinic_id.toString()
    }
  } catch (error) {
    console.error('Error fetching clinics:', error)
    showToast('Failed to load clinics', 'error')
  }
}

function getStatusColor(status: string) {
  switch (status) {
    case 'paid': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    case 'pending_review': return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
    case 'overdue': return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'cancelled': return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

function getStatusIcon(status: string) {
  switch (status) {
    case 'paid': return CheckCircleIcon
    case 'pending': return ClockIcon
    case 'pending_review': return EyeIcon
    case 'overdue': return ExclamationTriangleIcon
    default: return DocumentTextIcon
  }
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Debounced search
// let searchTimeout: NodeJS.Timeout
// function debouncedFetchInvoices() {
//   clearTimeout(searchTimeout)
//   searchTimeout = setTimeout(() => {
//     filters.value.page = 1
//     fetchInvoices()
//   }, 500)
// }

function changePage(page: number) {
  if (page < 1 || page > (pagination.value?.last_page || 1)) return
  filters.value.page = page
  fetchInvoices()
}

function handlePdfUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files) {
    uploadedFiles.value = Array.from(target.files)
    // Simulate OCR extraction preview for the first file
    if (uploadedFiles.value.length > 0) {
      simulateOcrExtraction(uploadedFiles.value[0])
    }
  }
}

function handleDrop(event: DragEvent) {
  event.preventDefault()
  if (event.dataTransfer?.files) {
    uploadedFiles.value = Array.from(event.dataTransfer.files).filter(file =>
      file.type === 'application/pdf'
    )
    if (uploadedFiles.value.length > 0) {
      simulateOcrExtraction(uploadedFiles.value[0])
    }
  }
}

function removeUploadedFile(index: number) {
  uploadedFiles.value.splice(index, 1)
  if (uploadedFiles.value.length === 0) {
    extractionPreview.value = null
  }
}

function simulateOcrExtraction(file: File) {
  // In a real application, this would call your OCR service
  extractionPreview.value = {
    invoice_number: `INV-${new Date().getFullYear()}-${Math.floor(Math.random() * 1000).toString().padStart(3, '0')}`,
    amount: Math.floor(Math.random() * 5000) + 1000,
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    serials: Array.from({ length: Math.floor(Math.random() * 3) + 1 }, () =>
      `GS${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`
    )
  }
}

async function processUploadedInvoices() {
  uploading.value = true
  try {
    for (const file of uploadedFiles.value) {
      const formData = new FormData()
      formData.append('pdf_file', file)

      const response = await api.post('/invoices/upload-pdf', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })

      const result = response.data

      // Create invoice from extracted data using first available clinic
      if (clinics.value.length > 0) {
        await createInvoiceFromExtraction(result.extracted_data, clinics.value[0].clinic_id)
      } else {
        throw new Error('No clinics available to assign invoice')
      }
    }

    showToast('PDFs processed successfully', 'success')
    showUploadModal.value = false
    uploadedFiles.value = []
    extractionPreview.value = null
    fetchInvoices()
    fetchStats()
  } catch (error) {
    console.error('Error processing PDFs:', error)
    showToast(error instanceof Error ? error.message : 'Failed to process PDFs', 'error')
  } finally {
    uploading.value = false
  }
}

async function createInvoiceFromExtraction(extractedData: any, clinicId: string) {
  const invoiceData = {
    invoice_number: extractedData.invoice_number,
    clinic_id: clinicId,
    amount: extractedData.amount,
    invoice_date: extractedData.invoice_date,
    due_date: extractedData.due_date,
    status: 'pending_review' as const,
    serials: extractedData.serials || [],
    notes: 'Created from PDF upload'
  }

  const response = await api.post('/invoices', invoiceData)
}

async function handleManualInvoiceSubmit() {
  submitting.value = true
  try {
    // Filter out empty serials
    const serials = manualInvoice.value.serials.filter(serial => serial.trim() !== '')

    const invoiceData = {
      ...manualInvoice.value,
      serials,
      clinic_id: manualInvoice.value.clinic_id || undefined,
      order_id: manualInvoice.value.order_id || undefined,
      notes: manualInvoice.value.notes || undefined
    }

    const response = await api.post('/invoices', invoiceData)

    showToast('Invoice created successfully', 'success')
    showManualModal.value = false
    resetManualInvoiceForm()
    fetchInvoices()
    fetchStats()
  } catch (error) {
    console.error('Error creating invoice:', error)
    showToast(error instanceof Error ? error.message : 'Failed to create invoice', 'error')
  } finally {
    submitting.value = false
  }
}

function resetManualInvoiceForm() {
  manualInvoice.value = {
    invoice_number: '',
    clinic_id: clinics.value.length > 0 ? clinics.value[0].clinic_id.toString() : '',
    invoice_date: '',
    due_date: '',
    amount: 0,
    status: 'pending_review',
    order_id: '',
    serials: [''],
    notes: ''
  }
}

function addSerial() {
  manualInvoice.value.serials.push('')
}

function removeSerial(index: number) {
  if (manualInvoice.value.serials.length > 1) {
    manualInvoice.value.serials.splice(index, 1)
  }
}

function viewInvoiceDetails(invoice: Invoice) {
  selectedInvoice.value = invoice
  showInvoiceModal.value = true
}

function markAsPaid(invoice: Invoice) {
  invoiceToMarkPaid.value = invoice
  paymentData.value = {
    paid_amount: invoice.amount,
    payment_method: '',
    payment_reference: ''
  }
  showMarkPaidModal.value = true
}

async function confirmMarkAsPaid() {
  if (!invoiceToMarkPaid.value || !paymentData.value.paid_amount) return

  try {
    const response = await api.post(`/invoices/${invoiceToMarkPaid.value.id}/status`, {
      status: 'paid',
      paid_amount: paymentData.value.paid_amount,
      payment_method: paymentData.value.payment_method,
      payment_reference: paymentData.value.payment_reference
    })

    showToast('Invoice marked as paid', 'success')
    showMarkPaidModal.value = false
    invoiceToMarkPaid.value = null
    fetchInvoices()
    fetchStats()
  } catch (error) {
    console.error('Error marking invoice as paid:', error)
    showToast(error instanceof Error ? error.message : 'Failed to mark invoice as paid', 'error')
  }
}

function reviewExtractedData(invoice: Invoice) {
  invoiceUnderReview.value = JSON.parse(JSON.stringify(invoice))
  showReviewModal.value = true
}

function linkToOrder(invoice: Invoice) {
  selectedInvoice.value = invoice
  showReviewModal.value = true
}

// Lifecycle
onMounted(() => {
  fetchClinics().then(() => {
    fetchInvoices()
    fetchStats()
  })
})
</script>