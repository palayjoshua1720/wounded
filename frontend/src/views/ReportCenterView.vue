<template>
	<div class="space-y-8">
		<!-- Header -->
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
			<div class="space-y-2">
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reports & Analytics</h1>
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
	// Report generation is handled within ReportsModule component
}
</script>