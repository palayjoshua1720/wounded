<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clinic Management</h1>
				<p class="text-gray-600 dark:text-gray-400">View and update clinic information with ease.</p>
			</div>

			<button
				@click="
					selectedUser = null; 
					showCreateForm = true
				"
				class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
			>
				<HousePlus class="w-4 h-4 mr-2" />
				Add Clinic
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
						placeholder="Search Clinics..."
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

		<!-- Tabs for switching views -->
		<!-- <div class="flex space-x-2 mb-4">
			<button
				:class="[
				'px-4 py-2 rounded-t-lg font-medium focus:outline-none',
				activeTab === 'table'
					? 'bg-blue-600 text-white'
					: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
				]"
				@click="activeTab = 'table'"
			>
				Table
			</button>
			<button
				:class="[
				'px-4 py-2 rounded-t-lg font-medium focus:outline-none',
				activeTab === 'card'
					? 'bg-blue-600 text-white'
					: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
				]"
				@click="activeTab = 'card'"
			>
				Card
			</button>
		</div> -->

		<!-- Table View -->
		<!-- <div class="bg-white dark:bg-gray-800 rounded shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
					<thead class="bg-gray-50 dark:bg-gray-700">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								User
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Contact Persion
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Status
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Phone Number
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <TableLoader v-if="tableLoader" :colspan="7" />
						<template v-else>
							<tr
							v-for="user in filteredUsers"
							:key="user.id"
							class="hover:bg-gray-50 dark:hover:bg-gray-700"
							>
								<td class="px-6 py-4 whitespace-nowrap">
									<div>
										<div class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
										<div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span class="text-sm text-gray-500 dark:text-gray-400">
										{{ user.contactPerson }}
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span
										v-if="userStatus[user.isActive]"
										:class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', userStatus[user.isActive].classes]"
									>
										{{ userStatus[user.isActive].label }}
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ user.phone ? user.phone : 'N/A' }}
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
									<button
									@click="selectedUser = user"
									class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
									title="View Details"
									>
										<Eye class="w-5 h-5" />
									</button>
									<button
									@click="editUser(user)"
									class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
									title="Edit User"
									>
										<SquarePen class="w-5 h-5" />
									</button>
									<button
									@click="handleToggleStatus(user.id)"
									:class="user.isActive ? 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300' : 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'"
									:title="user.isActive ? 'Activate' : 'Deactivate'"
									>
										<component :is="user.isActive ? CircleCheck : CircleX" class="w-5 h-5" />
									</button>
									<button
									@click="handleDeleteUser(user.id)"
									class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
									title="Delete User"
									>
										<Trash2 class="w-5 h-5" />
									</button>
								</td>
							</tr>
							<tr v-if="filteredUsers.length === 0 && !tableLoader">
								<td colspan="7" class="text-center text-gray-400 py-6">No clinics found.</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
		</div> -->

		<!-- Card View -->
		<ContentLoader v-if="tableLoader"/>
		<div v-if="filteredUsers && filteredUsers.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
			<div
				v-for="user in filteredUsers"
				:key="user.id"
				class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow"
			>
				<div class="flex items-start justify-between mb-4">
					<div>
						<div class="flex items-center gap-3">
							<!-- Icon -->
							<div class="p-2 bg-green-100 rounded-lg">
								<Hospital class="w-5 h-5 text-green-600" />
							</div>

							<!-- Text & Status in column -->
							<div class="flex flex-col">
								<h3 class="font-semibold text-gray-900 dark:text-white">
								{{ user.name }}
								</h3>
								<span
								:class="[
									'inline-flex px-2 py-1 text-xs rounded-full w-fit',
									user.isActive === 1
									? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
									: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
								]"
								>
								{{ userStatus[user.isActive]?.label || 'Unknown' }}
								</span>
							</div>
						</div>

					</div>
					<div class="flex items-center space-x-2">
						<button @click="selectedUser = user" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
							<Eye class="w-5 h-4" />
						</button>
						<button @click="editUser(user)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
							<SquarePen class="w-4 h-4" />
						</button>
						<button @click="handleToggleStatus(user.id)" :class="user.isActive ? 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300' : 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'" :title="user.isActive ? 'Activate' : 'Deactivate'">
							<component :is="user.isActive ? CircleCheck : CircleX" class="w-4 h-4" />
						</button>
						<button @click="handleDeleteUser(user.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
							<Trash2 class="w-4 h-4" />
						</button>
					</div>
				</div>
				<div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
					<div class="flex items-center gap-2">
						<MapPin class="w-4 h-4" />
						<span>{{ user.address }}</span>
					</div>
					<div class="flex items-center gap-2">
						<Mail class="w-4 h-4" />
						<span>{{ user.email }}</span>
					</div>
					<div class="flex items-center gap-2">
						<Phone class="w-4 h-4" />
						<span>{{ user.phone || 'N/A' }}</span>
					</div>
					<div class="flex items-center gap-2">
						<CalendarPlus class="w-4 h-4" />
						<span>{{ formatDate(user.createdAt) }}</span>
					</div>
					<div v-if="user.clinicId" class="flex items-center gap-2">
						<IdCardLanyard class="w-4 h-4" />
						<span>{{ user.clinicId }}</span>
					</div>
					<div v-if="user.clinicPubId" class="flex items-center gap-2">
						<IdCard class="w-4 h-4" />
						<span>{{ user.clinicPubId }}</span>
					</div>
					<hr>
					<div class="flex items-center gap-2">
						<strong>Contact Person:</strong>
						<span>{{ user.contactPerson }}</span>
					</div>
				</div>
			</div>
		</div>

		<div v-else-if="filteredUsers && filteredUsers.length === 0 && !tableLoader" class="">
			<div class="flex flex-col items-center justify-center gap-2 text-center">
				<Hospital class="w-10 h-10 mb-1 text-gray-700" />
				<span class="text-gray-600 dark:text-gray-300">No clinics found.</span>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination v-if="filteredUsers && filteredUsers.length > 0" :pagination="pagination" @update:page="getAllClinics" />
		</template>

		<!-- User Details Modal -->
		<BaseModal v-model="showUserDetailsModal" title="User Details">
			<template v-if="selectedUser">
				<div class="space-y-4">
					<div class="grid grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
							<p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedUser.name }}</p>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
							<p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedUser.email }}</p>
						</div>
					</div>

					<div class="grid grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Person</label>
							<span :class="['inline-flex items-center py-0.5 rounded-full text-xs font-medium']">
								{{ selectedUser.contactPerson }}
							</span>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
							<span
                                v-if="userStatus[selectedUser.isActive]"
                                :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', userStatus[selectedUser.isActive].classes]"
                            >
                                {{ userStatus[selectedUser.isActive].label }}
                            </span>
						</div>
					</div>

					<div class="grid grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
							<p class="mt-1 text-sm text-gray-900 dark:text-white">
								{{ selectedUser.phone ? selectedUser.phone : 'N/A' }}
							</p>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created</label>
							<p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedUser.createdAt) }}</p>
						</div>
					</div>

					<div v-if="selectedUser.clinicId">
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic ID</label>
						<p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedUser.clinicId }}</p>
					</div>
				</div>
			</template>
		</BaseModal>

		<!-- Create/Edit User Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add new Clinic' : 'Edit Clinic'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name<span class="text-red-500">*</span></label>
						<input
						v-model="formData.name"
						type="text"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email<span class="text-red-500">*</span></label>
						<input
						v-model="formData.email"
						type="email"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
				</div>

				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Person<span class="text-red-500">*</span></label>
						<input
						v-model="formData.contactPerson"
						type="text"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status<span class="text-red-500">*</span></label>
						<select
						v-model="formData.isActive"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option :value="true">Active</option>
							<option :value="false">Inactive</option>
						</select>
					</div>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic ID (for clinicians/staff)</label>
					<input
						v-model="formData.clinicId"
						type="text"
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						placeholder="Optional - only for clinic users"
					/>
				</div>

				<div v-if="showCreateForm">
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password<span class="text-red-500">*</span></label>
					<input
						v-model="formData.password"
						type="password"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						placeholder="Enter password"
					/>
				</div>

				<div class="flex justify-end space-x-3 pt-4">
					<button
						type="button"
						@click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
					>
						Cancel
					</button>
					<button
						type="submit"
						class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
					>
						{{ showCreateForm ? 'Create User' : 'Update User' }}
					</button>
				</div>
			</form>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
// import TableLoader from '@/components/ui/TableLoader.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import {
    Search,
    Funnel,
    Eye,
    SquarePen,
    CircleCheck,
    CircleX,
    Trash2,
	Hospital,
	MapPin,
	Phone,
	Mail,
	CalendarPlus,
	IdCard,
	IdCardLanyard,
	HousePlus
} from 'lucide-vue-next';
import api from '@/services/api'

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
}

// const activeTab = ref<'table' | 'card'>('table')

// Pagination
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})
const itemsPerPage = ref(10)
const users = ref<User[]>([])
const tableLoader = ref(false);
const userStatus: Record<number, { label: string; classes: string }> = {
	0: { label: 'Active', classes: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
	1: { label: 'Inactive', classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' },
	2: { label: 'Archived', classes: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400' },
}

const searchTerm = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')
const selectedUser = ref<User | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)

const formData = ref({
	name: '',
	email: '',
	contactPerson: '' as User['contactPerson'],
	isActive: true,
	clinicId: '',
	clinicPubId: '',
	password: '',
	phone: '',
	address: ''
})

function handleToggleStatus(userId: string) {
	const userIndex = users.value.findIndex(user => user.id === userId)
	if (userIndex !== -1) {
		users.value[userIndex].isActive = users.value[userIndex].isActive === 1 ? 0 : 1
	}
}

function handleDeleteUser(userId: string) {
	if (confirm('Are you sure you want to delete this user?')) {
		users.value = users.value.filter(user => user.id !== userId)
	}
}

function editUser(user: User) {
	selectedUser.value = user
	formData.value = {
		name: user.name,
		email: user.email,
		contactPerson: user.contactPerson,
        isActive: user.isActive === 1,
		clinicId: user.clinicId || '',
		clinicPubId: user.clinicPubId || '',
		password: '',
        phone: user.phone,
		address: user.address
	}
	showEditForm.value = true
}

function handleSubmitForm() {
	if (showCreateForm.value) {
		// Create new user
		const newUser: User = {
			id: `${Math.floor(Math.random() * 100000)}`,
			name: formData.value.name,
			email: formData.value.email,
			contactPerson: formData.value.contactPerson,
			isActive: formData.value.isActive ? 1 : 0,
			clinicId: formData.value.clinicId || undefined,
			phone: formData.value.phone,
			createdAt: new Date().toISOString(),
			address: formData.value.address
		}
		users.value.unshift(newUser)
	} else if (showEditForm.value && selectedUser.value) {
		// Update existing user
		const userIndex = users.value.findIndex(user => user.id === selectedUser.value?.id)
		if (userIndex !== -1) {
			users.value[userIndex] = {
				...users.value[userIndex],
				name: formData.value.name,
				email: formData.value.email,
				contactPerson: formData.value.contactPerson,
				isActive: formData.value.isActive ? 1 : 0,
				clinicId: formData.value.clinicId || undefined,
                phone: formData.value.phone
			}
		}
	}
	closeForm()
}

function closeForm() {
	showCreateForm.value = false
	showEditForm.value = false
	selectedUser.value = null
	formData.value = {
		name: '',
		email: '',
		contactPerson: '',
		isActive: true,
		clinicId: '',
		clinicPubId: '',
		password: '',
        phone: '',
		address: ''
	}
}

const filteredUsers = computed(() => {
	return users.value.filter(user => {
		const matchesSearch = user.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
							user.email.toLowerCase().includes(searchTerm.value.toLowerCase())
		const matchesRole = roleFilter.value === 'all' || user.contactPerson === roleFilter.value
		const matchesStatus = statusFilter.value === 'all' || 
							(statusFilter.value === 'active' && !user.isActive) ||
							(statusFilter.value === 'inactive' && user.isActive)
		return matchesSearch && matchesRole && matchesStatus
	})
})

const formatDate = (dateStr: string) => {
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	})
}

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

async function getAllClinics(page = 1)
{
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

onMounted(async () => {
    getAllClinics(1);
})

watch(itemsPerPage, () => {
    getAllClinics(1)
})
</script> 