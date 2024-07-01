<script setup>
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'

import DashboardAdmin from '@/views/pages/dashboard/admin.vue'
import DashboardDoctor from '@/views/pages/dashboard/doctor.vue'
import DashboardPatient from '@/views/pages/dashboard/patient.vue'

const authStore = useAuthStore()

const icon = computed(() => {
  switch (authStore?.user?.role) {
  case 'admin':
    return 'tabler-shield-check'
  case 'doctor':
    return 'tabler-stethoscope'
  case 'patient':
    return 'tabler-user'
  }
  
  return 'tabler-user'
})
</script>

<template>
  <div>
    <VRow class="match-height">
      <VCol
        cols="12"
        sm="6"
        md="4"
      >
        <VCard title="Account">
          <VCardText
            class="d-flex justify-center align-center"
            style="min-height: 180px;"
          >
            <VRow>
              <VCol
                cols="12"
                md="4"
                class="d-flex justify-center align-center"
              >
                <VAvatar
                  color="primary"
                  size="64"
                  rounded="sm"
                >
                  <VIcon
                    :icon="icon"
                    size="48"
                  />
                </VAvatar>
              </VCol>
              <VCol
                cols="12"
                md="8"
              >
                <h6 class="text-lg font-weight-medium">
                  Welcome, {{ authStore?.profile?.fullname || authStore?.user?.email }}
                </h6>
                <p class="mb-2">
                  You are logged in as <span class="font-weight-medium">{{ authStore?.user?.role }}</span>
                </p>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <DashboardAdmin v-if="authStore?.user?.role == 'admin'" />
      <DashboardDoctor v-if="authStore?.user?.role == 'doctor'" />
      <DashboardPatient v-if="authStore?.user?.role == 'patient'" />
    </VRow>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/libs/apex-chart.scss";
</style>

<route lang="yaml">
meta:
  requiresAuth: true
</route>
