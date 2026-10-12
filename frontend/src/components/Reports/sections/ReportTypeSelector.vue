<template>
	<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
		<div class="flex items-center justify-between mb-6">
			<div>
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Select Report Type</h3>
				<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Choose the type of report you want to generate</p>
			</div>
		</div>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
			<div
				v-for="type in reportTypes"
				:key="type.id"
				@click="$emit('select', type.id)"
				:class="[
					'p-4 border-2 rounded-xl cursor-pointer transition-all duration-300 group relative overflow-hidden',
					selectedType === type.id
						? 'border-red-500 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/20 shadow-md'
						: 'border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-700 hover:shadow-sm'
				]"
			>
				<!-- Selected Indicator -->
				<div v-if="selectedType === type.id" class="absolute top-3 right-3">
					<div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
						<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
						</svg>
					</div>
				</div>
				
				<div class="flex items-start space-x-3">
					<div :class="[
						'p-2.5 rounded-xl transition-all duration-300 shadow-sm',
						selectedType === type.id 
							? 'bg-gradient-to-br from-red-500 to-rose-600 shadow-red-200 dark:shadow-red-900/50' 
							: 'bg-gray-100 dark:bg-gray-700 group-hover:bg-red-50 dark:group-hover:bg-red-900/30'
					]">
						<component 
							:is="type.icon" 
							:class="[
								'w-5 h-5 transition-all duration-300',
								selectedType === type.id ? 'text-white' : 'text-gray-500 dark:text-gray-400 group-hover:text-red-500'
							]" 
						/>
					</div>
					<div class="flex-1 pr-6">
						<h4 :class="[
							'font-semibold text-sm mb-0.5',
							selectedType === type.id ? 'text-red-900 dark:text-red-100' : 'text-gray-900 dark:text-white'
						]">
							{{ type.name }}
						</h4>
						<p :class="[
							'text-xs leading-relaxed',
							selectedType === type.id ? 'text-red-700 dark:text-red-300' : 'text-gray-500 dark:text-gray-400'
						]">
							{{ type.description }}
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
interface ReportType {
	id: string
	name: string
	icon: any
	description: string
}

defineProps<{
	reportTypes: ReportType[]
	selectedType: string
}>()

defineEmits<{
	(e: 'select', typeId: string): void
}>()
</script>
