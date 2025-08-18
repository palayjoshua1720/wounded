<template>
	<div class="space-y-6">
		<div>
			<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
			<p class="text-gray-600 dark:text-gray-400">Overview of your medical inventory system</p>
		</div>

		<!-- Stats Grid -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
			<div v-for="stat in stats" :key="stat.name" class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.name }}</p>
						<p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stat.value }}</p>
					</div>
					<div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
						<component :is="stat.icon" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
					</div>
				</div>
				<div class="mt-4 flex items-center">
					<span :class="['text-sm font-medium', stat.changeType === 'positive' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
						{{ stat.change }}
					</span>
					<span class="text-sm text-gray-500 dark:text-gray-400 ml-2">from last month</span>
				</div>
			</div>
		</div>
		
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
				<div class="space-y-4">
					<div
						v-for="order in recentActivity"
						:key="order.id"
						class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
					>
						<component
							:is="getActivityIcon(order.type)"
							class="w-4 h-4"
							:class="getIconStyle(order.type)"
						/>
						
						<div class="flex-1 ml-3">
							<p class="font-medium text-gray-900 dark:text-white">{{ order.action }}</p>
							<p class="text-sm text-gray-600 dark:text-gray-400">{{ order.clinic }}</p>
						</div>

						<div class="text-right">
							<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ order.time }}</p>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Alerts -->
			<div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Alerts</h3>
				<div class="space-y-4">
					<div v-for="(alert, index) in alerts" :key="index" class="flex items-start space-x-3">
						<div class="flex-shrink-0">
							<component v-if="alert.type === 'warning'" :is="ExclamationTriangleIcon" class="w-5 h-5 text-yellow-500" />
							<component v-else-if="alert.type === 'info'" :is="InformationCircleIcon" class="w-5 h-5 text-blue-500" />
							<component v-else-if="alert.type === 'error'" :is="XCircleIcon" class="w-5 h-5 text-red-500" />
						</div>
						<div class="flex-1">
							<p class="text-sm text-gray-900 dark:text-white">{{ alert.message }}</p>
							<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ alert.time }}</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { UserGroupIcon, ShoppingCartIcon, CubeIcon, CurrencyDollarIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline'
import {
	ExclamationTriangleIcon, InformationCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

const stats = [
	{
		name: 'Total Orders',
		value: '156',
		change: '+12.5%',
		changeType: 'positive',
		icon: ShoppingCartIcon,
	},
	{
		name: 'Revenue',
		value: '$125,430',
		change: '+8.2%',
		changeType: 'positive',
		icon: CurrencyDollarIcon,
	},
	{
		name: 'Active Clinics',
		value: '24',
		change: '+4.1%',
		changeType: 'positive',
		icon: UserGroupIcon,
	},
	{
		name: 'Grafts Used',
		value: '423',
		change: '-2.3%',
		changeType: 'negative',
		icon: CubeIcon,
	},
]

const recentActivity = [
    { id: 1, action: 'New order submitted', clinic: 'Metro Wound Care', time: '2 minutes ago', type: 'order' },
    { id: 2, action: 'Usage log uploaded', clinic: 'Advanced Healing', time: '15 minutes ago', type: 'usage' },
    { id: 3, action: 'Invoice payment received', clinic: 'City Medical', time: '1 hour ago', type: 'payment' },
    { id: 4, action: 'IVR approved', clinic: 'Metro Wound Care', time: '2 hours ago', type: 'ivr' },
    { id: 5, action: 'Return processed', clinic: 'Healing Center', time: '3 hours ago', type: 'return' }
]

const getActivityIcon = (type: string) => {
	switch (type) {
		case 'order':
			return CubeIcon;
		case 'usage':
			return CheckCircleIcon;
		case 'payment':
			return CurrencyDollarIcon;
		case 'ivr':
			return CheckCircleIcon;
		case 'return':
			return CubeIcon;
		default:
			return ClockIcon;
	}
};

const getIconStyle = (type: string) => {
	switch (type) {
		case 'order':
			return 'text-blue-600 w-5 h-5';
		case 'usage':
			return 'text-green-600 w-5 h-5';
		case 'payment':
			return 'text-green-600 w-5 h-5';
		case 'ivr':
			return 'text-indigo-600 w-5 h-5';
		case 'return':
			return 'text-orange-600 w-5 h-5';
		default:
			return 'text-gray-600 w-5 h-5';
	}
};

const alerts = [
	{
		type: 'warning',
		message: '12 grafts expiring within 30 days',
		time: '2 hours ago',
	},
	{
		type: 'error',
		message: '3 usage logs overdue',
		time: '4 hours ago',
	},
	{
		type: 'info',
		message: '5 IVR requests pending',
		time: '1 day ago',
	},
	{
		type: 'warning',
		message: '8 invoices unpaid > 30 days',
		time: '1 day ago',
	},
]
</script>