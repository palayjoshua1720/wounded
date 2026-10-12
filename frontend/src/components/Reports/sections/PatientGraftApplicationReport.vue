<template>
	<div class="space-y-5">
		<!-- KPI Cards Row -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
			<!-- Total Applications Card -->
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Applications</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalApplications }}</p>
					</div>
					<div class="p-2.5 bg-rose-50 dark:bg-rose-900/30 rounded-lg">
						<ClipboardDocumentCheckIcon class="w-5 h-5 text-rose-600 dark:text-rose-400" />
					</div>
				</div>
			</div>

			<!-- Patients Treated Card -->
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Unique Patients</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalUniquePatients }}</p>
					</div>
					<div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
						<UserGroupIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
					</div>
				</div>
			</div>

			<!-- Serials Applied Card -->
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Serials Applied</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalUniqueSerials }}</p>
					</div>
					<div class="p-2.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
						<CubeIcon class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
					</div>
				</div>
			</div>
		</div>

		<!-- Breakdown Section -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
			<!-- Top Brands Breakdown -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
						<TagIcon class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Graft Brands Applied</h3>
				</div>
				<div v-if="brandBreakdown.length === 0" class="py-8 text-center text-xs text-gray-500 dark:text-gray-400">
					No brand application data available for the selected filters.
				</div>
				<div v-else class="space-y-3">
					<div v-for="b in brandBreakdown" :key="b.name" class="flex items-center justify-between text-xs">
						<span class="font-medium text-gray-700 dark:text-gray-300">{{ b.name }}</span>
						<div class="flex items-center space-x-2">
							<span class="px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-semibold">{{ b.count }} apps</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Top Clinicians Breakdown -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-amber-50 dark:bg-amber-900/30 rounded-lg">
						<UserIcon class="w-4 h-4 text-amber-600 dark:text-amber-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Performing Clinicians</h3>
				</div>
				<div v-if="clinicianBreakdown.length === 0" class="py-8 text-center text-xs text-gray-500 dark:text-gray-400">
					No clinician application data available for the selected filters.
				</div>
				<div v-else class="space-y-3">
					<div v-for="c in clinicianBreakdown" :key="c.name" class="flex items-center justify-between text-xs">
						<span class="font-medium text-gray-700 dark:text-gray-300">{{ c.name }}</span>
						<div class="flex items-center space-x-2">
							<span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-semibold">{{ c.count }} apps</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Detailed Applications Table -->
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
			<div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
				<div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Graft Application Records</h3>
					<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Detailed log of graft applications, wound sites, and serial numbers</p>
				</div>
				<input
					v-model="searchQuery"
					type="text"
					placeholder="Search serial, patient, clinician..."
					class="px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-gray-900 dark:text-white transition-all w-full sm:w-64"
				/>
			</div>

			<div class="overflow-x-auto">
				<table class="w-full text-left text-xs">
					<thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">
						<tr>
							<th class="px-4 py-3">Code</th>
							<th class="px-4 py-3">Date</th>
							<th class="px-4 py-3">Patient</th>
							<th class="px-4 py-3">Clinic</th>
							<th class="px-4 py-3">Clinician</th>
							<th class="px-4 py-3">Brand & Size</th>
							<th class="px-4 py-3">Serial #</th>
							<th class="px-4 py-3">Wound Site</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-gray-100 dark:divide-gray-700">
						<tr v-if="filteredLogs.length === 0">
							<td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
								No graft application records match your filter criteria.
							</td>
						</tr>
						<tr
							v-for="log in filteredLogs"
							:key="log.graft_log_code || log.graft_log_id"
							class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors text-gray-800 dark:text-gray-200"
						>
							<td class="px-4 py-3 font-mono font-medium text-rose-600 dark:text-rose-400 whitespace-nowrap">
								{{ log.graft_log_code }}
							</td>
							<td class="px-4 py-3 whitespace-nowrap">{{ log.date_of_service }}</td>
							<td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
								{{ getPatientDisplayName(log) }}
							</td>
							<td class="px-4 py-3 whitespace-nowrap">{{ log.clinic_name }}</td>
							<td class="px-4 py-3 whitespace-nowrap">{{ log.clinician_name }}</td>
							<td class="px-4 py-3 whitespace-nowrap">
								<span class="font-medium">{{ log.brand_name }}</span>
								<span class="text-gray-400 dark:text-gray-500 ml-1">({{ log.size }})</span>
							</td>
							<td class="px-4 py-3 font-mono text-xs whitespace-nowrap">{{ log.serial_number }}</td>
							<td class="px-4 py-3 whitespace-nowrap">
								<span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
									{{ log.wound_site || 'N/A' }} (Wk {{ log.week_number || 1 }})
								</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
	ClipboardDocumentCheckIcon,
	UserGroupIcon,
	CubeIcon,
	TagIcon,
	UserIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps<{
	graftLogs: any[]
	isLoadingData: boolean
	anonymizePhi: boolean
}>()

const searchQuery = ref('')

const totalApplications = computed(() => props.graftLogs.length)

const totalUniquePatients = computed(() => {
	const set = new Set(props.graftLogs.map(l => l.patient_id).filter(Boolean))
	return set.size
})

const totalUniqueSerials = computed(() => {
	const set = new Set(props.graftLogs.map(l => l.serial_number).filter(Boolean))
	return set.size
})

const brandBreakdown = computed(() => {
	const counts: Record<string, number> = {}
	for (const log of props.graftLogs) {
		const name = log.brand_name || 'Unknown Brand'
		counts[name] = (counts[name] || 0) + 1
	}
	return Object.entries(counts)
		.map(([name, count]) => ({ name, count }))
		.sort((a, b) => b.count - a.count)
})

const clinicianBreakdown = computed(() => {
	const counts: Record<string, number> = {}
	for (const log of props.graftLogs) {
		const name = log.clinician_name || 'Unknown Clinician'
		counts[name] = (counts[name] || 0) + 1
	}
	return Object.entries(counts)
		.map(([name, count]) => ({ name, count }))
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
})

function anonymizeName(name: string): string {
	if (!name) return 'N/A'
	const parts = name.trim().split(/\s+/)
	const initials = parts.map(p => p.charAt(0).toUpperCase() + '.')
	return initials.join(' ')
}

function getPatientDisplayName(log: any): string {
	if (props.anonymizePhi && log.patient_name) {
		return anonymizeName(log.patient_name)
	}
	return log.patient_name || (`Patient #${log.patient_id}`)
}

const filteredLogs = computed(() => {
	if (!searchQuery.value.trim()) return props.graftLogs
	const q = searchQuery.value.toLowerCase().trim()
	return props.graftLogs.filter(log => {
		const code = (log.graft_log_code || '').toLowerCase()
		const serial = (log.serial_number || '').toLowerCase()
		const patient = (log.patient_name || '').toLowerCase()
		const clinician = (log.clinician_name || '').toLowerCase()
		const brand = (log.brand_name || '').toLowerCase()
		const site = (log.wound_site || '').toLowerCase()
		return code.includes(q) || serial.includes(q) || patient.includes(q) || clinician.includes(q) || brand.includes(q) || site.includes(q)
	})
})
</script>
