<template>
	<div class="flex justify-end items-center mt-custom">
		<nav class="flex items-center gap-1">
			<!-- Prev -->
			<button
				class="w-8 h-8 flex items-center justify-center rounded border text-sm transition 
					disabled:opacity-40 disabled:cursor-not-allowed
					bg-white border-gray-300 text-gray-500 
					hover:bg-indigo-50 hover:text-indigo-600"
				:disabled="pagination.current_page === 1"
				@click="changePage(pagination.current_page - 1)"
			>
				&lt;
			</button>

			<!-- Page Numbers -->
			<button
				v-for="page in pagination.last_page"
				:key="page"
				class="w-8 h-8 flex items-center justify-center rounded border text-sm transition"
				:class="page === pagination.current_page 
				? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' 
				: 'bg-white text-gray-600 border-gray-300 hover:bg-indigo-50 hover:text-indigo-600'"
				@click="changePage(page)"
			>
				{{ page }}
			</button>

			<!-- Next -->
			<button
				class="w-8 h-8 flex items-center justify-center rounded border text-sm transition 
					disabled:opacity-40 disabled:cursor-not-allowed
					bg-white border-gray-300 text-gray-500 
					hover:bg-indigo-50 hover:text-indigo-600"
				:disabled="pagination.current_page === pagination.last_page"
				@click="changePage(pagination.current_page + 1)"
			>
				&gt;
			</button>
		</nav>
	</div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'

const props = defineProps<{
  pagination: { current_page: number; last_page: number }
}>()

const emit = defineEmits<{
  (e: 'update:page', page: number): void
}>()

function changePage(page: number) {
  if (page < 1 || page > props.pagination.last_page) return
  emit('update:page', page)
}
</script>

<style scoped>
.mt-custom {
 	 margin-top: 0.5rem !important;
}
</style>
