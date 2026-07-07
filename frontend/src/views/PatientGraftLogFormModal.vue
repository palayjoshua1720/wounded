<!--
============================================================================
PATIENT GRAFT LOG MODULE - SHARED FORM MODAL
----------------------------------------------------------------------------
Add/Edit modal shared by PatientGraftLogView (list) and
PatientGraftLogDetailView (per-patient timeline).
To remove this module, delete this file plus the matching module files.
============================================================================
-->
<template>
	<Teleport to="body">
		<div v-if="isOpen" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
			<div class="fixed inset-0 bg-black/40 transition-opacity" @click="onCancel"></div>

			<div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
				<!-- Header -->
				<div class="flex-shrink-0 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">
						{{ editingLog ? 'Edit Log Entry' : 'Add Log Entry' }}
					</h3>
					<button
						type="button"
						class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
						@click="onCancel"
					>
						<X class="w-5 h-5" />
					</button>
				</div>

				<!-- Body -->
				<form class="flex-1 overflow-y-auto p-6 space-y-6" @submit.prevent="submit">
					<!-- Section: Patient & Graft -->
					<div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-4">
						<div class="flex items-center gap-2 mb-1">
							<UserCircle class="w-4 h-4 text-blue-500" />
							<h4 class="text-sm font-semibold text-gray-900 dark:text-white">Patient &amp; Graft</h4>
						</div>

						<!-- Patient -->
						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Patient <span class="text-red-500">*</span></label>
							<div class="relative">
								<UserCircle class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none" />
								<select
									v-model.number="form.patient_id"
									:disabled="lockPatient"
									class="w-full pl-9 pr-9 py-2.5 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all duration-200 appearance-none truncate disabled:opacity-80 disabled:cursor-not-allowed"
									:class="errors.patient_id
										? 'bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white ring-2 ring-red-300'
										: 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white'"
								>
									<option :value="null">Select a patient</option>
									<option v-for="p in patients" :key="p.patient_id" :value="p.patient_id">
										{{ p.patient_name }} — {{ p.clinic_name }}
									</option>
								</select>
								<ChevronDown class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none" />
							</div>
							<p v-if="errors.patient_id" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
								<AlertCircle class="w-3 h-3" />
								{{ errors.patient_id }}
							</p>
						</div>

						<!-- Serial picker -->
						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Serial No. / Graft Used <span class="text-red-500">*</span></label>
							<div class="relative mb-2">
								<Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
								<input
									v-model="serialSearch"
									type="text"
									placeholder="Search by item #, size or brand..."
									class="w-full pl-9 pr-9 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm transition-all duration-200"
								/>
								<span
									v-if="searchingSerials"
									class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full border-2 border-blue-500/30 border-t-blue-500 animate-spin"
								/>
							</div>
							<div class="border border-gray-200 dark:border-gray-600 rounded-xl max-h-48 overflow-y-auto bg-white dark:bg-gray-600">
								<button
									v-for="s in filteredSerials"
									:key="s.graft_size_id"
									type="button"
									class="w-full text-left px-3 py-2.5 text-sm border-b last:border-b-0 border-gray-100 dark:border-gray-700/60 transition-all flex items-center gap-3"
									:class="form.graft_size_id === s.graft_size_id
										? 'bg-blue-50 dark:bg-blue-900/20'
										: 'hover:bg-gray-50 dark:hover:bg-gray-700/40'"
									@click="form.graft_size_id = s.graft_size_id"
								>
									<div
										class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center"
										:class="form.graft_size_id === s.graft_size_id
											? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
											: 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
									>
										<Package class="w-4 h-4" />
									</div>
									<div class="flex-1 min-w-0">
										<div class="font-mono text-xs font-semibold text-gray-900 dark:text-white truncate">{{ s.item_no || s.serial_number }}</div>
										<div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
											{{ s.brand_name }}<span v-if="s.size"> &middot; {{ s.size }}</span>
										</div>
									</div>
									<CheckCircle2
										v-if="form.graft_size_id === s.graft_size_id"
										class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0"
									/>
								</button>
								<div v-if="filteredSerials.length === 0" class="px-4 py-6 text-xs text-gray-400 dark:text-gray-500 text-center">
									{{ searchingSerials ? 'Searching...' : 'No matching serials available.' }}
								</div>
							</div>
							<p v-if="errors.graft_size_id" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
								<AlertCircle class="w-3 h-3" />
								{{ errors.graft_size_id }}
							</p>
						</div>
					</div>

					<!-- Section: Clinical Details -->
					<div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-4">
						<div class="flex items-center gap-2 mb-1">
							<ClipboardList class="w-4 h-4 text-indigo-500" />
							<h4 class="text-sm font-semibold text-gray-900 dark:text-white">Clinical Details</h4>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div>
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Date of Service <span class="text-red-500">*</span></label>
								<div class="relative">
									<Calendar class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none" />
									<input
										v-model="form.date_of_service"
										type="date"
										class="w-full pl-9 pr-3 py-2.5 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all duration-200"
										:class="errors.date_of_service
											? 'bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white ring-2 ring-red-300'
											: 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white'"
									/>
								</div>
								<p v-if="errors.date_of_service" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
									<AlertCircle class="w-3 h-3" />
									{{ errors.date_of_service }}
								</p>
							</div>

							<div>
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Location</label>
								<div class="relative">
									<MapPin class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none" />
									<input
										v-model="form.location"
										type="text"
										placeholder="Optional"
										class="w-full pl-9 pr-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm transition-all duration-200"
									/>
								</div>
							</div>
						</div>

						<div>
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Wound Site <span class="text-red-500">*</span></label>
							<input
								v-model="form.wound_site"
								type="text"
								placeholder="e.g. Left heel"
								class="w-full px-3 py-2.5 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all duration-200"
								:class="errors.wound_site
									? 'bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white ring-2 ring-red-300'
									: 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white'"
							/>
							<p v-if="errors.wound_site" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
								<AlertCircle class="w-3 h-3" />
								{{ errors.wound_site }}
							</p>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
							<div>
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Wound #</label>
								<input
									v-model.number="form.wound_number"
									type="number"
									min="1"
									placeholder="—"
									class="w-full px-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm transition-all duration-200"
								/>
							</div>
							<div>
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Week #</label>
								<input
									v-model.number="form.week_number"
									type="number"
									min="1"
									placeholder="—"
									class="w-full px-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm transition-all duration-200"
								/>
							</div>
							<div>
								<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Clinician <span class="text-red-500">*</span></label>
								<div class="relative">
									<select
										v-model.number="form.clinician_id"
										class="w-full pl-3 pr-9 py-2.5 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all duration-200 appearance-none truncate"
										:class="errors.clinician_id
											? 'bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white ring-2 ring-red-300'
											: 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white'"
									>
										<option :value="null">Select</option>
										<option v-for="c in clinicians" :key="c.id" :value="c.id">
											{{ c.full_name }}
										</option>
									</select>
									<ChevronDown class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none" />
								</div>
								<p v-if="errors.clinician_id" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
									<AlertCircle class="w-3 h-3" />
									{{ errors.clinician_id }}
								</p>
							</div>
						</div>
					</div>

					<!-- Section: Invoice Linking (optional) -->
					<div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-3">
						<div class="flex items-center justify-between gap-3">
							<div class="flex items-center gap-2">
								<Receipt class="w-4 h-4 text-emerald-500" />
								<h4 class="text-sm font-semibold text-gray-900 dark:text-white">Link an Invoice</h4>
								<span class="text-[11px] text-gray-400 dark:text-gray-500">(optional)</span>
							</div>
							<label class="relative inline-flex items-center cursor-pointer">
								<input
									v-model="linkInvoice"
									type="checkbox"
									class="sr-only peer"
								/>
								<div class="w-10 h-5 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-200"></div>
								<div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 peer-checked:translate-x-5"></div>
							</label>
						</div>
						<div v-if="linkInvoice">
							<label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Invoice Number</label>
							<input
								v-model="form.invoice_number"
								type="text"
								placeholder="e.g. INV-2026-0001"
								class="w-full px-3 py-2.5 border-0 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition-all duration-200"
								:class="errors.invoice_number
									? 'bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white ring-2 ring-red-300'
									: 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white'"
							/>
							<p v-if="errors.invoice_number" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
								<AlertCircle class="w-3 h-3" />
								{{ errors.invoice_number }}
							</p>
							<p v-else class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5">
								We will validate the invoice number against existing invoices.
							</p>
						</div>
					</div>

					<!-- Section: Notes -->
					<div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-3">
						<div class="flex items-center gap-2 mb-1">
							<FileText class="w-4 h-4 text-amber-500" />
							<h4 class="text-sm font-semibold text-gray-900 dark:text-white">Notes</h4>
						</div>
						<textarea
							v-model="form.notes"
							rows="3"
							placeholder="Optional notes about this application..."
							class="w-full px-3 py-2.5 border-0 bg-white dark:bg-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm transition-all duration-200 resize-none"
						/>
					</div>
				</form>

				<!-- Footer -->
				<div class="flex-shrink-0 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3 bg-white dark:bg-gray-800">
					<button
						type="button"
						class="px-5 py-2.5 text-sm font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200"
						@click="onCancel"
					>
						Cancel
					</button>
					<button
						type="button"
						class="flex items-center gap-1.5 px-5 py-2.5 text-sm font-medium rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30 disabled:opacity-60 disabled:cursor-not-allowed group active:scale-95"
						:disabled="saving"
						@click="submit"
					>
						<span v-if="saving" class="w-4 h-4 rounded-full border-2 border-white/40 border-t-white animate-spin" />
						<Save v-else class="w-4 h-4 group-hover:scale-110 transition-transform" />
						{{ saving ? 'Saving...' : 'Save Entry' }}
					</button>
				</div>
			</div>
		</div>
	</Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import {
	Search, MapPin, ClipboardList, ChevronDown, X, Save,
	Package, FileText, UserCircle, Calendar, AlertCircle, CheckCircle2,
	Receipt,
} from 'lucide-vue-next'
import { patientGraftLogService } from '@/services/api'

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
	// Legacy fields retained from old ledger-based payload so existing
	// records still render correctly in edit mode.
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

const props = defineProps<{
	isOpen: boolean
	patients: PatientRow[]
	serials: SerialOption[]
	clinicians: ClinicianOption[]
	editingLog?: LogEntry | null
	prefillPatientId?: number | null
	lockPatient?: boolean
}>()

const emit = defineEmits<{
	(e: 'close'): void
	(e: 'saved', entry: LogEntry, isEdit: boolean): void
	(e: 'serials-updated', list: SerialOption[]): void
}>()

const form = reactive<{
	patient_id: number | null
	graft_size_id: number | null
	date_of_service: string
	location: string
	wound_site: string
	wound_number: number | null
	week_number: number | null
	clinician_id: number | null
	notes: string
	invoice_number: string
}>({
	patient_id: null,
	graft_size_id: null,
	date_of_service: '',
	location: '',
	wound_site: '',
	wound_number: null,
	week_number: null,
	clinician_id: null,
	notes: '',
	invoice_number: '',
})

const linkInvoice     = ref(false)

const errors          = ref<Record<string, string>>({})
const serialSearch    = ref('')
const saving          = ref(false)
const localSerials    = ref<SerialOption[]>([])
const searchingSerials = ref(false)
let serialSearchTimer: ReturnType<typeof setTimeout> | null = null

const filteredSerials = computed(() => {
	const q = serialSearch.value.trim().toLowerCase()
	const list = localSerials.value.length ? localSerials.value : props.serials
	if (!q) return list
	return list.filter(s =>
		(s.item_no ? s.item_no.toLowerCase().includes(q) : false) ||
		(s.serial_number ? s.serial_number.toLowerCase().includes(q) : false) ||
		(s.brand_name ? s.brand_name.toLowerCase().includes(q) : false) ||
		(s.size ? s.size.toLowerCase().includes(q) : false),
	)
})

// Debounced server-side lookup: when the client list has no match, ping the
// backend so serials outside the preloaded window become visible.
watch(serialSearch, (q) => {
	if (serialSearchTimer) clearTimeout(serialSearchTimer)
	const term = q.trim()
	if (term.length < 2) return
	serialSearchTimer = setTimeout(async () => {
		searchingSerials.value = true
		try {
			const { data } = await patientGraftLogService.searchSerials(term, false)
			const fetched = (data.data ?? []) as SerialOption[]
			const existing = new Map(localSerials.value.map(s => [s.graft_size_id, s]))
			for (const s of fetched) {
				if (!existing.has(s.graft_size_id)) existing.set(s.graft_size_id, s)
			}
			localSerials.value = Array.from(existing.values())
			emit('serials-updated', localSerials.value)
		} catch (e) {
			console.error('Failed to search serials', e)
		} finally {
			searchingSerials.value = false
		}
	}, 300)
})

watch(() => props.isOpen, async (open) => {
	if (!open) return
	resetForm()
	localSerials.value = [...props.serials]

	if (props.editingLog) {
		const log = props.editingLog
		form.patient_id      = log.patient_id
		form.graft_size_id   = log.graft_size_id
		form.date_of_service = log.date_of_service ?? ''
		form.location        = log.location ?? ''
		form.wound_site      = log.wound_site
		form.wound_number    = log.wound_number
		form.week_number     = log.week_number
		form.clinician_id    = log.clinician_id
		form.notes           = log.notes ?? ''
		form.invoice_number  = log.invoice_number ?? ''
		linkInvoice.value    = !!(log.invoice_id || log.invoice_number)

		if (log.graft_size_id && !localSerials.value.some(s => s.graft_size_id === log.graft_size_id)) {
			try {
				const { data } = await patientGraftLogService.searchSerials(log.serial_number, true)
				const extra = (data.data ?? []) as SerialOption[]
				localSerials.value = [...extra, ...localSerials.value]
				emit('serials-updated', localSerials.value)
			} catch (e) {
				console.error('Failed to fetch existing serial', e)
			}
		}
	} else if (props.prefillPatientId) {
		form.patient_id = props.prefillPatientId
	}
}, { immediate: false })

function resetForm() {
	form.patient_id      = null
	form.graft_size_id   = null
	form.date_of_service = new Date().toISOString().slice(0, 10)
	form.location        = ''
	form.wound_site      = ''
	form.wound_number    = null
	form.week_number     = null
	form.clinician_id    = null
	form.notes           = ''
	form.invoice_number  = ''
	linkInvoice.value    = false
	errors.value         = {}
	serialSearch.value   = ''
}

function validate(): boolean {
	const e: Record<string, string> = {}
	if (!form.patient_id) e.patient_id = 'Please select a patient.'
	if (!form.graft_size_id) e.graft_size_id = 'Please select a serial number.'
	if (!form.date_of_service) e.date_of_service = 'Date of service is required.'
	if (!form.wound_site || !form.wound_site.trim()) e.wound_site = 'Wound site is required.'
	if (!form.clinician_id) e.clinician_id = 'Please select a clinician.'
	if (linkInvoice.value && !form.invoice_number.trim()) e.invoice_number = 'Please enter an invoice number or turn off linking.'
	errors.value = e
	return Object.keys(e).length === 0
}

async function submit() {
	if (!validate()) return
	saving.value = true
	try {
		const { invoice_number, ...rest } = form
		const payload: Record<string, unknown> = { ...rest }
		// Only include invoice_number in the payload when the user has toggled
		// linking on. When editing an existing log we send an empty string to
		// explicitly clear any previous link.
		if (linkInvoice.value) {
			payload.invoice_number = invoice_number.trim()
		} else if (props.editingLog) {
			payload.invoice_number = ''
		}
		if (props.editingLog) {
			const { data } = await patientGraftLogService.update(props.editingLog.graft_log_id, payload)
			emit('saved', data.data, true)
		} else {
			const { data } = await patientGraftLogService.create(payload)
			emit('saved', data.data, false)
		}
		emit('close')
	} catch (err: any) {
		const resp = err?.response?.data
		if (resp?.errors) {
			const flat: Record<string, string> = {}
			for (const k of Object.keys(resp.errors)) flat[k] = resp.errors[k][0]
			errors.value = flat
		} else if (resp?.message) {
			errors.value = { graft_size_id: resp.message }
		}
	} finally {
		saving.value = false
	}
}

function onCancel() {
	emit('close')
}
</script>
