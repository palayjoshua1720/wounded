<template>
    <div class="bg-gray-50 py-4 px-4">
        <!-- Loader -->
        <div v-if="loading" class="flex items-center justify-center">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-600">Validating magic link...</p>
            </div>
        </div>

        <!-- Authorized -->
        <div v-else-if="isAuthorized && displayOrder" class="max-w-8xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 inline-flex items-center gap-1">
                            <ReceiptText class="w-6 h-6" />
                            Order Details
                        </h1>
                        <p class="text-gray-600">WoundMed System - New Order Notification</p>
                    </div>
                    <span class="inline-flex items-center gap-1" :class="getStatusBadge(displayOrder.statusLabel)">
                        <component :is='getStatusIcon(displayOrder.statusLabel)' class="w-4 h-4 "/>
                        {{ displayOrder.statusLabel }}
                    </span>
                </div>

                <!-- Order Code + Tracking -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <FileText />
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Order Code</p>
                            <p class="text-sm font-semibold text-gray-900">{{ displayOrder.orderCode }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <Truck />
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Tracking Number</p>
                            <p class="text-sm font-semibold text-gray-900">{{ displayOrder.trackingNumber }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Info -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 inline-flex items-center gap-1">
                    <ShoppingCart class="w-5 h-5" />
                    Order Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Manufacturer</p>
                            <p class="text-sm text-gray-900 mt-1">{{ displayOrder.manufacturer }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ordering Clinic</p>
                            <p class="text-sm text-gray-900 mt-1">{{ displayOrder.orderingClinic }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ordering Clinician</p>
                            <p class="text-sm text-gray-900 mt-1">{{ displayOrder.orderingClinician }}</p>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Patient Name</p>
                            <p class="text-sm text-gray-900 mt-1">{{ displayOrder.patientName }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Order Created</p>
                            <p class="text-sm text-gray-900 mt-1">{{ displayOrder.createdAt }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900 inline-flex items-center gap-1">
                        <ShoppingBag class="w-5 h-5" />
                        Order Items
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <td class="px-6 py-4">Brand</td>
                                <td class="px-6 py-4">Graft Size</td>
                                <td class="px-6 py-4 text-center">Qty</td>
                                <td class="px-6 py-4 text-right">Subtotal</td>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in displayOrder.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ getBrandName(item.brand) }}</td>
                                <td class="px-6 py-4">{{ getSizeName(item.graftSize) }}</td>
                                <td class="px-6 py-4 text-center">{{ item.quantity }}</td>
                                <td class="px-6 py-4 text-right font-medium">
                                ${{ Number(item.subtotal).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>

                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right font-semibold">
                                Total Order Amount:
                                </td>
                                <td class="px-6 py-4 text-right text-lg font-bold">
                                ${{ Number(displayOrder.totalAmount).toFixed(2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Order Actions</h3>
                        <p class="text-sm text-gray-600">Update the order status</p>
                    </div>

                    <div>
                        <div v-if="orderActionConfig">
                            <button
                                v-if="orderActionConfig.nextStatus"
                                :class="orderActionConfig.buttonClass"
                                @click="handleAction(orderActionConfig.nextStatus)"
                            >
                                {{ orderActionConfig.label }}
                            </button>
                            <div
                                v-else
                                :class="orderActionConfig.finalMessageClass"
                            >
                                <component  :is='CircleCheckBig' class="w-4 h-4"/>
                                <span>{{ orderActionConfig.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unauthorized (redirect handled in script) -->
        <div v-else></div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    FileText, Truck, CircleCheckBig,
    BadgeCheck, TruckElectric, PackageCheck,
    PackageX, ReceiptText, ShoppingBag,
    ShoppingCart, 
} from 'lucide-vue-next';
import api from "@/services/api";
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface Order {
	order_id: number;
	order_code: string;
	ordered_at: string;
	order_status: OrderStatus;
	notes?: string;
	items: OrderItem[];
	tracking_num?: string;

	clinic?: Clinic;
	clinician?: Clinician;
	patient?: PatientInfo;
    manufacturer?: Manufacturer;
}

interface Clinic {
	clinic_id: number
	clinic_code: string
	clinic_name: string
	clinicians: Clinician[]
}

interface Clinician {
	id: number
	first_name: string
	middle_name: string
	last_name: string
}

interface PatientInfo {
	patient_id: number
	patient_name: string
	clinic_id?: number
	ivrs: IVR[]
	user_id: User[]
}

interface Brand {
	brand_id: string
	description: string
	brand_name: string
	mue: number
	manufacturer?: {
		manufacturer_id: string
		manufacturer_name: string
		primary_email: string | string[]
	}
}

interface IVR {
	ivr_id: number
	ivr_number: string
	eligibility_status: number
	manufacturer: Manufacturer
}

interface Manufacturer {
	manufacturer_id: string
	manufacturer_name: string
	primary_email: string
	brands: Brand[]
}

interface User {
	id: number
	first_name: string
	last_name: string
	brand: Brand[]
}

type OrderItem = {
	id: string;
	brandId: string;
	productType: 0 | 1
	sizeId: string;
	graft_id: number;
	quantity: number;
	asp: number;
	totalAsp?: number;
	deviceType?: string;
	graftStock?: number
}

interface GraftSize {
	graft_size_id: number
	size: string
	area: string
	price: number
	stock: number
	graft_status: number
	brand: Brand
}

interface DisplayOrderItem {
    id: string;
    brand: string;
    graftSize: string;
    quantity: number;
    subtotal: number;
}

interface DisplayOrder {
    orderId: number;
    orderCode: string;
    trackingNumber: string;
    statusLabel: OrderStatus;
    manufacturer: string;
    orderingClinic: string;
    orderingClinician: string;
    patientName: string;
    createdAt: string;
    items: DisplayOrderItem[];
    totalAmount: number;
}

type OrderStatus = 'submitted' | 'acknowledged' | 'shipped' | 'delivered' | 'cancelled'
const orderStatusMap: Record<OrderStatus, number> = {
	submitted: 0,
	acknowledged: 1,
	shipped: 2,
	delivered: 3,
	cancelled: 4
}

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const isAuthorized = ref(false);
const order = ref<Order | null>(null)
const graftSizes = ref<GraftSize[]>([])
const brands = ref<Brand[]>([])

const token = route.query.token as string;
const orderId = route.query.order_id as string;

/** Validate magic link */
async function accessMagicLink() {
    try {
        const { data } = await api.post("/magic-order-auth", { token, order_id: orderId });

        order.value = transformOrderResponse(data.order);
        isAuthorized.value = true;
    } catch (error) {
        router.replace({ name: "not-found-link" });
        return;
    } finally {
        loading.value = false;
    }
}

async function getAllGraftSizes() {
	try {
		const { data } = await api.get(`/management/order/getgraftsizes`, {
			headers: {
				Authorization: `Bearer ${localStorage.getItem('auth_token')}`
			}
		})
		graftSizes.value = data.graft_size_data || []

		const brandMap = new Map()
		graftSizes.value.forEach(gs => {
			const brand = gs.brand
			if (brand?.brand_id && !brandMap.has(brand.brand_id)) {
				brandMap.set(brand.brand_id, brand)
			}
		})
		brands.value = Array.from(brandMap.values())
	} catch (error) {
		console.error('Error loading graft sizes:', error)
		graftSizes.value = []
	} finally {
		//
	}
}

function transformOrderResponse(raw: any): Order {
    if (!raw) {
        return {
            order_id: 0,
            order_code: '',
            ordered_at: '',
            order_status: 'submitted',
            items: []
        }
    }

    return {
        order_id: Number(raw.order_id ?? raw.id ?? 0),
        order_code: String(raw.order_code ?? ''),
        ordered_at: String(raw.ordered_at ?? raw.created_at ?? ''),
        order_status: mapOrderStatus(raw.order_status),
        notes: raw.notes ?? '',
        items: normalizeOrderItems(raw.items),
        tracking_num: raw.tracking_num ?? raw.trackingNumber ?? '',
        clinic: raw.clinic,
        clinician: raw.clinician,
        patient: raw.patient,
        manufacturer: raw.manufacturer
    }
}

function normalizeOrderItems(rawItems: any): OrderItem[] {
    if (!rawItems) return [];

    let items = rawItems;
    if (typeof rawItems === 'string') {
        try {
            items = JSON.parse(rawItems);
        } catch {
            items = [];
        }
    }

    if (!Array.isArray(items)) return [];

    return items.map((it: any, idx: number) => ({
        id: String(it.id ?? idx),
        brandId: String(it.brandId ?? it.brand_id ?? ''),
        productType: Number(it.productType ?? it.product_type ?? 0) === 1 ? 1 : 0,
        sizeId: String(it.sizeId ?? it.size_id ?? it.graft_id ?? ''),
        graft_id: Number(it.graft_id ?? it.sizeId ?? it.size_id ?? 0),
        quantity: Number(it.quantity ?? 0),
        asp: Number(it.asp ?? 0),
        totalAsp: Number(it.asp ?? 0) * Number(it.quantity ?? 0),
        deviceType: it.deviceType ?? it.device_type ?? '',
        graftStock: Number(it.graftStock ?? it.graft_stock ?? 0)
    }))
}

function mapOrderStatus(status: any): OrderStatus {
    const labels: OrderStatus[] = ['submitted', 'acknowledged', 'shipped', 'delivered', 'cancelled'];
    if (typeof status === 'string' && labels.includes(status as OrderStatus)) {
        return status as OrderStatus;
    }
    const idx = Number(status);
    return labels[idx] ?? 'submitted';
}

const displayOrder = computed<DisplayOrder | null>(() => {
    const current = order.value;
    if (!current) return null;

    const brandLookup = new Map(
        (current.manufacturer?.brands ?? []).map(brand => [String(brand.brand_id), brand.brand_name])
    );

    const items = (current.items ?? []).map((item, idx) => {
        const brandId = String(item.brandId ?? '');
        const brand = brandLookup.get(brandId) || (brandId ? `${brandId}` : '—');
        const graftSizeId = item.sizeId || item.graft_id?.toString() || '';
        const graftSize = graftSizeId ? `${graftSizeId}` : '—';
        const quantity = Number(item.quantity ?? 0);
        const subtotal = Number(item.asp ?? 0) * quantity;

        return {
            id: item.id || String(idx),
            brand,
            graftSize,
            quantity,
            subtotal
        };
    });

    return {
        orderId: current.order_id || 0,
        orderCode: current.order_code || '—',
        trackingNumber: current.tracking_num || '—',
        statusLabel: current.order_status,
        manufacturer: current.manufacturer?.manufacturer_name || '—',
        orderingClinic: current.clinic?.clinic_name || '—',
        orderingClinician: formatClinicianName(current.clinician),
        patientName: current.patient?.patient_name || '—',
        createdAt: formatDate(current.ordered_at),
        items,
        totalAmount: items.reduce((sum, item) => sum + item.subtotal, 0)
    };
});

function getSizeName(graft_size: string | number) {
	if (!graft_size) return "Unknown size"
	const id = Number(graft_size)

	const graftSize = graftSizes.value.find(g => g.graft_size_id == id)

	return graftSize?.size ?? `Size ${graft_size}`
}

function getBrandName(brandId: string) {
	const brand = brands.value.find(b => b.brand_id == brandId)
	return brand ? brand.brand_name : `Brand ${brandId}`
}

function formatClinicianName(clinician?: Clinician) {
    if (!clinician) return '—';
    const parts = [clinician.first_name, clinician.middle_name, clinician.last_name]
        .filter(Boolean)
        .join(' ')
        .trim();
    return parts || '—';
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

/** Status badges */
const getStatusBadge = (status: string) => {
    const map: any = {
        submitted: "px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold uppercase",
        acknowledged: "px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold uppercase",
        shipped: "px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-semibold uppercase",
        delivered: "px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold uppercase",
        cancelled: "px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold uppercase",
    };
    return map[status] || "";
};

const getStatusIcon = (status: string) => {
    const map: any = {
        submitted: PackageCheck,
        acknowledged: BadgeCheck,
        shipped: TruckElectric,
        delivered: PackageCheck,
        cancelled: PackageX,
    };
    return map[status] || "";
}

type ActionConfig = {
    label: string;
    nextStatus?: OrderStatus;
    buttonClass?: string;
    finalMessageClass?: string;
    showSuccessIcon?: boolean;
};

const orderActionConfig = computed<ActionConfig | null>(() => {
    const status = order.value?.order_status;
    if (!status) return null;

    const map: Record<OrderStatus, ActionConfig> = {
        submitted: {
            label: 'Acknowledge Order',
            nextStatus: 'acknowledged',
            buttonClass: 'px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium'
        },
        acknowledged: {
            label: 'Mark as Shipped',
            nextStatus: 'shipped',
            buttonClass: 'px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium'
        },
        shipped: {
            label: 'Mark as Delivered',
            nextStatus: 'delivered',
            buttonClass: 'px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium'
        },
        delivered: {
            label: 'Order Completed',
            finalMessageClass: 'flex items-center gap-2 text-green-600 font-medium',
            showSuccessIcon: true
        },
        cancelled: {
            label: 'Order Cancelled',
            finalMessageClass: 'text-red-600 font-medium'
        }
    };

    return map[status];
});

async function handleAction(newStatus: OrderStatus) {
    if (!order.value) return;

    const result = await Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, update status"
    });

    if (!result.isConfirmed) return;

    Swal.fire({
		title: 'Updating Status',
		text: 'Please wait while we are updating…',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => {
			Swal.showLoading()
		}
	})

    try {
        const statusNumber = orderStatusMap[newStatus];

        await api.put(`/management/magicorder/update/${order.value.order_id}/updateorderstatus`, {
            order_status: statusNumber,
            token
        });

        Swal.close()
        order.value.order_status = newStatus;
        toast.success('Status has been updated successfully.')

    } catch (error) {
        console.error(error);
        Swal.close()
        toast.error('Failed to update order status.')
    }
}

onMounted(
    accessMagicLink
);

onMounted(async () => {
	await getAllGraftSizes()
})
</script>
