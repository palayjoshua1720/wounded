<template>
	<div class="space-y-6">
		<!-- Welcome Banner -->
		<div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-4 rounded-2xl shadow-md flex items-center justify-between">
			<div class="flex items-center space-x-4">
				<div class="w-20 h-20 rounded-full overflow-hidden shadow-md flex items-center justify-center bg-indigo-700">
					<span class="text-lg font-bold text-white">
							{{ userInitials }}
					</span>
				</div>

				<div>
					<h2 class="text-xl font-bold">Welcome back, {{ currentUser?.first_name + ' ' + ' ' + currentUser?.last_name }}</h2>
					<p class="text-sm opacity-90">Here's a quick overview of the system today.</p>
				</div>
			</div>
		</div>

		<!-- Stats Grid -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
			<div v-for="stat in stats" :key="stat.name" class="relative bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<!-- Loader inside each stat -->
				<div v-if="statsLoader" class="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-gray-900/70 z-10 rounded-lg">
					<span class="relative flex items-center justify-center h-16 w-16">
						<svg class="absolute animate-ping-slow h-12 w-12 text-blue-400 dark:text-blue-700 opacity-30" viewBox="0 0 64 64" fill="none">
							<circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4"/>
						</svg>
						<svg class="relative h-8 w-8 text-blue-600 dark:text-blue-400" viewBox="0 0 64 64" fill="none">
							<rect x="27" y="12" width="10" height="40" rx="3" fill="currentColor"/>
							<rect x="12" y="27" width="40" height="10" rx="3" fill="currentColor"/>
						</svg>
					</span>
				</div>

				<!-- Content -->
				<div class="flex items-center justify-between">
					<div>
					<p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.name }}</p>
					<p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stat.value }}</p>
					</div>
					<div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
					<component :is="stat.icon" class="w-6 h-6 text-green-600 dark:text-green-400" />
					</div>
				</div>
				<div class="mt-4">
					<span class="text-sm text-gray-500 dark:text-gray-400">{{ stat.change }}</span>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<!-- Recent Orders -->
			<div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Orders</h3>
				<div class="space-y-4">
					<div v-for="order in recentOrders" :key="order.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
						<div class="flex-1">
							<p class="font-medium text-gray-900 dark:text-white">{{ order.id }}</p>
							<p class="text-sm text-gray-600 dark:text-gray-400">{{ order.patient }}</p>
							<p class="text-sm text-gray-500 dark:text-gray-500">{{ order.product }}</p>
							<div v-if="order.serials.length > 0" class="flex items-center mt-2">
								<component :is="Box" class="w-4 h-4 text-gray-400 dark:text-gray-500 mr-1" />
								<span class="text-xs text-gray-500 dark:text-gray-400">
									Serials: {{ order.serials.join(', ') }}
								</span>
							</div>
						</div>
						<div class="text-right">
							<span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
								order.status === 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
								order.status === 'shipped' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' :
								'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400']">
								{{ order.status }}
							</span>
							<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
								{{ order.date ? formatDate(order.date) : 'N/A' }}
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Usage Reminders -->
			<div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Usage Log Reminders</h3>
				<div class="space-y-4">
					<div v-for="reminder in usageReminders" :key="reminder.serial" class="flex items-center space-x-3">
						<div class="flex-shrink-0">
							<component v-if="reminder.daysAgo === 0" :is="CircleCheck" class="w-5 h-5 text-green-500" />
							<component v-else-if="reminder.daysAgo > 0 && reminder.daysAgo <= 2" :is="Clock" class="w-5 h-5 text-yellow-500" />
							<component v-else :is="TriangleAlert" class="w-5 h-5 text-red-500" />
						</div>
						<div class="flex-1">
							<p class="text-sm font-medium text-gray-900 dark:text-white">
								{{ reminder.product }} - {{ reminder.serial }}
							</p>
							<p class="text-sm text-gray-600 dark:text-gray-400">Patient: {{ reminder.patient }}</p>
							<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
								<span v-if="reminder.daysAgo === 0">Delivered today - log usage</span>
								<span v-else>Delivered {{ reminder.daysAgo }} days ago - pending usage log</span>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Recent Activity -->
			<div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
				<div >
					<div v-if="recentActivity.length > 0" class="space-y-4">
						<div
							v-for="log in recentActivity"
							:key="log.audit_log_id"
							class="flex items-center justify-between p-2 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700"
						>
							<div class="flex items-center space-x-3">
								<component
									:is="log.status === 0 ? CircleCheck : TriangleAlert"
									:class="[
									'w-5 h-5 mt-1',
									log.status === 0 ? 'text-green-500' : 'text-red-500'
									]"
								/>

								<div>
									<p class="text-sm font-medium text-gray-900 dark:text-white">
									{{ humanizeActionType(log.action_type) }}
									<br>
									<span class="text-sm text-gray-700 dark:text-gray-200">
										{{ parseActionMessage(log.action_message).text }}
									</span>
									<br v-if="parseActionMessage(log.action_message).value">
									<span v-if="parseActionMessage(log.action_message).value" class="inline-flex items-center py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
										{{ parseActionMessage(log.action_message).value }}
									</span>
									</p>
									<p class="text-sm text-gray-600 dark:text-gray-400">
									{{ humanizeActionType(log.entity_type) }} <span class="text-gray-400">(IP: {{ log.ip_address }})</span>
									</p>
									<p class="text-xs text-gray-600 dark:text-gray-400">
									{{ timeAgo(log.timestamp) }}
									</p>
								</div>
							</div>

							<div class="text-right">
							<span
								class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
								:class="log.status === 0 
								? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200' 
								: 'bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200'"
							>
								{{ log.status === 0 ? 'Success' : 'Failed' }}
							</span>
							<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
								{{ formatDate(log.timestamp) }}
							</p>
							</div>
						</div>
					</div>

					<div v-else class="flex flex-col items-center justify-center gap-2 py-6">
						<Users class="w-10 h-10 mb-1" />
						<span>No recent activity found.</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
    ShoppingCart,
    FileText,
    Hospital,
    Users,
    Box,
    CircleCheck,
    Clock,
    TriangleAlert,
} from 'lucide-vue-next';
import { useNotification } from '@/composables/ui/useNotification';
import api from '@/services/api'
import { useUser } from '@/composables/auth/useUser'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const { userInitials } = useUser()

const currentUser = computed(() => authStore.currentUser)

interface AuditLog {
	audit_log_id: number
	user_id: number | null
	attempted_identifier: string | null
	ip_address: string | null
	action_type: string
	action_message: string
	entity_id: number | null
	entity: string
	entity_type: string
	status: number
	timestamp: string
}

const clinicCount = ref(0)
const clinicianCount = ref(0)
const recentActivity = ref<AuditLog[]>([])
const statsLoader = ref(false);

const { notify } = useNotification();
function testNotification() {
	notify('Test Notification', {
		body: 'This is a test notification from WoundMed!',
		icon: '/icons/favicon-96x96.png'
	});
}

const stats = computed(() => [
	{
		name: 'Clinics',
		value: clinicCount.value.toString() || '0',
		change: '',
		icon: Hospital,
	},
	{
		name: 'Clinicians',
		value: clinicianCount.value.toString() || '0',
		change: '',
		icon: Users,
	},
	{
		name: 'My Orders',
		value: '23',
		change: '+3 this month',
		icon: ShoppingCart,
	},
	{
		name: 'Usage Logs',
		value: '156',
		change: '+12 this week',
		icon: FileText,
	},
])

const recentOrders = [
	{
		id: 'ORD-001',
		patient: 'John Doe',
		product: 'Graft Matrix Pro',
		status: 'delivered',
		date: '2025-01-27',
		serials: ['GM001', 'GM002'],
	},
	{
		id: 'ORD-002',
		patient: 'Jane Smith',
		product: 'Wound Care Plus',
		status: 'shipped',
		date: '2025-01-26',
		serials: ['WC003'],
	},
	{
		id: 'ORD-003',
		patient: 'Bob Johnson',
		product: 'Skin Graft Advanced',
		status: 'pending',
		date: '2025-01-25',
		serials: [],
	},
]

const usageReminders = [
	{
		serial: 'GM001',
		product: 'Graft Matrix Pro',
		patient: 'John Doe',
		deliveredDate: '2025-01-27',
		daysAgo: 0,
	},
	{
		serial: 'WC005',
		product: 'Wound Care Plus',
		patient: 'Mary Johnson',
		deliveredDate: '2025-01-25',
		daysAgo: 2,
	},
	{
		serial: 'SG002',
		product: 'Skin Graft Advanced',
		patient: 'David Wilson',
		deliveredDate: '2025-01-23',
		daysAgo: 4,
	},
]

const formatDate = (dateStr: string) => {
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	})
}

function humanizeActionType(type: string | null | undefined) {
	if (!type) return ''
	return type
		.toString()
		.replace(/_/g, ' ')
		.replace(/\b\w/g, (m) => m.toUpperCase())
}

function parseActionMessage(msg: string | null | undefined) {
	if (!msg) return { text: '', value: null }

	// Helper map for eligibility codes
	const eligMap: Record<string, string> = {
		'0': 'Pending',
		'1': 'Eligible',
		'2': 'Not Eligible',
	}

	// If message contains a colon, use the last segment as value
	if (msg.includes(':')) {
		const parts = msg.split(':')
		const text = parts.slice(0, parts.length - 1).join(':').trim()
		let value = parts[parts.length - 1].trim()

		// map eligibility numeric values when applicable
		if (/^\d$/.test(value) && /elig/i.test(msg)) {
			value = eligMap[value] ?? value
		}

		if (value === '' || value === '0') return { text, value: null }

		return { text, value }
	}

	// Try to detect trailing IVR token like "#IVR-..." or trailing single-digit eligibility codes (e.g., "Updated2")
	const ivrMatch = msg.match(/(.*?)(#?IVR-[A-F0-9-]+)$/i)
	if (ivrMatch) {
		const text = ivrMatch[1].trim()
		const value = ivrMatch[2].trim()
		return { text, value }
	}

	const eligMatch = msg.match(/^(.*?)(?:\s|-|:)?([012])$/)
	if (eligMatch) {
		const text = eligMatch[1].trim()
		const code = eligMatch[2]
		const value = eligMap[code] ?? code
		return { text, value }
	}

	// Fallback: return full message as text, no value
	return { text: msg.trim(), value: null }
}

function timeAgo(timestamp: string | number | Date): string {
	const now = Date.now();
	const date = new Date(timestamp).getTime();

	const diff = Math.floor((now - date) / 1000);

	if (diff < 60) return `${diff} sec${diff !== 1 ? "s" : ""} ago`;
	if (diff < 3600) {
		const mins = Math.floor(diff / 60);
		return `${mins} min${mins !== 1 ? "s" : ""} ago`;
	}
	if (diff < 86400) {
		const hours = Math.floor(diff / 3600);
		return `${hours} hr${hours !== 1 ? "s" : ""} ago`;
	}
	const days = Math.floor(diff / 86400);
	return `${days} day${days !== 1 ? "s" : ""} ago`;
}


onMounted(async () => {
	statsLoader.value = true;
	try {
		const [clinicsRes, cliniciansRes, recentActivities] = await Promise.all([
			api.get('/management/users/clinics', {
				headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
			}),
			api.get('/management/users/clinician', {
				headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
			}),
			api.get('/utility/activity/clinician', {
				headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
			})
		])

		clinicCount.value = clinicsRes.data.meta.total
		clinicianCount.value = cliniciansRes.data.meta.total
		recentActivity.value = recentActivities.data.data

	} catch (error) {
		console.error('Error fetching stats:', error)
	} finally {
		statsLoader.value = false;
	}
})
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
</style> 