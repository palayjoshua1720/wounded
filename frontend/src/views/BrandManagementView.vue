<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Brand Management</h1>
				<p class="text-gray-600 dark:text-gray-400">View and manage brands — mirror the manufacturer layout for
					consistency.</p>
			</div>
			<button @click="selectedBrand = null; showCreateForm = true"
				class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
				<PackagePlus class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" />
				New Brand
			</button>
		</div>

		<!-- Filters (match Manufacturer style) -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col lg:flex-row gap-6">
				<div class="flex-1">
					<div class="relative">
						<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search Brand..."
							class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
						<ChevronDown
							class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
				</div>
				<div class="flex flex-col sm:flex-row gap-4">
					<div class="relative">
						<Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
						<select v-model="statusFilter"
							class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
							<option value="all">All Status</option>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
							<option value="archive">Archived</option>
						</select>
						<ChevronDown
							class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
					<div class="relative">
						<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
						<select id="per-page" v-model="itemsPerPage"
							class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
						</select>
						<ChevronDown
							class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
				</div>
			</div>
		</div>

		<!-- Card View -->
		<ContentLoader v-if="tableLoader" />
		<div v-if="filteredBrands && filteredBrands.length > 0"
			class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
			<div v-for="brand in filteredBrands" :key="brand.id"
				class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow">
				<div class="flex items-start justify-between mb-4">
					<div>
						<div class="flex items-center gap-3">
							<!-- Logo or Icon -->
							<div v-if="brand.logoUrl" class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100">
								<img :src="brand.logoUrl" :alt="`${brand.brandName} logo`"
									class="w-full h-full object-cover" />
							</div>
							<div v-else class="p-2 bg-green-100 rounded-lg">
								<Package class="w-5 h-5 text-green-600" />
							</div>

							<div class="flex flex-col">
								<h3 class="font-semibold text-gray-900 dark:text-white">
									{{ brand.brandName }}
								</h3>
								<span :class="[
									'inline-flex px-2 py-1 text-xs rounded-full w-fit',
									brand.brandStatus === 0
										? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
										: brand.brandStatus === 1
											? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
											: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
								]">
									{{ brand.brandStatus === 0 ? 'Active' : brand.brandStatus === 1 ? 'Inactive' :
										'Archived' }}
								</span>
							</div>
						</div>
					</div>

					<!-- Quick Actions -->
					<div class="flex items-center space-x-2">
						<!-- View -->
						<button @click="viewBrand = brand"
							class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
							<Eye class="w-5 h-4" />
						</button>

						<!-- Edit -->
						<button @click="editBrand(brand)"
							class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
							<SquarePen class="w-4 h-4" />
						</button>

						<!-- Toggle Active / Inactive / Reactivate -->
						<button @click="handleToggleStatus(brand.id, brand.brandStatus)" :class="brand.brandStatus === 0
							? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
							: 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'" :title="brand.brandStatus === 0
								? 'Deactivate'
								: (brand.brandStatus === 1 ? 'Activate' : 'Reactivate')">
							<component :is="brand.brandStatus === 0 ? CircleX : CircleCheck" class="w-4 h-4" />
						</button>

						<!-- Archive when not Archived -->
						<button v-if="brand.brandStatus !== 2" @click="handleArchiveBrand(brand.id)"
							class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
							title="Archive">
							<Archive class="w-4 h-4" />
						</button>

						<!-- Delete only when Archived -->
						<button v-if="brand.brandStatus === 2" @click="handleDeleteBrand(brand.id)"
							class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
							title="Delete">
							<Trash2 class="w-4 h-4" />
						</button>
					</div>

				</div>

				<div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
					<div class="flex items-center gap-2">
						<Factory class="w-4 h-4" />
						<span>{{ brand.manufacturerName || 'No Manufacturer' }}</span>
					</div>
					<div class="flex items-center gap-2">
						<TriangleAlert class="w-4 h-4" />
						<span>{{ brand.mue || 'N/A' }} MUE</span>
					</div>


					<hr />
					<div class="flex items-center gap-2 flex-wrap"
						v-if="getActiveSizes(brand).length > 0 || getInactiveCount(brand) > 0">
						<strong>Available Sizes:</strong>
						<span v-for="sizeObj in getActiveSizes(brand).slice(0, 4)" :key="sizeObj.id || sizeObj.size"
							class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 ml-1 mt-1">
							{{ sizeObj.size }}
						</span>
						<span v-if="getActiveSizes(brand).length > 4"
							class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 ml-1 mt-1">
							+{{ getActiveSizes(brand).length - 4 }} more
						</span>
						<span v-if="getInactiveCount(brand) > 0" class="text-xs text-gray-500 ml-1">
							(+{{ getInactiveCount(brand) }} inactive)
						</span>
					</div>
					<p v-else class="text-xs text-gray-500">No sizes available</p>
				</div>
			</div>
		</div>

		<div v-else-if="filteredBrands && filteredBrands.length === 0 && !tableLoader" class="">
			<div class="flex flex-col items-center justify-center gap-2 text-center">
				<Package class="w-10 h-10 mb-1 text-gray-700" />
				<span class="text-gray-600 dark:text-gray-300">No brands found.</span>
			</div>
		</div>

		<template v-if="!tableLoader">
			<div
				class="bg-white dark:bg-gray-800 mt-4 rounded-bl-2xl rounded-br-2xl shadow-sm dark:shadow-gray-900 border border-gray-200 dark:border-gray-700">
				<Pagination v-if="filteredBrands && filteredBrands.length > 0" :pagination="pagination"
					@update:page="getAllBrands" />
			</div>
		</template>

		<!-- Create/Edit Form Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Brand' : 'Edit Brand'">
			<form @submit.prevent="handleSubmitForm" class="space-y-4">
				<!-- Brand Information -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<Package class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Brand Information</h3>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input v-model="formData.brandName" type="text" required placeholder="Brand Name" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
										focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
						</div>

						<!-- Status -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select v-model="formData.brandStatus" required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option :value="0">Active</option>
									<option :value="1">Inactive</option>
									<option :value="2">Archived</option>
								</select>
							</div>
						</div>

						<!-- Manufacturer -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Manufacturer<span
									class="text-red-500">*</span></label>
							<div class="relative">
								<Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select v-model="formData.manufacturerId" required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option v-for="opt in manufacturerOptions" :key="opt.value" :value="opt.value">
										{{ opt.label }}
									</option>
								</select>
							</div>
						</div>

						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">MUE (Medically
								Unlikely
								Edits)<span class="text-red-500">*</span></label>
							<div class="relative">
								<Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input v-model.number="formData.mue" type="number" min="1" required
									placeholder="Enter MUE value" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                          focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
							</div>
							<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum units allowed per day per
								patient
							</p>
						</div>

						<!-- Logo Upload Section -->
						<div class="sm:col-span-2">
							<div class="flex items-center gap-2 mb-2">
								<Image class="w-5 h-5 text-green-500" />
								<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Brand Logo (Optional)
								</h3>
							</div>

							<!-- Drag & Drop Area (only shown when no image selected) -->
							<div v-if="!selectedLogoFile && !formData.logoUrl" class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer
										bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition cursor-pointer"
								@drop.prevent="handleLogoDrop" @dragover.prevent="allowLogoDrop">
								<input id="logo-upload" type="file" accept="image/png,image/jpeg,image/jpg"
									class="hidden" @change="handleLogoChange" />
								<label for="logo-upload" class="flex flex-col items-center justify-center text-center">
									<UploadCloud class="w-10 h-10 mb-3 text-gray-400" />
									<p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
										<span class="font-semibold text-purple-600 dark:text-purple-400">Click to
											upload</span>
										or drag and drop
									</p>
									<p class="text-xs text-gray-500 dark:text-gray-400">JPEG, JPG, PNG (max. 2MB)</p>
								</label>
							</div>

							<!-- Selected (cropped) file preview -->
							<div v-if="selectedLogoFile"
								class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
								<div class="flex items-center gap-2">
									<Image class="w-4 h-4 text-gray-400" />
									<span>Selected: <span class="font-medium">{{ selectedLogoFile.name }}</span></span>
									<img :src="formData.logoUrl" class="w-8 h-8 rounded object-cover ml-2 border" />
								</div>
								<button @click="removeCurrentLogo" class="text-red-500 hover:text-red-600 transition">
									<X class="w-5 h-5" />
								</button>
							</div>

							<!-- Existing logo preview (when editing) -->
							<div v-else-if="formData.logoUrl && !selectedLogoFile"
								class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg">
								<div class="flex items-center gap-2">
									<Image class="w-4 h-4 text-gray-400" />
									<span>Current logo:</span>
									<img :src="formData.logoUrl" class="w-8 h-8 rounded object-cover border" />
								</div>
								<button @click="removeCurrentLogo" class="text-red-500 hover:text-red-600 transition">
									<X class="w-5 h-5" />
								</button>
							</div>
						</div>

						<div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Description
								(Optional)</label>
							<div class="relative">
								<Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<textarea v-model="formData.description" placeholder="Description" rows="3"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-vertical"></textarea>
							</div>
						</div>
					</div>
				</div>

				<!-- Graft Sizes -->
				<div>
					<div class="flex items-center gap-2 mb-2">
						<PencilRuler class="w-5 h-5 text-green-500" />
						<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Graft Sizes (Optional)</h3>
					</div>
					<div class="space-y-3">
						<div v-for="(graftSize, index) in formData.graftSizes" :key="graftSize.id || index"
							:class="index > 0 ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : ''">
							<div class="flex items-center justify-between mb-2">
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">Size {{ index + 1
								}}</span>
								<button v-if="index > 0" type="button" @click="removeGraftSize(index)"
									class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
									<Trash2 class="w-4 h-4" />
								</button>
							</div>
							<div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Item
										No</label>
									<div class="relative">
										<Barcode class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model="graftSize.item_no" type="text" placeholder="ABC-0000"
											class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Size
										(e.g., 2cm x
										2cm)</label>
									<div class="relative">
										<RulerDimensionLine class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model="graftSize.size" type="text" placeholder="2cm x 2cm" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Area
										(cm²)</label>
									<div class="relative">
										<Diameter class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model.number="graftSize.area" type="number" placeholder="0" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
								<div>
									<label
										class="block text-sm font-medium text-gray-600 dark:text-gray-400">Price</label>
									<div class="relative">
										<DollarSign class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model.number="graftSize.price" type="number" step="0.01"
											placeholder="0.00" class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
								<div>
									<label
										class="block text-sm font-medium text-gray-600 dark:text-gray-400">Stock</label>
									<div class="relative">
										<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input v-model.number="graftSize.stock" type="number" min="0" placeholder="0"
											class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
									</div>
								</div>
							</div>
						</div>
						<button type="button" @click="addGraftSize"
							class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors w-full sm:w-auto">
							<Plus class="w-4 h-4 mr-2" />
							Add Size
						</button>
					</div>
				</div>

				<!-- Modal Footer -->
				<div class="flex justify-end space-x-3 pt-4">
					<button type="button" @click="closeForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
							text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
						Cancel
					</button>
					<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
						{{ showCreateForm ? 'Create Brand' : 'Update Brand' }}
					</button>
				</div>
			</form>
		</BaseModal>

		<!-- Logo Cropper Modal -->
		<BaseModal v-model="showLogoCropModal" title="Crop Logo" max-width="520px">
			<div class="p-6 space-y-6">
				<!-- Instructions -->
				<div class="text-center">
					<p class="text-sm text-gray-600 dark:text-gray-400">
						Adjust your logo to fit perfectly. Drag to reposition and use the zoom controls below.
					</p>
				</div>

				<!-- Crop Area Container -->
				<div
					class="relative mx-auto w-full max-w-[320px] aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700">
					<!-- Canvas Container with subtle grid background -->
					<div class="absolute inset-0 opacity-20">
						<div class="w-full h-full bg-grid-pattern bg-[length:20px_20px]"></div>
					</div>

					<canvas ref="logoCanvas" @mousedown="logoStartPan" @mousemove="logoHandlePan" @mouseup="logoEndPan"
						@mouseleave="logoEndPan" @wheel="logoHandleZoom" @touchstart="logoStartTouchPan"
						@touchmove="logoHandleTouchPan" @touchend="logoEndTouchPan" @touchcancel="logoEndTouchPan"
						class="absolute inset-0 w-full h-full touch-none cursor-grab active:cursor-grabbing select-none transition-transform duration-150"
						:class="{ 'cursor-grabbing': logoIsPanning }"></canvas>

					<!-- Circular Crop Overlay with modern design -->
					<div class="absolute inset-0 pointer-events-none">
						<!-- Outer shadow/mask -->
						<div class="absolute inset-0 bg-black/30 backdrop-blur-[1px]"></div>

						<!-- Circular crop window -->
						<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-4/5 h-4/5">
							<!-- Border with glow effect -->
							<div
								class="absolute inset-0 border-2 border-white/80 rounded-full shadow-[0_0_0_1px_rgba(255,255,255,0.3),0_0_20px_rgba(0,0,0,0.2)]">
							</div>

							<!-- Corner indicators -->
							<div class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-white rounded-tl">
							</div>
							<div class="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-white rounded-tr">
							</div>
							<div
								class="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-white rounded-bl">
							</div>
							<div
								class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-white rounded-br">
							</div>
						</div>

						<!-- Help text -->
						<div class="absolute bottom-4 left-1/2 -translate-x-1/2">
							<div class="flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-sm rounded-full">
								<svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor"
									viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
										d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
								</svg>
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
							<span
								class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
								{{ Math.round(logoScale * 100) }}%
							</span>
						</div>

						<div class="flex items-center gap-4">
							<button @click="logoZoomOut" :disabled="logoScale <= 0.5"
								class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow">
								<Minus class="w-5 h-5 text-gray-500" />
							</button>

							<div class="flex-1 relative">
								<input type="range" min="0.5" max="3" step="0.1" v-model="logoScale"
									@input="drawLogoCanvas"
									class="w-full h-2 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-lg appearance-none cursor-pointer slider-thumb" />
								<div
									class="absolute inset-0 flex justify-between items-center pointer-events-none px-1">
									<span class="text-xs text-gray-500">50%</span>
									<span class="text-xs text-gray-500">300%</span>
								</div>
							</div>

							<button @click="logoZoomIn" :disabled="logoScale >= 3"
								class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow">
								<Plus class="w-5 h-5 text-gray-500" />
							</button>
						</div>
					</div>

					<!-- Action Buttons -->
					<div class="flex flex-wrap gap-3 justify-center">
						<button @click="logoResetPosition"
							class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow">
							<RefreshCw class="w-5 h-5 text-gray-500" />
							Reset View
						</button>

						<button @click="logoSelectNewImage"
							class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200 shadow-sm hover:shadow">
							<Image class="w-5 h-5 text-blue-500" />
							Change Image
						</button>
					</div>
				</div>
			</div>

			<!-- Modal Footer -->
			<template #actions>
				<div class="flex justify-end gap-3 px-6 pb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
					<button @click="logoCancelCrop"
						class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow">
						Cancel
					</button>
					<button @click="logoConfirmCrop"
						class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.02]">
						Apply Crop
					</button>
				</div>
			</template>
		</BaseModal>

		<!-- Show Brand Details -->
		<BaseModal v-model="showBrandDetailsModal" title="Brand Details" max-width="800px">
			<template v-if="viewBrand">
				<div class="p-6 space-y-8">
					<!-- Header Section -->
					<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
						<div class="flex items-center gap-5">
							<!-- Logo -->
							<div class="flex-shrink-0">
								<div v-if="viewBrand.logoUrl"
									class="w-20 h-20 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
									<img :src="viewBrand.logoUrl" :alt="`${viewBrand.brandName} logo`"
										class="w-full h-full object-cover" />
								</div>
								<div v-else
									class="w-20 h-20 rounded-xl bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 flex items-center justify-center border border-green-200 dark:border-green-700">
									<PackageOpen class="w-10 h-10 text-green-600 dark:text-green-400" />
								</div>
							</div>

							<!-- Title & Status -->
							<div>
								<h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
									{{ viewBrand.brandName }}
								</h2>
								<div class="mt-2">
									<span :class="[
										'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
										viewBrand.brandStatus === 0 ? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400' :
											viewBrand.brandStatus === 1 ? 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400' :
												'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400'
									]">
										<span class="w-2 h-2 rounded-full bg-current mr-2"></span>
										{{ viewBrand.brandStatus === 0 ? 'Active' : viewBrand.brandStatus === 1 ?
											'Inactive' : 'Archived' }}
									</span>
								</div>
							</div>
						</div>

						<!-- Quick Info Badges -->
						<div class="flex flex-wrap gap-3">
							<div
								class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
								<Factory class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mr-2" />
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">
									{{ viewBrand.manufacturerName || 'No Manufacturer' }}
								</span>
							</div>
							<div
								class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
								<Ruler class="w-5 h-5 text-purple-500 dark:text-purple-400 mr-2" />
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">
									{{ getActiveSizes(viewBrand).length }} Sizes
								</span>
							</div>
						</div>
					</div>

					<!-- Key Stats Grid -->
					<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
						<div class="bg-gray-100 dark:bg-gray-700/70 rounded-xl p-5 text-center shadow-sm">
							<div class="text-2xl font-bold text-gray-900 dark:text-white">{{ viewBrand.graftSizes.length
							}}</div>
							<div class="text-sm text-gray-600 dark:text-gray-300 mt-1">Total Sizes</div>
						</div>
						<div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 text-center">
							<div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{
								getActiveSizes(viewBrand).length }}</div>
							<div class="text-sm text-gray-600 dark:text-gray-400">Active Sizes</div>
						</div>
						<div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-5 text-center">
							<div class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ viewBrand.mue || 0
							}}</div>
							<div class="text-sm text-gray-600 dark:text-gray-400">MUE Limit</div>
						</div>
						<div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-5 text-center">
							<div class="text-2xl font-bold text-orange-700 dark:text-orange-300">
								{{ viewBrand.productType || 'Graft' }}
							</div>
							<div class="text-sm text-gray-600 dark:text-gray-400">Product Type</div>
						</div>
					</div>
					<!-- Description -->
					<div>
						<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
						<div
							class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
							<p v-if="viewBrand.description"
								class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
								{{ viewBrand.description }}
							</p>
							<p v-else class="text-gray-500 dark:text-gray-400 italic">
								No description provided.
							</p>
						</div>
					</div>

					<!-- Graft Sizes -->
					<div>
						<h3
							class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
							Graft Sizes
							<span class="text-sm font-medium text-gray-500 dark:text-gray-400">
								{{ viewBrand.graftSizes.length }} total
								<span v-if="getActiveSizes(viewBrand).length !== viewBrand.graftSizes.length"
									class="ml-2 text-gray-400">
									({{ getActiveSizes(viewBrand).length }} active)
								</span>
							</span>
						</h3>

						<div v-if="viewBrand.graftSizes.length === 0"
							class="text-center py-12 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700">
							<Package class="w-14 h-14 mx-auto text-gray-400 mb-4 opacity-80" />
							<h4 class="text-base font-medium text-gray-700 dark:text-gray-300 mb-1">No graft sizes
								configured</h4>
							<p class="text-sm text-gray-500 dark:text-gray-400">Add sizes when creating or editing this
								brand.</p>
						</div>

						<div v-else
							class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
							<!-- Table Header (sticky) -->
							<div
								class="bg-gray-100 dark:bg-gray-700/90 px-6 py-3.5 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
								<div
									class="grid grid-cols-12 gap-4 text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
									<div class="col-span-2">Item No</div>
									<div class="col-span-4">Size & Area</div>
									<div class="col-span-2 text-center">Price</div>
									<div class="col-span-2 text-center">Stock</div>
									<div class="col-span-2 text-center">Status</div>
								</div>
							</div>

							<!-- Scrollable Body -->
							<div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/60">
								<div v-for="(size, index) in viewBrand.graftSizes" :key="size.id || size.size" :class="[
									'px-6 py-5 transition-colors duration-150',
									index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50/70 dark:bg-gray-800/30'
								]">
									<div class="grid grid-cols-12 gap-4 items-center text-sm">
										<!-- Item Number -->
										<div class="col-span-2 font-medium text-gray-800 dark:text-gray-100">
											{{ size.item_no || '—' }}
										</div>

										<!-- Size & Area (merged - styled like reference) -->
										<div class="col-span-4">
											<div class="space-y-1">
												<div
													class="text-base font-medium text-gray-900 dark:text-gray-100 leading-tight">
													{{ size.size }}
												</div>
												<div v-if="size.area" class="text-sm text-gray-600 dark:text-gray-400">
													{{ size.area.toFixed(2) }} cm²
												</div>
												<div v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
													— cm²
												</div>
											</div>
										</div>

										<!-- Price -->
										<div class="col-span-2 text-center tabular-nums font-medium">
											<span v-if="size.price" class="text-emerald-700 dark:text-emerald-400">
												${{ size.price.toFixed(2) }}
											</span>
											<!-- <span v-else class="text-gray-400">—</span> -->
										</div>

										<!-- Stock -->
										<div class="col-span-2 text-center font-medium tabular-nums">
											{{ size.stock || 0 }}
											<span v-if="size.stock === 0"
												class="text-red-500 dark:text-red-400 text-xs ml-1.5">(out)</span>
										</div>

										<!-- Status -->
										<div class="col-span-2 text-center">
											<span :class="[
												'inline-flex items-center px-3 py-1 text-xs font-medium rounded-full',
												size.graftStatus === 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' :
													size.graftStatus === 1 ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' :
														'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'
											]">
												<span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></span>
												{{ size.graftStatus === 0 ? 'Active' : size.graftStatus === 1 ?
													'Inactive' : 'Archived' }}
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>

			<template #actions>
				<div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
					<button type="button" @click="viewBrand = null"
						class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
						Close
					</button>
				</div>
			</template>
		</BaseModal>
	</div>

	<UploadLoader :is-visible="isLoadingFile" :progress="loadProgress" context="brand" title="Preparing..." />
</template>

<script setup lang="ts">
import { useToast } from "vue-toastification"
import Swal from "sweetalert2"
import { ref, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import UploadLoader from '@/components/ui/UploadLoader.vue'
import { Package, PackagePlus, Eye, SquarePen, Trash2, Archive, CircleCheck, CircleX, Factory, TriangleAlert, Hash, RulerDimensionLine, Diameter, DollarSign, PencilRuler, Plus, Search, Funnel, Globe, Ruler, PackageOpen, PackageSearch, ChevronDown, Image, UploadCloud, X, RefreshCw, Minus, Barcode } from 'lucide-vue-next'
import api from '@/services/api'
import axios from "axios";

const toast = useToast()

// types
interface GraftSize {
	id?: number
	item_no?: string | null
	size: string
	area: number | null
	price: number | null
	graftStatus?: number
	stock?: number
}

interface SimpleManufacturer {
	id: number
	manufacturerName: string
}

interface Brand {
	id: number
	brandName: string
	brandStatus: number
	manufacturerId?: number
	manufacturerName?: string
	mue?: number | null
	logoUrl?: string | null
	description?: string
	graftSizes: GraftSize[]
	createdAt: string
	updatedAt: string
}

const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const itemsPerPage = ref(10)
const brands = ref<Brand[]>([])
const manufacturers = ref<SimpleManufacturer[]>([])
const tableLoader = ref(false)

const selectedBrand = ref<Brand | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const viewBrand = ref<Brand | null>(null)
const graftSizesExpanded = ref(false)

const showBrandDetailsModal = computed({
	get: () => viewBrand.value !== null,
	set: (value: boolean) => {
		if (!value) {
			viewBrand.value = null
			graftSizesExpanded.value = false
		}
	},
})


const formData = ref({
	brandName: '',
	brandStatus: 0,
	manufacturerId: null as number | null,
	mue: null as number | null,
	logoUrl: '',
	description: '',
	graftSizes: [{ size: '', area: null, price: null, stock: 0, item_no: '' }] as GraftSize[]
})

const manufacturerOptions = computed(() => {
	const opts = manufacturers.value.map(m => ({ value: m.id, label: m.manufacturerName }))
	if (formData.value.manufacturerId === null) {
		return [{ value: null, label: 'Select a Manufacturer' }, ...opts]
	} else {
		return opts
	}
})

const getActiveSizes = (brand: Brand) => brand.graftSizes.filter(s => s.graftStatus === 0)

const getInactiveCount = (brand: Brand) => brand.graftSizes.filter(s => s.graftStatus !== 0).length

async function getAllManufacturers() {
	try {
		const { data } = await api.get(`/management/manufacturers`, {
			headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
		})
		manufacturers.value = data.manufacturerData.map((m: any) => ({
			id: m.id,
			manufacturerName: m.manufacturerName,
		}))
	} catch (error) {
		console.error('Error fetching manufacturers:', error)
	}
}

async function getAllBrands(page = 1) {
	tableLoader.value = true
	try {
		const { data } = await api.get(`/management/brands?page=${page}&per_page=${itemsPerPage.value}`, {
			headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
		})
		brands.value = data.brandData.map((b: any) => ({
			id: b.id,
			brandName: b.brandName,
			brandStatus: b.brandStatus,
			manufacturerId: b.manufacturerId ?? null,
			manufacturerName: b.manufacturerName,
			mue: b.mue ?? null,
			logoUrl: b.logoUrl,
			description: b.description,
			graftSizes: (b.graftSizes || []).map((s: any) => ({
				id: s.id,
				item_no: s.item_no,
				size: s.size,
				area: s.area,
				price: s.price,
				stock: s.stock || 0,
				graftStatus: s.graftStatus || 0
			})),
			createdAt: b.createdAt,
			updatedAt: b.updatedAt,
		}))
		pagination.value = {
			current_page: data.meta.current_page,
			last_page: data.meta.last_page,
			per_page: data.meta.per_page,
			total: data.meta.total,
		}
	} catch (error) {
		console.error('Error fetching brands:', error)
	} finally {
		tableLoader.value = false
	}
}

function editBrand(b: Brand) {
	selectedBrand.value = b
	formData.value = {
		brandName: b.brandName || '',
		brandStatus: b.brandStatus ?? 0,
		manufacturerId: b.manufacturerId ?? null,
		mue: b.mue ?? null,
		logoUrl: b.logoUrl || '',
		description: b.description || '',
		graftSizes: b.graftSizes.length > 0
			? b.graftSizes.map((s: GraftSize) => ({
				id: s.id,
				item_no: s.item_no || '',
				size: s.size,
				area: s.area,
				price: s.price,
				stock: s.stock || 0,
				graftStatus: s.graftStatus || 0
			}))
			: [{ size: '', area: null, price: null, stock: 0, item_no: '' }]
	}
	showEditForm.value = true
}

function addGraftSize() {
	formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0 })
}

function removeGraftSize(index: number) {
	formData.value.graftSizes.splice(index, 1)
	if (formData.value.graftSizes.length === 0) {
		formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, item_no: '' })
	}
}

// submit create/edit form
async function handleSubmitForm() {
	try {
		const form = new FormData()

		form.append('brandName', formData.value.brandName)
		form.append('brandStatus', formData.value.brandStatus.toString())
		form.append('manufacturerId', (formData.value.manufacturerId ?? '').toString())
		form.append('mue', formData.value.mue ? formData.value.mue.toString() : '')
		form.append('description', formData.value.description || '')

		// === LOGO HANDLING – FIXED ORDER (100% reliable) ===
		if (selectedLogoFile.value) {
			form.append('logo', selectedLogoFile.value)
		} else if (removeLogoFlag.value) {
			form.append('remove_logo', '1')
		}

		// Graft sizes as JSON
		form.append('graftSizes', JSON.stringify(formData.value.graftSizes))

		if (showCreateForm.value) {
			await api.post('/management/brands', form, {
				headers: {
					Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
					'Content-Type': 'multipart/form-data'
				}
			})
			toast.success('Brand created!')
		} else if (showEditForm.value && selectedBrand.value) {
			await api.post(`/management/brands/${selectedBrand.value.id}`, form, {
				headers: {
					Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
					'Content-Type': 'multipart/form-data'
				}
			})
			toast.success('Brand updated!')
		}

		closeForm()
		getAllBrands(1)

	} catch (error: any) {
		console.error(error.response?.data || error)
		toast.error(error.response?.data?.message || 'Something went wrong!')
	}
}

// archive brand
async function handleArchiveBrand(id: number) {
	try {
		const result = await Swal.fire({
			title: 'Archive Brand?',
			text: "This action will move the brand to archived status. You can restore it later if needed.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Yes, archive it!',
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			const response = await api.get(`/management/brands/${id}/archive`)
			const data = response.data

			const idx = brands.value.findIndex(b => b.id === id)
			if (idx !== -1) {
				brands.value[idx].brandStatus = data.data.brandStatus
			}

			toast.success('Brand archived successfully')
		}
	} catch (error) {
		console.error(error)
		toast.error('Error archiving brand')
	}
}

// toggle status (active/inactive/reactivate)
async function handleToggleStatus(id: number, currentStatus: number) {
	let actionText = ''

	if (currentStatus === 0) actionText = 'deactivate'
	else if (currentStatus === 1) actionText = 'activate'
	else if (currentStatus === 2) actionText = 'reactivate'

	try {
		const result = await Swal.fire({
			title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Brand?`,
			text: `Are you sure you want to ${actionText} this brand?`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: `Yes, ${actionText} it!`,
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			const response = await api.get(`/management/brands/${id}/toggle`)
			const data = response.data

			const idx = brands.value.findIndex(b => b.id === id)
			if (idx !== -1) {
				brands.value[idx].brandStatus = data.data.brandStatus
			}

			toast.success(`Brand ${actionText}d successfully`)
		}
	} catch (error) {
		console.error(error)
		toast.error(`Error trying to ${actionText} brand`)
	}
}

// delete brand
async function handleDeleteBrand(id: number) {
	try {
		const result = await Swal.fire({
			title: 'Delete Brand?',
			text: "This action cannot be undone.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Yes, delete it!',
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			await api.delete(`/management/brands/${id}`)
			brands.value = brands.value.filter(b => b.id !== id)
			toast.success('Brand deleted successfully')
		}
	} catch (error) {
		console.error(error)
		toast.error('Error deleting brand')
	}
}

function closeForm() {
	showCreateForm.value = false
	showEditForm.value = false
	selectedBrand.value = null
	formData.value = {
		brandName: '',
		brandStatus: 0,
		manufacturerId: null,
		mue: null,
		logoUrl: '',
		description: '',
		graftSizes: [{ size: '', area: null, price: null, stock: 0, item_no: '' }]
	}
	selectedLogoFile.value = null
}

// search & filter
const searchTerm = ref('')
const statusFilter = ref('all')
const filteredBrands = computed(() => {
	return brands.value.filter(b => {
		const matchesSearch =
			b.brandName.toLowerCase().includes(searchTerm.value.toLowerCase())
		const matchesStatus =
			statusFilter.value === 'all' ||
			(statusFilter.value === 'active' && b.brandStatus === 0) ||
			(statusFilter.value === 'inactive' && b.brandStatus === 1) ||
			(statusFilter.value === 'archive' && b.brandStatus === 2)
		return matchesSearch && matchesStatus
	})
})

const isLoadingFile = ref(false)
const loadProgress = ref(0)

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

// file handling
const selectedFile = ref<File | null>(null)
function handleFileChange(event: any) { selectedFile.value = event.target.files[0] }
function handleDrop(event: any) { event.preventDefault(); selectedFile.value = event.dataTransfer.files[0] }
function allowDrop(event: any) { event.preventDefault() }

// Logo file handling (NEW)
const selectedLogoFile = ref<File | null>(null)
const logoObjectUrl = ref<string | null>(null)
const removeLogoFlag = ref(false)

// Only allow PNG and JPEG/JPG for logos
const allowedLogoTypes = ['image/png', 'image/jpeg', 'image/jpg']

function revokeLogoObjectUrl() {
	if (logoObjectUrl.value && logoObjectUrl.value.startsWith('blob:')) {
		URL.revokeObjectURL(logoObjectUrl.value)
	}
	logoObjectUrl.value = null
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

// When user selects a logo, validate then open cropper modal
async function handleLogoChange(event: any) {
	const file: File | undefined = event.target?.files?.[0]
	if (!file) return
	const ok = await validateLogoFile(file)
	if (!ok) return
	openLogoCropper(file)
}

async function handleLogoDrop(event: any) {
	event.preventDefault()
	const file: File | undefined = event.dataTransfer?.files?.[0]
	if (!file) return
	const ok = await validateLogoFile(file)
	if (!ok) return
	openLogoCropper(file)
}

function allowLogoDrop(event: any) { event.preventDefault() }

// --- Logo cropper (profile-style canvas editor) ---
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

// Pan controls
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

// Touch pan/zoom
function getTouchDistance(touches: TouchList): number {
	if (touches.length < 2) return 0
	const t1 = touches[0], t2 = touches[1]
	const dx = t1.clientX - t2.clientX
	const dy = t1.clientY - t2.clientY
	return Math.sqrt(dx * dx + dy * dy)
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

	// Start the beautiful loader FIRST
	simulateLoading()

	try {
		const blob = await new Promise<Blob | null>((resolve) => {
			logoCanvas.value!.toBlob((b) => resolve(b), 'image/png')
		})

		if (!blob) throw new Error('Failed to generate image')

		const newFile = new File([blob], logoPendingFile.name, { type: 'image/png' })

		selectedLogoFile.value = newFile
		revokeLogoObjectUrl()
		logoObjectUrl.value = URL.createObjectURL(newFile)
		formData.value.logoUrl = logoObjectUrl.value
		removeLogoFlag.value = false

		// Now safe to close modal
		showLogoCropModal.value = false

		// Cleanup
		if (logoImageSrc.value?.startsWith('blob:')) {
			URL.revokeObjectURL(logoImageSrc.value)
		}
		logoImageSrc.value = null
		logoSelectedImage.value = null
		logoPendingFile = null

	} catch (err) {
		console.error('Crop error:', err)
		toast.error('Failed to process image')
		isLoadingFile.value = false
	}
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
	formData.value.logoUrl = ''        // clear preview
	revokeLogoObjectUrl()              // clean blob URL
}

// cleanup object URLs on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
	revokeLogoObjectUrl()
})

// modal binding
const showFormModal = computed({
	get: () => showCreateForm.value || showEditForm.value,
	set: (val: boolean) => { if (!val) { showCreateForm.value = false; showEditForm.value = false } }
})

onMounted(() => {
	getAllManufacturers()
	getAllBrands(1)
})

watch(itemsPerPage, () => {
	getAllBrands(1)
})

watch(showFormModal, (val) => {
	if (!val) closeForm()
})

</script>
<style scoped>
/* Custom grid pattern for the background */
.bg-grid-pattern {
	background-image:
		linear-gradient(rgba(0, 0, 0, 0.1) 1px, transparent 1px),
		linear-gradient(90deg, rgba(0, 0, 0, 0.1) 1px, transparent 1px);
}

/* Enhanced range slider styling */
input[type="range"].slider-thumb {
	-webkit-appearance: none;
	appearance: none;
	background: linear-gradient(to right, #3b82f6 0%, #3b82f6 calc((var(--value, 50) - 50) * 100 / 250), #e5e7eb calc((var(--value, 50) - 50) * 100 / 250), #e5e7eb 100%);
	border-radius: 10px;
	height: 6px;
}

input[type="range"].slider-thumb::-webkit-slider-thumb {
	-webkit-appearance: none;
	appearance: none;
	width: 20px;
	height: 20px;
	background: #3b82f6;
	border: 2px solid #ffffff;
	border-radius: 50%;
	cursor: pointer;
	box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
	transition: all 0.2s ease;
}

input[type="range"].slider-thumb::-webkit-slider-thumb:hover {
	transform: scale(1.1);
	box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
}

input[type="range"].slider-thumb::-moz-range-thumb {
	width: 20px;
	height: 20px;
	background: #3b82f6;
	border: 2px solid #ffffff;
	border-radius: 50%;
	cursor: pointer;
	box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
	transition: all 0.2s ease;
	border: none;
}

input[type="range"].slider-thumb::-moz-range-thumb:hover {
	transform: scale(1.1);
	box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
}

/* Smooth transitions for all interactive elements */
button {
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

canvas {
	transition: transform 0.15s ease;
}
</style>