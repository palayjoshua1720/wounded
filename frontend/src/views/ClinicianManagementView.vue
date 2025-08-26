<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clinician Management</h1>
				<p class="text-gray-600 dark:text-gray-400">Manage clinician accounts, roles, and access permissions</p>
			</div>

			<button
				@click="
					selectedUser = null; 
					showCreateForm = true
				"
				class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
			>
				<UserPlus class="w-4 h-4 mr-2" />
				Add Clinician
			</button>
		</div>

		<!-- Filters -->
		<div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded shadow-sm border border-gray-200 dark:border-gray-700">
			<div class="flex-1">
				<div class="relative">
					<Search class="absolute left-3 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" />
					<input
						v-model="searchTerm"
						type="text"
						placeholder="Search Clinicians..."
						class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
					/>
				</div>
			</div>
			<div class="flex items-center space-x-2">
				<Funnel class="w-4 h-4 text-gray-500 dark:text-gray-400" />
				<select
				v-model="statusFilter"
				class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
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
						focus:ring-2 focus:ring-green-500 focus:border-transparent 
						bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
				>
					<option value="10">10</option>
					<option value="25">25</option>
					<option value="50">50</option>
				</select>
			</div>
		</div>

		<!-- Users Table -->
		<div class="bg-white dark:bg-gray-800 rounded shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
					<thead class="bg-gray-50 dark:bg-gray-700">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Name
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Contact Number
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Date Joined
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Last Login
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Status
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
							v-for="user in filteredUsers"
							:key="user.id"
							class="hover:bg-gray-50 dark:hover:bg-gray-700 "
							>
								<td class="px-6 py-3 whitespace-nowrap">
									<div class="flex items-center">
										<div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
											<userProfile class="w-5 h-5 text-green-600" />
										</div>
										<div class="ml-4">
											<div class="text-sm text-gray-900 dark:text-white">{{ user.name }}</div>
											<div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
										</div>
									</div>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									<div class="flex items-center gap-2">
										<Phone class="w-4 h-4" />
										<span>{{ user.phone ? user.phone : 'N/A' }}</span>
									</div>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ user.createdAt ? formatDate(user.createdAt) : 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ user.lastLogin ? formatDate(user.lastLogin) : 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap">
									<button
										@click="handleToggleStatus(user.id)"
										:class="['flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors', userStatus[user.isActive].classes]"
										:title="user.isActive ? 'Activate' : 'Deactivate'"
									>
										<component :is="user.isActive ? UserX : UserCheck" class="w-4 h-4" />
										<span v-if="userStatus[user.isActive]">
											{{ userStatus[user.isActive].label }}
										</span>
									</button>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm font-medium space-x-2">
									<button
									@click="selectedUser = user"
									class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
									title="View Details"
									>
										<Eye class="w-5 h-4" />
									</button>
									<button
									@click="editUser(user)"
									class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
									title="Edit User"
									>
										<SquarePen class="w-4 h-4" />
									</button>
									<button
									@click="handleDeleteUser(user.id)"
									class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
									title="Delete User"
									>
										<Trash2 class="w-4 h-4" />
									</button>
								</td>
							</tr>
							<tr v-if="filteredUsers.length === 0 && !tableLoader">
								<td colspan="7" class="text-center text-gray-400 py-6">
									<div class="flex flex-col items-center justify-center gap-2">
										<Users class="w-10 h-10 mb-1" />
										<span>No clinicians found.</span>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination :pagination="pagination" @update:page="getAllClinicians" />
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
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
							<span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getRoleColor(selectedUser.role)]">
								{{ selectedUser.role }}
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
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Login</label>
							<p class="mt-1 text-sm text-gray-900 dark:text-white">
								{{ selectedUser.lastLogin ? formatDate(selectedUser.lastLogin) : 'Never' }}
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
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add new Clinician' : 'Edit Clinic'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<div class="grid grid-cols-3 gap-4">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Firstname<span class="text-red-500">*</span></label>
						<input
						v-model="formData.first_name"
						type="text"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Middlename</label>
						<input
						v-model="formData.middle_name"
						type="text"
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lastname<span class="text-red-500">*</span></label>
						<input
						v-model="formData.last_name"
						type="text"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
				</div>

				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email<span class="text-red-500">*</span></label>
						<input
						v-model="formData.email"
						type="email"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone number<span class="text-red-500">*</span></label>
						<input
						v-model="formData.phone"
						type="text"
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
				</div>

				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role<span class="text-red-500">*</span></label>
						<input
						v-model="formData.role"
						type="email"
						required
						value="3"
						readonly
						placeholder="Clinician"
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
							<option :value="false">Active</option>
							<option :value="true">Inactive</option>
						</select>
					</div>
				</div>

				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic ID (for clinicians/staff)</label>
						<input
							v-model="formData.clinicId"
							type="number"
							class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							placeholder="Optional - only for clinic users"
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Manufacturer ID (for clinicians/staff)</label>
						<input
							v-model="formData.manufacturerId"
							type="number"
							class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							placeholder="Optional - only for clinic users"
						/>
					</div>
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
import axios from 'axios'
import BaseModal from '../components/common/BaseModal.vue'
import Pagination from '../components/ui/Pagination.vue'
import TableLoader from '../components/ui/TableLoader.vue'
import ContentLoader from '../components/ui/ContentLoader.vue'
import {
    UserPlus,
	Funnel,
    Search,
    Eye,
    SquarePen,
    Users,
    Trash2,
	Phone,
	User as userProfile,
	UserCheck,
	UserX
} from 'lucide-vue-next'
import api from '../services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

interface User {
	id: string
	email: string
	name: string
	first_name: string
	middle_name: string
	last_name: string
	phone: string
	role: 'Admin' | 'Sales' | 'Clinician' | 'Staff'
	clinicId?: string
	manufacturerId?: string
	isActive: number
	lastLogin?: string
	createdAt: string
}

const users = ref<User[]>([])
const itemsPerPage = ref(10)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})
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
	email: '',
	first_name: '',
	middle_name: '',
	last_name: '',
	phone: '',
	role: 'Clinician' as User['role'],
	isActive: false,
	clinicId: '',
	manufacturerId: '',
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
		first_name: user.first_name,
		middle_name: user.last_name,
		last_name: user.middle_name,
		phone: user.phone,
		email: user.email,
		role: user.role,
		isActive: user.isActive === 1,
		clinicId: user.clinicId || '',
		manufacturerId: user.manufacturerId || '',
	}
	showEditForm.value = true
}

async function handleSubmitForm() {
	try {
		const payload = {
			first_name: formData.value.first_name,
			middle_name: formData.value.middle_name,
			last_name: formData.value.last_name,
			email: formData.value.email,
			role: formData.value.role,
			isActive: formData.value.isActive ? 1 : 0,
			clinicId: formData.value.clinicId || undefined,
			manufacturerId: formData.value.manufacturerId || '',
			phone: formData.value.phone,
		}

		if (showCreateForm.value) {
			const { data } = await api.post(
				'/management/users/add/clinician',
				payload,
				{
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
					}
				}
			)

			toast.success(data.message || 'Clinician added successfully!')
			await getAllClinicians()
		} else if (showEditForm.value && selectedUser.value) {
			const response = await fetch(`/management/users/${selectedUser.value.id}`, {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json'
				},
				body: JSON.stringify(payload)
			})

			const result = await response.json()

			if (response.ok) {
				const userIndex = users.value.findIndex(user => user.id === selectedUser.value?.id)
				if (userIndex !== -1) {
					users.value[userIndex] = result.data
				}
			}
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
	selectedUser.value = null
	formData.value = {
		email: '',
		first_name: '',
		middle_name: '',
		last_name: '',
		phone: '',
		role: 'Clinician',
		isActive: true,
		clinicId: '',
		manufacturerId: '',
	}
}

const filteredUsers = computed(() => {
	return users.value.filter(user => {
		const matchesSearch = user.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
							user.email.toLowerCase().includes(searchTerm.value.toLowerCase())
		const matchesRole = roleFilter.value === 'all' || user.role === roleFilter.value
		const matchesStatus = statusFilter.value === 'all' || 
							(statusFilter.value === 'active' && !user.isActive) ||
							(statusFilter.value === 'inactive' && user.isActive)
		return matchesSearch && matchesRole && matchesStatus
	})
})

const getRoleColor = (role: User['role']) => {
	switch (role) {
		case 'Admin': return 'bg-purple-100 text-purple-800'
		case 'Sales': return 'bg-blue-100 text-blue-800'
		case 'Clinician': return 'bg-green-100 text-green-800'
		case 'Staff': return 'bg-gray-100 text-gray-800'
		default: return 'bg-gray-100 text-gray-800'
	}
}

const showFormModal = computed({
	get: () => showCreateForm.value || showEditForm.value,
	set: (value: boolean) => {
		if (!value) {
			showCreateForm.value = false
			showEditForm.value = false
		} else {
			selectedUser.value = null
		}
	}
})

const showUserDetailsModal = computed({
	get: () => selectedUser.value !== null,
	set: (value: boolean) => {
		if (!value) {
			selectedUser.value = null
		} else {
			showCreateForm.value = false
			showEditForm.value = false
		}
	}
})

const formatDate = (dateStr: string) => {
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	})
}

async function getAllClinicians(page = 1) {
    tableLoader.value = true
    try {
        const { data } = await api.get(`/management/users/clinician?page=${page}&per_page=${itemsPerPage.value}`, {
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
    getAllClinicians(1)
})

watch(itemsPerPage, () => {
    getAllClinicians(1)
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