import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig, AxiosResponse } from 'axios'
import { useAppStore } from '@/stores/app'

// Create axios instance with default config
const api: AxiosInstance = axios.create({
  baseURL: process.env.VUE_APP_API_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const appStore = useAppStore()
    appStore.setLoading(true)

    // Get token from localStorage
    const token = localStorage.getItem('token')
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error: AxiosError) => {
    const appStore = useAppStore()
    appStore.setLoading(false)
    console.error('Request error:', error)
    appStore.setError('Request failed')
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response: AxiosResponse) => {
    const appStore = useAppStore()
    appStore.setLoading(false)
    return response
  },
  (error: AxiosError) => {
    const appStore = useAppStore()
    appStore.setLoading(false)
    console.error('Response error:', error)

    if (error.response) {
      console.error('Error response:', error.response.data)
      switch (error.response.status) {
        case 401:
          // Handle unauthorized
          appStore.setError('Unauthorized access')
          // Redirect to login if needed
          break
        case 403:
          appStore.setError('Access forbidden')
          break
        case 404:
          appStore.setError('Resource not found')
          break
        case 500:
          appStore.setError('Server error')
          break
        default:
          appStore.setError('An error occurred')
      }
    } else if (error.request) {
      console.error('No response received:', error.request)
      appStore.setError('No response from server')
    } else {
      console.error('Request setup error:', error.message)
      appStore.setError('Request failed')
    }

    return Promise.reject(error)
  }
)

export const sampleApi = {
  getMessage: async () => {
    try {
      const response = await api.get('/sample')
      return response.data
    } catch (error) {
      console.error('Sample API error:', error)
      throw error
    }
  }
}

export const authApi = {
  forgotPassword(email: string) {
    return api.post('/forgot-password', { email })
  },
  resetPassword(payload: { token: string, email: string, password: string, password_confirmation: string }) {
    return api.post('/reset-password', payload)
  },
}


export const userService = {
  getUsers(params?: { search?: string; role?: string | number; status?: string; page?: number }) {
    return api.get('/users', { params });
  },

  getUserStats() {
    return api.get('/users/stats');
  },

  getManufacturers() {
    return api.get('/management/manufacturers');
  },

  getClinics() {
    return api.get('/management/users/clinics');
  },

  getClinicians(simple = true) {
    return api.get('/management/users/clinician', { params: { simple } });
  },

  getUser(id: string) {
    return api.get(`/users/${id}`);
  },

  createUser(userData: any) {
    return api.post('/users', userData);
  },

  updateUser(id: string, userData: any) {
    return api.put(`/users/${id}`, userData);
  },

  toggleUserStatus(id: string) {
    return api.patch(`/users/${id}/toggle-status`);
  },

  archiveUser(id: string) {
    return api.patch(`/users/${id}/archive`);
  },

  softDeleteUser(id: string) {
    return api.delete(`/users/${id}/soft-delete`);
  },

  restoreUser(id: string) {
    return api.patch(`/users/${id}/restore`);
  },
};

export const inventoryService = {
  /**
   * Get all inventory items from usage logs (paginated)
   */
  getAllInventory(params?: { page?: number; per_page?: number; search?: string; status?: string; brand_id?: string }) {
    return api.get('/inventory/all', { params });
  },

  /**
   * Get all usage logs
   */
  getUsageLogs() {
    return api.get('/inventory/all');
  },

  /**
   * Get inventory by serial number
   */
  getInventoryBySerial(serialNumber: string) {
    return api.get(`/inventory/serial/${serialNumber}`);
  },

  /**
   * Get inventory by status
   */
  getInventoryByStatus(status: string) {
    return api.get(`/inventory/status/${status}`);
  },

  /**
   * Create a new usage log
   */
  createUsageLog(data: any) {
    return api.post('/inventory/usage-logs', data);
  },

  /**
   * Update a usage log
   */
  updateUsageLog(id: string | number, data: any) {
    return api.put(`/inventory/usage-logs/${id}`, data);
  },

  /**
   * Delete a usage log
   */
  deleteUsageLog(id: string | number) {
    return api.delete(`/inventory/usage-logs/${id}`);
  },

  /**
   * Search for patients by name
   */
  searchPatients(query: string) {
    return api.get('/inventory/search-patients', { params: { q: query } });
  },

  /**
   * Get graft size details by ID
   */
  getGraftSize(graftSizeId: string | number) {
    return api.get(`/inventory/graft-size/${graftSizeId}`);
  },

  /**
   * Get clinicians with clinic information for inventory
   */
  getClinicians() {
    return api.get('/inventory/clinicians');
  },

  /**
   * Get lightweight brands list for dropdowns (id + name only)
   */
  getBrandsList() {
    return api.get('/inventory/brands-list');
  },

  /**
   * Get lightweight graft sizes list for dropdowns (minimal fields only)
   */
  getGraftSizesList() {
    return api.get('/inventory/graft-sizes-list');
  },

  /**
   * Update inventory item status
   */
  updateInventoryStatus(id: string | number, logStatus: number) {
    return api.patch(`/inventory/${id}/status`, { log_status: logStatus });
  },
};

export const graftSizeService = {
  /**
   * Get all graft sizes (paginated)
   */
  getAllGraftSizes(params?: { page?: number; per_page?: number }) {
    return api.get('/management/graft-sizes', { params });
  },
};

export const orderService = {
  /**
   * Get all orders (paginated)
   */
  getAllOrders(params?: { page?: number; per_page?: number }) {
    return api.get('/management/order/getorders', { params });
  },
};

export const brandService = {
  /**
   * Get all brands (paginated)
   */
  getAllBrands(params?: { page?: number; per_page?: number }) {
    return api.get('/management/brands', { params });
  },
};

export const patientService = {
  /**
   * Get all patients with optional filters (minimal data for list view)
   */
  getPatients(params?: { search?: string; clinic_id?: string; page?: number }) {
    return api.get('/patients', { params });
  },

  /**
   * Get patient by ID with full details (HIPAA compliant with audit logging)
   */
  getPatientById(id: number) {
    return api.get(`/patients/${id}`);
  },

  /**
   * Get patient statistics
   */
  getPatientStats() {
    return api.get('/patients/stats');
  },

  /**
   * Create a new patient
   */
  createPatient(data: { patient_name: string; email: string; clinic_id?: string | null; user_id?: string | null }) {
    return api.post('/patients', data);
  },

  /**
   * Update a patient
   */
  updatePatient(id: number, data: { patient_name: string; email: string; clinic_id?: string | null }) {
    return api.put(`/patients/${id}`, data);
  },

  /**
   * Delete a patient
   */
  deletePatient(id: number) {
    return api.delete(`/patients/${id}`);
  },

  /**
   * Get clinics for patient dropdown
   */
  getPatientClinics() {
    return api.get('/patients/clinics/list');
  },

  /**
   * Get assignable clinicians (role 3) for the logged-in clinic user's clinic
   */
  getPatientAssignableClinicians() {
    return api.get('/patients/clinicians/list');
  },

  /**
   * Get all patients (legacy endpoint)
   */
  getAllPatients() {
    return api.get('/management/patients/patientinfo');
  },
};

export const dashboardService = {
  getMetrics() {
    return api.get('/dashboard/stats');
  },
  getRecentActivity() {
    return api.get('/dashboard/recent-activity');
  },
  getSystemAlerts() {
    return api.get('/dashboard/system-alerts');
  }
};

export const clinicDashboardService = {
  getOverview() {
    return api.get('/clinic-dashboard/order-overview');
  },
  getSystemAlerts() {
    return api.get('/clinic-dashboard/system-alerts');
  }
};

export const invoiceService = {
  getAllInvoices(params?: { page?: number; per_page?: number }) {
    return api.get('/invoice-management', { params });
  },
  getInvoiceStats() {
    return api.get('/invoice-management/stats');
  }
};

export const ivrService = {
  getAllIVRRequests(params?: { page?: number; per_page?: number }) {
    return api.get('/management/ivr/ivrrequests', { params });
  }
};

export const returnsService = {
  getAllReturns(params?: { page?: number; per_page?: number }) {
    return api.get('/management/returns', { params });
  },
  getReturnStats() {
    return api.get('/management/returns/stats');
  }
};

export const notificationService = {
  getNotifications(params?: {
    search?: string
    type?: string
    start_date?: string
    end_date?: string
    page?: number
    per_page?: number
    clinic_id?: number | string
  }) {
    return api.get('/notifications', { params })
  },
  getNotificationStats(params?: { clinic_id?: number | string }) {
    return api.get('/notifications/stats', { params })
  },
  markAsRead(id: string) {
    return api.post(`/notifications/${id}/read`)
  },
  markAllAsRead(ids: string[] = []) {
    return api.post('/notifications/mark-all-read', { ids })
  }
}

// ============================================================================
// INVENTORY LEDGER MANAGEMENT MODULE - SERVICE
// ----------------------------------------------------------------------------
// This service handles API calls for the standalone Inventory Ledger
// Management feature. To remove this module, delete this service block
// and all related frontend files.
// ============================================================================
export const inventoryLedgerService = {
  /**
   * Combined init endpoint: returns brands, clinics, products, invoices, stats
   * in a single request. Reduces 5 separate HTTP calls to 1.
   */
  getInitData() {
    return api.get('/inventory-ledger/init')
  },

  import(data: FormData) {
    return api.post('/inventory-ledger/import', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  getAll(params?: {
    page?: number
    per_page?: number
    search?: string
    status?: string
    brand_id?: string
    clinic_id?: string
    invoice_status?: string
    product_type?: string
  }) {
    return api.get('/inventory-ledger', { params })
  },

  getStats() {
    return api.get('/inventory-ledger/stats')
  },

  getById(id: string | number) {
    return api.get(`/inventory-ledger/${id}`)
  },

  create(data: any) {
    return api.post('/inventory-ledger', data)
  },

  update(id: string | number, data: any) {
    return api.put(`/inventory-ledger/${id}`, data)
  },

  delete(id: string | number) {
    return api.delete(`/inventory-ledger/${id}`)
  },

  restore(id: string | number) {
    return api.post(`/inventory-ledger/${id}/restore`)
  },

  getProducts() {
    return api.get('/inventory-ledger/products/list')
  },

  getInvoices() {
    return api.get('/inventory-ledger/invoices/list')
  },

  searchOrders(search: string, limit = 10) {
    return api.get('/inventory-ledger/orders/search', { params: { search, limit } })
  },

  downloadTemplate() {
    return api.get('/inventory-ledger/import/template', {
      responseType: 'blob',
    })
  },
}
// ============================================================================
// END INVENTORY LEDGER MANAGEMENT MODULE
// ============================================================================

// ============================================================================
// PATIENT GRAFT LOG MODULE - SERVICE
// ----------------------------------------------------------------------------
// Standalone API client for the Patient Graft Log feature. To remove this
// module, delete this entire block and the related view.
// ============================================================================
export const patientGraftLogService = {
  getInitData() {
    return api.get('/patient-graft-log/init')
  },
  getPatients(search = '') {
    return api.get('/patient-graft-log/patients', { params: { search } })
  },
  getPatientHistory(id: number | string) {
    return api.get(`/patient-graft-log/patient/${id}`)
  },
  searchSerials(search = '', includeUsed = false) {
    return api.get('/patient-graft-log/serials/search', {
      params: { search, include_used: includeUsed ? 1 : 0 },
    })
  },
  getClinicians() {
    return api.get('/patient-graft-log/clinicians')
  },
  create(data: any) {
    return api.post('/patient-graft-log', data)
  },
  update(id: number | string, data: any) {
    return api.put(`/patient-graft-log/${id}`, data)
  },
  delete(id: number | string) {
    return api.delete(`/patient-graft-log/${id}`)
  },
  deleteByPatient(patientId: number | string) {
    return api.delete(`/patient-graft-log/patient/${patientId}`)
  },
}
// ============================================================================
// END PATIENT GRAFT LOG MODULE
// ============================================================================

export { api }
export default api 