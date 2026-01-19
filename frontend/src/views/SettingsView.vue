<template>
	<div
		class="min-h-screen flex flex-col md:flex-row justify-center bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6">
		<div
			class="flex w-full max-w-6xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
			<!-- Settings Sidebar -->
			<aside
				class="w-72 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col py-8 px-6 shadow-lg">
				<!-- Back Button above Settings heading, inside sidebar -->
				<button @click="goBack"
					class="mb-6 flex items-center text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-200">
					<ChevronsLeft class="w-5 h-5 mr-2" />
					<span class="font-medium">Back to Dashboard</span>
				</button>
				<h2
					class="text-2xl font-bold text-gray-900 dark:text-white mb-8 pb-2 border-b border-gray-200 dark:border-gray-700">
					Settings</h2>
				<nav class="flex flex-col space-y-3">
					<button
						class="flex items-center px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 ease-in-out transform hover:scale-[1.02]"
						:class="section === 'personal' ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-200 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
						@click="section = 'personal'">
						<CircleUserRound class="w-6 h-6 mr-4" />
						Personal Details
					</button>
					<button
						class="flex items-center px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 ease-in-out transform hover:scale-[1.02]"
						:class="section === 'security' ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-200 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
						@click="section = 'security'">
						<IdCard class="w-6 h-6 mr-4" />
						Security
					</button>
					<button
						class="flex items-center px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 ease-in-out transform hover:scale-[1.02]"
						:class="section === 'preferences' ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-200 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
						@click="section = 'preferences'">
						<SlidersHorizontal class="w-6 h-6 mr-4" />
						Preferences
					</button>
				</nav>
			</aside>

			<!-- Main Content -->
			<main class="flex-1 flex flex-col items-center justify-start py-14 px-6 sm:px-8">
				<div class="w-full max-w-5xl space-y-8">
					<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
						<SlidersHorizontal class="w-8 h-8 mr-3 text-blue-500 dark:text-blue-400" />
						{{ sectionTitle }}
					</h1>
					<div v-if="section === 'preferences'">
						<div
							class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-8 w-full border border-gray-100 dark:border-gray-700">
							<h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
								<SlidersHorizontal class="w-5 h-5 mr-2 text-blue-500 dark:text-blue-400" />
								Appearance
							</h2>
							<div
								class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-6 p-4 bg-white dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
								<div class="flex items-center">
									<Palette class="w-5 h-5 mr-3 text-gray-500 dark:text-gray-400" />
									<span class="text-base font-medium text-gray-700 dark:text-gray-200">Theme:</span>
								</div>
								<button @click="toggleTheme"
									class="flex items-center px-5 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow-md">
									<span v-if="isDarkMode" class="mr-3 text-lg">🌙</span>
									<span v-else class="mr-3 text-lg">🌞</span>
									<span class="font-medium">{{ isDarkMode ? 'Dark Mode' : 'Light Mode' }}</span>
								</button>
							</div>
						</div>
					</div>

					<div v-if="section === 'personal'">
						<!-- Profile Header -->
						<div
							class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-8 flex flex-col items-center mb-6 border border-gray-100 dark:border-gray-700">
							<div
								class="h-24 w-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold mb-4 shadow-lg">
								{{ userInitials }}
							</div>
							<h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ headerDisplayName }}
							</h2>
							<p class="text-gray-600 dark:text-gray-300 mb-3">{{ displayEmail }}</p>
							<span v-if="currentUser" :class="[
								'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold mt-2',
								roleLabels[currentUser.user_role as number]?.classes || 'bg-gray-100 text-gray-800'
							]">
								{{ roleLabels[currentUser.user_role as number]?.label || 'User' }}
							</span>
						</div>


						<!-- Security Status -->
						<div
							class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-6 mb-6 space-y-4 border border-gray-100 dark:border-gray-700">
							<h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
								<Lock class="w-5 h-5 mr-2 text-blue-500" />
								Security Status
							</h3>
							<div
								class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
								<div class="flex items-center mb-2 sm:mb-0">
									<IdCard class="w-5 h-5 mr-3 text-gray-500 dark:text-gray-400" />
									<span class="text-base font-medium text-gray-700 dark:text-gray-200">Two-Factor
										Authentication (2FA)</span>
								</div>
								<span v-if="twoFAEnabled"
									class="px-3 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-semibold">Enabled</span>
								<span v-else
									class="px-3 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-sm font-semibold">Disabled</span>
							</div>
							<div
								class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
								<div class="flex items-center mb-2 sm:mb-0">
									<SquareAsterisk class="w-5 h-5 mr-3 text-gray-500 dark:text-gray-400" />
									<span class="text-base font-medium text-gray-700 dark:text-gray-200">One Time Code
										Via Email</span>
								</div>
								<span v-if="oneTimeEmail"
									class="px-3 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-semibold">Enabled</span>
								<span v-else
									class="px-3 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-sm font-semibold">Disabled</span>
							</div>
							<div
								class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
								<div class="flex items-center mb-2 sm:mb-0">
									<CloudDownload class="w-5 h-5 mr-3 text-gray-500 dark:text-gray-400" />
									<span class="text-base font-medium text-gray-700 dark:text-gray-200">Backup
										Codes</span>
								</div>
								<span v-if="backupCodesEnabled"
									class="px-3 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-semibold">Enabled</span>
								<span v-else
									class="px-3 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-sm font-semibold">Disabled</span>
							</div>
						</div>

						<!-- Edit Personal Information -->
						<div
							class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-8 mb-6 border border-gray-100 dark:border-gray-700">
							<h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
								<CircleUserRound class="w-5 h-5 mr-2 text-blue-500" />
								Edit Personal Information
							</h3>
							<form @submit.prevent="updateProfile" class="w-full space-y-5">
								<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
									<div>
										<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
											First Name
										</label>
										<input v-model="profileForm.firstName" type="text"
											class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base transition-colors"
											placeholder="Enter your first name" />
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
											Last Name
										</label>
										<input v-model="profileForm.lastName" type="text"
											class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base transition-colors"
											placeholder="Enter your last name" />
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
											Middle Name (Optional)
										</label>
										<input v-model="profileForm.middleName" type="text"
											class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base transition-colors"
											placeholder="Enter your middle name (if any)" />
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
											Phone Number
										</label>
										<input v-model="profileForm.phone" type="tel"
											class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base transition-colors"
											placeholder="Enter your phone number" />
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
										Email Address
									</label>
									<input v-model="profileForm.email" type="email"
										class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base transition-colors"
										placeholder="Enter your email address" />
								</div>

								<div class="pt-4 flex justify-end">
									<button type="submit" :disabled="isUpdating || !hasProfileChanges"
										class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed text-base font-semibold shadow-md transition-all duration-200 transform hover:scale-[1.02]">
										{{ isUpdating ? 'Updating...' : 'Update Profile' }}
									</button>
								</div>
							</form>
						</div>
					</div>

					<div v-else-if="section === 'security'">
						<div
							class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-8 w-full mb-6 border border-gray-100 dark:border-gray-700">
							<h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
								<IdCard class="w-5 h-5 mr-2 text-blue-500 dark:text-blue-400" />
								Two-Factor Authentication (2FA)
							</h2>
							<div
								class="flex items-center mb-6 p-4 bg-white dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
								<label class="flex items-center cursor-pointer w-full">
									<div class="flex items-center h-5">
										<input type="checkbox" v-model="twoFAEnabled"
											class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" />
									</div>
									<span class="ml-3 text-gray-800 dark:text-gray-200 text-base font-medium">Enable 2FA
										with 4-digit PIN</span>
								</label>
							</div>
							<div v-if="twoFAEnabled">
								<div v-if="!pinSet" class="space-y-4">
									<div class="text-gray-700 dark:text-gray-300 mb-2">Set your 4-digit PIN:</div>
									<form @submit.prevent="handleSetPin" class="flex flex-col space-y-4 max-w-xs">
										<div class="flex space-x-3 justify-center">
											<input v-for="(d, i) in 4" :key="i" :ref="el => {
												const input = el as HTMLInputElement
												if (input && input.tagName === 'INPUT') setPinRefs[i].value = input
											}" v-model="pinBoxes[i]" :type="showPin ? 'text' : 'password'" maxlength="1" inputmode="numeric"
												pattern="\d"
												class="w-12 h-12 text-2xl text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
												@input="e => onPinBoxInput(i, 'set', e as InputEvent)"
												@keydown="e => onPinBoxKeydown(i, 'set', e)" />
										</div>
										<label
											class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
											<input type="checkbox" v-model="showPin" class="form-checkbox" />
											<span>Show PIN</span>
										</label>
										<div v-if="pinError" class="text-red-600 text-sm text-center">{{ pinError }}
										</div>
										<button type="submit"
											class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Set
											PIN</button>
									</form>
								</div>
								<div v-else>
									<div class="flex items-center justify-between">
										<span class="text-green-700 dark:text-green-300 font-medium">2FA is enabled with
											a PIN.</span>
										<button @click="showChangePin = !showChangePin"
											class="text-blue-600 hover:underline dark:text-blue-400">Change PIN</button>
									</div>
									<div class="flex items-center justify-between my-4">
										<span class="text-green-700 text-sm">You will be required to enter your
											configured PIN every time you log in.</span>
									</div>
									<form v-if="showChangePin" @submit.prevent="handleChangePin"
										class="flex flex-col space-y-4 max-w-xs">
										<div class="flex space-x-3 justify-center">
											<input v-for="(d, i) in 4" :key="i" :ref="el => {
												const input = el as HTMLInputElement
												if (input && input.tagName === 'INPUT') changePinRefs[i].value = input
											}" v-model="newPinBoxes[i]" :type="showPin ? 'text' : 'password'" maxlength="1" inputmode="numeric"
												pattern="\d"
												class="w-12 h-12 text-2xl text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
												@input="e => onPinBoxInput(i, 'change', e as InputEvent)"
												@keydown="e => onPinBoxKeydown(i, 'change', e)" />
										</div>
										<label
											class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
											<input type="checkbox" v-model="showPin" class="form-checkbox" />
											<span>Show PIN</span>
										</label>
										<div v-if="pinError" class="text-red-600 text-sm text-center">{{ pinError }}
										</div>
										<button type="submit"
											class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Change
											PIN</button>
									</form>
								</div>
							</div>
						</div>

						<div
							class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-8 w-full mb-6 border border-gray-100 dark:border-gray-700">
							<h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
								<SquareAsterisk class="w-5 h-5 mr-2 text-blue-500" />
								One Time Code
							</h2>
							<div
								class="flex items-center mb-6 p-4 bg-white dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
								<label class="flex items-center cursor-pointer w-full">
									<div class="flex items-center h-5">
										<input type="checkbox" v-model="oneTimeEmail"
											class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" />
									</div>
									<span class="ml-3 text-gray-800 dark:text-gray-200 text-base font-medium">Enable
										one-time verification code via email</span>
								</label>
							</div>
							<div v-if="oneTimeEmail">
								<div class="flex items-center justify-between">
									<span class="text-green-700 dark:text-green-300 font-medium">One Time Code
										Authentication Enabled</span>
								</div>
								<div class="flex items-center justify-between mt-4">
									<span class="text-green-700 text-sm">Each time you log in, a verification code will
										be sent to your email: <b>{{ currentUser?.email }}</b></span>
								</div>
							</div>
						</div>

						<div
							class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-8 w-full mb-6 border border-gray-100 dark:border-gray-700">
							<h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
								<CloudDownload class="w-5 h-5 mr-2 text-blue-500" />
								Backup Codes
							</h2>
							<div
								class="flex items-center mb-6 p-4 bg-white dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
								<label class="flex items-center cursor-pointer w-full">
									<div class="flex items-center h-5">
										<input type="checkbox" v-model="backupCodesEnabled"
											class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" />
									</div>
									<span class="ml-3 text-gray-800 dark:text-gray-200 text-base font-medium">Enable and
										generate backup codes</span>
								</label>
							</div>
							<div v-if="backupCodesEnabled">
								<div class="flex items-center justify-between">
									<span class="text-green-700 dark:text-green-300 font-medium">Backup Codes
										Authentication Enabled</span>
									<button @click="generateBackupCodes()"
										class="flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 font-semibold transition-all duration-200 shadow-md transform hover:scale-[1.03]">
									<RefreshCcw class="w-5 h-5 mr-2" />
										Generate New Codes
									</button>
								</div>
								<div v-if="backupCodes.length > 0" class="flex flex-col space-y-2 mb-4">
									<div class="mt-3 mb-3">
										<div class="flex justify-start mb-2">
											<button @click="copyAllBackupCodes()"
												class="flex items-center px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-sm">
												<Copy class="w-4 h-4 mr-1" />
												Copy All
											</button>
										</div>
										<ul class="grid grid-cols-2 gap-2">
											<li v-for="code in backupCodes" :key="code"
												class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded text-center font-mono text-base text-gray-900 dark:text-white">
												{{ code }}
											</li>
										</ul>
										<div class="mt-5 text-red-600 dark:text-red-400 text-xs">
											<strong>Important:</strong> These codes are only shown once. Please save
											them now as you won't be able to view them again.
											Each backup code is valid for one-time use only. Please store them securely,
											as they cannot be reused.
										</div>

									</div>
								</div>
								<div v-else class="flex flex-col space-y-2 mb-4 pt-4">
									<p class="text-gray-600 dark:text-gray-400 text-xs">No backup codes generated
										yet. Click "Generate New Codes" to create your backup codes.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
			<div v-if="showDisable2FAModal"
				class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
				<div
					class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md border border-gray-200 dark:border-gray-700 transform transition-all duration-300 scale-100">
					<div
						class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
						<TriangleAlert class="w-6 h-6 text-red-600 dark:text-red-400" />
					</div>
					<h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2">Disable Two-Factor
						Authentication?</h3>
					<p class="text-gray-700 dark:text-gray-300 text-center mb-6">Are you sure you want to disable 2FA?
						This will remove your PIN and reduce account security.</p>
					<div class="flex justify-center space-x-4">
						<button @click="cancelDisable2FA"
							class="px-5 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-colors duration-200">Cancel</button>
						<button @click="confirmDisable2FA"
							class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-red-700 text-white hover:from-red-700 hover:to-red-800 font-semibold transition-all duration-200 shadow-md transform hover:scale-[1.03]">Disable</button>
					</div>
				</div>
			</div>
			<div v-if="showDisableOneTimeModal"
				class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
				<div
					class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md border border-gray-200 dark:border-gray-700 transform transition-all duration-300 scale-100">
					<div
						class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
						<TriangleAlert class="w-6 h-6 text-red-600 dark:text-red-400" />
					</div>
					<h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2">Disable One Time Code
						Authentication?</h3>
					<p class="text-gray-700 dark:text-gray-300 text-center mb-6">Are you sure you want to disable One
						Time Code? This will remove the code requirement and reduce account security.</p>
					<div class="flex justify-center space-x-4">
						<button @click="cancelDisableOneTime"
							class="px-5 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-colors duration-200">Cancel</button>
						<button @click="confirmDisableOneTime"
							class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-red-700 text-white hover:from-red-700 hover:to-red-800 font-semibold transition-all duration-200 shadow-md transform hover:scale-[1.03]">Disable</button>
					</div>
				</div>
			</div>
			<div v-if="showDisableBackupModal"
				class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
				<div
					class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md border border-gray-200 dark:border-gray-700 transform transition-all duration-300 scale-100">
					<div
						class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
						<TriangleAlert class="w-6 h-6 text-red-600 dark:text-red-400" />
					</div>
					<h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2">Disable Backup Codes?
					</h3>
					<p class="text-gray-700 dark:text-gray-300 text-center mb-6">Are you sure you want to disable backup
						codes? All generated codes will be deleted.</p>
					<div class="flex justify-center space-x-4">
						<button @click="cancelDisableBackup"
							class="px-5 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-colors duration-200">Cancel</button>
						<button @click="confirmDisableBackup"
							class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-red-700 text-white hover:from-red-700 hover:to-red-800 font-semibold transition-all duration-200 shadow-md transform hover:scale-[1.03]">Disable</button>
					</div>
				</div>
			</div>
			<div v-if="showGenerateBUCodes"
				class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
				<div
					class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md border border-gray-200 dark:border-gray-700 transform transition-all duration-300 scale-100">
					<div
						class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-blue-100 dark:bg-blue-900/30 mb-4">
						<RefreshCcw class="w-6 h-6 text-blue-600 dark:text-blue-400" />
					</div>
					<h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2">Generate New Backup
						Codes?</h3>
					<p class="text-gray-700 dark:text-gray-300 text-center mb-6">Are you sure you want to generate new
						backup codes? This will invalidate your current codes and create a new set.</p>
					<div class="flex justify-center space-x-4">
						<button @click="cancelGenerateBackupCodes"
							class="px-5 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-colors duration-200">Cancel</button>
						<button @click="confirmGenerateBackupCodes"
							class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 font-semibold transition-all duration-200 shadow-md transform hover:scale-[1.03]">Generate</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useUser } from '@/composables/auth/useUser'
import { useRouter } from 'vue-router'
import { useThemeStore } from '@/stores/theme'
import { toast } from 'vue3-toastify'
import axios from 'axios'
import api from '@/services/api'
import 'vue3-toastify/dist/index.css'
import {
	SquareAsterisk,
	CloudDownload,
	Palette,
	Lock,
	CircleUserRound,
	IdCard,	
	SlidersHorizontal, 
	RefreshCcw, 
	Copy,
	TriangleAlert,
	ChevronsLeft
} from 'lucide-vue-next'; 

const section = ref<'preferences' | 'personal' | 'security'>('personal')
const sectionTitle = computed(() => {
	if (section.value === 'preferences') return 'Preferences'
	if (section.value === 'personal') return 'Personal Details'
	return 'Security'
})

// Profile logic
const authStore = useAuthStore()
const { userInitials } = useUser()
const currentUser = computed(() => authStore.currentUser)
const isUpdating = ref(false)
const originalProfileData = ref({
	firstName: '',
	middleName: '',
	lastName: '',
	email: '',
	phone: ''
})

const profileForm = ref({
	firstName: '',
	middleName: '',
	lastName: '',
	email: '',
	phone: ''
})

// Display name and email for header
const headerDisplayName = ref('')
const displayEmail = ref('')

const roleLabels: Record<number, { label: string; classes: string }> = {
	0: { label: 'Admin', classes: 'bg-purple-100 text-purple-800' },
	1: { label: 'Office Staff', classes: 'bg-yellow-100 text-yellow-800' },
	2: { label: 'Clinic', classes: 'bg-blue-100 text-blue-800' },
	3: { label: 'Clinician', classes: 'bg-green-100 text-green-800' },
	4: { label: 'Manufacturer', classes: 'bg-pink-100 text-pink-800' },
	5: { label: 'Billier', classes: 'bg-red-100 text-red-800' },
}

const hasProfileChanges = computed(() => {
	return (
		profileForm.value.firstName !== originalProfileData.value.firstName ||
		profileForm.value.middleName !== originalProfileData.value.middleName ||
		profileForm.value.lastName !== originalProfileData.value.lastName ||
		profileForm.value.email !== originalProfileData.value.email ||
		profileForm.value.phone !== originalProfileData.value.phone
	)
})

async function updateProfile() {
	isUpdating.value = true
	// Store current name parts to prevent disappearance during update
	const tempNameParts = [
		profileForm.value.firstName,
		profileForm.value.middleName,
		profileForm.value.lastName
	].filter(part => part);
	const tempDisplayName = tempNameParts.join(' ');

	// Immediately update display name to prevent flickering
	headerDisplayName.value = tempDisplayName;

	try {
		// Get current user ID
		const userId = currentUser.value?.id
		if (!userId) {
			throw new Error('User not found')
		}

		// Prepare update data
		const updateData = {
			first_name: profileForm.value.firstName,
			middle_name: profileForm.value.middleName || null,
			last_name: profileForm.value.lastName,
			email: profileForm.value.email,
			phone: profileForm.value.phone || null
		}

		// Make API call to update user profile
		const response = await api.put(`/users/${userId}`, updateData)

		// Update auth store with new user data
		if (response.data.user) {
			authStore.$patch({
				user: response.data.user
			})

			// Update header display immediately
			const nameParts = [
				response.data.user.first_name,
				response.data.user.middle_name,
				response.data.user.last_name
			].filter(part => part);
			const newDisplayName = nameParts.join(' ');
			headerDisplayName.value = newDisplayName;
			displayEmail.value = response.data.user.email;

			await nextTick()
		}

		// Update original data for change detection
		originalProfileData.value = { ...profileForm.value }

		// Show success message
		toast.success('Profile updated successfully', { autoClose: 2000 })

		// Reload the page to ensure changes are reflected
		window.location.reload()
	} catch (error: any) {
		console.error('Failed to update profile:', error)

		// Restore display name on error
		displayEmail.value = currentUser.value?.email || '';

		// Show error message
		toast.error(error.response?.data?.message || 'Failed to update profile. Please try again.', { autoClose: 2000 })
	} finally {
		isUpdating.value = false
	}
}


// 2FA logic
const twoFAEnabled = ref(localStorage.getItem('2fa-enabled') === 'true')
const pinSet = ref(!!localStorage.getItem('2fa-pin'))
const showPin = ref(false)
const pinBoxes = ref<string[]>(['', '', '', ''])
const pinError = ref('')
const setPinRefs = [ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>()]
const newPinBoxes = ref<string[]>(['', '', '', ''])
const changePinRefs = [ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>()]
const showChangePin = ref(false)

watch(twoFAEnabled, (val) => {
	requestDisable2FA(val)
})

const showDisable2FAModal = ref(false)
const showDisableOneTimeModal = ref(false)
const pending2FAValue = ref<boolean | null>(null)
const pendingOneTimeValue = ref<boolean | null>(null)

function requestDisable2FA(val: boolean) {
	if (!val) {
		pending2FAValue.value = val
		showDisable2FAModal.value = true
	} else {
		twoFAEnabled.value = val
		localStorage.setItem('2fa-enabled', 'true')
	}
}

async function confirmDisable2FA() {
	try {
		await api.post('/auth/profile/security/disable-tfauth')

		// Clear frontend state
		twoFAEnabled.value = false
		pinSet.value = false
		pinBoxes.value = ['', '', '', '']
		newPinBoxes.value = ['', '', '', '']
		pinError.value = ''
		showChangePin.value = false
		showDisable2FAModal.value = false
		pending2FAValue.value = null

		// Remove any localStorage if used
		localStorage.removeItem('2fa-enabled')
		localStorage.removeItem('2fa-pin')

		toast.warn('Two-Factor Authentication has been disabled. Your account is now less secure.', { autoClose: 2000 })
	} catch (err: unknown) {
		console.error('Failed to disable 2FA', err)
		toast.error('Failed to disable 2FA. Please try again.', { autoClose: 2000 })
		// revert checkbox back
		twoFAEnabled.value = true
		showDisable2FAModal.value = false
		pending2FAValue.value = null
	}
}

function cancelDisable2FA() {
	twoFAEnabled.value = true
	showDisable2FAModal.value = false
	pending2FAValue.value = null
}

function onPinBoxInput(i: number, mode: 'set' | 'change', e: InputEvent) {
	const arr = mode === 'set' ? pinBoxes.value : newPinBoxes.value
	const refs = mode === 'set' ? setPinRefs : changePinRefs
	const input = e.target as HTMLInputElement
	let val = input.value.replace(/[^\d]/g, '')

	// Handle paste or fast typing
	if (e.inputType === 'insertFromPaste' || val.length > 1) {
		const pasted = (e.data || val).replace(/[^\d]/g, '')
		for (let j = 0; j < 4; j++) {
			arr[j] = pasted[j] || ''
		}
		refs[Math.min(pasted.length, 4) - 1]?.value?.focus()
		return
	}

	arr[i] = val
	if (val && i < 3) {
		refs[i + 1].value?.focus()
	}
}

function onPinBoxKeydown(i: number, mode: 'set' | 'change', e: KeyboardEvent) {
	const arr = mode === 'set' ? pinBoxes.value : newPinBoxes.value
	const refs = mode === 'set' ? setPinRefs : changePinRefs

	if (e.key === 'Backspace') {
		if (arr[i]) {
			arr[i] = ''
		} else if (i > 0) {
			refs[i - 1].value?.focus()
			arr[i - 1] = ''
			e.preventDefault()
		}
	} else if (e.key === 'Delete') {
		if (arr[i]) {
			arr[i] = ''
		} else if (i < 3) {
			refs[i + 1].value?.focus()
			e.preventDefault()
		}
	} else if (e.key >= '0' && e.key <= '9' && arr[i] && i < 3) {
		refs[i + 1].value?.focus()
	} else if (e.key === 'ArrowLeft' && i > 0) {
		refs[i - 1].value?.focus()
		e.preventDefault()
	} else if (e.key === 'ArrowRight' && i < 3) {
		refs[i + 1].value?.focus()
		e.preventDefault()
	}
}

async function handleSetPin() {
	pinError.value = ''
	const pin = pinBoxes.value.join('')

	if (!/^\d{4}$/.test(pin)) {
		pinError.value = 'PIN must be exactly 4 digits.'
		return
	}

	try {
		const response = await api.post('/auth/profile/security/enable-tfa', { pin })
		pinSet.value = true
		twoFAEnabled.value = true
		pinBoxes.value = ['', '', '', '']
		toast.success(response.data.message, { autoClose: 2000 })
	} catch (err: unknown) {
		if (axios.isAxiosError(err) && err.response?.data?.message) {
			pinError.value = err.response.data.message
		} else if (err instanceof Error) {
			pinError.value = err.message
		} else {
			pinError.value = 'An unknown error occurred'
		}
	}
}

async function handleChangePin() {
	pinError.value = ''
	const pin = newPinBoxes.value.join('')

	if (!/^\d{4}$/.test(pin)) {
		pinError.value = 'PIN must be exactly 4 digits.'
		return
	}

	try {
		await api.post('/auth/profile/security/update-tfauth', { pin })

		// Update frontend state and localStorage
		localStorage.setItem('2fa-pin', pin)
		showChangePin.value = false
		newPinBoxes.value = ['', '', '', '']
		toast.success('Your PIN has been successfully changed.', { autoClose: 2000 })
	} catch (err: unknown) {
		console.error('Failed to update 2FA PIN', err)
		toast.error('Failed to change PIN. Please try again.', { autoClose: 2000 })
	}
}

// One Time Email logic
const oneTimeEmail = ref(localStorage.getItem('one-time-email-enabled') === 'true')
watch(oneTimeEmail, (val) => {
	requestDisableOneTime(val)
})

async function requestDisableOneTime(val: boolean) {
	if (!val) {
		pendingOneTimeValue.value = val
		showDisableOneTimeModal.value = true
	} else {
		try {
			await api.post('/auth/profile/security/enable-one-time-email')
			oneTimeEmail.value = val
			localStorage.setItem('one-time-email-enabled', 'true')
			toast.success('One Time Code Authentication is now enabled.', { autoClose: 2000 })

			// Update user data in auth store
			if (authStore.currentUser) {
				authStore.currentUser.one_time_email_verification = 1
			}
		} catch (err: any) {
			console.error('Failed to enable one-time email verification', err)
			toast.error(err.response?.data?.message || 'Failed to enable one-time email verification', { autoClose: 2000 })
			oneTimeEmail.value = false
		}
	}
}

function cancelDisableOneTime() {
	oneTimeEmail.value = true
	showDisableOneTimeModal.value = false
	pendingOneTimeValue.value = null
}

async function confirmDisableOneTime() {
	try {
		await api.post('/auth/profile/security/disable-one-time-email')

		localStorage.removeItem('one-time-email-enabled')
		showDisableOneTimeModal.value = false
		pendingOneTimeValue.value = null
		toast.warn('One Time Code Authentication has been disabled. Your account is now less secure.', { autoClose: 2000 })
		oneTimeEmail.value = false

		// Update user data in auth store
		if (authStore.currentUser) {
			authStore.currentUser.one_time_email_verification = 0
		}
	} catch (err: any) {
		console.error('Failed to disable one-time email verification', err)
		toast.error(err.response?.data?.message || 'Failed to disable one-time email verification', { autoClose: 2000 })

		// Restore checkbox state
		oneTimeEmail.value = true
		showDisableOneTimeModal.value = false
		pendingOneTimeValue.value = null
	}
}

// Backup Codes logic
const backupCodesEnabled = ref(localStorage.getItem('backup-codes-enabled') === 'true')
const backupCodes = ref<string[]>(JSON.parse(localStorage.getItem('backup-codes') || '[]'))
const showDisableBackupModal = ref(false)
const pendingBackupValue = ref<boolean | null>(null)
const showGenerateBUCodes = ref(false)

// Track if this is the initial load to avoid regenerating codes on page refresh
let isInitialLoad = true

watch(backupCodesEnabled, (val, oldVal) => {
	if (!val) {
		requestDisableBackup(val)
	} else if (!oldVal && val && !isInitialLoad) {  // Only generate codes if not initial load
		generateBackupCodes()
	}
	isInitialLoad = false
})

function requestDisableBackup(val: boolean) {
	if (!val) {
		pendingBackupValue.value = val
		showDisableBackupModal.value = true
	}
}

async function generateBackupCodes() {
	try {
		const response = await api.post('/auth/profile/security/enable-backup-codes')
		backupCodes.value = response.data.backup_codes
		backupCodesEnabled.value = response.data.backup_codes_enabled
		showDisableBackupModal.value = false
		pendingBackupValue.value = null
		toast.success(response.data.message, { autoClose: 2000 })
	} catch (err: any) {
		console.error('Failed to generate backup codes', err)
		toast.error(err.response?.data?.message || 'Failed to generate backup codes', { autoClose: 2000 })
		backupCodesEnabled.value = false
	}
}

async function confirmDisableBackup() {
	try {
		await api.post('/auth/profile/security/disable-backup-codes')
		backupCodesEnabled.value = false
		backupCodes.value = []
		showDisableBackupModal.value = false
		pendingBackupValue.value = null
		toast.warn('Backup Codes have been disabled. All generated codes have been deleted.', { autoClose: 2000 })
	} catch (err: any) {
		console.error('Failed to disable backup codes', err)
		toast.error(err.response?.data?.message || 'Failed to disable backup codes', { autoClose: 2000 })
		showDisableBackupModal.value = false
		pendingBackupValue.value = null
	}
}

function cancelDisableBackup() {
	backupCodesEnabled.value = true
	showDisableBackupModal.value = false
	pendingBackupValue.value = null
}

function copyBackupCode(code: string) {
	navigator.clipboard.writeText(code)
	toast.success('Backup code copied to clipboard!', { autoClose: 1500 })
}

function copyAllBackupCodes() {
	const allCodes = backupCodes.value.join('\n')
	navigator.clipboard.writeText(allCodes)
	toast.success('All backup codes copied to clipboard!', { autoClose: 1500 })
}

async function fetchExistingBackupCodes() {
	try {
		const response = await api.get('/auth/profile/security/backup-codes')
		if (response.data.backup_codes) {
			backupCodes.value = response.data.backup_codes
		}
	} catch (err: any) {
		console.error('Failed to fetch existing backup codes', err)
	}
}

function confirmGenerateBackupCodes() {
	generateBackupCodes()
	showGenerateBUCodes.value = false
	toast.success('New backup codes have been generated.', { autoClose: 2000 })
}

function cancelGenerateBackupCodes() {
	showGenerateBUCodes.value = false
}

const themeStore = useThemeStore()
const isDarkMode = computed(() => themeStore.isDarkMode)
function toggleTheme() {
	themeStore.toggleTheme()
}

const router = useRouter()
function goBack() {
	router.back()
}

// Watch for user data changes
watch(currentUser, (newUser) => {
	if (newUser) {
		const fullName = newUser.first_name && newUser.last_name
			? `${newUser.first_name} ${newUser.last_name}`
			: newUser.full_name || newUser.name || ''

		profileForm.value = {
			firstName: newUser.first_name || '',
			middleName: newUser.middle_name || '',
			lastName: newUser.last_name || '',
			email: newUser.email || '',
			phone: newUser.phone || ''
		}

		// Update display name and email
		const nameParts = [
			newUser.first_name,
			newUser.middle_name,
			newUser.last_name
		].filter(part => part);
		const newName = nameParts.join(' ');
		headerDisplayName.value = newName;
		displayEmail.value = newUser.email;

		// Update oneTimeEmail from user data
		oneTimeEmail.value = newUser.one_time_email_verification === 1;

		// Update original data for change detection
		originalProfileData.value = { ...profileForm.value }
	}
}, { immediate: true })



onMounted(async () => {
	try {
		const response = await api.get('/auth/me')
		const userData = response.data
		twoFAEnabled.value = userData.tfa_enabled === 1
		pinSet.value = userData.tfa_enabled === 1
		oneTimeEmail.value = userData.one_time_email_verification === 1

		// Initialize profile form data
		if (userData) {
			const fullName = userData.first_name && userData.last_name
				? `${userData.first_name} ${userData.last_name}`
				: userData.full_name || userData.name || ''

			profileForm.value = {
				firstName: userData.first_name || '',
				middleName: userData.middle_name || '',
				lastName: userData.last_name || '',
				email: userData.email || '',
				phone: userData.phone || ''
			}

			// Initialize display name and email
			const nameParts = [
				userData.first_name,
				userData.middle_name,
				userData.last_name
			].filter(part => part);
			const newName = nameParts.join(' ');
			headerDisplayName.value = newName;
			displayEmail.value = userData.email;

			// Store original data for change detection
			originalProfileData.value = { ...profileForm.value }
		}

		// Initialize other components
		showGenerateBUCodes.value = false

		// Initialize backup codes from user data
		backupCodesEnabled.value = userData.backup_codes_enabled || false

		// If backup codes are enabled, fetch the existing codes
		if (backupCodesEnabled.value) {
			fetchExistingBackupCodes()
		}
	} catch (err: unknown) {
		console.error('Failed to fetch user profile', err)
	}
});


</script>