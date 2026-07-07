<template>
    <div class="flex items-center space-x-2">
        <button v-for="action in visibleActions" :key="action.id" @click="handleClick(action)"
            :disabled="action.disabled" :class="getActionClasses(action)" :title="getActionTitle(action)" type="button">
            <component :is="action.icon" :class="iconSizeClass" />
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

export interface ActionConfig {
    id: string
    icon: any
    title: string
    altTitle?: string
    onClick: () => void
    visible?: boolean
    disabled?: boolean
    variant?: 'default' | 'success' | 'danger' | 'warning' | 'info'
    hoverColor?: 'blue' | 'indigo' | 'red' | 'green' | 'orange' | 'purple'
    iconOnly?: boolean
}

const props = withDefaults(defineProps<{
    actions: ActionConfig[]
    iconSize?: 'sm' | 'md' | 'lg'
    buttonSize?: 'sm' | 'md' | 'lg'
}>(), {
    iconSize: 'md',
    buttonSize: 'md'
})

const emit = defineEmits<{
    (e: 'action', action: ActionConfig): void
}>()

const iconSizeClass = computed(() => {
    switch (props.iconSize) {
        case 'sm': return 'w-3 h-3'
        case 'lg': return 'w-5 h-5'
        default: return 'w-4 h-4'
    }
})

const buttonPaddingClass = computed(() => {
    switch (props.buttonSize) {
        case 'sm': return 'p-1.5'
        case 'lg': return 'p-3'
        default: return 'p-2'
    }
})

const visibleActions = computed(() => {
    return props.actions.filter(action => action.visible !== false)
})

function getActionClasses(action: ActionConfig): string {
    const baseClasses = [
        buttonPaddingClass.value,
        'rounded-lg',
        'transition-all',
        'duration-200',
        'inline-flex',
        'items-center',
        'justify-center'
    ]

    // Base color
    const colorMap: Record<string, string> = {
        blue: 'text-gray-500 hover:text-blue-600 dark:hover:text-blue-400',
        indigo: 'text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400',
        red: 'text-gray-500 hover:text-red-600 dark:hover:text-red-400',
        green: 'text-gray-500 hover:text-green-600 dark:hover:text-green-400',
        orange: 'text-gray-500 hover:text-orange-600 dark:hover:text-orange-400',
        purple: 'text-gray-500 hover:text-purple-600 dark:hover:text-purple-400',
    }

    const hoverBgMap: Record<string, string> = {
        blue: 'hover:bg-blue-50 dark:hover:bg-blue-900/20',
        indigo: 'hover:bg-indigo-50 dark:hover:bg-indigo-900/20',
        red: 'hover:bg-red-50 dark:hover:bg-red-900/20',
        green: 'hover:bg-green-50 dark:hover:bg-green-900/20',
        orange: 'hover:bg-orange-50 dark:hover:bg-orange-900/20',
        purple: 'hover:bg-purple-50 dark:hover:bg-purple-900/20',
    }

    const hoverColor = action.hoverColor || 'blue'
    const colorClass = colorMap[hoverColor] || colorMap.blue
    const hoverBgClass = hoverBgMap[hoverColor] || hoverBgMap.blue

    // Variant-based styling (for toggle actions that change color based on state)
    if (action.variant === 'success') {
        baseClasses.push('text-gray-500 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20')
    } else if (action.variant === 'danger') {
        baseClasses.push('text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20')
    } else if (action.variant === 'warning') {
        baseClasses.push('text-gray-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20')
    } else if (action.variant === 'info') {
        baseClasses.push(colorClass, hoverBgClass)
    } else {
        baseClasses.push(colorClass, hoverBgClass)
    }

    if (action.disabled) {
        baseClasses.push('opacity-50', 'cursor-not-allowed', 'pointer-events-none')
    }

    return baseClasses.join(' ')
}

function getActionTitle(action: ActionConfig): string {
    if (action.altTitle) {
        return action.altTitle
    }
    return action.title
}

function handleClick(action: ActionConfig) {
    if (!action.disabled) {
        emit('action', action)
        action.onClick()
    }
}
</script>
