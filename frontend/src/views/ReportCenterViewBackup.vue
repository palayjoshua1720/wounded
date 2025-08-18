<template>
  <div class="space-y-6">
    <!-- Header and Generate Custom Report -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reporting</h1>
        <p class="text-gray-600 dark:text-gray-400">Generate and view comprehensive system reports</p>
      </div>
      <button
        @click="showGenerateModal = true"
        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        <DocumentChartBarIcon class="w-4 h-4 mr-2" />
        Generate Custom Report
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Reports</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ reports.length }}</p>
          </div>
          <ChartBarIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Generated Today</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ todayCount }}</p>
          </div>
          <CalendarIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Records</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ totalRecords.toLocaleString() }}</p>
          </div>
          <CubeIcon class="w-8 h-8 text-purple-600 dark:text-purple-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Report Types</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ reportTypeCount }}</p>
          </div>
          <ArrowTrendingUpIcon class="w-8 h-8 text-orange-600 dark:text-orange-400" />
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="flex items-center space-x-2">
        <FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <select v-model="typeFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="all">All Report Types</option>
          <option v-for="type in reportTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
        </select>
        <select v-model="dateRange" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
          <option value="7">Last 7 days</option>
          <option value="30">Last 30 days</option>
          <option value="90">Last 90 days</option>
          <option value="365">Last year</option>
          <option value="all">All time</option>
        </select>
      </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="report in filteredReports"
        :key="report.id"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center space-x-3">
            <component :is="getTypeIcon(report.type)" class="w-5 h-5" />
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ report.name }}</h3>
              <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getTypeColor(report.type)]">
                {{ reportTypeLabel(report.type) }}
              </span>
            </div>
          </div>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ report.description }}</p>

        <div class="space-y-2 mb-4">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">Last Generated:</span>
            <span class="text-gray-900 dark:text-white">{{ formatDate(report.lastGenerated) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">Records:</span>
            <span class="text-gray-900 dark:text-white">{{ report.recordCount.toLocaleString() }}</span>
          </div>
        </div>

        <div class="flex space-x-2">
          <button
            @click="generateReport(report)"
            class="flex-1 flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <ChartBarIcon class="w-4 h-4 mr-2" />
            Generate
          </button>
          <button 
            @click="downloadReport(report)"
            class="flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <ArrowDownTrayIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
      <div v-if="filteredReports.length === 0" class="text-center py-12 text-gray-400 dark:text-gray-500 col-span-full">
        No reports found.
      </div>
    </div>

    <!-- Generate Custom Report Modal -->
    <BaseModal v-model="showGenerateModal" title="Generate Custom Report">
      <form @submit.prevent="handleCreateReport" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Report Name<span class="text-red-500">*</span></label>
          <input v-model="newReport.name" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter report name" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Report Type<span class="text-red-500">*</span></label>
            <select v-model="newReport.type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option v-for="type in reportTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range<span class="text-red-500">*</span></label>
            <select v-model="newReport.dateRange" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="7">Last 7 days</option>
              <option value="30">Last 30 days</option>
              <option value="90">Last 90 days</option>
              <option value="365">Last year</option>
              <option value="custom">Custom range</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filters</label>
          <div class="mt-2 space-y-2">
            <div class="flex items-center">
              <input v-model="newReport.filters.activeOnly" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700" />
              <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include only active records</label>
            </div>
            <div class="flex items-center">
              <input v-model="newReport.filters.groupByClinic" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700" />
              <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Group by clinic</label>
            </div>
            <div class="flex items-center">
              <input v-model="newReport.filters.includeFinancial" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700" />
              <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include financial data</label>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Export Format<span class="text-red-500">*</span></label>
          <div class="mt-2 flex space-x-4">
            <div class="flex items-center">
              <input v-model="newReport.exportFormat" type="radio" value="pdf" class="text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" />
              <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">PDF</label>
            </div>
            <div class="flex items-center">
              <input v-model="newReport.exportFormat" type="radio" value="excel" class="text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" />
              <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Excel</label>
            </div>
            <div class="flex items-center">
              <input v-model="newReport.exportFormat" type="radio" value="csv" class="text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" />
              <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">CSV</label>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button type="button" @click="showGenerateModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Generate Report</button>
        </div>
      </form>
    </BaseModal>

    <!-- Report Generation Modal -->
    <BaseModal v-model="showGenerationModal" title="Generating Report">
      <div class="text-center">
        <div class="flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900/20 rounded-full mx-auto mb-4">
          <ChartBarIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Generating Report</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ generatingReport?.name }}</p>
        
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-4">
          <div class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full animate-pulse" :style="{ width: generationProgress + '%' }"></div>
        </div>
        
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Processing {{ generatingReport?.recordCount?.toLocaleString() }} records...</p>
        
        <button
          @click="cancelGeneration"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          Cancel
        </button>
      </div>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
  ChartBarIcon,
  CalendarIcon,
  CubeIcon,
  ArrowTrendingUpIcon,
  FunnelIcon,
  ArrowDownTrayIcon,
  DocumentChartBarIcon
} from '@heroicons/vue/24/outline'

const reportTypes = [
  { value: 'orders', label: 'Orders', icon: CubeIcon, color: 'bg-blue-100 text-blue-800' },
  { value: 'inventory', label: 'Inventory', icon: CubeIcon, color: 'bg-green-100 text-green-800' },
  { value: 'usage', label: 'Usage', icon: ArrowTrendingUpIcon, color: 'bg-purple-100 text-purple-800' },
  { value: 'invoices', label: 'Invoices', icon: CubeIcon, color: 'bg-yellow-100 text-yellow-800' },
  { value: 'ivr', label: 'IVR', icon: CubeIcon, color: 'bg-indigo-100 text-indigo-800' },
  { value: 'financial', label: 'Financial', icon: ChartBarIcon, color: 'bg-red-100 text-red-800' },
]

const reports = ref([
  {
    id: '1',
    name: 'Order Summary Report',
    description: 'Comprehensive overview of all orders by status, clinic, and time period',
    type: 'orders',
    lastGenerated: '2025-01-27T10:00:00Z',
    recordCount: 2847
  },
  {
    id: '2',
    name: 'Inventory Status Report',
    description: 'Current inventory levels, expiry dates, and serial tracking',
    type: 'inventory',
    lastGenerated: '2025-01-27T09:30:00Z',
    recordCount: 15678
  },
  {
    id: '3',
    name: 'Usage Analytics Report',
    description: 'Graft usage patterns, clinician performance, and patient outcomes',
    type: 'usage',
    lastGenerated: '2025-01-26T16:45:00Z',
    recordCount: 1234
  },
  {
    id: '4',
    name: 'Financial Summary Report',
    description: 'Revenue, payments, outstanding invoices, and financial trends',
    type: 'financial',
    lastGenerated: '2025-01-26T14:20:00Z',
    recordCount: 567
  },
  {
    id: '5',
    name: 'IVR Status Report',
    description: 'Insurance verification status, approval rates, and pending requests',
    type: 'ivr',
    lastGenerated: '2025-01-25T11:00:00Z',
    recordCount: 892
  },
  {
    id: '6',
    name: 'Invoice Aging Report',
    description: 'Outstanding invoices, payment delays, and collection metrics',
    type: 'invoices',
    lastGenerated: '2025-01-24T18:30:00Z',
    recordCount: 234
  }
])

const typeFilter = ref('all')
const dateRange = ref('all')
const selectedReport = ref(null)
const showGenerateModal = ref(false)
const showGenerationModal = ref(false)
const generatingReport = ref(null)
const generationProgress = ref(0)
const generationInterval = ref(null)

const newReport = ref({
  name: '',
  description: '',
  type: 'orders',
  dateRange: '30',
  exportFormat: 'pdf',
  filters: {
    activeOnly: false,
    groupByClinic: false,
    includeFinancial: false
  }
})

function handleCreateReport() {
  if (!newReport.value.name || !newReport.value.type || !newReport.value.exportFormat) {
    return
  }
  
  const now = new Date().toISOString()
  const recordCount = Math.floor(Math.random() * 10000) + 100
  
  const report = {
    id: `${Math.floor(Math.random() * 100000)}`,
    name: newReport.value.name,
    description: newReport.value.description || `Custom ${newReport.value.type} report`,
    type: newReport.value.type,
    lastGenerated: now,
    recordCount: recordCount,
  }
  
  reports.value.unshift(report)
  showGenerateModal.value = false
  newReport.value = { 
    name: '', 
    description: '', 
    type: 'orders', 
    dateRange: '30',
    exportFormat: 'pdf',
    filters: {
      activeOnly: false,
      groupByClinic: false,
      includeFinancial: false
    }
  }
  
  // Start generation process
  generateReport(report)
}

function generateReport(report: any) {
  generatingReport.value = report
  showGenerationModal.value = true
  generationProgress.value = 0
  
  // Simulate generation progress
  generationInterval.value = setInterval(() => {
    generationProgress.value += Math.random() * 15
    if (generationProgress.value >= 100) {
      generationProgress.value = 100
      clearInterval(generationInterval.value)
      
      // Update the report's last generated time
      const reportIndex = reports.value.findIndex(r => r.id === report.id)
      if (reportIndex !== -1) {
        reports.value[reportIndex].lastGenerated = new Date().toISOString()
      }
      
      setTimeout(() => {
        showGenerationModal.value = false
        generatingReport.value = null
        generationProgress.value = 0
      }, 1000)
    }
  }, 200)
}

function cancelGeneration() {
  if (generationInterval.value) {
    clearInterval(generationInterval.value)
  }
  showGenerationModal.value = false
  generatingReport.value = null
  generationProgress.value = 0
}

function downloadReport(report: any) {
  // Simulate download
  const link = document.createElement('a')
  link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(`Report: ${report.name}\nGenerated: ${new Date().toLocaleString()}\nRecords: ${report.recordCount}`)
  link.download = `${report.name.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`
  link.click()
}

const filteredReports = computed(() => {
  return reports.value.filter(report => {
    const matchesType = typeFilter.value === 'all' || report.type === typeFilter.value
    // Date range filter (for demo, just filter by lastGenerated date)
    if (dateRange.value !== 'all') {
      const days = parseInt(dateRange.value)
      const cutoff = new Date()
      cutoff.setDate(cutoff.getDate() - days)
      if (new Date(report.lastGenerated) < cutoff) return false
    }
    return matchesType
  })
})

const todayCount = computed(() => {
  const today = new Date().toDateString()
  return reports.value.filter(r => new Date(r.lastGenerated).toDateString() === today).length
})

const totalRecords = computed(() => reports.value.reduce((sum, r) => sum + r.recordCount, 0))
const reportTypeCount = computed(() => new Set(reports.value.map(r => r.type)).size)

const getTypeIcon = (type: string) => {
  const found = reportTypes.find(t => t.value === type)
  return found ? found.icon : ChartBarIcon
}

const getTypeColor = (type: string) => {
  const found = reportTypes.find(t => t.value === type)
  return found ? found.color : 'bg-gray-100 text-gray-800'
}

const reportTypeLabel = (type: string) => {
  const found = reportTypes.find(t => t.value === type)
  return found ? found.label : type
}

const formatDate = (dateStr: string) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString()
}

onMounted(() => {
  // Clean up interval on component unmount
  return () => {
    if (generationInterval.value) {
      clearInterval(generationInterval.value)
    }
  }
})
</script> 