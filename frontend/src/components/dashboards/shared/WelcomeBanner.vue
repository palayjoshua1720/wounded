<template>
    <div class="banner-wrapper text-white p-4 rounded-2xl shadow-md flex items-center justify-between">
        <!-- Decorative ghost circles -->
        <span class="banner-circle" style="width:160px;height:160px;top:-60px;right:60px;"></span>
        <span class="banner-circle" style="width:80px;height:80px;bottom:-30px;right:200px;"></span>
        <span class="banner-circle" style="width:40px;height:40px;top:10px;right:240px;"></span>

        <div class="flex items-center gap-3.5 relative z-10">
            <div
                class="w-20 h-20 rounded-full bg-white/20 border border-white/25 flex items-center justify-center flex-shrink-0">
                <span class="text-lg font-bold text-white">{{ userInitials }}</span>
            </div>
            <div>
                <h2 class="text-xl font-bold">
                    Welcome back, {{ currentUser?.first_name }} {{ currentUser?.last_name }}
                </h2>
                <p class="text-sm opacity-90">{{ message }}</p>
            </div>
        </div>
        <p class="text-xs opacity-70 hidden sm:block relative z-10">{{ todayLabel }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useUser } from '@/composables/auth/useUser'
import { useAuthStore } from '@/stores/auth'
import dayjs from 'dayjs'

const props = withDefaults(defineProps<{
    message?: string
}>(), {
    message: "Here's a quick overview of your dashboard today."
})

const authStore = useAuthStore()
const { userInitials } = useUser()
const currentUser = computed(() => authStore.currentUser)

const todayLabel = computed(() =>
    dayjs().format('ddd, MMM D, YYYY')
)
</script>

<style scoped>
.banner-wrapper {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    position: relative;
    overflow: hidden;
}

.banner-circle {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.12);
    pointer-events: none;
}
</style>
