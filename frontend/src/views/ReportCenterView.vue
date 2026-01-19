<template>
	<div class="space-y-8">
		<!-- Header -->
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
			<div class="space-y-2">
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reports & Analytics</h1>
				<p class="text-gray-600 dark:text-gray-400 max-w-2xl">Generate comprehensive reports and analyze system data across all modules</p>
			</div>
		</div>

		<!-- Reports Module -->
		<ReportsModule @generate-report="handleGenerateReport" />
	</div>
</template>

<script setup lang="ts">
import ReportsModule from '@/components/Reports/ReportsModule.vue'

function handleGenerateReport(filters: any) {
	console.log('Generating report with filters:', filters)
	// Implement report generation logic here
	// For now, just show a success message
	alert(`Report generated successfully with filters: ${JSON.stringify(filters)}`)
}
</script> 