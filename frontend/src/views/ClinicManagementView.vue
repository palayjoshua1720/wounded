<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Clinic Management</h1>
			</div>

			<button @click="
				closeForm();
			showCreateForm = true
				" class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
				<HousePlus class="w-4 h-4 mr-2" />
				Add Clinic
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col lg:flex-row gap-6">
				<div class="flex-1">
					<div class="relative">
						<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search Clinics..."
							class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
					</div>
				</div>
				<div class="flex flex-col sm:flex-row gap-4">
					<div class="relative">
						<Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
						<select v-model="statusFilter"
							class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
							<option value="all">All Status</option>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
						</select>
						<ChevronDown
							class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
				</div>
				<div class="relative">
					<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
					<select id="per-page" v-model="itemsPerPage"
						class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
						<option value="9">9</option>
						<option value="25">25</option>
						<option value="50">50</option>
					</select>
					<ChevronDown
						class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
				</div>
			</div>
		</div>

		<!-- Card View -->
		<ClinicCards :users="filteredUsers" :user-status="userStatus" :table-loader="tableLoader"
			:pagination="pagination" @view-clinic="selectedUser = $event" @edit-clinic="editUser"
			@toggle-status="handleToggleStatus" @delete-clinic="confirmDelete" @archive-clinic="confirmArchive"
			@unarchive-clinic="confirmUnarchive" @update:page="getAllClinics" />

		<!-- Clinic Details Modal -->
		<BaseModal v-model="showUserDetailsModal" title="Clinic Details">
			<template v-if="selectedUser">
				<div class="space-y-4">
					<div class="grid grid-cols-2 gap-4">
						<div class="flex items-center space-x-4">
							<div v-if="selectedUser.logo" class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
								<img :src="selectedUser.logo" :alt="`${selectedUser.name} logo`"
									class="w-full h-full object-cover border" />
							</div>
							<div v-else class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
								<Hospital class="w-8 h-8 text-green-500 dark:text-green-400" />
							</div>
							<div>
								<p class="text-xl font-semibold text-gray-900 dark:text-white">{{ selectedUser.name }}</p>
								<span v-if="userStatus[selectedUser.isActive]"
									:class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit transition-all duration-200', getStatusClasses(selectedUser.isActive)]">
									<component :is="getStatusIcon(selectedUser.isActive)" class="w-2.5 h-2.5 mr-1" />
									{{ userStatus[selectedUser.isActive].label }}
								</span>
							</div>
						</div>
					</div>

					<div>
						<h4 class="text-lg text-gray-900 dark:text-white mb-4">Clinic Contact Information</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<MapPin class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-gray-700 dark:text-gray-400">Address</p>
										<p class="text-gray-900 dark:text-gray-200">{{ selectedUser.address }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Phone class="w-5 h-5 text-green-500" />
									<div>
										<p class="text-sm text-gray-700 dark:text-gray-400">Contact Number</p>
										<p class="text-gray-900 dark:text-gray-200">{{ selectedUser.phone }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Mail class="w-5 h-5 text-purple-500" />
									<div>
										<p class="text-sm text-gray-700 dark:text-gray-400">Email</p>
										<p class="text-gray-900 dark:text-gray-200">{{ selectedUser.email }}</p>
									</div>
								</div>
							</div>
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Contact class="w-5 h-5 text-orange-500" />
									<div>
										<p class="text-sm text-gray-700 dark:text-gray-400">Contact Person</p>
										<p class="text-gray-900 dark:text-gray-200">{{ selectedUser.contactPerson }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<FileText class="w-5 h-5 text-indigo-500" />
									<div>
										<p class="text-sm text-gray-700 dark:text-gray-400">Clinic ID</p>
										<p class="text-gray-900 dark:text-gray-200">{{ selectedUser.clinicId ?? "N/A" }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<FileText class="w-5 h-5 text-red-500" />
									<div>
										<p class="text-sm text-gray-700 dark:text-gray-400">Clinic Public ID</p>
										<p class="text-gray-900 dark:text-gray-200">{{ selectedUser.clinicPubId ?? "N/A" }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- <div class="border rounded-lg">
						<button
							@click="showUsers = !showUsers"
							class="w-full flex justify-between items-center px-4 py-3 text-left bg-gray-100 rounded-lg hover:bg-gray-200"
						>
							<h4 class="text-lg font-medium text-gray-900">
							Associated Users ({{ selectedUser.assigned_clinician_ids?.length || 0 }})
							</h4>
							<ChevronUp 
							:class="{ 'rotate-180': showUsers }"
							class="w-5 h-5 text-gray-600 transform transition-transform duration-200"
							/>
						</button>

						<transition
							enter-active-class="transition ease-out duration-200"
							enter-from-class="opacity-0 max-h-0"
							enter-to-class="opacity-100 max-h-screen"
							leave-active-class="transition ease-in duration-150"
							leave-from-class="opacity-100 max-h-screen"
							leave-to-class="opacity-0 max-h-0"
						>
							<div v-show="showUsers" class="p-4 bg-gray-50">
								<div v-if="selectedUser.assigned_clinician_ids?.length" class="overflow-x-auto">
									<table class="min-w-full bg-white border border-gray-200 rounded-lg">
									<thead class="bg-gray-50">
										<tr>
										<th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
										<th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Email</th>
										<th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Phone</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="clinician in selectedUser.assigned_clinician_ids" :key="clinician.id" class="border-t text-start">
										<td class="px-4 py-2 text-sm text-gray-800">{{ clinician.name }}</td>
										<td class="px-4 py-2 text-sm text-gray-800">{{ clinician.email }}</td>
										<td class="px-4 py-2 text-sm text-gray-800">{{ clinician.phone ?? 'N/A' }}</td>
										</tr>
									</tbody>
									</table>
								</div>

								<p v-else class="text-center py-8 bg-gray-50 rounded-lg text-gray-600">
									<Users class="w-12 h-12 text-gray-400 mx-auto mb-2" />
									No users associated with this clinic
								</p>
							</div>
						</transition>
					</div> -->

					<!-- <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
						<h4 class="font-medium text-blue-900 mb-3">Clinic Statistics</h4>
						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 place-items-center text-center">
							<div class="flex flex-col items-center justify-center h-24 bg-white rounded-lg shadow-sm w-full max-w-[14rem]">
								<p class="text-2xl font-bold text-blue-900">{{ totalUsers }}</p>
								<p class="text-sm text-blue-700">Total Clinicians</p>
							</div>
							<div class="flex flex-col items-center justify-center h-24 bg-white rounded-lg shadow-sm w-full max-w-[14rem]">
								<p class="text-2xl font-bold text-blue-900">{{ activeCliniciansCount }}</p>
								<p class="text-sm text-blue-700">Active Clinicians</p>
							</div>
							<div class="flex flex-col items-center justify-center h-24 bg-white rounded-lg shadow-sm w-full max-w-[14rem]">
								<p class="text-2xl font-bold text-blue-900">{{ userStatus[selectedUser.isActive].label }}</p>
								<p class="text-sm text-blue-700">Status</p>
							</div>
						</div>
					</div> -->
				</div>
			</template>
			<template #actions>
				<!-- Actions -->
				<div class="p-4">
					<button type="button" @click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
						Close
					</button>
				</div>
			</template>
		</BaseModal>

		<!-- Create/Edit Clinic Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Clinic' : 'Edit Clinic'"
			:class="{ 'opacity-0 pointer-events-none': isCropperOpen }"
			@close-form="closeForm">
			<form @submit.prevent="handleSubmitForm" class="space-y-6">

				<!-- Clinic Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<HousePlus class="w-5 h-5 text-green-500 dark:text-green-400" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Clinic Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<Building class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
								<input v-model="formData.name" type="text" required placeholder="Clinic Name"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Email<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
								<input v-model="formData.email" type="email" required placeholder="example@clinic.com"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Clinic Public
								Code</label>
							<div class="relative">
								<FileKey class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
								<input v-model="formData.publicId" type="text" placeholder="Optional code"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
								<select v-model="formData.isActive" required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option :value="false">Active</option>
									<option :value="true">Inactive</option>
								</select>
							</div>
						</div>
						<div v-if="!showCreateForm">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Clinic
								Code</label>
							<div class="relative">
								<Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
								<input v-model="formData.clinicId" type="text" placeholder="Optional code" disabled
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>
					</div>
				</div>

				<!-- Contact Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<Contact class="w-5 h-5 text-green-500 dark:text-green-400" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Contact Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact
								Person<span class="text-red-500">*</span></label>
							<div class="relative">
								<UserProfile class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
								<input v-model="formData.contactPerson" type="text" required
									placeholder="Contact Person"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact
								Number<span class="text-red-500">*</span></label>
							<div class="relative">
								<Phone class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
								<input v-model="formData.phone" type="text" required placeholder="Phone/Tel"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>
					</div>
				</div>

				<!-- Assign Clinicians -->
				<!-- <div>
					<div class="flex items-center gap-2 mb-2">
						<UserPlus class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Assign Clinicians</h3>
					</div>

					<div class="relative">
						<div
						@click="isOpen = !isOpen"
						class="flex flex-wrap items-center gap-1 border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700 cursor-pointer"
						>
							<span
								v-for="(c, index) in formData.assigned_clinic_ids"
								:key="index"
								class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-sm flex items-center gap-1"
							>
								{{ c.name }}
								<button @click.stop="removeClinician(index)" class="text-xs">✕</button>
							</span>
							<span v-if="!formData.assigned_clinic_ids.length" class="text-gray-400 ">Select clinicians...</span>
						</div>

						<div
							v-show="isOpen"
							class="absolute mt-1 w-full bg-white dark:bg-gray-700 rounded shadow-lg z-10"
							>
							<ul
								v-show="isOpen"
								class="absolute mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-gray-300 bg-white dark:bg-gray-700 shadow-lg z-10"
								>
								<li
									v-for="c in clinicianOptions"
									:key="c.id"
									@click="selectClinician(c)"
									class="px-3 py-2 hover:bg-blue-100 dark:hover:bg-gray-600 cursor-pointer"
								>
									{{ c.name }} - {{ roleLabels[c.user_role ?? 0] || 'Unknown' }}
								</li>
							</ul>
						</div>
					</div>
				</div> -->

				<!-- Address Input -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<MapPin class="w-5 h-5 text-green-500 dark:text-green-400" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Address</h3>
					</div>

					<div class="relative">
						<Map class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-500" />
						<input v-model="formData.address" type="text" required placeholder="Clinic Address"
							class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
					</div>
				</div>

				<!-- Logo Upload Section -->
				<ImageUploadCrop v-model="selectedLogoFile" v-model:model-preview-url="formData.logo"
					v-model:model-remove-flag="removeLogoFlag" label="Clinic Logo" :show-label="true" :is-optional="true"
					accept="image/png,image/jpeg,image/jpg" accept-hint="JPEG, JPG, or PNG (max. 2MB)"
					@cropper-opening="isCropperOpen = true" @cropper-closing="isCropperOpen = false" />
			</form>
			<template #actions>
				<!-- Actions -->
				<div class="p-4 flex items-center gap-2">
					<button type="button" @click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
						Cancel
					</button>
					<button type="button" @click="handleSubmitForm"
						class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
						{{ showCreateForm ? 'Save Clinic' : 'Update Clinic' }}
					</button>
				</div>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
// import TableLoader from '@/components/ui/TableLoader.vue'
import ClinicCards from '@/components/Clinic/ClinicCards.vue'
import ImageUploadCrop from '@/components/common/ImageUploadCrop.vue'
import axios from 'axios'
import {
	Search, Funnel, CircleCheck, Hospital,
	MapPin, Phone, Mail, HousePlus,
	Contact, Building, Hash, User as UserProfile,
	Map, FileKey, ChevronDown,
} from 'lucide-vue-next';
import api from '@/services/api'
import { confirmAction } from '@/services/alert'
import { useAppToast } from '@/composables/ui/useAppToast'
import { useProductStatus } from '@/composables/products/useProductStatus'

const { getStatusClasses, getStatusIcon } = useProductStatus()

interface User {
	id: string
	email: string
	name: string
	contactPerson: string
	clinicId?: string
	clinicPubId?: string
	isActive: number
	phone: string
	createdAt: string
	address: string
	clinicianId: string
	assigned_clinician_ids: Clinician[]
	logo?: string | null
}

interface Clinician {
	id: string;
	name: string;
	email: string;
	phone?: string;
	user_role?: number;
}

interface Clinic {
	id: number
	name: string
	user_role: number
}

const selectedLogoFile = ref<File | null>(null)
const removeLogoFlag = ref(false)
const isCropperOpen = ref(false)

const showUsers = ref(false)
const isOpen = ref(false);
const clinicianOptions = ref<Clinician[]>([]);
const selectedClinicians = computed(() => formData.value.assigned_clinic_ids);

const roleLabels: Record<number, string> = {
	1: "Admin",
	2: "Manager",
	3: "Clinician",
	4: "Nurse",
	5: "Doctor",
};

// Pagination
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 9,
	total: 0,
})
const itemsPerPage = ref(9)
const users = ref<User[]>([])
const tableLoader = ref(false);
const userStatus: Record<number, { label: string; classes: string }> = {
	0: { label: 'Active', classes: 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800' },
	1: { label: 'Inactive', classes: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800' },
	2: { label: 'Archived', classes: 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400 border border-orange-200 dark:border-orange-800' },
}

const searchTerm = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')
const selectedUser = ref<User | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const toast = useAppToast()

function getClinicName(clinicId: string) {
	return users.value.find(user => user.id === clinicId)?.name || 'Clinic'
}

const formData = ref({
	name: '',
	email: '',
	contactPerson: '' as User['contactPerson'],
	isActive: true,
	clinicId: '',
	clinicPubId: '',
	password: '',
	phone: '',
	address: '',
	assigned_clinic_ids: [] as Clinician[],
	publicId: '',
	logo: '',
})

async function handleToggleStatus(clinicId: string) {
	const clinicName = getClinicName(clinicId)
	const action = users.value.find(user => user.id === clinicId)?.isActive === 0
		? 'deactivate'
		: 'activate'

	try {
		const result = await confirmAction({
			title: `${action.charAt(0).toUpperCase() + action.slice(1)} Clinic?`,
			text: `Are you sure you want to ${action} this clinic?`,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: `Yes, ${action} it!`,
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			const response = await api.put(`/management/facilities/clinics/${clinicId}/updatestatus`);

			if (response.data.success) {
				const idx = users.value.findIndex(user => user.id === clinicId);
				if (idx !== -1) {
					users.value[idx].isActive = response.data.status;
				}
			}
			await getAllClinics();
			toast.success(`${clinicName} was ${action}d successfully.`)
		}
	} catch (err) {
		console.error('Error toggling status:', err);
		toast.error(`Error trying to ${action} clinic`)
	}
}

const editUser = (user: User) => {
	showUserDetailsModal.value = false;
	showCreateForm.value = false;
	showEditForm.value = true;
	selectedUser.value = user;

	formData.value = {
		name: user.name,
		email: user.email,
		contactPerson: user.contactPerson,
		isActive: !!user.isActive,
		clinicId: user.clinicId ?? '',
		clinicPubId: user.clinicPubId ?? '',
		phone: user.phone ?? '',
		address: user.address ?? '',
		assigned_clinic_ids: [...user.assigned_clinician_ids],
		publicId: user.clinicPubId ?? '',
		password: '',
		logo: user.logo ?? '',
	};

	selectedLogoFile.value = null;
};


async function handleSubmitForm() {
	try {
		const form = new FormData()
		form.append('name', formData.value.name)
		form.append('email', formData.value.email)
		form.append('publicId', formData.value.publicId)
		form.append('contactPerson', formData.value.contactPerson)
		form.append('isActive', formData.value.isActive.toString())
		form.append('clinicId', formData.value.clinicId)
		form.append('phone', formData.value.phone)
		form.append('address', formData.value.address)
		// form.append('assigned_clinicians_id', selectedClinicians.value.map(c => Number(c.id)))

		if (selectedLogoFile.value) {
			form.append('logo', selectedLogoFile.value)
		}
		if (removeLogoFlag.value) {
			form.append('remove_logo', '1')
		}

		if (showCreateForm.value) {
			await api.post(
				'/management/facilities/clinics',
				form,
				{
					headers: {
						'Content-Type': 'multipart/form-data',
						'Accept': 'application/json',
					}
				}
			)

			toast.success(`${formData.value.name || 'Clinic'} was created successfully.`)
			await getAllClinics()

		} else if (showEditForm.value && selectedUser.value) {
			await api.post(
				`/management/facilities/clinics/${selectedUser.value.id}/update`,
				form,
				{
					headers: {
						'Content-Type': 'multipart/form-data',
						'Accept': 'application/json',
					}
				}
			)

			toast.success(`${formData.value.name || 'Clinic'} details were updated successfully.`)
			await getAllClinics()
		}

		closeForm()
	} catch (error: unknown) {
		if (axios.isAxiosError(error)) {
			const status = error.response?.status
			const data = error.response?.data

			if (status === 422 && data?.errors) {
				const messages = Object.values(data.errors).flat().map(String)
				const validationMessage = [
					'Correct the following before saving:',
					...messages.map(message => `- ${message}`),
					'',
					'Please correct these items and try again.',
				].join('\n')
				toast.error(validationMessage, 'Please review the form')
			} else {
				toast.error(data?.message || `Request failed with status code ${status}`)
			}
		} else if (error instanceof Error) {
			toast.error("Error: " + error.message)
		} else {
			toast.error("Something went wrong")
		}
	}
}

function closeForm() {
	showCreateForm.value = false
	showEditForm.value = false
	selectedUser.value = null
	selectedLogoFile.value = null
	isCropperOpen.value = false

	formData.value = {
		name: '',
		email: '',
		contactPerson: '',
		isActive: true,
		clinicId: '',
		clinicPubId: '',
		password: '',
		phone: '',
		address: '',
		assigned_clinic_ids: [],
		publicId: '',
		logo: '',
	}
}

const filteredUsers = computed(() => {
	return users.value.filter(user => {
		const searchLower = searchTerm.value.toLowerCase()
		const matchesSearch = user.name.toLowerCase().includes(searchLower) ||
			user.email.toLowerCase().includes(searchLower) ||
			(user.clinicId?.toLowerCase().includes(searchLower)) ||
			(user.clinicPubId?.toLowerCase().includes(searchLower))
		const matchesRole = roleFilter.value === 'all' || user.contactPerson === roleFilter.value
		const matchesStatus = statusFilter.value === 'all' ||
			(statusFilter.value === 'active' && !user.isActive) ||
			(statusFilter.value === 'inactive' && user.isActive)
		return matchesSearch && matchesRole && matchesStatus
	})
})

const showFormModal = computed({
	get: () => showCreateForm.value || showEditForm.value,
	set: (value: boolean) => {
		if (!value) {
			showCreateForm.value = false
			showEditForm.value = false
		}
	}
})

const showUserDetailsModal = computed({
	get: () => selectedUser.value !== null,
	set: (value: boolean) => {
		if (!value) {
			selectedUser.value = null
		}
	}
})

const totalUsers = computed(() => {
	return selectedUser.value?.assigned_clinician_ids?.length || 0;
});

const activeCliniciansCount = computed(() => {
	return selectedUser.value?.assigned_clinician_ids?.length || 0;
});

async function getAllClinics(page = 1) {
	tableLoader.value = true;
	try {
		const { data } = await api.get(`/management/users/clinics?page=${page}&per_page=${itemsPerPage.value}`, {
			headers: {
				Authorization: `Bearer ${localStorage.getItem('auth_token')}`
			}
		})
		users.value = data.user_data
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

async function fetchClinicianOptions() {
	const { data } = await api.get('/management/users/clinician?simple=true', {
		headers: {
			Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
		},
	});
	clinicianOptions.value = data.map((c: any) => ({
		id: String(c.id),
		name: c.name,
		email: c.email ?? '', // fallback if missing
		phone: c.phone ?? '',
		user_role: c.user_role,
	}));
}

function selectClinician(c: Clinician) {
	if (!formData.value.assigned_clinic_ids.find(cl => cl.id === c.id)) {
		formData.value.assigned_clinic_ids.push(c);
	}
	isOpen.value = false;
}

function removeClinician(index: number) {
	formData.value.assigned_clinic_ids = [
		...formData.value.assigned_clinic_ids.slice(0, index),
		...formData.value.assigned_clinic_ids.slice(index + 1)
	];
}

async function confirmDelete(userId: string) {
	const clinicName = getClinicName(userId)
	const result = await confirmAction({
		title: 'Delete Clinic?',
		html: `
			Are you sure you want to delete <b>${escapeHtml(clinicName)}</b>?<br>
			<span style="color:#d33;">This action cannot be undone!</span>
		`,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Yes, delete it!',
		cancelButtonText: 'Cancel'
	});

	if (result.isConfirmed && userId) {
		try {
			await api.put(`/management/facilities/clinics/${userId}/deleteclinic`)
			toast.success(`${clinicName} was permanently deleted.`)
			await getAllClinics()
		} catch (error) {
			toast.error('Error trying to delete clinic')
		}
	}
}

async function confirmArchive(userId: string) {
	const clinicName = getClinicName(userId)
	const result = await confirmAction({
		title: 'Archive Clinic?',
		text: 'This action will move the clinic to archived status. You can restore it later if needed.',
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Yes, archive it!',
		cancelButtonText: 'Cancel'
	});

	if (result.isConfirmed && userId) {
		try {
			await api.put(`/management/facilities/clinics/${userId}/archiveclinic`)
			toast.success(`${clinicName} was archived successfully and can be restored later.`)
			await getAllClinics()
		} catch (error) {
			toast.error('Error trying to archive clinic')
		}
	}
}

async function confirmUnarchive(userId: string) {
	const clinicName = getClinicName(userId)
	const result = await confirmAction({
		title: 'Unarchive Clinic?',
		text: 'This action will restore the clinic from archived status.',
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#6b7280',
		confirmButtonText: 'Yes, unarchive it!',
		cancelButtonText: 'Cancel'
	});

	if (result.isConfirmed && userId) {
		try {
			await api.put(`/management/facilities/clinics/${userId}/unarchiveclinic`);
			toast.success(`${clinicName} was successfully restored from archived status.`);
			await getAllClinics();
		} catch (error) {
			toast.error('Error trying to unarchive clinic');
		}
	}
}

function escapeHtml(value: string) {
	return value
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;')
}



onMounted(async () => {
	getAllClinics(1);
	fetchClinicianOptions();
})

watch(itemsPerPage, () => {
	getAllClinics(1)
})
</script>
