<template>
	<transition name="fade">
		<div v-if="modelValue" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center overflow-y-auto overflow-x-hidden">
			<div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close" />
			<div :class="['relative rounded-t-2xl sm:rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 sm:mx-4 my-0 sm:my-8 overflow-hidden w-full', widthClass]" class="max-h-[90vh] flex flex-col" style="contain: layout style;">
				<!-- Header - Fixed -->
				<div class="flex items-center justify-between p-4 border-b flex-shrink-0 bg-white dark:bg-gray-800 sticky top-0 z-10">
					<h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white pr-4 line-clamp-1">{{ title }}</h2>
					<button @click="close" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-2xl leading-none flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">&times;</button>
				</div>
				<!-- Content - Scrollable -->
				<div class="p-4 sm:p-6 flex-1 overflow-y-auto overflow-x-hidden">
					<slot />
				</div>
				<!-- Actions Footer -->
				<div v-if="$slots.actions" class="border-t border-gray-100 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800 p-3">
					<slot name="actions" />
				</div>
			</div>
		</div>
	</transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'
const props = defineProps({
	modelValue: Boolean,
	title: String,
	width: {
		type: String,
		default: 'max-w-4xl w-full'
	}
})
const emit = defineEmits(['update:modelValue', 'close-form'])
const close = () => {
	emit('close-form')
	emit('update:modelValue', false)
}
const widthClass = computed(() => props.width)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
</style>