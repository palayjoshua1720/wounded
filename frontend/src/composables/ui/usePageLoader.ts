// composables/usePageLoader.ts
import { ref } from 'vue'

export const pageLoader = ref(false)

export function usePageLoader() {
  const show = () => pageLoader.value = true
  const hide = () => pageLoader.value = false

  return { pageLoader, show, hide }
}