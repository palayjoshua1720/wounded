<!--
============================================================================
PATIENT GRAFT LOG MODULE - LIST VIEW
----------------------------------------------------------------------------
Grid-of-patient-cards entry point for the Patient Graft Log feature.
Clicking a card opens the dedicated PatientGraftLogDetailView at
/:role/patient-graft-log/:id.
To remove this module, delete this file, PatientGraftLogDetailView.vue,
PatientGraftLogFormModal.vue, and the matching blocks in
frontend/src/router/index.ts and the api.ts service block.
============================================================================
-->
<template>
	<div class="space-y-8">
		<!-- Header -->
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
			<div class="space-y-2">
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Graft Log</h1>
			</div>
			<div class="flex items-center gap-4">
				<button @click="showStats = !showStats"
					class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200 hover:shadow-sm active:scale-95">
					<BarChart3 class="w-5 h-5 mr-2" />
					{{ showStats ? 'Hide' : 'Show' }} Stats
				</button>
				<button
					v-if="!isAdmin"
					type="button"
					class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30 group active:scale-95"
					@click="openAdd()"
				>
					<Plus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
					Add Log Entry
				</button>
			</div>
		</div>

		<!-- Stats Panel -->
		<transition enter-active-class="transition ease-out duration-300"
			enter-from-class="transform opacity-0 -translate-y-4" enter-to-class="transform opacity-100 translate-y-0"
			leave-active-class="transition ease-in duration-200" leave-from-class="transform opacity-100 translate-y-0"
			leave-to-class="transform opacity-0 -translate-y-4">
			<div v-if="showStats"
				class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Graft Log Statistics</h3>

				<!-- Main Stats -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
					<div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-sm hover:bg-white dark:hover:bg-gray-700">
						<div
							class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
							<Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
						</div>
						<div>
							<p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalPatients }}</p>
							<p class="text-sm text-gray-500 dark:text-gray-400">Total Patient w/ graft records</p>
						</div>
					</div>
					<div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-sm hover:bg-white dark:hover:bg-gray-700">
						<div
							class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
							<Package class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
						</div>
						<div>
							<p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalGrafts }}</p>
							<p class="text-sm text-gray-500 dark:text-gray-400">Total Grafts Used</p>
						</div>
					</div>
				</div>
			</div>
		</transition>

		<!-- Filters Card (inventory-ledger style) -->
		<div class="bg-white dark:bg-gray-800 p-4 lg:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<!-- Mobile: stacked -->
			<div class="lg:hidden space-y-3">
				<div class="relative">
					<Search class="absolute left-3.5 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" />
					<input
						v-model="search"
						type="text"
						placeholder="Search by patient name or clinic..."
						class="w-full pl-11 pr-10 py-2.5 border border-transparent bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white text-sm transition-all duration-200 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-700/70"
					/>
					<button
						v-if="search"
						type="button"
						class="absolute right-3 top-3 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
						@click="search = ''"
					>
						<X class="w-4 h-4" />
					</button>
				</div>
				<div class="relative">
					<select
						v-model.number="itemsPerPage"
						class="w-full pl-3 pr-8 py-2.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm appearance-none transition-all duration-200"
					>
						<option :value="9">9 per page</option>
						<option :value="18">18 per page</option>
						<option :value="36">36 per page</option>
					</select>
					<ChevronDown class="absolute right-3 top-3 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
				</div>
			</div>

			<!-- Desktop: horizontal -->
			<div class="hidden lg:flex lg:flex-row lg:items-center gap-4">
				<div class="relative flex-1">
					<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
					<input
						v-model="search"
						type="text"
						placeholder="Search by patient name or clinic..."
						class="w-full pl-12 pr-10 py-3.5 border border-transparent bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-700/70"
					/>
					<button
						v-if="search"
						type="button"
						class="absolute right-3 top-3.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
						@click="search = ''"
					>
						<X class="w-4 h-4" />
					</button>
				</div>
				<div class="relative w-44">
					<select
						v-model.number="itemsPerPage"
						class="w-full pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
					>
						<option :value="9">9 per page</option>
						<option :value="18">18 per page</option>
						<option :value="36">36 per page</option>
					</select>
					<ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
				</div>
			</div>
		</div>

		<!-- Loading State -->
		<div
			v-if="loadingPatients"
			class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 flex flex-col items-center justify-center"
		>
			<div class="w-8 h-8 rounded-full border-4 border-gray-200 dark:border-gray-700 border-t-red-600 animate-spin"></div>
			<p class="text-sm text-gray-400 dark:text-gray-500 mt-3">Loading patients...</p>
		</div>

		<!-- Empty State -->
		<div
			v-else-if="filteredPatients.length === 0"
			class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center"
		>
			<div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
				<UserSearch class="w-7 h-7 text-gray-400 dark:text-gray-500" />
			</div>
			<p class="text-base font-medium text-gray-700 dark:text-gray-300">
				{{ search ? 'No patients found' : 'No patients yet' }}
			</p>
			<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
				{{ search ? 'Try a different search term.' : 'Add a log entry to begin tracking patient grafts.' }}
			</p>
		</div>

		<!-- Patient Card Grid -->
		<div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
			<div
				v-for="p in paginatedPatients"
				:key="p.patient_id"
				class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:shadow-red-900/5 hover:border-red-200 dark:hover:border-red-800/60 transition-all duration-300 hover:-translate-y-1 overflow-hidden cursor-pointer"
				@click="openPatient(p.patient_id)"
			>
				<!-- Delete (removes ALL graft logs for this patient) -->
				<button
					v-if="!isAdmin"
					type="button"
					class="absolute top-2.5 right-2.5 z-10 p-1.5 rounded-lg bg-white/80 dark:bg-gray-700/80 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 opacity-0 group-hover:opacity-100 transition-all duration-200"
					:disabled="deletingPatientId === p.patient_id"
					:title="`Delete all graft logs for ${p.patient_name}`"
					@click.stop="confirmDeletePatient(p)"
				>
					<Trash2 v-if="deletingPatientId !== p.patient_id" class="w-4 h-4" />
					<div v-else class="w-4 h-4 rounded-full border-2 border-red-300 border-t-red-600 animate-spin"></div>
				</button>

				<div class="p-5">
					<div class="flex items-start gap-3 mb-4">
						<div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-red-600 dark:group-hover:bg-red-500 transition-colors duration-300">
							<UserCircle class="w-6 h-6 text-blue-600 dark:text-blue-400 group-hover:text-white transition-colors duration-300" />
						</div>
						<div class="min-w-0 flex-1">
							<h3 class="text-base font-semibold text-gray-900 dark:text-white truncate">
								{{ p.patient_name }}
							</h3>
							<p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate flex items-center gap-1">
								<Building2 class="w-3 h-3 flex-shrink-0" />
								{{ p.clinic_name }}
							</p>
						</div>
					</div>

					<!-- stat pill -->
					<div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 flex items-center gap-3">
						<div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
							<Activity class="w-4 h-4 text-blue-500" />
						</div>
						<div class="min-w-0 flex-1">
							<p class="text-[11px] text-gray-400 dark:text-gray-500">Applications</p>
							<p class="text-sm font-semibold text-gray-900 dark:text-white">
								{{ p.graft_count }}
								{{ p.graft_count === 1 ? 'graft' : 'grafts' }}
							</p>
						</div>
						<ChevronRight class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-red-500 group-hover:translate-x-1 transition-all duration-300 flex-shrink-0" />
					</div>
				</div>
			</div>
		</div>

		<!-- Pagination -->
		<Pagination
			v-if="!loadingPatients && filteredPatients.length > 0 && pagination.last_page > 1"
			:pagination="pagination"
			@update:page="handlePageChange"
		/>

		<!-- Shared add/edit modal -->
		<PatientGraftLogFormModal
			:is-open="modalOpen"
			:patients="allPatients"
			:serials="serials"
			:clinicians="clinicians"
			:editing-log="null"
			:prefill-patient-id="null"
			@close="closeModal"
			@saved="onSaved"
			@serials-updated="onSerialsUpdated"
		/>

		<!-- Log Entry Choice Modal -->
		<div v-if="showChoiceModal" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
			<div class="fixed inset-0 bg-black/40 transition-opacity" @click="closeChoiceModal"></div>
			<div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
				<div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Log Entry</h3>
					<button
						type="button"
						class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
						@click="closeChoiceModal"
					>
						<X class="w-5 h-5" />
					</button>
				</div>
				<div class="flex-1 overflow-y-auto p-6 space-y-6">
					<p class="text-sm text-gray-600 dark:text-gray-400">Choose how you want to create the graft log entry:</p>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<button
							type="button"
							class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-700 rounded-xl hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg transition-all duration-200"
							@click="chooseManual"
						>
							<div class="w-12 h-12 bg-blue-100 dark:bg-blue-800/50 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
								<Edit class="w-6 h-6 text-blue-600 dark:text-blue-400" />
							</div>
							<span class="font-semibold text-gray-900 dark:text-white text-base">Manual Entry</span>
							<span class="text-sm text-gray-500 dark:text-gray-400 mt-1.5 text-center">Enter graft log details manually</span>
						</button>

						<button
							type="button"
							class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-2 border-purple-200 dark:border-purple-700 rounded-xl hover:border-purple-500 dark:hover:border-purple-500 hover:shadow-lg transition-all duration-200"
							@click="chooseUpload"
						>
							<div class="w-12 h-12 bg-purple-100 dark:bg-purple-800/50 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
								<UploadCloud class="w-6 h-6 text-purple-600 dark:text-purple-400" />
							</div>
							<span class="font-semibold text-gray-900 dark:text-white text-base">Upload File</span>
							<span class="text-sm text-gray-500 dark:text-gray-400 mt-1.5 text-center">Upload graft log data via file</span>
						</button>
					</div>
				</div>
				<div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
					<button
						type="button"
						class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium"
						@click="closeChoiceModal"
					>
						Cancel
					</button>
				</div>
			</div>
		</div>

		<!-- Upload File Modal (placeholder) -->
		<div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
			<div class="fixed inset-0 bg-black/40 transition-opacity" @click="closeUploadModal"></div>
			<div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
				<div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Graft Log File</h3>
					<button
						type="button"
						class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
						@click="closeUploadModal"
					>
						<X class="w-5 h-5" />
					</button>
				</div>
				<div class="flex-1 overflow-y-auto p-6 space-y-4">
					<div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center">
						<div class="w-14 h-14 rounded-full bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center mx-auto mb-3">
							<UploadCloud class="w-7 h-7 text-purple-500" />
						</div>
						<p class="text-base font-medium text-gray-900 dark:text-white">File upload coming soon</p>
						<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">This feature is under development. For now, please use Manual Entry to log graft usage.</p>
					</div>
				</div>
				<div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
					<button
						type="button"
						class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium"
						@click="closeUploadModal"
					>
						Cancel
					</button>
				</div>
			</div>
		</div>

		<!-- Delete Confirmation Modal (Patient graft logs) -->
		<div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
			<div class="fixed inset-0 bg-black/40 transition-opacity" @click="cancelDelete"></div>
			<div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-md flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
				<div class="flex-1 overflow-y-auto p-6">
					<div class="space-y-5">
						<div class="flex justify-center">
							<div class="w-14 h-14 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
								<AlertTriangle class="w-7 h-7 text-red-500 dark:text-red-400" />
							</div>
						</div>
						<div class="text-center space-y-1.5">
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete all graft logs?</h3>
							<p class="text-sm text-gray-500 dark:text-gray-400">Every graft log record tied to this patient will be permanently deleted. This action cannot be undone.</p>
						</div>
						<div v-if="patientToDelete" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-2">
							<div class="flex justify-between">
								<span class="text-xs text-gray-500 dark:text-gray-400">Patient</span>
								<span class="text-sm font-medium text-gray-900 dark:text-white truncate ml-3">{{ patientToDelete.patient_name }}</span>
							</div>
							<div class="flex justify-between">
								<span class="text-xs text-gray-500 dark:text-gray-400">Clinic</span>
								<span class="text-sm font-medium text-gray-900 dark:text-white truncate ml-3">{{ patientToDelete.clinic_name }}</span>
							</div>
							<div class="flex justify-between">
								<span class="text-xs text-gray-500 dark:text-gray-400">Records to delete</span>
								<span class="text-sm font-medium text-red-600 dark:text-red-400">{{ patientToDelete.graft_count }} {{ patientToDelete.graft_count === 1 ? 'entry' : 'entries' }}</span>
							</div>
						</div>
					</div>
				</div>
				<div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
					<button @click="cancelDelete" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">Cancel</button>
					<button @click="deletePatientLogs" :disabled="isDeleting" class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2 shadow-md">
						<Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
						<Trash2 v-else class="w-4 h-4" />
						Delete
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
	Plus, Search, X, UserSearch, UserCircle, Building2, Activity, ChevronRight, ChevronDown,
	Trash2, AlertTriangle, Loader2, Edit, UploadCloud, BarChart3, Users, Package,
} from 'lucide-vue-next'
import { patientGraftLogService } from '@/services/api'
import PatientGraftLogFormModal from '@/views/PatientGraftLogFormModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { useAuthStore } from '@/stores/auth'

interface PatientRow {
	patient_id: number
	patient_name: string
	clinic_id: number | null
	clinic_name: string
	graft_count: number
}

interface SerialOption {
	graft_size_id: number
	item_no: string | null
	serial_number: string
	brand_id: number | null
	brand_name: string
	size: string | null
	stock?: number
	// Legacy fields retained for backward-compat with old ledger-based payload.
	ledger_id?: number | null
	clinic_id?: number | null
	clinic_name?: string | null
	invoice_id?: number | null
	invoice_number?: string | null
	is_used?: boolean
}

interface ClinicianOption {
	id: number
	full_name: string
	clinic_id: number | null
	clinic_name: string | null
	role: number
}

interface LogEntry {
	graft_log_id: number
	graft_log_code?: string | null
	patient_id: number
	application_number: number
	date_of_service: string | null
	serial_number: string
	ledger_id: number | null
	brand_id: number | null
	brand_name: string
	graft_size_id: number | null
	size: string | null
	clinic_id: number | null
	clinic_name: string | null
	invoice_id: number | null
	invoice_number: string | null
	clinician_id: number | null
	clinician_name: string | null
	location: string | null
	wound_site: string
	wound_number: number | null
	week_number: number | null
	notes: string | null
}

const route  = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isAdmin = computed(() => authStore.currentUser?.user_role === 0 || authStore.currentUser?.user_role === 1)

const patients        = ref<PatientRow[]>([])
const allPatients     = ref<PatientRow[]>([])
const serials         = ref<SerialOption[]>([])
const clinicians      = ref<ClinicianOption[]>([])
const search          = ref('')
const loadingPatients = ref(false)
const modalOpen       = ref(false)
const itemsPerPage    = ref(9)
const currentPage     = ref(1)
const deletingPatientId = ref<number | null>(null)
const showDeleteModal   = ref(false)
const patientToDelete   = ref<PatientRow | null>(null)
const isDeleting        = ref(false)
const showChoiceModal   = ref(false)
const showUploadModal   = ref(false)
const showStats         = ref(false)

const stats = computed(() => {
	const totalPatients = allPatients.value.filter(p => p.graft_count > 0).length
	const totalGrafts = allPatients.value.reduce((sum, p) => sum + p.graft_count, 0)
	
	return { totalPatients, totalGrafts }
})

const filteredPatients = computed(() => {
	const q = search.value.trim().toLowerCase()
	if (!q) return patients.value
	return patients.value.filter(p =>
		p.patient_name.toLowerCase().includes(q) ||
		p.clinic_name.toLowerCase().includes(q),
	)
})

const pagination = computed(() => {
	const total = filteredPatients.value.length
	const perPage = itemsPerPage.value || 9
	const last = Math.max(1, Math.ceil(total / perPage))
	return {
		current_page: Math.min(currentPage.value, last),
		last_page: last,
		per_page: perPage,
		total,
	}
})

const paginatedPatients = computed(() => {
	const start = (pagination.value.current_page - 1) * pagination.value.per_page
	return filteredPatients.value.slice(start, start + pagination.value.per_page)
})

// Reset to first page whenever search or per-page changes
watch([search, itemsPerPage], () => {
	currentPage.value = 1
})

function handlePageChange(page: number) {
	currentPage.value = page
}

onMounted(async () => {
	await loadInit()
})

async function loadInit() {
	loadingPatients.value = true
	try {
		const { data } = await patientGraftLogService.getInitData()
		patients.value    = data.data.patients ?? []
		allPatients.value = data.data.all_patients ?? data.data.patients ?? []
		serials.value     = data.data.serials ?? []
		clinicians.value  = data.data.clinicians ?? []
	} catch (e) {
		console.error('Failed to load Graft Log init', e)
	} finally {
		loadingPatients.value = false
	}
}

function openPatient(id: number) {
	// Navigate to the detail page under the current role prefix
	// (e.g. /admin/patient-graft-log → /admin/patient-graft-log/:id)
	router.push(`${route.path.replace(/\/$/, '')}/${id}`)
}

function openAdd() {
	showChoiceModal.value = true
}

function closeChoiceModal() {
	showChoiceModal.value = false
}

function chooseManual() {
	showChoiceModal.value = false
	modalOpen.value = true
}

function chooseUpload() {
	showChoiceModal.value = false
	showUploadModal.value = true
}

function closeUploadModal() {
	showUploadModal.value = false
	// Return to the choice modal so the user can still pick Manual Entry
	showChoiceModal.value = true
}

function closeModal() {
	modalOpen.value = false
}

function onSerialsUpdated(list: SerialOption[]) {
	serials.value = list
}

function onSaved(entry: LogEntry, isEdit: boolean) {
	if (!isEdit) {
		const target = patients.value.find(p => p.patient_id === entry.patient_id)
		if (target) {
			target.graft_count += 1
		} else {
			// New patient appeared — reload to pick up full row
			loadInit()
		}
	}
	if (entry.ledger_id) {
		const s = serials.value.find(x => x.ledger_id === entry.ledger_id)
		if (s) s.is_used = true
	}
}

async function confirmDeletePatient(p: PatientRow) {
	patientToDelete.value = p
	showDeleteModal.value = true
}

function cancelDelete() {
	if (isDeleting.value) return
	showDeleteModal.value = false
	patientToDelete.value = null
}

async function deletePatientLogs() {
	if (!patientToDelete.value) return
	const p = patientToDelete.value
	isDeleting.value = true
	deletingPatientId.value = p.patient_id
	try {
		await patientGraftLogService.deleteByPatient(p.patient_id)
		patients.value    = patients.value.filter(x => x.patient_id !== p.patient_id)
		allPatients.value = allPatients.value.map(x =>
			x.patient_id === p.patient_id ? { ...x, graft_count: 0 } : x,
		)
		showDeleteModal.value = false
		patientToDelete.value = null
	} catch (e) {
		console.error('Failed to delete patient graft logs', e)
	} finally {
		isDeleting.value = false
		deletingPatientId.value = null
	}
}
</script>
