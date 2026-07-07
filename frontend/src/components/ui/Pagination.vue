<template>
	<div
		class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 px-3 sm:px-6 py-3 sm:py-4 bg-white dark:bg-gray-800 rounded-2xl sm:rounded-none border border-gray-100 dark:border-gray-700 sm:border-0 sm:border-t sm:border-gray-200 dark:sm:border-gray-700 shadow-sm sm:shadow-none">
		<!-- <div
		class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 bg-white dark:bg-gray-800 rounded-2xl sm:rounded-none"> -->

		<!-- Results Summary (Desktop only) -->
		<p class="hidden sm:block text-sm text-gray-600 dark:text-gray-400">
			Showing <span class="font-semibold text-gray-800 dark:text-white">{{ start }}</span> to <span
				class="font-semibold text-gray-800 dark:text-white">{{ end }}</span> of <span
				class="font-semibold text-gray-800 dark:text-white">{{ pagination.total }}</span> results
		</p>

		<!-- Mobile Pagination (Simplified) -->
		<nav class="flex sm:hidden w-full items-center justify-between">
			<!-- Prev Button -->
			<button
				class="flex items-center justify-center gap-1.5 flex-1 max-w-[120px] py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
				:disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
				</svg>
				Prev
			</button>

			<!-- Page Counter -->
			<div class="flex items-center gap-1.5 px-4">
				<span class="text-lg font-bold text-gray-900 dark:text-white">{{ pagination.current_page }}</span>
				<span class="text-gray-400">/</span>
				<span class="text-base text-gray-500 dark:text-gray-400">{{ pagination.last_page }}</span>
			</div>

			<!-- Next Button -->
			<button
				class="flex items-center justify-center gap-1.5 flex-1 max-w-[120px] py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
				:disabled="pagination.current_page === pagination.last_page"
				@click="changePage(pagination.current_page + 1)">
				Next
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
				</svg>
			</button>
		</nav>

		<!-- Desktop Pagination Buttons -->
		<nav class="hidden sm:flex items-center gap-2">
			<!-- Prev -->
			<button
				class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
				:disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
				Previous
			</button>

			<!-- Page Numbers -->
			<div class="flex items-center gap-1">
				<template v-for="(page, index) in visiblePages" :key="index">
					<span v-if="page === '...'" class="px-2 py-1.5 text-sm text-gray-500 dark:text-gray-400">...</span>
					<button v-else class="border px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
						:class="page === pagination.current_page
							? 'bg-blue-600 text-white border-blue-600 shadow-sm'
							: 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:bg-indigo-50 dark:hover:bg-gray-700 hover:text-blue-600'" @click="changePage(page as number)">
						{{ page }}
					</button>
				</template>
			</div>

			<!-- Next -->
			<button
				class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
				:disabled="pagination.current_page === pagination.last_page"
				@click="changePage(pagination.current_page + 1)">
				Next
			</button>
		</nav>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

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

// Compute visible page numbers with ellipsis
const visiblePages = computed(() => {
	const current = props.pagination.current_page
	const last = props.pagination.last_page
	const pages: (number | string)[] = []

	if (last <= 7) {
		// Show all pages if 7 or fewer
		for (let i = 1; i <= last; i++) {
			pages.push(i)
		}
	} else {
		// Always show first page
		pages.push(1)

		if (current <= 3) {
			// Near start: show 1 2 3 4 5 ... last
			for (let i = 2; i <= 5; i++) {
				pages.push(i)
			}
			pages.push('...')
			pages.push(last)
		} else if (current >= last - 2) {
			// Near end: show 1 ... last-4 last-3 last-2 last-1 last
			pages.push('...')
			for (let i = last - 4; i <= last; i++) {
				pages.push(i)
			}
		} else {
			// Middle: show 1 ... current-1 current current+1 ... last
			pages.push('...')
			for (let i = current - 1; i <= current + 1; i++) {
				pages.push(i)
			}
			pages.push('...')
			pages.push(last)
		}
	}

	return pages
})
</script>

<style scoped>
.mt-custom {
	margin-top: 0.5rem !important;
}
</style>
