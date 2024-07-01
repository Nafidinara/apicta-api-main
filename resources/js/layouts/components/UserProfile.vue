<script setup>
import axiosIns from '@/plugins/axios'
import { showToast } from '@/plugins/toast'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

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

async function logout() {
  axiosIns.get('/api/v1/account/logout')
    .then(response => {
      authStore.deleteData()
      showToast('Logout successfully', 'success')
      router.push('/login')
    })
}
</script>

<template>
  <VCard
    class="cursor-pointer"
    color="primary"
    variant="outlined"
    :loading="!authStore?.user?.email"
  >
    <VCardItem class="py-0 px-2">
      <template #prepend>
        <VAvatar
          color="primary"
          variant="tonal"
        >
          <VIcon :icon="icon" size="32" color="primary" />
        </VAvatar>
      </template>
      <VCardTitle class="text-body-1 text-primary pa-0">
        {{ authStore?.user?.email }}
      </VCardTitle>
      <VCardSubtitle class="text-body-2 text-primary pa-0">
        {{ authStore?.role }}
      </VCardSubtitle>
    </VCardItem>

    <!-- SECTION Menu -->
    <VMenu
      activator="parent"
      width="230"
      location="bottom end"
      offset="14px"
    >
      <VList>
        <!-- 👉 Profile -->
        <VListItem
          link
          to="/profile"
        >
          <template #prepend>
            <VIcon
              class="me-2"
              icon="tabler-user"
              size="22"
            />
          </template>

          <VListItemTitle>Profile</VListItemTitle>
        </VListItem>

        <!-- Divider -->
        <VDivider class="my-2" />

        <!-- 👉 Logout -->
        <VListItem @click.prevent="logout">
          <template #prepend>
            <VIcon
              class="me-2"
              icon="tabler-logout"
              size="22"
            />
          </template>

          <VListItemTitle>Logout</VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
    <!-- !SECTION -->
  </VCard>
</template>
