<template>
	<transition name="fade">
		<div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden">
			<div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close" />
			<div :class="['relative rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 mx-4 my-8 overflow-hidden', widthClass]" class="max-h-[90vh] w-full flex flex-col" style="contain: layout style;">
				<div class="flex items-center justify-between p-4 border-b flex-shrink-0">
					<h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
					<button @click="close" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-2xl leading-none">&times;</button>
				</div>
				<div class="p-6 flex-1 overflow-y-auto overflow-x-hidden">
					<slot />
				</div>
				<div v-if="$slots.actions" class="border-t flex justify-end space-x-3 flex-shrink-0">
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
.fade-enter-active, .fade-leave-active {
	transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
	opacity: 0;
}
</style> 