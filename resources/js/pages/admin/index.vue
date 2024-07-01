<template>
  <div>
    <VCard
      class="mb-6"
      title="Admin Page"
    >
      <VCardText>Admin</VCardText>
      <VCardText>
        Admin
      </VCardText>
    </VCard>
  </div>
</template>

<route lang="yaml">
meta:
  requiresAuth: true
  allowedRoles: ['admin']
</route>
