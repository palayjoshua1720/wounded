<template>
	<div class="p-6 max-w-8xl mx-auto">
		<div class="flex justify-between items-center mb-4">
			<h1 class="text-2xl font-bold flex items-center gap-2">
				Smart Graft Size Calculator
			</h1>
			<button
				@click="showCreateForm = true"
				class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h5 mr-2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
				</svg>
				Add Graft Size
			</button>
			<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Graft' : 'Edit User'">
				<form action="" class="space-y-4" @submit.prevent>
					<div class="grid grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand Name<span class="text-red-500">*</span></label>
							<input
								type="text"
								required
								v-model="form.brandName"
								class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							/>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Type<span class="text-red-500">*</span></label>
							<select
								required
								v-model="form.productType"
								class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							>
								<option value="graft">Graft</option>
								<option value="another">Another</option>
							</select>
						</div>
					</div>
					<div class="flex justify-end">
						<button
							type="button"
							@click="addOption"
							class="flex items-center px-2 py-1 text-green-500 rounded-lg hover:bg-blue-700"
						>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h5 mr-2">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
							</svg>
							Add Options
						</button>
					</div>
					<!-- loop here -->
					<div
						class="overflow-y-auto"
						:class="{
							'max-h-[50vh]': form.options.length > 3,
							'max-h-fit': form.options.length <= 3
						}"
					>
						<div v-for="(option, idx) in form.options" :key="idx" class="mb-2 pb-2">
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
								Size {{ idx + 1 }}
							</label>
							<label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mt-2">(W x H)<span class="text-red-500">*</span></label>
							<div class="grid grid-cols-12 gap-4 items-center">
								<div class="col-span-5">
									<input
										type="number"
										required
										maxlength="1"
										v-model.number="option.width"
										placeholder="W"
										class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
									/>
								</div>
								<div class="col-span-5">
									<input
										type="number"
										required
										maxlength="1"
										v-model.number="option.height"
										placeholder="H"
										class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
									/>
								</div>
								<div class="col-span-1 flex items-center">
									<span class="text-gray-700 dark:text-gray-300 text-sm">cm²</span>
								</div>
								<div class="col-span-1 flex justify-end" v-if="idx > 0">
									<button type="button" @click="removeOption(idx)" class="text-red-500 text-xs hover:underline">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
											<path stroke-linecap="round" stroke-linejoin="round"
												d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
										</svg>
									</button>
								</div>
							</div>
						</div>
					</div>
					<!-- end loop here -->
					 <div class="flex justify-end space-x-3 pt-4">
						<button
							@click="closeForm"
							type="button"
							class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
						>
							Cancel
						</button>
						<button
							type="submit"
							class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
						>
							{{ showCreateForm ? 'Add New Graft' : 'Update User' }}
						</button>
					</div>
				</form>
			</BaseModal>
		</div>
		<SmartGraftSelector :brands="brands" />
	</div>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import SmartGraftSelector from '@/components/SmartSelector/SmartGraftSelector.vue'
import BaseModal from '@/components/common/BaseModal.vue'

const showCreateForm = ref(false)

// Form state for modal
const form = reactive({
	brandName: '',
	productType: 'graft',
	options: [
		{ width: null, height: null, productType: 'graft' }
	] as Array<{ width: number | null, height: number | null, productType: string }>
})

function addOption() {
	form.options.push({ width: null, height: null, productType: 'graft' })
}
function removeOption(idx: number) {
	if (idx === 0) return;
	form.options.splice(idx, 1)
}

// Mock brands data (should match the expected structure in SmartGraftSelector)
const brands = [
	{
		id: '1',
		name: 'DermaGraft Pro',
		productType: 'graft',
		sizes: [
			{ id: '1', size: '2cm x 2cm', area: 4 },
			{ id: '2', size: '2cm x 3cm', area: 6 },
			{ id: '3', size: '3cm x 3cm', area: 9 },
			{ id: '4', size: '4cm x 4cm', area: 16 },
		],
	},
	{
		id: '2',
		name: 'HealMatrix Advanced',
		productType: 'graft',
		sizes: [
			{ id: '5', size: '2cm x 2cm', area: 4 },
			{ id: '6', size: '2cm x 3cm', area: 6 },
			{ id: '7', size: '3cm x 4cm', area: 12 },
		],
	},
	{
		id: '3',
		name: 'Biolab Skin Graft',
		productType: 'graft',
		sizes: [
			{ id: '9', size: '2cm x 2cm', area: 4 },
			{ id: '11', size: '3cm x 3cm', area: 9 },
			{ id: '12', size: '4cm x 3cm', area: 12 },
		],
	},
]

const showFormModal = computed({
	get: () => showCreateForm.value,
	set: (value: boolean) => {
		if (!value) {
			showCreateForm.value = false
		}
	}
})

function closeForm() {
	showCreateForm.value = false
}
</script>