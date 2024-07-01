<script setup>
import axiosIns from '@/plugins/axios'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { VForm } from 'vuetify/components/VForm'

import loginImg from '@images/login_illustration.png'

import { showToast } from '@/plugins/toast'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const isPasswordVisible = ref(false)
const refVForm = ref()
const email = ref('')
const password = ref('')
const rememberMe = ref(false)

const loading = ref(false)

async function login() {
  const validation = await refVForm.value.validate()
  if (!validation.valid) return

  loading.value = true

  axiosIns.post('/api/v1/account/login', {
    email: email.value,
    password: password.value,
  }).then(async response => {
    authStore.saveData(response.data.data.access_token)
    await authStore.getRoleUser()
    await authStore.getUser()
    showToast(response.data.message, 'success')
    loading.value = false
    router.push('/dashboard')
  }).catch(error => {
    showToast(error.response.data.error, 'error')
    loading.value = false
  })
}
</script>

<template>
  <VRow
    no-gutters
    class="auth-wrapper bg-surface"
  >
    <VCol
      md="7"
      lg="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-primary w-100 ma-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            max-width="505"
            :src="loginImg"
            class="auth-illustration rounded-lg"
            fluid
          />
        </div>
      </div>
    </VCol>

    <VCol
      cols="12"
      md="5"
      lg="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <VNodeRenderer
            :nodes="themeConfig.app.logo"
            class="mb-6"
          />

          <h5 class="text-h5 mb-1">
            Welcome to <span class="text-capitalize"> {{ themeConfig.app.title }}</span>
          </h5>

          <p class="mb-0">
            Please login to your account
          </p>
        </VCardText>

        <VCardText>
          <VAlert
            color="primary"
            variant="tonal"
          >
            <p class="text-caption mb-2">
              for testing purposes, you can use the following credentials:
            </p>
            <p class="text-caption mb-2">
              Email: <strong>admin@mail.com</strong> / Pass: <strong>password</strong>
            </p>
            <p class="text-caption mb-2">
              Email: <strong>doctor1@mail.com</strong> / Pass: <strong>password</strong>
            </p>
            <p class="text-caption mb-0">
              Email: <strong>patient1@mail.com</strong> / Pass: <strong>password</strong>
            </p>
          </VAlert>
        </VCardText>

        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="login"
          >
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  label="Email"
                  type="email"
                  autofocus
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="password"
                  label="Password"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <!-- 
                  <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                  <VCheckbox
                  v-model="rememberMe"
                  label="Remember me"
                  />
                  <a
                  class="text-primary ms-2 mb-1"
                  href="#"
                  >
                  Forgot Password?
                  </a>
                  </div> 
                -->
              </VCol>
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :disabled="!email || !password || loading"
                  class="mb-2"
                  :loading="loading"
                >
                  Login
                </VBtn>
                <a
                  href="/"
                  class="text-decoration-underline"
                >
                  Back to Home
                </a>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
  requiresAuth: false
</route>
