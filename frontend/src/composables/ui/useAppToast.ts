import { useToast } from 'vue-toastification'
import StatusToast from '@/components/common/StatusToast.vue'

type StatusToastVariant = 'success' | 'error' | 'warning' | 'info'

const statusToastOptions = {
  icon: false,
  closeButton: false,
  hideProgressBar: true,
  toastClassName: 'status-toast-host',
  bodyClassName: 'status-toast-host-body',
} as const

export function useAppToast() {
  const toast = useToast()

  function show(variant: StatusToastVariant, title: string, message: string) {
    const content = {
      component: StatusToast,
      props: { variant, title, message },
    }

    if (variant === 'success') return toast.success(content, statusToastOptions)
    if (variant === 'warning') return toast.warning(content, statusToastOptions)
    if (variant === 'info') return toast.info(content, statusToastOptions)
    return toast.error(content, statusToastOptions)
  }

  return {
    success: (message: string, title = 'Success') => show('success', title, message),
    error: (message: string, title = 'Error') => show('error', title, message),
    warning: (message: string, title = 'Warning') => show('warning', title, message),
    info: (message: string, title = 'Information') => show('info', title, message),
  }
}
