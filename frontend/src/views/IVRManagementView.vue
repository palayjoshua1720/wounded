<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">IVR Management</h1>
				<p class="text-gray-600 dark:text-gray-400">Manage insurance verification requests</p>
			</div>

			<button
				@click="
					clearForm()
					selectedIvrRequest = null; 
					showCreateForm = true
				"
				class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
			>
				<FilePlus2 class="w-5 h-5 mr-2" />
				New IVR Request
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white px-6 py-4 border-b border-gray-200 dark:border-gray-600 mb-2 shadow-sm">
			<div class="flex items-center justify-between">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white">IVR Requests</h2>
				<div class="flex items-center space-x-4">
					<div class="relative">
						<Search class="absolute left-3 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input
							v-model="searchTerm"
							type="text"
							placeholder="Search IVR Requests..."
							class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div class="flex items-center space-x-2">
						<Funnel class="w-4 h-4 text-gray-500 dark:text-gray-400" />
						<select
						v-model="statusFilter"
						class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="all">All Status</option>
							<option value="pending">Pending</option>
							<option value="eligible">Eligible</option>
							<option value="not_eligible">Not Eligible</option>
						</select>
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
								Patient
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Clinic
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Brand
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Submitted
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Status
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Expiry
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
						<TableLoader v-if="tableLoader" :colspan="7" />
						<template v-else>
							<tr
							v-for="ivr in filteredIVRRequest"
							:key="ivr.ivr_id"
							class="hover:bg-gray-50 dark:hover:bg-gray-700 "
							>
								<td class="px-6 py-3 whitespace-nowrap">
									<div class="flex items-center">
										<div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
											<userProfile class="w-5 h-5 text-green-600" />
										</div>
										<div class="ml-4">
											<div class="text-sm text-gray-900 dark:text-white">{{ ivr.patient?.patient_name }}</div>
											<div class="text-sm text-gray-500 dark:text-gray-400">{{ ivr.patient?.email }}</div>
										</div>
									</div>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									<div class="flex items-center gap-2">
										<span>{{ ivr.clinic?.clinic_name ? ivr.clinic?.clinic_name : 'N/A' }}</span>
									</div>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ ivr.brand?.manufacturer?.manufacturer_name || 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ ivr.submitted_at ? formatDate(ivr.submitted_at) : 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap">
									<button
										@click="handleToggleStatus(ivr.ivr_id)"
										:class="[
										'flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors',
										ivr.ivr_status === 1
											? ivrStatus[1].classes
											: ivrEligibilityStatus[ivr.ivr_status]?.classes
										]"
										:title="ivr.ivr_status === 1 ? 'Archived' : 'Toggle Status'"
									>
										<span>
										{{ ivr.ivr_status === 1
											? ivrStatus[1].label
											: ivrEligibilityStatus[ivr.ivr_status]?.label }}
										</span>
									</button>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ ivr.submitted_at ? formatDate(ivr.submitted_at) : 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm font-medium space-x-2">
									<button
									@click="selectedIvrRequest = ivr; showUserDetailsModal = true"
									class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
									title="View Details"
									>
										<Eye class="w-5 h-4" />
									</button>
									<button
									@click="editIVR(ivr)"
									class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
									title="Edit User"
									>
										<SquarePen class="w-4 h-4" />
									</button>
									<button
									@click="confirmDelete(ivr)"
									class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
									title="Delete User"
									>
										<Trash2 class="w-4 h-4" />
									</button>
									<button
									@click="confirmArchive(ivr)"
									:class="[
										'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300',
									]"
									:title="ivr.ivr_status === 1 ? 'Unarchive User' : 'Archive User'"
									>
										<component
											:is="ivr.ivr_status === 1 ? ArchiveRestore : Archive"
											class="w-4 h-4"
										/>
									</button>
								</td>
							</tr>
							<tr v-if="filteredIVRRequest.length === 0 && !tableLoader">
								<td colspan="7" class="text-center text-gray-400 py-6">
									<div class="flex flex-col items-center justify-center gap-2">
										<Users class="w-10 h-10 mb-1" />
										<span>No IVR Request found.</span>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination :pagination="pagination" @update:page="getAllIVRRequests" />
		</template>

		<!-- User Details Modal -->
		<BaseModal v-model="showUserDetailsModal" title="IVR Request Details">
			<template v-if="selectedIvrRequest">
				<div class="space-y-4">
					<div class="grid grid-cols-2 gap-4">
						<div class="flex items-center space-x-4">
							<div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
								<ShieldPlus class="w-8 h-8 text-green-500" />
							</div>
							<div>
								<p class="text-xl font-semibold text-gray-900">{{ selectedIvrRequest.ivr_number }}</p>
								<span
									v-if="ivrStatus[selectedIvrRequest.ivr_status]"
									:class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', ivrStatus[selectedIvrRequest.ivr_status].classes]"
								>
									{{ ivrStatus[selectedIvrRequest.ivr_status].label }}
								</span>
							</div>
						</div>
					</div>

					<div>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<ShieldUser class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-gray-700">Patient</p>
										<p class="text-gray-900">{{ selectedIvrRequest.patient?.patient_name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Hospital class="w-5 h-5 text-green-500" />
									<div>
										<p class="text-sm text-gray-700">Clinic</p>
										<p class="text-gray-900">{{ selectedIvrRequest.patient?.patient_name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Package class="w-5 h-5 text-purple-500" />
									<div>
										<p class="text-sm text-gray-700">Brand</p>
										<p class="text-gray-900">{{ selectedIvrRequest.brand?.brand_name }}</p>
									</div>
								</div>
							</div>
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Calendar class="w-5 h-5 text-orange-500" />
									<div>
										<p class="text-sm text-gray-700">Date Submitted</p>
										<p class="text-gray-900">{{ formatDate(selectedIvrRequest.submitted_at) }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<CircleCheckBig class="w-5 h-5 text-green-500" />
									<div>
										<p class="text-sm text-gray-700">Eligibiltiy Status</p>
										<p
											v-if="ivrEligibilityStatus[Number(selectedIvrRequest.eligibility_status)]"
											:class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', ivrEligibilityStatus[Number(selectedIvrRequest.eligibility_status)].classes]"
										>
											{{ ivrEligibilityStatus[Number(selectedIvrRequest.eligibility_status)].label }}
										</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Calendar class="w-5 h-5 text-gray-500" />
									<div>
										<p class="text-sm text-gray-700">Date Verified</p>
										<p class="text-gray-900">{{ formatDate(selectedIvrRequest.verified_at) ? formatDate(selectedIvrRequest.verified_at) : 'N/A' }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Calendar class="w-5 h-5 text-red-500" />
									<div>
										<p class="text-sm text-gray-700">Expiry Date</p>
										<p class="text-gray-900">{{ selectedIvrRequest.patient?.patient_name ?? "N/A" }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
						<div class="flex items-start space-x-3">
							<NotebookPen class="w-5 h-5 text-pink-500 mt-0.5" />
							<div>
							<p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes</p>
							<p class="text-gray-900 dark:text-gray-100 whitespace-pre-line">
								{{ selectedIvrRequest.description || 'No notes provided.' }}
							</p>
							</div>
						</div>
					</div>

					<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
						<h4 class="font-medium text-blue-900 mb-3">Manufacturer Information</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Building class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-blue-700">
											<strong>Manufacturer: </strong>
										</p>
										<p class="text-sm text-blue-700">{{ selectedIvrRequest.patient?.patient_name || '--' }}</p>
									</div>
								</div>
							</div>
							<div class="space-y-4">
								<div class="space-y-1">
									<p class="text-sm text-blue-700">
										<strong>IVR Form:</strong>
									</p>
									
									<div class="flex items-center space-x-3">
										<Download class="w-5 h-5 text-green-600" />
										<a 
										v-if="selectedIvrRequest.manufacturer?.filepath" 
										:href="selectedIvrRequest.manufacturer?.filepath" 
										target="_blank"
										class="text-sm text-blue-700"
										>
										Download Form
										</a>
										<span v-else class="text-sm text-gray-500">No file available</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>
		</BaseModal>

		<!-- Create/Edit User Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Submit New IVR Request' : 'Edit IVR Request Details'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<input type="hidden" name="clinic_id" v-model="formData.clinic_id" value="">
				<input type="hidden" name="manufacturer_id" v-model="formData.manufacturer_id" value="">
				<input type="hidden" name="eligibility_status" v-model="formData.eligibility_status" value="">
				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<userProfile class="w-5 h-5 text-green-600" />
							<span>Patient<span class="text-red-500">*</span></span>
						</label>
						<select
							v-model="formData.patient_id"
							required
							class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						>
							<option disabled value="">-- Select Patient --</option>
							<option 
								v-for="patient in patientData" 
								:key="patient.patient_id" 
								:value="patient.patient_id"
							>
								{{ patient.patient_name }}
							</option>
						</select>
					</div>
					<div>
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<Package class="w-5 h-5 text-green-600" />
							<span>Brand</span>
						</label>
						<select
							v-model="formData.brand_id"
							class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						>
							<option disabled value="">-- Select Brand --</option>
							<option 
								v-for="brand in brandData" 
								:key="brand.brand_id" 
								:value="brand.brand_id"
							>
								{{ brand.brand_name }} - {{ brand.manufacturer?.manufacturer_name }}
							</option>
						</select>
					</div>
				</div>
				<div v-if="selectedBrand" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
					<div>
						<h4 class="font-medium text-blue-900 mb-2">IVR Form Information</h4>
						<div class="space-y-2">
							<p class="text-sm text-blue-700">
								<strong>Manufacturer: </strong>
								<span>{{ selectedBrand.manufacturer?.manufacturer_name || '--' }}</span>
							</p>
							<p class="text-sm text-blue-700">
								<strong>Form Type: </strong>
								<span>File</span>
							</p>
						</div>
						<div class="flex items-center gap-2 mt-2">
							<Download class="w-5 h-5 text-green-600" />
							<a 
								v-if="selectedBrand.manufacturer?.filepath" 
								:href="selectedBrand.manufacturer.filepath" 
								target="_blank"
								class="text-blue-700 underline"
							>
								Download Form
							</a>
							<span v-else class="text-gray-500">No file available</span>
						</div>
					</div>
				</div>
				<div>
					<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
						<NotebookPen class="w-5 h-5 text-green-600" />
						Notes
					</label>
					<textarea
					v-model="formData.description"
					rows="4"
					class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
							bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
							focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
					placeholder="Additional notes for this IVR request..."
					></textarea>
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
						{{ showCreateForm ? 'Submit Request' : 'Update IVR Details' }}
					</button>
				</div>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import BaseModal from '../components/common/BaseModal.vue'
import Pagination from '../components/ui/Pagination.vue'
import TableLoader from '../components/ui/TableLoader.vue'
import ContentLoader from '../components/ui/ContentLoader.vue'
import {
	Funnel,
    Search,
    Eye,
    SquarePen,
    Users,
    Trash2,
	User as userProfile,
	Download,
	ShieldUser,
	ShieldPlus,
	Package,
	NotebookPen,
	FilePlus2,
	Hospital,
	Calendar,
	CircleCheckBig,
	Archive,
	ArchiveRestore
} from 'lucide-vue-next'
import api from '../services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface IVRRequest {
	ivr_id: string
	ivr_number: string
	patient_id: string
	clinic_id: string
	brand_id: string
	manufacturer_id: string
	eligibility_status: string
	ivr_status: number
	submitted_at: string
	created_at: string
	expiryDate?: string
	notes: string
	description: string
	verified_at: string

	// Nested relations
	patient?: {
		patient_id: string
		patient_name: string
		email?: string
	}
	clinic?: {
		clinic_id: string
		clinic_name: string
	}
	brand?: {
		brand_id: string
		description: string
		brand_name: string
		manufacturer?: {
			manufacturer_id: string
			manufacturer_name: string
		}
	}
	manufacturer?: {
		manufacturer_id: string
		manufacturer_name: string
		filepath: string
	}
}


interface Patient {
	patient_id: string
	patient_name: string
	clinics?: Clinic[]
	ivrs?: IVRRequest[]
}

interface Brand {
	brand_id: string
	brand_name: string
	description: string

	manufacturer?: {
		manufacturer_id: string
		manufacturer_name: string
		filepath: string
	}
}

interface Clinic {
	clinic_id: string
	clinic_name: string
}

const ivrRequest = ref<IVRRequest[]>([])
const brandData = ref<Brand[]>([])
const patientData = ref<Patient[]>([])
const itemsPerPage = ref(10)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})
const tableLoader = ref(false);
const ivrStatus: Record<number, { label: string; classes: string }> = {
	0: { label: 'On the List', classes: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
	1: { label: 'Archive', classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' },
}

const ivrEligibilityStatus: Record<number, { label: string; classes: string }> = {
	0: { 
		label: 'Pending', 
		classes: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' 
	},
	1: { 
		label: 'Eligible', 
		classes: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
	},
	2: { 
		label: 'Not Eligible', 
		classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' 
	},
}


const searchTerm = ref('')
const statusFilter = ref('all')
const selectedIvrRequest = ref<IVRRequest | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showUserDetailsModal = ref(false)

const formData = ref({
	clinic_id: '',
	brand_id: '',
	manufacturer_id: '',
	patient_id: '',
	description: '',
	status: '',
	eligibility_status: '',
})

function handleToggleStatus(id: string) {
	const userIndex = ivrRequest.value.findIndex(user => user.ivr_id === id)
	if (userIndex !== -1) {
		ivrRequest.value[userIndex].ivr_status = ivrRequest.value[userIndex].ivr_status === 1 ? 0 : 1
	}
}

async function editIVR(ivr: IVRRequest) {
	selectedIvrRequest.value = ivr
	showCreateForm.value = false
	showUserDetailsModal.value = false
	showEditForm.value = true
	await nextTick();
	formData.value = {
		patient_id: ivr.patient_id,
		clinic_id: ivr.clinic_id,
		brand_id: ivr.brand_id,
		description: ivr.description,
		status: ivr.eligibility_status,
		manufacturer_id: ivr.manufacturer_id || '',
		eligibility_status: ivr.eligibility_status,
	}
}

async function confirmDelete(ivr: IVRRequest) {	
	try {
		const result = await Swal.fire({
			title: "Deleting " + ivr.ivr_number,
			text: "You won't be able to revert this!",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: "Yes, delete it!"
		});

		if (result.isConfirmed) {
			await api.put(`/management/delete/${ivr.ivr_id}/deleteivrrequest`);
			
			await Swal.fire({
				title: "Deleted!",
				text: "IVR Request has been deleted.",
				icon: "success"
			});

			toast.success('IVR deleted successfully!');
			await getAllIVRRequests();
		}
	} catch (error) {
		toast.error('Failed to delete IVR.');
	}
}

async function confirmArchive(ivr: IVRRequest) {
	try {
		const isArchived = ivr.ivr_status === 1;

		const result = await Swal.fire({
			title: `${isArchived ? 'Unarchiving' : 'Archiving'} ${ivr.ivr_number}`,
			text: isArchived
				? 'This IVR request will be restored to the list.'
				: 'This IVR request will be archived',
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: isArchived ? 'Yes, unarchive it!' : 'Yes, archive it!'
		});

		if (result.isConfirmed) {
			const endpoint = isArchived
				? `/management/archive/${ivr.ivr_id}/unarchiveivrrequest`
				: `/management/archive/${ivr.ivr_id}/archiveivrrequest`;

			await api.put(endpoint);
			
			await Swal.fire({
				title: isArchived ? 'Unarchived!' : 'Archived!',
				text: `IVR Request has been ${isArchived ? 'unarchived' : 'archived'}.`,
				icon: 'success'
			});

			toast.success(`IVR ${isArchived ? 'unarchived' : 'archived'} successfully!`);
			await getAllIVRRequests();
		}
	} catch (error) {
		toast.error(`Failed to ${ivr.ivr_status === 1 ? 'unarchive' : 'archive'} IVR.`);
	}
}

async function handleSubmitForm() {
	try {
		const payload = {
			patient_id: formData.value.patient_id,
			brand_id: formData.value.brand_id,
			notes: formData.value.description,
			clinic_id: formData.value.clinic_id,
			manufacturer_id: formData.value.manufacturer_id,
			eligibility_status: formData.value.eligibility_status,
		}

		if (showCreateForm.value) {
			const { data } = await api.post(
				'/management/add/newivrrequest',
				payload,
				{
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
					}
				}
			)

			toast.success(data.message || 'IVR Request added successfully!')
			await getAllIVRRequests()
		} else if (showEditForm.value) {
			const { data } = await api.put(
                `/management/update/${selectedIvrRequest.value?.ivr_id}/updateivrrequest`,
                payload,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                }
         	)

			toast.success(data.message || 'IVR Request Updated Successfully!')
			await getAllIVRRequests()
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
	selectedIvrRequest.value = null
	clearForm()
}

function clearForm(){
	formData.value = {
		clinic_id: '',
		brand_id: '',
		manufacturer_id: '',
		patient_id: '',
		description: '',
		status: '',
		eligibility_status: '',
	}
}

const filteredIVRRequest = computed(() => {
	const statusMap: Record<'pending' | 'eligible' | 'not_eligible', number> = {
		pending: 0,
		eligible: 1,
		not_eligible: 2,
	};

    return ivrRequest.value.filter(ivr => {
        const patientName = ivr.patient?.patient_name || '';
        const email = ivr.patient?.email || '';
        const clinicName = ivr.clinic?.clinic_name || '';
        const brandDesc = ivr.brand?.description || '';
        const matchesSearch = patientName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              email.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              clinicName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              brandDesc.toLowerCase().includes(searchTerm.value.toLowerCase());

		const key = statusFilter.value as keyof typeof statusMap;

		const matchesEligibilityStatus =
		statusFilter.value === 'all' ||
		Number(ivr.eligibility_status) === statusMap[key];

		return matchesSearch && matchesEligibilityStatus;
    });
});

const showFormModal = computed({
	get: () => showCreateForm.value || showEditForm.value,
	set: (value: boolean) => {
		if (!value) {
			showCreateForm.value = false
			showEditForm.value = false
			selectedIvrRequest.value = null
		}
	}
})

const formatDate = (dateStr: string) => {
	if (!dateStr) return null;
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	})
}

const selectedBrand = computed(() => {
	return brandData.value.find(b => b.brand_id === formData.value.brand_id)
})

const selectedPatient = computed(() => {
	return patientData.value.find(p => p.patient_id === formData.value.patient_id)
})

// get all patient
async function getAllPatients() {
    tableLoader.value = true
    try {
        const { data } = await api.get(`/management/patients/patientinfo`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        patientData.value = data.patient_data
    } catch (error) {
        console.error('Error fetching clinic users:', error)
    } finally {
        tableLoader.value = false
    }
}

// get all IVR Request
async function getAllIVRRequests(page= 1){
	tableLoader.value = true
    try {
        const { data } = await api.get(`/management/ivr/ivrrequests?page=${page}&per_page=${itemsPerPage.value}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        ivrRequest.value = data.data
		pagination.value = {
            current_page: data.meta.current_page,
            last_page: data.meta.last_page,
            per_page: data.meta.per_page,
            total: data.meta.total,
        }
    } catch (error) {
        console.error('Error fetching clinic users:', error)
    } finally {
        tableLoader.value = false
    }
}

watch(selectedBrand, (brand) => {
	if (brand?.manufacturer) {
		formData.value.manufacturer_id = brand.manufacturer.manufacturer_id
	} else {
		formData.value.manufacturer_id = ''
	}
})

watch(selectedPatient, (patient) => {
	const clinic = patient?.clinics?.[0];
	const ivr = patient?.ivrs?.[0];

	if (clinic) {
		formData.value.clinic_id = clinic.clinic_id;
	} else {
		formData.value.clinic_id = '';
	}

	if (ivr && typeof ivr.eligibility_status === 'number') {
		formData.value.eligibility_status = ivr.eligibility_status;
	} else {
		formData.value.eligibility_status = '';
	}
});

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
async function getAllClinics(page= 1){
	tableLoader.value = true
    try {
        const { data } = await api.get(`/management/ivr/getclinics?page=${page}&per_page=${itemsPerPage.value}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        ivrRequest.value = data.user_data
		pagination.value = {
            current_page: data.meta.current_page,
            last_page: data.meta.last_page,
            per_page: data.meta.per_page,
            total: data.meta.total,
        }
    } catch (error) {
        console.error('Error fetching clinic users:', error)
    } finally {
        tableLoader.value = false
    }
}


onMounted(async () => {
    getAllPatients()
    getAllIVRRequests(1)
    getAllBrands()
    getAllClinics(1)
})

watch(itemsPerPage, () => {
    getAllPatients()
	getAllIVRRequests(1)
    getAllBrands()
    getAllClinics(1)
})
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