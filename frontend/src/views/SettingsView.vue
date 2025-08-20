<template>
	<div class="min-h-screen flex flex-col md:flex-row justify-center bg-gray-50 dark:bg-gray-900 py-12">
		<div class="flex w-full max-w-6xl bg-white dark:bg-gray-900 rounded shadow-2xl overflow-hidden">
			<!-- Settings Sidebar -->
			<aside class="w-72 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col py-10 px-6">
				<!-- Back Button above Settings heading, inside sidebar -->
				<button @click="goBack" class="mb-2 flex items-center text-gray-500 hover:text-blue-600 dark:hover:text-blue-400">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
						<path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
					</svg>
					Back
				</button>
				<h2 class="text-xl font-bold text-gray-900 dark:text-white mb-10">Settings</h2>
				<nav class="flex flex-col space-y-2">
					<button
						class="flex items-center px-3 py-2 rounded-lg text-base font-medium transition-colors"
						:class="section === 'personal' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
						@click="section = 'personal'"
					>
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-3" >
							<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
						</svg>
						Personal Details
					</button>
					<button
						class="flex items-center px-3 py-2 rounded-lg text-base font-medium transition-colors"
						:class="section === 'security' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
						@click="section = 'security'"
					>
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-3" >
							<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
						</svg>
						Security
					</button>
					<button
						class="flex items-center px-3 py-2 rounded-lg text-base font-medium transition-colors"
						:class="section === 'preferences' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
						@click="section = 'preferences'"
					>
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-3" >
							<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
						</svg>
						Preferences
					</button>
				</nav>
			</aside>
			
			<!-- Main Content -->
			<main class="flex-1 flex flex-col items-center justify-start py-14 px-8">
				<div class="w-full max-w-5xl space-y-8">
					<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">{{ sectionTitle }}</h1>
					<div v-if="section === 'preferences'">
						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 w-full">
							<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Appearance</h2>
							<div class="flex items-center space-x-4">
								<span class="text-base text-gray-700 dark:text-gray-200">Theme:</span>
								<button @click="toggleTheme" class="flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
									<span v-if="isDarkMode" class="mr-2">🌙</span>
									<span v-else class="mr-2">🌞</span>
									<span>{{ isDarkMode ? 'Dark Mode' : 'Light Mode' }}</span>
								</button>
							</div>
						</div>
					</div>

					<div v-if="section === 'personal'">
						<!-- Profile Header -->
						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 flex flex-col items-center mb-4">
							<div class="h-24 w-24 rounded-full bg-indigo-600 flex items-center justify-center text-white text-3xl font-bold mb-3">
								{{ userInitials }}
							</div>
							<h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ currentUser?.first_name + ' ' + currentUser?.last_name }}</h2>
							<p class="text-gray-500 dark:text-gray-400">{{ currentUser?.email }}</p>
							<span
								v-if="currentUser"
								:class="[
									'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2',
									roleLabels[currentUser.user_role as number]?.classes || 'bg-gray-100 text-gray-800'
								]"
							>
								{{ roleLabels[currentUser.user_role as number]?.label || 'User' }}
							</span>
						</div>
						<!-- Account Statistics -->
						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mb-4">
							<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Statistics</h3>
							<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
								<div>
									<div class="text-xs text-gray-500 dark:text-gray-400">Member Since</div>
									<div class="text-base text-gray-900 dark:text-gray-100 font-semibold">{{ memberSince }}</div>
								</div>
								<div>
									<div class="text-xs text-gray-500 dark:text-gray-400">Last Login</div>
									<div class="text-base text-gray-900 dark:text-gray-100 font-semibold">{{ lastLogin }}</div>
								</div>
								<div>
									<div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
									<div class="text-green-600 dark:text-green-400 font-semibold">Active</div>
								</div>
							</div>
						</div>
						<!-- Role-Specific Information -->
						<div v-if="currentUser?.user_role === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mb-4">
							<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Administrator Information</h3>
							<ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
								<li>• Full system administration</li>
								<li>• User management</li>
								<li>• System configuration</li>
								<li>• Data analytics</li>
							</ul>
						</div>
						<div v-else-if="currentUser?.user_role === 2" class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mb-8">
							<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Clinic Information</h3>
							<div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
								<div><strong>Clinic Name:</strong> Medical Center ABC</div>
								<div><strong>License:</strong> MC-2024-001</div>
								<div><strong>Address:</strong> 123 Healthcare Ave</div>
								<div><strong>Contact:</strong> (555) 123-4567</div>
							</div>
						</div>
						<div v-else-if="currentUser?.user_role === 1" class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mb-8">
							<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Sales Information</h3>
							<div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
								<div><strong>Sales Region:</strong> North America</div>
								<div><strong>Employee ID:</strong> SAL-2024-001</div>
								<div><strong>Manager:</strong> John Smith</div>
								<div><strong>Department:</strong> Sales & Marketing</div>
							</div>
						</div>

						<!-- Security Status -->
						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mb-4 space-y-2">
							<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Security</h3>
							<div class="flex items-center space-x-3">
								<span class="text-base text-gray-700 dark:text-gray-200">Two-Factor Authentication (2FA):</span>
								<span v-if="twoFAEnabled" class="px-3 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-semibold">Enabled</span>
								<span v-else class="px-3 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-sm font-semibold">Disabled</span>
							</div>
							<div class="flex items-center space-x-3">
								<span class="text-base text-gray-700 dark:text-gray-200">One Time Code Via Email:</span>
								<span v-if="oneTimeEmail" class="px-3 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-semibold">Enabled</span>
								<span v-else class="px-3 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-sm font-semibold">Disabled</span>
							</div>
							<div class="flex items-center space-x-3">
								<span class="text-base text-gray-700 dark:text-gray-200">Backup Codes:</span>
								<span v-if="backupCodesEnabled" class="px-3 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-semibold">Enabled</span>
								<span v-else class="px-3 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-sm font-semibold">Disabled</span>
							</div>
						</div>
						
						<!-- Edit Personal Information -->
						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 mb-4">
							<h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-6">Edit Personal Information</h3>
							<form @submit.prevent="updateProfile" class="w-full space-y-6">
								<div>
									<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
										Full Name
									</label>
									<input
										v-model="profileForm.name"
										type="text"
										class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base"
									/>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
										Email Address
									</label>
									<input
										v-model="profileForm.email"
										type="email"
										class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base"
									/>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
										Phone Number
									</label>
									<input
										v-model="profileForm.phone"
										type="tel"
										class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base"
									/>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
										Department
									</label>
									<input
										v-model="profileForm.department"
										type="text"
										class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100 text-base"
									/>
								</div>
								<div class="pt-2 flex justify-end">
									<button
										type="submit"
										:disabled="isUpdating"
										class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed text-base font-semibold"
									>
										{{ isUpdating ? 'Updating...' : 'Update Profile' }}
									</button>
								</div>
							</form>
						</div>
					</div>

					<div v-else-if="section === 'security'">
						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 w-full mb-4">
							<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Two-Factor Authentication (2FA)</h2>
							<div class="flex items-center mb-6">
								<label class="flex items-center cursor-pointer">
									<input type="checkbox" v-model="twoFAEnabled" class="form-checkbox h-5 w-5 text-blue-600" />
									<span class="ml-3 text-gray-800 dark:text-gray-200 text-base font-medium">Enable 2FA with 4-digit PIN</span>
								</label>
							</div>
							<div v-if="twoFAEnabled">
								<div v-if="!pinSet" class="space-y-4">
									<div class="text-gray-700 dark:text-gray-300 mb-2">Set your 4-digit PIN:</div>
									<form @submit.prevent="handleSetPin" class="flex flex-col space-y-4 max-w-xs">
										<div class="flex space-x-3 justify-center">
											<input
												v-for="(d, i) in 4"
												:key="i"
												:ref="el => {
													const input = el as HTMLInputElement
													if (input && input.tagName === 'INPUT') setPinRefs[i].value = input
												}"
												v-model="pinBoxes[i]"
												:type="showPin ? 'text' : 'password'"
												maxlength="1"
												inputmode="numeric"
												pattern="\d"
												class="w-12 h-12 text-2xl text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
												@input="e => onPinBoxInput(i, 'set', e as InputEvent)"
												@keydown="e => onPinBoxKeydown(i, 'set', e)"
											/>
										</div>
										<label class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
											<input type="checkbox" v-model="showPin" class="form-checkbox" />
											<span>Show PIN</span>
										</label>
										<div v-if="pinError" class="text-red-600 text-sm text-center">{{ pinError }}</div>
										<button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Set PIN</button>
									</form>
								</div>
								<div v-else>
									<div class="flex items-center justify-between">
										<span class="text-green-700 dark:text-green-300 font-medium">2FA is enabled with a PIN.</span>
										<button @click="showChangePin = !showChangePin" class="text-blue-600 hover:underline dark:text-blue-400">Change PIN</button>
									</div>
									<div class="flex items-center justify-between my-4">
										<span class="text-green-700 text-sm">You will be required to enter your configured PIN every time you log in.</span>
									</div>
									<form v-if="showChangePin" @submit.prevent="handleChangePin" class="flex flex-col space-y-4 max-w-xs">
										<div class="flex space-x-3 justify-center">
											<input
												v-for="(d, i) in 4"
												:key="i"
												:ref="el => {
													const input = el as HTMLInputElement
													if (input && input.tagName === 'INPUT') changePinRefs[i].value = input
												}"
												v-model="newPinBoxes[i]"
												:type="showPin ? 'text' : 'password'"
												maxlength="1"
												inputmode="numeric"
												pattern="\d"
												class="w-12 h-12 text-2xl text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
												@input="e => onPinBoxInput(i, 'change', e as InputEvent)"
												@keydown="e => onPinBoxKeydown(i, 'change', e)"
											/>
										</div>
										<label class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
											<input type="checkbox" v-model="showPin" class="form-checkbox" />
											<span>Show PIN</span>
										</label>
										<div v-if="pinError" class="text-red-600 text-sm text-center">{{ pinError }}</div>
										<button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Change PIN</button>
									</form>
								</div>
							</div>
						</div>

						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 w-full mb-4">
							<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">One Time Code</h2>
							<div class="flex items-center mb-6">
								<label class="flex items-center cursor-pointer">
									<input type="checkbox" v-model="oneTimeEmail" class="form-checkbox h-5 w-5 text-blue-600" />
									<span class="ml-3 text-gray-800 dark:text-gray-200 text-base font-medium">Enable one-time verification code via email</span>
								</label>
							</div>
							<div v-if="oneTimeEmail">
								<div class="flex items-center justify-between">
									<span class="text-green-700 dark:text-green-300 font-medium">One Time Code Authentication Enabled</span>
								</div>
								<div class="flex items-center justify-between mt-4">
									<span class="text-green-700 text-sm">Each time you log in, a verification code will be sent to your email: <b>{{ currentUser?.email }}</b></span>
								</div>
							</div>
						</div>

						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 w-full mb-4">
							<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Backup Codes</h2>
							<div class="flex items-center mb-6">
								<label class="flex items-center cursor-pointer">
									<input type="checkbox" v-model="backupCodesEnabled" class="form-checkbox h-5 w-5 text-blue-600" />
									<span class="ml-3 text-gray-800 dark:text-gray-200 text-base font-medium">Enable and generate backup codes</span>
								</label>
							</div>
							<div v-if="backupCodesEnabled">
								<div class="flex items-center justify-between">
									<span class="text-green-700 dark:text-green-300 font-medium">Backup Codes Authentication Enabled</span>
									<button @click="generateBackupCodes()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
										</svg>
										Generate New Codes
									</button>
								</div>
								<div class="flex flex-col space-y-2 mb-4">
									<span class="text-green-700 text-sm">Each backup code is valid for one-time use only. Please store them securely, as they cannot be reused:</span>
									<ul class="mt-2 grid grid-cols-2 gap-2">
										<li v-for="code in backupCodes" :key="code" class="relative px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded text-center font-mono text-base text-gray-900 dark:text-white flex items-center justify-center">
											<span class="flex-1">{{ code }}</span>
											<button
												@click="copyBackupCode(code)"
												class="ml-2 p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
												title="Copy code"
												type="button"
											>
												<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
													<rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
													<rect x="3" y="3" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
												</svg>
											</button>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
			<div v-if="showDisable2FAModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-sm">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Disable Two-Factor Authentication?</h3>
					<p class="text-gray-700 dark:text-gray-300 mb-6">Are you sure you want to disable 2FA? This will remove your PIN and reduce account security.</p>
					<div class="flex justify-end space-x-3">
						<button @click="cancelDisable2FA" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold">Cancel</button>
						<button @click="confirmDisable2FA" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-semibold">Disable</button>
					</div>
				</div>
			</div>
			<div v-if="showDisableOneTimeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-md">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Disable One Time Code Authentication?</h3>
					<p class="text-gray-700 dark:text-gray-300 mb-6">Are you sure you want to disable One Time Code? This will the code requirement and reduce account security.</p>
					<div class="flex justify-end space-x-3">
						<button @click="cancelDisableOneTime" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold">Cancel</button>
						<button @click="confirmDisableOneTime" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-semibold">Disable</button>
					</div>
				</div>
			</div>
			<div v-if="showDisableBackupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-md">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Disable Backup Codes?</h3>
					<p class="text-gray-700 dark:text-gray-300 mb-6">Are you sure you want to disable backup codes? All generated codes will be deleted.</p>
					<div class="flex justify-end space-x-3">
						<button @click="cancelDisableBackup" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold">Cancel</button>
						<button @click="confirmDisableBackup" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-semibold">Disable</button>
					</div>
				</div>
			</div>
			<div v-if="showGenerateBUCodes" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-full max-w-md">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Generate New Backup Codes?</h3>
					<p class="text-gray-700 dark:text-gray-300 mb-6">
						Are you sure you want to generate new backup codes? This will invalidate your current codes and create a new set.
					</p>
					<div class="flex justify-end space-x-3">
						<button @click="cancelGenerateBackupCodes" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold">Cancel</button>
						<button @click="confirmGenerateBackupCodes" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-semibold">Generate</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useUser } from '@/composables/auth/useUser'
import { useRouter } from 'vue-router'
import { useThemeStore } from '@/stores/theme'
import { toast } from 'vue3-toastify'
import axios from 'axios'
import api from '@/services/api'
import 'vue3-toastify/dist/index.css'

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
const profileForm = ref({
	name: currentUser.value?.name || '',
	email: currentUser.value?.email || '',
	phone: '',
	department: ''
})
const roleLabels: Record<number, { label: string; classes: string }> = {
	0: { label: 'Admin',        classes: 'bg-purple-100 text-purple-800' },
	1: { label: 'Office Staff', classes: 'bg-yellow-100 text-yellow-800' },
	2: { label: 'Clinic',       classes: 'bg-blue-100 text-blue-800' },
	3: { label: 'Clinician',    classes: 'bg-green-100 text-green-800' },
	4: { label: 'Manufacturer', classes: 'bg-pink-100 text-pink-800' },
	5: { label: 'Billier',      classes: 'bg-red-100 text-red-800' },
}

function updateProfile() {
	isUpdating.value = true
	setTimeout(() => {
		// Simulate update
		isUpdating.value = false
	}, 1000)
}

// Add mock data for memberSince and lastLogin
const memberSince = 'Jan 2023'
const lastLogin = 'May 2024'

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

        toast.warn('Two-Factor Authentication has been disabled. Your account is now less secure.')
    } catch (err: unknown) {
        console.error('Failed to disable 2FA', err)
        toast.error('Failed to disable 2FA. Please try again.')
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
        toast.success(response.data.message)
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
        toast.success('Your PIN has been successfully changed.')
    } catch (err: unknown) {
        console.error('Failed to update 2FA PIN', err)
        toast.error('Failed to change PIN. Please try again.')
    }
}

// One Time Email logic
const oneTimeEmail = ref(localStorage.getItem('one-time-email-enabled') === 'true')
watch(oneTimeEmail, (val) => {
	requestDisableOneTime(val)
})

function requestDisableOneTime(val: boolean) {
    if (!val) {
        pendingOneTimeValue.value = val
        showDisableOneTimeModal.value = true
    } else {
		localStorage.setItem('one-time-email-enabled', 'true')
        oneTimeEmail.value = val
		toast.success('One Time Code Authentication is now enabled.')
    }
}

function cancelDisableOneTime() {
    oneTimeEmail.value = true
    showDisableOneTimeModal.value = false
    pendingOneTimeValue.value = null
}

function confirmDisableOneTime() {
    localStorage.removeItem('one-time-email-enabled')
    showDisableOneTimeModal.value = false
    pendingOneTimeValue.value = null
	toast.warn('One Time Code Authentication has been disabled. Your account is now less secure.')
	oneTimeEmail.value = false
}

// Backup Codes logic
const backupCodesEnabled = ref(localStorage.getItem('backup-codes-enabled') === 'true')
const backupCodes = ref<string[]>(JSON.parse(localStorage.getItem('backup-codes') || '[]'))
const showDisableBackupModal = ref(false)
const pendingBackupValue = ref<boolean | null>(null)
const showGenerateBUCodes = ref(false)

watch(backupCodesEnabled, (val, oldVal) => {
    if (!val) {
        requestDisableBackup(val)
    } else if (!oldVal && val) {
        generateBackupCodes()
    }
})

function requestDisableBackup(val: boolean) {
    if (!val) {
        pendingBackupValue.value = val
        showDisableBackupModal.value = true
    }
}

function generateBackupCodes() {
    backupCodes.value = Array.from({ length: 6 }, () =>
        Math.random().toString(36).slice(-8).toUpperCase()
    )
	backupCodesEnabled.value = true
	showDisableBackupModal.value = false
	pendingBackupValue.value = null
    localStorage.setItem('backup-codes-enabled', 'true')
    localStorage.setItem('backup-codes', JSON.stringify(backupCodes.value))
	toast.success('Backup Codes have been generated and enabled.')
}

function confirmDisableBackup() {
    backupCodesEnabled.value = false
    localStorage.removeItem('backup-codes-enabled')
    localStorage.removeItem('backup-codes')
    backupCodes.value = []
    showDisableBackupModal.value = false
    pendingBackupValue.value = null
	toast.warn('Backup Codes have been disabled. All generated codes have been deleted.')
}

function cancelDisableBackup() {
    backupCodesEnabled.value = true
    showDisableBackupModal.value = false
    pendingBackupValue.value = null
}

function copyBackupCode(code: string) {
    navigator.clipboard.writeText(code)
}

function confirmGenerateBackupCodes() {
	generateBackupCodes()
	showGenerateBUCodes.value = false
	toast.success('New backup codes have been generated.')
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

onMounted(() => {
  showGenerateBUCodes.value = false
})

onMounted(async () => {
    try {
        const response = await api.get('/auth/me')
        const userData = response.data
        twoFAEnabled.value = userData.tfa_enabled === 1
        pinSet.value = userData.tfa_enabled === 1
    } catch (err: unknown) {
        console.error('Failed to fetch user profile', err)
    }
})
</script> 