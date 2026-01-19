<template>
	<div class="space-y-8">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">IVR Management</h1>
				<p class="text-gray-600 dark:text-gray-400">Manage insurance verification requests</p>
			</div>

			<button
				@click="
					clearForm()
					selectedIvrRequest = null; 
					showCreateForm = true
				"
				class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group"
			>
				<FilePlus2 class="w-5 h-5 mr-2" />
				New IVR Request
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col lg:flex-row gap-6">
				<div class="flex-1">
					<div class="relative">
						<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search IVR Requests..."
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
							<option value="pending">Pending</option>
							<option value="eligible">Eligible</option>
							<option value="not_eligible">Not Eligible</option>
						</select>
						<ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
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
					<ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
				</div>
			</div>
		</div>

		<!-- IVR Table -->
		<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
					<thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Patient
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Clinic
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Manufacturer
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Submitted
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Eligibility Status
							</th>
							<!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Expiry
							</th> -->
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
						<TableLoader v-if="tableLoader" :colspan="7" />
						<template v-else>
							<tr
							v-for="ivr in filteredIVRRequest"
							:key="ivr.ivr_id"
							class="hover:bg-gray-50 dark:hover:bg-gray-700 "
							>
								<td class="px-6 py-3 whitespace-nowrap">
									<div class="flex items-center">
										<div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
											<userProfile class="w-5 h-5 text-green-600" />
										</div>
										<div class="ml-4">
											<div class="text-sm text-gray-900 dark:text-white">{{ ivr.patient?.patient_name }}</div>
											<div class="text-sm text-gray-500 dark:text-gray-400">{{ ivr.patient?.email }}</div>
										</div>
									</div>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									<div class="flex items-center gap-2">
										<span>{{ ivr.clinic?.clinic_name ? ivr.clinic?.clinic_name : 'N/A' }}</span>
									</div>
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ ivr.manufacturer?.manufacturer_name || 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ ivr.submitted_at ? formatDateTime(ivr.submitted_at) : 'N/A' }}
								</td>
								<td class="px-6 py-3 whitespace-nowrap">
									<button
										@click="handleToggleStatus(ivr.ivr_id)"
										:class="[
										'flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors',
										ivr.ivr_status === 1
											? ivrStatus[1].classes
											: ivrEligibilityStatus[ivr.eligibility_status]?.classes
										]"
										:title="ivr.ivr_status === 1 ? 'Archived' : 'Toggle Status'"
									>
										<span>
										{{ ivr.ivr_status === 1
											? ivrStatus[1].label
											: ivrEligibilityStatus[ivr.eligibility_status]?.label }}
										</span>
									</button>
								</td>
								<!-- <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
									{{ ivr.submitted_at ? formatDateTime(ivr.submitted_at) : 'N/A' }}
								</td> -->
								<td class="px-6 py-3 whitespace-nowrap text-sm font-medium space-x-2">
									<button
									@click="
										selectedIvrRequest = ivr;
										showIvrDetails(ivr)
									"
									class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
									title="View Details"
									>
										<Eye class="w-5 h-4" />
									</button>
									<button
									@click="editIVR(ivr)"
									class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
									title="Edit User"
									>
										<SquarePen class="w-4 h-4" />
									</button>
									<button
									@click="confirmDelete(ivr)"
									class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
									title="Delete User"
									>
										<Trash2 class="w-4 h-4" />
									</button>
									<button
									@click="confirmArchive(ivr)"
									:class="[
										'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300',
									]"
									:title="ivr.ivr_status === 1 ? 'Unarchive User' : 'Archive User'"
									>
										<component
											:is="ivr.ivr_status === 1 ? ArchiveRestore : Archive"
											class="w-4 h-4"
										/>
									</button>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>

			<div v-if="filteredIVRRequest.length === 0 && !tableLoader" class="text-center py-12">
				<div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <FileXIcon class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No IVR Request found.</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to find
                    what you're looking for.</p>
			</div>

			<template v-if="!tableLoader">
				<Pagination :pagination="pagination" @update:page="getAllIVRRequests" />
			</template>
		</div>

		<!-- User Details Modal -->
		<BaseModal v-model="showUserDetailsModal" title="IVR Request Details">
			<template v-if="selectedIvrRequest">
				<div class="space-y-4">
					<div
						class="flex items-center bg-gradient-to-r from-green-50 to-emerald-50
						dark:from-green-900/20 dark:to-emerald-900/20
						p-4 rounded-xl border border-green-100 dark:border-green-800 shadow-sm"
					>
						<div class="p-3 bg-green-600 text-white rounded-lg shadow-md mr-3">
							<ShieldPlus class="w-6 h-6" />
						</div>

						<div class="flex-1 flex flex-col">
							<p class="text-sm text-gray-700 dark:text-gray-300">
								IVR:
								<span class="font-semibold text-green-700 dark:text-green-300">
									{{ selectedIvrRequest.ivr_number }}
								</span>
							</p>

							<span
								v-if="ivrStatus[selectedIvrRequest.ivr_status]"
								:class="[
									'mt-1 inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium w-fit whitespace-nowrap',
									ivrStatus[selectedIvrRequest.ivr_status].classes
								]"
							>
								<CircleCheck class="w-4 h-4" />
								{{ ivrStatus[selectedIvrRequest.ivr_status].label }}
							</span>
						</div>
					</div>

					<div>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<ShieldUser class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-gray-700">Patient</p>
										<p class="text-gray-900">{{ selectedIvrRequest.patient?.patient_name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Hospital class="w-5 h-5 text-green-500" />
									<div>
										<p class="text-sm text-gray-700">Clinic</p>
										<p class="text-gray-900">{{ selectedIvrRequest.clinic?.clinic_name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<CircleCheckBig class="w-5 h-5 text-green-500" />
									<div>
										<p class="text-sm text-gray-700">Eligibiltiy Status</p>
										<p
											v-if="ivrEligibilityStatus[Number(selectedIvrRequest.eligibility_status)]"
											:class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', ivrEligibilityStatus[Number(selectedIvrRequest.eligibility_status)].classes]"
										>
											{{ ivrEligibilityStatus[Number(selectedIvrRequest.eligibility_status)].label }}
										</p>
									</div>
								</div>
							</div>
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Calendar class="w-5 h-5 text-orange-500" />
									<div>
										<p class="text-sm text-gray-700">Date Submitted</p>
										<p class="text-gray-900">{{ formatDate(selectedIvrRequest.submitted_at) }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Calendar class="w-5 h-5 text-gray-500" />
									<div>
										<p class="text-sm text-gray-700">Date Verified</p>
										<p class="text-gray-900">{{ formatDate(selectedIvrRequest.verified_at) ? formatDate(selectedIvrRequest.verified_at) : 'N/A' }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
						<div class="flex items-start space-x-3">
							<NotebookPen class="w-5 h-5 text-pink-500 mt-0.5" />
							<div>
							<p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes</p>
							<p class="text-gray-900 dark:text-gray-100 whitespace-pre-line">
								{{ selectedIvrRequest.description || 'No notes provided.' }}
							</p>
							</div>
						</div>
					</div>

					<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
						<h4 class="font-medium text-blue-900 mb-3">Manufacturer Information</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Building class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-blue-700">
											<strong>Manufacturer: </strong>
										</p>
										<p class="text-sm text-blue-700">{{ selectedIvrRequest.manufacturer?.manufacturer_name || '--' }} ({{ selectedIvrRequest.manufacturer?.contact_person || '--' }})</p>
									</div>
								</div>
							</div>
							<div class="space-y-4">
								<div class="space-y-1">
									<p class="text-sm text-blue-700">
										<strong>Submitted IVR Form:</strong>
									</p>
									
									<div class="flex items-center space-x-3">
										<button
											type="button"
											v-if="selectedIvrRequest.manufacturer?.filepath" 
											@click="downloadIVRForm(selectedIvrRequest.manufacturer?.manufacturer_id)" 
											target="_blank"
											class="flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 active:bg-blue-800 transition"
										>
											<Download class="w-5 h-5" />
											Download Form
										</button>
										<span v-else class="text-sm text-gray-500">No file available</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div v-if="filePreviewUrl" class="mt-2 border rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 p-3">
						<div v-if="isImageFile(filePreviewUrl)" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
							<img 
								:src="selectedIvrRequest.filepath" 
								:alt="selectedIvrRequest.filepath"
								class="max-w-full h-auto rounded-lg shadow-md"
								
							/>
						</div>
						<div v-else-if="isPDFFile(filePreviewUrl)" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
							<iframe 
								:src="selectedIvrRequest.filepath" 
								class="w-full h-96 rounded-lg"
								frameborder="0"
							></iframe>
						</div>
						<div v-else class="text-center py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/50">
							<File class="w-16 h-16 text-gray-400 mx-auto mb-3" />
							<p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Preview not available for this file type</p>
							<p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Download to view the file</p>
						</div>
					</div>

					<div class="border-t border-gray-200 pt-6">
						<label class="text-lg font-medium text-gray-900 dark:text-white mb-4">
							<span>Override Eligibility Status</span>
						</label>
						<div class="flex items-center gap-3 w-full">
							<div class="flex space-x-3 items-center">
								<select
									:key="selectedIvrRequest.eligibility_status"
									v-model="overrideStatus"
									class="w-56 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
									bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 
									shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
									transition-all duration-150 cursor-pointer
									disabled:opacity-50 disabled:cursor-not-allowed"
								>
									<option disabled value="">-- Select Eligibility Status --</option>

									<option
										value="0"
										:disabled="selectedIvrRequest.eligibility_status === 0"
									>
										Pending
									</option>
									<option
										value="1"
										:disabled="selectedIvrRequest.eligibility_status === 1"
									>
										Eligible
									</option>
									<option
										value="2"
										:disabled="selectedIvrRequest.eligibility_status === 2"
									>
										Not Eligible
									</option>
								</select>
							</div>
							<button
								:disabled="!overrideStatus || overrideStatus === selectedIvrRequest.eligibility_status"
								@click="applyOverride"
								class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors 
									disabled:opacity-50 disabled:cursor-not-allowed"
							>
								Apply Override
							</button>
						</div>
					</div>
				</div>
			</template>
			<template #actions>
				<!-- Actions -->
				<div class="p-4 flex items-center gap-2">
					<button
						type="button"
						@click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
					>
						Cancel
					</button>
				</div>
			</template>
		</BaseModal>

		<!-- Create/Edit User Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Submit New IVR Request' : 'Edit IVR Request Details'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<userProfile class="w-5 h-5 text-green-600" />
							<span>Patient<span class="text-red-500">*</span></span>
						</label>
						<select
							v-model="formData.patient_id"
							required
							class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
						>
							<option disabled value="">-- Select Patient --</option>
							<option 
								v-for="patient in patientData" 
								:key="patient.patient_id" 
								:value="patient.patient_id"
							>
								{{ patient.patient_name }}
							</option>
						</select>
					</div>
					<div v-if="isCreateMode">
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<Package class="w-5 h-5 text-green-600" />
							<span>Manufacturer</span>
						</label>
						<select
							:disabled="!formData.patient_id"
							v-model="formData.manufacturer_id"
							class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200
							disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400"
						>
							<option disabled value="">-- Select Manufacturer --</option>
							<option 
								v-for="manufacturer in manufacturerData" 
								:key="manufacturer.manufacturer_id" 
								:value="manufacturer.manufacturer_id"
								:disabled="isCreateMode && hasExistingIVR(Number(formData.patient_id), Number(manufacturer.manufacturer_id))"
								class="disabled:opacity-40 disabled:bg-gray-200 disabled:text-gray-400"
							>
								{{ manufacturer.manufacturer_name }} {{ hasExistingIVR(Number(formData.patient_id), Number(manufacturer.manufacturer_id)) ? ' (Existing IVR)' : '' }}
							</option>
						</select>
					</div>
					<div v-else>
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<Package class="w-5 h-5 text-green-600" />
							<span>Manufacturer</span>
						</label>
						<select
							v-model="formData.manufacturer_id"
							class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200
							disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400"
						>
							<option disabled value="">-- Select Manufacturer --</option>
							<option 
								v-for="manufacturer in manufacturerData" 
								:key="manufacturer.manufacturer_id" 
								:value="manufacturer.manufacturer_id"
								:disabled="isCreateMode && hasExistingIVR(Number(formData.patient_id), Number(manufacturer.manufacturer_id))"
								class="disabled:opacity-40 disabled:bg-gray-200 disabled:text-gray-400"
							>
								{{ manufacturer.manufacturer_name }} {{ hasExistingIVR(Number(formData.patient_id), Number(manufacturer.manufacturer_id)) ? ' (Existing IVR)' : '' }}
							</option>
						</select>
					</div>
					<!-- <div v-if="showEditForm">
						<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
							<ShieldCheck class="w-5 h-5 text-green-600" />
							<span>IVR Status<span class="text-red-500">*</span></span>
						</label>
						<select
							:key="formData.eligibility_status"
							v-model="formData.eligibility_status"
							class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl 
								focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 
								text-gray-900 dark:text-white transition-all duration-200"
						>
							<option disabled value="">-- Select Eligibility Status --</option>

							<option value="0">Pending</option>
							<option value="1">Eligible</option>
							<option value="2">Not Eligible</option>
						</select>
					</div> -->
				</div>

				<transition name="fade-slide">
					<div v-if="selectedManufacturer" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
						<div>
							<h4 class="font-medium text-blue-900 mb-2">IVR Form Information</h4>
							<div class="space-y-2">
								<p class="text-sm text-blue-700">
									<strong>Manufacturer: </strong>
									<span>{{ selectedManufacturer.manufacturer_name || '--' }}</span>
								</p>
								<p class="text-sm text-blue-700">
									<strong>Form Type: </strong>
									<span>File</span>
								</p>
							</div>
							<div class="flex items-center gap-2 mt-2">
								<button
									type="button"
									v-if="selectedManufacturer.filepath"
									@click="downloadIVRForm(selectedManufacturer.manufacturer_id)"
									class="flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 active:bg-blue-800 transition"
								>
									<Download class="w-4 h-4" />
									Download Form
								</button>
								<span v-else class="text-gray-500">No file available</span>
							</div>
							<p class="shadow-md text-sm mt-4 leading-relaxed bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border-l-4 border-yellow-400 text-gray-600 dark:text-gray-300">
								<strong class="text-red-700 dark:text-red-400">Note:</strong>
								After downloading the form, please complete all required fields. Once finished, save your changes and re-upload the updated file using the upload section below.
							</p>
						</div>
					</div>
				</transition>

				<!-- IVR Information -->
				 <transition name="fade-slide">
					<div v-if="selectedManufacturer" class="relative">

						<!-- Loader Overlay -->
						<transition name="fade"> 
							<div
								v-if="isLoadingFile"
								class="absolute inset-0 bg-white/70 dark:bg-gray-900/60 backdrop-blur-sm flex flex-col items-center justify-center rounded-lg z-10"
							>
								<div class="w-60 bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden mb-3">
									<div
										class="h-full bg-purple-500 transition-all duration-100"
										:style="{ width: loadProgress + '%' }"
									></div>
								</div>

								<p class="text-sm text-gray-700 dark:text-gray-300">
									Preparing file... {{ loadProgress }}%
								</p>
							</div>
						</transition>

						<!-- Upload Box -->
						<div :class="{ 'blur-sm': isLoadingFile }">

							<div class="flex items-center gap-2 mb-2">
								<FilePenLine class="w-5 h-5 text-green-500" />
								<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">IVR Information</h3>
							</div>

							<div v-if="isCreateMode ? !selectedFile : (!existingFile && !selectedFile)"
								class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer 
									bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
								@drop="handleDrop"
								@dragover="allowDrop"
							>
								<input
									id="ivr-upload"
									type="file"
									accept=".pdf,.doc,.docx"
									class="hidden"
									@change="handleFileChange"
								/>

								<label for="ivr-upload" class="text-center cursor-pointer">
									<div class="mx-auto w-16 h-16 bg-purple-100 dark:bg-purple-900/40 rounded-full flex items-center justify-center mb-3">
										<CloudUpload class="w-8 h-8 text-purple-500" />
									</div>

									<p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
										<span class="text-purple-600 dark:text-purple-400">Click to upload</span> or drag and drop
									</p>
									<p class="text-xs text-gray-500 dark:text-gray-400">PDF or DOCX (max. 10MB)</p>
								</label>
							</div>

							<!-- File Preview -->
							<div
								v-if="selectedFile" 
								class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg"
							>
								<div class="flex items-center gap-2">
									<FileText class="w-4 h-4 text-gray-400" />
									<div>
										<p class="font-medium">{{ selectedFile.name }}</p>
										<p class="text-xs text-gray-500">
											Size: {{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB • Type: {{ selectedFile.type || 'N/A' }}
										</p>
									</div>
								</div>

								<!-- Remove Button -->
								<button 
									@click="removeFile"
									class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition"
									title="Remove file"
								>
									<X class="w-5 h-5" />
								</button>
							</div>
							
							<!-- Existing file (Edit mode only) -->
							<div
								v-if="!showCreateForm && existingFile"
								class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg"
							>
								<div class="flex items-center gap-2">
									<FileText class="w-4 h-4 text-gray-400" />
									<div>
										<p class="font-medium">{{ existingFile.name }}</p>
										<p class="text-xs text-gray-500">Current uploaded file</p>
									</div>
								</div>

								<div class="inline-flex items-center gap-1">
								<a 
									:href="existingFile.url" 
									target="_blank"
									class="text-blue-600 hover:underline"
								>
									<Eye class="w-5 h-4" />
								</a>
								<button 
									@click="removeExistingFile"
									class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition"
									title="Remove file"
								>
									<X class="w-5 h-5" />
								</button>
								</div>
							</div>
						</div>
					</div>
				</transition>
				<div>
					<label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
						<NotebookPen class="w-5 h-5 text-green-600" />
						Notes
					</label>
					<textarea
					v-model="formData.description"
					rows="4"
					class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 resize-none"
					placeholder="Additional notes for this IVR request..."
					></textarea>
				</div>
			</form>
			<template #actions>
				<!-- Actions -->
				<div class="p-4 flex items-center gap-2">
					<button
						type="button"
						@click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
					>
						Cancel
					</button>
					<button
						type="button"
						@click="handleSubmitForm"
						class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
					>
						{{ showCreateForm ? 'Submit Request' : 'Update IVR Details' }}
					</button>
				</div>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import BaseModal from '../components/common/BaseModal.vue'
import Pagination from '../components/ui/Pagination.vue'
import TableLoader from '../components/ui/TableLoader.vue'
import {
	Funnel, Search, Eye, SquarePen,
    Trash2, User as userProfile,
	Download, ShieldUser, ShieldPlus,
	Package, NotebookPen, FilePlus2,
	Hospital, Calendar, CircleCheckBig,
	Archive, ArchiveRestore, FilePenLine,
	CloudUpload, FileText, X, ShieldCheck,
	ChevronDown, FileXIcon, CircleCheck
} from 'lucide-vue-next'
import api from '../services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface IVRRequest {
	ivr_id: string
	ivr_number: string
	patient_id: string
	clinic_id: string
	brand_id: string
	manufacturer_id: string
	eligibility_status: number
	ivr_status: number
	submitted_at: string
	created_at: string
	expiryDate?: string
	notes: string
	description: string
	verified_at: string
	filepath: string

	// Nested relations
	patient?: {
		patient_id: string
		patient_name: string
		email?: string
	}
	clinic?: {
		clinic_id: string
		clinic_name: string
	}
	brand?: {
		brand_id: string
		description: string
		brand_name: string
		manufacturer?: {
			manufacturer_id: string
			manufacturer_name: string
		}
	}
	manufacturer?: {
		manufacturer_id: string
		manufacturer_name: string
		filepath: string
		contact_person?: string
		primary_email?: string | null
	}
}

interface Patient {
	patient_id: string
	patient_name: string
	clinic?: Clinic
	ivrs?: IVRRequest[]
}

interface Brand {
	brand_id: string
	brand_name: string
	description: string

	manufacturer?: Manufacturer
}

interface Manufacturer {
	manufacturer_id: string
	manufacturer_name: string
	filepath: string
	primary_email?: string
	eligibility_status: number
}

interface Clinic {
	clinic_id: string
	clinic_name: string
}

const overrideStatus = ref<number>(0);

const ivrRequest = ref<IVRRequest[]>([])
const brandData = ref<Brand[]>([])
const manufacturerData = ref<Manufacturer[]>([])
const patientData = ref<Patient[]>([])
const itemsPerPage = ref(10)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})
const tableLoader = ref(false);
const ivrStatus: Record<number, { label: string; classes: string }> = {
	0: { label: 'On the List', classes: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' },
	1: { label: 'Archive', classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' },
}

const ivrEligibilityStatus: Record<number, { label: string; classes: string }> = {
	0: { 
		label: 'Pending', 
		classes: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' 
	},
	1: { 
		label: 'Eligible', 
		classes: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
	},
	2: { 
		label: 'Not Eligible', 
		classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' 
	},
}


const searchTerm = ref('')
const statusFilter = ref('all')
const selectedIvrRequest = ref<IVRRequest | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showUserDetailsModal = ref(false)

// file handling
const isLoadingFile = ref(false)
const loadProgress = ref(0)
const selectedFile = ref<File | null>(null)

const formData = ref({
	clinic_id: '',
	brand_id: '',
	manufacturer_id: '',
	primary_email: '',
	patient_id: '',
	description: '',
	status: '',
	eligibility_status: 0,
	filepath: '',
	ivr_status: 0
})

function handleToggleStatus(id: string) {
	const userIndex = ivrRequest.value.findIndex(user => user.ivr_id === id)
	if (userIndex !== -1) {
		ivrRequest.value[userIndex].ivr_status = ivrRequest.value[userIndex].ivr_status === 1 ? 0 : 1
	}
}

function showIvrDetails(ivr: IVRRequest){
	showUserDetailsModal.value = true
	formData.value.eligibility_status = ivr.eligibility_status
}

async function editIVR(ivr: IVRRequest) {
	selectedIvrRequest.value = ivr

	formData.value = {
		patient_id: ivr.patient_id,
		clinic_id: ivr.clinic_id,
		brand_id: ivr.brand_id,
		description: ivr.description,
		status: ivr.ivr_status.toString(),
		manufacturer_id: ivr.manufacturer?.manufacturer_id || '',
		eligibility_status: ivr.eligibility_status,
		filepath: ivr.filepath || '',
		ivr_status:ivr.ivr_status || 0,
		primary_email: '',
	}
	
	await nextTick();

	showCreateForm.value = false
	showUserDetailsModal.value = false
	showEditForm.value = true
}

async function confirmDelete(ivr: IVRRequest) {	
	try {
		const result = await Swal.fire({
			title: "Deleting " + ivr.ivr_number,
			text: "You won't be able to revert this!",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: "Yes, delete it!"
		});

		if (result.isConfirmed) {
			await api.put(`/management/delete/${ivr.ivr_id}/deleteivrrequest`);
			
			await Swal.fire({
				title: "Deleted!",
				text: "IVR Request has been deleted.",
				icon: "success"
			});

			toast.success('IVR deleted successfully!');
			await getAllIVRRequests();
		}
	} catch (error) {
		toast.error('Failed to delete IVR.');
	}
}

async function confirmArchive(ivr: IVRRequest) {
	try {
		const isArchived = ivr.ivr_status === 1;

		const result = await Swal.fire({
			title: `${isArchived ? 'Unarchiving' : 'Archiving'} ${ivr.ivr_number}`,
			text: isArchived
				? 'This IVR request will be restored to the list.'
				: 'This IVR request will be archived',
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: isArchived ? 'Yes, unarchive it!' : 'Yes, archive it!'
		});

		if (result.isConfirmed) {
			const endpoint = isArchived
				? `/management/archive/${ivr.ivr_id}/unarchiveivrrequest`
				: `/management/archive/${ivr.ivr_id}/archiveivrrequest`;

			await api.put(endpoint);
			
			await Swal.fire({
				title: isArchived ? 'Unarchived!' : 'Archived!',
				text: `IVR Request has been ${isArchived ? 'unarchived' : 'archived'}.`,
				icon: 'success'
			});

			toast.success(`IVR ${isArchived ? 'unarchived' : 'archived'} successfully!`);
			await getAllIVRRequests();
		}
	} catch (error) {
		toast.error(`Failed to ${ivr.ivr_status === 1 ? 'unarchive' : 'archive'} IVR.`);
	}
}

async function handleSubmitForm() {	
	try {
		const payload = new FormData()

		payload.append('patient_id', formData.value.patient_id)
		payload.append('brand_id', formData.value.brand_id || '')
		payload.append('notes', formData.value.description)
		payload.append('clinic_id', formData.value.clinic_id)
		payload.append('manufacturer_id', formData.value.manufacturer_id)
		payload.append('eligibility_status', formData.value.eligibility_status.toString())
		payload.append('primary_email', formData.value.primary_email)

		console.log('payload: ');
		console.log(payload);

		if (selectedFile.value) {
			payload.append('filepath', selectedFile.value)
		} else {
			toast.error('IVR Information Required.')
			return;
		}

		Swal.fire({
			title: 'Processing Request',
			text: 'Please wait while we submit your request…',
			allowOutsideClick: false,
			allowEscapeKey: false,
			showConfirmButton: false,
			didOpen: () => {
				Swal.showLoading()
			}
		})

		if (showCreateForm.value) {
			const { data } = await api.post(
				'/management/add/newivrrequest',
				payload,
				{
					headers: {
						'Content-Type': 'multipart/form-data',
						'Accept': 'application/json',
					}
				}
			)

			Swal.close()
			toast.success(data.message || 'IVR Request added successfully!')
			await getAllIVRRequests()
			closeForm()
		} else if (showEditForm.value) {
			const { data } = await api.post(
                `/management/update/${selectedIvrRequest.value?.ivr_id}/updateivr`,
                payload,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json',
                    }
                }
         	)

			Swal.close()
			toast.success(data.message || 'IVR Request Updated Successfully!')
			await getAllIVRRequests()
			resetFileState()
		}
		closeForm()
	} catch (err: unknown) {
		Swal.close()
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

async function applyOverride() {
	if (!overrideStatus.value) return;

	const statusNumber = {
			0: 'Pending',
			1: 'Eligible',
			2: 'Not Eligible',
		}[overrideStatus.value];
	
	const result = await Swal.fire({
		title: "Override Eligiblity Status?",
		text: `Set status to "${statusNumber}"`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Yes, override",
		cancelButtonText: "Cancel",
		confirmButtonColor: "#d33"
	});

	if (!result.isConfirmed) return;

	try {

		await api.put(
			`/management/update/${selectedIvrRequest.value?.ivr_id}/updateivreligiblity`,
			{ eligibility_status: overrideStatus.value }
		);

		toast.success("Order status overridden successfully!");
		overrideStatus.value = 0;
		await getAllIVRRequests(1);
		closeForm();
	} catch (error) {
		console.error(error);
		toast.error("Failed to override status.");
	}
}

function closeForm() {
	showCreateForm.value = false
	showEditForm.value = false
	showUserDetailsModal.value = false
	selectedIvrRequest.value = null
	clearForm()
}

function clearForm(){
	formData.value = {
		clinic_id: '',
		brand_id: '',
		manufacturer_id: '',
		primary_email: '',
		patient_id: '',
		description: '',
		status: '',
		eligibility_status: 0,
		filepath: '',
		ivr_status: 0,
	}
}

function resetFileState() {
    selectedFile.value = null;
    loadProgress.value = 0;
    isLoadingFile.value = false;
}

const isCreateMode = computed(() => showCreateForm.value);

const filteredIVRRequest = computed(() => {
	const statusMap: Record<'pending' | 'eligible' | 'not_eligible', number> = {
		pending: 0,
		eligible: 1,
		not_eligible: 2,
	};

    return ivrRequest.value.filter(ivr => {
        const patientName = ivr.patient?.patient_name || '';
        const email = ivr.patient?.email || '';
        const clinicName = ivr.clinic?.clinic_name || '';
        const brandDesc = ivr.brand?.description || '';
        const matchesSearch = patientName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              email.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              clinicName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                              brandDesc.toLowerCase().includes(searchTerm.value.toLowerCase());

		const key = statusFilter.value as keyof typeof statusMap;

		const matchesEligibilityStatus =
		statusFilter.value === 'all' ||
		Number(ivr.eligibility_status) === statusMap[key];

		return matchesSearch && matchesEligibilityStatus;
    });
});

const showFormModal = computed({
	get: () => showCreateForm.value || showEditForm.value,
	set: (value: boolean) => {
		if (!value) {
			showCreateForm.value = false
			showEditForm.value = false
			selectedIvrRequest.value = null
		}
	}
})

const formatDate = (dateStr: string) => {
	if (!dateStr) return null;
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	})
}

const formatDateTime = (dateStr: string) => {
	if (!dateStr) return '--';

	const date = new Date(dateStr);

	const month = date.toLocaleString('en-US', { month: 'short' }) + '.';
	const formattedDate = `${month} ${date.getDate()}, ${date.getFullYear()}`;

	const formattedTime = date.toLocaleTimeString('en-US', {
		hour: 'numeric',
		minute: '2-digit',
		hour12: true
	});

	return `${formattedDate} [${formattedTime}]`;
};

const selectedBrand = computed(() => {
	return brandData.value.find(b => b.brand_id === formData.value.brand_id)
})

const selectedManufacturer = computed(() => {
	return manufacturerData.value.find(m => m.manufacturer_id === formData.value.manufacturer_id)
})

const selectedPatient = computed(() => {
	return patientData.value.find(p => p.patient_id === formData.value.patient_id)
})

const simulateLoading = () => {
	isLoadingFile.value = true
	loadProgress.value = 0

	const interval = setInterval(() => {
		if (loadProgress.value >= 100) {
			clearInterval(interval)
			isLoadingFile.value = false
			return
		}
		loadProgress.value += 10
	}, 80)
}

const handleFileChange = (event: any) => {
	const file = event.target.files[0]
	if (!file) return

	selectedFile.value = file
	simulateLoading()
}

const handleDrop = (event: any) => {
	event.preventDefault()
	const file = event.dataTransfer.files[0]
	if (!file) return

	selectedFile.value = file
	simulateLoading()
}

const allowDrop = (event: any) => {
	event.preventDefault()
}

const removeFile = () => {
	selectedFile.value = null
	loadProgress.value = 0
	isLoadingFile.value = false
}

const removeExistingFile = () => {
	formData.value.filepath = '';
	selectedFile.value = null
	loadProgress.value = 0
	isLoadingFile.value = false
}

const existingFile = computed(() => {
    return formData.value.filepath ? {
        name: formData.value.filepath.split('/').pop(),
        url: formData.value.filepath
    } : null
})

// get all patient
async function getAllPatients() {
    tableLoader.value = true
    try {
        const { data } = await api.get(`/management/patients/patientinfo`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        patientData.value = data.patient_data
    } catch (error) {
        console.error('Error fetching clinic users:', error)
    } finally {
        tableLoader.value = false
    }
}

// get all IVR Request
async function getAllIVRRequests(page= 1){
	tableLoader.value = true
    try {
        const { data } = await api.get(`/management/ivr/ivrrequests?page=${page}&per_page=${itemsPerPage.value}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        ivrRequest.value = data.data
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

// get all Brands
async function getAllBrands(){
	tableLoader.value = true
    try {
        const { data } = await api.get(`/management/ivr/getbrands`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        brandData.value = data.brand_data		
    } catch (error) {
        console.error('Error fetching brands:', error)
    } finally {
        tableLoader.value = false
    }
}

// get all Manufacturers
async function getAllManufacturers(){
	tableLoader.value = true
    try {
        const { data } = await api.get(`/management/manufacturer/getmanufacturers`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        manufacturerData.value = data.manufacturer_data
    } catch (error) {
        console.error('Error fetching brands:', error)
    } finally {
        tableLoader.value = false
    }
}

// get all Clinics
async function getAllClinics(page= 1){
	tableLoader.value = true
    try {
        const { data } = await api.get(`/management/ivr/getclinics?page=${page}&per_page=${itemsPerPage.value}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

        ivrRequest.value = data.user_data
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

const downloadIVRForm = async (id: string) => {
	try {
		const response = await api.get(`/management/ivr/download/${id}/downloadivrform`, {
			responseType: 'blob',
		});

		const blob = new Blob([response.data]);
		const link = document.createElement('a');
		link.href = URL.createObjectURL(blob);
		link.download = 'IVR_Form.pdf';
		link.click();
		URL.revokeObjectURL(link.href);
	} catch (error) {
		console.error('Download failed:', error);
	}
};

const hasExistingIVR = (patientId: number, manufacturerId: number) => {
	if (!patientId || !manufacturerId) return false

	return ivrRequest.value?.some(
		ivr =>
			Number(ivr.patient_id) === Number(patientId) &&
			Number(ivr.manufacturer_id) === Number(manufacturerId)
	)
}

const filePreviewUrl = computed(() => {
    if (!selectedIvrRequest.value?.manufacturer?.filepath) return null;
    return `/storage/${selectedIvrRequest.value.manufacturer.filepath}`;
});

function isImageFile(filename: string) {
    if (!filename) return false
    const ext = filename.split('.').pop()?.toLowerCase() || ''
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)
}

function isPDFFile(filename: string) {
    if (!filename) return false
    return filename.toLowerCase().endsWith('.pdf')
}

onMounted(async () => {
    getAllPatients()
    getAllIVRRequests(1)
    getAllBrands()
	getAllManufacturers()
    getAllClinics(1)
})

watch(selectedManufacturer, (manufacturer) => {
	if (manufacturer) {
		formData.value.manufacturer_id = manufacturer.manufacturer_id
		formData.value.primary_email = manufacturer.primary_email ?? ''
		console.log(formData.value.primary_email);
		formData.value.brand_id = selectedIvrRequest.value?.brand_id ?? '';
		
	} else {
		formData.value.manufacturer_id = ''
	}
})
watch(selectedManufacturer, (manufacturer) => {
	if (manufacturer) {
		formData.value.manufacturer_id = manufacturer.manufacturer_id
		formData.value.primary_email = manufacturer.primary_email ?? ''
		console.log(formData.value.primary_email);
		formData.value.brand_id = selectedIvrRequest.value?.brand_id ?? '';
		
	} else {
		formData.value.manufacturer_id = ''
	}
})

watch(selectedPatient, (patient) => {
	const clinic = patient?.clinic;
	const ivr = patient?.ivrs?.[0];

	console.log('clinic: ' + clinic?.clinic_id);	

	if (clinic) {
		formData.value.clinic_id = clinic.clinic_id;
	} else {
		formData.value.clinic_id = '';
	}

	if (ivr && typeof ivr.eligibility_status === 'number') {
		formData.value.eligibility_status = ivr.eligibility_status;
	} else {
		formData.value.eligibility_status = 0;
	}
});

watch(() => formData.value.manufacturer_id, (newVal) => {
	if (!newVal || !formData.value.patient_id) return

	const patientId = Number(formData.value.patient_id)
	const manufacturerId = Number(newVal)

	if (hasExistingIVR(patientId, manufacturerId) && showCreateForm.value) {
		toast.error("This patient already has an IVR for this manufacturer.")
		formData.value.manufacturer_id = ''
	}
})

watch(itemsPerPage, () => {
    getAllPatients()
	getAllIVRRequests(1)
    getAllBrands()
    getAllClinics(1)
})

watch(
	() => selectedIvrRequest.value,
	(newVal) => {
		if (!newVal || isCreateMode.value) return;

		formData.value.brand_id = newVal.brand_id?.toString() || '';
	},
	{ immediate: true }
);
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

.fade-slide-enter-active,
.fade-slide-leave-active {
    @apply transition-all duration-300 ease-out;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-slide-enter-to {
    opacity: 1;
    transform: translateY(0);
}

.fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style> 