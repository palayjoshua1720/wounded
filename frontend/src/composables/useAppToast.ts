import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import type { ToastOptions } from 'vue3-toastify'

/**
 * Centralized toast composable — the single place that interacts with the toast library.
 *
 * Usage:
 *   const toast = useAppToast()
 *   toast.success('Saved!')
 *   toast.error('Something went wrong.')
 *   toast.warning('Check your input.')
 *   toast.info('FYI …')
 */
export function useAppToast() {
    function success(message: string, options?: ToastOptions) {
        return toast.success(message, options)
    }

    function error(message: string, options?: ToastOptions) {
        return toast.error(message, options)
    }

    function warning(message: string, options?: ToastOptions) {
        return toast.warning(message, options)
    }

    function info(message: string, options?: ToastOptions) {
        return toast.info(message, options)
    }

    return {
        success,
        error,
        warning,
        info,
    }
}
