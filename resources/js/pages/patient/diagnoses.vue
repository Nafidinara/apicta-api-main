<script setup>
import Waveform from '@/components/Waveform.vue'
import axiosIns from '@/plugins/axios'
import { showToast } from '@/plugins/toast'
import { useAuthStore } from '@/stores/auth'
import uDiagnose from '@/utils/diagnose'
import { Dropzone } from 'dropzone'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { VDataTable } from 'vuetify/labs/VDataTable'

const route = useRoute()
const router = useRouter()

const authStore = useAuthStore()

const defaultItem = ref({
  id: '',
  diagnoses: '',
  notes: '',
  is_verified: false,
  file: '',
  doctor: {
    fullname: '',
  },
})

const editDialog = ref(false)
const editedItem = ref(defaultItem.value)
const editedIndex = ref(-1)

const close = () => {
  editDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
}

const itemShow = item => {
  editedIndex.value = diagnoses.value.indexOf(item)
  editedItem.value = { ...item }
  editDialog.value = true
}

const headers = [
  { title: 'Updated', key: 'updated_at' },
  { title: 'Doctor Name', key: 'doctor.fullname' },
  { title: 'Notes', key: 'notes' },
  { title: 'Diagnoses', key: 'diagnoses' },
  { title: 'Verification', key: 'is_verified' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const sortBy = ref([{ key: 'updated_at', order: 'desc' }])

const search = ref('')
const diagnoses = ref([])

const searchedDiagnoses = computed(() => {
  return diagnoses.value.filter(diagnose => {
    return Object.values(diagnose).some(value => {
      return String(value).toLowerCase().includes(search.value.toLowerCase())
    })
  })
})

const loading = ref(false)

async function loadDiagnoses() {
  loading.value = true
  await axiosIns.get('/api/v1/patient/diagnoses')
    .then(response => {
      diagnoses.value = response.data.data?.map(item => ({
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
      itemShow(item)
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
      url: '/api/v1/patient/predict', 
      acceptedFiles: "audio/*",
      headers: {
        'Authorization': `Bearer ${authStore.access_token}`,
      },
      init: function() {
        this.on('sending', function(file, xhr, formData) {
          if (authStore?.profile?.id) {
            formData.append('patient_id', authStore.profile.id)
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
    <VCard
      title="My Diagnoses"
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
              <IconBtn @click="itemShow(item.raw)">
                <VIcon
                  icon="mdi-information-outline"
                  color="primary"
                />
              </IconBtn>
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>

    <VCard
      class="mt-4"
      title="Upload Audio"
    >
      <VCardText density="compact">
        <div
          ref="dropRef"
          class="dropzone custom-dropzone mt-2"
        />
      </VCardText>
    </VCard>
    
    <!-- DIALOG SHOW -->
    <VDialog
      v-model="editDialog"
      max-width="600px"
    >
      <VCard>
        <VForm
          ref="refForm"
          @submit.prevent
        >
          <VCardTitle>
            <span class="headline">
              Diagnoses
            </span>
          </VCardTitle>
          <VCardText>
            <VContainer>
              <VRow>
                <VCol cols="12">
                  <Waveform :url="'https://apicta-s3-bucket.s3.ap-southeast-1.amazonaws.com/' + editedItem.file" />
                </VCol>
                <VCol cols="12">
                  <VLabel class="text-body-2 mb-1">
                    Doctor Name
                  </VLabel>
                  <div class="text-h5">
                    {{ editedItem.doctor.fullname }}
                  </div>
                </VCol>
                <VCol cols="12">
                  <VLabel class="text-body-2 mb-1">
                    Notes
                  </VLabel>
                  <div class="text-h5">
                    {{ editedItem.notes }}
                  </div>
                </VCol>
                <VCol cols="6">
                  <VLabel class="text-body-2 mb-1">
                    Diagnoses
                  </VLabel>
                  <div>
                    <VChip
                      :color="uDiagnose.color(editedItem.diagnoses)"
                      label
                      variant="elevated"
                    >
                      {{ uDiagnose.text(editedItem.diagnoses) }}
                    </VChip>
                  </div>
                </VCol>
                <VCol cols="6">
                  <VLabel class="text-body-2 mb-1">
                    Verification
                  </VLabel>
                  <div>
                    <VChip
                      :color="editedItem.is_verified ? 'success' : 'error'"
                      label
                      variant="elevated"
                    >
                      {{ editedItem.is_verified ? 'Verified' : 'Not Verified' }}
                    </VChip>
                  </div>
                </VCol>
                <VCol cols="12">
                  <VLabel class="text-body-2 mb-1">
                    Updated
                  </VLabel>
                  <div class="text-h5">
                    {{ formatTimestamp(editedItem.updated_at) }}
                  </div>
                </VCol>
              </VRow>
            </VContainer>
          </VCardText>

          <VCardActions>
            <VSpacer />
            <VBtn
              color="primary"
              variant="elevated"
              @click="close"
            >
              Close
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
  allowedRoles: ['patient']
</route>
