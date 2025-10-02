<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manufacturer Management</h1>
			</div>

			<button
				@click="
					selectedManufacturer = null; 
					showCreateForm = true
				"
				class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
			>
				<HousePlus class="w-4 h-4 mr-2" />
				Add Manufacturer
			</button>
		</div>

		<!-- Filters -->
		<div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded shadow-sm border border-gray-200 dark:border-gray-700">
			<div class="flex-1">
				<div class="relative">
					<Search class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
					<input
						v-model="searchTerm"
						type="text"
						placeholder="Search Manufacturer..."
						class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
					/>
				</div>
			</div>
			<div class="flex items-center space-x-2">
				<Funnel class="w-4 h-4 text-gray-500 dark:text-gray-400" />
				<select
				v-model="statusFilter"
				class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
				>
					<option value="all">All Status</option>
					<option value="active">Active</option>
					<option value="inactive">Inactive</option>
					<option value="archive">Archived</option>
				</select>
			</div>
			<div class="flex items-center space-x-2">
				<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
				<select
					id="per-page"
					v-model="itemsPerPage"
					class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded 
						focus:ring-2 focus:ring-blue-500 focus:border-transparent 
						bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
				>
					<option value="10">10</option>
					<option value="25">25</option>
					<option value="50">50</option>
				</select>
			</div>
		</div>

		<!-- Card View -->
		<ContentLoader v-if="tableLoader"/>
		<div
		v-if="filteredManufacturers && filteredManufacturers.length > 0"
		class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
		>
		<div
			v-for="manufacturer in filteredManufacturers"
			:key="manufacturer.id"
			class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow"
		>
			<div class="flex items-start justify-between mb-4">
				<div>
					<div class="flex items-center gap-3">
					<!-- Icon -->
					<div class="p-2 bg-green-100 rounded-lg">
						<Factory class="w-5 h-5 text-green-600" />
					</div>

					<div class="flex flex-col">
						<h3 class="font-semibold text-gray-900 dark:text-white">
						{{ manufacturer.manufacturerName }}
						</h3>
						<span
							:class="[
							'inline-flex px-2 py-1 text-xs rounded-full w-fit',
							manufacturer.manufacturerStatus === 0
							? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
							: manufacturer.manufacturerStatus === 1
							? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
							: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
						]"
						>
						{{ manufacturer.manufacturerStatus === 0 ? 'Active' : manufacturer.manufacturerStatus === 1 ? 'Inactive' : 'Archived' }}
						</span>
					</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="flex items-center space-x-2">
					<!-- View -->
					<button
						@click="viewManufacturer = manufacturer"
						class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
					>
						<Eye class="w-5 h-4" />
					</button>

					<!-- Edit -->
					<button
						@click="editManufacturer(manufacturer)"
						class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
					>
						<SquarePen class="w-4 h-4" />
					</button>

					<!-- Toggle Active / Inactive / Reactivate -->
					<button
						@click="handleToggleStatus(manufacturer.id, manufacturer.manufacturerStatus)"
						:class="manufacturer.manufacturerStatus === 0
						? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
						: 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'"
						:title="manufacturer.manufacturerStatus === 0
						? 'Deactivate'
						: (manufacturer.manufacturerStatus === 1 ? 'Activate' : 'Reactivate')"
					>
						<component
						:is="manufacturer.manufacturerStatus === 0 ? CircleX : CircleCheck"
						class="w-4 h-4"
						/>
					</button>

					<!-- Archive when not Archived -->
					<button
						v-if="manufacturer.manufacturerStatus !== 2"
						@click="handleArchiveManufacturer(manufacturer.id)"
						class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
						title="Archive"
					>
						<Archive class="w-4 h-4" />
					</button>

					<!-- Delete only when Archived -->
					<button
						v-if="manufacturer.manufacturerStatus === 2"
						@click="handleDeleteManufacturer(manufacturer.id)"
						class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
						title="Delete"
					>
						<Trash2 class="w-4 h-4" />
					</button>
				</div>

			</div>

			<div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
				<div class="flex items-center gap-2">
					<Mail class="w-4 h-4" />
					<span>{{ manufacturer.primaryEmail }}</span>

					<template v-if="manufacturer.secondaryEmail">
						<span class="text-gray-400">|</span>
						<span>{{ manufacturer.secondaryEmail }}</span>
					</template>
				</div>
				<div class="flex items-center gap-2" v-if="manufacturer.contactNumber">
					<Phone class="w-4 h-4" />
					<span>{{ manufacturer.contactNumber }}</span>
				</div>
				<div class="flex items-center gap-2" v-if="manufacturer.website">
					<Globe class="w-4 h-4" />
					<span>{{ manufacturer.website }}</span>
				</div>
				<div class="flex items-center gap-2" v-if="manufacturer.filename">
					<FileText class="w-4 h-4" />
					<span>IVR Form: {{ manufacturer.filename }}</span>
				</div>
				<hr />
				<div class="flex items-center gap-2">
					<strong>Contact Person:</strong>
					<span>{{ manufacturer.contactPerson }}</span>
				</div>

				<hr />
				<div class="flex items-center gap-2">
					<strong>Brands:</strong>
					<span
					v-for="brand in brands"
					:key="brand"
					class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
					>
					{{ brand }}
					</span>
				</div>
				</div>
			</div>
		</div>

		<div
		v-else-if="filteredManufacturers && filteredManufacturers.length === 0 && !tableLoader"
		class="">
			<div class="flex flex-col items-center justify-center gap-2 text-center">
				<Factory class="w-10 h-10 mb-1 text-gray-700" />
				<span class="text-gray-600 dark:text-gray-300">No manufacturers found.</span>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination v-if="filteredManufacturers && filteredManufacturers.length > 0" :pagination="pagination" @update:page="getAllManufacturers" />
		</template>

		<!-- Create/Edit Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Manufacturer' : 'Edit Manufacturer'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<!-- Manufacturer Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<Building2 class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Manufacturer Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<!-- Manufacturer Name -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span class="text-red-500">*</span></label>
							<div class="relative">
								<Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
								v-model="formData.manufacturerName"
								type="text"
								required
								placeholder="Manufacturer Name"
								class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
										focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>

						<!-- Status -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status<span class="text-red-500">*</span></label>
							<div class="relative">
								<CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select
									v-model="formData.manufacturerStatus"
									required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option :value="0">Active</option>
									<option :value="1">Inactive</option>
									<option :value="2">Archived</option>
								</select>
							</div>
						</div>

						<!-- Website -->
						<div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Website (Optional)</label>
							<div class="relative">
								<Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
								v-model="formData.website"
								type="text"
								placeholder="https://"
								@blur="fixWebsite"
								class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
										focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
						
						<!-- Address -->
						<div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Address (Optional)</label>
							<div class="relative">
								<MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
								v-model="formData.address"
								type="text"
								placeholder="Manufacturer Address"
								class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
										focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
					</div>
				</div>

				<!-- Contact Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<Contact class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Contact Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<!-- Contact Person -->
						<div>
						<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Person<span class="text-red-500">*</span></label>
						<div class="relative">
							<UserRound class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
							<input
							v-model="formData.contactPerson"
							type="text"
							required
							placeholder="Contact Person"
							class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
									focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							/>
						</div>
						</div>
						<!-- Contact Number -->
						<div>
						<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Number<span class="text-red-500">*</span></label>
						<div class="relative">
							<Phone class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
							<input
							v-model="formData.contactNumber"
							type="text"
							required
							placeholder="Phone/Tel"
							class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
									focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							/>
						</div>
						</div>

						<!-- Primary Email -->
						<div>
						<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Primary Email<span class="text-red-500">*</span></label>
						<div class="relative">
							<Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
							<input
							v-model="formData.primaryEmail"
							type="email"
							required
							placeholder="example@manufacturer.com"
							class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
									focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							/>
						</div>
						</div>
						<!-- Secondary Email -->
						<div>
						<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Secondary Email (Optional)</label>
						<div class="relative">
							<Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
							<input
							v-model="formData.secondaryEmail"
							type="email"
							placeholder="example@manufacturer.com"
							class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
									focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							/>
						</div>
						</div>
					</div>
				</div>

				<!-- IVR Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<FilePenLine class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">IVR Information</h3>
					</div>
					<div
						class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer 
							bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
						@drop="handleDrop"
						@dragover="allowDrop"
					>
						<input
						id="ivr-upload"
						type="file"
						accept=".pdf,.doc,.docx"
						class="hidden"
						@change="handleFileChange"
						/>
						<label for="ivr-upload" class="flex flex-col items-center justify-center text-center cursor-pointer">
						<svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6h.1a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
						</svg>
						<p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
							<span class="font-semibold">Click to upload</span> or drag and drop
						</p>
						<p class="text-xs text-gray-500 dark:text-gray-400">PDF or DOCX (max. 10MB)</p>
						</label>
					</div>
					<!-- File Preview -->
					<div v-if="selectedFile" class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
						<FileText class="w-4 h-4 text-gray-400" />
						<span>Selected file: <span class="font-medium">{{ selectedFile.name }}</span></span>
					</div>
				</div>

				<!-- Modal Footer -->
				<div class="flex justify-end space-x-3 pt-4">
					<button
						type="button"
						@click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
							text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
					>
						Cancel
					</button>
					<button
						type="submit"
						class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
					>
						{{ showCreateForm ? 'Create Manufacturer' : 'Update Manufacturer' }}
					</button>
				</div>
			</form>
		</BaseModal>

		<BaseModal v-model="showManufacturerDetailsModal" title="Manufacturer Details">
			<template v-if="viewManufacturer">
				<div class="space-y-4">
				<!-- Header -->
				<div class="flex items-center space-x-4">
					<div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
					<Factory class="w-8 h-8 text-green-500" />
					</div>
					<div>
					<p class="text-xl font-semibold text-gray-900 dark:text-white">
						{{ viewManufacturer.manufacturerName }}
					</p>
					<span
							:class="[
							'inline-flex px-2 py-1 text-xs rounded-full w-fit',
							viewManufacturer.manufacturerStatus === 0
							? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
							: viewManufacturer.manufacturerStatus === 1
							? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
							: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
						]"
						>
						{{ viewManufacturer.manufacturerStatus === 0 ? 'Active' : viewManufacturer.manufacturerStatus === 1 ? 'Inactive' : 'Archived' }}
						</span>
					</div>
				</div>

				<!-- Contact Info -->
				<div>
					<!-- <h4 class="text-lg text-gray-900 dark:text-white mb-4">Manufacturer Contact Information</h4> -->
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="space-y-4">
						<div class="flex items-center space-x-3">
						<Mail class="w-5 h-5 text-purple-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Primary Email</p>
							<p class="text-gray-900 dark:text-white">{{ viewManufacturer.primaryEmail }}</p>
						</div>
						</div>
						<div v-if="viewManufacturer.secondaryEmail" class="flex items-center space-x-3">
						<Mail class="w-5 h-5 text-indigo-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Secondary Email</p>
							<p class="text-gray-900 dark:text-white">{{ viewManufacturer.secondaryEmail }}</p>
						</div>
						</div>
						<div class="flex items-center space-x-3">
						<UserRound class="w-5 h-5 text-orange-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Contact Person</p>
							<p class="text-gray-900 dark:text-white">{{ viewManufacturer.contactPerson }}</p>
						</div>
						</div>
					</div>
					<div class="space-y-4">
						<div class="flex items-center space-x-3">
						<Phone class="w-5 h-5 text-green-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Contact Number</p>
							<p class="text-gray-900 dark:text-white">{{ viewManufacturer.contactNumber }}</p>
						</div>
						</div>
						<div v-if="viewManufacturer.website" class="flex items-center space-x-3">
						<Globe class="w-5 h-5 text-blue-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Website</p>
							<a
							:href="viewManufacturer.website"
							target="_blank"
							class="text-blue-600 underline"
							>{{ viewManufacturer.website }}</a>
						</div>
						</div>
						<div v-if="viewManufacturer.address" class="flex items-center space-x-3">
						<MapPin class="w-5 h-5 text-red-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Address</p>
							<p class="text-gray-900 dark:text-white">{{ viewManufacturer.address }}</p>
						</div>
						</div>
					</div>
					</div>
				</div>

				<!-- IVR Form Information -->
				<div
				v-if="viewManufacturer.filename"
				class="mt-6 rounded-lg border border-blue-100 bg-blue-50 p-4 dark:bg-blue-900/10 dark:border-blue-900/20"
				>
				<h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
					IVR Form Information
				</h4>

				<div class="grid grid-cols-1 sm:grid-cols-2 items-center gap-4">
					<!-- Form Type -->
					<div class="flex items-center gap-3">
					<!-- PDF icon -->
					<div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-50">
						<FileText class="w-6 h-6 text-red-500" />
					</div>
					<div>
						<p class="text-xs text-gray-600 dark:text-gray-400">Form Type</p>
						<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-medium dark:bg-red-900/20 dark:text-red-400">
						PDF
						</span>
					</div>
					</div>

					<!-- Template Download -->
					<div class="flex items-center gap-3">
					<div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100">
						<Download class="w-6 h-6 text-blue-500" />
					</div>
					<div>
						<p class="text-xs text-gray-600 dark:text-gray-400">Template</p>
						<a
						:href="`/uploads/ivr/${viewManufacturer.filename}`"
						download
						class="text-blue-600 hover:underline text-sm font-medium"
						>
						Download IVR Form
						</a>
					</div>
					</div>
				</div>
				</div>

				</div>
			</template>
			<template #actions>
				<div class="p-4">
				<button
					type="button"
					@click="viewManufacturer = null"
					class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
				>
					Close
				</button>
				</div>
			</template>
		</BaseModal>

	</div>
</template>

<script setup lang="ts">
import { useToast } from "vue-toastification"
import Swal from "sweetalert2"
import { ref, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import { Search, Funnel, Eye, SquarePen, CircleCheck, CircleX, Trash2, HousePlus, FileText, Factory, Globe, Building2, Mail, Contact, Phone, UserRound, FilePenLine, MapPin, Archive, Download  } from 'lucide-vue-next'
import api from '@/services/api'
import axios from "axios";

const toast = useToast()

// types
interface Manufacturer {
  id: number
  manufacturerName: string
  primaryEmail: string
  secondaryEmail?: string
  website?: string
  address?: string
  contactPerson: string
  contactNumber: string
  filename?: string
  manufacturerStatus: number
  createdAt: string
  updatedAt: string
}

const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const itemsPerPage = ref(10)
const manufacturers = ref<Manufacturer[]>([])
const tableLoader = ref(false)

const selectedManufacturer = ref<Manufacturer | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const viewManufacturer = ref<Manufacturer | null>(null)

const showManufacturerDetailsModal = computed({
  get: () => viewManufacturer.value !== null,
  set: (value: boolean) => {
    if (!value) viewManufacturer.value = null
  },
})

const formData = ref({
  manufacturerName: '',
  primaryEmail: '',
  secondaryEmail: '',
  website: '',
  address: '',
  contactPerson: '',
  contactNumber: '',
  filename: '',
  manufacturerStatus: 0,
})

async function getAllManufacturers(page = 1) {
  tableLoader.value = true
  try {
    const { data } = await api.get(`/management/manufacturers?page=${page}&per_page=${itemsPerPage.value}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    manufacturers.value = data.manufacturerData.map((m: any) => ({
      id: m.id,
      manufacturerName: m.manufacturerName,
      primaryEmail: m.primaryEmail,
      secondaryEmail: m.secondaryEmail,
      website: m.website,
      address: m.address,
      contactPerson: m.contactPerson,
      contactNumber: m.contactNumber,
      filename: m.filepath ? m.filepath.split('/').pop() : '',
      manufacturerStatus: m.manufacturerStatus,
      createdAt: m.createdAt,
      updatedAt: m.updatedAt,
    }))
    pagination.value = {
      current_page: data.meta.current_page,
      last_page: data.meta.last_page,
      per_page: data.meta.per_page,
      total: data.meta.total,
    }
  } catch (error) {
    console.error('Error fetching manufacturers:', error)
  } finally {
    tableLoader.value = false
  }
}

function editManufacturer(m: Manufacturer) {
	selectedManufacturer.value = m
   	// formData.value = { ...m }
	 formData.value = {
		manufacturerName: m.manufacturerName || '',
		primaryEmail: m.primaryEmail || '',
		secondaryEmail: m.secondaryEmail || '',
		website: m.website || '',
		address: m.address || '',
		contactPerson: m.contactPerson || '',
		contactNumber: m.contactNumber || '',
		filename: m.filename || '',
		manufacturerStatus: m.manufacturerStatus ?? 0,
	}
  	showEditForm.value = true
}

// submit create/edit form
async function handleSubmitForm() {
  try {
    const form = new FormData()

    // normalize website
    let website = formData.value.website || ''
    if (website && !/^https?:\/\//i.test(website)) {
      website = 'https://' + website
    }

    form.append('manufacturerName', formData.value.manufacturerName)
    form.append('primaryEmail', formData.value.primaryEmail)
    form.append('secondaryEmail', formData.value.secondaryEmail || '')
    form.append('website', website)
    form.append('address', formData.value.address || '')
    form.append('contactPerson', formData.value.contactPerson)
    form.append('contactNumber', formData.value.contactNumber)
    form.append('manufacturerStatus', formData.value.manufacturerStatus.toString())

    if (selectedFile.value) {
      form.append('ivrForm', selectedFile.value)
    }

    if (showCreateForm.value) {
      await api.post('/management/manufacturers', form, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'multipart/form-data'
        }
      })
	  toast.success('Manufacturer created!')
    } else if (showEditForm.value && selectedManufacturer.value) {
      await api.post(`/management/manufacturers/${selectedManufacturer.value.id}?_method=PUT`, form, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'multipart/form-data'
        }
      })
	  toast.success('Manufacturer updated!')
    }

    closeForm()
    getAllManufacturers(1)
  } catch (error: unknown) {
		if (axios.isAxiosError(error)) {
			const status = error.response?.status
			const data = error.response?.data

			if (status === 422 && data?.errors) {
				const messages = Object.values(data.errors).flat()
				toast.error("Error: " + messages.join("\n"))
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

// archive manufacturer
async function handleArchiveManufacturer(id: number) {
  try {
    const result = await Swal.fire({
      	title: 'Archive Manufacturer?',
		text: "This action will move the manufacturer to archived status. You can restore it later if needed.",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Yes, archive it!',
		cancelButtonText: 'Cancel'
    })

    if (result.isConfirmed) {
      const response = await api.get(`/management/manufacturers/${id}/archive`)
      const data = response.data

      const idx = manufacturers.value.findIndex(m => m.id === id)
      if (idx !== -1) {
        manufacturers.value[idx].manufacturerStatus = data.data.manufacturerStatus
      }

      toast.success('Manufacturer archived successfully')
    }
  } catch (error) {
    console.error(error)
    toast.error('Error archiving manufacturer')
  }
}

// toggle status (active/inactive/reactivate)
async function handleToggleStatus(id: number, currentStatus: number) {
  let actionText = ''

  if (currentStatus === 0) actionText = 'deactivate'
  else if (currentStatus === 1) actionText = 'activate'
  else if (currentStatus === 2) actionText = 'reactivate'

  try {
    const result = await Swal.fire({
      title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Manufacturer?`,
      text: `Are you sure you want to ${actionText} this manufacturer?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: `Yes, ${actionText} it!`,
      cancelButtonText: 'Cancel'
    })

    if (result.isConfirmed) {
      const response = await api.get(`/management/manufacturers/${id}/toggle`)
      const data = response.data

      const idx = manufacturers.value.findIndex(m => m.id === id)
      if (idx !== -1) {
        manufacturers.value[idx].manufacturerStatus = data.data.manufacturerStatus
      }

      toast.success(`Manufacturer ${actionText}d successfully`)
    }
  } catch (error) {
    console.error(error)
    toast.error(`Error trying to ${actionText} manufacturer`)
  }
}

// delete manufacturer
async function handleDeleteManufacturer(id: number) {
  try {
    const result = await Swal.fire({
      title: 'Delete Manufacturer?',
      text: "This action cannot be undone.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    })

    if (result.isConfirmed) {
      await api.delete(`/management/manufacturers/${id}`)
      manufacturers.value = manufacturers.value.filter(m => m.id !== id)
      toast.success('Manufacturer deleted successfully')
    }
  } catch (error) {
    console.error(error)
    toast.error('Error deleting manufacturer')
  }
}

function closeForm() {
  showCreateForm.value = false
  showEditForm.value = false
  selectedManufacturer.value = null
  formData.value = {
    manufacturerName: '',
    primaryEmail: '',
    secondaryEmail: '',
    website: '',
    address: '',
    contactPerson: '',
    contactNumber: '',
    filename: '',
    manufacturerStatus: 0, // reset to Active
  }
}

// search & filter
const searchTerm = ref('')
const statusFilter = ref('all')
const filteredManufacturers = computed(() => {
  return manufacturers.value.filter(m => {
    const matchesSearch =
      m.manufacturerName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      m.primaryEmail.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'active' && m.manufacturerStatus === 0) ||
      (statusFilter.value === 'inactive' && m.manufacturerStatus === 1) ||
	  (statusFilter.value === 'archive' && m.manufacturerStatus === 2)
    return matchesSearch && matchesStatus
  })
})

// file handling
const selectedFile = ref<File | null>(null)
function handleFileChange(event: any) { selectedFile.value = event.target.files[0] }
function handleDrop(event: any) { event.preventDefault(); selectedFile.value = event.dataTransfer.files[0] }
function allowDrop(event: any) { event.preventDefault() }

// modal binding
const showFormModal = computed({
  get: () => showCreateForm.value || showEditForm.value,
  set: (val: boolean) => { if (!val) { showCreateForm.value = false; showEditForm.value = false } }
})

function fixWebsite() {
  const url = formData.value.website
  if (url && !/^https?:\/\//i.test(url)) {
    formData.value.website = 'https://' + url
  }
}

onMounted(() => {
  getAllManufacturers(1)
})

watch(itemsPerPage, () => {
  getAllManufacturers(1)
})

watch(showFormModal, (val) => {
  if (!val) closeForm()
})


// STATIC VALUES ---- WILL CHANGE TO DYNAMIC LATER
const brands = ['Brand A', 'Brand B', 'Brand C'] // Placeholder brands

</script> 
