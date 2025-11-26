<template>
    <div class="bg-gray-50 py-4 px-4">
        <!-- Loader -->
        <div v-if="loading" class="flex items-center justify-center">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-600">Validating access link...</p>
            </div>
        </div>

        <!-- Error Message -->
        <div v-else-if="errorMessage" class="max-w-4xl mx-auto">
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-red-800 mb-2">Invalid or Expired Link</h3>
                <p class="text-red-700 mb-4">{{ errorMessage }}</p>
                <router-link to="/login" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                    Back to Login
                </router-link>
            </div>
        </div>

        <!-- Authorized -->
        <div v-else-if="isAuthorized && ivr" class="max-w-8xl mx-auto">
            
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-start justify-between mb-4">

                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 inline-flex items-center gap-1">
                            <component :is="ReceiptText" class="w-6 h-6" />
                            IVR Request Details
                        </h1>
                        <p class="text-gray-600">WoundMed System - Insurance Verification Request</p>
                    </div>

                    <span class="inline-flex items-center gap-1" :class="getStatusBadge(ivr.eligibility_status)">
                        <component :is="getStatusIcon(ivr.eligibility_status)" class="w-4 h-4" />
                        {{ getStatusLabel(ivr.eligibility_status) }}
                    </span>

                </div>

                <!-- IVR Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">

                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <component :is="FileText" />
                        <div>
                            <p class="text-xs text-gray-500 font-medium">IVR Number</p>
                            <p class="text-sm font-semibold text-gray-900">{{ ivr.ivr_number }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <component :is="User" />
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Patient Name</p>
                            <p class="text-sm font-semibold text-gray-900">{{ patientName }}</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Request Information -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 inline-flex items-center gap-1">
                    <component :is="FileText" class="w-5 h-5" />
                    Request Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Manufacturer</p>
                            <p class="text-sm text-gray-900 mt-1">{{ manufacturerName }}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clinic</p>
                            <p class="text-sm text-gray-900 mt-1">{{ clinicName }}</p>
                        </div>

                    </div>

                    <div>
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Brand</p>
                            <p class="text-sm text-gray-900 mt-1">{{ brandName }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Submitted</p>
                            <p class="text-sm text-gray-900 mt-1">{{ formatDate(ivr.submitted_at) }}</p>
                        </div>
                    </div>

                </div>
                
                <!-- Notes -->
                <div class="mt-6" v-if="ivr.description">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Notes</p>
                    <p class="text-sm text-gray-900 mt-1">{{ ivr.description }}</p>
                </div>
            </div>

            <!-- Eligibility Status Update -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 inline-flex items-center gap-2">
                    <component :is="ClipboardCheck" class="w-5 h-5" />
                    Update Eligibility Status
                </h2>

                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Eligibility Status
                </label>

                <div class="flex items-center space-x-3">
                    <select
                        v-model="eligibilityStatus"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 w-48"
                    >
                        <option value="0">Pending</option>
                        <option value="1">Eligible</option>
                        <option value="2">Not Eligible</option>
                    </select>

                    <button
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold"
                        @click="submitEligibility"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? 'Saving...' : 'Save Status' }}
                    </button>
                </div>
            </div>


            <!-- Attached File -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6" v-if="ivr.filepath">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 inline-flex items-center gap-2">
                    <component :is="FileDown" class="w-5 h-5" />
                    Attached IVR File:
                </h2>
                <div>
                    <button
                        @click="downloadIVRForm(ivr.manufacturer_id)"
                        class="inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold"
                        target="_blank"
                    >
                        Download IVR File
                    </button>
                </div>
            </div>
        </div>
        <div v-else></div>
    </div>
</template>


<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import {
    FileText, User, ClipboardCheck, FileDown, ReceiptText
} from 'lucide-vue-next';
import api from "@/services/api";
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface IVR {
    ivr_id: number;
    ivr_number: string;
    clinic_id: number;
    eligibility_status: number;
    submitted_at: string;
    description: string;
    patient_id: number;
    brand_id: number;
    manufacturer_id: number;
    filepath?: string;
    
    // Relations
    clinic?: Clinic;
    brand?: Brand;
    manufacturer?: Manufacturer;
    patient?: PatientInfo;
}

interface Clinic {
    clinic_id: number;
    clinic_name: string;
}

interface Brand {
    brand_id: number;
    brand_name: string;
    manufacturer_id: number;
    manufacturer?: Manufacturer;
}

interface Manufacturer {
    manufacturer_id: number;
    manufacturer_name: string;
}

interface PatientInfo {
    patient_id: number;
    patient_name: string;
}

const route = useRoute();

const loading = ref(true);
const isAuthorized = ref(false);
const errorMessage = ref('');
const ivr = ref<IVR | null>(null);
const eligibilityStatus = ref('0');
const isSubmitting = ref(false);

const token = route.query.token as string;
const ivrId = route.query.ivr_id as string;

/** Validate magic link */
async function accessMagicLink() {
    try {
        const { data } = await api.post("/magic-ivr-auth", { token, ivr_id: ivrId });

        if (!data.success) {
            errorMessage.value = data.message || 'Invalid magic link.';
            return;
        }

        ivr.value = data.ivr_data;
        eligibilityStatus.value = String(data.ivr_data.eligibility_status || '0');
        isAuthorized.value = true;
    } catch (error: any) {
        console.error('Magic link error:', error);
        errorMessage.value = error.response?.data?.message || 'Invalid or expired magic link.';
    } finally {
        loading.value = false;
    }
}

/** Get formatted status label */
function getStatusLabel(status: number): string {
    const labels: Record<number, string> = {
        0: 'Pending',
        1: 'Eligible',
        2: 'Not Eligible'
    };
    return labels[status] || 'Unknown';
}

/** Status badges */
function getStatusBadge(status: number) {
    const map: Record<number, string> = {
        0: "px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold uppercase",
        1: "px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold uppercase",
        2: "px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold uppercase",
    };
    return map[status] || "px-3 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-semibold uppercase";
}

function getStatusIcon(status: number) {
    const map: Record<number, any> = {
        0: FileText,
        1: ClipboardCheck,
        2: FileText,
    };
    return map[status] || FileText;
}

function formatDate(dateString?: string) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return '—';
    
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
}

const patientName = computed(() => {
    return ivr.value?.patient?.patient_name || 'Unknown Patient';
});

const clinicName = computed(() => {
    return ivr.value?.clinic?.clinic_name || 'Unknown Clinic';
});

const brandName = computed(() => {
    return ivr.value?.brand?.brand_name || 'Unknown Brand';
});

const manufacturerName = computed(() => {
    return ivr.value?.manufacturer?.manufacturer_name || 'Unknown Manufacturer';
});

async function submitEligibility() {
    if (!ivr.value) return;
    
    isSubmitting.value = true;
    try {
        let result: any;

        const newStatus = parseInt(eligibilityStatus.value);

        if (newStatus === 1) {
            result = await Swal.fire({
                title: "Updating IVR: " + ivr.value.ivr_number,
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, update it!"
            });
        } else {
            result = { isConfirmed: true };
        }

        if (result?.isConfirmed) {
            await api.post(
                `/management/update/${ivr.value.ivr_id}/updateivrrequest`,
                {
                    eligibility_status: newStatus,
                    token
                },
                {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("auth_token")}`
                    }
                }
            );

            if (ivr.value) {
                ivr.value.eligibility_status = newStatus;
            }

            toast.success("Eligibility status updated successfully.");
            accessMagicLink()
        }
    } catch (error) {
        console.error('Error updating eligibility:', error);
        toast.error('Failed to update eligibility status.');
    } finally {
        isSubmitting.value = false;
    }
}

const downloadIVRForm = async (id: number) => {
	try {
		const response = await api.get(`/management/ivr/download/${id}/downloadivrform`, {
			responseType: 'blob',
		});

		const blob = new Blob([response.data]);
		const link = document.createElement('a');
		link.href = URL.createObjectURL(blob);
		link.download = 'IVR_Form.pdf';
		link.click();
		URL.revokeObjectURL(link.href);
	} catch (error) {
		console.error('Download failed:', error);
	}
};

onMounted(() => {
    accessMagicLink();
});
</script>