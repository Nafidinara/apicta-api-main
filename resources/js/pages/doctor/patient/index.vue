<script setup>
import axiosIns from '@/plugins/axios'
import { onMounted } from 'vue'
import { VDataTable } from 'vuetify/labs/VDataTable'

const headers = [
  { title: 'Fullname', key: 'fullname' },
  { title: 'Addres', key: 'address' },
  { title: 'Phone', key: 'phone' },
  { title: 'Emergency Phone', key: 'emergency_phone' },
  { title: 'Gender', key: 'gender' },
  { title: 'Age', key: 'age' },
  { title: 'Condition', key: 'condition' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const sortBy = ref([{ key: 'fullname', order: 'asc' }])

const search = ref('')
const patients = ref([])

const searchedPatients = computed(() => {
  return patients.value.filter(patient => {
    return Object.values(patient).some(value => {
      return String(value).toLowerCase().includes(search.value.toLowerCase())
    })
  })
})

const loading = ref(false)

onMounted(async () => {
  loading.value = true
  await axiosIns.get('/api/v1/doctor/patients')
    .then(response => {
      patients.value = response.data.data
    })
    .catch(error => {
      console.log(error)
    })
  loading.value = false
})
</script>

<template>
  <div>
    <VCard
      title="My Patients"
      :loading="loading"
    >
      <VCardText density="compact">
        <AppTextField
          v-model="search"
          prepend-inner-icon="tabler-search"
          :append-inner-icon="search.length > 0 ? 'tabler-x' : null"
          placeholder="Search"
          class="mb-2"
          @click:append-inner="search=''"
        />
        <VDataTable
          v-model:sort-by="sortBy"
          :headers="headers"
          :items="searchedPatients"
          :items-per-page="10"
          density="compact"
        >
          <template #item.actions="{ item }">
            <div class="d-flex gap-1">
              <RouterLink :to="`/doctor/patient/${item.raw.id}`">
                <IconBtn>
                  <VIcon
                    icon="mdi-information-outline"
                    color="primary"
                  />
                </IconBtn>
              </RouterLink> 
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </div>
</template>

<route lang="yaml">
meta:
  requiresAuth: true
  allowedRoles: ['doctor']
</route>
