<script setup>
import axiosIns from '@/plugins/axios'
import uDiagnose from '@/utils/diagnose'
import { computed, onMounted } from 'vue'
import { VDataTable } from 'vuetify/labs/VDataTable'

const totalStatistics = ref(null)
const latestDiagnoses = ref([])

const diagnosesStatistics = computed(() => {
  return [
    {
      title: 'Verified',
      stats: totalStatistics.value?.total_verified,
      icon: 'tabler-checks',
      color: 'primary',
    },
    {
      title: 'Not Verified',
      stats: totalStatistics.value?.total_unverified,
      icon: 'tabler-x',
      color: 'warning',
    },
    {
      title: 'Normal',
      stats: totalStatistics.value?.total_patient_normal,
      icon: 'tabler-checks',
      color: 'success',
    },
    {
      title: 'Myocardial Infarction',
      stats: totalStatistics.value?.total_patient_mi,
      icon: 'tabler-alert-triangle',
      color: 'error',
    },
  ]
})

onMounted(async () => {
  axiosIns.get('/api/v1/patient/dashboard/total-statistic').then(response => {
    totalStatistics.value = response.data.data
  })
  axiosIns.get('/api/v1/patient/dashboard/latest-diagnose').then(response => {
    latestDiagnoses.value = response.data.data.diagnoses?.map(item => ({
      ...item,
      is_verified: item.is_verified == 1,
    }))
  })
})

const headers = [
  { title: 'Updated', key: 'updated_at' },
  { title: 'Notes', key: 'notes' },
  { title: 'Diagnoses', key: 'diagnoses' },
  { title: 'Verification', key: 'is_verified' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const sortBy = ref([{ key: 'updated_at', order: 'desc' }])

function formatTimestamp(timestamp) {
  const date = new Date(timestamp)
  
  return date.toLocaleString(navigator.language)
}
</script>

<template>
  <VCol
    cols="12"
    sm="6"
    md="8"
  >
    <VCard title="Diagnoses">
      <VCardText>
        <VRow>
          <VCol
            v-for="item in diagnosesStatistics"
            :key="item.title"
            cols="6"
          >
            <div class="d-flex align-center gap-4">
              <VAvatar
                :color="item.color"
                variant="tonal"
                size="64"
                rounded="sm"
              >
                <VIcon
                  :icon="item.icon"
                  size="32"
                />
              </VAvatar>

              <div class="d-flex flex-column">
                <span class="text-h4 font-weight-medium">{{ item.stats }}</span>
                <span class="text-body">
                  {{ item.title }}
                </span>
              </div>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
  <VCol>
    <VCard
      title="Latest Diagnoses"
      density="compact"
    >
      <VCardText>
        <VRow>
          <VCol>
            <VDataTable
              v-model:sort-by="sortBy"
              :headers="headers"
              :items="latestDiagnoses"
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
                  <RouterLink :to="`/patient/diagnoses?diagnoses_id=${item.raw.id}`">
                    <IconBtn>
                      <VIcon
                        icon="mdi-information-outline"
                        color="primary"
                      />
                    </IconBtn>
                  </RouterLink> 
                </div>
              </template>

              <!-- remove footer pagination -->
              <template #bottom />
            </VDataTable>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>

<style lang="scss">
@use "@core-scss/template/libs/apex-chart.scss";
</style>

