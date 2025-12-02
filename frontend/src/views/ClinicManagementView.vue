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
					closeForm(); 
					showCreateForm = true
				"
				class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group"
			>
				<HousePlus class="w-4 h-4 mr-2" />
				Add Clinic
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col lg:flex-row gap-6">
				<div class="flex-1">
					<div class="relative">
						<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search Clinics..."
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
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
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
						<option value="9">9</option>
						<option value="25">25</option>
						<option value="50">50</option>
					</select>
					<ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
				</div>
			</div>
		</div>

		<!-- Card View -->
		<ClinicCards
			:users="filteredUsers"
			:user-status="userStatus"
			:table-loader="tableLoader"
			:pagination="pagination"
			@view-clinic="selectedUser = $event"
			@edit-clinic="editUser"
			@toggle-status="handleToggleStatus"
			@delete-clinic="confirmDelete"
			@archive-clinic="confirmArchive"
			@unarchive-clinic="confirmUnarchive"
			@update:page="getAllClinics"
		/>

		<!-- Clinic Details Modal -->
		<BaseModal v-model="showUserDetailsModal" title="Clinic Details">
			<template v-if="selectedUser">
				<div class="space-y-4">
					<div class="grid grid-cols-2 gap-4">
						<div class="flex items-center space-x-4">
							<div v-if="selectedUser.logo" class="w-16 h-16 rounded-full overflow-hidden bg-gray-100">
                                <img :src="selectedUser.logo" :alt="`${selectedUser.name} logo`" class="w-full h-full object-cover border" />
                            </div>
							<div v-else class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
								<Hospital class="w-8 h-8 text-green-500" />
							</div>
							<div>
								<p class="text-xl font-semibold text-gray-900">{{ selectedUser.name }}</p>
								<span
									v-if="userStatus[selectedUser.isActive]"
									:class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', userStatus[selectedUser.isActive].classes]"
								>
									{{ userStatus[selectedUser.isActive].label }}
								</span>
							</div>
						</div>
					</div>

					<div>
						<h4 class="text-lg text-gray-900 mb-4">Clinic Contact Information</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<MapPin class="w-5 h-5 text-blue-500" />
									<div>
										<p class="text-sm text-gray-700">Address</p>
										<p class="text-gray-900">{{ selectedUser.address }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Phone class="w-5 h-5 text-green-500" />
									<div>
										<p class="text-sm text-gray-700">Contact Number</p>
										<p class="text-gray-900">{{ selectedUser.phone }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<Mail class="w-5 h-5 text-purple-500" />
									<div>
										<p class="text-sm text-gray-700">Email</p>
										<p class="text-gray-900">{{ selectedUser.email }}</p>
									</div>
								</div>
							</div>
							<div class="space-y-4">
								<div class="flex items-center space-x-3">
									<Contact class="w-5 h-5 text-orange-500" />
									<div>
										<p class="text-sm text-gray-700">Contact Person</p>
										<p class="text-gray-900">{{ selectedUser.contactPerson }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<FileText class="w-5 h-5 text-indigo-500" />
									<div>
										<p class="text-sm text-gray-700">Clinic ID</p>
										<p class="text-gray-900">{{ selectedUser.clinicId ?? "N/A" }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3">
									<FileText class="w-5 h-5 text-red-500" />
									<div>
										<p class="text-sm text-gray-700">Clinic Public ID</p>
										<p class="text-gray-900">{{ selectedUser.clinicPubId ?? "N/A" }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- <div class="border rounded-lg">
						<button
							@click="showUsers = !showUsers"
							class="w-full flex justify-between items-center px-4 py-3 text-left bg-gray-100 rounded-lg hover:bg-gray-200"
						>
							<h4 class="text-lg font-medium text-gray-900">
							Associated Users ({{ selectedUser.assigned_clinician_ids?.length || 0 }})
							</h4>
							<ChevronUp 
							:class="{ 'rotate-180': showUsers }"
							class="w-5 h-5 text-gray-600 transform transition-transform duration-200"
							/>
						</button>

						<transition
							enter-active-class="transition ease-out duration-200"
							enter-from-class="opacity-0 max-h-0"
							enter-to-class="opacity-100 max-h-screen"
							leave-active-class="transition ease-in duration-150"
							leave-from-class="opacity-100 max-h-screen"
							leave-to-class="opacity-0 max-h-0"
						>
							<div v-show="showUsers" class="p-4 bg-gray-50">
								<div v-if="selectedUser.assigned_clinician_ids?.length" class="overflow-x-auto">
									<table class="min-w-full bg-white border border-gray-200 rounded-lg">
									<thead class="bg-gray-50">
										<tr>
										<th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
										<th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Email</th>
										<th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Phone</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="clinician in selectedUser.assigned_clinician_ids" :key="clinician.id" class="border-t text-start">
										<td class="px-4 py-2 text-sm text-gray-800">{{ clinician.name }}</td>
										<td class="px-4 py-2 text-sm text-gray-800">{{ clinician.email }}</td>
										<td class="px-4 py-2 text-sm text-gray-800">{{ clinician.phone ?? 'N/A' }}</td>
										</tr>
									</tbody>
									</table>
								</div>

								<p v-else class="text-center py-8 bg-gray-50 rounded-lg text-gray-600">
									<Users class="w-12 h-12 text-gray-400 mx-auto mb-2" />
									No users associated with this clinic
								</p>
							</div>
						</transition>
					</div> -->

					<!-- <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
						<h4 class="font-medium text-blue-900 mb-3">Clinic Statistics</h4>
						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 place-items-center text-center">
							<div class="flex flex-col items-center justify-center h-24 bg-white rounded-lg shadow-sm w-full max-w-[14rem]">
								<p class="text-2xl font-bold text-blue-900">{{ totalUsers }}</p>
								<p class="text-sm text-blue-700">Total Clinicians</p>
							</div>
							<div class="flex flex-col items-center justify-center h-24 bg-white rounded-lg shadow-sm w-full max-w-[14rem]">
								<p class="text-2xl font-bold text-blue-900">{{ activeCliniciansCount }}</p>
								<p class="text-sm text-blue-700">Active Clinicians</p>
							</div>
							<div class="flex flex-col items-center justify-center h-24 bg-white rounded-lg shadow-sm w-full max-w-[14rem]">
								<p class="text-2xl font-bold text-blue-900">{{ userStatus[selectedUser.isActive].label }}</p>
								<p class="text-sm text-blue-700">Status</p>
							</div>
						</div>
					</div> -->
				</div>
			</template>
			<template #actions>
				<!-- Actions -->
				<div class="p-4">
					<button
						type="button"
						@click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
					>
						Close
					</button>
				</div>
			</template>
		</BaseModal>

		<!-- Create/Edit Clinic Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Clinic' : 'Edit Clinic'" @close-form="closeForm">
			<form @submit.prevent="handleSubmitForm" class="space-y-6">

				<!-- Clinic Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<HousePlus class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Clinic Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span class="text-red-500">*</span></label>
							<div class="relative">
								<Building class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
									v-model="formData.name"
									type="text"
									required
									placeholder="Clinic Name"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Email<span class="text-red-500">*</span></label>
							<div class="relative">
								<Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
									v-model="formData.email"
									type="email"
									required
									placeholder="example@clinic.com"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Clinic Public Code</label>
							<div class="relative">
								<FileKey class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
									v-model="formData.publicId"
									type="text"
									placeholder="Optional code"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status<span class="text-red-500">*</span></label>
							<div class="relative">
								<CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select
									v-model="formData.isActive"
									required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option :value="false">Active</option>
									<option :value="true">Inactive</option>
								</select>
							</div>
						</div>
						<div v-if="!showCreateForm">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Clinic Code</label>
							<div class="relative">
								<Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
									v-model="formData.clinicId"
									type="text"
									placeholder="Optional code"
									disabled
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
					</div>
				</div>

				<!-- Contact Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<Contact class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Contact Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Person<span class="text-red-500">*</span></label>
							<div class="relative">
								<UserProfile class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
									v-model="formData.contactPerson"
									type="text"
									required
									placeholder="Contact Person"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Number<span class="text-red-500">*</span></label>
							<div class="relative">
								<Phone class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
									v-model="formData.phone"
									type="text"
									required
									placeholder="Phone/Tel"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>
					</div>
				</div>

				<!-- Assign Clinicians -->
				<!-- <div>
					<div class="flex items-center gap-2 mb-2">
						<UserPlus class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Assign Clinicians</h3>
					</div>

					<div class="relative">
						<div
						@click="isOpen = !isOpen"
						class="flex flex-wrap items-center gap-1 border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700 cursor-pointer"
						>
							<span
								v-for="(c, index) in formData.assigned_clinic_ids"
								:key="index"
								class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-sm flex items-center gap-1"
							>
								{{ c.name }}
								<button @click.stop="removeClinician(index)" class="text-xs">✕</button>
							</span>
							<span v-if="!formData.assigned_clinic_ids.length" class="text-gray-400 ">Select clinicians...</span>
						</div>

						<div
							v-show="isOpen"
							class="absolute mt-1 w-full bg-white dark:bg-gray-700 rounded shadow-lg z-10"
							>
							<ul
								v-show="isOpen"
								class="absolute mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-gray-300 bg-white dark:bg-gray-700 shadow-lg z-10"
								>
								<li
									v-for="c in clinicianOptions"
									:key="c.id"
									@click="selectClinician(c)"
									class="px-3 py-2 hover:bg-blue-100 dark:hover:bg-gray-600 cursor-pointer"
								>
									{{ c.name }} - {{ roleLabels[c.user_role ?? 0] || 'Unknown' }}
								</li>
							</ul>
						</div>
					</div>
				</div> -->

				<!-- Address Input -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<MapPin class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Address</h3>
					</div>

					<div class="relative">
						<Map class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
						<input
							v-model="formData.address"
							type="text"
							required
							placeholder="Clinic Address"
							class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
				</div>

				<!-- Logo Upload Section (NEW: Dedicated field for logo upload) -->
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
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <Image class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Clinic Logo (Optional)</h3>
                    </div>
                    <div v-if="!selectedLogoFile && !formData.logo"
                        class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer
                            bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
                        @drop="handleLogoDrop"
                        @dragover="allowLogoDrop"
                    >
                        <input
                            id="logo-upload"
                            type="file"
                            accept="image/png,image/jpeg,image/jpg"
                            class="hidden"
                            @change="handleLogoChange"
                        />
                        <label for="logo-upload" class="flex flex-col items-center justify-center text-center cursor-pointer">
							<UploadCloud class="w-10 h-10 mb-3 text-gray-400" />
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-semibold text-purple-600 dark:text-purple-400">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">JPEG, JPG, or PNG (max. 2MB)</p>
                        </label>
                    </div>
                    <div v-if="selectedLogoFile" class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
						<div class="flex items-center gap-2">
							<Image class="w-4 h-4 text-gray-400" />
							<span>Selected: <span class="font-medium">{{ selectedLogoFile.name }}</span> <img :src="formData.logo" class="w-6 h-6 rounded object-cover inline ml-1" /></span>
						</div>
						<button
							@click="removeCurrentLogo"
							class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition"
							title="Remove file"
						>
							<X class="w-5 h-5" />
						</button>
                    </div>
                    <div v-else-if="formData.logo" class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
						<div class="flex items-center gap-2">
							<Image class="w-4 h-4 text-gray-400" />
                        	<span>Current: {{ formData.logo.split('/').pop() }}</span> <img :src="formData.logo" class="w-6 h-6 rounded object-cover inline ml-1" />
						</div>
                        <button
							@click="removeCurrentLogo"
							class="ml-2 text-red-500 hover:text-red-700 text-xs"
						>
							<X class="w-5 h-5" />
						</button>
                    </div>
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
						{{ showCreateForm ? 'Save Clinic' : 'Update Clinic' }}
					</button>
				</div>
			</template>
		</BaseModal>

		<BaseModal v-model="showLogoCropModal" title="Crop Logo" max-width="520px">
			<div class="p-6 space-y-6">
				<!-- Instructions -->
				<div class="text-center">
					<p class="text-sm text-gray-600 dark:text-gray-400">
						Adjust your logo to fit perfectly. Drag to reposition and use the zoom controls below.
					</p>
				</div>

				<!-- Crop Area Container -->
				<div class="relative mx-auto w-full max-w-[320px] aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700">
					<!-- Canvas Container with subtle grid background -->
					<div class="absolute inset-0 opacity-20">
						<div class="w-full h-full bg-grid-pattern bg-[length:20px_20px]"></div>
					</div>
					
					<canvas
						ref="logoCanvas"
						@mousedown="logoStartPan"
						@mousemove="logoHandlePan"
						@mouseup="logoEndPan"
						@mouseleave="logoEndPan"
						@wheel="logoHandleZoom"
						@touchstart="logoStartTouchPan"
						@touchmove="logoHandleTouchPan"
						@touchend="logoEndTouchPan"
						@touchcancel="logoEndTouchPan"
						class="absolute inset-0 w-full h-full touch-none cursor-grab active:cursor-grabbing select-none transition-transform duration-150"
						:class="{ 'cursor-grabbing': logoIsPanning }"
					></canvas>

					<!-- Circular Crop Overlay with modern design -->
					<div class="absolute inset-0 pointer-events-none">
						<!-- Outer shadow/mask -->
						<div class="absolute inset-0 bg-black/30 backdrop-blur-[1px]"></div>
						
						<!-- Circular crop window -->
						<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-4/5 h-4/5">
							<!-- Border with glow effect -->
							<div class="absolute inset-0 border-2 border-white/80 rounded-full shadow-[0_0_0_1px_rgba(255,255,255,0.3),0_0_20px_rgba(0,0,0,0.2)]"></div>
							
							<!-- Corner indicators -->
							<div class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-white rounded-tl"></div>
							<div class="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-white rounded-tr"></div>
							<div class="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-white rounded-bl"></div>
							<div class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-white rounded-br"></div>
						</div>

						<!-- Help text -->
						<div class="absolute bottom-4 left-1/2 -translate-x-1/2">
							<div class="flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-sm rounded-full">
								<FoldVertical class="w-3 h-3 text-white/80" />
								<span class="text-xs text-white/80 font-medium">Drag to move • Scroll to zoom</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Controls Section -->
				<div class="space-y-6">
					<!-- Zoom Controls -->
					<div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
						<div class="flex items-center justify-between mb-3">
							<label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Zoom Level</label>
							<span class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
								{{ Math.round(logoScale * 100) }}%
							</span>
						</div>
						
						<div class="flex items-center gap-4">
							<button 
								@click="logoZoomOut"
								:disabled="logoScale <= 0.5"
								class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow"
							>
								<Minus class="w-5 h-5 text-gray-600 dark:text-gray-400" />
							</button>
							<span class="text-xs text-gray-500">50%</span>
							<div class="flex-1 relative">
								<input
									type="range"
									min="0.5"
									max="3"
									step="0.1"
									v-model="logoScale"
									@input="drawLogoCanvas"
									class="w-full h-2 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-lg appearance-none cursor-pointer slider-thumb"
								/>
							</div>
							<span class="text-xs text-gray-500">300%</span>
							
							<button 
								@click="logoZoomIn"
								:disabled="logoScale >= 3"
								class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow"
							>
								<Plus class="w-5 h-5 text-gray-600 dark:text-gray-400" />
							</button>
						</div>
					</div>

					<!-- Action Buttons -->
					<div class="flex flex-wrap gap-3 justify-center">
						<button
							@click="logoResetPosition"
							class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow"
						>
							<RefreshCcw class="w-4 h-4" />
							Reset View
						</button>
						
						<button
							@click="logoSelectNewImage"
							class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200 shadow-sm hover:shadow"
						>
							<Image class="w-4 h-4" />
							Change Image
						</button>
					</div>
				</div>
			</div>

			<!-- Modal Footer -->
			<template #actions>
				<div class="flex justify-end gap-3 px-6 pb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
					<button
						@click="logoCancelCrop"
						class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow"
					>
						Cancel
					</button>
					<button
						@click="logoConfirmCrop"
						class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.02]"
					>
						<Check class="w-4 h-4" />
						Apply Crop
					</button>
				</div>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
// import TableLoader from '@/components/ui/TableLoader.vue'
import ClinicCards from '@/components/Clinic/ClinicCards.vue'
import axios from 'axios'
import {
    Search, Funnel, CircleCheck, Hospital,
	MapPin, Phone, Mail, HousePlus,
	Contact, Building, Hash, User as UserProfile,
	Map, FileKey, FileText, ChevronDown,
	UploadCloud, FoldVertical, Minus, Plus,
	RefreshCcw, Image, Check, X
} from 'lucide-vue-next';
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

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
	logo?: string | null
}

interface Clinician {
	id: string;
	name: string;
	email: string;
	phone?: string;
	user_role?: number;
}

interface Clinic{
	id: number
	name: string
	user_role: number
}

const isLoadingFile = ref(false)
const loadProgress = ref(0)
const allowedLogoTypes = ['image/png','image/jpeg','image/jpg']
const selectedLogoFile = ref<File | null>(null)
const logoObjectUrl = ref<string | null>(null)
const removeLogoFlag = ref(false)
function allowDrop(event: any) { event.preventDefault() }
function allowLogoDrop(event: any) { event.preventDefault() }

const showUsers = ref(false)
const isOpen = ref(false);
const clinicianOptions = ref<Clinician[]>([]);
const selectedClinicians = computed(() => formData.value.assigned_clinic_ids);

const roleLabels: Record<number, string> = {
	1: "Admin",
	2: "Manager",
	3: "Clinician",
	4: "Nurse",
	5: "Doctor",
};

// Pagination
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 9,
	total: 0,
})
const itemsPerPage = ref(9)
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
	address: '',
	assigned_clinic_ids: [] as Clinician[],
	publicId: '',
	logo: '',
})

async function handleToggleStatus(clinicId: string) {
    try {
        const response = await api.put(`/management/facilities/clinics/${clinicId}/updatestatus`);

        if (response.data.success) {
			const idx = users.value.findIndex(user => user.id === clinicId);
			if (idx !== -1) {
				users.value[idx].isActive = response.data.status;
			}
			getAllClinics();
		}

		toast.success(response.data.message || 'Status updated successfully!')
    } catch (err) {
        console.error('Error toggling status:', err);
    }
}

const editUser = (user: User) => {
    showUserDetailsModal.value = false;
    showCreateForm.value = false;
    showEditForm.value = true;
    selectedUser.value = user;

	formData.value = {
		name: user.name,
		email: user.email,
		contactPerson: user.contactPerson,
		isActive: !!user.isActive,
		clinicId: user.clinicId ?? '',
		clinicPubId: user.clinicPubId ?? '',
		phone: user.phone ?? '',
		address: user.address ?? '',
		assigned_clinic_ids: [...user.assigned_clinician_ids],
		publicId: user.clinicPubId ?? '',
		password: '',
		logo: user.logo ?? '',
	};

	selectedLogoFile.value = null;
};


async function handleSubmitForm() {
	try {
		const form = new FormData()
		form.append('name', formData.value.name)
		form.append('email', formData.value.email)
		form.append('publicId', formData.value.publicId)
		form.append('contactPerson', formData.value.contactPerson)
		form.append('isActive', formData.value.isActive.toString())
		form.append('clinicId', formData.value.clinicId)
		form.append('phone', formData.value.phone)
		form.append('address', formData.value.address)
		// form.append('assigned_clinicians_id', selectedClinicians.value.map(c => Number(c.id)))

		if (selectedLogoFile.value) {
			form.append('logo', selectedLogoFile.value)
		}
		if (removeLogoFlag.value) {
			form.append('remove_logo', '1')
		}

		if (showCreateForm.value) {
			const { data } = await api.post(
				'/management/facilities/clinics',
				form,
				{
					headers: {
						'Content-Type': 'multipart/form-data',
						'Accept': 'application/json',
					}
				}
			)

			toast.success(data.message || 'Clinic added successfully!')
			await getAllClinics()

		} else if (showEditForm.value && selectedUser.value) {
			const { data } = await api.put(
                `/management/facilities/clinics/${selectedUser.value.id}/update`,
                form,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                }
         	)

			toast.success(data.message || 'Clinic Updated Successfully!')
			await getAllClinics()
		}
		
		closeForm()
	} catch (error: unknown) {
		if (axios.isAxiosError(error)) {
			const status = error.response?.status
			const data = error.response?.data

			if (status === 422 && data?.errors) {
				const messages = Object.values(data.errors).flat()
				toast.error("Error: " + messages.join("\n"))
			} else {
				toast.error(data?.message || `Request failed with status code ${status}`)
			}
		} else if (error instanceof Error) {
			toast.error("Error: " + error.message)
		} else {
			toast.error("Something went wrong")
		}
	}
}

function closeForm() {
	showCreateForm.value = false
	showEditForm.value = false
	selectedUser.value = null
	selectedLogoFile.value = null

	formData.value = {
		name: '',
		email: '',
		contactPerson: '',
		isActive: true,
		clinicId: '',
		clinicPubId: '',
		password: '',
        phone: '',
		address: '',
		assigned_clinic_ids: [],
		publicId: '',
		logo: '',
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

const totalUsers = computed(() => {
	return selectedUser.value?.assigned_clinician_ids?.length || 0;
});

const activeCliniciansCount = computed(() => {
	return selectedUser.value?.assigned_clinician_ids?.length || 0;
});

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

async function fetchClinicianOptions() {
    const { data } = await api.get('/management/users/clinician?simple=true', {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        },
    });
    clinicianOptions.value = data.map((c: any) => ({
        id: String(c.id),
        name: c.name,
        email: c.email ?? '', // fallback if missing
        phone: c.phone ?? '',
        user_role: c.user_role,
    }));
}

function selectClinician(c: Clinician) {
    if (!formData.value.assigned_clinic_ids.find(cl => cl.id === c.id)) {
        formData.value.assigned_clinic_ids.push(c);
    }
    isOpen.value = false;
}

function removeClinician(index: number) {
	formData.value.assigned_clinic_ids = [
		...formData.value.assigned_clinic_ids.slice(0, index),
		...formData.value.assigned_clinic_ids.slice(index + 1)
	];
}

async function confirmDelete(userId: string) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This action cannot be undone.",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Yes, delete it!"
	});

    if (result.isConfirmed && userId) {
        try {
            await api.put(`/management/facilities/clinics/${userId}/deleteclinic`)
            toast.success('Clinic deleted successfully!')
            await getAllClinics()
        } catch (error) {
            toast.error('Failed to delete clinic.')
        }
    }
}

async function confirmArchive(userId: string) {
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This action cannot be undone.",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Yes, archive it!"
	});

	if (result.isConfirmed && userId) {
		try {
			await api.put(`/management/facilities/clinics/${userId}/archiveclinic`)
			toast.success('Clinic archived successfully!')
			await getAllClinics()
		} catch (error) {
			toast.error('Failed to archive clinic.')
		}
	}
}

async function confirmUnarchive(userId: string) {
	console.log('unarchvie: ' + userId);
	
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This will restore the clinic.",
		icon: "question",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Yes, unarchive it!"
	});

	if (result.isConfirmed && userId) {
		try {
			await api.put(`/management/facilities/clinics/${userId}/unarchiveclinic`);
			toast.success("Clinic restored successfully!");
			await getAllClinics();
		} catch (error) {
			toast.error("Failed to unarchive clinic.");
		}
	}
}

const showLogoCropModal = ref(false)
const logoImageSrc = ref<string | null>(null)
const logoCanvas = ref<HTMLCanvasElement | null>(null)
const logoSelectedImage = ref<HTMLImageElement | null>(null)
const logoScale = ref(1)
const logoOffsetX = ref(0)
const logoOffsetY = ref(0)
const logoIsPanning = ref(false)
const logoLastPanPoint = ref({ x: 0, y: 0 })
const logoLastTouchDistance = ref(0)
let logoPendingFile: File | null = null

function openLogoCropper(file: File) {
    logoPendingFile = file
    revokeLogoObjectUrl()
    logoImageSrc.value = URL.createObjectURL(file)
    logoScale.value = 1
    logoOffsetX.value = 0
    logoOffsetY.value = 0
    showLogoCropModal.value = true
    // when modal opens, load image into an HTMLImageElement and draw
    setTimeout(async () => {
        if (!logoImageSrc.value) return
        const img = document.createElement('img') as HTMLImageElement
        img.src = logoImageSrc.value
        await new Promise((res, rej) => { img.onload = res; img.onerror = rej })
        logoSelectedImage.value = img
        // await nextTick()
        drawLogoCanvas()
    }, 50)
}

function drawLogoCanvas() {
    if (!logoCanvas.value || !logoSelectedImage.value) return
    const canvasEl = logoCanvas.value
    const ctx = canvasEl.getContext('2d')
    if (!ctx) return

    const rect = canvasEl.getBoundingClientRect()
    const size = Math.floor(Math.min(rect.width, rect.height))
    canvasEl.width = size
    canvasEl.height = size

    ctx.clearRect(0, 0, size, size)
    ctx.fillStyle = '#ffffff'
    ctx.fillRect(0, 0, size, size)

    const img = logoSelectedImage.value
    const imgAspect = img.naturalWidth / img.naturalHeight
    const canvasAspect = 1

    let drawWidth: number, drawHeight: number
    if (imgAspect > canvasAspect) {
        drawWidth = size
        drawHeight = size / imgAspect
    } else {
        drawHeight = size
        drawWidth = size * imgAspect
    }

    drawWidth *= logoScale.value
    drawHeight *= logoScale.value

    const x = (size - drawWidth) / 2 + logoOffsetX.value
    const y = (size - drawHeight) / 2 + logoOffsetY.value

    // square clip (full-canvas square) and border
    ctx.save()
    ctx.beginPath()
    ctx.rect(0, 0, size, size)
    ctx.clip()
    ctx.drawImage(img, x, y, drawWidth, drawHeight)
    ctx.restore()
}

function validateLogoFile(file: File): Promise<boolean> {
    return new Promise((resolve) => {
        if (!file) return resolve(false)
        if (!allowedLogoTypes.includes(file.type)) {
            toast.error('Unsupported logo format. Use PNG or JPEG/JPG.')
            return resolve(false)
        }
        // We allow any dimensions; provide a client-side crop step after selection
        resolve(true)
    })
}

function revokeLogoObjectUrl() {
    if (logoObjectUrl.value && logoObjectUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(logoObjectUrl.value)
    }
    logoObjectUrl.value = null
}

async function handleLogoDrop(event: any) {
    event.preventDefault()
    const file: File | undefined = event.dataTransfer?.files?.[0]
    if (!file) return
    const ok = await validateLogoFile(file)
    if (!ok) return
    openLogoCropper(file)
}

async function handleLogoChange(event: any) {
    const file: File | undefined = event.target?.files?.[0]
    if (!file) return
    const ok = await validateLogoFile(file)
    if (!ok) return
    openLogoCropper(file)
}

function logoStartPan(event: MouseEvent) {
    logoIsPanning.value = true
    logoLastPanPoint.value = { x: event.clientX, y: event.clientY }
}
function logoHandlePan(event: MouseEvent) {
    if (!logoIsPanning.value) return
    const deltaX = event.clientX - logoLastPanPoint.value.x
    const deltaY = event.clientY - logoLastPanPoint.value.y
    logoOffsetX.value += deltaX
    logoOffsetY.value += deltaY
    logoLastPanPoint.value = { x: event.clientX, y: event.clientY }
    drawLogoCanvas()
}
function logoEndPan() { logoIsPanning.value = false }

// Zoom controls
function logoZoomIn() {
    logoScale.value = Math.min(3, logoScale.value + 0.1)
    drawLogoCanvas()
}
function logoZoomOut() {
    logoScale.value = Math.max(0.5, logoScale.value - 0.1)
    drawLogoCanvas()
}
function logoHandleZoom(event: WheelEvent) {
    event.preventDefault()
    const delta = event.deltaY > 0 ? -0.05 : 0.05
    logoScale.value = Math.max(0.5, Math.min(3, logoScale.value + delta))
    drawLogoCanvas()
}

// Touch pan/zoom
function getTouchDistance(touches: TouchList): number {
    if (touches.length < 2) return 0
    const t1 = touches[0], t2 = touches[1]
    const dx = t1.clientX - t2.clientX
    const dy = t1.clientY - t2.clientY
    return Math.sqrt(dx*dx + dy*dy)
}
function logoStartTouchPan(event: TouchEvent) {
    event.preventDefault()
    if (event.touches.length === 1) {
        logoIsPanning.value = true
        const t = event.touches[0]
        logoLastPanPoint.value = { x: t.clientX, y: t.clientY }
    } else if (event.touches.length === 2) {
        logoIsPanning.value = false
        logoLastTouchDistance.value = getTouchDistance(event.touches)
    }
}
function logoHandleTouchPan(event: TouchEvent) {
    event.preventDefault()
    if (event.touches.length === 1 && logoIsPanning.value) {
        const t = event.touches[0]
        const dx = t.clientX - logoLastPanPoint.value.x
        const dy = t.clientY - logoLastPanPoint.value.y
        logoOffsetX.value += dx
        logoOffsetY.value += dy
        logoLastPanPoint.value = { x: t.clientX, y: t.clientY }
        drawLogoCanvas()
    } else if (event.touches.length === 2) {
        const dist = getTouchDistance(event.touches)
        if (logoLastTouchDistance.value > 0) {
            const delta = (dist - logoLastTouchDistance.value) * 0.01
            logoScale.value = Math.max(0.5, Math.min(3, logoScale.value + delta))
            drawLogoCanvas()
        }
        logoLastTouchDistance.value = dist
    }
}
function logoEndTouchPan() { logoIsPanning.value = false; logoLastTouchDistance.value = 0 }

function logoResetPosition() {
    logoScale.value = 1
    logoOffsetX.value = 0
    logoOffsetY.value = 0
    drawLogoCanvas()
}

function logoSelectNewImage() {
    // clear current, allow user to pick new file
    logoSelectedImage.value = null
    logoImageSrc.value = null
    logoPendingFile = null
    // trigger the hidden file input used for logo (re-use logo-upload)
    const input = document.getElementById('logo-upload') as HTMLInputElement | null
    if (input) input.click()
}

async function logoConfirmCrop() {
    if (!logoCanvas.value || !logoSelectedImage.value || !logoPendingFile) return
    const canvasEl = logoCanvas.value
	
    canvasEl.toBlob((blob) => {
        if (!blob) return
        const newFile = new File([blob], logoPendingFile!.name, { type: blob.type })
        selectedLogoFile.value = newFile
        revokeLogoObjectUrl()
        logoObjectUrl.value = URL.createObjectURL(newFile)
        formData.value.logo = logoObjectUrl.value
        removeLogoFlag.value = false
        showLogoCropModal.value = false
        if (logoImageSrc.value && logoImageSrc.value.startsWith('blob:')) URL.revokeObjectURL(logoImageSrc.value)
        logoImageSrc.value = null
        logoSelectedImage.value = null
        logoPendingFile = null
    }, 'image/png')

	simulateLoading()
}

const removeFile = () => {
	selectedLogoFile.value = null
	loadProgress.value = 0
	isLoadingFile.value = false
} 

function logoCancelCrop() {
    showLogoCropModal.value = false
    if (logoImageSrc.value && logoImageSrc.value.startsWith('blob:')) URL.revokeObjectURL(logoImageSrc.value)
    logoImageSrc.value = null
    logoSelectedImage.value = null
    logoPendingFile = null
}

function removeCurrentLogo() {
    removeLogoFlag.value = true
    selectedLogoFile.value = null
    formData.value.logo = ''
    revokeLogoObjectUrl()
}

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

onMounted(async () => {
    getAllClinics(1);
	fetchClinicianOptions();
})

watch(itemsPerPage, () => {
    getAllClinics(1)
})
</script> 