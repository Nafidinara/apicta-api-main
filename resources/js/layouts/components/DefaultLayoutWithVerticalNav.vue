<script setup>
import { useAuthStore } from '@/stores/auth'
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { computed } from 'vue'

// Components
import Footer from '@/layouts/components/Footer.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

const { appRouteTransition, isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()

const authStore = useAuthStore()

// DYNAMIC SIDEBAR NAV ITEMS
const navItems = computed(()=>{
  const items = [
    { heading: 'Dashboard' },
    {
      title: 'Home',
      to: { name: 'dashboard' },
      icon: { icon: 'tabler-smart-home' },
    },
  ]

  if (authStore.role == 'admin') {
    items.push(...[
      { heading: 'User Management' },
      {
        title: 'Patient',
        to: { name: 'admin-patient' },
        icon: { icon: 'tabler-user-filled' },
      },
      {
        title: 'Doctor',
        to: { name: 'admin-doctor' },
        icon: { icon: 'tabler-stethoscope' },
      },
    ])
  } else if (authStore.role == 'doctor') {
    items.push(...[
      { heading: 'Patients' },
      {
        title: 'My Patients',
        to: { name: 'doctor-patient' },
        icon: { icon: 'tabler-user-filled' },
      },
    ])
  } else if (authStore.role == 'patient') {
    items.push(...[
      { heading: 'Diagnoses' },
      {
        title: 'My Diagnoses',
        to: { name: 'patient-diagnoses' },
        icon: { icon: 'tabler-clipboard-check' },
      },
    ])
  }

  items.push(...[
    { heading: 'Account' },
    {
      title: 'Profile',
      to: { name: 'profile' },
      icon: { icon: 'tabler-user' },
    },
  ])

  return items
})
</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn
          v-if="isLessThanOverlayNavBreakpoint(windowWidth)"
          id="vertical-nav-toggle-btn"
          class="ms-n3"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon
            size="26"
            icon="tabler-menu-2"
          />
        </IconBtn>

        <!-- <NavbarThemeSwitcher /> -->

        <VSpacer />

        <UserProfile />
      </div>
    </template>

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Transition
        :name="appRouteTransition"
        mode="out-in"
      >
        <Component :is="Component" />
      </Transition>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>
