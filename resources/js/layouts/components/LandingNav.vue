<script setup>
import { useThemeConfig } from '@core/composable/useThemeConfig'

import logo from '@images/logo.png'

// @layouts plugin
import { TopNavLayout } from '@layouts'

const { appRouteTransition } = useThemeConfig()
</script>

<template>
  <TopNavLayout>
    <!-- 👉 navbar -->
    <template #navbar>
      <div class="d-flex h-100 align-center">
        <img
          :src="logo"
          alt="MyoScope"
          height="32"
        >
        <h1 class="text-h2 ms-2">
          MyoScope
        </h1>

        <VSpacer />

        <VBtn
          color="primary"
          outlined
          to="/login"
          prepend-icon="mdi-login"
        >
          Login
        </VBtn>
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
  </TopNavLayout>
</template>
