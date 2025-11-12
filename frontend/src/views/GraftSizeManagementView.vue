<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Graft Sizes Management</h1>
				<p class="text-gray-600 dark:text-gray-400">Manage graft sizes</p>
			</div>

			<button
				@click="
					clearForm()
					selectedGraftRequest = null; 
					showCreateForm = true
				"
				class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
			>
				<ListPlus class="w-5 h-5 mr-2" />
				New Graft Size
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white px-6 py-4 border-b border-gray-200 dark:border-gray-600 mb-2 shadow-sm">
			<div class="flex items-center justify-between">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white">Graft Sizes Management</h2>
				<div class="flex items-center space-x-4">
					<div class="relative">
						<Search class="absolute left-3 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input
							v-model="searchTerm"
							type="text"
							placeholder="Search Graft Size..."
							class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div class="flex items-center space-x-2">
						<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
						<select
							id="per-page"
							v-model="itemsPerPage"
							class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded 
								focus:ring-2 focus:ring-green-500 focus:border-transparent 
								bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- Users Table -->
		<div class="bg-white dark:bg-gray-800 rounded shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
					<thead class="bg-gray-50 dark:bg-gray-700">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Clinic
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Brand
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Size
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Stock
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
						<TableLoader v-if="tableLoader" :colspan="5" />
						<template v-else>
							<tr
							v-for="graft in filteredGraftRequest"
							:key="graft.graft_size_id"
							class="hover:bg-gray-50 dark:hover:bg-gray-700 "
							>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
									{{ graft.clinic?.clinic_name || 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ graft.brand?.brand_name || 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ graft.size || 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ graft.stock || 0 }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm font-medium space-x-2">
									<button
									@click="selectedGraftRequest = graft; showUserDetailsModal = true"
									class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
									title="View Details"
									>
										<Eye class="w-5 h-4" />
									</button>
									<button
									@click="editGraft(graft)"
									class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
									title="Edit Graft"
									>
										<SquarePen class="w-4 h-4" />
									</button>
									<button
									@click="confirmDelete(graft)"
									class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
									title="Delete Graft"
									>
										<Trash2 class="w-4 h-4" />
									</button>
									<button
									@click="confirmArchive(graft)"
									:class="[
										'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300',
									]"
									:title="graft.graft_status === 1 ? 'Unarchive Graft' : 'Archive Graft'"
									>
										<component
											:is="graft.graft_status === 1 ? ArchiveRestore : Archive"
											class="w-4 h-4"
										/>
									</button>
								</td>
							</tr>
							<tr v-if="filteredGraftRequest.length === 0 && !tableLoader">
								<td colspan="5" class="text-center text-gray-400 py-6">
									<div class="flex flex-col items-center justify-center gap-2">
										<PencilRuler class="w-10 h-10 mb-1" />
										<span>No Graft Size found.</span>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination :pagination="pagination" @update:page="getAllGraftRequests" />
		</template>

		<!-- User Details Modal -->
		<BaseModal v-model="showUserDetailsModal" title="Graft Size Details">
			<template v-if="selectedGraftRequest">
				<div class="space-y-4">
					<div class="grid grid-cols-2 gap-4">
						<div class="flex items-center space-x-4">
							<div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
								<ShieldPlus class="w-8 h-8 text-green-500" />
							</div>
							<div>
								<p class="text-xl font-semibold text-gray-900">{{ selectedGraftRequest.graft_number }}</p>
								<span
									v-if="graftStatus[selectedGraftRequest.graft_status]"
									:class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', graftStatus[selectedGraftRequest.graft_status].classes]"
								>
									{{ graftStatus[selectedGraftRequest.graft_status].label }}
								</span>
							</div>
						</div>
					</div>

					<div>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Hospital class="w-5 h-5 text-green-500" />
									<div>
										<p class="text-sm text-gray-700">Clinic</p>
										<p class="text-gray-900">{{ selectedGraftRequest.clinic?.clinic_name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Package class="w-5 h-5 text-purple-500" />
									<div>
										<p class="text-sm text-gray-700">Brand</p>
										<p class="text-gray-900">{{ selectedGraftRequest.brand?.brand_name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<RulerDimensionLine class="w-5 h-5 text-purple-500" />
									<div>
										<p class="text-sm text-gray-700">Size</p>
										<p class="text-gray-900">{{ selectedGraftRequest.size }}</p>
									</div>
								</div>
							</div>
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Diameter class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-gray-700">Area (cm²)</p>
										<p class="text-gray-900">{{ selectedGraftRequest.area || 'N/A' }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<DollarSign class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-gray-700">Price</p>
										<p class="text-gray-900">${{ selectedGraftRequest.price ? selectedGraftRequest.price.toFixed(2) : 'N/A' }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Package class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-gray-700">Stock</p>
										<p class="text-gray-900">{{ selectedGraftRequest.stock }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>
		</BaseModal>

		<!-- Create/Edit User Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'New Graft Size' : 'Edit Graft Size Details'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<!-- Basic Information -->
				<div class="sm:col-span-2">
					<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
						<Package class="w-5 h-5 text-green-600" />
						<span>Brand<span class="text-red-500">*</span></span>
					</label>
					<select
						v-model="formData.brand_id"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					>
						<option disabled value="">Select a Brand</option>
						<option 
							v-for="brand in brandData" 
							:key="brand.brand_id" 
							:value="brand.brand_id"
						>
							{{ brand.brand_name }}
						</option>
					</select>
				</div>
				<!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<Hospital class="w-5 h-5 text-green-600" />
							<span>Clinic<span class="text-red-500">*</span></span>
						</label>
						<select
							v-model="formData.clinic_id"
							required
							class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						>
							<option disabled value="">Select a Clinic</option>
							<option 
								v-for="clinic in clinicData" 
								:key="clinic.clinic_id" 
								:value="clinic.clinic_id"
							>
								{{ clinic.clinic_name }}
							</option>
						</select>
					</div>
					<div>
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<Package class="w-5 h-5 text-green-600" />
							<span>Brand<span class="text-red-500">*</span></span>
						</label>
						<select
							v-model="formData.brand_id"
							required
							class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						>
							<option disabled value="">Select a Brand</option>
							<option 
								v-for="brand in brandData" 
								:key="brand.brand_id" 
								:value="brand.brand_id"
							>
								{{ brand.brand_name }}
							</option>
						</select>
					</div>
				</div> -->

				<!-- Graft Sizes -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<PencilRuler class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Graft Sizes<span class="text-red-500">*</span></h3>
					</div>
					<div class="space-y-3">
						<div
							v-for="(graftSize, index) in formData.graftSizes"
							:key="graftSize.id || index"
							:class="index > 0 ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : ''"
						>
							<div class="flex items-center justify-between mb-2">
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">Size {{ index + 1 }}</span>
								<button
									v-if="formData.graftSizes.length > 1"
									type="button"
									@click="removeGraftSize(index)"
									class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
								>
									<Trash2 class="w-4 h-4" />
								</button>
							</div>
							<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Size (e.g., 2cm x 2cm)</label>
									<div class="relative">
										<RulerDimensionLine class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model="graftSize.size"
										type="text"
										required
										placeholder="2cm x 2cm"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Area (cm²)</label>
									<div class="relative">
										<Diameter class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model.number="graftSize.area"
										type="number"
										required
										min="0"
										step="0.01"
										placeholder="0"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Price</label>
									<div class="relative">
										<DollarSign class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model.number="graftSize.price"
										type="number"
										required
										min="0"
										step="0.01"
										placeholder="0.00"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Stock</label>
									<div class="relative">
										<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model.number="graftSize.stock"
										type="number"
										required
										min="0"
										placeholder="0"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
							</div>
						</div>
						<button
							type="button"
							@click="addGraftSize"
							class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors w-full sm:w-auto"
						>
							<Plus class="w-4 h-4 mr-2" />
							Add Size
						</button>
					</div>
				</div>
			</form>
			<template #actions>
				<!-- Actions -->
				<div class="p-4 flex items-center gap-2">
					<button
						type="button"
						@click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
					>
						Cancel
					</button>
					<button
						type="button"
						@click="handleSubmitForm"
						class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
					>
						{{ showCreateForm ? 'Create Graft Size' : 'Update Graft Size' }}
					</button>
				</div>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import { Search, Eye, SquarePen, Trash2, Package, FilePlus2, Hospital, Archive, ArchiveRestore, PencilRuler, ListPlus, RulerDimensionLine, Diameter, DollarSign, Plus, ShieldPlus } from 'lucide-vue-next'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface GraftSize {
  id?: string
  size: string
  area: number | null
  price: number | null
  stock: number
}

interface GraftRequest {
	graft_size_id: string
	graft_number: string
	clinic_id: string
	brand_id: string
	size: string
	area?: number | null
	price?: number | null
	stock: number
	graft_status: number
	created_at: string

	// Nested relations
	clinic?: {
		clinic_id: string
		clinic_name: string
	}
	brand?: {
		brand_id: string
		brand_name: string
	}
}

interface Brand {
	brand_id: string
	brand_name: string
}

interface Clinic {
	clinic_id: string
	clinic_name: string
}

const graftRequest = ref<GraftRequest[]>([])
const brandData = ref<Brand[]>([])
const clinicData = ref<Clinic[]>([])
const itemsPerPage = ref(10)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})
const tableLoader = ref(false);
const graftStatus: Record<number, { label: string; classes: string }> = {
	0: { label: 'On the List', classes: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
	1: { label: 'Archive', classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' },
}

const searchTerm = ref('')
const selectedGraftRequest = ref<GraftRequest | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showUserDetailsModal = ref(false)

const formData = ref({
	clinic_id: '',
	brand_id: '',
	graftSizes: [{ size: '', area: null, price: null, stock: 0, id: undefined as string | undefined }] as GraftSize[]
})

function addGraftSize() {
  formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, id: undefined })
}

function removeGraftSize(index: number) {
  formData.value.graftSizes.splice(index, 1)
  if (formData.value.graftSizes.length === 0) {
    formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, id: undefined })
  }
}

async function editGraft(graft: GraftRequest) {
	selectedGraftRequest.value = graft
	showCreateForm.value = false
	showUserDetailsModal.value = false
	showEditForm.value = true
	await nextTick();
	formData.value = {
		clinic_id: graft.clinic_id,
		brand_id: graft.brand_id,
		graftSizes: [{
			id: graft.graft_size_id,
			size: graft.size,
			area: graft.area ?? null,
			price: graft.price ?? null,
			stock: graft.stock
		}]
	}
}

async function confirmDelete(graft: GraftRequest) {	
	try {
		const result = await Swal.fire({
			title: "Deleting " + graft.graft_number,
			text: "You won't be able to revert this!",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: "Yes, delete it!"
		});

		if (result.isConfirmed) {
			await api.put(`/management/delete/${graft.graft_size_id}/deletegraftsize`);
			
			await Swal.fire({
				title: "Deleted!",
				text: "Graft Size has been deleted.",
				icon: "success"
			});

			toast.success('Graft deleted successfully!');
			await getAllGraftRequests();
		}
	} catch (error) {
		toast.error('Failed to delete Graft.');
	}
}

async function confirmArchive(graft: GraftRequest) {
	try {
		const isArchived = graft.graft_status === 1;

		const result = await Swal.fire({
			title: `${isArchived ? 'Unarchiving' : 'Archiving'} ${graft.graft_number}`,
			text: isArchived
				? 'This graft size will be restored to the list.'
				: 'This graft size will be archived',
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: isArchived ? 'Yes, unarchive it!' : 'Yes, archive it!'
		});

		if (result.isConfirmed) {
			const endpoint = isArchived
				? `/management/archive/${graft.graft_size_id}/unarchivegraftsize`
				: `/management/archive/${graft.graft_size_id}/archivegraftsize`;

			await api.put(endpoint);
			
			await Swal.fire({
				title: isArchived ? 'Unarchived!' : 'Archived!',
				text: `Graft Size has been ${isArchived ? 'unarchived' : 'archived'}.`,
				icon: 'success'
			});

			toast.success(`Graft ${isArchived ? 'unarchived' : 'archived'} successfully!`);
			await getAllGraftRequests();
		}
	} catch (error) {
		toast.error(`Failed to ${graft.graft_status === 1 ? 'unarchive' : 'archive'} Graft.`);
	}
}

async function handleSubmitForm() {
	try {
		if (showCreateForm.value) {
			const validSizes = formData.value.graftSizes.filter(gs => gs.size.trim() !== '')
			if (validSizes.length === 0) {
				toast.error('At least one valid size is required.')
				return
			}
			const payload = {
				clinic_id: formData.value.clinic_id,
				brand_id: formData.value.brand_id,
				graftSizes: validSizes.map(gs => ({
					size: gs.size,
					area: gs.area,
					price: gs.price,
					stock: gs.stock
				}))
			}

			const { data } = await api.post(
				'/management/add/newgraftsize',
				payload
			)

			toast.success(data.message || 'Graft Size added successfully!')
			await getAllGraftRequests()
		} else if (showEditForm.value) {
			const graftSize = formData.value.graftSizes[0]
			if (!graftSize.size.trim()) {
				toast.error('Size is required.')
				return
			}
			const payload = {
				size: graftSize.size,
				area: graftSize.area,
				price: graftSize.price,
				stock: graftSize.stock,
			}

			const { data } = await api.put(
                `/management/update/${selectedGraftRequest.value?.graft_size_id}/updategraftsize`,
                payload
         	)

			toast.success(data.message || 'Graft Size Updated Successfully!')
			await getAllGraftRequests()
		}
		closeForm()
	} catch (err: unknown) {
		if (axios.isAxiosError(err)) {
			const status = err.response?.status
			const data = err.response?.data

			if (status === 422 && data?.errors) {
				const messages = Object.values(data.errors).flat()
				toast.error("Error: " + messages.join("\n"))
			} else {
				toast.error(data?.message || `Request failed with status code ${status}`)
			}
		} else if (err instanceof Error) {
			toast.error("Error: " + err.message)
		} else {
			toast.error("Something went wrong")
		}
	}
}

function closeForm() {
	showCreateForm.value = false
	showEditForm.value = false
	showUserDetailsModal.value = false
	selectedGraftRequest.value = null
	clearForm()
}

function clearForm(){
	formData.value = {
		clinic_id: '',
		brand_id: '',
		graftSizes: [{ size: '', area: null, price: null, stock: 0, id: undefined }]
	}
}

const filteredGraftRequest = computed(() => {
    return graftRequest.value.filter(graft => {
        const clinicName = graft.clinic?.clinic_name || '';
        const brandName = graft.brand?.brand_name || '';
        const matchesSearch = clinicName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              brandName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              graft.size.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              (graft.area?.toString().toLowerCase().includes(searchTerm.value.toLowerCase()) ?? false) ||
                              (graft.price?.toString().toLowerCase().includes(searchTerm.value.toLowerCase()) ?? false) ||
                              graft.stock.toString().toLowerCase().includes(searchTerm.value.toLowerCase());

		return matchesSearch;
    });
});

const showFormModal = computed({
	get: () => showCreateForm.value || showEditForm.value,
	set: (value: boolean) => {
		if (!value) {
			showCreateForm.value = false
			showEditForm.value = false
			selectedGraftRequest.value = null
		}
	}
})

// get all Brands
async function getAllBrands(){
	tableLoader.value = true
    try {
        const { data } = await api.get(`/management/ivr/getbrands`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        brandData.value = data.brand_data		
    } catch (error) {
        console.error('Error fetching brands:', error)
    } finally {
        tableLoader.value = false
    }
}

// get all Clinics
// async function getAllClinics(){
// 	tableLoader.value = true
//     try {
//         const { data } = await api.get(`/management/ivr/getclinics`, {
//             headers: {
//                 Authorization: `Bearer ${localStorage.getItem('auth_token')}`
//             }
//         })

//         clinicData.value = data.clinics || [] 
//     } catch (error) {
//         console.error('Error fetching clinics:', error)
//     } finally {
//         tableLoader.value = false
//     }
// }

// get all Graft Requests
async function getAllGraftRequests(page = 1) {
    tableLoader.value = true
    try {
        const { data } = await api.get(`/management/graft-sizes?page=${page}&per_page=${itemsPerPage.value}`, {  // Changed: Added "-s" and hyphen
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
        graftRequest.value = data.graftData
        pagination.value = {
            current_page: data.meta.current_page,
            last_page: data.meta.last_page,
            per_page: data.meta.per_page,
            total: data.meta.total,
        }
    } catch (error) {
        console.error('Error fetching graft requests:', error)
        // Optional: Add toast.error here for better UX, e.g., toast.error('Failed to load graft sizes.')
    } finally {
        tableLoader.value = false
    }
}

onMounted(async () => {
    getAllBrands()
    // getAllClinics()
    getAllGraftRequests(1)
})

watch(itemsPerPage, () => {
	getAllGraftRequests(1)
})

// watch(showFormModal, async (val) => {
//   if (val && clinicData.value.length === 0) await getAllClinics();
// });
</script>

<style scoped>
@keyframes ping-slow {
    0% { transform: scale(1); opacity: 0.3; }
    70% { transform: scale(1.3); opacity: 0; }
    100% { transform: scale(1.3); opacity: 0; }
}
.animate-ping-slow {
    animation: ping-slow 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>