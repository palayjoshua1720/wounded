import Swal from 'sweetalert2'
import type { SweetAlertOptions } from 'sweetalert2'
import type { SweetAlertResult } from 'sweetalert2'

export function confirmAction(options: SweetAlertOptions): Promise<SweetAlertResult> {
  return Swal.fire({
    icon: 'warning',
    showCancelButton: true,
    ...options,
  })
}
