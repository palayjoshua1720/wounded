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
							<div v-if="showLogo(user)" class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                <img :src="user.logo" :alt="`${user.name} logo`" class="w-full h-full object-cover border" @error="onLogoError(user.id)" />
                            </div>
							<div v-else class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
								<Hospital class="w-5 h-5 text-green-600 dark:text-green-400" />
							</div>

							<div class="flex flex-col">
								<h3 class="font-semibold text-gray-900 dark:text-white">
									{{ user.name }}
								</h3>
								<span :class="[
									'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit transition-all duration-200',
									getStatusClasses(user.isActive)
								]">
									<component :is="getStatusIcon(user.isActive)" class="w-2.5 h-2.5 mr-1" />
									{{ userStatus[user.isActive]?.label || 'Unknown' }}
								</span>
							</div>
						</div>
					</div>
					<!-- Quick Actions using ActionGroup component - visible only on sm+ screens -->
					<div class="hidden sm:block shrink-0">
						<ActionGroup :actions="getClinicActions(user)" />
					</div>
				</div>
				<div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
					<div v-if="user.address" class="flex items-center gap-2">
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

				<!-- Mobile Card Actions - visible only on small screens -->
				<div class="sm:hidden pt-3 mt-3 border-t border-gray-200 dark:border-gray-700 -mx-6 -mb-6 px-6 pb-6 rounded-b bg-gray-50 dark:bg-gray-700/30">
					<div class="flex items-center justify-center gap-1.5 flex-wrap">
						<ActionGroup :actions="getClinicActions(user)" />
					</div>
				</div>
			</div>
		</div>

		<div v-else-if="!tableLoader && users && users.length === 0" class="">
			<div class="flex flex-col items-center justify-center gap-2 text-center">
				<Hospital class="w-10 h-10 mb-1 text-gray-700 dark:text-gray-400" />
				<span class="text-gray-600 dark:text-gray-300">No clinics found.</span>
			</div>
		</div>

		<template v-if="!tableLoader">
			<div class="bg-white mt-4 rounded-bl-2xl rounded-br-2xl shadow-sm dark:bg-gray-800">
				<Pagination v-if="users && users.length > 0" :pagination="pagination" @update:page="(p:number) => $emit('update:page', p)" />
			</div>
		</template>
	</div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ActionGroup from '@/components/ui/ActionGroup.vue'
import type { ActionConfig } from '@/components/ui/ActionGroup.vue'
import { useProductStatus } from '@/composables/products/useProductStatus'
import {
	Eye, SquarePen, CircleCheck, CircleX,
	Trash2, Hospital, MapPin, Phone,
	Mail, CalendarPlus, IdCard, IdCardLanyard,
	Archive, ArchiveRestore,
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
	logo?: string | null
	assigned_clinician_ids: Clinician[]
}

const props = defineProps<{
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

const emit = defineEmits<{
	'view-clinic': [user: User]
	'edit-clinic': [user: User]
	'toggle-status': [userId: string]
	'delete-clinic': [userId: string]
	'archive-clinic': [userId: string]
	'unarchive-clinic': [userId: string]
	'update:page': [page: number]
}>()

const logoErrors = reactive<Set<string>>(new Set())

const { getStatusClasses, getStatusIcon } = useProductStatus()

function showLogo(user: User) {
	return !!user.logo && !logoErrors.has(user.id)
}

function onLogoError(userId: string) {
	logoErrors.add(userId)
}

function getClinicActions(user: User): ActionConfig[] {
	const isActive = user.isActive === 0
	const isInactive = user.isActive === 1
	const isArchived = user.isActive === 2

	const actions: ActionConfig[] = [
		{
			id: 'view',
			icon: Eye,
			title: 'View',
			hoverColor: 'green',
			onClick: () => emit('view-clinic', user),
		},
		{
			id: 'edit',
			icon: SquarePen,
			title: 'Edit',
			hoverColor: 'blue',
			onClick: () => emit('edit-clinic', user),
		},
		{
			id: 'toggle-status',
			icon: isActive ? CircleX : CircleCheck,
			title: isActive ? 'Deactivate' : 'Activate',
			variant: isActive ? 'danger' : 'success',
			visible: isActive || isInactive,
			onClick: () => emit('toggle-status', user.id),
		},
		{
			id: 'archive',
			icon: Archive,
			title: 'Archive',
			hoverColor: 'orange',
			visible: isActive || isInactive,
			onClick: () => emit('archive-clinic', user.id),
		},
		{
			id: 'unarchive',
			icon: ArchiveRestore,
			title: 'Unarchive',
			hoverColor: 'green',
			visible: isArchived,
			onClick: () => emit('unarchive-clinic', user.id),
		},
		{
			id: 'delete',
			icon: Trash2,
			title: 'Delete',
			hoverColor: 'red',
			visible: isArchived,
			onClick: () => emit('delete-clinic', user.id),
		},
	]

	return actions
}

function formatDate(dateStr: string) {
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric',
	})
}
</script>


