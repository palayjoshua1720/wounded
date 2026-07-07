<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Brand Management</h1>
			</div>
			<button @click="selectedBrand = null; showCreateForm = true"
				class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
				<PackagePlus class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" />
				New Brand
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col lg:flex-row gap-6">
				<div class="flex-1">
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
							class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
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
				@delete="handleDeleteBrand" />
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
		<BrandFormModal v-model="showFormModal" :is-edit="showEditForm" :form-data="formData"
			:manufacturers="manufacturers" @submit="handleFormModalSubmit" @cancel="handleFormModalCancel" />

		<!-- Show Brand Details -->
		<BrandDetailsModal v-model="showBrandDetailsModal" :brand="viewBrand" />
	</div>
</template>

<script setup lang="ts">
import { useToast } from "vue-toastification"
import Swal from "sweetalert2"
import { ref, computed, onMounted, watch } from 'vue'
import Pagination from '@/components/ui/Pagination.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import BaseEmptyState from '@/components/common/BaseEmptyState.vue'
import BrandCard from '@/components/brands/BrandCard.vue'
import BrandFormModal from '@/components/brands/modals/BrandFormModal.vue'
import BrandDetailsModal from '@/components/brands/modals/BrandDetailsModal.vue'
import { Package, PackagePlus, ChevronDown, Funnel, Search } from 'lucide-vue-next'
import api from '@/services/api'
import { formatCurrency, formatNumber } from '@/utils/currency'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const isStaff = computed(() => authStore.user?.user_role === 1)

const toast = useToast()

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
const itemsPerPage = ref(9)
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

// Handle form modal submit from BrandFormModal component
async function handleFormModalSubmit(data: { formData: any; logoFile: File | null; removeLogoFlag: boolean }) {
	try {
		const form = new FormData()

		form.append('brandName', data.formData.brandName)
		form.append('brandStatus', data.formData.brandStatus.toString())
		form.append('manufacturerId', (data.formData.manufacturerId ?? '').toString())
		form.append('mue', data.formData.mue ? data.formData.mue.toString() : '')
		form.append('description', data.formData.description || '')

		// === LOGO HANDLING ===
		if (data.logoFile) {
			form.append('logo', data.logoFile)
		} else if (data.removeLogoFlag) {
			form.append('remove_logo', '1')
		}

		// Graft sizes as JSON
		form.append('graftSizes', JSON.stringify(data.formData.graftSizes))

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

	} catch (error: any) {
		console.error(error.response?.data || error)
		toast.error(error.response?.data?.message || 'Something went wrong!')
	}
}

// Handle form modal cancel from BrandFormModal component
function handleFormModalCancel() {
	closeForm()
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

	} catch (error: any) {
		console.error(error.response?.data || error)
		toast.error(error.response?.data?.message || 'Something went wrong!')
	}
}

// archive/unarchive brand
async function handleArchiveBrand(id: number) {
	const brand = brands.value.find(b => b.id === id)
	if (!brand) return

	const isArchived = brand.brandStatus === 2
	const action = isArchived ? 'unarchive' : 'archive'
	const actionTitle = `${action.charAt(0).toUpperCase() + action.slice(1)} Brand`
	const text = isArchived
		? 'Are you sure you want to unarchive this brand? It will be restored to active.'
		: 'Are you sure you want to archive this brand? It will no longer appear in active or inactive lists.'

	try {
		const result = await Swal.fire({
			title: actionTitle,
			text: text,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: isArchived ? 'Yes, unarchive it!' : 'Yes, archive it!',
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			const endpoint = isArchived
				? `/management/brands/${id}/unarchive`
				: `/management/brands/${id}/archive`
			const response = await api.get(endpoint)
			const data = response.data

			const idx = brands.value.findIndex(b => b.id === id)
			if (idx !== -1) {
				brands.value[idx].brandStatus = data.data.brandStatus
			}

			toast.success(`Brand ${action}ed successfully`)
		}
	} catch (error) {
		console.error(error)
		toast.error(`Error ${action}ing brand`)
	}
}

// toggle status (active/inactive/activate)
async function handleToggleStatus(id: number, currentStatus: number) {
	let actionText = ''

	if (currentStatus === 0) actionText = 'deactivate'
	else actionText = 'activate'

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
async function handleDeleteBrand(brand: any) {
	try {
		const result = await Swal.fire({
			title: `Delete Brand?`,
			html: `
				Are you sure you want to delete 
				<span class="font-bold text-blue-600">
					${brand.brandName}
				</span>?
				<br />
				This action cannot be undone.
			`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Yes, delete it!',
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			await api.delete(`/management/brands/${brand.id}`)

			brands.value = brands.value.filter(b => b.id !== brand.id)

			toast.success(`${brand.brandName} deleted successfully`)
		}
	} catch (error) {
		console.error(error)
		toast.error(`Error deleting ${brand.brandName}`)
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
