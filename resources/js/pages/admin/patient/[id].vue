<script setup>
import axiosIns from '@/plugins/axios'
import { showToast } from '@/plugins/toast'
import { useRoute, useRouter } from 'vue-router'
import { VDataTable } from 'vuetify/labs/VDataTable'

const route = useRoute()
const router = useRouter()

const search = ref('')
const patient = ref({})
const assignedDoctors = ref([])

const searchedAssignedDoctors = computed(() => {
  return assignedDoctors.value.filter(doctor => {
    return Object.values(doctor).some(value => {
      return String(value).toLowerCase().includes(search.value.toLowerCase())
    })
  })
})

const details = [
  {
    icon: 'tabler-user',
    title: 'Fullname',
    key: 'fullname',
  },
  {
    icon: 'tabler-address-book',
    title: 'Address',
    key: 'address',
  },
  {
    icon: 'tabler-phone',
    title: 'Phone',
    key: 'phone',
  },
  {
    icon: 'tabler-phone',
    title: 'Emergency Phone',
    key: 'emergency_phone',
  },
  {
    icon: 'tabler-gender-bigender',
    title: 'Gender',
    key: 'gender',
  },
  {
    icon: 'tabler-123',
    title: 'Age',
    key: 'age',
  },
  {
    icon: 'tabler-activity-heartbeat',
    title: 'Condition',
    key: 'condition',
  },
]

const selectedUnassignDoctors = ref([])

const headers = [
  { title: 'Fullname', key: 'fullname' },
  { title: 'Phone', key: 'phone' },
]

const sortBy = ref([{ key: 'fullname', order: 'asc' }])

const unassignLoading = ref(false)

async function unassignDoctors() {
  if (selectedUnassignDoctors.value.length === 0) return

  unassignLoading.value = true

  await axiosIns.post('/api/v1/admin/unassign/doctor', {
    patient_id: route.params.id,
    doctor_ids: selectedUnassignDoctors.value,
  })
    .then (response => {
      showToast(response.data.message, 'success')
    })
    .catch(error => {
      showToast(error.response.data.error, 'error')
    })

  selectedUnassignDoctors.value.forEach(doctorId => {
    const idx = assignedDoctors.value.findIndex(doctor => doctor.id === doctorId)

    assignedDoctors.value.splice(idx, 1)
  })

  selectedUnassignDoctors.value = []

  unassignLoading.value = false
}

const loading = ref(false)

onMounted(async () => {
  loading.value = true
  await axiosIns.get(`/api/v1/admin/patient/${route.params.id}`)
    .then(response => {
      patient.value = response.data.data
      assignedDoctors.value = response.data.data.doctors
    })
    .catch(error => {
      console.log(error)
    })
  loading.value = false
})
</script>

<template>
  <div>
    <VRow>
      <VCol cols="4">
        <VCard
          title="Patient Details"
          density="compact"
          :loading="loading"
        >
          <VCardText>
            <VList class="text-medium-emphasis">
              <VListItem
                v-for="detail in details"
                :key="detail.key"
              >
                <template #prepend>
                  <VIcon
                    :icon="detail.icon"
                    size="32"
                    start
                  />
                </template>
                <VListItemTitle>
                  <span class="font-weight-light me-1">{{ detail.title }}</span>
                </VListItemTitle>
                <VListItemMedia>
                  <span class="font-weight-medium me-1">{{ patient[detail.key] }}</span>
                </VListItemMedia>
              </VListItem>
            </VList>
          </VCardText>

          <VCardActions>
            <VBtn
              color="primary"
              variant="elevated"
              block
              @click="router.back"
            >
              Back
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
      <VCol>
        <VCard
          title="Doctors"
          density="compact"
          :loading="loading"
        >
          <VCardText>
            <AppTextField
              v-model="search"
              prepend-inner-icon="tabler-search"
              :append-inner-icon="search.length > 0 ? 'tabler-x' : null"
              placeholder="Search"
              class="mb-2"
              @click:append-inner="search=''"
            />
            <VDataTable
              v-model="selectedUnassignDoctors"
              v-model:sort-by="sortBy"
              :headers="headers"
              :items="searchedAssignedDoctors"
              :items-per-page="10"
              show-select
              density="compact"
            >
              <template #footer.prepend>
                <VBtn
                  color="primary"
                  variant="elevated"
                  :disabled="selectedUnassignDoctors.length === 0 || unassignLoading"
                  :loading="unassignLoading"
                  @click="unassignDoctors"
                >
                  Unassign
                </VBtn>
                <VSpacer />
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<route lang="yaml">
meta:
  requiresAuth: true
  allowedRoles: ['admin']
</route>

