<script setup>
const currentTab = ref(0)
</script>

<template>
  <div>
    <VCard>
      <VTabs v-model="currentTab">
        <VTab>Profile</VTab>
        <VTab>Account Settings</VTab>
        <VTab>Security</VTab>
      </VTabs>

      <VCardText>
        <VWindow v-model="currentTab">
          <VWindowItem key="0">
            <p>Profile</p>
            <p>
              Lorem ipsum dolor sit, amet consectetur adipisicing elit. Repudiandae, tempore dolorum eaque quibusdam quam nemo harum repellat quod sapiente officiis beatae assumenda illum praesentium cumque?
            </p>
          </VWindowItem>
          <VWindowItem key="1">
            <p>Account Settings</p>
            <p>
              Lorem ipsum dolor sit, amet consectetur adipisicing elit. Repudiandae, tempore dolorum eaque quibusdam quam nemo harum repellat quod sapiente officiis beatae assumenda illum praesentium cumque?
            </p>
          </VWindowItem>
          <VWindowItem key="2">
            <p>Security</p>
            <p>
              Lorem ipsum dolor sit, amet consectetur adipisicing elit. Repudiandae, tempore dolorum eaque quibusdam quam nemo harum repellat quod sapiente officiis beatae assumenda illum praesentium cumque?
            </p>
          </VWindowItem>
        </VWindow>
      </VCardText>
    </VCard>
  </div>
</template>

<route lang="yaml">
meta:
  requiresAuth: true
</route>
