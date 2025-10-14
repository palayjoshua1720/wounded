<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Brand Management</h1>
			</div>

			<button
				@click="
					selectedBrand = null; 
					showCreateForm = true
				"
				class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
			>
				<PackagePlus class="w-4 h-4 mr-2" />
				Add Brand
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
						placeholder="Search Brand..."
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
		v-if="filteredBrands && filteredBrands.length > 0"
		class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
		>
		<div
			v-for="brand in filteredBrands"
			:key="brand.id"
			class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow"
		>
			<div class="flex items-start justify-between mb-4">
				<div>
					<div class="flex items-center gap-3">
					<!-- Icon -->
					<div class="p-2 bg-green-100 rounded-lg">
						<Package class="w-5 h-5 text-green-600" />
					</div>

					<div class="flex flex-col">
						<h3 class="font-semibold text-gray-900 dark:text-white">
						 {{ brand.brandName }}
						</h3>
						<span
							:class="[
							'inline-flex px-2 py-1 text-xs rounded-full w-fit',
							brand.brandStatus === 0
							? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
							: brand.brandStatus === 1
							? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
							: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
						]"
						>
						{{ brand.brandStatus === 0 ? 'Active' : brand.brandStatus === 1 ? 'Inactive' : 'Archived' }}
						</span>
					</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="flex items-center space-x-2">
					<!-- View -->
					<button
						@click="viewBrand = brand"
						class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
					>
						<Eye class="w-5 h-4" />
					</button>

					<!-- Edit -->
					<button
						@click="editBrand(brand)"
						class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
					>
						<SquarePen class="w-4 h-4" />
					</button>

					<!-- Toggle Active / Inactive / Reactivate -->
					<button
						@click="handleToggleStatus(brand.id, brand.brandStatus)"
						:class="brand.brandStatus === 0
						? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
						: 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'"
						:title="brand.brandStatus === 0
						? 'Deactivate'
						: (brand.brandStatus === 1 ? 'Activate' : 'Reactivate')"
					>
						<component
						:is="brand.brandStatus === 0 ? CircleX : CircleCheck"
						class="w-4 h-4"
						/>
					</button>

					<!-- Archive when not Archived -->
					<button
						v-if="brand.brandStatus !== 2"
						@click="handleArchiveBrand(brand.id)"
						class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
						title="Archive"
					>
						<Archive class="w-4 h-4" />
					</button>

					<!-- Delete only when Archived -->
					<button
						v-if="brand.brandStatus === 2"
						@click="handleDeleteBrand(brand.id)"
						class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
						title="Delete"
					>
						<Trash2 class="w-4 h-4" />
					</button>
				</div>

			</div>

			<div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
				<div class="flex items-center gap-2">
					<Factory class="w-4 h-4" />
					<span>{{ brand.manufacturerName || 'No Manufacturer' }}</span>
				</div>
                <div class="flex items-center gap-2">
					<TriangleAlert class="w-4 h-4" />
					<span>{{ brand.mue || '0' }} MUE</span>
				</div>
                

				<hr />
				<div class="flex items-center gap-2 flex-wrap">
					<strong>Available Sizes:</strong>
					<span
					v-for="sizeObj in brand.graftSizes"
					:key="sizeObj.size"
					class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 ml-1 mt-1"
					>
					{{ sizeObj.size }}
					</span>
				</div>
				</div>
			</div>
		</div>

		<div
		v-else-if="filteredBrands && filteredBrands.length === 0 && !tableLoader"
		class="">
			<div class="flex flex-col items-center justify-center gap-2 text-center">
				<Factory class="w-10 h-10 mb-1 text-gray-700" />
				<span class="text-gray-600 dark:text-gray-300">No brands found.</span>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination v-if="filteredBrands && filteredBrands.length > 0" :pagination="pagination" @update:page="getAllBrands" />
		</template>

		<!-- Create/Edit Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Brand' : 'Edit Brand'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<!-- Brand Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<Package class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Brand Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span class="text-red-500">*</span></label>
							<div class="relative">
								<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
								v-model="formData.brandName"
								type="text"
								required
								placeholder="Brand Name"
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
									v-model="formData.brandStatus"
									required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option :value="0">Active</option>
									<option :value="1">Inactive</option>
									<option :value="2">Archived</option>
								</select>
							</div>
						</div>

						<!-- Manufacturer -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Manufacturer<span class="text-red-500">*</span></label>
							<div class="relative">
								<Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select
									v-model="formData.manufacturerId"
									required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option
										v-for="opt in manufacturerOptions"
										:key="opt.value"
										:value="opt.value"
									>
										{{ opt.label }}
									</option>
								</select>
							</div>
						</div>

						<div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">MUE (Medically Unlikely Edits)<span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                v-model.number="formData.mue"
                                type="number"
                                required
                                placeholder="Enter MUE value"
                                class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum units allowed per day per patient</p>
                        </div>

                        <div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Description (Optional)</label>
							<div class="relative">
								<Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<textarea
									v-model="formData.description"
									placeholder="Description"
									rows="3"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-vertical"
								></textarea>
							</div>
						</div>
					</div>
				</div>

                <!-- Graft Sizes -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<PencilRuler class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Graft Sizes</h3>
					</div>
					<div class="space-y-3">
						<div
							v-for="(graftSize, index) in formData.graftSizes"
							:key="index"
							:class="index > 0 ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : ''"
						>
							<div v-if="index > 0" class="flex items-center justify-between mb-2">
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">Size {{ index + 1 }}</span>
								<button
									type="button"
									@click="removeGraftSize(index)"
									class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
								>
									<Trash2 class="w-4 h-4" />
								</button>
							</div>
							<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Size (e.g., 2cm x 2cm)</label>
									<div class="relative">
										<RulerDimensionLine class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model="graftSize.size"
										type="text"
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
										step="0.01"
										placeholder="0.00"
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
						{{ showCreateForm ? 'Create Brand' : 'Update Brand' }}
					</button>
				</div>
			</form>
		</BaseModal>

		<BaseModal v-model="showBrandDetailsModal" title="Brand Details">
			<template v-if="viewBrand">
				<div class="space-y-4">
				<!-- Header -->
				<div class="flex items-center space-x-4">
					<div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
					<Factory class="w-8 h-8 text-green-500" />
					</div>
					<div>
					<p class="text-xl font-semibold text-gray-900 dark:text-white">
						{{ viewBrand.brandName }}
					</p>
					<span
							:class="[
							'inline-flex px-2 py-1 text-xs rounded-full w-fit',
							viewBrand.brandStatus === 0
							? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
							: viewBrand.brandStatus === 1
							? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
							: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
						]"
						>
						{{ viewBrand.brandStatus === 0 ? 'Active' : viewBrand.brandStatus === 1 ? 'Inactive' : 'Archived' }}
						</span>
					</div>
				</div>

				<!-- Contact Info -->
				<div>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="space-y-4">
						<div class="flex items-center space-x-3">
						<Mail class="w-5 h-5 text-purple-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Primary Email</p>
							<p class="text-gray-900 dark:text-white">{{ viewBrand.primaryEmail }}</p>
						</div>
						</div>
						<div v-if="viewBrand.secondaryEmail" class="flex items-center space-x-3">
						<Mail class="w-5 h-5 text-indigo-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Secondary Email</p>
							<p class="text-gray-900 dark:text-white">{{ viewBrand.secondaryEmail }}</p>
						</div>
						</div>
						<div class="flex items-center space-x-3">
						<UserRound class="w-5 h-5 text-orange-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Contact Person</p>
							<p class="text-gray-900 dark:text-white">{{ viewBrand.contactPerson }}</p>
						</div>
						</div>
					</div>
					<div class="space-y-4">
						<div class="flex items-center space-x-3">
						<Phone class="w-5 h-5 text-green-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Contact Number</p>
							<p class="text-gray-900 dark:text-white">{{ viewBrand.contactNumber }}</p>
						</div>
						</div>
						<div v-if="viewBrand.website" class="flex items-center space-x-3">
						<Globe class="w-5 h-5 text-blue-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Website</p>
							<a
							:href="viewBrand.website"
							target="_blank"
							class="text-blue-600 underline"
							>{{ viewBrand.website }}</a>
						</div>
						</div>
						<div v-if="viewBrand.address" class="flex items-center space-x-3">
						<MapPin class="w-5 h-5 text-red-500" />
						<div>
							<p class="text-sm text-gray-700 dark:text-gray-300">Address</p>
							<p class="text-gray-900 dark:text-white">{{ viewBrand.address }}</p>
						</div>
						</div>
					</div>
					</div>
				</div>

				<!-- IVR Form Information -->
				<div
				v-if="viewBrand.filename"
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
							<button @click="downloadIVRForm(viewBrand.id)" class="text-blue-600 hover:underline text-sm font-medium">
							Download IVR Form
							</button>

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
					@click="viewBrand = null"
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
import { Package, PackagePlus, Eye, SquarePen, Trash2, Archive, CircleCheck, CircleX, Factory, TriangleAlert, Hash, RulerDimensionLine, Diameter, DollarSign, PencilRuler, Plus, Search, Funnel, Mail, UserRound, Phone, Globe, MapPin, FileText, Download } from 'lucide-vue-next'
import api from '@/services/api'
import axios from "axios";

const toast = useToast()

// types
interface GraftSize {
  size: string
  area: number
  price: number
}

interface SimpleManufacturer {
  id: number
  manufacturerName: string
}

interface Brand {
  id: number
  brandName: string
  primaryEmail: string
  secondaryEmail?: string
  website?: string
  address?: string
  contactPerson: string
  contactNumber: string
  filename?: string
  brandStatus: number
  manufacturerId?: number
  manufacturerName?: string
  mue?: number
  description?: string
  graftSizes: GraftSize[]
  createdAt: string
  updatedAt: string
}

const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const itemsPerPage = ref(10)
const brands = ref<Brand[]>([])
const manufacturers = ref<SimpleManufacturer[]>([])
const tableLoader = ref(false)

const selectedBrand = ref<Brand | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const viewBrand = ref<Brand | null>(null)

const showBrandDetailsModal = computed({
  get: () => viewBrand.value !== null,
  set: (value: boolean) => {
    if (!value) viewBrand.value = null
  },
})


const formData = ref({
  brandName: '',
  primaryEmail: '',
  secondaryEmail: '',
  website: '',
  address: '',
  contactPerson: '',
  contactNumber: '',
  filename: '',
  brandStatus: 0,
  manufacturerId: null as number | null,
  mue: 0,
  description: '',
  graftSizes: [{ size: '', area: 0, price: 0 }] as GraftSize[]
})

const manufacturerOptions = computed(() => {
  const opts = manufacturers.value.map(m => ({ value: m.id, label: m.manufacturerName }))
  if (formData.value.manufacturerId === null) {
    return [{ value: null, label: 'Select a Manufacturer' }, ...opts]
  } else {
    return opts
  }
})

async function getAllManufacturers() {
  try {
    const { data } = await api.get(`/management/manufacturers`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    manufacturers.value = data.manufacturerData.map((m: any) => ({
      id: m.id,
      manufacturerName: m.manufacturerName,
    }))
  } catch (error) {
    console.error('Error fetching manufacturers:', error)
  }
}

async function getAllBrands(page = 1) {
  tableLoader.value = true
  try {
    const { data } = await api.get(`/management/brands?page=${page}&per_page=${itemsPerPage.value}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    brands.value = data.brandData.map((b: any) => ({
      id: b.id,
      brandName: b.brandName,
      primaryEmail: b.primaryEmail,
      secondaryEmail: b.secondaryEmail,
      website: b.website,
      address: b.address,
      contactPerson: b.contactPerson,
      contactNumber: b.contactNumber,
      filename: b.filepath ? b.filepath.split('/').pop() : '',
      brandStatus: b.brandStatus,
      manufacturerId: b.manufacturerId ?? null,
      manufacturerName: b.manufacturerName,
      mue: b.mue,
      description: b.description,
      graftSizes: b.graftSizes || [],
      createdAt: b.createdAt,
      updatedAt: b.updatedAt,
    }))
    pagination.value = {
      current_page: data.meta.current_page,
      last_page: data.meta.last_page,
      per_page: data.meta.per_page,
      total: data.meta.total,
    }
  } catch (error) {
    console.error('Error fetching brands:', error)
  } finally {
    tableLoader.value = false
  }
}

function editBrand(b: Brand) {
	selectedBrand.value = b
	formData.value = {
		brandName: b.brandName || '',
		primaryEmail: b.primaryEmail || '',
		secondaryEmail: b.secondaryEmail || '',
		website: b.website || '',
		address: b.address || '',
		contactPerson: b.contactPerson || '',
		contactNumber: b.contactNumber || '',
		filename: b.filename || '',
		brandStatus: b.brandStatus ?? 0,
		manufacturerId: b.manufacturerId ?? null,
		mue: b.mue || 0,
		description: b.description || '',
		graftSizes: b.graftSizes.length > 0 ? [...b.graftSizes] : [{ size: '', area: 0, price: 0 }]
	}
  	showEditForm.value = true
}

function addGraftSize() {
  formData.value.graftSizes.push({ size: '', area: 0, price: 0 })
}

function removeGraftSize(index: number) {
  formData.value.graftSizes.splice(index, 1)
  if (formData.value.graftSizes.length === 0) {
    formData.value.graftSizes.push({ size: '', area: 0, price: 0 })
  }
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

    form.append('brandName', formData.value.brandName)
    form.append('primaryEmail', formData.value.primaryEmail)
    form.append('secondaryEmail', formData.value.secondaryEmail || '')
    form.append('website', website)
    form.append('address', formData.value.address || '')
    form.append('contactPerson', formData.value.contactPerson)
    form.append('contactNumber', formData.value.contactNumber)
    form.append('brandStatus', formData.value.brandStatus.toString())
    form.append('manufacturerId', (formData.value.manufacturerId ?? '').toString())
    form.append('mue', formData.value.mue.toString())
    form.append('description', formData.value.description || '')

    // Append graft sizes as JSON
    form.append('graftSizes', JSON.stringify(formData.value.graftSizes))

    if (selectedFile.value) {
      form.append('ivrForm', selectedFile.value)
    }

    if (showCreateForm.value) {
      await api.post('/management/brands', form, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'multipart/form-data'
        }
      })
	  toast.success('Brand created!')
    } else if (showEditForm.value && selectedBrand.value) {
      await api.post(`/management/brands/${selectedBrand.value.id}`, form, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'multipart/form-data'
        }
      })
	  toast.success('Brand updated!')
    }

    closeForm()
    getAllBrands(1)
  } catch (error) {
    console.error(error.response?.data || error)
    toast.error('Something went wrong!')
  }
}

// archive brand
async function handleArchiveBrand(id: number) {
  try {
    const result = await Swal.fire({
      	title: 'Archive Brand?',
		text: "This action will move the brand to archived status. You can restore it later if needed.",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Yes, archive it!',
		cancelButtonText: 'Cancel'
    })

    if (result.isConfirmed) {
      const response = await api.get(`/management/brands/${id}/archive`)
      const data = response.data

      const idx = brands.value.findIndex(b => b.id === id)
      if (idx !== -1) {
        brands.value[idx].brandStatus = data.data.brandStatus
      }

      toast.success('Brand archived successfully')
    }
  } catch (error) {
    console.error(error)
    toast.error('Error archiving brand')
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
      title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Brand?`,
      text: `Are you sure you want to ${actionText} this brand?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: `Yes, ${actionText} it!`,
      cancelButtonText: 'Cancel'
    })

    if (result.isConfirmed) {
      const response = await api.get(`/management/brands/${id}/toggle`)
      const data = response.data

      const idx = brands.value.findIndex(b => b.id === id)
      if (idx !== -1) {
        brands.value[idx].brandStatus = data.data.brandStatus
      }

      toast.success(`Brand ${actionText}d successfully`)
    }
  } catch (error) {
    console.error(error)
    toast.error(`Error trying to ${actionText} brand`)
  }
}

// delete brand
async function handleDeleteBrand(id: number) {
  try {
    const result = await Swal.fire({
      title: 'Delete Brand?',
      text: "This action cannot be undone.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    })

    if (result.isConfirmed) {
      await api.delete(`/management/brands/${id}`)
      brands.value = brands.value.filter(b => b.id !== id)
      toast.success('Brand deleted successfully')
    }
  } catch (error) {
    console.error(error)
    toast.error('Error deleting brand')
  }
}

function closeForm() {
  showCreateForm.value = false
  showEditForm.value = false
  selectedBrand.value = null
  formData.value = {
    brandName: '',
    primaryEmail: '',
    secondaryEmail: '',
    website: '',
    address: '',
    contactPerson: '',
    contactNumber: '',
    filename: '',
    brandStatus: 0, // reset to Active
    manufacturerId: null,
    mue: 0,
    description: '',
    graftSizes: [{ size: '', area: 0, price: 0 }]
  }
}

// search & filter
const searchTerm = ref('')
const statusFilter = ref('all')
const filteredBrands = computed(() => {
  return brands.value.filter(b => {
    const matchesSearch =
      b.brandName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      b.primaryEmail.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'active' && b.brandStatus === 0) ||
      (statusFilter.value === 'inactive' && b.brandStatus === 1) ||
	  (statusFilter.value === 'archive' && b.brandStatus === 2)
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

async function downloadIVRForm(id: number) {
  try {
    const response = await axios.get(`/management/brands/${id}/download-ivr`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` },
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `ivr-form-${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error downloading IVR form:', error)
    toast.error('Failed to download IVR form')
  }
}

onMounted(() => {
  getAllManufacturers()
  getAllBrands(1)
})

watch(itemsPerPage, () => {
  getAllBrands(1)
})

watch(showFormModal, (val) => {
  if (!val) closeForm()
})

</script>