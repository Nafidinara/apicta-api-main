<script setup>
import axiosIns from '@/plugins/axios'
import { showToast } from '@/plugins/toast'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { VDataTable } from 'vuetify/labs/VDataTable'

const route = useRoute()
const router = useRouter()

const search = ref('')
const doctor = ref({})
const assignedPatients = ref([])
const patients = ref([])

const unassignedPatients = computed(() => {
  return patients.value.filter(patient => {
    return !assignedPatients.value.some(assignedPatient => assignedPatient.id === patient.id)
  })
})

const searchedAssignedPatients = computed(() => {
  return assignedPatients.value.filter(patient => {
    return Object.values(patient).some(value => {
      return String(value).toLowerCase().includes(search.value.toLowerCase())
    })
  })
})

const searchedUnassignedPatients = computed(() => {
  return unassignedPatients.value.filter(patient => {
    return Object.values(patient).some(value => {
      return String(value).toLowerCase().includes(search.value.toLowerCase())
    })
  })
})

const currentTab = ref(0)

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
]

const selectedUnassignPatients = ref([])
const selectedAssignPatients = ref([])

const headers = [
  { title: 'Fullname', key: 'fullname' },
  { title: 'Phone', key: 'phone' },
  { title: 'Gender', key: 'gender' },
]

const sortBy1 = ref([{ key: 'fullname', order: 'asc' }])
const sortBy2 = ref([{ key: 'fullname', order: 'asc' }])

const unassignLoading = ref(false)
const assignLoading = ref(false)

async function unassignPatients() {
  if (selectedUnassignPatients.value.length === 0) return

  unassignLoading.value = true

  await axiosIns.post('/api/v1/admin/unassign', {
    doctor_id: route.params.id,
    patient_ids: selectedUnassignPatients.value,
  })
    .then (response => {
      showToast(response.data.message, 'success')
    })
    .catch(error => {
      showToast(error.response.data.error, 'error')
    })

  selectedUnassignPatients.value.forEach(patientId => {
    const idx = assignedPatients.value.findIndex(patient => patient.id === patientId)

    const toMove = assignedPatients.value[idx]

    assignedPatients.value.splice(idx, 1)
    patients.value.push(toMove)

    unassignLoading.value = false
  })

  selectedUnassignPatients.value = []
}

async function assignPatients() {
  if (selectedAssignPatients.value.length === 0) return

  assignLoading.value = true

  await axiosIns.post('/api/v1/admin/assign', {
    doctor_id: route.params.id,
    patient_ids: selectedAssignPatients.value,
  })
    .then (response => {
      showToast(response.data.message, 'success')
    })
    .catch(error => {
      showToast(error.response.data.error, 'error')
    })

  selectedAssignPatients.value.forEach(patientId => {
    const idx = patients.value.findIndex(patient => patient.id === patientId)

    const toMove = patients.value[idx]

    patients.value.splice(idx, 1)
    assignedPatients.value.push(toMove)
  })
  selectedAssignPatients.value = []

  assignLoading.value = false
}

const doctorLoading = ref(false)
const patientLoading = ref(false)

onMounted(() => {
  doctorLoading.value = true
  axiosIns.get(`/api/v1/admin/doctor/${route.params.id}`)
    .then(response => {
      doctor.value = response.data.data
      assignedPatients.value = response.data.data.patients
      doctorLoading.value = false
    })
    .catch(error => {
      console.log(error)
      doctorLoading.value = false
    })

  patientLoading.value = true
  axiosIns.get(`/api/v1/admin/patient`)
    .then(response => {
      patients.value = response.data.data
      patientLoading.value = false
    })
    .catch(error => {
      console.log(error)
      patientLoading.value = false
    })
})
</script>

<template>
  <div>
    <VRow>
      <VCol cols="4">
        <VCard
          title="Doctor Details"
          density="compact"
          :loading="doctorLoading"
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
                  <span class="font-weight-medium me-1">{{ doctor[detail.key] }}</span>
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
          title="Patients"
          density="compact"
          :loading="patientLoading"
        >
          <VTabs v-model="currentTab">
            <VTab>Assigned Patients</VTab>
            <VTab>Other Patients</VTab>
          </VTabs>

          <VCardText>
            <AppTextField
              v-model="search"
              prepend-inner-icon="tabler-search"
              :append-inner-icon="search.length > 0 ? 'tabler-x' : null"
              placeholder="Search"
              class="mb-2"
              @click:append-inner="search=''"
            />
            <VWindow v-model="currentTab">
              <VWindowItem key="0">
                <VDataTable
                  v-model="selectedUnassignPatients"
                  v-model:sort-by="sortBy1"
                  :headers="headers"
                  :items="searchedAssignedPatients"
                  :items-per-page="10"
                  show-select
                  density="compact"
                >
                  <template #footer.prepend>
                    <VBtn
                      color="primary"
                      variant="elevated"
                      :disabled="selectedUnassignPatients.length === 0 || unassignLoading"
                      :loading="unassignLoading"
                      @click="unassignPatients"
                    >
                      Unassign
                    </VBtn>
                    <VSpacer />
                  </template>
                </VDataTable>
              </VWindowItem>
              <VWindowItem key="1">
                <VDataTable
                  v-model="selectedAssignPatients"
                  v-model:sort-by="sortBy2"
                  :headers="headers"
                  :items="searchedUnassignedPatients"
                  :items-per-page="10"
                  show-select
                  density="compact"
                >
                  <template #footer.prepend>
                    <VBtn
                      color="primary"
                      variant="elevated"
                      :disabled="selectedAssignPatients.length === 0 || assignLoading"
                      :loading="assignLoading"
                      @click="assignPatients"
                    >
                      Assign
                    </VBtn>
                    <VSpacer />
                  </template>
                </VDataTable>
              </VWindowItem>
            </VWindow>
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
