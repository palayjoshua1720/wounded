<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clinic Management</h1>
				<p class="text-gray-600 dark:text-gray-400">Manage clinician accounts, roles, and access permissions</p>
			</div>
			<button
				@click="showCreateForm = true"
				class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
			>
				<PlusIcon class="w-4 h-4 mr-2" />
				Create User
			</button>
		</div>

		<!-- Filters -->
		<div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
			<div class="flex-1">
				<div class="relative">
					<MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
					<input
						v-model="searchTerm"
						type="text"
						placeholder="Search Clinicians..."
						class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
					/>
				</div>
			</div>
			<div class="flex items-center space-x-2">
				<FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
				<select
				v-model="statusFilter"
				class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
				>
					<option value="all">All Status</option>
					<option value="active">Active</option>
					<option value="inactive">Inactive</option>
				</select>
			</div>
		</div>

		<!-- Users Table -->
		<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
					<thead class="bg-gray-50 dark:bg-gray-700">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								User
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Role
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Status
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Last Login
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-if="tableLoader" class="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-gray-900/70 z-10 rounded-lg">
                            <span class="relative flex items-center justify-center h-16 w-16">
                                <svg class="absolute animate-ping-slow h-16 w-16 text-blue-400 dark:text-blue-700 opacity-30" viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4"/></svg>
                                <svg class="relative h-12 w-12 text-blue-600 dark:text-blue-400" viewBox="0 0 64 64" fill="none">
                                    <rect x="27" y="12" width="10" height="40" rx="3" fill="currentColor"/>
                                    <rect x="12" y="27" width="40" height="10" rx="3" fill="currentColor"/>
                                </svg>
                            </span>
                        </div>
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
								<span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getRoleColor(user.role)]">
									{{ user.role }}
								</span>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', user.isActive ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400']">
									{{ user.isActive ? 'Active' : 'Inactive' }}
								</span>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
								{{ user.lastLogin ? formatDate(user.lastLogin) : 'Never' }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
								<button
								@click="selectedUser = user"
								class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
								title="View Details"
								>
									<EyeIcon class="w-4 h-4" />
								</button>
								<button
								@click="editUser(user)"
								class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
								title="Edit User"
								>
									<PencilSquareIcon class="w-4 h-4" />
								</button>
								<button
								@click="handleToggleStatus(user.id)"
								:class="user.isActive ? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300' : 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'"
								:title="user.isActive ? 'Deactivate' : 'Activate'"
								>
									<component :is="user.isActive ? XCircleIcon : CheckCircleIcon" class="w-4 h-4" />
								</button>
								<button
								@click="handleDeleteUser(user.id)"
								class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
								title="Delete User"
								>
									<TrashIcon class="w-4 h-4" />
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

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
							<span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', selectedUser.isActive ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400']">
								{{ selectedUser.isActive ? 'Active' : 'Inactive' }}
							</span>
						</div>
					</div>

					<div class="grid grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Login</label>
							<p class="mt-1 text-sm text-gray-900 dark:text-white">
								{{ selectedUser.lastLogin ? formatDateTime(selectedUser.lastLogin) : 'Never' }}
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
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Create New User' : 'Edit User'">
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
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role<span class="text-red-500">*</span></label>
						<select
						v-model="formData.role"
						required
						class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="admin">Admin</option>
							<option value="sales">Sales</option>
							<option value="clinician">Clinician</option>
							<option value="staff">Staff</option>
						</select>
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
import { ref, computed, onMounted } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
	PlusIcon,
	MagnifyingGlassIcon,
	FunnelIcon,
	EyeIcon,
	PencilSquareIcon,
	CheckCircleIcon,
	XCircleIcon,
	TrashIcon
} from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface User {
	id: string
	email: string
	name: string
	role: 'admin' | 'sales' | 'clinician' | 'staff'
	clinicId?: string
	isActive: boolean
	lastLogin?: string
	createdAt: string
}

const users = ref<User[]>([])
const tableLoader = ref(false);

const searchTerm = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')
const selectedUser = ref<User | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)

const formData = ref({
	name: '',
	email: '',
	role: 'staff' as User['role'],
	isActive: true,
	clinicId: '',
	password: ''
})

function handleToggleStatus(userId: string) {
	const userIndex = users.value.findIndex(user => user.id === userId)
	if (userIndex !== -1) {
		users.value[userIndex].isActive = !users.value[userIndex].isActive
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
		role: user.role,
		isActive: user.isActive,
		clinicId: user.clinicId || '',
		password: ''
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
			role: formData.value.role,
			isActive: formData.value.isActive,
			clinicId: formData.value.clinicId || undefined,
			lastLogin: undefined,
			createdAt: new Date().toISOString()
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
				role: formData.value.role,
				isActive: formData.value.isActive,
				clinicId: formData.value.clinicId || undefined
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
		role: 'staff',
		isActive: true,
		clinicId: '',
		password: ''
	}
}

const filteredUsers = computed(() => {
	return users.value.filter(user => {
		const matchesSearch = user.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
							user.email.toLowerCase().includes(searchTerm.value.toLowerCase())
		const matchesRole = roleFilter.value === 'all' || user.role === roleFilter.value
		const matchesStatus = statusFilter.value === 'all' || 
							(statusFilter.value === 'active' && user.isActive) ||
							(statusFilter.value === 'inactive' && !user.isActive)
		return matchesSearch && matchesRole && matchesStatus
	})
})

const getRoleColor = (role: User['role']) => {
	switch (role) {
		case 'admin': return 'bg-purple-100 text-purple-800'
		case 'sales': return 'bg-blue-100 text-blue-800'
		case 'clinician': return 'bg-green-100 text-green-800'
		case 'staff': return 'bg-gray-100 text-gray-800'
		default: return 'bg-gray-100 text-gray-800'
	}
}

const formatDate = (dateStr: string) => {
	return new Date(dateStr).toLocaleDateString()
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

const formatDateTime = (dateStr: string) => {
	return new Date(dateStr).toLocaleString()
}

onMounted(async () => {
    tableLoader.value = true;
    try {
        const { data } = await api.get('/management/users/clinics', {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
        users.value = data
    } catch (error) {
        console.error('Error fetching clinic users:', error)
    } finally {
        tableLoader.value = false
    }
})
</script> 