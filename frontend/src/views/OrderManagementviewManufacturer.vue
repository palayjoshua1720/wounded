<template>
	<div class="space-y-6">
		<!-- Header and Create Order -->
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
			<div class="space-y-2">
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Order Management</h1>
				<p class="text-gray-600 dark:text-gray-400 max-w-2xl">View, organize, and track every order in one place.</p>
			</div>
		</div>

		<!-- Filters -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col lg:flex-row gap-6">
				<div class="flex-1">
					<div class="relative">
						<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search Order..."
							class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
					</div>
				</div>
				<div class="flex flex-col sm:flex-row gap-4">
					<div class="relative">
						<Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
						<select
						v-model="statusFilter"
						class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
						>
							<option value="all">All Status</option>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
						</select>
						<ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
				</div>
				<div class="relative">
					<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
					<select
						id="per-page"
						v-model="itemsPerPage"
						class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
					>
						<option value="10">10</option>
						<option value="25">25</option>
						<option value="50">50</option>
					</select>
					<ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
				</div>
			</div>
		</div>

		<!-- Orders Table -->
		<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
					<thead class="bg-gray-50 dark:bg-gray-700">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order ID</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Clinic</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
						<TableLoader v-if="tableLoader" :colspan="7" />
						<template v-else>
							<tr
							v-for="order in filteredOrders"
							:key="order.order_id"
							class="hover:bg-gray-50 dark:hover:bg-gray-700">
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm font-medium text-gray-900 dark:text-white">#{{ order.order_code }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-gray-900 dark:text-white">{{ order.clinic?.clinic_name }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-gray-900 dark:text-white">{{ order.patient?.patient_name || 'Not specified' }}</div>
								</td>
								<td class="px-6 py-4">
									<div class="text-sm text-gray-900 dark:text-white">
										<div v-for="(item, idx) in order.items" :key="idx" class="mb-1">
											{{ getBrandName(item.brandId) }} - {{ getSizeName(item.graft_id) }} × {{ item.quantity }}
										</div>
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-gray-900 dark:text-white">{{ formatDate(order.ordered_at) }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span :class="['inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(order.order_status)]">
										<component :is="getStatusIcon(order.order_status)" class="w-4 h-4" />
										<span class="capitalize">{{ order.order_status }}</span>
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
									<div class="flex items-center space-x-2">
										<button @click="showOrderDetails(order)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
											<Eye class="w-4 h-4" />
										</button>
										<div class="inline-flex space-x-1">
											<button v-if="order.order_status === 'submitted'" @click="updateOrderStatusNew(order, 'acknowledged')" class="px-2 py-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded hover:bg-purple-200 dark:hover:bg-purple-900/30">Acknowledge?</button>
											<button v-if="order.order_status === 'acknowledged'" @click="updateOrderStatusNew(order, 'shipped')" class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/30">Ready to Ship?</button>
											<button v-if="order.order_status === 'shipped'" @click="updateOrderStatusNew(order, 'delivered')" class="px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/30">Ready to Deliver?</button>
										</div>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>

			<div v-if="filteredOrders.length === 0 && !tableLoader" class="text-center py-12">
				<div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <ShoppingCart class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No orders found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to find
                    what you're looking for.</p>
			</div>

			<template v-if="!tableLoader">
				<Pagination :pagination="pagination" @update:page="getAllOrders" />
			</template>
		</div>

		<!-- View Order Modal -->
		<BaseModal v-model="showOrderModal" title="Order Details">
			<template v-if="selectedOrder">
				<div
					class="flex items-center bg-gradient-to-r from-blue-50 to-indigo-50 
					dark:from-blue-900/20 dark:to-indigo-900/20 
					p-4 rounded-xl border border-blue-100 dark:border-blue-800 shadow-sm"
				>
					<div class="p-3 bg-blue-600 text-white rounded-lg shadow-md mr-3">
						<Package class="w-6 h-6" />
					</div>

					<div class="flex-1 flex flex-col">
						<p class="text-sm text-gray-700 dark:text-gray-300">
							Code:
							<span class="font-semibold text-blue-700 dark:text-blue-300">
								{{ selectedOrder.order_code }}
							</span>
						</p>

						<span
							:class="[
								'mt-1 w-fit inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap',
								getStatusColor(selectedOrder.order_status),
							]"
						>
							<CircleCheck class="w-4 h-4" />
							{{ selectedOrder.order_status.charAt(0).toUpperCase() + selectedOrder.order_status.slice(1) }}
						</span>
					</div>
				</div>

				<div class="space-y-6 p-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<Box class="w-5 h-5 text-blue-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.clinic?.clinic_name }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<CircleUser class="w-5 h-5 text-green-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clinician</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.clinician?.first_name }} {{ selectedOrder.clinician?.last_name }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<CircleUser class="w-5 h-5 text-purple-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Patient</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.patient?.patient_name || 'Not specified' }}</p>
								</div>
							</div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<Calendar1 class="w-5 h-5 text-orange-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Date</p>
									<p class="text-gray-900 dark:text-white">{{ formatDate(selectedOrder.ordered_at) }}</p>
								</div>
							</div>
							<div v-if="selectedOrder.tracking_num" class="flex items-center space-x-3">
								<Truck class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Number</p>
									<p class="text-gray-900 dark:text-white font-mono">{{ selectedOrder.tracking_num }}</p>
								</div>
							</div>
						</div>
					</div>
					<div>
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Order Items</h3>
						<div class="overflow-x-auto">
							<table class="w-full">
								<thead class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Brand</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Size</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ASP</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
									</tr>
								</thead>
								<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
									<tr v-for="(item, idx) in selectedOrder.items" :key="idx">
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getBrandName(item.brandId) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getSizeName(item.graft_id) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ item.quantity }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${{ item.asp.toFixed(2) }}</td>
										<td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">${{ (item.asp * item.quantity).toFixed(2) }}</td>
									</tr>
								</tbody>
								<tfoot class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white text-right">Total Amount:</td>
										<td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">${{ selectedOrder.items.reduce((sum, item) => sum + (item.asp * item.quantity), 0).toFixed(2) }}</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<div v-if="selectedOrder.notes">
						<div class="flex items-center space-x-2 mb-2">
							<FileTextIcon class="w-5 h-5 text-gray-600" />
							<h3 class="text-lg font-medium text-gray-900 dark:text-white">Notes</h3>
						</div>
						<div class="bg-gray-50 dark:bg-gray-900/20 rounded-lg p-4">
							<p class="text-gray-700 dark:text-gray-200">{{ selectedOrder.notes }}</p>
						</div>
					</div>

					
					<div class="border-t border-gray-200 pt-6">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Update Status</h3>
						<div class="flex space-x-3">
							<button v-if="selectedOrder.order_status === 'submitted'" @click="updateOrderStatusNew(selectedOrder.order_id, 'acknowledged')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Mark Acknowledged</button>
							<button v-if="selectedOrder.order_status === 'acknowledged'" @click="updateOrderStatusNew(selectedOrder.order_id, 'shipped')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Mark Shipped</button>
							<button v-if="selectedOrder.order_status === 'shipped'" @click="updateOrderStatusNew(selectedOrder.order_id, 'delivered')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Mark Delivered</button>
							<span v-if="selectedOrder.order_status === 'delivered'" class="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">Delivered</span>
						</div>
					</div>
				</div>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '../components/ui/Pagination.vue'
import TableLoader from '../components/ui/TableLoader.vue'
import {
    Search, Funnel, Eye, CircleCheck,
	Truck, Box, CircleUser, Calendar1,
	FileTextIcon, ShoppingCart, ChevronDown,
	Package, 
} from 'lucide-vue-next';
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface Order {
	order_id: number;
	order_code: string
	ordered_at: string;
	followup_last_sent_at: string
	order_status: OrderStatus;
	notes?: string;
	items: OrderItem[];
	tracking_num?: string;

	clinic: Clinic
	clinician: Clinician
	patient: PatientInfo
	// brand: Brand
}

interface Clinic {
	clinic_id: number
	clinic_code: string
	clinic_name: string
	clinicians: Clinician[]
}

interface Clinician {
	id: number
	first_name: string
	middle_name: string
	last_name: string
}

interface PatientInfo {
	patient_id: number
	patient_name: string
	clinic_id?: number
	ivrs: IVR[]
	user_id: User[]
}

interface Brand {
	brand_id: string
	description: string
	brand_name: string
	mue: number
	manufacturer?: {
		manufacturer_id: string
		manufacturer_name: string
	}
}

interface GraftSize {
	graft_size_id: number
	size: string
	area: string
	price: number
	graft_status: number
	brand: Brand
}

interface IVR {
	ivr_id: number
	eligibility_status: number
}

interface User {
	id: number
	first_name: string
	last_name: string
}

type OrderItem = {
	id: string;
	brandId: string;
	productType: 0 | 1
	sizeId: string;
	graft_id: number;
	quantity: number;
	asp: number;
	totalAsp?: number;
	deviceType?: string;
}

// 0-submitted | 1-acknowledged | 2-shipped | 3-delivered | 4-cancelled
type OrderStatus = 'submitted' | 'acknowledged' | 'shipped' | 'delivered' | 'cancelled'

const orderStatusMap: Record<OrderStatus, number> = {
	submitted: 0,
	acknowledged: 1,
	shipped: 2,
	delivered: 3,
	cancelled: 4
}

const tableLoader = ref(false);
const orders = ref<Order[]>([])
const clinics = ref<Clinic[]>([])
const patients = ref<PatientInfo[]>([])
const brands = ref<Brand[]>([])
const graftSizes = ref<GraftSize[]>([])
const itemsPerPage = ref(10)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})


const searchTerm = ref('')
const statusFilter = ref('all')

const selectedOrder = ref<Order | null>(null)
const showOrderModal = ref(false)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showFormModal = ref(false)
const selectedOrderForEdit = ref<Order | null>(null)

const formData = ref({
	clinicId: '',
	clinicianId: '',
	patientId: '',
	brandId: '',
	sizeId: '',
	dateOfOrder: '',
	status: 'submitted' as const,
	notes: '',
	items: [
		{ 
			id: Date.now().toString(), 
			brandId: '', 
			productType: 0 as const,
			sizeId: '',
			graft_id: 0,
			quantity: 1, 
			asp: 0, 
			totalAsp: 0,
			deviceType: '' 
		}
	],
	manufacturerId: '',
	trackingNumber: ''
})

function getBrandName(brandId: string) {
	const brand = brands.value.find(b => b.brand_id == brandId)
	return brand ? brand.brand_name : `Brand ${brandId}`
}

function getSizeName(graft_size: string | number) {
	if (!graft_size) return "Unknown size"
	const id = Number(graft_size)

	const graftSize = graftSizes.value.find(g => g.graft_size_id == id)
	return graftSize?.size ?? `Size ${graft_size}`
}

function resetCreateForm() {
	formData.value = {
		clinicId: '',
		clinicianId: '',
		patientId: '',
		brandId: '',
		sizeId: '',
		dateOfOrder: '',
		status: 'submitted',
		notes: '',
		items: [{ 
			id: Date.now().toString(), 
			brandId: '', 
			productType: 0 as const, 
			sizeId: '', 
			graft_id: 0,
			quantity: 1, 
			asp: 0, 
			totalAsp: 0, 
			deviceType: '' 
		}],
		manufacturerId: '',
		trackingNumber: ''
	}
}

function closeForm() {
	showFormModal.value = false
	showCreateForm.value = false
	showEditForm.value = false
	selectedOrderForEdit.value = null
	resetCreateForm()
}

function showOrderDetails(order: Order) {
	selectedOrder.value = order
	showOrderModal.value = true
}

const filteredOrders = computed(() => {
	const term = searchTerm.value.trim().toLowerCase();

	return (orders.value ?? []).filter(order => {
		const orderCode = order.order_code?.toLowerCase() || '';
		const clinicCode = order.clinic?.clinic_code?.toLowerCase() || '';
		const clinicName = order.clinic?.clinic_name?.toLowerCase() || '';
		const patientName = order.patient?.patient_name?.toLowerCase() || '';
		const status = order.order_status?.toLowerCase() || '';

		const matchesSearch =
			term === '' ||
			orderCode.includes(term) ||
			clinicCode.includes(term) ||
			clinicName.includes(term) ||
			patientName.includes(term);

		const matchesStatus =
			statusFilter.value === 'all' ||
			order.order_status === statusFilter.value;

		return matchesSearch && matchesStatus;
	});
});

const getStatusColor = (status: string) => {
	switch (status) {
		case 'delivered': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
		case 'shipped': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
		case 'acknowledged': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
		case 'submitted': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
		case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
		default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
	}
}
const getStatusIcon = (status: string) => {
	switch (status) {
		case 'delivered': return CircleCheck
		case 'shipped': return Truck
		case 'acknowledged': return Box
		default: return null
	}
}
const formatDate = (dateStr: string) => {
	if (!dateStr) return null;
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	})
}

async function getAllOrders(page = 1)
{
	tableLoader.value = true;
    try {
        const { data } = await api.get(`/management/manufacturer/order/getmanufacturerorders?page=${page}&per_page=${itemsPerPage.value}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

		const rows = Array.isArray(data?.order_data) ? data.order_data : []

		orders.value = rows.map((o: any) => {
			let rawItems = o.items
			if (!Array.isArray(rawItems) && typeof rawItems === 'string') {
				try { rawItems = JSON.parse(rawItems) } catch { rawItems = [] }
			}
			const items: OrderItem[] = Array.isArray(rawItems) ? rawItems.map((it: any, idx: number) => ({
				id: String(it.id ?? idx),
				brandId: String(it.brandId ?? it.brand_id ?? ''),
				productType: (it.productType ?? it.product_type ?? 'graft'),
				sizeId: String(it.sizeId ?? it.size_id ?? it.graft_id ?? ''),
				graft_id: Number(it.graft_id ?? 0),
				quantity: Number(it.quantity ?? 0),
				asp: Number(it.asp ?? 0),
				deviceType: it.deviceType ?? it.device_type ?? ''
			})) : []

			return {
				order_id: Number(o.order_id ?? 0),
				order_code: String(o.order_code ?? ''),
				ordered_at: String(o.ordered_at ?? o.created_at ?? ''),
				followup_last_sent_at: String(o.followup_last_sent_at ?? o.followup_last_sent_at ?? ''),
				order_status: (['submitted','acknowledged','shipped','delivered','cancelled'][Number(o.order_status ?? 0)] ?? 'submitted') as OrderStatus,
				notes: o.notes ?? '',
				items,
				tracking_num: o.tracking_num ?? '',
				clinic: o.clinic,
				clinician: o.clinician,
				patient: o.patient,
				brand: o.brand
			} as Order
		})

		pagination.value = {
            current_page: Number(data?.current_page ?? 1),
            last_page: Number(data?.last_page ?? 1),
            per_page: Number(data?.per_page ?? itemsPerPage.value),
            total: Number(data?.total ?? rows.length),
        }
    } catch (error: any) {
		orders.value = []
		const backendMessage = error.response?.data?.message || 'Something went wrong';
    	toast.error(backendMessage);
    } finally {
        tableLoader.value = false
    }
}

async function getAllClinics()
{
	try {
		const { data } = await api.get(`/management/order/getclinics`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
		clinics.value = data.clinic_data || []
	} catch (error) {
		clinics.value = []
    } finally {
        tableLoader.value = false
    }
}

async function getAllPatients()
{
	try {
		const { data } = await api.get(`/management/order/users/getpatients`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
		patients.value = data.patient_data || []
	} catch (error) {
		patients.value = []
    } finally {
        tableLoader.value = false
    }
}

async function getAllGraftSizes() {
	try {
		const { data } = await api.get(`/management/order/getgraftsizes`, {
			headers: {
				Authorization: `Bearer ${localStorage.getItem('auth_token')}`
			}
		})
		graftSizes.value = data.graft_size_data || []

		const brandMap = new Map()
		graftSizes.value.forEach(gs => {
			const brand = gs.brand
			if (brand?.brand_id && !brandMap.has(brand.brand_id)) {
				brandMap.set(brand.brand_id, brand)
			}
		})
		brands.value = Array.from(brandMap.values())
	} catch (error) {
		console.error('Error loading graft sizes:', error)
		graftSizes.value = []
	} finally {
		tableLoader.value = false
	}
}

async function updateOrderStatusNew(orderOrId: Order | number, newStatus: OrderStatus) {
	const order_id = typeof orderOrId === 'number' ? orderOrId : orderOrId.order_id;

	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This action cannot be undone.",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Yes, confirm!"
	});

	if (!result.isConfirmed) return;

	try {
		const statusNumber = orderStatusMap[newStatus];

		await api.put(`/management/order/update/${order_id}/updateorderstatus`, {
			order_status: statusNumber
		});

		if (typeof orderOrId !== 'number') {
			const orderIndex = orders.value.findIndex(o => o.order_id === order_id);
			if (orderIndex !== -1) {
				orders.value[orderIndex].order_status = newStatus;
			}
			if (selectedOrder.value && selectedOrder.value.order_id === order_id) {
				selectedOrder.value.order_status = newStatus;
			}
		}

		toast.success('Order details Updated Successfully!')
		await getAllOrders(1)
		closeForm()
	} catch (error) {
		console.error(error);
		toast.error('Failed to update order status.')
	}
}

onMounted(async () => {
    getAllOrders(1)
	getAllClinics()
	getAllPatients()
	await getAllGraftSizes()
})

watch(itemsPerPage, () => {
    getAllOrders(1)
})

watch(() => formData.value.items, (items) => {
	items.forEach(item => {
		item.totalAsp = item.asp * item.quantity
	})
}, { deep: true })
</script>