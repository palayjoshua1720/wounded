<template>
	<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
		<!-- Title Row -->
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
			<div>
				<h2 class="text-xl font-bold text-gray-900 dark:text-white">
					{{ reportName }}
				</h2>
				<p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
					{{ dateRangeLabel }}
				</p>
			</div>
			<div class="sm:text-right">
				<span class="inline-flex items-center px-2.5 py-1 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-xs font-medium rounded-md">
					Generated {{ currentTimestamp }}
				</span>
			</div>
		</div>

		<!-- Filter Badges -->
		<div class="flex flex-wrap gap-2">
			<span class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
				<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
				</svg>
				{{ dateRangeLabel }}
			</span>
			<span class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
				<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
				</svg>
				{{ clinicName }}
			</span>
			<span v-if="showBrandBadge" class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
				<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
				</svg>
				{{ brandName }}
			</span>
			<span v-else-if="showManufacturerBadge" class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
				<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
				</svg>
				{{ manufacturerName }}
			</span>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
	reportName: string
	dateRangeLabel: string
	currentTimestamp: string
	clinicFilter: string
	clinics: any[]
	brandFilter: string
	brands: any[]
	manufacturerFilter: string
	manufacturers: any[]
	reportType: string
}>()

const clinicName = computed(() => {
	return props.clinicFilter === 'all' 
		? 'All Clinics' 
		: props.clinics.find((c: any) => c.id === props.clinicFilter)?.name || 'Unknown'
})

const brandName = computed(() => {
	return props.brandFilter === 'all' 
		? 'All Brands' 
		: props.brands.find((b: any) => b.id === props.brandFilter)?.name || 'Unknown'
})

const manufacturerName = computed(() => {
	return props.manufacturerFilter === 'all' 
		? 'All Manufacturers' 
		: (props.manufacturers.find((m: any) => String(m.id) === String(props.manufacturerFilter))?.name || 'Unknown')
})

const showBrandBadge = computed(() => {
	return ['orders', 'inventory', 'usage'].includes(props.reportType)
})

const showManufacturerBadge = computed(() => {
	return props.reportType === 'ivr'
})
</script>
