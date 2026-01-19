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
            <input v-model="searchTerm" type="text" placeholder="Search Manufacturer..."
              class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
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
        </div>
        <div class="relative">
          <label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
          <select id="per-page" v-model="itemsPerPage"
            class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
          <ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
        </div>
      </div>
    </div>

    <!-- Card View -->
    <ContentLoader v-if="tableLoader" />
    <div v-if="filteredManufacturers && filteredManufacturers.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="manufacturer in filteredManufacturers" :key="manufacturer.id"
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
          <div>
            <div class="flex items-center gap-3">
              <div v-if="manufacturer.logoUrl" class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100">
                <img :src="manufacturer.logoUrl" :alt="`${manufacturer.manufacturerName} logo`"
                  class="w-full h-full object-cover" />
              </div>
              <div v-else class="p-2 bg-green-100 rounded-lg">
                <Factory class="w-5 h-5 text-green-600" />
              </div>
              <div class="flex flex-col">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                  {{ manufacturer.manufacturerName }}
                </h3>
                <span :class="[
                  'inline-flex px-2 py-1 text-xs rounded-full w-fit',
                  manufacturer.manufacturerStatus === 0
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                    : manufacturer.manufacturerStatus === 1
                      ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                      : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                ]">
                  {{
                    manufacturer.manufacturerStatus === 0
                      ? 'Active'
                      : manufacturer.manufacturerStatus === 1
                        ? 'Inactive'
                        : 'Archived'
                  }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="flex items-center space-x-2">
            <button @click="viewManufacturer = manufacturer"
              class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
              <Eye class="w-5 h-4" />
            </button>
            <button @click="editManufacturer(manufacturer)"
              class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
              <SquarePen class="w-4 h-4" />
            </button>
            <button @click="handleToggleStatus(manufacturer.id, manufacturer.manufacturerStatus)" :class="manufacturer.manufacturerStatus === 0
              ? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
              : 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'
              " :title="manufacturer.manufacturerStatus === 0
                ? 'Deactivate'
                : manufacturer.manufacturerStatus === 1
                  ? 'Activate'
                  : 'Reactivate'
                ">
              <component :is="manufacturer.manufacturerStatus === 0 ? CircleX : CircleCheck" class="w-4 h-4" />
            </button>
            <button v-if="manufacturer.manufacturerStatus !== 2" @click="handleArchiveManufacturer(manufacturer.id)"
              class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
              title="Archive">
              <Archive class="w-4 h-4" />
            </button>
            <button v-if="manufacturer.manufacturerStatus === 2" @click="handleDeleteManufacturer(manufacturer.id)"
              class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Contact info -->
        <div class="space-y-3 text-sm">
          <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-md border border-gray-200 dark:border-gray-700">
            <div class="space-y-2.5">
              <div class="flex items-start gap-2">
                <Mail class="w-4 h-4 text-purple-500 mt-0.5 shrink-0" />
                <div>
                  <div class="font-medium text-gray-800 dark:text-gray-200">
                    {{ manufacturer.primaryEmail }}
                  </div>
                  <div v-if="manufacturer.secondaryEmail" class="text-xs text-gray-500 dark:text-gray-400">
                    Secondary: {{ manufacturer.secondaryEmail }}
                  </div>
                </div>
              </div>
              <div v-if="manufacturer.contactNumber" class="flex items-center gap-2">
                <Phone class="w-4 h-4 text-green-600" />
                <span class="text-gray-900 dark:text-gray-100">{{ manufacturer.contactNumber }}</span>
              </div>
              <div v-if="manufacturer.contactPerson" class="flex items-center gap-2">
                <UserRound class="w-4 h-4 text-orange-500" />
                <span class="text-gray-900 dark:text-gray-100">
                  {{ manufacturer.contactPerson }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex items-start gap-2 bg-blue-50 dark:bg-blue-950/30 p-2.5 rounded-md">
            <Mail class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
            <div>
              <div class="text-xs font-medium text-blue-800 dark:text-blue-300">Order email</div>
              <div class="text-gray-900 dark:text-gray-100 break-all">
                <template v-if="manufacturer.orderEmail">
                  {{ manufacturer.orderEmail }}
                </template>
                <template v-else>
                  <span class="italic text-gray-500 dark:text-gray-400">
                    No record found
                  </span>
                </template>
              </div>
            </div>
          </div>

          <div class="flex items-start gap-2 bg-indigo-50 dark:bg-indigo-950/30 p-2.5 rounded-md">
            <Mail class="w-4 h-4 text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0" />
            <div>
              <div class="text-xs font-medium text-indigo-800 dark:text-indigo-300">
                Eligibility email
              </div>
              <div class="text-gray-900 dark:text-gray-100 break-all">
                <template v-if="manufacturer.eligibilityEmail">
                  {{ manufacturer.eligibilityEmail }}
                </template>
                <template v-else>
                  <span class="italic text-gray-500 dark:text-gray-400">
                    No record found
                  </span>
                </template>
              </div>
            </div>
          </div>

          <!-- Forms Section -->
          <div class="pt-5 border-t border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4">
              <!-- IVR Card -->
              <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden flex flex-col h-32 min-h-[8rem]">
                <div
                  class="px-3 py-2.5 bg-red-50 dark:bg-red-950/30 border-b border-red-100 dark:border-red-900/30 flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded bg-red-100 dark:bg-red-900/40 flex items-center justify-center shrink-0">
                    <FileText class="w-4 h-4 text-red-600 dark:text-red-400" />
                  </div>
                  <span class="text-sm font-medium text-red-800 dark:text-red-300 truncate">IVR Form</span>
                </div>

                <div class="flex-1 px-3 py-2.5 flex items-center justify-center text-center">
                  <div class="w-full">
                    <div v-if="manufacturer.ivrFilename" class="space-y-1">
                      <p class="text-xs text-gray-600 dark:text-gray-400 truncate font-medium">
                        <!-- {{ manufacturer.ivrFilename }} -->
                        Available for
                      </p>
                      <button @click="downloadFile(manufacturer.id, 'ivr')"
                        class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium underline-offset-2 hover:underline">
                        Download
                      </button>
                    </div>
                    <p v-else class="text-xs text-gray-500 dark:text-gray-400 italic">
                      Not available
                    </p>
                  </div>
                </div>
              </div>

              <!-- Order Card -->
              <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden flex flex-col h-32 min-h-[8rem]">
                <div
                  class="px-3 py-2.5 bg-blue-50 dark:bg-blue-950/30 border-b border-blue-100 dark:border-blue-900/30 flex items-center gap-2.5">
                  <div
                    class="w-8 h-8 rounded bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                    <FileText class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                  </div>
                  <span class="text-sm font-medium text-blue-800 dark:text-blue-300 truncate">Order Form</span>
                </div>

                <div class="flex-1 px-3 py-2.5 flex items-center justify-center text-center">
                  <div class="w-full">
                    <div v-if="manufacturer.orderFilename" class="space-y-1">
                      <p class="text-xs text-gray-600 dark:text-gray-400 truncate font-medium">
                        <!-- {{ manufacturer.orderFilename }} -->
                        Available for
                      </p>
                      <button @click="downloadFile(manufacturer.id, 'order')"
                        class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium underline-offset-2 hover:underline">
                        Download
                      </button>
                    </div>
                    <p v-else class="text-xs text-gray-500 dark:text-gray-400 italic">
                      Not available
                    </p>
                  </div>
                </div>
              </div>

              <!-- Onboarding Card -->
              <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden flex flex-col h-32 min-h-[8rem]">
                <div
                  class="px-3 py-2.5 bg-purple-50 dark:bg-purple-950/30 border-b border-purple-100 dark:border-purple-900/30 flex items-center gap-2.5">
                  <div
                    class="w-8 h-8 rounded bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center shrink-0">
                    <FileText class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                  </div>
                  <span class="text-sm font-medium text-purple-800 dark:text-purple-300 truncate">Onboarding File</span>
                </div>

                <div class="flex-1 px-3 py-2.5 flex items-center justify-center text-center">
                  <div class="w-full">
                    <div v-if="manufacturer.onboardingFilename" class="space-y-1">
                      <p class="text-xs text-gray-600 dark:text-gray-400 truncate font-medium">
                        <!-- {{ manufacturer.onboardingFilename }} -->
                        Available for
                      </p>
                      <button @click="downloadFile(manufacturer.id, 'onboarding')"
                        class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium underline-offset-2 hover:underline">
                        Download
                      </button>
                    </div>
                    <p v-else class="text-xs text-gray-500 dark:text-gray-400 italic">
                      Not available
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Brands -->
          <div class="pt-2 border-t border-gray-200 dark:border-gray-700"
            v-if="getActiveBrands(manufacturer).length > 0 || getInactiveBrandCount(manufacturer) > 0">
            <div class="flex items-center gap-2 flex-wrap">
              <strong>Brands:</strong>
              <span v-for="brandObj in getActiveBrands(manufacturer).slice(0, 4)"
                :key="brandObj.id || brandObj.brandName"
                class="inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 ml-1 mt-1">
                {{ brandObj.brandName }}
              </span>
              <span v-if="getActiveBrands(manufacturer).length > 4"
                class="inline-flex px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 ml-1 mt-1">
                +{{ getActiveBrands(manufacturer).length - 4 }} more
              </span>
              <span v-if="getInactiveBrandCount(manufacturer) > 0" class="text-xs text-gray-500 ml-1">
                (+{{ getInactiveBrandCount(manufacturer) }} inactive)
              </span>
            </div>
          </div>
          <p v-else class="pt-2 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500">
            No brands available
          </p>
        </div>
      </div>
    </div>

    <div v-else-if="filteredManufacturers && filteredManufacturers.length === 0 && !tableLoader" class="">
      <div class="flex flex-col items-center justify-center gap-2 text-center">
        <Factory class="w-10 h-10 mb-1 text-gray-700" />
        <span class="text-gray-600 dark:text-gray-300">No manufacturers found.</span>
      </div>
    </div>

    <template v-if="!tableLoader">
      <Pagination v-if="filteredManufacturers && filteredManufacturers.length > 0" :pagination="pagination"
        @update:page="getAllManufacturers" />
    </template>

    <!-- Create/Edit Form Modal -->
    <BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Manufacturer' : 'Edit Manufacturer'">
      <form @submit.prevent="handleSubmitForm" class="space-y-6">
        <!-- Manufacturer Information -->
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Building2 class="w-5 h-5 text-green-500" />
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Manufacturer Information</h3>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">
                Name<span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <Factory class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.manufacturerName" type="text" required placeholder="Manufacturer Name"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">
                Status<span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <CircleCheck class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <select v-model="formData.manufacturerStatus" required
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                  <option :value="0">Active</option>
                  <option :value="1">Inactive</option>
                  <option :value="2">Archived</option>
                </select>
              </div>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Website (Optional)</label>
              <div class="relative">
                <Globe class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.website" type="text" placeholder="https://" @blur="fixWebsite"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Address (Optional)</label>
              <div class="relative">
                <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.address" type="text" placeholder="Manufacturer Address"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>
          </div>
        </div>

        <!-- Logo Upload -->
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Image class="w-5 h-5 text-green-500" />
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Manufacturer Logo (Optional)</h3>
          </div>
          <!-- Drag & Drop Area (only shown when no image selected) -->
          <div v-if="!selectedLogoFile && !formData.logoUrl"
            class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer
                                bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition cursor-pointer" @drop.prevent="handleLogoDrop"
            @dragover.prevent="allowLogoDrop">
            <input id="logo-upload" type="file" accept="image/png,image/jpeg,image/jpg" class="hidden"
              @change="handleLogoChange" />
            <label for="logo-upload" class="flex flex-col items-center justify-center text-center">
              <UploadCloud class="w-10 h-10 mb-3 text-gray-400" />
              <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-purple-600 dark:text-purple-400">Click to upload</span> or drag and drop
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

        <!-- Contact Information -->
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Contact class="w-5 h-5 text-green-500" />
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Contact Information</h3>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Person<span
                  class="text-red-500">*</span></label>
              <div class="relative">
                <UserRound class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.contactPerson" type="text" required placeholder="Contact Person"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>

            <!-- Contact Number -->
            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Contact Number<span
                  class="text-red-500">*</span></label>
              <div class="relative">
                <Phone class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.contactNumber" type="text" required placeholder="Phone/Tel"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>

            <!-- Primary Email -->
            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Primary Email<span
                  class="text-red-500">*</span></label>
              <div class="relative">
                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.primaryEmail" type="email" required placeholder="example@manufacturer.com"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>

            <!-- Secondary Email -->
            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Secondary Email
                (Optional)</label>
              <div class="relative">
                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.secondaryEmail" type="email" placeholder="example@manufacturer.com"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>

            <!-- Order Email -->
            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Email for Orders<span
                  class="text-red-500">*</span></label>
              <div class="relative">
                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.orderEmail" type="email" required placeholder="example@manufacturer.com"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>

            <!-- Eligibility Email -->
            <div>
              <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Email for Eligibility
                Checking<span class="text-red-500">*</span></label>
              <div class="relative">
                <Mail class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="formData.eligibilityEmail" type="email" required placeholder="example@manufacturer.com"
                  class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                            focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>
            </div>
          </div>
        </div>

        <!-- File Upload Sections -->
        <div class="space-y-8">
          <!-- IVR -->
          <div>
            <div class="flex items-center gap-2 mb-2">
              <FilePenLine class="w-5 h-5 text-green-500" />
              <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">IVR Information</h3>
            </div>
            <FileUploadSection v-model:selectedFile="selectedIvrFile" v-model:previewUrl="ivrPreviewUrl"
              v-model:removeFlag="removeIvrFlag" :existingFilename="formData.ivrFilename" label="IVR Form"
              accept=".pdf,.doc,.docx" @remove-existing="removeExistingIvr" @preview="previewExistingFile('ivr')" />
          </div>

          <!-- Order -->
          <div>
            <div class="flex items-center gap-2 mb-2">
              <FilePenLine class="w-5 h-5 text-green-500" />
              <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Order Form</h3>
            </div>
            <FileUploadSection v-model:selectedFile="selectedOrderFile" v-model:previewUrl="orderPreviewUrl"
              v-model:removeFlag="removeOrderFlag" :existingFilename="formData.orderFilename" label="Order Form"
              accept=".pdf,.doc,.docx" @remove-existing="removeExistingOrder" @preview="previewExistingFile('order')" />
          </div>

          <!-- Onboarding -->
          <div>
            <div class="flex items-center gap-2 mb-2">
              <FilePenLine class="w-5 h-5 text-green-500" />
              <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Onboarding File</h3>
            </div>
            <FileUploadSection v-model:selectedFile="selectedOnboardingFile" v-model:previewUrl="onboardingPreviewUrl"
              v-model:removeFlag="removeOnboardingFlag" :existingFilename="formData.onboardingFilename"
              label="Onboarding File" accept=".pdf,.doc,.docx" @remove-existing="removeExistingOnboarding"
              @preview="previewExistingFile('onboarding')" />
          </div>
        </div>

        <template #actions>
          <div class="flex justify-end gap-3 px-6 pb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
            <button @click="closeForm"
              class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow">
              Cancel
            </button>
            <button @click="logoConfirmCrop"
              class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.02]">
              {{ showCreateForm ? 'Create Manufacturer' : 'Update Manufacturer' }}
            </button>
          </div>
        </template>

        <!-- Submit Buttons -->
        <!-- <div class="flex justify-end space-x-3 pt-4">
          <button type="button" @click="closeForm"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            {{ showCreateForm ? 'Create Manufacturer' : 'Update Manufacturer' }}
          </button>
        </div> -->
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
              <div class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-white rounded-tl"></div>
              <div class="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-white rounded-tr"></div>
              <div class="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-white rounded-bl"></div>
              <div class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-white rounded-br"></div>
            </div>

            <!-- Help text -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2">
              <div class="flex items-center gap-2 px-3 py-1.5 bg-black/60 backdrop-blur-sm rounded-full">
                <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
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
                <input type="range" min="0.5" max="3" step="0.1" v-model="logoScale" @input="drawLogoCanvas"
                  class="w-full h-2 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-lg appearance-none cursor-pointer slider-thumb" />
                <div class="absolute inset-0 flex justify-between items-center pointer-events-none px-1">
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

    <!-- View Manufacturer Details Modal -->
    <BaseModal v-model="showManufacturerDetailsModal" title="Manufacturer Details">
      <template v-if="viewManufacturer">
        <div class="space-y-6">
          <!-- Header -->
          <div class="flex items-center space-x-4">
            <div v-if="viewManufacturer.logoUrl" class="w-16 h-16 rounded-full overflow-hidden bg-gray-100">
              <img :src="viewManufacturer.logoUrl" :alt="`${viewManufacturer.manufacturerName} logo`"
                class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
              <Factory class="w-8 h-8 text-green-500" />
            </div>

            <div>
              <p class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ viewManufacturer.manufacturerName }}
              </p>
              <span :class="[
                'inline-flex px-2 py-1 text-xs rounded-full w-fit',
                viewManufacturer.manufacturerStatus === 0
                  ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                  : viewManufacturer.manufacturerStatus === 1
                    ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
              ]">
                {{
                  viewManufacturer.manufacturerStatus === 0
                    ? 'Active'
                    : viewManufacturer.manufacturerStatus === 1
                      ? 'Inactive'
                      : 'Archived'
                }}
              </span>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Left Column -->
              <div class="space-y-4">
                <div class="flex items-center space-x-3">
                  <UserRound class="w-5 h-5 text-orange-500 flex-shrink-0" />
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Contact Person</p>
                    <p class="text-gray-900 dark:text-white">{{ viewManufacturer.contactPerson }}</p>
                  </div>
                </div>

                <div class="flex items-center space-x-3">
                  <Phone class="w-5 h-5 text-green-500 flex-shrink-0" />
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Contact Number</p>
                    <p class="text-gray-900 dark:text-white">{{ viewManufacturer.contactNumber }}</p>
                  </div>
                </div>

                <div v-if="viewManufacturer.address" class="flex items-center space-x-3">
                  <MapPin class="w-5 h-5 text-red-500 flex-shrink-0" />
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Address</p>
                    <p class="text-gray-900 dark:text-white">{{ viewManufacturer.address }}</p>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="space-y-4">
                <div class="flex items-center space-x-3">
                  <Mail class="w-5 h-5 text-purple-500 flex-shrink-0" />
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Primary Email</p>
                    <p class="text-gray-900 dark:text-white">{{ viewManufacturer.primaryEmail }}</p>
                  </div>
                </div>

                <div v-if="viewManufacturer.secondaryEmail" class="flex items-center space-x-3">
                  <Mail class="w-5 h-5 text-indigo-500 flex-shrink-0" />
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Secondary Email</p>
                    <p class="text-gray-900 dark:text-white">{{ viewManufacturer.secondaryEmail }}</p>
                  </div>
                </div>

                <div v-if="viewManufacturer.website" class="flex items-center space-x-3">
                  <Globe class="w-5 h-5 text-blue-500 flex-shrink-0" />
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Website</p>
                    <a :href="viewManufacturer.website" target="_blank"
                      class="text-blue-600 underline hover:text-blue-800 truncate block max-w-[200px]">
                      {{ viewManufacturer.website }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Specialized Emails Section (only show if at least one email exists) -->
          <div v-if="viewManufacturer.orderEmail || viewManufacturer.eligibilityEmail" class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Other Emails</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Order Email Card -->
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <div class="flex items-start space-x-3">
                  <Mail class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
                  <div class="flex-1">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Order Email</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">For order processing & notifications</p>
                    <div v-if="viewManufacturer.orderEmail">
                      <p class="text-sm font-medium text-gray-900 dark:text-white break-all">{{
                        viewManufacturer.orderEmail }}</p>
                    </div>
                    <div v-else>
                      <span class="text-xs text-gray-500 dark:text-gray-400 italic">No Records Found</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Eligibility Email Card -->
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <div class="flex items-start space-x-3">
                  <Mail class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" />
                  <div class="flex-1">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Eligibility Email</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">For eligibility & compliance</p>
                    <div v-if="viewManufacturer.eligibilityEmail">
                      <p class="text-sm font-medium text-gray-900 dark:text-white break-all">{{
                        viewManufacturer.eligibilityEmail }}</p>
                    </div>
                    <div v-else>
                      <span class="text-xs text-gray-500 dark:text-gray-400 italic">No Records Found</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Files Section (only show if at least one file exists) -->
          <div
            v-if="viewManufacturer.ivrFilename || viewManufacturer.orderFilename || viewManufacturer.onboardingFilename"
            class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Files & Documents</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- IVR Form Card -->
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                      <FileText :class="getFileTypeIcon(getFileExtension(viewManufacturer.ivrFilename))"
                        class="w-5 h-5" />
                    </div>
                    <div>
                      <h4 class="text-sm font-semibold text-gray-900 dark:text-white">IVR Form</h4>
                      <div v-if="viewManufacturer.ivrFilename" class="flex items-center gap-2 mt-1">
                        <span :class="[
                          'inline-flex px-2 py-0.5 text-xs rounded-full font-medium',
                          getFileExtension(viewManufacturer.ivrFilename) === 'pdf'
                            ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                        ]">
                          {{ getFileExtension(viewManufacturer.ivrFilename).toUpperCase() }}
                        </span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                          {{ getFileTypeDisplay(getFileExtension(viewManufacturer.ivrFilename)) }}
                        </span>
                      </div>
                      <div v-else class="mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400 italic">Not Available</span>
                      </div>
                    </div>
                  </div>
                </div>
                <button v-if="viewManufacturer.ivrFilename" @click="downloadFile(viewManufacturer.id, 'ivr')"
                  class="w-full mt-2 px-3 py-2 text-sm bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                  Download IVR Form
                </button>
                <div v-else
                  class="w-full mt-2 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                  No File Uploaded
                </div>
              </div>

              <!-- Order Form Card -->
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                      <FileText :class="getFileTypeIcon(getFileExtension(viewManufacturer.orderFilename))"
                        class="w-5 h-5" />
                    </div>
                    <div>
                      <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Order Form</h4>
                      <div v-if="viewManufacturer.orderFilename" class="flex items-center gap-2 mt-1">
                        <span :class="[
                          'inline-flex px-2 py-0.5 text-xs rounded-full font-medium',
                          getFileExtension(viewManufacturer.orderFilename) === 'pdf'
                            ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                        ]">
                          {{ getFileExtension(viewManufacturer.orderFilename).toUpperCase() }}
                        </span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                          {{ getFileTypeDisplay(getFileExtension(viewManufacturer.orderFilename)) }}
                        </span>
                      </div>
                      <div v-else class="mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400 italic">Not Available</span>
                      </div>
                    </div>
                  </div>
                </div>
                <button v-if="viewManufacturer.orderFilename" @click="downloadFile(viewManufacturer.id, 'order')"
                  class="w-full mt-2 px-3 py-2 text-sm bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                  Download Order Form
                </button>
                <div v-else
                  class="w-full mt-2 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                  No File Uploaded
                </div>
              </div>

              <!-- Onboarding File Card -->
              <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                      <FileText :class="getFileTypeIcon(getFileExtension(viewManufacturer.onboardingFilename))"
                        class="w-5 h-5" />
                    </div>
                    <div>
                      <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Onboarding</h4>
                      <div v-if="viewManufacturer.onboardingFilename" class="flex items-center gap-2 mt-1">
                        <span :class="[
                          'inline-flex px-2 py-0.5 text-xs rounded-full font-medium',
                          getFileExtension(viewManufacturer.onboardingFilename) === 'pdf'
                            ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                        ]">
                          {{ getFileExtension(viewManufacturer.onboardingFilename).toUpperCase() }}
                        </span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                          {{ getFileTypeDisplay(getFileExtension(viewManufacturer.onboardingFilename)) }}
                        </span>
                      </div>
                      <div v-else class="mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400 italic">Not Available</span>
                      </div>
                    </div>
                  </div>
                </div>
                <button v-if="viewManufacturer.onboardingFilename"
                  @click="downloadFile(viewManufacturer.id, 'onboarding')"
                  class="w-full mt-2 px-3 py-2 text-sm bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                  Download Onboarding
                </button>
                <div v-else
                  class="w-full mt-2 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                  No File Uploaded
                </div>
              </div>
            </div>
          </div>

          <!-- Brands Information -->
          <div v-if="viewManufacturer.brands && viewManufacturer.brands.length > 0"
            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Associated Brands</h3>

            <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
              <div v-for="brand in viewManufacturer.brands" :key="brand.id"
                class="p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex justify-between items-center">
                  <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">{{ brand.brandName }}</h4>
                  </div>
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="[
                    brand.brandStatus === 0
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                      : brand.brandStatus === 1
                        ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                  ]">
                    {{ brand.brandStatus === 0 ? 'Active' : brand.brandStatus === 1 ? 'Inactive' : 'Archived' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Total: {{ viewManufacturer.brands.length }} brand{{ viewManufacturer.brands.length !== 1 ? 's' : '' }}
              </p>
            </div>
          </div>

          <div v-else
            class="text-center py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
            <p class="text-gray-500 dark:text-gray-400">No brands associated</p>
          </div>
        </div>
      </template>

      <template #actions>
        <div class="p-4">
          <button type="button" @click="viewManufacturer = null"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            Close
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted, nextTick } from 'vue'
import { useToast } from "vue-toastification"
import Swal from "sweetalert2"
import {
  Search, Funnel, Eye, SquarePen, CircleCheck, CircleX, Trash2, HousePlus,
  FileText, Factory, Globe, Building2, Mail, Contact, Phone, UserRound,
  FilePenLine, MapPin, Archive, Download, ChevronDown, Image, UploadCloud, X,
  RefreshCw, Plus, Minus
} from 'lucide-vue-next'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ContentLoader from '@/components/ui/ContentLoader.vue'
import FileUploadSection from '@/components/common/FileUploadSection.vue'
import api from '@/services/api'
import axios from "axios"

const toast = useToast()

// ──────────────────────────────────────────────────────────────────────────────
// Types
// ──────────────────────────────────────────────────────────────────────────────
interface Brand {
  id: number
  brandName: string
  brandStatus: number
}

interface Manufacturer {
  id: number
  manufacturerName: string
  primaryEmail: string
  orderEmail: string
  eligibilityEmail: string
  secondaryEmail?: string
  website?: string
  address?: string
  contactPerson: string
  contactNumber: string
  ivrFilename?: string
  orderFilename?: string
  onboardingFilename?: string
  manufacturerStatus: number
  logoUrl?: string | null
  brands?: Brand[]
  createdAt: string
  updatedAt: string
}

// ──────────────────────────────────────────────────────────────────────────────
// State
// ──────────────────────────────────────────────────────────────────────────────
const submitted = ref(false)
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const itemsPerPage = ref(10)
const manufacturers = ref<Manufacturer[]>([])
const tableLoader = ref(false)
const selectedManufacturer = ref<Manufacturer | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const viewManufacturer = ref<Manufacturer | null>(null)
const brandsExpanded = ref(false)
const formData = ref({
  manufacturerName: '',
  primaryEmail: '',
  orderEmail: '',
  eligibilityEmail: '',
  secondaryEmail: '',
  website: '',
  address: '',
  contactPerson: '',
  contactNumber: '',
  manufacturerStatus: 0,
  logoUrl: '',
  ivrFilename: '',
  orderFilename: '',
  onboardingFilename: '',
})

// ── Logo handling ────────────────────────────────────────────────────────────
const selectedLogoFile = ref<File | null>(null)
const logoObjectUrl = ref<string | null>(null)
const removeLogoFlag = ref(false)
const allowedLogoTypes = ['image/png', 'image/jpeg', 'image/jpg']

// ── File handling – IVR / Order / Onboarding ────────────────────────────────
const selectedIvrFile = ref<File | null>(null)
const ivrPreviewUrl = ref<string | null>(null)
const removeIvrFlag = ref(false)

const selectedOrderFile = ref<File | null>(null)
const orderPreviewUrl = ref<string | null>(null)
const removeOrderFlag = ref(false)

const selectedOnboardingFile = ref<File | null>(null)
const onboardingPreviewUrl = ref<string | null>(null)
const removeOnboardingFlag = ref(false)

// ── Logo cropper state ───────────────────────────────────────────────────────
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

// ──────────────────────────────────────────────────────────────────────────────
// Computed
// ──────────────────────────────────────────────────────────────────────────────
const showFormModal = computed({
  get: () => showCreateForm.value || showEditForm.value,
  set: (val: boolean) => {
    if (!val) {
      showCreateForm.value = false
      showEditForm.value = false
    }
  },
})

const showManufacturerDetailsModal = computed({
  get: () => viewManufacturer.value !== null,
  set: (value: boolean) => {
    if (!value) {
      viewManufacturer.value = null
      brandsExpanded.value = false
    }
  },
})

const hasFiles = computed(() => {
  const m = viewManufacturer.value
  if (!m) return false
  return m.ivrFilename || m.orderFilename || m.onboardingFilename
})

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

// ──────────────────────────────────────────────────────────────────────────────
// Watchers
// ──────────────────────────────────────────────────────────────────────────────
watch(itemsPerPage, () => {
  getAllManufacturers(1)
})

watch(showFormModal, (val) => {
  if (!val) closeForm()
})

// ──────────────────────────────────────────────────────────────────────────────
// Data Fetching
// ──────────────────────────────────────────────────────────────────────────────
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
      orderEmail: m.orderEmail,
      eligibilityEmail: m.eligibilityEmail,
      secondaryEmail: m.secondaryEmail,
      website: m.website,
      address: m.address,
      contactPerson: m.contactPerson,
      contactNumber: m.contactNumber,
      ivrFilename: m.ivrFilename || '',
      orderFilename: m.orderFilename || '',
      onboardingFilename: m.onboardingFilename || '',
      manufacturerStatus: m.manufacturerStatus,
      logoUrl: m.logoUrl,
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
    orderEmail: m.orderEmail || '',
    eligibilityEmail: m.eligibilityEmail || '',
    secondaryEmail: m.secondaryEmail || '',
    website: m.website || '',
    address: m.address || '',
    contactPerson: m.contactPerson || '',
    contactNumber: m.contactNumber || '',
    manufacturerStatus: m.manufacturerStatus ?? 0,
    logoUrl: m.logoUrl || '',
    ivrFilename: m.ivrFilename || '',
    orderFilename: m.orderFilename || '',
    onboardingFilename: m.onboardingFilename || '',
  }
  showEditForm.value = true
}

async function downloadFile(manufacturerId: number, type: 'ivr' | 'order' | 'onboarding') {
  if (!manufacturerId) {
    toast.error('No manufacturer selected for download')
    return
  }

  const endpoint = `/management/manufacturers/${manufacturerId}/download/${type}`

  let displayName = ''
  switch (type) {
    case 'ivr': displayName = 'IVR Form'; break
    case 'order': displayName = 'Order Form'; break
    case 'onboarding': displayName = 'Onboarding File'; break
  }

  try {
    const response = await api.get(endpoint, {
      responseType: 'blob',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`
      }
    })

    // Get filename from backend (basename stored in DB)
    // Fallback to a friendly name if header is missing
    let filename = `${displayName.replace(/\s+/g, '_')}.pdf`

    // Try to get real filename from Content-Disposition if present
    const disposition = response.headers['content-disposition']
    if (disposition && disposition.includes('filename=')) {
      const matches = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)
      if (matches?.[1]) {
        filename = matches[1].replace(/['"]/g, '')
      }
    }

    const blob = new Blob([response.data], {
      type: response.headers['content-type'] || 'application/pdf'
    })

    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    toast.success(`${displayName} downloaded successfully`)
  } catch (error: any) {
    console.error(`Download failed for ${type}:`, error)

    if (axios.isAxiosError(error)) {
      if (error.response?.status === 404) {
        toast.error(`No ${type} file available for this manufacturer`)
      } else if (error.response?.status === 401) {
        toast.error('Authentication error – please log in again')
      } else {
        toast.error(`Failed to download ${type} form (server error)`)
      }
    } else {
      toast.error(`Unexpected error downloading ${type} form`)
    }
  }
}

async function previewExistingFile(type: 'ivr' | 'order' | 'onboarding') {
  if (!selectedManufacturer.value?.id) {
    toast.error('No manufacturer selected for preview')
    return
  }

  const manufacturerId = selectedManufacturer.value.id
  const endpoint = `/management/manufacturers/${manufacturerId}/download/${type}`

  try {
    const response = await api.get(endpoint, {
      responseType: 'blob',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`
      }
    })

    // Determine content type and filename
    const contentType = response.headers['content-type'] || 'application/pdf'
    let filename = `${type.toUpperCase()}_Form.pdf`

    // Try to get real filename from Content-Disposition
    const disposition = response.headers['content-disposition']
    if (disposition && disposition.includes('filename=')) {
      const matches = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)
      if (matches?.[1]) {
        filename = matches[1].replace(/['"]/g, '')
      }
    }

    const blob = new Blob([response.data], { type: contentType })
    const url = window.URL.createObjectURL(blob)

    // Open in new tab for preview (most browsers will show PDF/doc inline)
    const newWindow = window.open(url, '_blank')
    if (!newWindow) {
      // If popup blocked, fallback to download
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', filename)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      toast.info('Popup blocked — file downloaded instead')
    }

    // Clean up after a delay (let browser handle display)
    setTimeout(() => window.URL.revokeObjectURL(url), 60000)

    toast.success(`Opening ${type.toUpperCase()} file preview`)
  } catch (error: any) {
    console.error(`Preview failed for ${type}:`, error)
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 404) {
        toast.error(`No ${type} file available to preview`)
      } else {
        toast.error(`Failed to load ${type} file for preview`)
      }
    } else {
      toast.error('Unexpected error during preview')
    }
  }
}

// ──────────────────────────────────────────────────────────────────────────────
// Form Submission
// ──────────────────────────────────────────────────────────────────────────────
async function handleSubmitForm() {
  submitted.value = true
  try {
    const form = new FormData()
    let website = formData.value.website?.trim() || ''
    if (website && !/^https?:\/\//i.test(website)) {
      website = 'https://' + website
    }

    form.append('manufacturerName', formData.value.manufacturerName)
    form.append('primaryEmail', formData.value.primaryEmail)
    form.append('orderEmail', formData.value.orderEmail)
    form.append('eligibilityEmail', formData.value.eligibilityEmail)
    if (formData.value.secondaryEmail) form.append('secondaryEmail', formData.value.secondaryEmail)
    if (website) form.append('website', website)
    if (formData.value.address) form.append('address', formData.value.address)
    form.append('contactPerson', formData.value.contactPerson)
    form.append('contactNumber', formData.value.contactNumber)
    form.append('manufacturerStatus', formData.value.manufacturerStatus.toString())

    // Logo
    if (selectedLogoFile.value) {
      form.append('logo', selectedLogoFile.value)
    } else if (removeLogoFlag.value) {
      form.append('remove_logo', '1')
    }

    // Files
    if (selectedIvrFile.value) form.append('ivrForm', selectedIvrFile.value)
    else if (removeIvrFlag.value) form.append('remove_ivr', '1')
    if (selectedOrderFile.value) form.append('orderForm', selectedOrderFile.value)
    else if (removeOrderFlag.value) form.append('remove_order', '1')
    if (selectedOnboardingFile.value) form.append('onboardingForm', selectedOnboardingFile.value)
    else if (removeOnboardingFlag.value) form.append('remove_onboarding', '1')

    const config = {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'multipart/form-data'
      }
    }

    if (showCreateForm.value) {
      await api.post('/management/manufacturers', form, config)
      toast.success('Manufacturer created successfully!')
    } else if (selectedManufacturer.value?.id) {
      await api.post(`/management/manufacturers/${selectedManufacturer.value.id}`, form, config)
      toast.success('Manufacturer updated successfully!')
    }

    closeForm()
    await getAllManufacturers(pagination.value.current_page || 1)
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      const status = error.response?.status
      const data = error.response?.data
      if (status === 422 && data?.errors) {
        const messages = Object.values(data.errors).flat()
        toast.error("Validation Error:\n" + messages.join("\n"))
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

// ──────────────────────────────────────────────────────────────────────────────
// Logo Handlers (the missing ones)
// ──────────────────────────────────────────────────────────────────────────────
function allowLogoDrop(event: DragEvent) {
  event.preventDefault()
}

async function handleLogoChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  const ok = await validateLogoFile(file)
  if (!ok) return

  openLogoCropper(file)
  // Reset input value so same file can be selected again if needed
  input.value = ''
}

async function handleLogoDrop(event: DragEvent) {
  event.preventDefault()
  const file = event.dataTransfer?.files?.[0]
  if (!file) return

  const ok = await validateLogoFile(file)
  if (!ok) return

  openLogoCropper(file)
}

function removeCurrentLogo() {
  removeLogoFlag.value = true
  selectedLogoFile.value = null
  revokeLogoObjectUrl()
  formData.value.logoUrl = ''
}

// ──────────────────────────────────────────────────────────────────────────────
// File Helpers (revoke, remove, etc.)
// ──────────────────────────────────────────────────────────────────────────────
function removeExistingIvr() {
  removeIvrFlag.value = true
  selectedIvrFile.value = null
  formData.value.ivrFilename = ''
  if (ivrPreviewUrl.value) {
    revokePreview(ivrPreviewUrl.value)
    ivrPreviewUrl.value = null
  }
}

function removeExistingOrder() {
  removeOrderFlag.value = true
  selectedOrderFile.value = null
  formData.value.orderFilename = ''
  if (orderPreviewUrl.value) {
    revokePreview(orderPreviewUrl.value)
    orderPreviewUrl.value = null
  }
}

function removeExistingOnboarding() {
  removeOnboardingFlag.value = true
  selectedOnboardingFile.value = null
  formData.value.onboardingFilename = ''
  if (onboardingPreviewUrl.value) {
    revokePreview(onboardingPreviewUrl.value)
    onboardingPreviewUrl.value = null
  }
}

function revokePreview(url: string | null) {
  if (url && url.startsWith('blob:')) URL.revokeObjectURL(url)
}

function closeFilePreviews() {
  revokePreview(ivrPreviewUrl.value)
  revokePreview(orderPreviewUrl.value)
  revokePreview(onboardingPreviewUrl.value)
}

function revokeLogoObjectUrl() {
  if (logoObjectUrl.value && logoObjectUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(logoObjectUrl.value)
  }
  logoObjectUrl.value = null
}

const getFileExtension = (filename: string | undefined): string => {
  if (!filename) return ''
  return filename.split('.').pop()?.toLowerCase() || ''
}

const getFileTypeDisplay = (extension: string): string => {
  const typeMap: Record<string, string> = {
    'pdf': 'PDF Document',
    'doc': 'Word Document',
    'docx': 'Word Document'
  }
  return typeMap[extension] || 'Document'
}

const getFileTypeIcon = (extension: string): string => {
  if (!extension) return 'text-gray-400'

  const iconMap: Record<string, string> = {
    'pdf': 'text-red-500',
    'doc': 'text-blue-500',
    'docx': 'text-blue-500'
  }
  return iconMap[extension] || 'text-gray-500'
}

// ──────────────────────────────────────────────────────────────────────────────
// Form Reset
// ──────────────────────────────────────────────────────────────────────────────
function closeForm() {
  showCreateForm.value = false
  showEditForm.value = false
  selectedManufacturer.value = null
  formData.value = {
    manufacturerName: '',
    primaryEmail: '',
    orderEmail: '',
    eligibilityEmail: '',
    secondaryEmail: '',
    website: '',
    address: '',
    contactPerson: '',
    contactNumber: '',
    manufacturerStatus: 0,
    logoUrl: '',
    ivrFilename: '',
    orderFilename: '',
    onboardingFilename: '',
  }
  selectedLogoFile.value = null
  logoObjectUrl.value = null
  removeLogoFlag.value = false
  selectedIvrFile.value = null
  selectedOrderFile.value = null
  selectedOnboardingFile.value = null
  ivrPreviewUrl.value = null
  orderPreviewUrl.value = null
  onboardingPreviewUrl.value = null
  removeIvrFlag.value = false
  removeOrderFlag.value = false
  removeOnboardingFlag.value = false
  submitted.value = false
  closeFilePreviews()
  revokeLogoObjectUrl()
}

// ──────────────────────────────────────────────────────────────────────────────
// Status / Archive / Delete (unchanged from your new version)
// ──────────────────────────────────────────────────────────────────────────────
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

// ──────────────────────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────────────────────
const getActiveBrands = (manufacturer: Manufacturer) => (manufacturer.brands || []).filter(b => b.brandStatus === 0)
const getInactiveBrandCount = (manufacturer: Manufacturer) => ((manufacturer.brands || []).filter(b => b.brandStatus !== 0)).length

function fixWebsite() {
  const url = formData.value.website
  if (url && !/^https?:\/\//i.test(url)) {
    formData.value.website = 'https://' + url
  }
}

// ──────────────────────────────────────────────────────────────────────────────
// Logo Cropper Methods
// ──────────────────────────────────────────────────────────────────────────────
async function validateLogoFile(file: File): Promise<boolean> {
  return new Promise((resolve) => {
    if (!file) return resolve(false)
    if (!allowedLogoTypes.includes(file.type)) {
      toast.error('Unsupported logo format. Use PNG or JPEG/JPG.')
      return resolve(false)
    }
    resolve(true)
  })
}

async function openLogoCropper(file: File) {
  logoPendingFile = file
  revokeLogoObjectUrl()
  logoImageSrc.value = URL.createObjectURL(file)
  logoScale.value = 1
  logoOffsetX.value = 0
  logoOffsetY.value = 0
  showLogoCropModal.value = true
  await nextTick()
  if (!logoImageSrc.value) return

  const img = new window.Image()
  img.src = logoImageSrc.value
  await new Promise((resolve, reject) => {
    img.onload = resolve
    img.onerror = reject
  })
  logoSelectedImage.value = img
  drawLogoCanvas()
}

function drawLogoCanvas() {
  if (!logoCanvas.value || !logoSelectedImage.value) return
  const canvas = logoCanvas.value
  const ctx = canvas.getContext('2d')
  if (!ctx) return

  const rect = canvas.getBoundingClientRect()
  const size = Math.floor(Math.min(rect.width, rect.height))
  canvas.width = size
  canvas.height = size

  ctx.clearRect(0, 0, size, size)
  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, size, size)

  const img = logoSelectedImage.value
  const imgAspect = img.naturalWidth / img.naturalHeight
  let drawWidth = size
  let drawHeight = size / imgAspect
  if (imgAspect < 1) {
    drawHeight = size
    drawWidth = size * imgAspect
  }

  drawWidth *= logoScale.value
  drawHeight *= logoScale.value

  const x = (size - drawWidth) / 2 + logoOffsetX.value
  const y = (size - drawHeight) / 2 + logoOffsetY.value

  ctx.save()
  ctx.beginPath()
  ctx.rect(0, 0, size, size)
  ctx.clip()
  ctx.drawImage(img, x, y, drawWidth, drawHeight)
  ctx.restore()
}

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

function logoEndPan() {
  logoIsPanning.value = false
}

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
    const deltaX = t.clientX - logoLastPanPoint.value.x
    const deltaY = t.clientY - logoLastPanPoint.value.y
    logoOffsetX.value += deltaX
    logoOffsetY.value += deltaY
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

function logoEndTouchPan() {
  logoIsPanning.value = false
  logoLastTouchDistance.value = 0
}

function logoResetPosition() {
  logoScale.value = 1
  logoOffsetX.value = 0
  logoOffsetY.value = 0
  drawLogoCanvas()
}

function logoSelectNewImage() {
  logoSelectedImage.value = null
  logoImageSrc.value = null
  logoPendingFile = null
  const input = document.getElementById('logo-upload') as HTMLInputElement | null
  if (input) input.click()
}

async function logoConfirmCrop() {
  if (!logoCanvas.value || !logoSelectedImage.value || !logoPendingFile) return
  const canvas = logoCanvas.value
  canvas.toBlob((blob) => {
    if (!blob) return
    const croppedFile = new File([blob], logoPendingFile!.name, { type: 'image/png' })
    selectedLogoFile.value = croppedFile
    revokeLogoObjectUrl()
    logoObjectUrl.value = URL.createObjectURL(croppedFile)
    formData.value.logoUrl = logoObjectUrl.value
    removeLogoFlag.value = false
    showLogoCropModal.value = false

    if (logoImageSrc.value && logoImageSrc.value.startsWith('blob:')) {
      URL.revokeObjectURL(logoImageSrc.value)
    }
    logoImageSrc.value = null
    logoSelectedImage.value = null
    logoPendingFile = null
  }, 'image/png', 0.95)
}

function logoCancelCrop() {
  showLogoCropModal.value = false
  if (logoImageSrc.value && logoImageSrc.value.startsWith('blob:')) {
    URL.revokeObjectURL(logoImageSrc.value)
  }
  logoImageSrc.value = null
  logoSelectedImage.value = null
  logoPendingFile = null
}

// ──────────────────────────────────────────────────────────────────────────────
// Lifecycle
// ──────────────────────────────────────────────────────────────────────────────
onMounted(() => {
  getAllManufacturers(1)
})

onUnmounted(() => {
  revokeLogoObjectUrl()
  closeFilePreviews()
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
