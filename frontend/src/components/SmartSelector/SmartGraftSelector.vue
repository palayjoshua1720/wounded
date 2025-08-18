<template>
	<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
		<div class="flex items-center space-x-3 mb-6">
			<div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
				<CalculatorIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
			</div>
			<div>
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white">Smart Graft Selector</h2>
				<p class="text-gray-600 dark:text-gray-400">Find the optimal graft combination for your wound size</p>
			</div>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
			<div>
				<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Brand *</label>
				<select v-model="selectedBrand" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
					<option value="">Choose a brand</option>
					<option v-for="brand in brands.filter(b => b.productType === 'graft')" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
				</select>
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Wound Size (cm²) *</label>
				<div class="relative">
					<TagIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
					<input type="number" min="0.1" step="0.1" v-model.number="woundSize" class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder:text-left" placeholder="Enter wound size" />
				</div>
			</div>
		</div>

		<div v-if="suggestions.length > 0">
		<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Suggested Combinations</h3>
			<div class="space-y-3">
				<div v-for="(suggestion, index) in suggestions" :key="index" @click="handleSelectSuggestion(suggestion)" :class="['border rounded-lg p-4 cursor-pointer transition-colors', selectedSuggestion === suggestion ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-500']">
					<div class="flex items-center justify-between mb-2">
						<div class="flex items-center space-x-2">
							<span class="text-sm font-medium text-gray-900 dark:text-white">Option {{ index + 1 }}</span>
							<span v-if="suggestion.excessArea === 0" class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Exact Match</span>
							<span v-if="!suggestion.inStock" class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full flex items-center space-x-1">
								<ExclamationTriangleIcon class="w-3 h-3" />
								<span>Limited Stock</span>
							</span>
						</div>
						<div class="text-sm text-gray-600 dark:text-gray-300">
							Total: {{ suggestion.totalArea }}cm²
							<span v-if="suggestion.excessArea > 0" class="text-orange-600 ml-2">(+{{ suggestion.excessArea }}cm² excess)</span>
						</div>
					</div>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
						<div v-for="(item, itemIndex) in suggestion.combination" :key="itemIndex" class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-900/10 rounded">
							<div>
								<span class="font-medium">{{ getSizeName(selectedBrand, item.sizeId) }}</span>
								<span class="text-gray-600 dark:text-gray-400 ml-2">({{ item.area }})</span>
								<span class="text-gray-600 dark:text-gray-400 ml-2">× {{ item.quantity }}</span>
							</div>
							<div class="flex items-center space-x-2">
								<ArchiveBoxIcon class="w-4 h-4 text-gray-400 dark:text-gray-500" />
								<span class="text-sm text-gray-600 dark:text-gray-400">{{ getInventoryCount(selectedBrand, item.area) }} in stock</span>
							</div>
						</div>
					</div>
					<div class="mt-3 flex items-center justify-between text-sm">
						<span class="text-gray-600 dark:text-gray-300">{{ suggestion.graftCount }} graft{{ suggestion.graftCount !== 1 ? 's' : '' }} total</span>
						<span v-if="suggestion.inStock" class="text-green-600 dark:text-green-400 font-medium flex items-center gap-2">
							All items in stock
							<button type="button" class="ml-2 px-2 py-1 text-xs rounded bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40 transition" @click.stop="openViewModal(suggestion)">View</button>
						</span>
						<span v-else class="text-red-600 dark:text-red-400 font-medium">Check availability</span>
					</div>
				</div>
			</div>
		</div>

		<div v-else-if="selectedBrand && woundSize > 0" class="text-center py-8">
			<ExclamationTriangleIcon class="w-12 h-12 text-yellow-400 dark:text-yellow-300 mx-auto mb-4" />
			<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No suitable combinations found</h3>
			<p class="text-gray-600 dark:text-gray-400">Try adjusting the wound size or check if inventory is available for this brand.</p>
		</div>

		<div v-if="selectedSuggestion" class="mt-6 p-4 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-400 rounded-lg">
			<h4 class="font-medium text-green-800 dark:text-green-400 mb-2">Selection Confirmed</h4>
			<p class="text-green-700 dark:text-green-300 text-sm">You can now proceed to create an order with this graft combination.</p>
		</div>

		<!-- Modal -->
		<div v-if="viewModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
			<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md relative">
				<button class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" @click="closeViewModal">&times;</button>
				<h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Combination Details</h3>
				<div v-if="viewModalSuggestion">
					<ul class="divide-y divide-gray-200 dark:divide-gray-700">
						<li v-for="(item, idx) in viewModalSuggestion.combination" :key="idx" class="py-2 flex justify-between items-center">
							<div>
								<span class="font-medium">{{ getSizeName(selectedBrand, item.sizeId) }}</span>
								<span class="text-gray-500 dark:text-gray-400 ml-2">× {{ item.quantity }}</span>
							</div>
							<div class="flex items-center space-x-2">
								<ArchiveBoxIcon class="w-4 h-4 text-gray-400 dark:text-gray-500" />
								<span class="text-sm text-gray-600 dark:text-gray-400">{{ getInventoryCount(selectedBrand, item.area) }} in stock</span>
							</div>
						</li>
					</ul>
					<div class="mt-4 text-sm text-gray-700 dark:text-gray-300">
						<div>Total area: <span class="font-medium">{{ viewModalSuggestion.totalArea }}cm²</span></div>
						<div>Excess area: <span class="font-medium">{{ viewModalSuggestion.excessArea }}cm²</span></div>
						<div>Total grafts: <span class="font-medium">{{ viewModalSuggestion.graftCount }}</span></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { CalculatorIcon, TagIcon, ArchiveBoxIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

// Props
const props = defineProps<{ brands: any[], onSelectionMade?: (selection: any) => void }>()

const selectedBrand = ref('')
const woundSize = ref<number>(0)
const suggestions = ref<any[]>([])
const selectedSuggestion = ref<any | null>(null)
const viewModalOpen = ref(false)
const viewModalSuggestion = ref<any | null>(null)

// Mock inventory (replace with real data as needed)
const mockInventory: Record<string, Record<string, number>> = {
	'1': { '4': 10, '6': 8, '10': 5, '16': 3 },
	'2': { '4': 15, '6': 12, '10': 7, '16': 2 },
}

function getSizeName(brandId: string, sizeId: string) {
	const brand = props.brands.find(b => b.id === brandId)
	const size = brand?.sizes.find((s: any) => s.id === sizeId)
	return size?.size || 'Unknown'
}
function getInventoryCount(brandId: string, area: number) {
	const inventory = mockInventory[brandId] || {}
	return inventory[area] || 0
}
function handleSelectSuggestion(suggestion: any) {
	selectedSuggestion.value = suggestion
	props.onSelectionMade?.({ brandId: selectedBrand.value, woundSize: woundSize.value, suggestion })
}
function openViewModal(suggestion: any) {
	viewModalSuggestion.value = suggestion
	viewModalOpen.value = true
}
function closeViewModal() {
	viewModalOpen.value = false
	viewModalSuggestion.value = null
}
function generateSuggestions(brandId: string, targetArea: number) {
	const brand = props.brands.find(b => b.id === brandId)
	if (!brand || targetArea <= 0) return []
	const availableSizes = brand.sizes.filter((size: any) => {
		const inventory = mockInventory[brandId] || {}
		return (inventory[size.area] || 0) > 0
	})
	const suggestions: any[] = []
	function generateCombinations(sizes: any[], target: number, current: Array<{ sizeId: string; quantity: number; area: number }> = [], totalArea = 0) {
		if (totalArea >= target) {
			const graftCount = current.reduce((sum, item) => sum + item.quantity, 0)
			const excessArea = totalArea - target
			const inStock = current.every(item => {
				const inventory = mockInventory[brandId] || {}
				return (inventory[item.area] || 0) >= item.quantity
			})
			suggestions.push({ combination: [...current], totalArea, excessArea, inStock, graftCount })
			return
		}
		for (const size of sizes) {
			const inventory = mockInventory[brandId] || {}
			const available = inventory[size.area] || 0
			for (let qty = 1; qty <= Math.min(available, 5); qty++) {
				if (totalArea + (size.area * qty) <= target + 10) {
					generateCombinations(
						sizes,
						target,
						[...current, { sizeId: size.id, quantity: qty, area: size.area }],
						totalArea + (size.area * qty)
					)
				}
			}
		}
	}
	generateCombinations(availableSizes, targetArea)
	return suggestions
		.sort((a, b) => {
			if (a.excessArea === 0 && b.excessArea > 0) return -1
			if (b.excessArea === 0 && a.excessArea > 0) return 1
			if (a.excessArea !== b.excessArea) return a.excessArea - b.excessArea
			return a.graftCount - b.graftCount
		})
	.slice(0, 5)
}
watch([selectedBrand, woundSize], ([brand, size]) => {
	if (brand && size > 0) {
		suggestions.value = generateSuggestions(brand, size)
		selectedSuggestion.value = null
	} else {
		suggestions.value = []
		selectedSuggestion.value = null
	}
})
</script>

<style scoped>
/* Ensure placeholder is left-aligned */
input::placeholder {
	text-align: left;
}
</style> 