<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports & Analytics</h1>
				<!-- <p class="text-gray-600 dark:text-gray-400">Generate and view comprehensive system reports</p> -->
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