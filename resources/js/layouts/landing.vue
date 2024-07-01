<script setup>
import { useSkins } from '@core/composable/useSkins'

const LandingNav = defineAsyncComponent(() => import('./components/LandingNav.vue'))

const { layoutAttrs, injectSkinClasses } = useSkins()

injectSkinClasses()
</script>

<template>
  <LandingNav v-bind="layoutAttrs" />
</template>

<style lang="scss">
// As we are using `layouts` plugin we need its styles to be imported
@use "@layouts/styles/default-layout";
</style>
