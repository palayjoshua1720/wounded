<template>
	<div class="space-y-6">
		<!-- Report Type Selection Card -->
		<ReportTypeSelector
			:report-types="reportTypes"
			:selected-type="reportType"
			@select="reportType = $event"
		/>

		<!-- Filters Card -->
		<ReportFilters
			:date-range="dateRange"
			:date-range-options="dateRangeOptions"
			:start-date="startDate"
			:end-date="endDate"
			:date-range-error="dateRangeError"
			:clinic-filter="clinicFilter"
			:clinics="clinics"
			:brand-filter="brandFilter"
			:brands="brands"
			:manufacturer-filter="manufacturerFilter"
			:manufacturers="manufacturers"
			:anonymize-phi="anonymizePhi"
			:report-type="reportType"
			:is-loading-data="isLoadingData"
			:is-exporting="isExporting"
			:report-generated="reportGenerated"
			:show-export-dropdown="showExportDropdown"
			:selected-report-type="selectedReportType"
			@clear="clearFilters"
			@generate="handleGenerateReport"
			@toggle-export="toggleExportDropdown"
			@export-pdf="exportAsPdf"
			@export-excel="exportAsExcel"
			@update:date-range="dateRange = $event"
			@update:start-date="startDate = $event"
			@update:end-date="endDate = $event"
			@update:clinic-filter="clinicFilter = $event"
			@update:brand-filter="brandFilter = $event"
			@update:manufacturer-filter="manufacturerFilter = $event"
			@update:anonymize-phi="anonymizePhi = $event"
		/>

		<!-- Report Preview -->
		<div v-if="selectedReportType && reportGenerated" class="report-preview-container space-y-6">
			<!-- Report Header -->
			<ReportHeader
				:report-name="selectedReportType.name"
				:date-range-label="dateRange === 'custom' ? `${startDate} to ${endDate}` : dateRangeOptions.find(opt => opt.value === dateRange)?.label || ''"
				:current-timestamp="currentTimestamp"
				:clinic-filter="clinicFilter"
				:clinics="clinics"
				:brand-filter="brandFilter"
				:brands="brands"
				:manufacturer-filter="manufacturerFilter"
				:manufacturers="manufacturers"
				:report-type="reportType"
			/>

			<!-- Orders Report -->
			<OrdersReport
				v-if="reportType === 'orders'"
				:total-orders="totalOrders"
				:total-order-items="totalOrderItems"
				:total-order-value="totalOrderValue"
				:order-status-breakdown="orderStatusBreakdown"
				:top-clinics="topClinics"
				:top-products="topProducts"
				:brand-distribution="brandDistribution"
				:size-distribution="sizeDistribution"
				:clinic-filter="clinicFilter"
				:brand-filter="brandFilter"
				:report-generated="reportGenerated"
				:is-loading-data="isLoadingData"
			/>

			<!-- Inventory Report -->
			<InventoryReport
				v-else-if="reportType === 'inventory'"
				:total-inventory="totalInventory"
				:used-inventory-count="usedInventoryCount"
				:in-use-inventory-count="inUseInventoryCount"
				:inventory-status-breakdown="inventoryStatusBreakdown"
				:top-inventory-clinics="topInventoryClinics"
				:inventory-brand-distribution="inventoryBrandDistribution"
				:utilization-rate="utilizationRate"
				:report-generated="reportGenerated"
				:is-loading-data="isLoadingData"
			/>

			<!-- Usage Report -->
			<UsageReport
				v-else-if="reportType === 'usage'"
				:total-usage-logs="filteredUsageLogs.length"
				:usage-by-wound-part="usageByWoundPart"
				:top-usage-clinics="topUsageClinics"
				:usage-brand-distribution="usageBrandDistribution"
				:report-generated="reportGenerated"
				:is-loading-data="isLoadingData"
			/>

			<!-- Invoice Report -->
			<InvoiceReport
				v-else-if="reportType === 'invoices'"
				:total-invoices="filteredInvoices.length"
				:total-invoice-amount="totalInvoiceAmount"
				:pending-invoice-amount="pendingInvoiceAmount"
				:invoice-status-breakdown="invoiceStatusBreakdown"
				:top-invoice-clinics="topInvoiceClinics"
				:report-generated="reportGenerated"
				:is-loading-data="isLoadingData"
			/>

			<!-- IVR Report -->
			<IVRReport
				v-else-if="reportType === 'ivr'"
				:totalIVRRequests="filteredIVRRequests.length"
				:overallApprovalRate="overallApprovalRate"
				:manufacturerApprovalRate="manufacturerApprovalRate"
				:clinicApprovalRate="clinicApprovalRate"
				:ivrEligibilityBreakdown="ivrEligibilityBreakdown"
				:topIVRManufacturers="topIVRManufacturers"
				:topIVRClinics="topIVRClinics"
				:manufacturerFilter="manufacturerFilter"
				:manufacturers="manufacturers"
				:clinicFilter="clinicFilter"
				:clinics="clinics"
				:reportGenerated="reportGenerated"
				:isLoadingData="isLoadingData"
			/>

			<!-- Patient Graft Application Report -->
			<PatientGraftApplicationReport
				v-else-if="reportType === 'patient_graft_application'"
				:graft-logs="filteredPatientGraftLogs"
				:is-loading-data="isLoadingData"
				:anonymize-phi="anonymizePhi"
			/>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import {
	ChartBarIcon,
	ArrowDownTrayIcon,
	CubeIcon,
	ArrowTrendingUpIcon,
	CurrencyDollarIcon,
	UsersIcon,
	ChartBarSquareIcon,
	ClipboardDocumentListIcon,
	DocumentTextIcon,
	CheckCircleIcon,
	ArrowPathIcon,
	ClockIcon,
	ShieldCheckIcon,
	BuildingStorefrontIcon,
	ClipboardDocumentCheckIcon,
	BeakerIcon
} from '@heroicons/vue/24/outline'
import { Chart, registerables, type ChartType } from 'chart.js'
import type { Ref } from 'vue'
import { orderService, userService, brandService, inventoryService, invoiceService, ivrService, returnsService, patientGraftLogService, api } from '@/services/api'
import { Package, Building2 } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import html2canvas from 'html2canvas'
import * as XLSX from 'xlsx'
import ReportTypeSelector from './sections/ReportTypeSelector.vue'
import ReportFilters from './sections/ReportFilters.vue'
import ReportHeader from './sections/ReportHeader.vue'
import OrdersReport from './sections/OrdersReport.vue'
import InventoryReport from './sections/InventoryReport.vue'
import UsageReport from './sections/UsageReport.vue'
import InvoiceReport from './sections/InvoiceReport.vue'
import IVRReport from './sections/IVRReport.vue'
import PatientGraftApplicationReport from './sections/PatientGraftApplicationReport.vue'

Chart.register(...registerables)

// --- REAL DATA FROM API ---
const orders = ref<any[]>([])
const clinics = ref<any[]>([])
const brands = ref<any[]>([])
const manufacturers = ref<any[]>([])
const inventory = ref<any[]>([])
const invoices = ref<any[]>([])
const ivrRequests = ref<any[]>([])
const usageLogs = ref<any[]>([])
const patientGraftLogs = ref<any[]>([])
const isLoadingData = ref(false)
const isExporting = ref(false) // Track if export is in progress
const reportGenerated = ref(false) // Track if report has been generated
const showExportDropdown = ref(false)
const exportDropdownRef = ref<HTMLElement | null>(null)

// Fetch real data from API
async function fetchReportData() {
	isLoadingData.value = true
	try {
		// Fetch all data including manufacturers in parallel
		const [ordersRes, clinicsRes, brandsRes, manufacturersRes, inventoryRes, invoicesRes, ivrRes, graftLogsRes] = await Promise.all([
			orderService.getAllOrders({ per_page: 10000 }),
			userService.getClinics(),
			brandService.getAllBrands({ per_page: 1000 }),
			userService.getManufacturers(),
			inventoryService.getAllInventory(),
			invoiceService.getAllInvoices({ per_page: 10000 }),
			ivrService.getAllIVRRequests({ per_page: 10000 }),
			patientGraftLogService.getAllLogs()
		])

		if (graftLogsRes.data?.success && Array.isArray(graftLogsRes.data?.data)) {
			patientGraftLogs.value = graftLogsRes.data.data
		}

		// Parse orders
		const orderRows = Array.isArray(ordersRes.data?.order_data) ? ordersRes.data.order_data : []
		orders.value = orderRows.map((o: any) => {
			let rawItems = o.items
			if (!Array.isArray(rawItems) && typeof rawItems === 'string') {
				try { rawItems = JSON.parse(rawItems) } catch { rawItems = [] }
			}
			return {
				order_id: o.order_id,
				order_code: o.order_code,
				ordered_at: o.ordered_at || o.created_at,
				order_status: ['submitted','acknowledged','shipped','delivered','cancelled'][Number(o.order_status ?? 0)] || 'submitted',
				clinic: o.clinic,
				clinic_id: o.clinic?.clinic_id || o.clinic_id,
				items: Array.isArray(rawItems) ? rawItems : [],
				brand: o.brand
			}
		})

		// Parse clinics - API returns user_data
		const clinicRows = Array.isArray(clinicsRes.data?.user_data) ? clinicsRes.data.user_data : 
						  Array.isArray(clinicsRes.data?.clinic_data) ? clinicsRes.data.clinic_data : 
						  Array.isArray(clinicsRes.data) ? clinicsRes.data : []
		clinics.value = clinicRows.map((c: any) => ({
			id: String(c.id || c.clinic_id || c.clinicId),
			name: c.clinic_name || c.clinicName || c.name || c.clinicPubId || 'Unknown Clinic'
		}))

		// Parse brands - API returns brandData
		const brandRows = Array.isArray(brandsRes.data?.brandData) ? brandsRes.data.brandData : 
						  Array.isArray(brandsRes.data?.data) ? brandsRes.data.data : []
		brands.value = brandRows.map((b: any) => ({
			id: String(b.id || b.brand_id || b.brandId),
			name: b.brand_name || b.brandName || b.name || 'Unknown Brand'
		}))

		// Parse manufacturers - Check manufacturerData field first (like fetchFilterOptions does)
		console.log('🔍 Raw manufacturers response:', manufacturersRes.data)
		const manufacturerRows = Array.isArray(manufacturersRes.data?.manufacturerData) ?
								 manufacturersRes.data.manufacturerData :
								 Array.isArray(manufacturersRes.data?.data) ?
								 manufacturersRes.data.data :
								 Array.isArray(manufacturersRes.data?.manufacturers) ?
								 manufacturersRes.data.manufacturers :
								 Array.isArray(manufacturersRes.data) ? manufacturersRes.data : []
		console.log('🔍 Parsed manufacturer rows:', manufacturerRows)
		manufacturers.value = manufacturerRows.map((m: any) => ({
			id: String(m.id || m.manufacturer_id || m.manufacturerId),
			name: m.manufacturer_name || m.manufacturerName || m.name || 'Unknown Manufacturer'
		}))
		console.log('🔍 Final manufacturers array:', manufacturers.value)

		// Parse inventory/usage logs
		const inventoryRows = Array.isArray(inventoryRes.data?.data) ? inventoryRes.data.data : 
							  Array.isArray(inventoryRes.data) ? inventoryRes.data : []
		inventory.value = inventoryRows
		// Usage logs are items with status 2 (used) or 3 (partially used)
		usageLogs.value = inventoryRows.filter((item: any) => {
			const status = Number(item.log_status)
			return status === 2 || status === 3
		})

		// Parse invoices
		const invoiceRows = Array.isArray(invoicesRes.data?.data) ? invoicesRes.data.data : 
						   Array.isArray(invoicesRes.data?.invoices) ? invoicesRes.data.invoices : 
						   Array.isArray(invoicesRes.data) ? invoicesRes.data : []
		invoices.value = invoiceRows

		// Parse IVR requests
		const ivrRows = Array.isArray(ivrRes.data?.ivrRequests) ? ivrRes.data.ivrRequests : 
					   Array.isArray(ivrRes.data?.data) ? ivrRes.data.data : 
					   Array.isArray(ivrRes.data) ? ivrRes.data : []
		ivrRequests.value = ivrRows

		console.log('Loaded orders:', orders.value.length)
		console.log('Loaded clinics:', clinics.value.length)
		console.log('Loaded brands:', brands.value.length)
		console.log('Loaded manufacturers:', manufacturers.value.length)
		console.log('Loaded inventory:', inventory.value.length)
		console.log('Loaded usage logs:', usageLogs.value.length)
		console.log('Loaded invoices:', invoices.value.length)
		console.log('Loaded IVR requests:', ivrRequests.value.length)
	} catch (error) {
		console.error('Error fetching report data:', error)
	} finally {
		isLoadingData.value = false
	}
}

// Fetch only filter options (clinics, brands, manufacturers) on mount
async function fetchFilterOptions() {
	try {
		console.log('🔄 Fetching filter options...')
		const [clinicsRes, brandsRes, manufacturersRes] = await Promise.all([
			userService.getClinics(),
			brandService.getAllBrands({ per_page: 1000 }),
			userService.getManufacturers()
		])

		console.log('✅ Clinics response:', clinicsRes.data)
		console.log('✅ Brands response:', brandsRes.data)
		console.log('✅ Manufacturers response:', manufacturersRes.data)

		// Parse clinics - API returns user_data
		const clinicRows = Array.isArray(clinicsRes.data?.user_data) ? clinicsRes.data.user_data : 
						  Array.isArray(clinicsRes.data?.clinic_data) ? clinicsRes.data.clinic_data : 
						  Array.isArray(clinicsRes.data) ? clinicsRes.data : []
		clinics.value = clinicRows.map((c: any) => ({
			id: String(c.id || c.clinic_id || c.clinicId),
			name: c.clinic_name || c.clinicName || c.name || c.clinicPubId || 'Unknown Clinic'
		}))

		// Parse brands - API returns brandData
		const brandRows = Array.isArray(brandsRes.data?.brandData) ? brandsRes.data.brandData : 
						  Array.isArray(brandsRes.data?.data) ? brandsRes.data.data : []
		brands.value = brandRows.map((b: any) => ({
			id: String(b.id || b.brand_id || b.brandId),
			name: b.brand_name || b.brandName || b.name || 'Unknown Brand'
		}))

		// Parse manufacturers
		const manufacturerRows = Array.isArray(manufacturersRes.data?.manufacturerData) ?
								 manufacturersRes.data.manufacturerData :
								 Array.isArray(manufacturersRes.data?.data) ?
								 manufacturersRes.data.data :
								 Array.isArray(manufacturersRes.data?.manufacturers) ?
								 manufacturersRes.data.manufacturers :
								 Array.isArray(manufacturersRes.data) ? manufacturersRes.data : []
		manufacturers.value = manufacturerRows.map((m: any) => ({
			id: String(m.id || m.manufacturer_id || m.manufacturerId),
			name: m.manufacturer_name || m.manufacturerName || m.name || 'Unknown Manufacturer'
		}))

		console.log('📊 Parsed clinics:', clinics.value.length)
		console.log('📊 Parsed brands:', brands.value.length)
		console.log('📊 Parsed manufacturers:', manufacturers.value)
	} catch (error) {
		console.error('❌ Error fetching filter options:', error)
	}
}

interface ReportType {
	id: string
	name: string
	icon: any
	description: string
}

interface DateRangeOption {
	value: string
	label: string
}

const emit = defineEmits<{
	generateReport: [filters: any]
}>()

const reportType = ref('orders')
const dateRange = ref('last_30_days') // Default to Last 30 Days for better performance
const startDate = ref('')
const endDate = ref('')
const dateRangeError = ref('') // Validation error message for custom date range
const clinicFilter = ref('all')
const brandFilter = ref('all')
const manufacturerFilter = ref('all') // For IVR report
const anonymizePhi = ref(false) // HIPAA De-identification toggle
const graphType = ref('area') // 'area', 'line', 'bar'

const reportTypes: ReportType[] = [
	{ 
		id: 'orders', 
		name: 'Orders Report', 
		icon: ClipboardDocumentListIcon, 
		description: 'Orders by clinic, clinician, date range' 
	},
	{ 
		id: 'inventory', 
		name: 'Inventory Report', 
		icon: CubeIcon, 
		description: 'Inventory counts per clinic, brand, size' 
	},
	{ 
		id: 'usage', 
		name: 'Usage Report', 
		icon: ArrowTrendingUpIcon, 
		description: 'Grafts ordered vs used vs unused' 
	},
	{ 
		id: 'invoices', 
		name: 'Invoice Report', 
		icon: CurrencyDollarIcon, 
		description: 'Invoice status per serial' 
	},
	{ 
		id: 'ivr', 
		name: 'IVR Report', 
		icon: ShieldCheckIcon, 
		description: 'Insurance verification status' 
	},
	{ 
		id: 'patient_graft_application', 
		name: 'Patient Graft Application Report', 
		icon: ClipboardDocumentCheckIcon, 
		description: 'Graft applications, serials, and wound sites' 
	}
]

const dateRangeOptions: DateRangeOption[] = [
	{ value: 'last_7_days', label: 'Last 7 Days' },
	{ value: 'last_30_days', label: 'Last 30 Days' },
	{ value: 'last_year', label: 'Last Year' },
	{ value: 'custom', label: 'Custom Range' }
]

const selectedReportType = computed(() => {
	return reportTypes.find(type => type.id === reportType.value)
})

// Current timestamp for report generation
const currentTimestamp = computed(() => {
	const now = new Date()
	return now.toLocaleString('en-US', { 
		year: 'numeric', 
		month: 'long', 
		day: 'numeric', 
		hour: '2-digit', 
		minute: '2-digit',
		second: '2-digit',
		hour12: true 
	})
})

const clearFilters = () => {
	dateRange.value = 'last_30_days'
	startDate.value = ''
	endDate.value = ''
	dateRangeError.value = ''
	clinicFilter.value = 'all'
	brandFilter.value = 'all'
	manufacturerFilter.value = 'all'
}

const handleGenerateReport = async () => {
	// Validate custom date range before generating report
	if (dateRange.value === 'custom') {
		if (!startDate.value || !endDate.value) {
			dateRangeError.value = 'Please select both start and end dates.'
			return
		}
		
		if (dateRangeError.value) {
			// Don't generate report if there's a validation error
			return
		}
	}
	
	const filters = {
		reportType: reportType.value,
		dateRange: dateRange.value,
		startDate: dateRange.value === 'custom' ? startDate.value : undefined,
		endDate: dateRange.value === 'custom' ? endDate.value : undefined,
		clinicFilter: clinicFilter.value !== 'all' ? clinicFilter.value : undefined,
		brandFilter: brandFilter.value !== 'all' ? brandFilter.value : undefined
	}

	// Fetch report data when Generate Report is clicked
	await fetchReportData()
	reportGenerated.value = true

	emit('generateReport', filters)
}

// Toggle export dropdown
const toggleExportDropdown = () => {
	showExportDropdown.value = !showExportDropdown.value
}

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
	if (exportDropdownRef.value && !exportDropdownRef.value.contains(event.target as Node)) {
		showExportDropdown.value = false
	}
}

// Add click outside listener
onMounted(() => {
	document.addEventListener('click', handleClickOutside)
})

// Export as PDF via backend
const exportAsPdf = async () => {
	if (!reportGenerated.value) {
		toast.warning('Please generate a report first before exporting.')
		return
	}
	
	showExportDropdown.value = false
	isExporting.value = true
	
	try {
		const filters: any = {
			report_type: reportType.value,
			date_range: dateRange.value,
			clinic_id: clinicFilter.value,
			brand_id: brandFilter.value,
			manufacturer_id: manufacturerFilter.value,
			anonymize_phi: anonymizePhi.value,
		}
		// Only include dates if they have values (for custom date range)
		if (startDate.value) filters.start_date = startDate.value
		if (endDate.value) filters.end_date = endDate.value

		const response = await api.post('/reports/export/pdf', filters, {
			responseType: 'blob',
		})

		const blob = new Blob([response.data], { type: 'application/pdf' })
		const link = document.createElement('a')
		link.href = URL.createObjectURL(blob)
		
		const reportName = selectedReportType.value?.name.replace(/\s+/g, '_').toLowerCase() || 'report'
		const dateLabel = dateRange.value.replace('_', '-')
		const timestamp = new Date().toISOString().split('T')[0]
		link.download = `${reportName}_${dateLabel}_${timestamp}.pdf`
		
		link.click()
		URL.revokeObjectURL(link.href)
		
		toast.success('PDF exported successfully')
	} catch (error) {
		console.error('❌ Error exporting PDF:', error)
		toast.error('Failed to export PDF. Please try again.')
	} finally {
		isExporting.value = false
	}
}

// Export as Excel
const exportAsExcel = async () => {
	if (!reportGenerated.value) {
		toast.warning('Please generate a report first before exporting.')
		return
	}
	
	showExportDropdown.value = false
	isExporting.value = true
	
	try {
		const filters: any = {
			report_type: reportType.value,
			date_range: dateRange.value,
			clinic_id: clinicFilter.value,
			brand_id: brandFilter.value,
			manufacturer_id: manufacturerFilter.value,
			anonymize_phi: anonymizePhi.value,
		}
		// Only include dates if they have values (for custom date range)
		if (startDate.value) filters.start_date = startDate.value
		if (endDate.value) filters.end_date = endDate.value

		const response = await api.post('/reports/export/excel', filters, {
			responseType: 'blob',
		})

		const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
		const link = document.createElement('a')
		link.href = URL.createObjectURL(blob)
		
		const reportName = selectedReportType.value?.name.replace(/\s+/g, '_').toLowerCase() || 'report'
		const dateLabel = dateRange.value.replace('_', '-')
		const timestamp = new Date().toISOString().split('T')[0]
		link.download = `${reportName}_${dateLabel}_${timestamp}.xlsx`
		
		link.click()
		URL.revokeObjectURL(link.href)
		
		toast.success('Excel exported successfully')
	} catch (error) {
		console.error('Error exporting Excel:', error)
		toast.error('Failed to export Excel. Please try again.')
	} finally {
		isExporting.value = false
	}
}

// Legacy export function (fallback to PDF)
const exportReport = exportAsPdf

const chartRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

function getDateLabels(range: string) {
	// Generate date labels for the selected range
	const today = new Date('2025-01-27') // fixed for mock
	
	let days = 7
	if (range === 'last_30_days') days = 30
	else if (range === 'last_year') days = 365
	if (range === 'custom' && startDate.value && endDate.value) {
		const start = new Date(startDate.value)
		const end = new Date(endDate.value)
		const labels = []
		for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
			labels.push(new Date(d).toISOString().split('T')[0])
		}
		return labels
	}
	const labels = []
	for (let i = days - 1; i >= 0; i--) {
		const d = new Date(today)
		d.setDate(today.getDate() - i)
		labels.push(d.toISOString().split('T')[0])
	}
	return labels
}

function getReportData(type: string, range: string) {
	const labels = getDateLabels(range)
	const data = Array(labels.length).fill(0)
	// Helper filter functions
	const clinic = clinicFilter.value !== 'all' ? clinicFilter.value : null
	const brand = brandFilter.value !== 'all' ? brandFilter.value : null
	if (type === 'orders') {
		orders.value.forEach((o: any) => {
			const d = (o.ordered_at || '').split('T')[0]
			if (clinic && String(o.clinic_id) !== String(clinic)) return
			if (brand && o.items && !o.items.some((item: any) => String(item.brand_id) === String(brand))) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Orders' }
	} else if (type === 'usage') {
		usageLogs.value.forEach((u: any) => {
			const d = u.usageDate || u.usage_date
			if (clinic && String(u.clinicId || u.clinic_id) !== String(clinic)) return
			if (brand && String(u.brandId || u.brand_id) !== String(brand)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Grafts Used' }
	} else if (type === 'inventory') {
		inventory.value.forEach((i: any) => {
			const d = i.expiryDate || i.expiry_date
			if (clinic && String(i.clinicId || i.clinic_id) !== String(clinic)) return
			if (brand && String(i.brandId || i.brand_id) !== String(brand)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Inventory Expiry' }
	} else if (type === 'invoices') {
		invoices.value.forEach((inv: any) => {
			const d = (inv.createdAt || inv.created_at || '').split('T')[0]
			if (clinic && String(inv.clinicId || inv.clinic_id) !== String(clinic)) return
			if (brand && String(inv.brandId || inv.brand_id) !== String(brand)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx] += (inv.amount || 0)
		})
		return { labels, data, label: 'Invoice Amount' }
	} else if (type === 'ivr') {
		ivrRequests.value.forEach((req: any) => {
			const d = req.dateSubmitted || req.date_submitted
			if (clinic && String(req.clinicId || req.clinic_id) !== String(clinic)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'IVR Requests' }
	}
	return { labels: [], data: [], label: '' }
}

function getChartType(): ChartType {
	if (graphType.value === 'area') return 'line'
	return graphType.value as ChartType
}

function getChartOptions() {
	return {
		responsive: true,
		plugins: {
			legend: { display: true }, // Show legend
			title: { display: false }
		},
		scales: {
			x: { grid: { display: false } },
			y: { beginAtZero: true }
		}
	}
}

function renderChart() {
	if (!chartRef.value) return
	if (chartInstance) chartInstance.destroy()
	const { labels, data, label } = getReportData(reportType.value, dateRange.value)
	chartInstance = new Chart(chartRef.value, {
		type: getChartType(),
		data: {
			labels,
			datasets: [{
			label,
			data,
			borderColor: '#7c3aed',
			backgroundColor: 'rgba(124, 58, 237, 0.2)',
			tension: 0.4,
			pointRadius: 3,
			pointBackgroundColor: '#7c3aed',
			}]
		},
		options: getChartOptions()
	})
}

watch([reportType, dateRange, graphType], () => {
	setTimeout(renderChart, 100)
})

onMounted(() => {
	// Only fetch filter options on mount, not the full report data
	fetchFilterOptions()
})

// Add a computed property for table data
const tableData = computed(() => {
	const { labels, data, label } = getReportData(reportType.value, dateRange.value)
	if (reportType.value === 'top_sizes') {
		// Show size and count
		return labels.map((size, i) => ({
			metric: size,
			value: data[i],
			change: ''
		}))
	} else {
		// Show date and value (count or sum)
		return labels.map((date, i) => ({
			metric: date,
			value: data[i],
			change: '' // Optionally, calculate change vs previous day
		}))
	}
})

// Orders Report Computed Properties
// Filtered orders based on clinic and brand filters
const filteredOrders = computed(() => {
	let filtered = orders.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((order: any) => {
			const orderClinicId = String(order.clinic_id || order.clinic?.clinic_id || '')
			return orderClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply brand filter
	if (brandFilter.value !== 'all') {
		filtered = filtered.filter((order: any) => {
			if (order.items && Array.isArray(order.items)) {
				return order.items.some((item: any) => {
					const itemBrandId = String(item.brand_id || item.brandId || '')
					return itemBrandId === String(brandFilter.value)
				})
			}
			return false
		})
	}
	
	// Apply date range filter
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((order: any) => {
			const orderDate = new Date(order.ordered_at || order.created_at)
			return orderDate >= startDateFilter! && orderDate <= endDateFilter!
		})
	}
	
	return filtered
})

const totalOrders = computed(() => {
	return filteredOrders.value.length
})

const totalOrderItems = computed(() => {
	return filteredOrders.value.reduce((sum, order) => {
		const items = typeof order.items === 'string' ? JSON.parse(order.items) : (order.items || [])
		return sum + items.reduce((itemSum: number, item: any) => itemSum + (item.quantity || 1), 0)
	}, 0)
})

const totalOrderValue = computed(() => {
	return filteredOrders.value.reduce((sum, order) => {
		// Calculate from items - orders use 'asp' (Average Selling Price) field
		const items = typeof order.items === 'string' ? JSON.parse(order.items) : (order.items || [])
		return sum + items.reduce((itemSum: number, item: any) => {
			const price = item.asp || item.price || item.unit_price || item.unitPrice || 0
			const quantity = item.quantity || 1
			return itemSum + (price * quantity)
		}, 0)
	}, 0)
})

const dateRangeLabel = computed(() => {
	const options: Record<string, string> = {
		last_7_days: 'Last 7 Days',
		last_30_days: 'Last 30 Days',
		last_year: 'Last Year',
		custom: 'Custom Range'
	}
	return options[dateRange.value] || 'Last 30 Days'
})

const orderStatusBreakdown = computed(() => {
	const statuses: Record<string, { count: number; color: string }> = {
		submitted: { count: 0, color: 'bg-red-500' },
		acknowledged: { count: 0, color: 'bg-purple-500' },
		shipped: { count: 0, color: 'bg-yellow-500' },
		delivered: { count: 0, color: 'bg-green-500' },
		cancelled: { count: 0, color: 'bg-red-500' }
	}
	
	filteredOrders.value.forEach((order: any) => {
		const status = order.order_status || 'submitted'
		if (statuses[status]) {
			statuses[status].count++
		}
	})
	
	const total = filteredOrders.value.length || 1
	return Object.entries(statuses).map(([name, data]) => ({
		name,
		count: data.count,
		percentage: ((data.count / total) * 100).toFixed(1),
		color: data.color
	}))
})

const topClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredOrders.value.forEach((order: any) => {
		const clinicId = order.clinic_id || order.clinic?.clinic_id
		const clinicName = order.clinic?.clinic_name || 'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

const brandDistribution = computed(() => {
	const brandCounts: Record<string, { name: string; count: number; brand_id: string }> = {}
	const colors = ['bg-purple-500', 'bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-rose-500', 'bg-pink-500']
	
	filteredOrders.value.forEach((order: any) => {
		if (order.items && Array.isArray(order.items)) {
			order.items.forEach((item: any) => {
				const brandId = item.brand_id || item.brandId
				
				// Look up brand name from brands array
				let brandName = item.brand_name || order.brand?.brand_name
				if (!brandName && brandId) {
					const brandFromList = brands.value.find(b => String(b.id) === String(brandId))
					brandName = brandFromList?.name || 'Unknown Brand'
				}
				
				if (brandId) {
					if (!brandCounts[brandId]) {
						brandCounts[brandId] = {
							brand_id: String(brandId),
							name: brandName || 'Unknown Brand',
							count: 0
						}
					}
					brandCounts[brandId].count++
				}
			})
		}
	})
	
	return Object.values(brandCounts)
		.sort((a, b) => b.count - a.count)
		.map((brand, index) => ({
			...brand,
			color: colors[index % colors.length]
		}))
})

// Top Products (shown when clinic is filtered)
const topProducts = computed(() => {
	const productCounts: Record<string, { name: string; count: number; product_id: string }> = {}
	
	filteredOrders.value.forEach((order: any) => {
		if (order.items && Array.isArray(order.items)) {
			order.items.forEach((item: any) => {
				const productId = item.product_id || item.productId || item.id
				const productName = item.product_name || item.productName || item.name || 'Unknown Product'
				
				if (productId) {
					if (!productCounts[productId]) {
						productCounts[productId] = {
							product_id: String(productId),
							name: productName,
							count: 0
						}
					}
					productCounts[productId].count++
				}
			})
		}
	})
	
	const sortedProducts = Object.values(productCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedProducts[0]?.count || 1
	return sortedProducts.map(product => ({
		...product,
		percentage: ((product.count / maxCount) * 100).toFixed(0)
	}))
})

// Size Distribution (shown when brand is filtered)
const sizeDistribution = computed(() => {
	const sizeCounts: Record<string, { name: string; count: number; size_id: string }> = {}
	const colors = ['bg-orange-500', 'bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-rose-500', 'bg-pink-500']
	
	filteredOrders.value.forEach((order: any) => {
		if (order.items && Array.isArray(order.items)) {
			order.items.forEach((item: any) => {
				const sizeId = item.graft_size_id || item.graftSizeId || item.size_id || item.sizeId
				const sizeName = item.graft_size_name || item.graftSizeName || item.size_name || item.sizeName || `Size ${sizeId}`
				
				if (sizeId) {
					if (!sizeCounts[sizeId]) {
						sizeCounts[sizeId] = {
							size_id: String(sizeId),
							name: sizeName,
							count: 0
						}
					}
					sizeCounts[sizeId].count++
				}
			})
		}
	})
	
	return Object.values(sizeCounts)
		.sort((a, b) => b.count - a.count)
		.map((size, index) => ({
			...size,
			color: colors[index % colors.length]
		}))
})

// Inventory Report Computed Properties
const totalInventory = computed(() => {
	return filteredInventory.value.length
})

const usedInventoryCount = computed(() => {
	// Count from inventory where log_status = 2 (used)
	// Status mapping: 0=expected, 1=delivered, 2=used, 3=partially_used, 4=reassigned, 5=unused, 6=expired
	return filteredInventory.value.filter((item: any) => {
		const status = Number(item.log_status ?? item.logStatus)
		return status === 2
	}).length
})

const inUseInventoryCount = computed(() => {
	// Count from inventory where log_status = 1 (delivered/in use)
	// Status mapping: 0=expected, 1=delivered, 2=used, 3=partially_used, 4=reassigned, 5=unused, 6=expired
	return filteredInventory.value.filter((item: any) => {
		const status = Number(item.log_status ?? item.logStatus)
		return status === 1
	}).length
})

const filteredInventory = computed(() => {
	let filtered = inventory.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			// API returns: clinicId, patientClinicId (camelCase)
			const itemClinicId = String(item.clinicId || item.clinic_id || 
										 item.patientClinicId || item.patient_clinic_id || '')
			return itemClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply brand filter
	if (brandFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			// API returns: brandId (camelCase)
			const itemBrandId = String(item.brandId || item.brand_id || '')
			return itemBrandId === String(brandFilter.value)
		})
	}
	
	// Apply date range filter (based on various date fields)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((item: any) => {
			// PRIMARY: Use loggedAt (when item was added to inventory system)
			// FALLBACK: Use other date fields if loggedAt is missing
			const dateValue = item.loggedAt || item.logged_at || 
							  item.dateOfService || item.date_of_service || 
							  item.createdAt || item.created_at || 
							  item.deliveryDate || item.delivery_date || 
							  item.usageDate || item.usage_date ||
							  item.updatedAt || item.updated_at
			
			if (!dateValue) {
				return true // Include items without dates
			}
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

const inventoryStatusBreakdown = computed(() => {
	// Map log_status numbers to status names
	// Status mapping: 0=expected, 1=delivered, 2=used, 3=partially_used, 4=reassigned, 5=unused, 6=expired
	const statusMap: Record<number, { name: string; color: string }> = {
		0: { name: 'expected', color: 'bg-gray-500' },
		1: { name: 'delivered', color: 'bg-red-500' },
		2: { name: 'used', color: 'bg-green-500' },
		3: { name: 'partially used', color: 'bg-yellow-500' },
		4: { name: 'reassigned', color: 'bg-orange-500' },
		5: { name: 'unused', color: 'bg-red-500' },
		6: { name: 'expired', color: 'bg-purple-500' }
	}
	
	const statusCounts: Record<string, { count: number; color: string }> = {}
	
	// Initialize all statuses
	Object.values(statusMap).forEach(status => {
		statusCounts[status.name] = { count: 0, color: status.color }
	})
	
	filteredInventory.value.forEach((item: any) => {
		// The API returns both 'logStatus' (number 0-6) and 'status' (string)
		// We need to use 'logStatus' for correct mapping
		const logStatus = Number(item.logStatus ?? item.log_status ?? 0)
		const statusInfo = statusMap[logStatus] || statusMap[0]
		if (statusCounts[statusInfo.name]) {
			statusCounts[statusInfo.name].count++
		}
	})
	
	const total = filteredInventory.value.length || 1
	return Object.entries(statusCounts).map(([name, data]) => ({
		name,
		count: data.count,
		percentage: ((data.count / total) * 100).toFixed(1),
		color: data.color
	}))
})

const topInventoryClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredInventory.value.forEach((item: any) => {
		// API returns: clinicId, patientClinicId, clinicName, patientClinicName (camelCase)
		const clinicId = item.clinicId || item.clinic_id || 
						 item.patientClinicId || item.patient_clinic_id
		const clinicName = item.clinicName || item.clinic_name || 
						   item.patientClinicName || item.patient_clinic_name ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

const inventoryBrandDistribution = computed(() => {
	const brandCounts: Record<string, { name: string; count: number; brand_id: string }> = {}
	const colors = ['bg-purple-500', 'bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-rose-500', 'bg-pink-500']
	
	filteredInventory.value.forEach((item: any) => {
		// API returns: brandId, brandName (camelCase)
		const brandId = item.brandId || item.brand_id
		let brandName = item.brandName || item.brand_name
		
		if (!brandName && brandId) {
			const brandFromList = brands.value.find(b => String(b.id) === String(brandId))
			brandName = brandFromList?.name || 'Unknown Brand'
		}
		
		if (brandId) {
			if (!brandCounts[brandId]) {
				brandCounts[brandId] = {
					brand_id: String(brandId),
					name: brandName || 'Unknown Brand',
					count: 0
				}
			}
			brandCounts[brandId].count++
		}
	})
	
	return Object.values(brandCounts)
		.sort((a, b) => b.count - a.count)
		.map((brand, index) => ({
			...brand,
			color: colors[index % colors.length]
		}))
})

const utilizationRate = computed(() => {
	// Delivered = status 1 and above (not expected=0)
	const delivered = filteredInventory.value.filter((item: any) => {
		const logStatus = Number(item.logStatus ?? item.log_status ?? 0)
		return logStatus >= 1
	}).length
	
	// Used = status 2 (used) or 3 (partially used)
	const used = filteredInventory.value.filter((item: any) => {
		const logStatus = Number(item.logStatus ?? item.log_status ?? 0)
		return logStatus === 2 || logStatus === 3
	}).length
	
	if (delivered === 0) return '0'
	return ((used / delivered) * 100).toFixed(1)
})

// ========== USAGE REPORT COMPUTED PROPERTIES ==========
const filteredUsageLogs = computed(() => {
	let filtered = usageLogs.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			const itemClinicId = String(item.clinicId || item.clinic_id || 
										 item.patientClinicId || item.patient_clinic_id || '')
			return itemClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply brand filter
	if (brandFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			const itemBrandId = String(item.brandId || item.brand_id || '')
			return itemBrandId === String(brandFilter.value)
		})
	}
	
	// Apply date range filter (use loggedAt as primary)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((item: any) => {
			const dateValue = item.loggedAt || item.logged_at || 
							  item.dateOfService || item.date_of_service ||
							  item.createdAt || item.created_at
			if (!dateValue) return true
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

const usageByWoundPart = computed(() => {
	const woundPartCounts: Record<string, number> = {}
	const colors = ['bg-orange-500', 'bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-rose-500', 'bg-pink-500']
	
	filteredUsageLogs.value.forEach((item: any) => {
		const woundPart = item.woundPart || item.wound_part || 'Unknown'
		if (!woundPartCounts[woundPart]) {
			woundPartCounts[woundPart] = 0
		}
		woundPartCounts[woundPart]++
	})
	
	return Object.entries(woundPartCounts)
		.sort(([, a], [, b]) => b - a)
		.map(([name, count], index) => ({
			name,
			count,
			color: colors[index % colors.length]
		}))
})

const topUsageClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredUsageLogs.value.forEach((item: any) => {
		const clinicId = item.clinicId || item.clinic_id || item.patientClinicId || item.patient_clinic_id
		const clinicName = item.clinicName || item.clinic_name || 
						   item.patientClinicName || item.patient_clinic_name ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

const usageBrandDistribution = computed(() => {
	const brandCounts: Record<string, { name: string; count: number; brand_id: string }> = {}
	const colors = ['bg-purple-500', 'bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-rose-500', 'bg-pink-500']
	
	filteredUsageLogs.value.forEach((item: any) => {
		const brandId = item.brandId || item.brand_id
		let brandName = item.brandName || item.brand_name
		
		if (!brandName && brandId) {
			const brandFromList = brands.value.find(b => String(b.id) === String(brandId))
			brandName = brandFromList?.name || 'Unknown Brand'
		}
		
		if (brandId) {
			if (!brandCounts[brandId]) {
				brandCounts[brandId] = {
					brand_id: String(brandId),
					name: brandName || 'Unknown Brand',
					count: 0
				}
			}
			brandCounts[brandId].count++
		}
	})
	
	return Object.values(brandCounts)
		.sort((a, b) => b.count - a.count)
		.map((brand, index) => ({
			...brand,
			color: colors[index % colors.length]
		}))
})

// ========== INVOICE REPORT COMPUTED PROPERTIES ==========
const filteredInvoices = computed(() => {
	let filtered = invoices.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((invoice: any) => {
			const invoiceClinicId = String(invoice.clinic_id || invoice.clinicId || '')
			return invoiceClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply date range filter (use created_at or invoice_date)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((invoice: any) => {
			const dateValue = invoice.invoice_date || invoice.invoiceDate || 
							  invoice.created_at || invoice.createdAt
			if (!dateValue) {
				return true
			}
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

const totalInvoiceAmount = computed(() => {
	return filteredInvoices.value.reduce((sum, invoice: any) => {
		return sum + (Number(invoice.amount) || 0)
	}, 0)
})

const pendingInvoiceAmount = computed(() => {
	return filteredInvoices.value
		.filter((invoice: any) => invoice.status === 'pending' || invoice.status === 'pending_review')
		.reduce((sum, invoice: any) => sum + (Number(invoice.amount) || 0), 0)
})

const invoiceStatusBreakdown = computed(() => {
	const statusCounts: Record<string, { count: number; amount: number; color: string; displayName: string }> = {
		pending: { count: 0, amount: 0, color: 'bg-yellow-500', displayName: 'Pending' },
		pending_review: { count: 0, amount: 0, color: 'bg-orange-500', displayName: 'Pending Review' },
		paid: { count: 0, amount: 0, color: 'bg-green-500', displayName: 'Paid' },
		overdue: { count: 0, amount: 0, color: 'bg-red-500', displayName: 'Overdue' },
		cancelled: { count: 0, amount: 0, color: 'bg-gray-500', displayName: 'Cancelled' }
	}
	
	filteredInvoices.value.forEach((invoice: any) => {
		const status = invoice.status || 'pending'
		if (statusCounts[status]) {
			statusCounts[status].count++
			statusCounts[status].amount += Number(invoice.amount) || 0
		}
	})
	
	return Object.entries(statusCounts)
		.map(([name, data]) => ({
			name,
			displayName: data.displayName,
			count: data.count,
			amount: data.amount,
			color: data.color
		}))
		.filter(status => status.count > 0) // Only show statuses with data
})

const topInvoiceClinics = computed(() => {
	const clinicAmounts: Record<string, { name: string; amount: number; clinic_id: string }> = {}
	
	filteredInvoices.value.forEach((invoice: any) => {
		const clinicId = invoice.clinic_id || invoice.clinicId
		const clinicName = invoice.clinic?.clinic_name || invoice.clinic?.clinicName ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicAmounts[clinicId]) {
				clinicAmounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					amount: 0
				}
			}
			clinicAmounts[clinicId].amount += Number(invoice.amount) || 0
		}
	})
	
	const sortedClinics = Object.values(clinicAmounts)
		.sort((a, b) => b.amount - a.amount)
		.slice(0, 5)
	
	const maxAmount = sortedClinics[0]?.amount || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.amount / maxAmount) * 100).toFixed(0)
	}))
})

// ========== IVR REPORT COMPUTED PROPERTIES ==========
const filteredIVRRequests = computed(() => {
	let filtered = ivrRequests.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((ivr: any) => {
			const ivrClinicId = String(ivr.clinic_id || ivr.clinicId || '')
			return ivrClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply manufacturer filter
	if (manufacturerFilter.value !== 'all') {
		filtered = filtered.filter((ivr: any) => {
			const ivrManufacturerId = String(ivr.manufacturer_id || ivr.manufacturerId || '')
			return ivrManufacturerId === String(manufacturerFilter.value)
		})
	}
	
	// Apply date range filter (use submitted_at or created_at)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((ivr: any) => {
			const dateValue = ivr.submitted_at || ivr.submittedAt || 
							  ivr.created_at || ivr.createdAt
			if (!dateValue) {
				return true
			}
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

// ========== PATIENT GRAFT APPLICATION COMPUTED PROPERTIES ==========
const filteredPatientGraftLogs = computed(() => {
	let filtered = patientGraftLogs.value

	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((log: any) => {
			const cId = String(log.clinic_id || log.clinicId || '')
			return cId === String(clinicFilter.value)
		})
	}

	// Apply brand filter
	if (brandFilter.value !== 'all') {
		filtered = filtered.filter((log: any) => {
			const bId = String(log.brand_id || log.brandId || '')
			return bId === String(brandFilter.value)
		})
	}

	// Apply date range filter
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null

	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}

	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((log: any) => {
			if (!log.date_of_service) return true
			const itemDate = new Date(log.date_of_service)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}

	return filtered
})

const ivrEligibilityBreakdown = computed(() => {
	// eligibility_status: 0 = pending, 1 = approved, 2 = denied
	const statusCounts: Record<string, { count: number; color: string }> = {
		pending: { count: 0, color: 'bg-yellow-500' },
		approved: { count: 0, color: 'bg-green-500' },
		denied: { count: 0, color: 'bg-red-500' }
	}
	
	filteredIVRRequests.value.forEach((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		const statusName = eligibilityStatus === 1 ? 'approved' : eligibilityStatus === 2 ? 'denied' : 'pending'
		
		if (statusCounts[statusName]) {
			statusCounts[statusName].count++
		}
	})
	
	return Object.entries(statusCounts)
		.map(([name, data]) => ({
			name,
			count: data.count,
			color: data.color
		}))
		.filter(status => status.count > 0)
})

const topIVRManufacturers = computed(() => {
	const manufacturerCounts: Record<string, { name: string; count: number; manufacturer_id: string }> = {}
	
	filteredIVRRequests.value.forEach((ivr: any) => {
		const manufacturerId = ivr.manufacturer_id || ivr.manufacturerId
		const manufacturerName = ivr.manufacturer?.manufacturer_name || ivr.manufacturer?.manufacturerName ||
								 'Unknown Manufacturer'
		
		if (manufacturerId) {
			if (!manufacturerCounts[manufacturerId]) {
				manufacturerCounts[manufacturerId] = {
					manufacturer_id: String(manufacturerId),
					name: manufacturerName,
					count: 0
				}
			}
			manufacturerCounts[manufacturerId].count++
		}
	})
	
	const sortedManufacturers = Object.values(manufacturerCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedManufacturers[0]?.count || 1
	return sortedManufacturers.map(mfr => ({
		...mfr,
		percentage: ((mfr.count / maxCount) * 100).toFixed(0)
	}))
})

const topIVRClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredIVRRequests.value.forEach((ivr: any) => {
		const clinicId = ivr.clinic_id || ivr.clinicId
		const clinicName = ivr.clinic?.clinic_name || ivr.clinic?.clinicName ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

// Manufacturer Approval Rate - for filtered manufacturer
const manufacturerApprovalRate = computed(() => {
	const total = filteredIVRRequests.value.length
	if (total === 0) return '0'
	
	const approved = filteredIVRRequests.value.filter((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		return eligibilityStatus === 1 // approved
	}).length
	
	return ((approved / total) * 100).toFixed(1)
})

// Overall Approval Rate - for all IVR requests
const overallApprovalRate = computed(() => {
	const total = filteredIVRRequests.value.length
	if (total === 0) return '0'
	
	const approved = filteredIVRRequests.value.filter((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		return eligibilityStatus === 1 // approved
	}).length
	
	return ((approved / total) * 100).toFixed(1)
})

// Clinic Approval Rate - for filtered clinic
const clinicApprovalRate = computed(() => {
	const total = filteredIVRRequests.value.length
	if (total === 0) return '0'
	
	const approved = filteredIVRRequests.value.filter((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		return eligibilityStatus === 1 // approved
	}).length
	
	return ((approved / total) * 100).toFixed(1)
})

// Chart refs for Orders Report
const statusChartRef = ref<HTMLCanvasElement | null>(null)
const brandChartRef = ref<HTMLCanvasElement | null>(null)
const sizeChartRef = ref<HTMLCanvasElement | null>(null)
const inventoryStatusChartRef = ref<HTMLCanvasElement | null>(null)
const inventoryBrandChartRef = ref<HTMLCanvasElement | null>(null)
// Chart refs for Usage Report
const usageWoundPartChartRef = ref<HTMLCanvasElement | null>(null)
const usageBrandChartRef = ref<HTMLCanvasElement | null>(null)
// Chart refs for Invoice Report
const invoiceStatusChartRef = ref<HTMLCanvasElement | null>(null)
// Chart refs for IVR Report
const ivrEligibilityChartRef = ref<HTMLCanvasElement | null>(null)

let statusChartInstance: Chart | null = null
let brandChartInstance: Chart | null = null
let sizeChartInstance: Chart | null = null
let inventoryStatusChartInstance: Chart | null = null
let inventoryBrandChartInstance: Chart | null = null
let usageWoundPartChartInstance: Chart | null = null
let usageBrandChartInstance: Chart | null = null
let invoiceStatusChartInstance: Chart | null = null
let ivrEligibilityChartInstance: Chart | null = null

// Render status breakdown pie chart
function renderStatusChart() {
	if (!statusChartRef.value) return
	if (statusChartInstance) statusChartInstance.destroy()
	
	const statusData = orderStatusBreakdown.value
	statusChartInstance = new Chart(statusChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1)),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: ['#3b82f6', '#a855f7', '#eab308', '#22c55e', '#ef4444'],
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							const total = (context.dataset.data as number[]).reduce((a, b) => a + b, 0)
							const percentage = ((value / total) * 100).toFixed(1)
							return `${label}: ${value} (${percentage}%)`
						}
					}
				}
			}
		}
	})
}

// Render brand distribution donut chart
function renderBrandChart() {
	if (!brandChartRef.value) return
	if (brandChartInstance) brandChartInstance.destroy()
	
	const brandData = brandDistribution.value
	const colors = ['#a855f7', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	brandChartInstance = new Chart(brandChartRef.value, {
		type: 'doughnut',
		data: {
			labels: brandData.map(b => b.name),
			datasets: [{
				data: brandData.map(b => b.count),
				backgroundColor: colors.slice(0, brandData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} orders`
						}
					}
				}
			}
		}
	})
}

// Render size distribution donut chart
function renderSizeChart() {
	if (!sizeChartRef.value) return
	if (sizeChartInstance) sizeChartInstance.destroy()
	
	const sizeData = sizeDistribution.value
	const colors = ['#f97316', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	sizeChartInstance = new Chart(sizeChartRef.value, {
		type: 'doughnut',
		data: {
			labels: sizeData.map(s => s.name),
			datasets: [{
				data: sizeData.map(s => s.count),
				backgroundColor: colors.slice(0, sizeData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} orders`
						}
					}
				}
			}
		}
	})
}

// Render inventory status breakdown pie chart
function renderInventoryStatusChart() {
	if (!inventoryStatusChartRef.value) return
	if (inventoryStatusChartInstance) inventoryStatusChartInstance.destroy()
	
	const statusData = inventoryStatusBreakdown.value
	const colors = ['#6b7280', '#3b82f6', '#22c55e', '#eab308', '#f97316', '#ef4444', '#a855f7']
	
	inventoryStatusChartInstance = new Chart(inventoryStatusChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1)),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: colors,
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							const total = (context.dataset.data as number[]).reduce((a, b) => a + b, 0)
							const percentage = ((value / total) * 100).toFixed(1)
							return `${label}: ${value} (${percentage}%)`
						}
					}
				}
			}
		}
	})
}

// Render inventory brand distribution donut chart
function renderInventoryBrandChart() {
	if (!inventoryBrandChartRef.value) return
	if (inventoryBrandChartInstance) inventoryBrandChartInstance.destroy()
	
	const brandData = inventoryBrandDistribution.value
	const colors = ['#a855f7', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	inventoryBrandChartInstance = new Chart(inventoryBrandChartRef.value, {
		type: 'doughnut',
		data: {
			labels: brandData.map(b => b.name),
			datasets: [{
				data: brandData.map(b => b.count),
				backgroundColor: colors.slice(0, brandData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} items`
						}
					}
				}
			}
		}
	})
}

// ========== USAGE REPORT CHART RENDERING ==========
function renderUsageWoundPartChart() {
	if (!usageWoundPartChartRef.value) return
	if (usageWoundPartChartInstance) usageWoundPartChartInstance.destroy()
	
	const data = usageByWoundPart.value
	const colors = ['#f97316', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	usageWoundPartChartInstance = new Chart(usageWoundPartChartRef.value, {
		type: 'doughnut',
		data: {
			labels: data.map(d => d.name),
			datasets: [{
				data: data.map(d => d.count),
				backgroundColor: colors.slice(0, data.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} uses`
						}
					}
				}
			}
		}
	})
}

function renderUsageBrandChart() {
	if (!usageBrandChartRef.value) return
	if (usageBrandChartInstance) usageBrandChartInstance.destroy()
	
	const brandData = usageBrandDistribution.value
	const colors = ['#a855f7', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	usageBrandChartInstance = new Chart(usageBrandChartRef.value, {
		type: 'doughnut',
		data: {
			labels: brandData.map(b => b.name),
			datasets: [{
				data: brandData.map(b => b.count),
				backgroundColor: colors.slice(0, brandData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} uses`
						}
					}
				}
			}
		}
	})
}

// ========== INVOICE REPORT CHART RENDERING ==========
function renderInvoiceStatusChart() {
	if (!invoiceStatusChartRef.value) return
	if (invoiceStatusChartInstance) invoiceStatusChartInstance.destroy()
	
	const statusData = invoiceStatusBreakdown.value
	const colors = ['#eab308', '#f97316', '#22c55e', '#ef4444', '#6b7280']
	
	invoiceStatusChartInstance = new Chart(invoiceStatusChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1).replace('_', ' ')),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: colors,
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const idx = context.dataIndex
							const status = statusData[idx]
							return `${context.label}: ${status.count} ($${status.amount.toLocaleString()})`
						}
					}
				}
			}
		}
	})
}

// ========== IVR REPORT CHART RENDERING ==========
function renderIVREligibilityChart() {
	if (!ivrEligibilityChartRef.value) return
	if (ivrEligibilityChartInstance) ivrEligibilityChartInstance.destroy()
	
	const statusData = ivrEligibilityBreakdown.value
	// Map Tailwind color classes to hex colors for Chart.js
	const colorMap: Record<string, string> = {
		'bg-yellow-500': '#eab308',
		'bg-green-500': '#22c55e',
		'bg-red-500': '#ef4444'
	}
	const colors = statusData.map(s => colorMap[s.color] || '#6b7280')
	
	ivrEligibilityChartInstance = new Chart(ivrEligibilityChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1)),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: colors,
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							const total = (context.dataset.data as number[]).reduce((a, b) => a + b, 0)
							const percentage = ((value / total) * 100).toFixed(1)
							return `${label}: ${value} (${percentage}%)`
						}
					}
				}
			}
		}
	})
}

// Watch for report type changes to render appropriate charts
watch([reportType, dateRange, orders, inventory, usageLogs, invoices, ivrRequests, clinicFilter, brandFilter, startDate, endDate], () => {
	if (reportType.value === 'orders') {
		setTimeout(() => {
			renderStatusChart()
			// Render brand chart or size chart based on filter
			if (brandFilter.value !== 'all') {
				renderSizeChart()
			} else {
				renderBrandChart()
			}
		}, 100)
	} else if (reportType.value === 'inventory') {
		setTimeout(() => {
			renderInventoryStatusChart()
			renderInventoryBrandChart()
		}, 100)
	} else if (reportType.value === 'usage') {
		setTimeout(() => {
			renderUsageWoundPartChart()
			renderUsageBrandChart()
		}, 100)
	} else if (reportType.value === 'invoices') {
		setTimeout(() => {
			renderInvoiceStatusChart()
		}, 100)
	} else if (reportType.value === 'ivr') {
		setTimeout(() => {
			renderIVREligibilityChart()
		}, 100)
	} else {
		setTimeout(renderChart, 100)
	}
}, { deep: true })

// Validate custom date range (maximum 2 months / 60 days)
watch([startDate, endDate], () => {
	if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		const start = new Date(startDate.value)
		const end = new Date(endDate.value)
		
		// Calculate difference in days
		const diffTime = Math.abs(end.getTime() - start.getTime())
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
		
		if (diffDays > 60) {
			dateRangeError.value = `Date range is ${diffDays} days. Please select a range within 2 months (60 days) for optimal performance.`
		} else if (end < start) {
			dateRangeError.value = 'End date must be after start date.'
		} else {
			dateRangeError.value = ''
		}
	} else {
		dateRangeError.value = ''
	}
})
</script>