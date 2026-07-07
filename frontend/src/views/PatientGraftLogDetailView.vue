<!--
============================================================================
PATIENT GRAFT LOG MODULE - DETAIL VIEW
----------------------------------------------------------------------------
Dedicated per-patient timeline page. Accessed via /.../patient-graft-log/:id
To remove this module, delete this file plus the matching module files.
============================================================================
-->
<template>
	<div class="space-y-6 lg:space-y-8">
		<!-- Header with back button -->
		<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
			<div class="flex items-center gap-3 min-w-0">
				<button
					type="button"
					class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-sm hover:-translate-x-1 transition-all duration-200"
					title="Back to patients"
					@click="goBack"
				>
					<ArrowLeft class="w-5 h-5" />
				</button>
				<div class="space-y-1 min-w-0">
					<h1 class="text-xl lg:text-3xl font-bold text-gray-900 dark:text-white truncate">Graft Log</h1>
				</div>
			</div>
			<div class="flex items-center gap-3 w-full lg:w-auto">
				<button
					v-if="patientDetail && !isAdmin"
					type="button"
					class="w-full lg:w-auto flex items-center justify-center lg:justify-start px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30 active:scale-95 group"
					@click="openAdd()"
				>
					<Plus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
					Add Log Entry
				</button>
			</div>
		</div>

		<!-- Loading -->
		<div
			v-if="loading"
			class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 flex flex-col items-center justify-center"
		>
			<div class="w-8 h-8 rounded-full border-4 border-gray-200 dark:border-gray-700 border-t-red-600 animate-spin"></div>
			<p class="text-sm text-gray-400 dark:text-gray-500 mt-3">Loading patient history...</p>
		</div>

		<!-- Not found -->
		<div
			v-else-if="!patientDetail"
			class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center"
		>
			<ClipboardList class="w-14 h-14 text-gray-200 dark:text-gray-700 mx-auto mb-3" />
			<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Patient not found</p>
			<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
				The patient you're looking for doesn't exist or you don't have access.
			</p>
			<button
				type="button"
				class="mt-5 inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-medium"
				@click="goBack"
			>
				<ArrowLeft class="w-4 h-4 mr-1.5" />
				Back to patients
			</button>
		</div>

		<!-- Detail content -->
		<template v-else>
			<!-- Patient header card -->
			<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
				<div class="px-4 lg:px-6 py-4 flex flex-wrap items-start justify-between gap-3">
					<div class="flex items-start gap-3 min-w-0">
						<div class="w-10 h-10 shrink-0 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
							<UserCircle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
						</div>
						<div class="min-w-0">
							<h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight truncate">
								{{ patientDetail.patient.patient_name }}
							</h2>
							<div class="mt-1 flex items-center gap-2 flex-wrap">
								<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
									<Building2 class="w-3 h-3" />
									{{ patientDetail.patient.clinic_name }}
								</span>
								<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
									<Activity class="w-3 h-3" />
									{{ patientDetail.logs.length }}
									{{ patientDetail.logs.length === 1 ? 'application ' : 'applications' }}
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Timeline -->
			<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
				<div class="px-4 lg:px-6 py-5 lg:py-6">
					<div v-if="patientDetail.logs.length === 0" class="text-center py-16">
						<ClipboardList class="w-12 h-12 text-gray-200 dark:text-gray-700 mx-auto mb-3" />
						<p class="text-sm text-gray-400 dark:text-gray-500">No graft applications yet</p>
						<p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Add the first entry using the button above.</p>
					</div>

					<ol v-else class="relative">
						<li
							v-for="log in patientDetail.logs"
							:key="log.graft_log_id"
							class="relative pl-6 lg:pl-8 pb-6 lg:pb-7 last:pb-0"
						>
							<span class="absolute left-[7px] top-7 bottom-0 w-px bg-gradient-to-b from-red-200 to-transparent dark:from-red-900 dark:to-transparent" />
							<span class="absolute left-0 top-2 w-3.5 h-3.5 rounded-full bg-red-500 ring-4 ring-red-50 dark:ring-gray-800 shadow-sm" />

							<div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">
								{{ formatDate(log.date_of_service) }}
							</div>

							<div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-red-900/5 hover:border-red-200 dark:hover:border-red-800/60 transition-all duration-300 hover:-translate-y-0.5 overflow-hidden group/card">
								<!-- card header -->
								<div class="px-3 lg:px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-start justify-between gap-2 lg:gap-3">
									<div class="flex items-center gap-2.5 lg:gap-3 min-w-0">
										<div class="w-9 h-9 lg:w-10 lg:h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
											<Package class="w-4 h-4 lg:w-5 lg:h-5 text-blue-600 dark:text-blue-400" />
										</div>
										<div class="min-w-0">
											<p class="text-[13px] lg:text-sm font-semibold text-gray-900 dark:text-white font-mono tracking-tight truncate">{{ log.serial_number }}</p>
											<p class="text-[11px] lg:text-xs text-gray-500 dark:text-gray-400 truncate">
												{{ log.brand_name }}<span v-if="log.size"> &middot; {{ log.size }}</span>
											</p>
										</div>
									</div>
									<div class="shrink-0 flex flex-col items-end gap-1">
										<span class="inline-flex items-center px-2 lg:px-2.5 py-0.5 rounded-full text-[10px] lg:text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 whitespace-nowrap">
											<span class="lg:hidden">App</span><span class="hidden lg:inline">Application</span> {{ log.application_number }}
										</span>
									</div>
								</div>

								<!-- wound meta row (shown only when extra context is present) -->
								<div
									v-if="log.wound_number || log.week_number || log.location"
									class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs"
								>
									<template v-if="log.wound_number">
										<span class="text-gray-600 dark:text-gray-400">Wound #{{ log.wound_number }}</span>
									</template>
									<template v-if="log.week_number">
										<span v-if="log.wound_number" class="text-gray-300 dark:text-gray-600">|</span>
										<span class="text-gray-600 dark:text-gray-400">Week #{{ log.week_number }}</span>
									</template>
									<template v-if="log.location">
										<span v-if="log.wound_number || log.week_number" class="text-gray-300 dark:text-gray-600">|</span>
										<MapPin class="w-3 h-3" />
										<span class="text-gray-600 dark:text-gray-400">{{ log.location }}</span>
									</template>
								</div>

								<!-- card body 3-col -->
								<div class="p-3 lg:p-4">
									<div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 lg:p-4 space-y-3 lg:space-y-4">
										<!-- Log ID row (identifier at a glance) -->
										<div v-if="log.graft_log_code" class="flex items-center justify-between gap-3 pb-3 border-b border-gray-200 dark:border-gray-600">
											<div class="flex items-center gap-3 min-w-0">
												<div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
													<Hash class="w-4 h-4 text-indigo-500" />
												</div>
												<div class="min-w-0">
													<p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Graft Log ID</p>
													<p class="text-sm font-semibold font-mono tracking-wider text-indigo-700 dark:text-indigo-300 truncate">{{ log.graft_log_code }}</p>
												</div>
											</div>
										</div>

										<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
											<div class="flex items-start gap-3">
												<div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0 mt-0.5">
													<UserCircle class="w-4 h-4 text-indigo-500" />
												</div>
												<div class="min-w-0">
													<p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Clinician</p>
													<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ log.clinician_name || '-' }}</p>
												</div>
											</div>
											<div class="flex items-start gap-3">
												<div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0 mt-0.5">
													<Tag class="w-4 h-4 text-blue-500" />
												</div>
												<div class="min-w-0">
													<p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Brand</p>
													<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ log.brand_name || '-' }}</p>
												</div>
											</div>
											<div class="flex items-start gap-3">
												<div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0 mt-0.5">
													<Crosshair class="w-4 h-4 text-rose-500" />
												</div>
												<div class="min-w-0">
													<p class="text-[11px] text-gray-400 dark:text-gray-500 mb-0.5">Wound Site</p>
													<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ log.wound_site || '-' }}</p>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- notes -->
								<div v-if="log.notes" class="px-3 lg:px-4 pb-3 lg:pb-4">
									<div class="bg-amber-50/50 dark:bg-amber-900/10 rounded-xl border border-amber-200/40 dark:border-amber-800/30">
										<button
											type="button"
											class="w-full px-4 py-2.5 flex items-center justify-between text-xs font-semibold text-gray-700 dark:text-gray-300 transition"
											@click="toggleNotes(log.graft_log_id)"
										>
											<span class="flex items-center gap-1.5">
												<StickyNote class="w-3.5 h-3.5 text-amber-500" />
												Notes
											</span>
											<ChevronDown
												class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform"
												:class="expandedNotes.has(log.graft_log_id) ? 'rotate-180' : ''"
											/>
										</button>
										<div
											v-if="expandedNotes.has(log.graft_log_id)"
											class="px-4 pb-3 text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed"
										>
											{{ log.notes }}
										</div>
									</div>
								</div>

								<!-- card footer -->
								<div class="px-3 lg:px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-white dark:bg-gray-800">
									<span
										v-if="log.invoice_number"
										class="self-start inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300"
									>
										<FileText class="w-3 h-3" />
										{{ log.invoice_number }}
									</span>
									<span v-else class="self-start inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
										<FileText class="w-3 h-3" />
										No invoice linked
									</span>

									<div v-if="!isAdmin" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-2 w-full lg:w-auto">
										<button
											type="button"
											class="w-full lg:w-auto flex items-center justify-center lg:justify-start px-4 py-2.5 lg:py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md hover:shadow-blue-500/30 text-xs font-medium group active:scale-95"
											@click="openEdit(log)"
										>
											<Pencil class="w-3.5 h-3.5 mr-1.5 group-hover:scale-110 transition-transform" />
											Edit
										</button>
										<button
											type="button"
											class="w-full lg:w-auto flex items-center justify-center lg:justify-start px-4 py-2.5 lg:py-2 border border-red-200 dark:border-red-900/40 lg:border-0 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-xl transition-colors text-xs font-medium"
											@click="confirmDelete(log)"
										>
											<Trash2 class="w-3.5 h-3.5 mr-1.5" />
											Delete
										</button>
									</div>
								</div>
							</div>
						</li>
					</ol>
				</div>
			</div>
		</template>

		<!-- Shared add/edit modal -->
		<PatientGraftLogFormModal
			:is-open="modalOpen"
			:patients="patients"
			:serials="serials"
			:clinicians="clinicians"
			:editing-log="editingLog"
			:prefill-patient-id="patientId"
			lock-patient
			@close="closeModal"
			@saved="onSaved"
			@serials-updated="onSerialsUpdated"
		/>

		<!-- Delete Confirmation Modal -->
		<div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
			<div class="fixed inset-0 bg-black/40 transition-opacity" @click="showDeleteModal = false"></div>
			<div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-md flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
				<div class="flex-1 overflow-y-auto p-6">
					<div class="space-y-5">
						<div class="flex justify-center">
							<div class="w-14 h-14 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
								<AlertTriangle class="w-7 h-7 text-red-500 dark:text-red-400" />
							</div>
						</div>
						<div class="text-center space-y-1.5">
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete this graft log entry?</h3>
							<p class="text-sm text-gray-500 dark:text-gray-400">The serial will be marked as available again. This action cannot be undone.</p>
						</div>
						<div v-if="logToDelete" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-2">
							<div class="flex justify-between">
								<span class="text-xs text-gray-500 dark:text-gray-400">Serial Number</span>
								<span class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ logToDelete.serial_number }}</span>
							</div>
							<div v-if="logToDelete.graft_log_code" class="flex justify-between">
								<span class="text-xs text-gray-500 dark:text-gray-400">Graft Log ID</span>
								<span class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ logToDelete.graft_log_code }}</span>
							</div>
							<div v-if="logToDelete.date_of_service" class="flex justify-between">
								<span class="text-xs text-gray-500 dark:text-gray-400">Date of Service</span>
								<span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(logToDelete.date_of_service) }}</span>
							</div>
						</div>
					</div>
				</div>
				<div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
					<button @click="showDeleteModal = false" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">Cancel</button>
					<button @click="deleteLog" :disabled="isDeleting" class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2 shadow-md">
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
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
	Plus, MapPin, ClipboardList, ChevronDown, Pencil, Trash2,
	Activity, Package, StickyNote, FileText, UserCircle, Hash, Tag, ArrowLeft,
	Crosshair, Building2, AlertTriangle, Loader2,
} from 'lucide-vue-next'
import { patientGraftLogService } from '@/services/api'
import PatientGraftLogFormModal from '@/views/PatientGraftLogFormModal.vue'
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
	clinician_clinic_id?: number | null
	clinician_clinic_name?: string | null
	location: string | null
	wound_site: string
	wound_number: number | null
	week_number: number | null
	notes: string | null
}

interface PatientDetail {
	patient: PatientRow
	logs: LogEntry[]
}

const route  = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isAdmin = computed(() => authStore.currentUser?.user_role === 0 || authStore.currentUser?.user_role === 1)

const patientId = computed(() => Number(route.params.id))

const loading       = ref(false)
const patientDetail = ref<PatientDetail | null>(null)
const patients      = ref<PatientRow[]>([])
const serials       = ref<SerialOption[]>([])
const clinicians    = ref<ClinicianOption[]>([])
const expandedNotes = ref<Set<number>>(new Set())

const modalOpen  = ref(false)
const editingLog = ref<LogEntry | null>(null)

const showDeleteModal = ref(false)
const logToDelete     = ref<LogEntry | null>(null)
const isDeleting      = ref(false)

onMounted(async () => {
	loading.value = true
	try {
		const [initRes, historyRes] = await Promise.all([
			patientGraftLogService.getInitData(),
			patientGraftLogService.getPatientHistory(patientId.value),
		])
		patients.value   = initRes.data.data.all_patients ?? initRes.data.data.patients ?? []
		serials.value    = initRes.data.data.serials ?? []
		clinicians.value = initRes.data.data.clinicians ?? []
		patientDetail.value = historyRes.data.data ?? null
	} catch (e) {
		console.error('Failed to load patient graft log detail', e)
	} finally {
		loading.value = false
	}
})

function goBack() {
	// Strip the trailing /:id from the current path to go back to list.
	const basePath = route.path.replace(/\/\d+$/, '')
	router.push(basePath || '/')
}

function toggleNotes(id: number) {
	const next = new Set(expandedNotes.value)
	next.has(id) ? next.delete(id) : next.add(id)
	expandedNotes.value = next
}

function openAdd() {
	editingLog.value = null
	modalOpen.value = true
}

function openEdit(log: LogEntry) {
	editingLog.value = log
	modalOpen.value = true
}

function closeModal() {
	modalOpen.value = false
	editingLog.value = null
}

function onSerialsUpdated(list: SerialOption[]) {
	serials.value = list
}

function onSaved(entry: LogEntry, isEdit: boolean) {
	if (!patientDetail.value) return
	if (isEdit) {
		patientDetail.value.logs = patientDetail.value.logs.map(l =>
			l.graft_log_id === entry.graft_log_id ? entry : l,
		)
	} else {
		patientDetail.value.logs = [entry, ...patientDetail.value.logs]
		patientDetail.value.patient.graft_count += 1
	}
	if (entry.ledger_id) {
		const s = serials.value.find(x => x.ledger_id === entry.ledger_id)
		if (s) s.is_used = true
	}
}

async function confirmDelete(log: LogEntry) {
	logToDelete.value = log
	showDeleteModal.value = true
}

async function deleteLog() {
	if (!logToDelete.value) return
	const log = logToDelete.value
	isDeleting.value = true
	try {
		await patientGraftLogService.delete(log.graft_log_id)
		if (patientDetail.value) {
			patientDetail.value.logs = patientDetail.value.logs.filter(l => l.graft_log_id !== log.graft_log_id)
			patientDetail.value.patient.graft_count = Math.max(0, patientDetail.value.patient.graft_count - 1)
		}
		if (log.ledger_id) {
			const s = serials.value.find(x => x.ledger_id === log.ledger_id)
			if (s) s.is_used = false
		}
		showDeleteModal.value = false
		logToDelete.value = null
	} catch (e) {
		console.error('Failed to delete graft log', e)
	} finally {
		isDeleting.value = false
	}
}

function formatDate(value: string | null): string {
	if (!value) return ''
	try {
		const d = new Date(value)
		if (isNaN(d.getTime())) return value
		const month = d.toLocaleString('en-US', { month: 'short' })
		const day = d.getDate()
		const year = d.getFullYear()
		let hours = d.getHours()
		const minutes = d.getMinutes().toString().padStart(2, '0')
		const ampm = hours >= 12 ? 'PM' : 'AM'
		hours = hours % 12 || 12
		return `${month} ${day}, ${year} [${hours}:${minutes} ${ampm}]`
	} catch {
		return value
	}
}
</script>
