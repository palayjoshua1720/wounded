<template>
	<div class="space-y-4 sm:space-y-6">
		<!-- Header -->
		<div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-3">
			<h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Brand Management</h1>
			<button @click="selectedBrand = null; showCreateForm = true"
				class="flex items-center justify-center w-full sm:w-auto px-4 sm:px-5 py-2.5 sm:py-3 text-sm sm:text-base bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group whitespace-nowrap">
				<PackagePlus class="w-4 h-4 mr-1.5 sm:mr-2 group-hover:scale-110 transition-transform" />
				New Brand
			</button>
		</div>

		<!-- Filters -->
		<div
			class="bg-white dark:bg-gray-800 p-4 lg:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<!-- Mobile: Stacked Layout -->
			<div class="lg:hidden space-y-3">
				<!-- Search and Filters Toggle Row -->
				<div class="flex flex-col sm:flex-row gap-2">
					<!-- Search Input -->
					<div class="relative flex-1">
						<Search
							class="absolute left-3 sm:left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 sm:h-5 sm:w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search Brand...."
							class="w-full pl-9 sm:pl-10 pr-10 py-2.5 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900 dark:text-white text-sm transition-all duration-200" />
					</div>

					<!-- Filters Toggle Button -->
					<button @click="showFilters = !showFilters"
						class="flex-shrink-0 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium h-[2.7rem] min-h-[2.5rem]">
						<SlidersHorizontal class="w-4 h-4" />
						Filters
						<span v-if="statusFilter !== 'all'" class="w-2 h-2 bg-green-500 rounded-full"></span>
					</button>
				</div>

				<!-- Filter Pills Row -->
				<div class="flex flex-wrap gap-2">
					<span v-if="statusFilter !== 'all'"
						class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg text-xs">
						{{ statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1) }}
						<button @click="statusFilter = 'all'" class="hover:text-green-900 ml-1">
							<X class="w-3 h-3" />
						</button>
					</span>
					<span v-if="itemsPerPage !== '9'"
						class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs">
						{{ itemsPerPage }} per page
						<button @click="itemsPerPage = '9'" class="hover:text-blue-900 ml-1">
							<X class="w-3 h-3" />
						</button>
					</span>
				</div>

				<!-- Expandable Filters -->
				<div v-if="showFilters" class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-700">
					<div>
						<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
						<div class="flex gap-2">
							<button @click="statusFilter = 'all'"
								:class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
									statusFilter === 'all' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
								All
							</button>
							<button @click="statusFilter = 'active'"
								:class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
									statusFilter === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
								Active
							</button>
							<button @click="statusFilter = 'inactive'"
								:class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
									statusFilter === 'inactive' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
								Inactive
							</button>
							<button @click="statusFilter = 'archive'"
								:class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
									statusFilter === 'archive' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
								Archived
							</button>
						</div>
					</div>
					<div>
						<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Items per
							page</label>
						<div class="flex gap-2">
							<button @click="itemsPerPage = '9'"
								:class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
									itemsPerPage === '9' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
								9
							</button>
							<button @click="itemsPerPage = '25'"
								:class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
									itemsPerPage === '25' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
								25
							</button>
							<button @click="itemsPerPage = '50'"
								:class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
									itemsPerPage === '50' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
								50
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Desktop: Original Layout -->
			<div class="hidden lg:flex flex-col lg:flex-row gap-6">
				<div class="flex-1 min-w-0">
					<div class="relative">
						<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search Brand...."
							class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
					</div>
				</div>
				<div class="flex flex-col sm:flex-row gap-4">
					<div class="relative">
						<Funnel
							class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400" />
						<select v-model="statusFilter"
							class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
							<option value="all">All Status</option>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
							<option value="archive">Archived</option>
						</select>
						<ChevronDown
							class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
					<div class="relative">
						<select v-model="itemsPerPage"
							class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
							<option value="9">9 per page</option>
							<option value="25">25 per page</option>
							<option value="50">50 per page</option>
						</select>
						<ChevronDown
							class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
				</div>
			</div>
		</div>

		<!-- Card View -->
		<ContentLoader v-if="tableLoader" />
		<div v-if="filteredBrands && filteredBrands.length > 0"
			class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
			<BrandCard v-for="brand in filteredBrands" :key="brand.id" :brand="brand" :is-staff="isStaff"
				@view="viewBrand = $event" @edit="editBrand" @toggle="handleToggleStatus" @archive="handleArchiveBrand"
				@unarchive="handleUnarchiveBrand" @delete="handleDeleteBrand" />
		</div>

		<div v-else-if="filteredBrands && filteredBrands.length === 0 && !tableLoader">
			<BaseEmptyState :icon="Package" :searching="isFiltering" empty-title="No brands yet"
				empty-description="Get started by creating a new brand." variant="dark" :bordered="false" />
		</div>

		<template v-if="!tableLoader">
			<div
				class="bg-white dark:bg-gray-800 mt-4 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
				<Pagination v-if="filteredBrands && filteredBrands.length > 0" :pagination="pagination"
					@update:page="getAllBrands" />
			</div>
		</template>

		<!-- Create/Edit Form Modal -->
		<BaseModal v-model="showFormModal" :title="showEditForm ? 'Edit Brand' : 'Add New Brand'"
			:class="{ 'pointer-events-none': isCropperOpen, 'opacity-0': isCropperOpen }">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<!-- Brand Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<Package class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Brand Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input v-model="formData.brandName" type="text" required placeholder="Brand Name"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
									focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>

						<!-- Status -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select v-model="formData.brandStatus" required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option :value="0">Active</option>
									<option :value="1">Inactive</option>
									<option :value="2">Archived</option>
								</select>
							</div>
						</div>

						<!-- Manufacturer -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Manufacturer<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select v-model="formData.manufacturerId" required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option v-for="opt in manufacturerOptions" :key="opt.value ?? 'none'"
										:value="opt.value">
										{{ opt.label }}
									</option>
								</select>
							</div>
						</div>

						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">MUE (MedicallyUnlikely
								Edits) <span class="text-red-500">*</span>
							</label>
							<div class="relative">
								<Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input v-model.number="formData.mue" type="number" min="1" required
									placeholder="Enter MUE value" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
								focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
							<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum units allowed per day per
								patient
							</p>
						</div>

						<!-- Logo Upload Section -->
						<div class="sm:col-span-2">
							<ImageUploadCrop v-model="selectedLogoFile" v-model:model-preview-url="formData.logoUrl"
								v-model:model-remove-flag="removeLogoFlag" label="Brand Logo" :show-label="true"
								:is-optional="true" existing-label="Current logo:" @cropper-opening="isCropperOpen = true"
								@cropper-closing="isCropperOpen = false" />
						</div>

						<div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Description
								(Optional)</label>
							<div class="relative">
								<Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<textarea v-model="formData.description" placeholder="Description" rows="3"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
									focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-vertical"></textarea>
							</div>
						</div>
					</div>
				</div>

				<!-- Graft Sizes -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<PencilRuler class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Graft Sizes (Optional)</h3>
					</div>
					<div class="space-y-3">
						<div v-for="(graftSize, index) in formData.graftSizes" :key="graftSize.id || index"
							:class="index > 0 ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : ''">
							<div class="flex items-center justify-between mb-2">
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">Size {{ index + 1
									}}</span>
								<button v-if="index > 0" type="button" @click="removeGraftSize(index)"
									class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
									<Trash2 class="w-4 h-4" />
								</button>
							</div>
							<div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Item
										No</label>
									<div class="relative">
										<Barcode class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model="graftSize.item_no" type="text" placeholder="ABC-0000"
											class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Size</label>
									<div class="relative">
										<RulerDimensionLine class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model="graftSize.size" type="text" placeholder="2cm x 2cm" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Area
										(cm²)</label>
									<div class="relative">
										<Diameter class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model.number="graftSize.area" type="number" placeholder="0" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Price</label>
									<div class="relative">
										<DollarSign class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model.number="graftSize.price" type="number" step="0.01" placeholder="0.00"
											class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Stock</label>
									<div class="relative">
										<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model.number="graftSize.stock" type="number" min="0" placeholder="0" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
							</div>
						</div>
						<button type="button" @click="addGraftSize"
							class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors w-full sm:w-auto">
							<Plus class="w-4 h-4 mr-2" />
							Add Size
						</button>
					</div>
				</div>
			</form>
			<template #actions>
				<div class="flex justify-end w-full p-5 gap-3">
					<button type="button" @click="closeForm" class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl 
					text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 
					transition-all duration-200">
						Cancel
					</button>

					<button type="submit" @click="handleSubmitForm" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white 
					rounded-xl hover:from-blue-700 hover:to-indigo-700 
					transition-all duration-200 shadow-md hover:shadow-lg">
						{{ showEditForm ? 'Update Brand' : 'Create Brand' }}
					</button>
				</div>
			</template>
		</BaseModal>

		<!-- Show Brand Details -->
		<BrandDetailsModal v-model="showBrandDetailsModal" :brand="viewBrand" />
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import Pagination from '@/components/ui/Pagination.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import BaseEmptyState from '@/components/common/BaseEmptyState.vue'
import BrandCard from '@/components/brands/BrandCard.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import ImageUploadCrop from '@/components/common/ImageUploadCrop.vue'
import BrandDetailsModal from '@/components/brands/modals/BrandDetailsModal.vue'
import {
	Package, PackagePlus, ChevronDown, Funnel, Search, SlidersHorizontal, X,
	CircleCheck, Factory, Hash, Globe, PencilRuler, Plus, Trash2, Barcode,
	RulerDimensionLine, Diameter, DollarSign
} from 'lucide-vue-next'
import api from '@/services/api'
import axios from 'axios'
import { formatCurrency, formatNumber } from '@/utils/currency'
import { useAuthStore } from '@/stores/auth'
import { confirmAction } from '@/services/alert'
import { useAppToast } from '@/composables/ui/useAppToast'

const authStore = useAuthStore()
const isStaff = computed(() => authStore.user?.user_role === 1)

const toast = useAppToast()

const isFiltering = computed(() => searchTerm.value.trim().length > 0 || statusFilter.value !== 'all')

// types
interface GraftSize {
	id?: number
	item_no?: string | null
	size: string
	area: number | null
	price: number | null
	graftStatus?: number
	stock?: number
}

interface SimpleManufacturer {
	id: number
	manufacturerName: string
}

interface Brand {
	id: number
	brandName: string
	brandStatus: number
	manufacturerId?: number
	manufacturerName?: string
	mue?: number | null
	logoUrl?: string | null
	description?: string
	productType?: string
	graftSizes: GraftSize[]
	createdAt: string
	updatedAt: string
}

const pagination = ref({ current_page: 1, last_page: 1, per_page: 9, total: 0 })
const itemsPerPage = ref<string>('9')
const showFilters = ref(false)
const brands = ref<Brand[]>([])
const manufacturers = ref<SimpleManufacturer[]>([])
const tableLoader = ref(false)

const selectedBrand = ref<Brand | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const viewBrand = ref<Brand | null>(null)
const graftSizesExpanded = ref(false)

const showBrandDetailsModal = computed({
	get: () => viewBrand.value !== null,
	set: (value: boolean) => {
		if (!value) {
			viewBrand.value = null
			graftSizesExpanded.value = false
		}
	},
})


const formData = ref({
	brandName: '',
	brandStatus: 0,
	manufacturerId: null as number | null,
	mue: null as number | null,
	logoUrl: '',
	description: '',
	graftSizes: [{ size: '', area: null, price: null, stock: 0, item_no: '' }] as GraftSize[]
})

const manufacturerOptions = computed(() => {
	const opts = manufacturers.value.map(m => ({ value: m.id, label: m.manufacturerName }))
	if (formData.value.manufacturerId === null) {
		return [{ value: null, label: 'Select a Manufacturer' }, ...opts]
	} else {
		return opts
	}
})

const getActiveSizes = (brand: Brand) => brand.graftSizes.filter(s => s.graftStatus === 0)

const getInactiveCount = (brand: Brand) => brand.graftSizes.filter(s => s.graftStatus !== 0).length

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
			brandStatus: b.brandStatus,
			manufacturerId: b.manufacturerId ?? null,
			manufacturerName: b.manufacturerName,
			mue: b.mue ?? null,
			logoUrl: b.logoUrl,
			description: b.description,
			graftSizes: (b.graftSizes || []).map((s: any) => ({
				id: s.id,
				item_no: s.item_no,
				size: s.size,
				area: s.area,
				price: s.price,
				stock: s.stock || 0,
				graftStatus: s.graftStatus || 0
			})),
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
		brandStatus: b.brandStatus ?? 0,
		manufacturerId: b.manufacturerId ?? null,
		mue: b.mue ?? null,
		logoUrl: b.logoUrl || '',
		description: b.description || '',
		graftSizes: b.graftSizes.length > 0
			? b.graftSizes.map((s: GraftSize) => ({
				id: s.id,
				item_no: s.item_no || '',
				size: s.size,
				area: s.area,
				price: s.price,
				stock: s.stock || 0,
				graftStatus: s.graftStatus || 0
			}))
			: [{ size: '', area: null, price: null, stock: 0, item_no: '' }]
	}
	showEditForm.value = true
}

function addGraftSize() {
	formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0 })
}

function removeGraftSize(index: number) {
	formData.value.graftSizes.splice(index, 1)
	if (formData.value.graftSizes.length === 0) {
		formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, item_no: '' })
	}
}

const brandValidationLabels: Record<string, string> = {
	brandName: 'Brand name',
	brandStatus: 'Status',
	manufacturerId: 'Manufacturer',
	mue: 'MUE',
	description: 'Description',
	graftSizes: 'Graft sizes',
	logo: 'Brand logo',
}

const graftSizeValidationLabels: Record<string, string> = {
	item_no: 'Item number',
	size: 'Size',
	area: 'Area',
	price: 'Price',
	stock: 'Stock',
	graftStatus: 'Status',
}

function getBrandValidationLabel(field: string) {
	const graftSizeMatch = field.match(/^graftSizes\.(\d+)\.(.+)$/)
	if (graftSizeMatch) {
		const rowNumber = Number(graftSizeMatch[1]) + 1
		const fieldLabel = graftSizeValidationLabels[graftSizeMatch[2]] || graftSizeMatch[2]
		return `Graft size ${rowNumber} ${fieldLabel}`
	}

	return brandValidationLabels[field] || field
		.replace(/([A-Z])/g, ' $1')
		.replace(/[_-]/g, ' ')
		.replace(/^./, character => character.toUpperCase())
}

function getBrandValidationMessage(field: string, message: string) {
	const normalizedMessage = message.toLowerCase()

	if (normalizedMessage.includes('required')) return 'This field is required.'
	if (field === 'manufacturerId' && (normalizedMessage.includes('exists') || normalizedMessage.includes('invalid'))) {
		return 'Select a valid manufacturer.'
	}
	if (field === 'brandStatus' && normalizedMessage.includes('invalid')) return 'Select a valid status.'
	if (field === 'mue' && normalizedMessage.includes('integer')) return 'Enter a whole number.'
	if (field === 'mue' && normalizedMessage.includes('at least')) return 'Enter a whole number of 1 or more.'
	if (['area', 'price'].some(part => field.endsWith(`.${part}`)) && normalizedMessage.includes('numeric')) {
		return 'Enter a valid number.'
	}
	if (['area', 'price', 'stock'].some(part => field.endsWith(`.${part}`)) && normalizedMessage.includes('min')) {
		return 'Enter a value of 0 or more.'
	}
	if (field.endsWith('.stock') && normalizedMessage.includes('integer')) return 'Enter a whole number.'
	if (field === 'logo' && normalizedMessage.includes('image')) return 'Upload a PNG or JPEG/JPG image.'
	if (field === 'logo' && normalizedMessage.includes('type')) return 'Upload a PNG or JPEG/JPG image.'
	if (field === 'logo' && (normalizedMessage.includes('greater than') || normalizedMessage.includes('too large'))) {
		return 'Use an image no larger than 2 MB.'
	}
	if (field === 'graftSizes' && normalizedMessage.includes('json')) return 'Enter valid graft size details.'

	const escapedField = field.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
	const fieldPattern = new RegExp(`^The ${escapedField}( field)? `, 'i')
	return message.replace(fieldPattern, '').replace(/^./, character => character.toUpperCase())
}

function showBrandRequestError(error: unknown) {
	if (axios.isAxiosError(error)) {
		const status = error.response?.status
		const data = error.response?.data

		if (status === 422 && data?.errors) {
			const messages = Object.entries(data.errors).flatMap(([field, errors]) => {
				const fieldMessages = Array.isArray(errors) ? errors : [errors]
				return fieldMessages.map(message => {
					const label = getBrandValidationLabel(field)
					const friendlyMessage = getBrandValidationMessage(field, String(message))
					return `- ${label}: ${friendlyMessage}`
				})
			})
			const validationMessage = [
				'Correct the following before saving:',
				...messages,
				'',
				'Please correct these items and try again.',
			].join('\n')
			toast.error(validationMessage, 'Please review the form')
			return
		}

		toast.error(data?.message || `Request failed with status code ${status}`)
		return
	}

	if (error instanceof Error) {
		toast.error(`Error: ${error.message}`)
		return
	}
	toast.error('Something went wrong')
}

function getBrandName(id: number) {
	return brands.value.find(brand => brand.id === id)?.brandName
}

function getBrandLabel(name?: string) {
	return name ? `Brand "${name}"` : 'The brand'
}

function escapeHtml(value: string) {
	return value.replace(/[&<>'"]/g, character => ({
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		"'": '&#39;',
		'"': '&quot;',
	}[character] || character))
}



// Legacy submit handler for backwards compatibility
async function handleSubmitForm() {
	try {
		const form = new FormData()

		form.append('brandName', formData.value.brandName)
		form.append('brandStatus', formData.value.brandStatus.toString())
		form.append('manufacturerId', (formData.value.manufacturerId ?? '').toString())
		form.append('mue', formData.value.mue ? formData.value.mue.toString() : '')
		form.append('description', formData.value.description || '')

		// === LOGO HANDLING – FIXED ORDER (100% reliable) ===
		if (selectedLogoFile.value) {
			form.append('logo', selectedLogoFile.value)
		} else if (removeLogoFlag.value) {
			form.append('remove_logo', '1')
		}

		// Graft sizes as JSON
		form.append('graftSizes', JSON.stringify(formData.value.graftSizes))

		if (showCreateForm.value) {
			await api.post('/management/brands', form, {
				headers: {
					Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
					'Content-Type': 'multipart/form-data'
				}
			})
			toast.success(`${getBrandLabel(formData.value.brandName)} was created successfully.`)
		} else if (showEditForm.value && selectedBrand.value) {
			await api.post(`/management/brands/${selectedBrand.value.id}`, form, {
				headers: {
					Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
					'Content-Type': 'multipart/form-data'
				}
			})
			toast.success(`${getBrandLabel(formData.value.brandName)} details were updated successfully.`)
		}

		closeForm()
		getAllBrands(1)

	} catch (error: any) {
		console.error(error.response?.data || error)
		showBrandRequestError(error)
	}
}

// archive brand
async function handleArchiveBrand(id: number) {
	const brand = brands.value.find(b => b.id === id)
	if (!brand) return

	try {
		const result = await confirmAction({
			title: 'Archive Brand?',
			text: 'This action will move the brand to archived status. You can restore it later if needed.',
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

			toast.success(`${getBrandLabel(getBrandName(id))} was archived successfully and can be restored later.`)
		}
	} catch (error) {
		console.error(error)
		toast.error('Error archiving brand')
	}
}

// unarchive brand
async function handleUnarchiveBrand(id: number) {
	const brand = brands.value.find(b => b.id === id)
	if (!brand) return

	try {
		const result = await confirmAction({
			title: 'Unarchive Brand?',
			text: 'This action will restore the brand from archived status.',
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#6b7280',
			confirmButtonText: 'Yes, unarchive it!',
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			const response = await api.get(`/management/brands/${id}/unarchive`)
			const data = response.data

			const idx = brands.value.findIndex(b => b.id === id)
			if (idx !== -1) {
				brands.value[idx].brandStatus = data.data.brandStatus
			}

			toast.success(`${getBrandLabel(getBrandName(id))} was successfully restored from archived status.`)
		}
	} catch (error) {
		console.error(error)
		toast.error('Error unarchiving brand')
	}
}

// toggle status (active/inactive/activate)
async function handleToggleStatus(id: number, currentStatus: number) {
	let actionText = ''

	if (currentStatus === 0) actionText = 'deactivate'
	else actionText = 'activate'

	try {
		const result = await confirmAction({
			title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Brand?`,
			text: `Are you sure you want to ${actionText} this brand?`,
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

			toast.success(`${getBrandLabel(getBrandName(id))} was ${actionText}d successfully.`)
		}
	} catch (error) {
		console.error(error)
		toast.error(`Error trying to ${actionText} brand`)
	}
}

// delete brand
async function handleDeleteBrand(brand: any) {
	try {
		const result = await confirmAction({
			title: 'Delete Brand?',
			html: `
				Are you sure you want to delete <b>${escapeHtml(brand.brandName)}</b>?<br>
				<span style="color:#d33;">This action cannot be undone!</span>
			`,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Yes, delete it!',
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			await api.delete(`/management/brands/${brand.id}`)

			brands.value = brands.value.filter(b => b.id !== brand.id)

			toast.success(`${getBrandLabel(brand.brandName)} was permanently deleted.`)
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
		brandStatus: 0,
		manufacturerId: null,
		mue: null,
		logoUrl: '',
		description: '',
		graftSizes: [{ size: '', area: null, price: null, stock: 0, item_no: '' }]
	}
	selectedLogoFile.value = null
	removeLogoFlag.value = false
	isCropperOpen.value = false
}

// search & filter
const searchTerm = ref('')
const statusFilter = ref('all')
const filteredBrands = computed(() => {
	return brands.value.filter(b => {
		const matchesSearch =
			b.brandName.toLowerCase().includes(searchTerm.value.toLowerCase())
		const matchesStatus =
			statusFilter.value === 'all' ||
			(statusFilter.value === 'active' && b.brandStatus === 0) ||
			(statusFilter.value === 'inactive' && b.brandStatus === 1) ||
			(statusFilter.value === 'archive' && b.brandStatus === 2)
		return matchesSearch && matchesStatus
	})
})

// Logo file handling (managed by ImageUploadCrop component)
const selectedLogoFile = ref<File | null>(null)
const removeLogoFlag = ref(false)
const isCropperOpen = ref(false)

// modal binding
const showFormModal = computed({
	get: () => showCreateForm.value || showEditForm.value,
	set: (val: boolean) => { if (!val) { showCreateForm.value = false; showEditForm.value = false } }
})

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
