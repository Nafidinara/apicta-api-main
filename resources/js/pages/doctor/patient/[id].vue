<script setup>
import Waveform from '@/components/Waveform.vue'
import axiosIns from '@/plugins/axios'
import { showToast, showToastCenter } from '@/plugins/toast'
import { useAuthStore } from '@/stores/auth'
import uDiagnose from '@/utils/diagnose'
import { requiredValidator } from '@validators'
import { Dropzone } from 'dropzone'
import { useRoute, useRouter } from 'vue-router'
import { VDataTable } from 'vuetify/labs/VDataTable'

const authStore = useAuthStore()

const route = useRoute()
const router = useRouter()

const search = ref('')
const patient = ref({})
const diagnoses = ref([])

const searchedDiagnoses = computed(() => {
  return diagnoses.value.filter(diagnose => {
    return Object.values(diagnose).some(value => {
      return String(value).toLowerCase().includes(search.value.toLowerCase())
    })
  })
})

const editDialog = ref(false)

const defaultItem = ref({
  id: '',
  diagnoses: '',
  notes: '',
  is_verified: false,
  file: '',
})

const editedItem = ref(defaultItem.value)
const editedIndex = ref(-1) 
const refForm = ref()

const loadingEdit = ref(false)
const loadingPredict = ref(false)

const close = () => {
  editDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
  refForm.value.reset()
}

const editItemShow = item => {
  editedIndex.value = diagnoses.value.indexOf(item)
  editedItem.value = {
    ...item,
  }
  editDialog.value = true
}

const save = async () => {
  const validation = await refForm.value.validate()
  if (!validation.valid) return

  loadingEdit.value = true

  if (editedIndex.value > -1) {
    await axiosIns.put(`/api/v1/patient/diagnoses/${editedItem.value.id}`, {
      diagnoses: editedItem.value.diagnoses,
      notes: editedItem.value.notes,
      is_verified: editedItem.value.is_verified,
    })
      .then (response => {
        showToast(response.data.message, 'success')
      })
      .catch(error => {
        showToast(error.response.data.error, 'error')
      })
    editedItem.value.updated_at = new Date()
    Object.assign(diagnoses.value[editedIndex.value], editedItem.value)
    loadDiagnoses()
  }
  loadingEdit.value = false
  close()
}

const repredict = async () => {
  loadingPredict.value = true

  const formData = new FormData()

  const fileData = await axiosIns.get('https://apicta-s3-bucket.s3.ap-southeast-1.amazonaws.com/' + editedItem.value.file, {
    responseType: 'blob',
    transformRequest: [(data, headers) => {
      delete headers.Authorization
      
      return data
    }],

  }).then(response => {
    return new File([response.data], 'audio.wav', { type: 'audio/wav' })
  }).catch(error => {
    showToastCenter('Cannot get audio file', 'error')
  })

  if (!fileData) {
    loadingPredict.value = false
    
    return
  }

  formData.append('file', fileData)
  formData.append('patient_id', patient.value.id)

  await axiosIns.post('/api/v1/public/predict', formData)
    .then (response => {
      const newDiagnose = response.data.data.result == 0 ? 'normal' : 'mi'
      if (newDiagnose == editedItem.value.diagnoses) {
        showToastCenter('No changes', 'info')
      } else {
        showToastCenter('Diagnoses changed to ' + uDiagnose.text(newDiagnose), 'success')
      }
      editedItem.value.diagnoses = newDiagnose
      editedItem.value.is_verified = false
    })
    .catch(error => {
      showToastCenter(error.response.data.error, 'error')
    })

  loadingPredict.value = false
}

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

const headers = [
  { title: 'Updated', key: 'updated_at' },
  { title: 'Notes', key: 'notes' },
  { title: 'Diagnoses', key: 'diagnoses' },
  { title: 'Verification', key: 'is_verified' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const sortBy = ref([{ key: 'updated_at', order: 'desc' }])

const loading = ref(false)

async function loadDiagnoses() {
  loading.value = true
  await axiosIns.get(`/api/v1/doctor/patients/${route.params.id}`)
    .then(response => {
      patient.value = response.data.data
      diagnoses.value = response.data.data.diagnoses?.map(item => ({
        ...item,
        is_verified: item.is_verified == 1,
      }))
    })
    .catch(error => {
      console.log(error)
    })
  loading.value = false
}

onMounted(async () => {
  await loadDiagnoses()

  if (route.query?.diagnoses_id) {
    const diagnosesId = route.query.diagnoses_id
    const item = diagnoses.value.find(item => item.id === diagnosesId)
    if (item) {
      editItemShow(item)
    } else {
      router.replace({ query: null })
    }
  }
})

function formatTimestamp(timestamp) {
  const date = new Date(timestamp)
  
  return date.toLocaleString(navigator.language)
}

// DROPZONE:START
const dropRef = ref(null)

Dropzone.autoDiscover = false

onMounted(() => {
  if(dropRef.value !== null) {
    new Dropzone(dropRef.value, {
      url: '/api/v1/doctor/predict',
      acceptedFiles: "audio/*",
      headers: {
        'Authorization': `Bearer ${authStore.access_token}`,
      },
      init: function() {
        this.on('sending', function(file, xhr, formData) {
          if (patient?.value?.id) {
            formData.append('patient_id', patient?.value?.id)
          } else {
            xhr.abort()
            showToast('Authenticating failed, please try again later', 'error')
          }
        })
        this.on('success', function(file, response) {
          this.removeFile(file)
          loadDiagnoses()
        })
        this.on('error', function(file, response) {
          this.removeFile(file)
          showToast(response?.message, 'error')
        })
      },
    })
  }
})

// DROPZONE:END
</script>

<template>
  <div>
    <VRow class="match-height">
      <VCol cols="3">
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
                density="compact"
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
      <VCol cols="9">
        <VCard
          title="Diagnoses History"
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
              v-model:sort-by="sortBy"
              :headers="headers"
              :items="searchedDiagnoses"
              :items-per-page="10"
              density="compact"
            >
              <template #item.updated_at="{ item }">
                {{ formatTimestamp(item.raw.updated_at) }}
              </template>

              <template #item.diagnoses="{ item }">
                <VChip
                  :color="uDiagnose.color(item.raw.diagnoses)"
                  label
                  variant="elevated"
                >
                  {{ uDiagnose.text(item.raw.diagnoses) }}
                </VChip>
              </template>

              <template #item.is_verified="{ item }">
                <VChip
                  :color="item.raw.is_verified ? 'success' : 'error'"
                  label
                  variant="elevated"
                >
                  {{ item.raw.is_verified ? 'Verified' : 'Not Verified' }}
                </VChip>
              </template>

              <template #item.actions="{ item }">
                <div class="d-flex gap-1">
                  <IconBtn @click="editItemShow(item.raw)">
                    <VIcon
                      icon="mdi-pencil-outline"
                      color="primary"
                    />
                  </IconBtn>
                </div>
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12">
        <VCard title="Upload Audio">
          <VCardText density="compact">
            <div
              ref="dropRef"
              class="dropzone custom-dropzone mt-2"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- DIALOG EDIT -->
    <VDialog
      v-model="editDialog"
      max-width="600px"
      persistent
    >
      <VCard>
        <VForm
          ref="refForm"
          @submit.prevent
        >
          <VCardTitle>
            <span class="headline">
              Edit Diagnoses
            </span>
          </VCardTitle>
          <VCardText>
            <VContainer>
              <VRow>
                <VCol cols="12">
                  <Waveform :url="'https://apicta-s3-bucket.s3.ap-southeast-1.amazonaws.com/' + editedItem.file" />
                </VCol>
                <VCol cols="12">
                  <AppTextarea
                    v-model="editedItem.notes"
                    label="Notes"
                    rows="2"
                    auto-grow
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="6">
                  <AppSelect
                    v-model="editedItem.diagnoses"
                    :items="['normal', 'mi']"
                    label="Diagnose"
                    density="compact"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="6">
                  <VLabel class="text-body-2">
                    Verification
                  </VLabel>
                  <VSwitch
                    v-model="editedItem.is_verified"
                    color="success"
                    :inset="false"
                    :label="editedItem.is_verified ? 'Verified' : 'Not Verified'"
                  />
                </VCol>
              </VRow>
            </VContainer>
          </VCardText>

          <VCardActions>
            <VBtn
              color="primary"
              variant="tonal"
              :loading="loadingPredict"
              :disabled="loadingPredict || loadingEdit"
              @click="repredict"
            >
              Re-Predict
            </VBtn>
            <VSpacer />
            <VBtn
              color="primary"
              variant="outlined"
              :disabled="loadingPredict || loadingEdit"
              @click="close"
            >
              Cancel
            </VBtn>
            <VBtn
              color="success"
              variant="elevated"
              :loading="loadingEdit"
              :disabled="loadingPredict || loadingEdit"
              @click="save"
            >
              Save
            </VBtn>
          </VCardActions>
        </VForm>
      </VCard>
    </VDialog>
  </div>
</template>

<style>
.custom-dropzone {
  border-style: dashed;
  border-width: 2px;
  padding: 16px;
  border-radius: 4px;
  color:rgb(114, 114, 114);
  text-align: center;
}
</style>

<route lang="yaml">
meta:
  requiresAuth: true
  allowedRoles: ['doctor']
</route>

