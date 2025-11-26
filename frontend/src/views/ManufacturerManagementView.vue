<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manufacturer Management</h1>
                <p class="text-gray-600 dark:text-gray-400">View and update manufacturer information with ease.</p>
            </div>
            <button @click="selectedManufacturer = null; showCreateForm = true"
                class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
                <HousePlus class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" />
                Add Manufacturer
            </button>
        </div>
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="flex-1">
                    <div class="relative">
                        <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Search Manufacturer..."
                            class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                        />
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
                            <option value="archive">Archived</option>
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
                        class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
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
        <!-- Card View -->
        <ContentLoader v-if="tableLoader"/>
        <div
            v-if="filteredManufacturers && filteredManufacturers.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
        >
            <div
                v-for="manufacturer in filteredManufacturers"
                :key="manufacturer.id"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <!-- Logo or Icon -->
                            <div v-if="manufacturer.logoUrl" class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100">
                                <img :src="manufacturer.logoUrl" :alt="`${manufacturer.manufacturerName} logo`" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="p-2 bg-green-100 rounded-lg">
                                <Factory class="w-5 h-5 text-green-600" />
                            </div>
                            <div class="flex flex-col">
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ manufacturer.manufacturerName }}
                                </h3>
                                <span
                                    :class="[
                                        'inline-flex px-2 py-1 text-xs rounded-full w-fit',
                                        manufacturer.manufacturerStatus === 0
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                        : manufacturer.manufacturerStatus === 1
                                        ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                    ]"
                                >
                                    {{ manufacturer.manufacturerStatus === 0 ? 'Active' : manufacturer.manufacturerStatus === 1 ? 'Inactive' : 'Archived' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Quick Actions -->
                    <div class="flex items-center space-x-2">
                        <!-- View -->
                        <button
                            @click="viewManufacturer = manufacturer"
                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                        >
                            <Eye class="w-5 h-4" />
                        </button>
                        <!-- Edit -->
                        <button
                            @click="editManufacturer(manufacturer)"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            <SquarePen class="w-4 h-4" />
                        </button>
                        <!-- Toggle Active / Inactive / Reactivate -->
                        <button
                            @click="handleToggleStatus(manufacturer.id, manufacturer.manufacturerStatus)"
                            :class="manufacturer.manufacturerStatus === 0
                            ? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
                            : 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'"
                            :title="manufacturer.manufacturerStatus === 0
                            ? 'Deactivate'
                            : (manufacturer.manufacturerStatus === 1 ? 'Activate' : 'Reactivate')"
                        >
                            <component
                                :is="manufacturer.manufacturerStatus === 0 ? CircleX : CircleCheck"
                                class="w-4 h-4"
                            />
                        </button>
                        <!-- Archive when not Archived -->
                        <button
                            v-if="manufacturer.manufacturerStatus !== 2"
                            @click="handleArchiveManufacturer(manufacturer.id)"
                            class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                            title="Archive"
                        >
                            <Archive class="w-4 h-4" />
                        </button>
                        <!-- Delete only when Archived -->
                        <button
                            v-if="manufacturer.manufacturerStatus === 2"
                            @click="handleDeleteManufacturer(manufacturer.id)"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                            title="Delete"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <Mail class="w-4 h-4" />
                        <span>{{ manufacturer.primaryEmail }}</span>
                        <template v-if="manufacturer.secondaryEmail">
                            <span class="text-gray-400">|</span>
                            <span>{{ manufacturer.secondaryEmail }}</span>
                        </template>
                    </div>
                    <div class="flex items-center gap-2" v-if="manufacturer.contactNumber">
                        <Phone class="w-4 h-4" />
                        <span>{{ manufacturer.contactNumber }}</span>
                    </div>
                    <div class="flex items-center gap-2" v-if="manufacturer.website">
                        <Globe class="w-4 h-4" />
                        <span>{{ manufacturer.website }}</span>
                    </div>
                    <div class="flex items-center gap-2" v-if="manufacturer.filename">
                        <FileText class="w-4 h-4" />
                        <button @click="downloadIVRForm(manufacturer.id)" class="hover:underline text-sm">IVR form available for download</button>
                    </div>
                    <hr />
                    <div class="flex items-center gap-2">
                        <strong>Contact Person:</strong>
                        <span>{{ manufacturer.contactPerson }}</span>
                    </div>
                    <hr />
                    <div class="flex items-center gap-2 flex-wrap" v-if="getActiveBrands(manufacturer).length > 0 || getInactiveBrandCount(manufacturer) > 0">
                        <strong>Brands:</strong>
                        <span
                            v-for="brandObj in getActiveBrands(manufacturer).slice(0, 4)"
                            :key="brandObj.id || brandObj.brandName"
                            class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 ml-1 mt-1"
                        >
                            {{ brandObj.brandName }}
                        </span>
                        <span
                            v-if="getActiveBrands(manufacturer).length > 4"
                            class="inline-flex px-2 py-1 text-xs rounded-full w-fit bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 ml-1 mt-1"
                        >
                            +{{ getActiveBrands(manufacturer).length - 4 }} more
                        </span>
                        <span v-if="getInactiveBrandCount(manufacturer) > 0" class="text-xs text-gray-500 ml-1">
                            (+{{ getInactiveBrandCount(manufacturer) }} inactive)
                        </span>
                    </div>
                    <p v-else class="text-xs text-gray-500">No brands available</p>
                </div>
            </div>
        </div>
        <div
            v-else-if="filteredManufacturers && filteredManufacturers.length === 0 && !tableLoader"
            class=""
        >
            <div class="flex flex-col items-center justify-center gap-2 text-center">
                <Factory class="w-10 h-10 mb-1 text-gray-700" />
                <span class="text-gray-600 dark:text-gray-300">No manufacturers found.</span>
            </div>
        </div>
        <template v-if="!tableLoader">
            <Pagination v-if="filteredManufacturers && filteredManufacturers.length > 0" :pagination="pagination" @update:page="getAllManufacturers" />
        </template>
        <!-- Create/Edit Form Modal -->
        <BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Manufacturer' : 'Edit Manufacturer'">
            <form @submit.prevent="handleSubmitForm" class="space-y-4">
                <!-- Manufacturer Information -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <Building2 class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Manufacturer Information</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Manufacturer Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Name<span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="formData.manufacturerName"
                                    type="text"
                                    required
                                    placeholder="Manufacturer Name"
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
                                    v-model="formData.manufacturerStatus"
                                    required
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                >
                                    <option :value="0">Active</option>
                                    <option :value="1">Inactive</option>
                                    <option :value="2">Archived</option>
                                </select>
                            </div>
                        </div>
                        <!-- Website -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Website (Optional)</label>
                            <div class="relative">
                                <Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="formData.website"
                                    type="text"
                                    placeholder="https://"
                                    @blur="fixWebsite"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                      
                        <!-- Address -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Address (Optional)</label>
                            <div class="relative">
                                <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="formData.address"
                                    type="text"
                                    placeholder="Manufacturer Address"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Logo Upload Section (NEW: Dedicated field for logo upload) -->
                <!-- <div>
                    <div class="flex items-center gap-2 mb-2">
                        <Image class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Manufacturer Logo (Optional)</h3>
                    </div>
                    <div
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
                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6h.1a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-semibold">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">JPEG, JPG, or PNG (max. 2MB)</p>
                        </label>
                    </div>
                    <div v-if="selectedLogoFile" class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <Image class="w-4 h-4 text-gray-400" />
                        <span>Selected: <span class="font-medium">{{ selectedLogoFile.name }}</span></span>
                    </div>
                    <div v-else-if="formData.logoUrl" class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <Image class="w-4 h-4 text-gray-400" />
                        <span>Current: <img :src="formData.logoUrl" class="w-6 h-6 rounded object-cover inline ml-1" /> {{ formData.logoUrl.split('/').pop() }}</span>
                        <button @click="removeCurrentLogo" class="ml-2 text-red-500 hover:text-red-700 text-xs">Remove</button>
                    </div>
                </div> -->
                <!-- Contact Information -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <Contact class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Contact Information</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Contact Person -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Person<span class="text-red-500">*</span></label>
                            <div class="relative">
                                <UserRound class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="formData.contactPerson"
                                    type="text"
                                    required
                                    placeholder="Contact Person"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                        <!-- Contact Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Number<span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Phone class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="formData.contactNumber"
                                    type="text"
                                    required
                                    placeholder="Phone/Tel"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                        <!-- Primary Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Primary Email<span class="text-red-500">*</span></label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="formData.primaryEmail"
                                    type="email"
                                    required
                                    placeholder="example@manufacturer.com"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                        <!-- Secondary Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Secondary Email (Optional)</label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="formData.secondaryEmail"
                                    type="email"
                                    placeholder="example@manufacturer.com"
                                    class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- IVR Information -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <FilePenLine class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">IVR Information</h3>
                    </div>
                    <div
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
                        <label for="ivr-upload" class="flex flex-col items-center justify-center text-center cursor-pointer">
                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6h.1a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-semibold">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PDF or DOCX (max. 10MB)</p>
                        </label>
                    </div>
                    <!-- File Preview -->
                    <div v-if="selectedFile" class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <FileText class="w-4 h-4 text-gray-400" />
                        <span>Selected file: <span class="font-medium">{{ selectedFile.name }}</span></span>
                        <button @click="removeSelectedIvr" class="ml-2 px-2 py-1 bg-red-500 text-white rounded text-xs">Remove</button>
                    </div>
                    <div v-else-if="formData.filename" class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <FileText class="w-4 h-4 text-gray-400" />
                        <span>Existing file: <span class="font-medium">{{ formData.filename }}</span></span>
                        <button @click="previewExistingIvr" class="ml-2 text-blue-500 hover:text-blue-700 text-xs">Preview</button> | 
                        <button v-if="!removeIvrFlag" @click="removeExistingIvr" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
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
                        {{ showCreateForm ? 'Create Manufacturer' : 'Update Manufacturer' }}
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
                            <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
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
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        
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
                            <div class="absolute inset-0 flex justify-between items-center pointer-events-none px-1">
                                <span class="text-xs text-gray-500">50%</span>
                                <span class="text-xs text-gray-500">300%</span>
                            </div>
                        </div>
                        
                        <button 
                            @click="logoZoomIn"
                            :disabled="logoScale >= 3"
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow"
                        >
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 justify-center">
                    <button
                        @click="logoResetPosition"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset View
                    </button>
                    
                    <button
                        @click="logoSelectNewImage"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200 shadow-sm hover:shadow"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Apply Crop
                </button>
            </div>
        </template>
    </BaseModal>
        <BaseModal v-model="showManufacturerDetailsModal" title="Manufacturer Details">
            <template v-if="viewManufacturer">
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex items-center space-x-4">
                        <!-- Logo or Icon in Details -->
                        <div v-if="viewManufacturer.logoUrl" class="w-16 h-16 rounded-full overflow-hidden bg-gray-100">
                            <img :src="viewManufacturer.logoUrl" :alt="`${viewManufacturer.manufacturerName} logo`" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                            <Factory class="w-8 h-8 text-green-500" />
                        </div>
                        <div>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ viewManufacturer.manufacturerName }}
                            </p>
                            <span
                                :class="[
                                    'inline-flex px-2 py-1 text-xs rounded-full w-fit',
                                    viewManufacturer.manufacturerStatus === 0
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                    : viewManufacturer.manufacturerStatus === 1
                                    ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                ]"
                            >
                                {{ viewManufacturer.manufacturerStatus === 0 ? 'Active' : viewManufacturer.manufacturerStatus === 1 ? 'Inactive' : 'Archived' }}
                            </span>
                        </div>
                    </div>
                    <!-- Contact Info -->
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3">
                                    <Mail class="w-5 h-5 text-purple-500" />
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Primary Email</p>
                                        <p class="text-gray-900 dark:text-white">{{ viewManufacturer.primaryEmail }}</p>
                                    </div>
                                </div>
                                <div v-if="viewManufacturer.secondaryEmail" class="flex items-center space-x-3">
                                    <Mail class="w-5 h-5 text-indigo-500" />
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Secondary Email</p>
                                        <p class="text-gray-900 dark:text-white">{{ viewManufacturer.secondaryEmail }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <UserRound class="w-5 h-5 text-orange-500" />
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Contact Person</p>
                                        <p class="text-gray-900 dark:text-white">{{ viewManufacturer.contactPerson }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3">
                                    <Phone class="w-5 h-5 text-green-500" />
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Contact Number</p>
                                        <p class="text-gray-900 dark:text-white">{{ viewManufacturer.contactNumber }}</p>
                                    </div>
                                </div>
                                <div v-if="viewManufacturer.website" class="flex items-center space-x-3">
                                    <Globe class="w-5 h-5 text-blue-500" />
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Website</p>
                                        <a
                                            :href="viewManufacturer.website"
                                            target="_blank"
                                            class="text-blue-600 underline"
                                        >{{ viewManufacturer.website }}</a>
                                    </div>
                                </div>
                                <div v-if="viewManufacturer.address" class="flex items-center space-x-3">
                                    <MapPin class="w-5 h-5 text-red-500" />
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">Address</p>
                                        <p class="text-gray-900 dark:text-white">{{ viewManufacturer.address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- IVR Form Information -->
                    <div
                        v-if="viewManufacturer.filename"
                        class="mt-6 rounded-lg border border-blue-100 bg-blue-50 p-4 dark:bg-blue-900/10 dark:border-blue-900/20"
                    >
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            IVR Form Information
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 items-center gap-4">
                            <!-- Form Type -->
                            <div class="flex items-center gap-3">
                                <!-- PDF icon -->
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-50">
                                    <FileText class="w-6 h-6 text-red-500" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Form Type</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-medium dark:bg-red-900/20 dark:text-red-400">
                                        PDF
                                    </span>
                                </div>
                            </div>
                            <!-- Template Download -->
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100">
                                    <Download class="w-6 h-6 text-blue-500" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Template</p>
                                    <button @click="downloadIVRForm(viewManufacturer.id)" class="text-blue-600 hover:underline text-sm font-medium">
                                        Download IVR Form
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Brands Information -->
                    <div
                        v-if="viewManufacturer.brands && viewManufacturer.brands.length > 0"
                        class="mt-6 rounded-lg border border-gray-100 bg-gray-50 p-4 dark:bg-gray-900/10 dark:border-gray-900/20"
                    >
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            Associated Brands
                        </h4>
                        <button
                            type="button"
                            class="w-full flex items-center justify-between px-4 py-3 text-left bg-gray-100 dark:bg-gray-700 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors focus:outline-none rounded mb-2"
                            @click="brandsExpanded = !brandsExpanded"
                        >
                            <span class="font-bold text-gray-900 dark:text-white">View All Brands ({{ viewManufacturer.brands.length }})</span>
                            <ChevronDown :class="{ 'rotate-180': brandsExpanded }" class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-transform duration-300" />
                        </button>
                        <div v-show="brandsExpanded" class="divide-y divide-gray-200 dark:divide-gray-700 max-h-80 overflow-y-auto">
                            <div
                                v-for="brand in viewManufacturer.brands"
                                :key="brand.id"
                                class="px-4 py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <div class="flex justify-between items-center">
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-base font-semibold text-gray-900 dark:text-white truncate mb-1">{{ brand.brandName }}</h5>
                                    </div>
                                    <span
                                        class="ml-4 flex-shrink-0 inline-flex px-2 py-0.5 text-xs font-medium rounded-full"
                                        :class="[
                                            brand.brandStatus === 0
                                                ? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400'
                                                : brand.brandStatus === 1
                                                ? 'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400'
                                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400'
                                        ]"
                                    >
                                        {{ brand.brandStatus === 0 ? 'Active' : brand.brandStatus === 1 ? 'Inactive' : 'Archived' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <p>No brands are currently associated with this manufacturer.</p>
                    </div>
                </div>
            </template>
            <template #actions>
                <div class="p-4">
                    <button
                        type="button"
                        @click="viewManufacturer = null"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
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
import { Search, Funnel, Eye, SquarePen, CircleCheck, CircleX, Trash2, HousePlus, FileText, Factory, Globe, Building2, Mail, Contact, Phone, UserRound, FilePenLine, MapPin, Archive, Download, ChevronDown, Image } from 'lucide-vue-next'  // Added Image icon
import api from '@/services/api'
import axios from "axios";
const toast = useToast()
// types
interface Brand {
  id: number
  brandName: string
  brandStatus: number
}
interface Manufacturer {
  id: number
  manufacturerName: string
  primaryEmail: string
  secondaryEmail?: string
  website?: string
  address?: string
  contactPerson: string
  contactNumber: string
  filename?: string
  manufacturerStatus: number
  logoUrl?: string | null  // URL for logo
  brands?: Brand[]
  createdAt: string
  updatedAt: string
}
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const itemsPerPage = ref(10)
const manufacturers = ref<Manufacturer[]>([])
const tableLoader = ref(false)
const selectedManufacturer = ref<Manufacturer | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const viewManufacturer = ref<Manufacturer | null>(null)
const brandsExpanded = ref(false)
const showManufacturerDetailsModal = computed({
  get: () => viewManufacturer.value !== null,
  set: (value: boolean) => {
    if (!value) {
      viewManufacturer.value = null
      brandsExpanded.value = false
    }
  },
})
// Download file securely (use central `api` client and include auth header)
const downloadIVRForm = async (id: number) => {
    try {
        const response = await api.get(`/management/manufacturers/${id}/download`, {
            responseType: 'blob',
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` },
        });
        const blob = new Blob([response.data]);
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'IVR_Form.pdf';
        link.click();
        URL.revokeObjectURL(link.href);
    } catch (error) {
        console.error('Download failed:', error);
        toast.error('Failed to download IVR form.');
    }
};
// Preview the existing IVR in a new tab (fetches blob and opens)
const previewExistingIvr = async () => {
    const id = selectedManufacturer.value?.id
    if (!id) {
        toast.error('No manufacturer selected for preview.')
        return
    }
    try {
        const response = await api.get(`/management/manufacturers/${id}/download`, {
            responseType: 'blob',
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` },
        })
        const contentType = response.headers['content-type'] || 'application/pdf'
        const blob = new Blob([response.data], { type: contentType })
        const url = URL.createObjectURL(blob)
        window.open(url, '_blank')
        setTimeout(() => URL.revokeObjectURL(url), 30000)
    } catch (error) {
        console.error('Preview failed:', error)
        toast.error('Failed to preview IVR form.')
    }
}

// Remove an IVR file that the user selected in the form (not the existing stored file)
function removeSelectedIvr() {
    selectedFile.value = null
    revokeIvrPreview()
    removeIvrFlag.value = false
}

// Helper to download IVR for the currently selected manufacturer (safely checks id)
function downloadSelectedManufacturerIvr() {
    const id = selectedManufacturer.value?.id
    if (!id) {
        toast.error('No manufacturer selected for download.')
        return
    }
    downloadIVRForm(id)
}
const formData = ref({
  manufacturerName: '',
  primaryEmail: '',
  secondaryEmail: '',
  website: '',
  address: '',
  contactPerson: '',
  contactNumber: '',
  filename: '',
  manufacturerStatus: 0,
  logoUrl: '',  // For preview in edit mode
})
async function getAllManufacturers(page = 1) {
  tableLoader.value = true
  try {
    const { data } = await api.get(`/management/manufacturers?page=${page}&per_page=${itemsPerPage.value}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    manufacturers.value = data.manufacturerData.map((m: any) => ({
      id: m.id,
      manufacturerName: m.manufacturerName,
      primaryEmail: m.primaryEmail,
      secondaryEmail: m.secondaryEmail,
      website: m.website,
      address: m.address,
      contactPerson: m.contactPerson,
      contactNumber: m.contactNumber,
      filename: m.filename,
      manufacturerStatus: m.manufacturerStatus,
      logoUrl: m.logoUrl,  // From API
      brands: (m.brands || []).map((b: any) => ({
        id: parseInt(b.id),
        brandName: b.brandName,
        brandStatus: b.brandStatus,
      })),
      createdAt: m.createdAt,
      updatedAt: m.updatedAt,
    }))
    pagination.value = {
      current_page: data.meta.current_page,
      last_page: data.meta.last_page,
      per_page: data.meta.per_page,
      total: data.meta.total,
    }
  } catch (error) {
    console.error('Error fetching manufacturers:', error)
  } finally {
    tableLoader.value = false
  }
}
function editManufacturer(m: Manufacturer) {
    selectedManufacturer.value = m
     formData.value = {
        manufacturerName: m.manufacturerName || '',
        primaryEmail: m.primaryEmail || '',
        secondaryEmail: m.secondaryEmail || '',
        website: m.website || '',
        address: m.address || '',
        contactPerson: m.contactPerson || '',
        contactNumber: m.contactNumber || '',
        filename: m.filename || '',
        manufacturerStatus: m.manufacturerStatus ?? 0,
        logoUrl: m.logoUrl || '',  // Set for preview
    }
    showEditForm.value = true
}
// submit create/edit form
async function handleSubmitForm() {
  try {
    const form = new FormData()
    // normalize website
    let website = formData.value.website || ''
    if (website && !/^https?:\/\//i.test(website)) {
      website = 'https://' + website
    }
    form.append('manufacturerName', formData.value.manufacturerName)
    form.append('primaryEmail', formData.value.primaryEmail)
    form.append('secondaryEmail', formData.value.secondaryEmail || '')
    form.append('website', website)
    form.append('address', formData.value.address || '')
    form.append('contactPerson', formData.value.contactPerson)
    form.append('contactNumber', formData.value.contactNumber)
    form.append('manufacturerStatus', formData.value.manufacturerStatus.toString())
    if (selectedFile.value) {
      form.append('ivrForm', selectedFile.value)
    }
    if (selectedLogoFile.value) {  // Append logo file
      form.append('logo', selectedLogoFile.value)
    }
        // Pass removal flags so backend can delete existing files when requested
        if (removeLogoFlag.value) {
            form.append('remove_logo', '1')
        }
        if (removeIvrFlag.value) {
            form.append('remove_ivr', '1')
        }
    if (showCreateForm.value) {
      await api.post('/management/manufacturers', form, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'multipart/form-data'
        }
      })
      toast.success('Manufacturer created!')
    } else if (showEditForm.value && selectedManufacturer.value) {
      await api.post(`/management/manufacturers/${selectedManufacturer.value.id}`, form, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'multipart/form-data'
        }
      })
      toast.success('Manufacturer updated!')
    }
    closeForm()
    getAllManufacturers(1)
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
// archive manufacturer
async function handleArchiveManufacturer(id: number) {
  try {
    const result = await Swal.fire({
        title: 'Archive Manufacturer?',
        text: "This action will move the manufacturer to archived status. You can restore it later if needed.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, archive it!',
        cancelButtonText: 'Cancel'
    })
    if (result.isConfirmed) {
      const response = await api.get(`/management/manufacturers/${id}/archive`)
            const data = response.data
            const idx = manufacturers.value.findIndex(m => m.id === id)
            if (idx !== -1) {
                const newStatus = data?.data?.manufacturerStatus ?? data?.data?.manufacturer_status
                manufacturers.value[idx].manufacturerStatus = typeof newStatus !== 'undefined' ? Number(newStatus) : manufacturers.value[idx].manufacturerStatus
            }
      toast.success('Manufacturer archived successfully')
    }
  } catch (error) {
    console.error(error)
    toast.error('Error archiving manufacturer')
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
      title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Manufacturer?`,
      text: `Are you sure you want to ${actionText} this manufacturer?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: `Yes, ${actionText} it!`,
      cancelButtonText: 'Cancel'
    })
    if (result.isConfirmed) {
      const response = await api.get(`/management/manufacturers/${id}/toggle`)
            const data = response.data
            const idx = manufacturers.value.findIndex(m => m.id === id)
            if (idx !== -1) {
                const newStatus = data?.data?.manufacturerStatus ?? data?.data?.manufacturer_status
                manufacturers.value[idx].manufacturerStatus = typeof newStatus !== 'undefined' ? Number(newStatus) : manufacturers.value[idx].manufacturerStatus
            }
      toast.success(`Manufacturer ${actionText}d successfully`)
    }
  } catch (error) {
    console.error(error)
    toast.error(`Error trying to ${actionText} manufacturer`)
  }
}
// delete manufacturer
async function handleDeleteManufacturer(id: number) {
  try {
    const result = await Swal.fire({
      title: 'Delete Manufacturer?',
      text: "This action cannot be undone.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    })
    if (result.isConfirmed) {
      await api.delete(`/management/manufacturers/${id}`)
      manufacturers.value = manufacturers.value.filter(m => m.id !== id)
      toast.success('Manufacturer deleted successfully')
    }
  } catch (error) {
    console.error(error)
    toast.error('Error deleting manufacturer')
  }
}
function closeForm() {
  showCreateForm.value = false
  showEditForm.value = false
  selectedManufacturer.value = null
  formData.value = {
    manufacturerName: '',
    primaryEmail: '',
    secondaryEmail: '',
    website: '',
    address: '',
    contactPerson: '',
    contactNumber: '',
    filename: '',
    manufacturerStatus: 0, // reset to Active
    logoUrl: '',  // Reset preview
  }
  selectedLogoFile.value = null  // Clear selected file
}
// search & filter (unchanged)
const searchTerm = ref('')
const statusFilter = ref('all')
const filteredManufacturers = computed(() => {
  return manufacturers.value.filter(m => {
    const matchesSearch =
      m.manufacturerName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      m.primaryEmail.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'active' && m.manufacturerStatus === 0) ||
      (statusFilter.value === 'inactive' && m.manufacturerStatus === 1) ||
      (statusFilter.value === 'archive' && m.manufacturerStatus === 2)
    return matchesSearch && matchesStatus
  })
})
const getActiveBrands = (manufacturer: Manufacturer) => (manufacturer.brands || []).filter(b => b.brandStatus === 0)
const getInactiveBrandCount = (manufacturer: Manufacturer) => ((manufacturer.brands || []).filter(b => b.brandStatus !== 0)).length
// file handling (IVR)
const selectedFile = ref<File | null>(null)
const ivrPreviewUrl = ref<string | null>(null)
const removeIvrFlag = ref(false)

function revokeIvrPreview() {
    if (ivrPreviewUrl.value && ivrPreviewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(ivrPreviewUrl.value)
    }
    ivrPreviewUrl.value = null
}

function allowDrop(event: any) { event.preventDefault() }
function allowLogoDrop(event: any) { event.preventDefault() }

async function handleFileChange(event: any) {
    const file: File | undefined = event.target?.files?.[0]
    if (!file) return
    // optional: validate file type/size if needed
    selectedFile.value = file
    revokeIvrPreview()
    ivrPreviewUrl.value = URL.createObjectURL(file)
    removeIvrFlag.value = false
}

async function handleDrop(event: any) {
    event.preventDefault()
    const file: File | undefined = event.dataTransfer?.files?.[0]
    if (!file) return
    selectedFile.value = file
    revokeIvrPreview()
    ivrPreviewUrl.value = URL.createObjectURL(file)
    removeIvrFlag.value = false
}

function removeExistingIvr() {
    // mark to remove on submit
    removeIvrFlag.value = true
    selectedFile.value = null
    if (formData.value.filename) formData.value.filename = ''
    revokeIvrPreview()
}

// Logo file handling (NEW)
const selectedLogoFile = ref<File | null>(null)
const logoObjectUrl = ref<string | null>(null)
const removeLogoFlag = ref(false)

// Only allow PNG and JPEG/JPG for logos
const allowedLogoTypes = ['image/png','image/jpeg','image/jpg']

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
        formData.value.logoUrl = logoObjectUrl.value
        removeLogoFlag.value = false
        showLogoCropModal.value = false
        if (logoImageSrc.value && logoImageSrc.value.startsWith('blob:')) URL.revokeObjectURL(logoImageSrc.value)
        logoImageSrc.value = null
        logoSelectedImage.value = null
        logoPendingFile = null
    }, 'image/png')
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
    formData.value.logoUrl = ''
    revokeLogoObjectUrl()
}

// cleanup object URLs on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
    revokeLogoObjectUrl()
    revokeIvrPreview()
})
// Also revoke any crop temp URL if left
// onUnmounted(() => {
//     if (cropImageUrl.value && cropImageUrl.value.startsWith('blob:')) URL.revokeObjectURL(cropImageUrl.value)
// })
// modal binding (unchanged)
const showFormModal = computed({
  get: () => showCreateForm.value || showEditForm.value,
  set: (val: boolean) => { if (!val) { showCreateForm.value = false; showEditForm.value = false } }
})
function fixWebsite() {
  const url = formData.value.website
  if (url && !/^https?:\/\//i.test(url)) {
    formData.value.website = 'https://' + url
  }
}
onMounted(() => {
  getAllManufacturers(1)
})
watch(itemsPerPage, () => {
  getAllManufacturers(1)
})
watch(showFormModal, (val) => {
  if (!val) closeForm()
})
</script>

<style scoped>
/* Custom grid pattern for the background */
.bg-grid-pattern {
    background-image: 
        linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px);
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

