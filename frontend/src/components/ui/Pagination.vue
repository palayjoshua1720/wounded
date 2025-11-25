<template>
	<div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
		
		<!-- Results Summary -->
		<p class="text-sm text-gray-600 dark:text-gray-400">
			Showing <span class="font-semibold text-gray-800 dark:text-white">{{ start }}</span> to <span class="font-semibold text-gray-800 dark:text-white">{{ end }}</span> of <span class="font-semibold text-gray-800 dark:text-white">{{ pagination.total }}</span> results
		</p>

		<!-- Pagination Buttons -->
		<nav class="flex items-center space-x-2">
			<!-- Prev -->
			<button
				class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
				:disabled="pagination.current_page === 1"
				@click="changePage(pagination.current_page - 1)"
			>
				Previous
			</button>

			<!-- Page Numbers -->
			<div class="flex items-center space-x-1">
				<button
					v-for="page in pagination.last_page"
					:key="page"
					class="border px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
					:class="page === pagination.current_page 
					? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
					: 'bg-white text-gray-600 border-gray-300 hover:bg-indigo-50 hover:text-blue-600'"
					@click="changePage(page)"
				>
					{{ page }}
				</button>
			</div>

			<!-- Next -->
			<button
				class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
				:disabled="pagination.current_page === pagination.last_page"
				@click="changePage(pagination.current_page + 1)"
			>
				Next
			</button>
		</nav>
	</div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, computed } from 'vue'

const props = defineProps<{
  	pagination: {
		current_page: number
		last_page: number
		per_page: number
		total: number
	}
}>()

const emit = defineEmits<{
  	(e: 'update:page', page: number): void
}>()

function changePage(page: number) {
	if (page < 1 || page > props.pagination.last_page) return
	emit('update:page', page)
}

const start = computed(() => {
	return (props.pagination.current_page - 1) * props.pagination.per_page + 1
})

const end = computed(() => {
	return Math.min(
		props.pagination.current_page * props.pagination.per_page,
		props.pagination.total
	)
})
</script>

<style scoped>
.mt-custom {
 	 margin-top: 0.5rem !important;
}
</style>
