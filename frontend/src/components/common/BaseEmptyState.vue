<template>
    <div :class="['empty-state', containerClass]">
        <div class="icon-stack">
            <div :class="['icon-ring', ringClass]" />
            <div :class="['icon-circle', iconBgClass]">
                <component :is="icon" :class="['icon', iconColorClass]" stroke-width="1.5" />
            </div>
        </div>

        <div class="copy">
            <p :class="['title', textClass]">
                {{ displayTitle }}
            </p>

            <p v-if="displayDescription" :class="['description', descriptionClass]">
                {{ displayDescription }}
            </p>
        </div>

        <div v-if="$slots.action" class="action">
            <slot name="action" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    icon: any

    /**
     * Whether this empty state is caused by searching/filtering.
     */
    searching?: boolean

    /**
     * Generic overrides.
     * If provided, these always take priority.
     */
    title?: string
    description?: string

    /**
     * Empty state messages.
     */
    emptyTitle?: string
    emptyDescription?: string

    /**
     * Search state messages.
     */
    searchTitle?: string
    searchDescription?: string

    variant?: 'default' | 'muted' | 'dark'
    bordered?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    searching: false,

    variant: 'default',
    bordered: true,

    emptyTitle: 'Nothing here yet',
    emptyDescription: '',

    searchTitle: 'No matching results found',
    searchDescription: 'Try adjusting your search or filters.'
})

const displayTitle = computed(() => {
    if (props.title) return props.title

    return props.searching
        ? props.searchTitle
        : props.emptyTitle
})

const displayDescription = computed(() => {
    if (props.description !== undefined) {
        return props.description
    }

    return props.searching
        ? props.searchDescription
        : props.emptyDescription
})

const containerClass = computed(() => {
    const base = props.bordered ? 'bordered' : ''

    switch (props.variant) {
        case 'muted':
            return `${base} bg-transparent`

        case 'dark':
            return `${base} bg-gray-50 dark:bg-gray-900/40`

        default:
            return `${base} bg-white dark:bg-gray-800/30`
    }
})

const ringClass = computed(() => {
    switch (props.variant) {
        case 'muted':
            return 'ring-8 ring-gray-100 dark:ring-gray-800'

        case 'dark':
            return 'ring-8 ring-gray-200/70 dark:ring-gray-700/50'

        default:
            return 'ring-8 ring-indigo-50 dark:ring-indigo-500/10'
    }
})

const iconBgClass = computed(() => {
    switch (props.variant) {
        case 'muted':
            return 'bg-gray-100 dark:bg-gray-800'

        case 'dark':
            return 'bg-white dark:bg-gray-800 shadow-sm'

        default:
            return 'bg-gradient-to-b from-indigo-50 to-white dark:from-indigo-500/10 dark:to-transparent'
    }
})

const iconColorClass = computed(() => {
    switch (props.variant) {
        case 'muted':
            return 'text-gray-400 dark:text-gray-500'

        case 'dark':
            return 'text-gray-600 dark:text-gray-300'

        default:
            return 'text-indigo-500 dark:text-indigo-400'
    }
})

const textClass = computed(() => {
    switch (props.variant) {
        case 'muted':
            return 'text-gray-600 dark:text-gray-400'

        case 'dark':
            return 'text-gray-800 dark:text-gray-100'

        default:
            return 'text-gray-800 dark:text-gray-100'
    }
})

const descriptionClass = computed(() => {
    switch (props.variant) {
        case 'muted':
            return 'text-gray-400 dark:text-gray-500'

        case 'dark':
            return 'text-gray-500 dark:text-gray-400'

        default:
            return 'text-gray-500 dark:text-gray-400'
    }
})
</script>

<style scoped>
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 3.5rem 1.5rem;
    border-radius: 16px;
    text-align: center;
    animation: fade-in-up 0.35s ease-out;
}

.bordered {
    border: 1px dashed rgb(229 231 235);
}

:global(.dark) .bordered {
    border-color: rgb(55 65 81);
}

.icon-stack {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
}

.icon-ring {
    position: absolute;
    inset: 0;
    border-radius: 18px;
}

.icon-circle {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 14px;
}

.icon {
    width: 26px;
    height: 26px;
}

.copy {
    max-width: 22rem;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.title {
    font-size: 0.9375rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.description {
    font-size: 0.8125rem;
    line-height: 1.6;
}

.action {
    margin-top: 0.25rem;
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .empty-state {
        animation: none;
    }
}
</style>