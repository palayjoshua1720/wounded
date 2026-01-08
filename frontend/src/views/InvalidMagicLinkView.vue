<template>
	<div class="min-h-screen flex items-center justify-center bg-primary-bg dark:bg-primary-dark-bg py-12 px-4 sm:px-6 lg:px-8">
		<div class="max-w-2xl w-full">
			<!-- Main Content Card -->
			<div class="card bg-white dark:bg-gray-800 shadow-xl">
				<div class="card-body p-8 sm:p-12">
					<!-- 404 Illustration -->
					<div class="relative mb-8">
						<div class="absolute inset-0 flex items-center justify-center">
							<div class="w-48 h-48 rounded-full bg-primary dark:bg-primary-dark bg-opacity-10 dark:bg-opacity-10 animate-pulse"></div>
						</div>
						<div class="relative text-center">
							<h1 class="text-9xl font-extrabold text-primary dark:text-primary-dark opacity-20">404</h1>
							<div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
								<TriangleAlert class="w-24 h-24 text-primary dark:text-primary-dark" />
							</div>
						</div>
					</div>

					<!-- Error Message -->
					<div class="text-center space-y-4">
						<h2 class="text-3xl font-bold text-red-600 dark:text-red-400">
							Access Unavailable
						</h2>

						<p class="text-lg text-gray-600 dark:text-gray-300 mx-auto">
							This link is no longer available. It may have already been used, expired, or is no longer valid. 
							For security reasons, these links can only be opened once or until the order has been completed.
						</p>

						<p class="text-sm text-gray-500 dark:text-gray-400 mx-auto">
							Please contact the WoundMed representative or request a new link if you need continued access.
						</p>
					</div>

					<!-- Action Buttons -->
					<div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
						<router-link
							to="/"
							class="btn btn-primary w-full sm:w-auto group flex items-center justify-center space-x-2"
						>
							<House class="w-4 h-4" />
							<span>Return to Home</span>
						</router-link>
						<button
							@click="handleGoBack"
							class="btn btn-info w-full sm:w-auto group flex items-center justify-center space-x-2"
						>
							<ArrowLeft class="w-4 h-4" />
							<span>Go Back</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { useTitle } from '@/composables/ui/useTitle'
import { useRouter } from 'vue-router'
import { useColorScheme } from '@/composables/ui/useColorScheme'
import { onMounted, computed } from 'vue'
import {
    House, ArrowLeft, TriangleAlert
} from 'lucide-vue-next';

const appTitle = computed(() => process.env.VUE_APP_TITLE || 'WOUNDMED INC.')

useTitle(`${appTitle.value} - Page Not Found`)

const router = useRouter()
const { applyColorScheme } = useColorScheme()

// Apply color scheme on mount
onMounted(() => {
  	applyColorScheme()
})

// Handle going back
const handleGoBack = () => {
  	router.back()
}
</script>

<style scoped>
.animate-pulse {
	animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
	0%, 100% {
		opacity: 0.5;
		transform: scale(1);
	}
	50% {
		opacity: 0.2;
		transform: scale(1.05);
	}
}

/* Smooth transitions for all interactive elements */
.btn {
	transition: all 0.3s ease;
}

.btn:hover {
  	transform: translateY(-1px);
}

.btn:active {
  	transform: translateY(1px);
}

/* Ensure proper spacing in buttons */
.btn > * {
	display: inline-flex;
	align-items: center;
}
</style> 