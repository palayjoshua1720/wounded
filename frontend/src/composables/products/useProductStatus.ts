// src/composables/products/useProductStatus.ts
import { CheckCircle2, XCircle, Archive } from 'lucide-vue-next'

export const PRODUCT_STATUSES = {
    0: {
        label: 'Active',
        classes:
            'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800',
    },
    1: {
        label: 'Inactive',
        classes:
            'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800',
    },
    2: {
        label: 'Archived',
        classes:
            'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400 border border-orange-200 dark:border-orange-800',
    },
} as const

export function useProductStatus() {
    const getStatusClasses = (status: number | null | undefined) => {
        if (status == null) {
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700'
        }

        const entry = PRODUCT_STATUSES[status as keyof typeof PRODUCT_STATUSES]
        return (
            entry?.classes ||
            'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700'
        )
    }

    const getStatusIcon = (status: number | null | undefined) => {
        if (status == null) return XCircle

        switch (status) {
            case 0:
                return CheckCircle2
            case 1:
                return XCircle
            case 2:
                return Archive
            default:
                return XCircle
        }
    }

    const getStatusLabel = (status: number | null | undefined) => {
        if (status == null) return 'Unknown'

        return PRODUCT_STATUSES[status as keyof typeof PRODUCT_STATUSES]?.label || 'Unknown'
    }

    return {
        getStatusClasses,
        getStatusIcon,
        getStatusLabel,
        PRODUCT_STATUSES, // optional – if you ever need the full map
    }
}