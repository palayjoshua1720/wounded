<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice & Payment Tracking</h1>
        <!-- <p class="text-gray-600 dark:text-gray-400">Manage invoices, OCR extraction, and payment synchronization</p> -->
      </div>
      <div class="flex gap-2">
        <button
          class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group"
          @click="showAddInvoiceModal = true" :disabled="loading">
          <FileUp class="w-4 h-4 mr-2" />
          Add Invoice
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <div class="flex flex-col lg:flex-row gap-6">
        <div class="flex-1">
          <div class="relative">
            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
            <input type="text" placeholder="Search invoices, serial numbers, clinics..."
              class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
              v-model="filters.search" @input="updateFiltersAndFetch" />
          </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="relative">
            <Filter class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="filters.clinic_id" @change="updateFiltersAndFetch"
              class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
              <option value="all">All Clinics</option>
              <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="clinic.clinic_id">
                {{ clinicDisplayName(clinic) }}
              </option>
            </select>
            <ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
          <input type="date" v-model="filters.date_from" @change="updateFiltersAndFetch"
            class="px-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
            placeholder="From Date" />
          <input type="date" v-model="filters.date_to" @change="updateFiltersAndFetch"
            class="px-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
            placeholder="To Date" />
          <button type="button" @click="resetFilters"
            class="inline-flex items-center px-4 py-3.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 border-0 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
            <X class="w-4 h-4 mr-2" />Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Invoice Details
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Invoice Dates
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <TableLoader v-if="loading && invoices.length === 0" :colspan="4" />
            <tr v-else-if="invoices.length > 0" v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <FileText class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ invoice.invoice_number }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ invoice.serials?.length || 0 }} serials</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(invoice.amount) }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(invoice.invoice_date) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Due: {{ formatDateOnly(invoice.due_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2 text-center">
                <div class="flex justify-center space-x-2">
                  <button @click="viewInvoiceDetails(invoice)"
                    class="text-gray-600 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    <Eye class="w-4 h-4" />
                  </button>
                  <button @click="editInvoice(invoice)"
                    class="text-gray-600 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button v-if="isAdmin" @click="confirmDeleteInvoice(invoice)"
                    class="text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-else-if="!loading && invoices.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                <FileText class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-500" />
                <div class="flex flex-col items-center justify-center gap-2">
                  <span class="text-gray-400">No invoices found.</span>
                </div>
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
  </div>

  <!-- Upload Invoice PDF Modal -->
  <BaseModal v-model="showUploadModal" title="Upload Invoice PDF for OCR Processing" size="lg">
    <div class="space-y-6">
      <!-- File Upload -->
      <div
        class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-400 transition-colors"
        @drop="handleDrop" @dragover.prevent @dragenter.prevent>
        <FileText class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Invoice PDF</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          Drag and drop your PDF file here, or click to browse
        </p>
        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-4">
          <p class="text-xs text-blue-800 dark:text-blue-300">
            <strong>Tip:</strong> For best results, upload clear PDF invoices with readable text. Scanned images may not extract properly.
            If extraction fails, you'll be able to enter details manually.
          </p>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg mb-4 border border-yellow-200 dark:border-yellow-800">
          <p class="text-xs text-yellow-800 dark:text-yellow-300">
            <strong>Note:</strong> Only PDF files are accepted. Other file formats (DOC, DOCX, etc.) will not be processed.
          </p>
        </div>
        <input type="file" accept=".pdf" class="hidden" id="invoice-pdf-upload" @change="handlePdfUpload" ref="pdfInput" />
        <label for="invoice-pdf-upload"
          class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg cursor-pointer">
          <Upload class="w-4 h-4 mr-2" />
          Choose PDF Files
        </label>
      </div>

      <!-- Uploaded Files -->
      <div v-if="uploadedFiles.length > 0" class="space-y-2">
        <h4 class="font-medium text-gray-900 dark:text-white">Selected Files:</h4>
        <div v-for="(file, index) in uploadedFiles" :key="index"
          class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
          <div class="flex items-center space-x-3">
            <FileText class="w-5 h-5 text-gray-400" />
            <span class="text-sm text-gray-900 dark:text-white">{{ file.name }}</span>
          </div>
          <button @click="removeUploadedFile(index)" class="text-red-600 hover:text-red-800">
            <X class="w-5 h-5" />
          </button>
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
            <span class="text-gray-600 dark:text-gray-400">Due Date:</span>
            <span class="font-medium">{{ formatDate(extractionPreview.due_date) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Vendor:</span>
            <span class="font-medium">{{ extractionPreview.vendor || 'Not found' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Bill To:</span>
            <span class="font-medium">{{ extractionPreview.bill_to || 'Not found' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Serials Found:</span>
            <span class="font-medium">{{ extractionPreview.serials?.length || 0 }}</span>
          </div>
          <div v-if="extractionPreview.line_items && extractionPreview.line_items.length > 0" class="mt-3">
            <h5 class="font-medium text-gray-900 dark:text-white mb-2">Line Items ({{ extractionPreview.line_items.length }}):</h5>
            <div class="max-h-32 overflow-y-auto">
              <div v-for="(item, index) in extractionPreview.line_items" :key="index"
                class="flex justify-between text-xs py-1 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400 truncate mr-2">{{ item.description }}</span>
                <span class="font-medium">{{ formatCurrency(item.amount) }}</span>
              </div>
            </div>
          </div>
          <div v-if="extractionPreview.notes" class="mt-2">
            <h5 class="font-medium text-gray-900 dark:text-white mb-1">Notes:</h5>
            <p class="text-gray-600 dark:text-gray-400 text-xs">{{ extractionPreview.notes }}</p>
          </div>
          <div v-if="extractionPreview.payment_terms" class="mt-2">
            <h5 class="font-medium text-gray-900 dark:text-white mb-1">Payment Terms:</h5>
            <p class="text-gray-600 dark:text-gray-400 text-xs">{{ extractionPreview.payment_terms }}</p>
          </div>
        </div>
      </div>
    </div>
    <template #actions>
      <div class="p-5 flex gap-2">
        <button @click="showUploadModal = false; showAddInvoiceModal = true;"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          :disabled="uploading">
          Back
        </button>
        <button @click="processUploadedInvoices" :disabled="!hasFilesForProcessing || uploading"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
          <RefreshCw v-if="uploading" class="w-4 h-4 mr-2 inline animate-spin" />
          <span v-else>Process {{ uploadedFiles.length }} File(s)</span>
        </button>
      </div>
    </template>
  </BaseModal>

  <!-- Add Invoice Manually Modal -->
  <BaseModal v-model="showManualModal" title="Add Invoice Manually" size="lg">
    <form @submit.prevent="handleManualInvoiceSubmit" class="space-y-6">
      <!-- Invoice Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Number</label>
          <input v-model="manualInvoice.invoice_number"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Invoice Number" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clinic</label>
          <select v-model="manualInvoice.clinic_id"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Select Clinic</option>
            <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="clinic.clinic_id">
              {{ clinicDisplayName(clinic) }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
          <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
            {{ formatCurrency(calculateTotalAmount()) }}
          </div>
          <input v-model.number="manualInvoice.amount" type="hidden" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Date</label>
          <input v-model="manualInvoice.invoice_date" type="date"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
          <input v-model="manualInvoice.due_date" type="date"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bill To</label>
          <input v-model="manualInvoice.bill_to"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Bill To" />
        </div>
      </div>

      <!-- Line Items -->
      <div class="border-t pt-4">
        <div class="flex justify-between items-center mb-3">
          <h4 class="text-lg font-medium text-gray-900 dark:text-white">Line Items</h4>
          <button type="button" @click="addProduct"
            class="flex items-center px-2 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40">
            <Plus class="w-4 h-4 mr-1" />
            Add Item
          </button>
        </div>
        <div class="max-h-96 overflow-y-auto space-y-3">
          <div v-for="(product, index) in manualInvoice.products" :key="index"
            class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <input v-model="product.name"
                  class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                  placeholder="Product description" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Size</label>
                <input v-model="product.size"
                  class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                  placeholder="Product size (e.g. 2x2cm)" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Serial Number</label>
                <input v-model="product.serials[0]"
                  class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                  placeholder="Serial number" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                <input v-model.number="product.quantity" type="number" min="1"
                  class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                  placeholder="1" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                <input v-model.number="product.unit_price" type="number" step="0.01" min="0"
                  class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                  placeholder="0.00" />
              </div>
            </div>
            <div class="flex justify-end">
              <button type="button" @click="removeProduct(index)" v-if="manualInvoice.products.length > 1"
                class="text-red-600 hover:text-red-800 text-sm flex items-center">
                <Minus class="w-4 h-4 mr-1" />
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <button type="button" @click="showManualModal = false; showAddInvoiceModal = true;"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          Back
        </button>
        <button type="submit" :disabled="submitting"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
          <Plus v-if="submitting" class="w-4 h-4 mr-2 inline animate-spin" />
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
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.clinic.clinic_name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
          <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(selectedInvoice.amount) }}</p>
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
        <div v-if="selectedInvoice.bill_to">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bill To</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedInvoice.bill_to }}</p>
        </div>
      </div>

      <!-- Line Items -->
      <div class="border-t pt-4">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Product Details</h4>
        <div class="max-h-96 overflow-y-auto">
          <!-- Display line items if available -->
          <div v-if="selectedInvoice.has_line_items && selectedInvoice.line_items && selectedInvoice.line_items.length > 0">
            <div v-for="(item, index) in selectedInvoice.line_items" :key="index"
              class="py-2 border-b border-gray-200 dark:border-gray-700 text-sm">
              <div class="flex justify-between items-start">
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">{{ item.description }} <span v-if="item.size">({{ item.size }})</span></div>
                  <div v-if="item.serial" class="text-gray-600 dark:text-gray-400 text-xs">
                    S/N: {{ item.serial }}
                  </div>
                  <div class="text-gray-600 dark:text-gray-400 text-xs">{{ formatCurrency(item.amount) }} x {{ item.quantity || 1 }} = {{ formatCurrency(item.amount * (item.quantity || 1)) }}</div>
                </div>
                <button v-if="item.serial" @click="markIndividualSerialAsPaid(item.serial)" :disabled="paidSerials[item.serial as string]"
                  :class="getSerialPaymentButtonClass(item.serial)">
                  {{ paidSerials[item.serial as string] ? 'Paid' : 'Mark as Paid' }}
                </button>
              </div>
            </div>
          </div>
          <!-- Fallback to parsing notes if no line items -->
          <div v-else-if="selectedInvoice.notes && (selectedInvoice.notes.includes('Line Items:') || selectedInvoice.notes.includes('Line Items :'))">
            <div v-for="(product, index) in parseLineItems(selectedInvoice.notes)" :key="index"
              class="py-2 border-b border-gray-200 dark:border-gray-700 text-sm">
              <div class="font-medium text-gray-900 dark:text-white">{{ product.size ? product.name + ' ' + product.size : product.name }}</div>
              <div v-for="(serial, sIndex) in product.serials" :key="sIndex"
                class="flex justify-between items-start">
                <div class="text-gray-600 dark:text-gray-400 text-xs">
                  S/N: {{ serial }}
                </div>
                <button @click="markIndividualSerialAsPaid(serial)" :disabled="paidSerials[serial]"
                  :class="getSerialPaymentButtonClass(serial)">
                  {{ paidSerials[serial] ? 'Paid' : 'Mark as Paid' }}
                </button>
              </div>
              <div class="text-gray-600 dark:text-gray-400 text-xs">{{ formatCurrency(product.unit_price) }} x {{ product.quantity || 0 }} = {{ formatCurrency(product.total || (product.unit_price * product.quantity)) }}</div>
            </div>
          </div>
          <!-- Fallback to serials only -->
          <div v-else-if="selectedInvoice.serials && selectedInvoice.serials.length > 0">
            <div v-for="(serial, index) in selectedInvoice.serials" :key="index"
              class="py-2 border-b border-gray-200 dark:border-gray-700 text-sm">
              <div class="flex justify-between items-start">
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">Graft Product</div>
                  <div class="text-gray-600 dark:text-gray-400 text-xs">S/N: {{ serial }}</div>
                  <div class="text-gray-600 dark:text-gray-400 text-xs">{{ formatCurrency(0) }} x 1 = {{ formatCurrency(0) }}</div>
                </div>
                <button @click="markIndividualSerialAsPaid(serial)" :disabled="paidSerials[serial]"
                  :class="getSerialPaymentButtonClass(serial)">
                  {{ paidSerials[serial] ? 'Paid' : 'Mark as Paid' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <template #actions>
      <div class="flex justify-end w-full p-5">
        <button @click="showInvoiceModal = false"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          Close
        </button>
      </div>
    </template>
  </BaseModal>

  <!-- Add Invoice Selection Modal -->
  <BaseModal v-model="showAddInvoiceModal" title="Add Invoice">
    <div class="space-y-4">
      <p class="text-gray-600 dark:text-gray-400">Choose how you want to add the invoice:</p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <button @click="showUploadModal = true; showAddInvoiceModal = false"
          class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
          <Upload class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" />
          <span class="font-medium text-gray-900 dark:text-white">Upload PDF</span>
          <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">Extract data from invoice PDF</span>
        </button>

        <button @click="showManualModal = true; showAddInvoiceModal = false"
          class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-gray-700 transition-colors">
          <Plus class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" />
          <span class="font-medium text-gray-900 dark:text-white">Manual Entry</span>
          <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter invoice details manually</span>
        </button>
      </div>
    </div>

    <template #actions>
      <div class="flex justify-end w-full p-5">
        <button @click="showAddInvoiceModal = false; showManualModal = false; showUploadModal = false;"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          Cancel
        </button>
      </div>
    </template>
  </BaseModal>

  <!-- PDF Review Modal -->
  <BaseModal v-model="showPdfReviewModal" title="Review Extracted Invoice Data" size="xl">
    <div class="space-y-6">
      <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <p class="text-sm text-blue-800 dark:text-blue-300">
          Please review the extracted data from the PDF. You can edit any fields before saving.
        </p>
      </div>

      <div v-if="extractedInvoiceData" class="space-y-4">
        <!-- Invoice Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Number</label>
            <input v-model="extractedInvoiceData.invoice_number"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Invoice Number" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
            <input v-model.number="extractedInvoiceData.amount" type="number" step="0.01"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Amount" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Date</label>
            <input v-model="extractedInvoiceData.invoice_date" type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
            <input v-model="extractedInvoiceData.due_date" type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clinic</label>
            <select v-model="extractedInvoiceData.clinic_id"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Clinic</option>
              <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="clinic.clinic_id">
                {{ clinicDisplayName(clinic) }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bill To</label>
            <input v-model="extractedInvoiceData.bill_to"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Bill To" />
          </div>
        </div>

        <!-- Extraction Source Information -->
        <div v-if="extractedInvoiceData.source && extractedInvoiceData.source !== 'pdf'"
          class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
          <div class="flex items-center">
            <Info class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" />
            <span class="text-sm text-blue-800 dark:text-blue-300">
              <span v-if="extractedInvoiceData.source === 'fallback'">PDF extraction failed. Please manually review and enter all details.</span>
              <span v-else>Enhanced extraction used ({{ extractedInvoiceData.source }}).</span>
            </span>
          </div>
        </div>

        <!-- Line Items -->
        <div class="border-t pt-4">
          <div class="flex justify-between items-center mb-3">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white">Line Items</h4>
            <button @click="addLineItem"
              class="flex items-center px-2 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40">
              <Plus class="w-4 h-4 mr-1" />
              Add Item
            </button>
          </div>
          <div class="max-h-96 overflow-y-auto space-y-3">
            <div v-for="(item, index) in extractedInvoiceData.line_items" :key="index"
              class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-2">
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                  <input v-model="item.description"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="Product description" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Size</label>
                  <input v-model="item.size"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="Product size (e.g. 2x2cm)" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Serial Number</label>
                  <input v-model="item.serial"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="Serial number" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                  <input v-model.number="item.quantity" type="number" min="1"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="1" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                  <input v-model.number="item.amount" type="number" step="0.01" min="0"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="0.00" />
                </div>
              </div>
              <div class="flex justify-end">
                <button @click="removeLineItem(index)" class="text-red-600 hover:text-red-800 text-sm flex items-center">
                  <Minus class="w-4 h-4 mr-1" />
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #actions>
      <div class="flex gap-2 p-5">
        <button @click="showPdfReviewModal = false"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button @click="resetLineItems" v-if="extractedInvoiceData && extractedInvoiceData.source === 'fallback'"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
          Clear & Add Manually
        </button>
        <button @click="saveReviewedInvoice"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
          Save Invoice
        </button>
      </div>
    </template>
  </BaseModal>

  <!-- Mark as Paid Modal -->
  <BaseModal v-model="showMarkPaidModal" title="Mark Invoice as Paid">
    <div v-if="invoiceToMarkPaid" class="space-y-4">
      <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <p class="text-sm text-blue-800 dark:text-blue-300">
          Marking <strong>{{ invoiceToMarkPaid.invoice_number }}</strong> as paid.
          Amount: <strong>{{ formatCurrency(invoiceToMarkPaid.amount) }}</strong>
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
          <AlertTriangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" />
          <span class="text-sm text-yellow-800 dark:text-yellow-300">
            This will be marked as a partial payment. Remaining:
            <strong>{{ formatCurrency(invoiceToMarkPaid.amount - paymentData.paid_amount) }}</strong>
          </span>
        </div>
      </div>
    </div>
    <template #actions>
      <div class="flex gap-2">
        <button @click="showMarkPaidModal = false"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button @click="confirmMarkAsPaid" :disabled="!paymentData.paid_amount"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
          Mark as Paid
        </button>
      </div>
    </template>
  </BaseModal>

  <!-- Edit Invoice Modal -->
  <BaseModal v-model="showEditModal" title="Edit Invoice" size="xl">
    <form @submit.prevent="handleEditInvoiceSubmit" class="space-y-6">
      <div v-if="invoiceToEdit" class="space-y-4">
        <!-- Invoice Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Number</label>
            <input v-model="invoiceToEdit.invoice_number"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Invoice Number" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clinic</label>
            <select v-model="invoiceToEdit.clinic_id"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Clinic</option>
              <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="clinic.clinic_id">
                {{ clinicDisplayName(clinic) }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (Calculated)</label>
            <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
              {{ formatCurrency(calculateEditTotalAmount()) }}
            </div>
            <input v-model.number="invoiceToEdit.amount" type="hidden" />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Automatically calculated from line items</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice Date</label>
            <input v-model="invoiceToEdit.invoice_date" type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
            <input v-model="invoiceToEdit.due_date" type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bill To</label>
            <input v-model="invoiceToEdit.bill_to"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Bill To" />
          </div>
        </div>

        <!-- Line Items -->
        <div class="border-t pt-4">
          <div class="flex justify-between items-center mb-3">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white">Line Items</h4>
            <button type="button" @click="addEditLineItem"
              class="flex items-center px-2 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40">
              <Plus class="w-4 h-4 mr-1" />
              Add Item
            </button>
          </div>
          <div class="max-h-96 overflow-y-auto space-y-3">
            <div v-for="(item, index) in invoiceToEdit.line_items" :key="index"
              class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-2">
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                  <input v-model="item.description"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="Product description" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Size</label>
                  <input v-model="item.size"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="Product size (e.g. 2x2cm)" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Serial Number</label>
                  <input v-model="item.serial"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="Serial number" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                  <input v-model.number="item.quantity" type="number" min="1"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="1" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                  <input v-model.number="item.amount" type="number" step="0.01" min="0"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    placeholder="0.00" />
                </div>
              </div>
              <div class="flex justify-end">
                <button type="button" @click="removeEditLineItem(index)"
                  class="text-red-600 hover:text-red-800 text-sm flex items-center">
                  <Minus class="w-4 h-4 mr-1" />
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <button type="button" @click="showEditModal = false"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button type="submit" :disabled="submitting || !hasInvoiceChanges"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
          <RefreshCw v-if="submitting" class="w-4 h-4 mr-2 inline animate-spin" />
          <span v-else>Update Invoice</span>
        </button>
      </div>
    </form>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import BaseModal from '@/components/common/BaseModal.vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import api from '@/services/api'
import axios from 'axios'
import { formatCurrency } from '@/utils/currency'
import Swal from 'sweetalert2'
import {
  Upload,
  Search,
  Filter,
  FileText,
  FileUp,
  Plus,
  Minus,
  RefreshCw,
  Eye,
  X,
  Info,
  Edit,
  Trash2,
  ChevronDown,
  AlertTriangle
} from 'lucide-vue-next'

// Types
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
  line_items: Array<{
    description: string
    size: string
    serial: string
    quantity: number
    amount: number
  }>
  has_line_items: boolean
  notes?: string
  bill_to?: string
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

interface Product {
  name: string
  size: string
  unit_price: number
  quantity: number
  serials: string[]
}

// Auth store
const authStore = useAuthStore()

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
  clinic_id: 'all',
  date_from: '',
  date_to: '',
  page: 1
})

// Modal states
const showAddInvoiceModal = ref(false)
const showUploadModal = ref(false)
const showManualModal = ref(false)
const showInvoiceModal = ref(false)
const showMarkPaidModal = ref(false)
const showReviewModal = ref(false)
const showPdfReviewModal = ref(false)
const showEditModal = ref(false)

// Selected data
const selectedInvoice = ref<Invoice | null>(null)
const invoiceUnderReview = ref<Invoice | null>(null)
const invoiceToMarkPaid = ref<Invoice | null>(null)
const extractedInvoiceData = ref<any>(null)
const invoiceToDelete = ref<Invoice | null>(null)
const invoiceToEdit = ref<Invoice | null>(null)
const originalInvoice = ref<Invoice | null>(null)
const paidSerials = ref<Record<string, boolean>>({})

// Upload state
const uploadedFiles = ref<File[]>([])
const pdfInput = ref<HTMLInputElement | null>(null)
const extractionPreview = ref<any>(null)

// Manual invoice form
const manualInvoice = ref({
  invoice_number: '',
  clinic_id: '',
  invoice_date: '',
  due_date: '',
  amount: 0,
  bill_to: '',
  products: [
    {
      name: '',
      size: '',
      unit_price: 0,
      quantity: 1,
      serials: ['']
    }
  ]
})

// Payment data
const paymentData = ref({
  paid_amount: 0,
  payment_method: '',
  payment_reference: ''
})

// Computed properties
const hasFilesForProcessing = computed(() => uploadedFiles.value.length > 0)

const hasInvoiceChanges = computed(() => {
  if (!invoiceToEdit.value || !originalInvoice.value) return false

  const normalizeDate = (dateStr: string): string => {
    if (!dateStr) return ''
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr
    try {
      const dateObj = new Date(dateStr)
      return dateObj.toISOString().split('T')[0]
    } catch {
      return dateStr
    }
  }

  if (invoiceToEdit.value.invoice_number !== originalInvoice.value.invoice_number) return true
  if (invoiceToEdit.value.clinic_id !== originalInvoice.value.clinic_id) return true
  if (Math.abs(invoiceToEdit.value.amount - originalInvoice.value.amount) > 0.01) return true
  if (normalizeDate(invoiceToEdit.value.invoice_date) !== normalizeDate(originalInvoice.value.invoice_date)) return true
  if (normalizeDate(invoiceToEdit.value.due_date) !== normalizeDate(originalInvoice.value.due_date)) return true
  if (invoiceToEdit.value.bill_to !== originalInvoice.value.bill_to) return true

  if (invoiceToEdit.value.line_items && originalInvoice.value.line_items) {
    if (invoiceToEdit.value.line_items.length !== originalInvoice.value.line_items.length) return true

    for (let i = 0; i < invoiceToEdit.value.line_items.length; i++) {
      const editedItem = invoiceToEdit.value.line_items[i]
      const originalItem = originalInvoice.value.line_items[i]

      if (editedItem.description !== originalItem.description) return true
      if (editedItem.size !== originalItem.size) return true
      if (editedItem.serial !== originalItem.serial) return true
      if (editedItem.quantity !== originalItem.quantity) return true
      if (Math.abs(editedItem.amount - originalItem.amount) > 0.01) return true
    }
  } else if (invoiceToEdit.value.line_items || originalInvoice.value.line_items) {
    return true
  }

  return false
})

const isAdmin = computed(() => {
  return authStore.currentUser?.user_role === 0
})

const clinicDisplayName = (clinic: Clinic) => {
  return `${clinic.clinic_name} (${clinic.clinic_code})`
}

// Methods
function showAlert(message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info', duration = 2000) {
  const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info'
  Swal.fire({
    text: message,
    icon: icon,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: duration,
    timerProgressBar: true
  })
}

async function fetchInvoices() {
  loading.value = true
  try {
    const params = new URLSearchParams()

    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.clinic_id !== 'all') params.append('clinic_id', filters.value.clinic_id)
    if (filters.value.date_from) params.append('date_from', filters.value.date_from)
    if (filters.value.date_to) params.append('date_to', filters.value.date_to)
    params.append('page', filters.value.page.toString())

    const response = await api.get(`/invoice-management`, { params })
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
    showAlert('Failed to load invoices', 'error')
  } finally {
    loading.value = false
  }
}

function updateFiltersAndFetch() {
  filters.value.page = 1
  fetchInvoices()
}

function resetFilters() {
  filters.value = {
    search: '',
    clinic_id: 'all',
    date_from: '',
    date_to: '',
    page: 1
  }
  fetchInvoices()
}

async function fetchStats() {
  try {
    const response = await api.get('/invoice-management/stats')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

async function fetchClinics() {
  try {
    const response = await api.get('/invoice-management/clinics')
    clinics.value = response.data
    if (clinics.value.length > 0 && !manualInvoice.value.clinic_id) {
      manualInvoice.value.clinic_id = clinics.value[0].clinic_id
    }
  } catch (error) {
    console.error('Error fetching clinics:', error)
    showAlert('Failed to load clinics', 'error')
  }
}

function formatDate(dateString: string) {
  const date = new Date(dateString)
  const options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }
  let datePart = date.toLocaleDateString('en-US', options)
  datePart = datePart.replace(/(\w+)\s+(\d+,\s+\d+)/, '$1. $2')
  
  const timePart = date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
  return `${datePart} [${timePart}]`
}

function formatDateOnly(dateString: string) {
  const date = new Date(dateString)
  const options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }
  let datePart = date.toLocaleDateString('en-US', options)
  datePart = datePart.replace(/(\w+)\s+(\d+,\s+\d+)/, '$1. $2')
  return datePart
}

function changePage(page: number) {
  if (page < 1 || page > (pagination.value?.last_page || 1)) return
  filters.value.page = page
  fetchInvoices()
}

function handlePdfUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files) {
    uploadedFiles.value = Array.from(target.files)
    extractionPreview.value = null
  }
}

function handleDrop(event: DragEvent) {
  event.preventDefault()
  if (event.dataTransfer?.files) {
    uploadedFiles.value = Array.from(event.dataTransfer.files)
    extractionPreview.value = null
  }
}

function removeUploadedFile(index: number) {
  uploadedFiles.value.splice(index, 1)
  if (uploadedFiles.value.length === 0) {
    extractionPreview.value = null
  }
}

function resetUploadState() {
  uploadedFiles.value = []
  extractionPreview.value = null
  if (pdfInput.value) {
    pdfInput.value.value = ''
  }
}

watch(showUploadModal, (isOpen) => {
  if (!isOpen) {
    resetUploadState()
  }
})

function editInvoice(invoice: Invoice) {
  originalInvoice.value = JSON.parse(JSON.stringify(invoice))
  invoiceToEdit.value = JSON.parse(JSON.stringify(invoice))

  if (invoiceToEdit.value && !invoiceToEdit.value.line_items) {
    invoiceToEdit.value.line_items = []
  }

  if (invoiceToEdit.value && invoiceToEdit.value.line_items) {
    invoiceToEdit.value.line_items = invoiceToEdit.value.line_items.map((item: any) => ({
      description: item.description || '',
      size: item.size || '',
      serial: item.serial || '',
      quantity: Number(item.quantity) || 1,
      amount: Number(item.amount) || 0
    }))
  }

  if (invoiceToEdit.value && invoiceToEdit.value.invoice_date) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(invoiceToEdit.value.invoice_date)) {
      const dateObj = new Date(invoiceToEdit.value.invoice_date)
      invoiceToEdit.value.invoice_date = dateObj.toISOString().split('T')[0]
    }
  }

  if (invoiceToEdit.value && invoiceToEdit.value.due_date) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(invoiceToEdit.value.due_date)) {
      const dateObj = new Date(invoiceToEdit.value.due_date)
      invoiceToEdit.value.due_date = dateObj.toISOString().split('T')[0]
    }
  }

  showEditModal.value = true
}

async function confirmDeleteInvoice(invoice: Invoice) {
  invoiceToDelete.value = invoice
  const result = await Swal.fire({
    title: 'Are you sure?',
    html: `Are you sure you want to delete invoice <strong>${invoice.invoice_number}</strong>? This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  })

  if (result.isConfirmed) {
    deleteInvoice()
  } else {
    invoiceToDelete.value = null
  }
}

async function handleEditInvoiceSubmit() {
  if (!invoiceToEdit.value) return

  const invoiceData = JSON.parse(JSON.stringify(invoiceToEdit.value))
  invoiceData.amount = calculateEditTotalAmount()
  invoiceData.serials = extractSerialsFromLineItems(invoiceData.line_items)
  invoiceData.has_line_items = invoiceData.line_items && invoiceData.line_items.length > 0

  submitting.value = true
  try {
    const response = await api.put(`/invoice-management/${invoiceToEdit.value.id}`, invoiceData)

    showAlert('Invoice updated successfully', 'success')
    showEditModal.value = false

    const index = invoices.value.findIndex(inv => inv.id === invoiceToEdit.value!.id)
    if (index !== -1) {
      invoices.value[index] = response.data.invoice
    }

    fetchStats()
    fetchInvoices()
  } catch (error: any) {
    console.error('Error updating invoice:', error)
    let errorMessage = 'Failed to update invoice'
    if (axios.isAxiosError(error)) {
      const status = error.response?.status
      const data = error.response?.data

      if (status === 422 && data?.errors) {
        const errors = data.errors
        if (errors.invoice_number) {
          errorMessage = `Invoice number error: ${errors.invoice_number[0]}`
        }
      } else {
        errorMessage = data?.message || `Request failed with status code ${status}`
      }
    } else if (error instanceof Error) {
      errorMessage = error.message
    }
    showAlert(errorMessage, 'error')
  } finally {
    submitting.value = false
  }
}

async function processUploadedInvoices() {
  if (uploadedFiles.value.length === 0) return
  
  uploading.value = true
  try {
    const file = uploadedFiles.value[0]
    const formData = new FormData()
    formData.append('pdf_file', file)

    const response = await api.post('/invoice-management/upload-pdf', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    const result = response.data
    extractedInvoiceData.value = result.extracted_data

    if (!extractedInvoiceData.value.line_items || extractedInvoiceData.value.line_items.length === 0) {
      if (extractedInvoiceData.value.serials && extractedInvoiceData.value.serials.length > 0) {
        extractedInvoiceData.value.line_items = extractedInvoiceData.value.serials.map((serial: string) => ({
          description: 'Graft Product',
          size: '',
          serial: serial,
          quantity: 1,
          amount: 0
        }))
      } else {
        extractedInvoiceData.value.line_items = [{
          description: '',
          size: '',
          serial: '',
          quantity: 1,
          amount: 0
        }]
      }
    } else {
      extractedInvoiceData.value.line_items = extractedInvoiceData.value.line_items.map((item: any) => ({
        description: item.description || '',
        size: item.size || '',
        serial: item.serial || '',
        quantity: item.quantity || 1,
        amount: item.amount || 0
      }))
    }

    if (extractedInvoiceData.value.source && extractedInvoiceData.value.source === 'fallback') {
      showAlert('PDF extraction partially failed. Please review and complete the details.', 'warning')
    }

    showPdfReviewModal.value = true
    showUploadModal.value = false
  } catch (error: any) {
    console.error('Error processing PDF:', error)
    let errorMessage = 'Failed to process PDF'
    if (axios.isAxiosError(error)) {
      const status = error.response?.status
      const data = error.response?.data

      if (status === 422 && data?.errors) {
        const errors = data.errors
        if (errors.invoice_number) {
          errorMessage = `Invoice already exists: ${errors.invoice_number[0]}`
        }
      } else if (status === 500) {
        errorMessage = 'Failed to extract data from PDF. The manual entry form will open for you to input the details.'
        setTimeout(() => {
          showPdfReviewModal.value = false
          showManualModal.value = true
        }, 2000)
      } else {
        errorMessage = data?.message || `Request failed with status code ${status}`
      }
    } else if (error instanceof Error) {
      errorMessage = error.message
    }
    showAlert(errorMessage, 'error')
  } finally {
    uploading.value = false
  }
}

async function saveReviewedInvoice() {
  if (!extractedInvoiceData.value || clinics.value.length === 0) return

  submitting.value = true
  try {
    let notes = 'Created from PDF upload'

    if (extractedInvoiceData.value.vendor) {
      notes += `\nVendor: ${extractedInvoiceData.value.vendor}`
    }

    if (extractedInvoiceData.value.notes) {
      notes += `\nNotes: ${extractedInvoiceData.value.notes}`
    }

    if (extractedInvoiceData.value.payment_terms) {
      notes += `\nPayment Terms: ${extractedInvoiceData.value.payment_terms}`
    }

    if (extractedInvoiceData.value.bill_to) {
      notes += `\nBill To: ${extractedInvoiceData.value.bill_to}`
    }
    
    const allSerials: string[] = []
    const lineItemsForStorage: Array<{ description: string, size: string, serial: string, quantity: number, amount: number }> = []

    if (extractedInvoiceData.value.line_items && extractedInvoiceData.value.line_items.length > 0) {
      notes += `\nLine Items:\n`
      extractedInvoiceData.value.line_items.forEach((item: any) => {
        const itemDescription = item.size ? `${item.description} (${item.size})` : item.description
        notes += `${itemDescription}\n`
        notes += `S/N: ${item.serial}\n`
        notes += `${formatCurrency(item.amount)} x ${item.quantity || 1} = ${formatCurrency(item.amount * (item.quantity || 1))}\n\n`

        if (item.serial && typeof item.serial === 'string' && item.serial.trim() !== '') {
          if (!allSerials.includes(item.serial.trim())) {
            allSerials.push(item.serial.trim())
          }
        }

        lineItemsForStorage.push({
          description: item.description,
          size: item.size || '',
          serial: item.serial || '',
          quantity: Number(item.quantity) || 1,
          amount: Number(item.amount) || 0
        })
      })
    }

    if (extractedInvoiceData.value.raw_text) {
      notes += `\n\nRaw Extracted Text (first 1000 chars):\n${extractedInvoiceData.value.raw_text}`
    }

    const validSerials = [...new Set(allSerials.filter(serial => serial && typeof serial === 'string' && serial.trim() !== ''))]
    const selectedClinicId = extractedInvoiceData.value.clinic_id || (clinics.value.length > 0 ? clinics.value[0].clinic_id : '')

    const invoiceData = {
      invoice_number: extractedInvoiceData.value.invoice_number,
      clinic_id: selectedClinicId,
      amount: extractedInvoiceData.value.amount,
      invoice_date: extractedInvoiceData.value.invoice_date,
      due_date: extractedInvoiceData.value.due_date,
      status: 'pending_review' as const,
      serials: validSerials,
      line_items: lineItemsForStorage,
      has_line_items: lineItemsForStorage.length > 0,
      notes: notes,
      bill_to: extractedInvoiceData.value.bill_to
    }

    await api.post('/invoice-management', invoiceData)

    showAlert('Invoice created successfully', 'success')
    showPdfReviewModal.value = false
    extractedInvoiceData.value = null
    fetchInvoices()
    fetchStats()
  } catch (error: any) {
    console.error('Error creating invoice:', error)
    let errorMessage = 'Failed to create invoice'
    if (axios.isAxiosError(error)) {
      const status = error.response?.status
      const data = error.response?.data

      if (status === 422 && data?.errors) {
        const errors = data.errors
        if (errors.invoice_number) {
          errorMessage = `Invoice already exists: ${errors.invoice_number[0]}`
        }
      } else {
        errorMessage = data?.message || `Request failed with status code ${status}`
      }
    } else if (error instanceof Error) {
      errorMessage = error.message
    }
    showAlert(errorMessage, 'error')
  } finally {
    submitting.value = false
  }
}

async function handleManualInvoiceSubmit() {
  submitting.value = true
  try {
    const allSerials: string[] = []
    let lineItemsNotes = ''
    const lineItemsForStorage: Array<{ description: string, size: string, serial: string, quantity: number, amount: number }> = []

    if (manualInvoice.value.products && manualInvoice.value.products.length > 0) {
      manualInvoice.value.products.forEach((product) => {
        const itemDescription = product.size ? `${product.name} (${product.size})` : product.name
        lineItemsNotes += `${itemDescription}\n`
        lineItemsNotes += `S/N: ${product.serials[0]}\n`
        lineItemsNotes += `${formatCurrency(Number(product.unit_price))} x ${Number(product.quantity || 1)} = ${formatCurrency(Number(product.unit_price) * Number(product.quantity || 1))}\n\n`

        if (product.serials[0] && typeof product.serials[0] === 'string' && product.serials[0].trim() !== '') {
          if (!allSerials.includes(product.serials[0].trim())) {
            allSerials.push(product.serials[0].trim())
          }
        }

        lineItemsForStorage.push({
          description: product.name,
          size: product.size || '',
          serial: product.serials[0] || '',
          quantity: Number(product.quantity) || 1,
          amount: Number(product.unit_price) || 0
        })
      })
    }

    let notes = ''
    if (lineItemsNotes) {
      notes += `\nLine Items:\n${lineItemsNotes}`
    }

    const validSerials = [...new Set(allSerials.filter(serial => serial && typeof serial === 'string' && serial.trim() !== ''))]

    const invoiceData = {
      invoice_number: manualInvoice.value.invoice_number,
      clinic_id: manualInvoice.value.clinic_id || clinics.value[0].clinic_id,
      amount: calculateTotalAmount(),
      invoice_date: manualInvoice.value.invoice_date,
      due_date: manualInvoice.value.due_date,
      status: 'pending_review' as const,
      serials: validSerials,
      line_items: lineItemsForStorage,
      has_line_items: lineItemsForStorage.length > 0,
      notes: notes,
      bill_to: manualInvoice.value.bill_to || null
    }

    await api.post('/invoice-management', invoiceData)

    showAlert('Invoice created successfully', 'success')
    showManualModal.value = false
    resetManualInvoiceForm()
    fetchInvoices()
    fetchStats()
  } catch (error: any) {
    console.error('Error creating invoice:', error)
    let errorMessage = 'Failed to create invoice'
    if (axios.isAxiosError(error)) {
      const status = error.response?.status
      const data = error.response?.data

      if (status === 422 && data?.errors) {
        const errors = data.errors
        if (errors.invoice_number) {
          errorMessage = `Invoice already exists: ${errors.invoice_number[0]}`
        }
      } else {
        errorMessage = data?.message || `Request failed with status code ${status}`
      }
    } else if (error instanceof Error) {
      errorMessage = error.message
    }
    showAlert(errorMessage, 'error')
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
    bill_to: '',
    products: [
      {
        name: '',
        size: '',
        unit_price: 0,
        quantity: 1,
        serials: ['']
      }
    ]
  }
}

function calculateTotalAmount() {
  return manualInvoice.value.products.reduce((total, product) => {
    return total + (Number(product.unit_price) * Number(product.quantity || 1))
  }, 0)
}

function removeProduct(index: number) {
  if (manualInvoice.value.products.length > 1) {
    manualInvoice.value.products.splice(index, 1)
  }
}

function addProduct() {
  manualInvoice.value.products.push({
    name: '',
    size: '',
    unit_price: 0,
    quantity: 1,
    serials: ['']
  })
}

function addProductSerial(productIndex: number) {
  manualInvoice.value.products[productIndex].serials.push('')
}

function removeProductSerial(productIndex: number, serialIndex: number) {
  const product = manualInvoice.value.products[productIndex]
  if (product.serials.length > 1) {
    product.serials.splice(serialIndex, 1)
  }
}

function addLineItem() {
  if (extractedInvoiceData.value && extractedInvoiceData.value.line_items) {
    extractedInvoiceData.value.line_items.push({
      description: '',
      size: '',
      serial: '',
      quantity: 1,
      amount: 0
    })
  }
}

function removeLineItem(index: number) {
  if (extractedInvoiceData.value && extractedInvoiceData.value.line_items && extractedInvoiceData.value.line_items.length > 1) {
    extractedInvoiceData.value.line_items.splice(index, 1)
  }
}

function addEditLineItem() {
  if (invoiceToEdit.value && invoiceToEdit.value.line_items) {
    invoiceToEdit.value.line_items.push({
      description: '',
      size: '',
      serial: '',
      quantity: 1,
      amount: 0
    })
  }
}

function removeEditLineItem(index: number) {
  if (invoiceToEdit.value && invoiceToEdit.value.line_items && invoiceToEdit.value.line_items.length > 1) {
    invoiceToEdit.value.line_items.splice(index, 1)
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
    await api.post(`/invoice-management/${invoiceToMarkPaid.value.id}/status`, {
      status: 'paid',
      paid_amount: paymentData.value.paid_amount,
      payment_method: paymentData.value.payment_method,
      payment_reference: paymentData.value.payment_reference
    })

    showAlert('Invoice marked as paid', 'success')
    showMarkPaidModal.value = false
    invoiceToMarkPaid.value = null
    fetchInvoices()
    fetchStats()
  } catch (error) {
    console.error('Error marking invoice as paid:', error)
    showAlert(error instanceof Error ? error.message : 'Failed to mark invoice as paid', 'error')
  }
}

async function markIndividualSerialAsPaid(serialNumber: string) {
  if (!selectedInvoice.value) return
  
  try {
    await api.post(`/invoice-management/${selectedInvoice.value.id}/serial-payment`, {
      serial_number: serialNumber,
      status: 'paid',
      payment_method: 'individual_serial_paid',
      payment_reference: `Payment for serial ${serialNumber}`
    })
    
    paidSerials.value[serialNumber] = true
    showAlert(`Serial ${serialNumber} marked as paid`, 'success')
    fetchInvoices()
    fetchStats()
  } catch (error) {
    console.error('Error marking serial as paid:', error)
    showAlert(error instanceof Error ? error.message : 'Failed to mark serial as paid', 'error')
  }
}

function getSerialPaymentButtonClass(serialNumber: string) {
  if (paidSerials.value[serialNumber]) {
    return 'ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded dark:bg-green-900/30 dark:text-green-300 cursor-default opacity-75'
  }
  return 'ml-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:hover:bg-yellow-900/50 cursor-pointer'
}

function reviewExtractedData(invoice: Invoice) {
  invoiceUnderReview.value = JSON.parse(JSON.stringify(invoice))
  showReviewModal.value = true
}

function linkToOrder(invoice: Invoice) {
  selectedInvoice.value = invoice
  showReviewModal.value = true
}

function deleteInvoice() {
  if (!invoiceToDelete.value) return

  api.delete(`/invoice-management/${invoiceToDelete.value.id}`)
    .then(() => {
      showAlert('Invoice deleted successfully', 'success')
      invoices.value = invoices.value.filter(invoice => invoice.id !== invoiceToDelete.value!.id)
      invoiceToDelete.value = null
      fetchStats()
    })
    .catch(error => {
      console.error('Error deleting invoice:', error)
      if (axios.isAxiosError(error)) {
        console.error('Axios error details:', error.response?.data, error.response?.status)
      }
      showAlert(error instanceof Error ? error.message : 'Failed to delete invoice', 'error')
    })
}

function calculateEditTotalAmount() {
  if (!invoiceToEdit.value || !invoiceToEdit.value.line_items) return 0
  return invoiceToEdit.value.line_items.reduce((total, item) => {
    return total + (Number(item.amount) * Number(item.quantity || 1))
  }, 0)
}

function extractSerialsFromLineItems(lineItems: any[]): string[] {
  if (!lineItems || lineItems.length === 0) return []
  const serials: string[] = []
  lineItems.forEach((item: any) => {
    if (item.serial && typeof item.serial === 'string' && item.serial.trim() !== '') {
      const trimmedSerial = item.serial.trim()
      if (!serials.includes(trimmedSerial)) {
        serials.push(trimmedSerial)
      }
    }
  })
  return serials
}

function calculateExtractedInvoiceTotalAmount() {
  if (!extractedInvoiceData.value || !extractedInvoiceData.value.line_items) return 0
  return extractedInvoiceData.value.line_items.reduce((total: number, item: any) => {
    return total + (Number(item.amount) * Number(item.quantity || 1))
  }, 0)
}

function resetLineItems() {
  if (extractedInvoiceData.value) {
    extractedInvoiceData.value.line_items = [{
      description: '',
      size: '',
      serial: '',
      quantity: 1,
      amount: 0
    }]
    extractedInvoiceData.value.invoice_number = ''
    extractedInvoiceData.value.amount = 0
    extractedInvoiceData.value.invoice_date = ''
    extractedInvoiceData.value.due_date = ''
    extractedInvoiceData.value.serials = []
    extractedInvoiceData.value.vendor = ''
    extractedInvoiceData.value.notes = ''
    extractedInvoiceData.value.payment_terms = ''
  }
}

function parseLineItems(notes: string) {
  const lines = notes.split('\n')
  const lineItems = []
  let inLineItemsSection = false

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i]

    if (line.startsWith('Line Items:') || line.startsWith('Line Items :')) {
      inLineItemsSection = true
      continue
    }

    if (inLineItemsSection) {
      if (line.trim() !== '' && !line.startsWith('S/N: ') && !line.startsWith('$') && !line.startsWith('Raw Extracted Text')) {
        let productNameMatch = line.match(/(.+)\s*\((.+)\)/)
        let productName, productSize

        if (productNameMatch) {
          productName = productNameMatch[1].trim()
          productSize = productNameMatch[2].trim()
        } else {
          const sizePatterns = [/\s+(\d+x\d+cm)$/, /\s+(\d+cm)$/, /\s+(\d+x\d+)$/, /\s+(\d+\.\d+cm)$/, /\s+(\d+\.\d+x\d+\.\d+cm)$/]
          let foundSize = false

          for (const pattern of sizePatterns) {
            const match = line.match(pattern)
            if (match) {
              productSize = match[1]
              productName = line.replace(pattern, '').trim()
              foundSize = true
              break
            }
          }

          if (!foundSize) {
            productName = line.trim()
            productSize = ''
          }
        }

        const serialLine = i + 1 < lines.length ? lines[i + 1] : ''
        const pricingLine = i + 2 < lines.length ? lines[i + 2] : ''

        let serials: string[] = []
        let unitPrice = 0
        let quantity = 0
        let total = 0

        if (serialLine.startsWith('S/N: ')) {
          serials.push(serialLine.replace('S/N: ', '').trim())
          let nextIndex = i + 2
          while (nextIndex < lines.length && lines[nextIndex].startsWith('S/N: ')) {
            serials.push(lines[nextIndex].replace('S/N: ', '').trim())
            nextIndex++
          }
        }

        if (pricingLine.startsWith('$')) {
          const pricingMatch = pricingLine.match(/\$(.+) x (.+) = \$(.+)/)
          if (pricingMatch) {
            unitPrice = Number(pricingMatch[1]) || 0
            quantity = Number(pricingMatch[2]) || 0
            total = Number(pricingMatch[3]) || 0
          }
        }

        lineItems.push({
          name: productName,
          size: productSize,
          serials: serials,
          unit_price: unitPrice,
          quantity: quantity,
          total: total
        })

        i += 2 + (serials.length > 1 ? serials.length - 1 : 0)
      } else if (line.startsWith('Raw Extracted Text') || line.trim() === '') {
        if (lineItems.length > 0 && line.startsWith('Raw Extracted Text')) {
          break
        }
      }
    }
  }

  return lineItems
}

// Watchers
watch(() => manualInvoice.value.products, () => {
  manualInvoice.value.amount = calculateTotalAmount()
}, { deep: true })

watch(() => extractedInvoiceData.value?.line_items, () => {
  if (extractedInvoiceData.value) {
    extractedInvoiceData.value.amount = calculateExtractedInvoiceTotalAmount()
  }
}, { deep: true })

watch(() => invoiceToEdit.value?.line_items, () => {
  if (invoiceToEdit.value) {
    invoiceToEdit.value.amount = calculateEditTotalAmount()
  }
}, { deep: true })

watch(selectedInvoice, async (newInvoice) => {
  if (newInvoice) {
    paidSerials.value = {}
    
    if (newInvoice.line_items && newInvoice.line_items.length > 0) {
      newInvoice.line_items.forEach(item => {
        if (item.serial) {
          paidSerials.value[item.serial as string] = false
        }
      })
    }
    
    if (newInvoice.notes && (newInvoice.notes.includes('Line Items:') || newInvoice.notes.includes('Line Items :'))) {
      const parsedItems = parseLineItems(newInvoice.notes)
      parsedItems.forEach(product => {
        if (product.serials && product.serials.length > 0) {
          product.serials.forEach(serial => {
            paidSerials.value[serial] = false
          })
        }
      })
    }
    
    if (newInvoice.serials && newInvoice.serials.length > 0) {
      newInvoice.serials.forEach(serial => {
        paidSerials.value[serial] = false
      })
    }
    
    try {
      const response = await api.get(`/invoice-management/${newInvoice.id}/serial-payments`)
      const serialPayments = response.data
      
      serialPayments.forEach((payment: any) => {
        if (payment.status === 'paid') {
          paidSerials.value[payment.serial_number] = true
        }
      })
    } catch (error) {
      console.error('Error fetching serial payments:', error)
    }
  }
}, { immediate: true })

function safeNumber(value: any, defaultValue: number = 0): number {
  const num = Number(value)
  return isNaN(num) ? defaultValue : num
}

function calculateTotal(amount: any, quantity: any): string {
  const amt = safeNumber(amount)
  const qty = safeNumber(quantity, 1)
  return formatCurrency(amt * qty)
}

// Lifecycle
onMounted(() => {
  fetchClinics().then(() => {
    fetchInvoices()
    fetchStats()
  })
})
</script>

<style>
/* Global scrollbar styles for invoice modal */
.max-h-96.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
  border-radius: 30% !important;
}

.max-h-96.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 30% !important;
}

.max-h-96.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 30% !important;
  transition: background-color 0.2s ease;
}

.max-h-96.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #a0aec0;
}

/* Dark mode styles */
.dark .max-h-96.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #4a5568;
}

.dark .max-h-96.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #718096;
}

/* Firefox support */
.max-h-96.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 transparent;
}

.dark .max-h-96.overflow-y-auto {
  scrollbar-color: #4a5568 transparent;
}
</style>