<template>
	<div>
		<ContentLoader v-if="tableLoader" />
		<div v-if="!tableLoader && users && users.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
			<div
				v-for="user in users"
				:key="user.id"
				class="border border-gray-200 dark:border-gray-700 rounded p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow"
			>
				<div class="flex items-start justify-between mb-4">
					<div>
						<div class="flex items-center gap-3">
							<div class="p-2 bg-green-100 rounded-lg">
								<Hospital class="w-5 h-5 text-green-600" />
							</div>

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
						<button @click="$emit('view-clinic', user)" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
							<Eye class="w-5 h-4" />
						</button>
						<button @click="$emit('edit-clinic', user)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
							<SquarePen class="w-4 h-4" />
						</button>
						<button 
							@click="$emit('toggle-status', user.id)" 
							:class="user.isActive === 0 
								? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300' 
								: 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'"
							:title="user.isActive === 0 ? 'Active' : 'Inactive'"
							>
							<component :is="user.isActive === 0 ? CircleX : CircleCheck" class="w-4 h-4" />
						</button>
						<button @click="$emit('delete-clinic', user.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete User">
							<Trash2 class="w-4 h-4" />
						</button>
						<button @click="$emit('delete-clinic', user.id)" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300" title="Archive User">
							<Archive class="w-4 h-4" />
						</button>
						<!-- can only be archived when inactive -->
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

		<div v-else-if="!tableLoader && users && users.length === 0" class="">
			<div class="flex flex-col items-center justify-center gap-2 text-center">
				<Hospital class="w-10 h-10 mb-1 text-gray-700" />
				<span class="text-gray-600 dark:text-gray-300">No clinics found.</span>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination v-if="users && users.length > 0" :pagination="pagination" @update:page="(p:number) => $emit('update:page', p)" />
		</template>
	</div>
</template>

<script setup lang="ts">
import ContentLoader from '@/components/ui/ContentLoader.vue'
import Pagination from '@/components/ui/Pagination.vue'
import {
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
	Archive,
} from 'lucide-vue-next'

interface Clinician {
	id: string;
	name: string;
	email: string;
	phone?: string;
}

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
}

defineProps<{
	users: User[]
	userStatus: Record<number, { label: string; classes: string }>
	tableLoader: boolean
	pagination: {
		current_page: number
		last_page: number
		per_page: number
		total: number
	}
}>()

defineEmits<{
	'view-clinic': [user: User]
	'edit-clinic': [user: User]
	'toggle-status': [userId: string]
	'delete-clinic': [userId: string]
	'update:page': [page: number]
}>()

function formatDate(dateStr: string) {
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric',
	})
}
</script>


