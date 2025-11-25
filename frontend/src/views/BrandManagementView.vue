<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Brand Management</h1>
			</div>
			<button @click="selectedBrand = null; showCreateForm = true"
				class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
				<PackagePlus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
				New Brand
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white px-6 py-4 border-b border-gray-200 dark:border-gray-600 mb-2 shadow-sm">
			<div class="flex items-center justify-between">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white">Brand Management</h2>
				<div class="flex items-center space-x-4">
					<div class="relative">
						<Search class="absolute left-3 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input
							v-model="searchTerm"
							type="text"
							placeholder="Search Brand..."
							class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div class="flex items-center space-x-2">
						<Funnel class="w-4 h-4 text-gray-500 dark:text-gray-400" />
						<select
						v-model="statusFilter"
						class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="all">All Status</option>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
							<option value="archive">Archived</option>
						</select>
					</div>
					<div class="flex items-center space-x-2">
						<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
						<select
							id="per-page"
							v-model="itemsPerPage"
							class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded 
								focus:ring-2 focus:ring-green-500 focus:border-transparent 
								bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- Card View -->
		<ContentLoader v-if="tableLoader"/>
		<div
		v-if="filteredBrands && filteredBrands.length > 0"
		class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
		>
		<div
			v-for="brand in filteredBrands"
			:key="brand.id"
			class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow"
		>
			<div class="flex items-start justify-between mb-4">
				<div>
					<div class="flex items-center gap-3">
					<!-- Icon -->
					<div class="p-2 bg-green-100 rounded-lg">
						<Package class="w-5 h-5 text-green-600" />
					</div>

					<div class="flex flex-col">
						<h3 class="font-semibold text-gray-900 dark:text-white">
						 {{ brand.brandName }}
						</h3>
						<span
							:class="[
							'inline-flex px-2 py-1 text-xs rounded-full w-fit',
							brand.brandStatus === 0
							? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
							: brand.brandStatus === 1
							? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
							: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
						]"
						>
						{{ brand.brandStatus === 0 ? 'Active' : brand.brandStatus === 1 ? 'Inactive' : 'Archived' }}
						</span>
					</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="flex items-center space-x-2">
					<!-- View -->
					<button
						@click="viewBrand = brand"
						class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
					>
						<Eye class="w-5 h-4" />
					</button>

					<!-- Edit -->
					<button
						@click="editBrand(brand)"
						class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
					>
						<SquarePen class="w-4 h-4" />
					</button>

					<!-- Toggle Active / Inactive / Reactivate -->
					<button
						@click="handleToggleStatus(brand.id, brand.brandStatus)"
						:class="brand.brandStatus === 0
						? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
						: 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'"
						:title="brand.brandStatus === 0
						? 'Deactivate'
						: (brand.brandStatus === 1 ? 'Activate' : 'Reactivate')"
					>
						<component
						:is="brand.brandStatus === 0 ? CircleX : CircleCheck"
						class="w-4 h-4"
						/>
					</button>

					<!-- Archive when not Archived -->
					<button
						v-if="brand.brandStatus !== 2"
						@click="handleArchiveBrand(brand.id)"
						class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
						title="Archive"
					>
						<Archive class="w-4 h-4" />
					</button>

					<!-- Delete only when Archived -->
					<button
						v-if="brand.brandStatus === 2"
						@click="handleDeleteBrand(brand.id)"
						class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
						title="Delete"
					>
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
					<div class="flex items-center gap-2 flex-wrap" v-if="getActiveSizes(brand).length > 0 || getInactiveCount(brand) > 0">
						<strong>Available Sizes:</strong>
						<span
							v-for="sizeObj in getActiveSizes(brand).slice(0, 4)"
							:key="sizeObj.id || sizeObj.size"
							class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 ml-1 mt-1"
						>
							{{ sizeObj.size }}
						</span>
						<span
							v-if="getActiveSizes(brand).length > 4"
							class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 ml-1 mt-1"
						>
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

		<div
		v-else-if="filteredBrands && filteredBrands.length === 0 && !tableLoader"
		class="">
			<div class="flex flex-col items-center justify-center gap-2 text-center">
				<Package class="w-10 h-10 mb-1 text-gray-700" />
				<span class="text-gray-600 dark:text-gray-300">No brands found.</span>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination v-if="filteredBrands && filteredBrands.length > 0" :pagination="pagination" @update:page="getAllBrands" />
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
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span class="text-red-500">*</span></label>
							<div class="relative">
								<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<input
								v-model="formData.brandName"
								type="text"
								required
								placeholder="Brand Name"
								class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
										focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>

						<!-- Status -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Status<span class="text-red-500">*</span></label>
							<div class="relative">
								<CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select
									v-model="formData.brandStatus"
									required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option :value="0">Active</option>
									<option :value="1">Inactive</option>
									<option :value="2">Archived</option>
								</select>
							</div>
						</div>

						<!-- Manufacturer -->
						<div>
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Manufacturer<span class="text-red-500">*</span></label>
							<div class="relative">
								<Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<select
									v-model="formData.manufacturerId"
									required
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option
										v-for="opt in manufacturerOptions"
										:key="opt.value"
										:value="opt.value"
									>
										{{ opt.label }}
									</option>
								</select>
							</div>
						</div>

						<div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">MUE (Medically Unlikely Edits)<span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                v-model.number="formData.mue"
                                type="number"
                                min="1"
                                required
                                placeholder="Enter MUE value"
                                class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum units allowed per day per patient</p>
                        </div>

                        <div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Description (Optional)</label>
							<div class="relative">
								<Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
								<textarea
									v-model="formData.description"
									placeholder="Description"
									rows="3"
									class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
											focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-vertical"
								></textarea>
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
						<div
							v-for="(graftSize, index) in formData.graftSizes"
							:key="graftSize.id || index"
							:class="index > 0 ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : ''"
						>
							<div class="flex items-center justify-between mb-2">
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">Size {{ index + 1 }}</span>
								<button
									v-if="index > 0"
									type="button"
									@click="removeGraftSize(index)"
									class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
								>
									<Trash2 class="w-4 h-4" />
								</button>
							</div>
							<div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Size (e.g., 2cm x 2cm)</label>
									<div class="relative">
										<RulerDimensionLine class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model="graftSize.size"
										type="text"
										placeholder="2cm x 2cm"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Area (cm²)</label>
									<div class="relative">
										<Diameter class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model.number="graftSize.area"
										type="number"
										placeholder="0"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Price</label>
									<div class="relative">
										<DollarSign class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model.number="graftSize.price"
										type="number"
										step="0.01"
										placeholder="0.00"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Stock</label>
									<div class="relative">
										<Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
										<input
										v-model.number="graftSize.stock"
										type="number"
										min="0"
										placeholder="0"
										class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
												focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
										/>
									</div>
								</div>
							</div>
						</div>
						<button
							type="button"
							@click="addGraftSize"
							class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors w-full sm:w-auto"
						>
							<Plus class="w-4 h-4 mr-2" />
							Add Size
						</button>
					</div>
				</div>

				<!-- Modal Footer -->
				<div class="flex justify-end space-x-3 pt-4">
					<button
						type="button"
						@click="closeForm"
						class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
							text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
					>
						Cancel
					</button>
					<button
						type="submit"
						class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
					>
						{{ showCreateForm ? 'Create Brand' : 'Update Brand' }}
					</button>
				</div>
			</form>
		</BaseModal>

		<!-- Show Brand Details -->
		<BaseModal v-model="showBrandDetailsModal" title="Brand Details">
			<template v-if="viewBrand">
				<div class="space-y-6 p-4 sm:p-2">
					
					<div class="flex items-start justify-between">
						<div class="flex items-center space-x-4">
							<div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
								<PackageOpen class="w-6 h-6 text-blue-600 dark:text-blue-400" />
							</div>
							<div>
								<h2 class="text-2xl font-bold text-gray-900 dark:text-white">
									{{ viewBrand.brandName }}
								</h2>
							</div>
						</div>

						<span
							:class="[
								'inline-flex px-3 py-1 text-sm font-medium rounded-full w-fit capitalize transition-colors duration-200',
								viewBrand.brandStatus === 0
									? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400'
									: viewBrand.brandStatus === 1
									? 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400'
									: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400'
							]"
						>
							{{ viewBrand.brandStatus === 0 ? 'Active' : viewBrand.brandStatus === 1 ? 'Inactive' : 'Archived' }} 
						</span>
					</div>

					<hr class="border-gray-200 dark:border-gray-700">

					<div>
						<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Product Overview</h3>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							
							<div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
								<div class="flex items-start gap-3">
									<Factory class="w-5 h-5 text-indigo-500 dark:text-indigo-400 flex-shrink-0 mt-0.5" />
									<div>
										<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-0.5">Manufacturer</h4>
										<p class="text-base text-gray-900 dark:text-gray-100 font-medium">
											{{ viewBrand.manufacturerName || 'No Manufacturer' }}
										</p>
									</div>
								</div>
							</div>

							<div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
								<div class="flex items-start gap-3">
									<PackageSearch class="w-5 h-5 text-teal-500 dark:text-teal-400 flex-shrink-0 mt-0.5" />
									<div>
										<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-0.5">Product Type</h4>
										<p class="text-base text-gray-900 dark:text-gray-100 font-medium">
											Graft
										</p>
									</div>
								</div>
							</div>

							<div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
								<div class="flex items-start gap-3">
									<Ruler class="w-5 h-5 text-purple-500 dark:text-purple-400 flex-shrink-0 mt-0.5" />
									<div>
										<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-0.5">Available Sizes</h4>
										<p class="text-base text-gray-900 dark:text-gray-100 font-medium">
											{{ getActiveSizes(viewBrand).length }} size options
										</p>
									</div>
								</div>
							</div>

							<div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
								<div class="flex items-start gap-3">
									<TriangleAlert class="w-5 h-5 text-orange-500 dark:text-orange-400 flex-shrink-0 mt-0.5" />
									<div>
										<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-0.5">MUE Limit</h4>
										<p class="text-base text-gray-900 dark:text-gray-100 font-medium">
											{{ viewBrand.mue || 'N/A' }} units / day / patient
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<hr class="border-gray-200 dark:border-gray-700">

					<div>
						<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
						<div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
							<p class="text-gray-600 dark:text-gray-400 italic" v-if="!viewBrand.description">
								No detailed description provided for this brand.
							</p>
							<p class="text-gray-800 dark:text-gray-300 whitespace-pre-wrap" v-else>
								{{ viewBrand.description }}
							</p>
						</div>
					</div>

					<hr class="border-gray-200 dark:border-gray-700">

					<div>
						<h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Graft Sizes</h4>
						
						<div v-if="viewBrand.graftSizes.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
							<p>No graft sizes are currently configured for this brand.</p>
						</div>
						
						<div v-else class="border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden shadow-md">
							
							<button
								type="button"
								class="w-full flex items-center justify-between px-4 py-3 text-left bg-gray-100 dark:bg-gray-700 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors focus:outline-none" 
								@click="graftSizesExpanded = !graftSizesExpanded"
							>
								<span class="font-bold text-gray-900 dark:text-white">View All Sizes ({{ viewBrand.graftSizes.length }})</span>
								<ChevronDown :class="{ 'rotate-180': graftSizesExpanded }" class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-transform duration-300" />
							</button>
							
							<div v-show="graftSizesExpanded" class="divide-y divide-gray-200 dark:divide-gray-700 max-h-80 overflow-y-auto">
								<div 
									v-for="size in viewBrand.graftSizes" 
									:key="size.id || size.size" 
									class="px-4 py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
								>
									<div class="flex justify-between items-center">
										<div class="flex-1 min-w-0">
											<h5 class="text-base font-semibold text-gray-900 dark:text-white truncate mb-1">{{ size.size }}</h5>
											<div class="flex flex-wrap gap-x-4 text-sm text-gray-600 dark:text-gray-400">
												<span class="flex items-center">
													Area: <span class="ml-1 font-medium text-gray-700 dark:text-gray-300">{{ size.area }} cm²</span>
												</span>
												<span class="flex items-center">
													Price: <span class="ml-1 font-medium text-gray-700 dark:text-gray-300">{{ size.price ? `$${size.price.toFixed(2)}` : 'N/A' }}</span>
												</span>
												<span class="flex items-center">
													Stock: <span class="ml-1 font-medium text-gray-700 dark:text-gray-300">{{ size.stock || 0 }}</span>
												</span>
											</div>
										</div>
										
										<span 
											class="ml-4 flex-shrink-0 inline-flex px-2 py-0.5 text-xs font-medium rounded-full"
											:class="[
												size.graftStatus === 0
													? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400'
													: size.graftStatus === 1
													? 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400'
													: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400'
											]"
										>
											{{ size.graftStatus === 0 ? 'Active' : size.graftStatus === 1 ? 'Inactive' : 'Archived' }}
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>
			
			<template #actions>
				<div class="p-4 sm:p-6 pt-0">
					<button
						type="button"
						@click="viewBrand = null"
						class="w-full justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors shadow-sm font-medium"
					>
						Close
					</button>
				</div>
			</template>
		</BaseModal>

	</div>
</template>

<script setup lang="ts">
import { useToast } from "vue-toastification"
import Swal from "sweetalert2"
import { ref, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import { Package, PackagePlus, Eye, SquarePen, Trash2, Archive, CircleCheck, CircleX, Factory, TriangleAlert, Hash, RulerDimensionLine, Diameter, DollarSign, PencilRuler, Plus, Search, Funnel, Globe, Ruler, PackageOpen, PackageSearch, ChevronDown } from 'lucide-vue-next'
import api from '@/services/api'
import axios from "axios";

const toast = useToast()

// types
interface GraftSize {
  id?: number  // Optional: graft_size_id from backend (for edit/delete)
  size: string
  area: number | null
  price: number | null
  graftStatus?: number  // Optional: 0=Active, 1=Inactive, 2=Archive
  stock?: number  // Added stock field
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
  description: '',
  graftSizes: [{ size: '', area: null, price: null, stock: 0 }] as GraftSize[]  // Added stock field with default value
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
      description: b.description,
     graftSizes: (b.graftSizes || []).map((s: any) => ({
        id: s.id,                        // graft_size_id
        size: s.size,
        area: s.area,
        price: s.price,
        stock: s.stock || 0,  // Added stock field
        graftStatus: s.graftStatus || 0  // Default active
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
		description: b.description || '',
		graftSizes: b.graftSizes.length > 0 
		? b.graftSizes.map((s: GraftSize) => ({  // Preserve id/graftStatus
			id: s.id,
			size: s.size,
			area: s.area,
			price: s.price,
			stock: s.stock || 0,
			graftStatus: s.graftStatus || 0
			}))
		: [{ size: '', area: null, price: null }]
	}
  	showEditForm.value = true
}

function addGraftSize() {
  formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0 })  // Added stock field with default value
}

function removeGraftSize(index: number) {
  formData.value.graftSizes.splice(index, 1)
  if (formData.value.graftSizes.length === 0) {
    formData.value.graftSizes.push({ size: '', area: null, price: null })
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

    // Append graft sizes as JSON
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
  } catch (error) {
    console.error(error.response?.data || error)
    toast.error('Something went wrong!')
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
    description: '',
    graftSizes: [{ size: '', area: null, price: null }]
  }
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

// file handling
const selectedFile = ref<File | null>(null)
function handleFileChange(event: any) { selectedFile.value = event.target.files[0] }
function handleDrop(event: any) { event.preventDefault(); selectedFile.value = event.dataTransfer.files[0] }
function allowDrop(event: any) { event.preventDefault() }

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
