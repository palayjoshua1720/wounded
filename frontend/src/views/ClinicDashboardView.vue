<template>
	<div class="space-y-6">
		<!-- Test Notification Button -->
		<button @click="testNotification" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
		Test Notification
		</button>

		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clinic Dashboard</h1>
				<p class="text-gray-600 dark:text-gray-400">Overview of your clinic's activity</p>
			</div>
		</div>

		<!-- Stats Grid -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
			<div v-for="stat in stats" :key="stat.name" class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
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
								<component :is="CubeIcon" class="w-4 h-4 text-gray-400 dark:text-gray-500 mr-1" />
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
							<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ order.date }}</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Usage Reminders -->
			<div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Usage Log Reminders</h3>
				<div class="space-y-4">
					<div v-for="reminder in usageReminders" :key="reminder.serial" class="flex items-start space-x-3">
						<div class="flex-shrink-0">
							<component v-if="reminder.daysAgo === 0" :is="CheckCircleIcon" class="w-5 h-5 text-green-500" />
							<component v-else-if="reminder.daysAgo > 0 && reminder.daysAgo <= 2" :is="ClockIcon" class="w-5 h-5 text-yellow-500" />
							<component v-else :is="ExclamationTriangleIcon" class="w-5 h-5 text-red-500" />
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
		</div>
	</div>
</template>

<script setup lang="ts">
import { ShoppingCartIcon, DocumentTextIcon, ReceiptPercentIcon, ShieldCheckIcon, CubeIcon, CheckCircleIcon, ClockIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useNotification } from '@/composables/ui/useNotification';
const { notify } = useNotification();

function testNotification() {
  notify('Test Notification', {
    body: 'This is a test notification from WoundMed!',
    icon: '/icons/favicon-96x96.png'
  });
}

const stats = [
  {
    name: 'My Orders',
    value: '23',
    change: '+3 this month',
    icon: ShoppingCartIcon,
  },
  {
    name: 'Usage Logs',
    value: '156',
    change: '+12 this week',
    icon: DocumentTextIcon,
  },
  {
    name: 'Pending Invoices',
    value: '4',
    change: '2 due soon',
    icon: ReceiptPercentIcon,
  },
  {
    name: 'IVR Eligible',
    value: '89%',
    change: 'of patients',
    icon: ShieldCheckIcon,
  },
]

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
</script> 